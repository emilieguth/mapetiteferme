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
			$h .= '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/operation:create" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Ajouter une écriture").'</a>';
		$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getJournal(\company\Company $eCompany, \Collection $cOperation): string {

		if ($cOperation->empty() === true) {
			return '<div class="util-info">'.s("Aucune opération n'a encore été enregistrée").'</div>';
		}

		$h = '';

		$h = '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="sale-item-table tr-bordered tr-even">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>'.s("Date de l'opération").'</th>';
						$h .= '<th>'.s("Classe de compte").'</th>';
						$h .= '<th>'.s("Numéro de compte").'</th>';
						$h .= '<th>'.s("Description").'</th>';
						$h .= '<th>'.s("Crédit").'</th>';
						$h .= '<th>'.s("Débit").'</th>';
						$h .= '<th>'.s("Lettrage").'</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

					foreach($cOperation as $eOperation) {
						$h .= '<tr>';

							$h .= '<td>';
								$h .= \util\DateUi::numeric($eOperation['date']);
							$h .= '</td>';

							$h .= '<td>';
								$h .= $eOperation['account']['class'];
							$h .= '</td>';

							$h .= '<td>';
								$h .= $eOperation['accountLabel'];
							$h .= '</td>';

							$h .= '<td>';
								$h .= encode($eOperation['description']);
							$h .= '</td>';

							$h .= '<td>';
								$h .= match($eOperation['type']) {
									Operation::CREDIT => \util\TextUi::number($eOperation['amount'], 2),
									default => \util\TextUi::number(0, 2),
								};
							$h .= '</td>';

							$h .= '<td>';
								$h .= match($eOperation['type']) {
									Operation::DEBIT => \util\TextUi::number($eOperation['amount'], 2),
									default => \util\TextUi::number(0, 2),
								};
							$h .= '</td>';

							$h .= '<td>';
								$h .= encode($eOperation['lettering']);
							$h .= '</td>';

							$h .= '</td>';

						$h .= '</tr>';
					}

				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;

	}

}

?>