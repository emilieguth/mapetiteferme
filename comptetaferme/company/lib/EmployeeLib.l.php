<?php
namespace company;

use company\Company;
use company\Employee;
use company\EmployeeCrud;

class EmployeeLib extends EmployeeCrud {

	private static ?\Collection $cEmployeeOnline = NULL;

	public static function getPropertiesCreate(): array {
		return ['id', 'role', 'email'];
	}

	public static function getPropertiesUpdate(): \Closure {

		return function(Employee $e) {

			if($e->canUpdateRole() === FALSE) {
				return [];
			} else {
				return ['role'];
			}

		};

	}

	public static function getOnline(): \Collection {

		if(self::$cEmployeeOnline === NULL) {
			$eUser = \user\ConnectionLib::getOnline();
			self::$cEmployeeOnline = self::getByUser($eUser);
		}

		return self::$cEmployeeOnline;

	}

	public static function getOnlineByFarm(Farm $eFarm): Employee {
		return self::getOnline()[$eFarm['id']] ?? new Employee();
	}

	public static function create(Employee $e): void {

		$e->expects(['id', 'role']);

		$isGhost = $e['farmGhost'] ?? FALSE;

		// On lance une invitation si ce n'est pas un utilisateur fantôme ou si c'est une tentative de transformer un fantôme qui pré-existe
		if(
			$isGhost === FALSE or
			$e['id'] !== NULL
		) {

			$eInvite = new Invite([
				'farm' => $e['farm'],
				'type' => Invite::FARMER,
				'farmer' => $e
			]);

			$fw = new \FailWatch();

			$eInvite->buildProperty('email', $e['email']);

			if($fw->ko()) {
				return;
			}

		} else {
			$eInvite = new Invite();
		}

		try {

			Employee::model()->beginTransaction();

			if($e['id'] === NULL) {

				Employee::model()->insert($e);

			} else {

				Employee::model()->update($e, [
					'role' => $e['role'],
					'status' => Employee::INVITED
				]);

			}

			if($eInvite->notEmpty()) {
				InviteLib::create($eInvite);
			}

			Employee::model()->commit();


		} catch(\DuplicateException $e) {

			Employee::model()->rollBack();
			Employee::fail('duplicate');

		}

	}

	public static function isEmployee(\user\User $eUser, Farm $eFarm, ?string $status = Employee::IN): bool {

		if($status !== NULL) {
			Employee::model()->whereStatus($status);
		}

		return Employee::model()
			->whereUser($eUser)
			->whereFarm($eFarm)
			->whereFarmStatus(Employee::ACTIVE)
			->exists();

	}

	public static function getByUser(\user\User $eUser): \Collection {

		return Employee::model()
			->select(Employee::getSelection())
			->whereUser($eUser)
			->whereFarmStatus(Employee::ACTIVE)
			->whereStatus(Employee::IN)
			->getCollection(NULL, NULL, 'farm');

	}

	public static function getByFarm(Farm $eFarm, bool $onlyInvite = FALSE, $onlyGhost = FALSE): \Collection {

		$sort = [];

		$eUserOnline = \user\ConnectionLib::getOnline();

		if($eUserOnline->notEmpty()) {
			$sort[] = new \Sql('user = '.$eUserOnline['id'].' DESC');
		}

		$sort['createdAt'] = SORT_ASC;

		Employee::model()->sort($sort);

		if($onlyGhost) {
			Employee::model()->whereFarmGhost(TRUE);
		} else if($onlyInvite) {
			Employee::model()
				->select([
					'invite' => Invite::model()
						->select(Invite::getSelection())
						->whereStatus(Invite::PENDING)
						->delegateElement('farmer')
				])
				->whereStatus(Employee::INVITED);
		} else {
			Employee::model()->whereStatus(Employee::IN);
		}

		return Employee::model()
			->select(Employee::getSelection())
			->whereFarm($eFarm)
			->getCollection();

	}

	public static function getUsersByFarm(Farm $eFarm, bool $selectInvite = FALSE, bool $onlyGhost = FALSE, bool $withPresenceAbsence = FALSE): \Collection {

		$cUser = self::getByFarm($eFarm, $selectInvite, $onlyGhost)
			->getColumnCollection('user', 'user')
			->sort([
				'firstName' => SORT_ASC,
				'lastName' => SORT_ASC
			]);

		if($withPresenceAbsence) {
			\hr\PresenceLib::fillUsers($eFarm, $cUser);
			\hr\AbsenceLib::fillUsers($eFarm, $cUser);
		}

		return $cUser;

	}

