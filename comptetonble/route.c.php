<?php
Route::register([
	'DELETE' => [
	],
	'GET' => [
		'/journal/analyze/bank' => [
			'request' => 'journal/analyze',
			'priority' => 5,
			'route' => ['journal', 'analyze', 'bank'],
		],
		'/journal/analyze/bank/{financialYear}' => [
			'request' => 'journal/analyze',
			'priority' => 5,
			'route' => ['journal', 'analyze', 'bank', '{financialYear}'],
		],
		'/journal/analyze/charges' => [
			'request' => 'journal/analyze',
			'priority' => 5,
			'route' => ['journal', 'analyze', 'charges'],
		],
		'/journal/analyze/charges/{financialYear}' => [
			'request' => 'journal/analyze',
			'priority' => 5,
			'route' => ['journal', 'analyze', 'charges', '{financialYear}'],
		],
		'/journal/analyze/result' => [
			'request' => 'journal/analyze',
			'priority' => 5,
			'route' => ['journal', 'analyze', 'result'],
		],
		'/journal/analyze/result/{financialYear}' => [
			'request' => 'journal/analyze',
			'priority' => 5,
			'route' => ['journal', 'analyze', 'result', '{financialYear}'],
		],
		'/minify/{version}/{filename}' => [
			'request' => 'dev/minify',
			'priority' => 5,
			'route' => ['minify', '{version}', '{filename}'],
		],
		'/presentation/engagements' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'engagements'],
		],
		'/presentation/entreprise' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'entreprise'],
		],
		'/presentation/faq' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'faq'],
		],
		'/presentation/invitation' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'invitation'],
		],
		'/presentation/legal' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'legal'],
		],
		'/presentation/service' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'service'],
		],
		'/robots.txt' => [
			'request' => 'main/seo',
			'priority' => 5,
			'route' => ['robots.txt'],
		],
		'/sitemap.xml' => [
			'request' => 'main/sitemap',
			'priority' => 5,
			'route' => ['sitemap.xml'],
		],
	],
	'HEAD' => [
	],
	'POST' => [
		'/@module/bank/Account/doQuick' => [
			'request' => 'bank/account',
			'priority' => 5,
			'route' => ['@module', 'bank', 'Account', 'doQuick'],
		],
		'/@module/bank/Account/quick' => [
			'request' => 'bank/account',
			'priority' => 5,
			'route' => ['@module', 'bank', 'Account', 'quick'],
		],
		'/@module/journal/Operation/doQuick' => [
			'request' => 'journal/operation',
			'priority' => 5,
			'route' => ['@module', 'journal', 'Operation', 'doQuick'],
		],
		'/@module/journal/Operation/quick' => [
			'request' => 'journal/operation',
			'priority' => 5,
			'route' => ['@module', 'journal', 'Operation', 'quick'],
		],
	],
	'PUT' => [
	],
]);
?>