<?php
namespace journal;

Class StatementLib {

	static $BALANCE_SQL = 'SUM(IF(type = "credit", amount, 0) - IF(type = "debit", amount, 0))';

	private static function extractDescription(array $balance, \Collection $cAccount, \Collection $cAccountBank): string {

		// bank account
		$eAccountBankFound = $cAccountBank->find(fn($eAccount) => $eAccount['label'] === $balance['accountLabel'])->first();
		if($eAccountBankFound !== NULL) {
			return s("{name} {number}", ['name' => new \bank\BankUi()->bankLabel(), 'number' => $eAccountBankFound['id']]);
		}

		// cash
		if(str_starts_with($balance['accountLabel'], \Setting::get('accounting\cashAccountClass')) === TRUE) {
			$number = (int)rtrim(mb_substr($balance['accountLabel'], strlen(\Setting::get('accounting\cashAccountClass'))), '0');
			return s("{name} {number}", ['name' => new \bank\BankUi()->cashLabel(), 'number' => $number + 1]);
		}

		// known account
		$eAccountFound = $cAccount->find(fn($eAccount) => $eAccount['id'] === $balance['accountId'])->first();
		if($eAccountFound['class'] === rtrim($balance['accountLabel'], '0')) {
			return $eAccountFound['description'];
		}

		// custom account
		return $balance['descriptionAny'];

	}

	public static function getAccountingBalanceSheet(\accounting\FinancialYear $eFinancialYear): array {

		$cOperation = Operation::model()
			->select([
				'accountLabel',
				'accountId' => new \Sql('ANY_VALUE(account)', 'int'),
				'descriptionAny' => new \Sql('ANY_VALUE(description)'),
				'startCredit' => new \Sql('SUM(IF(type = "credit" AND date = "'.$eFinancialYear['startDate'].'", amount, 0))', 'float'),
				'startDebit' => new \Sql('SUM(IF(type = "debit" AND date = "'.$eFinancialYear['startDate'].'", amount, 0))', 'float'),
				'moveCredit' => new \Sql('SUM(IF(type = "credit" AND date > "'.$eFinancialYear['startDate'].'", amount, 0))', 'float'),
				'moveDebit' => new \Sql('SUM(IF(type = "debit" AND date > "'.$eFinancialYear['startDate'].'", amount, 0))', 'float'),
				'balanceCredit' => new \Sql('IF('.self::$BALANCE_SQL.' > 0, '.self::$BALANCE_SQL.', 0)', 'float'),
				'balanceDebit' => new \Sql('IF('.self::$BALANCE_SQL.' < 0, -1 * '.self::$BALANCE_SQL.', 0)', 'float'),
			])
			->where(new \Sql('date BETWEEN "'.$eFinancialYear['startDate'].'" AND "'.$eFinancialYear['endDate'].'"'))
			->group('accountLabel')
			->sort(['accountLabel' => SORT_ASC])
			->getCollection();

		$accountingBalanceSheet = $cOperation->getArrayCopy();

		$cAccount = \accounting\AccountLib::getByIds($cOperation->getColumn('accountId'));

		$cOperationLastFinancialYear = self::getLastYearBalance($eFinancialYear);
		$cAccountBank = \bank\AccountLib::getAll();

		foreach($accountingBalanceSheet as &$accountingBalance) {

			$accountingBalance['description'] = self::extractDescription($accountingBalance, $cAccount, $cAccountBank);
			$accountingBalance['lastBalanceCredit'] = $cOperationLastFinancialYear[$accountingBalance['accountLabel']]['balanceCredit'] ?? 0;
			$accountingBalance['lastBalanceDebit'] = $cOperationLastFinancialYear[$accountingBalance['accountLabel']]['balanceDebit'] ?? 0;
		}

		$total = [
			'accountLabel' => 'total',
			'description' => '',
			'startCredit' => 0,
			'startDebit' => 0,
			'moveCredit' => array_sum(array_column($accountingBalanceSheet, 'moveCredit'))
			+ array_sum(array_column($accountingBalanceSheet, 'startCredit')),
			'moveDebit' => array_sum(array_column($accountingBalanceSheet, 'moveDebit'))
			+ array_sum(array_column($accountingBalanceSheet, 'startDebit')),
			'balanceCredit' => array_sum(array_column($accountingBalanceSheet, 'balanceCredit')),
			'balanceDebit' => array_sum(array_column($accountingBalanceSheet, 'balanceDebit')),
			'lastBalanceCredit' => array_sum(array_column($accountingBalanceSheet, 'lastBalanceCredit')),
			'lastBalanceDebit' => array_sum(array_column($accountingBalanceSheet, 'lastBalanceDebit')),
		];
		$accountingBalanceSheet[] = $total;

		return $accountingBalanceSheet;

	}

	public static function getLastYearBalance(\accounting\FinancialYear $eFinancialYear): \Collection {

		$startDate = date('Y-m-d', strtotime($eFinancialYear['startDate'].' - 1 year'));
		$endDate = date('Y-m-d', strtotime($eFinancialYear['endDate']. ' - 1 year'));

		return Operation::model()
       ->select([
         'accountLabel',
         'balanceCredit' => new \Sql('IF('.self::$BALANCE_SQL.' > 0, '.self::$BALANCE_SQL.', 0)', 'float'),
         'balanceDebit' => new \Sql('IF('.self::$BALANCE_SQL.' < 0, -1 * '.self::$BALANCE_SQL.', 0)', 'float'),
       ])
       ->where(new \Sql('date BETWEEN "'.$startDate.'" AND "'.$endDate.'"'))
       ->group('accountLabel')
       ->getCollection(NULL, NULL, 'accountLabel');
	}

}

?>
