<?php
new \journal\OperationPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = REQUEST('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		\company\CompanyLib::connectSpecificDatabaseAndServer($data->eCompany);
	}
)
	->quick(['document'], [], ['canQuickDocument'])
	->create(function($data) {

		$eAccount = get_exists('account') ? \accounting\AccountLib::getByIdWithVatAccount(GET('account', 'int')) : new \accounting\Account();

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
		$thirdParty = \journal\ThirdPartyLib::getByName(GET('thirdParty'));

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
		]);

		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		throw new ViewAction($data);

	})
	->post('addOperation', function($data) {

		$data->index = POST('index');
		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

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
	->doDelete(function($data) {
		throw new ReloadAction('journal', 'Operation::deleted');
	});
?>
