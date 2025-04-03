<?php
new Page()
	->get('index', function($data) {

		$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canRemote');

		$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
		if($data->cFinancialYear->empty() === TRUE) {
			throw new NotExpectedAction('Cannot generate PDF of book with no financial year');
		}

		$data->eFinancialYear = \accounting\FinancialYearLib::getById(GET('financialYear'));

		$search = new Search(['financialYear' => $data->eFinancialYear]);

		$data->cOperation = \journal\OperationLib::getAllForBook($search);

		throw new ViewAction($data);

	});
?>
