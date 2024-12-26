<?php
new AdaptativeView('manage', function($data, FarmTemplate $t) {

	$t->title = s("L'équipe de {value}", $data->eFarm['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eFarm);

	$t->mainTitle = (new \company\FarmerUi())->getManageTitle($data->eFarm);

	echo (new \company\FarmerUi())->getManage($data->eFarm, $data->cFarmer, $data->cFarmerInvite, $data->cFarmerGhost);

});

new AdaptativeView('show', function($data, FarmTemplate $t) {

	$t->title = \user\UserUi::name($data->eFarmer['user']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eFarmer['farm']);

	$t->mainTitle = (new \company\FarmerUi())->getUserTitle($data->eFarmer);

	echo (new \company\FarmerUi())->getUser($data->eFarmer, $data->cPresence, $data->cAbsence);

});

new AdaptativeView('createUser', function($data, PanelTemplate $t) {
	return (new \company\FarmerUi())->createUser($data->eFarm);
});

new AdaptativeView('updateUser', function($data, PanelTemplate $t) {
	return (new \company\FarmerUi())->updateUser($data->eFarm, $data->eUserOnline);
});

new AdaptativeView('create', function($data, PanelTemplate $t) {
	return (new \company\FarmerUi())->create($data->e, $data->eFarmerLink);
});

new AdaptativeView('update', function($data, PanelTemplate $t) {
	return (new \company\FarmerUi())->update($data->e);
});

new JsonView('doUpdate', function($data, AjaxTemplate $t) {
	$t->js()->moveHistory(-1);
});
?>
