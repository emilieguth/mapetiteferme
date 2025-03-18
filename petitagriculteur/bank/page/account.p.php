<?php
new \bank\AccountPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = REQUEST('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		\company\CompanyLib::connectSpecificDatabaseAndServer($data->eCompany);

		$data->eFinancialYearSelected = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));
	}
)
	->quick(['label'])
	->get('index', function($data) {

		$data->cAccount = \bank\AccountLib::getAll();
		throw new ViewAction($data);

	});
?>
