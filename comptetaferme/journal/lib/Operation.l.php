<?php
namespace journal;

class OperationLib extends OperationCrud {

	public static function getPropertiesCreate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'lettering'];
	}

	public static function getAll(): \Collection {

		return Operation::model()
			->select(Operation::getSelection() + ['account' => ['class', 'description']])
			->getCollection(NULL, NULL, 'id');

	}

}
?>