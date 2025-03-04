<?php
namespace journal;

class OperationLib extends OperationCrud {

	public static function getPropertiesCreate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type'];
	}
	public static function getPropertiesUpdate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'thirdParty'];
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
			->whereDocument('LIKE', '%'.$search->get('document').'%', if: $search->get('document'))
			->whereCashflow('=', $search->get('cashflow'), if: $search->get('cashflow'))
			->whereType($search->get('type'), if: $search->get('type'));

	}

	public static function getByThirdPartyAndOrderedByUsage(ThirdParty $eThirdParty): \Collection {

		return \journal\Operation::model()
			->select(['account', 'count' => new \Sql('COUNT(*)')])
			->whereThirdParty($eThirdParty)
			->group('account')
			->sort(['count' => SORT_DESC])
			->getCollection(NULL, NULL, 'account');

	}

	public static function getAllForBook(\Search $search = new \Search(), bool $hasSort = FALSE): \Collection {

		$ccOperation = self::applySearch($search)
			->select(
				Operation::getSelection()
				+ ['account' => ['class', 'description']]
				+ ['thirdParty' => ['name']]
			)
			->sort(['date' => SORT_ASC])
			->getCollection()
			->reindex(['account', 'class']);

		$cccOperation = new \Collection();
		foreach($ccOperation as $class => $cOperation) {
			$cccOperation[$class] = $cOperation->reindex(['accountLabel']);
		}
		return $cccOperation;

	}
	public static function getAllForJournal(\Search $search = new \Search(), bool $hasSort = FALSE): \Collection {

		return self::applySearch($search)
			->select(
				Operation::getSelection()
				+ ['account' => ['class', 'description']]
				+ ['thirdParty' => ['name']]
			)
			->sort($hasSort === TRUE ? $search->buildSort() : ['date' => SORT_ASC, 'id' => SORT_ASC])
			->getCollection();

	}

	public static function getGrouped(\Search $search = new \Search()): \Collection {
		return self::applySearch($search)
			->select([
				'account' => ['id', 'class', 'description'],
				'credit' => new \Sql('SUM(IF(type = "'.OperationElement::CREDIT.'", amount, 0))'),
				'debit' => new \Sql('SUM(IF(type = "'.OperationElement::DEBIT.'", amount, 0))'),
			])
			->group('account')
			->getCollection()
			->reindex(['account', 'class']);

	}
	public static function updateAccountLabels(\bank\Account $eAccount): bool {

		$eOperation = ['accountLabel' => $eAccount['label']];
		$eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		Operation::model()
			// Liée aux cashflow de ce compte bancaire
			->join(\bank\Cashflow::model(), 'm1.cashflow = m2.id')
			->where('m2.account = '.$eAccount['id'])
			// Type banque
			->join(\accounting\Account::model(), 'm1.account = m3.id')
			->where('m3.class = '.\Setting::get('accounting\bankAccountClass'))
			// De l'exercice fiscal courant
			->where('m1.date >= "'.$eFinancialYear['startDate'].'"')
			->where('m1.date <= "'.$eFinancialYear['endDate'].'"')
			->update($eOperation);

		return TRUE;
	}

	public static function update(Operation $e, array $properties): void {
		parent::update($e, $properties);

		// Quick document update
		if(in_array('document', $properties) === TRUE) {
			// On rattache cette pièce comptable au cashflow + aux opérations liées
			if($e['cashflow']->exists() === TRUE) {
				$eCashflow = $e['cashflow'];
				$eCashflow['document'] = $e['document'];
				\bank\CashflowLib::update($eCashflow, ['document']);
				Operation::model()
					->select('document')
					->whereCashflow('=', $e['cashflow']['id'])
					->update(['document' => $e['document']]);
			}
		}
	}
}
?>
