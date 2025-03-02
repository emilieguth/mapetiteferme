<?php
namespace accounting;

/**
 * Alert messages
 *
 */
class AlertUi {

	public static function getError(string $fqn): mixed {

		return match($fqn) {

			'FinancialYear::startDate.check' => s("Cette date est incluse dans un autre exercice."),
			'FinancialYear::endDate.check' => s("Cette date est incluse dans un autre exercice."),
			'FinancialYear::startDate.loseOperations' => s("En modifiant cette date, certaines écritures ne seront plus rattachées à un exercice existant."),
			'FinancialYear::endDate.loseOperations' => s("En modifiant cette date, certaines écritures ne seront plus rattachées à un exercice existant."),

			default => null

		};

	}

	public static function getSuccess(string $fqn): ?string {

		return match($fqn) {

			'FinancialYear::created' => s("L'exercice comptable a bien été créé."),
			'FinancialYear::updated' => s("L'exercice comptable a bien été mis à jour."),
			'FinancialYear::closed' => s("L'exercice comptable a bien été clôturé et le suivant créé."),

			default => null

		};


	}

}
?>
