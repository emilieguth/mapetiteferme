<?php
(new \company\CompanyPage(
		function($data) {
			\user\ConnectionLib::checkLogged();
		}
	))
	->getCreateElement(fn($data) => new \company\Company([
		'owner' => \user\ConnectionLib::getOnline()
	]))
	->create()
	->doCreate(function($data) {
		throw new RedirectAction(\company\CompanyUi::url($data->e).'?success=company:Company.created');
	});

(new \company\CompanyPage())
	->applyElement(function($data, \company\Company $e) {
		$e->validate('canWrite');
	})
	->update(function($data) {

		$data->eCompany = $data->e;
		\company\EmployeeLib::register($data->e);

		throw new ViewAction($data);

	})
	->doUpdate(fn() => throw new ReloadAction('company', 'Company.updated'))
	->write('doClose', function($data) {

		$data->e['status'] = \company\Company::CLOSED;

		\company\CompanyLib::update($data->e, ['status']);

		throw new RedirectAction('/?success=company:Company.closed');

	});
?>
