<?php
/**
 * Affichage d'une page de navigation pour l'entreprise
 */
class CompanyTemplate extends MainTemplate {

	public string $template = 'company ';

	public ?string $mainYear = NULL;

	public ?string $mainTitle = NULL;
	public ?string $mainTitleClass = '';

	public string $subNav = '';

	public string $tab;

	public function __construct() {

		parent::__construct();

		\Asset::css('company', 'design.css');

	}

	protected function buildAjaxScroll(AjaxTemplate $t): void {

		if(server_exists('HTTP_X_REQUESTED_HISTORY') === FALSE) {
			$t->package('main')->keepScroll();
		}

	}

	protected function buildAjaxHeader(AjaxTemplate $t): void {

		try {

			$subTab = match($this->tab) {
				'bank' => \Setting::get('main\viewBank'),
				'journal' => \Setting::get('main\viewJournal'),
				default => NULL,
			};

		} catch(Exception) {
			$subTab = NULL;
		}

		$t->package('main')->updateHeader(
			$this->tab,
			$subTab,
			$this->getCompanyNav(),
			$this->getCompanySubNav(),
		);

	}

	protected function getCompanyNav(): string {
		return (new \company\CompanyUi())->getMainTabs($this->data->eCompany, $this->tab);
	}

	protected function getCompanySubNav(): string {
		return $this->subNav;
	}

	protected function getHeader(): string {

		$h = $this->getCompanyNav();
		$h .= $this->getCompanySubNav();

		return $h;

	}

	protected function getMain(string $stream):string {

		$h = '';

		if($this->main) {

			$h .= $this->getMainTitle();
			$h .= $this->main;

			return $h;

		} else {

			$h .= $this->getMainTitle();
			$h .= parent::getMain($stream);

			return $h;

		}

	}

	protected function getMainTitle():string {

		if($this->mainTitle) {

			$h = '<div class="container company-template-main-title '.($this->mainYear ? 'company-template-main-title-with-year' : '').' '.$this->mainTitleClass.'">';
				if($this->mainYear !== NULL) {
					$h .= '<div class="company-template-main-year">'.$this->mainYear.'</div>';
				}
				$h .= '<div class="company-template-main-content"><div>'.$this->mainTitle.'</div></div>';
			$h .= '</div>';

			return $h;

		} else {
			return '';
		}

	}

	protected function getNav(): string {

		$company = '<div class="nav-title">';

			if($this->data->cCompanyUser->count() > 1) {

				$company .= '<div class="nav-title-company">';
					$company .= '<div>'.\company\CompanyUi::getVignette($this->data->eCompany, '4rem').'</div>';
					$company .= '<a data-dropdown="bottom-start" data-dropdown-hover="true">'.encode($this->data->eCompany['name']).'  '.Asset::icon('chevron-down').'</a>';
					$company .= '<div class="dropdown-list bg-primary">';
						foreach($this->data->cCompanyUser as $eCompany) {
							$company .= '<a href="'.$eCompany->getHomeUrl().'" data-ajax-navigation="never" class="dropdown-item">'.\company\CompanyUi::getVignette($eCompany, '1.75rem').'&nbsp;&nbsp;'.encode($eCompany['name']).'</a>';
						}
					$company .= '</div>';
				$company .= '</div>';

			} else {
				$company .= '<div class="nav-title-company">';
					$company .= '<div>'.\company\CompanyUi::getVignette($this->data->eCompany, '1.75rem').'</div>';
					$company .= '<div>'.encode($this->data->eCompany['name']).'</div>';
				$company .= '</div>';
			}

		$company .= '</div>';

		return $this->getDefaultNav($company);

	}

}
?>
