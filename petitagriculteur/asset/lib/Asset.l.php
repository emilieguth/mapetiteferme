<?php
namespace asset;

class AssetLib extends \asset\AssetCrud {

	public static function getPropertiesCreate(): array {
		return ['value', 'type', 'description', 'mode', 'acquisitionDate', 'startDate', 'duration'];
	}
	public static function getPropertiesUpdate(): array {
		return ['value', 'type', 'description', 'mode', 'acquisitionDate', 'startDate', 'duration', 'status'];
	}

	public static function getAcquisitions(\accounting\FinancialYear $eFinancialYear, string $type): \Collection {

		return Asset::model()
			->select(Asset::getSelection())
			->whereAcquisitionDate('>=', $eFinancialYear['startDate'])
			->whereAcquisitionDate('<=', $eFinancialYear['endDate'])
			->whereAccountLabel('LIKE', match($type) {
				'asset' => \Setting::get('accounting\assetClass').'%',
				'subvention' => \Setting::get('accounting\subventionAssetClass').'%',
			})
			->sort(['accountLabel' => SORT_ASC, 'startDate' => SORT_ASC])
			->getCollection();

	}

	public static function getSubventionsByFinancialYear(\accounting\FinancialYear $eFinancialYear): \Collection {

		return Asset::model()
      ->select(
        Asset::getSelection()
        + ['account' => \accounting\Account::getSelection()]
      )
      ->whereStartDate('<=', $eFinancialYear['endDate'])
			->whereAccountLabel('LIKE', \Setting::get('accounting\subventionAssetClass').'%')
      ->sort(['accountLabel' => SORT_ASC, 'startDate' => SORT_ASC])
      ->getCollection();
	}

	public static function getAssetsByFinancialYear(\accounting\FinancialYear $eFinancialYear): \Collection {

		return Asset::model()
			->select(
				Asset::getSelection()
				+ ['account' => \accounting\Account::getSelection()]
			)
			->whereStartDate('<=', $eFinancialYear['endDate'])
			->whereAccountLabel('LIKE', \Setting::get('accounting\assetClass').'%')
			->sort(['accountLabel' => SORT_ASC, 'startDate' => SORT_ASC])
			->getCollection();

	}

	public static function prepareAsset(\journal\Operation $eOperation, array $assetData, int $index): ?Asset {

		$eOperation->expects(['accountLabel']);

		if(
			(int)mb_substr($eOperation['accountLabel'], 0, 1) !== \Setting::get('accounting\assetClass')
			and
			(int)mb_substr($eOperation['accountLabel'], 0, 2) !== \Setting::get('accounting\subventionAssetClass')
		) {
			return NULL;
		}

		$eAsset = new Asset();
		$fw = new \FailWatch();

		$properties = new \Properties('create');
		$properties->setWrapper(function(string $property) use($index) {
			return 'asset['.$index.']['.$property.']';
		});
		$eAsset->build(['value', 'type', 'acquisitionDate', 'startDate', 'duration'], $assetData, $properties);
		if($fw->ko() === TRUE) {
			return NULL;
		}

		$eAsset['account'] = $eOperation['account'];
		$eAsset['accountLabel'] = $eOperation['accountLabel'];
		$eAsset['description'] = $eOperation['description'];
		$eAsset['endDate'] = date('Y-m-d', strtotime($eAsset['startDate'].' + '.$eAsset['duration'].' year - 1 day'));

		Asset::model()->insert($eAsset);

		return $eAsset;

	}

	public static function deleteByIds(array $ids): void {

		Asset::model()
			->whereId('IN', $ids)
			->delete();

	}

	protected static function calculateThisFinancialYearDepreciation(\accounting\FinancialYear $eFinancialYear, Asset $eAsset): float {

		if($eAsset['type'] === AssetElement::WITHOUT) {
			return 0;
		}

		$base = $eAsset['value'];
		$rate = 1 / $eAsset['duration']; // Durée en années

		// Calcul du nombre de mois complets
		$startDatetime = new \DateTime(max($eFinancialYear['startDate'], $eAsset['startDate']));
		$endDatetime = new \DateTime(min($eFinancialYear['endDate'], $eAsset['endDate']));
		$interval = $startDatetime->diff($endDatetime);
		$months = (int)$interval->format('%m');
		$days = $months * 30; // En comptabilité, un mois fait 30 jours.

		// Ajout du nombre de jours de prorata (début)
		if($eAsset['startDate'] > $eFinancialYear['startDate']) {
			$lastDayOfMonth = date("Y-m-d", mktime(0, 0, 0, (int)date('m', strtotime($eAsset['startDate'])) + 1, 0, date('Y', strtotime($eAsset['startDate']))));

			$days += min(30, (int)date('d', strtotime($lastDayOfMonth)) - (int)date('d', strtotime($eAsset['startDate'])) + 1);
		}

		// Ajout du nombre de jours de prorata (fin)
		if($eAsset['endDate'] < $eFinancialYear['endDate']) {
			$days += min(date('d', strtotime($eAsset['endDate'])), 30);
		}

		return $base * $rate * $days / 360;

	}

	public static function getWithDepreciationsById(int $id): Asset {

		$eAsset = AssetLib::getById($id);

		$cDepreciation = Depreciation::model()
			->select(['amount', 'date', 'type', 'financialYear' => \accounting\FinancialYear::getSelection()])
			->whereAsset($eAsset)
			->sort(['date' => SORT_ASC])
			->getCollection();

		$eAsset['depreciations'] = $cDepreciation;

		return $eAsset;

	}
}
?>
