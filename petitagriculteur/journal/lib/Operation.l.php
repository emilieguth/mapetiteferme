<?php
namespace journal;

class OperationLib extends OperationCrud {

	public static function getPropertiesCreate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'vatRate', 'thirdParty', 'asset', 'journalType'];
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
			->whereJournalType('=', $search->get('journalType'), if: $search->get('journalType'))
			->whereDate('LIKE', '%'.$search->get('date').'%', if: $search->get('date'))
			->whereDate('>=', $search->get('financialYear')['startDate'], if: $search->has('financialYear'))
			->whereDate('<=', $search->get('financialYear')['endDate'], if: $search->get('financialYear'))
			->whereAccountLabel('LIKE', '%'.$search->get('accountLabel').'%', if: $search->get('accountLabel'))
			->whereDescription('LIKE', '%'.$search->get('description').'%', if: $search->get('description'))
			->whereDocument($search->get('document'), if: $search->get('document'))
			->whereCashflow('=', $search->get('cashflow'), if: $search->get('cashflow'))
			->whereCashflow(NULL, if: $search->get('cashflowFilter') === TRUE)
			->whereType($search->get('type'), if: $search->get('type'))
			->whereAsset($search->get('asset'), if: $search->get('asset'))
			->whereThirdParty('=', $search->get('thirdParty'), if: $search->get('thirdParty'));

	}

	public static function getByThirdPartyAndOrderedByUsage(ThirdParty $eThirdParty): \Collection {

		return \journal\Operation::model()
			->select(['account', 'count' => new \Sql('COUNT(*)')])
			->whereThirdParty($eThirdParty)
			->group('account')
			->sort(['count' => SORT_DESC])
			->getCollection(NULL, NULL, 'account');

	}

	public static function getAllForBook(\Search $search = new \Search()): \Collection {

		return self::applySearch($search)
			->select(
				Operation::getSelection()
				+ ['thirdParty' => ['name']]
				+ ['class' => new \Sql('SUBSTRING(IF(accountLabel IS NULL, m2.class, accountLabel), 1, 3)')]
				+ ['accountLabel' => new \Sql('IF(accountLabel IS NULL, RPAD(m2.class, 8, "0"), accountLabel)')]
				+ ['account' => ['description']]
			)
			->join(\accounting\Account::model(), 'm1.account = m2.id')
			->sort(['m1_accountLabel' => SORT_ASC, 'date' => SORT_ASC])
			->getCollection();

	}
	public static function getAllForJournal(\Search $search = new \Search(), bool $hasSort = FALSE): \Collection {

		return self::applySearch($search)
			->select(
				Operation::getSelection()
				+ ['operation' => ['id']]
				+ ['account' => ['class', 'description']]
				+ ['thirdParty' => ['id', 'name']]
			)
			->sort($hasSort === TRUE ? $search->buildSort() : ['date' => SORT_ASC, 'id' => SORT_ASC])
			->getCollection();

	}

	public static function updateAccountLabels(\bank\Account $eAccount): bool {

		$eOperation = ['accountLabel' => $eAccount['label'], 'updatedAt' => new \Sql('NOW()')];
		$eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		Operation::model()
			->select(['accountLabel', 'updatedAt'])
			// Liée aux cashflow de ce compte bancaire
			->join(\bank\Cashflow::model(), 'm1.cashflow = m2.id')
			->where('m2.account = '.$eAccount['id'])
			// Type banque
			->join(\accounting\Account::model(), 'm1.account = m3.id')
			->where('m3.class = '.\Setting::get('accounting\bankAccountClass'))
			// De l'exercice comptable courant
			->where('m1.date >= "'.$eFinancialYear['startDate'].'"')
			->where('m1.date <= "'.$eFinancialYear['endDate'].'"')
			->update($eOperation);

		return TRUE;
	}

	public static function update(Operation $e, array $properties): void {

		$e['updatedAt'] = new \Sql('NOW()');
		$properties[] = 'updatedAt';
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
		$vatValues = var_filter($input['vatValue'] ?? [], 'array');
		$document = $input['cashflow']['document'] ?? NULL;
		$isFromCashflow = ($eOperationDefault->offsetExists('cashflow') === TRUE);

		$fw = new \FailWatch();

		$cAccounts = \accounting\AccountLib::getByIdsWithVatAccount($accounts);

		$cOperation = new \Collection();
		$properties = ['account', 'accountLabel', 'description', 'amount', 'type', 'document', 'vatRate', 'comment'];
		if($isFromCashflow === FALSE) {
			$properties[] = 'date';
			$properties[] = 'journalType';
			$properties[] = 'cashflow';
		}

		$eOperationDefault['thirdParty'] = NULL;

		foreach($accounts as $index => $account) {

			$eOperation = clone $eOperationDefault;
			$eOperation['index'] = $index;

			$eOperation->buildIndex($properties, $input, $index);

			$eOperation['amount'] = abs($eOperation['amount']);

			$thirdParty = $input['thirdParty'][$index] ?? null;
			if($thirdParty !== null) {
				$eOperation['thirdParty'] = \journal\ThirdPartyLib::getById($thirdParty);
				if($eOperationDefault['thirdParty'] === NULL) {
					$eOperationDefault['thirdParty'] = $eOperation['thirdParty'];
				}
			}

			// Ce type d'écriture a un compte de TVA correspondant
			$eAccount = $cAccounts[$account] ?? new \accounting\Account();
			$vatValue = var_filter($vatValues[$index] ?? NULL, 'float', 0.0);
			$hasVatAccount = (
				$eAccount->exists() === TRUE
				and $eAccount['vatAccount']->exists() === TRUE
				and (
					$vatValue !== 0.0
					// Cas où on enregistre quand même une entrée de TVA à 0% : Si c'est explicitement indiqué dans eAccount.
					or $eAccount['vatRate'] === 0.0
				)
			);
			if($hasVatAccount === TRUE) {
				$eOperation['vatAccount'] = $eAccount['vatAccount'];
			}

			$fw->validate();

			// Class 2 => Vérification et création de l'immobilisation
			$eAsset = \asset\AssetLib::prepareAsset($eOperation, $input['asset'][$index] ?? [], $index);

			$fw->validate();

			$eOperation['asset'] = $eAsset;

			\journal\Operation::model()->insert($eOperation);
			$cOperation->append($eOperation);

			// Ajout de l'entrée de compte de TVA correspondante
			if($hasVatAccount === TRUE) {

				$eOperationVat = \journal\OperationLib::createVatOperation(
					$eOperation,
					$eAccount,
					$input['vatValue'][$index],
					$isFromCashflow === TRUE
						? [
							'date' => $eOperationDefault['cashflow']['date'],
							'description' => $eOperation['description'] ?? $eOperationDefault['cashflow']['memo'],
							'cashflow' => $eOperationDefault['cashflow'],
							'journalType' => $eOperation['journalType'],
						]
						: $eOperation->getArrayCopy(),
				);

				$cOperation->append($eOperationVat);
			}

			// Journal de caisse : créer une entrée contrepartie en caisse (TTC)
			if($isFromCashflow === FALSE) {

				if($eOperation['journalType'] === OperationElement::CASH) {

					$eOperationCounterpart = self::createCounterPartOperation(
						$eOperation,
						$eOperation['amount'] + ($eOperationVat['amount'] ?? 0),
						\Setting::get('accounting\cashAccountClass'),
					);
					$cOperation->append($eOperationCounterpart);

				} else if($eOperation['journalType'] === OperationElement::BANK) {

					$eOperationCounterpart = self::createCounterPartOperation(
						$eOperation,
						$eOperation['amount'] + ($eOperationVat['amount'] ?? 0),
						\Setting::get('accounting\bankAccountClass'),
					);
					$cOperation->append($eOperationCounterpart);

				}
			}
		}

		// Ajout de la transaction sur la classe de compte bancaire 512
		if($isFromCashflow === TRUE) {

			$eOperationBank = \journal\OperationLib::createBankOperationFromCashflow(
				$eOperationDefault['cashflow'],
				$document,
				$eOperationDefault['thirdParty'],
			);
			$cOperation->append($eOperationBank);

		}

		if($fw->ko()) {
			return new \Collection();
		}

		return $cOperation;
	}

	private static function createCounterPartOperation(Operation $eOperationBase, float $amount, string $class): Operation {

		$eOperation = clone $eOperationBase;
		unset($eOperation['id']);

		$eOperation['account'] = \accounting\AccountLib::getByClass($class);

		if($class === \Setting::get('accounting\bankAccountClass')) {

			$eOperation['accountLabel'] = \bank\AccountLib::getDefaultAccount()['label'];

		} else {

			$eOperation['accountLabel'] = \accounting\AccountLib::padClass($eOperation['account']['class']);

		}

		$eOperation['type'] = $eOperationBase['type'] === OperationElement::CREDIT ? OperationElement::DEBIT : OperationElement::CREDIT;
		$eOperation['amount'] = $amount;

		\journal\Operation::model()->insert($eOperation);

		return $eOperation;
	}

	public static function createVatOperation(Operation $eOperationLinked, \accounting\Account $eAccount, float $vatValue, array $defaultValues): Operation {

		$values = [
			...$defaultValues,
			'account' => $eAccount['vatAccount']['id'] ?? NULL,
			'accountLabel' => \accounting\AccountLib::padClass($eAccount['vatAccount']['class']),
			'document' => $eOperationLinked['document'],
			'thirdParty' => $eOperationLinked['thirdParty']['id'] ?? NULL,
			'type' => $eOperationLinked['type'],
			'amount' => abs($vatValue),
		];
		if($eOperationLinked['cashflow']->exists() === TRUE) {
			$values['cashflow'] = $eOperationLinked['cashflow']['id'];
		}

		$eOperationVat = new Operation();

		$fw = new \FailWatch();

		$eOperationVat->build(
			['cashflow', 'date', 'account', 'accountLabel', 'description', 'document', 'thirdParty', 'type', 'amount', 'operation', 'journalType'],
			$values,
			new \Properties('create'),
		);
		$eOperationVat['operation'] = $eOperationLinked;

		$fw->validate();

		Operation::model()->insert($eOperationVat);

		return $eOperationVat;

	}

	public static function delete(Operation $e): void {

		\journal\Operation::model()->beginTransaction();

		$e->expects(['id', 'asset']);

		// Deletes related operations (like assets... or VAT)
		if($e['asset']->exists() === TRUE) {
			\asset\AssetLib::deleteByIds([$e['asset']['id']]);
		}

		Operation::model()
			->whereOperation($e)
			->delete();

		parent::delete($e);

		\journal\Operation::model()->commit();

	}

	private static function addOpenFinancialYearCondition(): OperationModel {

		$cFinancialYear = \accounting\FinancialYearLib::getOpenFinancialYears();
		$dateConditions = [];
		foreach($cFinancialYear as $eFinancialYear) {
			$dateConditions[] = 'date BETWEEN "'.$eFinancialYear['startDate'].'" AND "'.$eFinancialYear['endDate'].'"';
		}

		return Operation::model()->where(join(' OR ', $dateConditions), if: empty($dateConditions) === FALSE);

	}
	public static function getOperationsForAttach(\bank\Cashflow $eCashflow): \Collection {

		$amount = abs($eCashflow['amount']);

		$properties = Operation::getSelection()
			+ ['account' => ['class', 'description']]
			+ ['thirdParty' => ['name']];

		$cOperation = self::addOpenFinancialYearCondition()
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

		$properties = ['cashflow', 'updatedAt', 'journalType'];
		$eOperation = new Operation([
			'cashflow' => $eCashflow,
			'updatedAt' => Operation::model()->now(),
			'journalType' => OperationElement::BANK,
		]);

		$updated = self::addOpenFinancialYearCondition()
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

	public static function createBankOperationFromCashflow(\bank\Cashflow $eCashflow, ?string $document = NULL, ?ThirdParty $eThirdParty = NULL): Operation {

		$eAccountBank = \bank\AccountLib::getByClass(\Setting::get('accounting\bankAccountClass'));

		if($eCashflow['import']['account']['label'] !== NULL) {
			$label = $eCashflow['import']['account']['label'];
		} else {
			$label = \accounting\AccountLib::padClass(\Setting::get('accounting\defaultBankAccountLabel'));
		}

		$values = [
			'date' => $eCashflow['date'],
			'cashflow' => $eCashflow['id'] ?? NULL,
			'account' => $eAccountBank['id'] ?? NULL,
			'accountLabel' => $label,
			'description' => $eCashflow['memo'],
			'document' => $document,
			'thirdParty' => $eThirdParty['id'] ?? NULL,
			'type' => match($eCashflow['type']) {
				\bank\Cashflow::CREDIT => Operation::DEBIT,
				\bank\Cashflow::DEBIT => Operation::CREDIT,
			},
			'amount' => abs($eCashflow['amount']),
			'journalOperation' => OperationElement::BANK,
		];

		$eOperationBank = new Operation();

		$fw = new \FailWatch();

		$eOperationBank->build(['cashflow', 'date', 'account', 'accountLabel', 'description', 'document', 'thirdParty', 'type', 'amount', 'operation'], $values, new \Properties('create'));

		$fw->validate();

		\journal\Operation::model()->insert($eOperationBank);


		return $eOperationBank;

	}

	public static function createFromValues(array $values): void {

		$eOperation = new Operation();

		$fw = new \FailWatch();

		$eOperation->build(
			['cashflow', 'date', 'account', 'accountLabel', 'description', 'document', 'thirdParty', 'type', 'amount', 'operation', 'vatRate', 'vatAccount', 'asset'],
			$values,
			new \Properties('create')
		);

		if(($values['asset'] ?? NULL) !== NULL) {
			$eOperation['asset'] = $values['asset'];
		}

		if(($values['cashflow'] ?? NULL) !== NULL) {
			$eOperation['cashflow'] = $values['cashflow'];
		}

		$fw->validate();

		Operation::model()->insert($eOperation);
	}

	public static function getByCashflow(\bank\Cashflow $eCashflow): \Collection {

		return Operation::model()
			->select(['id', 'asset'])
      ->whereCashflow($eCashflow)
      ->getCollection();
	}

	public static function deleteByCashflow(\bank\Cashflow $eCashflow): void {

		if($eCashflow->exists() === FALSE) {
			return;
		}

		Operation::model()->beginTransaction();

		// Get all the operation and check if we have to delete the assets too
		$cAsset = OperationLib::getByCashflow($eCashflow)->getColumnCollection('asset');
		if($cAsset->empty() === FALSE) {
			\asset\AssetLib::deleteByIds($cAsset->getIds());
		}

		\journal\Operation::model()
      ->whereCashflow('=', $eCashflow['id'])
      ->delete();

		Operation::model()->commit();

	}

	public static function countGroupByThirdParty(): \Collection {

		return Operation::model()
			->select(['count' => new \Sql('COUNT(*)', 'int'), 'thirdParty'])
			->group('thirdParty')
			->getCollection(NULL, NULL, 'thirdParty');

	}

	public static function getLabels(string $query, ?int $thirdParty, ?int $account): array {

		$labels = Operation::model()
			->select(['accountLabel' => new \Sql('DISTINCT(accountLabel)')])
			->whereThirdParty($thirdParty, if: $thirdParty !== NULL)
			->whereAccount($account, if: $account !== NULL)
			->where('accountLabel LIKE "%'.$query.'%"', if: $query !== '')
			->sort(['accountLabel' => SORT_ASC])
			->getCollection()
			->getColumn('accountLabel');

		return $labels;

	}

	public static function countByAccounts(\Collection $cAccount): \Collection {

		return Operation::model()
			->select([
				'count' => new \Sql('COUNT(*)', 'int'),
				'account'
			])
			->whereAccount('IN', $cAccount->getIds())
			->group('account')
			->getCollection(NULL, NULL, 'account');

	}

	public static function countByAccount(\accounting\Account $eAccount): int {

		$eAccount->expects(['id']);

		return Operation::model()
			->whereAccount($eAccount)
			->count();

	}
}
?>
