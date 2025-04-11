<?php
new Page(function($data) {

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');

	[$data->cFinancialYear, $data->eFinancialYear] = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));

	\Setting::set('main\viewJournal', 'vat');

	$data->eThirdParty = get_exists('thirdParty')
		? \journal\ThirdPartyLib::getById(GET('thirdParty', 'int'))
		: NULL;

	$search = new Search([
		'date' => GET('date'),
		'accountLabel' => GET('accountLabel'),
		'description' => GET('description'),
		'type' => GET('type'),
		'document' => GET('document'),
		'thirdParty' => GET('thirdParty'),
		'asset' => GET('asset'),
	], GET('sort'));

	$search->set('cashflowFilter', GET('cashflowFilter', 'bool'));

	$data->search = clone $search;

})
	->get('buy', function($data) {

		$data->type = 'buy';

		$hasSort = get_exists('sort') === TRUE;
		// Ne pas ouvrir le bloc de recherche
		$search = clone $data->search;
		$search->set('financialYear', $data->eFinancialYear);

		$data->cccOperation = \journal\OperationLib::getAllForVatJournal($data->type, $search, $hasSort);

		throw new ViewAction($data, ':index');

	})
	->get('sell', function($data) {

		$data->type = 'sell';

		$hasSort = get_exists('sort') === TRUE;
		// Ne pas ouvrir le bloc de recherche
		$search = clone $data->search;
		$search->set('financialYear', $data->eFinancialYear);

		$data->cccOperation = \journal\OperationLib::getAllForVatJournal($data->type, $search, $hasSort);

		throw new ViewAction($data, ':index');

	});
