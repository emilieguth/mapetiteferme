<?php
(new Page(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = INPUT('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		\Setting::set('main\viewBank', 'import');
	}
))
	->get('index', function($data) {

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();

		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
		$data->eFinancialYearSelected = get_exists('financialYear')
			? \accounting\FinancialYearLib::getById(GET('financialYear'))
			: $data->eFinancialYearCurrent;


		$data->imports = \bank\ImportLib::formatCurrentFinancialYearImports($data->eFinancialYearSelected);
		$data->cImport = \bank\ImportLib::getAll($data->eFinancialYearSelected);

		throw new ViewAction($data);

	})
	->get('import', function($data) {

		throw new ViewAction($data);

	})
	->post('doImport', function($data) {

		$fw = new FailWatch();

		$result = \bank\ImportLib::importBankStatement();

		if($fw->ok()) {
			throw new RedirectAction(\company\CompanyUi::urlBank($data->eCompany).'/import?success=bank:Import::'.$result);
		} else {
			throw new RedirectAction(\company\CompanyUi::urlBank($data->eCompany).'/import:import?error='.$fw->getLast());
		}

	});
?>