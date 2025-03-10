<?php
namespace journal;

/**
 * Alert messages
 *
 */
class AlertUi {

	public static function getError(string $fqn): mixed {

		return match($fqn) {

			'Operation::allocate.accountsCheck' => s("Veuillez sélectionner au moins une classe de compte."),
			'Operation::allocate.noOperation' => s("Aucune opération n'a pu être enregistrée."),
			'Operation::date.check' => s("La date doit correspondre à l'exercice fiscal actuellement ouvert."),
			'Operation::account.check' => s("N'oubliez pas de choisir une classe de compte !"),

			default => null

		};

	}

	public static function getSuccess(string $fqn): ?string {

		return match($fqn) {

			'Operation::created' => s("L'écriture a bien été enregistrée."),
			'Operation::createdSeveral' => s("Les écritures ont bien été enregistrées."),
			'Operation::deleted' => s("L'écriture a bien été supprimée."),

			'ThirdParty::created' => s("Le tiers a bien été créé."),

			default => null

		};


	}

}
?>
