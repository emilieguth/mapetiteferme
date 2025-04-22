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

	public static function filterByCashflow(\Collection $cThirdParty, \bank\Cashflow $eCashflow): \Collection {

		$memoItems = explode(' ', $eCashflow['memo']);

		foreach($cThirdParty as &$eThirdParty) {
			$eThirdParty['weight'] = 0;

			foreach($memoItems as $memoItem) {
				if(mb_strpos($eThirdParty['name'], $memoItem) !== FALSE) {
					$eThirdParty['weight'] ++;
				}
			}
		}

		return $cThirdParty->sort(['weight' => SORT_DESC, 'name' => SORT_ASC]);

	}

}
?>
