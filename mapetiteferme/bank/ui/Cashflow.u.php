<?php
namespace bank;

class CashflowUi {

	public function __construct() {
		\Asset::js('bank', 'cashflow.js');
	}

	public function getSearch(\Search $search, \accounting\FinancialYear $eFinancialYearSelected): string {

		$h = '<div id="cashflow-search" class="util-block-search stick-xs '.($search->empty(['ids', 'status']) ? 'hide' : '').'">';

		$form = new \util\FormUi();
		$url = LIME_REQUEST_PATH.'?financialYear='.$eFinancialYearSelected['id'];
		$statuses = CashflowUi::p('status')->values;

		$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

		$h .= '<div>';
			$h .= $form->month('date', $search->get('date'), ['placeholder' => s("Mois")]);
			$h .= $form->text('memo', $search->get('memo'), ['placeholder' => s("Libellé")]);
			$h .= $form->select('status', $statuses, $search->get('status'), ['placeholder' => s("Statut")]);
		$h .= '</div>';
		$h .= '<div>';
			$h .= $form->submit(s("Chercher"), ['class' => 'btn btn-secondary']);
			$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
		$h .= '</div>';

		$h .= $form->close();

		$h .= '</div>';

		return $h;

	}

	public function getSummarize(
		\company\Company $eCompany,
		\Collection $nCashflow,
		\Search $search
	): string {

		$h = '<ul class="util-summarize util-summarize-overflow">';

			foreach(CashflowUi::p('status')->translation as $status => $translation) {

				$count = $nCashflow[$status]['count'] ?? 0;

				$h .= '<li '.($search->get('status') === $status ? 'class="selected"' : '').'>';

					$h .= '<a href="'.\company\CompanyUi::urlBank($eCompany).'/cashflow?'.$search->toQuery(['status']).'&status='.$status.'">';

						$h .= '<h5>';
							if($count > 1) {
								$h .= $translation['plural'];
							} else {
								$h .= $translation['singular'];
							}
						$h .='</h5>';

						$h .= '<div>'.$count.'</div>';

					$h .= '</a>';

				$h .= '</li>';

			}
		$h .= '</ul>';
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
			if($search->empty(['ids']) === FALSE) {
				return '<div class="util-info">'.
					s("Aucune opération bancaire ne correspond à vos critères de recherche.").
					'</div>';

			}
			return '<div class="util-info">'.
				s("Aucun import bancaire n'a été réalisé pour l'exercice {year} (<link>importer</link>)", [
					'year' => \accounting\FinancialYearUi::getYear($eFinancialYearSelected),
					'link' => '<a href="'.\company\CompanyUi::urlBank($eCompany).'/import">',
				]).
			'</div>';
		}

		$highlightedCashflowId = GET('id', 'int');
		$showMonthHighlight = $search->getSort() === 'date';

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

		$h .= '<div id="cashflow-list" class="stick-sm util-overflow-sm" '.($highlightedCashflowId !== NULL ? ' onrender="CashflowList.scrollTo('.$highlightedCashflowId.');"' : '').' data-render-timeout="1">';

			$h .= '<table class="tr-even tr-hover">';

				$h .= '<thead class="thead-sticky">';
					$h .= '<tr>';
						$h .= '<th>';
							$label = s("Numéro");
							$h .= ($search ? $search->linkSort('id', $label) : $label);
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

					if($showMonthHighlight and ($lastMonth === '' or $lastMonth !== substr($eCashflow['date'], 0, 7))) {
						$lastMonth = substr($eCashflow['date'], 0, 7);

							$h .= '<tr class="row-emphasis row-bold">';

								$h .= '<td class="td-min-content" colspan="8">';
									$h .= mb_ucfirst(\util\DateUi::textual($eCashflow['date'], \util\DateUi::MONTH_YEAR));
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

		if($eCompany->canWrite() === FALSE) {
			return '';
		}

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
					$h .= '<span name="cashflow-amount" class="hide">'.$eCashflow['amount'].'</span>';
					$h .= '<span>'.\util\TextUi::money($eCashflow['amount']).'</span>';
				$h .= '</dd>';
			$h .= '</dl>';
		$h .= '</div>';

		return $h;
	}

