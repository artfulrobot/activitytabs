<?php
use CRM_Activitytabs_ExtensionUtil as E;

class CRM_Activitytabs_Page_Activitytabs_Tab extends CRM_Core_Page {

  public $_contactId = NULL;

  public $_atabConfig = NULL;

  public function run() {

    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, empty($this->_id));
    $requested_name = CRM_Utils_Request::retrieve('activitytab', 'String', $this, TRUE);

    $tab = CRM_ActivityTab::createFromTabName($requested_name);
    $result = $tab->getActivities($this->_contactId);

    // Assign tpl vars.
    $this->assign('displayName', CRM_Contact_BAO_Contact::displayName($this->_contactId));
    $this->assign('contactId', $this->_contactId);
    $this->assign('atabConfig', $tab->tab_config);
    $this->assign('columnMap', $tab->getColumnNameMap());
    $this->assign('tabCount', $result['count']);
    $this->assign('activities', $result['values']);

    $this->ajaxResponse['tabCount'] = $result['count'];

    parent::run();
  }

}
