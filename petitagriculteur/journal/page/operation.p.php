<?php
new \journal\OperationPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = REQUEST('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		\company\CompanyLib::connectSpecificDatabaseAndServer($data->eCompany);
	}
)
	->quick(['document', 'description', 'amount'], [], ['canQuickUpdate'])
	->create(function($data) {

		if(get_exists('account') === TRUE) {
			$eAccount = \accounting\AccountLib::getByIdWithVatAccount(GET('account', 'int'));
		} elseif(get_exists('accountPrefix') === TRUE) {
			$eAccount = \accounting\AccountLib::getByPrefixWithVatAccount(GET('accountPrefix', 'int'));
		} else {
			$eAccount = new \accounting\Account();
		}

		if(get_exists('cashflow') === TRUE) {
			$eCashflow = \bank\CashflowLib::getById(GET('cashflow', 'int'));
		} else {
			$eCashflow = new \bank\Cashflow();
		}
		// Apply default bank account label if the class is a bank account class.
		$label = '';
		if(get_exists('accountLabel') and mb_strlen(GET('accountLabel') > 0)) {
			$label = GET('accountLabel');
		} elseif($eAccount->exists() === TRUE and $eAccount['class'] === \Setting::get('accounting\bankAccountClass')) {
			$eAccountBank = \bank\AccountLib::getDefaultAccount();
			if($eAccountBank->exists() === TRUE) {
				$label = $eAccountBank['accountLabel'];
			}
		}

		// Third party
		$thirdParty = \journal\ThirdPartyLib::getById(GET('thirdParty', 'int'));

		$data->e->merge([
			'company' => $data->eCompany['id'],
			'account' => $eAccount,
			'accountLabel' => $label,
			'vatRate' => $eAccount['vatRate'] ?? 0,
			'thirdParty' => $thirdParty,
			'date' => GET('date'),
			'description' => GET('description'),
			'document' => GET('document'),
			'type' => GET('type'),
			'amount' => GET('amount', 'float'),
			'cashflow' => $eCashflow,
		]);

		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		throw new ViewAction($data);

	})
	->post('addOperation', function($data) {

		$data->index = POST('index');
		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		$eThirdParty = post_exists('thirdParty') ? \journal\ThirdPartyLib::getById(POST('thirdParty')) : new \journal\ThirdParty();
		$data->eOperation = new \journal\Operation(['account' => new \accounting\Account(), 'thirdParty' => $eThirdParty]);

		throw new ViewAction($data);

	})
	->post('doCreate', function($data) {

		$fw = new FailWatch();

		\journal\Operation::model()->beginTransaction();

		$accounts = post('account', 'array', []);

		if(count($accounts) === 0) {
			Fail::log('Operation::allocate.accountsCheck');
		}

		$cOperation = \journal\OperationLib::prepareOperations($_POST, new \journal\Operation());

		if($cOperation->empty() === TRUE) {
			\Fail::log('Operation::allocate.noOperation');
		}

		$fw->validate();

		\journal\Operation::model()->commit();

		throw new ReloadAction('journal', $cOperation->count() > 1 ? 'Operation::createdSeveral' : 'Operation::created');

	});

	new \journal\OperationPage(
		function($data) {
			\user\ConnectionLib::checkLogged();
			$company = REQUEST('company');

			$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
			\company\CompanyLib::connectSpecificDatabaseAndServer($data->eCompany);
			$data->eOperation = \journal\OperationLib::getById(REQUEST('id', 'int'))->validate('canUpdate');
		}
	)
	->post('doDelete', function($data) {

		\journal\OperationLib::delete($data->eOperation);

		throw new ReloadAction('journal', 'Operation::deleted');
	});
?>
