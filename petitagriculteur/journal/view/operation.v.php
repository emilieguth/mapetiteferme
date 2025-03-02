<?php
new AdaptativeView('create', function($data, PanelTemplate $t) {

	return (new \journal\OperationUi())->create($data->eCompany, $data->e, $data->eFinancialYear);

});

new AdaptativeView('update', function($data, PanelTemplate $t) {

	return (new \journal\OperationUi())->update($data->eCompany, $data->e, $data->eFinancialYear);

});

?>