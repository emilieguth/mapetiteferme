<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les imports bancaires de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'bank';
	$t->subNav = (new \company\CompanyUi())->getBankSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlBank($data->eCompany).'/import';

	$t->js()->replaceHistory($t->canonical);
	$t->package('main')->updateHeader(
		$this->tab,
		'import',
		$this->getCompanyNav(),
		$this->getCompanySubNav(),
	);

	$t->mainTitle = (new \bank\ImportUi())->getImportTitle($data->eCompany);

	$t->mainYear = (new \accounting\FinancialYearUi())->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlJournal($data->eCompany).'/?financialYear='.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo (new \bank\ImportUi())->getImport($data->eCompany, $data->cImport, $data->eFinancialYearSelected);

});

new AdaptativeView('import', function($data, PanelTemplate $t) {

	return (new \bank\CashflowUi())->import($data->eCompany);

});