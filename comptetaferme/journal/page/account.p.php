<?php
(new Page())
	->get('index', function($data) {

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company);
		$data->cAccount = \journal\AccountLib::getAll();

	throw new ViewAction($data);

})
	->post('query', function($data) {

		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company);

		$data->cAccount = \journal\AccountLib::getAll();

		throw new \ViewAction($data);

	});

?>