<?php
namespace accounting;

class FinancialYearUi {

	public function __construct() {
		\Asset::css('accounting', 'accounting.css');
	}

	public function getManageTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

		$h .= '<h1>';
		$h .= '<a href="'.\company\CompanyUi::urlSettings($eCompany).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
		$h .= s("Les exercices comptables");
		$h .= '</h1>';

		$h .= '</div>';

		return $h;

	}

	protected function getAction(\company\Company $eCompany, FinancialYear $eFinancialYear, string $btn): string {

		$h = '<a data-dropdown="bottom-end" class="dropdown-toggle btn '.$btn.'">'.\Asset::icon('gear-fill').'</a>';
		$h .= '<div class="dropdown-list">';
			$h .= '<div class="dropdown-title">'.s("Exercice {year}", ['year' => self::getYear($eFinancialYear)]).'</div>';
			$h .= '<a data-ajax="'.\company\CompanyUi::urlAccounting($eCompany).'/financialYear:close" post-id="'.$eFinancialYear['id'].'" class="dropdown-item" data-confirm="'.s("Action irréversible ! Souhaitez-vous confirmer la clôture de cet exercice comptable ? Le suivant sera créé automatiquement.").'">'.s("Clôturer").'</a>';;
		$h .= '</div>';

		return $h;

	}

	public function getManage(\company\Company $eCompany, \Collection $cFinancialYear): string {

		if ($cFinancialYear->empty() === true) {
			return '<div class="util-info">'.s("Aucun exercice comptable n'a encore été enregistré").'</div>';
		}

		$h = '';

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="financialYear-item-table tr-bordered tr-even">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th class="text-center">'.s("#").'</th>';
						$h .= '<th>'.s("Date de début").'</th>';
						$h .= '<th>'.s("Date de fin").'</th>';
						$h .= '<th class="text-center">'.s("Statut").'</th>';
						$h .= '<th class="td-min-content"></th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';
					$h .= '<div class="util-overflow-sm">';

					foreach($cFinancialYear as $eFinancialYear) {

						$h .= '<tr>';

						$h .= '<td class="td-min-content text-center">';
							if ($eFinancialYear['status'] === FinancialYear::CLOSE) {
								$h .= $eFinancialYear['id'];
							} else {
								$h .= '<a href="'.\company\CompanyUi::urlAccounting($eCompany).'/financialYear:update?id='.$eFinancialYear['id'].'" class="btn btn-sm btn-outline-primary">'.$eFinancialYear['id'].'</a>';
							}
						$h .= '</td>';

						$h .= '<td>';
							$h .= \util\DateUi::numeric($eFinancialYear['startDate']);
						$h .= '</td>';

						$h .= '<td>';
							$h .= \util\DateUi::numeric($eFinancialYear['endDate']);
						$h .= '</td>';

						$h .= '<td class="text-center">';
							$h .= match($eFinancialYear['status']) {
								FinancialYearElement::OPEN => s("En cours"),
								FinancialYearElement::CLOSE => s("Clôturé"),
							};
						$h .= '</td>';
						$h .= '<td>';
							if ($eFinancialYear['status'] === FinancialYearElement::OPEN) {
								$h .= self::getAction($eCompany, $eFinancialYear, 'btn-outline-primary');
							}
						$h .= '</td>';

						$h .= '</tr>';
					}


				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;

	}


	public function create(\company\Company $eCompany, FinancialYear $eFinancialYear): \Panel {

		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax(\company\CompanyUi::urlAccounting($eCompany).'/financialYear:doCreate', ['id' => 'accounting-financialYear-create', 'autocomplete' => 'off']);

			$h .= $form->asteriskInfo();

			$h .= $form->hidden('company', $eCompany['id']);

			$h .= $form->dynamicGroups($eFinancialYear, ['startDate*', 'endDate*']);

			$h .= $form->group(
				content: $form->submit(s("Créer l'exercice"))
			);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-accounting-financialYear-create',
			title: s("Ajouter un exercice comptable"),
			body: $h
		);

	}

	public function update(\company\Company $eCompany, FinancialYear $eFinancialYear): \Panel {

		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax(\company\CompanyUi::urlAccounting($eCompany).'/financialYear:doUpdate', ['id' => 'accounting-financialYear-update', 'autocomplete' => 'off']);

			$h .= $form->asteriskInfo();

			$h .= $form->hidden('company', $eCompany['id']);
			$h .= $form->hidden('id', $eFinancialYear['id']);

			$h .= $form->dynamicGroups($eFinancialYear, ['startDate*', 'endDate*']);

			$h .= $form->group(
				content: $form->submit(s("Mettre à jour l'exercice"))
			);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-accounting-financialYear-update',
			title: s("Modifier un exercice comptable"),
			body: $h
		);

	}

	public static function getYear(\accounting\FinancialYear $eFinancialYear): string {

		if (substr($eFinancialYear['startDate'], 0, 4) === substr($eFinancialYear['endDate'], 0, 4)) {
			return substr($eFinancialYear['startDate'], 0, 4);
		}

		return substr($eFinancialYear['startDate'], 0, 4).' - '.substr($eFinancialYear['endDate'], 0, 4);

	}
	public function getFinancialYearTabs(\Closure $url, \Collection $cFinancialYear, \accounting\FinancialYear $eFinancialYearSelected): string {

		$h = ' <a data-dropdown="bottom-start" data-dropdown-hover="true" data-dropdown-offset-x="2" class="nav-year">';
			$h .= s("Exercice {year}", ['year' => self::getYear($eFinancialYearSelected).'  '.\Asset::icon('chevron-down')]);
		$h .= '</a>';

		$h .= '<div class="dropdown-list bg-primary">';

		$h .= '<div class="dropdown-title">'.s("Changer d'exercice").'</div>';

		foreach($cFinancialYear as $eFinancialYear) {
			$h .= '<a href="'.$url($eFinancialYear).'" class="dropdown-item '.($eFinancialYear['id'] === $eFinancialYearSelected['id'] ? 'selected' : '').'">'.s("Exercice {year}", ['year' => self::getYear($eFinancialYear)]).'</a>';
		}

		/*$h .= '<div class="dropdown-divider"></div>';

		$newLast = $eFarm['seasonLast'] + 1;
		$newFirst = $eFarm['seasonFirst'] - 1;

		if($newLast > date('Y') + 10) {
			$alert = s("Il n'est pas possible d'ajouter des saisons plus de dix ans en avance !");
			$h .= '<div class="dropdown-item farm-tab-disabled" data-alert="'.$alert.'" title="'.$alert.'">';
			$h .= s("Ajouter la saison {year}", ['year' => $newLast]);
			$h .= '</div> ';
		} else {
			$h .= '<a data-ajax="/farm/farm:doSeasonLast" post-id="'.$eFarm['id'].'" post-increment="1" class="dropdown-item">';
			$h .= s("Ajouter la saison {year}", ['year' => $newLast]);
			$h .= '</a> ';
		}

		$h .= '<a data-ajax="/farm/farm:doSeasonFirst" post-id="'.$eFarm['id'].'" post-increment="-1" class="dropdown-item">';
		$h .= s("Ajouter la saison {year}", ['year' => $newFirst]);
		$h .= '</a> ';*/

		$h .= '</div>';

		return $h;

	}

	public static function p(string $property): \PropertyDescriber {

		$d = FinancialYear::model()->describer($property, [
			'startDate' => s("Date de début"),
			'endDate' => s("Date de fin"),
		]);

		switch($property) {

			case 'startDate' :
			case 'endDate' :
				$d->prepend = \Asset::icon('calendar-date');
				break;

		}

		return $d;

	}
}

?>