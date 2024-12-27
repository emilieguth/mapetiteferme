<?php
Lime::setUrls([
  'dev' => 'http://www.dev-comptetaferme.fr',
  'prod' => 'https://www.comptetaferme.fr',
]);

Lime::setApps(['framework', 'comptetaferme']);

L::setLang('fr_FR');
L::setVariables([
  'siteName' => 'comptetaferme.fr',
]);

require_once Lime::getPath().'/secret.c.php';

switch(LIME_ENV) {

  case 'prod' :

    Setting::set('dev\minify', TRUE);
    Asset::setVersion(hash_file('crc32', LIME_DIRECTORY.'/.git/FETCH_HEAD'));

    Database::setPackages([
      'company' => 'comptetaferme',
      'dev' => 'comptetaferme',
      'mail' => 'comptetaferme',
      'media' => 'comptetaferme',
      'util' => 'comptetaferme',
      'session' => 'comptetaferme',
      'user' => 'comptetaferme',
      'storage' => 'comptetaferme',
    ]);

    break;

  case 'dev' :

    Database::setDebug(get_exists('sql'));

    Database::setPackages([
      'company' => 'dev_comptetaferme',
      'dev' => 'dev_comptetaferme',
      'mail' => 'dev_comptetaferme',
      'media' => 'dev_comptetaferme',
      'util' => 'dev_comptetaferme',
      'session' => 'dev_comptetaferme',
      'user' => 'dev_comptetaferme',
      'storage' => 'dev_comptetaferme',
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