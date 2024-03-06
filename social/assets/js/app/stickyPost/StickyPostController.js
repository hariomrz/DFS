!(function () {
    'use strict';

    app.controller('StickyPostController', ['$scope', '$log', '$window', 'appInfo', 'WallService', function ($scope, $log, $window, appInfo, WallService) {
    	// Params: ModuleID,ModuleEntityGUID,PageSize,PageNo
        var StickyPostCtrl = this;
        StickyPostCtrl.totalRecords = 0;
        StickyPostCtrl.baseURL = base_url;
        StickyPostCtrl.ShouldLoadMore = true;
        StickyPostCtrl.IncludeGroupSticky = 0;
        StickyPostCtrl.IsStickyRequested = false;
        StickyPostCtrl.ImageServerPath = image_server_path;
        StickyPostCtrl.stickiesAlreadyShown = {};
        StickyPostCtrl.PageNo = 1;
        var requestData = {
          ModuleID: angular.element('#module_id').val(),
          ModuleEntityGUID : angular.element('#module_entity_guid').val(),
          IncludeGroupSticky : StickyPostCtrl.IncludeGroupSticky, //0 to include group sticky else 1
          PageSize : 10,
          PageNo : StickyPostCtrl.PageNo
        };

        StickyPostCtrl.stickyPostList = [];

        StickyPostCtrl.createDateObj = function(dateFromDb, ActivityGUID) {
            StickyPostCtrl.stickiesAlreadyShown[ActivityGUID] = true;
            return new Date(dateFromDb.replace(/-/g,"/"));
        };
        
        StickyPostCtrl.sticky_date_format = function(date)
        {
          var localTime = new Date(date);
          date = moment.tz(localTime, TimeZone);

          return date.format('D MMM')+' at '+date.format('LT');
        }

        StickyPostCtrl.createSharedPostContent = function(sticky) {
            var shareTypes = { Share : true, ShareSelf : true, ShareMedia : true, ShareMediaSelf : true };
            if( shareTypes[sticky.ActivityType] && ( sticky.PostContent == '' ) ){              
              return "Shared Post.";
            }
            return sticky.PostContent;
        };

        StickyPostCtrl.openStickyPopup = function(popupType, ActivityGUID) {
          var stickyData = { action: 'open', popupType : popupType };
          if(ActivityGUID){
            stickyData['ActivityGUID'] = ActivityGUID;
          }
          $scope.$emit('toggleStickyPopup',  stickyData);
        };

        StickyPostCtrl.getStickyPostList = function(includeGroupSticky) {
          if(includeGroupSticky != undefined) {
            requestData['IncludeGroupSticky'] = ( includeGroupSticky ) ? '1' : '0' ;
            requestData.PageNo = StickyPostCtrl.PageNo = 1;
            StickyPostCtrl.ShouldLoadMore = true;
          } else {
            requestData.PageNo = StickyPostCtrl.PageNo;
          }
          if(!StickyPostCtrl.IsStickyRequested && StickyPostCtrl.ShouldLoadMore){
            StickyPostCtrl.IsStickyRequested = true;
            var stickyUrl = ( StickyPostCtrl.stickyType == 1 ) ? 'sticky/get_sticky_by_others' : 'sticky/get_sticky_by_me' ;
            WallService.CallPostApi(appInfo.serviceUrl + stickyUrl, requestData, function (successResp) {
              var response = successResp.data;
              if( response.ResponseCode == 200 ) {
                StickyPostCtrl.totalRecords = response.TotalRecords;
                if(StickyPostCtrl.PageNo === 1) {
                  StickyPostCtrl.stickyPostList = response.Data;
                } else {
                  StickyPostCtrl.stickyPostList = StickyPostCtrl.stickyPostList.concat(response.Data);
                }
                if(StickyPostCtrl.stickyPostList.length >= StickyPostCtrl.totalRecords) {
                  StickyPostCtrl.ShouldLoadMore = false;
                } else {
                  StickyPostCtrl.ShouldLoadMore = true;
                }
                requestData.IncludeGroupSticky = StickyPostCtrl.IncludeGroupSticky = response.IncludeGroupSticky;
                StickyPostCtrl.PageNo++;
              }
              delete requestData['IncludeGroupSticky'];
              StickyPostCtrl.IsStickyRequested = false;
            },
            function (error) {
                $log.log(error);
            });
          }
        };
        
        $scope.$on('updateStickyToStickyWidget', function (event, stickyData) {
          console.log(' 1 ',StickyPostCtrl.stickyType);
          if( StickyPostCtrl.stickyType == 2 ) {
          console.log(' 2 ',StickyPostCtrl.stickyType);
            if( stickyData.stickyAction === 'create' ) {
              if( !( stickyData.ModuleID == '1' && requestData.IncludeGroupSticky == 0 ) ) {
                if( StickyPostCtrl.stickiesAlreadyShown[stickyData.newSticky.ActivityGUID] ) {
                    angular.forEach(StickyPostCtrl.stickyPostList, function (stickyPost, index) {
                      if (stickyPost.ActivityGUID === stickyData.newSticky.ActivityGUID) {
  //                      StickyPostCtrl.stickyPostList[index] = stickyData.newSticky;
                        StickyPostCtrl.stickyPostList.splice(index,1);
                        return false;
                      }
                    });
                    StickyPostCtrl.stickyPostList.unshift(stickyData.newSticky);
                } else {
                  StickyPostCtrl.stickyPostList.unshift(stickyData.newSticky);
                  StickyPostCtrl.totalRecords++;
                }
              }
            } else if ( ( stickyData.stickyAction === 'remove' ) && ( StickyPostCtrl.stickyPostList.length ) ) {
              angular.forEach(StickyPostCtrl.stickyPostList, function (stickyPost, stickyIndex) {
                !(function (index) {
                  if (stickyPost.ActivityGUID === stickyData.newSticky.ActivityGUID) {
                    if ( stickyData.newSticky.SelfSticky ) {
                      StickyPostCtrl.stickyPostList[index].SelfSticky = stickyData.newSticky.SelfSticky;
                      StickyPostCtrl.stickyPostList[index].GroupSticky = stickyData.newSticky.GroupSticky;
                      StickyPostCtrl.stickyPostList[index].EveryoneSticky = stickyData.newSticky.EveryoneSticky;
                    } else {
                      if (StickyPostCtrl.stickiesAlreadyShown[stickyPost.ActivityGUID]) {
                        delete StickyPostCtrl.stickiesAlreadyShown[stickyPost.ActivityGUID];
                      }
                      StickyPostCtrl.stickyPostList.splice(index,1);
                      StickyPostCtrl.totalRecords--;
                    }
                    return false;
                  }
                })(stickyIndex);
              });
            }
          }
        });
        
        StickyPostCtrl.unmarkAsSticky = function (ActivityGUID, removeStickyFor, stickyIndex) { //remove sticky
          var reqData = {
            ActivityGUID: ActivityGUID,
            StickyType: removeStickyFor ? removeStickyFor : 1
          };
          WallService.CallPostApi(appInfo.serviceUrl + 'sticky/remove_sticky', reqData, function (successResp) {
            var response = successResp.data;
            var responseJson = response.Data;
            var entity = '';
            switch (removeStickyFor) {
              case 1:
                entity = ' you ';
                break;
              case 2:
                entity = ' group ';
                break;
              case 3:
                entity = ' everyone ';
                break;
              default:
                //default code block
            }
            if ( ( response.ResponseCode == 200 ) && StickyPostCtrl.stickyPostList[stickyIndex] ) {
              var stickyDataToUpdate = { ActivityGUID : ActivityGUID };
              if ( responseJson.SelfSticky ) {
                StickyPostCtrl.stickyPostList[stickyIndex].SelfSticky = responseJson.SelfSticky;
                StickyPostCtrl.stickyPostList[stickyIndex].GroupSticky = responseJson.GroupSticky;
                StickyPostCtrl.stickyPostList[stickyIndex].EveryoneSticky = responseJson.EveryoneSticky;
                stickyDataToUpdate = responseJson;
              } else {
                if (StickyPostCtrl.stickiesAlreadyShown[ActivityGUID]) {
                  StickyPostCtrl.totalRecords--;
                  delete StickyPostCtrl.stickiesAlreadyShown[ActivityGUID];
                }
                StickyPostCtrl.stickyPostList.splice(stickyIndex,1);
              }
              $scope.$emit('updateStickyToNewsFeed', {stickyAction: 'remove', stickyDataToUpdate : stickyDataToUpdate, removeStickyFor : removeStickyFor } );
              showResponseMessage('This post is no more sticky for' + entity + '.', 'alert-success');
            } else if( response.Message && ( response.Message !== '' ) ) {
              showResponseMessage(response.Message, 'alert-danger');
            }
          });
        };
        
    }]);
    
})();