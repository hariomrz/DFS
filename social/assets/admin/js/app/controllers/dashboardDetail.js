!(function () {
  'use strict';
  app.controller('DashboardDetailController', ['$scope', '$rootScope', '$timeout', '$q', 'webStorage', 'DashboardService', function ($scope, $rootScope, $timeout, $q, webStorage, DashboardService) {
      var unverifiedEntitiesListDefault = {
        search: '',
        entityType: 'USERS',
        page_size: 12,
        page_no: 1
      };


      $scope.unverifiedEntitiesList = {};
      $scope.unverifiedEntitiesListCount = 0;
      $scope.activeTab = webStorage.getStorageData('activeTab');
      
        $scope.entitySearchPlaceholder =  ' '+ $scope.lang.ModulesTypes_profiles;
        if($scope.Settings.m1 == 1) {
            $scope.entitySearchPlaceholder += '/'+  $scope.lang.ModulesTypes_groups;
        }

        if($scope.Settings.m18 == 1) {
            $scope.entitySearchPlaceholder += '/'+  $scope.lang.ModulesTypes_pages;
        }
        
        $scope.activeTab = 'All';
                    
      if ( $scope.activeTab === false ) {
        unverifiedEntitiesListDefault['entityType'] = 'USERS';
      }
      
      if($scope.activeTab == 'Groups')
      {
        unverifiedEntitiesListDefault['entityType'] = 'GROUPS';
      }
      if($scope.activeTab == 'Pages')
      {
        unverifiedEntitiesListDefault['entityType'] = 'PAGES';
      }
      if($scope.activeTab == 'All')
      {
        unverifiedEntitiesListDefault['entityType'] = 'ALL';
      }

      $scope.unverifiedEntitiesListLoader = false;
      $scope.isUpdateEntityProcessing = false;
      $scope.isUpdateEntityTagsProcessing = false;
      $scope.pageNo = 1;
      $scope.rowDisplayLimit = 11;
      $scope.charDisplayLimit = 99;
      $scope.EntityTotalRecord = 0;
      $scope.listObj = angular.copy(unverifiedEntitiesListDefault);

      $scope.dynamicPopover = 'myPopoverTemplate.html';
      $scope.busyUnverifiedEntitiesService = false;

      $scope.searchUnverifiedEntities = function () {
        //if ( ( ( $scope.listObj.search.length === 0 ) || ( $scope.listObj.search.length > 2 ) ) && !$scope.unverifiedEntitiesListLoader ) {
          $scope.pageNo = $scope.listObj.page_no = unverifiedEntitiesListDefault.page_no;
          $scope.unverifiedEntitiesList = {};
          $scope.unverifiedEntitiesListCount = 0;
          $scope.EntityTotalRecord = 0;
          $scope.getUnverifiedEntities();
        //}
      }
      
      
      $scope.getUnverifiedEntities = function (entityType) {
        if($scope.busyUnverifiedEntitiesService) {
            return;
        }
        
        
        $scope.busyUnverifiedEntitiesService = true;
        if (!$scope.unverifiedEntitiesListLoader && ( ( $scope.unverifiedEntitiesListCount < $scope.EntityTotalRecord ) || ( $scope.pageNo === 1 ) ) ) {
//          $scope.listObj.entityType = (entityType) ? entityType : 'USERS';
          $scope.unverifiedEntitiesListLoader = true;
          DashboardService.CallPostApi('admin_api/dashboard/get_unverified_entities', $scope.listObj, function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
              var unverifiedEntitiesListPromises = [],
                  unverifiedEntitiesList = {};
              angular.forEach(response.Data, function (entityInfo, entityKey) {
                  
                (function(entity, key){
                    
                    var entityData = entity;
                  //unverifiedEntitiesListPromises.push($scope.makeResolvedPromisefunction(entity).then(function (entityData) {
                    if (!$scope.unverifiedEntitiesList[entityData.ModuleEntityID]) {
                      if ( entityData.CreatedDate ) {
                        entityData.CreatedDate = $scope.parseDateOnly(entityData.CreatedDate);
                        entityData['OrderBy'] = new Date(entityData.CreatedDate);
                      } else {
                        entityData['OrderBy'] = '';
                      }
                      unverifiedEntitiesList['unverified_prefix_' + entityData.ModuleEntityID] = entityData;
                    }
                  //}));
                })(entityInfo, entityKey);
              });
              
              populateData(response, unverifiedEntitiesList);
//              $q.all(unverifiedEntitiesListPromises).then(function (data) {
//                
//              });
            } else {
              $scope.unverifiedEntitiesListLoader = false;    
              ShowErrorMsg(response.Message);
            }
            
          }, function () {
            $scope.unverifiedEntitiesListLoader = false;
          });
        }

      }
      
      function populateData(response, unverifiedEntitiesList) {
        $scope.EntityTotalRecord = parseInt( response.TotalRecords );
        if ($scope.pageNo > 1) {
//                  $scope.unverifiedEntitiesList = $scope.unverifiedEntitiesList.concat(response.Data);
          $scope.unverifiedEntitiesList = angular.extend({}, $scope.unverifiedEntitiesList, unverifiedEntitiesList);
        } else {
          $scope.unverifiedEntitiesList = angular.copy(unverifiedEntitiesList);
        }
        $scope.unverifiedEntitiesListCount = Object.keys($scope.unverifiedEntitiesList).length;
        $scope.pageNo++;
        $scope.listObj.page_no = $scope.pageNo; 
        $scope.unverifiedEntitiesListLoader = false;   
        $scope.busyUnverifiedEntitiesService = false;       
      }
      
      
      
      $scope.makeResolvedPromisefunction = function (data) {
        var deferred = $q.defer();
        deferred.resolve(data);
        return deferred.promise;
      }

      $scope.removeAndUpdateEntityList = function (entityIndex) {
        if ($scope.unverifiedEntitiesList[entityIndex]) {
          //$scope.unverifiedEntitiesList[entityIndex].Verified = 1;
          delete $scope.unverifiedEntitiesList[entityIndex];
          $scope.unverifiedEntitiesListCount = Object.keys($scope.unverifiedEntitiesList).length;
          $scope.EntityTotalRecord--;
        }
      }

      $scope.showUserPersona = function(a,b,c)
      {
        return true;
        showUserPersona(a,b,c); 
      }

      $scope.updateEntity = function (ModuleID, ModuleEntityID, action, entityIndex) {
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
                    msg = (action === 'verify') ? 'Verified successfully' : 'Deleted successfully';
                    ShowSuccessMsg(msg);
                    $scope.removeAndUpdateEntityList(entityIndex);
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
              msg = (action === 'verify') ? 'Verified successfully' : 'Deleted successfully';
              ShowSuccessMsg(msg);
              $scope.removeAndUpdateEntityList(entityIndex);
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
      }
      
      $scope.updateEntityTags = function (entityData, entityIndex) {
        if ( entityData && entityData.ModuleEntityID && entityData.ModuleID && !$scope.isUpdateEntityTagsProcessing) {
          var requestObj = {}, url, msg;
          if ( entityData.Featured_TagID == 0 ) {
            requestObj = {
              "EntityID": entityData.ModuleEntityID,              
              "TagsList": [ { "Name": "Feature" }],
              "IsFrontEnd": "1",
              "TagsIDs": []
            };
            switch ( true ) {
                case ( entityData.ModuleID == 3 ):
                    requestObj['EntityType'] = 'USER';
                    requestObj['TagType'] = 'USER';
                    break;
                case ( entityData.ModuleID == 1 ):
                    requestObj['EntityType'] = 'GROUP';
                    requestObj['TagType'] = 'GROUP';
                    break;
                case ( entityData.ModuleID == 18 ):
                    requestObj['EntityType'] = 'PAGE';
                    requestObj['TagType'] = 'PAGE';
                    break;
            }
            url = 'api/tag/save';
          } else {
            requestObj = {
              "EntityID": entityData.ModuleEntityID,
              "EntityTagIDs": [entityData.Featured_TagID]
            }
            switch ( true ) {
                case ( entityData.ModuleID == 3 ):
                    requestObj['EntityType'] = 'USER';
                    break;
                case ( entityData.ModuleID == 1 ):
                    requestObj['EntityType'] = 'GROUP';
                    break;
                case ( entityData.ModuleID == 18 ):
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
              if ( entityData.Featured_TagID == 0 ) {
                if ( response.Data && response.Data[0].EntityTagID ) {
                  $scope.unverifiedEntitiesList[entityIndex].Featured_TagID = response.Data[0].EntityTagID;
                }
                if ( ( entityData.ModuleID  == 1 ) || ( entityData.ModuleID  == 18 ) ) {
                  if ( $scope.unverifiedEntitiesList[entityIndex].Tags ) {
                    //$scope.unverifiedEntitiesList[entityIndex].Tags.push('Feature');

                    $scope.unverifiedEntitiesList[entityIndex].Tags.push({EntityTagID: response.Data[0].EntityTagID, Name: 'Feature'});

                  } else {
                    $scope.unverifiedEntitiesList[entityIndex]['Tags'] = ['Feature']
                  }
                }
                msg = 'Mark as featured successfully.';
              } else {                
                if ( ( ( entityData.ModuleID  == 1 ) || ( entityData.ModuleID  == 18 ) ) && $scope.unverifiedEntitiesList[entityIndex].Tags ) {

                  angular.forEach($scope.unverifiedEntitiesList[entityIndex].Tags, function (tagInfo, tagKey) {
                    return (function(tag, key){
                      if ( tag.EntityTagID == $scope.unverifiedEntitiesList[entityIndex].Featured_TagID ) {
                        $scope.unverifiedEntitiesList[entityIndex].Tags.splice(key, 1);
                      }
                    })(tagInfo, tagKey);
                  });

                  /*var index = $scope.unverifiedEntitiesList[entityIndex].Tags.indexOf('Feature');
                  if ( index > -1 ) {
                    $scope.unverifiedEntitiesList[entityIndex].Tags.splice(index, 1);
                  }*/
                  
                }
                $scope.unverifiedEntitiesList[entityIndex].Featured_TagID = 0;
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
      }

      $scope.parseDateOnly = function (date) {
        var dateArray = date.split(' ');
        return dateArray[0];
      };

      $scope.changeTab = function ($event, entityType, tabToActive) {
        if ($scope.unverifiedEntitiesListLoader) {
           $event.stopPropagation();
        } else {
          $scope.listObj = angular.copy(unverifiedEntitiesListDefault);
          $scope.pageNo = 1;
          $scope.listObj.entityType = entityType;
          $scope.unverifiedEntitiesList = {};
          $scope.EntityTotalRecord = 0;
          $scope.getUnverifiedEntities(entityType);
          $scope.activeTab = tabToActive;
          webStorage.setStorageData('activeTab', $scope.activeTab);
        }
      };

      $scope.parseTags = function (entityTags, entityIndex) {
        if ( entityTags && ( entityIndex || ( entityIndex > -1 ) ) ) {
          $scope.unverifiedEntitiesList[entityIndex]['ParseTags'] = entityTags.split(',');
        }
      };
      
      // If only profile module is enabled
      if(($scope.Settings.m18 == 0 && $scope.Settings.m1 == 0)) {            
            $scope.changeTab({stopPropagation : function(){}}, 'USERS', 'Profiles');
      }
      

    }]);

})();
