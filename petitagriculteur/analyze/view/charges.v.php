<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les charges de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->canonical = \company\CompanyUi::urlAnalyze($data->eCompany).'/charges';
	$t->subNav = new \company\CompanyUi()->getAnalyzeSubNav($data->eCompany);

	$t->mainTitle = new \analyze\ChargesUi()->getTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlAnalyze($data->eCompany).'/charges?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \analyze\ChargesUi()->get($data->eCompany, $data->eFinancialYearSelected, $data->cOperation, $data->cAccount);

});
