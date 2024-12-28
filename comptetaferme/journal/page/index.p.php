<?php
(new Page())
	->get('index', function($data) {

		$company = GET('company');

		$data->search = new Search([
			'date' => GET('date'),
			'accountLabel' => GET('accountLabel'),
			'description' => GET('description'),
			'type' => GET('type'),
			'lettering' => GET('lettering'),
		], GET('sort'));

		$data->eCompany = \company\CompanyLib::getById($company);

		$data->cOperation = \journal\OperationLib::getAll($data->search);
		$data->cOperationGrouped = \journal\OperationLib::getGrouped($data->search);
		$data->cAccount = \journal\AccountLib::getAll();

		throw new ViewAction($data);

	});
?>