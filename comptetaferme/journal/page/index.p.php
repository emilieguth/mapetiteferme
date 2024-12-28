<?php
(new Page())
	->get('index', function($data) {

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company);

		$data->cOperation = \journal\OperationLib::getAll();
		$data->cOperationGrouped = \journal\OperationLib::getGrouped();
		$data->cAccount = \journal\AccountLib::getAll();

		throw new ViewAction($data);

	});
?>