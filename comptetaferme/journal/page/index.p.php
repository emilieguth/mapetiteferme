<?php
(new Page())
	->get('index', function($data) {

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company);

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();

		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
		$data->eFinancialYearSelected = get_exists('financialYear')
			? \accounting\FinancialYearLib::getById(GET('financialYear'))
			: $data->eFinancialYearCurrent;

		$data->search = new Search([
			'date' => GET('date'),
			'accountLabel' => GET('accountLabel'),
			'description' => GET('description'),
			'type' => GET('type'),
			'lettering' => GET('lettering'),
			'financialYear' => $data->eFinancialYearSelected
		], GET('sort'));

		$data->cOperation = \journal\OperationLib::getAll($data->search);
		$data->cOperationGrouped = \journal\OperationLib::getGrouped($data->search);
		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	});
?>