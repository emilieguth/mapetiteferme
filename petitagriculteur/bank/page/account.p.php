<?php
new \bank\AccountPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = REQUEST('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		\company\CompanyLib::connectSpecificDatabaseAndServer($data->eCompany);

		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
	}
)
	->quick(['label'])
	->get('index', function($data) {

		$data->cAccount = \bank\AccountLib::getAll();
		throw new ViewAction($data);

	});
?>
