<?php
Privilege::register('accounting', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('accounting', [
	'assetClass' => 2,
	'subventionAssetClass' => 13,

	'thirdAccountGeneralClass' => 4,
	'vatClass' => 445,
	'bankAccountGeneralClass' => 5,
	'chargeAccountClass' => 6,
	'productAccountClass' => 7,

	'bankAccountClass' => '512',
	'cashAccountClass' => '5310', // caisse
	'defaultBankAccountLabel' => '5121',

	'nonDepreciableAssetClass' => '2125',

	'shippingChargeAccountClass' => '624',

	'disposalAssetValueClass' => '675', // Valeur comptable des éléments d'actifs cédés
	'productAssetValueClass' => '775', // Produits des cessions d'éléments d'actif

	'intangibleAssetsClass' => '20', // Immobilisations incorporelles
	'tangibleAssetsClasses' => ['21', '24'], // Immobilisations corporelles

	'intangibleAssetsDepreciationChargeClass' => '68111', // Dotation aux amortissements sur immos incorporelles
	'tangibleAssetsDepreciationChargeClass' => '68112', // Dotation aux amortissements sur immos corporelles
	'exceptionalDepreciationChargeClass' => '6871', // Dotation aux amortissements exceptionnels

	'receivablesOnAssetDisposalClass' => '462', // Créances sur cessions d'immobilisations

	'summaryAccountingBalanceCategories' => accounting\AccountUi::getSummaryBalanceCategories(),
	'balanceAssetCategories' => accounting\AccountUi::getAssetBalanceCategories(),
	'balanceLiabilityCategories' => accounting\AccountUi::getLiabilityBalanceCategories(),

	'vatBuyVatClasses' => ['44562', '44566'],
	'vatBuyClassPrefix' => '4456',
	'vatSellVatClasses' => ['44571'],
	'vatSellClassPrefix' => '4457',

]);
?>
