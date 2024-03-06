!(function () {
  'use strict';
  app.controller('MsgModalPopupController', ['$scope', '$http', '$uibModalInstance', 'appInfo', 'modalData', function ($scope, $http, $uibModalInstance, appInfo, modalData) {
      var requestObject = {
        "ModuleID": modalData.ModuleID,
        "ModuleEntityID": modalData.ModuleEntityID,
        "Replyable": 1,
        "Body": "Testing message",
        "Media": [],
        "Subject": ''
      }
      $scope.message = '';
      $scope.recipientName = modalData.Name;
      $scope.messageProcessing = false;
      $scope.submitMessage = function (messageModalFrom) {
        if (messageModalFrom.$submitted && messageModalFrom.$valid) {
          requestObject.Body = $scope.message;
          $scope.messageProcessing = true;
          $http.post(appInfo.serviceUrl + 'admin_api/dashboard/send_message', requestObject).then( function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
              $scope.reset(messageModalFrom);
              $scope.close('close');
              ShowSuccessMsg(response.Message);
            } else {
              ShowErrorMsg(response.Message);
            }
            $scope.messageProcessing = false;
          }, function () {
            $scope.messageProcessing = false;
          });
        }
      };
      
      $scope.reset = function(form) {
        if (form) {
          form.$setPristine();
          form.$setUntouched();
        }
        $scope.message = '';
        $scope.recipientName = '';
      };
      
      $scope.close = function (dataOnClose) {
        $uibModalInstance.close(dataOnClose);
      };

      $scope.dismiss = function (dataOnDismiss) {
        $uibModalInstance.dismiss(dataOnDismiss);
      };
    }]);
})();