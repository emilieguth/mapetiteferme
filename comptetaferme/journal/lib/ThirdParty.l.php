<?php
namespace journal;

class ThirdPartyLib extends ThirdPartyCrud {

	public static function getPropertiesCreate(): array {
		return ['name'];
	}

	public static function getAll(): \Collection {

		return ThirdParty::model()
			->select(ThirdParty::getSelection())
			->sort('name', SORT_ASC)
			->getCollection();

	}

	public static function getByName(string $name): ThirdParty|\Element {

		return ThirdParty::model()
			->select(ThirdParty::getSelection())
			->whereName('=', $name)
			->get();

	}
}
?>