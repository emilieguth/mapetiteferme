<?php
(new Page())
  ->get('/company/{id}/employee:manage', function($data) {

    $company = GET('id');

    $data->eCompany = \company\CompanyLib::getById($company);

    \company\EmployeeLib::register($data->eCompany);

    $data->cEmployee = \company\EmployeeLib::getByCompany($data->eCompany);
    $data->cInvite = \company\InviteLib::getByCompany($data->eCompany);

    $data->cUser = $data->cEmployee->getColumnCollection('user');

    throw new ViewAction($data, ':manage');

  })
  ->get('/company/{id}/employee:show', function($data) {

    $data->eEmployee = \company\EmployeeLib::getById(GET('id'));
    $data->eCompany = \company\CompanyLib::getById($data->eEmployee['company']);

    \company\EmployeeLib::register($data->eCompany);


    throw new ViewAction($data, ':show');

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
  ->update(page: '/company/{company}/employee:update')
  ->doUpdate(fn($data) => throw new ViewAction($data))
  ->doDelete(fn($data) => throw new RedirectAction(\company\CompanyUi::url($data->e['company']).'/employee:manage?company='.$data->e['company']['id'].'&success=company:Employee::deleted'));
?>