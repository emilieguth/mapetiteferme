<?php
new Page(function($data) {

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');

	$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
	if($data->cFinancialYear->empty() === TRUE) {
		throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear:create?message=FinancialYear::toCreate');
	}

	$data->eFinancialYear = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));

})
	->get('index', function($data) {

		\Setting::set('main\viewJournal', 'book');

		$search = new Search(['financialYear' => $data->eFinancialYear]);

		$data->cOperation = \journal\OperationLib::getAllForBook($search);
		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	})
	->get('pdf', function($data) {

		$content = pdf\PdfLib::generate($data->eCompany, $data->eFinancialYear, \pdf\PdfElement::JOURNAL_BOOK);

		if($content === NULL) {
			throw new NotExistsAction();
		}

		$filename = journal\PdfUi::filenameBook($data->eCompany).'.pdf';

		throw new PdfAction($content, $filename);
	});
?>
