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
		[$cOperation, $cThirdParty] = \bank\CashflowLib::prepareAllocate($data->eCashflow, $_POST);

		$fw->validate();

		\journal\Operation::model()->insert($cOperation);

		\bank\Cashflow::model()->update(
			$data->eCashflow,
			['memo' => POST('memo'), 'status' => \bank\CashflowElement::ALLOCATED]
		);

		foreach($cThirdParty as $eThirdParty) {
			\journal\ThirdPartyLib::update($eThirdParty, ['accounts']);
		}

		throw new ReloadAction('bank', 'Cashflow::allocated');

	})
	->post('deAllocate', function($data) {

		$fw = new FailWatch();

		if ($data->eCashflow->exists() === FALSE) {
			\bank\Cashflow::fail('internal');
		}

		$fw->validate();

		\journal\Operation::model()
			->whereCashflow('=', $data->eCashflow['id'])
			->delete();

		$data->eCashflow['status'] = \bank\CashflowElement::WAITING;
		\bank\CashflowLib::update($data->eCashflow, ['status']);

		throw new ReloadAction('bank', 'Cashflow::deallocated');

	});
?>