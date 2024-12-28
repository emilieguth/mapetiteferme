<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->title = s("Tous les opérations de {value}", $data->eCompany['name']);
	$t->tab = 'journal';
	$t->subNav = (new \company\CompanyUi())->getJournalSubNav($data->eCompany);
	$t->canonical = \company\CompanyUi::urlJournal($data->eCompany);

	$t->mainTitle = (new \journal\JournalUi())->getJournalTitle($data->eCompany);
	$t->mainTitleClass = 'hide-lateral-down';

	echo '<div class="journal-operation-list">';
		echo '<a href="'.\company\CompanyUi::urlJournal($data->eCompany).'/operation:create" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Ajouter une écriture").'</a>';
		echo (new \journal\JournalUi())->getJournal($data->eCompany, $data->cOperation, $data->cOperationGrouped, $data->cAccount);
	echo '</div>';

});
