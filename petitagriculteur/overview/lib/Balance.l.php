<?php
namespace overview;

Class BalanceLib {

	public static function getAccountLabelsWithAmort(array $accountLabels): array {

		$accountLabelsWithAmort = [];
		foreach($accountLabels as $accountLabel) {
			$accountLabelsWithAmort[] = $accountLabel;
			$accountLabelsWithAmort[] = (int)(mb_substr($accountLabel, 0, 1).'8'.mb_substr($accountLabel, 1));
		}

		return $accountLabelsWithAmort;

	}

	public static function getBalance(\accounting\FinancialYear $eFinancialYear): array {

		$categories = \accounting\AccountUi::getAssetBalanceCategories() + \accounting\AccountUi::getLiabilityBalanceCategories();
		$accountLabels = new BalanceUi()->extractLabelsFromCategories($categories);
		$accountLabelsWithAmort = self::getAccountLabelsWithAmort($accountLabels);

		[$resultTable, ] = \journal\AnalyzeLib::getResult($eFinancialYear);
		$result = array_sum(array_column($resultTable, 'credit')) - array_sum(array_column($resultTable, 'debit'));

		$where = implode('%" OR accountLabel LIKE "', $accountLabelsWithAmort);
		$case = '';
		foreach($accountLabelsWithAmort as $accountLabel) {
			$case .= ' WHEN accountLabel LIKE "'.$accountLabel.'%" THEN '.$accountLabel;
			$case .= ' WHEN accountLabel LIKE "'.$accountLabel.'%" THEN '.$accountLabel;
		}

		$cOperation = \journal\Operation::model()
			->select([
				'accountPrefix' => new \Sql('CASE '.$case.' END'),
				'amount' => new \Sql('ABS(SUM(if(type = "debit" OR accountLabel LIKE "139%", -1 * amount, amount)))', 'float')
			])
			->where('accountLabel LIKE "'.$where.'"')
			->where('date BETWEEN "'.$eFinancialYear['startDate'].'" AND "'.$eFinancialYear['endDate'].'"')
			->group('accountPrefix')
			->getCollection(NULL, NULL, 'accountPrefix');

		if($result > 0) {
			$cOperation->offsetSet(120, new \journal\Operation(['accountPrefix' => '120', 'amount' => $result]));
		} else {
			$cOperation->offsetSet(129, new \journal\Operation(['accountPrefix' => '129', 'amount' => $result]));
		}

		// We set all the amortissements to negative values.
		foreach($cOperation as &$eOperation) {
			$secondCar = mb_substr($eOperation['accountPrefix'], 1, 1);
			if($secondCar === '8' or str_starts_with($eOperation['accountPrefix'], '139')) {
				$eOperation['amount'] *= -1;
			}
		}

		return $cOperation->getArrayCopy();

	}

}

?>
