<?php
(new \company\CompanyPage())
	->get('index', function($data) {

		throw new ViewAction($data);

	});
?>
