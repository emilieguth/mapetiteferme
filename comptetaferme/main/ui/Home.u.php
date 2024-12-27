<?php
namespace main;

class HomeUi {

	public function __construct() {

		\Asset::css('main', 'home.css');

	}

	public function getCompanies(\Collection $cCompany): string {

		$h = '';

		if($cCompany->empty()) {
			if((new \company\Company())->canCreate()) {
				$h .= (new \company\FarmerUi())->getNoFarms();
			} else {
				$h .= '';
			}
		} else {

			$h .= '<h2>'.($cCompany->count() === 1 ? s("Mon entreprise") : s("Mes entreprises")).'</h2>';
			$h .= (new \company\EmployeeUi())->getMyCompanies($cCompany);

		}

		return $h;

	}

	public function getBlog(\website\News $eNews, bool $displayFallback): string {

		\Asset::css('main', 'font-oswald.css');

		if($eNews->empty()) {

			if($displayFallback === FALSE) {
				return '';
			}

			$h = '<h2>'.s("Quoi de neuf sur {siteName} ?").'</h2>';

			$h .= '<div class="home-blog bg-info util-block-flat">';
				$h .= \Asset::image('main', 'favicon.png', ['style' => 'width: 6rem; height: 6rem']).'';
				$h .= '<div>';
					$h .= '<p class="font-oswald" style="font-size: 1.3rem; line-height: 1.3">'.s("Suivez le blog de {siteName} pour retrouver les annonces de nouvelles fonctionnalités, la feuille de route avec les priorités de développement pour les mois à venir  et des ressources pour faciliter la prise en main du site !").'</p>';
					$h .= '<a href="https://blog.comptetaferme.fr/" target="_blank" class="btn btn-secondary">'.\Asset::icon('chevron-right').' '.s("Découvrir le blog").'</a>';
				$h .= '</div>';
			$h .= '</div>';

		} else {

			$content = (new \editor\ReadorFormatterUi())->getFromXml($eNews['content']);

			$start = strpos($content, '<p>') + 3;
			$length = strpos($content, '</p>') - $start;

			$content = substr($content, $start, $length);

			$h = '<h3>'.s("Du nouveau sur {siteName} !").'</h3>';

			$h .= '<div class="home-blog bg-info util-block-flat">';
				$h .= '<div>';
					$h .= \Asset::image('main', 'favicon.png').'';
				$h .= '</div>';
				$h .= '<div>';
					$h .= '<h4 class="mb-0 color-secondary">'.\util\DateUi::textual($eNews['publishedAt'], \util\DateUi::DATE).'</h4>';
					$h .= '<h2 class="font-oswald">';
						$h .= encode($eNews['title']);
					$h .= '</h2>';
					$h .= '<div>';
						$h .= '<p>'.$content.'</p>';
						$h .= '<a href="https://blog.comptetaferme.fr/" target="_blank" class="btn btn-secondary">'.\Asset::icon('chevron-right').' '.s("Lire la suite").'</a>';
					$h .= '</div>';
				$h .= '</div>';
			$h .= '</div>';

		}

		return $h;

	}

	public function getCustomer(\user\Role $eRole): string {

		$class = $eRole->empty() ? '' : ($eRole['fqn'] === 'customer' ? 'selected' : 'other');

		$h = '<a href="/user/signUp?role=customer" class="home-user-type home-user-type-'.$class.'">';
			$h .= '<h2>👨‍🍳</h2>';
			$h .= '<h4>'.s("Je suis client / cliente").'</h4>';
		$h .= '</a>';

		return $h;

	}

	public function getCompany(\user\Role $eRole): string {

		$class = $eRole->empty() ? '' : ($eRole['fqn'] === 'farmer' ? 'selected' : 'other');

		$h = '<a href="/user/signUp?role=farmer" class="home-user-type home-user-type-'.$class.'">';
			$h .= '<h2>👩‍🌾</h2>';
			$h .= '<h4>'.s("Je suis producteur / productrice").'</h4>';
		$h .= '</a>';

		return $h;

	}
	
	public function getPoints(): string {

		\Asset::css('main', 'font-oswald.css');

		$h = '<h2>'.s("Principes de conception de {siteName}").'</h2>';
		
		$h .= '<div class="home-points">';
			$h .= '<div class="home-point" style="grid-column: span 2">';
				$h .= \Asset::icon('inboxes');
				$h .= '<h4>'.s("Toutes les fonctionnalités sont indépendantes,<br/>vous utilisez seulement celles adaptées à votre ferme !").'</h4>';
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
				$h .= \Asset::icon('people');
				$h .= '<h4>'.s("Développé par et pour<br/>des producteurs").'</h4>';
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
