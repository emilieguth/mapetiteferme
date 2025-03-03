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

	public function getEmployee(): Employee {

		$this->expects(['id']);

		return EmployeeLib::getOnline()[$this['id']] ?? new Employee();

	}


	// Peut gérer l'entreprise
	public function canManage(): bool {
		if($this->empty()) {
			return FALSE;
		}

		if($this['status'] === CompanyElement::CLOSED) {
			return FALSE;
		}

		$eEmployee = $this->getEmployee();

		return $eEmployee->notEmpty();

	}

	// Peut voir les données personnelles des clients et la page de gestion d'équipe
	public function canPersonalData(): bool {
		return $this->canWrite();
	}


	public function getHomeUrl(): string {

    return CompanyUi::urlJournal($this).'/';

	}

	public function build(array $properties, array $input, \Properties $p = new \Properties()): void {

		parent::build($properties, $input, $p);

	}

}
?>
