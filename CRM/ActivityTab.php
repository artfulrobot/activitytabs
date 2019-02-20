<?php

class CRM_ActivityTab
{

  /**
   * Holds the config for a particular tab.
   */
  public $tab_config;

  /**
   * Return the tabs config.
   */
  public static function getConfigForAllTabs() {
    $activitytabs = json_decode(Civi::settings()->get('activitytabs')) ?? [];
    return $activitytabs;
  }

  /**
   * Constructor
   *
   */
  public function __construct() {

  }

  /**
   * Constructor
   *
   * @param StdClass $config
   * @return CRM_ActivityTab
   */
  public function setConfig($config) {
    $this->tab_config = $config;
    return $this;
  }

  /**
   * Factory method
   *
   * @param string $tabname
   * @return CRM_ActivityTab
   */
  public static function createFromTabName($tabname) {
    foreach (static::getConfigForAllTabs() as $atab) {
      if ($atab->name === $tabname) {
        $tab = new static();
        $tab->setConfig($atab);
        return $tab;
      }
    }
    throw new \Exception("Failed to find requested tab '$tabname'");
  }

  /**
   * Factory method
   *
   * @param StdClass $config
   * @return CRM_ActivityTab
   */
  public static function createFromConfig($config) {
    $tab = new static();
    return $tab->setConfig($config);
  }

  /**
   * Count the activities for the given contact.
   *
   * @return int
   */
  public function getCount($contact_id) {

    // Look up activities.
    $params = [
      'target_contact_id' => $contact_id,
      'activity_type_id'  => ['IN' => $this->tab_config->types ],
    ];

    // Do API call.
    $result = civicrm_api3('Activity', 'getcount', $params);

    return (int) $result;
  }
  /**
   *
   * Return an array for hook_civicrm_tabset()
   *
   * @param int $contact_id
   * @return Array
   */
  public function getTabSetDefinition($contact_id) {
    $url = CRM_Utils_System::url(
      'civicrm/activitytabs/view',
      "reset=1&snippet=1&force=1&cid=$contact_id&activitytab=" . urlencode($this->tab_config->name));
    return [
      'id'     => 'activitytabs' . preg_replace('/[^a-zA-Z0-9-_]+/', '', $this->tab_config->name),
      'url'    => $url,
      'title'  => $this->tab_config->name,
      'count'  => $this->getCount($contact_id),
      'weight' => 300
    ];
  }
  /**
   * Do API get call.
   *
   * @param int $contact_id
   * @return Array API result
   */
  public function getActivities($contact_id) {

    // Ensure we have activity_type_id in lookup, it's very useful even if it's
    // not needed in the final output.
    $return_fields = $this->tab_config->columns;
    if (!in_array('activity_type_id', $return_fields)) {
      $return_fields[] = 'activity_type_id';
    }
    // We have to doctor the return array because getfields returns
    // 'contact_id' instead of 'activity_contact_id'
    $contact_id_index = array_search('contact_id', $return_fields);
    if ($contact_id_index !== FALSE) {
      $return_fields[$contact_id_index] = 'activity_contact_id';
    }
    // Look up activities.
    $params = [
      'target_contact_id' => $contact_id,
      'activity_type_id'  => ['IN' => $this->tab_config->types ],
      'return'            => $return_fields,
      'sequential'        => TRUE,
      'options'           => ['limit' => 0],
    ];

    // Do API call.
    $result = civicrm_api3('Activity', 'get', $params);

    // Loop results to flatten the data to a table.
    $this->flattenContacts($result['values']);
    $this->replaceActivityTypeIds($result['values']);

    // Rename 'source_contact_id' to 'contact_id' which is set in the config.
    if ($contact_id_index !== FALSE) {
      array_walk($result['values'], function(&$v) {
        $v['contact_id'] = $v['source_contact_id'];
        unset($v['source_contact_id']);
      });
    }

    return $result;
  }

  /**
   * Replace arrays of contact ids with HTML links to the contacts by display name.
   *
   * @param array &$rows from Activity.get API result.
   */
  public function flattenContacts(&$rows) {

    // Find unique contact ids.
    $contact_ids = [];
    $relevant_cols = array_intersect(['target_contact_id', 'assignee_contact_id'],
     $this->tab_config->columns);
    if (in_array('contact_id', $this->tab_config->columns)) {
      $relevant_cols[] = 'source_contact_id';
    }
    if (!$relevant_cols) {
      return;
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
    if (!$contact_ids) {
      return;
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
  /**
   * Replace Type IDs with names.
   *
   * @param array &$rows from Activity.get API result.
   */
  public function replaceActivityTypeIds(&$rows) {

    if (!in_array('activity_type_id', $this->tab_config->columns)) {
      return;
    }

    // Extract unique activity types.
    $types = [];
    foreach($rows as $row) {
      $types[$row['activity_type_id']] = TRUE;
    }

    // Look them up.
    $types = civicrm_api3('OptionValue', 'get', [
      'return' => ['value', 'label'],
      'option_group_id' => 'activity_type',
      'value' => ['IN' => array_keys($types)],
      'options' => ['limit' => 0],
    ]);

    // Create map
    $map = [];
    foreach ($types['values'] as $type) {
      $map[$type['value']] = $type['label'];
    }

    foreach ($rows as &$row) {
      $row['activity_type_id'] = $map[$row['activity_type_id']];
    }
  }
  /**
   * Make an array with column names as keys and column headers as values.
   *
   * This is used by the smarty template for creating the headers.
   *
   * @return array.
   */
  public function getColumnNameMap() {
    // Provide prettier column names.
    $result = civicrm_api3('Activity', 'getfields', [ "action"=> "get" ]);
    $map = [];
    foreach ($this->tab_config->columns as $col) {
      // remove 'ID' from 'Contact ID'...
      $map[$col] = strtr($result['values'][$col]['title'],[
        'Contact ID'       => 'Contact',
        'Activity Type ID' => 'Type',
        'Activity Date'    => 'Date',
      ]);

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
    return $map;
  }
}
