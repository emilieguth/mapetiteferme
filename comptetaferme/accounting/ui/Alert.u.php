<?php
namespace accounting;

/**
 * Alert messages
 *
 */
class AlertUi {

	public static function getError(string $fqn): mixed {

		return match($fqn) {

			'Company::disabled' => s("Vous avez désactivé cette fonctionnalité sur votre entreprise."),

			default => null

		};

	}

	public static function getSuccess(string $fqn): ?string {

		return match($fqn) {

			'FinancialYear::created' => s("L'exercice comptable a bien été créé."),
			'FinancialYear::updated' => s("L'exercice comptable a bien été mis à jour."),

			default => null

		};


	}

}
?>
