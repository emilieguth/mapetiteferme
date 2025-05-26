<?php
namespace main;

class BackupLib {

	const LOCAL_DATABASE_BACKUP_DIR = '/var/www/mysql-backup/';
	const LOCAL_STORAGE_BACKUP_DIR = '/var/www/mpf-storage/';
	const SERVER_BACKUP_DIR = '/home/ubuntu/mpf-backup/';
	const DATABASE_FOLDER = 'mysql';
	const STORAGE_FOLDER = 'storage';

	const DURATION_BACKUP_IN_DAYS = '7';
	const DURATION_BACKUP_MAX_IN_DAYS = 365 * 7;

	public static function backupDatabase(): void {

		$day = date('Y-m-d');
		exec('ls '.self::LOCAL_DATABASE_BACKUP_DIR.$day.'*', $files);

		[$serverUser, $serverHostname] = self::getHostData();

		foreach($files as $file) {

			$command = 'scp '.$file.' '.$serverUser.'@'.$serverHostname.':'.self::SERVER_BACKUP_DIR.self::DATABASE_FOLDER;
			self::exec($command);

		}

	}

	public static function backupStorage(): void {

		[$serverUser, $serverHostname] = self::getHostData();
		$day = date('Y-m-d');

		$command = 'scp -r '.self::LOCAL_STORAGE_BACKUP_DIR.' '.$serverUser.'@'.$serverHostname.':'.self::SERVER_BACKUP_DIR.self::STORAGE_FOLDER.'/'.$day;
		self::exec($command);

	}

	/**
	 * On ne garde que 1 backup par jour pendant 1 semaine glissante, puis la backup du 1er et du 15 du mois
	 * à la fois pour la BD MySQL et pour le stockage de fichiers
	 */
	public static function cleanDatabase(): void {

		self::doClean(self::SERVER_BACKUP_DIR.self::DATABASE_FOLDER);

	}

	public static function cleanStorage(): void {

		self::doClean(self::SERVER_BACKUP_DIR.self::STORAGE_FOLDER);

	}

	private static function getHostData(): array {

		$serverUser = \Setting::get('main\backupServer')['user'];
		$serverHostname = \Setting::get('main\backupServer')['hostname'];

		if(strlen($serverUser) === 0 or strlen($serverHostname) === 0) {
			throw new \Exception('backupServer must be configured.');
		}

		return [$serverUser, $serverHostname];

	}

	private static function exec(string $command): mixed {

		if(LIME_ENV === 'prod') {

			exec($command, $result);
			return $result;

		} else {

			echo $command."\n";
			return [];

		}

	}

	private static function doClean(string $folder): void {

		[$serverUser, $serverHostname] = self::getHostData();

		$today = date('Y-m-d');

		$command = 'ssh '.$serverUser.'@'.$serverHostname.' "ls '.$folder.'"';

		$files = self::exec($command);

		foreach($files as $file) {

			$fileDate = substr($file, 0, 10);
			$fileDay = substr($fileDate, -2);

			$interval = \util\DateLib::interval($today, $fileDate);
			$days = $interval / 60 / 60 / 24;

			if(in_array($fileDay, ['01, 15'])) { // Dans tous les cas on supprime les backups trop vieilles
				$check = self::DURATION_BACKUP_MAX_IN_DAYS;
			} else {
				$check = self::DURATION_BACKUP_IN_DAYS;
			}

			if($days > $check) {
				$command = 'ssh '.$serverUser.'@'.$serverHostname.' "rm -rf '.$folder.'/'.$file.'"';
				self::exec($command);
			}

		}

	}

}
?>
