<?php
namespace accounting;
class FinancialYearLib extends FinancialYearCrud {

	public static function selectDefaultFinancialYear(): FinancialYear {

		$eFinancialYear = new FinancialYear();

		FinancialYear::model()
			->select(FinancialYear::getSelection())
			->whereStatus(FinancialYearElement::OPEN)
			->get($eFinancialYear);

		return $eFinancialYear;

	}

	public static function getAll($query = ''): \Collection {

		return FinancialYear::model()
			->select(FinancialYear::getSelection())
			->sort(['startDate' => SORT_DESC])
			->getCollection();

	}
	public static function createDefault(): void {

		$eFinancialYear = new FinancialYear(['startDate' => date('Y').'-01-01', 'endDate' => date('Y').'-12-31']);

		self::create($eFinancialYear);

	}
}

?>