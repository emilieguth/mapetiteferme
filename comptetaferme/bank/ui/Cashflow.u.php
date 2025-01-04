<?php
namespace bank;

class CashflowUi {

	public function __construct() {
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
		\Search $search = new \Search()
	): string {

		\Asset::css('bank', 'cashflow.css');

		if ($cCashflow->empty() === true) {
			return '<div class="util-info">'.
				s("Aucun import bancaire n'a été réalisé pour l'exercice {year}", ['year' => \accounting\FinancialYearUi::getYear($eFinancialYearSelected)]).
			'</div>';
		}

		$h = '';

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="tr-bordered tr-even">';

				$h .= '<thead>';
					$h .= '<tr>';
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

					if ($lastMonth === '' or $lastMonth !== substr($eCashflow['date'], 0, 7)) {
						$lastMonth = substr($eCashflow['date'], 0, 7);

							$h .= '<tr>';

								$h .= '<td class="td-min-content" colspan="7">';
									$h .= '<strong>'.mb_ucfirst(\util\DateUi::textual($eCashflow['date'], \util\DateUi::MONTH_YEAR)).'</strong>';
								$h .= '</td>';

							$h .= '</tr>';
					}

					$h .= '<tr>';

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
						$h .= '<a class="cashflow-status-label cashflow-status-'.$eCashflow['status'].'" href="'.\company\CompanyUi::urlJournal($eCompany).'/?cashflow='.$eCashflow['id'].'">';
							$h .= CashflowUi::p('status')->values[$eCashflow['status']];
						$h .= '</a>';
					$h .= '</td>';

					$h .= '<td>';
							if (
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

		$primaryList = '<a href="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:allocate?id='.$eCashflow['id'].'" class="dropdown-item">'.s("Attribuer des écritures").'</a>';;

		$secondaryList = '<a data-ajax="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:doDelete" post-id="'.$eCashflow['id'].'" class="dropdown-item" data-confirm="'.s("Confirmer la suppression de cette transaction ?").'">'.s("Supprimer").'</a>';

		$h = '<a data-dropdown="bottom-end" class="dropdown-toggle btn '.$btn.'">'.\Asset::icon('gear-fill').'</a>';
		$h .= '<div class="dropdown-list">';

			$h .= $primaryList;

			if($secondaryList) {

				$h .= '<div class="dropdown-divider"></div>';
				$h .= $secondaryList;

			}

		$h .= '</div>';

		return $h;

	}

	public static function getAllocate(\company\Company $eCompany, \accounting\FinancialYear $eFinancialYear, Cashflow $eCashflow): \Panel {

		\Asset::js('bank', 'cashflow.js');
		$h = '';

		$type = match($eCashflow['type']) {
			CashflowElement::CREDIT => s("Crédit"),
			CashflowElement::DEBIT => s("Débit"),
			default => '',
		};

		$h .= '<div class="util-block stick-xs bg-background-light">';
			$h .= '<dl class="util-presentation util-presentation-2">';
				$h .= '<dt>'.s("Date").'</dt>';
				$h .= '<dd>'.\util\DateUi::numeric($eCashflow['date'], \util\DateUi::DATE).'</dd>';
				$h .= '<dt>'.s("Libellé").'</dt>';
				$h .= '<dd>'.encode($eCashflow['memo']).'</dd>';
				$h .= '<dt>'.s("Type").'</dt>';
				$h .= '<dd>'.$type.'</dd>';
				$h .= '<dt>'.s("Montant").'</dt>';
				$h .= '<dd><span id="get-allocate-total-amount">'.$eCashflow['amount'].'</span>€</dd>';
			$h .= '</dl>';
		$h .= '</div>';

		$form = new \util\FormUi();
		$eOperation = new \journal\Operation(['account' => new Account()]);
		$index = 0;
		$defaultValues = [
			'date' => $eCashflow['date'],
			'amount' => abs($eCashflow['amount']),
			'type' => $eCashflow['type'],
			'description' => $eCashflow['memo'],
		];

		$h .= $form->openAjax(\company\CompanyUi::urlBank($eCompany).'/cashflow:doAllocate', ['id' => 'bank-cashflow-allocate', 'autocomplete' => 'off']);

			$h .= $form->hidden('company', $eCompany['id']);
			$h .= $form->hidden('id', $eCashflow['id']);

			$h .= $form->asteriskInfo();

			$h .= '<div id="cashflow-create-operation-list">';
				$h .= self::addOperation($eCompany, $eOperation, $eFinancialYear, $eCashflow, $index, $form, $defaultValues);
			$h .= '</div>';

			$h .= '<div id="cashflow-allocate-difference-warning" class="util-danger hide">';
				$h .= s("Attention, les montants saisis doivent correspondre au montant total de la transaction. Il y a une différence de {difference}€.", ['difference' => '<span id="cashflow-allocate-difference-value">0</span>']);
			$h .= '</div>';

			$buttons = '<a id="cashflow-add-operation" onclick="Cashflow.recalculateAmounts(); return true;" data-ajax="'.\company\CompanyUi::urlBank($eCompany).'/cashflow:addAllocate" post-index="'.($index + 1).'" post-id="'.$eCashflow['id'].'" post-amount="" class="btn btn-outline-secondary">';
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

			$h .= '<div class="util-block-flat bg-background-light">';

				$h .= '<div class="util-title">';

				$h .= '<div class="cashflow-create-operation-title">';
					$h .= '<h4>'.s("Écriture #{number}", ['number' => $index + 1]).'</h4>';
				$h .= '</div>';

				$h .= '<div class="cashflow-create-operation-delete hide" data-index="'.$index.'">';
				$h .= '<a onclick="Cashflow.deleteOperation(this)" class="btn btn-outline-primary">'.\Asset::icon('trash').'</a>';
				$h .= '</div>';

				$h .= '</div>';

				$h .= (new \journal\OperationUi())->getFieldsCreate($form, $eCashflow, $eOperation, $eFinancialYear, $suffix, $defaultValues);

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

	public static function p(string $property): \PropertyDescriber {

		$d = Cashflow::model()->describer($property, [
			'date' => s("Date"),
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