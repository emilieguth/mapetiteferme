<?php

new Page(function($data) {

	\user\ConnectionLib::checkLogged();

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');

	$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
	if($data->cFinancialYear->empty() === TRUE) {
		throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear:create?message=FinancialYear::toCreate');
	}

	$data->eFinancialYear = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));

})
	->get('index', function($data) {

		throw new \ViewAction($data);

	});
?>
