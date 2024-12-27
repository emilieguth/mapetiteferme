<?php
namespace company;

class CompanyUi {

	public function __construct() {
		\Asset::css('company', 'company.css');
		\Asset::js('company', 'company.js');
	}

	public static function link(Company $eCompany, bool $newTab = FALSE): string {
		return '<a href="'.self::url($eCompany).'" '.($newTab ? 'target="_blank"' : '').'>'.encode($eCompany['name']).'</a>';
	}

	public static function url(Company $eCompany): string {
		return '/company/'.$eCompany['id'];
	}

	public static function urlSettings(Company $eCompany): string {
		return self::url($eCompany).'/configuration';
	}

	public static function urlFinances(Company $eCompany): string {
		return self::url($eCompany).'/finances';
	}

	public static function urlSuppliers(Company $eCompany): string {
		return self::url($eCompany).'/fournisseurs';
	}

	public static function urlCustomers(Company $eCompany): string {
		return self::url($eCompany).'/clients';
	}

	/**
	 * Display a field to search companies
	 *
	 *
	 */
	public function query(\PropertyDescriber $d, bool $multiple = FALSE) {

		$d->prepend = \Asset::icon('house-door-fill');
		$d->field = 'autocomplete';

		$d->placeholder = s("Tapez un nom d'entreprise...");
		$d->multiple = $multiple;

		$d->autocompleteUrl = '/company/search:query';
		$d->autocompleteResults = function(Company $e) {
			return self::getAutocomplete($e);
		};

	}

	public static function getAutocomplete(Company $eCompany): array {

		$item = self::getVignette($eCompany, '2.5rem');
		$item .= '<div>';
		$item .= encode($eCompany['name']).'<br/>';
		$item .= '</div>';

		return [
			'value' => $eCompany['id'],
			'itemHtml' => $item,
			'itemText' => $eCompany['name']
		];

	}

