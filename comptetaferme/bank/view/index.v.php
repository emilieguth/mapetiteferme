<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les flux de trésorerie de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'bank';
	$t->subNav = (new \company\CompanyUi())->getBankSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlBank($data->eCompany);

	$t->mainTitle = (new \bank\BankUi())->getBankTitle($data->eCompany);

	$t->mainYear = (new \accounting\FinancialYearUi())->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlJournal($data->eCompany).'/?financialYear='.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo (new \bank\CashflowUi())->getSearch($data->search, $data->eFinancialYearSelected);
	echo (new \bank\CashflowUi())->getCashflow($data->eCompany, $data->cCashflow, $data->eFinancialYearSelected, $data->search);

});
