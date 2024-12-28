<?php
namespace journal;

class Operation extends OperationElement {

	public function canUpdate(): bool {

		$eFinancialYear = \accounting\FinancialYearLib::selectDefaultFinancialYear();

		return ($this['date'] >= $eFinancialYear['startDate'] && $this['date'] <= $eFinancialYear['endDate']);

	}
}
?>