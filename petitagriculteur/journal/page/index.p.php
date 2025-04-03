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

		\user\ConnectionLib::checkLogged();
		\Setting::set('main\viewJournal', 'journal');

		$data->eThirdParty = get_exists('thirdParty')
			? \journal\ThirdPartyLib::getById(GET('thirdParty', 'int'))
			: NULL;

		$search = new Search([
			'date' => GET('date'),
			'accountLabel' => GET('accountLabel'),
			'description' => GET('description'),
			'type' => GET('type'),
			'document' => GET('document'),
			'thirdParty' => GET('thirdParty'),
		], GET('sort'));

		$search->set('cashflowFilter', GET('cashflowFilter', 'bool'));

		$hasSort = get_exists('sort') === TRUE;
		$data->search = clone $search;
		// Ne pas ouvrir le bloc de recherche
		$search->set('financialYear', $data->eFinancialYear);

		$data->eCashflow = \bank\CashflowLib::getById(GET('cashflow'));
		if($data->eCashflow->exists() === TRUE) {
			$search->set('cashflow', GET('cashflow'));
		}

		$data->cOperation = \journal\OperationLib::getAllForJournal($search, $hasSort);
		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	})
	->get('pdf', function($data) {

		$content = pdf\PdfLib::generateOnTheFly($data->eCompany, $data->eFinancialYear, 'journal-index');

		if($content === NULL) {
			throw new NotExistsAction();
		}

		$filename = journal\PdfUi::filenameJournal($data->eCompany).'.pdf';

		throw new PdfAction($content, $filename);
	});
?>
