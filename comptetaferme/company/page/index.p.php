<?php
(new Page(function($data) {

		\user\ConnectionLib::checkLogged();

		$data->eCompany = \company\CompanyLib::getById(GET('id'));

		\company\EmployeeLib::register($data->eCompany);

		$data->tipNavigation = 'close';

	}))
	->get('/company/{id}', function($data) {

		throw new ViewAction($data);

	})
	->get('/company/{id}/configuration', function($data) {

		throw new ViewAction($data);

	})
	->get('/company/{id}/finances', function($data) {

		throw new ViewAction($data);

	})
	->get('/company/{id}/fournisseurs', function($data) {

		throw new ViewAction($data);

	})
	->get('/company/{id}/clients', function($data) {

		throw new ViewAction($data);

	});
?>
