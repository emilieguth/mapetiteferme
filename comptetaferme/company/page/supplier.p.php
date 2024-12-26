<?php
(new \company\SupplierPage())
	->getCreateElement(function($data) {

		$data->eFarm = \company\CompanyLib::getById(INPUT('farm'));

		return new \company\Supplier([
			'farm' => $data->eFarm,
		]);

	})
	->create()
	->doCreate(fn($data) => throw new ViewAction($data));

(new \company\SupplierPage())
	->applyElement(function($data, \company\Supplier $e) {

		$e->validate('canWrite');

		$data->eFarm = $e['farm'];

		\company\Company::model()
			->select('status', 'name')
			->get($data->eFarm);

		$data->eFarm->validate('active');

	})
	->quick(['name'])
	->update()
	->doUpdate(fn($data) => throw new ViewAction($data))
	->doDelete(fn($data) => throw new ViewAction($data));

(new Page())
	->post('query', function($data) {

		$eFarm = \company\CompanyLib::getById(POST('farm'))->validate('canWrite');

		$data->cSupplier = \company\SupplierLib::getFromQuery(POST('query'), $eFarm);

		throw new \ViewAction($data);

	})
	->get('manage', function($data) {

		$data->eFarm = \company\CompanyLib::getById(GET('farm'))->validate('canManage');

		$data->search = new Search([
			'name' => GET('name')
		]);

		$data->cSupplier = \company\SupplierLib::getByFarm($data->eFarm, $data->search);

		\company\FarmerLib::register($data->eFarm);

		throw new \ViewAction($data);

	});
?>
