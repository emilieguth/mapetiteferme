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

		$h .= $form->openAjax(
			\company\CompanyUi::urlJournal($eCompany).'/operation:doCreate',
			[
				'id' => 'journal-operation-create',
				'data-thirdParty' => 'journal-operation-create',
				'data-account' => 'journal-operation-create',
				'onrender' => 'Operation.checkShippingButtonStatus();'
			],
		);

		$h .= $form->asteriskInfo();

		$h .= $form->hidden('company', $eCompany['id']);

		$h .= '<div class="util-info">';
		$h .= s(
			"Une écriture avec une classe de compte de TVA sera automatiquement créée si la classe de compte de l'écriture est associée à une classe de compte de TVA. Ceci est vérifiable dans <link>Paramétrage > Les classes de compte</link>. Vous pouvez corriger le taux ou le montant si nécessaire.",
			['link' => '<a href="'.\company\CompanyUi::urlAccounting($eCompany).'/account" target="_blank">']
		);
		$h .= '</div>';

		$h .= '<div class="util-block bg-background-light" data-operation="original">';

			$h .= '<h4>'.s("Nouvelle écriture").'</h4>';

			$h .= self::getFieldsCreate($eCompany, $form, $eOperation, $eFinancialYear, NULL, '[0]', $eOperation->getArrayCopy(), []);

		$h .= '</div>';

		$buttons = $form->button(
			\Asset::icon('plus-circle').'&nbsp;'.s("🚚 Ajouter des frais de port liés"),
			['onclick' => 'Operation.addShippingBlock();', 'class' => 'btn btn-outline-secondary', 'id' => 'journal-operation-create-shipping-button'],
		);
		$buttons .= '&nbsp;';
		$buttons .= $form->submit(s("Enregistrer"));

		$h .= $form->group(content: $buttons);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-journal-operation-create',
			title: s("Ajouter une écriture"),
			body: $h
		);

	}

	public static function addShipping(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, Operation $eOperation): string {

		$form = new \util\FormUi();

		$h = '<div class="util-block bg-background-light">';

			$h .= '<h4>'.s("🚚 Frais de port").'</h4>';
			$h .= self::getFieldsCreate(
				$eCompany,
				$form,
				$eOperation,
				$eFinancialYear,
				NULL,
				'[1]',
				['date' => $eOperation['date'], 'description' => $eOperation['description'], 'type' => $eOperation['type']],
				['thirdParty', 'account'],
			);

		$h .= '</div>';

		return $h;

	}

	public static function getFieldsCreate(
		\company\Company $eCompany,
		\util\FormUi $form,
		Operation $eOperation,
		\accounting\FinancialYear $eFinancialYear,
		?float $cashflowAmount,
		?string $suffix,
		array $defaultValues,
		array $disabled
	): string {

		\Asset::js('journal', 'operation.js');
		$index = ($suffix !== NULL) ? mb_substr($suffix, 1, mb_strlen($suffix) - 2) : NULL;
		$onchange = $cashflowAmount !== NULL
			? 'Cashflow.fillShowHideAmountWarning('.abs($cashflowAmount).')'
			: 'Operation.calculateVAT('.$index.')';
		$onrender = $cashflowAmount !== NULL ? '' : 'Operation.checkShippingButtonStatus();';

		$h = '<div class="operation-write" '.($onrender !== NULL ? 'onrender="'.$onrender.'"' : '').'>';

			$h .= $form->dynamicGroup($eOperation, 'thirdParty'.$suffix, function($d) use($form, $index, $disabled) {
				$d->autocompleteDispatch = '[data-thirdParty="'.$form->getId().'"]';
				$d->attributes['data-index'] = $index;
				if(in_array('thirdParty', $disabled) === TRUE) {
					$d->attributes['disabled'] = TRUE;
				}
			});

			$h .= $form->dynamicGroup($eOperation, 'account'.$suffix, function($d) use($form, $index, $disabled) {
				$d->autocompleteDispatch = '[data-account="'.$form->getId().'"]';
				$d->attributes['data-index'] = $index;
				if(in_array('account', $disabled) === TRUE) {
					$d->attributes['disabled'] = TRUE;
				}
			});

			$h .= $form->dynamicGroup($eOperation, 'accountLabel'.$suffix);
			$h .= $form->group(
				self::p('date')->label.' '.\util\FormUi::asterisk(),
				$form->date('date'.$suffix, $defaultValues['date'] ?? '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']])
			);
			$h .= $form->group(
				self::p('description')->label.' '.\util\FormUi::asterisk(),
				$form->text('description'.$suffix, $defaultValues['description'] ?? '')
			);
			$h .= $form->group(
				self::p('amount')->label.' '.\util\FormUi::asterisk(),
					$form->inputGroup(
						$form->number(
							'amount'.$suffix,
							$defaultValues['amount'] ?? '',
							[
								'min' => 0, 'step' => 0.01, 'data-field' => 'amount',
								'data-index' => $index,
								'onchange' => $onchange
							]
						)
						.$form->addon('€ ')
					)
			);
			$h .= $form->group(
				self::p('type')->label.' '.\util\FormUi::asterisk(),
				$form->radio(
					'type'.$suffix,
					Operation::DEBIT,
					self::p('type')->values[Operation::DEBIT],
					$defaultValues['type'] ?? '',
					[
						'onchange' => $onchange
					]
				).
				$form->radio(
					'type'.$suffix, Operation::CREDIT,
					self::p('type')->values[Operation::CREDIT],
					$defaultValues['type'] ?? '',
					[
						'onchange' => $onchange
					]
				)
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
			$vatAmountDefault = $vatRateDefault !== 0 ? round(($defaultValues['amount'] ?? 0) * $vatRateDefault / 100,2) : 0;

			$eOperation['vatRate'.$suffix] = '';
			$h .= $form->group(
				s("Taux de TVA").' '.\util\FormUi::asterisk(),
				$form->inputGroup($form->number('vatRate'.$suffix,  $vatRateDefault, ['data-field' => 'vatRate', 'min' => 0, 'max' => 20, 'step' => 0.1, 'onchange' => $onchange]).$form->addon('% '))
			);
			$h .= $form->group(
				s("Valeur de TVA (calcul automatique)"),
				$form->inputGroup($form->number('vatValue'.$suffix,  $vatAmountDefault, ['data-field' => 'vatValue', 'min' => 0.0, 'step' => 0.01, 'onchange' => $onchange]).$form->addon('€'))
			);

		$h .= '</div>';

		return $h;

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
				new \accounting\AccountUi()->query($d, GET('company', '?int'));
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
				new ThirdPartyUi()->query($d, GET('company', '?int'));
				break;

			case 'document':
				$d->attributes = [
					//'onchange' => 'Cashflow.copyDocument(this)'
				];
				break;


		}

		return $d;

	}

}

?>
