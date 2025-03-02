<?php
namespace company;

class Employee extends EmployeeElement {

	public static function getSelection(): array {

		return [
			'user' => [
				'name' => new \Sql('IF(firstName IS NULL, lastName, CONCAT(firstName, " ", lastName))'),
				'email', 'firstName', 'lastName', 'visibility', 'vignette', 'createdAt'
			],
		] + parent::getSelection();

	}

	public function build(array $properties, array $input, array $callbacks = [], ?string $for = NULL): array {

		return parent::build($properties, $input, $callbacks + [

			'email.check' => function(string $email): bool {

				return \Filter::check('email', $email);

			},

			'id.prepare' => function(int &$id): bool {

				if($id === 0) {
					$id = NULL;
				}

				return TRUE;

			},

			'id.check' => function(?int $id): bool {

				if($id === NULL) {
					return TRUE;
				}

				$this->expects(['company']);

				// On vérifie qu'on est sur la même ferme
				return Employee::model()
					->whereId($id)
					->whereCompany($this['company']['id'])
					->exists();

			},

			'role.prepare' => function(?string $role): bool {

				return ($role !== NULL);

			},

			'email.duplicate' => function(string $email): bool {

				$this->expects(['company']);

				$eUser = \user\UserLib::getByEmail($email);

				if($eUser->empty()) {
					return TRUE;
				}

				return Employee::model()
					->whereUser($eUser)
					->whereCompany($this['company'])
					->exists() === FALSE;

			},

			'email.set' => function(string $email): bool {
				$this['email'] = $email;
				return TRUE;
			},

		]);

	}

}
?>