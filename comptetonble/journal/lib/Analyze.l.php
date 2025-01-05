<?php
namespace journal;

class AnalyzeLib {


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