!(function () {
  'use strict';
  app.factory('DashboardService', ['$http', '$q', 'appInfo', function ($http, $q, appInfo) {
      // Return public API.
      return {
        CallPostApi: function (Url, payLoadData, success, error) { // Common Function to Call Post Api on given Url with request params.
          return $http.post(appInfo.serviceUrl + Url, payLoadData).then(success, error);
        },
        CallGetApi: function (Url, success, error) { // Common Function to Call Post Api on given Url with request params.
          return $http.get(appInfo.serviceUrl + Url).then(success, error);
        }
      };
    }]);
})();