<?php
namespace journal;

class StatementUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getStatementTitle(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear): string {

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
				$h .= \s("Les balances");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

}
?>
