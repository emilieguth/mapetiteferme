<?php
namespace bank;

class CashflowUi {

	public function __construct() {
		\Asset::js('bank', 'cashflow.js');
	}

	public function getSearch(\Search $search, \accounting\FinancialYear $eFinancialYearSelected): string {

		$h = '<div id="cashflow-search" class="util-block-search stick-xs '.($search->empty(['ids']) ? 'hide' : '').'">';

		$form = new \util\FormUi();
		$url = LIME_REQUEST_PATH.'?financialYear='.$eFinancialYearSelected['id'];

		$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

		$h .= '<div>';
			$h .= $form->month('date', $search->get('date'), ['placeholder' => s("Mois")]);
			$h .= $form->text('memo', $search->get('memo'), ['placeholder' => s("Libellé")]);
		$h .= '</div>';
		$h .= '<div>';
			$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
			$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
		$h .= '</div>';

		$h .= $form->close();

		$h .= '</div>';

		return $h;

	}

	public function getCashflow(
		\company\Company $eCompany,
		\Collection $cCashflow,
		\accounting\FinancialYear $eFinancialYearSelected,
		Import $eImport,
		\Search $search,
	): string {

		\Asset::css('bank', 'cashflow.css');

		if($cCashflow->empty() === TRUE) {
			return '<div class="util-info">'.
				s("Aucun import bancaire n'a été réalisé pour l'exercice {year} (<link>importer</link>)", [
					'year' => \accounting\FinancialYearUi::getYear($eFinancialYearSelected),
					'link' => '<a href="'.\company\CompanyUi::urlBank($eCompany).'/import">',
				]).
			'</div>';
		}

		$highlightedCashflowId = GET('id', 'int');

		$h = '';

		if($eImport->exists() === TRUE) {

			$h .= '<div class="util-block-search stick-xs">';
			$h .= s(
				"Vous visualisez actuellement les opérations bancaires correspondant à l'import #{id} du {date}.",
				[
					'id' => GET('import'),
					'date' => \util\DateUi::numeric($eImport['createdAt'], \util\DateUi::DATE),
				]
			);
			$h .= '</div>';
		}

		$h .= '<div id="cashflow-list" class="dates-item-wrapper stick-sm util-overflow-sm" '.($highlightedCashflowId !== NULL ? ' onrender="CashflowList.scrollTo('.$highlightedCashflowId.');"' : '').'>';

			$h .= '<table class="table-block tr-even tr-hover">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$h .= s("Numéro");
						$h .= '</th>';
						$h .= '<th class="text-end">';
							$label = s("Date");
							$h .= ($search ? $search->linkSort('date', $label) : $label);
						$h .= '</th>';
						$h .= '<th>';
							$label = s("Libellé");
							$h .= ($search ? $search->linkSort('memo', $label) : $label);
						$h .= '</th>';
						$h .= '<th class="text-end">'.s("Débit (D)").'</th>';
						$h .= '<th class="text-end">'.s("Crédit (C)").'</th>';
						$h .= '<th class="text-center">'.s("Statut").'</th>';
						$h .= '<th></th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

				$lastMonth = '';
				foreach($cCashflow as $eCashflow) {

					if($lastMonth === '' or $lastMonth !== substr($eCashflow['date'], 0, 7)) {
						$lastMonth = substr($eCashflow['date'], 0, 7);

							$h .= '<tr class="group-row">';

								$h .= '<td class="td-min-content" colspan="8">';
									$h .= '<strong>'.mb_ucfirst(\util\DateUi::textual($eCashflow['date'], \util\DateUi::MONTH_YEAR)).'</strong>';
								$h .= '</td>';

							$h .= '</tr>';
					}

					$h .= '<tr name="cashflow-'.$eCashflow['id'].'" '.($highlightedCashflowId === $eCashflow['id'] ? ' class="row-highlight"' : '').'>';

						$h .= '<td class="text-left">';
							$h .= encode($eCashflow['id']);
						$h .= '</td>';

						$h .= '<td class="text-end">';
							$h .= \util\DateUi::numeric($eCashflow['date']);
						$h .= '</td>';

						$h .= '<td>';
							$h .= encode($eCashflow['memo']);
						$h .= '</td>';

						$h .= '<td class="text-end">';
							$h .= match($eCashflow['type']) {
								CashflowElement::DEBIT => \util\TextUi::money($eCashflow['amount']),
								default => \util\TextUi::money(0),
							};
						$h .= '</td>';

						$h .= '<td class="text-end">';
							$h .= match($eCashflow['type']) {
								CashflowElement::CREDIT => \util\TextUi::money($eCashflow['amount']),
								default => \util\TextUi::money(0),
							};
						$h .= '</td>';

					$h .= '<td class="td-min-content text-center">';
					if($eCashflow['status'] === CashflowElement::ALLOCATED) {
						$h .= '<a class="cashflow-status-label cashflow-status-'.$eCashflow['status'].'" href="'.\company\CompanyUi::urlJournal($eCompany).'/?cashflow='.$eCashflow['id'].'">';
							$h .= CashflowUi::p('status')->values[$eCashflow['status']];
						$h .= '</a>';
					} else {
						$h .= '<div class="cashflow-status-label cashflow-status-'.$eCashflow['status'].'">';
							$h .= CashflowUi::p('status')->values[$eCashflow['status']];
						$h .= '</div>';
					}
					$h .= '</td>';

					$h .= '<td>';
							if(
								$eFinancialYearSelected['status'] === \accounting\FinancialYear::OPEN
								&& $eCashflow['date'] <= $eFinancialYearSelected['endDate']
								&& $eCashflow['date'] >= $eFinancialYearSelected['startDate']
							) {
								$h .= $this->getUpdate($eCompany, $eCashflow, 'btn-outline-secondary');
							}
						$h .= '</td>';

					$h .= '</tr>';
				}

				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;

	}
	protected function getUpdate(\company\Company $eCompany, Cashflow $eCashflow, string $btn): string {

		$h = '<a data-dropdown="bottom-end" class="dropdown-toggle btn '.$btn.'">'.\Asset::icon('gear-fill').'</a>';
		$h .= '<div class="dropdown-list">';
			$h .= '<div class="dropdown-title">'.self::getName($eCashflow).'</div>';

			if($eCashflow['status'] === CashflowElement::ALLOCATED) {
				$confirm = s("Cette action supprimera les écritures actuellement liées à l'opération bancaire qui repassera l'opération bancaire au statut attente. Confirmez-vous ?");
				$h .= '<a data-ajax="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:deAllocate" post-id="'.$eCashflow['id'].'" class="dropdown-item" data-confirm="'.$confirm.'">';
					$h .= s("Annuler les écritures liées");
				$h .= '</a>';

			} else if($eCashflow['status'] === CashflowElement::WAITING) {
				$h .= '<a href="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:allocate?id='.$eCashflow['id'].'" class="dropdown-item">';
					$h .= s("Créer de nouvelles écritures");
				$h .= '</a>';
				$h .= '<a href="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:attach?id='.$eCashflow['id'].'" class="dropdown-item">';
					$h .= s("Rattacher des écritures comptables");
				$h .= '</a>';
			}

		$h .= '</div>';

		return $h;

	}

