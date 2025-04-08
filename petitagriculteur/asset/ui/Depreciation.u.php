<?php
namespace asset;

Class DepreciationUi {

	public function __construct() {
		\Asset::js('asset', 'asset.js');
	}

	public static function getTitle(): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Amortissements");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	private static function getDepreciationLine(\company\Company $eCompany, array $depreciation): string {

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

		if(GET('id', 'int') === $depreciation['id']) {
			$class .= 'row-highlight';
		}

		$link = \company\CompanyUi::urlAsset($eCompany).'/depreciation:view?id='.$depreciation['id'];
		$h = '<tr name="asset-'.$depreciation['id'].'" class="'.$class.'">';
			$h .= '<td><a href="'.$link.'">'.encode($depreciation['description']).'</a></td>';
			$h .= '<td><a href="'.$link.'">'.encode($depreciation['id']).'</a></td>';
			$h .= '<td>';
				if($depreciation['acquisitionDate'] !== NULL) {
					$h .= \util\DateUi::numeric($depreciation['acquisitionDate'], \util\DateUi::DATE);
				}
			$h .= '</td>';
			$h .= '<td class="td-min-content">';
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
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['excess']['reversal'], $default, 2).'</td>';
			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['excess']['endFinancialYearValue'], $default, 2).'</td>';

			$h .= '<td class="util-unit text-end">'.new AssetUi()->number($depreciation['fiscalNetValue'], $default, 2).'</td>';

		$h .= '</tr>';

		return $h;
	}

	public static function addTotalLine(array &$total, array $line): void {

		$total['acquisitionValue'] += $line['acquisitionValue'];
		$total['economic']['startFinancialYearValue'] += $line['economic']['startFinancialYearValue'];
		$total['economic']['currentFinancialYearDepreciation'] += $line['economic']['currentFinancialYearDepreciation'];
		$total['economic']['financialYearDiminution'] += $line['economic']['financialYearDiminution'];
		$total['economic']['endFinancialYearValue'] += $line['economic']['endFinancialYearValue'];
		$total['grossValueDiminution'] += $line['grossValueDiminution'];
		$total['netFinancialValue'] += $line['netFinancialValue'];
		$total['excess']['startFinancialYearValue'] += $line['excess']['startFinancialYearValue'];
		$total['excess']['currentFinancialYearDepreciation'] += $line['excess']['currentFinancialYearDepreciation'];
		$total['excess']['reversal'] += $line['excess']['reversal'];
		$total['excess']['endFinancialYearValue'] += $line['excess']['endFinancialYearValue'];
		$total['fiscalNetValue'] += $line['fiscalNetValue'];

	}

	public static function getDepreciationTable(\company\Company $eCompany, array $depreciations): string {

		$highlightedAssetId = GET('id', 'int');

		$h = '<div class="stick-sm util-overflow-sm">';

			$h .= '<table id="asset-list" class="tr-even td-vertical-top tr-hover table-bordered" '.($highlightedAssetId !== NULL ? ' onrender="DepreciationList.scrollTo('.$highlightedAssetId.');"' : '').'>';

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
					$h .= '<th colspan="2" class="text-center border-bottom">'.s("Mode E/F et durée").'</th>';
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
					'reversal' => 0,
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

						$h .= self::getDepreciationLine($eCompany, $total);
						self::addTotalLine($generalTotal, $total);
						$total = $emptyLine;

					}
					$currentAccountLabel = $depreciation['accountLabel'];
					$total['description'] = $depreciation['accountLabel'].' '.$depreciation['accountDescription'];

					$h .= self::getDepreciationLine($eCompany, $depreciation);
					self::addTotalLine($total, $depreciation);

				}
				self::addTotalLine($generalTotal, $total);
				$h .= self::getDepreciationLine($eCompany, $total);
				$h .= self::getDepreciationLine($eCompany, $generalTotal);

			$h .= '</tbody>';

			$h .= '</table>';

		$h .= '</div>';

		return $h;


	}

	public static function viewAsset(\company\Company $eCompany, Asset $eAsset): \Panel {

		$h = '<div class="util-block stick-xs bg-background-light">';
			$h .= '<dl class="util-presentation util-presentation-2">';
				$h .= '<dt>'.s("Numéro").'</dt>';
				$h .= '<dd>'.$eAsset['id'].'</dd>';
				$h .= '<dt>'.s("Date d'acquisition").'</dt>';
				$h .= '<dd>'.\util\DateUi::numeric($eAsset['acquisitionDate'], \util\DateUi::DATE).'</dd>';
				$h .= '<dt>'.s("N° compte").'</dt>';
				$h .= '<dd>'.encode($eAsset['accountLabel']).'</dd>';
				$h .= '<dt>'.s("Libellé").'</dt>';
				$h .= '<dd>'.encode($eAsset['description']).'</dd>';
				$h .= '<dt>'.s("Type").'</dt>';
				$h .= '<dd>'.AssetUi::p('type')->values[$eAsset['type']].'</dd>';
				$h .= '<dt>'.s("Valeur d'achat").'</dt>';
				$h .= '<dd>'.\util\TextUi::money($eAsset['value']).'</dd>';
			$h .= '</dl>';
		$h .= '</div>';

		$h .= '<div>';
			//$h .= '<a href="'.\company\CompanyUi::urlAsset($eCompany).'/depreciation:out" class="btn btn-primary">'.\Asset::icon('box-arrow-right').' '.s("Céder l'immobiliation").'</a>';
		$h .= '</div>';

		$h .= '<div id="depreciation-out-form">';
		$h .= '</div>';

		$h .= '<h2>'.s("Amortissements").'</h2>';

		if($eAsset['depreciations']->empty()) {

			$h .= '<div class="util-info">';
				$h .= s("Il n'y a pas encore eu d'amortissement enregistré pour cette immobilisation.");
			$h .= '</div>';

		} else {

			$h .= '<div class="stick-sm util-overflow-sm">';

				$h .= '<table class="tr-even td-vertical-top tr-hover table-bordered">';

					$h .= '<thead class="thead-sticky">';
						$h .= '<tr>';
							$h .= '<th>'.s("Date").'</th>';
							$h .= '<th>'.s("Type").'</th>';
							$h .= '<th>'.s("Montant").'</th>';
							$h .= '<th>'.s("Exercice comptable").'</th>';
						$h .= '</tr>';
					$h .= '</thead>';

					$h .= '<tbody>';

						foreach($eAsset['depreciations'] as $eDepreciation) {

							$h .= '<tr>';
								$h .= '<td>'.\util\DateUi::numeric($eDepreciation['date'], \util\DateUi::DATE).'</td>';
								$h .= '<td>'.DepreciationUi::p('type')->values[$eDepreciation['type']].'</td>';
								$h .= '<td>'.\util\TextUi::money($eDepreciation['amount']).'</td>';
								$h .= '<td>'.\accounting\FinancialYearUi::getYear($eDepreciation['financialYear']).'</td>';
							$h .= '</tr>';

						}

					$h .= '</tbody>';

				$h .= '</table>';

			$h .= '</div>';

		}

		return new \Panel(
			id: 'panel-asset-view',
			title: s("Immobilisation #{id}", ['id' => $eAsset['id']]),
			body: $h
		);

	}
	public static function p(string $property): \PropertyDescriber {

		$d = \journal\Operation::model()->describer($property, [
			'asset' => s("Immobilisation"),
			'amount' => s("Montant (HT)"),
			'type' => s("Type d'amortissement"),
			'date' => s("Date"),
			'financialYear' => s("Exercice comptable"),
		]);

		switch($property) {

			case 'type':
				$d->values = [
					DepreciationElement::EXCESS => s("Dérogatoire"),
					DepreciationElement::ECONOMIC => s("Économique"),
				];
				break;

		}

		return $d;

	}
}

?>
