<?php

new AdaptativeView('/company/{id}', function($data, CompanyTemplate $t) {

	$t->tab = 'finances';
	$t->subNav = (new \company\CompanyUi())->getFinancesSubNav($data->eCompany);

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

new AdaptativeView('/company/{id}/finances', function($data, CompanyTemplate $t) {

	$t->tab = 'finances';
	$t->subNav = (new \company\CompanyUi())->getFinancesSubNav($data->eCompany);

	$t->title = s("Finances & écritures pour {value}", $data->eCompany['name']);
	$t->canonical = \company\CompanyUi::urlFinances($data->eCompany);

	$t->package('main')->updateNavFinances($t->canonical);

	$t->mainTitle = '<h1>'.s("Finances & écritures").'</h1>';
	$t->mainTitleClass = 'hide-lateral-down';

	echo "todo finances";

});

new AdaptativeView('/company/{id}/fournisseurs', function($data, CompanyTemplate $t) {

	$t->tab = 'suppliers';
	$t->subNav = (new \company\CompanyUi())->getSuppliersSubNav($data->eCompany);

	$t->title = s("Fournisseurs de {value}", $data->eCompany['name']);
	$t->canonical = \company\CompanyUi::urlSuppliers($data->eCompany);

	$t->package('main')->updateNavSuppliers($t->canonical);

	$t->mainTitle = '<h1>'.s("Fournisseurs").'</h1>';
	$t->mainTitleClass = 'hide-lateral-down';

	echo "todo fournisseurs";

});

new AdaptativeView('/company/{id}/clients', function($data, CompanyTemplate $t) {

	$t->tab = 'customers';
	$t->subNav = (new \company\CompanyUi())->getCustomersSubNav($data->eCompany);

	$t->title = s("Clients de {value}", $data->eCompany['name']);
	$t->canonical = \company\CompanyUi::urlCustomers($data->eCompany);

	$t->package('main')->updateNavCustomers($t->canonical);

	$t->mainTitle = '<h1>'.s("Clients").'</h1>';
	$t->mainTitleClass = 'hide-lateral-down';

	echo "todo clients";

});
?>
