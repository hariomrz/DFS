app.controller('ForumCtrl', ['DragDropHandler', '$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService','$window', 'tagCloudSrvc', 
    
function (DragDropHandler, $rootScope, $scope, appInfo, $http, profileCover, WallService, $window, tagCloudSrvc)
{
    
    tagCloudSrvc.extendScope($scope);
    
    $scope.ImageServerPath = image_server_path;
    $scope.BaseUrl = base_url;
    $scope.forums = [];
    $scope.forums_reorder = [];
    $scope.forum_categories = [];
    $scope.featured_categories = [];
    $scope.current_forum_id = 0;
    $scope.SelectedForumCategoryVisibilityID = [];

    $scope.redirectToLink = function(link)
    {
        window.top.location = link;
    }

    $scope.redirectToBaseLink = function(link)
    {
        window.top.location = link;
    }
    
    $scope.setFilterFixed = function(val)
    {
        $scope.filterFixed = val;
    }

    $scope.slice_string = function(val,count)
    {
      return  smart_sub_str(count, val, true); //smart_substr(count,val);
    }

    $scope.CreateUpdateForum = function()
    {
        var reqData = $scope.CreateUpdate;
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/create', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                if(reqData.ForumID)
                {
                    $scope.getForums();
                }
                else
                {
                    $scope.get_forum_names();
                    $scope.forums.push(response.Data[0]);
                    $scope.forums_reorder.push(response.Data[0]);
                    $("html, body").animate({ scrollTop: $(document).height() }, 1000);
                }
                $('#addForum').modal('toggle');
                showResponseMessage(response.Message,'alert-success');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.reset_media = function()
    {
        $('#CatMediaGUID').val('');
        $('#forumcatprofilepic').attr('src',image_server_path+'upload/profile/220x220/category_default.png');
    }

    $scope.CreateUpdateCategory = function()
    {
        var reqData = $scope.CreateUpdateCat;
        reqData['MediaGUID'] = $('#CatMediaGUID').val();
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/create_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                if($('#IsWall').length>0)
                {
                    $scope.get_category_details();
                }
                else
                {
                    $scope.getForums();
                }
                $('#addCategory').modal('toggle');
                showResponseMessage(response.Message,'alert-success');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.CreateUpdateSubCategory = function(forum_id)
    {
        var reqData = $scope.SubCat;
        reqData['ForumID'] = $scope.current_forum_id;
        reqData['MediaGUID'] = $('#SubCatMediaGUID').val();
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/create_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                if($('#IsWall').length>0)
                {
                    $scope.get_category_details();
                }
                else
                {
                    $scope.getForums();
                }
                showResponseMessage(response.Message,'alert-success');
                $('#addSubCategory').modal('toggle');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.set_current_forum_id = function(forum_id)
    {
        $scope.current_forum_id = forum_id;
    }

    $scope.set_current_forum_guid = function(forum_guid,stop)
    {
        if(!stop)
        {
            $('#module_entity_guid').val(forum_guid);
        }
    }

    $scope.initChoosen = function()
    {
        $("#CategorySelect").trigger("chosen:updated");
    }

    $scope.forum_names = [];
    $scope.get_forum_names = function()
    {
        var reqData = {};
        if($scope.LoginSessionKey)
        {
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/forum_name', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data,function(val,key){
                        $scope.forum_names[val.ForumID] = val.Name;
                    });
                }
                else
                {
                    showResponseMessage(response.Message,'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }
    }
    if($('#breadcrumb_forum_all_types').length == 0) {
        $scope.get_forum_names();
    }
    

    $scope.reqMembers = {};
    $scope.reqMembers.PageNo = 1;
    $scope.reqFriendMembers = {};
    $scope.reqFriendMembers.PageNo =1;
    $scope.TotalRecordsFriendMembers =0;
    $scope.ListFriendMembers = [];

    $scope.LoadMoreMembers = function () {
        $scope.reqMembers.PageNo = $scope.reqMembers.PageNo + 1; // Show Next Page
        $scope.get_category_members_list();
    }

    $scope.get_category_members_list = function()
    {
        $scope.FrLoader = 1;
        $scope.reqFriendMembers = {
            ForumCategoryID: $('#ForumCategoryID').val(),
            SearchKeyword: $scope.searchKey,
            Filter: 'Members',
            PageNo: $scope.reqMembers.PageNo,
            PageSize: $scope.MemberLimit
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_members', $scope.reqFriendMembers, function (successResp) {
            var response = successResp.data;
            $scope.FrLoader = 0;

            if (response.ResponseCode == 200)
            {
                if ($scope.reqMembers.PageNo == 1)
                {
                    $scope.ListMembers = response.Data;
                     $scope.TotalRecordsMembers = response.TotalRecords;
                } else
                {
                    angular.forEach(response.Data, function (val, index) {
                        $scope.ListMembers.push(val);
                    });
                }
               
            } 
            else
            {
                //Show Error Message
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }
    
    $scope.get_category_experts_list = function()
    {
        $scope.FrLoader = 1;

        $scope.reqFriendMembers = {
            ForumCategoryID: $('#ForumCategoryID').val(),
            ExpertOnly : 1,
            PageNo: $scope.reqMembers.PageNo,
            PageSize: $scope.ExpertMemberLimit
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_members', $scope.reqFriendMembers, function (successResp) {
            var response = successResp.data;
            $scope.FrLoader = 0;

            if (response.ResponseCode == 200)
            {
                if ($scope.reqMembers.PageNo == 1)
                {
                    $scope.ListExpertMembers = response.Data;
                     $scope.TotalRecordsMembers = response.TotalRecords;
                } else
                {
                    angular.forEach(response.Data, function (val, index) {
                        $scope.ListExpertMembers.push(val);
                    });
                }
               
            } 
            else
            {
                //Show Error Message
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.get_members_talking = function(members)
    {
        if(!members)
        {
            return;
        }
        var total_members_count = members.length;
        var html = '';
        var count = 0;
        angular.forEach(members,function(val,key){
            count++;
            html += '<a>'+val.Name+'</a>'
            if(total_members_count == count)
            {
                if(total_members_count == 1)
                {
                    html += ' <span class="regular">is talking</span> ';
                }
                else
                {
                    html += ' <span class="regular">are talking</span> ';
                }
            }
            else if(total_members_count-1 == count)
            {
                html +=' <span class="regular">and</span> '
            }
            else
            {
                html += '<span class="regular">,</span> ';
            }
        });
        return html;
    }

    $scope.admin_suggestions = [];
    $scope.get_forum_admin_suggestions = function()
    {
        var reqData = {ForumID:$('#ForumID').val(),PageNo:1,PageSize:10};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/admin_suggestion', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.admin_suggestions = response.Data;
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.category_visibility_suggestions = [];
    $scope.get_category_visibility_suggestions = function()
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),PageNo:1,PageSize:10};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_visibility_suggestion', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.category_visibility_suggestions = response.Data;
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.category_visibilty = [];
    $scope.get_category_visibilty = function()
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val()};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_category_visibilty', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.get_category_member_suggestions();
                $scope.category_visibilty = response.Data;
                $scope.category_visibilty_total_records = response.TotalRecords;
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.change_default_permissions = function(key)
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),Param:$scope.category_detail.Param};
        if($scope.category_detail.Param[key])
        {
            reqData['Param'][key] = 0;
        }
        else
        {
            reqData['Param'][key] = 1;
        }

        WallService.CallPostApi(appInfo.serviceUrl + 'forum/save_default_permisson', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.category_detail.Param[key] = reqData['Param'][key];
                showResponseMessage(response.Message,'alert-success');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.category_member = [];
    $scope.current_page = 1;
    $scope.numPerPage = 10;
    $scope.maxSize = 5;
    $scope.MemberSearchKeyword = '';
    $scope.get_category_members = function(page_no,field,sort)
    {
        $scope.current_page = page_no;
        var reqData = {
            ForumCategoryID:$('#ForumCategoryID').val(),
            PageNo:$scope.current_page,
            PageSize:$scope.numPerPage,
            SearchKeyword:$scope.MemberSearchKeyword
        };
        if(field)
        {
            reqData['OrderBy'] = field;
        }
        if(sort)
        {
            reqData['SortBy'] = 'ASC';   
        }
        else
        {
            reqData['SortBy'] = 'DESC';   
        }
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_members', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.category_member = response.Data;
                $scope.total_category_member = response.TotalRecords;
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.get_follow_category = function(category_follow)
    {
        var html = '';
        if(category_follow.length == 1)
        {
            html += category_follow[0].Name;
        }
        if(category_follow.length == 2)
        {
            html += category_follow[0].Name+' and '+category_follow[1].Name;
        }
        if(category_follow.length == 3)
        {
            html += category_follow[0].Name+', '+category_follow[1].Name+' and '+category_follow[2].Name;
        }
        if(category_follow.length > 3)
        {
            html += category_follow[0].Name+', '+category_follow[1].Name+' and '+(parseInt(category_follow.length)-2)+' others';
        }
        return html;
    }

    $scope.change_default_value = function(field,module_id,module_entity_id,value)
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),Key:field,ModuleID:module_id,ModuleEntityID:module_entity_id};
        if(field == 'ModuleRoleID')
        {
            if(value == 17)
            {
                reqData['Value'] = 16;
            }
            else
            {
                reqData['Value'] = 17;
            }
        }
        else
        {
            if(value == 1)
            {
                reqData['Value'] = 0;
            }
            else
            {
                reqData['Value'] = 1;
            }
        }

        WallService.CallPostApi(appInfo.serviceUrl + 'forum/set_member_permission', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                angular.forEach($scope.category_member,function(val,key){
                    if(val.ModuleID == module_id && val.ModuleEntityID == module_entity_id)
                    {
                        $scope.category_member[key][field] = reqData['Value'];
                    }
                    else
                    {
                        showResponseMessage(response.Message,'alert-success');
                    }
                });
                showResponseMessage(response.Message, 'alert-success');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.num_pages = function () {
        return Math.ceil($scope.total_category_member / $scope.numPerPage);
    };

    $scope.StartPageLimit = function()
    {   
    
        return (($scope.current_page-1) * $scope.numPerPage )+1;
    }

    $scope.EndPageLimit = function()
    {       
        var EndLimiit =  (($scope.current_page) * $scope.numPerPage);

        if(EndLimiit > $scope.total_category_member)
        {
            EndLimiit = $scope.total_category_member;
        }

        return EndLimiit;
       
    }

    $scope.add_multiple_members = function(location)
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),Members:[]};
        if(location == 'visibilitylist')
        {
            angular.forEach($scope.visibilitylist,function(val,key){
                var member_data = {ModuleID:val.ModuleID,ModuleEntityID:val.ModuleEntityID,ModuleRoleID:$scope.category_detail.ModuleRoleID,CanPostOnWall:$scope.category_detail.ModuleRoleID,IsExpert:$scope.category_detail.ModuleRoleID};
                reqData.Members.push(member_data);
            });
        }
        else if(location == 'memberslist')
        {
            angular.forEach($scope.memberslist,function(val,key){
                var member_data = {ModuleID:val.ModuleID,ModuleEntityID:val.ModuleEntityID,ModuleRoleID:$scope.category_detail.ModuleRoleID,CanPostOnWall:$scope.category_detail.ModuleRoleID,IsExpert:$scope.category_detail.ModuleRoleID};
                reqData.Members.push(member_data);
            });
        }
        if(reqData.Members.length == 0)
        {
            return false;
        }
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_members', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.memberslist = [];
                showResponseMessage('Success', 'alert-success');
                $scope.get_category_member_suggestions();
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.prefill_url_forum = function(val)
    {
        var url = val.replace(new RegExp(' ', 'g'),'');
        if(url.length<=40)
        {
            $scope.CreateUpdate.URL = url;
        }
    }

    $scope.prefill_url_cat = function(val)
    {
        var url = val.replace(new RegExp(' ', 'g'),'');
        if(url.length<=40)
        {
            $scope.CreateUpdateCat.URL = url;
        }
    }

    $scope.prefill_url_scat = function(val)
    {
        var url = val.replace(new RegExp(' ', 'g'),'');
        if(url.length<=40)
        {
            $scope.SubCat.URL = url;
        }
    }

    $scope.visibilitylist = [];
    $scope.memberslist = [];
    $scope.add_multiple_visibility = function(location)
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),Members:[]};
        if(location == 'visibilitylist')
        {
            angular.forEach($scope.visibilitylist,function(val,key){
                var member_data = {ModuleID:val.ModuleID,ModuleEntityID:val.ModuleEntityID,ModuleRoleID:$scope.category_detail.ModuleRoleID,CanPostOnWall:$scope.category_detail.ModuleRoleID,IsExpert:$scope.category_detail.ModuleRoleID};
                reqData.Members.push(member_data);
            });
        }
        else if(location == 'memberslist')
        {
            angular.forEach($scope.memberslist,function(val,key){
                var member_data = {ModuleID:val.ModuleID,ModuleEntityID:val.ModuleEntityID,ModuleRoleID:$scope.category_detail.ModuleRoleID,CanPostOnWall:$scope.category_detail.ModuleRoleID,IsExpert:$scope.category_detail.ModuleRoleID};
                reqData.Members.push(member_data);
            });
        }

        if(reqData.Members.length == 0)
        {
            return false;
        }
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_visibility', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.visibilitylist = [];
                $scope.get_category_visibilty();
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.add_member_to_category = function(module_id,module_entity_id,location)
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),Members:[]};
        var member_data = {ModuleID:module_id,ModuleEntityID:module_entity_id,ModuleRoleID:17,CanPostOnWall:0,IsExpert:0};
        if($('#'+location+'-'+module_id+'-'+module_entity_id+' .chk-module-role-id').is(':checked'))
        {
            member_data['ModuleRoleID'] = 16;
        }

        if($('#'+location+'-'+module_id+'-'+module_entity_id+' .chk-subject-experts').is(':checked'))
        {
            member_data['IsExpert'] = 1;
        }

        if($('#'+location+'-'+module_id+'-'+module_entity_id+' .chk-can-post').is(':checked'))
        {
            member_data['CanPostOnWall'] = 1;
        }
        reqData.Members.push(member_data);
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_members', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                showResponseMessage('Success', 'alert-success');
                $scope.get_category_member_suggestions();
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.add_member_to_visibility = function(module_id,module_entity_id,location)
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),Members:[]};
        var member_data = {ModuleID:module_id,ModuleEntityID:module_entity_id,ModuleRoleID:17,CanPostOnWall:0,IsExpert:0};
        if($('#'+location+'-'+module_id+'-'+module_entity_id+' .chk-module-role-id').is(':checked'))
        {
            member_data['ModuleRoleID'] = 16;
        }

        if($('#'+location+'-'+module_id+'-'+module_entity_id+' .chk-subject-experts').is(':checked'))
        {
            member_data['IsExpert'] = 1;
        }

        if($('#'+location+'-'+module_id+'-'+module_entity_id+' .chk-can-post').is(':checked'))
        {
            member_data['CanPostOnWall'] = 1;
        }
        angular.forEach($scope.category_visibility_suggestions,function(v,k){
            if(module_id == v.ModuleID && module_entity_id == v.ModuleEntityID)
            {
                $scope.category_visibility_suggestions.splice(k,1);
            }
        });
        reqData.Members.push(member_data);
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_visibility', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.get_category_visibilty();
                showResponseMessage('Success', 'alert-success');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.follow_category = function(category_id,forum_id,wall)
    {
        var reqData = {ForumCategoryID:category_id};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/follow_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                if(wall=='1')
                {
                    angular.forEach($scope.category_detail.SubCategory,function(val,key){
                        if(val.ForumCategoryID == category_id)
                        {
                            $scope.category_detail.SubCategory[key].Permissions.IsMember = true;
                        }
                    });
                }
                else if(wall=='2')
                {
                    $scope.category_detail.Permissions.IsMember = true;
                    angular.forEach($scope.category_detail.SubCategory,function(val,key){
                        $scope.category_detail.SubCategory[key].Permissions.IsMember = true;
                    });
                }
                else
                {
                    angular.forEach($scope.forums,function(val,key){
                        if(val.ForumID == forum_id)
                        {
                            angular.forEach(val.CategoryData,function(v,k){
                                if(v.ForumCategoryID == category_id)
                                {
                                    $scope.forums[key].CategoryData[k].Permissions.IsMember = true;
                                }
                            });
                        }
                    });
                }
                showResponseMessage('Success', 'alert-success');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage('error', 'alert-danger');
        });
    }

    $scope.unfollow_category = function(category_id,forum_id,wall)
    {
        var reqData = {ForumCategoryID:category_id};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/unfollow_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                if(wall == '1')
                {
                    angular.forEach($scope.category_detail.SubCategory,function(val,key){
                        if(val.ForumCategoryID == category_id)
                        {
                            $scope.category_detail.SubCategory[key].Permissions.IsMember = false;
                        }
                    });
                }
                else if(wall=='2')
                {
                    $scope.category_detail.Permissions.IsMember = false;
                }
                else
                {
                    angular.forEach($scope.forums,function(val,key){
                        if(val.ForumID == forum_id)
                        {
                            angular.forEach(val.CategoryData,function(v,k){
                                if(v.ForumCategoryID == category_id)
                                {
                                    $scope.forums[key].CategoryData[k].Permissions.IsMember = false;
                                }
                            });
                        }
                    });
                }
                showResponseMessage('Success', 'alert-success');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage('error', 'alert-danger');
        });
    }

    $scope.set_selected_forum = function(forum_id)
    {
        $scope.CreateUpdateCat.ForumID = forum_id;
    }

    $scope.SubCat = {CanAllMemberPost:1,Visibility:2};

    $scope.set_selected_category = function(category_id)
    {
        $scope.SubCat.ParentCategoryID = category_id;
    }
    $scope.set_selected_category_data = function(category)
    {
        $scope.SubCat.Cat = category;
    }

    $scope.category_member_suggestions = [];
    $scope.get_category_member_suggestions = function()
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),PageNo:1,PageSize:10};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_member_suggestion', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.category_member_suggestions = response.Data;
            }
            else
            {
                window.top.location = site_url+'dashboard';
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            //showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.admin_search = '';
    $scope.admins = [];
    $scope.get_admins = function()
    {
        var reqData = {ForumID:$('#ForumID').val(),PageNo:1,PageSize:10,SearchKeyword:$scope.admin_search};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/manager', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.admins = response.Data;
            }
            if(response.ResponseCode == 412)
            {
                window.top.location = site_url+'dashboard';
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.change_category_order = function()
    {
        var reqData = {ForumID:$scope.current_forum_id,ForumCategoryID:$scope.current_category_id,OrderData:[]};
        var count = 1;
        angular.forEach($scope.forum_categories,function(val,key){
            reqData.OrderData.push({ForumCategoryID:val.ForumCategoryID,DisplayOrder:count});
            count++;
        });
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/change_category_order', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $('#reOrderCategory').modal('toggle');
                $scope.getForums();
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.change_forum_order = function()
    {
        var reqData = {OrderData:[]};
        var count = 1;
        angular.forEach($scope.forums_reorder,function(val,key){
            reqData.OrderData.push({ForumID:val.ForumID,DisplayOrder:count});
            count++;
        });
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/change_order', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $('#reOrderForum').modal('toggle');
                $scope.getForums();
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.expandCategory = function(forum_id)
    {
        angular.forEach($scope.forums,function(val,key){
            if($scope.forums[key].ForumID == forum_id)
            {
                $scope.forums[key].expandCat = 1;
                angular.forEach($scope.forums[key].CategoryFollow,function(v,k){
                    v['IsFollow'] = 1;
                    $scope.forums[key].CategoryData.push(v);
                });
            }
        });
    }
    $scope.collapseCategory = function(forum_id)
    {
        angular.forEach($scope.forums,function(val,key){
            if($scope.forums[key].ForumID == forum_id)
            {
                $scope.forums[key].expandCat = 0;
                $scope.forums[key].CategoryData.splice($scope.forums[key].CategoryData.length-$scope.forums[key].CategoryFollow.length,$scope.forums[key].CategoryFollow.length)
            }
        });
    }

    $scope.article_list = [];
    $scope.get_suggested_articles = function()
    {
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/suggested_articles', {}, function (successResp) {
            var response = successResp.data;
            if(response.ResponseCode == 200)
            {
                $scope.article_list = response.Data;
                if(!$scope.$$phase)
                {
                    $scope.$apply();
                }
            }
        });
    }

    $scope.date_format = function(date)
    {
        var localTime = moment.utc(date).toDate();
        date = moment.tz(localTime, TimeZone);
        return date.format('D MMM [at] h:mm A');
    }

    $scope.subscribe_article = function (EntityGUID) {
        var reqData = {
            EntityType: 'ACTIVITY',
            EntityGUID: EntityGUID
        };
        WallService.CallApi(reqData, 'subscribe/toggle_subscribe').then(function (response) {
            if (response.ResponseCode == 200) {
                $($scope.article_list).each(function (key, val) {
                    if ($scope.article_list[key].ActivityGUID == EntityGUID) {
                        $scope.article_list[key].IsSubscribed = response.Data.IsSubscribed;
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip({
                                container: "body"
                            });
                        }, 100);
                    }
                });
            }
        });
    }

    $scope.active_users = [];
    $scope.get_most_active_users = function()
    {
        if($('#module_entity_id').val()<=0)
        {
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/most_active_users',reqData).then(function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.active_users = response.Data;
                }
            });
        }
    }
    $scope.top_active_user = [];
    $scope.get_top_active_users = function()
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val(),PageNo:1,PageSize:5};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/top_active_user_of_forum',reqData).then(function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.top_active_user = response.Data;
                }
            });
    }

    $scope.toggle_follow = function(memberid) {
        var reqData = { MemberID: memberid, GUID: 1, Type: 'user' }
        WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function(response) {
            response = response.data;
            if (response.ResponseCode == 200) {
                angular.forEach($scope.active_users, function(val, key) {
                    if (val.UserGUID == memberid) {
                        if (val.FollowStatus == '1') {
                            $scope.active_users[key].FollowStatus = '2';
                        } else {
                            $scope.active_users[key].FollowStatus = '1';
                        }
                    }
                });
            } else {
                showResponseMessage(response.Message, 'alert-danger');
            }
        });
    }

	$scope.getForums = function()
	{
		var reqData = {ForumID:$('#module_entity_id').val()};
		WallService.CallPostApi(appInfo.serviceUrl + 'forum/list', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                logActivity();
                $scope.forums = response.Data;
                $scope.forums_reorder = angular.copy(response.Data);
                $.each($scope.forums,function(){
                    this.CategoryData=this.CategoryData.map( function (repo) {
                    repo.IsCategoryData = 1;
                    return repo;
                  });
                    this.CategoryFollow=this.CategoryFollow.map( function (repo) {
                    repo.IsCategoryData = 0;
                    return repo;
                  });
                })
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
	}
        
        function logActivity() {
            var jsonData = {
                EntityType: 'Forum'
            };
            
            if(LoginSessionKey=='') {
                return false;
            }
            WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });
        }

    $scope.forum_categories_list = [];
    $scope.current_category_id = 0;
    $scope.get_forum_categories = function(forum_id,category_id)
    {
        $scope.current_forum_id = forum_id;
        $scope.forum_categories = [];
        $scope.featured_categories = [];
        $scope.forum_categories_list = [];
        if(!category_id)
        {
            category_id = 0;
        }
        var reqData = {ForumID:forum_id,ForumCategoryID:category_id};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/manage_feature_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.forum_categories = response.Data;
                $scope.current_category_id = category_id;
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.get_forum_category_list = function(forum_id,category_id,category_data)
    { 
        $scope.forum_categories_list = [];
        var reqData = {ForumID:forum_id};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/forum_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                angular.forEach(response.Data,function(val,key){
                    $scope.forum_categories_list[val.ForumCategoryID] = val.Name;
                });
                $scope.set_selected_category(category_id);
                $scope.set_selected_category_data(category_data);
                console.log('here');
                if(!category_data.Visibility)
                {
                    $scope.SubCat.Visibility=2;
                }
                else
                {
                    $scope.SubCat.Visibility=category_data.Visibility;
                }
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.set_forum_categories = function()
    {
        var reqData = {ForumID:$scope.current_forum_id,FeatureData:[]};
        
        angular.forEach($scope.forum_categories,function(val,key){
            if(val.IsFeatured == 1)
            {
                reqData.FeatureData.push({ForumCategoryID:val.ForumCategoryID});
            }
        });

        WallService.CallPostApi(appInfo.serviceUrl + 'forum/set_feature_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $('#manageFeature').modal('toggle');
                $scope.getForums();
                showResponseMessage('Success', 'alert-success');
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.make_featured = function(forum_id)
    {
        angular.forEach($scope.forum_categories,function(val,key){
            if(val.ForumCategoryID == forum_id)
            {
                if($scope.forum_categories[key].IsFeatured == 1)
                {
                    $scope.forum_categories[key].IsFeatured = 0;
                }
                else
                {
                    $scope.forum_categories[key].IsFeatured = 1;
                }
            }
        });
    }

    $scope.moveObject = function(from, to, fromList, toList) {
        var item = $scope.items[fromList][from];
        DragDropHandler.addObject(item, $scope.items[toList], to);
        $scope.items[fromList].splice(from, 1);
    }

    $scope.createObject = function(object, to, list) {
        var newItem = angular.copy(object);
        newItem.id = Math.ceil(Math.random() * 1000);
        DragDropHandler.addObject(newItem, $scope.items[list], to);
    };
    
    $scope.deleteItem = function(itemId) {
      for (var list in $scope.items) {
        if ($scope.items.hasOwnProperty(list)) {
          $scope.items[list] = _.reject($scope.items[list], function(item) {
            return item.id == itemId; 
          });
        }
      }
    };

    $scope.disable_checkbox = function(forum_category_id)
    {
        var count = 0;
        angular.forEach($scope.forum_categories,function(val,key){
            if(val.IsFeatured == 1)
            {
                if(val.ForumCategoryID == forum_category_id)
                {
                    console.log(1);
                    return false;
                }
                count++;
            }
        });

        if(count >= 3)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    $scope.loadFriendslist = function($query) {
        if($query.length<2)
        {
            return false;
        }
        var requestPayload = { SearchKeyword: $query, ForumID: $('#ForumID').val(),Loginsessionkey:LoginSessionKey };
        var url = appInfo.serviceUrl + 'forum/admin_suggestion';
        return WallService.CallPostApi(url, requestPayload, function(successResp) {
            var response = successResp.data;
            return response.Data.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.loadMemberslist = function($query) {
        if($query.length<2)
        {
            return false;
        }
        var requestPayload = { SearchKeyword: $query, ForumCategoryID: $('#ForumCategoryID').val(),Loginsessionkey:LoginSessionKey };
        var url = appInfo.serviceUrl + 'forum/category_member_suggestion';
        return WallService.CallPostApi(url, requestPayload, function(successResp) {
            var response = successResp.data;
            angular.forEach(response.Data,function(val,key){
                response.Data[key].KeyProperty = val.ModuleID+'-'+val.ModuleEntityID;
            });
            return response.Data.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.loadVisibilitylist = function($query) {
        if($query.length<2)
        {
            return false;
        }
        var requestPayload = { SearchKeyword: $query, ForumCategoryID: $('#ForumCategoryID').val(),Loginsessionkey:LoginSessionKey };
        var url = appInfo.serviceUrl + 'forum/category_visibility_suggestion';
        return WallService.CallPostApi(url, requestPayload, function(successResp) {
            var response = successResp.data;
            angular.forEach(response.Data,function(val,key){
                response.Data[key].KeyProperty = val.ModuleID+'-'+val.ModuleEntityID;
            });
            return response.Data.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.setCardValues = function (FirstName, LastName, UserGUID)
    {
        $('#toAddressCard').val(FirstName+' '+LastName);
        $('#ToMssgFrmCardGUID').val(UserGUID);
    }

    $scope.add_admins = function()
    {
        var reqData = {ForumID:$('#ForumID').val(),Members:[]};
        angular.forEach($scope.addAdmins,function(val,key){
            reqData.Members.push({ModuleID:val.ModuleID,ModuleEntityID:val.ModuleEntityID});
        });
        if(reqData.Members.length == 0)
        {
            return false;
        }
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_admin', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.addAdmins = [];
                $scope.get_admins();
                $scope.get_forum_admin_suggestions();
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.add_single_admin = function(module_id,module_entity_id)
    {
        var reqData = {ForumID:$('#ForumID').val(),Members:[{ModuleID:module_id,ModuleEntityID:module_entity_id}]};

        WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_admin', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.get_forum_admin_suggestions();
                $scope.get_admins();
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.delete_forum = function(forum_id)
    {
        var reqData = {ForumID:forum_id};
        showConfirmBox("Delete Forum", "Are you sure? This will delete all sub-categories and discussions within this one.", function (e) {
            if (e) {
                WallService.CallPostApi(appInfo.serviceUrl + 'forum/delete', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.getForums();
                }
                else
                {
                    showResponseMessage(response.Message,'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
            }
        });
    }

    $scope.prefill_forum = function(forum_id)
    {
        $scope.addEditForumPopupTitle = 'Edit Forum';
        angular.forEach($scope.forums,function(val,key){
            if(val.ForumID == forum_id)
            {
                $scope.CreateUpdate = {Name:val.Name,Description:val.Description,URL:val.URL,ForumID:forum_id};
            }
        });
    }

    $scope.prefill_category = function(forum_id,category_id,wall)
    {
        if(wall == '1')
        {
            $scope.CreateUpdateCat = {IsDiscussionAllowed:$scope.category_detail.IsDiscussionAllowed,CanAllMemberPost:$scope.category_detail.CanAllMemberPost,Visibility:$scope.category_detail.Visibility,Name:$scope.category_detail.Name,Description:$scope.category_detail.Description,URL:$scope.category_detail.URL,ForumCategoryID:category_id,ProfilePicture:$scope.category_detail.ProfilePicture,ForumID:$scope.category_detail.ForumID};
            if($scope.category_detail.ProfilePicture!=='')
            {
                $('#forumcatprofilepic').attr('src',image_server_path+'upload/profile/220x220/'+$scope.category_detail.ProfilePicture);
            }
            $('#CatMediaGUID').val($scope.category_detail.MediaGUID);
        }
        else
        {
            angular.forEach($scope.forums,function(val,key){
                if(val.ForumID == forum_id)
                {
                    angular.forEach(val.CategoryData,function(v,k){
                        if(v.ForumCategoryID == category_id)
                        {
                            $scope.CreateUpdateCat = {IsDiscussionAllowed:v.IsDiscussionAllowed.toString(),CanAllMemberPost:v.CanAllMemberPost,Visibility:v.Visibility,Name:v.Name,Description:forum_id,URL:v.URL,Description:v.Description,ForumCategoryID:category_id,ProfilePicture:v.ProfilePicture,ForumID:v.ForumID};
                            if(v.ProfilePicture!=='')
                            {
                                $('#forumcatprofilepic').attr('src',image_server_path+'upload/profile/220x220/'+v.ProfilePicture);
                            }
                            $('#CatMediaGUID').val(v.MediaGUID);
                        }
                    });
                }
            }); 
        }
    }

    $scope.sendFriendRequest = function (friendid) {
            var reqData = {FriendGUID: friendid}
            var matchCriteria={};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/addFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                        
                    matchCriteria['ModuleEntityGUID']=friendid;
                    // Find and update friend status In Managers
                    var Findkey = _.findIndex($scope.ListManagers,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListManagers[Findkey].FriendStatus = 2;
                    }

                    // Members

                    Findkey = _.findIndex($scope.ListMembers,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListMembers[Findkey].FriendStatus = 2;
                    }

                    // Can Post

                    Findkey = _.findIndex($scope.ListCanPost,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListCanPost[Findkey].FriendStatus = 2;
                    }

                    // Knowledgebase
                    Findkey = _.findIndex($scope.ListKnowledgeBase,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListKnowledgeBase[Findkey].FriendStatus = 2;
                    }

                      // Can comment
                    Findkey = _.findIndex($scope.ListCanComment,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListCanComment[Findkey].FriendStatus = 2;
                    }
    
                      // Other Group Members
                    Findkey = _.findIndex($scope.ListOthers,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListOthers[Findkey].FriendStatus = 2;
                    }

                    Findkey = _.findIndex($scope.group_members,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                        $scope.group_members[Findkey].FriendStatus = 2;
                    }

        
                    showResponseMessage(response.Message, 'alert-success');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                $('.tooltip').remove();
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.RejectFriendRequest = function (friendid) {
            var reqData = {FriendGUID: friendid}
            var matchCriteria={};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/rejectFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    
                    matchCriteria['ModuleEntityGUID']=friendid;
                    // Find and update friend status In Managers
                    var Findkey = _.findIndex($scope.ListManagers,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                        $scope.ListManagers[Findkey].FriendStatus = 4;
                    }
                    //
                    // Members

                    Findkey = _.findIndex($scope.ListMembers,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListMembers[Findkey].FriendStatus = 4;
                    }

                    // Can Post

                    Findkey = _.findIndex($scope.ListCanPost,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListCanPost[Findkey].FriendStatus = 4;
                    }

                    // Knowledgebase
                    Findkey = _.findIndex($scope.ListKnowledgeBase,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListKnowledgeBase[Findkey].FriendStatus = 4;
                    }

                      // Can comment
                    Findkey = _.findIndex($scope.ListCanComment,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListCanComment[Findkey].FriendStatus = 4;
                    }
    
                      // Other Group Members
                    Findkey = _.findIndex($scope.ListOthers,matchCriteria);
                    
                    if(Findkey!=-1)
                    {  
                     $scope.ListOthers[Findkey].FriendStatus = 4;
                    }


                    showResponseMessage(response.Message, 'alert-success');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                $('.tooltip').remove();
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };
    
    $scope.remove_category_member = function(category_id,module_id,module_entity_guid)
    {
        showConfirmBox("Remove Member", "Are you sure? You want to remove this member.", function (e) {
            if (e) {
                var reqData = {ForumCategoryID:category_id,ModuleID:module_id,ModuleEntityGUID:module_entity_guid};
                WallService.CallPostApi(appInfo.serviceUrl + 'forum/remove_category_member', reqData, function (successResp) {
                    var response = successResp.data;
                    if(response.ResponseCode == 200)
                    {
                        angular.forEach($scope.ListMembers,function(val,key){
                            if(val.ModuleID==module_id && val.ModuleEntityGUID==module_entity_guid)
                            {
                                $scope.ListMembers.splice(key,1);
                                $scope.TotalRecordsMembers=$scope.TotalRecordsMembers - 1;
                            }
                        });
                    }
                });
            }
        });
    }

    $scope.remove_category_picture = function()
    {
        $('#CatMediaGUID').val('');
        $scope.CreateUpdateCat.ProfilePicture = '';
    }
    
    $scope.remove_subcategory_picture = function()
    {
        $('#SubCatMediaGUID').val('');
        $scope.SubCat.ProfilePicture = '';
    }

    /*$scope.forum_categories = [];
    $scope.get_forum_categories = function(forum_id)
    {
        $scope.forum_categories = [];
        var reqData = {ForumID:forum_id};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/forum_category', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                angular.forEach(response.Data,function(val,key){

                });
                $scope.forum_categories = response.Data;
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }*/

    $scope.clear_prefill_category = function(forum_id)
    {
        $scope.CreateUpdateCat = {IsDiscussionAllowed:2,CanAllMemberPost:2,Visibility:1,Name:'',Description:forum_id,URL:'',Description:'',ProfilePicture:''};
    }

    $scope.clear_prefill_subcat = function()
    {
        $scope.SubCat = {ProfilePicture:'',Name:'',Description:'',URL:'',CanAllMemberPost:1,Visibility:2};
    }

    $scope.prefill_subcat = function(CategoryDetails,SubCatID)
    {
        angular.forEach(CategoryDetails.SubCategory,function(val,key){
            if(val.ForumCategoryID == SubCatID)
            {
                $scope.SubCat = {ForumCategoryID:SubCatID,ProfilePicture:val.ProfilePicture,Name:val.Name,Description:val.Description,URL:val.URL,CanAllMemberPost:val.CanAllMemberPost,Visibility:val.Visibility};
            }
        });
    }
    $scope.prefill_subcat_data = function(SubCategoryDetails,SubCatID)
    {
        var val = SubCategoryDetails;
        $scope.SubCat = {ForumCategoryID:val.ForumCategoryID,ProfilePicture:val.ProfilePicture,Name:val.Name,Description:val.Description,URL:val.URL,CanAllMemberPost:val.CanAllMemberPost,Visibility:val.Visibility};
    }

    $scope.clear_forum = function()
    {
        $scope.addEditForumPopupTitle = 'Add Forum';
        $scope.CreateUpdate = {Name:'',Description:'',URL:'',ForumID:''};
    }

    $scope.delete_category = function(category_id,wall,title)
    {        
        if(typeof(title) === "undefined"){
            title = 'Category';
        }
        var msg = "Are you sure? This will delete all sub-categories and discussions within this one.";
        if(title == "Subcategory"){
            msg = "Are you sure? This will delete all discussions within this sub-category.";
        }
        var reqData = {ForumCategoryID:category_id};
        showConfirmBox("Delete "+title, msg, function (e) {
            if (e) {
                WallService.CallPostApi(appInfo.serviceUrl + 'forum/delete_category', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        if(wall == '1')
                        {
                            $scope.get_category_details();
                        }
                        if(wall == '3'){
                            setTimeout(function(){
                                $window.location.href = base_url+'forum';
                            },500)
                        }
                        else
                        {
                            $scope.getForums();
                        }
                    }
                    else
                    {
                        showResponseMessage(response.Message,'alert-danger');
                    }
                }, function (error) {
                    showResponseMessage(response.Message, 'alert-danger');
                });
            }
        });
    }

    $scope.remove_admin = function(forum_manager_id)
    {
        var reqData = {ForumID:$('#ForumID').val(),ForumManagerID:forum_manager_id};

        showConfirmBox("Remove Admin", "Are you sure, you want to delete this admin ?", function (e) {
            if (e) {
                WallService.CallPostApi(appInfo.serviceUrl + 'forum/delete_admin', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        $scope.get_admins();
                    }
                    else
                    {
                        showResponseMessage(response.Message,'alert-danger');
                    }
                }, function (error) {
                    showResponseMessage(response.Message, 'alert-danger');
                });
            }
        });
    }

    $scope.category_detail = [];
    $scope.get_category_details = function()
    {
        var reqData = {ForumCategoryID:$('#ForumCategoryID').val()};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_details', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.category_detail = response.Data;
                $scope.config_detail['IsAdmin'] = $scope.category_detail.Permissions.IsAdmin;
                if(!$scope.category_detail.Param)
                {
                    $scope.category_detail.Param = {'a':0,'ge':0,'p':0};
                }
                $scope.SubcatData();
            }
            else
            {
                if(response.ResponseCode == 412)
                {
                    $('#ForumCtrl').remove();
                }
                showResponseMessage(response.Message,'alert-danger');
                setTimeout(function(){
                    $window.location.href = base_url+'forum';
                },500)
                
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }
    
    $scope.bradcrumbs_details = [];
    $scope.get_bradcrumbs_details = function()
    {
        var reqData = {
            ModuleID : $('#Activity_ModuleID').val(),
            ModuleEntityID : $('#Activity_ModuleEntityID').val()
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'activity_helper/get_entity_bradcrumbs', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $rootScope.bradcrumbs_details = response.Data;
            }
            else
            {
                if(response.ResponseCode == 412)
                {
                    $('#ForumCtrl').remove();
                }
                showResponseMessage(response.Message,'alert-danger');
                setTimeout(function(){
                    //$window.location.href = base_url+'forum';
                },500)
                
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.forum_detail = [];
    $scope.get_forum_details = function()
    {
        var reqData = {ForumID:$('#ForumID').val()};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/details', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                $scope.forum_detail = response.Data;
            }
            else
            {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });
    }

    $scope.get_new_featured_post = function(forum_id,category_id,page_no)
    {
        if(!page_no)
        {
            page_no = 1;
        }
        page_no = page_no+1;

        var reqData = {ForumCategoryID:category_id,PageNo:page_no};
        WallService.CallPostApi(appInfo.serviceUrl + 'forum/featured_activity', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200)
            {
                angular.forEach($scope.forums,function(val,key){
                    if(val.ForumID == forum_id)
                    {
                        angular.forEach(val.CategoryData,function(v,k){
                            if(v.ForumCategoryID == category_id)
                            {
                                $scope.forums[key].CategoryData[k].FeaturedPost = [];
                                if(response.Data.length>0)
                                {
                                    $scope.forums[key].CategoryData[k].FeaturedPost.push(response.Data[0]);
                                }
                                $scope.forums[key].CategoryData[k]['FeaturedPageNo'] = page_no;
                            }
                        });
                    }
                });
            }
        }, function (error) {
            showResponseMessage(response.Message, 'alert-danger');
        });

    }
    $scope.visibility_toggle = function(SelectedForumCategoryVisibilityID,ForumCategoryVisibilityID)
    {
        var idx = SelectedForumCategoryVisibilityID.indexOf(ForumCategoryVisibilityID);
        if (idx > -1) {
          SelectedForumCategoryVisibilityID.splice(idx, 1);
        }
        else {
          SelectedForumCategoryVisibilityID.push(ForumCategoryVisibilityID);
        }
    }
    
    $scope.RemoveVisibility = function()
    {
        if($scope.SelectedForumCategoryVisibilityID.length >0)
        {
            var reqData = {ForumCategoryID:$scope.category_detail.ForumCategoryID,ForumCategoryVisibilityIDs:$scope.SelectedForumCategoryVisibilityID};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/remove_category_visibility', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $('#visibility').modal('toggle');
                    $scope.SelectedForumCategoryVisibilityID=[];
                    $scope.get_category_visibilty();
                    showResponseMessage(response.Message, 'alert-success');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }
       
    }
    $scope.getSubCategoryTooltip = function (SubCategory) {
        var str = '';
        $.each(SubCategory, function (k) {
            if(k>=3 && k<13)  
            {
              str += '<div>' + this.Name + '</div>';
            }
            if(k>=13)
            {
                return false;
            }
        });
        $scope.callToolTip();
        return str;
    }

    $scope.SubcatData = function(){
        $scope.category_detail.nonFollowCat = [];
        $scope.category_detail.FollowedCat = [];
        $scope.category_detail.FollowedCatMoreText = '';
        $scope.category_detail.FollowedCatMoreTextNames='';
        $scope.category_detail.NoUnfollowed = false;
        var total_allowed_unfollowed = 4;
        var addedNames = 1;
        $scope.category_detail.SubCategoryFollowed;
        $scope.category_detail.SubCategoryUnFollowed;
        if($scope.category_detail.SubCategory.length > 0){
            //if total subcategories are less or equal to four
            if($scope.category_detail.SubCategory.length <= 4){
                $scope.category_detail.nonFollowCat = $scope.category_detail.SubCategory;
            }else if($scope.category_detail.SubCategoryUnFollowed<4 && $scope.category_detail.SubCategoryUnFollowed > 0){
                angular.forEach($scope.category_detail.SubCategory,function(val,key){
                    if($scope.category_detail.nonFollowCat.length<5){
                        $scope.category_detail.nonFollowCat.push(val);    
                    }else{
                        if(addedNames<3){
                            $scope.category_detail.FollowedCatMoreTextNames+=val.Name+', ';
                            addedNames++;
                        }
                        $scope.category_detail.FollowedCat.push(val);
                    }
                });
                if($scope.category_detail.FollowedCat.length>2){

                    remainCat = $scope.category_detail.FollowedCat.length-2;
                    $scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);               
                    $scope.category_detail.FollowedCatMoreText='You are following '+$scope.category_detail.FollowedCatMoreTextNames+' and '+remainCat+' more.';
                }else{
                    remainCat = $scope.category_detail.SubCategory.FollowedCat.length;
                    $scope.category_detail.FollowedCatMoreText='You are following '+remainCat+' more.';
                }
            }else if($scope.category_detail.SubCategoryUnFollowed==0){
                $scope.category_detail.nonFollowCat = $scope.category_detail.SubCategory;
            }

            else{
                angular.forEach($scope.category_detail.SubCategory,function(val,key){
                if(val.Permissions.IsMember){
                    if(addedNames<3){
                        $scope.category_detail.FollowedCatMoreTextNames+=val.Name+', ';
                        addedNames++;
                    }
                    $scope.category_detail.FollowedCat.push(val);
                }else{
                    $scope.category_detail.nonFollowCat.push(val);    
                }
                });
                if($scope.category_detail.FollowedCat.length>2){
                    remainCat = $scope.category_detail.FollowedCat.length-2;
                    $scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);               
                    $scope.category_detail.FollowedCatMoreText='You are following '+$scope.category_detail.FollowedCatMoreTextNames+' and '+remainCat+' more.';
                }else{
                    remainCat = $scope.category_detail.FollowedCat.length;
                    //$scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);               
                    $scope.category_detail.FollowedCatMoreText='You are following '+remainCat+' more.';
                }
                    //check if you have followed all the subcategories
                    if($scope.category_detail.nonFollowCat.length==0){
                        $scope.category_detail.nonFollowCat = [];
                        $scope.category_detail.FollowedCat = [];
                        $scope.category_detail.FollowedCatMoreText = '';
                        $scope.category_detail.FollowedCatMoreTextNames='';
                        $scope.category_detail.NoUnfollowed = false;
                        var addedNames = 1;
                        angular.forEach($scope.category_detail.SubCategory,function(val,key){
                            if(addedNames<5){
                                $scope.category_detail.nonFollowCat.push(val); 
                                addedNames++;
                            }else{
                                $scope.category_detail.FollowedCatMoreTextNames+=val.Name+', ';
                                $scope.category_detail.FollowedCat.push(val);   
                            }
                        });
                        if($scope.category_detail.SubCategory.length>4){
                            remainCat = $scope.category_detail.SubCategory.length-4;
                            $scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);               
                            $scope.category_detail.FollowedCatMoreText='You are following '+$scope.category_detail.FollowedCatMoreTextNames+' and '+remainCat+' more.';
                        }else{
                            remainCat = $scope.category_detail.FollowedCat.length;
                            //$scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);               
                            $scope.category_detail.FollowedCatMoreText='You are following '+remainCat+' more.';
                        }
                    }
                }
        }           

    }

    $scope.category = false;
    $scope.setCatValue = function(value)
    {
        $scope.category = value;
    }
    
    
    

    $scope.subcategory = false;
    $scope.setSubCatValue = function(value)
    {
        $scope.subcategory = value;
    }
}]);

function removeLastComma(str) {
    if(str!=undefined){
        return str.replace(/,(\s+)?$/, '');        
    }
   
}