	public static function extractPaymentTypeFromCashflowDescription(string $description): ?string {

		if(mb_strpos(mb_strtolower($description), 'carte') !== FALSE) {
			return \journal\OperationElement::CREDIT_CARD;
		}

		if(
			mb_strpos(mb_strtolower($description), 'virement') !== FALSE
			or mb_strpos(mb_strtolower($description), 'sepa') !== FALSE
		) {
			return \journal\OperationElement::TRANSFER;
		}

		if(
			mb_strpos(mb_strtolower($description), 'prélèvement') !== FALSE
			or mb_strpos(mb_strtolower($description), 'prelevement') !== FALSE
		) {
			return \journal\OperationElement::DIRECT_DEBIT;
		}

		if(
			mb_strpos(mb_strtolower($description), 'chèque') !== FALSE
			or mb_strpos(mb_strtolower($description), 'cheque') !== FALSE
		) {
			return \journal\OperationElement::CHEQUE;
		}

		if(
			mb_strpos(mb_strtolower($description), 'espèce') !== FALSE
			or mb_strpos(mb_strtolower($description), 'espece') !== FALSE
		) {
			return \journal\OperationElement::CHEQUE;
		}

		return NULL;

	}

	public static function getAllocate(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, Cashflow $eCashflow): \Panel {

		\Asset::js('journal', 'operation.js');
		\Asset::js('bank', 'cashflow.js');
		\Asset::js('journal', 'asset.js');
		\Asset::js('journal', 'thirdParty.js');

		\Asset::css('journal', 'operation.css');
		\Asset::css('bank', 'cashflow.css');

		$form = new \util\FormUi();

		$dialogOpen = $form->openAjax(
			\company\CompanyUi::urlBank($eCompany).'/cashflow:doAllocate',
			[
				'id' => 'bank-cashflow-allocate',
				'third-party-create-index' => 0,
				'class' => 'panel-dialog container',
			]
		);

		$eOperation = new \journal\Operation(['account' => new Account()]);
		$index = 0;
		$defaultValues = [
			'date' => $eCashflow['date'],
			'amount' => abs($eCashflow['amount']),
			'type' => $eCashflow['type'],
			'description' => $eCashflow['memo'],
			'paymentDate' => $eCashflow['date'],
			'paymentMode' => self::extractPaymentTypeFromCashflowDescription($eCashflow['memo']),
			'cashflow' => $eCashflow,
			'amountIncludingVAT' => abs($eCashflow['amount']),
		];

		$h = $form->hidden('company', $eCompany['id']);
		$h .= $form->hidden('id', $eCashflow['id']);
		$h .= $form->hidden('type', $eCashflow['type']);
		$h .= '<span name="cashflow-amount" class="hide">'.$eCashflow['amount'].'</span>';

		$title = '<div class="panel-title-container">';
			$title .= '<h2 class="panel-title">'.encode($eCashflow['memo']).'</h2>';
			$title .= '<a class="panel-close-desktop" onclick="Lime.Panel.closeLast()">'.\Asset::icon('x').'</a>';
			$title .= '<a class="panel-close-mobile" onclick="Lime.Panel.closeLast()">'.\Asset::icon('arrow-left-short').'</a>';
		$title .= '</div>';

		$subtitle = '<h2 class="panel-subtitle">';
			$subtitle .= s(
				"Opération bancaire #<b>{number}</b> du <b>{date}</b> | <b>{type}</b> d'un montant de <b class='color-primary'>{amount}</b>",
				[
					'amount' => \util\TextUi::money(abs($eCashflow['amount'])),
					'number' => $eCashflow['id'],
					'type' => self::p('type')->values[$eCashflow['type']],
					'date' => \util\DateUi::numeric($eCashflow['date'], \util\DateUi::DATE),
				]
			);
		$subtitle .= '</h2>';
		$subtitle .= '<div class="create-operation-cashflow-general mt-1">';
			$subtitle .= '<div class="create-operation-cashflow-title">'.\journal\OperationUi::p('paymentDate').'</div>';
			$subtitle .= $form->date(
				'paymentDate',
					$defaultValues['paymentDate'] ?? '',
				['min' => $eFinancialYear['startDate'], 'max' => $eFinancialYear['endDate']],
			);
			$subtitle .= '<div class="create-operation-cashflow-title">'.\journal\OperationUi::p('paymentMode').'</div>';
			$subtitle .= $form->select(
				'paymentMode',
				\journal\OperationUi::p('paymentMode')->values,
					$defaultValues['paymentMode'] ?? '',
				['mandatory' => TRUE],
			);
		$subtitle .= '</div>';

		$h .= \journal\OperationUi::getCreateGrid($eOperation, $eFinancialYear, $index, $form, $defaultValues);

		$amountWarning = '<div id="cashflow-allocate-difference-warning" class="util-danger hide">';
			$amountWarning .= s("Attention, les montants saisis doivent correspondre au montant total de la transaction. Il y a une différence de {difference}.", ['difference' => '<span id="cashflow-allocate-difference-value">0</span>']);
		$amountWarning .= '</div>';

		$addButton = '<a id="add-operation" onclick="Cashflow.recalculateAmounts(); return TRUE;" data-ajax="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:addAllocate" post-index="'.($index + 1).'" post-id="'.$eCashflow['id'].'" post-third-party="" post-amount="" class="btn btn-outline-secondary">';
		$addButton .= \Asset::icon('plus-circle').'&nbsp;'.s("Ajouter une autre écriture");
		$addButton .= '</a>';

		$saveButton = $form->submit(
			s("Enregistrer l'écriture"),
			[
				'id' => 'submit-save-operation',
				'class' => 'btn btn-primary',
				'data-text-singular' => s("Enregistrer l'écriture"),
				'data-text-plural' => s("Enregistrer les écritures"),
				'data-confirm-text' => s("Il y a une incohérence entre les écritures saisies et le montant de l'opération bancaire. Voulez-vous vraiment les enregistrer tel quel ?"),
			],
		);

		return new \Panel(
			id         : 'panel-bank-cashflow-allocate',
			dialogOpen : $dialogOpen,
			dialogClose: $form->close(),
			body       : $h,
			header     : $title.$subtitle,
			footer     : $amountWarning.'<div class="create-operation-buttons">'.$addButton.$saveButton.'</div>',
		);

	}

	public static function addAllocate(\journal\Operation $eOperation, \accounting\FinancialYear $eFinancialYear, Cashflow $eCashflow, int $index): string {

		$form = new \util\FormUi();
		$form->open('bank-cashflow-allocate');
		$defaultValues = [
			'date' => $eCashflow['date'],
			'type' => $eCashflow['type'],
			'description' => $eCashflow['memo'],
			'cashflow' => $eCashflow,
		];

		return \journal\OperationUi::getFieldsCreateGrid($form, $eOperation, $eFinancialYear, '['.$index.']', $defaultValues, []);

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
				$h .= '<span class="hide" name="cashflow-amount">'.$eCashflow['amount'].'</span>';

				$h .= '<div class="stick-sm util-overflow-sm">';
					$h .= '<table class="tr-even tr-hover">';

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
				$d->translation = [
					CashflowElement::ALLOCATED => ['singular' => s("Attribuée"), 'plural' => s("Attribuées")],
					CashflowElement::WAITING => ['singular' => s("En attente"), 'plural' => s("En attente")],
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
