<?php
namespace journal;

/**
 * Alert messages
 *
 */
class AlertUi {

	public static function getError(string $fqn): mixed {

		return match($fqn) {

			'Operation::date.check' => s("La date doit correspondre à l'exercice fiscal actuellement ouvert."),

			default => null

		};

	}

	public static function getSuccess(string $fqn): ?string {

		return match($fqn) {

			'Operation::created' => s("L'opération a bien été enregistrée."),
			'Operation::updated' => s("La ligne a bien été mise à jour."),

			default => null

		};


	}

}
?>
