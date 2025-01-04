<?php
namespace journal;

class ThirdPartyLib extends ThirdPartyCrud {

	public static function getPropertiesCreate(): array {
		return ['name'];
	}

	public static function getAllThirdPartiesWithAccounts(): \Collection {

		return ThirdParty::model()
			->select(ThirdParty::getSelection())
			->sort('name', SORT_ASC)
			->getCollection();

	}
}
?>