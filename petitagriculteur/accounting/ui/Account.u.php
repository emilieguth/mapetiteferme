<?php
namespace accounting;

class AccountUi {

	public function __construct() {
	}

	public function getManageTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

		$h .= '<h1>';
			$h .= '<a href="'.\company\CompanyUi::urlSettings($eCompany).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
			$h .= s("Les classes de comptes");
		$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getManage(\company\Company $eCompany, \Collection $cAccount): string {

		if($cAccount->empty() === TRUE) {
			return '<div class="util-info">'.s("Aucun compte n'a encore été enregistré").'</div>';
		}
		\Asset::js('main', 'settings.js');

		$h = '<div class="util-overflow-sm">';

			$h .= '<table id="account-list" class="table-block tr-even tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Classe");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Libellé");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Compte de TVA");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Taux de TVA");
						$h .= '</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

				foreach($cAccount as $eAccount) {

					$classNumber = strlen($eAccount['class']) - 2;

					$h .= '<tr name="account-'.$eAccount['id'].'">';

						$h .= '<td>';
							$h .= '<span class="ml-'.$classNumber.'">';
								$h .= $classNumber === 0 ? '<b>' : '';
									$h .= encode($eAccount['class']);
								$h .= $classNumber === 0 ? '</b>' : '';
							$h .= '</span>';
						$h .= '</td>';

						$h .= '<td>';
							$h .= '<span class="ml-'.$classNumber.'">';
								$h .= $classNumber === 0 ? '<b>' : '';
									$h .= encode($eAccount['description']).'</span>';
								$h .= $classNumber === 0 ? '</b>' : '';
						$h .= '</td>';

						$h .= '<td>';
							$h .= ($eAccount['vatAccount']->exists() === TRUE ? '<a '.attr('onclick', 'Settings.scrollTo('.$eAccount['vatAccount']['id'].');').'>'.encode($eAccount['vatAccount']['class']).'</a>' : '');
						$h .= '</td>';

						$h .= '<td>';
							if($eAccount['vatAccount']->exists() === TRUE) {
								$h .= encode($eAccount['vatAccount']['vatRate']).'%';
							} else {
								$h .= $eAccount['vatRate'] ? $eAccount['vatRate'].'%' : '';
							}
						$h .= '</td>';

					$h .= '</tr>';
				}

				$h .= '<tbody>';
			$h .= '</table';

		$h .= '</div>';

		return $h;

	}

	public static function getAutocomplete(int $company, Account $eAccount): array {

		\Asset::css('media', 'media.css');

		$vatRate = 0.0;
		if($eAccount['vatRate'] !== NULL) {
			$vatRate = $eAccount['vatRate'];
		} elseif($eAccount['vatAccount']->exists() === TRUE) {
			$vatRate = $eAccount['vatAccount']['vatRate'];
		}

		return [
			'value' => $eAccount['id'],
			'class' => encode($eAccount['class']),
			'vatRate' => $vatRate,
			'company' => $company,
			'itemHtml' => encode($eAccount['class'].' '.$eAccount['description']),
			'itemText' => encode($eAccount['class'].' '.$eAccount['description'])
		];

	}


	public function query(\PropertyDescriber $d, int $company, bool $multiple = FALSE) {

		$d->prepend = \Asset::icon('journal-text');
		$d->field = 'autocomplete';

		$d->placeholder ??= s("Commencez à saisir la classe...");
		$d->multiple = $multiple;
		$d->group += ['wrapper' => 'account'];

		$d->autocompleteUrl = \company\CompanyUi::urlAccounting($company).'/account:query';
		$d->autocompleteResults = function(Account $e) use ($company) {
			return self::getAutocomplete($company, $e);
		};

	}

}

?>
