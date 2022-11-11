<?php
/**
 * Környezeti változók
 */
define('C_ENV', [
  'level' => 'dev', // dev, prod
  'version' => '0.1',
  'domain' => 'lektoratus.kozterkep.hu',
  'basepath' => str_replace('/includes', '/', __DIR__),
]);


/**
 * Session beállítások
 */
define('C_SESSION', [
  // Alapértelmezett PHP session
  'base' => 'lektoratusDB',
  'base_expiry' => false, // false: session erejéig, egyébként mp-ben add meg

  // User session
  'user' => 'lektoratusDBUser',
  'user_expiry' => 90*24*60*60,
]);


/**
 * DB változók
 */
define('C_DB', [
  'user' => 'kozterkep',
  'pass' => 'PeT2V#VaZXP+xc?v',
  'name' => 'lektoratus',
  'encoding' => 'utf8',
]);


/**
 * Valid userek
 */
define('C_USERS', [
  'paltamas' => 'ohXudura6641',
  'eszter' => 'Mefkv9ND',
  'kozterkep' => 'WrMJu4zUNc',
]);



/**
 * Legfontosabb, közös függvények
 */
require_once 'functions.php';
require_once 'users.php';