	public static function getCashflowHeader(Cashflow $eCashflow): string {

		$type = match($eCashflow['type']) {
			CashflowElement::CREDIT => s("Crédit"),
			CashflowElement::DEBIT => s("Débit"),
			default => '',
		};

		$h = '<div class="util-block stick-xs bg-background-light">';
			$h .= '<dl class="util-presentation util-presentation-2">';
				$h .= '<dt>'.s("Numéro").'</dt>';
				$h .= '<dd>'.$eCashflow['id'].'</dd>';
				$h .= '<dt>'.s("Date").'</dt>';
				$h .= '<dd>'.\util\DateUi::numeric($eCashflow['date'], \util\DateUi::DATE).'</dd>';
				$h .= '<dt>'.s("Libellé").'</dt>';
				$h .= '<dd>'.encode($eCashflow['memo']).'</dd>';
				$h .= '<dt>'.s("Type").'</dt>';
				$h .= '<dd>'.$type.'</dd>';
				$h .= '<dt>'.s("Montant").'</dt>';
				$h .= '<dd>';
					$h .= '<span name="cashflowAmount" class="hide">'.$eCashflow['amount'].'</span>';
					$h .= '<span>'.\util\TextUi::money($eCashflow['amount']).'</span>';
				$h .= '</dd>';
			$h .= '</dl>';
		$h .= '</div>';

		return $h;
	}

