<?php
new AdaptativeView('create', function($data, PanelTemplate $t) {

		return new \journal\OperationUi()->create($data->eCompany, $data->e, $data->eFinancialYear);

});

new JsonView('addOperation', function($data, AjaxTemplate $t) {

	$form = new \util\FormUi();
	$form->open('journal-operation-create');
	$defaultValues = [];

	$t->qs('.create-operations-container')->setAttribute('data-columns', $data->index + 1);
	$t->qs('.create-operation[data-index="'.($data->index - 1).'"]')->insertAdjacentHtml('afterend', new \journal\OperationUi()::getFieldsCreateGrid($form, $data->eOperation, $data->eFinancialYear, '['.$data->index.']', $defaultValues, []));
	$t->qs('#add-operation')->setAttribute('post-index', $data->index + 1);
	$t->js()->eval('Operation.showOrHideDeleteOperation()');
	$t->js()->eval('Operation.preFillNewOperation('.$data->index.')');

});

?>
