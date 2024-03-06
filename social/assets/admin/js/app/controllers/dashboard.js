!(function () {
    'use strict';
    app.controller('DashboardController', ['$scope', '$rootScope', '$timeout', '$q', 'DashboardService', 'socket', function ($scope, $rootScope, $timeout, $q, DashboardService, socket) {
            var unverifiedEntitiesListDefault = {
                search: '',
//        entityType: 'GROUPS',
//        entityType: 'PAGES',
                entityType: 'ALL',
                page_size: 22,
                page_no: 1
            };
            $scope.unverifiedEntitiesList = {};
            $scope.unverifiedEntitiesListCount = 0;
            $scope.unverifiedEntitiesListLoader = false;
            $scope.isUpdateEntityProcessing = false;
            $scope.pageNo = 1;
            $scope.rowDisplayLimit = 11;
            $scope.listObj = angular.copy(unverifiedEntitiesListDefault);

            
            $scope.entitySearchPlaceholder =  ' '+ $scope.lang.ModulesTypes_profiles;
            if($scope.Settings.m1 == 1) {
                $scope.entitySearchPlaceholder += '/'+  $scope.lang.ModulesTypes_groups;
            }
            
            if($scope.Settings.m18 == 1) {
                $scope.entitySearchPlaceholder += '/'+  $scope.lang.ModulesTypes_pages;
            }
            
//      $scope.dynamicPopover = 'myPopoverTemplate.html';
            $scope.dynamicPopover = base_url + 'assets/admin/js/app/partials/profilePopover.html';
            $scope.partialsUrl = partialsUrl;
            $scope.imageServerPath = image_server_path;
            $scope.showSeeMore = true;

            $scope.searchUnverifiedEntities = function () {
                //if ( ( ( $scope.listObj.search.length === 0 ) || ( $scope.listObj.search.length > 2 ) ) && !$scope.unverifiedEntitiesListLoader ) {
                $scope.listObj.entityType = unverifiedEntitiesListDefault.entityType;
                $scope.pageNo = $scope.listObj.page_no = unverifiedEntitiesListDefault.page_no;
                $scope.unverifiedEntitiesList = {};
                $scope.unverifiedEntitiesListCount = 0;
                $scope.EntityTotalRecord = 0;
                $scope.showSeeMore = true;
                if ($scope.listObj.search.length) {
                    $scope.showSeeMore = false;
                }
                $scope.getUnverifiedEntities();
                //}
            }

            $scope.getUnverifiedEntities = function () {
//        console.log('unverifiedEntitiesList : ', $scope.unverifiedEntitiesListCount);
//        console.log('EntityTotalRecord : ', $scope.EntityTotalRecord);
                if (!$scope.unverifiedEntitiesListLoader && (($scope.unverifiedEntitiesListCount <= $scope.EntityTotalRecord) || ($scope.pageNo === 1))) {
                    $scope.unverifiedEntitiesListLoader = true;
                    DashboardService.CallPostApi('admin_api/dashboard/get_unverified_entities', $scope.listObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200) {
                            var unverifiedEntitiesListPromises = [],
                                    unverifiedEntitiesList = {};
                            angular.forEach(response.Data, function (entityInfo, entityKey) {
                                (function (entity, key) {
                                    unverifiedEntitiesListPromises.push($scope.makeResolvedPromisefunction(entity).then(function (entityData) {
                                        if (!$scope.unverifiedEntitiesList[entityData.ModuleEntityID]) {
                                            entityData['showPopover'] = false;
//                      if ( entityData.Tags ) {
//                        entityData['ParseTags'] = entityData.Tags.split(',');
//                      }
                                            unverifiedEntitiesList['unverified_prefix_' + entityData.ModuleEntityID] = entityData;
                                        }
                                    }));
                                })(entityInfo, entityKey);
                            });
                            $q.all(unverifiedEntitiesListPromises).then(function (data) {
                                $scope.EntityTotalRecord = parseInt(response.TotalRecords);
                                if ($scope.pageNo > 1) {
//                  $scope.unverifiedEntitiesList = $scope.unverifiedEntitiesList.concat(response.Data);
                                    $scope.unverifiedEntitiesList = angular.extend({}, $scope.unverifiedEntitiesList, unverifiedEntitiesList);
                                } else {
                                    $scope.unverifiedEntitiesList = angular.copy(unverifiedEntitiesList);

                                }
                                $scope.unverifiedEntitiesListCount = Object.keys($scope.unverifiedEntitiesList).length;
                                $scope.pageNo++;
                                $scope.listObj.page_no = $scope.pageNo;
                            });
                        } else {
                            ShowErrorMsg(response.Message);
                        }
                        $scope.unverifiedEntitiesListLoader = false;

                    }, function () {
                        $scope.unverifiedEntitiesListLoader = false;
                    });
                }

            }

            $scope.removeAndUpdateEntityList = function (entityIndex) {
                if ($scope.unverifiedEntitiesList[entityIndex]) {
                    delete $scope.unverifiedEntitiesList[entityIndex];
                    $scope.unverifiedEntitiesListCount = Object.keys($scope.unverifiedEntitiesList).length;
                    $scope.EntityTotalRecord--;
                    if ($scope.unverifiedEntitiesListCount <= 22) {
                        $scope.listObj.page_no = 1; // To set it to pervious state
                        $scope.getUnverifiedEntities();
                    }
                }
            }

            $scope.updateEntity = function (ModuleID, ModuleEntityID, action, entityIndex) {
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

            $scope.updateEntityTags = function (entityData, entityIndex) {
                if (entityData && entityData.ModuleEntityID && entityData.ModuleID && !$scope.isUpdateEntityTagsProcessing) {
                    var requestObj = {}, url, msg;
                    if (entityData.Featured_TagID == 0) {
                        requestObj = {
                            "EntityID": entityData.ModuleEntityID,
                            "TagsList": [{"Name": "Feature"}],
                            "IsFrontEnd": "1",
                            "TagsIDs": []
                        };
                        switch (true) {
                            case (entityData.ModuleID == 3):
                                requestObj['EntityType'] = 'USER';
                                requestObj['TagType'] = 'USER';
                                break;
                            case (entityData.ModuleID == 1):
                                requestObj['EntityType'] = 'GROUP';
                                requestObj['TagType'] = 'GROUP';
                                break;
                            case (entityData.ModuleID == 18):
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
                        switch (true) {
                            case (entityData.ModuleID == 3):
                                requestObj['EntityType'] = 'USER';
                                break;
                            case (entityData.ModuleID == 1):
                                requestObj['EntityType'] = 'GROUP';
                                break;
                            case (entityData.ModuleID == 18):
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
                            if (entityData.Featured_TagID == 0) {
                                if (response.Data && response.Data[0].EntityTagID) {
                                    $scope.unverifiedEntitiesList[entityIndex].Featured_TagID = response.Data[0].EntityTagID;
                                }
                                if ((entityData.ModuleID == 1) || (entityData.ModuleID == 18)) {
                                    if ($scope.unverifiedEntitiesList[entityIndex].Tags) {
                                        $scope.unverifiedEntitiesList[entityIndex].Tags.push('Feature');
                                    } else {
                                        $scope.unverifiedEntitiesList[entityIndex]['ParseTags'] = ['Feature']
                                    }
                                }
                                msg = 'Mark as featured successfully.';

                            } else {
                                $scope.unverifiedEntitiesList[entityIndex].Featured_TagID = 0;
                                if (((entityData.ModuleID == 1) || (entityData.ModuleID == 18)) && $scope.unverifiedEntitiesList[entityIndex].Tags) {
                                    var index = $scope.unverifiedEntitiesList[entityIndex].Tags.indexOf('Feature');
                                    if (index > -1) {
                                        $scope.unverifiedEntitiesList[entityIndex].Tags.splice(index, 1);
                                    }
                                }
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

            $scope.makeResolvedPromisefunction = function (data) {
                var deferred = $q.defer();
                deferred.resolve(data);
                return deferred.promise;
            }

            $scope.parseTags = function (entityTags, entityIndex) {
                if (entityTags && (entityIndex || (entityIndex > -1))) {
                    $scope.unverifiedEntitiesList[entityIndex]['ParseTags'] = entityTags.split(',');
                }
            };



        }]);

})();
