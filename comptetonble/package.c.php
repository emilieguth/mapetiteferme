<?php
Package::setList([
	'main' => 'comptetonble',
	'core' => 'framework',
	'dev' => 'framework',
	'editor' => 'framework',
	'example' => 'framework',
	'language' => 'framework',
	'session' => 'framework',
	'storage' => 'framework',
	'user' => 'framework',
	'util' => 'framework',
	'accounting' => 'comptetonble',
	'bank' => 'comptetonble',
	'company' => 'comptetonble',
	'dropbox' => 'comptetonble',
	'journal' => 'comptetonble',
	'mail' => 'comptetonble',
	'media' => 'comptetonble',
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