<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les immobilisations de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'asset';
	$t->subNav = new \company\CompanyUi()->getAssetSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlAsset($data->eCompany).'/depreciation';

	$t->mainTitle = new asset\DepreciationUi()->getTitle();

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlAsset($data->eCompany).'/depreciation?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);


	if($data->eFinancialYear['status'] !== \accounting\FinancialYearElement::CLOSE) {

		echo '<div class="util-warning">';
			echo s("Vous visualisez actuellement les immobilisations d'un exercice comptable encore ouvert : il s'agit donc d'une projection à la fin de l'exercice dans le cas où les immobilisations ne changent pas.");
		echo '</div>';

	}

	echo '<h1>'.s("Amortissement des immobilisations").'</h1>';
	echo \asset\DepreciationUi::getDepreciationTable($data->eCompany, $data->eFinancialYear, $data->assetDepreciations);

	echo '<h1>'.s("Amortissement des subventions").'</h1>';
	echo \asset\DepreciationUi::getDepreciationTable($data->eCompany, $data->eFinancialYear, $data->subventionDepreciations);

});
