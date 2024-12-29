<?php
(new \bank\CashflowPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
		$company = INPUT('company');

		$data->eCompany = \company\CompanyLib::getById($company)->validate('canManage');
	}
))
->get('import', function($data) {

	throw new ViewAction($data);

})
->post('doImport', function($data) {

	$fw = new FailWatch();

	\bank\ImportLib::importBankStatement();

	if($fw->ok()) {
		throw new RedirectAction(\company\CompanyUi::urlBank($data->eCompany).'/?success=bank:Cashflow::imported');
	} else {
		throw new RedirectAction(\company\CompanyUi::urlBank($data->eCompany).'/cashflow:import?error='.$fw->getLast());
	}

});

?>