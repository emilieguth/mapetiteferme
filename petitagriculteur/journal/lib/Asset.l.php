<?php
namespace journal;

class AssetLib extends \journal\AssetCrud {

	public static function getPropertiesCreate(): array {
		return ['value', 'type', 'description', 'mode', 'acquisitionDate', 'startDate', 'duration'];
	}
	public static function getPropertiesUpdate(): array {
		return ['value', 'type', 'description', 'mode', 'acquisitionDate', 'startDate', 'duration', 'status'];
	}

	public static function prepareAsset(Operation $eOperation, array $assetData, int $index): ?Asset {

		$eOperation->expects(['accountLabel']);

		if((int)mb_substr($eOperation['accountLabel'], 0, 1) !== \Setting::get('accounting\assetClass')) {
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

		$eAsset['accountLabel'] = $eOperation['accountLabel'];
		$eAsset['description'] = $eOperation['description'];
		$eAsset['endDate'] = date('Y-m-d', strtotime($eAsset['startDate'].' + '.$eAsset['duration'].' year'));

		Asset::model()->insert($eAsset);

		return $eAsset;

	}

	public static function deleteByIds(array $ids): void {

		Asset::model()
			->whereId('IN', $ids)
			->delete();

	}

	public static function getSummary(\accounting\FinancialYear $eFinancialYear): array {

		$cAsset = Asset::model()
			->select(Asset::getSelection())
			->whereEndDate('>=', $eFinancialYear['startDate'])
			->whereStartDate('<=', $eFinancialYear['endDate'])
			->getCollection();

		$assets = [];
		foreach($cAsset as $eAsset) {

			$accountLabel = $eAsset['accountLabel'];

			if(isset($assets[$accountLabel]) === FALSE) {
				$assets[$accountLabel] = [
					'accountName' => '', // Chercher un moyen de récupérer le nom du compte via le accountLabel qui peut être différent >.<
					'accountLabel' => $accountLabel,

					// Valeurs brutes
					'grossValue' => [

						'startValue' => 0, // Valeur en début d'exercice fiscal
						'buyValue' => 0, // Valeur d'achat (si achat en cours d'exercice fiscal)
						'decrease' => 0, // ??
						'endValue' => 0, // Valeur en fin d'exercice

					],

					// Valeurs économiques
					'economic' => [

						'startFinancialYear' => 0, // Début d'exercice
						'globalIncrease' => 0, // Amortissement global
						'linearIncrease' => 0, // Amortissement global dont linéaire
						'degressiveIncrease' => 0, // Amortissement global dont dégressif
						'decrease' => 0, // Diminution
						'endFinancialYear' => 0, // Fin d'exercice

					],

					'netBookValue' => 0, // VNC

					// Amortissement dérogatoire
					'excess' => [

						'startFinancialYear' => 0,
						'depreciation' => 0, // Dotation
						'reversal' => 0, // Reprise
						'endFinancialYear' => 0,
					],

					'netFinancialValue' => 0, // VNF

				];
			}


			// Acquisition en cours d'exercice
			if($eAsset['acquisitionDate'] >= $eFinancialYear['startDate']) {

				$assets[$accountLabel]['grossValue']['buyValue'] += $eAsset['value'];

			} else { // Acquis avant : indiquer dans grossValue-startValue la valeur de fin d'exercice de l'année passée

			}

		}

		$total = [
			'accountName' => 'total',
			'accountLabel' => 'total',

			// Valeurs brutes
			'grossValue' => [

				'startValue' => array_reduce($assets, fn($res, $asset) => $res + $asset['grossValue']['startValue'], 0),
				'buyValue' => array_reduce($assets, fn($res, $asset) => $res + $asset['grossValue']['buyValue'], 0),
				'decrease' => array_reduce($assets, fn($res, $asset) => $res + $asset['grossValue']['decrease'], 0),
				'endValue' => array_reduce($assets, fn($res, $asset) => $res + $asset['grossValue']['endValue'], 0),

			],

			// Valeurs économiques
			'economic' => [

				'startFinancialYear' => array_reduce($assets, fn($res, $asset) => $res + $asset['economic']['startFinancialYear'], 0),
				'globalIncrease' => array_reduce($assets, fn($res, $asset) => $res + $asset['economic']['globalIncrease'], 0),
				'linearIncrease' => array_reduce($assets, fn($res, $asset) => $res + $asset['economic']['linearIncrease'], 0),
				'degressiveIncrease' => array_reduce($assets, fn($res, $asset) => $res + $asset['economic']['degressiveIncrease'], 0),
				'decrease' => array_reduce($assets, fn($res, $asset) => $res + $asset['economic']['decrease'], 0),
				'endFinancialYear' => array_reduce($assets, fn($res, $asset) => $res + $asset['economic']['endFinancialYear'], 0),

			],

			'netBookValue' => array_sum(array_column($assets, 'netBookValue')),

			// Amortissement dérogatoire
			'excess' => [

				'startFinancialYear' => array_reduce($assets, fn($res, $asset) => $res + $asset['excess']['startFinancialYear'], 0),
				'depreciation' => array_reduce($assets, fn($res, $asset) => $res + $asset['excess']['depreciation'], 0),
				'reversal' => array_reduce($assets, fn($res, $asset) => $res + $asset['excess']['reversal'], 0),
				'endFinancialYear' => array_reduce($assets, fn($res, $asset) => $res + $asset['excess']['endFinancialYear'], 0),
			],

			'netFinancialValue' => array_sum(array_column($assets, 'netFinancialValue')),

		];

		ksort($assets);

		$assets['total'] = $total;

		return $assets;

	}
}
?>
