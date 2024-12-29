<?php
namespace journal;

class OperationLib extends OperationCrud {

	public static function getPropertiesCreate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'lettering'];
	}
	public static function getPropertiesUpdate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'lettering'];
	}

	public static function countByOldDatesButNotNewDate(\accounting\FinancialYear $eFinancialYear, string $newStartDate, string $newEndDate): int {

		return Operation::model()
			->whereDate('BETWEEN', new \Sql(\accounting\FinancialYear::model()->format($eFinancialYear['startDate']).' AND '.\accounting\FinancialYear::model()->format($eFinancialYear['endDate'])))
			->whereDate('NOT BETWEEN', new \Sql(\accounting\FinancialYear::model()->format($newStartDate).' AND '.\accounting\FinancialYear::model()->format($newEndDate)))
			->count();

	}

	public static function applySearch(\Search $search = new \Search()): OperationModel {

		return Operation::model()
			->whereDate('LIKE', '%'.$search->get('date').'%', if: $search->get('date'))
			->whereDate('>=', $search->get('financialYear')['startDate'], if: $search->has('financialYear'))
			->whereDate('<=', $search->get('financialYear')['endDate'], if: $search->get('financialYear'))
			->whereAccountLabel('LIKE', '%'.$search->get('accountLabel').'%', if: $search->get('accountLabel'))
			->whereDescription('LIKE', '%'.$search->get('description').'%', if: $search->get('description'))
			->whereLettering('LIKE', '%'.$search->get('lettering').'%', if: $search->get('lettering'))
			->whereType($search->get('type'), if: $search->get('type'));

	}
	public static function getAll(\Search $search = new \Search(), bool $hasSort = FALSE): \Collection {

		return self::applySearch($search)
			->select(Operation::getSelection() + ['account' => ['class', 'description']])
			->sort($hasSort === TRUE ? $search->buildSort() : ['accountLabel' => SORT_ASC, 'date' => SORT_DESC])
			->getCollection(NULL, NULL, 'id');

	}

	public static function getGrouped(\Search $search = new \Search()): \Collection {
		return self::applySearch($search)
			->select(['account', 'credit' => new \Sql('SUM(IF(type = "'.OperationElement::CREDIT.'", amount, 0))'), 'debit' => new \Sql('SUM(IF(type = "'.OperationElement::DEBIT.'", amount, 0))')])
			->group('account')
			->getCollection(NULL, NULL, 'account');

	}

}
?>