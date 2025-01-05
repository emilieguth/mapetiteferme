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
				$h .= s("Analyse");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#cashflow-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getBank(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, \Collection $cOperation): string {

		if ($cOperation->empty() === true) {

			$h = '<div class="util-info">';
				$h .= s("Le suivi de la trésorerie sera disponible lorsque vous aurez attribué des écritures à vos transactions bancaires pour cet exercice.");
			$h .= '</div>';

			return $h;
		}

		$h = '<div class="tabs-h" id="operation-analyze-bank" onrender="'.encode('Lime.Tab.restore(this, "analyze-bank")').'">';

			$h .= '<div class="tabs-item">';
				$h .= '<a class="tab-item selected" data-tab="analyze-bank" onclick="Lime.Tab.select(this)">'.s("Trésorerie").'</a>';
				$h .= '<a class="tab-item" data-tab="analyze-charge" onclick="Lime.Tab.select(this)">'.s("Charges (TODO)").'</a>';
				$h .= '<a class="tab-item" data-tab="analyze-result" onclick="Lime.Tab.select(this)">'.s("Résultat (TODO)").'</a>';
				$h .= '<a class="tab-item" data-tab="analyze-vat" onclick="Lime.Tab.select(this)">'.s("TVA (TODO)").'</a>';
			$h .= '</div>';

			$h .= '<div class="tab-panel" data-tab="analyze-bank">';
				$h .= '<div class="analyze-chart-table">';
					$h .= $this->getBankChart($cOperation);
					$h .= $this->getBankTable($cOperation);
				$h .= '</div>';
			$h .= '</div>';
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
			$total[] = $eOperation['credit'] + $eOperation['debit'];
		}

		return [[$credit, $debit, $total], $labels];
	}

	protected function getBankChart(\Collection $cOperation): string {

		\Asset::jsUrl('https://cdn.jsdelivr.net/npm/chart.js');

		[$values, $labels] = $this->getBankValues($cOperation);

		$h = '<div class="analyze-line">';
			$h .= '<canvas '.attr('onrender', 'Analyze.create3Lines(this, '.json_encode($labels).', '.json_encode($values).', '.json_encode([s("Recettes"), s("Dépenses"), s("Solde")]).')').'</canvas>';
		$h .= '</div>';

		return $h;

		return $h;
	}

	protected function getBankTable($cOperation): string {

		$h = '<div class="util-overflow-sm">';

			$h .= '<table class="tr-bordered tr-even">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Mois");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Recettes");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Dépenses");
						$h .= '</th>';
						$h .= '<th>';
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
						$h .= '<td>';
							$h .= \util\TextUi::money($eOperation['credit']);
						$h .= '</td>';
						$h .= '<td>';
							$h .= \util\TextUi::money(abs($eOperation['debit']));
						$h .= '</td>';
						$h .= '<td>';
							// TODO : ajouter le report du mois précédent ?
							$h .= \util\TextUi::money($eOperation['credit'] + $eOperation['debit']);
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