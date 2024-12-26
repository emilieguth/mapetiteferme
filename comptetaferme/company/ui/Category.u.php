<?php
namespace company;

class CategoryUi {

	public static function getColorCircle(Category $eCategory): string {

		$eCategory->expects(['color']);

		return '<div class="category-color-circle" style="background-color: '.$eCategory['color'].'"></div>';

	}

	public static function getShort(Category $eCategory): string {

		$eCategory->expects(['short', 'name']);

		return encode($eCategory['short'] ?? strtoupper(mb_substr($eCategory['name'], 0, 1)));

	}

	public static function text(\production\Flow|series\Task $e): string {

		if($e instanceof \production\Flow) {

			$e['variety'] = new \plant\Variety();

			$e->expects(['sequence']);

		} else {
			$e->expects(['series']);
		}

		$e->expects([
			'plant',
			'category' => ['name'],
			'variety',
			'description'
		]);

		$ePlant = $e['plant'];

		$eCategory = $e['category'];
		$eCategory->expects(['name']);

		\Asset::css('farm', 'category.css');

		$h = '<span class="category-text">';

		if($ePlant->empty()) {

			$h .= encode($eCategory['name']);

			if(
				($e instanceof series\Task and $e['series']->notEmpty()) or
				($e instanceof \production\Flow and $e['sequence']->notEmpty())
			) {
				$h .= ' <span class="category-name">'.s("PARTAGÉ").'</span>';
			}

		} else {

			$ePlant->expects(['name']);

			$plant = '<span class="category-name">'.encode($ePlant['name']).'</span>';

			$arguments = [
				'category' => encode($eCategory['name']),
				'plant' => $plant
			];

			if($eCategory['fqn'] === 'other') {

				if($e['description'] === NULL) {
					$h .= s("{category} de {plant}", $arguments);
				} else {
					$h .= $arguments['plant'];
				}

			} else {
				$h .= s("{category} de {plant}", $arguments);
			}

		}

		$h .= '</span>';

		return $h;

	}

	public function getManageTitle(\company\Company $eFarm, \Collection $cCategory): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= '<a href="/company/action:manage?farm='.$eFarm['id'].'" class="h-back">'.\Asset::icon('arrow-left').'</a>';
				$h .= s("Les catégories");
			$h .= '</h1>';

			if($cCategory->count() < \Setting::get('company\categoriesLimit')) {

				$h .= '<div>';
					$h .= '<a href="/company/category:create?farm='.$eFarm['id'].'" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Nouvelle catégorie").'</a>';
				$h .= '</div>';

			}

		$h .= '</div>';

		return $h;

	}

	public function getManage(\company\Company $eFarm, \Collection $cCategory): string {

		$h = '<p class="util-info">';
			$h .= p("Vous avez actuellement configuré {value} catégorie sur {max} possibles.", "Vous avez actuellement configuré {value} catégories sur {max} possibles.", $cCategory->count(), ['max' => \Setting::get('company\categoriesLimit')]);
		$h .= '</p>';

		$h .= '<br/>';

		$h .= '<table class="tr-bordered">';
			$h .= '<thead>';
				$h .= '<tr>';
					$h .= '<th colspan="2">'.s("Position").'</th>';
					$h .= '<th>'.self::p('name')->label.'</th>';
					$h .= '<th></th>';
				$h .= '</tr>';
			$h .= '</thead>';

			$h .= '<tbody>';

			foreach($cCategory as $eCategory) {

				$h .= '<tr>';
					$h .= '<td class="td-min-content">';
						$h .= '<b>'.$eCategory['position'].'.</b>';
					$h .= '</td>';
					$h .= '<td>';

						if($eCategory['position'] > 1) {
							$h .= '<a data-ajax="/company/category:doIncrementPosition" post-id='.$eCategory['id'].'" post-increment="-1" class="btn btn-sm btn-secondary">'.\Asset::icon('arrow-up').'</a> ';
						} else {
							$h .= '<a class="btn btn-sm btn-disabled">'.\Asset::icon('arrow-up').'</a> ';
						}

						if($eCategory['position'] !== $cCategory->count()) {
							$h .= '<a data-ajax="/company/category:doIncrementPosition" post-id='.$eCategory['id'].'" post-increment="1" class="btn btn-sm btn-secondary">'.\Asset::icon('arrow-down').'</a> ';
						} else {
							$h .= '<a class="btn btn-sm btn-disabled">'.\Asset::icon('arrow-down').'</a> ';
						}
					$h .= '</td>';
					$h .= '<td>';
						$h .= encode($eCategory['name']);
					$h .= '</td>';
					$h .= '<td class="text-end" style="white-space: nowrap">';

						$h .= '<a href="/company/category:update?id='.$eCategory['id'].'" class="btn btn-outline-secondary">';
							$h .= \Asset::icon('gear-fill');
						$h .= '</a> ';

						if($eCategory['fqn'] === NULL) {
							$h .= '<a data-ajax="/company/category:doDelete" data-confirm="'.s("Supprimer cette catégorie ?").'" post-id="'.$eCategory['id'].'" class="btn btn-outline-secondary">';
								$h .= \Asset::icon('trash-fill');
							$h .= '</a>';
						} else {
							$h .= '<div class="btn btn-outline-secondary btn-disabled" title="'.s("Catégorie indispensable au bon fonctionnement de {siteName}").'">';
								$h .= \Asset::icon('trash-fill');
							$h .= '</div>';
						}

					$h .= '</td>';
				$h .= '</tr>';
			}
			$h .= '</tbody>';
		$h .= '</table>';

		return $h;

	}

	public function create(\company\Company $eFarm): \Panel {

		$eCategory = new Category();

		$form = new \util\FormUi();

		$h = $form->openAjax('/company/category:doCreate');

			$h .= $form->asteriskInfo();

			$h .= $form->hidden('farm', $eFarm['id']);
			$h .= $form->dynamicGroups($eCategory, ['name*']);
			$h .= $form->group(
				content: $form->submit(s("Ajouter"))
			);

		$h .= $form->close();

		return new \Panel(
			title: s("Ajouter une nouvelle catégorie"),
			body: $h,
			close: 'reload'
		);

	}

	public function update(Category $eCategory): \Panel {

		$form = new \util\FormUi();

		$h = $form->openAjax('/company/category:doUpdate');

			$h .= $form->hidden('id', $eCategory['id']);
			$h .= $form->dynamicGroups($eCategory, ['name']);
			$h .= $form->group(
				content: $form->submit(s("Modifier"))
			);

		$h .= $form->close();

		return new \Panel(
			title: s("Modifier une catégorie"),
			body: $h,
			close: 'reload'
		);

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Category::model()->describer($property, [
			'name' => s("Nom de la catégorie"),
			'fqn' => s("Nom qualifié")
		]);

		switch($property) {

		}

		return $d;

	}


}
?>
