<?php
(new Page(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		\Setting::set('main\viewBank', 'cashflow');
	}
))
	->get('index', function($data) {

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();

		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
		$data->eFinancialYearSelected = get_exists('financialYear')
			? \accounting\FinancialYearLib::getById(GET('financialYear'))
			: $data->eFinancialYearCurrent;

		$search = new Search([
			'date' => GET('date'),
			'fitid' => GET('fitid'),
			'memo' => GET('memo'),
		], GET('sort'));
		$hasSort = get_exists('sort') === TRUE;
		$data->search = clone $search;

		// Ne pas ouvrir le bloc de recherche pour ces champs
		$search->set('financialYear', $data->eFinancialYearSelected);
		if (GET('import')) {
			$search->set('import', GET('import'));
		}

		$data->cCashflow = \bank\CashflowLib::getAll($search, $hasSort);

		throw new ViewAction($data);

	});

(new \bank\CashflowPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		$data->eCashflow = \bank\CashflowLib::getById(INPUT('id'));

		\Setting::set('main\viewBank', 'import');
		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
	}
))
	->get('allocate', function($data) {

		throw new ViewAction($data);

	})
	->post('addAllocate', function($data) {

		$data->index = POST('index');
		throw new ViewAction($data);

	})
	->post('doAllocate', function($data) {
		$fw = new FailWatch();

		$cOperation = \bank\CashflowLib::prepareAllocate($data->eCashflow, $_POST);

		$fw->validate();

		\journal\Operation::model()->insert($cOperation);

		\bank\Cashflow::model()->update($data->eCashflow, ['status' => \bank\CashflowElement::ALLOCATED]);

		throw new ReloadAction('bank', 'Cashflow::allocated');

	});
?>