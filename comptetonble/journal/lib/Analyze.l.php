<?php
namespace journal;

class AnalyzeLib {


	public static function getChargeOperationsByMonth(\accounting\FinancialYear $eFinancialYear): array {

		$cAccount = \accounting\Account::model()
			->select([
				'class',
				'description' => new \Sql('LOWER(description)'),
			])
			->where(new \Sql('SUBSTRING(class, 1, 1) = "'.\Setting::get('accounting\chargeAccountClass').'"'))
			->where('LENGTH(class) = 2')
			->sort(['description' => SORT_ASC])
			->getCollection();

		$cOperation = Operation::model()
			->select([
				'big_class' => new \Sql('SUBSTRING(m2.class, 1, 2)'),
				'total' => new \Sql('SUM(amount)'),
			])
			->whereDate('>=', $eFinancialYear['startDate'])
			->whereDate('<=', $eFinancialYear['endDate'])
			->where(new \Sql('SUBSTRING(m2.class, 1, 1) = "'.\Setting::get('accounting\chargeAccountClass').'"'))
			->join(\accounting\Account::model(), 'm1.account = m2.id')
			->group(['m1_big_class'])
			->getCollection(NULL, NULL, 'big_class');

		return [$cOperation, $cAccount];

	}

	public static function getBankOperationsByMonth(\accounting\FinancialYear $eFinancialYear): \Collection {

		$eAccountBankClass = \accounting\AccountLib::getBankClassAccount();

		$cOperation = Operation::model()
			->select([
				'month' => new \Sql('DATE_FORMAT(date, "%Y-%m")'),
				'credit' => new \Sql('SUM(IF(type = "credit", amount, 0))'),
				'debit' => new \Sql('SUM(IF(type = "debit", amount, 0))'),
				'total' => new \Sql('SUM(IF(type = "debit", amount, -amount))'),
			])
			->whereDate('>=', $eFinancialYear['startDate'])
			->whereDate('<=', $eFinancialYear['endDate'])
			->whereAccount($eAccountBankClass)
			->group(['month'])
			->sort(['month' => SORT_ASC])
			->getCollection();

		$lastSolde = 0;
		foreach($cOperation as &$eOperation) {
			$eOperation['total'] += $lastSolde;
			$lastSolde = $eOperation['total'];
		}

		return $cOperation;

	}


}
?>