!(function (app, angular) {
    angular.module('CreateGroupModule', [])
            .controller('CreateGroupCtrl', CreateGroupCtrl);

    CreateGroupCtrl.$inject = ['$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService', 'lazyLoadCS', '$sce'];


    function CreateGroupCtrl($rootScope, $scope, appInfo, $http, profileCover, WallService, lazyLoadCS, $sce)
    {
        $scope.showGroupPopup = false;
        $scope.setGroupPopup = function (val)
        {
            $scope.showGroupPopup = val;
        }

        $scope.GroupCatIDs = [];

        $scope.group_user_tags = [];
        $scope.group_admin_tags = [];
        $scope.EditIsPublic = 1;
        $scope.EditAllowedPostType = [];
        $scope.showIsPublic = true;
        $scope.showIsClose = true;
        $scope.showIsSecret = true;
        $scope.CreateEditGroup = function (Action, GroupGUID)
        {
            if (Action == 'createGroup')
            {
                // Set Pristine True
                $scope.formGroup.GroupName.$pristine = true;
                $scope.formGroup.CategoryIds.$pristine = true;
                $scope.formGroup.GroupDescription.$pristine = true;
            }

            $('#createGroup').modal('show');
            $scope.EditGroupName = '';
            $scope.EditGroupGUID = '';
            $scope.EditCreatedBy = '';
            $scope.EditGroupDescription = '';
            $scope.EditIsPublic = 1;
            $scope.EditCategory = '';
            $scope.FormName = '';
            $scope.FormButtonName = '';
            $scope.EditGroupType = '';
            $scope.showIsPublic = true;
            $scope.showIsClose = true;
            $scope.showIsSecret = true;

            $scope.tagsto2 = '';
            $scope.group_user_tags = [];
            $scope.tagsto1 = '';
            $scope.group_admin_tags = [];

            $('#noOfChargroup_description').text(400);
            $('.alert .alert-danger').css('display', 'none');
            $('#formGroup .text-field, #formGroup .text-field-select, #formGroup .textarea-field').removeClass('hasError');
            $('.error-block-overlay').text('');

            $(".alert-danger").css('display', 'none');

            $('#CategoryIds').val('').trigger("chosen:updated");

            $scope.cat_name = '';

            if (Action == 'EditGroup')
            {

                $scope.FormName = 'Update Group';
                $scope.FormButtonName = 'Update';

                var response = {};

                var reqData = {GroupGUID: GroupGUID};
                WallService.CallPostApi(appInfo.serviceUrl + 'group/details', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.GroupDetails = response.Data;

                        if ($scope.GroupDetails.IsPublic == 2) {
                            $scope.showIsPublic = false;
                            $scope.showIsClose = false;
                        }
                        if ($scope.GroupDetails.IsPublic == 0) {
                            $scope.showIsPublic = false;
                        }

                        $scope.EditGroupName = response.Data.GroupName;
                        $scope.EditGroupGUID = response.Data.GroupGUID;
                        $scope.EditGroupType = response.Data.Type;
                        $scope.EditCreatedBy = response.Data.CreatedBy;
                        $scope.EditGroupDescription = response.Data.GroupDescription;
                        $scope.EditIsPublic = response.Data.IsPublic;

                        $scope.tagsto2 = response.Data.Members;
                        $scope.group_user_tags = response.Data.Members;
                        $scope.tagsto1 = response.Data.Admins;
                        $scope.group_admin_tags = response.Data.Admins;
                        $scope.EditAllowedPostType = response.Data.AllowedPostType;
                        $scope.checkCount = response.Data.AllowedPostType.length;

                        $scope.editGroupPrivacyType = angular.copy($scope.EditIsPublic);

                        if (response.Data.Category.CategoryID != undefined && response.Data.Category.CategoryID != "")
                        {
                            $("#CategoryIds option:selected").val(response.Data.Category.CategoryID);
                            $scope.getSubCategories(response.Data.Category.CategoryID);

                        }


                        setTimeout(function () {

                            var CountChar = 400 - response.Data.GroupDescription.length;

                            if (CountChar < 0)
                                CountChar = 0;

                            $('#noOfChargroup_description').text(CountChar);

                            if (response.Data.Category.CategoryID != undefined && response.Data.Category.CategoryID != "")
                            {
                                $('#CategoryIds_chosen span').text(response.Data.Category.Name);
                                $scope.cat_name = response.Data.Category.Name;
                                if (!$scope.$$phase)
                                {
                                    $scope.$apply();
                                }
                            }

                            if (response.Data.Category.SubCategory != undefined && response.Data.Category.SubCategory != "")
                            {
                                $("#SubCategory option:selected").val(response.Data.Category.SubCategory.CategoryID);
                                $('#SubCategory_chosen span').text(response.Data.Category.SubCategory.Name);
                            }


                        }, 500);



                    }
                });

            }
        }

        $scope.checkAllowedType = function (value)
        {
            var r = false;
            angular.forEach($scope.EditAllowedPostType, function (val, key) {
                if (val.Value == value)
                {
                    r = true;
                }
            });
            return r;
        }
        $scope.catAdded = function (CatID) {

            $scope.GroupCatIDs.push(catId);

            //console.log($scope.GroupCatIDs);

        };

        $scope.SubCategory = "";

        $scope.FormSubmit = function ()
        {
            $scope.GroupCatIDs = new Array();
            var jsonData = {};
            var formData = $("#formGroup").serializeArray();
            $.each(formData, function () {
                if (jsonData[this.name]) {
                    if (!jsonData[this.name].push) {
                        jsonData[this.name] = [jsonData[this.name]];
                    }
                    jsonData[this.name].push(this.value || '');
                } else {
                    jsonData[this.name] = this.value || '';
                }

            });
            /*-----------Condition to set api url according to request type-----------*/
            var GroupTypeStatus = true;

            var show_alert = false;
            if (jsonData.GroupGUID)
            {
                if (jsonData['IsPublic'] != $scope.EditIsPublic)
                {
                    show_alert = true;
                }
                URL = 'group/update';
                if ($scope.GroupDetails.Type == 'INFORMAL')
                {
                    GroupTypeStatus = false;
                    if (jsonData.GroupName == '' && (jsonData.IsPublic != 2))
                    {
                        showResponseMessage('Can not change group status without converting into formal group.', 'alert-danger');
                        return;
                    }
                    if (jsonData.GroupName == '' || jsonData.GroupDescription == '')
                    {
                        showResponseMessage('Group Name & Description is required.', 'alert-danger');
                        return;
                    }
                }
            } else
            {
                show_alert = true;
                URL = 'group/create';
            }



            /*---validate only for create and update formal group--*/
            /*if (GroupTypeStatus)
             {
             var val = checkstatus('formGroup');
             if (val === false)
             return;
             }*/


            if (jsonData.CategoryIds != '' && jsonData.CategoryIds != undefined)
            {
                $scope.GroupCatIDs.push(jsonData.CategoryIds);
            }

            jsonData['CategoryIds'] = $scope.GroupCatIDs;


            if (jsonData['CategoryIds'] == '' && GroupTypeStatus == true)
            {
                $('#commonError').html('Please choose category');
                $('#commonError').parent('.alert').show().delay(2000).fadeOut();
                $('#formGroup .text-field').removeClass('hasError');
                $('.error-block-overlay').text('');
                return false;
            }

            $scope.SubCategoryIds = [];

            if (jsonData['SubCategory'] != "" && jsonData['SubCategory'] !== undefined)
            {
                $scope.SubCategoryIds.push(jsonData['SubCategory']);
            }

            jsonData['SubCategoryIds'] = $scope.SubCategoryIds;


            var AllowedGroupTypes = [];

            angular.forEach($('input[name="AllowedGroupTypes[]"]:checked'), function (val, key) {
                AllowedGroupTypes.push(val.value);
            });

            if (AllowedGroupTypes.length < 1)
            {
                showResponseMessage('Please choose atleast one group content type', 'alert-danger');
                return false;
            }
            jsonData['AllowedPostType'] = AllowedGroupTypes;

            delete jsonData['SubCategory'];
            delete jsonData['AllowedGroupTypes[]'];

            if (show_alert)
            {
                showConfirmBox("Wait a minute!", "Kindly set group privacy cautiously. Once created, group privacy can't be changed to make it more open. It can be made more private only.", function (e) {
                    if (e)
                    {
                        WallService.CallPostApi(appInfo.serviceUrl + URL, jsonData, function (successResp) {
                            var response = successResp.data;
                            $scope.response = response.ResponseCode;
                            $scope.message = response.Message;
                            //console.log(response);
                            var id = response.Data;
                            if (response.ResponseCode == '200')
                            {
                                console.log(response);
                                if (jsonData.GroupGUID)
                                {
                                    showResponseMessage(response.Message, 'alert-success');

                                } else
                                {
                                    showResponseMessage(response.Message, 'alert-success');
                                    setTimeout(function () {
                                        window.location.href = base_url + "group/" + response.Data.ProfileURL;
                                    }, 500);

                                }

                                if ($("#fromList").val() !== undefined)
                                {
                                    angular.element(document.getElementById('GroupPageCtrl')).scope().groupIManage('Manage', 'CreatedDate');
                                    angular.element(document.getElementById('GroupPageCtrl')).scope().groupIJoin('Join', 'CreatedDate');
                                } else
                                {

                                    setTimeout(function () {
                                        if (IsNewsFeed)
                                        {
                                            window.location.href = base_url + "group/" + response.Data.ProfileURL;
                                        }
                                    }, 500);
                                }

                                $('#createGroup').modal('toggle');


                                $scope.EditGroupName = '';
                                $scope.EditGroupGUID = '';
                                $scope.EditCreatedBy = '';
                                $scope.EditGroupDescription = '';
                                $scope.EditIsPublic = 1;
                                $scope.EditCategory = '';
                                $scope.FormName = '';
                                $scope.FormButtonName = '';


                            } else {
                                showResponseMessage(response.Message, 'alert-danger');
                                $('#formGroup .text-field').removeClass('hasError');
                                $('.error-block-overlay').text('');
                            }
                        }, function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    }
                });
            } else
            {
                WallService.CallPostApi(appInfo.serviceUrl + URL, jsonData, function (successResp) {
                    var response = successResp.data;
                    $scope.response = response.ResponseCode;
                    $scope.message = response.Message;
                    //console.log(response);
                    var id = response.Data;
                    if (response.ResponseCode == '200')
                    {
                        //console.log(response);
                        if (jsonData.GroupGUID)
                        {
                            showResponseMessage(response.Message, 'alert-success');

                        } else
                        {
                            showResponseMessage(response.Message, 'alert-success');
                            setTimeout(function () {
                                window.location.href = base_url + "group/" + response.Data.ProfileURL;
                            }, 500);

                        }

                        if ($("#fromList").val() !== undefined)
                        {
                            angular.element(document.getElementById('GroupPageCtrl')).scope().groupIManage('Manage', 'CreatedDate');
                            angular.element(document.getElementById('GroupPageCtrl')).scope().groupIJoin('Join', 'CreatedDate');
                        } else
                        {

                            setTimeout(function () {
                                if (IsNewsFeed)
                                {
                                    window.location.href = base_url + "group/" + response.Data.ProfileURL;
                                }
                            }, 500);
                        }

                        $('#createGroup').modal('toggle');


                        $scope.EditGroupName = '';
                        $scope.EditGroupGUID = '';
                        $scope.EditCreatedBy = '';
                        $scope.EditGroupDescription = '';
                        $scope.EditIsPublic = 1;
                        $scope.EditCategory = '';
                        $scope.FormName = '';
                        $scope.FormButtonName = '';


                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                        $('#formGroup .text-field').removeClass('hasError');
                        $('.error-block-overlay').text('');
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        }

        $scope.checkCount = -1;
        $scope.ContentTypeCheckCount = function ()
        {
            $scope.checkCount = 0;
            $('input[name="AllowedGroupTypes[]"]').each(function (e) {
                if ($('input[name="AllowedGroupTypes[]"]:eq(' + e + ')').is(':checked'))
                {
                    $scope.checkCount++;
                }
            });
        }

        $scope.ContentTypes = [];

        $scope.GetAllowedGroupTypes = function () {
            var req = {};
            if ($scope.LoginSessionKey) {
                WallService.CallPostApi(appInfo.serviceUrl + 'group/get_allowed_group_types', req, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.ContentTypes = response.Data;
                    }

                }, function (error) {

                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        }


        $scope.GroupCategories = function () {
            var jsonData = {};
            jsonData['ModuleID'] = 1;
            WallService.CallPostApi(appInfo.serviceUrl + 'category/get_categories', jsonData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;


                var id = response.Data;
                if (response.ResponseCode == '200')
                {

                    $scope.GroupCategoriesData = response.Data;
                } else
                {
                    $('#commonError').html(response.Message)
                    $('#commonError').parent('.alert').show();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.SubCategories = [];

        $scope.getSubCategories = function (ParentCatID) {
            var jsonData = {};
            jsonData['ModuleID'] = 1;
            jsonData['categoryLevelID'] = ParentCatID;
            WallService.CallPostApi(appInfo.serviceUrl + 'category/get_categories', jsonData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;

                if (response.ResponseCode == '200')
                {
                    $scope.SubCategories = response.Data;
                } else
                {
                    $('#commonError').html(response.Message)
                    $('#commonError').parent('.alert').show();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.GroupFormDetail = function ()
        {
            $scope.grpid = "";
            $scope.userloginid = '';
            if ($('#module_entity_id').val())
            {
                $scope.grpid = $('#module_entity_id').val();
            }


            var reqData = {GroupID: $scope.grpid};
            WallService.CallPostApi(appInfo.serviceUrl + 'group/groupDetail', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.groupname = response.Data.GroupName;
                    $('.semi-bold').val($scope.groupname);
                    $scope.GroupID = response.Data.GroupID;
                    $scope.CreatedBy = response.Data.CreatedBy;
                    $scope.GroupDescription = response.Data.GroupDescription;
                    $scope.IsPublic = response.Data.IsPublic;
                    $scope.image = response.Data.imagepath;
                    $scope.imagepath = response.Data.GroupImage;

                    $scope.editGroupPrivacyType = angular.copy($scope.IsPublic);

                    if (response.Data.imagepath == AssetBaseUrl + 'img/profiles/user_default.jpg') {
                        $('#add_group_photo').show();
                        $('#group_photo').hide();
                        $('.del-ico').hide();
                    } else {
                        $('#group_photo').show();
                        $('.del-ico').show();
                        $('#add_group_photo').hide();
                    }
                } else {
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.updateForm = function ()
        {
            var val = checkstatus('formGroup');
            if (val === false)
                return;
            var jsonData = {};
            var formData = $("#formGroup").serializeArray();
            $.each(formData, function () {
                if (jsonData[this.name]) {
                    if (!jsonData[this.name].push) {
                        jsonData[this.name] = [jsonData[this.name]];
                    }
                    jsonData[this.name].push(this.value || '');
                } else {
                    jsonData[this.name] = this.value || '';
                }

            });

            jsonData['GroupMedia'] = jsonData['group_media'];
            WallService.CallPostApi(appInfo.serviceUrl + 'group/create_update', jsonData, function (successResp) {
                var response = successResp.data;
                $scope.message = response.Message;

                //$('#alert_message').trigger('click');
                if (response.ResponseCode == '200')
                {
                    alertify.success(response.Message);
                    var id = response.Data;
                    setTimeout(function () {
                        window.location.href = base_url + "group/" + id;
                    }, 2000);

                } else {
                    $('#commonError').html(response.Message);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.$on('onCreateGroup', function (event, data) {
            var type = 'createGroup';
            var guid = '';
            if (typeof data.Action !== 'undefined')
            {
                type = data.Action;
            }
            if (typeof data.GroupGUID !== 'undefined')
            {
                guid = data.GroupGUID;
            }
            $scope.CreateEditGroup(type, guid);
        });
    }


})(app, angular);