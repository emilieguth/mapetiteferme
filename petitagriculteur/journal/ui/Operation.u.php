<?php
namespace journal;

class OperationUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public static function getAccountLabelFromAccountPrefix(string $accountPrefix): string {

		return str_pad($accountPrefix, 8, 0);

	}

	public function create(\company\Company $eCompany, Operation $eOperation, \accounting\FinancialYear $eFinancialYear): \Panel {

		\Asset::js('journal', 'operation.js');
		\Asset::js('journal', 'thirdParty.js');
		$form = new \util\FormUi();

		$h = '';

		$h .= $form->openAjax(
			\company\CompanyUi::urlJournal($eCompany).'/operation:doCreate',
			[
				'id' => 'journal-operation-create',
				'third-party-create-index' => 0,
				'onrender' => 'Operation.initAutocomplete();',
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

			$index = 0;
			$defaultValues = $eOperation->getArrayCopy();

			$h .= '<div id="create-operation-list">';
				$h .= self::addOperation($eOperation, $eFinancialYear, $index, $form, $defaultValues);
			$h .= '</div>';

			$buttons = '<a id="add-operation" onclick="Operation.addOperation(); return TRUE;" data-ajax="'.\company\CompanyUi::urlJournal($eCompany).'/operation:addOperation" post-index="'.($index + 1).'" post-amount="" post-third-party="" class="btn btn-outline-secondary">';
				$buttons .= \Asset::icon('plus-circle').'&nbsp;'.s("Ajouter une autre écriture");
			$buttons .= '</a>';
			$buttons .= '&nbsp;';
			$buttons .= $form->submit(
				s("Enregistrer l'écriture"),
				['id' => 'submit-save-operation', 'data-text-singular' => s("Enregistrer l'écriture"), 'data-text-plural' => s(("Enregistrer les écritures"))],
			);

			$h .= $form->group(content: $buttons);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-journal-operation-create',
			title: s("Ajouter une ou plusieurs écriture(s)"),
			body: $h
		);

	}

	public static function addOperation(
		Operation $eOperation,
		\accounting\FinancialYear $eFinancialYear,
		int $index,
		\util\FormUi $form,
		array $defaultValues,
	): string {

		$suffix = '['.$index.']';

		$h = '<div class="create-operation">';

			$h .= '<div class="util-block bg-background-light">';

				$h .= '<div class="util-title">';

					$h .= '<div class="create-operation-title">';
					$h .= '<h4>'.s("Écriture #{number}", ['number' => $index + 1]).'</h4>';
				$h .= '</div>';

				$h .= '<div class="create-operation-delete hide" data-index="'.$index.'">';
					$h .= '<a onclick="Operation.deleteOperation(this)" class="btn btn-outline-primary">'.\Asset::icon('trash').'</a>';
				$h .= '</div>';

				$h .= '</div>';

				$h .= \journal\OperationUi::getFieldsCreate($form, $eOperation, $eFinancialYear, $suffix, $defaultValues, []);

			$h .= '</div>';

		$h .= '</div>';

		return $h;
	}

	public static function getFieldsCreate(
		\util\FormUi $form,
		Operation $eOperation,
		\accounting\FinancialYear $eFinancialYear,
		?string $suffix,
		array $defaultValues,
		array $disabled
	): string {

		\Asset::js('journal', 'asset.js');
		\Asset::js('journal', 'operation.js');
		$index = ($suffix !== NULL) ? mb_substr($suffix, 1, mb_strlen($suffix) - 2) : NULL;

		$h = '<div class="operation-write">';

			if(isset($defaultValues['cashflow']) === TRUE) {
				$h .= $form->hidden('cashflow'.$suffix, $defaultValues['cashflow']);
			}

			$h .= $form->group(
				self::p('date')->label.' '.\util\FormUi::asterisk(),
				$form->date('date'.$suffix, $defaultValues['date'] ?? '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']])
			);

			$h .= $form->dynamicGroup($eOperation, 'document'.$suffix);

			$h .= $form->dynamicGroup($eOperation, 'thirdParty'.$suffix, function($d) use($form, $index, $disabled, $suffix) {
				$d->autocompleteDispatch = '[data-third-party="'.$form->getId().'"]';
				$d->attributes['data-index'] = $index;
				if(in_array('thirdParty', $disabled) === TRUE) {
					$d->attributes['disabled'] = TRUE;
				}
				$d->attributes['data-third-party'] = $form->getId();
				$d->default = fn($e, $property) => get('thirdParty');
			});

			$h .= $form->dynamicGroup($eOperation, 'account'.$suffix, function($d) use($form, $index, $disabled, $suffix) {
				$d->autocompleteDispatch = '[data-account="'.$form->getId().'"]';
				$d->attributes['data-wrapper'] = 'account'.$suffix;
				$d->attributes['data-index'] = $index;
				if(in_array('account', $disabled) === TRUE) {
					$d->attributes['disabled'] = TRUE;
				}
				$d->attributes['data-account'] = $form->getId();
				$d->label .=  ' '.\util\FormUi::asterisk();
			});

			$h .= $form->dynamicGroup($eOperation, 'accountLabel'.$suffix, function($d) use($form, $index, $suffix) {
				$d->autocompleteDispatch = '[data-account-label="'.$form->getId().'"]';
				$d->attributes['data-wrapper'] = 'accountLabel'.$suffix;
				$d->attributes['data-index'] = $index;
				$d->attributes['data-account-label'] = $form->getId();
				$d->label .=  ' '.\util\FormUi::asterisk();
			});
			$h .= $form->group(
				self::p('description')->label.' '.\util\FormUi::asterisk(),
				$form->text('description'.$suffix, $defaultValues['description'] ?? '')
			);
			$h .= $form->group(
				s("Montant TTC").\util\FormUi::info(s("Facultatif, ne sera pas enregistré")),
					$form->inputGroup(
						$form->number(
							'amountIncludingVAT'.$suffix,
							$defaultValues['amountIncludingVAT'] ?? '',
							[
								'min' => 0, 'step' => 0.01, 'data-field' => 'amountIncludingVAT',
								'data-index' => $index,
							]
						)
						.$form->addon('€ ')
					)
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
							]
						)
						.$form->addon('€ ')
					)
			);

			$h .= '<div data-asset="'.$form->getId().'" data-index="'.$index.'" class="util-block bg-white hide">';
				$h .= '<h4>'.s("Immobilisation").'</h4>';
					$h .= $form->group(
						AssetUi::p('type')->label.' '.\util\FormUi::asterisk(),
						$form->radio(
							'asset'.$suffix.'[type]',
							AssetElement::LINEAR,
							AssetUi::p('type')->values[AssetElement::LINEAR],
							''
						)
						.$form->radio(
							'asset'.$suffix.'[type]',
							AssetElement::WITHOUT,
							AssetUi::p('type')->values[AssetElement::WITHOUT],
							'',
						)
					);
					$h .= $form->group(
						AssetUi::p('acquisitionDate')->label.' '.\util\FormUi::asterisk(),
						$form->date('asset'.$suffix.'[acquisitionDate]', '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']])
					);
					$h .= $form->group(
						AssetUi::p('startDate')->label.' '.\util\FormUi::asterisk(),
						$form->date('asset'.$suffix.'[startDate]', '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']])
					);
					$h .= $form->group(
						AssetUi::p('value')->label.' '.\util\FormUi::asterisk(),
							$form->inputGroup(
								$form->number(
									'asset'.$suffix.'[value]',
									'',
									[
										'min' => 0, 'step' => 0.01,
									]
								)
								.$form->addon('€ ')
							)
					);
					$h .= $form->group(
						AssetUi::p('duration')->label.' '.\util\FormUi::asterisk(),
						$form->number('asset'.$suffix.'[duration]', '')
					);
			$h .= '</div>';

			$h .= $form->group(
				self::p('type')->label.' '.\util\FormUi::asterisk(),
				$form->radio(
					'type'.$suffix,
					Operation::DEBIT,
					self::p('type')->values[Operation::DEBIT],
					$defaultValues['type'] ?? '',
					[
						'data-index' => $index
					]
				).
				$form->radio(
					'type'.$suffix, Operation::CREDIT,
					self::p('type')->values[Operation::CREDIT],
					$defaultValues['type'] ?? '',
					[
						'data-index' => $index
					]
				)
			);

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
				$form->inputGroup($form->number('vatRate'.$suffix,  $vatRateDefault, ['data-index' => $index, 'data-field' => 'vatRate', 'data-vat-rate' => $form->getId(), 'min' => 0, 'max' => 20, 'step' => 0.1]).$form->addon('% '))
			);
			$h .= $form->group(
				s("Valeur de TVA"),
				$form->inputGroup(
					$form->number(
						'vatValue'.$suffix,
						$vatAmountDefault,
						['data-field' => 'vatValue', 'data-vat-value' => $form->getId(), 'min' => 0.0, 'step' => 0.01, 'data-index' => $index],
					).$form->addon('€'))
			);

		$h .= '<div data-index="'.$index.'" class="util-warning hide" data-vat-warning>';
			$h .= s("Attention, le montant de TVA ne correspond pas au montant HT et au taux de TVA indiqués. Notez que pourrez tout de même enregistrer.");
		$h .= '</div>';

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

			case 'accountLabel':
				$d->autocompleteBody = function(\util\FormUi $form, Operation $e) {
					return [
					];
				};
				new \accounting\AccountUi()->queryLabel($d, GET('company', '?int'), query: GET('query'));
				break;

			case 'amount' :
				$d->append = function(\util\FormUi $form, Operation $e) {
					return $form->addon(s("€"));
				};
				$d->before = fn(\util\FormUi $form, $e) => $e->isQuick() && (int)substr($e['accountLabel'], 0, 3) !== \Setting::get('accounting\vatClass') ? \util\FormUi::info(s("Attention, pensez à répercuter ce changement sur la ligne de TVA si elle existe")) : '';
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
