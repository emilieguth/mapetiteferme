<?php
(new Page(function($data) {

		\user\ConnectionLib::checkLogged();

		$data->eCompany = \company\CompanyLib::getById(GET('company'));

		\company\EmployeeLib::register($data->eCompany);

		$data->tipNavigation = 'close';

	}))
	->get('/company', function($data) {

		throw new ViewAction($data);

	});
?>
