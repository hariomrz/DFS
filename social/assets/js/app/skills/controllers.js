//Wall controller 
angular.module('App').controller('SkillsCtrl', ['$http', 'GlobalService', '$scope', '$rootScope', 'Settings', '$sce', '$timeout', 'setFormatDate', '$interval', '$compile', 'socket', '$q', 'DragDropHandler', '$q', 'appInfo', 'WallService', function ($http, GlobalService, $scope, $rootScope, Settings, $sce, $timeout, setFormatDate, $interval, $compile, socket, $qm, DragDropHandler, $q, appInfo, WallService) {
        var LoginType = '';
        $scope.FromModuleID = '';
        $scope.FromModuleEntityGUID = LoggedInUserGUID;
        $scope.ToModuleID = $('#module_id').val();
        $scope.ToModuleEntityGUID = $('#module_entity_guid').val();
        $scope.SkillPageNo = 1;
        $scope.SkillPageSize = 10;
        $scope.SkillTotalRecords = 0;
        $scope.editMode = false;
        $scope.endorseSearchUser = '';
        $scope.Endorsement = [];
        $scope.Endorsement = [];
        $scope.PageType = '';
        $scope.busy = false;
        $scope.stopExecution = 0;
        $scope.EndorseConnectionPageNo = 1;
        $scope.EndorseConnectionPageSize = 10;
        $scope.EndorseConnectionTemp = [];
        $scope.EndorseConnection = [];
        $scope.endorseModuleID = '';
        $scope.endorseModuleEntityGUID = '';
        $scope.endorseConnectionStopeExecution = 0;
        $scope.EndorseConnectionSkills = [];
        $scope.SkillData = [];
        $scope.ManageSkillSaveBtn = true;
        $scope.LoaderBtn = false;
        $scope.ShowEndorseBox = true;
        $scope.ShowEndorseBoxOnWall = true;
        $scope.getModuleID = function ()
        {
            $scope.FromModuleID = 3;
            switch (LoginType)
            {
                case 'page':
                    $scope.FromModuleID = 18;
                    break;
            }
        }

        $scope.getModuleID();
        $scope.endorsement_suggestion = []
        $scope.getEndorsementSuggestion = function ()
        {
            var reqData = {ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, FromModuleID: $scope.FromModuleID, FromModuleEntityGUID: $scope.FromModuleEntityGUID, Limit: 5};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/endorse_suggestion', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.endorsement_suggestion = response.Data;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.InitUserSkillAutocomplete = function (query)
        {
            var result = [];
            var deferred = $q.defer();
            return $http.get(base_url + 'api/skills/skills_list?Keyword=' + query + '&ModuleID=' + $scope.FromModuleID + '&ModuleEntityGUID=' + $scope.FromModuleEntityGUID).then(function (response) {
                if (response.data.length > 0) {
                    $.each(response.data, function (key, val) {
                        var IsPresent = jQuery.inArray(this.SkillID, $scope.AddEditSkillIndex);
                        if (IsPresent == -1) {
                            result.push(this);
                        }
                    });
                    deferred.resolve(result);
                    return deferred.promise;
                } else {
                    deferred.resolve(result);
                    return deferred.promise;
                }
            });
        }

        $scope.EndorseSkillAutocomplete = function (query)
        {
            var result = [];
            var deferred = $q.defer();
            return $http.get(base_url + 'api/skills/skills_list_for_endorsement?Search=' + query + '&ModuleID=' + $scope.ToModuleID + '&ModuleEntityGUID=' + $scope.ToModuleEntityGUID + '&VisitorModuleEntityGUID=' + $scope.FromModuleEntityGUID + '&VisitorModuleID=' + $scope.FromModuleID).then(function (response) {
                if (response.data.length > 0) {
                    var Data = response.data;
                    $.each(Data, function (key, val) {
                        var newval = true;
                        $.grep($scope.EndorseSkills, function (e) {
                            if (e.SkillID == Data[key].SkillID)
                            {
                                newval = false;
                            }
                        });
                        if (newval)
                        {
                            result.push(Data[key]);
                        }
                    });
                    deferred.resolve(result);
                    return deferred.promise;
                } else {
                    deferred.resolve(result);
                    return deferred.promise;
                }
            });
        }


        $scope.EndorseConnectionAutocomplete = function (query)
        {
            var result = [];
            var deferred = $q.defer();
            return $http.get(base_url + 'api/skills/skills_list_for_endorsement?Search=' + query + '&ModuleID=' + $scope.ToModuleID + '&ModuleEntityGUID=' + $scope.ToModuleEntityGUID + '&VisitorModuleEntityGUID=' + $scope.FromModuleEntityGUID + '&VisitorModuleID=' + $scope.FromModuleID).then(function (response) {
                if (response.data.length > 0) {
                    var Data = response.data;
                    $.each(Data, function (key, val) {
                        var newval = true;
                        $.grep($scope.SelectedEndorseConnection.EndorseSuggestion, function (e) {
                            if (e.SkillID == Data[key].SkillID)
                            {
                                newval = false;
                            }
                        });
                        if (newval)
                        {
                            result.push(Data[key]);
                        }
                    });
                    deferred.resolve(result);
                    return deferred.promise;
                } else {
                    deferred.resolve(result);
                    return deferred.promise;
                }
            });
        }

        $scope.SkillDataForDisplay = [];
        $scope.AddEditSkillIndex = [];
        $scope.SkillDataForDisplayCount = 0;
        $scope.showskillform = 0;
        $scope.TopSkillData = [];
        $scope.UserSkillData = [];
        $scope.PendingSkillData = [];
        $scope.PendingTotalRecord = 0;
        $scope.IgnoreSkillGUIDs = [];
        $scope.TempPendingArr = [];
        $scope.TempCount = 0;
        $scope.TempNameArr = [];
        $scope.EndorseSkills = [];
        $scope.getTempEndorseSkills = [];
        $scope.IsTopSkillCanEndorse = '';
        $scope.IsOtherSkillCanEndorse = '';
        $scope.showskillform = 0;
        $scope.showSkilldisplaydiv = 0;
        $scope.EntitySkillID = '';
        $scope.SkillName = '';
        $scope.EndorsementUserLists = [];
        $scope.EndorsementPageNo = 1;
        $scope.EndorsementPageSize = 20;
        $scope.IsEndorsementLoadMore = 0;
        
        $scope.getUserTopSkills = function (init) {
            if (init == 'init') {
                $scope.TopSkillData = [];
                $scope.IgnoreSkillGUIDs = [];
            }

            $('body').addClass('loading');
            $scope.getModuleID();
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, PageNo: 1, PageSize: 10, Filter: 1};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        val['StatusID'] = 2;
                        $scope.IgnoreSkillGUIDs.push(val.EntitySkillGUID);
                        $scope.TopSkillData.push(val);
                    });
                    $scope.getUserOtherSkills('init');
                    $scope.IsTopSkillCanEndorse = response.CanEndorse;
                    // $scope.tooltip();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.LoadMoreSkill = function () {
            $scope.SkillPageNo = parseInt($scope.SkillPageNo) + 1;
            $scope.getUserSkills();
        }
        $scope.getUserSkills = function (init) {
            if (init == 'init') {
                $scope.SkillPageNo = 1;
            }
            $scope.getModuleID();
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, PageNo: $scope.SkillPageNo, PageSize: $scope.SkillPageSize, Filter: 0, IgnoreEntitySkillGUID: $scope.IgnoreSkillGUIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/details', reqData, function (successResp) {
                var response = successResp.data;
                if (init == 'init') {
                    $scope.UserSkillData = [];
                }
                if (response.ResponseCode == 200)
                {
                    $scope.SkillTotalRecords = response.TotalRecords;
                    angular.forEach(response.Data, function (val, key) {
                        val['StatusID'] = 2;
                        $scope.UserSkillData.push(val);
                    });

                    $scope.IsOtherSkillCanEndorse = response.CanEndorse;
                    //$scope.tooltip();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $('body').removeClass('loading');
        }

        $scope.getUserPendingSkills = function (init) {
            if (init == 'init') {
                $scope.PendingSkillData = [];
            }
            $scope.getModuleID();
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, PageNo: 1, PageSize: 10, Filter: 2};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        val['StatusID'] = 2;
                        $scope.PendingSkillData.push(val);
                        // pemding section text
                        angular.forEach(val.Endorsements, function (val1, key1) {
                            var IsUserPresent = jQuery.inArray(val1.Name, $scope.TempNameArr);
                            if (IsUserPresent == -1) {
                                $scope.TempCount++;
                                $scope.TempPendingArr.push(val1);
                                $scope.TempNameArr.push(val1.Name);
                            }
                        });
                    });
                    $scope.TempNameArr = [];
                    $scope.PendingTotalRecord = response.TotalRecords;
                    //$scope.tooltip();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.DeleteSkill = function (SkillId, Type)
        {
            var SkillIDs = [];
            SkillIDs.push({'ID': SkillId});
            var reqData = {ModuleID: $scope.FromModuleID, ModuleEntityGUID: $scope.FromModuleEntityGUID, Skills: SkillIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/delete_skills', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.$parent.fetchDetails('load');
                    var pageCTRL = $scope.$parent.pageCTRL();
                    if (typeof pageCTRL != 'undefined') {
                        pageCTRL.GetPageDetails(pageCTRL.pageDetails.PageGUID);
                    }
// old code
                    /*if(Type == 'OtherSkill'){
                     $scope.getUserOtherSkills('init');
                     }else if(Type == 'TopSkill'){
                     $scope.getUserTopSkills('init');
                     }else{
                     $scope.getUserPendingSkills('init');
                     }*/

                    if (Type == 'OtherSkill') {
                        var commonObject = $scope.UserSkillData
                    } else if (Type == 'TopSkill') {
                        var commonObject = $scope.TopSkillData
                    } else {
                        var commonObject = $scope.PendingSkillData
                    }

                    angular.forEach(commonObject, function (val, key) {
                        if (SkillId == val.SkillID) {
                            commonObject.splice(key, 1);
                        }
                    });
                    if ($scope.TopSkillData.length == 0 && $scope.UserSkillData.length == 0) {
                        $scope.showskillform = 0;
                        $scope.showSkilldisplaydiv = 0;
                    }

                    if ($scope.PendingSkillData.length == 0) {
                        $scope.PendingTotalRecord = 0;
                        $scope.addSkillsItem = '';
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.AddEndorsement = function (SkillId, Type)
        {
            var SkillIDs = [];
            SkillIDs.push({'ID': SkillId});
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, Skills: SkillIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/save_endorsement', reqData, function (successResp) {
                var response = successResp.data;            
                if (response.ResponseCode == 200)
                {
                    $scope.getUserSkills('init');
                    $scope.addExperienceItem = '';
                    $('.tooltip').remove();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.DeleteEndorsement = function (SkillId, Type)
        {
            var reqData = {ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, SkillID: SkillId};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/delete_endorsement', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.getUserSkills('init');
                    $scope.addExperienceItem = '';
                    //$scope.tooltip();
                    $('.tooltip').remove();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
        /* $scope.tooltip = function ()
         {
         setTimeout(function () {
         $('[data-toggle="tooltip"]').tooltip({container: "body"});
         }, 500)
         }*/
        $scope.AddSkillToProfile = function () {
            var SkillIDs = [];
            angular.forEach($scope.PendingSkillData, function (val, key) {
                SkillIDs.push({'SkillID': val.SkillID});
            });
            $scope.LoaderBtn = true;
            var reqData = {ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, Skills: SkillIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/approve_pending_skills', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.PendingSkillData = [];
                    $scope.PendingTotalRecord = 0;
                    $scope.getUserSkills('init');
                    $scope.LoaderBtn = false;

                } else {
                    $scope.LoaderBtn = false;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.toggleuserskillname = function ()
        {
            $.each($scope.SkillData, function (key, val) {
                if (typeof this.SkillID != 'undefined' && this.SkillID != '') {
                    this.SkillID = this.SkillID;
                } else {
                    this.SkillID = '';
                    this.SkillName = this.Name;
                }

                this.StatusID = 2;
                $scope.SkillDataForDisplay.push(this);
                $scope.AddEditSkillIndex.push(this.SkillID);
                $scope.SkillDataForDisplayCount++;
            })
            $scope.SkillData = [];
            $scope.showSkilldisplaydiv = 1;
        }

        $scope.removeskill = function ($index, SkillID)
        {
            var currentPosition = jQuery.inArray(SkillID, $scope.AddEditSkillIndex);
            if (currentPosition > -1) {
                $scope.SkillDataForDisplay.splice($index, 1);
                $scope.AddEditSkillIndex.splice(currentPosition, 1);
            } else {
                $scope.SkillDataForDisplay[$index].StatusID = 3;
            }
            $scope.SkillDataForDisplayCount--;
        }

        $scope.toggleAddedSkills = function () {
            $.each($scope.SkillDataForDisplay, function (key, val) {
                var currentPosition = jQuery.inArray(this.SkillID, $scope.AddEditSkillIndex);
                if (currentPosition > -1) {
                    $scope.SkillDataForDisplay.splice(key, 1);
                }
            })
            $scope.SkillDataForDisplayCount = 0;
            $scope.AddEditSkillIndex = [];
            $scope.showskillform = 0;
        }

        $scope.save_skills = function ()
        {
            $('#SaveSkill').attr('disabled', 'disabled');
            var SkillIDs = [];
            if ($scope.SkillData.length > 0) {
                angular.forEach($scope.SkillData, function (val, key) {
                    SkillIDs.push({'ID': val.SkillID, 'Name': val.Name});
                });
            }
            var reqData = {Skills: SkillIDs, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/save', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.endorsement_suggestion = response.Data;
                    showResponseMessage('Skills has been saved successfully.', 'alert-success');
                    $scope.SkillDataForDisplayCount = 0;
                    $scope.SkillData = [];
                    $scope.AddEditSkillIndex = [];
                    $scope.SkillDataForDisplay = [];
                    $scope.showskillform = 0;
                    $scope.getUserSkills('init');
                    $('#SaveSkill').removeAttr('disabled');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.displayuserSkillData = function (UserSkills)
        {
            var errClass = $('#SkillName').closest('[data-error]').attr('data-danger');
            $('#SkillName').closest('[data-error]').removeClass(errClass);
            $scope.showSkilldisplaydiv = 1;
            $scope.showskillform = 1;
            $scope.skilleditvar = 1;
        }

        $scope.toggleSkills = function ()
        {
            if ($scope.showskillform == 0)
            {
                $scope.showskillform = 1;
                $scope.showSkilldisplaydiv = 1;
            } else {
                $scope.showskillform = 0;
                $scope.showSkilldisplaydiv = 0;
                $scope.SkillDataForDisplay = [];
                $scope.SkillDataForDisplayCount = 0;
            }
        }

        $scope.EndorsementPopup = function (EntitySkillID, SkillName, init) {
            $('#endorsedList').modal('show');
            $scope.endorseSearchUser = '';
            $scope.EntitySkillID = EntitySkillID;
            $scope.SkillName = SkillName;
            $scope.EndorsementSkillName = SkillName;
            $scope.EndorsementList(init);
        }
        $scope.EndorsementList = function (init) {
            if (init == 'init') {
                $scope.EndorsementUserLists = [];
                $scope.EndorsementPageNo = 1;
                $scope.EndorsementPageSize = 20;
                $('body').addClass('loading');
            }

            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, EntitySkillID: $scope.EntitySkillID, PageNo: $scope.EndorsementPageNo, PageSize: $scope.EndorsementPageSize, keyword: $scope.endorseSearchUser};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/endorsement_list', reqData, function (successResp) {
                var response = successResp.data;            
                if (response.ResponseCode == 200)
                {
                    $scope.EndorsementCount = response.TotalEndorsement;
                    angular.forEach(response.Data, function (val, key) {
                        $scope.EndorsementUserLists.push(val);
                    });
                    $scope.IsEndorsementLoadMore = 0;
                    if ($scope.EndorsementUserLists.length < $scope.EndorsementCount) {
                        $scope.IsEndorsementLoadMore = 1;
                        // $scope.ScrollEndrosementList();
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.ScrollEndrosementList = function () {
            $scope.EndorsementPageNo++;
            $scope.IsEndorsementLoadMore = 0;
            $scope.EndorsementList('');
        }

        $scope.getEndorseSkills = function (init) {
            if (init == 'init') {
                $scope.EndorseSkills = [];
            }
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, PageNo: 1, PageSize: 5};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/endorse_suggestion', reqData, function (successResp) {
                    var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        $scope.EndorseSkills.push(val);
                    });
                    $scope.IsCanEndroseSuggestion = response.CanEndorse;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.assignTempEndoreValue = function () {
            $scope.getTempEndorseSkills = angular.copy($scope.EndorseSkills);
        }

        $scope.SaveSuggestionEndorse = function () {
            var SkillIDs = [];
            if ($scope.getTempEndorseSkills.length > 0) {
                showResponseMessage('Please add endorse skill', 'alert-danger');
                return false;
            }
            if ($scope.EndorseSkills.length > 0) {
                angular.forEach($scope.EndorseSkills, function (val, key) {
                    SkillIDs.push({'ID': val.SkillID, 'Name': val.Name});
                });
                $('body').addClass('loading');
                $scope.LoaderBtn = true;
                var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, Skills: SkillIDs};
                WallService.CallPostApi(appInfo.serviceUrl + 'skills/save_endorsement', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        showResponseMessage(response.Message, 'alert-success');
                        $('body').removeClass('loading');
                        $scope.EndorseSkills = [];
                        $scope.getTempEndorseSkills = [];
                        $scope.getUserSkills('init');
                        $scope.getEndorseSkills('init');
                        $scope.addExperienceItem = '';
                        $scope.LoaderBtn = false;
                    } else {
                        $scope.LoaderBtn = false;
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            } else {
                showResponseMessage('Please select atleast one skill.', 'alert-danger');
            }
        }

        $scope.SaveEndorseConnectionSkill = function () {
            var SkillIDs = [];

            if ($scope.SelectedEndorseConnection.EndorseSuggestion.length > 0) {
                angular.forEach($scope.SelectedEndorseConnection.EndorseSuggestion, function (val, key) {
                    SkillIDs.push({'ID': val.SkillID, 'Name': val.Name});
                });
                $('body').addClass('loading');
                var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.SelectedEndorseConnection.ModuleID, ModuleEntityGUID: $scope.SelectedEndorseConnection.ModuleEntityGUID, Skills: SkillIDs};
                WallService.CallPostApi(appInfo.serviceUrl + 'skills/save_endorsement', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {

                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            } else {
                showResponseMessage('Please select atleast one skill.', 'alert-danger');
            }
        }

        $scope.editSkillBox = function () {
            $scope.ManageSkillSaveBtn = true;
            if ($scope.editMode) {
                $scope.editMode = false;
                $scope.SkillPageNo = 1;
                $scope.SkillPageSize = 20;
                $scope.getUserSkills('init');
            } else {
                $scope.SkillPageSize = 0;
                $scope.editMode = true;
                $scope.getUserSkills('init');
            }
        }

        $scope.RemoveUserSkill = function (data) {
            $scope.ManageSkillSaveBtn = false;
            data.StatusID = 3;
            return data;
        }
        $scope.SaveManageSkill = function () {
            $('#SaveManageSkill').attr('disabled', 'disabled');
            var SkillIDs = [];
            if ($scope.UserSkillData.length > 0) {
                angular.forEach($scope.UserSkillData, function (val, key) {
                    SkillIDs.push({'ID': val.SkillID, 'Name': val.Name, 'StatusID': val.StatusID, 'EntitySkillID': val.EntitySkillID});
                });
            }
            var reqData = {Skills: SkillIDs, ModuleID: $scope.FromModuleID, ModuleEntityGUID: $scope.FromModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/manage_save', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Skills has been saved successfully.', 'alert-success');
                    $('#SaveManageSkill').removeAttr('disabled');
                    $scope.editSkillBox();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });

        }

        $scope.CancelPendingSkill = function (EntitySkillGUID)
        {
            var EntitySkillGUIDArray = [];
            if (EntitySkillGUID == 'All') {
                $.each($scope.PendingSkillData, function () {
                    EntitySkillGUIDArray.push(this.EntitySkillGUID);
                })
            }
            else {
                EntitySkillGUIDArray.push(EntitySkillGUID);
            }
            $scope.DeletePendingSkill(EntitySkillGUIDArray);
        }
        $scope.DeletePendingSkill = function (EntitySkillGUIDArray)
        {
            var reqData = {EntitySkillGUIDs: EntitySkillGUIDArray};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/delete_pending_skill', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.getUserPendingSkills('init');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
        $scope.add_endorse_skill = function ()
        {
            $.each($scope.getTempEndorseSkills, function () {
                if (!this.hasOwnProperty("SkillImageName")) {
                    this.SkillImageName = '';
                }
                if (!this.hasOwnProperty("CategoryImageName")) {
                    this.CategoryImageName = '';
                }
                if (!this.hasOwnProperty("CategoryName")) {
                    this.CategoryName = '';
                }
                if (!this.hasOwnProperty("SubCategoryName")) {
                    this.SubCategoryName = '';
                }

                $scope.EndorseSkills.push(this);
            })
            $scope.getTempEndorseSkills = [];
        }
        $scope.add_endorse_connection_skill = function ()
        {
            $.each($scope.EndorseConnectionSkills, function () {
                if (!this.hasOwnProperty("SkillImageName")) {
                    this.SkillImageName = '';
                }
                if (!this.hasOwnProperty("CategoryImageName")) {
                    this.CategoryImageName = '';
                }
                if (!this.hasOwnProperty("CategoryName")) {
                    this.CategoryName = '';
                }
                if (!this.hasOwnProperty("SubCategoryName")) {
                    this.SubCategoryName = '';
                }

                $scope.SelectedEndorseConnection.EndorseSuggestion.push(this);
            })
            $scope.EndorseConnectionSkills = [];
        }

        $scope.RemoveEndorseSkill = function ($index)
        {
            $scope.EndorseSkills.splice($index, 1);
        }
        $scope.CancelEndorseSkill = function ()
        {
            $scope.ShowEndorseBox = false;
            $scope.EndorseSkills = [];
        }
        timeVar = '';
        $scope.$watch('endorseSearchUser', function (newVal, oldVal) {
            if (newVal != oldVal) {
                clearTimeout(timeVar);
                timeVar = setTimeout(function () {
                    $scope.EndorsementList('init');
                }, 500)
            }
        })

        $scope.getEndorsement = function (Stage)
        {
            if (Stage == 'init') {
                $scope.EndorsementPageNo = 1;
                $scope.EndorsementPageSize = 20;
                $scope.Endorsement = [];
            }
            var reqData = {VisitorModuleID: $scope.FromModuleID
                , VisitorModuleEntityGUID: $scope.FromModuleEntityGUID
                , PageNo: $scope.EndorsementPageNo
                , PageSize: $scope.EndorsementPageSize
                , ModuleID: $scope.ToModuleID
                , ModuleEntityGUID: $scope.ToModuleEntityGUID
                , EndorsmentEntityGUID: $('#EndorsmentEntityGUID').val()
            };
            if ($scope.stopExecution == 0) {
                if ($scope.busy)
                    return;
                $scope.busy = true;
                WallService.CallPostApi(appInfo.serviceUrl + 'skills/get_endorsement', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        $.each(response.Data, function () {
                            $scope.Endorsement.push(this);
                        })

                        if (parseInt(response.TotalRecords) == parseInt($scope.Endorsement.length))
                        {
                            $scope.stopExecution = 1;
                        }
                        else {
                            $scope.EndorsementPageNo = $scope.EndorsementPageNo + 1;
                        }
                        $scope.busy = false;
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        }

        $scope.EndorseUserPopup = function (Stage, endorseModuleID, endorseModuleEntityGUID)
        {
            $scope.EndorseConnectionSkills=[];
            $scope.endorseModuleID = endorseModuleID;
            $scope.endorseModuleEntityGUID = endorseModuleEntityGUID;
            if (Stage == 'init') {               
                $scope.EndorseConnectionPageNo = 1;
                $scope.EndorseConnectionTemp = [];
                $scope.EndorseConnection = [];
                $scope.endorseConnectionStopeExecution = 0;
            }
            $scope.getModuleID();
            if ($scope.endorseConnectionStopeExecution)
                return false;
            var reqData = {ModuleID: $scope.FromModuleID
                , ModuleEntityGUID: $scope.FromModuleEntityGUID
                , EndorseModuleID: $scope.endorseModuleID
                , endorseModuleEntityGUID: $scope.endorseModuleEntityGUID
                , PageNo: $scope.EndorseConnectionPageNo
                , PageSize: $scope.EndorseConnectionPageSize
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/endorse_connection', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.EndorseConnectionTemp = response.Data;
                    $('#endorsedTheme').modal('show');
                    $scope.EndorseConnectionTemp.map(function (repo) {
                        repo.IsSelecte = false;
                    });
                    if ($scope.EndorseConnectionTemp.length <= 0 || $scope.EndorseConnectionTemp.length < $scope.EndorseConnectionPageSize) {
                        $scope.endorseConnectionStopeExecution = 1;
                    }
                    if ($scope.EndorseConnectionPageNo == 1) {
                        $scope.EndorseConnection = $scope.EndorseConnectionTemp;
                        if ($scope.EndorseConnection.length > 0) {
                            $scope.SelectedEndorseConnection = $scope.EndorseConnection[0];
                            $scope.EndorseConnection[0].IsSelecte = true;
                        }
                        $scope.EndorseConnectionTemp = [];

                    } else {
                        $scope.SetEndorseTempData()
                    }
                    $scope.EndorseConnectionPageNo = $scope.EndorseConnectionPageNo + 1;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
        $scope.SaveEndorseConnection = function () {

            if ($scope.EndorseConnectionSkills.length > 0) {
                showResponseMessage('Please add skill.', 'alert-danger');
                return false;
            }
            if ($scope.SelectedEndorseConnection.EndorseSuggestion.length <= 0) {
                return false;
            }
            $scope.EndorseConnection = $.grep($scope.EndorseConnection, function (e) {
                return  e.IsSelecte != true;
            });
            $scope.SaveEndorseConnectionSkill();
            $scope.SelectedEndorseConnection = $scope.EndorseConnection[0];
            $scope.EndorseConnection[0].IsSelecte = true;
            $scope.SetEndorseTempData();
        }
        $scope.RemoveConnectionSkill = function ($index) {
            $scope.SelectedEndorseConnection.EndorseSuggestion.splice($index, 1);
        }
        $scope.SelectConnectionUser = function (data) {
            $scope.EndorseConnectionSkills = [];
            $scope.SelectedEndorseConnection = data;
            $scope.EndorseConnection.map(function (repo) {
                repo.IsSelecte = false;
            });
            data.IsSelecte = true;
        }
        $scope.SetEndorseTempData = function () {
            if ($scope.EndorseConnectionTemp.length > 0) {
                $scope.EndorseConnection.push($scope.EndorseConnectionTemp[0]);
                $scope.EndorseConnectionTemp.splice(0, 1);
            } else {
                $scope.EndorseUserPopup('', $scope.endorseModuleID, $scope.endorseModuleEntityGUID);
            }
        }
        $(document).ready(function () {
            $(window).scroll(function () {
                var pScroll = $(window).scrollTop();
                var pageBottomScroll1 = parseInt($(document).height()) - parseInt($(window).height()) - 350;
                if (pScroll >= pageBottomScroll1) {
                    setTimeout(function () {
                        if (pScroll >= pageBottomScroll1 && !$scope.busy) {
                            if ($scope.IsReminder == 1 && $scope.trr == $scope.activityData.length) {
                                return;
                            }
                            $scope.getEndorsement();
                        }
                    }, 200);
                }
            });
        });
    }]);