<?php
namespace journal;

class ThirdPartyUi {

	public function getThirdPartyTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= s("Les tiers");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#journal-thirdParty-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
				$h .= '<a href="'.\company\CompanyUi::urlJournal($eCompany).'/thirdParty:create" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Créer un tiers").'</a>';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function create(\company\Company $eCompany, ThirdParty $eThirdParty): \Panel {

		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax(\company\CompanyUi::urlJournal($eCompany).'/thirdParty:doCreate', ['id' => 'journal-thirdParty-create', 'autocomplete' => 'off']);

		$h .= $form->asteriskInfo();

		$h .= $form->dynamicGroup($eThirdParty, 'name*');

		$h .= $form->group(
			content: $form->submit(s("Créer le tiers"))
		);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-journal-thirdParty-create',
			title: s("Ajouter un tiers"),
			body: $h
		);

	}

	public static function p(string $property): \PropertyDescriber {

		$d = ThirdParty::model()->describer($property, [
			'name' => s("Nom"),
		]);

		return $d;

	}

	public static function manage(\company\Company $eCompany, \Collection $cThirdParty): string {

		if ($cThirdParty->empty() === true) {
			return '<div class="util-info">'.
				s("Aucun tiers n'a encore été créé.").
				'</div>';
		}

		$h = '<ul>';
		foreach($cThirdParty as $eThirdParty) {
			$h .= '<li>'.encode($eThirdParty['name']).'</li>';
		}
		$h .= '</ul>';

		return $h;


	}

}
?>