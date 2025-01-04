<?php
namespace bank;

class BankUi {

	public function __construct() {
	}

	public function getBankTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Les transactions bancaires");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#cashflow-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

}
?>