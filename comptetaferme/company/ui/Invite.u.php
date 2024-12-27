<?php
namespace company;

class InviteUi {

  public function __construct() {

  }

  public function check(Invite $eInvite): string {

    if($eInvite->empty()) {

      $h = '<div class="util-block text-center">';
        $h .= '<br/><br/>';
        $h .= '<h2>'.s("Le lien que vous avez utilisé pour activer votre compte a expiré !").'</h2>';
        $h .= '<h4>'.s("Merci de demander à l'entreprise de vous envoyer un nouveau lien.").'</h4>';
        $h .= '<br/><br/>';
      $h .= '</div>';

      return $h;

    }

    return $this->checkEmployee($eInvite);

  }

  public function checkEmployee(Invite $eInvite): string {

    $eFarm = $eInvite['farm'];

    if($eInvite->isValid() === FALSE) {

      $h = '<div class="util-block text-center">';
      $h .= '<br/><br/>';
      $h .= '<h2>'.s("Le lien que vous avez utilisé pour activer votre compte client a expiré !").'</h2>';
      $h .= '<h4>'.s("Merci de demander à la ferme {farm} de vous envoyer un nouveau lien.", ['farm' => '<b>'.encode($eFarm['name']).'</b>']).'</h4>';
      $h .= '<br/><br/>';
      $h .= '</div>';

      return $h;

    }

    $eUser = \user\ConnectionLib::getOnline();

    if($eUser->empty()) {

      $h = '<div class="util-block text-center">';
        $h .= '<br/><br/>';
        $h .= '<h2>'.s("Vous n'êtes pas connecté·e sur {siteName} !").'</h2>';
        $h .= '<h4>'.s("Pour créer votre compte client {company}, veuillez vous connecter sur {siteName} avec l'adresse e-mail {emailCurrent} ou créer un compte si vous n'en disposez pas.", ['company' => '<b>'.encode($eFarm['name']).'</b>', 'emailCurrent' => '<b>'.encode($eInvite['email']).'</b>']).'</h4>';
        $h .= '<div>';
          $h .= '<a href="/user/signUp?invite='.$eInvite['key'].'" class="btn btn-secondary">'.s("Créer un compte").'</a> ';
          $h .= '<a href="/user/log:form?invite='.$eInvite['key'].'" class="btn btn-outline-secondary">'.s("Me connecter").'</a>';
        $h .= '</div>';
        $h .= '<br/><br/>';
      $h .= '</div>';

      return $h;

    }

    if($eUser['email'] !== $eInvite['email']) {

      $h = '<div class="util-block text-center">';
        $h .= '<br/><br/>';
        $h .= '<h2>'.s("Vous devez être connecté·e sur {siteName} avec {emailExpected} pour activer votre compte client ! Vous êtes actuellement connecté avec l'adresse e-mail {emailCurrent}.", ['emailExpected' => '<b>'.encode($eInvite['email']).'</b>', 'emailCurrent' => encode($eUser['email'])]).'</h2>';
        $h .= '<h4>'.s("Merci de vous déconnecter de {siteName} et de vous reconnecter avec la bonne adresse e-mail, ou bien de demander à la ferme {farm} de vous envoyer un lien sur {emailCurrent}}.", ['farm' => '<b>'.encode($eFarm['name']).'</b>', 'emailCurrent' => encode($eUser['email'])]).'</h4>';
        $h .= '<br/><br/>';
      $h .= '</div>';

      return $h;

    }

    return $this->checkDefault();

  }

  public function signUp(Invite $e): string {

    $form = new \util\FormUi([
      'firstColumnSize' => 40
    ]);

    $h = $form->openAjax('/farm/invite:doAcceptUser', ['autocomplete' => 'off']);

    $h .= $form->hidden('key', $e['key']);

    $eUser = new \user\User([
      'email' => $e['email']
    ]);

    $h .= $form->dynamicGroup($eUser, 'email');

    $h .= $form->group(
      s("Votre mot de passe"),
      $form->password('password', NULL, ['placeholder' => s("Mot de passe")])
    );

    $h .= $form->group(
      s("Retapez le mot de passe"),
      $form->password('passwordBis')
    );

    $h .= $form->group(
      content: $form->submit(s("S'inscrire"))
    );

    $h .= $form->close();

    return $h;

  }

  protected function checkDefault(): string {

    $h = '<div class="util-block text-center">';
      $h .= '<br/><br/>';
      $h .= '<h2>'.s("Vous ne pouvez pas accepter l'invitation pour le moment.").'</h2>';
      $h .= '<h4>'.s("Merci de réessayer ultérieurement...").'</h4>';
      $h .= '<br/><br/>';
    $h .= '</div>';

    return $h;

  }

  public function accept(Invite $eInvite): string {

    $h = '<div class="util-block text-center">';
      $h .= '<br/><br/>';
      $h .= '<h2>'.s("Votre compte {company} a été activé !", ['company' => '<b>'.encode($eInvite['farm']['name']).'</b>']).'</h2>';
      $h .= '<h4>'.s("Vous avez désormais accès à l'ensemble des fonctionnalités.").'</h4>';
      $h .= '<div>';
        $h .= '<a href="/" class="btn btn-secondary">'.s("Découvrir l'entreprise").'</a> ';
      $h .= '</div>';
      $h .= '<br/><br/>';
    $h .= '</div>';

    return $h;

  }


  /**
   * Invitation à créer son compte
   */
  public static function getInviteMail(Invite $e): array {

    return self::getInviteEmployeeMail($e);

  }

  public static function getInviteEmployeeMail(Invite $e): array {

    $urlHash = \Lime::getUrl().'/company/invite:check?key='.$e['key'];

    $title = s("{company} vous invite à rejoindre son équipe !", ['company' => $e['company']['name']]);

    $text = s("Bonjour,

L'entreprise {company} vous invite à créer un compte {siteName} pour rejoindre l'équipe de l'entreprise.

Utilisez le lien suivant dans votre navigateur pour activer votre compte :
{url}

À bientôt,
L'équipe {siteName}", ['company' => $e['company']['name'], 'email' => $e['email'], 'url' => $urlHash]);


    return [
      $title,
      $text
    ];

  }

  /**
   * Invitation à créer son compte
   */
  public static function getAcceptMail(Invite $e): array {

    return self::getAcceptEmployeeMail($e);

  }

  public static function getAcceptEmployeeMail(Invite $e): array {

    $title = s("Vous avez rejoint l'équipe de {company} !", ['company' => $e['company']['name']]);

    $text = s("Bonjour,

Vous avez accepté l'invitation à rejoindre l'équipe de {company} avec succès sur {siteName}.

Vous pouvez accéder à la page de l'entreprise en utilisant le lien suivant :
{url}

À bientôt,
L'équipe {siteName}", ['company' => $e['company']['name'], 'email' => $e['email'], 'url' => \Lime::getUrl()]);


    return [
      $title,
      $text
    ];

  }

  public static function p(string $property): \PropertyDescriber {

    $d = Invite::model()->describer($property, [
      'email' => s("Adresse e-mail"),
    ]);

    return $d;

  }

}
?>
