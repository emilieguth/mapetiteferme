<?php
new Page(function($data) {

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canRemote');

	$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
	if($data->cFinancialYear->empty() === TRUE) {
		throw new NotExpectedAction('Cannot generate PDF of balance with no financial year');
	}

	$data->eFinancialYear = \accounting\FinancialYearLib::getById(GET('financialYear'));

})
	->get('summary', function($data) {

		$data->balanceSummarized = \overview\BalanceLib::getSummarizedBalance($data->eFinancialYear);

		throw new ViewAction($data);
	});
?>
