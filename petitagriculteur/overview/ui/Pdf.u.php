<?php
namespace overview;

class PdfUi {

	public function __construct() {

		\Asset::css('overview', 'pdf.css');

	}

	public function getHeader(\accounting\FinancialYear $eFinancialYear): string {

		$h = '<div style="display: grid; grid-column-gap: 1rem; grid-template-columns: 1fr 3fr 1fr; overflow: hidden; margin: 1cm; border-radius: 0.5cm; border: 1px solid black; padding: 0.5rem;  background-color: #F5F7F5FF; position: fixed; width: 19cm; height: 120px; font-size: 12px; align-content: center;">';

			$h .= '<div class="pdf-document-header-logo">';
			$h .= '</div>';

			$h .= '<div style="align-content: center;">';
				$h .= '<h2 class="pdf-document-title">'.\s("Bilan comptable").'</h2>';
			$h .= '</div>';

			$h .= '<div class="pdf-document-header-details">';

				$h .= '<table style="margin: 0;">';
					$h .= '<tr>';
						$h .= '<td style="text-align: end">'.s("Devise").'</td>';
						$h .= '<td style="background-color: white; border: 1px solid black; text-align: center">'.s("EURO").'</td>';
					$h .= '</tr>';
					$h .= '<tr>';
						$h .= '<td></td>';
						$h .= '<td style="text-align: center; font-weight: bold;">'.s("EXERCICE").'</td>';
					$h .= '</tr>';
					$h .= '<tr>';
						$h .= '<td style="text-align: end">'.s("Du").'</td>';
						$h .= '<td style="background-color: white; border: 1px solid black; text-align: center">'.\util\DateUi::numeric($eFinancialYear['startDate'], \util\DateUi::DATE).'</td>';
					$h .= '</tr>';
					$h .= '<tr>';
						$h .= '<td></td>';
						$h .= '<td></td>';
					$h .= '</tr>';
					$h .= '<tr>';
						$h .= '<td style="text-align: end">'.s("Au").'</td>';
						$h .= '<td style="background-color: white; border: 1px solid black; text-align: center">'.\util\DateUi::numeric($eFinancialYear['endDate'], \util\DateUi::DATE).'</td>';
					$h .= '</tr>';
				$h .= '</table>';

			$h .= '</div>';
		$h .= '</div>';
		return $h;
	}

	public function getFooter(): string {

		$h = '<div style="width: 19cm; font-size: 12px; display: grid; grid-template-columns: 1fr 1fr; margin: 0 1cm;">';
			$h .= '<span class="date" style="align-content: flex-start"></span>';
			$h .= '<span class="pageNumber" style="align-content: flex-end"></span>';
		$h .= '</div>';

		return $h;
	}

	public function getSummarizedBalance(array $balance): string {

		$h = '<style>@page {	size: A4; margin: calc(var(--margin-bloc-height) + 1cm) 1cm 1cm; }</style>';

		$h .= '<div class="pdf-document-wrapper">';

			$h .= '<div class="pdf-document-content">';

				$h .= '<table id="balance-assets" class="tr-even tr-hover table-bordered">';

					$h .= '<thead class="thead-sticky">';
						$h .= '<tr class="row-header row-upper">';
						$h .= '<td class="text-center">'.s("ACTIF").'</td>';
						$h .= '<td class="text-center">'.s("Brut").'</td>';
						$h .= '<td class="text-center">'.s("Amort prov.").'</td>';
						$h .= '<td class="text-center">'.s("Net").'</td>';
						$h .= '<td class="text-center">'.s("% actif").'</td>';
					$h .= '</tr>';

					$h .= new BalanceUi()->displaySubCategoryBody($balance['asset'], s("Total de l'actif"));

				$h .= '</table>';

				$h .= '<table id="balance-liabilities" class="table-sticky tr-even tr-hover table-bordered">';

					$h .= '<thead class="thead-sticky">';

						$h .= '<tr class="row-header row-upper">';
						$h .= '<td class="text-center">'.s("PASSIF").'</td>';
						$h .= '<td class="text-center">'.s("Brut").'</td>';
						$h .= '<td class="text-center">'.s("Amort prov.").'</td>';
						$h .= '<td class="text-center">'.s("Net").'</td>';
						$h .= '<td class="text-center">'.s("% passif").'</td>';
					$h .= '</tr>';

					$h .= new BalanceUi()->displaySubCategoryBody($balance['liability'], s("Total du passif"));

				$h .= '</table>';

			$h .= '</div>';

		$h .= '</div>';

		return $h;
	}

}

?>
