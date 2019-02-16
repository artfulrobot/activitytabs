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
    $this->assign('contactId', $this->_contactId);
    $requested_name = CRM_Utils_Request::retrieve('activitytab', 'String', $this, TRUE);

    $atabs = json_decode(Civi::settings()->get('activitytabs') ?? '[]');
    foreach ($atabs as $atab) {
      if ($atab->name === $requested_name) {
        $this->_atabConfig = $atab;
        break;
      }
    }
    if (!$this->_atabConfig) {
      throw new \Exception("Failed to find requested tab");
    }
    $this->assign('atabConfig', $this->_atabConfig);

    $displayName = CRM_Contact_BAO_Contact::displayName($this->_contactId);
    $this->assign('displayName', $displayName);

    // Look up activities.
    // Remove activitytabs_summary from columns.
    $return_fields = array_filter($this->_atabConfig->columns, function($field) {
      return ($field !== 'activitytabs_summary');
    });
    $params = [
      'target_contact_id' => $this->_contactId,
      'activity_type_id'  => ['IN' => $this->_atabConfig->types ],
      'return'            => $this->_atabConfig->columns,
      'sequential'        => TRUE,
    ];
    // We have to doctor the return array because getfields returns
    // 'contact_id' instead of 'activity_contact_id'
    $params['return'] = array_map(
      function($_) {
        if ($_ === 'contact_id') {
          $_ = 'activity_contact_id';
        }
        return $_;
      },
      $params['return']);
    $result = civicrm_api3('Activity', 'get', $params);

    // Loop results to flatten the data to a table.
    $this->flattenContacts($result['values']);
    array_walk($result['values'], function(&$v) {
      $v['contact_id'] = $v['source_contact_id'];
      unset($v['source_contact_id']);
    });

    if (in_array('activitytabs_summary', $this->_atabConfig->columns)) {
      // invoke hook.
      CRM_Utils_Hook::singleton()->invoke(
        1, $result['values'],
        $dummy, $dummy, $dummy, $dummy, $dummy,
        'activitytabs_summary');
    }

    $this->assign('tabCount', $result['count']);
    $this->ajaxResponse['tabCount'] = $result['count'];

    $this->assign('activities', $result['values']);
    $this->ajaxResponse['x1'] = $result['values'];

    // check logged in url permission
    // @todo CRM_Contact_Page_View::checkUserPermission($this);

    // Provide prettier column names.
    $result = civicrm_api3('Activity', 'getfields', [ "action"=> "get" ]);
    $map = [];
    foreach ($this->_atabConfig->columns as $col) {
      $map[$col] = $result['values'][$col]['title'];

      // The array keys are not the same as the 'name' values...in some cases!
      // e.g. 'subject' has arary key 'activity_subject' but it's 'subject' you
      // have to use when requesting or querying data.
      if (empty($map[$col])) {
        foreach($result['values'] as $field) {
          if ($field['name'] === $col) {
            $map[$col] = $field['title'];
            break;
          }
        }
      }
    }
    $this->assign('columnMap', $map);

    parent::run();
  }

  public function flattenContacts(&$rows) {

    // Find unique contact ids.
    $contact_ids = [];
    $relevant_cols = array_intersect(['target_contact_id', 'assignee_contact_id'],
     $this->_atabConfig->columns);
    if (in_array('contact_id', $this->_atabConfig->columns)) {
      $relevant_cols[] = 'source_contact_id';
    }
    foreach ($rows as $row) {
      foreach ($relevant_cols as $col) {
        if (isset($row[$col])) {
          if (is_array($row[$col])) {
            foreach ($row[$col] as $contact_id) {
              $contact_ids[$contact_id] = 1;
            }
          }
          else {
            // e.g. source_contact_id
            $contact_ids[$row[$col]] = 1;
          }
        }
      }
    }
    $contact_details = civicrm_api3( 'Contact', 'get', [
      'id' => ['IN' => array_keys($contact_ids)],
      'return' => 'display_name']);
    $contact_details = array_map(function($contact) {
      $url = CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $contact['contact_id']]);
      return '<a href="' . $url . '" >' . htmlspecialchars($contact['display_name']) . '</a>';
    }, $contact_details['values']);

    // Replace with html.
    foreach ($rows as &$row) {
      foreach ($relevant_cols as $col) {
        if (isset($row[$col])) {
          if (is_array($row[$col])) {
            $html = [];
            foreach ($row[$col] as $contact_id) {
              $html[] = $contact_details[$contact_id];
            }
            $row[$col] = implode(', ', $html);
          }
          else {
            $row[$col] = $contact_details[$row[$col]];
          }
        }
      }
    }
  }
}
