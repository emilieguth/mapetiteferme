<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Tous les comptes de {value}", $data->eCompany['name']);
	$t->tab = 'settings';
	$t->subNav = new \company\CompanyUi()->getSettingsSubNav($data->eCompany);

	$t->mainTitle = new \accounting\AccountUi()->getManageTitle($data->eCompany);

	echo new \accounting\AccountUi()->getSearch($data->search);
	echo new \accounting\AccountUi()->getManage($data->eCompany, $data->cAccount);

});

new JsonView('query', function($data, AjaxTemplate $t) {

	$results = $data->cAccount->makeArray(function($eAccount) use ($data) {
		return \accounting\AccountUi::getAutocomplete($data->eCompany['id'], $eAccount, $data->search);
	});

	$t->push('results', $results);

});

new JsonView('queryLabel', function($data, AjaxTemplate $t) {

	$results = array_map(function($label) use ($data) { return \accounting\AccountUi::getAutocompleteLabel(POST('query'), $data->eCompany['id'], $label); }, $data->labels);

	$t->push('results', $results);

});

new AdaptativeView('create', function($data, PanelTemplate $t) {

	return new \accounting\AccountUi()->create($data->eCompany, $data->e);

});
?>
