<?php
namespace main;

class HomeUi {

	public function __construct() {

		\Asset::css('main', 'home.css');

	}

	public function getCompanies(\Collection $cCompany): string {

		$h = '';

		if($cCompany->empty()) {
			if(new \company\Company()->canCreate()) {
				$h .= new \company\EmployeeUi()->getNoCompany();
			} else {
				$h .= '';
			}
		} else {

			$h .= '<h2>'.($cCompany->count() === 1 ? s("Ma ferme") : s("Mes fermes")).'</h2>';
			$h .= new \company\EmployeeUi()->getMyCompanies($cCompany);

		}

		return $h;

	}

	public function getPoints(): string {

		\Asset::css('main', 'font-oswald.css');

		$h = '<h2>'.s("Principes de conception de {siteName}").'</h2>';
		
		$h .= '<div class="home-points">';
			$h .= '<div class="home-point" style="grid-column: span 2">';
				$h .= \Asset::icon('inboxes');
				$h .= '<h4>'.s("Toutes les fonctionnalités sont indépendantes,<br/>vous utilisez seulement celles adaptées à votre activité !").'</h4>';
			$h .= '</div>';
			$h .= '<div class="home-point" style="grid-column: span 2">';
				$h .= \Asset::icon('columns-gap');
				$h .= '<h4>'.s("Les interfaces sont simples et intuitives,<br/>elles s'adaptent à vos pratiques").'</h4>';
			$h .= '</div>';
		$h .= '</div>';
		
		$h .= '<div class="home-points">';
			$h .= '<div class="home-point">';
				$h .= \Asset::icon('lock');
				$h .= '<h4>'.s("Vos données ne sont<br/>ni vendues, ni partagées").'</h4>';
			$h .= '</div>';
			$h .= '<div class="home-point">';
				$h .= \Asset::icon('cup-hot');
				$h .= '<h4>'.s("Conçu pour réduire la charge mentale<br/>sans décider à votre place").'</h4>';
			$h .= '</div>';
			$h .= '<div class="home-point">';
				$h .= \Asset::icon('flower2');
				$h .= '<h4>'.s("Développé dans le respect<br/>des bonnes pratiques écologiques").'</h4>';
			$h .= '</div>';
			$h .= '<div class="home-point">';
				$h .= \Asset::icon('phone');
				$h .= '<h4>'.s("Accessible facilement<br/>sur ordinateur et téléphone").'</h4>';
			$h .= '</div>';
		$h .= '</div>';

		return $h;

	}

}
?>
