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
	->get('addShipping', function($data) {

		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		$eAccountOriginalOperation = get_exists('account') ? \accounting\AccountLib::getById(GET('account', 'int')) : new \accounting\Account();
		if($eAccountOriginalOperation->exists() === FALSE) {
			throw new NotExpectedAction('Account should have been given before creating shipping operation');
		}
		// Search for the corresponding shipping account
		$eAccount = \accounting\AccountLib::getShippingAccountByOperationAccount($eAccountOriginalOperation);

		$thirdParty = \journal\ThirdPartyLib::getByName(GET('thirdParty'));

		$data->eOperation = new \journal\Operation([
      'company' => $data->eCompany['id'],
      'account' => $eAccount,
      'vatRate' => $eAccount['vatRate'] ?? 0,
      'thirdParty' => $thirdParty,
			'date' => GET('date'),
			'description' => GET('description'),
			'document' => GET('document'),
			'type' => GET('type'),
    ]);

		throw new ViewAction($data);

	})
	->post('doCreate', function($data) {

		\journal\OperationLib::createOperation($_POST);

		throw new ReloadAction('journal', 'Operation::created');

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
