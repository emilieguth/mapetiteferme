<?php
(new Page(function($data) {

	\user\ConnectionLib::checkLogged();

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');
}))
	->get('index', function($data) {

		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	})
	->post('query', function($data) {

		$query = POST('query');

		$data->cAccount = \accounting\AccountLib::getAll($query);

		throw new \ViewAction($data);

	});

?>