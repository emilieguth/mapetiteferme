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

	Setting::set('main\viewAnalyze', 'bank');

	if (get_exists('financialYear')) {
		$eFinancialYearSelected = \accounting\FinancialYearLib::getById(GET('financialYear'));
	} else {
		$eFinancialYearSelected = new \accounting\FinancialYear();
	}
	$data->eFinancialYearSelected = $eFinancialYearSelected->exists() === true ? $eFinancialYearSelected : $data->eFinancialYearCurrent;

	$data->cOperation = \journal\AnalyzeLib::getBankOperationsByMonth($data->eFinancialYearSelected);

	throw new ViewAction($data, ':analyseBank');

})
	->get(['/journal/analyze/charges', '/journal/analyze/charges/{financialYear}'], function($data) {

		Setting::set('main\viewAnalyze', 'charges');

		if (get_exists('financialYear')) {
			$eFinancialYearSelected = \accounting\FinancialYearLib::getById(GET('financialYear'));
		} else {
			$eFinancialYearSelected = new \accounting\FinancialYear();
		}
		$data->eFinancialYearSelected = $eFinancialYearSelected->exists() === true ? $eFinancialYearSelected : $data->eFinancialYearCurrent;

		[$data->cOperation, $data->cAccount] = \journal\AnalyzeLib::getChargeOperationsByMonth($data->eFinancialYearSelected);

		throw new ViewAction($data, ':analyseCharge');

	});

?>