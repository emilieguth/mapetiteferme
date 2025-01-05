<?php
namespace mail;

class DesignUi {

	public static function format(\company\Company $eCompany, string $title, string $content): array {

		$html = \mail\DesignUi::getBanner($eCompany).nl2br($content);
		$text = decode(strip_tags($html));

		return [
			$title,
			$text,
			$html
		];

	}

	public static function getBanner(\company\Company $eCompany): string {

		$eCompany->expects(['banner']);

		$html = '';

		if($eCompany['banner'] !== NULL) {

			$url = (\LIME_ENV === 'dev') ? 'https://media.comptetonble.fr/company-banner/500x100/659ff8c45b5dfde6eacp.png?6' : (new \media\CompanyBannerUi())->getUrlByElement($eCompany, 'm');

			$html .= '<div>';
				$html .= \Asset::image($url, attributes: ['width: 100%; max-width: 500px; height: auto; aspect-ratio: 5']);
			$html .= '</div>';
			$html .= '<br/>';

		}

		return $html;

	}

	public static function getButton($link, $content): string {

		$html = '<a href="'.$link.'" style="border-radius: 5px; padding: 10px; color: white; background-color: #505075; text-decoration: none">'.$content.'</a>';

		return $html;

	}

}
?>
