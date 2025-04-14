<?php
new \accounting\FinancialYearPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
	}
)
	->get('opening', function($data) {

	});

new \accounting\FinancialYearPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = GET('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
		$data->eFinancialYear = \accounting\FinancialYearLib::getById(GET('id'))->validate('canReadDocument');
	}
)
	->get('fec', function($data) {

		$fecData = \accounting\FecLib::generate($data->eFinancialYear);

		// TODO : date de clôture de l'exercice comptable
		throw new DataAction($fecData, 'text/txt', $data->eCompany['siret'].'FEC'.date('Ymd').'.txt');
	})
	->get('closing', function($data) {

	});
