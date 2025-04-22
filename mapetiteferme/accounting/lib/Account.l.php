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
	
	public static function getByClass(string $class): Account {

		$eAccount = new Account();

		Account::model()
			->select(Account::getSelection())
			->whereClass($class)
			->get($eAccount);

		return $eAccount;

	}

	public static function countByClass(string $class): int {

		return Account::model()
			->whereClass($class)
			->count();

	}

	public static function getByClasses(array $classes, string $index = 'id'): \Collection {

		return Account::model()
			->select(
				['name' => new \Sql('CONCAT(class, ". ", description)')]
				+ Account::getSelection()
				+ ['vatAccount' => ['class', 'vatRate', 'description']]
			)
			->whereClass('IN', $classes)
			->getCollection(NULL, NULL, $index);

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

	public static function getByPrefixWithVatAccount(string $prefix): Account {

		$eAccount = new Account();
		Account::model()
		       ->select(
			       ['name' => new \Sql('CONCAT(class, ". ", description)')]
			       + Account::getSelection()
			       + ['vatAccount' => ['class', 'vatRate', 'description']]
		       )
		       ->whereClass('LIKE', $prefix.'%')
		       ->get($eAccount);

		return $eAccount;

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

	public static function getAll(?\Search $search = new \Search(), string $query = ''): \Collection {

		return Account::model()
      ->select(
        ['name' => new \Sql('CONCAT(class, ". ", description)')]
        + Account::getSelection()
        + ['vatAccount' => ['class', 'vatRate', 'description']]
      )
			->sort(['class' => SORT_ASC])
			->where('class LIKE "%'.$query.'%" OR description LIKE "%'.$query.'%"', if: $query !== '')
			->where('class LIKE "'.$search->get('classPrefix').'%"', if: $search->get('classPrefix'))
			->whereClass('LIKE', '%'.$search->get('class').'%', if: $search->get('class'))
			->whereDescription('LIKE', '%'.$search->get('description').'%', if: $search->get('description'))
			->whereCustom(TRUE, if: $search->get('customFilter') === TRUE)
			->where('vatAccount IS NOT NULL', if: $search->get('vatFilter') === TRUE)
			->getCollection(NULL, NULL, 'id');
	}

	public static function orderAccounts(\Collection $cAccount, ?int $thirdParty, array $accountsAlreadyUsed): \Collection {

		if($thirdParty === NULL and count($accountsAlreadyUsed) === 0) {
			return $cAccount;
		}

		$eThirdParty = \journal\ThirdPartyLib::getById($thirdParty);

		$cOperationThirdParty = \journal\OperationLib::getByThirdPartyAndOrderedByUsage($eThirdParty);

		$cAccountByThirdParty = new \Collection();
		$cAccountOthers = new \Collection();

		// Comptes liés au tiers en priorité :
		// - triés par nombre d'usages décroissants
		// - en mettant classes 4 (comptes de tiers : TVA) et classe 5 (comptes financiers) et classes déjà utilisées
		$cAccountClassThird = new \Collection();
		$cAccountClassAfter = new \Collection();

		foreach($cOperationThirdParty as $eOperation) {

			if($cAccount->offsetExists($eOperation['account']['id']) === TRUE) {

				$eAccount = $cAccount->offsetGet($eOperation['account']['id']);
				$eAccount['thirdParty'] = TRUE;

				if(in_array($eAccount['id'], $accountsAlreadyUsed) === TRUE) {

						$cAccountClassAfter->append($eAccount);

				} else {

					switch((int)mb_substr($eAccount['class'], 0, 1)) {

						case \Setting::get('accounting\thirdAccountGeneralClass'):
							$cAccountClassThird->append($eAccount);
							break;

						case \Setting::get('accounting\bankAccountGeneralClass'):
							$cAccountClassAfter->append($eAccount);
							break;

						default:
							$cAccountByThirdParty->append($cAccount->offsetGet($eOperation['account']['id']));
					}

				}

			}

		}

		$cAccountByThirdParty->mergeCollection($cAccountClassThird);
		$cAccountByThirdParty->mergeCollection($cAccountClassAfter);

		// On empile tous les autres comptes
		foreach($cAccount as $eAccount) {
			if($cOperationThirdParty->offsetExists($eAccount['id']) === FALSE) {
				$eAccount['thirdParty'] = FALSE;
				$cAccountOthers->append($eAccount);
			}
		}

		return $cAccountByThirdParty->mergeCollection($cAccountOthers);

	}

	public static function createCustomClass(array $input): void {

		$fw = new \FailWatch();

		$eAccount = new Account();
		$eAccount->build(['class', 'description', 'vatAccount', 'vatRate'], $input);

		$fw->validate();

		$eAccount['custom'] = TRUE;

		Account::model()->insert($eAccount);

	}

	public static function delete(Account $e): void {

		if($e['custom'] === FALSE) {
			throw new \NotExpectedAction();
		}

		if(\journal\OperationLib::countByAccount($e) > 0) {
			throw new \NotExpectedAction();
		}

		Account::model()->delete($e);

	}

}
?>
