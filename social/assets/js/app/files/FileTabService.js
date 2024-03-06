
!(function () {
  'use strict';

  function FileTabService($http, appInfo) {

    /**
     * [getFilesList description]
     * @param  {[obj]} data    [description]
     * @param  {[function]} success [description]
     * @param  {[function]} error   [description]
     * @return {[promise]}         [description]
     */
    this.getFilesList = function getFilesList(data, success, error) {
//      return  $http.get(appInfo.serviceUrl + 'activity/get_entity_files' + '?ModuleID=' + data.ModuleID+ '&ModuleEntityGUID=' + data.ModuleEntityGUID + '&PageSize=' + data.PageSize + '&PageNo=' + data.PageNo + '&SearchKey=' + data.SearchKey).then(success, error);
      return  $http.post(appInfo.serviceUrl + 'activity/get_entity_files',data).then(success, error);
    };

  }

  app.service('FileTabService', ['$http', 'appInfo', FileTabService]);
})();