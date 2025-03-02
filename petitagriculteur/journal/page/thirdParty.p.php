<?php
(new \journal\ThirdPartyPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
	}
))
	->get('index', function($data) {

		$data->cThirdParty = \journal\ThirdPartyLib::getAll();

		throw new ViewAction($data);

	})
	->create(function($data) {

		throw new ViewAction($data);

	})
	->doCreate(function($data) {

		throw new ViewAction($data);

	});

(new Page(function($data) {

	\user\ConnectionLib::checkLogged();

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');
}))
->post('query', function($data) {

	$query = POST('query');

	$data->cThirdParty = \journal\ThirdPartyLib::getAll($query);

	throw new \ViewAction($data);

})
?>