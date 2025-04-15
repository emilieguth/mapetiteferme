<?php
new Page()
	->get('/robots.txt', function($data) {

		$data = 'User-agent: *'."\n";

		if(LIME_GENDER === 'm') {
			$data .= 'Disallow: '.Setting::get('main\robotsDisallow').''."\n";
		} else {
			$data .= 'Disallow: /'."\n";
		}

		throw new DataAction($data, 'text/txt');

	});
?>
