<?php
namespace accounting;

class FinancialYear extends FinancialYearElement {

	public function canUpdate(): bool {
		return ($this['status'] === FinancialYear::CLOSE);
	}
}
?>