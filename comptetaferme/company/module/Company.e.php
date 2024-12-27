<?php
namespace company;

class Company extends CompanyElement {

	const DEMO = 1;

	protected static array $companies = [];

	public function __construct(array $array = []) {

		parent::__construct($array);

	}

	public function active(): bool {
		return ($this['status'] === Company::ACTIVE);
	}

	// Peut voir les données personnelles des clients et la page de gestion d'équipe
	public function canPersonalData(): bool {
		return $this->canWrite();
	}


	public function getEmployee(): Employee {

		$this->expects(['id']);

		return EmployeeLib::getOnline()[$this['id']] ?? new Employee();

	}

	public function getHomeUrl(): string {

    return CompanyUi::url($this);

	}

	public function build(array $properties, array $input, array $callbacks = [], ?string $for = NULL): array {

		return parent::build($properties, $input, $callbacks);

	}

}
?>