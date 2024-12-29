<?php
namespace bank;

class CashflowUi {

	public function __construct() {
		\Asset::css('bank', 'bank.css');
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

		if ($cCashflow->empty() === true) {
			return '<div class="util-info">'.s("Aucun import bancaire n'a encore été réalisé").'</div>';
		}

		$h = '';

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="tr-bordered tr-even">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$label = s("#");
							$h .= ($search ? $search->linkSort('fitid', $label) : $label);
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
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

				$lastMonth = '';
				foreach($cCashflow as $eCashflow) {

					if ($lastMonth === '' or $lastMonth !== substr($eCashflow['date'], 0, 7)) {
						$lastMonth = substr($eCashflow['date'], 0, 7);

							$h .= '<tr>';

								$h .= '<td class="td-min-content" colspan="6">';
									$h .= '<strong>'.mb_ucfirst(\util\DateUi::textual($eCashflow['date'], \util\DateUi::MONTH_YEAR)).'</strong>';
								$h .= '</td>';

							$h .= '</tr>';
					}

					$h .= '<tr>';

						$h .= '<td class="td-min-content">';
							$h .= $eCashflow['fitid'];
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

					$h .= '</tr>';
				}

				$h .= '</tbody>';
			$h .= '</table>';
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


		$h .= $form->openUrl(\company\CompanyUi::urlBank($eCompany).'/cashflow:doImport', ['id' => 'cashflow-import', 'binary' => TRUE, 'method' => 'post']);
			$h .= $form->hidden('company', $eCompany['id']);
			$h .= '<label class="btn btn-primary">';
				$h .= $form->file('ofx', ['onchange' => 'this.form.submit()', 'accept' => '.ofx']);
				$h .= s("Importer un fichier OFX depuis mon ordinateur");
			$h .= '</label>';
		$h .= $form->close();


		$h .= $form->close();

		return new \Panel(
			id: 'panel-cashflow-import',
			title: s("Importer un relevé bancaire"),
			body: $h
		);
	}

}

?>