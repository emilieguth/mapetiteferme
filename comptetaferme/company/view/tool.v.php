<?php
new AdaptativeView('/outil/{id@int}', function($data, PanelTemplate $t) {
	return (new \company\ToolUi())->display($data->eFarm, $data->eTool);
});

new JsonView('query', function($data, AjaxTemplate $t) {

	$results = $data->cTool->makeArray(fn($eTool) => \company\ToolUi::getAutocomplete($eTool));
	$t->push('results', $results);

});

new AdaptativeView('manage', function($data, FarmTemplate $t) {

	$t->title = ($data->routineName ? \company\RoutineUi::getProperty($data->routineName, 'pageTitle')($data->eFarm) : s("Le matériel de {value}", $data->eFarm['name']));
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eFarm);

	$t->mainTitle = (new \company\ToolUi())->getManageTitle($data->eFarm, $data->routineName, $data->tools, $data->cTool, $data->search);
	echo (new \company\ToolUi())->getManage($data->eFarm, $data->routineName, $data->tools, $data->cTool, $data->eToolNew, $data->cActionUsed, $data->search);

});

new AdaptativeView('create', function($data, PanelTemplate $t) {
	return (new \company\ToolUi())->create($data->e);
});

new JsonView('doCreate', function($data, AjaxTemplate $t) {

	if(Route::getRequestedOrigin() === 'panel') {
		$t->js()->moveHistory(-1);
	} else {
		$t->ajaxReloadLayer();
	}

	$t->js()->success('farm', 'Tool::created');

});

new AdaptativeView('update', function($data, PanelTemplate $t) {

	return (new \company\ToolUi())->update($data->e, $data->routines);

});

new JsonView('doUpdate', function($data, AjaxTemplate $t) {

	$t->js()->moveHistory(-1);

});

new JsonView('doDelete', function($data, AjaxTemplate $t) {

	$t->js()->success('farm', 'Tool::deleted');
	$t->ajaxReloadLayer();

});

new AdaptativeView('getRoutinesField', function($data, AjaxTemplate $t) {

	if($data->routines === []) {
		$t->push('field', '');
	} else {
		$t->push('field', (new \company\RoutineUi())->getFields(array_keys($data->routines), $data->eTool));
	}


});
?>
