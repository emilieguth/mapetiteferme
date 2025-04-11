<?php
Privilege::register('journal', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('journal', [
	'codes' => [
		\journal\OperationElement::BANK => 'JBa',
		\journal\OperationElement::CASH => 'JCa',
		\journal\OperationElement::OPENING => 'JOp',
		\journal\OperationElement::STOCK_START => 'JSs',
		\journal\OperationElement::STOCK_END => 'JSe',
		\journal\OperationElement::MISC => 'JDi',
	],
]);
?>
