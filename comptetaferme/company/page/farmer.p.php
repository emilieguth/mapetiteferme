<?php
(new Page())
	->get('manage', function($data) {

		$farm = GET('farm', '?int');

		$data->eFarm = \company\CompanyLib::getById($farm)->validate('canManage');

		\company\FarmerLib::register($data->eFarm);
		\company\FarmerLib::setView('viewPlanning', $data->eFarm, \company\Farmer::TEAM);

		$data->cFarmer = \company\FarmerLib::getByFarm($data->eFarm);
		$data->cFarmerInvite = \company\FarmerLib::getByFarm($data->eFarm, onlyInvite: TRUE);
		$data->cFarmerGhost = \company\FarmerLib::getByFarm($data->eFarm, onlyGhost: TRUE);

		$cUser = $data->cFarmer->getColumnCollection('user');
		\hr\PresenceLib::fillUsers($data->eFarm, $cUser);

		throw new ViewAction($data);

	})
	->get('show', function($data) {

		$data->eFarmer = \company\FarmerLib::getById(GET('id'))->validate('canWrite');
		$data->eFarm = \company\CompanyLib::getById($data->eFarmer['farm']);

		\company\FarmerLib::register($data->eFarm);
		\company\FarmerLib::setView('viewPlanning', $data->eFarm, \company\Farmer::TEAM);

		$data->cPresence = \hr\PresenceLib::getByUser($data->eFarmer['farm'], $data->eFarmer['user']);
		$data->cAbsence = \hr\AbsenceLib::getByUser($data->eFarmer['farm'], $data->eFarmer['user']);

		throw new ViewAction($data);

	});

(new Page(function($data) {

		$data->eFarm = \company\CompanyLib::getById(INPUT('farm'))->validate('canManage');

		\user\ConnectionLib::checkLogged();

	}))
	->get('createUser', fn($data) => throw new ViewAction($data))
	->post('doCreateUser', function($data) {

		$fw = new FailWatch();

		$eUser = new \user\User([
			'email' => NULL,
			'visibility' => \user\User::PRIVATE,
			'role' => \user\RoleLib::getByFqn('farmer')
		]);

		$eUser->build(['firstName', 'lastName'], $_POST);

		$fw->validate();

		\company\FarmerLib::createGhostUser($data->eFarm, $eUser);

		throw new BackAction('farm', 'Farmer::userCreated');

	})
	->get('updateUser', function($data) {

		$data->eUserOnline = \user\UserLib::getById(GET('user'))->validate('isPrivate');

		if(\company\FarmerLib::isFarmer($data->eUserOnline, $data->eFarm, NULL) === FALSE) {
			throw new NotAllowedAction('Not farmer');
		}

		throw new ViewAction($data);

	})
	->post('doUpdateUser', function($data) {

		$eUser = \user\UserLib::getById(POST('user'))->validate('isPrivate');

		if(\company\FarmerLib::isFarmer($eUser, $data->eFarm, NULL) === FALSE) {
			throw new NotAllowedAction('Not farmer');
		}

		$fw = new FailWatch();

		$eUser->build(['firstName', 'lastName'], $_POST);

		$fw->validate();

		\user\UserLib::update($eUser, ['firstName', 'lastName']);

		throw new BackAction('farm', 'Farmer::userUpdated');

	})
	->post('doDeleteUser', function($data) {

		$eUser = \user\UserLib::getById(POST('user'))->validate('isPrivate');

		\company\FarmerLib::deleteGhostUser($data->eFarm, $eUser);

		throw new ReloadAction('farm', 'Farmer::userDeleted');

	});

(new \company\CompanyPage(function($data) {

		\user\ConnectionLib::checkLogged();

	}))
	->getCreateElement(function($data) {

		return new \company\Farmer([
			'farm' => \company\CompanyLib::getById(INPUT('farm'))
		]);

	})
	->create(function($data) {

		$data->eFarmerLink = \company\FarmerLib::getById(GET('farmer'));

		if(
			$data->eFarmerLink->notEmpty() and
			$data->eFarmerLink['farm']['id'] !== $data->e['farm']['id']
		) {
			throw new NotExpectedAction('Inconsistency');
		}

		throw new ViewAction($data);

	})
	->doCreate(fn($data) => throw new RedirectAction('/company/farmer:manage?farm='.$data->e['farm']['id'].'&success=farm:Farmer::created'))
	->write('doDeleteInvite', function($data) {

		\company\InviteLib::deleteFromFarmer($data->e);

		throw new RedirectAction('/company/farmer:manage?farm='.$data->e['farm']['id'].'&success=farm:Invite::deleted');

	})
	->doUpdateProperties('doUpdateStatus', ['status'], function($data) {
		$eFarm = \company\CompanyLib::getById($data->e['farm']);
		throw new RedirectAction(\company\FarmerUi::urlManage($eFarm).'&success=farm:'.($data->e['status'] === \company\Farmer::IN ? 'Farmer::created' : 'Farmer::deleted'));
	})
	->update()
	->doUpdate(fn($data) => throw new ViewAction($data))
	->doDelete(fn($data) => throw new RedirectAction('/company/farmer:manage?farm='.$data->e['farm']['id'].'&success=farm:Farmer::deleted'));
?>
