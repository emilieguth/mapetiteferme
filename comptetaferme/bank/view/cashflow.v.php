<?php
new AdaptativeView('import', function($data, PanelTemplate $t) {

	return (new \bank\CashflowUi())->import($data->eCompany);

});