	public static function getAllocate(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, Cashflow $eCashflow): \Panel {

		\Asset::js('bank', 'cashflow.js');
		$h = CashflowUi::getCashflowHeader($eCashflow);

		$form = new \util\FormUi();
		$eOperation = new \journal\Operation(['account' => new Account()]);
		$index = 0;
		$defaultValues = [
			'date' => $eCashflow['date'],
			'amount' => abs($eCashflow['amount']),
			'type' => $eCashflow['type'],
			'description' => $eCashflow['memo'],
		];

		$h .= $form->openAjax(
			\company\CompanyUi::urlBank($eCompany).'/cashflow:doAllocate',
			['id' => 'bank-cashflow-allocate', 'data-account' => 'bank-cashflow-allocate', 'data-thirdParty' => 'bank-cashflow-allocate', 'autocomplete' => 'off']
		);

			$h .= $form->hidden('company', $eCompany['id']);
			$h .= $form->hidden('id', $eCashflow['id']);

			$h .= $form->asteriskInfo();

			$h .= '<div>';
				$h .= '<div class="cashflow-create-operation-title">';
					$h .= '<h4>'.s("Opération bancaire #{id}", ['id' => $eCashflow['id']]).'</h4>';
				$h .= '</div>';

				$h .= $form->group(
					self::p('document'),
					$form->text(
						'document',
						attributes: ['name' => 'cashflow[document]'] + self::p('document')->attributes)
					.self::p('document')->after
				);
			$h .= '</div>';

			$h .= '<div class="util-info">';
				$h .= s(
					"Une écriture avec une classe de compte de TVA sera automatiquement créée si la classe de compte de l'écriture est associée à une classe de compte de TVA. Ceci est vérifiable dans <link>Paramétrage > Les classes de compte</link>. Vous pouvez corriger le taux ou le montant si nécessaire.",
					['link' => '<a href="'.\company\CompanyUi::urlAccounting($eCompany).'/account" target="_blank">']
				);
			$h .= '</div>';

			$h .= '<div id="cashflow-create-operation-list">';
				$h .= self::addOperation($eCompany, $eOperation, $eFinancialYear, $eCashflow, $index, $form, $defaultValues);
			$h .= '</div>';

			$h .= '<div id="cashflow-allocate-difference-warning" class="util-danger hide">';
				$h .= s("Attention, les montants saisis doivent correspondre au montant total de la transaction. Il y a une différence de {difference}€.", ['difference' => '<span id="cashflow-allocate-difference-value">0</span>']);
			$h .= '</div>';

			$buttons = '<a id="cashflow-add-operation" onclick="Cashflow.recalculateAmounts(); return TRUE;" data-ajax="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:addAllocate" post-index="'.($index + 1).'" post-id="'.$eCashflow['id'].'" post-amount="" class="btn btn-outline-secondary">';
				$buttons .= \Asset::icon('plus-circle').'&nbsp;'.s("Ajouter une autre écriture");
			$buttons .= '</a>';
			$buttons .= '&nbsp;';
			$buttons .= $form->submit(s("Attribuer des écritures"));

			$h .= $form->group(content: $buttons);

		$h .= $form->close();

		return new \Panel(
			id: 'panel-cashflow-allocate',
			title: s("Attribuer des écritures"),
			body: $h
		);

	}

	public static function addAllocate(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, Cashflow $eCashflow, int $index): string {

		$form = new \util\FormUi();
		$eOperation = new \journal\Operation(['account' => new Account()]);
		$defaultValues = [
			'date' => $eCashflow['date'],
			'type' => $eCashflow['type'],
			'description' => $eCashflow['memo'],
		];

		return self::addOperation($eCompany, $eOperation, $eFinancialYear, $eCashflow, $index, $form, $defaultValues);

	}

	public static function addOperation(\company\Company $eCompany, \journal\Operation $eOperation, \accounting\FinancialYear $eFinancialYear, Cashflow $eCashflow, int $index, \util\FormUi $form, array $defaultValues): string {

		$suffix = '['.$index.']';

		$h = '<div class="cashflow-create-operation">';

			$h .= '<div class="util-block bg-background-light">';

				$h .= '<div class="util-title">';

				$h .= '<div class="cashflow-create-operation-title">';
					$h .= '<h4>'.s("Écriture #{number}", ['number' => $index + 1]).'</h4>';
				$h .= '</div>';

				$h .= '<div class="cashflow-create-operation-delete hide" data-index="'.$index.'">';
				$h .= '<a onclick="Cashflow.deleteOperation(this)" class="btn btn-outline-primary">'.\Asset::icon('trash').'</a>';
				$h .= '</div>';

				$h .= '</div>';

				$h .= \journal\OperationUi::getFieldsCreate($eCompany, $form, $eOperation, $eFinancialYear, $eCashflow['amount'], $suffix, $defaultValues, []);

			$h .= '</div>';

		$h .= '</div>';

		return $h;
	}

