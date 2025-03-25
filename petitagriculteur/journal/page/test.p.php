<?php
new Page(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = REQUEST('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		\company\CompanyLib::connectSpecificDatabaseAndServer($data->eCompany);
		if(LIME_ENV !== 'dev') {
			throw new NotExistsAction();
		}
	})
	->get('index', function($data) {

		journal\PdfLib::generate($data->eCompany);
		throw new ViewAction($data);

	});

?>
