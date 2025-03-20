<?php
Privilege::register('accounting', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('accounting', [
	'assetClass' => 2,
	'thirdAccountGeneralClass' => 4,
	'vatClass' => 445,
	'bankAccountGeneralClass' => 5,
	'chargeAccountClass' => 6,
	'productAccountClass' => 7,

	'bankAccountClass' => '512',
	'cashAccountClass' => '5310', // caisse
	'defaultBankAccountLabel' => '5121',

	'shippingChargeAccountClass' => '624',

	'summaryAccountingBalanceCategories' => \accounting\AccountUi::getSummaryBalanceCategories(),
]);
?>
