<?php
namespace company;

class EmployeeUi {

  public function __construct() {
    \Asset::css('company', 'company.css');
  }

  public static function urlManage(Company $eCompany): string {
    return '/company/employee:manage?company='.$eCompany['id'].'';
  }

  public function getMyCompanies(\Collection $cCompany): string {

    $h = '<div class="employee-companies">';

    foreach($cCompany as $eCompany) {
      $h .= (new CompanyUi())->getPanel($eCompany);
    }

    $h .= '</div>';

    return $h;

  }

  public function getNoCompany(): string {

    $h = '<h2>'.s("Mon entreprise").'</h2>';
    $h .= '<div class="util-block-help">';
      $h .= '<h4>'.s("Bienvenue sur {siteName} !").'</h4>';
      $h .= '<p>'.s("Vous êtes chef d'entreprise et vous venez de vous inscrire sur {siteName}. Pour commencer à utiliser tous les outils numériques développés pour vous sur la plateforme, configurez maintenant votre entreprise en renseignant quelques informations de base !").'</p>';
    $h .= '</div>';
    $h .= '<div class="util-buttons">';
      $h .= '<a href="/company/company:create" class="bg-secondary util-button">';
        $h .= '<div>';
          $h .= '<h4>'.s("Démarrer la création de mon entreprise").'</h4>';
        $h .= '</div>';
        $h .= \Asset::icon('house-door-fill');
      $h .= '</a>';
    $h .= '</div>';

    return $h;

  }

  public function createUser(\company\Company $eCompany): \Panel {

    $form = new \util\FormUi();

    $h = $form->openAjax('/company/employee:doCreateUser');

    $h .= $form->asteriskInfo();

    $h .= $form->hidden('company', $eCompany['id']);

    $eUser = new \user\User();

    $h .= $form->dynamicGroups($eUser, ['firstName', 'lastName*'], [
      'firstName' => function($d) use ($form) {
        $d->after =  \util\FormUi::info(s("Facultatif"));
      }
    ]);

    $h .= $form->group(
      content: $form->submit(s("Créer l'utilisateur"))
    );

    $h .= $form->close();

    return new \Panel(
      title: s("Créer un utilisateur fantôme pour la ferme"),
      body: $h,
      close: 'reload'
    );

  }

  public function updateUser(\company\Company $eCompany, \user\User $eUser): \Panel {

    $form = new \util\FormUi();

    $h = $form->openAjax('/company/employee:doUpdateUser');

    $h .= $form->hidden('company', $eCompany['id']);
    $h .= $form->hidden('user', $eUser['id']);

    $h .= $form->dynamicGroups($eUser, ['firstName', 'lastName'], [
      'firstName' => function($d) use ($form) {
        $d->after =  \util\FormUi::info(s("Facultatif"));
      }
    ]);

    $h .= $form->group(
      content: $form->submit(s("Modifier l'utilisateur"))
    );

    $h .= $form->close();

    return new \Panel(
      title: s("Modifier un utilisateur de l'entreprise"),
      body: $h,
      close: 'reload'
    );

  }

  public function create(Employee $eEmployee, Employee $eEmployeeLink): \Panel {

    return new \Panel(
      title: s("Inviter un utilisateur dans l'équipe"),
      body: $this->createForm($eEmployee, $eEmployeeLink, 'panel'),
      close: 'reload'
    );

  }

  public function getManageTitle(Company $eCompany): string {

    $h = '<div class="util-action">';

    $h .= '<h1>';
      $h .= '<a href="'.\company\CompanyUi::urlSettings($eCompany).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
      $h .= s("L'équipe");
    $h .= '</h1>';

    $h .= '<div>';
      $h .= '<a href="/company/employee:create?company='.$eCompany['id'].'" class="btn btn-primary">'.\Asset::icon('plus-circle').' '.s("Inviter un utilisateur dans l'équipe").'</a>';
    $h .= '</div>';

    $h .= '</div>';

    return $h;

  }

  public function getManage(Company $eCompany, \Collection $cEmployee, \Collection $cEmployeeInvite): string {

    $h = '';

    if($cEmployee->notEmpty()) {

      $h .= '<div class="util-buttons">';

      foreach($cEmployee as $eEmployee) {

        $properties = [];

        if($eEmployee['companyGhost'] === FALSE) {
          $properties[] = self::p('role')->values[$eEmployee['role']];
        }

        $h .= '<a href="/company/employee:show?id='.$eEmployee['id'].'" class="util-button bg-secondary">';
          $h .= '<div>';
          $h .= '<h4>';
            $h .= \user\UserUi::name($eEmployee['user']);
          $h .= '</h4>';
          $h .= '<div class="util-button-text">';
            $h .= implode(' | ', $properties);
          $h .= '</div>';
          $h .= \user\UserUi::getVignette($eEmployee['user'], '4rem');
        $h .= '</a>';

      }

      $h .= '</div>';

      if($cEmployeeInvite->notEmpty()) {

        $h .= '<h3>'.s("Les invitations en cours").'</h3>';

        $h .= '<div class="util-buttons">';

          foreach($cEmployeeInvite as $eEmployee) {

            $h .= '<div class="util-button bg-primary">';
              $h .= '<div>';
                $h .= '<div>';
                  if($eEmployee['invite']->empty()) {
                    $h .= \Asset::icon('exclamation-triangle-fill').' '.s("Invitation expirée");
                  } else if($eEmployee['invite']->isValid() === FALSE) {
                    $h .= s("Invitation expirée pour {value}", '<b>'.encode($eEmployee['invite']['email']).'</b>');
                  } else {
                    $h .= s("Invitation envoyée à {value}", '<b>'.encode($eEmployee['invite']['email']).'</b>');
                  }
                $h .= '</div>';
                $h .= '<div class="mt-1">';
                  $h .= '<a data-ajax="/company/employee:doDeleteInvite" post-id="'.$eEmployee['id'].'" class="btn btn-transparent">';
                    $h .= s("Supprimer");
                  $h .= '</a> ';
                  $h .= '<a data-ajax="/company/invite:doExtends" post-id="'.$eEmployee['invite']['id'].'" data-confirm="'.s("Voulez-vous vraiment renvoyer un mail d'invitation à cette personne ?").'" class="btn btn-transparent">';
                    $h .= s("Renvoyer l'invitation");
                  $h .= '</a>';
                $h .= '</div>';
              $h .= '</div>';
              $h .= \user\UserUi::getVignette($eEmployee['user'], '4rem');
            $h .= '</div>';

          }

        $h .= '</div>';

      }

    }


    return $h;

  }

