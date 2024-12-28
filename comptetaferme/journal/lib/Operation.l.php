<?php
namespace journal;

class OperationLib extends OperationCrud {

	public static function getPropertiesCreate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'lettering'];
	}

	public static function getAll(): \Collection {

		return Operation::model()
			->select(Operation::getSelection() + ['account' => ['class', 'description']])
			->sort(['accountLabel' => SORT_ASC, 'date' => SORT_DESC])
			->getCollection(NULL, NULL, 'id');

	}

	public static function getGrouped(): \Collection {
		return Operation::model()
			->select(['account', 'credit' => new \Sql('SUM(IF(type = "credit", amount, 0))'), 'debit' => new \Sql('SUM(IF(type = "debit", amount, 0))')])
			->group('account')
			->getCollection(NULL, NULL, 'account');

	}

}
?>