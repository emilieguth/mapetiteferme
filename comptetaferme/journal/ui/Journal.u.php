<?php
namespace journal;

class JournalUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getJournalTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Journal d'écritures");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#journal-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
				$h .= '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/operation:create" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Ajouter une écriture").'</a>';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}


	public function getSearch(\Search $search): string {

		$h = '<div id="journal-search" class="util-block-search stick-xs '.($search->empty(['ids']) ? 'hide' : '').'">';

			$form = new \util\FormUi();
			$url = LIME_REQUEST_PATH;

			$statuses = OperationUi::p('type')->values;

			$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

				$h .= '<div>';
						$h .= $form->month('date', $search->get('date'), ['placeholder' => s("Mois")]);
						$h .= $form->text('accountLabel', $search->get('accountLabel'), ['placeholder' => s("Numéro de compte")]);
						$h .= $form->text('description', $search->get('description'), ['placeholder' => s("Description")]);
						$h .= $form->select('type', $statuses, $search->get('type'), ['placeholder' => s("Type")]);
						$h .= $form->text('lettering', $search->get('lettering'), ['placeholder' => s("Lettrage")]);
					$h .= '</div>';
					$h .= '<div>';
						$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
						$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
				$h .= '</div>';

			$h .= $form->close();

		$h .= '</div>';

		return $h;

	}

	public function getJournal(\company\Company $eCompany, \Collection $cOperation, \Collection $cOperationGrouped, \accounting\FinancialYear $eFinancialYearSelected): string {

		if ($cOperation->empty() === true) {
			return '<div class="util-info">'.s("Aucune opération n'a encore été enregistrée").'</div>';
		}

		$h = '';

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="sale-item-table tr-bordered tr-even">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th class="text-end">'.s("Date de l'opération").'</th>';
						$h .= '<th>'.s("Description").'</th>';
						$h .= '<th class="text-end">'.s("Crédit (C)").'</th>';
						$h .= '<th class="text-end">'.s("Débit (D)").'</th>';
						$h .= '<th class="text-end">'.s("Solde (D-C)").'</th>';
						$h .= '<th>'.s("Lettrage").'</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

					$lastAccount = new \accounting\Account();
					foreach($cOperation as $eOperation) {

						if ($lastAccount->empty() === true or $lastAccount['id'] !== $eOperation['account']['id']) {
							$lastAccount = $eOperation['account'];
							$h .= '<tr>';
								$h .= '<td>';
									$h .= '<strong>'.$eOperation['accountLabel'].'</strong>';
								$h .= '</td>';
								$h .= '<td>';
									$h .= '<strong>'.$lastAccount['description'].'</strong>';
								$h .= '</td>';
								$h .= '<td class="text-end">';
									$h .= '<strong>'.\util\TextUi::money($cOperationGrouped[$lastAccount['id']]['credit']).'</strong>';
								$h .= '</td>';
								$h .= '<td class="text-end">';
									$h .= '<strong>'.\util\TextUi::money($cOperationGrouped[$lastAccount['id']]['debit']).'</strong>';
								$h .= '</td>';
								$h .= '<td class="text-end">';
									$h .= '<strong>'.\util\TextUi::money($cOperationGrouped[$lastAccount['id']]['debit'] - $cOperationGrouped[$lastAccount['id']]['credit']).'</strong>';
								$h .= '</td>';
								$h .= '<td></td>';
								$h .= '<td></td>';
							$h .= '</tr>';
						}
						$h .= '<tr>';

							$h .= '<td class="text-end">';
								$h .= \util\DateUi::numeric($eOperation['date']);
							$h .= '</td>';

							$h .= '<td>';
								$h .= encode($eOperation['description']);
							$h .= '</td>';

							$h .= '<td class="text-end">';
								$h .= match($eOperation['type']) {
									Operation::CREDIT => \util\TextUi::money($eOperation['amount']),
									default => \util\TextUi::money(0),
								};
							$h .= '</td>';

						$h .= '<td class="text-end">';
						$h .= match($eOperation['type']) {
							Operation::DEBIT => \util\TextUi::money($eOperation['amount']),
							default => \util\TextUi::money(0),
						};
						$h .= '</td>';

						$balance = match($eOperation['type']) {
							Operation::CREDIT => $eOperation['amount'],
							Operation::DEBIT => -$eOperation['amount'],
							default => 0,
						};
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money($balance);
						$h .= '</td>';

							$h .= '<td>';
								$h .= encode($eOperation['lettering']);
							$h .= '</td>';

							$h .= '<td>';
								if (
									$eFinancialYearSelected['status'] === \accounting\FinancialYear::OPEN
									&& currentDate() <= $eFinancialYearSelected['endDate']
									&& currentDate() >= $eFinancialYearSelected['startDate']
								) {
									$h .= $this->getUpdate($eCompany, $eOperation, 'btn-outline-secondary');
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

		$primaryList = '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/operation:update?id='.$eOperation['id'].'" class="dropdown-item">'.s("Modifier la ligne").'</a>';;

		$secondaryList = '<a data-ajax="'.\company\CompanyUi::urlJournal($eCompany).'/operation:update" post-id="'.$eOperation['id'].'" class="dropdown-item" data-confirm="'.s("Confirmer la suppression de la ligne ?").'">'.s("Supprimer la ligne").'</a>';

		$h = '<a data-dropdown="bottom-end" class="dropdown-toggle btn '.$btn.'">'.\Asset::icon('gear-fill').'</a>';
		$h .= '<div class="dropdown-list">';
		$h .= '<div class="dropdown-title">'.s("Opération #{id}", ['id' => $eOperation['id']]).'</div>';

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