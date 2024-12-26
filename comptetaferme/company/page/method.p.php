<?php
(new \company\MethodPage())
	->getCreateElement(function($data) {

		$data->eAction = \company\ActionLib::getById(INPUT('action'))->validate('canWrite');

		return new \company\Method([
			'action' => $data->eAction,
			'farm' => $data->eAction['farm'],
		]);

	})
	->create()
	->doCreate(fn() => throw new ReloadAction('farm', 'Method::created'));

(new \company\MethodPage())
	->quick(['name'])
	->doDelete(fn() => throw new ReloadAction('farm', 'Method::deleted'));
?>
