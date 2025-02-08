<?php
namespace bank;

class AccountLib extends AccountCrud {

	public static function getAll(): \Collection {

		return Account::model()
			->select(Account::getSelection())
			->sort(['accountId' => SORT_ASC])
			->getCollection();
	}

	public static function getFromOfx(string $bankId, string $accountId): Account {

		$eAccount = new Account();

		Account::model()
			->select(Account::getSelection())
			->whereBankId($bankId)
			->whereAccountId($accountId)
			->get($eAccount);

		if ($eAccount->exists() === false) {

			$eAccount = new Account([
				'bankId' => $bankId,
				'accountId' => $accountId,
			]);

			Account::model()->insert($eAccount);
		}

		return $eAccount;
	}


	public static function update(Account $e, array $properties): void {
		parent::update($e, $properties);

		// Quick label update
		if ($properties === ['label']) {
			\journal\OperationLib::updateAccountLabels($e);
		}
	}
}
?>