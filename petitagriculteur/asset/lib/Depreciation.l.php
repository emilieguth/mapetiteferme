<?php
namespace asset;

class DepreciationLib extends \asset\DepreciationCrud {

	private static function computeCurrentFinancialYearExcessDepreciation(Asset $eAsset, float $alreadyDepreciated, \accounting\FinancialYear $eFinancialYear): float {

		return 0.0;

	}

	private static function countMonthsBetweenTwoDates(string $date1, string $date2): int {

		$startDatetime = new \DateTime($date1);
		$endDatetime = new \DateTime($date2);
		$interval = $startDatetime->diff($endDatetime);

		return (int)$interval->format('%m');

	}
	private static function computeCurrentFinancialYearDepreciation(Asset $eAsset, float $alreadyDepreciated, \accounting\FinancialYear $eFinancialYear): float {

		if($eAsset['type'] === AssetElement::WITHOUT) {
			return 0.0;
		}

		$base = $eAsset['value'];
		$rate = 1 / $eAsset['duration']; // duration in year

		// Calcul du nombre de jours dans l'exercice comptable
		$daysInFinancialYear = self::countMonthsBetweenTwoDates(
				$eFinancialYear['startDate'],
				$eFinancialYear['endDate']
			) * 30;

		// Calcul du nombre de mois complets
		$days = self::countMonthsBetweenTwoDates(
			max($eFinancialYear['startDate'], $eAsset['startDate']),
			min($eFinancialYear['endDate'], $eAsset['endDate'])
		) * 30; // En comptabilité, un mois fait 30 jours.

		// Ajout du nombre de jours de prorata (début)
		if($eAsset['startDate'] > $eFinancialYear['startDate']) {
			$lastDayOfMonth = date("Y-m-d", mktime(0, 0, 0, (int)date('m', strtotime($eAsset['startDate'])) + 1, 0, date('Y', strtotime($eAsset['startDate']))));

			$days += min(30, (int)date('d', strtotime($lastDayOfMonth)) - (int)date('d', strtotime($eAsset['startDate'])) + 1);
		}

		// Ajout du nombre de jours de prorata (fin)
		if($eAsset['endDate'] < $eFinancialYear['endDate']) {
			$days += min(date('d', strtotime($eAsset['endDate'])), 30);
		}

		return min($eAsset['value'] - $alreadyDepreciated, round($base * $rate * $days / $daysInFinancialYear, 2));

	}

	public static function getSummary(\accounting\FinancialYear $eFinancialYear): array {

		$depreciations = self::getByFinancialYear($eFinancialYear, 'asset');

		$emptyLine = [
			'account' => '',
			'accountLabel' => '',
			'acquisitionValue' => 0,
			'economic' => [
				'startFinancialYearValue' => 0,
				'currentFinancialYearDepreciation' => 0,
				'currentFinancialYearDegressiveDepreciation' => 0,
				'financialYearDiminution' => 0,
				'endFinancialYearValue' => 0,
			],
			'grossValueDiminution' => 0,
			'netFinancialValue' => 0,
			'excess' => [
				'startFinancialYearValue' => 0,
				'currentFinancialYearDepreciation' => 0,
				'reversal' => 0,
				'endFinancialYearValue' => 0,
			],
			'fiscalNetValue' => 0,
		];

		$lines = [];
		$total = $emptyLine;
		$generalTotal = $emptyLine;

		$currentAccountLabel = NULL;

		foreach($depreciations as $depreciation) {

			if($currentAccountLabel !== NULL and $depreciation['accountLabel'] !== $currentAccountLabel) {

				DepreciationUi::addTotalLine($generalTotal, $total);
				$lines[] = $total;
				$total = $emptyLine;

			}

			$total['accountLabel'] = $depreciation['accountLabel'];
			$total['description'] = $depreciation['accountDescription'];
			$currentAccountLabel = $depreciation['accountLabel'];
			DepreciationUi::addTotalLine($total, $depreciation);

		}

		DepreciationUi::addTotalLine($generalTotal, $total);

		$lines[] = $total;
		$lines[] = $generalTotal;

		return $lines;

	}

