//Reminder controller 
angular.module('App').controller('ReminderCtrl', ['GlobalService','$scope', '$rootScope', 'Settings', '$sce', '$timeout', 'setFormatDate', '$interval', 'appInfo', 'WallService', function(GlobalService,$scope, $rootScope, Settings, $sce, $timeout, setFormatDate, $interval, appInfo, WallService) {
        
    $scope.saveReminder = function(ActivityGUID){
        //'field' => 'ActivityGUID',
        //'field' => 'ReminderDateTime',
        var jsonData = {
            'ActivityGUID':ActivityGUID,
            'ReminderDateTime':''
        }
        WallService.CallPostApi(appInfo.serviceUrl + 'reminder/add', jsonData, function (successResp) {
          var response = successResp.data;
            
        });
    }

}]);//end of controller
