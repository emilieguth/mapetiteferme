<?php
namespace asset;

Class DepreciationUi {

	public static function getTitle(): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Amortissements");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	private static function getDepreciationLine(array $depreciation): string {

		$isTotalLine = match($depreciation['type']) {
			AssetElement::LINEAR, AssetElement::WITHOUT => FALSE,
			default => TRUE,
		};

		if($isTotalLine === TRUE) {

			$class = 'row-header';
			$default = '0.00';

		} else {

			$class = '';
			$default = '';

		}

		$h = '<tr class="'.$class.'">';
			$h .= '<td>'.encode($depreciation['description']).'</td>';
			$h .= '<td>'.encode($depreciation['id']).'</td>';
			$h .= '<td>';
				if($depreciation['acquisitionDate'] !== NULL) {
					$h .= \util\DateUi::numeric($depreciation['acquisitionDate'], \util\DateUi::DATE);
				}
			$h .= '</td>';
			$h .= '<td>';
				$h .= match($depreciation['type']) {
					AssetElement::LINEAR => 'L/L',
					AssetElement::WITHOUT => 'S/S',
					default => '',
				};
			$h .= '</td>';
			$h .= '<td>'.encode($depreciation['duration']).'</td>';

			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['acquisitionValue'], $default, 2) .'</td>';

			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['economic']['startFinancialYearValue'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['economic']['currentFinancialYearDepreciation'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['economic']['financialYearDiminution'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['economic']['endFinancialYearValue'], $default, 2).'</td>';

			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['grossValueDiminution'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['netFinancialValue'], $default, 2).'</td>';

			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['excess']['startFinancialYearValue'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['excess']['currentFinancialYearDepreciation'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['excess']['financialYearDiminution'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['excess']['endFinancialYearValue'], $default, 2).'</td>';

			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['fiscalNetValue'], $default, 2).'</td>';

		$h .= '</tr>';

		return $h;
	}

	private static function addTotalLine(array &$total, array $line): void {

		$total['acquisitionValue'] += $line['acquisitionValue'];
		$total['economic']['startFinancialYearValue'] += $line['economic']['startFinancialYearValue'];
		$total['economic']['currentFinancialYearDepreciation'] += $line['economic']['currentFinancialYearDepreciation'];
		$total['economic']['financialYearDiminution'] += $line['economic']['financialYearDiminution'];
		$total['economic']['endFinancialYearValue'] += $line['economic']['endFinancialYearValue'];
		$total['grossValueDiminution'] += $line['grossValueDiminution'];
		$total['netFinancialValue'] += $line['netFinancialValue'];
		$total['excess']['startFinancialYearValue'] += $line['excess']['startFinancialYearValue'];
		$total['excess']['currentFinancialYearDepreciation'] += $line['excess']['currentFinancialYearDepreciation'];
		$total['excess']['financialYearDiminution'] += $line['excess']['financialYearDiminution'];
		$total['excess']['endFinancialYearValue'] += $line['excess']['endFinancialYearValue'];
		$total['fiscalNetValue'] += $line['fiscalNetValue'];

	}

	public static function getDepreciationTable(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, array $depreciations): string{

		$h = '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="tr-even td-vertical-top tr-hover table-bordered">';

			$h .= '<thead class="thead-sticky">';
				$h .= '<tr class="row-bold">';
					$h .= '<th colspan="5" class="text-center">'.s("Caractéristiques").'</th>';
					$h .= '<th rowspan="2" class="text-center">'.s("Valeur acquisition").'</th>';
					$h .= '<th colspan="4" class="text-center">'.s("Amortissements économiques").'</th>';
					$h .= '<th rowspan="2" class="text-center">'.s("Dimin. de val. brut.").'</th>';
					$h .= '<th rowspan="2" class="text-center">'.s("VNC fin").'</th>';
					$h .= '<th colspan="4" class="text-center">'.s("Amortissements dérogatoires").'</th>';
					$h .= '<th rowspan="2" class="text-center">'.s("VNF fin").'</th>';
				$h .= '</tr>';
				$h .= '<tr>';
					$h .= '<th class="text-center">'.s("Libellé").'</th>';
					$h .= '<th class="text-center">'.s("Ordre").'</th>';
					$h .= '<th class="text-center">'.s("Date").'</th>';
					$h .= '<th colspan="2" class="text-center">'.s("Mode E/F et durée").'</th>';
					$h .= '<th class="text-center">'.s("Début exercice").'</th>';
					$h .= '<th class="text-center">'.s("Dotation exercice").'</th>';
					$h .= '<th class="text-center">'.s("Diminution exercice").'</th>';
					$h .= '<th class="text-center">'.s("Fin exercice").'</th>';
					$h .= '<th class="text-center">'.s("Début exercice").'</th>';
					$h .= '<th class="text-center">'.s("Dotation exercice").'</th>';
					$h .= '<th class="text-center">'.s("Diminution exercice").'</th>';
					$h .= '<th class="text-center">'.s("Fin exercice").'</th>';
				$h .= '</tr>';
			$h .= '</thead>';

			$emptyLine = [
				'description' => '',
				'id' => '',
				'acquisitionDate' => NULL,
				'type' => '',
				'duration' => '',
				'acquisitionValue' => 0,
				'economic' => [
					'startFinancialYearValue' => 0,
					'currentFinancialYearDepreciation' => 0,
					'financialYearDiminution' => 0,
					'endFinancialYearValue' => 0,
				],
				'grossValueDiminution' => 0,
				'netFinancialValue' => 0,
				'excess' => [
					'startFinancialYearValue' => 0,
					'currentFinancialYearDepreciation' => 0,
					'financialYearDiminution' => 0,
					'endFinancialYearValue' => 0,
				],
				'fiscalNetValue' => 0,
			];
			$total = $emptyLine;
			$generalTotal = $emptyLine;
			$generalTotal['description'] = s("Totaux");

			$currentAccountLabel = NULL;

			$h .= '<tbody>';

				foreach($depreciations as $depreciation) {

					if($currentAccountLabel !== NULL and $depreciation['accountLabel'] !== $currentAccountLabel) {

						$h .= self::getDepreciationLine($total);
						self::addTotalLine($generalTotal, $total);
						$total = $emptyLine;

					}
					$currentAccountLabel = $depreciation['accountLabel'];
					$total['description'] = $depreciation['accountLabel'].' '.$depreciation['accountDescription'];

					$h .= self::getDepreciationLine($depreciation);
					self::addTotalLine($total, $depreciation);

				}
				self::addTotalLine($generalTotal, $total);
				$h .= self::getDepreciationLine($total);
				$h .= self::getDepreciationLine($generalTotal);

			$h .= '</tbody>';

			$h .= '</table>';

		$h .= '</div>';

		return $h;


	}

}

?>
