<?php
Privilege::register('accounting', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('accounting', [
	'chargeAccountClass' => 6,
	'bankAccountClass' => '512',
	'bankAccountLabel' => '5121',
]);
?>