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
		$thirdParty = POST('thirdParty');

		$data->cAccount = \accounting\AccountLib::getAll($query);

		if(post_exists('thirdParty') === TRUE) {
			$data->cAccount = \accounting\AccountLib::orderAccountsWithThirdParty($thirdParty, $data->cAccount);

		}

		throw new \ViewAction($data);

	});

?>