	public static function import(\company\Company $eCompany): \Panel {

		$form = new \util\FormUi();
		$h = '';

		$h .= '<div class="util-block-help">';
			$h .= '<p>'.s("Seul l'export au format <b>.ofx</b> est actuellement supporté. Ce format est disponible sur la plupart des sites bancaires.").'</p>';
			$h .= '<p>'.s("Si certains flux bancaires ont déjà été précédemment importés, ils seront ignorés.").'</p>';
		$h .= '</div>';

		$h .= '<div class="util-info">'.s("Si le compte bancaire n'existe pas encore, il sera automatiquement créé (et vous pourrez paramétrer son libellé dans Paramétrage > Les comptes bancaires).").'</div>';


		$h .= $form->openUrl(\company\CompanyUi::urlBank($eCompany).'/import:doImport', ['id' => 'cashflow-import', 'binary' => TRUE, 'method' => 'post']);
			$h .= $form->hidden('company', $eCompany['id']);
			$h .= '<label class="btn btn-primary">';
				$h .= $form->file('ofx', ['onchange' => 'this.form.submit()', 'accept' => '.ofx']);
				$h .= s("Importer un fichier OFX depuis mon ordinateur");
			$h .= '</label>';
		$h .= $form->close();

		return new \Panel(
			id: 'panel-cashflow-import',
			title: s("Importer un relevé bancaire"),
			body: $h
		);
	}

	public function getAttach(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, Cashflow $eCashflow, \Collection $cOperation): \Panel {

		\Asset::js('bank', 'cashflow.js');
		$h = CashflowUi::getCashflowHeader($eCashflow);

		if($cOperation->empty() === TRUE) {

			$h .='<div class="util-warning">'.s("Aucune écriture comptable ne peut être actuellement rattachée à cette opération bancaire.").'</div>';

		} else {

			$form = new \util\FormUi();
			$h .= $form->openAjax(\company\CompanyUi::urlBank($eCompany).'/cashflow:doAttach', ['method' => 'post', 'id' => 'cashflow-doAttach']);

				$h .= $form->hidden('id', $eCashflow['id']);
				$h .= '<span class="hide" name="cashflowAmount">'.$eCashflow['amount'].'</span>';

				$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';
					$h .= '<table class="table-block tr-even tr-hover">';

						$h .= '<thead>';
							$h .= '<tr>';
								$h .= '<th class="text-end">';
									$h .= s("Date");
								$h .= '</th>';
								$h .= '<th>';
									$h .= s("Description");
								$h .= '</th>';
								$h .= '<th>';
									$h .= s("Tiers");
								$h .= '</th>';
								$h .= '<th class="text-end">'.s("Total").'</th>';
								$h .= '<th class="text-end">'.s("Débit (D)").'</th>';
								$h .= '<th class="text-end">'.s("Crédit (C)").'</th>';
								$h .= '<th class="text-center">'.s("Choisir").'</th>';
							$h .= '</tr>';
						$h .= '</thead>';

						$h .= '<tbody>';

						foreach($cOperation as $eOperation) {

							if($eOperation['links']->empty() === FALSE) {
								// Ajouter une ligne de résumé
								$h .= '<tr class="row-bold">';
									$h .= '<td class="text-end">';
										$h .= \util\DateUi::numeric($eOperation['date']);
									$h .= '</td>';
									$h .= '<td>';
										$h .= encode($eOperation['description']);
									$h .= '</td>';

									$h .= '<td>';
									if($eOperation['thirdParty']->exists() === TRUE) {
										$h .= encode($eOperation['thirdParty']['name']);
									}
									$h .= '</td>';

									$h .= '<td class="text-end">';
										$h .= \util\TextUi::money($eOperation['totalVATIncludedAmount']);
									$h .= '</td>';

									$h .= '<td class="text-end">';
									$h .= '</td>';

									$h .= '<td class="text-end">';
									$h .= '</td>';

									$h .= '<td class="text-center">';
										$h .= $form->checkbox('operation[]', $eOperation['id']);
										$h .= '<span class="hide" name="amount" data-operation="'.$eOperation['id'].'">'.$eOperation['totalVATIncludedAmount'].'</span>';
									$h .= '</td>';
								$h .= '</tr>';
							}

							// Ligne principale
							$h .= CashflowUi::getOperationLineForAttachment($eOperation, $eOperation['links']->empty() === TRUE ? $form : NULL);

							// Lignes rattachées
							foreach($eOperation['links'] as $eOperationLinked) {
								$h .= CashflowUi::getOperationLineForAttachment($eOperationLinked, NULL);
							}

						}

						$h .= '<tr>';
							$h .= '<td colspan="6" class="text-end">'.s("Total sélectionné :").'</td>';
							$h .= '<td class="text-center"><span data-field="totalAmount"></span></td>';
						$h .= '</tr>';

						$h .= '</tbody>';

					$h .= '</table>';
				$h .= '</div>';

				$h .= '<div id="cashflow-attach-difference-warning" class="util-danger hide">';
				$h .= s("Attention, le montant de l'opération bancaire ne correspond pas au total des écritures sélectionnées. Vous pouvez quand même valider.");
				$h .= '</div>';

				$h .= '<div class="text-end">'.$form->submit(s("Rattacher"), ['class' => 'btn btn-secondary']).'</div>';

			$h .= $form->close();
		}

		return new \Panel(
			id: 'panel-cashflow-attach',
			title: s("Rattacher l'opération bancaire à une ou plusieurs écritures comptables"),
			body: $h
		);

	}

