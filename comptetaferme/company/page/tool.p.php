<?php
(new \company\ToolPage(function($data) {

		\user\ConnectionLib::checkLogged();

		$data->eFarm = \company\CompanyLib::getById(INPUT('farm', '?int'));

	}))
	->getCreateElement(function($data) {

		return \company\ToolLib::getNewTool($data->eFarm, REQUEST('routineName', array_keys(\company\RoutineLib::list())));

	})
	->create(function($data) {

		$data->e['cAction'] = \company\ActionLib::getByFarm($data->eFarm);

		throw new ViewAction($data);
	})
	->doCreate(fn($data) => throw new ViewAction($data));

(new \company\ToolPage())
	->applyElement(function($data, \company\Tool $e) {

		$e->validate('canWrite');

		$data->eFarm = $e['farm'];

		\company\Company::model()
			->select('status', 'name')
			->get($data->eFarm);

		$data->eFarm->validate('active');

	})
	->quick(['name', 'action', 'stock'], [
		'action' => function($data) {
			$data->e['cAction'] = \company\ActionLib::getByFarm($data->e['farm']);
		}
	])
	->update(function($data) {

		$data->e['cAction'] = \company\ActionLib::getByFarm($data->eFarm);

		$data->routines = \company\RoutineLib::getByAction($data->e['action']);

		throw new ViewAction($data);

	})
	->doUpdate(fn($data) => throw new ViewAction($data))
	->doUpdateProperties('doUpdateStatus', ['status'], function() {
		throw new ReloadAction('farm', 'Tool::updated');
	})
	->doDelete(fn($data) => throw new ViewAction($data));

(new Page())
	->get('/outil/{id@int}', function($data) {

		$data->eTool = \company\ToolLib::getById(REQUEST('id'))->validate('canRead');

		$data->eFarm = \company\CompanyLib::getById($data->eTool['farm']);
		\company\FarmerLib::register($data->eFarm);

		throw new ViewAction($data);

	})
	->post('query', function($data) {

		$eFarm = \company\CompanyLib::getById(POST('farm'))->validate('canWrite');
		$eAction = \company\ActionLib::getById(POST('action'));

		$data->cTool = \company\ToolLib::getFromQuery(POST('query'), $eFarm, $eAction);

		throw new \ViewAction($data);

	})
	->post('getRoutinesField', function($data) {

		$eFarm = \company\CompanyLib::getById(POST('farm'))->validate('canWrite');
		$eAction = \company\ActionLib::getById(POST('action'))->validate('canRead');

		$data->eTool = new \company\Tool([
			'farm' => $eFarm,
			'action' => $eAction,
			'routineName' => NULL,
			'routineValue' => []
		]);

		$data->routines = \company\RoutineLib::getByAction($eAction, FALSE);

		throw new \ViewAction($data);

	})
	->get('manage', function($data) {

		$data->eFarm = \company\CompanyLib::getById(GET('farm'))->validate('canManage');

		$data->search = new Search([
			'name' => GET('name'),
			'action' => GET('action', 'company\Action'),
			'status' => GET('status', default: \company\Tool::ACTIVE),
		]);

		$data->routineName = GET('routineName');

		if(
			$data->routineName === NULL or
			\company\RoutineLib::exists($data->routineName) === FALSE or
			\company\RoutineLib::get($data->routineName)['standalone'] === FALSE
		) {
			$data->routineName = NULL;
		}

		$data->tools = \company\ToolLib::countByFarm($data->eFarm, $data->routineName);

		if(
			get_exists('status') and
			$data->tools[\plant\Plant::INACTIVE] === 0
		) {
			throw new RedirectAction(\company\ToolUi::urlManage($data->eFarm, $data->routineName));
		}

		$data->eToolNew = \company\ToolLib::getNewTool($data->eFarm, $data->routineName);
		$data->eToolNew['cAction'] = \company\ActionLib::getByFarm($data->eFarm);

		$data->cTool = \company\ToolLib::getByFarm($data->eFarm, routineName: $data->routineName, search: $data->search);

		$data->cActionUsed = \company\ToolLib::getActionsByFarm($data->eFarm);

		\company\FarmerLib::register($data->eFarm);

		throw new \ViewAction($data);

	});
?>
