<?php
namespace journal;

class AccountLib extends AccountCrud {

	public static function getAll(): \Collection {

		return Account::model()
			->select(['name' => new \Sql('CONCAT(class, ". ", description)')] + Account::getSelection())
			->sort('class')
			->getCollection(NULL, NULL, 'id');

	}

}
?>