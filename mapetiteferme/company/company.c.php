<?php
Privilege::register('company', [
	'admin' => FALSE,
	'access' => FALSE,
]);

Setting::register('company', [
	'subscriptionPrices' => [
		\company\SubscriptionElement::ACCOUNTING => 100,
		\company\SubscriptionElement::PRODUCTION => 150,
		\company\SubscriptionElement::SALES => 150,
	],
	'subscriptionPackPrice' => 300,
]);
?>
