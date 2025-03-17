<?php
namespace journal;

class JournalUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getJournalTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Le journal comptable");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#journal-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
				if(get_exists('cashflow') === FALSE) {
					$h .= '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/operation:create" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Ajouter une écriture").'</a>';
				}
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getSearch(\Search $search, \accounting\FinancialYear $eFinancialYearSelected, \bank\Cashflow $eCashflow, ?ThirdParty $eThirdParty): string {

		\Asset::js('journal', 'operation.js');

		$h = '<div id="journal-search" class="util-block-search stick-xs '.($search->empty(['ids']) === TRUE ? 'hide' : '').'">';

			$form = new \util\FormUi();
			$url = LIME_REQUEST_PATH.'?financialYear='.$eFinancialYearSelected['id'];

			$statuses = OperationUi::p('type')->values;

			$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

				$h .= '<div>';
					$h .= $form->month('date', $search->get('date'), ['placeholder' => s("Mois")]);
					$h .= $form->text('accountLabel', $search->get('accountLabel'), ['placeholder' => s("Classe de compte")]);
					$h .= $form->text('description', $search->get('description'), ['placeholder' => s("Description")]);
					$h .= $form->select('type', $statuses, $search->get('type'), ['placeholder' => s("Type")]);
					$h .= $form->dynamicField(new Operation(['thirdParty' => $eThirdParty]), 'thirdParty', function($d) use($form) {
						$d->autocompleteDispatch = '[data-third-party="form-search"]';
						$d->attributes['data-index'] = 0;
						$d->attributes['data-third-party'] = 'form-search';
					});
					$h .= $form->text('document', $search->get('document'), ['placeholder' => s("Pièce comptable")]);
					$h .= $form->checkbox('cashflowFilter', 1, ['checked' => $search->get('cashflowFilter'), 'callbackLabel' => fn($input) => $input.' '.s("Filtrer les écritures non rattachées")]);
				$h .= '</div>';
				$h .= '<div>';
					$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
					$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
				$h .= '</div>';

			$h .= $form->close();

		$h .= '</div>';

		if($eCashflow->exists() === TRUE) {
			$h .= '<div class="util-block-search stick-xs">';
				$h .= s(
					"Vous visualisez actuellement les écritures correspondant à l'opération bancaire du {date}, \"{memo}\" d'un {type} de {amount}.",
					[
						'date' => \util\DateUi::numeric($eCashflow['date']),
						'memo' => encode($eCashflow['memo']),
						'type' => mb_strtolower(\bank\CashflowUi::p('type')->values[$eCashflow['type']]),
						'amount' => \util\TextUi::money(abs($eCashflow['amount'])),
					]
				);
			$h .= '</div>';
		}

		return $h;

	}

	public function getJournal(
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

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="table-block tr-even td-vertical-top tr-hover">';

				$h .= '<thead>';
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
						$h .= '<th colspan="2">'.s("Compte (Classe et libellé)").'</th>';
						$h .= '<th>';
							$label = s("Description");
							$h .= ($search ? $search->linkSort('description', $label) : $label);
						$h .= '</th>';
						$h .= '<th>'.s("Tiers").'</th>';
						$h .= '<th class="text-end">'.s("Débit (D)").'</th>';
						$h .= '<th class="text-end">'.s("Crédit (C)").'</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

					foreach($cOperation as $eOperation) {

						$canUpdate = $eFinancialYearSelected['status'] === \accounting\FinancialYear::OPEN
							and $eOperation['date'] <= $eFinancialYearSelected['endDate']
							and $eOperation['date'] >= $eFinancialYearSelected['startDate'];

						$eOperation->setQuickAttribute('company', $eCompany['id']);

						$h .= '<tr>';

							$h .= '<td>';
								$h .= \util\DateUi::numeric($eOperation['date']);
							$h .= '</td>';

							$h .= '<td>';
								if($eOperation['cashflow']->exists() === TRUE) {
									$h .= '<a href="'.\company\CompanyUi::urlBank($eCompany).'/cashflow?id='.$eOperation['cashflow']['id'].'" class="color-text">'.$eOperation['cashflow']['id'].'</a>';
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
								if($eOperation['accountLabel'] !== NULL) {
									$h .= encode($eOperation['accountLabel']);
								} else {
									$h .= encode(str_pad($eOperation['account']['class'], 8, 0));
								}
							$h .= '</td>';

							$h .= '<td>';
								$h .= encode($eOperation['account']['description']);
							$h .= '</td>';

							$h .= '<td>';
								if($canUpdate === TRUE) {
									$h .= $eOperation->quick('description', encode($eOperation['description']));
								} else {
									$h .= encode($eOperation['description']);
								}
							$h .= '</td>';

							$h .= '<td>';
								if($eOperation['thirdParty']->exists() === TRUE) {
									$h .= encode($eOperation['thirdParty']['name']);
								}
							$h .= '</td>';

							$h .= '<td class="text-end">';
								$debitDisplay = match($eOperation['type']) {
									Operation::DEBIT => \util\TextUi::money($eOperation['amount']),
									default => '',
								};
								if($canUpdate === TRUE) {
									$h .= $eOperation->quick('amount', $debitDisplay);
								} else {
									$h .= $debitDisplay;
								}
							$h .= '</td>';

							$h .= '<td class="text-end">';
								$creditDisplay = match($eOperation['type']) {
									Operation::CREDIT => \util\TextUi::money($eOperation['amount']),
									default => '',
								};
								if($canUpdate === TRUE) {
									$h .= $eOperation->quick('amount', $creditDisplay);
								} else {
									$h .= $creditDisplay;
								}
							$h .= '</td>';

							$h .= '<td>';
								if(
									$canUpdate === TRUE
									// On ne supprime pas une opération unitaire : il faut refaire l'attribution
									and $eOperation['cashflow']->exists() === FALSE
								) {
									if($eOperation['vatAccount']->exists() === TRUE) {
										$message = s("En supprimant cette écriture, l'entrée de TVA associée sera également supprimée. Confirmez-vous la suppression de cette écriture ?");
									} else {
										$message = s("Confirmez-vous la suppression de cette écriture ?");
									}
									$h .= '<a data-ajax="'.\company\CompanyUi::urlJournal($eCompany).'/operation:doDelete" post-id="'.$eOperation['id'].'" data-confirm="'.$message.'" class="btn btn-outline-secondary btn-outline-danger">'.\Asset::icon('trash').'</a>';
								}
							$h .= '</td>';

						$h .= '</tr>';
					}

				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;

	}

	protected function getUpdate(\company\Company $eCompany, Operation $eOperation, string $btn): string {

		$primaryList = '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/operation:update?id='.$eOperation['id'].'" class="dropdown-item">'.s("Modifier").'</a>';;

		$secondaryList = '<a data-ajax="'.\company\CompanyUi::urlJournal($eCompany).'/operation:doDelete" post-id="'.$eOperation['id'].'" class="dropdown-item" data-confirm="'.s("Confirmer la suppression de cette écriture ?").'">'.s("Supprimer").'</a>';

		$h = '<a data-dropdown="bottom-end" class="dropdown-toggle btn '.$btn.'">'.\Asset::icon('gear-fill').'</a>';
		$h .= '<div class="dropdown-list">';

		$h .= $primaryList;

		if($secondaryList) {

			$h .= '<div class="dropdown-divider"></div>';
			$h .= $secondaryList;

		}

		$h .= '</div>';

		return $h;

	}
}

?>
