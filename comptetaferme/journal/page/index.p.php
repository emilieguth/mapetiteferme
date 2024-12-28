<?php
(new Page())
	->get('index', function($data) {

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company);

		$data->cOperation = \journal\OperationLib::getAll();

		throw new ViewAction($data);

	});
?>