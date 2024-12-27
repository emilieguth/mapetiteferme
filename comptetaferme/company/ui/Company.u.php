<?php
namespace company;

use main\PlaceUi;

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

	public static function urlAnalyzeWorkingTime(Company $eCompany, ?int $year = NULL, ?string $category = NULL): string {
		return self::url($eCompany).'/analyses/planning'.($year ? '/'.$year : '').($category ? '/'.$category : '');
	}

	public static function urlCultivationSeries(Company $eCompany, ?string $view = NULL, int $season = NULL): string {

		$view ??= \Setting::get('main\viewSeries');

		return match($view) {
			Employee::SEQUENCE => self::urlCultivationSequences($eCompany),
			default => self::url($eCompany).'/series'.($season ? '/'.$season : '').'?view='.$view
		};

	}

	public static function urlCultivationSoil(Company $eCompany, ?string $view = NULL, int $season = NULL): string {

		$view ??= \Setting::get('main\viewSoil');

		return match($view) {
			Employee::PLAN => self::urlSoil($eCompany, $season),
			Employee::ROTATION => self::urlHistory($eCompany, $season),
		};

	}

	public static function urlCultivationSequences(Company $eCompany): string {
		return self::url($eCompany).'/itineraires';
	}

	public static function urlCartography(Company $eCompany, int $season = NULL): string {
		return self::url($eCompany).'/carte'.($season ? '/'.$season : '');
	}

	public static function urlSoil(Company $eCompany, int $season = NULL): string {
		return self::url($eCompany).'/assolement'.($season ? '/'.$season : '');
	}

	public static function urlHistory(Company $eCompany, int $season = NULL): string {
		return self::url($eCompany).'/rotation'.($season ? '/'.$season : '');
	}

	public static function urlSelling(Company $eCompany, ?string $view = NULL): string {

		$view ??= \Setting::get('main\viewSelling');

		return match($view) {
			Employee::SALE => self::urlSellingSales($eCompany),
			Employee::PRODUCT => self::urlSellingProduct($eCompany),
			Employee::STOCK => self::urlSellingStock($eCompany),
			Employee::CUSTOMER => self::urlSellingCustomer($eCompany),
			Employee::INVOICE => self::urlSellingInvoice($eCompany)
		};

	}

	public static function urlSellingCustomer(Company $eCompany): string {
		return self::url($eCompany).'/clients';
	}

	public static function urlSellingProduct(Company $eCompany): string {
		return self::url($eCompany).'/produits';
	}

	public static function urlSellingStock(Company $eCompany): string {
		return self::url($eCompany).'/stocks';
	}

	public static function urlSellingInvoice(Company $eCompany): string {
		return self::url($eCompany).'/factures';
	}

	public static function urlSellingSales(Company $eCompany, ?string $view = NULL): string {

		$view ??= \Setting::get('main\viewSellingSales');

		return match($view) {
			Employee::ALL => self::urlSellingSalesAll($eCompany),
			Employee::PRIVATE => self::urlSellingSalesPrivate($eCompany),
			Employee::PRO => self::urlSellingSalesPro($eCompany),
			Employee::INVOICE => self::urlSellingSalesInvoice($eCompany),
			Employee::LABEL => self::urlSellingSalesLabel($eCompany)
		};

	}

	public static function urlSellingSalesAll(Company $eCompany): string {
		return self::url($eCompany).'/ventes';
	}

	public static function urlSellingSalesPrivate(Company $eCompany): string {
		return self::url($eCompany).'/ventes/particuliers';
	}

	public static function urlSellingSalesPro(Company $eCompany): string {
		return self::url($eCompany).'/ventes/professionnels';
	}

	public static function urlSellingSalesInvoice(Company $eCompany): string {
		return self::url($eCompany).'/factures';
	}

	public static function urlSellingSalesLabel(Company $eCompany): string {
		return self::url($eCompany).'/etiquettes';
	}

	public static function urlShop(Company $eCompany, ?string $view = NULL): string {

		$view ??= \Setting::get('main\viewShop');

		return match($view) {
			Employee::SHOP => self::urlShopList($eCompany),
			Employee::CATALOG => self::urlShopCatalog($eCompany),
			Employee::POINT => self::urlShopPoint($eCompany)
		};

	}

	public static function urlShopList(Company $eCompany): string {
		return self::url($eCompany).'/boutiques';
	}

	public static function urlShopCatalog(Company $eCompany): string {
		return self::url($eCompany).'/catalogues';
	}

	public static function urlShopPoint(Company $eCompany): string {
		return self::url($eCompany).'/livraison';
	}

	public static function urlAnalyzeReport(Company $eCompany, int $season = NULL): string {
		return self::url($eCompany).'/analyses/rapports'.($season ? '/'.$season : '');
	}

	public static function urlAnalyze(Company $eCompany, ?string $view = NULL): string {

		$view ??= \Setting::get('main\viewAnalyze');

		$categories = self::getAnalyzeCategories($eCompany);

		if(array_key_exists($view, $categories) === FALSE) {
			$view = array_key_first($categories);
		}

		return match($view) {
			Employee::WORKING_TIME => self::urlAnalyzeWorkingTime($eCompany),
			Employee::REPORT => self::urlAnalyzeReport($eCompany),
			Employee::SALES => self::urlAnalyzeSelling($eCompany),
			Employee::CULTIVATION => self::urlAnalyzeCultivation($eCompany)
		};

	}

	public static function urlAnalyzeSelling(Company $eCompany, ?int $year = NULL, ?string $category = NULL): string {
		return self::url($eCompany).'/analyses/ventes'.($year ? '/'.$year : '').($category ? '/'.$category : '');
	}

	public static function urlAnalyzeCultivation(Company $eCompany, ?int $season = NULL, ?string $category = NULL): string {
		return self::url($eCompany).'/analyses/cultures'.($season ? '/'.$season : '').($category ? '/'.$category : '');
	}

	public static function urlSettings(Company $eCompany): string {
		return self::url($eCompany).'/configuration';
	}

	/**
	 * Display a field to search companies
	 *
	 *
	 */
	public function query(\PropertyDescriber $d, bool $multiple = FALSE) {

		$d->prepend = \Asset::icon('house-door-fill');
		$d->field = 'autocomplete';

		$d->placeholder = s("Tapez un nom de ferme...");
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

			$h .= $form->dynamicGroups($eCompany, ['name*', 'startedAt*']);

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
			$h .= $form->dynamicGroups($eCompany, ['name', 'description', 'startedAt', 'url']);

			$h .= $form->group(
				content: $form->submit(s("Modifier"))
			);

		$h .= $form->close();

		return $h;

	}

	public function updateStockNotes(Company $eCompany): \Panel {

		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax('/selling/stock:doUpdateNote', ['autocomplete' => 'off']);

			$h .= $form->hidden('id', $eCompany['id']);

			$h .= $form->dynamicField($eCompany, 'stockNotes', function($d) {
				$d->attributes['style'] = 'height: 20rem';
			});
			$h .= '<br/>';
			$h .= $form->submit(s("Modifier"));

		$h .= $form->close();

		return new \Panel(
			id: 'panel-company-update-stock-notes',
			title: s("Modifier les notes de stock"),
			body: $h
		);

	}

	public function updateSeries(Company $eCompany): string {

		$form = new \util\FormUi();

		$h = $form->openAjax('/company/company:doUpdateSeries');

			$h .= $form->hidden('id', $eCompany);

			$input = '<div class="input-group mb-1">';
				$input .= $form->addon(s("Début en année n - 1 :"));
				$input .= $form->select('calendarMonthStart', array_slice(\util\DateUi::months(), 6, preserve_keys: TRUE), $eCompany['calendarMonthStart'], ['placeholder' => s("année non affichée"), 'onchange' => 'Company.changeCalendarMonth('.$eCompany['id'].', this)']);
			$input .= '</div>';
			$input .= '<div class="input-group mb-1">';
				$input .= $form->addon(s("Fin en année n + 1 :"));
				$input .= $form->select('calendarMonthStop', array_slice(\util\DateUi::months(), 0, -6, preserve_keys: TRUE), $eCompany['calendarMonthStop'], ['placeholder' => s("année non affichée"), 'onchange' => 'Company.changeCalendarMonth('.$eCompany['id'].', this)']);
			$input .= '</div>';

			$input .= '<div id="company-update-calendar-month">';
				$input .= (new \series\CultivationUi())->getListSeason($eCompany, date('Y'));
			$input .= '</div>';

			$h .= $form->group(s("Période affichée sur les diagrammes"), $input, ['wrapper' => 'calendarMonthStart calendarMonthStop']);

			$h .= $form->group(content: '<h3>'.s("Rotations").'</h3>');
			$h .= $form->dynamicGroups($eCompany, ['rotationYears', 'rotationExclude']);

			$h .= $form->group(
				content: $form->submit(s("Enregistrer"))
			);

		$h .= $form->close();

		return $h;

	}

	public function getMainTabs(Company $eCompany, string $tab): string {

		$prefix = '<span class="company-subnav-prefix">'.\Asset::icon('chevron-right').' </span>';

		$h = '<nav id="company-nav">';

			$h .= '<div class="company-tabs">';

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

	public function getPlanningSubNav(Company $eCompany, ?string $week = NULL): string {

		$selectedView = \Setting::get('main\viewPlanning');

		$h = '<nav id="company-subnav">';
			$h .= '<div class="company-subnav-wrapper">';

				foreach($this->getPlanningCategories($eCompany, $week) as $key => ['url' => $url, 'label' => $label]) {
					$h .= '<a href="'.$url.'" id="company-subnav-planning-'.$key.'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'">'.$label.'</a> ';
				}

			$h .= '</div>';
		$h .= '</nav>';

		return $h;
	}

	protected static function getPlanningCategories(Company $eCompany, ?string $week = NULL): array {
		return [
			'url' => CompanyUi::url($eCompany),
			'label' => s("Planning")
		];

	}

	public function getCultivationSeriesTitle(\company\Company $eCompany, ?int $selectedSeason, string $selectedView, ?int $nSeries = NULL, ?bool $firstSeries = NULL): string {

		$h = '<div class="util-action">';
			$h .= '<h1>';
				$h .= '<a class="util-action-navigation" data-dropdown="bottom-start" data-dropdown-hover="true">';
					$h .= $this->getSeriesCategories($eCompany)[$selectedView].' '.self::getNavigation();
				$h .= '</a>';
				$h .= '<div class="dropdown-list bg-primary">';
					foreach($this->getSeriesCategories($eCompany) as $key => $value) {
						$h .= '<a href="'.CompanyUi::urlCultivationSeries($eCompany, $key).'" class="dropdown-item '.($key === $selectedView ? 'selected' : '').'">'.$value.'</a> ';
					}
				$h .= '</div>';
			$h .= '</h1>';

			switch($selectedView) {

				case \company\Employee::AREA :
					$h .=  '<div>';
						if(
							$eCompany->canWrite() and
							$firstSeries === FALSE
						) {
							$h .= '<a data-get="/series/series:createFrom?farm='.$eCompany['id'].'&season='.$selectedSeason.'" class="btn btn-primary" data-ajax-class="Ajax.Query">'.\Asset::icon('plus-circle').'<span class="hide-xs-down"> '.s("Nouvelle série").'</span></a>';
						}
						if($nSeries >= 5) {
							$h .= ' <a class="btn btn-primary" '.attr('onclick', 'Lime.Search.toggle("#series-search")').'>';
								$h .= \Asset::icon('search');
							$h .= '</a>';
						}
					$h .=  '</div>';
					break;

				case \company\Employee::FORECAST:
					$h .=  '<div>';
						$h .= '<a href="/plant/forecast:create?farm='.$eCompany['id'].'&season='.$selectedSeason.'" class="btn btn-primary">'.\Asset::icon('plus-circle').'<span class="hide-xs-down"> '.s("Ajouter une espèce").'</span></a>';
					$h .=  '</div>';
					break;

				case \company\Employee::SEEDLING :
				case \company\Employee::HARVESTING :
				case \company\Employee::WORKING_TIME :
					$h .=  '<div>';
						if($nSeries >= 5) {
							$h .= '<a class="btn btn-primary" '.attr('onclick', 'Lime.Search.toggle("#series-search")').'>';
								$h .= \Asset::icon('search');
							$h .= '</a>';
						}
					$h .=  '</div>';
					break;

			}

		$h .=  '</div>';

		return $h;

	}

	public function getCultivationSeriesSearch(string $view, \company\Company $eCompany, int $season, \Search $search, \Collection $cSupplier = new \Collection()): string {

		$h = '<div id="series-search" class="util-block-search stick-xs '.($search->empty() ? 'hide' : '').'">';

			$form = new \util\FormUi();
			$url = \company\CompanyUi::urlCultivationSeries($eCompany, season: $season);

			$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

				$h .= '<div>';

					if($view === Employee::SEEDLING and $cSupplier->notEmpty()) {
						$h .= $form->select('supplier', $cSupplier, $search->get('supplier'), ['placeholder' => s("Fournisseur")]);
					}

					$h .= $form->inputGroup($form->addon(s('Largeur travaillée de planche')).$form->number('bedWidth', $search->get('bedWidth')).$form->addon(s('cm')));

					if(\Setting::get('main\viewSeries') === Employee::AREA) {

						$h .= $form->dynamicField(new Tool(['company' => $eCompany]), 'id', function($d) use ($search) {

							$d->name = 'tool';

							if($search->get('tool')->notEmpty()) {
								$d->autocompleteDefault = $search->get('tool');
							}

							$d->attributes = [
								'data-autocomplete-select' => 'submit',
								'style' => 'width: 20rem'
							];
						});

					}

					$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
					$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
				$h .= '</div>';

			$h .= $form->close();

		$h .= '</div>';

		return $h;

	}

	public function getCultivationSoilTitle(\company\Company $eCompany, int $selectedSeason, string $selectedView, \Collection $cZone): string {

		$h = '<div class="util-action">';
			$h .= '<h1>';
				$h .= '<a class="util-action-navigation" data-dropdown="bottom-start" data-dropdown-hover="true">';
					$h .= $this->getSoilCategories($eCompany)[$selectedView].' '.self::getNavigation();
				$h .= '</a>';
				$h .= '<div class="dropdown-list bg-primary">';
					foreach($this->getSoilCategories($eCompany) as $key => $value) {
						$h .= '<a href="'.CompanyUi::urlCultivationSoil($eCompany, $key).'" class="dropdown-item '.($key === $selectedView ? 'selected' : '').'">'.$value.'</a> ';
					}
				$h .= '</div>';
			$h .= '</h1>';
			$h .= '<div>';

			switch($selectedView) {

				case \company\Employee::PLAN :
					if($cZone->notEmpty()) {
						$h .= '<a href="'.\company\CompanyUi::urlCartography($eCompany, $selectedSeason).'" class="btn btn-primary">';
							$h .= \Asset::icon('geo-alt-fill').' ';
							if($eCompany->canWrite()) {
								$h .= s("Modifier le plan de la ferme");
							} else {
								$h .= s("Plan de la ferme");
							}
						$h .= '</a>';
					}
					break;

				case \company\Employee::ROTATION:
					if($cZone->notEmpty()) {
						$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#bed-rotation-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
						if($eCompany->canWrite()) {
							$h .= '<a href="/company/company:updateSeries?id='.$eCompany['id'].'" class="btn btn-primary">'.\Asset::icon('gear-fill').' '.s("Configurer").'</a>';
						}
					}
					break;

			}

			$h .= '</div>';
		$h .= '</div>';

		return $h;

	}

	public static function getSeriesCategories(Company $eCompany): array {

		$categories = [
			Employee::AREA => s("Plan de culture"),
			Employee::SEEDLING => s("Semences et plants"),
			Employee::FORECAST => s("Prévisionnel financier"),
			Employee::HARVESTING => s("Récoltes"),
			Employee::WORKING_TIME => s("Temps de travail"),
		];

		if($eCompany->hasFeatureTime() === FALSE) {
			unset($categories[Employee::WORKING_TIME]);
		}

		if($eCompany->canAnalyze() === FALSE) {
			unset($categories[Employee::FORECAST]);
		}

		return $categories;

	}

	public static function getSoilCategories(Company $eCompany): array {

		return [
			Employee::PLAN => s("Plan d'assolement"),
			Employee::ROTATION => s("Rotations"),
		];

	}

	public function getRotationSearch(\Search $search, array $seasons): string {

		$seen = [];
		$firstSeason = first($seasons);
		$lastSeason = last($seasons);

		foreach($seasons as $season) {

			if($season !== $firstSeason) {
				$seen[$season] = s("jamais entre {start} et {stop}", ['start' => $season, 'stop' => $firstSeason]);
			} else {
				$seen[$season] = s("jamais en {value}", $season);
			}

		}

		$seen[1] = s("au moins 1 fois entre {start} et {stop}", ['start' => last($seasons), 'stop' => first($seasons)]);

		for($i = 1; $i <= count($seasons); $i++) {
			$seen[$i] = p("exactement {value} fois entre {start} et {stop}", "exactement {value} fois entre {start} et {stop}", $i, ['start' => last($seasons), 'stop' => first($seasons)]);
		}

		$h = '<div id="bed-rotation-search" class="util-block-search stick-xs '.($search->empty(['cFamily']) ? 'hide' : '').' mt-1">';

			$form = new \util\FormUi();
			$url = LIME_REQUEST_PATH;

			$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

				$h .= '<div>';
					$h .= $form->select('family', $search->get('cFamily'), $search->get('family'), ['placeholder' => s("Famille..."), 'onchange' => 'Company.changeSearchFamily(this)']);
					$h .= $form->inputGroup(
						$form->addon(s("Cultivée")).
						$form->select('seen', $seen, $search->get('seen', $lastSeason), ['mandatory' => TRUE]),
						['class' => 'bed-rotation-search-seen '.($search->get('family')->notEmpty() ? NULL : 'hide')]
					);
					$h .= '<label><input type="checkbox" name="bed" value="1" '.($search->get('bed') ? 'checked="checked"' : '').'/> '.s("Uniquement les planches permanentes").'</label>';
					$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
					$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
				$h .= '</div>';
			$h .= $form->close();

		$h .= '</div>';

		return $h;

	}

	public function getSellingSubNav(Company $eCompany): string {

		$h = '<nav id="company-subnav">';
			$h .= $this->getSellingMenu($eCompany, tab: 'selling');
		$h .= '</nav>';

		return $h;

	}

	public function getSellingMenu(Company $eCompany, ?int $season = NULL, string $prefix = '', ?string $tab = NULL): string {

		$selectedView = ($tab === 'selling') ? \Setting::get('main\viewSelling') : NULL;

		$h = '<div class="company-subnav-wrapper">';

			foreach($this->getSellingCategories($eCompany) as $key => $value) {

				$h .= '<a href="'.CompanyUi::urlSelling($eCompany, $key).'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'" data-sub-tab="'.$key.'">';
					$h .= $prefix.'<span>'.$value.'</span>';
				$h .= '</a>';

			}

		$h .= '</div>';

		return $h;

	}

	protected static function getSellingCategories(Company $eCompany): array {

		$categories = [
			Employee::SALE => s("Ventes"),
			Employee::CUSTOMER => s("Clients"),
			Employee::PRODUCT => s("Produits"),
		];

		if($eCompany['hasSales']) {
			$categories += [
				Employee::INVOICE => s("Factures")
			];
		}

		if($eCompany['featureStock']) {
			$categories += [
				Employee::STOCK => s("Stocks")
			];
		}

		return $categories;

	}

	public function getSellingSalesTitle(Company $eCompany, string $selectedView): string {

		$categories = $this->getSellingSalesCategories();

		$title = $categories[$selectedView];

		$h = '<div class="util-action">';
			$h .= '<h1>';
				$h .= '<a class="util-action-navigation" data-dropdown="bottom-start" data-dropdown-hover="true">';
					$h .= $title.' '.self::getNavigation();
				$h .= '</a>';
				$h .= '<div class="dropdown-list bg-primary">';
					foreach($categories as $key => $value) {
						if($value === NULL) {
							$h .= '<div class="dropdown-divider"></div>';
						} else {
							$h .= '<a href="'.self::urlSellingSales($eCompany, $key).'" class="dropdown-item '.($key === $selectedView ? 'selected' : '').'">'.$value.'</a>';
						}
					}
				$h .= '</div>';
			$h .= '</h1>';

			switch($selectedView) {

				case Employee::ALL :
				case Employee::PRIVATE :
				case Employee::PRO :
					$h .= '<div>';
						$h .= '<a '.attr('onclick', 'Lime.Search.toggle("#sale-search")').' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
						$h .= '<a href="/selling/sale:create?farm='.$eCompany['id'].'" class="btn btn-primary">'.\Asset::icon('plus-circle').'<span class="hide-xs-down"> '.s("Nouvelle vente").'</span></a> ';
					$h .= '</div>';
					break;

			}

		$h .= '</div>';

		return $h;

	}

	protected static function getSellingSalesCategories(): array {
		return [
			Employee::ALL => s("Toutes les ventes"),
			Employee::PRO => s("Ventes aux professionnels"),
			Employee::PRIVATE => s("Ventes aux particuliers"),
			NULL,
			Employee::LABEL => s("Étiquettes de colisage"),
		];
	}

	public function getShopSubNav(Company $eCompany): string {

		$h = '<nav id="company-subnav">';
			$h .= $this->getShopMenu($eCompany, tab: 'shop');
		$h .= '</nav>';

		return $h;

	}

	public function getShopMenu(Company $eCompany, string $prefix = '', ?string $tab = NULL): string {

		$selectedView = ($tab === 'shop') ? \Setting::get('main\viewShop') : NULL;

		$h = '<div class="company-subnav-wrapper">';

			foreach($this->getShopCategories($eCompany) as $key => $value) {

				$h .= '<a href="'.CompanyUi::urlShop($eCompany, $key).'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'" data-sub-tab="'.$key.'">';
					$h .= $prefix.'<span>'.$value.'</span>';
				$h .= '</a>';

			}

		$h .= '</div>';

		return $h;

	}

	public static function getShopCategories(Company $eCompany): array {

		$categories = [
			Employee::SHOP => s("Boutiques")
		];

		if($eCompany['hasShops']) {

			$categories += [
				Employee::CATALOG => s("Catalogues"),
				Employee::POINT => s("Livraisons"),
			];

		}

		return $categories;

	}

	public function getAnalyzeSubNav(Company $eCompany): string {

		$h = '<nav id="company-subnav">';
			$h .= $this->getAnalyzeMenu($eCompany, tab: 'analyze');
		$h .= '</nav>';

		return $h;

	}

	public function getAnalyzeMenu(Company $eCompany, string $prefix = '', ?string $tab = NULL): string {

		$selectedView = ($tab === 'analyze') ? \Setting::get('main\viewAnalyze') : NULL;

		$h = '<div class="company-subnav-wrapper">';

			foreach($this->getAnalyzeCategories($eCompany) as $key => $value) {

				$h .= '<a href="'.CompanyUi::urlAnalyze($eCompany, $key).'" class="company-subnav-item '.($key === $selectedView ? 'selected' : '').'" data-sub-tab="'.$key.'">';
					$h .= $prefix.'<span>'.$value.'</span>';
				$h .= '</a>';

			}

		$h .= '</div>';

		return $h;

	}

	protected static function getAnalyzeCategories(Company $eCompany): array {

		$categories = [];

		if(
			$eCompany['hasCultivations'] and
			$eCompany->hasFeatureTime()
		) {
			$categories[Employee::WORKING_TIME] = s("Temps de travail");
		}

		if($eCompany['hasSales']) {
			$categories[Employee::SALES] = s("Ventes");
		}

		if($eCompany['hasCultivations']) {
			$categories[Employee::CULTIVATION] = s("Cultures");
		}

		if($eCompany['hasCultivations'] and $eCompany['hasSales']) {
			$categories[Employee::REPORT] = s("Rapports");
		}

		return $categories;

	}

	public function getAnalyzeReportTitle(\company\Company $eCompany, int $selectedSeason): string {

		$h = '<div class="util-action">';
			$h .= '<h1>';
				$h .= s("Rapports de production");
			$h .= '</h1>';
			$h .=  '<div>';
				if((new \analyze\Report(['company' => $eCompany]))->canCreate()) {
					$h .=  '<a href="/analyze/report:create?farm='.$eCompany['id'].'&season='.$selectedSeason.'" class="btn btn-primary">'.\Asset::icon('plus-circle').'<span class="hide-xs-down"> '.s("Nouveau rapport").'</span></a>';
				}
			$h .=  '</div>';
		$h .=  '</div>';

		return $h;

	}

	public function getAnalyzeWorkingTimeTitle(Company $eCompany, array $years, int $selectedYear, ?int $selectedMonth, ?string $selectedWeek, string $selectedView): string {

		$categories = $this->getAnalyzeWorkingTimeCategories();

		$h = '<div class="util-action">';
			$h .= '<h1>';
				$h .= '<a class="util-action-navigation" data-dropdown="bottom-start" data-dropdown-hover="true">';
					$h .= $categories[$selectedView].' '.self::getNavigation();
				$h .= '</a>';
				$h .= '<div class="dropdown-list bg-primary">';
					foreach($categories as $key => $value) {
						$h .= '<a href="'.\company\CompanyUi::urlAnalyzeWorkingTime($eCompany, $selectedYear, $key).'" class="dropdown-item '.($key === $selectedView ? 'selected' : '').'">'.$value.'</a> ';
					}
				$h .= '</div>';
			$h .= '</h1>';

			if($selectedView === Employee::TIME) {

				$h .= '<a class="dropdown-toggle btn btn-primary" data-dropdown="bottom-end">';
					$h .= \Asset::icon('calendar2-week-fill').'<span class="hide-sm-down"> '.s("Calendrier").'</span>';
				$h .= '</a>';
				$h .= '<div class="dropdown-list dropdown-list-minimalist">';
					$h .= \util\FormUi::weekSelector(
						$selectedYear,
						\company\CompanyUi::urlAnalyzeWorkingTime($eCompany, $selectedYear, $selectedView).'?week={current}',
						\company\CompanyUi::urlAnalyzeWorkingTime($eCompany, $selectedYear, $selectedView).'?month={current}',
						defaultWeek: $selectedWeek,
						showYear: FALSE
					);
				$h .= '</div>';

			}
		$h .= '</div>';

		return $h;

	}

	protected static function getAnalyzeWorkingTimeCategories(): array {
		return [
			Employee::TIME => s("Suivi du temps de travail"),
			Employee::PACE => s("Suivi de la productivité"),
			Employee::TEAM => s("Temps de travail de l'équipe"),
			Employee::PERIOD => s("Saisonnalité du travail"),
		];
	}

	public function getAnalyzeSellingTitle(\company\Company $eCompany, array $years, int $selectedYear, ?int $selectedMonth, ?string $selectedWeek, string $selectedView): string {

		$categories = $this->getAnalyzeSellingCategories($eCompany);

		$h = '<div class="util-action">';
			$h .= '<h1>';
				$h .= '<a class="util-action-navigation" data-dropdown="bottom-start" data-dropdown-hover="true">';
					$h .= $categories[$selectedView].' '.self::getNavigation();
				$h .= '</a>';
				$h .= '<div class="dropdown-list bg-primary">';
					foreach($categories as $key => $value) {
						$h .= '<a href="'.\company\CompanyUi::urlAnalyzeSelling($eCompany, $selectedYear, $key).'" class="dropdown-item '.($key === $selectedView ? 'selected' : '').'">'.$value.'</a> ';
					}
				$h .= '</div>';
			$h .= '</h1>';

			if($selectedView !== Employee::PERIOD) {

				$h .= '<a class="dropdown-toggle btn btn-primary" data-dropdown="bottom-end">';
					$h .= \Asset::icon('calendar2-week-fill').'<span class="hide-sm-down"> '.s("Calendrier").'</span>';
				$h .= '</a>';
				$h .= '<div class="dropdown-list dropdown-list-minimalist">';
					$h .= \util\FormUi::weekSelector(
						$selectedYear,
						\company\CompanyUi::urlAnalyzeSelling($eCompany, $selectedYear, $selectedView).'?week={current}',
						\company\CompanyUi::urlAnalyzeSelling($eCompany, $selectedYear, $selectedView).'?month={current}',
						defaultWeek: $selectedWeek,
						showYear: FALSE
					);
				$h .= '</div>';

			}

		$h .= '</div>';

		return $h;

	}

	protected static function getAnalyzeSellingCategories(Company $eCompany): array {

		$categories = [
			\company\Employee::ITEM => s("Ventes par culture"),
			\company\Employee::CUSTOMER => s("Ventes par client"),
			\company\Employee::SHOP => s("Boutiques en ligne"),
			\company\Employee::PERIOD => s("Saisonnalité des ventes")
		];

		if($eCompany->canPersonalData() === FALSE) {
			unset($categories[Employee::CUSTOMER]);
			unset($categories[Employee::SHOP]);
		}

		return $categories;

	}

	public function getAnalyzeCultivationTitle(\company\Company $eCompany, array $seasons, int $selectedSeason, string $selectedView, $actions): string {

		$categories = $this->getAnalyzeCultivationCategories();

		$h = '<div class="util-action">';
			$h .= '<h1>';
				$h .= '<a class="util-action-navigation" data-dropdown="bottom-start" data-dropdown-hover="true">';
					$h .= $categories[$selectedView].' '.self::getNavigation();
				$h .= '</a>';
				$h .= '<div class="dropdown-list bg-primary">';
					foreach($categories as $key => $value) {
						$h .= '<a href="'.\company\CompanyUi::urlAnalyzeCultivation($eCompany, $selectedSeason, $key).'" class="dropdown-item '.($key === $selectedView ? 'selected' : '').'">'.$value.'</a> ';
					}
				$h .= '</div>';
			$h .= '</h1>';
			$h .= $actions;
		$h .= '</div>';

		return $h;

	}

	protected static function getAnalyzeCultivationCategories(): array {
		return [
			\company\Employee::AREA => s("Surfaces cultivées"),
			\company\Employee::PLANT => s("Espèces cultivées"),
			\company\Employee::FAMILY => s("Familles cultivées"),
			\company\Employee::ROTATION => s("Rotations"),
		];
	}

	public function getSettingsSubNav(Company $eCompany): string {

		$h = '<nav id="company-subnav">';
			$h .= '<div class="company-subnav-wrapper">';

				foreach($this->getSettingsCategories($eCompany) as $key => ['url' => $url, 'label' => $label]) {
					$h .= '<a href="'.$url.'" class="company-subnav-item">'.$label.'</a> ';
				}

			$h .= '</div>';
		$h .= '</nav>';

		return $h;

	}

	protected static function getSettingsCategories(Company $eCompany): array {

		return [[
			'url' => CompanyUi::urlSettings($eCompany),
			'label' => s("Paramétrage")
		]];

	}

	public function getWebsiteSubNav(Company $eCompany, ?string $page = NULL): string {

		$h = '<nav id="company-subnav" class="company-subnav-settings">';
			$h .= '<div class="company-subnav-wrapper">';
				$h .= '<a href="'.self::urlSettings($eCompany).'" class="company-subnav-link">'.s("Configuration").'</a>';
				$h .= '<div class="company-subnav-separator">'.\Asset::icon('chevron-right').'</div>';
				$h .= '<a href="/website/manage?id='.$eCompany['id'].'" class="company-subnav-link">'.s("Site internet").'</a>';
				$h .= '<div class="company-subnav-separator">'.\Asset::icon('chevron-right').'</div>';
				$h .= '<div class="company-subnav-text">'.encode($page).'</div>';
			$h .= '</div>';
		$h .= '</nav>';

		return $h;

	}

	public function getSettings(Company $eCompany): string {

		$h = '';

		$h .= '<div class="util-block-optional">';

			$h .= '<h2>'.s("La ferme").'</h2>';

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

	public function getPanel(Company $eCompany): string {

		$h = '';

		$h .= '<a href="'.$eCompany->getHomeUrl().'" class="farmer-companies-item">';

			$h .= '<div class="farmer-companies-item-vignette">';
				$h .= self::getVignette($eCompany, '6rem');
			$h .= '</div>';
			$h .= '<div class="farmer-companies-item-content">';
				$h .= '<h4>';
					$h .= encode($eCompany['name']);
				$h .= '</h4>';
				$h .= '<div class="farmer-companies-item-infos">';

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
			'name' => s("Nom de l'entreprise"),
			'vignette' => s("Photo de présentation"),
			'description' => s("Présentation de l'entreprise"),
			'startedAt' => s("Année de création"),
			'url' => s("Site internet"),
			'logo' => s("Logo de l'entreprise"),
			'banner' => s("Bandeau à afficher en haut des e-mails envoyés à vos clients"),
		]);

		return $d;

	}

}
?>
