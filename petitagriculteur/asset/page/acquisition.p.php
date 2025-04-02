<?php
new Page()
	->get('index', function($data) {

		\Setting::set('main\viewAsset', 'acquisition');

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();

		if($data->cFinancialYear->empty() === TRUE) {
			throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear:create?message=FinancialYear::toCreate');
		}

		$data->eFinancialYear = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));

		$data->cAsset = asset\AssetLib::getAcquisitions($data->eFinancialYear, 'asset');
		$data->cAssetSubvention = asset\AssetLib::getAcquisitions($data->eFinancialYear, 'subvention');

		throw new ViewAction($data);

	});
?>
