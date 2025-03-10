<?php
new Page(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');

		\Setting::set('main\viewBank', 'cashflow');
	}
)
	->get('index', function($data) {

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
			'fitid' => GET('fitid'),
			'memo' => GET('memo'),
		], GET('sort'));
		$hasSort = get_exists('sort') === TRUE;
		$data->search = clone $search;

		// Ne pas ouvrir le bloc de recherche pour ces champs
		$search->set('financialYear', $data->eFinancialYearSelected);
		if(get_exists('import') === TRUE) {
			$search->set('import', GET('import'));
			$data->eImport = \bank\ImportLib::getById(GET('import', 'int'));
		} else {
			$data->eImport = new \bank\Import();
		}

		$data->cCashflow = \bank\CashflowLib::getAll($search, $hasSort);

		throw new ViewAction($data);

	});

new \bank\CashflowPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		$data->eCashflow = \bank\CashflowLib::getById(INPUT('id'))->validate('canAllocate');

		\Setting::set('main\viewBank', 'import');
		$data->eFinancialYearCurrent = \accounting\FinancialYearLib::selectDefaultFinancialYear();
	}
)
	->get('allocate', function($data) {

		throw new ViewAction($data);

	})
	->post('addAllocate', function($data) {

		$data->index = POST('index');
		throw new ViewAction($data);

	})
	->post('doAllocate', function($data) {
		$data->eCashflow = \bank\CashflowLib::getById(INPUT('id'),
			\bank\Cashflow::getSelection() +
			[
				'import' => \bank\Import::getSelection() +
					['account' => \bank\Account::getSelection()]
			]
		);
		$fw = new FailWatch();

		\journal\Operation::model()->beginTransaction();

		$accounts = post('account', 'array', []);

		if(count($accounts) === 0) {
			Fail::log('Cashflow::allocate.accountsCheck');
		}

		$cOperation = \journal\OperationLib::prepareOperations($_POST, new \journal\Operation(['cashflow' => $data->eCashflow, 'date' => $data->eCashflow['date']]));

		if($cOperation->empty() === TRUE) {
			\Fail::log('Cashflow::allocate.noOperation');
		}

		$fw->validate();

		\bank\Cashflow::model()->update(
			$data->eCashflow,
			['status' => \bank\CashflowElement::ALLOCATED, 'document' => POST('cashflow[document]'), 'updatedAt' => \bank\Cashflow::model()->now()]
		);

		\journal\Operation::model()->commit();

		throw new ReloadAction('bank', 'Cashflow::allocated');

	})
	->post('deAllocate', function($data) {

		$fw = new FailWatch();

		if($data->eCashflow->exists() === FALSE) {
			\bank\Cashflow::fail('internal');
		}

		$fw->validate();

		\journal\Operation::model()
			->whereCashflow('=', $data->eCashflow['id'])
			->delete();

		$data->eCashflow['status'] = \bank\CashflowElement::WAITING;
		\bank\CashflowLib::update($data->eCashflow, ['status']);

		throw new ReloadAction('bank', 'Cashflow::deallocated');

	})
	->get('attach', function($data) {

		$data->cOperation = \journal\OperationLib::getOperationsForAttach($data->eCashflow);

		throw new ViewAction($data);

	})
	->post('doAttach', function($data) {

		$fw = new FailWatch();

		if($data->eCashflow->exists() === FALSE) {
			\bank\Cashflow::fail('internal');
		}

		if(post_exists('operation') === FALSE) {
			\bank\Cashflow::fail('noSelectedOperation');
		}

		\bank\CashflowLib::attach($data->eCashflow, POST('operation', 'array'));

		$fw->validate();

		throw new ReloadAction('bank', 'Cashflow::attached');

	});
?>
