<?php
/**
 * Affichage d'une page de navigation pour la ferme
 */
class FarmTemplate extends MainTemplate {

	public string $template = 'farm ';

	public ?string $mainYear = NULL;

	public ?string $mainTitle = NULL;
	public ?string $mainTitleClass = '';

	public string $subNav = '';

	public string $tab;

	public function __construct() {

		parent::__construct();

		\Asset::css('farm', 'design.css');

	}

	protected function buildAjaxScroll(AjaxTemplate $t): void {

		if(server_exists('HTTP_X_REQUESTED_HISTORY') === FALSE) {
			$t->package('main')->keepScroll();
		}

	}

	protected function buildAjaxHeader(AjaxTemplate $t): void {

		try {

			$subTab = match($this->tab) {
				'cultivation' => \Setting::get('main\viewCultivation'),
				'selling' => \Setting::get('main\viewSelling'),
				'shop' => \Setting::get('main\viewShop'),
				'analyze' => \Setting::get('main\viewAnalyze'),
				default => NULL,
			};

		} catch(Exception) {
			$subTab = NULL;
		}

		$t->package('main')->updateHeader(
			$this->tab,
			$subTab,
			$this->getFarmNav(),
			$this->getFarmSubNav(),
		);

	}

	protected function getFarmNav(): string {
		return (new \farm\FarmUi())->getMainTabs($this->data->eFarm, $this->tab);
	}

	protected function getFarmSubNav(): string {
		return $this->subNav;
	}

	protected function getHeader(): string {

		$h = $this->getFarmNav();
		$h .= $this->getFarmSubNav();

		return $h;

	}

	protected function getMain(string $stream):string {

		if($this->main) {

			$h = '';

			$h .= $this->getMainTitle();
			$h .= $this->main;

			return $h;

		} else {

			$h = '';
			if($this->data->tip) {
				$h .= (new \farm\TipUi())->get($this->data->eFarm, $this->data->tip, $this->data->tipNavigation);
			}

			$h .= $this->getMainTitle();
			$h .= parent::getMain($stream);

			return $h;

		}

	}

	protected function getMainTitle():string {

		if($this->mainTitle) {

			$h = '<div class="container farm-template-main-title '.($this->mainYear ? 'farm-template-main-title-with-year' : '').' '.$this->mainTitleClass.'">';
				if($this->mainYear !== NULL) {
					$h .= '<div class="farm-template-main-year">'.$this->mainYear.'</div>';
				}
				$h .= '<div class="farm-template-main-content"><div>'.$this->mainTitle.'</div></div>';
			$h .= '</div>';

			return $h;

		} else {
			return '';
		}

	}

	protected function getNav(): string {

		$farm = '<div class="nav-title">';

			if($this->data->cFarmUser->count() > 1) {

				$farm .= '<div class="nav-title-farm">';
					$farm .= '<div>'.\farm\FarmUi::getVignette($this->data->eFarm, '4rem').'</div>';
					$farm .= '<a data-dropdown="bottom-start" data-dropdown-hover="true">'.encode($this->data->eFarm['name']).'  '.Asset::icon('chevron-down').'</a>';
					$farm .= '<div class="dropdown-list bg-primary">';
						foreach($this->data->cFarmUser as $eFarm) {
							$farm .= '<a href="'.$eFarm->getHomeUrl().'" data-ajax-navigation="never" class="dropdown-item">'.\farm\FarmUi::getVignette($eFarm, '1.75rem').'&nbsp;&nbsp;'.encode($eFarm['name']).'</a>';
						}
					$farm .= '</div>';
				$farm .= '</div>';

			} else {
				$farm .= '<div class="nav-title-farm">';
					$farm .= '<div>'.\farm\FarmUi::getVignette($this->data->eFarm, '1.75rem').'</div>';
					$farm .= '<div>'.encode($this->data->eFarm['name']).'</div>';
				$farm .= '</div>';
			}

		$farm .= '</div>';

		return $this->getDefaultNav($farm);

	}

}
?>
