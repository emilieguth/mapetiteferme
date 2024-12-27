<?php
Route::register([
	'DELETE' => [
	],
	'GET' => [
		'/company/{company}/company:update' => [
			'request' => 'company/company',
			'priority' => 5,
			'route' => ['company', '{company}', 'company:update'],
		],
		'/company/{company}/employee:update' => [
			'request' => 'company/employee',
			'priority' => 5,
			'route' => ['company', '{company}', 'employee:update'],
		],
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
		'/company/{id}/employee:manage' => [
			'request' => 'company/employee',
			'priority' => 5,
			'route' => ['company', '{id}', 'employee:manage'],
		],
		'/company/{id}/employee:show' => [
			'request' => 'company/employee',
			'priority' => 5,
			'route' => ['company', '{id}', 'employee:show'],
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
		'/company/{id}/invite:create' => [
			'request' => 'company/invite',
			'priority' => 5,
			'route' => ['company', '{id}', 'invite:create'],
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