	public static function getByFinancialYear(\accounting\FinancialYear $eFinancialYear, string $type): array {

		$cAsset = match($type) {
			'asset' => AssetLib::getAssetsByFinancialYear($eFinancialYear),
			'subvention' => AssetLib::getSubventionsByFinancialYear($eFinancialYear),
		};

		$ccDepreciation = Depreciation::model()
			->select(['asset', 'financialYear', 'amount', 'type'])
			->whereAsset('IN', $cAsset)
			->whereDate('<=', $eFinancialYear['endDate'])
			->getCollection(NULL, NULL, ['asset', 'financialYear']);

		$depreciations = [];

		foreach($cAsset as $eAsset) {

			$accountLabel = $eAsset['accountLabel'];

			$cDepreciation = $ccDepreciation->offsetExists($eAsset['id']) ? $ccDepreciation->offsetGet($eAsset['id']) : new \Collection();

			// sum what has already been depreciated for this asset (during previous financial years)
			$alreadyDepreciated = array_reduce(
				$cDepreciation->getArrayCopy(),
				fn($res, $eDepreciation) => $res + (($eDepreciation['financialYear']['id'] !== $eFinancialYear['id'] and $eDepreciation['type'] === DepreciationElement::ECONOMIC) ? $eDepreciation['amount'] : 0), 0,
			);
			$alreadyExcessDepreciated = array_reduce(
				$cDepreciation->getArrayCopy(),
				fn($res, $eDepreciation) => $res + (($eDepreciation['financialYear']['id'] !== $eFinancialYear['id'] and $eDepreciation['type'] === DepreciationElement::EXCESS) ? $eDepreciation['amount'] : 0), 0,
			);

			// This financial year depreciation
			if($eFinancialYear['status'] === \accounting\FinancialYearElement::CLOSE) {

				$currentDepreciation = ($cDepreciation->offsetExists($eFinancialYear['id']) and $cDepreciation[$eFinancialYear['id']]['type'] === DepreciationElement::ECONOMIC) ? $cDepreciation[$eFinancialYear['id']]['amount'] : 0;
				$currentExcessDepreciation = ($cDepreciation->offsetExists($eFinancialYear['id']) and $cDepreciation[$eFinancialYear['id']]['type'] === DepreciationElement::EXCESS) ? $cDepreciation[$eFinancialYear['id']]['amount'] : 0;

			} else {

				// Estimate
				$currentDepreciation = self::computeCurrentFinancialYearDepreciation($eAsset, $alreadyDepreciated, $eFinancialYear);
				$currentExcessDepreciation = self::computeCurrentFinancialYearExcessDepreciation($eAsset, $alreadyExcessDepreciated, $eFinancialYear);

			}

			$financialYearDiminution = 0; // TODO

			$vnc = $eAsset['value'] - $alreadyDepreciated - $currentDepreciation;
			$vnf = $currentExcessDepreciation > 0 ? $eAsset['value'] - $alreadyExcessDepreciated - $currentExcessDepreciation : $vnc;

			$depreciation = [
				'id' => $eAsset['id'],
				'accountLabel' => $accountLabel,
				'accountDescription' => $eAsset['account']['description'],
				'description' => $eAsset['description'],

				'status' => $eAsset['status'],
				'type' => $eAsset['type'],

				'acquisitionDate' => $eAsset['acquisitionDate'],
				'startDate' => $eAsset['startDate'],
				'endDate' => $eAsset['endDate'],

				'duration' => $eAsset['duration'],

				'acquisitionValue' => $eAsset['value'],

				// Economic depreciation
				'economic' => [
					// Début exercice : NULL si acquis durant l'exercice comptable
					'startFinancialYearValue' => $eAsset['startDate'] >= $eFinancialYear['startDate'] ? NULL : $eAsset['value'] - $alreadyDepreciated,
					'currentFinancialYearDepreciation' => $currentDepreciation,
					'currentFinancialYearDegressiveDepreciation' => 0,
					'financialYearDiminution' => $financialYearDiminution,
					'endFinancialYearValue' => $currentDepreciation - $financialYearDiminution,
				],

				// Diminution de valeur brut
				'grossValueDiminution' => 0, // TODO

				// VNC
				'netFinancialValue' => $vnc,

				// Excess depreciation (amortissement dérogatoire)
				'excess' => [
					'startFinancialYearValue' => 0,
					'currentFinancialYearDepreciation' => $currentExcessDepreciation,
					'reversal' => 0,
					'endFinancialYearValue' => $currentExcessDepreciation,
				],

				// VNF
				'fiscalNetValue' => $vnf,


			];

			$depreciations[] = $depreciation;

		}

		return $depreciations;

	}

}
?>
