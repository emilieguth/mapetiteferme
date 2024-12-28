<?php
namespace journal;

class AccountLib extends AccountCrud {

	public static function getAll($query = ''): \Collection {

		return Account::model()
			->select(['name' => new \Sql('CONCAT(class, ". ", description)')] + Account::getSelection())
			->sort('class')
			->where('class LIKE "%'.$query.'%" OR LOWER(description) LIKE "%'.strtolower($query).'%"', if: $query !== '')
			->getCollection(NULL, NULL, 'id');

	}

}
?>