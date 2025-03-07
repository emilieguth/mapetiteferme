<?php
new Page()
	->get('index', function($data) {

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();

		throw new ViewAction($data);

	});

new \accounting\FinancialYearPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
	}
)
	->create(function($data) {

		throw new ViewAction($data);

	})
	->doCreate(function($data) {

		throw new ReloadAction('accounting', 'FinancialYear::created');

	})
	->update(function($data) {

		throw new ViewAction($data);

	})
	->doUpdate(function($data) {

		throw new ReloadAction('accounting', 'FinancialYear::updated');

	})
	->write('close', function($data) {

		\accounting\FinancialYearLib::closeFinancialYear($data->e);

		throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear?success=accounting:FinancialYear::closed');
	});
?>
