<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	if($data->type === 'buy') {
		$t->title = s("Les journaux d'achat de TVA de {company}", ['company' => $data->eCompany['name']]);
	} else {
		$t->title = s("Les journaux de vente de TVA de {company}", ['company' => $data->eCompany['name']]);
	}
	$t->tab = 'journal';
	$t->subNav = new \company\CompanyUi()->getJournalSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/vat';

	$t->mainTitle = new \journal\VatUi()->getTitle($data->eCompany, $data->eFinancialYear, $data->type);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlJournal($data->eCompany).'/vat?financialYear='.$eFinancialYear['id'].'&'.http_build_query($data->search->getFiltered(['financialYear']));
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

	echo new \journal\VatUi()->getSearch($data->search, $data->eFinancialYear, $data->eThirdParty);
	echo new \journal\VatUi()->getJournal($data->eCompany, $data->cccOperation, $data->eFinancialYear, $data->search);

});
