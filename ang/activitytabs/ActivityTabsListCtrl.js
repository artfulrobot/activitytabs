(function(angular, $, _) {
  angular.module('activitytabs').config(function($routeProvider) {
      $routeProvider.when('/activitytabs', {
        controller: 'ActivitytabsActivityTabsListCtrl',
        templateUrl: '~/activitytabs/ActivityTabsListCtrl.html',

        // If you need to look up data when opening the page, list it out
        // under "resolve".
        resolve: {
          activitytabsList: function(crmApi) {
            return crmApi('Setting', 'getvalue', { name: 'activitytabs' })
            .then(r => JSON.parse(r.result), e => alert('Error fetching config.'))
          },
          activityTypes: function(crmApi) {
            return crmApi('OptionValue', 'get', {
              "sequential": 1,
              "return": ["name","label"],
              "option_group_id": "activity_type"
            })
            .then(r => r.values, e => alert('Error fetching config.'))
          },
          activityFields: function(crmApi) {
            return crmApi('Activity', 'getfields', { "api_action": "get" })
              .then(r => {
                const opts = [{name: 'activitytabs_summary', title: 'Custom summary'}];
                Object.keys(r.values).forEach(k => { opts.push(r.values[k]); });
                return opts;
              });
          }
        }
      });
    }
  );

  // The controller uses *injection*. This default injects a few things:
  //   $scope -- This is the set of variables shared between JS and HTML.
  //   crmApi, crmStatus, crmUiHelp -- These are services provided by civicrm-core.
  //   myContact -- The current contact, defined above in config().
  angular.module('activitytabs').controller('ActivitytabsActivityTabsListCtrl', function($scope, crmApi, crmStatus, crmUiHelp, activitytabsList, activityTypes, activityFields) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('activitytabs');
    //var hs = $scope.hs = crmUiHelp({file: 'CRM/activitytabs/ActivityTabsListCtrl'}); // See: templates/CRM/activitytabs/ActivityTabsListCtrl.hlp

    $scope.activitytabsList = activitytabsList;
    $scope.screen = 'list';
    $scope.editIndex = null;
    $scope.editItem = null;
    $scope.activityTypes = activityTypes;
    $scope.activityFields = activityFields;

    var typesHash = {};
    activityTypes.forEach(t => typesHash[t.name] = t.label);
    var columnsHash = {};
    activityFields.forEach(t => columnsHash[t.name] = t.title);

    function saveSettings() {
      return crmStatus(
        // Status messages. For defaults, just use "{}"
        {start: ts('Saving...'), success: ts('Saved')},
        // The save action. Note that crmApi() returns a promise.
        crmApi('Setting', 'create', { 'activitytabs': JSON.stringify(activitytabsList) })
      );
    }

    $scope.saveItem = function saveItem() {
      activitytabsList[$scope.editIndex] = $scope.editItem;
      $scope.cancelEdit();
      return saveSettings();
    };
    $scope.cancelEdit = function cancelEdit() {
      $scope.editIndex = null;
      $scope.editItem = null;
      $scope.screen = 'list';
    }
    $scope.showEditScreen = function (index) {
      $scope.editIndex = index;
      $scope.editItem = Object.assign({}, activitytabsList[index]);
      $scope.screen = 'edit';
    };
    $scope.deleteItemConfirm = function(index) {
      console.log("huh?");
      $scope.editIndex = index;
      $scope.editItem = Object.assign({}, activitytabsList[index]);
      $scope.screen = 'delete';
    };
    $scope.deleteItem = function() {
      activitytabsList.splice($scope.editIndex, 1);
      $scope.cancelEdit();
      return saveSettings();
    };
    $scope.typeList = function(types) {
      return types.map(t => typesHash[t]).join(', ');
    };
    $scope.colList = function(cols) {
      return cols.map(t => columnsHash[t]).join(', ');
    };
  });

})(angular, CRM.$, CRM._);
