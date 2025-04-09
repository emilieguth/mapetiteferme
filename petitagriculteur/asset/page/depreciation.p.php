<?php
new Page()
	->get('index', function($data) {

		\Setting::set('main\viewAsset', 'depreciation');

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
		if($data->cFinancialYear->empty() === TRUE) {
			throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear:create?message=FinancialYear::toCreate');
		}

		$data->eFinancialYear = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));

		$data->assetDepreciations = \asset\DepreciationLib::getByFinancialYear($data->eFinancialYear, 'asset');
		$data->subventionDepreciations = \asset\DepreciationLib::getByFinancialYear($data->eFinancialYear, 'subvention');

		throw new ViewAction($data);

	});
