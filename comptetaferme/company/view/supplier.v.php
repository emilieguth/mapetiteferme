<?php
new JsonView('query', function($data, AjaxTemplate $t) {

	$results = $data->cSupplier->makeArray(fn($eSupplier) => \company\SupplierUi::getAutocomplete($eSupplier));
	$t->push('results', $results);

});

new AdaptativeView('manage', function($data, FarmTemplate $t) {

	$t->title = s("Les fournisseurs de {value}", $data->eFarm['name']);
	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eFarm);

	$t->mainTitle = (new \company\SupplierUi())->getManageTitle($data->eFarm, $data->cSupplier, $data->search);
	echo (new \company\SupplierUi())->getManage($data->eFarm, $data->cSupplier, $data->search);

});

new AdaptativeView('create', function($data, PanelTemplate $t) {
	return (new \company\SupplierUi())->create($data->e);
});

new JsonView('doCreate', function($data, AjaxTemplate $t) {

	if(Route::getRequestedOrigin() === 'panel') {
		$t->js()->moveHistory(-1);
	} else {
		$t->ajaxReloadLayer();
	}

	$t->js()->success('farm', 'Supplier::created');

});

new AdaptativeView('update', function($data, PanelTemplate $t) {

	return (new \company\SupplierUi())->update($data->e);

});

new JsonView('doUpdate', function($data, AjaxTemplate $t) {

	$t->js()->moveHistory(-1);

});

new JsonView('doDelete', function($data, AjaxTemplate $t) {

	$t->js()->success('farm', 'Supplier::deleted');
	$t->ajaxReloadLayer();

});
?>
