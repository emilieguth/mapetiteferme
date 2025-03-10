<?php
namespace journal;

class OperationLib extends OperationCrud {

	public static function getPropertiesCreate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'vatRate', 'thirdParty'];
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
			->whereCashflow(NULL, if: $search->get('cashflowFilter') === TRUE)
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
	public static function prepareOperations(array $input, Operation $eOperationDefault): \Collection {

		$accounts = var_filter($input['account'] ?? [], 'array');
		$document = $input['cashflow']['document'] ?? null;

		$fw = new \FailWatch();

		$cAccounts = \accounting\AccountLib::getByIdsWithVatAccount($accounts);

		$cOperation = new \Collection();
		$properties = ['account', 'accountLabel', 'description', 'amount', 'type', 'document', 'vatRate'];
		if($eOperationDefault->offsetExists('date') === FALSE) {
			$properties[] = 'date';
		}

		foreach($accounts as $index => $account) {

			$eOperation = new \journal\Operation($eOperationDefault->getArrayCopy());
			$eOperation['index'] = $index;

			$eOperation->buildIndex($properties, $input, $index);

			$eOperation['amount'] = abs($eOperation['amount']);

			$thirdParty = $input['thirdParty'][$index] ?? null;
			if($thirdParty !== null) {
				$eOperation['thirdParty'] = \journal\ThirdPartyLib::getByName($thirdParty);
			}

			// Ce type d'écriture a un compte de TVA correspondant
			$eAccount = $cAccounts[$account] ?? new \accounting\Account();
			$hasVatAccount = $eAccount['vatAccount']->exists() === TRUE;
			if($hasVatAccount === TRUE) {
				$eOperation['vatAccount'] = $eAccount['vatAccount'];
			}

			\journal\Operation::model()->insert($eOperation);
			$cOperation->append($eOperation);

			// Ajout de l'entrée de compte de TVA correspondante
			if($hasVatAccount === TRUE) {

				$eOperationVat = \journal\OperationLib::createVatOperation(
					$eOperation,
					$eAccount,
					$input['vatValue'][$index],
					$eOperationDefault->offsetExists('cashflow') === TRUE
						? ['date' => $eOperationDefault['cashflow']['date'], 'description' => $eOperationDefault['cashflow']['memo'], 'cashflow' => $eOperationDefault['cashflow']]
						: $eOperation->getArrayCopy(),
				);

				$cOperation->append($eOperationVat);
			}
		}

		// Ajout de la transaction sur la classe de compte bancaire 512
		if($eOperationDefault->offsetExists('cashflow') === TRUE) {

			$eOperationBank = \journal\OperationLib::createBankOperationFromCashflow($eOperationDefault['cashflow'], $document);
			$cOperation->append($eOperationBank);

		}

		if($fw->ko()) {
			return new \Collection();
		}

		return $cOperation;
	}

	public static function createVatOperation(Operation $eOperationLinked, \accounting\Account $eAccount, float $vatValue, array $defaultValues): Operation {

		$eOperationVat = new \journal\Operation();
		$eOperationVat['cashflow'] = $defaultValues['cashflow'];
		$eOperationVat['date'] = $defaultValues['date'];
		$eOperationVat['account'] = $eAccount['vatAccount'];
		$eOperationVat['description'] = $defaultValues['description'];
		$eOperationVat['document'] = $eOperationLinked['document'];
		$eOperationVat['thirdParty'] = $eOperationLinked['thirdParty'];
		$eOperationVat['type'] = match(mb_substr($eAccount['class'], 0, 1)) {
			'7' => \journal\OperationElement::CREDIT,
			'2' => \journal\OperationElement::DEBIT,
			'6' => \journal\OperationElement::DEBIT,
			default => NULL,
		};
		$eOperationVat['amount'] = abs($vatValue);
		$eOperationVat['operation'] = $eOperationLinked;

		\journal\Operation::model()->insert($eOperationVat);

		return $eOperationVat;

	}

	public static function delete(Operation $e): void {

		$e->expects(['id']);

		// Deletes related operations (like TVA)
		Operation::model()
			->whereOperation($e)
			->delete();

		parent::delete($e);

	}

	public static function getOperationsForAttach(\bank\Cashflow $eCashflow): \Collection {

		$amount = abs($eCashflow['amount']);

		$properties = Operation::getSelection()
			+ ['account' => ['class', 'description']]
			+ ['thirdParty' => ['name']];

		$cOperation = Operation::model()
			->select($properties)
			->whereCashflow(NULL)
			->whereOperation(NULL)
			->getCollection();

		$cOperationLinked = $cOperation->empty() === FALSE ? Operation::model()
			->select($properties)
			->whereCashflow(NULL)
			->whereOperation('IN', $cOperation)
			->getCollection() : new \Collection();

		// Tri pour optimiser le montant
		foreach($cOperation as &$eOperation) {
			$eOperation['links'] = new \Collection();
			$sum = 0;
			foreach($cOperationLinked as $eOperationLinked) {
				if($eOperationLinked['operation']['id'] === $eOperation['id']) {
					$sum += $eOperationLinked['amount'];
					$eOperation['links']->append($eOperationLinked);
				}
			}
			$eOperation['totalVATIncludedAmount'] = $eOperation['amount'] + $sum;
			$eOperation['difference'] = abs($eOperation['totalVATIncludedAmount'] - $amount);
		}

		$cOperation->sort(['difference' => SORT_ASC]);

		return $cOperation;

	}

	public static function countByCashflow(\bank\Cashflow $eCashflow): int {

		return Operation::model()
			->whereCashflow($eCashflow)
			->count();

	}

	public static function attachIdsToCashflow(\bank\Cashflow $eCashflow, array $operationIds): int {

		$properties = ['cashflow', 'updatedAt'];
		$eOperation = new Operation(['cashflow' => $eCashflow, 'updatedAt' => Operation::model()->now()]);

		$updated = Operation::model()
			->select($properties)
			->whereId('IN', $operationIds)
			->whereCashflow(NULL)
			->update($eOperation);

		// Also update linked operations
		Operation::model()
			->select($properties)
			->whereOperation('IN', $operationIds)
			->whereCashflow(NULL)
			->update($eOperation);

		// Create Bank line
		OperationLib::createBankOperationFromCashflow($eCashflow);

		return $updated;
	}

	public static function createBankOperationFromCashflow(\bank\Cashflow $eCashflow, ?string $document = NULL): Operation {

		$eOperationBank = new Operation();

		$eAccountBank = new \accounting\Account();
		\accounting\Account::model()
			->select(\accounting\Account::getSelection())
			->whereClass('=', \Setting::get('accounting\bankAccountClass'))
			->get($eAccountBank);

		$eOperationBank['date'] = $eCashflow['date'];
		$eOperationBank['cashflow'] = $eCashflow;
		$eOperationBank['account'] = $eAccountBank;
		$eOperationBank['accountLabel'] = $eCashflow['import']['account']['label'] ?? \Setting::get('accounting\defaultBankAccountLabel');
		$eOperationBank['description'] = $eCashflow['memo'];
		$eOperationBank['document'] = $document;
		$eOperationBank['type'] = match($eCashflow['type']) {
			\bank\Cashflow::CREDIT => Operation::DEBIT,
			\bank\Cashflow::DEBIT => Operation::CREDIT,
		};
		$eOperationBank['amount'] = abs($eCashflow['amount']);

		\journal\Operation::model()->insert($eOperationBank);

		return $eOperationBank;

	}
}
?>
