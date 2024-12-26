<?php
new JsonView('query', function($data, AjaxTemplate $t) {

	$results = $data->cFarm->makeArray(fn($eFarm) => \company\CompanyUi::getAutocomplete($eFarm));

	$t->push('results', $results);

});