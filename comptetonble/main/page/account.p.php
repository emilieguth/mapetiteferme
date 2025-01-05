<?php
(new Page())
	->get('index', function($data) {

		\user\ConnectionLib::checkLogged();

		$data->canUpdate = user\SignUpLib::canUpdate($data->eUserOnline);
		$data->nCustomer = 0;

		throw new ViewAction($data);

	});
?>
