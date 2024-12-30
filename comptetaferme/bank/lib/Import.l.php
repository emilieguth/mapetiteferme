<?php
namespace bank;

class ImportLib extends ImportCrud {

	public static function getAll(): \Collection {
		return Import::model()
			->select(Import::getSelection() + ['account' => Account::getSelection()])
			->getCollection();
	}

	public static function importBankStatement(): ?string {

		if(isset($_FILES['ofx']) === FALSE) {
			return null;
		}

		$filepath = $_FILES['ofx']['tmp_name'];
		$filename = $_FILES['ofx']['name'];

		// Vérification de la taille (max 1 Mo)
		if(filesize($filepath) > 1024 * 1024) {
			\Fail::log('Import::ofxSize');
			return null;
		}

		try {

			$xmlFile = \bank\OfxParserLib::extractFile($filepath);;

			$eAccount = \bank\OfxParserLib::extractAccount($xmlFile);

			$import = \bank\OfxParserLib::extractImport($xmlFile);

			$eImport = new Import([
				'filename' => $filename,
				'account' => $eAccount,
				'startDate' => $import['startDate'],
				'endDate' => $import['endDate'],
				'result' => [],
				'status' => ImportElement::PROCESSING,
			]);

			Import::model()->insert($eImport);

			$cashflows = \bank\OfxParserLib::extractOperations($xmlFile, $eAccount, $eImport);
			$result = \bank\CashflowLib::insertMultiple($cashflows);

			if (count($result['imported']) === 0) {
				\Fail::log('Import::nothingImported');
				$status = ImportElement::NONE;
			} else if (count($result['alreadyImported']) > 0 or count($result['invalidDate']) > 0) {
				$status = ImportElement::PARTIAL;
			} else {
				$status = ImportElement::FULL;
			}

		} catch (\Exception $e) {

			\Fail::log('Import::ofxError');
			$status = ImportElement::ERROR;
			$result = [];

		}

		$eImport['result'] = $result;
		$eImport['status'] = $status;
		$eImport['processedAt'] = Import::model()->now();
		self::update($eImport, ['result', 'status', 'processedAt']);

		return $status;

	}

}
?>