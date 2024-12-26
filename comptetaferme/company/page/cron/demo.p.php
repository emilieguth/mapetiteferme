<?php
/**
 * Close expired user accounts
 *
 */
(new Page())
	->cron('index', function($data) {

		\company\DemoLib::rebuild();

	}, interval: '0 4 * * *');
?>