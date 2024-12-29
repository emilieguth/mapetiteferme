<?php
namespace accounting;

class FinancialYear extends FinancialYearElement {

	public function canUpdate(): bool {
		return ($this['status'] === FinancialYear::OPEN);
	}


	public function build(array $properties, array $input, array $callbacks = [], ?string $for = NULL): array {

		return parent::build($properties, $input, $callbacks + [

				'startDate.loseOperations' => function(string $date): bool {

					return \journal\OperationLib::countByOldDatesButNotNewDate($this, $date, $this['endDate']) === 0;

				},

				'startDate.check' => function(string $date): bool {

					$eFinancialYear = \accounting\FinancialYearLib::getFinancialYearSurroundingDate($date, $this['id']);

					return $eFinancialYear->exists() === FALSE;

				},

				'endDate.loseOperations' => function(string $date): bool {

					return \journal\OperationLib::countByOldDatesButNotNewDate($this, $this['startDate'], $date) === 0;

				},

				'endDate.check' => function(string $date): bool {

					$eFinancialYear = \accounting\FinancialYearLib::getFinancialYearSurroundingDate($date, $this['id']);

					return $eFinancialYear->exists() === FALSE;

				},

			]);

	}


}
?>