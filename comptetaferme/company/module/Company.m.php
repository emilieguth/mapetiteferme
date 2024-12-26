<?php
namespace company;

abstract class CompanyElement extends \Element {

	use \FilterElement;

	private static ?CompanyModel $model = NULL;

	const ORGANIC = 'organic';
	const NATURE_PROGRES = 'nature-progres';
	const CONVERSION = 'conversion';

	const ALL = 'all';
	const PRIVATE = 'private';
	const PRO = 'pro';
	const DISABLED = 'disabled';

	const ACTIVE = 'active';
	const CLOSED = 'closed';

	public static function getSelection(): array {
		return Company::model()->getProperties();
	}

	public static function model(): CompanyModel {
		if(self::$model === NULL) {
			self::$model = new FarmModel();
		}
		return self::$model;
	}

	public static function fail(string|\FailException $failName, array $arguments = [], ?string $wrapper = NULL): bool {
		return \Fail::log('Company::'.$failName, $arguments, $wrapper);
	}

}


class FarmModel extends \ModuleModel {

	protected string $module = 'company\Company';
	protected string $package = 'company';
	protected string $table = 'company';

	public function __construct() {

		parent::__construct();

		$this->properties = array_merge($this->properties, [
			'id' => ['serial32', 'cast' => 'int'],
			'name' => ['text8', 'min' => 1, 'max' => NULL, 'collate' => 'general', 'cast' => 'string'],
			'vignette' => ['textFixed', 'min' => 30, 'max' => 30, 'charset' => 'ascii', 'null' => TRUE, 'cast' => 'string'],
			'place' => ['text8', 'min' => 1, 'max' => NULL, 'null' => TRUE, 'cast' => 'string'],
			'placeLngLat' => ['point', 'null' => TRUE, 'cast' => 'json'],
			'url' => ['url', 'null' => TRUE, 'cast' => 'string'],
			'description' => ['editor24', 'null' => TRUE, 'cast' => 'string'],
			'logo' => ['textFixed', 'min' => 30, 'max' => 30, 'charset' => 'ascii', 'null' => TRUE, 'cast' => 'string'],
			'banner' => ['textFixed', 'min' => 30, 'max' => 30, 'charset' => 'ascii', 'null' => TRUE, 'cast' => 'string'],
			'seasonFirst' => ['int16', 'min' => 0, 'max' => NULL, 'cast' => 'int'],
			'seasonLast' => ['int16', 'min' => 0, 'max' => NULL, 'cast' => 'int'],
			'rotationYears' => ['int8', 'min' => 2, 'max' => 5, 'cast' => 'int'],
			'rotationExclude' => ['json', 'cast' => 'array'],
			'quality' => ['enum', [\company\Company::ORGANIC, \company\Company::NATURE_PROGRES, \company\Company::CONVERSION], 'null' => TRUE, 'cast' => 'enum'],
			'defaultBedLength' => ['int16', 'min' => 1, 'max' => NULL, 'null' => TRUE, 'cast' => 'int'],
			'defaultBedWidth' => ['int16', 'min' => 1, 'max' => NULL, 'null' => TRUE, 'cast' => 'int'],
			'defaultAlleyWidth' => ['int16', 'min' => 1, 'max' => NULL, 'null' => TRUE, 'cast' => 'int'],
			'calendarMonthStart' => ['int8', 'min' => 7, 'max' => 12, 'null' => TRUE, 'cast' => 'int'],
			'calendarMonthStop' => ['int8', 'min' => 1, 'max' => 6, 'null' => TRUE, 'cast' => 'int'],
			'planningDelayedMax' => ['int8', 'min' => 1, 'max' => 6, 'null' => TRUE, 'cast' => 'int'],
			'featureTime' => ['bool', 'cast' => 'bool'],
			'featureStock' => ['bool', 'cast' => 'bool'],
			'featureDocument' => ['enum', [\company\Company::ALL, \company\Company::PRIVATE, \company\Company::PRO, \company\Company::DISABLED], 'cast' => 'enum'],
			'stockNotes' => ['text16', 'null' => TRUE, 'cast' => 'string'],
			'stockNotesUpdatedAt' => ['datetime', 'null' => TRUE, 'cast' => 'string'],
			'stockNotesUpdatedBy' => ['element32', 'user\User', 'null' => TRUE, 'cast' => 'element'],
			'hasShops' => ['bool', 'cast' => 'bool'],
			'hasSales' => ['bool', 'cast' => 'bool'],
			'hasCultivations' => ['bool', 'cast' => 'bool'],
			'startedAt' => ['int16', 'min' => date('Y') - 100, 'max' => date('Y') + 10, 'cast' => 'int'],
			'createdAt' => ['datetime', 'cast' => 'string'],
			'status' => ['enum', [\company\Company::ACTIVE, \company\Company::CLOSED], 'cast' => 'enum'],
		]);

		$this->propertiesList = array_merge($this->propertiesList, [
			'id', 'name', 'vignette', 'place', 'placeLngLat', 'url', 'description', 'logo', 'banner', 'seasonFirst', 'seasonLast', 'rotationYears', 'rotationExclude', 'quality', 'defaultBedLength', 'defaultBedWidth', 'defaultAlleyWidth', 'calendarMonthStart', 'calendarMonthStop', 'planningDelayedMax', 'featureTime', 'featureStock', 'featureDocument', 'stockNotes', 'stockNotesUpdatedAt', 'stockNotesUpdatedBy', 'hasShops', 'hasSales', 'hasCultivations', 'startedAt', 'createdAt', 'status'
		]);

		$this->propertiesToModule += [
			'stockNotesUpdatedBy' => 'user\User',
		];

	}

