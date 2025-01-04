<?php
namespace accounting;

class AccountLib extends AccountCrud {

	public static function getByIdsWithVatAccount(array $ids): \Collection {

		return Account::model()
			->select([
					'name' => new \Sql('CONCAT(class, ". ", description)')] +
				Account::getSelection() +
				['vatAccount' => ['class', 'vatRate', 'description']
				])
			->whereId('IN', $ids)
			->getCollection(NULL, NULL, 'id');

	}

	public static function getByIdWithVatAccount(int $id): Account {

		return Account::model()
			->select([
					'name' => new \Sql('CONCAT(class, ". ", description)')] +
				Account::getSelection() +
				['vatAccount' => ['class', 'vatRate']
				])
			->whereId('=', $id)
			->get();

	}

	public static function getAll($query = ''): \Collection {

		return Account::model()
			->select([
				'name' => new \Sql('CONCAT(class, ". ", description)')] +
				Account::getSelection() +
				['vatAccount' => ['class', 'vatRate']
			])
			->sort('class')
			->where('class LIKE "%'.$query.'%" OR description LIKE "%'.strtolower($query).'%"', if: $query !== '')
			->getCollection(NULL, NULL, 'id');
	}

	public static function getBankClassAccount(): Account {

		return Account::model()
			->select(Account::getSelection())
			->whereClass('=', \Setting::get('accounting\bankAccountClass'))
			->get();

	}

}
?>