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
		if(get_exists('label') and mb_strlen(GET('label') > 0)) {
			$label = GET('label');
		} else if($eAccount->exists() === TRUE and $eAccount['class'] === \Setting::get('accounting\bankAccountClass')) {
			$eAccountBank = \bank\AccountLib::getDefaultAccount();
			if($eAccountBank->exists() === TRUE) {
				$label = $eAccountBank['label'];
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

	})
	->update(function($data) {

		$data->e->merge([
			'account' => \accounting\AccountLib::getById($data->e['id'], \accounting\Account::getSelection() + ['vatAccount' => \accounting\Account::getSelection()]),
		]);
		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		throw new ViewAction($data);

	})
	->doUpdate(function($data) {
		throw new ReloadAction('journal', 'Operation::updated');
	})
	->doDelete(function($data) {
		throw new ReloadAction('journal', 'Operation::deleted');
	});
?>
