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
	],
	'PUT' => [
	],
]);
?>