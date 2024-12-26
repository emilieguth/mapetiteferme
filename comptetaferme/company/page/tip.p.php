<?php
(new Page())
	->get('index', function($data) {

		$data->eFarm = \company\CompanyLib::getById(GET('farm'))->validate('canManage');

		\company\FarmerLib::register($data->eFarm);

		$data->tip = \company\TipLib::pickPosition($data->eUserOnline);
		$data->tipNavigation = 'next';

		throw new ViewAction($data);

	})
	->get('click', function($data) {

		$tip = GET('id', \company\TipLib::getList());

		if($tip === NULL) {
			throw new NotExpectedAction('Invalid tip');
		}

		\company\TipLib::changeStatus($data->eUserOnline, $tip, 'clicked');

		throw new RedirectAction(GET('redirect'));


	})
	->get('close', function($data) {

		$tip = GET('id', \company\TipLib::getList());

		if($tip === NULL) {
			throw new NotExpectedAction('Invalid tip');
		}

		\company\TipLib::changeStatus($data->eUserOnline, $tip, 'closed');

		throw new ViewAction($data);

	});
?>
