<?php
$hostname = match(GET('limeGender', 'string', 'm')) { 'f' => 'petiteagricultrice', default => 'petitagriculteur'};
Lime::setUrls([
  'dev' => 'http://www.dev-'.$hostname.'.fr',
  'prod' => 'https://www.'.$hostname.'.fr',
]);

Lime::setApps(['framework', 'petitagriculteur']);

L::setLang('fr_FR');
L::setVariables([
  'siteName' => $hostname,
]);

require_once Lime::getPath().'/secret.c.php';

switch(LIME_ENV) {

  case 'prod' :

    Setting::set('dev\minify', TRUE);
    Asset::setVersion(hash_file('crc32', LIME_DIRECTORY.'/.git/FETCH_HEAD'));

    Database::setPackages([
      'company' => 'petitagriculteur',
      'dev' => 'petitagriculteur',
      'mail' => 'petitagriculteur',
      'main' => 'petitagriculteur',
      'media' => 'petitagriculteur',
      'util' => 'petitagriculteur',
      'session' => 'petitagriculteur',
      'user' => 'petitagriculteur',
      'storage' => 'petitagriculteur',
    ]);

    break;

  case 'dev' :

    Database::setDebug(get_exists('sql'));

    Database::addPackages([
      'company' => 'dev_petitagriculteur',
      'dev' => 'dev_petitagriculteur',
      'mail' => 'dev_petitagriculteur',
      'main' => 'dev_petitagriculteur',
      'media' => 'dev_petitagriculteur',
      'util' => 'dev_petitagriculteur',
      'session' => 'dev_petitagriculteur',
      'user' => 'dev_petitagriculteur',
      'storage' => 'dev_petitagriculteur',
    ]);

    break;

}

Feature::set('user\ban', TRUE);
Setting::set('user\signUpRoles', ['employee']);
Setting::set('user\signUpView', 'main/index:signUp');

Page::construct(function($data) {

  \main\PageLib::common($data);

});

function vat_from_including(float $amount, float $vatRate): float {
  return $amount - round($amount / (1 + $vatRate / 100), 2);
}

function vat_from_excluding(float $amount, float $vatRate): float {
  return round($amount * $vatRate / 100, 2);
}

function including_from_excluding(float $amount, float $vatRate): float {
  return round($amount + vat_from_excluding($amount, $vatRate), 2);
}

function excluding_from_including(float $amount, float $vatRate): float {
  return round($amount - vat_from_including($amount, $vatRate), 2);
}
?>
