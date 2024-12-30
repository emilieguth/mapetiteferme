<?php
namespace bank;

class ImportUi {

	public function __construct() {
		\Asset::css('bank', 'bank.css');
	}

	public function getImportTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

		$h .= '<h1>';
		$h .= s("Les imports de relevés bancaires");
		$h .= '</h1>';

		$h .= '<div>';
		$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#cashflow-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
		$h .= '<a href="'.\company\CompanyUi::urlBank($eCompany).'/import:import" class="btn btn-primary">'.\Asset::icon('file-earmark-plus').' '.s("Importer").'</a>';
		$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getImport(
		\company\Company $eCompany,
		\Collection $cImport,
		\accounting\FinancialYear $eFinancialYearSelected,
	): string {

		if ($cImport->empty() === true) {
			return '<div class="util-info">'.s("Aucun import bancaire n'a encore été réalisé").'</div>';
		}

		$h = '';

		d($cImport);

		return $h;

	}

}
?>