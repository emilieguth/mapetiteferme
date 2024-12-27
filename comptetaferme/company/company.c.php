<?php
Privilege::register('company', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('company', [
	'seasonBegin' => '01-01',
	'categoriesLimit' => 5,
	'newSeason' => 10
]);
?>