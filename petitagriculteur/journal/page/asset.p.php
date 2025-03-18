<?php
new Page()
	->get('index', function($data) {

		\Setting::set('main\viewJournal', 'asset');

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
		if($data->cFinancialYear->empty() === TRUE) {
			throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear:create?message=FinancialYear::toCreate');
		}

		$data->eFinancialYearSelected = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));
		$data->assetSummary = \journal\AssetLib::getSummary($data->eFinancialYearSelected);

		throw new ViewAction($data);

	});
?>
