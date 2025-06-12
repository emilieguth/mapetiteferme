<?php
new AdaptativeView('create', function($data, PanelTemplate $t) {

		return new \journal\OperationUi()->create($data->eCompany, $data->e, $data->eFinancialYear);

});

new JsonView('addOperation', function($data, AjaxTemplate $t) {

	$form = new \util\FormUi();
	$form->open('journal-operation-create');
	$defaultValues = [];

	$t->qs('#create-operation-list')->setAttribute('data-columns', $data->index + 1);
	$t->qs('.create-operation[data-index="'.($data->index - 1).'"]')->insertAdjacentHtml(
		'afterend',
		new \journal\OperationUi()::getFieldsCreateGrid($data->eCompany, $form, $data->eOperation, $data->eFinancialYear, '['.$data->index.']', $defaultValues, [])
	);
	$t->qs('#add-operation')->setAttribute('post-index', $data->index + 1);
	if($data->index >= 4) {
		$t->qs('#add-operation')->addClass('not-visible');
	}
	$t->js()->eval('Operation.showOrHideDeleteOperation()');
	$t->js()->eval('Operation.preFillNewOperation('.$data->index.')');

});

?>
