<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les balances de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'overview';
	$t->subNav = new \company\CompanyUi()->getOverviewSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlOverview($data->eCompany).'/balance';

	$t->mainTitle = new overview\OverviewUi()->getAccountingTitle($data->eCompany, $data->eFinancialYear);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlOverview($data->eCompany).'/balance?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

	echo new overview\AccountingUi()->displayAccountingBalanceSheet($data->accountingBalanceSheet);
	echo new overview\AccountingUi()->displaySummaryAccountingBalance($data->summaryAccountingBalance);

});

?>
