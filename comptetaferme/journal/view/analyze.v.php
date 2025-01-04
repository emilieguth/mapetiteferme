<?php
new AdaptativeView('analyseBank', function($data, CompanyTemplate $t) {


	$t->title = s("La trésorerie de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'statement';
	$t->subNav = (new \company\CompanyUi())->getBankSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlBank($data->eCompany).'/cashflow';

	$t->mainTitle = (new \journal\AnalyzeUi())->getBankTitle($data->eCompany);

	$t->mainYear = (new \accounting\FinancialYearUi())->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlJournal($data->eCompany).'/analyze/bank/'.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	//echo (new \bank\CashflowUi())->getSearch($data->search, $data->eFinancialYearSelected);
	echo (new \journal\AnalyzeUi())->getBank($data->eCompany, $data->eFinancialYearSelected, $data->cOperation);


});

?>