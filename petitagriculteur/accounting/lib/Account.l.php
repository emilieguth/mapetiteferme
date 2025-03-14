<?php
namespace accounting;

class AccountLib extends AccountCrud {

	public static function getByClassWithVatAccount(string $class): Account {

		$eAccount = new Account();

		Account::model()
			->select([
         'name' => new \Sql('CONCAT(class, ". ", description)')]
	       + Account::getSelection()
	       + ['vatAccount' => ['class', 'vatRate', 'description']
       ])
			->whereClass($class)
			->get($eAccount);

		return $eAccount;

	}
	public static function getByIdsWithVatAccount(array $ids): \Collection {

		return Account::model()
			->select(
				['name' => new \Sql('CONCAT(class, ". ", description)')]
				+ Account::getSelection()
				+ ['vatAccount' => ['class', 'vatRate', 'description']]
			)
			->whereId('IN', $ids)
			->getCollection(NULL, NULL, 'id');

	}

	public static function getByIdWithVatAccount(int $id): Account {

		$eAccount = new Account();
		Account::model()
			->select(
				['name' => new \Sql('CONCAT(class, ". ", description)')]
				+ Account::getSelection()
				+ ['vatAccount' => ['class', 'vatRate', 'description']]
			)
			->whereId('=', $id)
			->get($eAccount);

		return $eAccount;
	}

	public static function getAll($query = ''): \Collection {

		return Account::model()
      ->select(
        ['name' => new \Sql('CONCAT(class, ". ", description)')]
        + Account::getSelection()
        + ['vatAccount' => ['class', 'vatRate', 'description']]
      )
			->sort('class')
			->where('class LIKE "%'.$query.'%" OR description LIKE "%'.strtolower($query).'%"', if: $query !== '')
			->getCollection(NULL, NULL, 'id');
	}

	public static function getBankClassAccount(): \Element {

		return Account::model()
			->select(Account::getSelection())
			->whereClass('=', \Setting::get('accounting\bankAccountClass'))
			->get();

	}

	public static function orderAccountsWithThirdParty(int $thirdParty, \Collection $cAccount): \Collection {

		$eThirdParty = \journal\ThirdPartyLib::getById($thirdParty);

		$cOperationThirdParty = \journal\OperationLib::getByThirdPartyAndOrderedByUsage($eThirdParty);

		$cAccountByThirdParty = new \Collection();
		$cAccountOthers = new \Collection();

		// Comptes liés au tiers en priorité (et triés par nombre d'usages décroissants)
		foreach($cOperationThirdParty as $eOperation) {
			if($cAccount->offsetExists($eOperation['account']['id']) === TRUE) {
				$cAccountByThirdParty->append($cAccount->offsetGet($eOperation['account']['id']) );
			}
		}

		// On empile tous les autres comptes
		foreach($cAccount as $eAccount) {
			if($cOperationThirdParty->offsetExists($eAccount['id']) === FALSE) {
				$cAccountOthers->append($eAccount);
			}
		}

		return $cAccountByThirdParty->mergeCollection($cAccountOthers);

	}

}
?>
