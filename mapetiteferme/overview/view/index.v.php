<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les bilans de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'overview';
	$t->subNav = new \company\CompanyUi()->getOverviewSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlOverview($data->eCompany);

	$t->mainTitle = new overview\OverviewUi()->getOverviewTitle($data->eCompany, $data->eFinancialYear);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlOverview($data->eCompany).'/?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

});

?>
