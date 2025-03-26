<?php
namespace journal;

Class AssetUi {

	public static function getTitle(): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("État des immobilisations");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;
	}

	public function number(mixed $number, ?string $valueIfEmpty, ?int $decimals = NULL): string {

		if(is_null($number) === true or $number === 0 or $number === 0.0) {

			if(is_null($valueIfEmpty) === FALSE) {
				return $valueIfEmpty;
			}

			return number_format(0, $decimals ?? 2, '.', ' ');

		}

		return number_format($number, $decimals ?? 2, '.', ' ');

	}

	public static function getSummary(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, array $assetSummary): string {

		$h = '<h1>'.s("Amortissement des immobilisations").'</h1>';

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="tr-even td-vertical-top tr-hover table-bordered">';

				$h .= '<thead class="thead-sticky">';
					$h .= '<tr class="row-bold">';
						$h .= '<th class="text-center">'.s("Caractéristiques").'</th>';
						$h .= '<th colspan="5" class="text-center">'.s("Valeurs brutes").'</th>';
						$h .= '<th colspan="6" class="text-center">'.s("Amortissements économiques").'</th>';
						$h .= '<th rowspan="4" class="text-center">'.s("VNC").'</th>';
						$h .= '<th colspan="4" class="text-center">'.s("Amortissements dérogatoires").'</th>';
						$h .= '<th rowspan="4" class="text-center">'.s("VNF").'</th>';
					$h .= '</tr>';
					$h .= '<tr>';
						$h .= '<th rowspan="3" class="text-center">'.s("Libellé").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Valeur début").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Acquis. ou apport").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Diminution poste à p.").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Sortie d'actif").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Val. fin d'exercice").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Début d'exercice").'</th>';
						$h .= '<th colspan="3" class="text-center">'.s("Augmentation (dotation de l'exercice)").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Diminution").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Fin exercice").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Début exercice").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Dotation").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Reprise").'</th>';
						$h .= '<th rowspan="3" class="text-center">'.s("Fin exercice").'</th>';
					$h .= '</tr>';
					$h .= '<tr>';
						$h .= '<th rowspan="2" class="text-center">'.s("Global").'</th>';
						$h .= '<th colspan="2" class="text-center">'.s("dont :").'</th>';
					$h .= '</tr>';
						$h .= '<th class="text-center">'.s("Linéaire").'</th>';
						$h .= '<th class="text-center">'.s("Dégressif").'</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '</tbody>';
					foreach($assetSummary as $asset) {
						$h .= '<tr>';
							$h .= '<td>'.encode($asset['accountLabel']).'&nbsp;'.encode($asset['description']).'</td>';

							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['grossValue']['startValue'], '', 2) .'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['grossValue']['buyValue'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['grossValue']['decrease'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['grossValue']['out'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['grossValue']['endValue'], '', 2).'</td>';

							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['economic']['startFinancialYear'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['economic']['globalIncrease'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['economic']['linearIncrease'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['economic']['degressiveIncrease'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['economic']['decrease'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['economic']['endFinancialYear'], '', 2).'</td>';

							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['netBookValue'], '', 2).'</td>';

							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['excess']['startFinancialYear'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['excess']['depreciation'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['excess']['reversal'], '', 2).'</td>';
							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['excess']['endFinancialYear'], '', 2).'</td>';

							$h .= '<td class="util-unit text-end">'.new AssetUi()->number($asset['netFinancialValue'], '', 2).'</td>';

						$h .= '</tr>';
					}
				$h .= '<tbody>';

			$h .= '</table>';

		$h .= '</table>';

//d($assetSummary);
		return $h;

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Operation::model()->describer($property, [
			'accountLabel' => s("Compte"),
			'value' => s("Valeur (HT)"),
			'type' => s("Type d'amortissement"),
			'mode' => s("Mode"),
			'acquisitionDate' => s("Date d'acquisition"),
			'startDate' => s("Date de mise en service"),
			'duration' => s("Durée (en années)"),
			'status' => s("Statut"),
			'endDate' => s('Date de fin'),
			'description' => s('Libellé'),
		]);

		switch($property) {

			case 'acquisitionDate' :
			case 'startDate' :
				$d->prepend = \Asset::icon('calendar-date');
				break;

			case 'type':
				$d->values = [
					AssetElement::LINEAR => s("Linéaire"),
					AssetElement::WITHOUT => s("Sans"),
				];
				break;

			case 'status':
				$d->values = [
					AssetElement::ONGOING => s("En cours"),
					AssetElement::SOLD => s("Vendu"),
					AssetElement::ENDED => s("Terminé"),
				];
				break;
		}

		return $d;

	}
}
?>
