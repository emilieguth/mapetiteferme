<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Tous les comptes de {value}", $data->eCompany['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eCompany);

	$t->mainTitle = (new \journal\AccountUi())->getManageTitle($data->eCompany);

	echo (new \journal\AccountUi())->getManage($data->eCompany, $data->cAccount);

});

new JsonView('query', function($data, AjaxTemplate $t) {

	$results = $data->cAccount->makeArray(fn($eAccount) => \journal\AccountUi::getAutocomplete($eAccount));

	$t->push('results', $results);

});

?>