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

		$data->e->merge([
			'company' => $data->eCompany['id'],
			'account' => $eAccount,
			'accountLabel' => $label,
			'vatRate' => $eAccount['vatRate'] ?? 0,
		]);

		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		throw new ViewAction($data);

	})
	->doCreate(function($data) {

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
