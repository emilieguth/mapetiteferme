<?php
new Page(function($data) {
	\user\ConnectionLib::checkLogged();

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');

	$data->cFinancialYear = \accounting\FinancialYearLib::getAll();
	if($data->cFinancialYear->empty() === TRUE) {
		throw new RedirectAction(\company\CompanyUi::urlAccounting($data->eCompany).'/financialYear/:create?message=FinancialYear::toCreate');
	}

	$data->eFinancialYear = \company\EmployeeLib::getDynamicFinancialYear($data->eCompany, GET('financialYear', 'int'));
	\Setting::set('main\viewOverview', 'balance');

})
->get('index', function($data) {

	$data->balanceSummarized = \overview\BalanceLib::getSummarizedBalance($data->eFinancialYear);
	$data->balanceDetailed = \overview\BalanceLib::getDetailedBalance($data->eFinancialYear);

	throw new \ViewAction($data);
})
->get('pdf', function($data) {

	$content = \pdf\PdfLib::generate($data->eCompany, $data->eFinancialYear, \pdf\PdfElement::OVERVIEW_BALANCE_SUMMARY);

	if($content === NULL) {
		throw new NotExistsAction();
	}

	$filename = \overview\PdfUi::filenameBalance($data->eCompany).'.pdf';

	throw new PdfAction($content, $filename);
});
?>
