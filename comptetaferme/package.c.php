<?php
Package::setList([
	'main' => 'comptetaferme',
	'core' => 'framework',
	'dev' => 'framework',
	'editor' => 'framework',
	'example' => 'framework',
	'language' => 'framework',
	'session' => 'framework',
	'storage' => 'framework',
	'user' => 'framework',
	'util' => 'framework',
	'company' => 'comptetaferme',
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
	],
	'ui' => [
		'user' => [
			'emailSignUp' => ['main'],
		],
	],
]);
?>