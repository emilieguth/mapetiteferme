<?php
namespace journal;

class ThirdPartyLib extends ThirdPartyCrud {

	public static function getPropertiesCreate(): array {
		return ['name'];
	}

	public static function getAll(string $query): \Collection {

		return ThirdParty::model()
			->select(ThirdParty::getSelection())
			->whereName('LIKE', '%'.$query.'%', if: $query !== null and mb_strlen($query) > 0)
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