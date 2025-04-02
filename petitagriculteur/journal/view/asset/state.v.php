<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("L'état des immobilisations de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'asset';
	$t->subNav = new \company\CompanyUi()->getAssetSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/asset/state';

	$t->mainTitle = new \journal\AssetUi()->getTitle();

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlJournal($data->eCompany).'/asset/state?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \journal\AssetUi()->getSummary($data->eCompany, $data->eFinancialYearSelected, $data->assetSummary);

});
