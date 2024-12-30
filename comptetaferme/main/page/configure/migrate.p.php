<?php
(new Page())
	->cli('index', function($data) {

		$cCompany = \company\CompanyLib::getList();

		foreach($cCompany as $eCompany) {
			d($eCompany['id']);

			\company\CompanyLib::connectSpecificDatabaseAndServer($eCompany);

			$databaseName = \company\CompanyLib::getDatabaseNameFromCompany($eCompany);
			\Database::addBase($databaseName, 'ctf-default');

			$packagesToAdd = [];
			foreach(\company\CompanyLib::$specificPackages as $package) {
				$packagesToAdd[$package] = $databaseName;
			}
			$packages = \Database::getPackages();
			\Database::setPackages(array_merge($packages, $packagesToAdd));


			// Recrée les modules puis crée ou recrée toutes les tables
			$libModule = new \dev\ModuleLib();
			$libModule->load();

			$classes = $libModule->getClasses();
			foreach($classes as $class) {
				$libModule->buildModule($class);
				try {
					(new \ModuleAdministration($class))->init();
					(new \ModuleAdministration($class))->rebuild([]);
				} catch (\Exception $e) {
				}
			}

		}

	});
?>