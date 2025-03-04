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

				$eOperationVat = new \journal\Operation();
				$eOperationVat['cashflow'] = $eCashflow;
				$eOperationVat['date'] = $eCashflow['date'];
				$eOperationVat['account'] = $eAccount['vatAccount'];
				$eOperationVat['description'] = $eCashflow['memo'];
				$eOperationVat['document'] = $document;
				$eOperationVat['type'] = match(mb_substr($eAccount['class'], 0, 1)) {
					'7' => \journal\OperationElement::CREDIT,
					'2' => \journal\OperationElement::DEBIT,
					'6' => \journal\OperationElement::DEBIT,
					default => NULL,
				};
				$eOperationVat['amount'] = abs($input['vatValue'][$index]);
				$eOperationVat['operation'] = $eOperation;

				\journal\Operation::model()->insert($eOperationVat);
				$cOperation->append($eOperationVat);
			}
		}

		// Ajout de la transaction sur la classe de compte bancaire 512
		$eOperationBank = new \journal\Operation();
		$eAccountBank = new Account();
		$eOperationBank['cashflow'] = $eCashflow;
		$eOperationBank['date'] = $eCashflow['date'];
		\accounting\Account::model()
			->select(\accounting\Account::getSelection())
			->whereClass('=', \Setting::get('accounting\bankAccountClass'))
			->get($eAccountBank);
		$eOperationBank['account'] = $eAccountBank;
		$eOperationBank['accountLabel'] = $eCashflow['import']['account']['label'] ?? \Setting::get('accounting\defaultBankAccountLabel');
		$eOperationBank['description'] = $eCashflow['memo'];
		$eOperationBank['document'] = $document;
		$eOperationBank['type'] = match($eCashflow['type']) {
			CashflowElement::CREDIT => \journal\Operation::DEBIT,
			CashflowElement::DEBIT => \journal\Operation::CREDIT,
		};
		$eOperationBank['amount'] = abs($eCashflow['amount']);

		\journal\Operation::model()->insert($eOperationBank);
		$cOperation->append($eOperation);

		if($fw->ko()) {
			return new \Collection();
		}

		return $cOperation;
	}

}
?>
