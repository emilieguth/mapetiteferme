<?php

new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Le résultat de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->canonical = \company\CompanyUi::urlAnalyze($data->eCompany).'/result';
	$t->subNav = new \company\CompanyUi()->getAnalyzeSubNav($data->eCompany);

	$t->mainTitle = new \analyze\ResultUi()->getTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlAnalyze($data->eCompany).'/result?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \analyze\ResultUi()->getByMonth($data->eCompany, $data->eFinancialYearSelected, $data->cOperation);
	echo new \analyze\ResultUi()->get($data->result, $data->cAccount);

});
