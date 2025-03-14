<?php
new \journal\ThirdPartyPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = REQUEST('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
	}
)
	->get('index', function($data) {

		$data->search = new Search([
			'name' => GET('name'),
		], GET('sort'));

		$data->cThirdParty = \journal\ThirdPartyLib::getAll($data->search);
		$cOperation = \journal\OperationLib::countGroupByThirdParty();
		foreach($data->cThirdParty as &$eThirdParty) {
			$eThirdParty['operations'] = $cOperation[$eThirdParty['id']]['count'] ?? 0;
		}

		throw new ViewAction($data);

	})
	->create(function($data) {

		throw new ViewAction($data);

	})
	->doCreate(function($data) {

		throw new ViewAction($data);

	})
	->quick(['name']);

new Page(function($data) {

	\user\ConnectionLib::checkLogged();

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');
})
->post('query', function($data) {

	$data->search = new Search([
		'name' => POST('query'),
	], GET('sort'));

	$data->cThirdParty = \journal\ThirdPartyLib::getAll($data->search);

	throw new \ViewAction($data);

})
?>
