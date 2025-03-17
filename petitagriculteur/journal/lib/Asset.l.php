<?php
namespace journal;

class AssetLib extends AssetCrud {

	public static function getPropertiesCreate(): array {
		return ['value', 'type', 'mode', 'acquisitionDate', 'startDate', 'duration'];
	}
	public static function getPropertiesUpdate(): array {
		return ['value', 'type', 'mode', 'acquisitionDate', 'startDate', 'duration', 'status'];
	}
}
?>
