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

		\Asset::css('journal', 'operation.css');
		\Asset::js('journal', 'operation.js');
		\Asset::js('journal', 'asset.js');
		\Asset::js('journal', 'thirdParty.js');

		$form = new \util\FormUi();

		$dialogOpen = $form->openAjax(
			\company\CompanyUi::urlJournal($eCompany).'/operation:doCreate',
			[
				'id' => 'journal-operation-create',
				'third-party-create-index' => 0,
				'class' => 'panel-dialog container',
			],
		);

		$h = $form->hidden('company', $eCompany['id']);

		$index = 0;
		$defaultValues = $eOperation->getArrayCopy();

		$h .= self::getCreateGrid($eOperation, $eFinancialYear, $index, $form, $defaultValues);

		$addButton = '<a id="add-operation" data-ajax="'.\company\CompanyUi::urlJournal($eCompany).'/operation:addOperation" post-index="'.($index + 1).'" post-amount="" post-third-party="" class="btn btn-outline-secondary">';
		$addButton .= \Asset::icon('plus-circle').'&nbsp;'.s("Ajouter une autre écriture");
		$addButton .= '</a>';

		$saveButton = $form->submit(
			s("Enregistrer l'écriture"),
			[
				'id' => 'submit-save-operation',
				'data-text-singular' => s("Enregistrer l'écriture"),
				'data-text-plural' => s(("Enregistrer les écritures")),
				'data-confirm-text-singular' => s("Il y a une incohérence de valeur de TVA, voulez-vous quand même enregistrer ?"),
				'data-confirm-text-plural' => s("Il y a plusieurs incohérences de valeur de TVA, voulez-vous quand même enregistrer ?"),
			],
		);

		$dialogClose = $form->close();

		return new \Panel(
			id: 'panel-journal-operation-create',
			title: s("Ajouter une ou plusieurs écriture(s)"),
			dialogOpen: $dialogOpen,
			dialogClose: $dialogClose,
			body: $h,
			footer: '<div class="create-operation-buttons">'.$addButton.$saveButton.'</div>',
		);

	}

	private static function getCreateHeader(bool $isFromCashflow): string {

		$h = '<div class="create-operation create-operation-headers">';

			$h .= '<h4>&nbsp;</h4>';
			$h .= '<div class="create-operation-header">'.self::p('date')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="create-operation-header">'.self::p('document')->label.'</div>';
			$h .= '<div class="create-operation-header">'.self::p('thirdParty')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="create-operation-header">'.self::p('account')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="create-operation-header">'.self::p('accountLabel')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="create-operation-header">'.self::p('description')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="create-operation-header">'.self::p('comment')->label.'</div>';
			$h .= '<div class="create-operation-header">'.s("Montant TTC").'</div>';
			$h .= '<div class="create-operation-header">'.self::p('amount')->label.' '.\util\FormUi::asterisk().'</div>';

			$h .= '<div class="operation-asset" data-is-asset="1">';
				$h .= '<h4>'.s("Immobilisation").'</h4>';
			$h .= '</div>';
			$h .= '<div class="operation-asset" data-is-asset="1">';
				$h .= \asset\AssetUi::p('type')->label.' '.\util\FormUi::asterisk();
			$h .= '</div>';
			$h .= '<div class="operation-asset" data-is-asset="1">'.\asset\AssetUi::p('acquisitionDate')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="operation-asset" data-is-asset="1">'.\asset\AssetUi::p('startDate')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="operation-asset" data-is-asset="1">'.\asset\AssetUi::p('value')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="operation-asset" data-is-asset="1">'.\asset\AssetUi::p('duration')->label.' '.\util\FormUi::asterisk().'</div>';

			$h .= '<div class="create-operation-header">'.self::p('type')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="create-operation-header">'.self::p('vatRate')->label.' '.\util\FormUi::asterisk().'</div>';
			$h .= '<div class="create-operation-header" data-wrapper="vatValue">'.self::p('vatValue')->label.' '.\util\FormUi::asterisk().'</div>';

			if($isFromCashflow === FALSE) {

				$h .= '<div class="create-operation-header">'.self::p('paymentDate')->label.' '.\util\FormUi::asterisk().'</div>';
				$h .= '<div class="create-operation-header">'.self::p('paymentMode')->label.' '.\util\FormUi::asterisk().'</div>';

			}

		$h .= '</div>';

		return $h;

	}

	private static function getAmountButtonIcons(string $type, int $index): string {

		$activeIcon = match($type) {
			'amountIncludingVAT' => 'lock',
			default => 'erase',
		};
		$h = '<div class="merchant-write hide">'.\Asset::icon('pencil').'</div>';
		$h .= '<div class="merchant-lock '.($activeIcon === 'lock' ? '' : 'hide').'">'.\Asset::icon('lock-fill').'</div>';
		$h .= '<div class="merchant-erase '.($activeIcon === 'erase' ? '' : 'hide').'">';
			$h .= '<a '.attr('onclick', "Operation.resetAmount('".$type."', ".$index.")").' title="'.s("Revenir à zéro").'">'.\Asset::icon('eraser-fill', ['style' => 'transform: scaleX(-1);']).'</a>';
		$h .= '</div>';

		return $h;
	}

	public static function getFieldsCreateGrid(
		\util\FormUi $form,
		Operation $eOperation,
		\accounting\FinancialYear $eFinancialYear,
		?string $suffix,
		array $defaultValues,
		array $disabled,
	): string {

		\Asset::js('journal', 'asset.js');
		\Asset::js('journal', 'operation.js');

		$index = ($suffix !== NULL) ? mb_substr($suffix, 1, mb_strlen($suffix) - 2) : NULL;
		$isFromCashflow = (isset($defaultValues['cashflow']) and $defaultValues['cashflow']->exists() === TRUE);

		$h = '<div class="create-operation" data-index="'.$index.'">';
			$h .= '<div class="create-operation-title">';
				$h .= '<h4>'.s("Écriture #{number}", ['number' => $index + 1]).'</h4>';

					$h .= '<div class="create-operation-actions">';
						if($isFromCashflow === TRUE) {

							$h .= '<div class="create-operation-magic" data-index="'.$index.'">';
								$h .= '<a onclick="Cashflow.recalculate('.$index.')" class="btn btn-outline-primary" title="'.s("Réinitialiser par rapport aux autres écritures").'">'.\Asset::icon('magic').'</a>';
							$h .= '</div>';

						}
					$h .= '<div class="create-operation-delete hide" data-index="'.$index.'">';
						$h .= '<a onclick="Operation.deleteOperation(this)" class="btn btn-outline-primary">'.\Asset::icon('trash').'</a>';
					$h .= '</div>';
				$h .= '</div>';
			$h .= '</div>';
			$h .= '<div data-wrapper="date'.$suffix.'">';
				$h .= $form->date('date'.$suffix, $defaultValues['date'] ?? '', [
						'min' => $eFinancialYear['startDate'],
						'max' => $eFinancialYear['endDate'],
						'data-date' => $form->getId(),
						'data-index' => $index,
					]);
			$h .='</div>';

			$h .= '<div data-wrapper="document'.$suffix.'">';
				$h .=  $form->dynamicField($eOperation, 'document'.$suffix);
			$h .='</div>';

			$h .= '<div data-wrapper="thirdParty'.$suffix.'">';
				$h .= $form->dynamicField($eOperation, 'thirdParty'.$suffix, function($d) use($form, $index, $disabled, $suffix) {
					$d->autocompleteDispatch = '[data-third-party="'.$form->getId().'"][data-index="'.$index.'"]';
					$d->attributes['data-index'] = $index;
					if(in_array('thirdParty', $disabled) === TRUE) {
						$d->attributes['disabled'] = TRUE;
					}
					$d->attributes['data-third-party'] = $form->getId();
					$d->default = fn($e, $property) => get('thirdParty');
				});
			$h .='</div>';

			$h .= '<div data-wrapper="account'.$suffix.'">';
				$h .= $form->dynamicField($eOperation, 'account'.$suffix, function($d) use($form, $index, $disabled, $suffix) {
					$d->autocompleteDispatch = '[data-account="'.$form->getId().'"][data-index="'.$index.'"]';
					$d->attributes['data-wrapper'] = 'account'.$suffix;
					$d->attributes['data-index'] = $index;
					if(in_array('account', $disabled) === TRUE) {
						$d->attributes['disabled'] = TRUE;
					}
					$d->attributes['data-account'] = $form->getId();
					$d->label .=  ' '.\util\FormUi::asterisk();
				});
			$h .='</div>';

			$h .= '<div data-wrapper="accountLabel'.$suffix.'">';
				$h .= $form->dynamicField($eOperation, 'accountLabel'.$suffix, function($d) use($form, $index, $suffix) {
					$d->autocompleteDispatch = '[data-account-label="'.$form->getId().'"][data-index="'.$index.'"]';
					$d->attributes['data-wrapper'] = 'accountLabel'.$suffix;
					$d->attributes['data-index'] = $index;
					$d->attributes['data-account-label'] = $form->getId();
					$d->label .=  ' '.\util\FormUi::asterisk();
				});
			$h .='</div>';

			$h .= '<div data-wrapper="description'.$suffix.'">';
				$h .= $form->dynamicField($eOperation, 'description'.$suffix, fn($d) => $d->default = $defaultValues['description'] ?? '');
			$h .='</div>';

			$h .= '<div data-wrapper="comment'.$suffix.'">';
				$h .= $form->dynamicField($eOperation, 'comment'.$suffix, function($d) {
					$d->default = $defaultValues['comment'] ?? '';
				});
			$h .='</div>';

			$h .= '<div data-wrapper="amountIncludingVAT'.$suffix.'">';
				$h .= $form->inputGroup($form->addon(self::getAmountButtonIcons('amountIncludingVAT', $index))
					.$form->calculation(
						'amountIncludingVAT'.$suffix,
						$defaultValues['amountIncludingVAT'] ?? '',
						[
							'min' => 0, 'step' => 0.01,
							'disabled' => TRUE,
							'data-field' => 'amountIncludingVAT',
							'data-index' => $index,
						]
					)
					.$form->addon('€ '));
			$h .='</div>';

			$h .= '<div data-wrapper="amount'.$suffix.'">';
				$h .= $form->dynamicField($eOperation, 'amount'.$suffix, function($d) use($defaultValues, $index) {
					$d->default = $defaultValues['amount'] ?? '';
					$d->attributes['min'] = 0;
					$d->attributes['step'] = 0.01;
					$d->attributes['data-field'] = 'amount';
					$d->attributes['data-index'] = $index;
					$d->prepend = OperationUi::getAmountButtonIcons('amount', $index);
				});
			$h .='</div>';

			$h .= '<div class="operation-asset" data-is-asset="1" data-index="'.$index.'">';
			$h .='</div>';

			$h .= '<div class="operation-asset" data-wrapper="asset'.$suffix.'[type]" data-is-asset="1" data-index="'.$index.'">';
				$h .= $form->radios('asset'.$suffix.'[type]', \asset\AssetUi::p('type')->values, '', [
					'data-index' => $index,
					'columns' => 2,
					'mandatory' => TRUE,
				]);
			$h .='</div>';

			$h .= '<div class="operation-asset" data-wrapper="asset'.$suffix.'[acquisitionDate]" data-is-asset="1" data-index="'.$index.'">';
				$h .= $form->date('asset'.$suffix.'[acquisitionDate]', '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']]);
			$h .='</div>';

			$h .= '<div class="operation-asset" data-wrapper="asset'.$suffix.'[startDate]" data-is-asset="1" data-index="'.$index.'">';
				$h .= $form->date('asset'.$suffix.'[startDate]', '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']]);
			$h .= '</div>';

			$h .= '<div class="operation-asset" data-wrapper="asset'.$suffix.'[value]" data-is-asset="1" data-index="'.$index.'">';
				$h .= $form->inputGroup(
					$form->number(
						'asset'.$suffix.'[value]',
						'',
						[
							'min' => 0, 'step' => 0.01,
						]
					)
					.$form->addon('€ ')
				);
			$h .= '</div>';

			$h .= '<div class="operation-asset" data-wrapper="asset'.$suffix.'[duration]" data-is-asset="1" data-index="'.$index.'">';
				$h .= $form->number('asset'.$suffix.'[duration]', '');
			$h .= '</div>';

			$h .= '<div data-wrapper="type'.$suffix.'">';
				$h .= $form->radios('type'.$suffix, self::p('type')->values, $defaultValues['type'] ?? '', [
						'data-index' => $index,
						'columns' => 2,
						'data-field' => 'type',
						'mandatory' => TRUE,
					]);
			$h .= '</div>';

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

			$h .= '<div data-wrapper="vatRate'.$suffix.'">';
				$h .= $form->inputGroup(
					$form->addon(self::getAmountButtonIcons('vatRate', $index))
					.$form->number(
						'vatRate'.$suffix,
						$vatRateDefault,
						['data-index' => $index, 'data-field' => 'vatRate', 'data-vat-rate' => $form->getId(), 'min' => 0, 'max' => 20, 'step' => 0.1],
					)
					.$form->addon('% '));
					$h .= '<div class="warning hide mt-1" data-vat-rate-warning data-index="'.$index.'">';
						$h .= s(
							"Attention : Habituellement, pour la classe <b>{class}</b> le taux de <b>{vatRate}%</b> est utilisé. Souhaitez-vous <link>l'utiliser</link> ?",
							[
								'vatRate' => '<span data-vat-rate-default data-index="'.$index.'"></span>',
								'class' => '<span data-vat-rate-class data-index="'.$index.'"></span>',
								'link' => '<a data-vat-rate-link data-index="'.$index.'">',
							],
						);
					$h .= '</div>';
			$h .= '</div>';

			$h .= '<div data-wrapper="vatValue'.$suffix.'">';
				$h .= $form->dynamicField($eOperation, 'vatValue'.$suffix, function($d) use($vatAmountDefault, $index) {
					$d->default = $vatAmountDefault ?? '';
					$d->attributes['min'] = 0;
					$d->attributes['step'] = 0.01;
					$d->attributes['data-field'] = 'vatValue';
					$d->attributes['data-index'] = $index;
					$d->prepend = OperationUi::getAmountButtonIcons('vatValue', $index);
				});
				$h .= '<div class="warning hide mt-1" data-vat-warning data-index="'.$index.'">';
					$h .= s(
						"Il y a une incohérence de calcul de TVA, souhaitiez-vous plutôt indiquer {amountVAT} ?",
						['amountVAT' => '<a onclick="Operation.updateVatValue('.$index.')" data-vat-warning-value data-index="'.$index.'"></a>'],
					);
				$h .= '</div>';
			$h .= '</div>';

			if($isFromCashflow === FALSE) {

				$h .= '<div data-wrapper="paymentDate'.$suffix.'">';
					$h .= $form->date('paymentDate'.$suffix, $defaultValues['paymentDate'] ?? '', ['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']]);
				$h .= '</div>';

				$h .= '<div data-wrapper="paymentMode'.$suffix.'">';
					$h .= $form->select(
						'paymentMode',
						\journal\OperationUi::p('paymentMode')->values,
						$defaultValues['paymentMode'] ?? '',
						['mandatory' => TRUE],
					);
				$h .= '</div>';

			}

		$h .= '</div>';

		return $h;

	}

	private static function getCreateValidate(): string {

		$h = '<div class="create-operation create-operation-validation">';

			$h .= '<h4></h4>';
			$h .= '<div class="create-operation-validate"></div>';
			$h .= '<div class="create-operation-validate"></div>';
			$h .= '<div class="create-operation-validate"></div>';
			$h .= '<div class="create-operation-validate"></div>';
			$h .= '<div class="create-operation-validate cashflow-warning">';
				$h .= '<div>';
					$h .= '<span id="cashflow-allocate-difference-warning" class="warning hide">';
					$h .= s("⚠️ Différence de <span></span>", ['span' => '<span id="cashflow-allocate-difference-value">']);
					$h .= '</span>';
				$h .= '</div>';
			$h .= '</div>';
			$h .= '<div class="create-operation-validate" data-field="amountIncludingVAT"><div><span>=</span><span data-type="value"></span></div></div>';
			$h .= '<div class="create-operation-validate" data-field="amount"><div><span>=</span><span data-type="value"></span></div></div>';

			$h .= '<div class="create-operation-validate operation-asset" data-is-asset="1"><h4></h4></div>';
			$h .= '<div class="create-operation-validate operation-asset" data-is-asset="1"></div>';
			$h .= '<div class="create-operation-validate operation-asset" data-is-asset="1"></div>';
			$h .= '<div class="create-operation-validate operation-asset" data-is-asset="1"></div>';
			$h .= '<div class="create-operation-validate operation-asset" data-is-asset="1" data-field="assetValue"><div><span>=</span><span data-type="value"></span></div></div>';
			$h .= '<div class="create-operation-validate operation-asset" data-is-asset="1"></div>';

			$h .= '<div class="create-operation-validate"></div>';
			$h .= '<div class="create-operation-validate"></div>';
			$h .= '<div class="create-operation-validate" data-field="vatValue"><div><span>=</span><span data-type="value"></span></div></div>';

		$h .= '</div>';

		return $h;

	}

	public static function getCreateGrid(
		Operation $eOperation,
		\accounting\FinancialYear $eFinancialYear,
		int $index,
		\util\FormUi $form,
		array $defaultValues,
	): string {

		$suffix = '['.$index.']';
		$isFromCashflow = ($defaultValues['cashflow']->exists() === TRUE);

		$h = '<div id="create-operation-list" class="create-operations-container" data-columns="1">';

			$h .= self::getCreateHeader($isFromCashflow);
			$h .= self::getFieldsCreateGrid($form, $eOperation, $eFinancialYear, $suffix, $defaultValues, []);

			if($isFromCashflow === TRUE) {
				$h .= self::getCreateValidate();
			}

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
			'documentDate' => s("Date de la pièce comptable"),
			'amount' => s("Montant (HT)"),
			'type' => s("Type (débit / crédit)"),
			'thirdParty' => s("Tiers"),
			'comment' => s("Commentaire"),
			'paymentMode' => s("Mode de paiement"),
			'paymentDate' => s("Date de paiement"),
			'vatRate' => s("Taux de TVA"),
			'vatValue' => s("Valeur de TVA"),
		]);

		switch($property) {

			case 'documentDate' :
			case 'paymentDate' :
			case 'date' :
				$d->prepend = \Asset::icon('calendar-date');
				break;

			case 'type':
				$d->values = [
					OperationElement::DEBIT => s("Débit"),
					OperationElement::CREDIT => s("Crédit"),
				];
				break;

			case 'account':
				$d->autocompleteBody = function(\util\FormUi $form, Operation $e) {
					return [
					];
				};
				$d->group += ['wrapper' => 'account'];
				new \accounting\AccountUi()->query($d, GET('company', '?int'));
				break;

			case 'accountLabel':
				$d->autocompleteBody = function(\util\FormUi $form, Operation $e) {
					return [
					];
				};
				$d->group += ['wrapper' => 'accountLabel'];
				new \accounting\AccountUi()->queryLabel($d, GET('company', '?int'), query: GET('query'));
				break;

			case 'vatValue' :
				$d->field = 'calculation';
				$d->append = function(\util\FormUi $form, Operation $e) {
					return $form->addon(s("€"));
				};
				break;

			case 'amount' :
				$d->field = 'calculation';
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
				break;

			case 'comment' :
				$d->attributes['data-limit'] = 250;
				break;

			case 'paymentMode' :
				$d->values = [
					OperationElement::CREDIT_CARD => s("Carte bancaire"),
					OperationElement::CHEQUE => s("Chèque bancaire"),
					OperationElement::CASH => s("Espèces"),
					OperationElement::TRANSFER => s("Virement bancaire"),
					OperationElement::DIRECT_DEBIT => s("Prélèvement"),
				];
				break;

		}

		return $d;

	}

}

?>
