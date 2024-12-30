<?php
namespace bank;

class BankUi {

	public function __construct() {
		\Asset::css('bank', 'bank.css');
	}

	public function getBankTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Les flux financiers");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#cashflow-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
				$h .= '<a href="'.\company\CompanyUi::urlBank($eCompany).'/import:import" class="btn btn-primary">'.\Asset::icon('file-earmark-plus').' '.s("Importer").'</a>';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

}
?>