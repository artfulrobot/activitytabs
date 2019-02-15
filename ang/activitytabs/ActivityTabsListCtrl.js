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
          }
        }
      });
    }
  );

  // The controller uses *injection*. This default injects a few things:
  //   $scope -- This is the set of variables shared between JS and HTML.
  //   crmApi, crmStatus, crmUiHelp -- These are services provided by civicrm-core.
  //   myContact -- The current contact, defined above in config().
  angular.module('activitytabs').controller('ActivitytabsActivityTabsListCtrl', function($scope, crmApi, crmStatus, crmUiHelp, activitytabsList) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('activitytabs');
    //var hs = $scope.hs = crmUiHelp({file: 'CRM/activitytabs/ActivityTabsListCtrl'}); // See: templates/CRM/activitytabs/ActivityTabsListCtrl.hlp

    $scope.activitytabsList = activitytabsList;
    $scope.screen = 'list';
    $scope.editIndex = null;
    $scope.editItem = null;

    $scope.saveItem = function saveItem() {
      activitytabsList[$scope.editIndex] = $scope.editItem;
      $scope.cancelEdit();
      // @todo save.
      return;

      return crmStatus(
        // Status messages. For defaults, just use "{}"
        {start: ts('Saving...'), success: ts('Saved')},
        // The save action. Note that crmApi() returns a promise.
        crmApi('Contact', 'create', {
          id: myContact.id,
          first_name: myContact.first_name,
          last_name: myContact.last_name
        })
      );
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
    $scope.deleteItem = function() {
      activitytabsList.splice($scope.editIndex, 1);
      $scope.cancelEdit();
      // @todo save.
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
      // @todo save.
    };
  });

})(angular, CRM.$, CRM._);
