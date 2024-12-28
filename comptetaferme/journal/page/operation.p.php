<?php
(new \journal\OperationPage(
	function($data) {
		\user\ConnectionLib::checkLogged();
	}
))
	->create(function($data) {

		$data->e['cAccount'] = \journal\AccountLib::getAll();

		throw new ViewAction($data);

	})
	->doCreate(function($data) {
		throw new ReloadAction('journal', 'Operation::created');
	});
?>