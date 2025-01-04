<?php
namespace journal;

abstract class ThirdPartyAccountElement extends \Element {

	use \FilterElement;

	private static ?ThirdPartyAccountModel $model = NULL;

	public static function getSelection(): array {
		return ThirdPartyAccount::model()->getProperties();
	}

	public static function model(): ThirdPartyAccountModel {
		if(self::$model === NULL) {
			self::$model = new ThirdPartyAccountModel();
		}
		return self::$model;
	}

	public static function fail(string|\FailException $failName, array $arguments = [], ?string $wrapper = NULL): bool {
		return \Fail::log('ThirdPartyAccount::'.$failName, $arguments, $wrapper);
	}

}


class ThirdPartyAccountModel extends \ModuleModel {

	protected string $module = 'journal\ThirdPartyAccount';
	protected string $package = 'journal';
	protected string $table = 'journalThirdPartyAccount';

	public function __construct() {

		parent::__construct();

		$this->properties = array_merge($this->properties, [
			'id' => ['serial32', 'cast' => 'int'],
			'thirdParty' => ['element32', 'journal\ThirdParty', 'cast' => 'element'],
			'account' => ['element32', 'accounting\Account', 'cast' => 'element'],
		]);

		$this->propertiesList = array_merge($this->propertiesList, [
			'id', 'thirdParty', 'account'
		]);

		$this->propertiesToModule += [
			'thirdParty' => 'journal\ThirdParty',
			'account' => 'accounting\Account',
		];

		$this->uniqueConstraints = array_merge($this->uniqueConstraints, [
			['thirdParty', 'account']
		]);

	}

	public function select(...$fields): ThirdPartyAccountModel {
		return parent::select(...$fields);
	}

	public function where(...$data): ThirdPartyAccountModel {
		return parent::where(...$data);
	}

	public function whereId(...$data): ThirdPartyAccountModel {
		return $this->where('id', ...$data);
	}

	public function whereThirdParty(...$data): ThirdPartyAccountModel {
		return $this->where('thirdParty', ...$data);
	}

	public function whereAccount(...$data): ThirdPartyAccountModel {
		return $this->where('account', ...$data);
	}


}


abstract class ThirdPartyAccountCrud extends \ModuleCrud {

	public static function getById(mixed $id, array $properties = []): ThirdPartyAccount {

		$e = new ThirdPartyAccount();

		if(empty($id)) {
			ThirdPartyAccount::model()->reset();
			return $e;
		}

		if($properties === []) {
			$properties = ThirdPartyAccount::getSelection();
		}

		if(ThirdPartyAccount::model()
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
			$properties = ThirdPartyAccount::getSelection();
		}

		if($sort !== NULL) {
			ThirdPartyAccount::model()->sort($sort);
		}

		return ThirdPartyAccount::model()
			->select($properties)
			->whereId('IN', $ids)
			->getCollection(NULL, NULL, $index);

	}

	public static function getCreateElement(): ThirdPartyAccount {

		return new ThirdPartyAccount(['id' => NULL]);

	}

	public static function create(ThirdPartyAccount $e): void {

		ThirdPartyAccount::model()->insert($e);

	}

	public static function update(ThirdPartyAccount $e, array $properties): void {

		$e->expects(['id']);

		ThirdPartyAccount::model()
			->select($properties)
			->update($e);

	}

	public static function updateCollection(\Collection $c, ThirdPartyAccount $e, array $properties): void {

		ThirdPartyAccount::model()
			->select($properties)
			->whereId('IN', $c)
			->update($e->extracts($properties));

	}

	public static function delete(ThirdPartyAccount $e): void {

		$e->expects(['id']);

		ThirdPartyAccount::model()->delete($e);

	}

}


class ThirdPartyAccountPage extends \ModulePage {

	protected string $module = 'journal\ThirdPartyAccount';

	public function __construct(
	   ?\Closure $start = NULL,
	   \Closure|array|null $propertiesCreate = NULL,
	   \Closure|array|null $propertiesUpdate = NULL
	) {
		parent::__construct(
		   $start,
		   $propertiesCreate ?? ThirdPartyAccountLib::getPropertiesCreate(),
		   $propertiesUpdate ?? ThirdPartyAccountLib::getPropertiesUpdate()
		);
	}

}
?>