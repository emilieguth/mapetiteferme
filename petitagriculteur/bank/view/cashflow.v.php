<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les opérations bancaires de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'bank';
	$t->subNav = new \company\CompanyUi()->getBankSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlBank($data->eCompany).'/cashflow';

	$t->mainTitle = new \bank\BankUi()->getBankTitle($data->eCompany);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlBank($data->eCompany).'/cashflow?financialYear='.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
	);

	echo new \bank\CashflowUi()->getSearch($data->search, $data->eFinancialYearSelected);
	echo new \bank\CashflowUi()->getCashflow($data->eCompany, $data->cCashflow, $data->eFinancialYearSelected, $data->eImport, $data->search);

});

new AdaptativeView('allocate', function($data, PanelTemplate $t) {

	return new \bank\CashflowUi()->getAllocate($data->eCompany, $data->eFinancialYearCurrent, $data->eCashflow);

});

new JsonView('addAllocate', function($data, AjaxTemplate $t) {

	$t->qs('#cashflow-create-operation-list')->insertAdjacentHtml('beforeend', new \bank\CashflowUi()->addAllocate($data->eCompany, $data->eFinancialYearCurrent, $data->eCashflow, $data->index));
	$t->qs('#cashflow-add-operation')->setAttribute('post-index', $data->index + 1);
	$t->js()->eval('Cashflow.updateNewOperationLine('.$data->index.')');
	$t->js()->eval('Cashflow.fillShowHideAmountWarning('.$data->eCashflow['amount'].')');
	$t->js()->eval('Cashflow.showOrHideDeleteOperation()');

});

new AdaptativeView('attach', function($data, PanelTemplate $t) {

		return new \bank\CashflowUi()->getAttach($data->eCompany, $data->eFinancialYearCurrent, $data->eCashflow, $data->cOperation);

});
?>
