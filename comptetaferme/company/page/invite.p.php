<?php
(new \company\InvitePage(function($data) {

\user\ConnectionLib::checkLogged();

}))
  ->getCreateElement(function($data) {

  return new \company\Invite([
  'company' => \company\CompanyLib::getById(INPUT('company'))
  ]);

  })
  ->create(function($data) {

    throw new ViewAction($data);

  })
  ->doCreate(fn($data) => throw new RedirectAction('/company/employee:manage?company='.$data->e['company']['id'].'&success=company:Invite::created'))
  ->write('doDeleteInvite', function($data) {

    \company\InviteLib::deleteFromEmployee($data->e);

    throw new RedirectAction('/company/employee:manage?company='.$data->e['company']['id'].'&success=company:Invite::deleted');

  })
  ->update()
  ->doUpdate(fn($data) => throw new ViewAction($data))
  ->doDelete(fn($data) => throw new RedirectAction('/company/employee:manage?company='.$data->e['company']['id'].'&success=company:Invite::deleted'));


(new \company\InvitePage())
  ->write('doExtend', function($data) {

    $data->e['company'] = \company\CompanyLib::getById($data->e['company']);

    \company\InviteLib::extends($data->e);

    throw new ReloadAction('company', 'Invite::extended');

  });


?>