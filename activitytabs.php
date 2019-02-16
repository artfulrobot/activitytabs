<?php

require_once 'activitytabs.civix.php';
use CRM_Activitytabs_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function activitytabs_civicrm_config(&$config) {
  _activitytabs_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function activitytabs_civicrm_xmlMenu(&$files) {
  _activitytabs_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function activitytabs_civicrm_install() {
  // Create our setting.
  Civi::settings()->set('activitytabs', '[]');
  _activitytabs_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function activitytabs_civicrm_postInstall() {
  _activitytabs_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function activitytabs_civicrm_uninstall() {

  // This looks the closest thing to 'delete'?
  Civi::settings()->revert('activitytabs');

  _activitytabs_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function activitytabs_civicrm_enable() {
  _activitytabs_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function activitytabs_civicrm_disable() {
  _activitytabs_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function activitytabs_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _activitytabs_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function activitytabs_civicrm_managed(&$entities) {
  _activitytabs_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function activitytabs_civicrm_caseTypes(&$caseTypes) {
  _activitytabs_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function activitytabs_civicrm_angularModules(&$angularModules) {
  _activitytabs_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function activitytabs_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _activitytabs_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function activitytabs_civicrm_entityTypes(&$entityTypes) {
  _activitytabs_civix_civicrm_entityTypes($entityTypes);
}



/**
 * Implmements hook_civicrm_tabset
 *
 * @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_tabset/
 */
function activitytabs_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName == 'civicrm/contact/view') {
    $activitytabs = json_decode(Civi::settings()->get('activitytabs')) ?? [];
    $contactId = $context['contact_id'];

    foreach ($activitytabs as $atab) {
      $url = CRM_Utils_System::url(
        'civicrm/activitytabs/view',
        "reset=1&snippet=1&force=1&cid=$contactId&activitytab=" . urlencode($atab->name));
      $tabs[] = [
        'id'     => 'activitytabs' . preg_replace('/[^a-zA-Z0-9-_]+/', '', $atab->name),
        'url'    => $url,
        'title'  => $atab->name,
        'weight' => 300
      ];
    }
  }
}

