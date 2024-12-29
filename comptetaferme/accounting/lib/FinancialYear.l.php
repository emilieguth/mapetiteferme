<?php
namespace accounting;
class FinancialYearLib extends FinancialYearCrud {

	public static function getPropertiesUpdate(): array {
		return ['startDate', 'endDate'];
	}

	public static function closeFinancialYear(FinancialYear $eFinancialYear): void {

		if ($eFinancialYear['status'] == FinancialYearElement::CLOSE) {
			throw new \NotExpectedAction('Financial year already closed');
		}

		$eFinancialYear['status'] = FinancialYearElement::CLOSE;
		self::update($eFinancialYear, ['status']);

		$eFinancialYearLast = FinancialYear::model()
			->select(FinancialYear::getSelection())
			->sort(['endDate' => SORT_DESC])
			->get();

		$eFinancialYearNew = new FinancialYear([
			'status' => FinancialYearElement::OPEN,
			'startDate' => date('Y-m-d', strtotime($eFinancialYearLast['endDate'].' +1 day')),
			'endDate' => date('Y-m-d', strtotime($eFinancialYearLast['endDate'].' +1 year'))
		]);

		self::create($eFinancialYearNew);

	}

	public static function getFinancialYearSurroundingDate(string $date, int $excludedId): FinancialYear {

		$eFinancialYear = new FinancialYear();

		FinancialYear::model()
			->select(FinancialYear::getSelection())
			->whereStartDate('<=', $date)
			->whereEndDate('>=', $date)
			->whereId('!=', $excludedId)
			->get($eFinancialYear);


		return $eFinancialYear;

	}
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