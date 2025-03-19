<?php
namespace journal;

class StatementUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getStatementTitle(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Les bilans");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getBalanceTitle(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Les balances");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	private function number(mixed $number, ?string $valueIfEmpty): string {

		if(is_null($number) === true or $number === 0 or $number === 0.0) {

			if(is_null($valueIfEmpty) === FALSE) {
				return $valueIfEmpty;
			}

			return number_format(0, 2, '.', ' ');

		}

		return number_format($number, 2, '.', ' ');

	}

	public function displayAccountingBalanceSheet(array $accountingBalanceSheet): string {

		if(empty($accountingBalanceSheet) === TRUE) {
			return '<div class="util-info">'.s("Il n'y a rien à afficher pour le moment.").'</div>';
		}

		$h = '<h2>'.s("Balance comptable").'</h2>';
		$h .= '<div class="util-overflow-sm">';

		$h .= '<table id="account-list" class="table-block tr-even tr-hover">';

			$h .= '<thead>';
				$h .= '<tr>';
					$h .= '<th>'.s("Compte").'</th>';
					$h .= '<th>'.s("Libellé").'</th>';
					$h .= '<th>'.s("Début débit").'</th>';
					$h .= '<th>'.s("Début crédit").'</th>';
					$h .= '<th>'.s("Mouvement débit").'</th>';
					$h .= '<th>'.s("Mouvement crédit").'</th>';
					$h .= '<th>'.s("Solde fin débiteur N").'</th>';
					$h .= '<th>'.s("Solde fin créditeur N").'</th>';
					$h .= '<th>'.s("Solde fin débiteur N-1").'</th>';
					$h .= '<th>'.s("Solde fin créditeur N-1").'</th>';
				$h .= '</tr>';
			$h .= '</thead>';

			$h .= '<tbody>';

				foreach($accountingBalanceSheet as $balance) {

					$isTotal = $balance['accountLabel'] === 'total';

					$h .= '<tr'.($isTotal === TRUE ? ' class="sub-header"' : '').'>';

						$h .= '<td>'.($isTotal === TRUE ? s("Totaux") : encode($balance['accountLabel'])).'</td>';
						$h .= '<td>'.($isTotal === TRUE ? s("comptes") : encode($balance['description'])).'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['startDebit'], '').'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['startCredit'], '').'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['moveDebit'], '').'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['moveCredit'], '').'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['balanceDebit'], '').'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['balanceCredit'], '').'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['lastBalanceDebit'], '').'</td>';
						$h .= '<td class="text-end util-unit">'.$this->number($balance['lastBalanceCredit'], '').'</td>';

					$h .= '</tr>';
				}

			$h .= '<tbody>';
			$h .= '</table>';

		$h .= '</div>';

		return $h;

	}

}
?>
