<?php
namespace journal;

class AnalyzeUi {

	public function __construct() {
		\Asset::css('journal', 'analyze.css');
		\Asset::js('journal', 'chart.js');
	}

	public static function getBankTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Suivi de la trésorerie");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getBank(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, \Collection $cOperation): string {

		if($cOperation->empty() === TRUE) {

			$h = '<div class="util-info">';
				$h .= s("Le suivi de la trésorerie sera disponible lorsque vous aurez attribué des écritures à vos opérations bancaires pour cet exercice.");
			$h .= '</div>';

			return $h;
		}

			$h = '<div class="analyze-chart-table">';
				$h .= $this->getBankChart($cOperation);
				$h .= $this->getBankTable($cOperation);
			$h .= '</div>';


		return $h;
	}

	protected function getBankValues(\Collection $cOperation): array {

		$credit = [];
		$debit = [];
		$total = [];
		$labels = [];

		foreach($cOperation as $eOperation) {
			$labels[] = \util\DateUi::textual($eOperation['month'], \util\DateUi::MONTH_YEAR);
			$credit[] = $eOperation['credit'];
			$debit[] = $eOperation['debit'];
			$total[] = $eOperation['total'];
		}

		return [[$debit, $credit, $total], $labels];
	}

	protected function getBankChart(\Collection $cOperation): string {

		\Asset::jsUrl('https://cdn.jsdelivr.net/npm/chart.js');

		[$values, $labels] = $this->getBankValues($cOperation);

		$h = '<div class="analyze-line">';
			$h .= '<canvas '.attr('onrender', 'Analyze.create3Lines(this, '.json_encode($labels).', '.json_encode($values).', '.json_encode([s("Recettes"), s("Dépenses"), s("Solde")]).')').'</canvas>';
		$h .= '</div>';

		return $h;
	}

	protected function getBankTable($cOperation): string {

		$h = '<div class="util-overflow-sm">';

			$h .= '<table class="table-block tr-even tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Mois");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$h .= s("Recettes");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$h .= s("Dépenses");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$h .= s("Solde");
						$h .= '</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

				foreach($cOperation as $eOperation) {
					$h .= '<tr>';
						$h .= '<td>';
							$h .= \util\DateUi::textual($eOperation['month'].'-01', \util\DateUi::MONTH_YEAR);
						$h .= '</td>';
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money($eOperation['debit']);
						$h .= '</td>';
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money(abs($eOperation['credit']));
						$h .= '</td>';
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money($eOperation['total']);
						$h .= '</td>';
					$h .= '</tr>';
				}

				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;
	}

	public static function getChargesTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

		$h .= '<h1>';
			$h .= s("Analyse des charges");
		$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getCharges(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, \Collection $cOperation, \Collection $cAccount): string {

		if($cOperation->empty() === TRUE) {

			$h = '<div class="util-info">';
				$h .= s("Le suivi des charges sera disponible lorsque vous aurez attribué des écritures à vos opérations bancaires pour cet exercice.");
			$h .= '</div>';

			return $h;
		}

		$h = '<div class="analyze-chart-table">';
			$h .= $this->getChargesChart($cOperation, $cAccount);
			$h .= $this->getChargesTable($cOperation, $cAccount);
		$h .= '</div>';

		return $h;
	}

	protected function getChargesChart(\Collection $cOperation, \Collection $cAccount): string {

		\Asset::jsUrl('https://cdn.jsdelivr.net/npm/chart.js');

		$total = array_reduce($cOperation->getArrayCopy(), function ($sum, $element) {
			$sum += $element['total'];
			return $sum;
		});

		$values = [];
		$labels = [];
		foreach($cAccount as $eAccount) {
			if($cOperation->offsetExists($eAccount['class']) === FALSE) {
				continue;
			}
			$values[] = 100 * ($cOperation->offsetGet($eAccount['class'])['total'] / $total);
			$labels[] = $this->formatAccountLabel($eAccount);
		}

		$h = '<div class="analyze-pie-canvas">';
			$h .= '<canvas '.attr('onrender', 'Analyze.createPie(this, '.json_encode($values).', '.json_encode($labels).')').'</canvas>';
		$h .= '</div>';

		return $h;
	}

	protected  function formatAccountLabel(\accounting\Account $eAccount): string {
		return encode(mb_ucfirst(mb_strtolower($eAccount['description'])));
	}

	protected function getChargesTable(\Collection $cOperation, \Collection $cAccount): string {

		$h = '<div class="util-overflow-sm">';

			$h .= '<table class="table-block tr-even tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Compte");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$h .= s("Montant");
						$h .= '</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

				foreach($cAccount as $eAccount) {
					$h .= '<tr>';
						$h .= '<td>';
							$h .= $this->formatAccountLabel($eAccount);
						$h .= '</td>';
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money($cOperation[$eAccount['class']]['total'] ?? 0);
						$h .= '</td>';
					$h .= '</tr>';
				}

				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;
	}

	public static function getResultTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

		$h .= '<h1>';
			$h .= s("Résultat");
		$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getResult(array $result, \Collection $cAccount): string {

		if(empty($result) === TRUE) {

			$h = '<div class="util-info">';
				$h .= s("Le compte de résultat sera disponible lorsque vous aurez créé des écritures pour cet exercice.");
			$h .= '</div>';

			return $h;
		}

		$h = '<h2>'.s("Compte de résultat").'</h2>';

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="table-block tr-even td-vertical-top tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>'.s("Comptes").'</th>';
						$h .= '<th>'.s("Libellé").'</th>';
						$h .= '<th>'.s("Montant").'</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$debit = 0;
				$credit = 0;
				$total = 0;
				$class = NULL;
				$h .= '<tbody>';

					foreach($result as $accountClass => $line) {

						if($class !== NULL and $class !== mb_substr($accountClass, 0, 1)) {

							$h .= $this->getResultSubTotalLine($class, $debit, $credit);
							$debit = 0;
							$credit = 0;

						}

						$class = mb_substr($accountClass, 0, 1);

						$h .= '<tr>';
							$h .= '<td>'.encode($accountClass).'</td>';
							$h .= '<td>'.encode($cAccount->offsetGet($accountClass)['description']).'</td>';
							$h .= '<td class="text-end">'.number_format(abs($line['credit'] - $line['debit']), thousands_separator: ' ').'</td>';
						$h .= '</tr>';

						$debit += $line['debit'];
						$credit += $line['credit'];

						$total += $line['credit'];
						$total -= $line['debit'];

					}

					$h .= $this->getResultSubTotalLine($class, $debit, $credit);
					$h .= $this->getResultTotalLine($total);

				$h .= '</tbody>';

			$h .= '</table>';

		$h .= '</div>';
		//d($result, $cAccount);
		return $h;

	}

	private function getResultTotalLine(float $total): string {

		$h = '<tr class="row-highlight row-bold">';
			$h .= '<td>12</td>';
			$h .= '<td class="text-end">'.s("Résultat (Produits - Charges)").'</td>';
			$h .= '<td class="text-end">'.number_format($total, thousands_separator: ' ').'</td>';
		$h .= '</tr>';

		return $h;

	}

	private function getResultSubTotalLine(string $class, float $debit, float $credit): string {

		$h = '<tr class="row-highlight row-bold">';
			$h .= '<td></td>';
			$h .= '<td class="text-end">'.match((int)$class) {
				\Setting::get('accounting\chargeAccountClass') => s("Total Charges"),
				\Setting::get('accounting\productAccountClass') => s("Total Produits")
				}.'</td>';
			$h .= '<td class="text-end">'.number_format(abs($credit - $debit), thousands_separator: ' ').'</td>';
		$h .= '</tr>';

		return $h;

	}

	public function getResultByMonth(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, \Collection $cOperation): string {

		if($cOperation->empty() === TRUE) {

			$h = '<div class="util-info">';
				$h .= s("Le suivi du résultat sera disponible lorsque vous aurez créé des écritures pour cet exercice.");
			$h .= '</div>';

			return $h;
		}

		$h = '<h2>'.s("Le résultat mois par mois").'</h2>';
		$h .= '<div class="analyze-chart-table">';
			$h .= $this->getResultChart($eFinancialYear, $cOperation);
			$h .= $this->getResultTable($eFinancialYear, $cOperation);
		$h .= '</div>';

		return $h;
	}


	protected function getResultChart(\accounting\FinancialYear $eFinancialYear, \Collection $cOperation): string {

		\Asset::jsUrl('https://cdn.jsdelivr.net/npm/chart.js');

		$charges = [];
		$products = [];
		$labels = [];
		for(
			$date = date('Y-m', strtotime($eFinancialYear['startDate']));
			$date <= $eFinancialYear['endDate'];
			$date = date("Y-m", strtotime("+1 month", strtotime($date)))
		) {
			$eOperation = $cOperation->offsetExists($date) ? $cOperation->offsetGet($date) : new Operation();
			$labels[] = \util\DateUi::textual($date.'-01', \util\DateUi::MONTH_YEAR);
			$charges[] = $eOperation['charge'] ?? 0;
			$products[] = $eOperation['product'] ?? 0;
		}

		$h = '<div class="analyze-bar">';
		$h .= '<canvas '.attr('onrender', 'Analyze.createDoubleBar(this, "'.s("Charges").'", '.json_encode($charges).', "'.s("Produits").'", '.json_encode($products).', '.json_encode($labels).')').'</canvas>';
		$h .= '</div>';

		return $h;
	}

	protected function getResultTable(\accounting\FinancialYear $eFinancialYear, \Collection $cOperation): string {

		$h = '<div class="util-overflow-sm">';

			$h .= '<table class="table-block tr-even tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Mois");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$h .= s("Produits");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$h .= s("Charges");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$h .= s("Resultat");
						$h .= '</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

				for(
					$date = date('Y-m', strtotime($eFinancialYear['startDate']));
					$date <= $eFinancialYear['endDate'];
					$date = date("Y-m", strtotime("+1 month", strtotime($date)))
				) {
					$eOperation = $cOperation->offsetExists($date) ? $cOperation->offsetGet($date) : new Operation();
					$h .= '<tr>';
						$h .= '<td>';
							$h .= \util\DateUi::textual($date.'-01', \util\DateUi::MONTH_YEAR);
						$h .= '</td>';
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money($eOperation['product'] ?? 0);
						$h .= '</td>';
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money($eOperation['charge'] ?? 0);
						$h .= '</td>';
						$h .= '<td class="text-end">';
							$h .= \util\TextUi::money(($eOperation['product'] ?? 0) - ($eOperation['charge'] ?? 0));
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
