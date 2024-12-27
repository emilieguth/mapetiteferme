<?php
Route::register([
	'DELETE' => [
	],
	'GET' => [
		'/ferme/{id}/analyses/cultures' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'cultures'],
		],
		'/ferme/{id}/analyses/cultures/{season}/{category}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'cultures', '{season}', '{category}'],
		],
		'/ferme/{id}/analyses/planning' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'planning'],
		],
		'/ferme/{id}/analyses/planning/{year}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'planning', '{year}'],
		],
		'/ferme/{id}/analyses/planning/{year}/{category}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'planning', '{year}', '{category}'],
		],
		'/ferme/{id}/analyses/rapports' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'rapports'],
		],
		'/ferme/{id}/analyses/rapports/{season}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'rapports', '{season}'],
		],
		'/ferme/{id}/analyses/ventes' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'ventes'],
		],
		'/ferme/{id}/analyses/ventes/{year}/{category}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'ventes', '{year}', '{category}'],
		],
		'/ferme/{id}/analyses/ventes/{year}/{category}/compare/{compare}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'analyses', 'ventes', '{year}', '{category}', 'compare', '{compare}'],
		],
		'/ferme/{id}/assolement' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'assolement'],
		],
		'/ferme/{id}/assolement/{season}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'assolement', '{season}'],
		],
		'/ferme/{id}/boutiques' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'boutiques'],
		],
		'/ferme/{id}/carte' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'carte'],
		],
		'/ferme/{id}/carte/{season}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'carte', '{season}'],
		],
		'/ferme/{id}/catalogues' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'catalogues'],
		],
		'/ferme/{id}/clients' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'clients'],
		],
		'/ferme/{id}/configuration' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'configuration'],
		],
		'/ferme/{id}/etiquettes' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'etiquettes'],
		],
		'/ferme/{id}/factures' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'factures'],
		],
		'/ferme/{id}/itineraires' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'itineraires'],
		],
		'/ferme/{id}/itineraires/{status}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'itineraires', '{status}'],
		],
		'/ferme/{id}/livraison' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'livraison'],
		],
		'/ferme/{id}/planning/{view}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'planning', '{view}'],
		],
		'/ferme/{id}/planning/{view}/{period}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'planning', '{view}', '{period}'],
		],
		'/ferme/{id}/planning/{view}/{period}/{subPeriod}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'planning', '{view}', '{period}', '{subPeriod}'],
		],
		'/ferme/{id}/produits' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'produits'],
		],
		'/ferme/{id}/rotation' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'rotation'],
		],
		'/ferme/{id}/rotation/{season}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'rotation', '{season}'],
		],
		'/ferme/{id}/series' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'series'],
		],
		'/ferme/{id}/series/{season}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'series', '{season}'],
		],
		'/ferme/{id}/stocks' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'stocks'],
		],
		'/ferme/{id}/taches/{week}/{action}' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'taches', '{week}', '{action}'],
		],
		'/ferme/{id}/ventes' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'ventes'],
		],
		'/ferme/{id}/ventes/particuliers' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'ventes', 'particuliers'],
		],
		'/ferme/{id}/ventes/professionnels' => [
			'request' => 'company/index',
			'priority' => 5,
			'route' => ['ferme', '{id}', 'ventes', 'professionnels'],
		],
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