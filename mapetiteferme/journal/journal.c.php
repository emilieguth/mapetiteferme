<?php
Privilege::register('journal', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('journal', [
	'codes' => [
		\journal\OperationElement::BANK => '21',
		\journal\OperationElement::CASH => '11',
		\journal\OperationElement::OPENING => '2',
		\journal\OperationElement::STOCK_START => '30',
		\journal\OperationElement::STOCK_END => '31',
		\journal\OperationElement::MISC => '90',
	],
]);
?>
