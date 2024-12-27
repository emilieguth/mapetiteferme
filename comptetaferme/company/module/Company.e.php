<?php
namespace company;

class Company extends CompanyElement {

	const DEMO = 1;

	protected static array $selling = [];
	protected static array $farms = [];

	public function __construct(array $array = []) {

		parent::__construct($array);

	}

	public static function getSelection(): array {

		return parent::getSelection() + [
			'calendarMonths' => new \Sql('IF(calendarMonthStart IS NULL, 0, 12 - calendarMonthStart + 1) + 12 + IF(calendarMonthStop IS NULL, 0, calendarMonthStop)', 'int'),
		];

	}

	public function validateSeason(mixed $season): self {

		if($this->checkSeason($season) === FALSE) {
			throw new \NotExpectedAction($this);
		}

		return $this;

	}

	public function getSeasons(): array {
		$seasons = [];
		for($season = $this['seasonLast']; $season >= $this['seasonFirst']; $season--) {
			$seasons[] = $season;
		}
		return $seasons;
	}

	public function getRotationSeasons(int $lastSeason): array {

		$this->expects(['rotationYears', 'seasonFirst']);

		$seasons = [];
		for($season = $lastSeason; $season >= $this['seasonFirst'] and count($seasons) < $this['rotationYears']; $season--) {
			$seasons[] = $season;
		}

		return $seasons;

	}

	public function checkSeason(mixed $season): bool {
		return ($season >= $this['seasonFirst'] and $season <= $this['seasonLast']);
	}

	public function active(): bool {
		return ($this['status'] === Farm::ACTIVE);
	}

	// Peut accéder aux pages d'analyse des données
	public function canAnalyze(): bool {
		return (
			$this->canManage() or
			$this->isRole(Employee::OBSERVER)
		);
	}

	// Peut voir le planning
	public function canPlanning(): bool {
		return (
			$this->canManage() or
			$this->isRole(Employee::OBSERVER) === FALSE
		);
	}

	// Peut voir les données personnelles des clients et la page de gestion d'équipe
	public function canPersonalData(): bool {
		return $this->canManage();
	}

	// Peut accéder en lecture aux pages de commercialisation et en écriture aux pages de ventes
	public function canSelling(): bool {
		return (
			$this->canManage() or
			$this->isRole(Employee::PERMANENT)
		);
	}

	// Peut créer ou modifier des interventions
	public function canTask(): bool {
		return (
			$this->canManage() or
			$this->isRole(Employee::PERMANENT)
		);
	}

	// Peut gérer son temps de travail et commenter les interventions
	public function canWork(): bool {
		return (
			$this->canManage() or
			$this->isRole(Employee::SEASONAL) or
			$this->isRole(Employee::PERMANENT)
		);
	}

	// Peut gérer la ferme
	public function canManage(): bool {
		return $this->isRole(Employee::OWNER);

	}

	public function isRole(string $role): bool {

		if($this->empty()) {
			return FALSE;
		}

		$eFarmer = $this->getFarmer();

		return (
			$eFarmer->notEmpty() and
			$eFarmer['role'] === $role
		);

	}

	public function canCreate(): bool {
		return (\user\ConnectionLib::getOnline()->isRole('customer') === FALSE);
	}

	public function canWrite(): bool {

		if($this->empty()) {
			return FALSE;
		}

		return $this->getFarmer()->notEmpty();

	}

	public function canRemote(): bool {
		return $this->canRead();
	}

	public function canShop(): bool {

		$this->expects(['legalEmail', 'legalName']);

		return (
			$this['legalEmail'] !== NULL and
			$this['legalName'] !== NULL
		);

	}

	public function saveFeaturesAsSettings() {

		foreach(['featureTime', 'featureDocument'] as $feature) {
			\Setting::set('company\\'.$feature, $this[$feature]);
		}

	}

	public function hasFeatureTime(): bool {

		$this->expects(['featureTime']);

		return $this['featureTime'];

	}


	public function getEmployee(): Employee {

		$this->expects(['id']);

		return EmployeeLib::getOnline()[$this['id']] ?? new Employee();

	}

	public function getHomeUrl(): string {

		if($this->canPlanning()) {
			return CompanyUi::urlPlanningWeekly($this);
		} else {
			return CompanyUi::urlCultivation($this, Employee::SERIES, Employee::AREA);
		}

	}

	public function build(array $properties, array $input, array $callbacks = [], ?string $for = NULL): array {

		return parent::build($properties, $input, $callbacks);

	}

}
?>