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

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#account-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
				$h .= '<a href="'.\company\CompanyUi::urlAccounting($eCompany).'/account:create" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Créer un compte personnalisé").'</a>';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getSearch(\Search $search): string {

		$h = '<div id="account-search" class="util-block-search stick-xs '.($search->empty(['ids']) === TRUE ? 'hide' : '').'">';

			$form = new \util\FormUi();
			$url = LIME_REQUEST_PATH;

			$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

				$h .= '<div>';
					$h .= $form->text('class', $search->get('class'), ['placeholder' => s("Classe de compte")]);
					$h .= $form->text('description', $search->get('description'), ['placeholder' => s("Libellé")]);
					$h .= $form->checkbox('vatFilter', 1, ['checked' => $search->get('vatFilter'), 'callbackLabel' => fn($input) => $input.' '.s("Avec compte de TVA uniquement")]);
					$h .= $form->checkbox('customFilter', 1, ['checked' => $search->get('customFilter'), 'callbackLabel' => fn($input) => $input.' '.s("Personnalisés")]);
				$h .= '</div>';
				$h .= '<div>';
					$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
					$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
				$h .= '</div>';

			$h .= $form->close();

		$h .= '</div>';

		return $h;

	}

	public function getManage(\company\Company $eCompany, \Collection $cAccount): string {

		if($cAccount->empty() === TRUE) {
			return '<div class="util-info">'.s("Aucun compte n'a encore été enregistré").'</div>';
		}
		\Asset::js('main', 'settings.js');

		$h = '<div class="util-overflow-sm">';

			$h .= '<table id="account-list" class="tr-even tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Classe");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Libellé");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Personnalisé ?");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Compte de TVA");
						$h .= '</th>';
						$h .= '<th>';
							$h .= s("Taux de TVA");
						$h .= '</th>';
						$h .= '<th></th>';
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
									if($eAccount['custom'] === TRUE) {
										$eAccount->setQuickAttribute('company', $eCompany['id']);
										$h .= $eAccount->quick('description', encode($eAccount['description']));
									} else {
										$h .= encode($eAccount['description']).'</span>';
									}
								$h .= $classNumber === 0 ? '</b>' : '';
						$h .= '</td>';

						$h .= '<td class="text-center">';
							if($eAccount['custom'] === TRUE) {
								$h .= 'oui';
							}
						$h .= '</td>';

						$h .= '<td>';
							$h .= ($eAccount['vatAccount']->exists() === TRUE ? '<a '.attr('onclick', 'Settings.scrollTo('.$eAccount['vatAccount']['id'].');').'>'.encode($eAccount['vatAccount']['class']).'</a>' : '');
						$h .= '</td>';

						$h .= '<td>';
							if($eAccount['vatAccount']->exists() === TRUE and $eAccount['vatAccount']['vatRate'] !== NULL) {
								$h .= encode($eAccount['vatAccount']['vatRate']).'%';
							} else {
								$h .= $eAccount['vatRate'] !== NULL ? $eAccount['vatRate'].'%' : '';
							}
						$h .= '</td>';
						$h .= '<td>';
							if($eAccount['custom'] === TRUE and $eAccount['nOperation'] === 0) {
								$message = s("Confirmez-vous la suppression de cette classe de compte ?");
								$h .= '<a data-ajax="'.\company\CompanyUi::urlAccounting($eCompany).'/account:doDelete" post-id="'.$eAccount['id'].'" data-confirm="'.$message.'" class="btn btn-outline-secondary btn-outline-danger">'.\Asset::icon('trash').'</a>';
							}
						$h .= '</td>';

					$h .= '</tr>';
				}

				$h .= '<tbody>';
			$h .= '</table>';

		$h .= '</div>';

		return $h;

	}

	public static function getAutocomplete(int $company, Account $eAccount, \Search $search = new \Search()): array {

		\Asset::css('media', 'media.css');

		$vatRate = 0.0;
		if($eAccount['vatRate'] !== NULL) {
			$vatRate = $eAccount['vatRate'];
		} elseif($eAccount['vatAccount']->exists() === TRUE) {
			$vatRate = $eAccount['vatAccount']['vatRate'];
		}

		$itemHtml = encode($eAccount['class'].' '.$eAccount['description']);
		if(
			$search->get('classPrefix')
			and $search->get('classPrefix') === (string)\Setting::get('accounting\vatClass')
			and $eAccount['vatRate'] !== NULL
		) {
			$itemHtml .= ' ('.$eAccount['vatRate'].'%)';
		}

		return [
			'value' => $eAccount['id'],
			'class' => encode($eAccount['class']),
			'vatRate' => $vatRate,
			'company' => $company,
			'itemHtml' => $itemHtml,
			'itemText' => $eAccount['class'].' '.$eAccount['description']
		];

	}

	public function query(\PropertyDescriber $d, int $company, bool $multiple = FALSE, array $query = []): void {

		$d->prepend = \Asset::icon('journal-text');
		$d->field = 'autocomplete';

		$d->placeholder ??= s("Commencez à saisir la classe...");
		$d->multiple = $multiple;
		$d->group += ['wrapper' => 'account'];

		$d->autocompleteUrl = \company\CompanyUi::urlAccounting($company).'/account:query?'.http_build_query($query);
		$d->autocompleteResults = function(Account $e) use ($company) {
			return self::getAutocomplete($company, $e);
		};

	}

	public static function getAutocompleteLabel(string $query, int $company, string $label): array {

		\Asset::css('media', 'media.css');

		return [
			'value' => $label,
			'company' => $company,
			'itemHtml' => str_replace($query, '<b>'.$query.'</b>', $label),
			'itemText' => encode($label),
		];

	}

	public function queryLabel(\PropertyDescriber $d, int $company, ?string $query, bool $multiple = FALSE): void {

		$d->prepend = \Asset::icon('123');
		$d->field = 'autocomplete';

		$d->placeholder ??= s("Commencez à saisir le compte...");
		$d->multiple = $multiple;
		$d->group += ['wrapper' => 'accountLabel'];

		$d->autocompleteUrl = \company\CompanyUi::urlAccounting($company).'/account:queryLabel';
		$d->autocompleteResults = function(string $label) use ($company, $query) {
			return self::getAutocompleteLabel($query, $company, $label);
		};

	}

	public function create(\company\Company $eCompany, Account $eAccount): \Panel {

		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax(\company\CompanyUi::urlAccounting($eCompany).'/account:doCreate', ['id' => 'accounting-account-create', 'autocomplete' => 'off']);

		$h .= $form->asteriskInfo();

		$h .= $form->dynamicGroups($eAccount, ['class*', 'description*']);

		$h .= $form->dynamicGroup($eAccount, 'vatAccount', function($d) use($form) {
		});
		$h .= $form->dynamicGroup($eAccount,  'vatRate', function($d) use ($form) {
				$d->after =  \util\FormUi::info(s("Facultatif"));
				$d->default = NULL;
			}
		);

		$h .= $form->group(
			content: $form->submit(s("Créer la classe"))
		);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-accounting-account-create',
			title: s("Ajouter une classe de compte personnalisée"),
			body: $h
		);

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Account::model()->describer($property, [
			'class' => s("Classe"),
			'description' => s("Libellé"),
			'custom' => s("Personnalisé"),
			'vatAccount' => s("Compte de TVA"),
			'vatRate' => s("Taux de TVA"),
		]);

		switch($property) {

			case 'vatAccount':
				$d->autocompleteBody = function (\util\FormUi $form, Account $e) {
					return [
					];
				};
				new \accounting\AccountUi()->query($d, GET('company', '?int'), query: ['classPrefix' => \Setting::get('accounting\vatClass')]);
				break;

			case 'class':
				$d->attributes['minlength'] = 4;
				$d->attributes['maxlength'] = 8 ;
		}
		return $d;

	}

}

?>
