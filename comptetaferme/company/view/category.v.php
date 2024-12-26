<?php
new AdaptativeView('manage', function($data, FarmTemplate $t) {

	$t->title = s("Les catégories d'interventions de {value}", $data->eFarm['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eFarm);

	$t->mainTitle = (new \company\CategoryUi())->getManageTitle($data->eFarm, $data->cCategory);
	echo (new \company\CategoryUi())->getManage($data->eFarm, $data->cCategory);

});

new AdaptativeView('create', function($data, PanelTemplate $t) {

	return (new \company\CategoryUi())->create($data->eFarm);

});

new JsonView('doCreate', function($data, AjaxTemplate $t) {

	$t->js()->moveHistory(-1);
	$t->js()->success('farm', 'category::created');

});

new AdaptativeView('update', function($data, PanelTemplate $t) {

	return (new \company\CategoryUi())->update($data->e);

});

new JsonView('doUpdate', function($data, AjaxTemplate $t) {

	$t->js()->success('farm', 'Category::updated');
	$t->js()->moveHistory(-1);

});

new JsonView('doDelete', function($data, AjaxTemplate $t) {

	$t->js()->success('farm', 'Category::deleted');
	$t->ajaxReloadLayer();

});
?>
