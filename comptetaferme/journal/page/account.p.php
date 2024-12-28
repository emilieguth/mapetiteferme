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
		$query = POST('query');

		$data->eCompany = \company\CompanyLib::getById($company);

		$data->cAccount = \journal\AccountLib::getAll($query);

		throw new \ViewAction($data);

	});

?>