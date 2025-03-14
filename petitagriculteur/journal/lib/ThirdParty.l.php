<?php
namespace journal;

class ThirdPartyLib extends ThirdPartyCrud {

	public static function getPropertiesCreate(): array {
		return ['name'];
	}

	public static function getAll(\Search $search): \Collection {

		$search->validateSort(['id', 'name']);

		return ThirdParty::model()
			->select(ThirdParty::getSelection())
      ->whereName('LIKE', '%'.$search->get('name').'%', if: $search->get('name'))
			->sort($search->buildSort())
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
