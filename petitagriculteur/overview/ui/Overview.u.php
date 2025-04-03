<?php
namespace overview;

class OverviewUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getOverviewTitle(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= \s("Les bilans");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getBalanceTitle(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Les bilans");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a href="'.PdfUi::urlBalance($eCompany, $eFinancialYear).'" data-ajax-navigation="never" class="btn btn-transparent">'.\Asset::icon('download').'&nbsp;'.s("Télécharger en PDF").'</a>';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getAccountingTitle(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Les balances");
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

}
?>
