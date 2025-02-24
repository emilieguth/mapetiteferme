<?php
namespace bank;

class AccountUi {

	public function __construct() {
	}

	public function getAccountTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

		$h .= '<h1>';
			$h .= s("Les comptes bancaires");
		$h .= '</h1>';

		$h .= '<div>';
			$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#cashflow-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
		$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function list(\company\Company $eCompany ,\Collection $cAccount): string {

		if($cAccount->empty() === TRUE) {
			return '<div class="util-info">'.s("Aucun compte bancaire n'a encore été enregistré. Lorsque vous effectuerez votre premier import de relevé bancaire, le compte bancaire rattaché sera automatiquement créé.").'</div>';
		}

		\Asset::js('util', 'form.js');
		\Asset::css('util', 'form.css');

		$h = '<div class="util-info">'.s("Les comptes bancaires se créent automatiquement lors de l'import d'un relevé. Vous pouvez modifier le libellé du compte").'</div>';
		$h .= '<div class="util-warning">'.s("Attention, en <b>modifiant le libellé d'un compte bancaire</b>, toutes les écritures comptables de l'exercice fiscal en cours qui sont liées à ce compte bancaire verront leur n° de compte être mis à jour avec ce libellé.").'</div>';
		$h .= '<div class="util-overflow-sm">';

			$h .= '<table class="table-block tr-even">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>'.s("N° banque").'</th>';
						$h .= '<th>'.s("N° Compte").'</th>';
						$h .= '<th>'.s("Libellé de compte").'</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

				foreach($cAccount as $eAccount) {

					$h .= '<tr>';

						$h .= '<td>'.encode($eAccount['bankId']).'</td>';
						$h .= '<td>'.encode($eAccount['accountId']).'</td>';
						$h .= '<td>';
								$eAccount->setQuickAttribute('company', $eCompany['id']);
								$h .= $eAccount->quick('label', $eAccount['label'] ? encode($eAccount['label']) : '<i>'.s("Non défini").'</i>');
							$h .= '</div>';
						$h .= '</td>';

					$h .= '</tr>';
				}

				$h .= '<tbody>';
			$h .= '</table';

		$h .= '</div>';

		return $h;

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Account::model()->describer($property, [
			'label' => s("Libellé de compte"),
		]);

		return $d;

	}

}
?>