	public function getDefaultValue(string $property) {

		switch($property) {

			case 'rotationYears' :
				return 4;

			case 'rotationExclude' :
				return [];

			case 'quality' :
				return Company::ORGANIC;

			case 'calendarMonthStart' :
				return 10;

			case 'calendarMonthStop' :
				return 3;

			case 'planningDelayedMax' :
				return 2;

			case 'featureTime' :
				return TRUE;

			case 'featureStock' :
				return FALSE;

			case 'featureDocument' :
				return Company::PRO;

			case 'hasShops' :
				return FALSE;

			case 'hasSales' :
				return FALSE;

			case 'hasCultivations' :
				return FALSE;

			case 'createdAt' :
				return new \Sql('NOW()');

			case 'status' :
				return Company::ACTIVE;

			default :
				return parent::getDefaultValue($property);

		}

	}

	public function encode(string $property, $value) {

		switch($property) {

			case 'placeLngLat' :
				return $value === NULL ? NULL : new \Sql($this->pdo()->api->getPoint($value));

			case 'rotationExclude' :
				return $value === NULL ? NULL : json_encode($value, JSON_UNESCAPED_UNICODE);

			case 'quality' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'featureDocument' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'status' :
				return ($value === NULL) ? NULL : (string)$value;

			default :
				return parent::encode($property, $value);

		}

	}

	public function decode(string $property, $value) {

		switch($property) {

			case 'placeLngLat' :
				return $value === NULL ? NULL : json_encode(json_decode($value, TRUE)['coordinates']);

			case 'rotationExclude' :
				return $value === NULL ? NULL : json_decode($value, TRUE);

			default :
				return parent::decode($property, $value);

		}

	}

	public function select(...$fields): FarmModel {
		return parent::select(...$fields);
	}

	public function where(...$data): FarmModel {
		return parent::where(...$data);
	}

	public function whereId(...$data): FarmModel {
		return $this->where('id', ...$data);
	}

	public function whereName(...$data): FarmModel {
		return $this->where('name', ...$data);
	}

	public function whereVignette(...$data): FarmModel {
		return $this->where('vignette', ...$data);
	}

	public function wherePlace(...$data): FarmModel {
		return $this->where('place', ...$data);
	}

	public function wherePlaceLngLat(...$data): FarmModel {
		return $this->where('placeLngLat', ...$data);
	}

	public function whereUrl(...$data): FarmModel {
		return $this->where('url', ...$data);
	}

	public function whereDescription(...$data): FarmModel {
		return $this->where('description', ...$data);
	}

	public function whereLogo(...$data): FarmModel {
		return $this->where('logo', ...$data);
	}

	public function whereBanner(...$data): FarmModel {
		return $this->where('banner', ...$data);
	}

	public function whereSeasonFirst(...$data): FarmModel {
		return $this->where('seasonFirst', ...$data);
	}

