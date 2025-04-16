<?php
namespace pdf;

class PdfLib extends \pdf\PdfCrud {

	public static function build(string $url, ?string $header, ?string $footer): string {

		return \Cache::redis()->lock(
			'pdf-'.$url, function () use ($header, $footer, $url) {

			$file = tempnam('/tmp', 'pdf-').'.pdf';

			$url .= str_contains($url, '?') ? '&' : '?';
			$url .= 'key='.\Setting::get('main\remoteKey');

			$args = '"--url='.$url.'"';
			$args .= ' "--destination='.$file.'"';
			if($header !== NULL) {
				$args .= ' "--header='.rawurlencode($header).'"';
			}
			if($footer !== NULL) {
				$args .= ' "--footer='.rawurlencode($footer).'"';
			}

			exec('node '.LIME_DIRECTORY.'/mapetiteferme/main/nodejs/pdf.js '.$args.' 2>&1');

			if(LIME_ENV === 'dev') {
				d('node '.LIME_DIRECTORY.'/mapetiteferme/main/nodejs/pdf.js '.$args.' 2>&1');
			}

			$content = file_get_contents($file);

			unlink($file);

			return $content;

		}, fn() => throw new \FailAction('journal\Pdf::fileLocked'), 5
		);

	}

	private static function generateContent(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, string $type): string {

		switch($type) {
			case PdfElement::OVERVIEW_BALANCE_SUMMARY;
				$url = \company\CompanyUi::urlOverview($eCompany).'/pdf/balance:summary?financialYear='.$eFinancialYear['id'].'&key='.\Setting::get('main\remoteKey');
				$header = PdfUi::getHeader(\overview\PdfUi::getTitle(), $eFinancialYear);
				break;

			case PdfElement::OVERVIEW_BALANCE_OPENING;
				$url = \company\CompanyUi::urlOverview($eCompany).'/pdf/balance:opening?financialYear='.$eFinancialYear['id'].'&key='.\Setting::get('main\remoteKey');
				$header = PdfUi::getHeader(\overview\PdfUi::getBalanceOpeningTitle(), $eFinancialYear);
				break;

			case PdfElement::JOURNAL_INDEX:
				$url = \company\CompanyUi::urlJournal($eCompany).'/pdf/?financialYear='.$eFinancialYear['id'].'&key='.\Setting::get('main\remoteKey');
				$header = PdfUi::getHeader(\journal\PdfUi::getJournalTitle(), $eFinancialYear);
				break;

			case PdfElement::JOURNAL_BOOK:
				$url = \company\CompanyUi::urlJournal($eCompany).'/pdf/book?financialYear='.$eFinancialYear['id'].'&key='.\Setting::get('main\remoteKey');
				$header = PdfUi::getHeader(\journal\PdfUi::getBookTitle(), $eFinancialYear);
				break;

			case PdfElement::JOURNAL_TVA_BUY:
				$url = \company\CompanyUi::urlJournal($eCompany).'/pdf/vat?financialYear='.$eFinancialYear['id'].'&type=buy&key='.\Setting::get('main\remoteKey');
				$header = PdfUi::getHeader(\journal\PdfUi::getVatTitle(PdfElement::JOURNAL_TVA_BUY), $eFinancialYear);
				break;

			case PdfElement::JOURNAL_TVA_SELL:
				$url = \company\CompanyUi::urlJournal($eCompany).'/pdf/vat?financialYear='.$eFinancialYear['id'].'&type=sell&key='.\Setting::get('main\remoteKey');
				$header = PdfUi::getHeader(\journal\PdfUi::getVatTitle(PdfElement::JOURNAL_TVA_SELL), $eFinancialYear);
				break;

			default:
				throw new \NotExpectedAction('Unknown pdf type');
		}

		$footer = PdfUi::getFooter();
		return self::build($url, $header, $footer);

	}

	public static function generate(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, string $type): ?string {

		if($eFinancialYear['status'] === \accounting\FinancialYearElement::CLOSE) {

			$ePdf = Pdf::model()
				->select(Pdf::getSelection() + ['content' => Content::getSelection()])
				->whereFinancialYear($eFinancialYear)
				->whereType($type)
				->get();

			if(
				$ePdf->notEmpty() and
				$ePdf['content']->notEmpty() and
				$ePdf['content']['hash']
			) {

				$ePdf['used'] = new \Sql('used + 1');

				Pdf::model()
					->select(['used'])
					->update($ePdf);

				return self::getContentByPdf($ePdf['content']);
			}
		}
		try {

			$content = self::generateContent($eCompany, $eFinancialYear, $type);

		} catch(\Exception) {

			return NULL;

		}

		if($eFinancialYear['status'] === \accounting\FinancialYearElement::CLOSE) {

			\pdf\Pdf::model()->beginTransaction();

			$eContent = new \pdf\Content();
			\pdf\Content::model()->insert($eContent);

			$hash = NULL;
			new \media\PdfContentLib()->send($eContent, $hash, $content, 'pdf');

			$ePdf = new \pdf\Pdf(['content' => $eContent, 'type' => $type, 'financialYear' => $eFinancialYear]);

			\pdf\Pdf::model()
			   ->option('add-replace')
			   ->insert($ePdf);

			\pdf\Pdf::model()->commit();

		}

		return $content;
	}

	public static function getContentByPdf(Content $eContent): ?string {

		$path = \storage\DriverLib::directory().'/'.new \media\PdfContentUi()->getBasenameByHash($eContent['hash']);
		return file_get_contents($path);

	}

}
?>
