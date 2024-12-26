<?php
new AdaptativeView('manage', function($data, FarmTemplate $t) {

	$t->title = s("Les interventions de {value}", $data->eFarm['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eFarm);

	$t->mainTitle = (new \company\ActionUi())->getManageTitle($data->eFarm);
	echo (new \company\ActionUi())->getManage($data->eFarm, $data->cAction, $data->cCategory);

});

new AdaptativeView('create', function($data, PanelTemplate $t) {

	return (new \company\ActionUi())->create($data->eFarm, $data->cCategory);

});

new JsonView('doCreate', function($data, AjaxTemplate $t) {

	$t->js()->moveHistory(-1);
	$t->js()->success('farm', 'Action::created');

});

new AdaptativeView('update', function($data, PanelTemplate $t) {

	return (new \company\ActionUi())->update($data->e);

});

new JsonView('doUpdate', function($data, AjaxTemplate $t) {

	$t->js()->success('farm', 'Action::updated');
	$t->js()->moveHistory(-1);

});

new JsonView('doDelete', function($data, AjaxTemplate $t) {

	$t->js()->success('farm', 'Action::deleted');
	$t->ajaxReloadLayer();

});

new AdaptativeView('analyzeTime', function($data, PanelTemplate $t) {
	return (new \company\AnalyzeUi())->getActionTime($data->e, $data->eCategory, $data->year, $data->cActionTimesheet, $data->cTimesheetMonth, $data->cTimesheetMonthBefore, $data->cTimesheetUser);
});
?>
