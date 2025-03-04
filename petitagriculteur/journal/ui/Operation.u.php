<?php
namespace journal;

class OperationUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
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

		$h .= $form->dynamicGroups($eOperation, ['accountLabel']);
		$h .= $form->group(s("Date de l'opération").' '.\util\FormUi::asterisk(), $form->date('date', $eOperation['date'] ?? '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']]));
		$h .= $form->dynamicGroups($eOperation, ['description*', 'amount*', 'type*']);

		$vatRateDefault = 0;
		if($eOperation['account']->exists() === TRUE) {
			if($eOperation['account']['vatRate'] !== NULL) {
				$vatRateDefault = $eOperation['account']['vatRate'];
			} else if($eOperation['account']['vatAccount']->exists() === TRUE) {
				$vatRateDefault = $eOperation['account']['vatAccount']['vatRate'];
			}
		}

		$h .= $form->group(
			s("Taux de TVA").' '.\util\FormUi::asterisk(),
			$form->inputGroup($form->number('vatRate*', $vatRateDefault).$form->addon('% '))
		);

		$h .= $form->group(
			content: $form->submit(s("Ajouter l'écriture"))
		);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-journal-operation-create',
			title: s("Ajouter une écriture"),
			body: $h
		);

	}

	public function getFieldsCreate(\company\Company $eCompany, \util\FormUi $form, \bank\Cashflow $eCashflow, Operation $eOperation, \accounting\FinancialYear $eFinancialYear, string $suffix, array $defaultValues): string {

		\Asset::js('journal', 'operation.js');
		$index = mb_substr($suffix, 1, mb_strlen($suffix) - 2);

		$h = '<div class="operation-write">';

			$h .= $form->dynamicGroup($eOperation, 'thirdParty'.$suffix, function($d) {
				$d->autocompleteDispatch = '[data-thirdParty="bank-cashflow-allocate"]';
			});

			$h .= $form->dynamicGroup($eOperation, 'account'.$suffix.'*', function($d) {
				$d->autocompleteDispatch = '[data-account="bank-cashflow-allocate"]';
			});

			$h .= $form->dynamicGroup($eOperation, 'accountLabel'.$suffix);
			$h .= $form->group(
				self::p('date')->label.' '.\util\FormUi::asterisk(),
				$form->date('date'.$suffix.'*', $defaultValues['date'] ?? '', ['disabled' => TRUE, 'min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']])
			);
			$h .= $form->group(
				self::p('description')->label.' '.\util\FormUi::asterisk(),
				$form->text('description'.$suffix.'*', $defaultValues['description'] ?? '')
			);
			$h .= $form->group(
				self::p('amount')->label.' '.\util\FormUi::asterisk(),
					$form->inputGroup($form->number('amount'.$suffix.'*', $defaultValues['amount'] ?? '', ['min' => 0, 'step' => 0.01, 'data-type' => 'amount', 'onchange' => 'Cashflow.fillShowHideAmountWarning('.$eCashflow['amount'].')', 'data-index' => $index]).$form->addon('€ '))
			);
			$h .= $form->group(
				self::p('type')->label.' '.\util\FormUi::asterisk(),
				$form->radio('type'.$suffix.'*', Operation::DEBIT, self::p('type')->values[Operation::DEBIT], $defaultValues['type'] ?? '', ['onchange' => 'Cashflow.fillShowHideAmountWarning('.$eCashflow['amount'].')']).
				$form->radio('type'.$suffix.'*', Operation::CREDIT, self::p('type')->values[Operation::CREDIT], $defaultValues['type'] ?? '', ['onchange' => 'Cashflow.fillShowHideAmountWarning('.$eCashflow['amount'].')'])
			);
			$h .= $form->dynamicGroup($eOperation, 'document'.$suffix);

			$vatRateDefault = 0;
			if($eOperation['account']->exists() === TRUE) {
				if($eOperation['account']['vatRate'] !== NULL) {
					$vatRateDefault = $eOperation['account']['vatRate'];
				} else if($eOperation['account']['vatAccount']->exists() === TRUE) {
					$vatRateDefault = $eOperation['account']['vatAccount']['vatRate'];
				}
			}

			$h .= '<div class="util-info">';
				$h .= s(
					"Une écriture avec une classe de compte de TVA sera automatiquement créée si la classe de compte de l'écriture est associée à une classe de compte de TVA. Ceci est vérifiable dans <link>Paramétrage > Les classes de compte</link>. Vous pouvez corriger le taux ou le montant si nécessaire.",
					['link' => '<a href="'.\company\CompanyUi::urlAccounting($eCompany).'/account" target="_blank">']
				);
			$h .= '</div>';

			$eOperation['vatRate'.$suffix] = '';
			$h .= $form->group(
				s("Taux de TVA").' '.\util\FormUi::asterisk(),
				$form->inputGroup($form->number('vatRate'.$suffix.'*',  $vatRateDefault, ['data-field' => 'vatRate', 'min' => 0, 'max' => 20, 'step' => 0.1, 'onchange' => 'Cashflow.fillShowHideAmountWarning();']).$form->addon('% '))
			);
			$h .= $form->group(
				s("Valeur de TVA (calcul automatique)"),
				$form->inputGroup($form->number('vatValue'.$suffix,  0, ['data-field' => 'vatValue', 'min' => 0.0, 'step' => 0.01]).$form->addon('€'))
			);

		$h .= '</div>';

		return $h;

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

		$h .= $form->dynamicGroups($eOperation, ['accountLabel']);
		$h .= $form->group(s("Date de l'opération").' '.\util\FormUi::asterisk(), $form->date('date', $eOperation['date'] ?? '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']]));
		$h .= $form->dynamicGroups($eOperation, ['description*', 'amount*', 'type*', 'document']);

		$h .= $form->group(
			s("Taux de TVA").' '.\util\FormUi::asterisk(),
			$form->inputGroup($form->number('vatRate*',  $eOperation['vatRate'] ?? 0, ['disabled' => 'disabled']).$form->addon('% '))
		);

		$h .= $form->group(
			content: $form->submit(s("Modifier l'écriture"))
		);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-journal-operation-create',
			title: s("Modifier une écriture"),
			body: $h
		);

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Operation::model()->describer($property, [
			'account' => s("Classe de compte"),
			'accountLabel' => s("Compte"),
			'date' => s("Date de l'opération"),
			'description' => s("Libellé"),
			'document' => s("Pièce comptable"),
			'amount' => s("Montant (HT)"),
			'type' => s("Type (débit / crédit)"),
			'thirdParty' => s("Tiers"),
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

			case 'thirdParty':
				$d->autocompleteBody = function(\util\FormUi $form, Operation $e) {
					return [
					];
				};
				(new ThirdPartyUi())->query($d, GET('company', '?int'));
				break;

			case 'document':
				$d->after = \util\FormUi::info(s("Si cette écriture a une pièce comptable spécifique (sinon, la pièce comptable de l'opération bancaire correspondante sera utilisée si elle est renseignée)."));
				$d->attributes = [
					'onchange' => 'Cashflow.copyDocument(this)'
				];
				break;


		}

		return $d;

	}

}

?>
