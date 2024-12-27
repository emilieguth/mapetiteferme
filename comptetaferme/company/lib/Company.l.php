<?php
namespace company;

class CompanyLib extends CompanyCrud {

	private static ?\Collection $cCompanyOnline = NULL;

	public static function getPropertiesCreate(): array {
		return ['name', 'startedAt'];
	}

	public static function getPropertiesUpdate(): array {
		return ['name', 'description', 'startedAt'];
	}

	public static function getOnline(): \Collection {

		if(self::$cCompanyOnline === NULL) {
			$eUser = \user\ConnectionLib::getOnline();
			self::$cCompanyOnline = self::getByUser($eUser);
		}

		return self::$cCompanyOnline;

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
			->join(Employee::model(), 'm1.id = m2.company')
			->select(Company::getSelection())
			->where('m2.user', $eUser)
			->where('m1.status', Company::ACTIVE)
			->getCollection(NULL, NULL, 'id');

	}

	public static function getByUsers(\Collection $cUser, ?string $role = NULL): \Collection {

		return Company::model()
			->select(Company::getSelection())
			->join(Employee::model(), 'm1.id = m2.company')
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
				name LIKE '.\company\Company::model()->format('%'.$query.'%').'
			');

		}

		return \company\Company::model()
			->select($properties ?: Company::getSelection())
			->whereStatus(\company\Company::ACTIVE)
			->sort([
				'name' => SORT_DESC
			])
			->getCollection(0, 20);

	}

	public static function create(Company $e): void {

    Company::model()->beginTransaction();

    Company::model()->insert($e);

		if(isset($e['owner'])) {

			$eEmployee = new Employee([
				'user' => $e['owner'],
				'company' => $e,
				'status' => Employee::IN,
			]);

			Employee::model()->insert($eEmployee);

		}

    (new \ModuleAdministration('company\Company'))->createDatabase(CompanyLib::getDatabaseNameFromCompany($e));

    Company::model()->commit();

	}

  public static function getDatabaseNameFromCompany (Company $e): string {

    return \Database::getPackages()[Company::model()->getPackage()].'_'.$e['id'];

  }

	public static function update(Company $e, array $properties): void {

    Company::model()->beginTransaction();

		parent::update($e, $properties);

		if(in_array('status', $properties)) {

			Employee::model()
				->whereCompany($e)
				->update([
					'companyStatus' => $e['status']
				]);

		}

    Company::model()->commit();

	}

	public static function delete(Company $e): void {

    (new \ModuleAdministration('company\Company'))->dropDatabase(CompanyLib::getDatabaseNameFromCompany($e));

	}

}
