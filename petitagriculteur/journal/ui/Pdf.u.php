<?php
namespace journal;

class PdfUi {

	public function __construct() {

		\Asset::css('pdf', 'pdf.css');

	}

	public static function filenameJournal(\company\Company $eCompany): string {

		return s("{date}-{company}-journal", ['date' => date('Y-m-d'), 'company' => $eCompany['siret']]);

	}

	public static function urlJournal(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear): string {

		return \company\CompanyUi::urlJournal($eCompany).'/:pdf';

	}

	public static function getJournalTitle(): string {

		return s("Journal");

	}

	public function getJournal(
		\company\Company $eCompany,
		\Collection $cOperation,
	): string {

		$h = '<style>@page {	size: A4; margin: calc(var(--margin-bloc-height) + 2cm) 1cm 1cm; }</style>';

		$h .= '<div class="pdf-document-wrapper">';

			$h .= '<div class="pdf-document-content">';
				$h .= '<table class="table-bordered">';

					$h .= '<thead>';
						$h .= '<tr class="row-header row-upper">';
							$h .= '<td>';
								$h .= s("Date de l'écriture");
							$h .= '</td>';
							$h .= '<td>';
								$h .= s("Pièce comptable");
							$h .= '</td>';
							$h .= '<td colspan="2">'.s("Compte (Classe et libellé)").'</td>';
							$h .= '<td>'.s("Tiers").'</td>';
							$h .= '<td class="text-end">'.s("Débit (D)").'</td>';
							$h .= '<td class="text-end">'.s("Crédit (C)").'</td>';
						$h .= '</tr>';
					$h .= '</thead>';

					$h .= '<tbody>';

					foreach($cOperation as $eOperation) {

						$h .= '<tr>';

							$h .= '<td rowspan="2">';
								$h .= \util\DateUi::numeric($eOperation['date']);
							$h .= '</td>';

							$h .= '<td rowspan="2" class="text-end">';
								$h .= '<div class="operation-info">';
									$h .= encode($eOperation['document']);
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
								$h .= encode($eOperation['description']);
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
								$h .= $debitDisplay;
							$h .= '</td>';

							$h .= '<td class="text-end">';
								$creditDisplay = match($eOperation['type']) {
									Operation::CREDIT => \util\TextUi::money($eOperation['amount']),
									default => '',
								};
								$h .= $creditDisplay;
							$h .= '</td>';

						$h .= '</tr>';
						$h .= '<tr>';
							$h .= '<td class="no-border" colspan="5">';
							$h .= encode($eOperation['account']['description']);
							$h .= '</td>';
						$h .= '</tr>';
					}

					$h .= '</tbody>';
				$h .= '</table>';
			$h .= '</div>';
		$h .= '</div>';

		return $h;

	}
}
?>
