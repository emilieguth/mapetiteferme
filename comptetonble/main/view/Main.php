<?php
/**
 * Affichage d'une page de navigation
 */
class MainTemplate extends BaseTemplate {

	/**
	 * Display nav ?
	 */
	public bool $nav = TRUE;

	/**
	 * Header content
	 */
	public ?string $header = NULL;

	/**
	 * Main content
	 */
	public ?string $main = NULL;

	/**
	 * Main container ?
	 */
	public bool $mainContainer = TRUE;

	/**
	 * Display something in the footer
	 */
	public ?string $footer = NULL;

	/**
	 * Admin page
	 */
	public ?string $admin = NULL;

	public function __construct() {

		parent::__construct();

		\Asset::css('main', 'design.css');

		$this->base = \Lime::getProtocol().'://'.SERVER('HTTP_HOST');

	}

	protected function getHeader(): string {

		$h = '';

		if($this->header === NULL and $this->title !== NULL) {
			$h .= '<div class="container">';
				$h .= '<h1>'.$this->title.'</h1>';
			$h .= '</div>';
		} else  if($this->header !== '') {
			$h .= '<div class="container">';
				$h .= $this->header;
			$h .= '</div>';
		}

		return $h;

	}

	protected function getMain(string $stream):string {

		if($this->main) {
			return $this->main;
		} else {

			$h = '';

			if($this->mainContainer) {
				$h .= '<div class="container">'.$stream.'</div>';
			} else {
				$h .= $stream;
			}

			if($this->data->browserObsolete) {
				$h .= '<div class="util-box-warning util-box-sticked">'.$this->getWarningObsoleteBrowser().'</div>';
			}

			return $h;

		}

	}

	protected function getLogo(string $size): string {

		$h = '<div class="logo-wrapper" style="width: '.$size.'; height: '.$size.'">';
			$h .= '<div class="logo-top-left-circle"></div>';
			$h .= '<div class="logo-bottom-right-circle"></div>';
			$h .= '<div class="logo-middle-circle"></div>';
		$h .= '</div>';

		return $h;

	}

	protected function getNav(): string {
		return $this->getDefaultNav();
	}

	protected function getDefaultNav(?string $center = NULL): string {

		if($this->nav === FALSE) {
			return '';
		}

		$h = '<div class="nav-wrapper nav-default-wrapper container">';

		if($center === NULL) {

			$h .= '<div class="nav-title">';
				$h .= '<a class="nav-logo" href="'.Lime::getUrl().'">';
					$h .= $this->getLogo('100%');
					$h .= '<div class="nav-logo-home">'.\Asset::icon('house-door-fill').'</div>';
				$h .= '</a>';

				$h .= '<a href="'.Lime::getUrl().'">'.Lime::getDomain().'</a>';
			$h .= '</div>';

		} else {

			$h .= $center;

		}

		if(
			Lime::getHost() === LIME_HOST and // Uniquement sur www
			currentDate() <= '2024-10-10'
		) {
			$h .= '<a href="https://blog.comptetonble.fr/" class="nav-news" target="_blank">';
				$h .= '<div class="nav-news-title">'.Asset::icon('cursor-fill').' '.s("Nouveautés").'</div>';
				$h .= '<div class="nav-news-name">'.s("5 octobre 2024").'</div>';
			$h .= '</a>';
		}

		$h .= '<ul class="nav-actions">';

			if($this->data->userDeletedAt) {
				$h .= '<li class="nav-deleted nav-action-optional">';
					$h .= '<a href="/main/account" class="nav-item" title="'.(new user\DropUi())->getCloseMessage($this->data->userDeletedAt).'"><span>'.\Asset::icon('exclamation-triangle-fill').'&nbsp;'.s("Compte en cours de fermeture").'&nbsp;'.\Asset::icon('exclamation-triangle-fill').'</span></a>';
				$h .= '</li>';
			}

			if($this->data->isLogged) {

				if($this->data->eUserOnline->notEmpty()) {
					$h .= $this->getUserNavItem($this->data);
				}

			} else {

				$h .= '<li id="signIn-item">';
					$h .= '<a href="'.Lime::getUrl().'/user/signUp" class="nav-item">'.s("Inscription").'</a>';
				$h .= '</li>';

				$h .= '<li id="logIn-item">';
					$h .= '<a href="/user/log:form" class="nav-item">'.s("Connexion").'</a>';
				$h .= '</li>';

			}

		$h .= '</ul>';

		$h .= '</div>';

		return $h;

	}

