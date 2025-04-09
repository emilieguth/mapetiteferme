<?php
namespace asset;

class AssetLib extends \asset\AssetCrud {

	public static function getPropertiesCreate(): array {
		return ['value', 'type', 'description', 'mode', 'acquisitionDate', 'startDate', 'duration'];
	}
	public static function getPropertiesUpdate(): array {
		return ['value', 'type', 'description', 'mode', 'acquisitionDate', 'startDate', 'duration', 'status'];
	}

	public static function isTangibleAsset(string $account): bool {

		foreach(\Setting::get('accounting\tangibleAssetsClasses') as $tangibleAssetsClass) {
			if(mb_substr($account, 0, strlen($tangibleAssetsClass)) === $tangibleAssetsClass) {
				return TRUE;
			}
		}

		return FALSE;

	}

	public static function isIntangibleAsset(string $account): bool {

		return mb_substr($account, 0, strlen(\Setting::get('accounting\intangibleAssetsClass'))) === \Setting::get('accounting\intangibleAssetsClass');

	}

	public static function depreciationClassByAssetClass(string $class): string {

		return mb_substr($class, 0, 1).'8'.mb_substr($class, 1);

	}

	public static function isDepreciationClass(string $class): bool {

		return (mb_substr($class, 1, 1) === '8');

	}

	public static function getAcquisitions(\accounting\FinancialYear $eFinancialYear, string $type): \Collection {

		return Asset::model()
			->select(Asset::getSelection())
			->whereAcquisitionDate('>=', $eFinancialYear['startDate'])
			->whereAcquisitionDate('<=', $eFinancialYear['endDate'])
			->whereAccountLabel('LIKE', match($type) {
				'asset' => \Setting::get('accounting\assetClass').'%',
				'subvention' => \Setting::get('accounting\subventionAssetClass').'%',
			})
			->sort(['accountLabel' => SORT_ASC, 'startDate' => SORT_ASC])
			->getCollection();

	}

	public static function getSubventionsByFinancialYear(\accounting\FinancialYear $eFinancialYear): \Collection {

		return Asset::model()
      ->select(
        Asset::getSelection()
        + ['account' => \accounting\Account::getSelection()]
      )
      ->whereStartDate('<=', $eFinancialYear['endDate'])
			->whereAccountLabel('LIKE', \Setting::get('accounting\subventionAssetClass').'%')
      ->sort(['accountLabel' => SORT_ASC, 'startDate' => SORT_ASC])
      ->getCollection();
	}

	public static function getAssetsByFinancialYear(\accounting\FinancialYear $eFinancialYear): \Collection {

		return Asset::model()
			->select(
				Asset::getSelection()
				+ ['account' => \accounting\Account::getSelection()]
			)
			->whereStartDate('<=', $eFinancialYear['endDate'])
			->whereAccountLabel('LIKE', \Setting::get('accounting\assetClass').'%')
			->sort(['accountLabel' => SORT_ASC, 'startDate' => SORT_ASC])
			->getCollection();

	}

	public static function prepareAsset(\journal\Operation $eOperation, array $assetData, int $index): ?Asset {

		$eOperation->expects(['accountLabel']);

		if(
			(int)mb_substr($eOperation['accountLabel'], 0, 1) !== \Setting::get('accounting\assetClass')
			and
			(int)mb_substr($eOperation['accountLabel'], 0, 2) !== \Setting::get('accounting\subventionAssetClass')
		) {
			return NULL;
		}

		$eAsset = new Asset();
		$fw = new \FailWatch();

		$properties = new \Properties('create');
		$properties->setWrapper(function(string $property) use($index) {
			return 'asset['.$index.']['.$property.']';
		});
		$eAsset->build(['value', 'type', 'acquisitionDate', 'startDate', 'duration'], $assetData, $properties);
		if($fw->ko() === TRUE) {
			return NULL;
		}

		$eAsset['account'] = $eOperation['account'];
		$eAsset['accountLabel'] = $eOperation['accountLabel'];
		$eAsset['description'] = $eOperation['description'];
		$eAsset['endDate'] = date('Y-m-d', strtotime($eAsset['startDate'].' + '.$eAsset['duration'].' year - 1 day'));

		Asset::model()->insert($eAsset);

		return $eAsset;

	}

	public static function deleteByIds(array $ids): void {

		Asset::model()
			->whereId('IN', $ids)
			->delete();

	}

	/**
	 * @throws \ModuleException
	 */
	public static function getWithDepreciationsById(int $id): Asset {

		$eAsset = new Asset();
		Asset::model()
			->select(
				Asset::getSelection()
				+ [
					'cDepreciation' => Depreciation::model()
						->select(['amount', 'date', 'type', 'financialYear' => \accounting\FinancialYear::getSelection()])
						->sort(['date' => SORT_ASC])
						->delegateCollection('asset'),
				]
			)
			->whereId($id)
			->get($eAsset);

		return $eAsset;

	}

