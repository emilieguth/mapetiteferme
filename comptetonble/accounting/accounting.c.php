<?php
Privilege::register('accounting', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('accounting', [
	'bankAccountClass' => '512',
	'bankAccountLabel' => '5121',
]);
?>