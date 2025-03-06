<?php
namespace bank;

class CashflowLib extends CashflowCrud {

	public static function getAll(\Search $search, bool $hasSort): \Collection {
		return Cashflow::model()
			->select(Cashflow::getSelection())
			->whereImport('=', $search->get('import'), if: $search->has('import'))
			->whereDate('LIKE', '%'.$search->get('date').'%', if: $search->get('date'))
			->whereDate('>=', $search->get('financialYear')['startDate'], if: $search->has('financialYear'))
			->whereDate('<=', $search->get('financialYear')['endDate'], if: $search->get('financialYear'))
			->whereFitid('LIKE', '%'.$search->get('fitid').'%', if: $search->get('fitid'))
			->whereMemo('LIKE', '%'.mb_strtolower($search->get('memo')).'%', if: $search->get('memo'))
			->sort($hasSort === TRUE ? $search->buildSort() : ['date' => SORT_DESC, 'fitid' => SORT_DESC])
			->getCollection();
	}

	public static function insertMultiple(array $cashflows, \company\Company $eCompany): array {

		$alreadyImported = [];
		$noFinancialYear = [];
		$imported = [];
		$invalidDate = [];
		$cFinancialYear = \accounting\FinancialYearLib::getAll();

		foreach($cashflows as $cashflow) {

			$isAlreadyImportedTransaction = (Cashflow::model()->whereFitid($cashflow['fitid'])->count() > 0);

			if($isAlreadyImportedTransaction === TRUE) {
				$alreadyImported[] = $cashflow['fitid'];
				continue;
			}

			$type = match($cashflow['type']) {
				'DEBIT' => CashflowElement::DEBIT,
				'CREDIT' => CashflowElement::CREDIT,
				default => $cashflow['amount'] > 0 ? CashflowElement::CREDIT : CashflowElement::DEBIT,
			};
			$date = substr($cashflow['date'], 0, 4).'-'.substr($cashflow['date'], 4, 2).'-'.substr($cashflow['date'], 6, 2);

			if(
				$eCompany['accountingType'] === \company\Company::CASH
				and \accounting\FinancialYearLib::isDateLinkedToFinancialYear($date, $cFinancialYear) === FALSE
			) {
				$noFinancialYear[] = $cashflow['fitid'];
				continue;
			}

			if(\util\DateLib::isValid($date) === FALSE) {
				$invalidDate[] = $cashflow['fitid'];
				continue;
			}

			$eCashflow = new Cashflow(
				array_merge(
					$cashflow,
					[
						'type' => $type,
						'date' => $date
					]
				)
			);

			Cashflow::model()->insert($eCashflow);
			$imported[] = $cashflow['fitid'];

		}

		return ['alreadyImported' => $alreadyImported, 'invalidDate' => $invalidDate, 'imported' => $imported, 'noFinancialYear' => $noFinancialYear];

	}

	public static function prepareAllocate(Cashflow $eCashflow, array $input): \Collection {

		$accounts = var_filter($input['account'] ?? [], 'array');
		$document = $input['cashflow']['document'] ?? null;

		$fw = new \FailWatch();

		if($accounts === []) {
			Cashflow::fail('accountsCheck');
			return new \Collection();
		}

		$cAccounts = \accounting\AccountLib::getByIdsWithVatAccount($accounts);

		$cOperation = new \Collection();

		foreach($accounts as $index => $account) {

			$eOperation = new \journal\Operation();
			$eOperation['index'] = $index;

			$eOperation->buildIndex(['account', 'accountLabel', 'description', 'amount', 'type', 'document', 'vatRate'], $input, $index);

			$eOperation['cashflow'] = $eCashflow;
			$eOperation['date'] = $eCashflow['date'];
			$eOperation['amount'] = abs($eOperation['amount']);

			$thirdParty = $input['thirdParty'][$index] ?? null;
			if($thirdParty !== null) {
				$eOperation['thirdParty'] = \journal\ThirdPartyLib::getByName($thirdParty);
			}

			// Ce type d'écriture a un compte de TVA correspondant
			$eAccount = $cAccounts[$account] ?? new Account();
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
					['date' => $eCashflow['date'], 'description' => $eCashflow['memo'], 'cashflow' => $eCashflow],
				);

				$cOperation->append($eOperationVat);
			}
		}

		// Ajout de la transaction sur la classe de compte bancaire 512
		$eOperationBank = \journal\OperationLib::createBankOperationFromCashflow($eCashflow, $document);
		$cOperation->append($eOperationBank);

		if($fw->ko()) {
			return new \Collection();
		}

		return $cOperation;
	}

	public static function attach(Cashflow $eCashflow, array $operations): void {

		Cashflow::model()->beginTransaction();

		if($eCashflow['status'] !== Cashflow::WAITING or \journal\OperationLib::countByCashflow($eCashflow) > 0) {
			throw new \NotExpectedAction('Cashflow #'.$eCashflow['id'].' already attached');
		}

		$updated = \journal\OperationLib::attachIdsToCashflow($eCashflow, $operations);
		if($updated !== count($operations)) {
			throw new \NotExpectedAction($updated.' operations updated instead of '.count($operations).' expected. Cashflow #'.$eCashflow['id'].' not attached.');
		}

		$properties = ['status', 'updatedAt'];
		$eCashflow['status'] = Cashflow::ALLOCATED;
		$eCashflow['updatedAt'] = Cashflow::model()->now();

		Cashflow::model()
			->select($properties)
			->whereId($eCashflow['id'])
			->update($eCashflow->extracts(['status', 'updatedAt']));

		Cashflow::model()->commit();
	}
}
?>