	public function create(): \Panel {

		$eCompany = new Company();

		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax('/company/company:doCreate', ['id' => 'company-create', 'autocomplete' => 'off']);

			$h .= $form->asteriskInfo();

			$h .= $form-> group(self::p('siret'), $form->text('siret', null, ['oninput' => 'Company.getCompanyDataBySiret(this)']));

			$h .= $form->dynamicGroups($eCompany, ['name*', 'addressLine1', 'addressLine2', 'postalCode', 'city']);
			$h .= $form->hidden('nafCode', null);

			$h .= $form->group(
				content: $form->submit(s("Créer mon entreprise"))
			);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-company-create',
			title: s("Créer mon entreprise"),
			body: $h
		);

	}

	public function update(Company $eCompany): string {

		$form = new \util\FormUi();

		$h = $form->openAjax('/company/company:doUpdate', ['id' => 'company-update', 'autocomplete' => 'off']);

			$h .= $form->hidden('id', $eCompany['id']);

			$h .= $form->group(
				self::p('vignette')->label,
				(new \media\CompanyVignetteUi())->getCamera($eCompany, size: '10rem')
			);
			$h .= $form-> group(self::p('siret'), $form->text('siret', $eCompany['siret'], ['oninput' => 'Company.getCompanyDataBySiret(this)']));
			$h .= $form->dynamicGroups($eCompany, ['nafCode', 'name', 'url', 'addressLine1', 'addressLine2', 'postalCode', 'city']);

			$h .= $form->group(
				content: $form->submit(s("Modifier"))
			);

		$h .= $form->close();

		return $h;

	}

	public function getMainTabs(Company $eCompany, string $tab): string {

		$prefix = '<span class="company-subnav-prefix">'.\Asset::icon('chevron-right').' </span>';

		$h = '<nav id="company-nav">';

			$h .= '<div class="company-tabs">';

				$h .= '<a href="'.CompanyUi::urlFinances($eCompany).'" class="company-tab '.($tab === 'finances' ? 'selected' : '').'" data-tab="finances">';
					$h .= '<span class="hide-lateral-down company-tab-icon">'.\Asset::icon('piggy-bank').'</span>';
						$h .= '<span class="hide-lateral-up company-tab-icon">'.\Asset::icon('piggy-bank-fill').'</span>';
						$h .= '<span class="company-tab-label hide-xs-down">';
						$h .= s("Finances");
					$h .= '</span>';
				$h .= '</a>';

				$h .= '<a href="'.CompanyUi::urlSuppliers($eCompany).'" class="company-tab '.($tab === 'suppliers' ? 'selected' : '').'" data-tab="suppliers">';
					$h .= '<span class="hide-lateral-down company-tab-icon">'.\Asset::icon('building-down').'</span>';
						$h .= '<span class="hide-lateral-up company-tab-icon">'.\Asset::icon('building-fill-down').'</span>';
						$h .= '<span class="company-tab-label hide-xs-down">';
						$h .= s("Fournisseurs");
					$h .= '</span>';
				$h .= '</a>';

				$h .= '<a href="'.CompanyUi::urlCustomers($eCompany).'" class="company-tab '.($tab === 'customers' ? 'selected' : '').'" data-tab="customers">';
					$h .= '<span class="hide-lateral-down company-tab-icon">'.\Asset::icon('file-earmark-person').'</span>';
						$h .= '<span class="hide-lateral-up company-tab-icon">'.\Asset::icon('file-earmark-person-fill').'</span>';
						$h .= '<span class="company-tab-label hide-xs-down">';
						$h .= s("Clients");
					$h .= '</span>';
				$h .= '</a>';

				$h .= '<a href="'.CompanyUi::urlSettings($eCompany).'" class="company-tab '.($tab === 'settings' ? 'selected' : '').'" data-tab="settings">';
					$h .= '<span class="hide-lateral-down company-tab-icon">'.\Asset::icon('gear').'</span>';
					$h .= '<span class="hide-lateral-up company-tab-icon">'.\Asset::icon('gear-fill').'</span>';
					$h .= '<span class="company-tab-label hide-xs-down">';
						$h .= s("Paramétrage");
					$h .= '</span>';
				$h .= '</a>';

			$h .= '</div>';

		$h .= '</nav>';

		return $h;

	}

	public function getSettingsSubNav(Company $eCompany): string {

		$selectedView = \Setting::get('main\viewSettings');

		$h = '<nav id="company-subnav">';
			$h .= '<div class="company-subnav-wrapper">';

				foreach($this->getSettingsCategories($eCompany) as $key => ['url' => $url, 'label' => $label]) {
					$h .= '<a href="'.$url.'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'">'.$label.'</a> ';
				}

			$h .= '</div>';
		$h .= '</nav>';

		return $h;

	}

	protected static function getSettingsCategories(Company $eCompany): array {

		return [
			'settings' => [
				'url' => CompanyUi::urlSettings($eCompany),
				'label' => s("Paramétrage")
			]
		];

	}


	public function getSettings(Company $eCompany): string {

		$h = '';

		$h .= '<div class="util-block-optional">';

			$h .= '<h2>'.s("L'entreprise").'</h2>';

			$h .= '<div class="util-buttons">';

				$h .= '<a href="/company/company:update?id='.$eCompany['id'].'" class="bg-secondary util-button">';
					$h .= '<h4>'.s("Les réglages de base<br/>de l'entreprise").'</h4>';
					$h .= \Asset::icon('gear-fill');
				$h .= '</a>';

				$h .= '<a href="'.EmployeeUi::urlManage($eCompany).'" class="bg-secondary util-button">';
					$h .= '<h4>'.s("L'équipe").'</h4>';
					$h .= \Asset::icon('people-fill');
				$h .= '</a>';

				$h .= '<a href="/company/company:updateFeature?id='.$eCompany['id'].'" class="bg-secondary util-button">';
					$h .= '<h4>'.s("Activer ou désactiver des fonctionnalités").'</h4>';
					$h .= \Asset::icon('toggle2-on');
				$h .= '</a>';

			$h .= '</div>';

		$h .= '</div>';

		$h .= '<div class="util-block-optional">';

			$h .= '<h2>😭</h2>';

			$h .= '<div class="util-buttons">';

				$h .= '<a data-ajax="/company/company:doClose" post-id="'.$eCompany['id'].'" data-confirm="'.s("Êtes-vous sûr de vouloir supprimer cette entreprise ?").'" class="bg-danger util-button">';

					$h .= '<h4>'.s("Supprimer l'entreprise").'</h4>';
					$h .= \Asset::icon('trash');

				$h .= '</a>';

			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getFinancesSubNav(Company $eCompany): string {

		$selectedView = \Setting::get('main\viewFinances');

		$h = '<nav id="company-subnav">';
			$h .= '<div class="company-subnav-wrapper">';

			foreach($this->getFinancesCategories($eCompany) as $key => ['url' => $url, 'label' => $label]) {
				$h .= '<a href="'.$url.'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'">'.$label.'</a> ';
			}

			$h .= '</div>';
		$h .= '</nav>';

		return $h;

	}

	protected static function getFinancesCategories(Company $eCompany): array {

		return [
			'finances' => [
				'url' => CompanyUi::urlFinances($eCompany),
				'label' => s("Finances")
			]
		];

	}

	public function getSuppliersSubNav(Company $eCompany): string {

		$selectedView = \Setting::get('main\viewSuppliers');

		$h = '<nav id="company-subnav">';
			$h .= '<div class="company-subnav-wrapper">';

				foreach($this->getSuppliersCategories($eCompany) as $key => ['url' => $url, 'label' => $label]) {
					$h .= '<a href="'.$url.'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'">'.$label.'</a> ';
				}

			$h .= '</div>';
		$h .= '</nav>';

		return $h;

	}

	protected static function getSuppliersCategories(Company $eCompany): array {

		return [
			'suppliers' => [
				'url' => CompanyUi::urlSuppliers($eCompany),
				'label' => s("Fournisseurs")
			]
		];

	}

	public function getCustomersSubNav(Company $eCompany): string {

		$selectedView = \Setting::get('main\viewCustomers');

		$h = '<nav id="company-subnav">';
			$h .= '<div class="company-subnav-wrapper">';

				foreach($this->getCustomersCategories($eCompany) as $key => ['url' => $url, 'label' => $label]) {
					$h .= '<a href="'.$url.'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'">'.$label.'</a> ';
				}

			$h .= '</div>';
		$h .= '</nav>';

		return $h;

	}

	protected static function getCustomersCategories(Company $eCompany): array {

		return [
			'customers' => [
				'url' => CompanyUi::urlCustomers($eCompany),
				'label' => s("Clients")
			]
		];

	}

	public function getPanel(Company $eCompany): string {

		$h = '';

		$h .= '<a href="'.$eCompany->getHomeUrl().'" class="employee-companies-item">';

			$h .= '<div class="employee-companies-item-vignette">';
				$h .= self::getVignette($eCompany, '6rem');
			$h .= '</div>';
			$h .= '<div class="employee-companies-item-content">';
				$h .= '<h4>';
					$h .= encode($eCompany['name']);
				$h .= '</h4>';
				$h .= '<div class="employee-companies-item-infos">';

					$infos = [];

					$h .= implode(' | ', $infos);

				$h .= '</div>';

			$h .= '</div>';

		$h .= '</a>';

		return $h;

	}

	public static function getVignette(Company $eCompany, string $size): string {

		$eCompany->expects(['id', 'vignette']);

		$class = 'company-vignette-view media-circle-view'.' ';
		$style = '';

		$ui = new \media\CompanyVignetteUi();

		if($eCompany['vignette'] === NULL) {

			$class .= ' media-vignette-default';
			$style .= 'color: var(--muted)';
			$content = \Asset::icon('house-door-fill');

		} else {

			$format = $ui->convertToFormat($size);

			$style .= 'background-image: url('.$ui->getUrlByElement($eCompany, $format).');';
			$content = '';

		}

		return '<div class="'.$class.'" style="'.$ui->getSquareCss($size).'; '.$style.'">'.$content.'</div>';

	}

	public static function getLogo(Company $eCompany, string $size): string {

		$eCompany->expects(['id', 'logo']);

		$ui = new \media\CompanyLogoUi();

		$class = 'company-logo-view media-rectangle-view'.' ';
		$style = '';

		if($eCompany['logo'] === NULL) {

			$class .= ' media-logo-default';
			$style .= '';

		} else {

			$format = $ui->convertToFormat($size);

			$style .= 'background-image: url('.$ui->getUrlByElement($eCompany, $format).');';

		}

		return '<div class="'.$class.'" style="'.$ui->getSquareCss($size).'; '.$style.'"></div>';

	}

	public static function getBanner(Company $eCompany, string $width): string {

		$eCompany->expects(['id', 'banner']);

		$ui = new \media\CompanyBannerUi();

		$class = 'company-banner-view media-rectangle-view'.' ';
		$style = '';

		if($eCompany['banner'] === NULL) {

			$class .= ' media-banner-default';
			$style .= '';

		} else {

			$style .= 'background-image: url('.$ui->getUrlByElement($eCompany, 'm').');';

		}

		return '<div class="'.$class.'" style="width: '.$width.'; max-width: 100%; height: auto; aspect-ratio: 5; '.$style.'"></div>';

	}

	public static function getNavigation(): string {
		return '<span class="h-menu">'.\Asset::icon('chevron-down').'</span>';
	}

	public static function p(string $property): \PropertyDescriber {

		$d = Company::model()->describer($property, [
			'addressLine1' => s("Adresse (ligne 1)"),
			'addressLine2' => s("Adresse (ligne 2)"),
			'banner' => s("Bandeau à afficher en haut des e-mails envoyés à vos clients"),
			'city' => s("Ville"),
			'logo' => s("Logo de l'entreprise"),
			'nafCode' => s("Code NAF de l'entreprise (APE)"),
			'name' => s("Nom de l'entreprise"),
			'postalCode' => s("Code postal"),
			'siret' => s("SIRET de l'entreprise*"),
			'url' => s("Site internet"),
			'vignette' => s("Photo de présentation"),
		]);

		return $d;

	}

}
?>
