<?php
namespace company;

abstract class FarmerElement extends \Element {

	use \FilterElement;

	private static ?FarmerModel $model = NULL;

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
		return Farmer::model()->getProperties();
	}

	public static function model(): FarmerModel {
		if(self::$model === NULL) {
			self::$model = new FarmerModel();
		}
		return self::$model;
	}

	public static function fail(string|\FailException $failName, array $arguments = [], ?string $wrapper = NULL): bool {
		return \Fail::log('Farmer::'.$failName, $arguments, $wrapper);
	}

}


class FarmerModel extends \ModuleModel {

	protected string $module = 'company\Farmer';
	protected string $package = 'farm';
	protected string $table = 'farmFarmer';

	public function __construct() {

		parent::__construct();

		$this->properties = array_merge($this->properties, [
			'id' => ['serial32', 'cast' => 'int'],
			'user' => ['element32', 'user\User', 'null' => TRUE, 'cast' => 'element'],
			'farm' => ['element32', 'company\Company', 'cast' => 'element'],
			'farmGhost' => ['bool', 'cast' => 'bool'],
			'farmStatus' => ['enum', [\company\Farmer::ACTIVE, \company\Farmer::CLOSED], 'cast' => 'enum'],
			'status' => ['enum', [\company\Farmer::INVITED, \company\Farmer::IN, \company\Farmer::OUT], 'cast' => 'enum'],
			'role' => ['enum', [\company\Farmer::SEASONAL, \company\Farmer::PERMANENT, \company\Farmer::OWNER, \company\Farmer::OBSERVER], 'null' => TRUE, 'cast' => 'enum'],
			'viewPlanning' => ['enum', [\company\Farmer::DAILY, \company\Farmer::WEEKLY, \company\Farmer::YEARLY], 'cast' => 'enum'],
			'viewPlanningYear' => ['int16', 'min' => 0, 'max' => NULL, 'null' => TRUE, 'cast' => 'int'],
			'viewPlanningCategory' => ['enum', [\company\Farmer::TIME, \company\Farmer::TEAM, \company\Farmer::PACE, \company\Farmer::PERIOD], 'cast' => 'enum'],
			'viewPlanningHarvestExpected' => ['enum', [\company\Farmer::TOTAL, \company\Farmer::WEEKLY], 'cast' => 'enum'],
			'viewPlanningField' => ['enum', [\company\Farmer::VARIETY, \company\Farmer::SOIL], 'cast' => 'enum'],
			'viewPlanningSearch' => ['json', 'null' => TRUE, 'cast' => 'array'],
			'viewPlanningUser' => ['element32', 'user\User', 'null' => TRUE, 'cast' => 'element'],
			'viewCultivation' => ['enum', [\company\Farmer::SERIES, \company\Farmer::SOIL, \company\Farmer::SEQUENCE], 'cast' => 'enum'],
			'viewCultivationCategory' => ['enum', [\company\Farmer::AREA, \company\Farmer::PLANT, \company\Farmer::FAMILY, \company\Farmer::ROTATION], 'cast' => 'enum'],
			'viewSeries' => ['enum', [\company\Farmer::AREA, \company\Farmer::FORECAST, \company\Farmer::SEEDLING, \company\Farmer::HARVESTING, \company\Farmer::WORKING_TIME], 'cast' => 'enum'],
			'viewSoil' => ['enum', [\company\Farmer::PLAN, \company\Farmer::ROTATION], 'cast' => 'enum'],
			'viewSelling' => ['enum', [\company\Farmer::SALE, \company\Farmer::PRODUCT, \company\Farmer::CUSTOMER, \company\Farmer::INVOICE, \company\Farmer::STOCK], 'cast' => 'enum'],
			'viewSellingSales' => ['enum', [\company\Farmer::ALL, \company\Farmer::PRIVATE, \company\Farmer::PRO, \company\Farmer::LABEL], 'cast' => 'enum'],
			'viewSellingCategory' => ['enum', [\company\Farmer::ITEM, \company\Farmer::CUSTOMER, \company\Farmer::SHOP, \company\Farmer::PERIOD], 'cast' => 'enum'],
			'viewSellingCategoryCurrent' => ['element32', 'selling\Category', 'null' => TRUE, 'cast' => 'element'],
			'viewShop' => ['enum', [\company\Farmer::SHOP, \company\Farmer::CATALOG, \company\Farmer::POINT], 'cast' => 'enum'],
			'viewShopCatalogCurrent' => ['element32', 'shop\Catalog', 'null' => TRUE, 'cast' => 'element'],
			'viewAnalyze' => ['enum', [\company\Farmer::WORKING_TIME, \company\Farmer::REPORT, \company\Farmer::SALES, \company\Farmer::CULTIVATION], 'cast' => 'enum'],
			'viewAnalyzeYear' => ['int16', 'min' => 0, 'max' => NULL, 'null' => TRUE, 'cast' => 'int'],
			'viewSettings' => ['enum', [\company\Farmer::SETTINGS, \company\Farmer::WEBSITE], 'cast' => 'enum'],
			'viewSeason' => ['int16', 'min' => 0, 'max' => NULL, 'null' => TRUE, 'cast' => 'int'],
			'viewShopCurrent' => ['element32', 'shop\Shop', 'null' => TRUE, 'cast' => 'element'],
			'createdAt' => ['datetime', 'cast' => 'string'],
		]);

		$this->propertiesList = array_merge($this->propertiesList, [
			'id', 'user', 'farm', 'farmGhost', 'farmStatus', 'status', 'role', 'viewPlanning', 'viewPlanningYear', 'viewPlanningCategory', 'viewPlanningHarvestExpected', 'viewPlanningField', 'viewPlanningSearch', 'viewPlanningUser', 'viewCultivation', 'viewCultivationCategory', 'viewSeries', 'viewSoil', 'viewSelling', 'viewSellingSales', 'viewSellingCategory', 'viewSellingCategoryCurrent', 'viewShop', 'viewShopCatalogCurrent', 'viewAnalyze', 'viewAnalyzeYear', 'viewSettings', 'viewSeason', 'viewShopCurrent', 'createdAt'
		]);

		$this->propertiesToModule += [
			'user' => 'user\User',
			'farm' => 'company\Company',
			'viewPlanningUser' => 'user\User',
			'viewSellingCategoryCurrent' => 'selling\Category',
			'viewShopCatalogCurrent' => 'shop\Catalog',
			'viewShopCurrent' => 'shop\Shop',
		];

		$this->uniqueConstraints = array_merge($this->uniqueConstraints, [
			['user', 'farm']
		]);

	}

