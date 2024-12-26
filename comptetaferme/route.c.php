<?php
Route::register([
	'DELETE' => [
	],
	'GET' => [
		'/minify/{version}/{filename}' => [
			'request' => 'dev/minify',
			'priority' => 5,
			'route' => ['minify', '{version}', '{filename}'],
		],
		'/presentation/faq' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'faq'],
		],
		'/presentation/formations' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'formations'],
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
		'/presentation/producteur' => [
			'request' => 'main/index',
			'priority' => 5,
			'route' => ['presentation', 'producteur'],
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