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
]);

Package::setObservers([
	'lib' => [
		'user' => [
			'sendVerifyEmail' => ['main'],
			'signUpCreate' => ['main'],
			'close' => ['main'],
			'logIn' => ['session'],
			'logOut' => ['session'],
		],
	],
	'ui' => [
		'user' => [
			'emailSignUp' => ['main'],
		],
	],
]);
?>