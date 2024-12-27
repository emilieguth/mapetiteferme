<?php

new AdaptativeView('/company/{id}', function($data, CompanyTemplate $t) {

	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eCompany);

	$t->canonical = \company\CompanyUi::url($data->eCompany);
	$t->title = $data->eCompany['name'];

	echo "coucou";

});

new AdaptativeView('/company/{id}/configuration', function($data, CompanyTemplate $t) {

	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eCompany);

	$t->title = s("Configuration pour {value}", $data->eCompany['name']);
	$t->canonical = \company\CompanyUi::urlSettings($data->eCompany);

	$t->package('main')->updateNavSettings($t->canonical);

	$t->mainTitle = '<h1>'.s("Paramétrage").'</h1>';
	$t->mainTitleClass = 'hide-lateral-down';

	echo (new \company\CompanyUi())->getSettings($data->eCompany);

});
?>
