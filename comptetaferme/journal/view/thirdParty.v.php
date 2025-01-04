<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les tiers de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany).'/thirdParty/';

	$t->mainTitle = (new \journal\ThirdPartyUi())->getThirdPartyTitle($data->eCompany);

	echo (new \journal\ThirdPartyUi())->manage($data->eCompany, $data->cThirdParty);

});

new AdaptativeView('create', function($data, PanelTemplate $t) {

	return (new \journal\ThirdPartyUi())->create($data->eCompany, $data->e);

});

new JsonView('query', function($data, AjaxTemplate $t) {

	$results = $data->cThirdParty->makeArray(function($eThirdParty) use ($data) { return \journal\ThirdPartyUi::getAutocomplete($data->eCompany['id'], $eThirdParty); });
	$results[] = \journal\ThirdPartyUi::getAutocompleteCreate($data->eCompany);

	$t->push('results', $results);

});

new AdaptativeView('doCreate', function($data, AjaxTemplate $t) {

	$t->js()->success('journal', 'ThirdParty::created');
	$t->js()->closePanel('#panel-journal-thirdParty-create');

});

?>