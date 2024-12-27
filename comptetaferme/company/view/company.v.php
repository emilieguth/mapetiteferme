<?php
new AdaptativeView('create', function($data, PanelTemplate $t) {

	return (new \company\CompanyUi())->create();

});

new AdaptativeView('update', function($data, CompanyTemplate $t) {

	$t->title = s("Réglages de base de {value}", $data->e['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->e);

	$h = '<h1>';
		$h .= '<a href="'.\company\CompanyUi::urlSettings($data->e).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
		$h .= s("Réglages de base de l'entreprise");
	$h .= '</h1>';

	$t->mainTitle = $h;

	echo (new \company\CompanyUi())->update($data->e);

});
?>
