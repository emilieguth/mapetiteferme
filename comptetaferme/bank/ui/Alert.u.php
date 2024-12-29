<?php
namespace bank;

Class AlertUi {

	public static function getError(string $fqn): mixed {

		return match($fqn) {

			'ofxSize' => s("Votre import ne peut pas excéder 1 Mo, merci de réduire la taille de votre fichier."),
			'ofxError' => s("Une erreur est survenue lors de l'import de votre fichier, merci de réessayer."),

			default => null,
		};

	}

	public static function getSuccess(string $fqn): ?string {

		return match($fqn) {

			'Cashflow::imported' => s("L'import de votre relevé bancaire a bien été effectué !"),

			default => null,

		};


	}

}

?>