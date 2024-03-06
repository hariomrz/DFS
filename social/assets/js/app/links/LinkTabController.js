!(function () {
    'use strict';

    app.controller('LinkTabController', ['$scope', '$log', '$window', 'appInfo', 'WallService', function ($scope, $log, $window, appInfo, WallService) {
    	// Params: ModuleID,ModuleEntityGUID,PageSize,PageNo
        var LinkTabCtrl = this;
        var totalRecords = 0;
        LinkTabCtrl.baseURL = base_url;
        LinkTabCtrl.ImageServerPath = image_server_path;
        LinkTabCtrl.isLinkListRequested = false;
        LinkTabCtrl.ShouldLoadMore = false;
        LinkTabCtrl.SearchText = '';
        LinkTabCtrl.SearchAction = '';
        $scope.isActivityPrevented = true;
        var requestData = {
          ModuleID: angular.element('#module_id').val(),
          ModuleEntityGUID : angular.element('#module_entity_guid').val(),
          IsNewsFeed : 0,
          SearchKey : '',
          PageSize : 10,
          PageNo : 1
        };
        LinkTabCtrl.linkTabList = [];

        angular.element('.wallloader').hide();

        LinkTabCtrl.createDateObj = function(dateFromDb) {
            return new Date(dateFromDb.replace(/-/g,"/"));
        };
        
        LinkTabCtrl.addClassesToIcon = function(link) {
          
          var responseClass = [];
          
          if(link.MediaExtension) {
            responseClass.push(link.MediaExtension);
          }
          
          if(link.EntityType) {
            responseClass.push(link.EntityType.toLowerCase());
          }
          
          return responseClass;
        };
        
        LinkTabCtrl.hitToDownload = function(MediaGUID, ConversionStatus, mediaFolder) {
          if(ConversionStatus !== 'Pending') {
            mediaFolder = ( mediaFolder && ( mediaFolder != '' ) ) ? mediaFolder : 'wall';
            $window.location.href = LinkTabCtrl.baseURL + 'home/download/' + MediaGUID + '/' + mediaFolder;
          } else {
            showResponseMessage("Link isn't processed yet, Please try again later.", "alert-danger");
          }
        };
        
        LinkTabCtrl.onSearchTextChange = function() {
            LinkTabCtrl.SearchAction = '';
        };

        LinkTabCtrl.doLinksTabAction = function(isSearch, SearchAction) {
            if(!LinkTabCtrl.isLinkListRequested){
              if(SearchAction === ''){
  //              Search
                requestData.PageNo = ( isSearch ) ? 1 : requestData.PageNo;
              } else {
  //              Reset
                  LinkTabCtrl.SearchAction = '';
                  LinkTabCtrl.SearchText = '';
                  requestData.PageNo = 1;
              }
              LinkTabCtrl.getLinksList();
            }
        };

        LinkTabCtrl.getLinksList = function() {
          if(!LinkTabCtrl.isLinkListRequested){
            LinkTabCtrl.isLinkListRequested = true;
            requestData.SearchKey = LinkTabCtrl.SearchText.trim();
            requestData.IsNewsFeed = LinkTabCtrl.NewsFeedLinkTab;
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/get_entity_links', requestData, function (successResp) {
              var response = successResp.data;
              if( response.ResponseCode === 200 ) {
                totalRecords = response.TotalRecords;
                if(requestData.PageNo === 1) {
                  LinkTabCtrl.linkTabList = response.Data;
                } else {
                  LinkTabCtrl.linkTabList = LinkTabCtrl.linkTabList.concat(response.Data);                        
                }
                if(LinkTabCtrl.linkTabList.length >= totalRecords) {
                  LinkTabCtrl.ShouldLoadMore = false;
                } else {
                  LinkTabCtrl.ShouldLoadMore = true;
                }
                requestData.PageNo++;
              }
              if(LinkTabCtrl.SearchText.length > 0) {
                LinkTabCtrl.SearchAction = 'icon-removeclose';
              } else {
                LinkTabCtrl.SearchAction = '';
              }
              LinkTabCtrl.isLinkListRequested = false;
            },
            function (error) {
                $log.log(error);
            });
          }
        };
    }]);
    
})();