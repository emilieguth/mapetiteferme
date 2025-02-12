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

	public static function insertMultiple(array $cashflows): array {

		$alreadyImported = [];
		$imported = [];
		$invalidDate = [];

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

		return ['alreadyImported' => $alreadyImported, 'invalidDate' => $invalidDate, 'imported' => $imported];

	}

	public static function prepareAllocate(Cashflow $eCashflow, array $input): array {

		$accounts = var_filter($input['account'] ?? [], 'array');
		$document = $input['cashflow']['document'] ?? null;

		$fw = new \FailWatch();

		if($accounts === []) {
			Cashflow::fail('accountsCheck');
			return [new \Collection(), new \Collection()];
		}

		$cAccounts = \accounting\AccountLib::getByIdsWithVatAccount($accounts);

		$cOperation = new \Collection();
		$cThirdParty = new \Collection();

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
			if($eAccount['vatAccount']->exists() === TRUE) {
				$eOperation['vatAccount'] = $cAccounts[$account]['vatAccount'];

				// Ajout de l'entrée de compte de TVA correspondante
				$eOperationTva = new \journal\Operation();
				$eOperationTva['cashflow'] = $eCashflow;
				$eOperationTva['date'] = $eCashflow['date'];
				$eOperationTva['account'] = $eAccount['vatAccount'];
				$eOperationTva['description'] = $eCashflow['memo'];
				$eOperationTva['document'] = $document;
				$eOperationTva['type'] = match(mb_substr($eAccount['class'], 0, 1)) {
					'7' => \journal\OperationElement::CREDIT,
					'2' => \journal\OperationElement::DEBIT,
					'6' => \journal\OperationElement::DEBIT,
					default => NULL,
				};
				$eOperationTva['amount'] = round($eOperation['amount'] * $eOperation['vatRate'] / 100, 2);
				$cOperation->append($eOperationTva);
			} else if($eOperation['vatRate'] !== 0.0) {
				\Fail::log('Cashflow::allocate.tvaInconsistency');
			}

			$cOperation->append($eOperation);

			// Vérification du tiers et affectation
			if(isset($input['thirdParty'][$index]) === TRUE) {
				$thirdParty = $input['thirdParty'][$index];
				$eThirdParty = \journal\ThirdPartyLib::getByName($thirdParty);
				if(in_array($eOperation['account']['id'], $eThirdParty['accounts']) === FALSE) {
					$eThirdParty['accounts'][] = $eOperation['account']['id'];
					$cThirdParty->append($eThirdParty);
				}
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
		$cOperation->append($eOperationBank);


		if($fw->ko()) {
			return [new \Collection(), new \Collection()];
		}

		return [$cOperation, $cThirdParty];
	}

}
?>