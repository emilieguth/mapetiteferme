<?php
Privilege::register('accounting', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('accounting', [
	'chargeAccountClass' => 6,
	'productAccountClass' => 7,
	'bankAccountClass' => '512',
	'bankAccountLabel' => '5121',
]);
?>