	public function getDefaultValue(string $property) {

		switch($property) {

			case 'farmGhost' :
				return FALSE;

			case 'farmStatus' :
				return Farmer::ACTIVE;

			case 'status' :
				return Farmer::INVITED;

			case 'viewPlanning' :
				return Farmer::WEEKLY;

			case 'viewPlanningCategory' :
				return Farmer::TIME;

			case 'viewPlanningHarvestExpected' :
				return Farmer::TOTAL;

			case 'viewPlanningField' :
				return Farmer::SOIL;

			case 'viewCultivation' :
				return Farmer::SERIES;

			case 'viewCultivationCategory' :
				return Farmer::AREA;

			case 'viewSeries' :
				return Farmer::AREA;

			case 'viewSoil' :
				return Farmer::PLAN;

			case 'viewSelling' :
				return Farmer::SALE;

			case 'viewSellingSales' :
				return Farmer::ALL;

			case 'viewSellingCategory' :
				return Farmer::ITEM;

			case 'viewShop' :
				return Farmer::SHOP;

			case 'viewAnalyze' :
				return Farmer::WORKING_TIME;

			case 'viewSettings' :
				return Farmer::SETTINGS;

			case 'createdAt' :
				return new \Sql('NOW()');

			default :
				return parent::getDefaultValue($property);

		}

	}

	public function encode(string $property, $value) {

		switch($property) {

			case 'farmStatus' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'status' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'role' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewPlanning' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewPlanningCategory' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewPlanningHarvestExpected' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewPlanningField' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewPlanningSearch' :
				return $value === NULL ? NULL : json_encode($value, JSON_UNESCAPED_UNICODE);

			case 'viewCultivation' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewCultivationCategory' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewSeries' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewSoil' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewSelling' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewSellingSales' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewSellingCategory' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewShop' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewAnalyze' :
				return ($value === NULL) ? NULL : (string)$value;

			case 'viewSettings' :
				return ($value === NULL) ? NULL : (string)$value;

			default :
				return parent::encode($property, $value);

		}

	}

	public function decode(string $property, $value) {

		switch($property) {

			case 'viewPlanningSearch' :
				return $value === NULL ? NULL : json_decode($value, TRUE);

			default :
				return parent::decode($property, $value);

		}

	}

	public function select(...$fields): FarmerModel {
		return parent::select(...$fields);
	}

	public function where(...$data): FarmerModel {
		return parent::where(...$data);
	}

	public function whereId(...$data): FarmerModel {
		return $this->where('id', ...$data);
	}

	public function whereUser(...$data): FarmerModel {
		return $this->where('user', ...$data);
	}

	public function whereFarm(...$data): FarmerModel {
		return $this->where('farm', ...$data);
	}

	public function whereFarmGhost(...$data): FarmerModel {
		return $this->where('farmGhost', ...$data);
	}

	public function whereFarmStatus(...$data): FarmerModel {
		return $this->where('farmStatus', ...$data);
	}

