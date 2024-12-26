<?php
(new \company\ActionPage())
	->getCreateElement(function($data) {

		$data->eFarm = \company\CompanyLib::getById(INPUT('farm'));

		return new \company\Action([
			'farm' => $data->eFarm,
		]);

	})
	->create(function($data) {

		$data->cCategory = \company\CategoryLib::getByFarm($data->eFarm);

		throw new ViewAction($data);

	})
	->doCreate(fn($data) => throw new ViewAction($data));

(new \company\ActionPage())
	->applyElement(function($data, \company\Action $e) {

		$e->validate('canWrite');

		$data->eFarm = $e['farm'];

		\company\Company::model()
			->select('status', 'name')
			->get($data->eFarm);

		$data->eFarm->validate('active');

	})
	->update(function($data) {

		$data->e['cCategory'] = \company\CategoryLib::getByFarm($data->eFarm);

		throw new ViewAction($data);

	})
	->doUpdate(fn($data) => throw new ViewAction($data))
	->doDelete(fn($data) => throw new ViewAction($data))
	->read('analyzeTime', function($data) {

		$data->year = GET('year', 'int', date('Y'));
		$data->eCategory = \company\CategoryLib::getByFarm($data->eFarm, id: GET('category'));

		$data->cActionTimesheet = \company\AnalyzeLib::getActionTimesheet($data->e, $data->eCategory, $data->year);
		[$data->cTimesheetMonth, $data->cTimesheetUser] = \company\AnalyzeLib::getActionMonths($data->e, $data->eCategory, $data->year);
		[$data->cTimesheetMonthBefore] = \company\AnalyzeLib::getActionMonths($data->e, $data->eCategory, $data->year - 1);

		throw new ViewAction($data);

	});

(new Page())
	->get('manage', function($data) {

		$data->eFarm = \company\CompanyLib::getById(GET('farm'))->validate('canManage');
		$data->cCategory = \company\CategoryLib::getByFarm($data->eFarm, index: 'id');
		$data->cAction = \company\ActionLib::getForManage($data->eFarm);

		\company\FarmerLib::register($data->eFarm);

		throw new \ViewAction($data);

	});
?>
