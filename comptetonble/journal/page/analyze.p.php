<?php
(new \bank\CashflowPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');
		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
	}
))
->get(['/journal/analyze/bank', '/journal/analyze/bank/{financialYear}'], function($data) {

	if (get_exists('financialYear')) {
		$eFinancialYearSelected = \accounting\FinancialYearLib::getById(GET('financialYear'));
	} else {
		$eFinancialYearSelected = new \accounting\FinancialYear();
	}
	$data->eFinancialYearSelected = $eFinancialYearSelected->exists() === true ? $eFinancialYearSelected : $data->eFinancialYearCurrent;

	$data->cOperation = \journal\AnalyzeLib::getBankOperationsByMonth($data->eFinancialYearSelected);

	throw new ViewAction($data, ':analyseBank');

});

?>