	protected static function getOperationLineForAttachment(\journal\Operation $eOperation, ?\util\FormUi $form): string {

		$h = '<tr '.($form !== NULL ? 'class="row-bold"' : '').'>';
			$h .= '<td class="text-end">';
				if($form !== NULL) {
					$h .= \util\DateUi::numeric($eOperation['date']);
				}
			$h .= '</td>';
			$h .= '<td>';
			if($form !== NULL) {
				$h .= encode($eOperation['account']['class'].' '.$eOperation['account']['description']).' - '.encode($eOperation['description']);
			} else {
				$h .= '<span class="ml-1">'.encode($eOperation['account']['class'].' '.$eOperation['account']['description']).'</span>';
			}
			$h .= '</td>';

			$h .= '<td>';
			if($eOperation['thirdParty']->exists() === TRUE and $form !== NULL) {
				$h .= encode($eOperation['thirdParty']['name']);
			}
			$h .= '</td>';

			$h .= '<td class="text-end">';
			$h .= '</td>';

			$h .= '<td class="text-end">';
			$h .= match($eOperation['type']) {
				\journal\Operation::DEBIT => \util\TextUi::money($eOperation['amount']),
				default => '',
			};
			$h .= '</td>';

			$h .= '<td class="text-end">';
			$h .= match($eOperation['type']) {
				\journal\Operation::CREDIT => \util\TextUi::money($eOperation['amount']),
				default => '',
			};
			$h .= '</td>';
			$h .= '<td class="text-center">';
				$h .= $form !== NULL
					? $form->checkbox('operation[]', $eOperation['id'])
						.'<span class="hide" name="amount" data-operation="'.$eOperation['id'].'">'.$eOperation['amount'].'</span>'
					: '';
			$h .= '</td>';
		$h .= '</tr>';

		return $h;

	}

	public static function getName(Cashflow $eCashflow): string {

		return s("Transaction #{id}", ['id' => $eCashflow['id']]);

	}

	public static function p(string $property): \PropertyDescriber {

		$d = Cashflow::model()->describer($property, [
			'date' => s("Date"),
			'document' => s("Pièce comptable"),
			'type' => s("Type"),
			'amount' => s("Montant"),
			'fitid' => s("Id transaction"),
			'name' => s("Nom"),
			'memo' => s("Libellé"),
			'account' => s("Compte"),
			'cashflow' => s("Flux"),
		]);

		switch($property) {

			case 'date' :
				$d->prepend = \Asset::icon('calendar-date');
				break;

			case 'document':
				$d->after = \util\FormUi::info(s("Nom de la pièce comptable de référence (n° facture, ...)."));
				$d->attributes = [
					'onchange' => 'Cashflow.copyDocument(this)'
				];
				break;

			case 'type':
				$d->values = [
					CashflowElement::CREDIT => s("Crédit"),
					CashflowElement::DEBIT => s("Débit"),
				];
				break;

			case 'status' :
				$d->values = [
					CashflowElement::ALLOCATED => s("Attribuée"),
					CashflowElement::WAITING => s("Attente"),
				];
				$d->shortValues = [
					CashflowElement::ALLOCATED => s("I"),
					CashflowElement::WAITING => s("A"),
				];
				break;
		}

		return $d;

	}
}

?>
