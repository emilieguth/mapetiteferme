<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Les bilans de {company}", ['company' => $data->eCompany['name']]);
	$t->tab = 'overview';
	$t->subNav = new \company\CompanyUi()->getOverviewSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlOverview($data->eCompany).'/accounting';

	$t->mainTitle = new overview\OverviewUi()->getBalanceTitle($data->eCompany, $data->eFinancialYear);

	$t->mainYear = new \accounting\FinancialYearUi()->getFinancialYearTabs(
		function(\accounting\FinancialYear $eFinancialYear) use ($data) {
			return \company\CompanyUi::urlOverview($data->eCompany).'/balance?financialYear='.$eFinancialYear['id'];
		},
		$data->cFinancialYear,
		$data->eFinancialYear,
	);

	echo '<div class="tabs-h" id="overview-balance" onrender="'.encode('Lime.Tab.restore(this, "balance-summarized")').'">';

		echo '<div class="tabs-item">';
			echo '<a class="tab-item selected" data-tab="balance-summarized" onclick="Lime.Tab.select(this)">'.s("Bilan comptable").'</a>';
			echo '<a class="tab-item" data-tab="balance-detailed" onclick="Lime.Tab.select(this)">'.s("Bilan comptable détaillé").'</a>';
		echo '</div>';

		echo '<div class="tab-panel" data-tab="balance-summarized">';
			echo new \overview\BalanceUi()->displaySummarizedBalance($data->balanceSummarized);
		echo '</div>';

		echo '<div class="tab-panel" data-tab="balance-detailed">';
			echo new \overview\BalanceUi()->displayDetailedBalance($data->balanceDetailed);
		echo '</div>';

	echo '</div>';

});

?>
