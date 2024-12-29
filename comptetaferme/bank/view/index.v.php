<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Tous les flux de trésorerie importés");
	$t->tab = 'bank';
	$t->subNav = (new \company\CompanyUi())->getBankSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlBank($data->eCompany);

	$t->mainTitle = (new \bank\BankUi())->getBankTitle($data->eCompany);


	echo (new \bank\CashflowUi())->getSearch($data->search, $data->eFinancialYearSelected);
	echo (new \bank\CashflowUi())->getCashflow($data->eCompany, $data->cCashflow, $data->eFinancialYearSelected, $data->search);

});
