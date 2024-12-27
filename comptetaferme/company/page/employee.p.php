<?php
(new Page())
  ->get('manage', function($data) {

    $company = GET('company', '?int');

    $data->eCompany = \company\CompanyLib::getById($company);

    \company\EmployeeLib::register($data->eCompany);

    $data->cEmployee = \company\EmployeeLib::getByCompany($data->eCompany);
    $data->cEmployeeInvite = \company\EmployeeLib::getByCompany($data->eCompany, onlyInvite: TRUE);

    $data->cUser = $data->cEmployee->getColumnCollection('user');

    throw new ViewAction($data);

  })
  ->get('show', function($data) {

    $data->eEmployee = \company\EmployeeLib::getById(GET('id'));
    $data->eCompany = \company\CompanyLib::getById($data->eEmployee['company']);

    \company\EmployeeLib::register($data->eCompany);


    throw new ViewAction($data);

  });

(new Page(function($data) {

  $data->eCompany = \company\CompanyLib::getById(INPUT('company'));

  \user\ConnectionLib::checkLogged();

}))
  ->get('createUser', fn($data) => throw new ViewAction($data))
  ->post('doCreateUser', function($data) {

    $fw = new FailWatch();

    $eUser = new \user\User([
      'email' => NULL,
      'visibility' => \user\User::PRIVATE,
    ]);

    $eUser->build(['firstName', 'lastName'], $_POST);

    $fw->validate();

    throw new BackAction('company', 'Employee::userCreated');

  })
  ->get('updateUser', function($data) {

    $data->eUserOnline = \user\UserLib::getById(GET('user'));

    if(\company\EmployeeLib::isEmployee($data->eUserOnline, $data->eCompany, NULL) === FALSE) {
      throw new NotAllowedAction('Not an employee');
    }

    throw new ViewAction($data);

  })
  ->post('doUpdateUser', function($data) {

    $eUser = \user\UserLib::getById(POST('user'))->validate('isPrivate');

    if(\company\EmployeeLib::isEmployee($eUser, $data->eCompany, NULL) === FALSE) {
      throw new NotAllowedAction('Not an employee');
    }

    $fw = new FailWatch();

    $eUser->build(['firstName', 'lastName'], $_POST);

    $fw->validate();

    \user\UserLib::update($eUser, ['firstName', 'lastName']);

    throw new BackAction('company', 'Employee::userUpdated');

  })
  ->post('doDeleteUser', function($data) {

    $eUser = \user\UserLib::getById(POST('user'));

    throw new ReloadAction('company', 'Employee::userDeleted');

  });

(new \company\EmployeePage(function($data) {

  \user\ConnectionLib::checkLogged();

}))
  ->getCreateElement(function($data) {

    return new \company\Employee([
      'company' => \company\CompanyLib::getById(INPUT('company'))
    ]);

  })
  ->create(function($data) {

    $data->eEmployeeLink = \company\EmployeeLib::getById(GET('employee'));

    if(
      $data->eEmployeeLink->notEmpty() and
      $data->eEmployeeLink['company']['id'] !== $data->e['company']['id']
    ) {
      throw new NotExpectedAction('Inconsistency');
    }

    throw new ViewAction($data);

  })
  ->doCreate(fn($data) => throw new RedirectAction('/company/employee:manage?company='.$data->e['company']['id'].'&success=company:Employee::created'))
  ->write('doDeleteInvite', function($data) {

    \company\InviteLib::deleteFromEmployee($data->e);

    throw new RedirectAction('/company/employee:manage?farm='.$data->e['company']['id'].'&success=company:Invite::deleted');

  })
  ->doUpdateProperties('doUpdateStatus', ['status'], function($data) {
    $eCompany = \company\CompanyLib::getById($data->e['company']);
    throw new RedirectAction(\company\EmployeeUi::urlManage($eCompany).'&success=company:'.($data->e['status'] === \company\Employee::IN ? 'Employee::created' : 'Employee::deleted'));
  })
  ->update()
  ->doUpdate(fn($data) => throw new ViewAction($data))
  ->doDelete(fn($data) => throw new RedirectAction('/company/employee:manage?company='.$data->e['company']['id'].'&success=company:Employee::deleted'));
?>