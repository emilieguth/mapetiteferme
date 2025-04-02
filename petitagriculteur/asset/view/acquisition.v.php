<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les acquisitions de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'asset';
	$t->subNav = new \company\CompanyUi()->getAssetSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlAsset($data->eCompany).'/acquisition';

	$t->mainTitle = new asset\AssetUi()->getAcquisitionTitle();

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlAsset($data->eCompany).'/acquisition?financialYear='.$eFinancialYear['id'];
			},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

	echo new asset\AssetUi()->getAcquisitionTable($data->cAsset);

});
