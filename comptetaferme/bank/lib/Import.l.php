<?php
namespace bank;

class ImportLib {


	public static function importBankStatement(): bool {

		if(isset($_FILES['ofx']) === FALSE) {
			return FALSE;
		}

		$filepath = $_FILES['ofx']['tmp_name'];

		// Vérification de la taille (max 1 Mo)
		if(filesize($filepath) > 1024 * 1024) {
			\Fail::log('ofxSize');
			return FALSE;
		}

		try {

			$xmlFile = \bank\OfxParserLib::extractFile($filepath);;

			$eAccount = \bank\OfxParserLib::extractAccount($xmlFile);

			$cashflows = \bank\OfxParserLib::extractOperations($xmlFile, $eAccount);

			\bank\CashflowLib::insertMultiple($cashflows);

		} catch (\Exception $e) {

			\Fail::log('ofxError');
			return FALSE;

		}

		return TRUE;

	}

}
?>