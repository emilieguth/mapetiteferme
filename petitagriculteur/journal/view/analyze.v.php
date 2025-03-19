<?php
new AdaptativeView('analyseBank', function($data, CompanyTemplate $t) {

	$t->title = s("La trésorerie de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->subNav = new \company\CompanyUi()->getAnalyzeSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/analyze/bank';

	$t->mainTitle = new \journal\AnalyzeUi()->getBankTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlJournal($data->eCompany).'/analyze/bank/'.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \journal\AnalyzeUi()->getBank([$data->cOperationBank, $data->cOperationCash]);

});

new AdaptativeView('analyseCharge', function($data, CompanyTemplate $t) {

	$t->title = s("Les charges de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/analyze/charges';
	$t->subNav = new \company\CompanyUi()->getAnalyzeSubNav($data->eCompany);

	$t->mainTitle = new \journal\AnalyzeUi()->getChargesTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlJournal($data->eCompany).'/analyze/charges/'.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \journal\AnalyzeUi()->getCharges($data->eCompany, $data->eFinancialYearSelected, $data->cOperation, $data->cAccount);

});

new AdaptativeView('analyseResult', function($data, CompanyTemplate $t) {

	$t->title = s("Le résultat de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'analyze';
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/analyze/result';
	$t->subNav = new \company\CompanyUi()->getAnalyzeSubNav($data->eCompany);

	$t->mainTitle = new \journal\AnalyzeUi()->getResultTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlJournal($data->eCompany).'/analyze/result/'.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \journal\AnalyzeUi()->getResultByMonth($data->eCompany, $data->eFinancialYearSelected, $data->cOperation);
	echo new \journal\AnalyzeUi()->getResult($data->result, $data->cAccount);

});

?>
