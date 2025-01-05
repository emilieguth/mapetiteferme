<?php
namespace bank;

class AccountLib extends AccountCrud {

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

}
?>