	public function whereStatus(...$data): FarmerModel {
		return $this->where('status', ...$data);
	}

	public function whereRole(...$data): FarmerModel {
		return $this->where('role', ...$data);
	}

	public function whereViewPlanning(...$data): FarmerModel {
		return $this->where('viewPlanning', ...$data);
	}

	public function whereViewPlanningYear(...$data): FarmerModel {
		return $this->where('viewPlanningYear', ...$data);
	}

	public function whereViewPlanningCategory(...$data): FarmerModel {
		return $this->where('viewPlanningCategory', ...$data);
	}

	public function whereViewPlanningHarvestExpected(...$data): FarmerModel {
		return $this->where('viewPlanningHarvestExpected', ...$data);
	}

	public function whereViewPlanningField(...$data): FarmerModel {
		return $this->where('viewPlanningField', ...$data);
	}

	public function whereViewPlanningSearch(...$data): FarmerModel {
		return $this->where('viewPlanningSearch', ...$data);
	}

	public function whereViewPlanningUser(...$data): FarmerModel {
		return $this->where('viewPlanningUser', ...$data);
	}

	public function whereViewCultivation(...$data): FarmerModel {
		return $this->where('viewCultivation', ...$data);
	}

	public function whereViewCultivationCategory(...$data): FarmerModel {
		return $this->where('viewCultivationCategory', ...$data);
	}

	public function whereViewSeries(...$data): FarmerModel {
		return $this->where('viewSeries', ...$data);
	}

	public function whereViewSoil(...$data): FarmerModel {
		return $this->where('viewSoil', ...$data);
	}

	public function whereViewSelling(...$data): FarmerModel {
		return $this->where('viewSelling', ...$data);
	}

	public function whereViewSellingSales(...$data): FarmerModel {
		return $this->where('viewSellingSales', ...$data);
	}

	public function whereViewSellingCategory(...$data): FarmerModel {
		return $this->where('viewSellingCategory', ...$data);
	}

	public function whereViewSellingCategoryCurrent(...$data): FarmerModel {
		return $this->where('viewSellingCategoryCurrent', ...$data);
	}

	public function whereViewShop(...$data): FarmerModel {
		return $this->where('viewShop', ...$data);
	}

	public function whereViewShopCatalogCurrent(...$data): FarmerModel {
		return $this->where('viewShopCatalogCurrent', ...$data);
	}

	public function whereViewAnalyze(...$data): FarmerModel {
		return $this->where('viewAnalyze', ...$data);
	}

	public function whereViewAnalyzeYear(...$data): FarmerModel {
		return $this->where('viewAnalyzeYear', ...$data);
	}

	public function whereViewSettings(...$data): FarmerModel {
		return $this->where('viewSettings', ...$data);
	}

	public function whereViewSeason(...$data): FarmerModel {
		return $this->where('viewSeason', ...$data);
	}

	public function whereViewShopCurrent(...$data): FarmerModel {
		return $this->where('viewShopCurrent', ...$data);
	}

	public function whereCreatedAt(...$data): FarmerModel {
		return $this->where('createdAt', ...$data);
	}


}


abstract class FarmerCrud extends \ModuleCrud {

	public static function getById(mixed $id, array $properties = []): Farmer {

		$e = new Farmer();

		if(empty($id)) {
			Farmer::model()->reset();
			return $e;
		}

		if($properties === []) {
			$properties = Farmer::getSelection();
		}

		if(Farmer::model()
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
			$properties = Farmer::getSelection();
		}

		if($sort !== NULL) {
			Farmer::model()->sort($sort);
		}

		return Farmer::model()
			->select($properties)
			->whereId('IN', $ids)
			->getCollection(NULL, NULL, $index);

	}

	public static function getCreateElement(): Farmer {

		return new Farmer(['id' => NULL]);

	}

	public static function create(Farmer $e): void {

		Farmer::model()->insert($e);

	}

	public static function update(Farmer $e, array $properties): void {

		$e->expects(['id']);

		Farmer::model()
			->select($properties)
			->update($e);

	}

	public static function updateCollection(\Collection $c, Farmer $e, array $properties): void {

		Farmer::model()
			->select($properties)
			->whereId('IN', $c)
			->update($e->extracts($properties));

	}

	public static function delete(Farmer $e): void {

		$e->expects(['id']);

		Farmer::model()->delete($e);

	}

}


class FarmerPage extends \ModulePage {

	protected string $module = 'company\Farmer';

	public function __construct(
	   ?\Closure $start = NULL,
	   \Closure|array|null $propertiesCreate = NULL,
	   \Closure|array|null $propertiesUpdate = NULL
	) {
		parent::__construct(
		   $start,
		   $propertiesCreate ?? FarmerLib::getPropertiesCreate(),
		   $propertiesUpdate ?? FarmerLib::getPropertiesUpdate()
		);
	}

}
?>