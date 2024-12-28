<?php

new AdaptativeView('/company', function($data, CompanyTemplate $t) {

	$t->tab = 'journal';
	$t->subNav = (new \company\CompanyUi())->getJournalSubNav($data->eCompany);

	$t->canonical = \company\CompanyUi::url($data->eCompany);
	$t->title = $data->eCompany['name'];

	echo "coucou";

});

?>