	/**
	 * Amortit l'immobilisation sur l'exercice comptable dépendant de sa date d'acquisition / date de fin d'amortissement
	 * Crée une entrée "Dotation aux amortissements" (classe 6) et une entrée "Amortissement" (classe 2)
	 *
	 * @param Asset $eAsset
	 * @return void
	 */
	public static function depreciate(\accounting\FinancialYear $eFinancialYear, Asset $eAsset, ?string $endDate): void {

		if($endDate === NULL) {
			$endDate = $eFinancialYear['endDate'];
		}

		$depreciationValue = DepreciationLib::calculateDepreciationByEndDate($eFinancialYear['startDate'], $endDate, $eAsset);

		// Dotation aux amortissements
		if(self::isIntangibleAsset($eAsset['account'])) {
			$depreciationChargeClass = \Setting::get('accounting\intangibleAssetsDepreciationChargeClass');
		} else {
			$depreciationChargeClass = \Setting::get('accounting\tangibleAssetsDepreciationChargeClass');
		}

		$eAccountDepreciationCharge = \accounting\AccountLib::getByClass($depreciationChargeClass);
		$values = [
			'account' => $eAccountDepreciationCharge['id'],
			'accountLabel' => \accounting\AccountLib::padClass($eAccountDepreciationCharge['class']),
			'date' => $endDate,
			'description' => $eAccountDepreciationCharge['description'],
			'amount' => $depreciationValue,
			'type' => \journal\OperationElement::DEBIT,
			'asset' => $eAsset,
		];
		\journal\OperationLib::createFromValues($values);

		// Amortissement
		$values = self::getDepreciationOperationValues($eAsset, $endDate, $depreciationValue);
		\journal\OperationLib::createFromValues($values);

		// Créer une entrée dans la table Depreciation
		$eDepreciation = new Depreciation([
			'asset' => $eAsset,
			'amount' => $depreciationValue,
			'type' => DepreciationElement::ECONOMIC,
			'date' => $endDate,
			'financialYear' => $eFinancialYear,
		]);

		Depreciation::model()->insert($eDepreciation);

	}

	/**
	 * Renvoie les valeurs d'une opération d'amortissement pour l'immobilisation et le montant donnés
	 *
	 * @param Asset $eAsset
	 * @param string $date
	 * @param float $value
	 *
	 * @return array
	 */
	private static function getDepreciationOperationValues(Asset $eAsset, string $date, float $amount): array {

		$depreciationClass = self::depreciationClassByAssetClass($eAsset['accountLabel']);
		$eAccountDepreciation = \accounting\AccountLib::getByClass(trim($depreciationClass, '0'));

		return [
			'account' => $eAccountDepreciation['id'],
			'accountLabel' => \accounting\AccountLib::padClass($eAccountDepreciation['class']),
			'date' => $date,
			'description' => $eAccountDepreciation['description'],
			'amount' => $amount,
			'type' => \journal\OperationElement::CREDIT,
			'asset' => $eAsset,
		];

	}

