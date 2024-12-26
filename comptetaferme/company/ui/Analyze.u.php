<?php
namespace company;

class AnalyzeUi {

	public function __construct() {

		\Asset::css('analyze', 'chart.css');
		\Asset::js('analyze', 'chart.js');

	}

	public function getActionTime(\company\Action $eAction, Category $eCategory, int $year, \Collection $cActionTimesheet, \Collection $cTimesheetMonth, \Collection $cTimesheetMonthBefore, \Collection $cTimesheetUser): \Panel {

		$h = '';

		if($cActionTimesheet->notEmpty()) {

			$h .= $this->getActionTimesheet($eAction, $eCategory, $cActionTimesheet, $year);

			if($cTimesheetMonth->notEmpty()) {

				$h .= '<br/>';

				$h .= '<h3>'.s("Temps de travail mensuel").'</h3>';
				$h .= '<div class="analyze-chart-table">';
					$h .= (new \series\AnalyzeUi())->getPeriodMonthTable($cTimesheetMonth, $eAction['farm']->canPersonalData() ? $cTimesheetUser : new \Collection());
					$h .= (new \series\AnalyzeUi())->getPeriodMonthChart($cTimesheetMonth, $year, $cTimesheetMonthBefore, $year - 1);
				$h .= '</div>';

			} else {
				$h .= '<p class="util-info">';
					$h .= s("Il n'y a aucune intervention en {value}.", $year);
				$h .= '</p>';
			}

		} else {

			$h .= '<p class="util-info">';
				$h .= s("Vous n'avez jamais utilisé cette intervention.");
			$h .= '</p>';

		}

		$title = s("{value} en {year}", ['value' => encode($eAction['name']), 'year' => $year]);

		return new \Panel(
			id: 'panel-action-analyze',
			documentTitle: $title,
			body: $h,
			header: '<h2 class="panel-title">'.$title.'</h2><h4 class="panel-subtitle">'.encode($eCategory['name']).'</h4>',
		);

	}

	public function getActionTimesheet(\company\Action $eAction, Category $eCategory, \Collection $cActionTimesheet, ?int $year): string {

		$h = '<ul class="util-summarize">';

			foreach($cActionTimesheet as $eActionTimesheet) {

				$h .= '<li '.($eActionTimesheet['year'] === $year ? 'class="selected"' : '').'>';
					$h .= '<a data-ajax="/company/action:analyzeTime?id='.$eAction['id'].'&category='.$eCategory['id'].'&year='.$eActionTimesheet['year'].'" data-ajax-method="get">';
						$h .= '<h5>'.$eActionTimesheet['year'].'</h5>';
						$h .= '<div>'.\series\TaskUi::convertTime($eActionTimesheet['time']).'</div>';
					$h .= '</a>';
				$h .= '</li>';

			}

		$h .= '</ul>';

		return $h;

	}

	public function getYears(\company\Company $eFarm, array $years, int $selectedYear, ?int $selectedMonth, ?string $selectedWeek, string $selectedView): string {

		if(count($years) === 1) {
			return '<div class="nav-year">'.$selectedYear.'</div>';
		}

		$h = '<a data-dropdown="bottom-start" data-dropdown-hover="true" data-dropdown-offset-x="2" class="nav-year">'.s("Année {value}", $selectedYear).'  '.\Asset::icon('chevron-down').'</a>';

		$h .= '<div class="dropdown-list bg-primary">';

			$h .= '<div class="dropdown-title">'.s("Changer l'année").'</div>';

			foreach($years as $year) {

				$url = \company\CompanyUi::urlAnalyzeWorkingTime($eFarm, $year, $selectedView);

				$h .= '<a href="'.$url.'" class="dropdown-item dropdown-item-full '.(($selectedYear === $year and $selectedMonth === NULL) ? 'selected' : '').'">'.s("Année {value}", $year).'</a>';

			}

		$h .= '</div>';

		if($selectedMonth !== NULL) {
			$h .= ' '.\Asset::icon('chevron-right').' ';
			$h .= mb_ucfirst(\util\DateUi::getMonthName($selectedMonth));
			$h .= ' <a href="'.\company\CompanyUi::urlAnalyzeWorkingTime($eFarm, $selectedYear, $selectedView).'" class="btn btn-sm btn-outline-secondary">'.\Asset::icon('x-circle').'</a>';
		}

		if($selectedWeek !== NULL) {
			$h .= ' '.\Asset::icon('chevron-right').' ';
			$h .= s("Semaine {value}", week_number($selectedWeek));
			$h .= ' <a href="'.\company\CompanyUi::urlAnalyzeWorkingTime($eFarm, $selectedYear, $selectedView).'" class="btn btn-sm btn-outline-secondary">'.\Asset::icon('x-circle').'</a>';
		}

		return $h;

	}

}
?>
