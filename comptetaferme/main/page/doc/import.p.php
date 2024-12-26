<?php
(new Page(function($data) {

		$cFarmer = \company\FarmerLib::getOnline();

		$data->eFarm = $cFarmer->notEmpty() ? $cFarmer->first()['farm'] : new \company\Company();

	}))
	->get('index', fn($data) => throw new ViewAction($data));
?>
