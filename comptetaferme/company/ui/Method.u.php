<?php
namespace company;

class MethodUi {

	public function create(\company\Method $eMethod): \Panel {

		$eAction = $eMethod['action'];

		$form = new \util\FormUi();

		$h = $form->openAjax('/company/method:doCreate');

			if(get_exists('help')) {

				$h .= '<div class="util-block-help">';
					$h .= '<h4>'.s("À quoi servent les méthodes de travail ?").'</h4>';
					$h .= '<p>'.s("Les méthodes de travail sont associées à une intervention et permettent de déterminer les différentes façons de réaliser une intervention. Elles sont ensuite visibles dans le planning pour les interventions concernées. Vous pouvez par exemple définir :").'</p>';
					$h .= '<ul>';
						$h .= '<li>'.s("Pour <u>Plantation</u> des méthodes <i>Manuelle</i> et <i>Planteuse</i>").'</li>';
						$h .= '<li>'.s("Pour <u>Désherbage</u> des méthodes <i>Houe maraîchère</i>, <i>Thermique</i> ou <i>Manuel</i>").'</li>';
					$h .= '</ul>';
					$h .= '<p>'.s("À vous de voir en fonction de votre système !").'</p>';
				$h .= '</div>';

			}

			$h .= $form->hidden('action', $eAction['id']);
			$h .= $form->dynamicGroups($eMethod, ['name*']);
			$h .= $form->group(
				content: $form->submit(\s("Ajouter"))
			);

			$h .= $form->asteriskInfo();

		$h .= $form->close();

		return new \Panel(
			title: \s("Ajouter une méthode de travail"),
			subTitle: ActionUi::getPanelHeader($eAction),
			body: $h
		);

	}

	public function update(Action $eAction): \Panel {

		$eAction->expects(['id', 'fqn', 'cCategory']);

		$form = new \util\FormUi();

		$h = $form->openAjax('/company/action:doUpdate');

			$h .= $form->hidden('id', $eAction['id']);

			if($eAction['fqn'] === NULL) {
				$properties = ['name', 'categories', 'color', 'pace'];
			} else {
				$properties = ['color', 'pace'];
			}

			$h .= $form->dynamicGroups($eAction, $properties);
			$h .= $form->group(
				content: $form->submit(\s("Modifier"))
			);

		$h .= $form->close();

		return new \Panel(
			title: \s("Modifier une intervention"),
			body: $h,
			close: 'reload'
		);

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Action::model()->describer($property, [
			'name' => \s("Nom de la méthode"),
		]);

		return $d;

	}


}
?>
