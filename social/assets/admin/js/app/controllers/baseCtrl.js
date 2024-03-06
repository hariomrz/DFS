!(function () {
  'use strict';
  function BaseController($scope, $rootScope, $timeout, $uibModal, $q, CommonService) {
    $scope.partialPageUrl = base_url + 'assets/admin/js/app/partials/';
    
    $scope.Settings = settings_data;
    $scope.lang = lang;
    
    $scope.$on('openMsgModalPopup', openMsgModalPopup);

    $scope.$on('openGroupDetailModalPopup', openGroupDetailModalPopup);

    function openMsgModalPopup(event, args) {
      var modalInstance = $uibModal.open({
        // ariaLabelledBy: 'modal-title',
        // ariaDescribedBy: 'modal-body',
        templateUrl: $scope.partialPageUrl + 'messageModalPopup.html',
        controller: 'MsgModalPopupController',
        resolve: {
          modalData: function () {
            return args;
          }
        }
      });

//      modalInstance.result.then(function (selectedItem) {
//        console.log('selectedItem : ', selectedItem);
//      }, function () {
//        console.log('Msg Modal Popup dismissed at: ' + new Date());
//      });
    }
    
    function openGroupDetailModalPopup(event, args) {
      var modalInstance = $uibModal.open({
        // ariaLabelledBy: 'modal-title',
        // ariaDescribedBy: 'modal-body',
        size: 'lg',
        templateUrl: $scope.partialPageUrl + 'groupDetailModalPopup.html',
        controller: 'GroupDetailModalPopupController',
        resolve: {
          modalData: function () {
            var requestObj = {
              "ModuleID": args.ModuleID,
              "ModuleEntityID": args.ModuleEntityID
            },
            deferred = $q.defer();
            CommonService.CallPostApi('admin_api/dashboard/get_unverified_entity', requestObj, function (resp) {
              var response = resp.data;
              if ( ( response.ResponseCode == 200 ) && response.Data && ( Object.keys( response.Data ).length > 0 ) ) {
                deferred.resolve(response.Data);
              } else {
                ShowErrorMsg('Unable to process.');
                deferred.reject({});
              }
              $scope.isUpdateEntityProcessing = false;
            }, function () {
              ShowErrorMsg('Unable to process.');
              deferred.reject({});
            });
            return deferred.promise.then(function(resolved){
              return resolved
            });
          }
        }
      });

      modalInstance.result.then(function (action) {
        $scope.$broadcast('refreshAdminDashbord', {action: action})
      }, function () {
//        console.log('Group Detail Modal Popup dismissed at: ' + new Date());
      });
    }
  }
  app.controller('BaseController', ['$scope', '$rootScope', '$timeout', '$uibModal', '$q', 'CommonService', BaseController]);
})();