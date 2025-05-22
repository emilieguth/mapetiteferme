<?php
namespace journal;

class OperationLib extends OperationCrud {

	public static function getPropertiesCreate(): array {
		return ['account', 'accountLabel', 'date', 'description', 'document', 'amount', 'type', 'vatRate', 'thirdParty', 'asset'];
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
			->whereDate('>=', fn() => $search->get('financialYear')['startDate'], if: $search->has('financialYear'))
			->whereDate('<=', fn() => $search->get('financialYear')['endDate'], if: $search->has('financialYear'))
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

	public static function getAllForVatJournal(string $type, \Search $search = new \Search(), bool $hasSort = FALSE): \Collection {

		$whereAccountLabels = [];
		if($type === 'buy') {
			foreach(\Setting::get('accounting\vatBuyVatClasses') as $class) {
				$whereAccountLabels[] = 'accountLabel LIKE "'.$class.'%"';
			}
		} elseif($type === 'sell') {
			foreach(\Setting::get('accounting\vatSellVatClasses') as $class) {
				$whereAccountLabels[] = 'accountLabel LIKE "'.$class.'%"';
			}
		}
		$whereAccountLabelSql = new \Sql(join(' OR ', $whereAccountLabels));

		return self::applySearch($search)
			->select(
				Operation::getSelection()
				+ ['operation' => [
					'id', 'account', 'accountLabel', 'document', 'type',
					'thirdParty' => ['id', 'name'],
					'description', 'amount', 'vatRate', 'cashflow', 'date'
				]]
				+ ['account' => ['class', 'description']]
				+ ['thirdParty' => ['id', 'name']]
				+ ['month' => new \Sql('SUBSTRING(date, 1, 7)')]
			)
			->sort($hasSort === TRUE ? $search->buildSort() : ['accountLabel' => SORT_ASC, 'date' => SORT_ASC, 'id' => SORT_ASC])
			->where($whereAccountLabelSql)
			->where(new \Sql('operation IS NOT NULL'))
			->getCollection(NULL, NULL, ['accountLabel', 'month', 'id']);

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
		if(in_array('document', $properties) === TRUE) {
			$properties[] = 'documentDate';
			$e['documentDate'] =  new \Sql('NOW()');
		}
		parent::update($e, $properties);

		// Quick document update
		if(in_array('document', $properties) === TRUE) {
			// On rattache cette pièce comptable au cashflow + aux opérations liées
			if($e['cashflow']->exists() === TRUE) {
				$eCashflow = $e['cashflow'];
				$eCashflow['document'] = $e['document'];
				\bank\CashflowLib::update($eCashflow, ['document']);
				Operation::model()
					->select('document', 'documentDate')
					->whereCashflow('=', $e['cashflow']['id'])
					->update(['document' => $e['document'], 'documentDate' => new \Sql('NOW()')]);
			}
		}
	}

	public static function prepareOperations(array $input, Operation $eOperationDefault): \Collection {

		$accounts = var_filter($input['account'] ?? [], 'array');
		$vatValues = var_filter($input['vatValue'] ?? [], 'array');
		$isFromCashflow = ($eOperationDefault->offsetExists('cashflow') === TRUE);

		$fw = new \FailWatch();

		$cAccounts = \accounting\AccountLib::getByIdsWithVatAccount($accounts);

		$cOperation = new \Collection();
		$properties = [
			'account', 'accountLabel',
			'description', 'amount', 'type', 'document', 'vatRate', 'comment',
		];
		if($isFromCashflow === FALSE) {
			$properties = array_merge($properties, ['date', 'cashflow', 'paymentDate', 'paymentMode']);
		}

		$eOperationDefault['thirdParty'] = NULL;

		if($isFromCashflow === TRUE) {
			$eOperationDefault->build(['paymentMode', 'paymentDate'], $input);
		}

		foreach($accounts as $index => $account) {

			$eOperation = clone $eOperationDefault;
			$eOperation['index'] = $index;

			$eOperation->buildIndex($properties, $input, $index);

			$eOperation['amount'] = abs($eOperation['amount']);

			// Date de la pièce justificative : date d'enregistrement
			if($eOperation['document'] !== NULL) {
				$eOperation['documentDate'] = new \Sql('NOW()');
			} else {
				$eOperation['documentDate'] = NULL;
			}

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
						]
						: $eOperation->getArrayCopy(),
				);

				$cOperation->append($eOperationVat);
			}
		}

		// Si toutes les écritures sont sur le même document, on utilise aussi celui-ci pour l'opération bancaire;
		$documents = $cOperation->getColumn('document');
		$uniqueDocuments = array_unique($documents);
		if(count($uniqueDocuments) === 1 and count($documents) === $cOperation->count()) {
			$document = first($uniqueDocuments);
		} else {
			$document = NULL;
		}

		// Ajout de la transaction sur la classe de compte bancaire 512
		if($isFromCashflow === TRUE) {

			$eOperationBank = \journal\OperationLib::createBankOperationFromCashflow(
				$eOperationDefault['cashflow'],
				$eOperationDefault,
				$document,
			);
			$cOperation->append($eOperationBank);

		}

		if($fw->ko()) {
			return new \Collection();
		}

		return $cOperation;
	}

	public static function createVatOperation(Operation $eOperationLinked, \accounting\Account $eAccount, float $vatValue, array $defaultValues): Operation {

		$values = [
			...$defaultValues,
			'account' => $eAccount['vatAccount']['id'] ?? NULL,
			'accountLabel' => \accounting\ClassLib::pad($eAccount['vatAccount']['class']),
			'document' => $eOperationLinked['document'],
			'thirdParty' => $eOperationLinked['thirdParty']['id'] ?? NULL,
			'type' => $eOperationLinked['type'],
			'paymentDate' => $eOperationLinked['paymentDate'],
			'paymentMode' => $eOperationLinked['paymentMode'],
			'amount' => abs($vatValue),
		];
		if($eOperationLinked['cashflow']->exists() === TRUE) {
			$values['cashflow'] = $eOperationLinked['cashflow']['id'];
		}

		$eOperationVat = new Operation();

		$fw = new \FailWatch();

		$eOperationVat->build(
			[
				'cashflow', 'date', 'account', 'accountLabel', 'description', 'document',
				'thirdParty', 'type', 'amount', 'operation',
				'paymentDate', 'paymentMode',
			],
			$values,
			new \Properties('create'),
		);
		$eOperationVat['operation'] = $eOperationLinked;
		if($eOperationLinked['document'] !== NULL) {
			$eOperationVat['documentDate'] = new \Sql('NOW()');
		}

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

		$properties = ['cashflow', 'updatedAt'];
		$eOperation = new Operation([
			'cashflow' => $eCashflow,
			'updatedAt' => Operation::model()->now(),
			'paymentDate' => $eCashflow['date'],
			'paymentMode' => new \bank\CashflowUi()::extractPaymentTypeFromCashflowDescription($eCashflow['memo']),
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
		OperationLib::createBankOperationFromCashflow($eCashflow, $eOperation);

		return $updated;
	}

	public static function createBankOperationFromCashflow(\bank\Cashflow $eCashflow, Operation $eOperation, ?string $document = NULL): Operation {

		$eAccountBank = \bank\AccountLib::getByClass(\Setting::get('accounting\bankAccountClass'));

		$eThirdParty = $eOperation['thirdParty'] ?? NULL;

		if($eCashflow['import']['account']['label'] !== NULL) {
			$label = $eCashflow['import']['account']['label'];
		} else {
			$label = \accounting\ClassLib::pad(\Setting::get('accounting\defaultBankAccountLabel'));
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
			'paymentDate' => $eCashflow['date'],
			'paymentMode'=> $eOperation['paymentMode'],
		];

		$eOperationBank = new Operation();

		$fw = new \FailWatch();

		$eOperationBank->build([
			'cashflow', 'date', 'account', 'accountLabel', 'description', 'document', 'thirdParty', 'type', 'amount',
			'operation', 'paymentDate', 'paymentMode',
		], $values, new \Properties('create'));

		if($document !== NULL) {
			$eOperationBank['documentDate'] = new \Sql('NOW()');
		}

		$fw->validate();

		\journal\Operation::model()->insert($eOperationBank);


		return $eOperationBank;

	}

	public static function createFromValues(array $values): void {

		$eOperation = new Operation();

		$fw = new \FailWatch();

		$eOperation->build(
			[
				'date',
				'cashflow', 'operation', 'asset', 'thirdParty',
				'account', 'accountLabel',
				'description', 'type', 'amount',
				'document', 'documentDate',
				'vatRate', 'vatAccount',
			],
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

	public static function setNumbers(\accounting\FinancialYear $eFinancialYear): void {

		$search = new \Search(['financialYear' => $eFinancialYear]);

		$cOperation = self::applySearch($search)
			->select('id')
			->sort(['date' => SORT_ASC, 'id' => SORT_ASC])
			->getCollection();

		$number = 0;
		foreach($cOperation as $eOperation) {

			$eOperation['number'] = ++$number;
			Operation::model()->select('number')->update($eOperation);

		}

	}
}
?>