	protected function getUserNavItem($data): string {

		$h = '<li>';

		$h .= '<a class="nav-user nav-item" data-dropdown="bottom" data-dropdown-hover="true">';
			$h .= \user\UserUi::getVignette($data->eUserOnline, '1.75rem');
			$h .= \Asset::icon('chevron-down');
		$h .= '</a>';

		$h .= '<div class="dropdown-list bg-primary">';

		$h .= '<div class="dropdown-title">'.\user\UserUi::name($data->eUserOnline).'</div>';

		$h .= '<a href="'.Lime::getUrl().'" class="dropdown-item">'.s("Accueil").'</a>';

		if(Lime::getHost() === LIME_HOST) {
			$h .= '<a href="/main/account" class="dropdown-item">'.s("Mon compte").'</a>';
		} else {
			$h .= '<a href="'.Lime::getUrl().'/main/account" class="dropdown-item" target="_blank">'.s("Mon compte").'</a>';
		}

		$h .= '<form method="post" action="'.Lime::getUrl().'/user/log:out">';
			$h .= '<button type="submit" class="dropdown-item">'.s("Me déconnecter").'</button>';

			if(Lime::getHost() === LIME_HOST) {
				$h .= '<input type="hidden" name="redirect" value="'.Lime::getProtocol().'://'.SERVER('HTTP_HOST').'"/>';
			} else {
				$h .= '<input type="hidden" name="redirect" value="'.Lime::getUrl().'"/>';
			}
		$h .='</form>';

		if(Privilege::can('user\admin')) {
			$h .= '<div class="dropdown-divider"></div>';
			$h .= '<a href="'.Lime::getUrl().'/user/admin/" class="dropdown-item">'.\Asset::icon('server').' '.s("Administrer").'</a>';
		}

		$h .= '</div>';

		$h .= '</li>';

		return $h;

	}

	protected function getFooter() {

		$h = '';

		if($this->footer === NULL) {

			if(Lime::getHost() === LIME_HOST) {

				$h .= '<div class="footer-content">';
					$h .= '<div class="footer-content-text">';
						$h .= Lime::getHost();
					$h .= '</div>';
					$h .= '<div class="footer-content-legal">';
						$h .= '<div>';
							$h .= '<h4>'.s("Ressources").'</h4>';
							$h .= '<a href="/presentation/faq">'.s("Foire aux questions").'</a><br/>';
							$h .= '<a href="https://app.element.io/#/room/#comptetonble:matrix.org" target="_blank">'.s("Signaler un problème").'</a><br/>';
							$h .= '<a href="https://blog.comptetonble.fr/" target="_blank">'.s("Blog").'</a>';
						$h .= '</div>';
						$h .= '<div>';
							$h .= '<h4>'.s("Usage").'</h4>';
							$h .= '<a href="/presentation/legal">'.s("Mentions légales").'</a><br/>';
							$h .= '<a href="/presentation/service">'.s("Conditions d'utilisation").'</a><br/>';
							$h .= '<a href="/presentation/producteur">'.s("Fonctionnalités").'</a>';
						$h .= '</div>';
					$h .= '</div>';
				$h .= '</div>';

			}

		} else {
			$h .= $this->footer;
		}

		if(
			$this->data->eUserOnline->notEmpty() and
			$this->data->logInExternal !== NULL
		) {
			$h .= (new user\UserUi())->logOutExternal($this->data->logInExternal[0]);
		}

		return $h;

	}

}
?>
