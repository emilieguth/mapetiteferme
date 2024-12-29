<?php
(new Page())
	->get('index', function($data) {

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();

		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
		$data->eFinancialYearSelected = get_exists('financialYear')
			? \accounting\FinancialYearLib::getById(GET('financialYear'))
			: $data->eFinancialYearCurrent;

		$search = new Search([
			'date' => GET('date'),
			'accountLabel' => GET('accountLabel'),
			'description' => GET('description'),
			'type' => GET('type'),
			'lettering' => GET('lettering'),
		], GET('sort'));
		$data->search = clone $search;
		// Ne pas ouvrir le bloc de recherche
		$search->set('financialYear', $data->eFinancialYearSelected);

		$data->cOperation = \journal\OperationLib::getAll($search);
		$data->cOperationGrouped = \journal\OperationLib::getGrouped($search);
		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	});
?>