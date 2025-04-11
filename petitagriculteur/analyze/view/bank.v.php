<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("La trésorerie de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->subNav = new \company\CompanyUi()->getAnalyzeSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlAnalyze($data->eCompany).'/bank';

	$t->mainTitle = new \analyze\BankUi()->getTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlAnalyze($data->eCompany).'/bank?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

	echo new \analyze\BankUi()->get([$data->cOperationBank, $data->cOperationCash]);

});
