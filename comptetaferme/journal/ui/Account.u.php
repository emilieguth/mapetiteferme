<?php
namespace journal;

class AccountUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getManageTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= '<a href="'.\company\CompanyUi::urlSettings($eCompany).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
				$h .= s("Les comptes");
			$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	public function getManage(\company\Company $eCompany, \Collection $cAccount): string {

		if ($cAccount->empty() === true) {
			return '<div class="util-info">'.s("Aucun compte n'a encore été enregistré").'</div>';
		}

		$h = '';

		if($cAccount->notEmpty()) {

			$h .= '<div class="util-overflow-sm">';

				$h .= '<ul class="list-unstyled">';

					foreach($cAccount as $eAccount) {
						$h .= '<li class="ml-'.(strlen($eAccount['class']) - 2).'">'.$eAccount['class'].'.&nbsp;'.$eAccount['description'].'</li>';
					}

				$h .= '</ul>';

			$h .= '</div>';

		}


		return $h;

	}

	public static function getAutocomplete(Account $eAccount): array {

		\Asset::css('media', 'media.css');

		return [
			'value' => $eAccount['id'],
			'itemHtml' => encode($eAccount['class'].' '.$eAccount['description']),
			'itemText' => encode($eAccount['class'].' '.$eAccount['description'])
		];

	}


	public function query(\PropertyDescriber $d, int $company, bool $multiple = FALSE) {

		$d->prepend = \Asset::icon('person-bounding-box');
		$d->field = 'autocomplete';

		$d->placeholder ??= s("Commencez à saisir la classe...");
		$d->multiple = $multiple;
		$d->group += ['wrapper' => 'customer'];

		$d->autocompleteUrl = \company\CompanyUi::urlJournal($company).'/account:query';
		$d->autocompleteResults = function(Account $e) {
			return self::getAutocomplete($e);
		};

	}

}

?>