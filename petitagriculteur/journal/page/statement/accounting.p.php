<?php

new Page(function($data) {

	\user\ConnectionLib::checkLogged();

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');

	$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
	if($data->cFinancialYear->empty() === TRUE) {
		throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear:create?message=FinancialYear::toCreate');
	}

	$data->eFinancialYear = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));
	\Setting::set('main\viewStatement', 'accounting-balance');

})
	->get('index', function($data) {

		$data->accountingBalanceSheet = \journal\StatementLib::getAccountingBalanceSheet($data->eFinancialYear);

		throw new \ViewAction($data);
	});
?>
