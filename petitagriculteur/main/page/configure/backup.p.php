<?php
	new Page()
		->cli('index', function($data) {

			$cCompany = \company\CompanyLib::getList();

			// Extract credentials
			$username = GET('username');
			$password = GET('password');

			exec('mysqldump -u '.$username.' "'.$password.'" petitagriculteur > /var/www/mysql-backup/petitagriculteur.sql');
			foreach($cCompany as $eCompany) {

				$database = \company\CompanyLib::getDatabaseName($eCompany);
				exec('mysqldump -u '.$username.' "'.$password.'" '.$database.' > /var/www/mysql-backup/'.$database.'.sql');

			}

		});
?>
