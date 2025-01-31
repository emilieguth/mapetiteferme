<?php
(new Page())
	->get('index', function($data) {

		if($data->eUserOnline->empty()) {
			throw new ViewAction($data, path: ':anonymous');
		}

		$data->cCustomerPro = 0;
		$data->cCustomerPrivate = 0;

		$data->cShop = 0;

		$data->cSale = new Collection();

		$data->eNews = [];

		throw new ViewAction($data, path: ':logged');

	})
	->get('/presentation/invitation', fn($data) => throw new ViewAction($data))
	->get('/presentation/entreprise', fn($data) => throw new ViewAction($data))
	->get('/presentation/faq', fn($data) => throw new ViewAction($data))
	->get('/presentation/engagements', fn($data) => throw new ViewAction($data))
	->get('/presentation/legal', fn($data) => throw new ViewAction($data))
	->get('/presentation/service', fn($data) => throw new ViewAction($data));
?>
