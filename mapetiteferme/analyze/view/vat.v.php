<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("La TVA de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->canonical = \company\CompanyUi::urlAnalyze($data->eCompany).'/vat';
	$t->subNav = new \company\CompanyUi()->getAnalyzeSubNav($data->eCompany);

	$t->mainTitle = new \analyze\VatUi()->getTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlAnalyze($data->eCompany).'/result?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

	echo new \analyze\VatUi()->getByMonth($data->eCompany, $data->eFinancialYear, $data->cOperation);
	echo new \analyze\VatUi()->get($data->result, $data->cAccount);

});
