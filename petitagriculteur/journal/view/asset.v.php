<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les immobilisations de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'journal';
	$t->subNav = new \company\CompanyUi()->getJournalSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/asset';

	$t->mainTitle = new \journal\AssetUi()->getTitle();

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlJournal($data->eCompany).'/asset?financialYear='.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \journal\AssetUi()->getSummary($data->eCompany, $data->eFinancialYearSelected, $data->cAsset);

});
