<?php
namespace bank;

class OfxParserLib {

	public static function extractFile(string $filepath): \SimpleXMLElement {

		$fileContent = explode("\n", file_get_contents($filepath));

		$fileArray = array_splice($fileContent, 9);

		foreach($fileArray as $index => $line) {

			if (preg_match('/<([A-Z]+)>(.*)/', $line, $matches) and strlen($matches[2]) > 0) {
				$fileArray[$index] = '<'.$matches[1].'>'.$matches[2].'</'.$matches[1].'>';
			}

		}

		$xmlContent = implode("\n", $fileArray);

		return simplexml_load_string($xmlContent);

	}

	public static function extractAccount(\SimpleXMLElement $xmlElement): Account {

		$bankId = $xmlElement->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->BANKID;
		$accountId = $xmlElement->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->ACCTID;

		if(strlen($bankId) === 0 or strlen($accountId) === 0) {
			return new Account();
		}

		return AccountLib::getFromOfx($bankId, $accountId);

	}

	public static function extractOperations(\SimpleXMLElement $xmlElement, Account $eAccount): array {

		$cashflows = [];

		foreach($xmlElement->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $operation) {

			$cashflows[] = [
				'date' => (string) $operation->DTPOSTED,
				'amount' => (float) $operation->TRNAMT,
				'type' => (string) $operation->TRNTYPE,
				'fitid' => (string) $operation->FITID,
				'name' => (string) $operation->NAME,
				'memo' => (string) $operation->MEMO,
				'account' => $eAccount,
			];

		}

		return $cashflows;

	}
}
?>