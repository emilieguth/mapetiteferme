<?php
namespace company;

/**
 * Alert messages
 *
 */
class AlertUi {

	public static function getError(string $fqn): mixed {

		return match($fqn) {

			'Company::disabled' => s("Vous avez désactivé cette fonctionnalité sur votre entreprise."),
			'Company::demo.delete' => s("Vous ne pouvez pas supprimer la démo !"),
			'Company::name.check' => s("Merci de renseigner le nom de l'entreprise !"),
			'Employee::demo.write' => s("Vous ne pouvez pas modifier l'équipe sur la démo !"),
			'Employee::user.check' => s("Vous n'avez pas sélectionné d'utilisateur."),
			'Employee::email.check' => s("Cette adresse e-mail est invalide."),
			'Employee::email.duplicate' => s("Il y a déjà un utilisateur rattaché à votre entreprise avec cette adresse e-mail..."),
			'Employee::deleteItself' => s("Vous ne pouvez pas vous sortir vous-même de l'entreprise."),

			'Invite::email.duplicate' => s("Une invitation a déjà été lancée pour cette adresse e-mail..."),
			'Invite::email.duplicateCustomer' => s("Cette adresse e-mail est déjà utilisée pour un autre client de votre entreprise..."),

			default => null

		};

	}

	public static function getSuccess(string $fqn): ?string {

		return match($fqn) {

			'Company::created' => s("L'entreprise a bien été créée, à vous de jouer !"),
			'Company::updated' => s("L'entreprise a bien été mise à jour !"),
			'Company::closed' => s("L'entreprise a bien été supprimée !"),

			'Employee::userCreated' => s("L'utilisateur a bien été créé et peut désormais être ajouté dans l'équipe de l'entreprise !"),
			'Employee::userUpdated' => s("L'utilisateur a bien été mis à jour !"),
			'Employee::userDeleted' => s("L'utilisateur a bien été supprimé !"),
			'Employee::created' => s("L'utilisateur a bien été ajouté à l'équipe de l'entreprise !"),
			'Employee::deleted' => s("L'utilisateur a bien été retiré de l'équipe de l'entreprise !"),

			'Invite::created' => s("Un email a bien été envoyé pour rejoindre l'équipe de l'entreprise !"),
			'Invite::extended' => s("L'invitation a bien été prolongée et un e-mail avec un nouveau lien a été renvoyé !"),
			'Invite::deleted' => s("L'invitation à rejoindre l'entreprise a bien été supprimée !"),

			default => null

		};


	}

}
?>
