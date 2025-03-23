<?php
namespace overview;

class BalanceUi {

	public function extractLabelsFromCategories(array $categories): array {

		$accountLabels = [];
		foreach($categories as $subcategories) {
			foreach(array_column($subcategories['categories'], 'accounts') as $labelsList) {
				$accountLabels = array_merge($accountLabels, $labelsList);
			}
		}

		return $accountLabels;
	}

	private function displayLine(string $text, float $value, float $amort, float $net, ?float $total): string {

		$h = '<td>'.($total === NULL ? '<span class="ml-1">'.$text.'</span>' : $text).'</td>';
		$h .= '<td class="text-end">'.(new OverviewUi()->number($value, valueIfEmpty: '', decimals: 0)).'</td>';
		$h .= '<td class="text-end">'.(new OverviewUi()->number(abs($amort), valueIfEmpty: '', decimals: 0)).'</td>';
		$h .= '<td class="text-end">'.(new OverviewUi()->number($net, valueIfEmpty: '', decimals: 0)).'</td>';
		$h .= '<td class="text-center">'.($total === NULL ? '' : (new OverviewUi()->number(round($net / $total * 100), valueIfEmpty: '', decimals: 0))).'</td>';

		return $h;

	}

	private function displaySubCategoryBody(array $categories, array $balance, string $totalText): string {

		$totalValue = 0;
		$totalAmort = 0;
		$totalNet = 0;

		$allLabels = $this->extractLabelsFromCategories($categories);
		foreach($balance as $balanceLine) {

			if(in_array((int)$balanceLine['accountPrefix'], $allLabels) === FALSE) {
				continue;
			}

			if(mb_substr($balanceLine['accountPrefix'], 1, 1) === '8') {
				$totalAmort += $balanceLine['amount'];
			} else {
				$totalValue += $balanceLine['amount'];
			}

		}

		$totalNet += $totalValue - $totalAmort;
		
		$h = '<tbody>';

			foreach($categories as $subCategories) {
				$name = $subCategories['name'];
				$categories = $subCategories['categories'];

				$totalCategoryValue = 0;
				$totalCategoryAmort = 0;
				$totalCategoryNet = 0;

				foreach($categories as $categoryDetails) {

					$categoryName = $categoryDetails['name'];
					$accounts = $categoryDetails['accounts'];

					$totalSubCategoryValue = 0;
					$totalSubCategoryAmort = 0;
					$totalSubCategoryNet = 0;
					foreach($accounts as $account) {

						$value = $balance[$account]['amount'] ?? 0;
						$accountAmort = mb_substr($account, 0, 1).'8'.mb_substr($account, 1);
						$valueAmort = $balance[$accountAmort]['amount'] ?? 0;
						$net = $value + $valueAmort;

						$totalSubCategoryValue += $value;
						$totalSubCategoryAmort += $valueAmort;
						$totalSubCategoryNet += $net;

						$h .= '<tr>';
							$h .= $this->displayLine(\accounting\AccountUi::getLabelByAccount($account), $value, $valueAmort, $net, null);
						$h .= '<tr>';

					}

					$h .= '<tr class="row-bold">';
						$h .= $this->displayLine($categoryName, $totalSubCategoryValue, $totalSubCategoryAmort, $totalSubCategoryNet, $totalNet);
					$h .= '</tr>';

					$totalCategoryValue += $totalSubCategoryValue;
					$totalCategoryAmort += $totalSubCategoryAmort;
					$totalCategoryNet += $totalSubCategoryNet;
				}

				$h .= '<tr class="row-upper row-emphasis row-bold">';
					$h .= $this->displayLine(s("Total {category}", ['category' => $name]), $totalCategoryValue, $totalCategoryAmort, $totalCategoryNet, $totalNet);
				$h .= '</tr>';


			}

			$h .= '<tr class="row-upper row-emphasis row-bold">';
				$h .= $this->displayLine($totalText, $totalValue, $totalAmort, $totalNet, $totalNet);
			$h .= '</tr>';

		$h .= '</tbody>';

		return $h;

	}

	public function displaySummarizedBalance(array $balance): string {

		if(empty($balance) === TRUE) {
			return '<div class="util-info">'.\s("Il n'y a rien à afficher pour le moment.").'</div>';
		}


		$h = '<h2>'.\s("Bilan comptable").'</h2>';
		$h .= '<div class="util-overflow-sm">';

			$balanceAssetCategories = \Setting::get('accounting\balanceAssetCategories');
			$h .= '<table id="balance-assets" class="tr-even tr-hover table-bordered">';

				$h .= '<thead class="thead-sticky">';

					$h .= '<tr class="row-header row-upper">';
						$h .= '<td class="text-center">'.s("ACTIF").'</td>';
						$h .= '<td class="text-center">'.s("Brut").'</td>';
						$h .= '<td class="text-center">'.s("Amort prov.").'</td>';
						$h .= '<td class="text-center">'.s("Net").'</td>';
						$h .= '<td class="text-center">'.s("% actif").'</td>';
					$h .= '</tr>';

					$h .= $this->displaySubCategoryBody($balanceAssetCategories, $balance, s("Total de l'actif"));

			$h .= '</table>';

			$balanceLiabilityCategories = \Setting::get('accounting\balanceLiabilityCategories');
			$h .= '<table id="balance-liabilities" class="table-sticky tr-even tr-hover table-bordered">';

				$h .= '<thead class="thead-sticky">';

					$h .= '<tr class="row-header row-upper">';
						$h .= '<td class="text-center">'.s("PASSIF").'</td>';
						$h .= '<td class="text-center">'.s("Brut").'</td>';
						$h .= '<td class="text-center">'.s("Amort prov.").'</td>';
						$h .= '<td class="text-center">'.s("Net").'</td>';
						$h .= '<td class="text-center">'.s("% passif").'</td>';
					$h .= '</tr>';

					$h .= $this->displaySubCategoryBody($balanceLiabilityCategories, $balance, s("Total du passif"));

			$h .= '</table>';

		$h .= '</div>';

		return $h;
	}

}

?>
