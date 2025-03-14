<?php
(new Page())
	->get('index', function($data) {

		\Setting::set('main\viewJournal', 'book');

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
		if($data->cFinancialYear->empty() === TRUE) {
			throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear:create?message=FinancialYear::toCreate');
		}

		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
		$data->eFinancialYearSelected = get_exists('financialYear')
			? \accounting\FinancialYearLib::getById(GET('financialYear'))
			: $data->eFinancialYearCurrent;

		$search = new Search(['financialYear' => $data->eFinancialYearSelected]);

		$data->cOperation = \journal\OperationLib::getAllForBook($search);
		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	});
?>
