<?php
new AdaptativeView('create', function($data, PanelTemplate $t) {

		return new \journal\OperationUi()->create($data->eCompany, $data->e, $data->eFinancialYear);

});

new JsonView('addOperation', function($data, AjaxTemplate $t) {

	$form = new \util\FormUi();
	$form->open('journal-operation-create');
	$eOperation = new \journal\Operation(['account' => new \accounting\Account()]);
	$defaultValues = [];

	$t->qs('#create-operation-list')->insertAdjacentHtml('beforeend', new \journal\OperationUi()::addOperation($eOperation, $data->eFinancialYear, NULL, $data->index, $form, $defaultValues));
	$t->qs('#add-operation')->setAttribute('post-index', $data->index + 1);
	$t->js()->eval('Operation.showOrHideDeleteOperation()');

});

?>
