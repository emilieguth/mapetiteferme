<?php
namespace overview;

/**
 * Dans le bilan, il y a uniquement les comptes 1, 2, 3, 4, 5.
 */
Class BalanceLib {

	private static function isAmortAccount(string $accountLabel): bool {

		return (mb_substr($accountLabel, 1, 1) === '8');

	}

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

		// Résultat en compte 120 si > 0, en compte 129 si < 0
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

		$balanceData = $cOperation->getArrayCopy();

		$balanceAssetCategories = \Setting::get('accounting\balanceAssetCategories');
		$balanceLiabilityCategories = \Setting::get('accounting\balanceLiabilityCategories');

		return [
			'asset' => self::formatBalanceData($balanceData, $balanceAssetCategories),
			'liability' => self::formatBalanceData($balanceData, $balanceLiabilityCategories),
		];
	}

	protected static function formatBalanceData(array $balance, array $categories): array {

		$totalValue = 0;
		$totalAmort = 0;
		$totalNet = 0;

		$formattedData = [];

		$allLabels = new BalanceUi()->extractLabelsFromCategories($categories);

		foreach($balance as $balanceLine) {

			if(in_array((int)$balanceLine['accountPrefix'], $allLabels) === FALSE) {
				continue;
			}

			if(self::isAmortAccount($balanceLine['accountPrefix']) === TRUE) {
				$totalAmort += $balanceLine['amount'];
			} else {
				$totalValue += $balanceLine['amount'];
			}

		}

		$totalNet += $totalValue - $totalAmort;

		foreach($categories as $subCategories) {
			$name = $subCategories['name'];
			$categories = $subCategories['categories'];

			$totalCategoryValue = 0;
			$totalCategoryAmort = 0;
			$totalCategoryNet = 0;

			foreach($categories as $categoryDetails) {

				$categoryName = $categoryDetails['name'];
				$accounts = $categoryDetails['accounts'];

				$totalSubCategoryValue = 0;
				$totalSubCategoryAmort = 0;
				$totalSubCategoryNet = 0;
				foreach($accounts as $account) {

					$value = $balance[$account]['amount'] ?? 0;
					$accountAmort = mb_substr($account, 0, 1).'8'.mb_substr($account, 1);
					$valueAmort = $balance[$accountAmort]['amount'] ?? 0;
					$net = $value + $valueAmort;

					$totalSubCategoryValue += $value;
					$totalSubCategoryAmort += $valueAmort;
					$totalSubCategoryNet += $net;

					$formattedData[] = [
						'type' => 'line',
						'label' => \accounting\AccountUi::getLabelByAccount($account),
						'value' => $value,
						'valueAmort' => $valueAmort,
						'net' => $net,
						'total' => NULL,
					];

				}

				$formattedData[] = [
					'type' => 'subcategory',
					'label' => $categoryName,
					'value' => $totalSubCategoryValue,
					'valueAmort' => $totalSubCategoryAmort,
					'net' => $totalSubCategoryNet,
					'total' => $totalNet,
				];

				$totalCategoryValue += $totalSubCategoryValue;
				$totalCategoryAmort += $totalSubCategoryAmort;
				$totalCategoryNet += $totalSubCategoryNet;
			}

			$formattedData[] = [
				'type' => 'category',
				'label' => $name,
				'value' => $totalCategoryValue,
				'valueAmort' => $totalCategoryAmort,
				'net' => $totalCategoryNet,
				'total' => $totalNet,
			];

		}

		$formattedData[] = [
			'type' => 'total',
			'label' => '',
			'value' => $totalValue,
			'valueAmort' => $totalAmort,
			'net' => $totalNet,
			'total' => $totalNet,
		];

		return $formattedData;

	}

}

?>
