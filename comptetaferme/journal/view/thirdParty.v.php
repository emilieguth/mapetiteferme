<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les tiers de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/thirdParty/';

	$t->mainTitle = (new \journal\ThirdPartyUi())->getThirdPartyTitle($data->eCompany);

	//echo (new \journal\JournalUi())->getSearch($data->search, $data->eFinancialYearSelected, $data->eCashflow);
	//echo (new \journal\JournalUi())->getJournal($data->eCompany, $data->cOperation, $data->cOperationGrouped, $data->eFinancialYearSelected, $data->search);


	echo (new \journal\ThirdPartyUi())->manage($data->eCompany, $data->cThirdParty);

});

new AdaptativeView('create', function($data, PanelTemplate $t) {

	return (new \journal\ThirdPartyUi())->create($data->eCompany, $data->e);

});

?>