	public static function getUsersByFarmForTasks(Farm $eFarm, \Collection $cTask, bool $withPresenceAbsence = FALSE): \Collection {

		$cUser = self::getUsersByFarmForPeriod(
			$eFarm,
			week_date_starts(currentWeek()),
			week_date_ends(currentWeek())
		);

		$weeks = array_merge($cTask->getColumn('plannedWeek'), $cTask->getColumn('doneWeek'));

		if($weeks) {

			$cUser->appendCollection(

				self::getUsersByFarmForPeriod(
					$eFarm,
					week_date_starts(min($weeks)),
					week_date_ends(max($weeks))
				)

			);

		}

		$cUser->appendCollection(

			\series\Timesheet::model()
				->select([
					'user' => \user\User::getSelection()
				])
				->whereFarm($eFarm)
				->whereTask('IN', $cTask)
				->getCollection()
				->getColumnCollection('user', 'user')
		);

		$cUser->sort([
			'firstName' => SORT_ASC,
			'lastName' => SORT_ASC
		]);

		if($withPresenceAbsence) {
			\hr\PresenceLib::fillUsers($eFarm, $cUser);
			\hr\AbsenceLib::fillUsers($eFarm, $cUser);
		}

		return $cUser;

	}

	public static function getUsersByFarmForPeriod(Farm $eFarm, string $start, string $stop, bool $withPresenceAbsence = FALSE): \Collection {

		$cUser =\hr\PresenceLib::getBetween($eFarm, $start, $stop)
			->getColumnCollection('user', 'user')
			->sort([
				'firstName' => SORT_ASC,
				'lastName' => SORT_ASC
			]);

		if($withPresenceAbsence) {
			\hr\PresenceLib::fillUsers($eFarm, $cUser);
			\hr\AbsenceLib::fillUsers($eFarm, $cUser);
		}

		return $cUser;

	}

	public static function createGhostUser(Farm $eFarm, \user\User $eUser) {

		Employee::model()->beginTransaction();

		$fw = new \FailWatch();

		\user\UserLib::create($eUser);

		if($fw->ok()) {

			$eEmployee = new Employee([
				'id' =>  NULL,
				'farm' => $eFarm,
				'farmGhost' => TRUE,
				'role' => NULL,
				'user' => $eUser,
				'status' => Employee::OUT
			]);

			self::create($eEmployee);

		}

		Employee::model()->commit();

	}

	public static function deleteGhostUser(Farm $eFarm, \user\User $eUser) {

		Employee::model()->beginTransaction();

		$affected = Employee::model()
			->whereFarmGhost(TRUE)
			->whereStatus(Employee::OUT)
			->whereFarm($eFarm)
			->whereUser($eUser)
			->delete();

		if($affected > 0) {
			\user\DropLib::closeNow($eUser);
		}

		Employee::model()->commit();

	}

	public static function associateUser(Employee $e, \user\User $eUser): void {

		$affected = Employee::model()
			->whereStatus(Employee::INVITED)
			->update($e, [
				'user' => $eUser,
				'farmGhost' => FALSE,
				'status' => Employee::IN
			]);

		if($affected) {

			// L'utilisateur passe forcément Employee

			$eRoleNew = \user\RoleLib::getByFqn('farmer');

			\user\User::model()->update($eUser, [
				'role' => $eRoleNew
			]);

		}

	}

	public static function delete(Employee $e): void {

		$e->expects(['farmGhost']);

		if($e['farmGhost']) {
			Employee::fail('deleteGhost');
			return;
		}

		if(
			$e['user']->notEmpty() and
			$e['user']['id'] === \user\ConnectionLib::getOnline()['id']
		) {
			Employee::fail('deleteItself');
			return;
		}

		Employee::model()->beginTransaction();

		parent::delete($e);

		Invite::model()
			->whereEmployee($e)
			->delete();

		Employee::model()->commit();

	}

	public static function register(Farm $eFarm): void {

		$eEmployee = self::getOnlineByFarm($eFarm);

		$properties = array_filter(Employee::model()->getProperties(), fn($property) => str_starts_with($property, 'view'));

		foreach($properties as $property) {
			\Setting::set('main\\'.$property, $eEmployee->notEmpty() ? $eEmployee[$property] : Employee::model()->getDefaultValue($property));
		}

	}

	/**
	 * Get season from $season or from Employee::$viewSeason
	 */
	public static function getDynamicSeason(Farm $eFarm, int $season): int {

		$eFarm->expects(['id', 'seasonLast']);

		if($season) {

			if($eFarm->checkSeason($season) === FALSE) {
				$season = $eFarm['seasonLast'];
			}

			\farm\EmployeeLib::setView('viewSeason', $eFarm, $season);

			return $season;

		} else {
			return \Setting::get('main\viewSeason') ?? $eFarm['seasonLast'];
		}

	}

	public static function setView(string $field, Farm $eFarm, mixed $newView): mixed {

		$eEmployee = self::getOnlineByFarm($eFarm);

		if($eEmployee->empty()) {
			return $newView;
		}

		if($newView === $eEmployee[$field]) {
			return $eEmployee[$field];
		}

		if(Employee::model()->check($field, $newView)) {

			$eEmployee[$field] = $newView;

			Employee::model()
				->select($field)
				->update($eEmployee);

			\Setting::set('main\\'.$field, $eEmployee[$field]);


		}

		return $eEmployee[$field];


	}

}
