<?php
namespace journal;

class PdfLib extends PdfCrud {

	public static function build($url): string {

		return \Cache::redis()->lock(
			'pdf-'.$url, function () use ($url) {

			$file = tempnam('/tmp', 'pdf-').'.pdf';

			$url .= str_contains($url, '?') ? '&' : '?';
			$url .= 'key='.\Setting::get('main\remoteKey');

			$args = '"--url='.$url.'"';
			$args .= ' "--destination='.$file.'"';

			exec('node '.LIME_DIRECTORY.'/petitagriculteur/main/nodejs/pdf.js '.$args.' 2>&1');

			$content = file_get_contents($file);

			unlink($file);

			return $content;

		}, fn() => throw new \FailAction('journal\Pdf::fileLocked'), 5
		);

	}

	public static function generate(\company\Company $eCompany): void {

		//$url = \company\CompanyUi::urlJournal($eCompany).'/';
		// Temporary
		$url = 'https://www.petitagriculteur.fr';
		$content = self::build($url);

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
