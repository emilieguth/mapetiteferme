<?php
Lime::setUrls([
  'dev' => 'http://www.dev-comptetonble.fr',
  'prod' => 'https://www.comptetonble.fr',
]);

Lime::setApps(['framework', 'comptetonble']);

L::setLang('fr_FR');
L::setVariables([
  'siteName' => 'comptetonble.fr',
]);

require_once Lime::getPath().'/secret.c.php';

switch(LIME_ENV) {

  case 'prod' :

    Setting::set('dev\minify', TRUE);
    Asset::setVersion(hash_file('crc32', LIME_DIRECTORY.'/.git/FETCH_HEAD'));

    Database::setPackages([
      'company' => 'comptetonble',
      'dev' => 'comptetonble',
      'mail' => 'comptetonble',
      'main' => 'comptetonble',
      'media' => 'comptetonble',
      'util' => 'comptetonble',
      'session' => 'comptetonble',
      'user' => 'comptetonble',
      'storage' => 'comptetonble',
    ]);

    break;

  case 'dev' :

    Database::setDebug(get_exists('sql'));

    Database::addPackages([
      'company' => 'dev_comptetonble',
      'dev' => 'dev_comptetonble',
      'mail' => 'dev_comptetonble',
      'main' => 'dev_comptetonble',
      'media' => 'dev_comptetonble',
      'util' => 'dev_comptetonble',
      'session' => 'dev_comptetonble',
      'user' => 'dev_comptetonble',
      'storage' => 'dev_comptetonble',
    ]);

    break;

}

Feature::set('user\ban', TRUE);
Setting::set('user\signUpRoles', ['customer', 'employee']);
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