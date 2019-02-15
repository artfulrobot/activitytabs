<?php
use CRM_Activitytabs_ExtensionUtil as E;

class CRM_Activitytabs_Page_Activitytabs_Tab extends CRM_Core_Page {

  public $_contactId = NULL;

  public $_atabConfig = NULL;

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    //CRM_Utils_System::setTitle(E::ts('Activitytabs_Tab'));

    // Example: Assign a variable for use in a template
    //$this->assign('currentTime', date('Y-m-d H:i:s'));

    //$this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'browse');
    //$this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, empty($this->_id));
    $requested_name = CRM_Utils_Request::retrieve('activitytab', 'String', $this, TRUE);

    $atabs = json_decode(Civi::settings()->get('activitytabs') ?? '[]');
    foreach ($atabs as $atab) {
      if ($atab->name === $requested_name) {
        break;
      }
      $atab = NULL;
    }
    $this->assign('atabConfig', $atab);
    if (!$atab) {
      throw new \Exception("Failed to find requested tab");
    }

    $displayName = CRM_Contact_BAO_Contact::displayName($this->_contactId);
    $this->assign('displayName', $displayName);

    // Look up activities.
    $params = [
      'target_contact_id' => $this->_contactId,
      'activity_type_id'  => ['IN' => $atab->types ],
      'return'            => $atab->columns,
      'sequential'        => TRUE,
    ];
    $result = civicrm_api3('Activity', 'get', $params);

    $this->assign('tabCount', $result['count']);
    $this->ajaxResponse['tabCount'] = $result['count'];

    $this->assign('activities', $result['values']);
    $this->ajaxResponse['x1'] = $result;

    // check logged in url permission
    // @todo CRM_Contact_Page_View::checkUserPermission($this);

    parent::run();
  }

}
