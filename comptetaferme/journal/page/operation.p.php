<?php
(new \journal\OperationPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
	}
))
	->create(function($data) {

		$eAccount = get_exists('account') ? \accounting\AccountLib::getByIdWithVatAccount(GET('account', 'int')) : new \accounting\Account();
		$data->e->merge([
			'company' => $data->eCompany['id'],
			'account' => $eAccount,
			'accountLabel' => GET('accountLabel') ?? '',
			'vatRate' => $eAccount['vatRate'] ?? $eAccount['vatRate'] ?? 0,
		]);

		$data->eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		throw new ViewAction($data);

	})
	->doCreate(function($data) {

		throw new ReloadAction('journal', 'Operation::created');

	})
	->update(function($data) {

		$data->e->merge([
			'account' => \accounting\AccountLib::getById($data->e['id']),
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