	public static function dispose(Asset $eAsset, array $input): void {

		$fw = new \FailWatch();

		$eAsset->build(['status'], $input);

		if($eAsset['status'] === AssetElement::SOLD) {

			if(($input['amount'] ?? NULL) === NULL or strlen($input['amount']) === 0) {
				Asset::fail('amount.check');
			}

			$amount = cast($input['amount'], 'float');

			$createReceivable = cast($input['createReceivable'] ?? FALSE, 'bool');

		} else {

			$amount = 0;

		}

		$date = $input['date'] ?? NULL;
		if(strlen($date) === 0 or \util\DateLib::isValid($date) === FALSE) {
			Asset::fail('date.check');
		}

		$eFinancialYear = \accounting\FinancialYearLib::getOpenFinancialYearByDate($date);
		if($eFinancialYear->exists() === FALSE) {
			throw new \NotExpectedAction('Open FinancialYear has not been found according to date "'.$date.'"');
		}

		$fw->validate();

		Asset::model()->beginTransaction();

		$eAsset['updatedAt'] = new \Sql('NOW()');
		Asset::model()
			->select(['status', 'updatedAt'])
			->update($eAsset);

		// Constater l'amortissement du début de l'exercice comptable jusqu'à la date de cession
		AssetLib::depreciate($eFinancialYear, $eAsset, $date);

		// Re-récupérer l'actif pour sommer les amortissements cumulés
		Asset::model()
			->select(Asset::getSelection() + [
					'cDepreciation' => Depreciation::model()
						->select(['amount', 'date', 'type', 'financialYear' => \accounting\FinancialYear::getSelection()])
						->sort(['date' => SORT_ASC])
						->delegateCollection('asset'),
					'account' => \accounting\Account::getSelection(),
				])
			->whereId($eAsset['id'])
			->get($eAsset);

		// Calcul de la VNC. Attention, pour certaines immos on retient la valeur vénale et non la valeur net pour le calcul des plus values. TODO
		// Valeur d'entrée
		$initialValue = $eAsset['value'];

		// Amortissements
		$accumulatedDepreciationsValue = $eAsset['cDepreciation']->sum('amount');
		$netAccountingValue = $initialValue - $accumulatedDepreciationsValue;

		// Sortir l'actif (immo : 2x)
		$values = [
			'account' => $eAsset['account']['id'],
			'accountLabel' => \accounting\AccountLib::padClass($eAsset['accountLabel']),
			'date' => $date,
			'description' => $eAsset['description'],
			'amount' => $eAsset['value'],
			'type' => \journal\OperationElement::CREDIT,
			'asset' => $eAsset,
		];
		\journal\OperationLib::createFromValues($values);

		// Sortir l'actif (amort. : 28x)
		$values = self::getDepreciationOperationValues($eAsset, $date, $accumulatedDepreciationsValue);
		$values['type'] = \journal\OperationElement::DEBIT;
		\journal\OperationLib::createFromValues($values);

		// 1/ Cas d'une vente :
		if($eAsset['status'] === AssetElement::SOLD) {

			// Sortir l'actif (charge exc. de la VNC 675). En cas de mise au rebut, la VNC est estimée à 0.
			$eAccountDisposal = \accounting\AccountLib::getByClass(\Setting::get('accounting\disposalAssetValueClass'));
			$values = [
				'account' => $eAccountDisposal['id'],
				'accountLabel' => \accounting\AccountLib::padClass($eAccountDisposal['class']),
				'date' => $date,
				'description' => $eAccountDisposal['description'],
				'amount' => $netAccountingValue,
				'type' => \journal\OperationElement::DEBIT,
				'asset' => $eAsset,
			];
			\journal\OperationLib::createFromValues($values);

			// b. Création de l'écriture de la vente 775
			$eAccountProduct = \accounting\AccountLib::getByClass(\Setting::get('accounting\productAssetValueClass'));
			$values = [
				'account' => $eAccountProduct['id'],
				'accountLabel' => \accounting\AccountLib::padClass($eAccountProduct['class']),
				'date' => $date,
				'description' => $eAccountProduct['description'],
				'amount' => $amount,
				'type' => \journal\OperationElement::CREDIT,
				'asset' => $eAsset,
			];
			\journal\OperationLib::createFromValues($values);

			// c. Créer l'écriture débit compte banque (512) OU le débit créance sur cession (462)
			if($createReceivable === TRUE) {

				$receivablesOnAssetDisposalClass = \Setting::get('accounting\receivablesOnAssetDisposalClass');
				$debitAccountLabel = \accounting\AccountLib::padClass($receivablesOnAssetDisposalClass);
				$eAccountDebit = \accounting\AccountLib::getByClass($receivablesOnAssetDisposalClass);

			} else {

				$bankClass = \Setting::get('accounting\bankAccountClass');
				$debitAccountLabel = \accounting\AccountLib::padClass($bankClass);
				$eAccountDebit = \accounting\AccountLib::getByClass($bankClass);

			}

			$values = [
				'date' => $date,
				'account' => $eAccountDebit['id'],
				'accountLabel' => $debitAccountLabel,
				'description' => $eAsset['description'],
				'type' => \journal\OperationElement::DEBIT,
				'amount' => $amount,
			];
			\journal\OperationLib::createFromValues($values);

			// TODO : voir s'il faut aussi collecter de la TVA ?

		// 2/ Case d'une mise au rebut : création d'un amortissement exceptionnel
		} else {

			// débiter le compte 6871 (Dotations aux amortissements exceptionnels des immobilisations)
			$eAccountDepreciationCharge = \accounting\AccountLib::getByClass(\Setting::get('accounting\exceptionalDepreciationChargeClass'));
			$values = [
				'account' => $eAccountDepreciationCharge['id'],
				'accountLabel' => \accounting\AccountLib::padClass($eAccountDepreciationCharge['class']),
				'date' => $date,
				'description' => $eAccountDepreciationCharge['description'],
				'amount' => $netAccountingValue,
				'type' => \journal\OperationElement::DEBIT,
				'asset' => $eAsset,
			];
			\journal\OperationLib::createFromValues($values);

			// Créditer le compte 28xx (amortissement)
			$depreciationClass = AssetLib::depreciationClassByAssetClass($eAsset['account']['class']);
			$eAccountDepreciation = \accounting\AccountLib::getByClass($depreciationClass);
			$values = [
				'account' => $eAccountDepreciation['id'],
				'accountLabel' => AssetLib::depreciationClassByAssetClass($eAsset['accountLabel']),
				'date' => $date,
				'description' => $eAccountDepreciation['description'],
				'amount' => $netAccountingValue,
				'type' => \journal\OperationElement::CREDIT,
				'asset' => $eAsset,
			];
			\journal\OperationLib::createFromValues($values);

		}

		Asset::model()->commit();

	}

}
?>
