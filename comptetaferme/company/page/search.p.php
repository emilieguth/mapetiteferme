<?php
(new Page())
	->post('query', function($data) {

		$data->cFarm = \company\CompanyLib::getFromQuery(POST('query'));

		throw new \ViewAction($data);

	});
?>