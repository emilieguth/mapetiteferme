<?php
namespace accounting;

class AccountLib extends AccountCrud {

	public static function getAll($query = ''): \Collection {

		return Account::model()
			->select(['name' => new \Sql('CONCAT(class, ". ", description)')] + Account::getSelection())
			->sort('class')
			->whereClass('class LIKE "%'.$query.'%" OR description LIKE "%'.strtolower($query).'%"', if: $query !== '')
			->getCollection(NULL, NULL, 'id');
	}

}
?>