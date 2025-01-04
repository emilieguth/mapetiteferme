<?php
(new \journal\ThirdPartyPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
	}
))
	->get('index', function($data) {

		$data->cThirdParty = \journal\ThirdPartyLib::getAllThirdPartiesWithAccounts();

		throw new ViewAction($data);

	})
	->create(function($data) {

		throw new ViewAction($data);

	})
	->doCreate(function($data) {

		throw new RedirectAction(\company\CompanyUi::urlJournal($data->eCompany).'/thirdParty?success=journal:ThirdParty::created');

	});

?>