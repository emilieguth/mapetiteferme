<?php
namespace journal;

class OperationUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
		\Asset::js('journal', 'journal.js');
	}

	public function create(\company\Company $eCompany, Operation $eOperation, \accounting\FinancialYear $eFinancialYear): \Panel {

		\Asset::js('journal', 'operation.js');
		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax(\company\CompanyUi::urlJournal($eCompany).'/operation:doCreate', ['id' => 'journal-operation-create', 'autocomplete' => 'off']);

		$h .= $form->asteriskInfo();

		$h .= $form->hidden('company', $eCompany['id']);

		$h .= $form->dynamicGroup($eOperation, 'account*', function($d) {
			$d->autocompleteDispatch = '#journal-operation-create';
		});

		$h .= $form->dynamicGroups($eOperation, ['accountLabel*']);
		$h .= $form->group(s("Date du mouvement").' '.\util\FormUi::asterisk(), $form->date('date', $eOperation['date'] ?? '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']]));
		$h .= $form->dynamicGroups($eOperation, ['description*', 'amount*', 'type*', 'lettering']);

		$h .= $form->group(
			content: $form->submit(s("Ajouter l'entrée"))
		);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-journal-operation-create',
			title: s("Ajouter une écriture"),
			body: $h
		);

	}

	public function update(\company\Company $eCompany, Operation $eOperation, \accounting\FinancialYear $eFinancialYear): \Panel {

		\Asset::js('journal', 'operation.js');
		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax(\company\CompanyUi::urlJournal($eCompany).'/operation:doUpdate', ['id' => 'journal-operation-create', 'autocomplete' => 'off']);

		$h .= $form->asteriskInfo();

		$h .= $form->hidden('company', $eCompany['id']);
		$h .= $form->hidden('id', $eOperation['id']);

		$h .= $form->dynamicGroup($eOperation, 'account*', function($d) {
			$d->autocompleteDispatch = '#journal-operation-create';
		});

		$h .= $form->dynamicGroups($eOperation, ['accountLabel*']);
		$h .= $form->group(s("Date du mouvement").' '.\util\FormUi::asterisk(), $form->date('date', $eOperation['date'] ?? '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']]));
		$h .= $form->dynamicGroups($eOperation, ['description*', 'amount*', 'type*', 'lettering']);

		$h .= $form->group(
			content: $form->submit(s("Modifier la ligne"))
		);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-journal-operation-create',
			title: s("Modifier une ligne"),
			body: $h
		);

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Operation::model()->describer($property, [
			'account' => s("Classe de compte"),
			'accountLabel' => s("Compte"),
			'date' => s("Date du mouvement"),
			'description' => s("Libellé"),
			'document' => s("Pièce comptable"),
			'amount' => s("Montant"),
			'type' => s("Type (débit / crédit)"),
			'lettering' => s("Lettrage"),
		]);

		switch($property) {

			case 'date' :
				$d->prepend = \Asset::icon('calendar-date');
				break;

			case 'type':
				$d->values = [
					OperationElement::CREDIT => s("Crédit"),
					OperationElement::DEBIT => s("Débit"),
				];
				break;

			case 'account':
				\Asset::js('journal', 'routine.js');

				$d->autocompleteBody = function(\util\FormUi $form, Operation $e) {
					return [
					];
				};
				(new \accounting\AccountUi())->query($d, GET('company', '?int'));
				break;

			case 'amount' :
				$d->append = function(\util\FormUi $form, Operation $e) {
					return $form->addon(s("€"));
				};
				break;

		}

		return $d;

	}

}

?>