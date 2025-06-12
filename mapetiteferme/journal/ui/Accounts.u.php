<?php
namespace journal;

class AccountsUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getAccountsTitle(\accounting\FinancialYear $eFinancialYear): string {

		$h = '<div class="util-action">';

		$h .= '<h1>';
			$h .= s("Les comptes auxiliaires");
		$h .= '</h1>';

		if($eFinancialYear->notEmpty()) {

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#accounts-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
			$h .= '</div>';

		}

		$h .= '</div>';

		return $h;

	}

	public function getSearch(\Search $search, \accounting\FinancialYear $eFinancialYearSelected, ?ThirdParty $eThirdParty): string {

		\Asset::js('journal', 'operation.js');

		$h = '<div id="accounts-search" class="util-block-search stick-xs '.($search->empty(['ids']) === TRUE ? 'hide' : '').'">';

			$form = new \util\FormUi();
			$url = LIME_REQUEST_PATH.'?financialYear='.$eFinancialYearSelected['id'].'&accountType='.GET('accountType');

			$statuses = OperationUi::p('type')->values;

			$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

			$h .= '<div>';
				$h .= $form->month('date', $search->get('date'), ['placeholder' => s("Mois")]);
				$h .= $form->text('description', $search->get('description'), ['placeholder' => s("Description")]);
				$h .= $form->select('type', $statuses, $search->get('type'), ['placeholder' => s("Type")]);
				$h .= $form->dynamicField(new Operation(['thirdParty' => $eThirdParty]), 'thirdParty', function($d) use($form) {
					$d->autocompleteDispatch = '[data-third-party="form-search"]';
					$d->attributes['data-index'] = 0;
					$d->attributes['data-third-party'] = 'form-search';
				});
				$h .= $form->text('document', $search->get('document'), ['placeholder' => s("Pièce comptable")]);
				$h .= $form->checkbox('letteringFilter', 1, ['checked' => $search->get('letteringFilter'), 'callbackLabel' => fn($input) => $input.' '.s("Filtrer les écritures non lettrées")]);
			$h .= '</div>';
			$h .= '<div>';
				$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
				$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
			$h .= '</div>';

			$h .= $form->close();

		$h .= '</div>';

		return $h;

	}


	public function getJournal(
		\company\Company $eCompany,
		\Collection $cOperation,
		\accounting\FinancialYear $eFinancialYearSelected,
		\Search $search = new \Search()
	): string {

		$accountsTypes = [
			'customer' => s("Compte clients"),
			'supplier' => s("Compte fournisseurs"),
		];
		$selectedAccountType = GET('accountType', 'string', 'customer');

		$h = '<div class="tabs-h" id="accounts">';

			$h .= '<div class="tabs-item">';

				foreach($accountsTypes as $accountsType => $translation) {

					$h .= '<a class="tab-item'.($selectedAccountType === $accountsType ? ' selected' : '').'" data-tab="account-'.$accountsType.'" href="'.\company\CompanyUi::urlJournal($eCompany).'/accounts?accountType='.$accountsType.'">'.$translation.'</a>';

				}

			$h .= '</div>';

			foreach($accountsTypes as $accountsType => $translation) {
				$h .= '<div class="tab-panel'.($selectedAccountType === $accountsType ? ' selected' : '').'" data-tab="account-'.$accountsType.'">';
					$h .= $this->getTableContainer($eCompany, $cOperation, $eFinancialYearSelected, $search);
				$h .= '</div>';
			}

		$h .= '</div>';

		return $h;

	}
	public function getTableContainer(
		\company\Company $eCompany,
		\Collection $cOperation,
		\accounting\FinancialYear $eFinancialYearSelected,
		\Search $search = new \Search()
	): string {

		if($cOperation->empty() === TRUE) {

			if($search->empty(['ids']) === TRUE) {
				return '<div class="util-info">'.s("Aucune écriture n'a encore été enregistrée").'</div>';
			}
			return '<div class="util-info">'.s("Aucune écriture ne correspond à vos critères de recherche").'</div>';

		}

		\Asset::js('util', 'form.js');
		\Asset::css('util', 'form.css');

		$h = '';

		$h .= '<div class="stick-sm util-overflow-sm">';

		$h .= '<table class="tr-even td-vertical-top tr-hover">';

		$h .= '<thead class="thead-sticky">';
			$h .= '<tr>';
				$h .= '<th>';
					$label = s("Date de l'écriture");
					$h .= ($search ? $search->linkSort('date', $label) : $label);
				$h .= '</th>';
				$h .= '<th>'.s("# Opération bancaire").'</th>';
				$h .= '<th>';
					$label = s("Pièce comptable");
					$h .= ($search ? $search->linkSort('document', $label) : $label);
				$h .= '</th>';
				$h .= '<th>';
					$label = s("Description");
					$h .= ($search ? $search->linkSort('description', $label) : $label);
				$h .= '</th>';
				$h .= '<th>'.s("Tiers").'</th>';
				$h .= '<th class="text-end">'.s("Débit (D)").'</th>';
				$h .= '<th class="text-end">'.s("Crédit (C)").'</th>';
				$h .= '<th></th>';
			$h .= '</tr>';
		$h .= '</thead>';

		$h .= '<tbody>';

		foreach($cOperation as $eOperation) {

			$canUpdate = ($eFinancialYearSelected['status'] === \accounting\FinancialYear::OPEN
				and $eOperation['date'] <= $eFinancialYearSelected['endDate']
				and $eOperation['date'] >= $eFinancialYearSelected['startDate']
				and $eCompany->canWrite() === TRUE);

			$eOperation->setQuickAttribute('company', $eCompany['id']);
			if($eOperation['cashflow']->exists() === TRUE) {
				$cashflowLink = \company\CompanyUi::urlBank($eCompany).'/cashflow?id='.$eOperation['cashflow']['id'];
			} else {
				$cashflowLink = NULL;
			}

			$h .= '<tr name="operation-'.$eOperation['id'].'" name-linked="operation-linked-'.($eOperation['operation']['id'] ?? '').'">';

			$h .= '<td>';
				$h .= \util\DateUi::numeric($eOperation['date']);
			$h .= '</td>';

			$h .= '<td>';
				if($eOperation['cashflow']->exists() === TRUE) {
					$h .= '<a href="'.$cashflowLink.'" class="color-text">'.$eOperation['cashflow']['id'].'</a>';
				} else {
					$h .= '';
				}
			$h .= '</td>';

			$h .= '<td>';
				$h .= '<div class="operation-info">';
				if($canUpdate === TRUE) {
					$h .= $eOperation->quick('document', $eOperation['document'] ? encode($eOperation['document']) : '<i>'.s("Non définie").'</i>');
				} else {
					$h .= encode($eOperation['document']);
				}
				$h .= '</div>';
			$h .= '</td>';

			$h .= '<td>';
				if($canUpdate === TRUE) {
					$h .= $eOperation->quick('description', encode($eOperation['description']));
				} else {
					$h .= encode($eOperation['description']);
				}
			$h .= '</td>';

			$h .= '<td>';
				$h .= encode($eOperation['thirdParty']['name']);
			$h .= '</td>';

			$h .= '<td class="text-end">';
				$debitDisplay = match($eOperation['type']) {
					Operation::DEBIT => \util\TextUi::money($eOperation['amount']),
					default => '',
				};
				$h .= $debitDisplay;
			$h .= '</td>';

			$h .= '<td class="text-end">';
				$creditDisplay = match($eOperation['type']) {
					Operation::CREDIT => \util\TextUi::money($eOperation['amount']),
					default => '',
				};
				$h .= $creditDisplay;
			$h .= '</td>';

			$h .= '<td>';
				//$h .= '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/operation:create?'.http_build_query($args).'" class="btn btn-outline-secondary" class="btn btn-outline-secondary" title="'.s("Enregistrer le paiement").'">'.\Asset::icon('truck').'</a>';
			$h .= '</td>';

			$h .= '</tr>';
		}

		$h .= '</tbody>';
		$h .= '</table>';
		$h .= '</div>';

		return $h;

	}

}
