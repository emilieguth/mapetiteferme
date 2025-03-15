<?php
new Page(function($data) {

	\user\ConnectionLib::checkLogged();

	$data->eCompany = \company\CompanyLib::getById(GET('company'))->validate('canManage');
})
	->get('index', function($data) {

		$data->cAccount = \accounting\AccountLib::getAll();

		throw new ViewAction($data);

	})
	->post('query', function($data) {

		$query = POST('query');
		$thirdParty = POST('thirdParty', '?int');

		$data->cAccount = \accounting\AccountLib::getAll($query);

		if(post_exists('thirdParty') === TRUE) {

			$data->cAccount = \accounting\AccountLib::orderAccountsWithThirdParty($thirdParty, $data->cAccount);

		}

		throw new \ViewAction($data);

	})
	->post('queryLabel', function($data) {

		$query = POST('query');
		$thirdParty = POST('thirdParty', '?int');
		$account = POST('account', '?int');

		$labels = \journal\OperationLib::getLabels($query, $thirdParty, $account);

		if(post_exists('account')) {
			$eAccount = \accounting\AccountLib::getById($account);
			$accountClass = str_pad($eAccount['class'], 8, '0');
			if($eAccount->exists() === TRUE and in_array($accountClass, $labels) === FALSE) {
				$labels[] = $accountClass;
			}
		}

		if(mb_strlen($query) > 0) {
			$accountClass = str_pad(post('query'), 8, '0');
			if(in_array($accountClass, $labels) === FALSE) {
				array_unshift($labels, $accountClass);
			}
		}
		$data->labels = $labels;
		throw new \ViewAction($data);

	});

?>
