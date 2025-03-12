<?php
	new Page()
		->cli('index', function($data) {

			$cCompany = \company\CompanyLib::getList();

			$output = shell_exec('crontab -l');
			$mysqldumpLine = NULL;
			foreach(explode("\n", $output) as $line) {
				if(strpos($line, 'mysqldump') > 0) {
					$mysqldumpLine = $line;
				}
			};

			// Extract credentials
			preg_match('/mysqldump -u (\w+) "(.+)"/', $mysqldumpLine, $matches);
			[, $username, $password] = $matches;

			exec('mysqldump -u '.$username.' "'.$password.'" petitagriculteur > /var/www/mysql-backup/petitagriculteur.sql');
			foreach($cCompany as $eCompany) {

				$database = \company\CompanyLib::getDatabaseName($eCompany);
				exec('mysqldump -u '.$username.' "'.$password.'" '.$database.' > /var/www/mysql-backup/'.$database.'.sql');

			}

		});
?>
