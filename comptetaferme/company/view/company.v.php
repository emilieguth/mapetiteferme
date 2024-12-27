<?php
new AdaptativeView('create', function($data, PanelTemplate $t) {

	return (new \company\CompanyUi())->create();

});

new AdaptativeView('update', function($data, FarmTemplate $t) {

	$t->title = s("Réglages de base de {value}", $data->e['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->e);

	$h = '<h1>';
		$h .= '<a href="'.\company\CompanyUi::urlSettings($data->e).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
		$h .= s("Réglages de base de la ferme");
	$h .= '</h1>';

	$t->mainTitle = $h;

	echo (new \company\CompanyUi())->update($data->e);

});

new AdaptativeView('updateSeries', function($data, FarmTemplate $t) {

	$t->title = s("Réglages de base de {value}", $data->e['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->e);

	$h = '<h1>';
		$h .= '<a href="'.\company\CompanyUi::urlSettings($data->e).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
		$h .= s("Réglages de base pour produire");
	$h .= '</h1>';

	$t->mainTitle = $h;

	echo (new \company\CompanyUi())->updateSeries($data->e);

});

new AdaptativeView('updateFeature', function($data, FarmTemplate $t) {

	$t->title = s("Configurer les fonctionnalités de {value}", $data->e['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->e);

	$h = '<h1>';
		$h .= '<a href="'.\company\CompanyUi::urlSettings($data->e).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
		$h .= s("Configurer les fonctionnalités");
	$h .= '</h1>';


	$t->mainTitle = $h;

	echo (new \company\CompanyUi())->updateFeature($data->e);

});

new AdaptativeView('calendarMonth', function($data, AjaxTemplate $t) {

	$t->qs('#farm-update-calendar-month')->innerHtml((new \series\CultivationUi())->getListSeason($data->e, date('Y')));

});

new AdaptativeView('export', function($data, FarmTemplate $t) {

	$t->title = s("Exporter les données de {value}", $data->e['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->e);

	$h = '<h1>';
		$h .= '<a href="'.\company\CompanyUi::urlSettings($data->e).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
		$h .= s("Exporter les données");
	$h .= '</h1>';
	
	$t->mainTitle = $h;
	
	echo (new \company\CompanyUi())->export($data->e, $data->year);

});
?>
