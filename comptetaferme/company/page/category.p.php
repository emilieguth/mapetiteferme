<?php
(new \company\CategoryPage(function($data) {

		\user\ConnectionLib::checkLogged();

	}))
	->getCreateElement(function($data) {

		$data->eFarm = \company\CompanyLib::getById(INPUT('farm'));

		return new \company\Category([
			'farm' => $data->eFarm,
		]);

	})
	->create()
	->doCreate(fn($data) => throw new ViewAction($data));

(new \company\CategoryPage())
	->update()
	->doUpdate(fn($data) => throw new ViewAction($data))
	->write('doIncrementPosition', function($data) {

		$increment = POST('increment', 'int');
		\company\CategoryLib::incrementPosition($data->e, $increment);

		throw new ReloadAction();

	})
	->doDelete(fn($data) => throw new ViewAction($data));

(new Page())
	->get('manage', function($data) {

		$data->eFarm = \company\CompanyLib::getById(GET('farm'))->validate('canManage');
		$data->cCategory = \company\CategoryLib::getByFarm($data->eFarm);

		\company\FarmerLib::register($data->eFarm);

		throw new ViewAction($data);

	});
?>
