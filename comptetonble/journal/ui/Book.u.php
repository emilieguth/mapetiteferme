<?php
namespace journal;

class BookUi {

	public function __construct() {
		\Asset::css('journal', 'journal.css');
	}

	public function getBookTitle(\company\Company $eCompany): string {

		$h = '<div class="util-action">';

			$h .= '<h1>';
				$h .= \s("Le Grand Livre des comptes");
			$h .= '</h1>';

			$h .= '<div>';
				$h .= '<a '. \attr('onclick', 'Lime.Search.toggle("#book-search")') .' class="btn btn-primary">'.\Asset::icon('search').'</a> ';
			$h .= '</div>';

		$h .= '</div>';

		return $h;

	}

	public function getSearch(\Search $search, \accounting\FinancialYear $eFinancialYearSelected, \bank\Cashflow $eCashflow): string {

		$h = '<div id="book-search" class="util-block-search stick-xs '.($search->empty(['ids']) ? 'hide' : '').'">';

			$form = new \util\FormUi();
			$url = \LIME_REQUEST_PATH .'?financialYear='.$eFinancialYearSelected['id'];

			$statuses = OperationUi::p('type')->values;

			$h .= $form->openAjax($url, ['method' => 'get', 'id' => 'form-search']);

				$h .= '<div>';
						$h .= $form->month('date', $search->get('date'), ['placeholder' => \s("Mois")]);
						$h .= $form->text('accountLabel', $search->get('accountLabel'), ['placeholder' => \s("Classe de compte")]);
						$h .= $form->text('description', $search->get('description'), ['placeholder' => \s("Description")]);
						$h .= $form->select('type', $statuses, $search->get('type'), ['placeholder' => \s("Type")]);
						$h .= $form->text('document', $search->get('document'), ['placeholder' => \s("Pièce comptable")]);
					$h .= '</div>';
					$h .= '<div>';
						$h .= $form->submit(\s("Chercher"), ['class' => 'btn btn-secondary']);
						$h .= '<a href="'.$url.'" class="btn btn-secondary">'.\Asset::icon('x-lg').'</a>';
				$h .= '</div>';

			$h .= $form->close();

		$h .= '</div>';

		return $h;

	}

	public function getBook(
		\company\Company $eCompany,
		\Collection $cccOperation,
		\Collection $cOperationGrouped,
		\accounting\FinancialYear $eFinancialYearSelected,
		\Search $search = new \Search()
	): string {

		if($cccOperation->empty() === TRUE) {
			return '<div class="util-info">'. \s("Aucune écriture n'a encore été enregistrée") .'</div>';
		}
		\Asset::js('util', 'form.js');
		\Asset::css('util', 'form.css');

		$h = '';

		$h .= '<div class="dates-item-wrapper stick-sm util-overflow-sm">';

			$h .= '<table class="tr-bordered td-vertical-top no-background">';

				$h .= '<thead>';
					$h .= '<tr>';
						$h .= '<th>';
							$label = \s("Date");
							$h .= ($search ? $search->linkSort('date', $label) : $label);
						$h .= '</th>';
						$h .= '<th>';
							$label = \s("Pièce");
							$h .= ($search ? $search->linkSort('document', $label) : $label);
						$h .= '</th>';
						$h .= '<th>';
							$label = \s("Description");
							$h .= ($search ? $search->linkSort('description', $label) : $label);
						$h .= '</th>';
						$h .= '<th class="text-end">'. \s("Débit (D)") .'</th>';
						$h .= '<th class="text-end">'. \s("Crédit (C)") .'</th>';
					$h .= '</tr>';
				$h .= '</thead>';

				$h .= '<tbody>';

					foreach($cccOperation as $class => $ccOperation) {

						$eOperationGrouped = $cOperationGrouped->offsetGet($class)[0];
						$eAccount = $eOperationGrouped['account'];

						foreach($ccOperation as $accountLabel => $cOperation) {

							$displayedAccountLabel = mb_strlen($accountLabel) > 0 ? $accountLabel : str_pad($eAccount['class'], 8, 0);

							$h .= '<tr>';
								$h .= '<td colspan="5">';
									$h .= '<strong>'.s("{class} - {description}", [
											'class' => $displayedAccountLabel,
											'description' => $eAccount['description'],
										]).'</strong>';
								$h .= '</td>';
							$h .= '</tr>';

							foreach($cOperation as $eOperation) {

								$h .= '<tr>';

									$h .= '<td>';
										$h .= \util\DateUi::numeric($eOperation['date']);
									$h .= '</td>';

									$h .= '<td>';
										$h .= encode($eOperation['document']);
									$h .= '</td>';

									$h .= '<td>';
										$h .= \encode($eOperation['description']);
									$h .= '</td>';

									$h .= '<td class="text-end">';
										$h .= match($eOperation['type']) {
											Operation::DEBIT => \util\TextUi::money($eOperation['amount']),
											default => '',
										};
									$h .= '</td>';

									$h .= '<td class="text-end">';
										$h .= match($eOperation['type']) {
											Operation::CREDIT => \util\TextUi::money($eOperation['amount']),
											default => '',
										};
									$h .= '</td>';

								$h .= '</tr>';

							}

							$h .= '<tr class="sub-total">';

								$h .= '<td colspan="3" class="text-end">';
									$h .= '<strong>'.s("Total pour le compte {class} :", [
										'class' => $displayedAccountLabel,
									]).'</strong>';
								$h .= '</td>';
									$h .= '<td class="text-end">';
								$h .= '<strong>'.\util\TextUi::money($eOperationGrouped['debit']).'</strong>';
								$h .= '</td>';
									$h .= '<td class="text-end">';
									$h .= '<strong>'.\util\TextUi::money($eOperationGrouped['credit']).'</strong>';
								$h .= '</td>';
							$h .= '</tr>';

							$balance = abs($eOperationGrouped['debit'] - $eOperationGrouped['credit']);
							$h .= '<tr class="sub-total">';

								$h .= '<td colspan="3" class="text-end">';
									$h .= '<strong>'.s("Solde :").'</strong>';
								$h .= '</td>';
								$h .= '<td class="text-end">';
									$h .= '<strong>'.($eOperationGrouped['debit'] > $eOperationGrouped['credit'] ? \util\TextUi::money($balance) : '').'</strong>';
								$h .= '</td>';
									$h .= '<td class="text-end">';
									$h .= '<strong>'.($eOperationGrouped['debit'] <= $eOperationGrouped['credit'] ? \util\TextUi::money($balance) : '').'</strong>';
								$h .= '</td>';
							$h .= '</tr>';
						}
					}

						// TODO : ajouter un total pour la période

				$h .= '</tbody>';
			$h .= '</table>';
		$h .= '</div>';

		return $h;

	}
}

?>