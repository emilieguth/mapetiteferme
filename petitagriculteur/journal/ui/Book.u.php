<?php
namespace journal;

class BookUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getBookTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Le Grand Livre des comptes");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getBook(
		\company\Company $eCompany,
		\Collection $cOperation,
		\accounting\FinancialYear $eFinancialYear,
	): string {

		if($cOperation->empty() === TRUE) {
			return '<div class="util-info">'. s("Aucune écriture n'a encore été enregistrée") .'</div>';
		}

		$h = '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="table-block tr-even td-vertical-top tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Date");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Pièce");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Description");
						$h .= '</th>';
						$h .= '<th class="text-end">'.s("Débit (D)").'</th>';
						$h .= '<th class="text-end">'.s("Crédit (C)").'</th>';
					$h .= '</tr>';
				$h .= '</thead>';



					$debit = 0;
					$credit = 0;
					$currentClass = NULL;
					$currentAccountLabel = NULL;

					foreach($cOperation as $eOperation) {

						if(
							$currentAccountLabel !== NULL
							&& $currentClass !== NULL
							&& ($eOperation['class'] !== $currentClass
							|| $eOperation['accountLabel'] !== $currentAccountLabel)
						) {

							$h .= self::getSubTotal($currentAccountLabel, $debit, $credit);

						}

						if(
							$currentAccountLabel === NULL
							|| $currentClass === NULL
							|| $eOperation['class'] !== $currentClass
							|| $eOperation['accountLabel'] !== $currentAccountLabel
						) {
							$debit = 0;
							$credit = 0;
							$currentClass = $eOperation['class'];
							$currentAccountLabel = $eOperation['accountLabel'];

							$h .= '</tbody>';
							$h .= '<tr class="sub-header">';
								$h .= '<td colspan="5">';
								$h .= '<strong>'.s("{class} - {description}", [
										'class' => $currentAccountLabel,
										'description' => $eOperation['account']['description'],
									]).'</strong>';
								$h .= '</td>';
							$h .= '</tr>';
							$h .= '<tbody>';
						}

						$h .= '<tr>';

							$h .= '<td>';
								$h .= \util\DateUi::numeric($eOperation['date']);
							$h .= '</td>';

							$h .= '<td>';
								$h .= '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/?document='.encode($eOperation['document']).'&financialYear='.$eFinancialYear['id'].'">'.encode($eOperation['document']).'</a>';
							$h .= '</td>';

							$h .= '<td>';
								$h .= \encode($eOperation['description']);
							$h .= '</td>';

							$h .= '<td class="text-end">';
								$h .= match($eOperation['type']) {
									Operation::DEBIT => \util\TextUi::money($eOperation['amount']),
									default => '',
								};
							$h .= '</td>';

							$h .= '<td class="text-end">';
								$h .= match($eOperation['type']) {
									Operation::CREDIT => \util\TextUi::money($eOperation['amount']),
									default => '',
								};
							$h .= '</td>';

						$h .= '</tr>';

						$debit += $eOperation['type'] === OperationElement::DEBIT ? $eOperation['amount'] : 0;
						$credit += $eOperation['type'] === OperationElement::CREDIT ? $eOperation['amount'] : 0;

					}

					// Dernier groupe
					$h .= self::getSubTotal($currentAccountLabel, $debit, $credit);

				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;

	}

	private static function getSubTotal(string $class, float $debit, float $credit): string {

		$h = '</tbody>';

			$h .= '<tr class="row-highlight">';

				$h .= '<td colspan="3" class="text-end">';
					$h .= '<strong>'.s("Total pour le compte {class} :", [
							'class' => $class,
					]).'</strong>';
				$h .= '</td>';
				$h .= '<td class="text-end">';
					$h .= '<strong>'.\util\TextUi::money($debit).'</strong>';
				$h .= '</td>';
				$h .= '<td class="text-end">';
					$h .= '<strong>'.\util\TextUi::money($credit).'</strong>';
				$h .= '</td>';
			$h .= '</tr>';

			$balance = abs($debit - $credit);
			$h .= '<tr class="row-highlight">';

				$h .= '<td colspan="3" class="text-end">';
					$h .= '<strong>'.s("Solde :").'</strong>';
				$h .= '</td>';
				$h .= '<td class="text-end">';
					$h .= '<strong>'.($debit > $credit ? \util\TextUi::money($balance) : '').'</strong>';
				$h .= '</td>';
				$h .= '<td class="text-end">';
					$h .= '<strong>'.($debit <= $credit ? \util\TextUi::money($balance) : '').'</strong>';
				$h .= '</td>';
			$h .= '</tr>';

		$h .= '<tbody>';

		return $h;
	}
}

?>
