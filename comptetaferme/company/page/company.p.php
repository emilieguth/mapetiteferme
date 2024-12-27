<?php
(new \company\CompanyPage(
		function($data) {
			\user\ConnectionLib::checkLogged();
		}
	))
	->getCreateElement(fn($data) => new \company\Company([
		'owner' => \user\ConnectionLib::getOnline()
	]))
	->create()
	->doCreate(function($data) {
		throw new RedirectAction(\company\CompanyUi::urlCartography($data->e).'?success=farm:Farm.created');
	});

(new \company\CompanyPage())
	->applyElement(function($data, \company\Company $e) {
		$e->validate('canManage');
	})
	->update(function($data) {

		$data->eFarm = $data->e;
		\company\FarmerLib::register($data->e);

		throw new ViewAction($data);

	})
	->doUpdate(fn() => throw new ReloadAction('farm', 'Farm.updated'))
	->update(function($data) {

		$data->eFarm = $data->e;
		\company\FarmerLib::register($data->e);

		$data->e['cPlantRotationExclude'] = \plant\PlantLib::getByIds($data->e['rotationExclude'], sort: 'name');

		throw new ViewAction($data);

	}, page: 'updateSeries')
	->doUpdateProperties('doUpdateSeries', ['calendarMonthStart', 'calendarMonthStop', 'rotationYears', 'rotationExclude'], fn() => throw new ReloadAction('farm', 'Farm.updatedRotation'))
	->update(function($data) {

		$data->eFarm = $data->e;
		\company\FarmerLib::register($data->e);

		throw new ViewAction($data);

	}, page: 'updateFeature')
	->doUpdateProperties('doUpdateFeature', ['featureTime', 'featureDocument'], fn() => throw new ReloadAction('farm', 'Farm.updatedFeatures'))
	->doUpdateProperties('doUpdatePlanningDelayedMax', ['planningDelayedMax'], fn() => throw new ReloadAction())
	->read('calendarMonth', function($data) {

		$data->e['calendarMonthStart'] = GET('calendarMonthStart', '?int');
		$data->e['calendarMonthStop'] = GET('calendarMonthStop', '?int');
		$data->e['calendarMonths'] = ($data->e['calendarMonthStart'] ? (12 - $data->e['calendarMonthStart'] + 1) : 0) + 12 + ($data->e['calendarMonthStop'] ?? 0);

		throw new ViewAction($data);

	})
	->write('doSeasonFirst', function($data) {

		$data->increment = POST('increment', 'int');
		\company\CompanyLib::updateSeasonFirst($data->e, $data->increment);

		throw new RedirectAction(\company\CompanyUi::urlCultivationSeries($data->e, \company\Employee::AREA, season: $data->e['seasonFirst'] + $data->increment));

	})
	->write('doSeasonLast', function($data) {

		$data->increment = POST('increment', 'int');
		\company\CompanyLib::updateSeasonLast($data->e, $data->increment);

		throw new RedirectAction(\company\CompanyUi::urlCultivationSeries($data->e, \company\Employee::AREA, season: $data->e['seasonLast'] + $data->increment));

	})
	->write('doClose', function($data) {

		$data->e['status'] = \company\Company::CLOSED;

		\company\CompanyLib::update($data->e, ['status']);

		throw new RedirectAction('/?success=farm:Farm.closed');

	})
	->read('export', function($data) {

		$data->eFarm = $data->e;
		$data->year = GET('year', default: $data->e['seasonLast']);

		\company\FarmerLib::register($data->e);

		throw new \ViewAction($data);

	}, validate: ['canPersonalData']);
?>
