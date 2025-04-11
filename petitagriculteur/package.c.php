<?php
Package::setList([
	'main' => 'petitagriculteur',
	'core' => 'framework',
	'dev' => 'framework',
	'editor' => 'framework',
	'example' => 'framework',
	'language' => 'framework',
	'session' => 'framework',
	'storage' => 'framework',
	'user' => 'framework',
	'util' => 'framework',
	'accounting' => 'petitagriculteur',
	'analyze' => 'petitagriculteur',
	'asset' => 'petitagriculteur',
	'bank' => 'petitagriculteur',
	'company' => 'petitagriculteur',
	'journal' => 'petitagriculteur',
	'mail' => 'petitagriculteur',
	'media' => 'petitagriculteur',
	'overview' => 'petitagriculteur',
	'pdf' => 'petitagriculteur',
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