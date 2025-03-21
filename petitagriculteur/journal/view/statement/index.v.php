<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les bilans de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'statement';
	$t->subNav = new \company\CompanyUi()->getStatementSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlStatement($data->eCompany);

	$t->mainTitle = new journal\StatementUi()->getStatementTitle($data->eCompany, $data->eFinancialYear);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlStatement($data->eCompany).'/?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

});

?>