  public function getUserTitle(\company\Employee $eEmployee): string {

    $h = '<h1>';
      $h .= '<a href="'.EmployeeUi::urlManage($eEmployee['company']).'"  class="h-back">'.\Asset::icon('arrow-left').'</a>';
      $h .= \user\UserUi::getVignette($eEmployee['user'], '3rem').' '.\user\UserUi::name($eEmployee['user']);
    $h .= '</h1>';

    return $h;

  }

  public function getUser(\company\Employee $eEmployee): string {

    $h = '<dl class="util-presentation util-presentation-1">';

      $h .= '<dt>'.s("Adresse e-mail").'</dt>';
      $h .= '<dd>'.($eEmployee['user']['visibility'] === \user\User::PUBLIC ? encode($eEmployee['user']['email']) : '<i>'.s("Utilisateur fantôme").'</i>').'</dd>';
      $h .= '<dt>'.s("Rôle").'</dt>';
      $h .= '<dd>'.self::p('role')->values[$eEmployee['role']].'</dd>';

    $h .= '</dl>';

    $h .= '<br/>';

    if($eEmployee['status'] === Employee::OUT) {

      $h .= '<div class="util-info">'.s("Cet utilisateur a été sorti de l'équipe de la ferme et ne peut donc plus accéder ni être affecté aux interventions sur la ferme.").'</div>';

    } else {

      $h .= '<div>';

      $h .= '<a href="/company/employee:update?id='.$eEmployee['id'].'" class="btn btn-primary">';
        $h .= s("Configurer l'utilisateur");
      $h .= '</a> ';

      $h .= '<a data-ajax="/company/employee:doDelete" post-id="'.$eEmployee['id'].'" class="btn btn-primary" data-confirm="'.s("Souhaitez-vous réellement retirer cet utilisateur de la ferme ?").'">';
        $h .= s("Sortir de l'équipe");
      $h .= '</a>';

      $h .= '</div>';

      $h .= '<br/>';

    }

    return $h;

  }

  protected function createForm(Employee $eEmployee, Employee $eEmployeeLink, string $origin): string {

    $form = new \util\FormUi();

    $h = $form->openAjax('/company/employee:doCreate', ['data-ajax-origin' => $origin]);

    $h .= $form->asteriskInfo();

    if($eEmployeeLink->notEmpty()) {
      $h .= $form->hidden('id', $eEmployeeLink['id']);
    }

    $h .= $form->hidden('company', $eEmployee['company']['id']);

    $description = '<div class="util-block-help">';
      $description .= '<p>'.s("En invitant un utilisateur à rejoindre l'équipe de votre ferme, vous lui permettrez d'accéder à un grand nombre de données sur votre ferme.").'</p>';
      $description .= '<p>'.s("Pour inviter un utilisateur, saisissez son adresse e-mail. Il recevra un e-mail lui donnant les instructions à suivre, et devra les réaliser dans un délai de trois jours.").'</p>';
    $description .= '</div>';

    $h .= $form->group(content: $description);

    $h .= $form->dynamicGroups($eEmployee, ['email*', 'role*']);

    $h .= $form->group(
      content: $form->submit(s("Ajouter"))
    );

    $h .= $form->close();

    return $h;

  }

  public function update(\company\Employee $eEmployee): \Panel {

    $form = new \util\FormUi();

    $h = $form->openAjax('/company/employee:doUpdate');

    $h .= $form->hidden('id', $eEmployee['id']);

    $h .= $form->group(
      content: $form->submit(s("Modifier"))
    );

    $h .= $form->close();

    return new \Panel(
      title: s("Modifier un utilisateur de l'entreprise"),
      body: $h,
      close: 'reload'
    );

  }

  public static function p(string $property): \PropertyDescriber {

    $d = Employee::model()->describer($property, [
      'email' => s("Adresse e-mail"),
      'user' => s("Utilisateur"),
    ]);

    switch($property) {

      case 'email' :
        $d->field = 'email';
        break;

    }

    return $d;

  }

}
