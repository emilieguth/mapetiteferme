<?php
new AdaptativeView('analyseBank', function($data, CompanyTemplate $t) {

	$t->title = s("La trésorerie de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/analyze/bank';
	$t->subNav = (new \company\CompanyUi())->getAnalyzeSubNav($data->eCompany);

	$t->mainTitle = (new \journal\AnalyzeUi())->getBankTitle($data->eCompany);

	$t->mainYear = (new \accounting\FinancialYearUi())->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlJournal($data->eCompany).'/analyze/bank/'.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo (new \journal\AnalyzeUi())->getBank($data->eCompany, $data->eFinancialYearSelected, $data->cOperation);


});

new AdaptativeView('analyseCharge', function($data, CompanyTemplate $t) {

	$t->title = s("Les charges de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/analyze/charges';
	$t->subNav = (new \company\CompanyUi())->getAnalyzeSubNav($data->eCompany);

	$t->mainTitle = (new \journal\AnalyzeUi())->getChargesTitle($data->eCompany);

	$t->mainYear = (new \accounting\FinancialYearUi())->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlJournal($data->eCompany).'/analyze/charges/'.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo (new \journal\AnalyzeUi())->getCharges($data->eCompany, $data->eFinancialYearSelected, $data->cOperation, $data->cAccount);


});

?>