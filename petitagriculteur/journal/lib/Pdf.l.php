<?php
namespace journal;

class PdfLib extends PdfCrud {

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

			exec('node '.LIME_DIRECTORY.'/petitagriculteur/main/nodejs/pdf.js '.$args.' 2>&1');

			if(LIME_ENV === 'dev') {
				d('node '.LIME_DIRECTORY.'/petitagriculteur/main/nodejs/pdf.js '.$args.' 2>&1');
			}

			$content = file_get_contents($file);

			unlink($file);

			return $content;

		}, fn() => throw new \FailAction('journal\Pdf::fileLocked'), 5
		);

	}

	public static function generate(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, string $type): void {

		switch($type) {
			case 'overview-balance-summary';
				$url = \company\CompanyUi::urlOverview($eCompany).'/pdf/balance:summary?financialYear='.$eFinancialYear['id'].'&key='.\Setting::get('main\remoteKey');
				$header = new \overview\PdfUi()->getHeader($eFinancialYear);
				$footer = new \overview\PdfUi()->getFooter();
			break;

			default:
				throw new \NotExpectedAction('Unknown pdf type');
		}

		$content = self::build($url, $header, $footer);

		Pdf::model()->beginTransaction();

		$ePdfContent = new PdfContent();
		PdfContent::model()->insert($ePdfContent);

		$hash = NULL;
		new \media\PdfContentLib()->send($ePdfContent, $hash, $content, 'pdf');

		$ePdf = new Pdf(['content' => $ePdfContent]);

		Pdf::model()
		   ->option('add-replace')
		   ->insert($ePdf);

		Pdf::model()->commit();

	}
}
?>