	public function whereSeasonLast(...$data): FarmModel {
		return $this->where('seasonLast', ...$data);
	}

	public function whereRotationYears(...$data): FarmModel {
		return $this->where('rotationYears', ...$data);
	}

	public function whereRotationExclude(...$data): FarmModel {
		return $this->where('rotationExclude', ...$data);
	}

	public function whereQuality(...$data): FarmModel {
		return $this->where('quality', ...$data);
	}

	public function whereDefaultBedLength(...$data): FarmModel {
		return $this->where('defaultBedLength', ...$data);
	}

	public function whereDefaultBedWidth(...$data): FarmModel {
		return $this->where('defaultBedWidth', ...$data);
	}

	public function whereDefaultAlleyWidth(...$data): FarmModel {
		return $this->where('defaultAlleyWidth', ...$data);
	}

	public function whereCalendarMonthStart(...$data): FarmModel {
		return $this->where('calendarMonthStart', ...$data);
	}

	public function whereCalendarMonthStop(...$data): FarmModel {
		return $this->where('calendarMonthStop', ...$data);
	}

	public function wherePlanningDelayedMax(...$data): FarmModel {
		return $this->where('planningDelayedMax', ...$data);
	}

	public function whereFeatureTime(...$data): FarmModel {
		return $this->where('featureTime', ...$data);
	}

	public function whereFeatureStock(...$data): FarmModel {
		return $this->where('featureStock', ...$data);
	}

	public function whereFeatureDocument(...$data): FarmModel {
		return $this->where('featureDocument', ...$data);
	}

	public function whereStockNotes(...$data): FarmModel {
		return $this->where('stockNotes', ...$data);
	}

	public function whereStockNotesUpdatedAt(...$data): FarmModel {
		return $this->where('stockNotesUpdatedAt', ...$data);
	}

	public function whereStockNotesUpdatedBy(...$data): FarmModel {
		return $this->where('stockNotesUpdatedBy', ...$data);
	}

	public function whereHasShops(...$data): FarmModel {
		return $this->where('hasShops', ...$data);
	}

	public function whereHasSales(...$data): FarmModel {
		return $this->where('hasSales', ...$data);
	}

	public function whereHasCultivations(...$data): FarmModel {
		return $this->where('hasCultivations', ...$data);
	}

	public function whereStartedAt(...$data): FarmModel {
		return $this->where('startedAt', ...$data);
	}

	public function whereCreatedAt(...$data): FarmModel {
		return $this->where('createdAt', ...$data);
	}

	public function whereStatus(...$data): FarmModel {
		return $this->where('status', ...$data);
	}


}


abstract class CompanyCrud extends \ModuleCrud {

	public static function getById(mixed $id, array $properties = []): Company {

		$e = new Company();

		if(empty($id)) {
			Company::model()->reset();
			return $e;
		}

		if($properties === []) {
			$properties = Company::getSelection();
		}

		if(Company::model()
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
			$properties = Company::getSelection();
		}

		if($sort !== NULL) {
			Company::model()->sort($sort);
		}

		return Company::model()
			->select($properties)
			->whereId('IN', $ids)
			->getCollection(NULL, NULL, $index);

	}

	public static function getCreateElement(): Company {

		return new Company(['id' => NULL]);

	}

	public static function create(Company $e): void {

		Company::model()->insert($e);

	}

	public static function update(Company $e, array $properties): void {

		$e->expects(['id']);

		Company::model()
			->select($properties)
			->update($e);

	}

	public static function updateCollection(\Collection $c, Company $e, array $properties): void {

		Company::model()
			->select($properties)
			->whereId('IN', $c)
			->update($e->extracts($properties));

	}

	public static function delete(Company $e): void {

		$e->expects(['id']);

		Company::model()->delete($e);

	}

}


class CompanyPage extends \ModulePage {

	protected string $module = 'company\Company';

	public function __construct(
	   ?\Closure $start = NULL,
	   \Closure|array|null $propertiesCreate = NULL,
	   \Closure|array|null $propertiesUpdate = NULL
	) {
		parent::__construct(
		   $start,
		   $propertiesCreate ?? CompanyLib::getPropertiesCreate(),
		   $propertiesUpdate ?? CompanyLib::getPropertiesUpdate()
		);
	}

}
?>