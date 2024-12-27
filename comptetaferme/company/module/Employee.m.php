<?php
namespace company;

abstract class EmployeeElement extends \Element {

	use \FilterElement;

	private static ?EmployeeModel $model = NULL;

	const ACTIVE = 'active';
	const CLOSED = 'closed';

	const INVITED = 'invited';
	const IN = 'in';
	const OUT = 'out';

	const SEASONAL = 'seasonal';
	const PERMANENT = 'permanent';
	const OWNER = 'owner';
	const OBSERVER = 'observer';

	const DAILY = 'daily';
	const WEEKLY = 'weekly';
	const YEARLY = 'yearly';

	const TIME = 'time';
	const TEAM = 'team';
	const PACE = 'pace';
	const PERIOD = 'period';

	const TOTAL = 'total';

	const VARIETY = 'variety';
	const SOIL = 'soil';

	const SERIES = 'series';
	const SEQUENCE = 'sequence';

	const AREA = 'area';
	const PLANT = 'plant';
	const FAMILY = 'family';
	const ROTATION = 'rotation';

	const FORECAST = 'forecast';
	const SEEDLING = 'seedling';
	const HARVESTING = 'harvesting';
	const WORKING_TIME = 'working-time';

	const PLAN = 'plan';

	const SALE = 'sale';
	const PRODUCT = 'product';
	const CUSTOMER = 'customer';
	const INVOICE = 'invoice';
	const STOCK = 'stock';

	const ALL = 'all';
	const PRIVATE = 'private';
	const PRO = 'pro';
	const LABEL = 'label';

	const ITEM = 'item';
	const SHOP = 'shop';

	const CATALOG = 'catalog';
	const POINT = 'point';

	const REPORT = 'report';
	const SALES = 'sales';
	const CULTIVATION = 'cultivation';

	const SETTINGS = 'settings';
	const WEBSITE = 'website';

	public static function getSelection(): array {
		return Employee::model()->getProperties();
	}

	public static function model(): EmployeeModel {
		if(self::$model === NULL) {
			self::$model = new EmployeeModel();
		}
		return self::$model;
	}

	public static function fail(string|\FailException $failName, array $arguments = [], ?string $wrapper = NULL): bool {
		return \Fail::log('Employee::'.$failName, $arguments, $wrapper);
	}

}


class EmployeeModel extends \ModuleModel {

	protected string $module = 'company\Employee';
	protected string $package = 'company';
	protected string $table = 'companyEmployee';

	public function __construct() {

		parent::__construct();

		$this->properties = array_merge($this->properties, [
			'id' => ['serial32', 'cast' => 'int'],
			'user' => ['element32', 'user\User', 'null' => TRUE, 'cast' => 'element'],
			'company' => ['element32', 'company\Company', 'cast' => 'element'],
			'companyGhost' => ['bool', 'cast' => 'bool'],
			'companyStatus' => ['enum', [\company\Employee::ACTIVE, \company\Employee::CLOSED], 'cast' => 'enum'],
			'status' => ['enum', [\company\Employee::INVITED, \company\Employee::IN, \company\Employee::OUT], 'cast' => 'enum'],
			'role' => ['enum', [\company\Employee::SEASONAL, \company\Employee::PERMANENT, \company\Employee::OWNER, \company\Employee::OBSERVER], 'null' => TRUE, 'cast' => 'enum'],
			'createdAt' => ['datetime', 'cast' => 'string'],
		]);

		$this->propertiesList = array_merge($this->propertiesList, [
			'id', 'user', 'company', 'companyGhost', 'companyStatus', 'status', 'role', 'createdAt'
		]);

		$this->propertiesToModule += [
			'user' => 'user\User',
			'company' => 'company\Farm',
		];

		$this->uniqueConstraints = array_merge($this->uniqueConstraints, [
			['user', 'company']
		]);

	}

	public function getDefaultValue(string $property) {

		switch($property) {

			case 'companyGhost' :
				return FALSE;

			case 'companyStatus' :
				return Employee::ACTIVE;

			case 'status' :
				return Employee::INVITED;


			case 'createdAt' :
				return new \Sql('NOW()');

			default :
				return parent::getDefaultValue($property);

		}

	}

	public function encode(string $property, $value) {

		switch($property) {

			case 'companyStatus' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'status' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'role' :
				return ($value === NULL) ? NULL : (string)$value;

			default :
				return parent::encode($property, $value);

		}

	}

	public function decode(string $property, $value) {

		switch($property) {

			default :
				return parent::decode($property, $value);

		}

	}

	public function select(...$fields): EmployeeModel {
		return parent::select(...$fields);
	}

	public function where(...$data): EmployeeModel {
		return parent::where(...$data);
	}

	public function whereId(...$data): EmployeeModel {
		return $this->where('id', ...$data);
	}

	public function whereUser(...$data): EmployeeModel {
		return $this->where('user', ...$data);
	}

	public function whereFarm(...$data): EmployeeModel {
		return $this->where('company', ...$data);
	}

	public function whereFarmGhost(...$data): EmployeeModel {
		return $this->where('companyGhost', ...$data);
	}

	public function whereFarmStatus(...$data): EmployeeModel {
		return $this->where('companyStatus', ...$data);
	}

	public function whereStatus(...$data): EmployeeModel {
		return $this->where('status', ...$data);
	}

	public function whereRole(...$data): EmployeeModel {
		return $this->where('role', ...$data);
	}


}


abstract class EmployeeCrud extends \ModuleCrud {

	public static function getById(mixed $id, array $properties = []): Employee {

		$e = new Employee();

		if(empty($id)) {
			Employee::model()->reset();
			return $e;
		}

		if($properties === []) {
			$properties = Employee::getSelection();
		}

		if(Employee::model()
			->select($properties)
			->whereId($id)
			->get($e) === FALSE) {
				$e->setGhost($id);
		}

		return $e;

	}

	public static function getByIds(mixed $ids, array $properties = [], mixed $sort = NULL, mixed $index = NULL): \Collection {

		if(empty($ids)) {
			return new \Collection();
		}

		if($properties === []) {
			$properties = Employee::getSelection();
		}

		if($sort !== NULL) {
			Employee::model()->sort($sort);
		}

		return Employee::model()
			->select($properties)
			->whereId('IN', $ids)
			->getCollection(NULL, NULL, $index);

	}

	public static function getCreateElement(): Employee {

		return new Employee(['id' => NULL]);

	}

	public static function create(Employee $e): void {

		Employee::model()->insert($e);

	}

	public static function update(Employee $e, array $properties): void {

		$e->expects(['id']);

		Employee::model()
			->select($properties)
			->update($e);

	}

	public static function updateCollection(\Collection $c, Employee $e, array $properties): void {

		Employee::model()
			->select($properties)
			->whereId('IN', $c)
			->update($e->extracts($properties));

	}

	public static function delete(Employee $e): void {

		$e->expects(['id']);

		Employee::model()->delete($e);

	}

}


class EmployeePage extends \ModulePage {

	protected string $module = 'company\Employee';

	public function __construct(
	   ?\Closure $start = NULL,
	   \Closure|array|null $propertiesCreate = NULL,
	   \Closure|array|null $propertiesUpdate = NULL
	) {
		parent::__construct(
		   $start,
		   $propertiesCreate ?? EmployeeLib::getPropertiesCreate(),
		   $propertiesUpdate ?? EmployeeLib::getPropertiesUpdate()
		);
	}

}
?>