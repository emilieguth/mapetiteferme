<?php
Package::setList([
	'main' => 'mapetiteferme',
	'core' => 'framework',
	'dev' => 'framework',
	'editor' => 'framework',
	'example' => 'framework',
	'language' => 'framework',
	'session' => 'framework',
	'storage' => 'framework',
	'user' => 'framework',
	'util' => 'framework',
	'accounting' => 'mapetiteferme',
	'analyze' => 'mapetiteferme',
	'asset' => 'mapetiteferme',
	'bank' => 'mapetiteferme',
	'company' => 'mapetiteferme',
	'journal' => 'mapetiteferme',
	'mail' => 'mapetiteferme',
	'media' => 'mapetiteferme',
	'overview' => 'mapetiteferme',
	'pdf' => 'mapetiteferme',
]);

Package::setObservers([
	'lib' => [
		'user' => [
			'sendVerifyEmail' => ['main'],
			'signUpCreate' => ['main'],
			'close' => ['main'],
			'logIn' => ['session', 'company'],
			'logOut' => ['session'],
			'formLog' => ['company'],
			'formSignUp' => ['company'],
		],
		'lime' => [
			'loadConf' => ['media'],
		],
	],
	'ui' => [
		'user' => [
			'emailSignUp' => ['main'],
		],
	],
]);
?>