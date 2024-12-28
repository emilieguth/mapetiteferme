<?php
namespace journal;

abstract class OperationElement extends \Element {

	use \FilterElement;

	private static ?OperationModel $model = NULL;

	const DEBIT = 'debit';
	const CREDIT = 'credit';

	public static function getSelection(): array {
		return Operation::model()->getProperties();
	}

	public static function model(): OperationModel {
		if(self::$model === NULL) {
			self::$model = new OperationModel();
		}
		return self::$model;
	}

	public static function fail(string|\FailException $failName, array $arguments = [], ?string $wrapper = NULL): bool {
		return \Fail::log('Operation::'.$failName, $arguments, $wrapper);
	}

}


class OperationModel extends \ModuleModel {

	protected string $module = 'journal\Operation';
	protected string $package = 'journal';
	protected string $table = 'journalOperation';

	public function __construct() {

		parent::__construct();

		$this->properties = array_merge($this->properties, [
			'id' => ['serial32', 'cast' => 'int'],
			'account' => ['element32', 'journal\Account', 'cast' => 'element'],
			'accountLabel' => ['text8', 'min' => 1, 'max' => NULL, 'null' => TRUE, 'cast' => 'string'],
			'date' => ['date', 'min' => toDate('NOW - 1 YEARS'), 'max' => toDate('NOW + 1 YEARS'), 'null' => TRUE, 'cast' => 'string'],
			'description' => ['text24', 'min' => 1, 'max' => NULL, 'cast' => 'string'],
			'document' => ['element32', 'journal\Document', 'null' => TRUE, 'cast' => 'element'],
			'amount' => ['decimal', 'digits' => 8, 'decimal' => 2, 'cast' => 'float'],
			'type' => ['enum', [\journal\Operation::DEBIT, \journal\Operation::CREDIT], 'cast' => 'enum'],
			'lettering' => ['text8', 'min' => 1, 'max' => NULL, 'null' => TRUE, 'cast' => 'string'],
		]);

		$this->propertiesList = array_merge($this->propertiesList, [
			'id', 'account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'lettering'
		]);

		$this->propertiesToModule += [
			'account' => 'journal\Account',
			'document' => 'journal\Document',
		];

	}

	public function encode(string $property, $value) {

		switch($property) {

			case 'type' :
				return ($value === NULL) ? NULL : (string)$value;

			default :
				return parent::encode($property, $value);

		}

	}

	public function select(...$fields): OperationModel {
		return parent::select(...$fields);
	}

	public function where(...$data): OperationModel {
		return parent::where(...$data);
	}

	public function whereId(...$data): OperationModel {
		return $this->where('id', ...$data);
	}

	public function whereAccount(...$data): OperationModel {
		return $this->where('account', ...$data);
	}

	public function whereAccountLabel(...$data): OperationModel {
		return $this->where('accountLabel', ...$data);
	}

	public function whereDate(...$data): OperationModel {
		return $this->where('date', ...$data);
	}

	public function whereDescription(...$data): OperationModel {
		return $this->where('description', ...$data);
	}

	public function whereDocument(...$data): OperationModel {
		return $this->where('document', ...$data);
	}

	public function whereAmount(...$data): OperationModel {
		return $this->where('amount', ...$data);
	}

	public function whereType(...$data): OperationModel {
		return $this->where('type', ...$data);
	}

	public function whereLettering(...$data): OperationModel {
		return $this->where('lettering', ...$data);
	}


}


abstract class OperationCrud extends \ModuleCrud {

	public static function getById(mixed $id, array $properties = []): Operation {

		$e = new Operation();

		if(empty($id)) {
			Operation::model()->reset();
			return $e;
		}

		if($properties === []) {
			$properties = Operation::getSelection();
		}

		if(Operation::model()
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
			$properties = Operation::getSelection();
		}

		if($sort !== NULL) {
			Operation::model()->sort($sort);
		}

		return Operation::model()
			->select($properties)
			->whereId('IN', $ids)
			->getCollection(NULL, NULL, $index);

	}

	public static function getCreateElement(): Operation {

		return new Operation(['id' => NULL]);

	}

	public static function create(Operation $e): void {

		Operation::model()->insert($e);

	}

	public static function update(Operation $e, array $properties): void {

		$e->expects(['id']);

		Operation::model()
			->select($properties)
			->update($e);

	}

	public static function updateCollection(\Collection $c, Operation $e, array $properties): void {

		Operation::model()
			->select($properties)
			->whereId('IN', $c)
			->update($e->extracts($properties));

	}

	public static function delete(Operation $e): void {

		$e->expects(['id']);

		Operation::model()->delete($e);

	}

}


class OperationPage extends \ModulePage {

	protected string $module = 'journal\Operation';

	public function __construct(
	   ?\Closure $start = NULL,
	   \Closure|array|null $propertiesCreate = NULL,
	   \Closure|array|null $propertiesUpdate = NULL
	) {
		parent::__construct(
		   $start,
		   $propertiesCreate ?? OperationLib::getPropertiesCreate(),
		   $propertiesUpdate ?? OperationLib::getPropertiesUpdate()
		);
	}

}
?>