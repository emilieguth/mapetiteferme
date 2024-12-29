<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Le journal comptable de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'journal';
	$t->subNav = (new \company\CompanyUi())->getJournalSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany);

	$t->mainTitle = (new \journal\JournalUi())->getJournalTitle($data->eCompany);

	$t->mainYear = (new \accounting\FinancialYearUi())->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) { return \company\CompanyUi::urlJournal($data->eCompany).'/?financialYear='.$eFinancialYear['id']; },
		$data->cFinancialYear,
		$data->eFinancialYearSelected,
);

	echo (new \journal\JournalUi())->getSearch($data->search, $data->eFinancialYearSelected);
	echo (new \journal\JournalUi())->getJournal($data->eCompany, $data->cOperation, $data->cOperationGrouped, $data->eFinancialYearSelected, $data->search);

});
