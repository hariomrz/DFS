!(function () {
    'use strict';

    app.controller('FileTabController', ['$scope', '$log', '$window', 'appInfo', 'WallService', function ($scope, $log, $window, appInfo, WallService) {
    	// Params: ModuleID,ModuleEntityGUID,PageSize,PageNo
        var FileTabCtrl = this;
        var totalRecords = 0;
        FileTabCtrl.baseURL = base_url;
        FileTabCtrl.isFileListRequested = false;
        FileTabCtrl.ShouldLoadMore = false;
        FileTabCtrl.SearchText = '';
        FileTabCtrl.SearchAction = '';
        $scope.isActivityPrevented = true;
        var requestData = {
          ModuleID: angular.element('#module_id').val(),
          ModuleEntityGUID : angular.element('#module_entity_guid').val(),
          IsNewsFeed : 0,
          SearchKey : '',
          PageSize : 20,
          PageNo : 1
        };
        FileTabCtrl.fileTabList = [];

        angular.element('.wallloader').hide();

        FileTabCtrl.createDateObj = function(dateFromDb) {
            return new Date(dateFromDb.replace(/-/g,"/"));
        };
        
        FileTabCtrl.addClassesToIcon = function(file) {
          
          var responseClass = [];
          
          if(file.MediaExtension) {
            responseClass.push(file.MediaExtension);
          }
          
          if(file.EntityType) {
            responseClass.push(file.EntityType.toLowerCase());
          }
          
          return responseClass;
        };
        
        FileTabCtrl.hitToDownload = function(MediaGUID, ConversionStatus, mediaFolder) {
          if(ConversionStatus !== 'Pending') {
            mediaFolder = ( mediaFolder && ( mediaFolder != '' ) ) ? mediaFolder : 'wall';
            $window.location.href = FileTabCtrl.baseURL + 'home/download/' + MediaGUID + '/' + mediaFolder;
          } else {
            showResponseMessage("File isn't processed yet, Please try again later.", "alert-danger");
          }
        };
        
        FileTabCtrl.onSearchTextChange = function() {
            FileTabCtrl.SearchAction = '';
        };

        FileTabCtrl.doFilesTabAction = function(isSearch, SearchAction) {
            if(!FileTabCtrl.isFileListRequested){
              if(SearchAction === ''){
  //              Search
                requestData.PageNo = ( isSearch ) ? 1 : requestData.PageNo;
              } else {
  //              Reset
                  FileTabCtrl.SearchAction = '';
                  FileTabCtrl.SearchText = '';
                  requestData.PageNo = 1;
              }
              FileTabCtrl.getFilesList();
            }
        };

        FileTabCtrl.getFilesList = function() {
          if(!FileTabCtrl.isFileListRequested){
            FileTabCtrl.isFileListRequested = true;
            requestData.SearchKey = FileTabCtrl.SearchText.trim();
            requestData.IsNewsFeed = FileTabCtrl.NewsFeedFileTab;
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/get_entity_files', requestData, function (successResp) {
              var response = successResp.data;
              if( response.ResponseCode === 200 ) {
                totalRecords = response.TotalRecords;
                if(requestData.PageNo === 1) {
                  FileTabCtrl.fileTabList = response.Data;
                } else {
                  FileTabCtrl.fileTabList = FileTabCtrl.fileTabList.concat(response.Data);                        
                }
                if(FileTabCtrl.fileTabList.length >= totalRecords) {
                  FileTabCtrl.ShouldLoadMore = false;
                } else {
                  FileTabCtrl.ShouldLoadMore = true;
                }
                requestData.PageNo++;
              }
              if(FileTabCtrl.SearchText.length > 0) {
                FileTabCtrl.SearchAction = 'icon-removeclose';
              } else {
                FileTabCtrl.SearchAction = '';
              }
              FileTabCtrl.isFileListRequested = false;
            },
            function (error) {
                $log.log(error);
            });
          }
        };
    }]);
    
})();