<?php
// Version
define('VERSION', '2.1.0.2');

// CLI must be called by cli php
if (php_sapi_name() != 'cli') {
    syslog(LOG_ERR, "cli $cli_action call attempted by non-cli.");
    http_response_code(400);
    exit;
}

// Ensure $cli_action is set
if (!isset($cli_action)) {
    echo 'ERROR: $cli_action must be set in calling script.';
    syslog(LOG_ERR, '$cli_action must be set in calling script');
    http_response_code(400);
    exit;
}

// Handle errors by writing to log
function cli_error_handler($log_level, $log_text, $error_file, $error_line) {
    syslog(LOG_ERR, 'CLI Error: ' . $log_text . ' in ' . $error_file . ': ' . $error_line);
    echo 'CLI Error: ' . $log_text . ' in ' . $error_file . ': ' . $error_line;
}
set_error_handler('cli_error_handler');

// Configuration
if (is_file('../admin/config.php')) {
  require_once('../admin/config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
  header('Location: ../install/index.php');
  exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Config
$config = new Config();
$registry->set('config', $config);

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

foreach ($query->rows as $setting) {
  if (!$setting['serialized']) {
    $config->set($setting['key'], $setting['value']);
  } else {
    $config->set($setting['key'], json_decode($setting['value'], true));
  }
}

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Url
$url = new Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
$registry->set('url', $url);

// Log
$log = new Log($config->get('config_error_filename'));
$registry->set('log', $log);

function error_handler($code, $message, $file, $line) {
  global $log, $config;

  // error suppressed with @
  if (error_reporting() === 0) {
    return false;
  }

  switch ($code) {
    case E_NOTICE:
    case E_USER_NOTICE:
      $error = 'Notice';
      break;
    case E_WARNING:
    case E_USER_WARNING:
      $error = 'Warning';
      break;
    case E_ERROR:
    case E_USER_ERROR:
      $error = 'Fatal Error';
      break;
    default:
      $error = 'Unknown';
      break;
  }

  if ($config->get('config_error_display')) {
    echo '<b>' . $error . '</b>: ' . $message . ' in <b>' . $file . '</b> on line <b>' . $line . '</b>';
  }

  if ($config->get('config_error_log')) {
    $log->write('PHP ' . $error . ':  ' . $message . ' in ' . $file . ' on line ' . $line);
  }

  return true;
}

// Error Handler
set_error_handler('error_handler');

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->set('response', $response);

// Cache
$cache = new Cache('file');
$registry->set('cache', $cache);

// Session
$session = new Session();
$registry->set('session', $session);

// Language
$languages = array();

$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");

foreach ($query->rows as $result) {
  $languages[$result['code']] = $result;
}

$config->set('config_language_id', $languages[$config->get('config_admin_language')]['language_id']);

// Language
$language = new Language($languages[$config->get('config_admin_language')]['directory']);
$language->load($languages[$config->get('config_admin_language')]['directory']);
$registry->set('language', $language);

// Document
$registry->set('document', new Document());

// Currency
$registry->set('currency', new Currency($registry));

// Weight
$registry->set('weight', new Weight($registry));

// Length
$registry->set('length', new Length($registry));

// User
$registry->set('user', new User($registry));

// OpenBay Pro
$registry->set('openbay', new Openbay($registry));

// Front Controller
$controller = new Front($registry);

// Compile Sass
// $controller->addPreAction(new Action('common/sass'));

// Login
// $controller->addPreAction(new Action('common/login/check'));

// Permission
// $controller->addPreAction(new Action('error/permission/check'));

$action = new Action($cli_action);

// Dispatch
$controller->dispatch($action, new Action('error/not_found'));

// Output
// $response->output();
