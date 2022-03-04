#!/usr/bin/php
<?php
// Find drupal webroot - go up dirs until we find 'sites dir'
$d = __DIR__;
while ($d && !preg_match('@/sites$@', $d)) {
  $d = dirname($d);
}
// up one more...
$d = dirname($d);
define('DRUPAL_ROOT', $d);


if (php_sapi_name() !== 'cli') {
  // Fail with 404 if not called from CLI.
  if (isset($_SERVER['HTTP_PROTOCOL'])) {
    header("$_SERVER[HTTP_PROTOCOL] 404 Not Found");
  }
  exit;
}
// Note: options MUST be given before positional parameters.

function die_with_help($error=NULL) {
  global $argv;
  if ($error !== NULL) {
    fwrite(STDERR, "$error\n");
  }
  echo <<<TXT
Usage: $argv[0] [-h]

This script is for use on development environments only. It will:

1. Create a 'Workshop' activity type
2. Create a custom field group on Workshop and a custom field 'theme'
3. Create a 'Wilma' contact
4. Find a contact with several activities and add a couple of workshops to it.

TXT;
  exit(1);
}

$optind = null;
// getopt format:
//
// - single letter on its own is a boolean
// - follow with : for a required value
// - follow with :: for an optional value
//
// Test for an option with isset()
// @see http://php.net/getopt
$options = getopt('u::p::s');
$optind = 1 + count($options);
$pos_args = array_slice($argv, $optind);

/* Require 2 or 3 positional arguments.
if (count($pos_args) <2 || count($pos_args)>3) {
  die_with_help("Wrong arguments.");
}
 */

// These things are typically needed.
$_SERVER["SCRIPT_FILENAME"] = __FILE__;
$_SERVER["REMOTE_ADDR"] = '127.0.0.1';
$_SERVER["SERVER_SOFTWARE"] = NULL;
$_SERVER["REQUEST_METHOD"] = 'GET';
$_SERVER["SCRIPT_NAME"] = __FILE__;

// Boostrap drupal
chdir(DRUPAL_ROOT); // This seems to be required.
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);


// Bootstrap CiviCRM.
$GLOBALS['_CV'] = array ();
define("CIVICRM_SETTINGS_PATH", DRUPAL_ROOT . '/sites/default/civicrm.settings.php');
$error = @include_once CIVICRM_SETTINGS_PATH;
if ($error == FALSE) {
  throw new \Exception("Could not load the CiviCRM settings file: {$settings}");
}
require_once $GLOBALS["civicrm_root"] . "/CRM/Core/ClassLoader.php";
\CRM_Core_ClassLoader::singleton()->register();\CRM_Core_Config::singleton();\CRM_Utils_System::loadBootStrap(array(), FALSE);

if (!civicrm_initialize()) {
  die_with_help("Failed to initialise civi.");
  exit;
}


echo "Ok, ready to start\n";
$api_get_or_create = function ($entity, $params_min, $params_extra = []) {
    $params_min += ['sequential' => 1];
    $result = civicrm_api3($entity, 'get', $params_min);
    if (!$result['count']) {
      Civi::log()->notice('get_or_create Could not find entity, creating now', ['entity' => $entity, 'min' => $params_min, 'extra' => $params_extra]);
      // Couldn't find it, create it now.
      $result = civicrm_api3($entity, 'create', $params_extra + $params_min);
      // reload
      $result = civicrm_api3($entity, 'get', $params_min);
    }
    else {
      Civi::log()->notice('get_or_create Found entity', ['entity' => $entity, 'min' => $params_min, 'found' => $result['values'][0]]);
    }
    return $result['values'][0];
  };

echo "Create demo activity types\n";

$workshop_activity = $api_get_or_create('OptionValue',
  [ 'option_group_id' => "activity_type", 'name' => "demo_workshop" ],
  [
    'label'           => "Workshop",
    'description'     => "Demo workshop activity",
  ]);

echo "create custom fieldset\n";
// Ensure we have the custom field group we need for project.
$workshop_custom_group = $api_get_or_create('CustomGroup', [
    'name' => "demo_workshop_fields",
    'extends' => "Activity",
  ],
  ['title' => 'Workshop details']);

echo "create custom field\n";
$theme_field = $api_get_or_create('CustomField', [
    'name' => "demo_theme",
    'custom_group_id' => $workshop_custom_group['id'],
  ],
  ['label' => 'Workshop theme',
    'data_type' => "String",
    'html_type' => "Text",
    'is_required' => "1",
    'is_searchable' => "1",
    'default_value' => "",
    'text_length' => "30",
]);
$theme_field_api_key = 'custom_' . $theme_field['id'];

echo "Create demo contact\n";
$wilma = $api_get_or_create('Contact', [
  'first_name' => 'Wilma',
  'last_name' => 'Flintstone',
  'contact_type' => 'Individual',
]);
$betty = $api_get_or_create('Contact', [
  'first_name' => 'Betty',
  'last_name' => 'Rubble',
  'contact_type' => 'Individual',
]);

echo "Create workshop activities for wilma\n";
$api_get_or_create('Activity',[
  'activity_type_id'   => 'demo_workshop',
  'activity_date_time' => '2019-01-02 09:00:00',
],[
  'target_id'          => $wilma['id'],
  'source_contact_id'  => $wilma['id'],
  'assignee_contact_id'  => $betty['id'],
  'subject'            => 'How to divest your institution',
  'status_id' => 'Completed',
  $theme_field_api_key => 'Fossil Free',
]);

$api_get_or_create('Activity',[
  'activity_type_id'   => 'demo_workshop',
  'activity_date_time' => '2019-05-02 09:00:00',
],[
  'target_id'          => $wilma['id'],
  'source_contact_id'  => $wilma['id'],
  'assignee_contact_id'  => $betty['id'],
  'status_id' => 'Scheduled',
  'subject'            => 'Celebrating successes',
  $theme_field_api_key => 'Fossil Free',
]);

echo 'Wilma: ' . CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $wilma['id']], $absolute=TRUE, '',
        $htmlize=FALSE, $frontend=FALSE, $forceBackend=FALSE) . "\n";
