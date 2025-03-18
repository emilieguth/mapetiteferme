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
}
?>
