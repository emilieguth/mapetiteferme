<?php
Privilege::register('accounting', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('accounting', [
	'assetClass' => 2,
	'thirdAccountGeneralClass' => 4,
	'bankAccountGeneralClass' => 5,
	'chargeAccountClass' => 6,
	'productAccountClass' => 7,

	'bankAccountClass' => '512',
	'defaultBankAccountLabel' => '5121',

	'shippingProductAccountClass' => '6242', // Produits
	'shippingChargeAccountClass' => '6241', // Achats
]);
?>
