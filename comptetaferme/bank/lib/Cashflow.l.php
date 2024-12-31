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

			if ($isAlreadyImportedTransaction === true) {
				$alreadyImported[] = $cashflow['fitid'];
				continue;
			}

			$type = match($cashflow['type']) {
				'DEBIT' => CashflowElement::DEBIT,
				'CREDIT' => CashflowElement::CREDIT,
				default => $cashflow['amount'] > 0 ? CashflowElement::CREDIT : CashflowElement::DEBIT,
			};
			$date = substr($cashflow['date'], 0, 4).'-'.substr($cashflow['date'], 4, 2).'-'.substr($cashflow['date'], 6, 2);

			if (\util\DateLib::isValid($date) === FALSE) {
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

	public static function prepareAllocate(Cashflow $eCashflow, array $input): \Collection {

		$accounts = var_filter($input['account'] ?? [], 'array');

		$fw = new \FailWatch();

		if($accounts === []) {
			Cashflow::fail('accountsCheck');
			return new \Collection();
		}

		$cOperation = new \Collection();

		foreach($accounts as $index => $account) {

			$eOperation = new \journal\Operation();
			$eOperation['index'] = $index;

			$eOperation->buildIndex(['account', 'accountLabel', 'description', 'amount', 'type', 'lettering'], $input, $index);
			$eOperation['cashflow'] = $eCashflow;
			$eOperation['date'] = $eCashflow['date'];
			$cOperation->append($eOperation);

		}

		// Ajout de la transaction sur le compte 512 (compte 5121)
		$eOperationBank = new \journal\Operation();
		$eAccountBank = new Account();
		$eOperationBank['cashflow'] = $eCashflow;
		$eOperationBank['date'] = $eCashflow['date'];
		\accounting\Account::model()
			->select(\accounting\Account::getSelection())
			->whereClass('=', \Setting::get('accounting\bankAccountClass'))
			->get($eAccountBank);
		$eOperationBank['account'] = $eAccountBank;
		$eOperationBank['accountLabel'] = \Setting::get('accounting\bankAccountLabel');
		$eOperationBank['description'] = $eCashflow['memo'];
		$eOperationBank['type'] = $eCashflow['type'];
		$eOperationBank['amount'] = $eCashflow['amount'];
		$cOperation->append($eOperationBank);


		if($fw->ko()) {
			return new \Collection();
		}

		return $cOperation;
	}

	public static function getByThirdParty(string $thirdParty): \Collection {
		return Cashflow::model()
			->whereThirdParty('LIKE', '%'.$thirdParty.'%')
			->sort(['thirdParty' => SORT_ASC])
			->getCollection(NULL, NULL, 'thirdParty');
	}

}
?>