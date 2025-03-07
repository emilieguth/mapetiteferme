<?php
(new Page())
	->get('index', function($data) {

		\Setting::set('main\viewJournal', 'journal');

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

		$search = new Search([
			'date' => GET('date'),
			'accountLabel' => GET('accountLabel'),
			'description' => GET('description'),
			'type' => GET('type'),
			'document' => GET('document'),
		], GET('sort'));

		$search->set('cashflowFilter', GET('cashflowFilter', 'bool'));

		$hasSort = get_exists('sort') === TRUE;
		$data->search = clone $search;
		// Ne pas ouvrir le bloc de recherche
		$search->set('financialYear', $data->eFinancialYearSelected);

		$data->eCashflow = \bank\CashflowLib::getById(GET('cashflow'));
		if($data->eCashflow->exists() === TRUE) {
			$search->set('cashflow', GET('cashflow'));
		}

		$data->cOperation = \journal\OperationLib::getAllForJournal($search, $hasSort);
		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	});
?>
