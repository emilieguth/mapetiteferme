<?php
namespace company;

class CompanyLib extends CompanyCrud {

	private static ?\Collection $cFarmOnline = NULL;

	public static function getPropertiesCreate(): array {
		return ['name', 'startedAt', 'place', 'placeLngLat', 'defaultBedLength', 'defaultBedWidth', 'defaultAlleyWidth', 'quality'];
	}

	public static function getPropertiesUpdate(): array {
		return ['name', 'description', 'startedAt', 'place', 'placeLngLat', 'url', 'defaultBedLength', 'defaultBedWidth', 'defaultAlleyWidth', 'quality'];
	}

	public static function getOnline(): \Collection {

		if(self::$cFarmOnline === NULL) {
			$eUser = \user\ConnectionLib::getOnline();
			self::$cFarmOnline = self::getByUser($eUser);
		}

		return self::$cFarmOnline;

	}

	public static function getList(?array $properties = NULL): \Collection {

		return Company::model()
			->select($properties ?? Company::getSelection())
			->whereStatus(Company::ACTIVE)
			->sort('name')
			->getCollection();

	}

	public static function getByUser(\user\User $eUser): \Collection {

		return Company::model()
			->join(Farmer::model(), 'm1.id = m2.farm')
			->select(Company::getSelection())
			->where('m2.user', $eUser)
			->where('m1.status', Company::ACTIVE)
			->getCollection(NULL, NULL, 'id');

	}

	public static function getByUsers(\Collection $cUser, ?string $role = NULL): \Collection {

		return Company::model()
			->select(Company::getSelection())
			->join(Farmer::model(), 'm1.id = m2.farm')
			->where('m2.user', 'IN', $cUser)
			->where('m2.role', $role, if: ($role !== NULL))
			->where('m1.status', Company::ACTIVE)
			->getCollection();

	}

	public static function getFromQuery(string $query, ?array $properties = []): \Collection {

		if(strpos($query, '#') === 0 and ctype_digit(substr($query, 1))) {

			\company\Company::model()->whereId(substr($query, 1));

		} else {

			\company\Company::model()->where('
				place LIKE '.\company\Company::model()->format('%'.$query.'%').' OR
				name LIKE '.\company\Company::model()->format('%'.$query.'%').'
			');

		}

		return \company\Company::model()
			->select($properties ?: Company::getSelection())
			->whereStatus(\company\Company::ACTIVE)
			->sort([
				new \Sql('IF(place LIKE '.\company\Company::model()->format('%'.$query.'%').', 1, 0) + IF(name LIKE '.\company\Company::model()->format('%'.$query.'%').', 2, 0) DESC'),
				'name' => SORT_DESC
			])
			->getCollection(0, 20);

	}

	public static function create(Company $e): void {

    Company::model()->beginTransaction();

		$e['seasonFirst'] = date('Y');
		$e['seasonLast'] = date('n') >= (\Setting::get('company\newSeason') - 1) ? date('Y') + 1 : date('Y');

    Company::model()->insert($e);

		if(isset($e['owner'])) {

			$eFarmer = new Farmer([
				'user' => $e['owner'],
				'farm' => $e,
				'status' => Farmer::IN,
				'role' => Farmer::OWNER
			]);

			Farmer::model()->insert($eFarmer);

			$ePresence = new \hr\Presence([
				'farm' => $e,
				'user' => $eFarmer['user'],
				'from' => $e['startedAt'].'-01-01'
			]);

			\hr\Presence::model()->insert($ePresence);

		}

		\selling\ConfigurationLib::createForFarm($e);

		\company\ActionLib::duplicateForFarm($e);
		\plant\PlantLib::duplicateForFarm($e);

    Company::model()->commit();

	}

	public static function update(Company $e, array $properties): void {

    Company::model()->beginTransaction();

		// Les notes de stocks laissées vide reste à '' pour éviter de les désactiver
		if(in_array('stockNotes', $properties)) {

			if($e['stockNotes'] === NULL) {
				$e['stockNotes'] = '';
			}

			$e['stockNotesUpdatedAt'] = new \Sql('NOW()');
			$e['stockNotesUpdatedBy'] = \user\ConnectionLib::getOnline();

			$properties[] = 'stockNotesUpdatedAt';
			$properties[] = 'stockNotesUpdatedBy';

		}

		parent::update($e, $properties);

		if(in_array('status', $properties)) {

			Farmer::model()
				->whereFarm($e)
				->update([
					'farmStatus' => $e['status']
				]);

		}

    Company::model()->commit();

	}

	public static function updateSeasonFirst(Company $e, int $increment): void {

		if($increment !== -1 and $increment !== 1) {
      Company::fail('seasonFirst.check');
			return;
		}

    Company::model()
			->where('seasonFirst + '.$increment.' <= seasonLast')
			->update($e, [
				'seasonFirst' => new \Sql('seasonFirst + '.$increment)
			]);

	}

	public static function updateSeasonLast(Company $e, int $increment): void {

		if($increment !== -1 and $increment !== 1) {
      Company::fail('seasonLast.check');
			return;
		}

    Company::model()
			->where('seasonLast + '.$increment.' BETWEEN seasonFirst AND '.(date('Y') + 10).'')
			->update($e, [
				'seasonLast' => new \Sql('seasonLast + '.$increment)
			]);

	}

	public static function updateStockNotesStatus(Company $e, bool $enable): void {

		Company::model()
			->update($e, [
				'stockNotes' => $enable ? '' : NULL,
				'stockNotesUpdatedAt' => NULL,
				'stockNotesUpdatedBy' => new \user\User()
			]);

	}

	public static function delete(Company $e): void {

		throw new \Exception('Not implemented');

	}

	public static function createNextSeason(): void {

		$newSeason = date('Y') + 1;

		Company::model()
			->where('seasonLast < '.$newSeason)
			->update([
				'seasonLast' => $newSeason
			]);

	}

}
