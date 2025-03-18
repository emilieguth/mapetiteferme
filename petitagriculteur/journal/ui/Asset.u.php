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

	public static function getSummary(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, \Collection $cAsset): string {

		return '';

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
