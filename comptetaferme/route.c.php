<?php
Route::register([
	'DELETE' => [
	],
	'GET' => [
		'/company/{id}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['company', '{id}'],
		],
		'/company/{id}/clients' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['company', '{id}', 'clients'],
		],
		'/company/{id}/configuration' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['company', '{id}', 'configuration'],
		],
		'/company/{id}/finances' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['company', '{id}', 'finances'],
		],
		'/company/{id}/fournisseurs' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['company', '{id}', 'fournisseurs'],
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