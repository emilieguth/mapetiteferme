<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les immobilisations de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'asset';
	$t->subNav = new \company\CompanyUi()->getAssetSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlAsset($data->eCompany).'/depreciation';

	$t->mainTitle = new asset\AssetUi()->getDepreciationTitle();

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlAsset($data->eCompany).'/depreciation?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);


});
