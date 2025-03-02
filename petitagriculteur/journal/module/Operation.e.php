<?php
namespace journal;

class Operation extends OperationElement {

	public function canQuickDocument(): bool {
		return TRUE;
	}

	public function canUpdate(): bool {

		$eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		return (
			$this['date'] >= $eFinancialYear['startDate']
			and $this['date'] <= $eFinancialYear['endDate']
			// On ne permet pas de mettre à jour une écriture si elle a été attribuée via le relevé bancaire
			and $this['cashflow']->exists() === FALSE
		);

	}

	public function build(array $properties, array $input, array $callbacks = [], ?string $for = NULL): array {

		return parent::build($properties, $input, $callbacks + [

				'date.check' => function(string $date): bool {

					$eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

					return ($date >= $eFinancialYear['startDate'] && $date <= $eFinancialYear['endDate']);

				},

			]);

	}


}
?>