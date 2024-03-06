!(function () {
  'use strict';
  app.controller('GroupDetailModalPopupController', ['$scope', '$http', '$uibModal', '$uibModalInstance', '$q', 'DashboardService', 'modalData', function ($scope, $http, $uibModal, $uibModalInstance, $q, DashboardService, modalData) {
      $scope.entityInfo = modalData;
      $scope.partialPageUrl = base_url + 'assets/admin/js/app/partials/';
      $scope.imageServerPath = image_server_path;
      $scope.noteUpdateMode = false;
      $scope.noteProcessing = false;
      $scope.tagUpdateMode = false;
      $scope.tagUpdateProcessing = false;
      $scope.NewAddedTags = [];
      $scope.TagsToRemove = [];
      
      $scope.createDateObject = function (date) {
        return new Date(date);
      };
      
      $scope.openMsg = function (modalData) {
        var modalInstance = $uibModal.open({
          templateUrl: $scope.partialPageUrl + 'messageModalPopup.html',
          controller: 'MsgModalPopupController',
          resolve: {
            modalData: function () {
              return modalData;
            }
          }
        });

//        modalInstance.result.then(function (selectedItem) {
//          console.log('selectedItem : ', selectedItem);
//        }, function () {
//          console.log('Msg Modal Popup dismissed at: ' + new Date());
//        });
      };
      
      $scope.updateEntityTags = function () {
        if ( $scope.entityInfo && $scope.entityInfo.ModuleEntityGUID && $scope.entityInfo.ModuleID && !$scope.isUpdateEntityTagsProcessing) {
          var requestObj = {}, url, msg;
          if ( $scope.entityInfo.Featured_TagID == 0 ) {
            requestObj = {
              "EntityGUID": $scope.entityInfo.ModuleEntityGUID,              
              "TagsList": [ { "Name": "Feature" }],
              "IsFrontEnd": "1",
              "TagsIDs": []
            };
            switch ( true ) {
                case ( $scope.entityInfo.ModuleID == 1 ):
                    requestObj['EntityType'] = 'GROUP';
                    requestObj['TagType'] = 'GROUP';
                    break;
                case ( $scope.entityInfo.ModuleID == 18 ):
                    requestObj['EntityType'] = 'PAGE';
                    requestObj['TagType'] = 'PAGE';
                    break;
            }
            url = 'api/tag/save';
          } else {
            requestObj = {
              "EntityGUID": $scope.entityInfo.ModuleEntityGUID,
              "EntityTagIDs": [$scope.entityInfo.Featured_TagID]
            }
            switch ( true ) {
                case ( $scope.entityInfo.ModuleID == 1 ):
                    requestObj['EntityType'] = 'GROUP';
                    break;
                case ( $scope.entityInfo.ModuleID == 18 ):
                    requestObj['EntityType'] = 'PAGE';
                    break;
            }
            url = 'api/tag/delete_entity_tag';
          }
//          console.log('url : ', url);
//          console.log('requestObj : ', requestObj); return false;
          $scope.isUpdateEntityTagsProcessing = true;
          DashboardService.CallPostApi(url, requestObj, function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
              if ( $scope.entityInfo.Featured_TagID == 0 ) {
                if ( response.Data && response.Data[0].EntityTagID ) {
                  $scope.entityInfo.Featured_TagID = response.Data[0].EntityTagID;
                }
                if ( ( $scope.entityInfo.ModuleID  == 1 ) || ( $scope.entityInfo.ModuleID  == 18 ) ) {
                  if ( $scope.entityInfo.Tags ) {
                    $scope.entityInfo.Tags.push({EntityTagID: response.Data[0].EntityTagID, Name: 'Feature'});
                  } else {
                    $scope.entityInfo['Tags'] = ['Feature']
                  }
                }
                msg = 'Mark as featured successfully.';
              } else {
                if ( ( ( $scope.entityInfo.ModuleID  == 1 ) || ( $scope.entityInfo.ModuleID  == 18 ) ) && $scope.entityInfo.Tags ) {
                  angular.forEach($scope.entityInfo.Tags, function (tagInfo, tagKey) {
                    return (function(tag, key){
                      if ( tag.EntityTagID == $scope.entityInfo.Featured_TagID ) {
                        $scope.entityInfo.Tags.splice(key, 1);
                      }
                    })(tagInfo, tagKey);
                  });
                }
                $scope.entityInfo.Featured_TagID = 0;
                msg = 'Remove from featured successfully.';
              }
              ShowSuccessMsg(msg);
            } else {
              ShowErrorMsg(response.Message);
            }
            $scope.isUpdateEntityTagsProcessing = false;
          }, function () {
            ShowErrorMsg('Unable to process.');
            $scope.isUpdateEntityTagsProcessing = false;
          });

        }
      };
      
      $scope.updateEntity = function (ModuleID, ModuleEntityID, action) {
        if(action == 'delete')
        {
          showAdminConfirmBox('Delete Entity','Are you sure, you want to delete this entity ?',function(e){
            if(e)
            {
              if (ModuleID && ModuleEntityID && !$scope.isUpdateEntityProcessing) {
                action = (action) ? action : 'verify';
                var requestObj = {
                  ModuleID: ModuleID,
                  ModuleEntityID: ModuleEntityID,
                  EntityColumn: (action === 'verify') ? 'Verified' : 'StatusID',
                  EntityColumnVal: (action === 'verify') ? 1 : 3,
                }, msg;
                $scope.isUpdateEntityProcessing = true;
                DashboardService.CallPostApi('admin_api/dashboard/update_entity', requestObj, function (resp) {
                  var response = resp.data;
                  if (response.ResponseCode == 200) {
                    if ( action === 'verify' ) {
                      $scope.entityInfo.IsVerified = 1;
                      msg = 'Verified successfully';
                    } else {
                      $scope.close('delete');
                      msg = 'Deleted successfully';
                    }
                    ShowSuccessMsg(msg);
                  } else {
                    ShowErrorMsg(response.Message);
                  }
                  $scope.isUpdateEntityProcessing = false;
                }, function () {
                  ShowErrorMsg('Unable to process.');
                  $scope.isUpdateEntityProcessing = false;
                });

              }
            }
          });
        }
        else
        {
          if (ModuleID && ModuleEntityID && !$scope.isUpdateEntityProcessing) {
          action = (action) ? action : 'verify';
          var requestObj = {
            ModuleID: ModuleID,
            ModuleEntityID: ModuleEntityID,
            EntityColumn: (action === 'verify') ? 'Verified' : 'StatusID',
            EntityColumnVal: (action === 'verify') ? 1 : 3,
          }, msg;
          $scope.isUpdateEntityProcessing = true;
          DashboardService.CallPostApi('admin_api/dashboard/update_entity', requestObj, function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
              if ( action === 'verify' ) {
                $scope.entityInfo.IsVerified = 1;
                msg = 'Verified successfully';
              } else {
                $scope.close('delete');
                msg = 'Deleted successfully';
              }
              ShowSuccessMsg(msg);
            } else {
              ShowErrorMsg(response.Message);
            }
            $scope.isUpdateEntityProcessing = false;
          }, function () {
            ShowErrorMsg('Unable to process.');
            $scope.isUpdateEntityProcessing = false;
          });

        }
        }
      };
      
      $scope.loadEntityTags = function ($query, ModuleEntityID, ModuleID, TagType, isSearch) {
        var url = 'api/tag/get_entity_tags';
        $query = $query.trim();
        url += '?SearchKeyword=' + $query;
        if(!isSearch) {
            url += '&EntityID=' + ModuleEntityID;
        }
        url += '&TagType=' + TagType;
        switch (true) {
          case (ModuleID == 1):
            url += '&EntityType=GROUP';
            break;
          case (ModuleID == 3):
            url += '&EntityType=USER';
            break;
          case (ModuleID == 14):
            url += '&EntityType=EVENT';
            break;
          case (ModuleID == 18):
            url += '&EntityType=PAGE';
            break;
        }
        return DashboardService.CallGetApi(url, function (resp) {
          var memberTagList = resp.data.Data;
          return memberTagList.filter(function (tlist) {
            return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
          });
        });
      };
      
      $scope.getEntityTags = function (ModuleEntityID, ModuleID, TagType) {
        var url = 'api/tag/get_entity_tags';
//        url += '?SearchKeyword=\'\'';
        url += '?EntityID=' + ModuleEntityID;
        url += '&TagType=' + TagType;
        switch (true) {
          case (ModuleID == 1):
            url += '&EntityType=GROUP';
            break;
          case (ModuleID == 3):
            url += '&EntityType=USER';
            break;
          case (ModuleID == 14):
            url += '&EntityType=EVENT';
            break;
          case (ModuleID == 18):
            url += '&EntityType=PAGE';
            break;
        }
        DashboardService.CallGetApi(url, function (resp) {
          var response = resp.data;
          if ( ( response.ResponseCode == 200 ) && ( response.Data.length > 0 ) ) {
            $scope.entityInfo.Tags = angular.copy(response.Data);
          }
        });
      };
      
      
      $scope.saveEntityTags = function (TagType, ModuleEntityID,  ModuleID, ModuleEntityGUID) {
        if (ModuleEntityID && ( ModuleID || ( ModuleID == 0 ) ) && TagType) {
          var requestObj = {}, msg;
          requestObj = {
            "EntityGUID": ModuleEntityGUID,
            "TagType": TagType,
            "TagsList": $scope.NewAddedTags, //[ { "Name": "Feature" }]
            "IsFrontEnd": "1",
            "TagsIDs": $scope.TagsToRemove
          };
          switch (true) {
            case (ModuleID == 1):
              requestObj['EntityType'] = 'GROUP';
              break;
            case (ModuleID == 3):
              requestObj['EntityType'] = 'USER';
              break;
            case (ModuleID == 14):
              requestObj['EntityType'] = 'EVENT';
              break;
            case (ModuleID == 18):
              requestObj['EntityType'] = 'PAGE';
              break;
          }
//          console.log('requestObj : ', requestObj); return false;
          $scope.tagUpdateProcessing = true;
          DashboardService.CallPostApi('api/tag/save', requestObj, function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
              $scope.getEntityTags(ModuleEntityID, ModuleID, TagType);
              $scope.NewAddedTags = [];
              $scope.TagsToRemove = [];
              msg = 'Updated successfully.';
              $scope.tagUpdateMode = false;
              ShowSuccessMsg(msg);
            } else {
              ShowErrorMsg(response.Message);
            }
            $scope.tagUpdateProcessing = false;
          }, function () {
            ShowErrorMsg('Unable to process.');
            $scope.tagUpdateProcessing = false;
          });

        }
      };
      
      $scope.addEntityTags = function (Tag) {
        if ( Tag && Tag.Name ) {
          $scope.NewAddedTags.push(Tag);
          angular.forEach($scope.TagsToRemove, function (tagInfo, tagKey) {
            return (function(tag, key){
              if ( tag == Tag.TagID ) {
                $scope.TagsToRemove.splice(key, 1);
              }
            })(tagInfo, tagKey);
          });
        }
      };

      $scope.removeEntityTags = function (Tag) {
        if ( Tag && Tag.Name ) {
          if ( Tag && Tag.TagID ) {
            $scope.TagsToRemove.push(Tag.TagID);
          }
          angular.forEach($scope.NewAddedTags, function (tagInfo, tagKey) {
            return (function(tag, key){
              if ( tag.Name == Tag.Name ) {
                $scope.NewAddedTags.splice(key, 1);
              }
            })(tagInfo, tagKey);
          });
        }
      };

      $scope.updateNote = function (noteModalFrom) {
        if (noteModalFrom.$submitted && noteModalFrom.$valid) {
          var requestObject = {
            "ModuleID": $scope.entityInfo.ModuleID,
            "ModuleEntityID": $scope.entityInfo.ModuleEntityID,
            "Description": $scope.entityInfo.Note
          };
          $scope.noteProcessing = true;
          DashboardService.CallPostApi('admin_api/dashboard/save_note', requestObject, function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
              $scope.noteUpdateMode = false;
              ShowSuccessMsg(response.Message);
            } else {
              ShowErrorMsg(response.Message);
            }
            $scope.noteProcessing = false;
          }, function () {
            $scope.noteProcessing = false;
          });
        }
      };
      
      $scope.getDefaultImgPlaceholder = function(name) {
          name = name.split(' ');
          if(name.length == 1)
          {
              name = name[0];
          }
          if(name.length > 1)
          {
            name = name[0].substring(1, 0) + name[1].substring(1, 0);
          }
          return name.toUpperCase();
      }
      
      $scope.makeResolvedPromisefunction = function (data) {
        var deferred = $q.defer();
        deferred.resolve(data);
        return deferred.promise;
      }

      $scope.close = function (action) {
        $uibModalInstance.close(action);
      };

      $scope.dismiss = function (dataOnDismiss) {
        $uibModalInstance.dismiss(dataOnDismiss);
      };
    }]);
})();