app.controller('RulesCtrl', function($scope, $timeout, RulesService, $window, $http,$sce) {

    $scope.rules_list = [];
    $scope.get_rules = function() {
        var reqData = {};
        RulesService.CallApi(reqData, 'admin_api/rules/get_rule').then(function(response) {
            if (response.ResponseCode == 200) {
                $scope.rules_list = response.Data;
            }
        });
    }

    $scope.gender = [{"value":0,"name":"Any"},{"value":1,"name":"Male"},{"value":2,"name":"Female"}]

    $scope.rule = {};
    $scope.add_rule = function() {
        var InterestIDs = [];
        angular.forEach($scope.rule.InterestIDs, function(val, key) {
            InterestIDs.push(val.CategoryID);
        });
        $scope.rule_send = angular.copy($scope.rule);
        $scope.rule_send.InterestIDs = InterestIDs;

        var SpecificUser = [];
        angular.forEach($scope.rule.SpecificUser, function(val, key) {
            SpecificUser.push({ EntityType: 'User', EntityID: val.UserID });
        });
        $scope.rule.SpecificUser = SpecificUser;

        var reqData = $scope.rule_send;
        RulesService.CallApi(reqData, 'admin_api/rules/add_rule').then(function(response) {
            if (response.ResponseCode == 200) {
                $('#createRule').modal('hide');
                $scope.rule = {};
                $scope.get_rules();
                if(!reqData['ActivityRuleID'] && $('#IsAdminDashboard').length==0)
                {
                    $scope.clearPopup();
                    setTimeout(function(){
                        $scope.$scope.clearPopupContent();
                        $scope.set_current_rule(response.Data.ActivityRuleID);
                        $scope.get_rule_details(response.Data.ActivityRuleID,1);
                    },1000);
                    $('#addContent').modal('show');
                }

                if($('#IsAdminDashboard').length>0)
                {
                    $('#addExistingRules').modal('hide');
                }
            }
            if (response.ResponseCode == 412) {
                ShowErrorMsg(response.Message);
                //$('#createRule').modal('hide');
                //$scope.rule = {};
            }
        });
    }


    $scope.age_groups = [];
    $scope.get_age_group = function() {
        var reqData = {};
        RulesService.CallApi(reqData, 'admin_api/rules/get_age_group').then(function(response) {
            if (response.ResponseCode == 200) {
                $scope.age_groups = [];
                $scope.age_groups.push({"AgeGroupID":"0","Name":"Any"});
                angular.forEach(response.Data,function(val,key){
                    $scope.age_groups.push(val);
                });
                //$scope.age_groups = response.Data;
            }
        });
    }

    $scope.clearPopup = function()
    {
        $('#accordion .collapse').removeClass('in');
        $('#Welcome.collapse').addClass('in');
        $('.accordion-heading').addClass('collapsed');
        $('.heading-1').removeClass('collapsed');
        $('.accordion-heading').removeClass('completed');
        $('.heading-1').addClass('completed');
    }

    $scope.clearPopupContent = function()
    {
        $scope.PostTags = [];
        $scope.PostInterests = [];
        $scope.PostSpecificUser = [];
        $scope.activity_link = "";
        $scope.activity_data = [];
        $scope.ProfileTags = [];
        $scope.ProfileInterests = [];
        $scope.ProfileSpecificUser = [];
        $scope.SpecificTags = [];
        $scope.content_rule.Location = [];
    }

    $scope.rules_config = { NoOfPostConfVal: 10, NoOfFrndConfVal: 0 };
    $scope.get_rules_config = function() {
        var reqData = {};
        RulesService.CallApi(reqData, 'admin_api/rules/get_rules_config').then(function(response) {
            if (response.ResponseCode == 200) {
                $scope.rules_config = response.Data;
                $scope.rules_config.NoOfPostConfVal = parseInt($scope.rules_config.NoOfPostConfVal);
                $scope.rules_config.NoOfFrndConfVal = parseInt($scope.rules_config.NoOfFrndConfVal);
            }
        });
    }

    $scope.set_rules_config = function() {
        var reqData = $scope.rules_config;
        RulesService.CallApi(reqData, 'admin_api/rules/set_rules_config').then(function(response) {
            if (response.ResponseCode == 200) {
                $('#ruleSetting').fadeOut();
                ShowSuccessMsg('Config Set');
            }
        });
    }

    $scope.set_welcome_message = function() {
        var reqData = { ActivityRuleID: $scope.current_rule_id, Welcome: $('#Welcome .note-editable').html() };
        RulesService.CallApi(reqData, 'admin_api/rules/rule_welcome').then(function(response) {
            if (response.ResponseCode == 200) {
                //$('#ruleSetting').fadeOut();
                //Show success message
                ShowSuccessMsg('Welcome Message Saved');
            }
        });
    }

    $scope.set_post_rule = function() {
        var reqData = { ActivityRuleID: $scope.current_rule_id, AllPublicPost: 0, PostWithTags: {}, PopularPost: {}, PublicPost: {}, SpecificUsers: {},CustomizePostIDs:$scope.activity_ids};

        var post_val = $('input[name="rulepost"]:checked').val();

        var data = {};
        angular.forEach($scope.content_rule, function(val, key) {
            data[key] = val;
        });

        if (post_val == 1) {
            reqData['AllPublicPost'] = 1;
            reqData['PublicPost'] = data;
        } else if (post_val == 2) {
            reqData['PostWithTags'] = data;

            reqData['PostWithTags']['Tag'] = [];
            angular.forEach($scope.PostTags, function(val, key) {
                reqData['PostWithTags']['Tag'].push(val);
            });

            reqData['PostWithTags']['Interest'] = [];
            angular.forEach($scope.PostInterests, function(val, key) {
                reqData['PostWithTags']['Interest'].push(val);
            });

            reqData['SpecificUsers'] = [];
            angular.forEach($scope.PostSpecificUser, function(val, key) {
                if(val.EntityType == 'Tag') {
                    reqData['SpecificUsers'].push({ EntityType: "Tag", EntityID: val.EntityID });
                } else {
                    reqData['SpecificUsers'].push({ EntityType: "User", EntityID: val.UserID });
                }
                
            });
        } else if (post_val == 3) {
            reqData['PopularPost'] = data;
        }

        RulesService.CallApi(reqData, 'admin_api/rules/rule_posts').then(function(response) {
            if (response.ResponseCode == 200) {
                //$('#ruleSetting').fadeOut();
                //Show success message
                ShowSuccessMsg('Post Rules Saved');
                $scope.get_rule_details($scope.current_rule_id,1);
            }
        });
    }

    $scope.set_tags_rule = function() {
        var tags = [];
        angular.forEach($scope.SpecificTags, function(val, key) {
            tags.push(val);
        });
        var reqData = { ActivityRuleID: $scope.current_rule_id, CustomizeTags: tags, TrendingTags: {} };

        var post_val = $('input[name="rulepost"]:checked').val();

        var data = {};
        angular.forEach($scope.content_rule, function(val, key) {
            data[key] = val;
        });

        reqData['TrendingTags'] = data;
        reqData['TrendingTags']['IsTrending'] = 0;
        if ($('#IsTrending').is(':checked')) {
            reqData['TrendingTags']['IsTrending'] = 1;
        }

        RulesService.CallApi(reqData, 'admin_api/rules/rule_tags').then(function(response) {
            if (response.ResponseCode == 200) {
                //$('#ruleSetting').fadeOut();
                //Show success message
                ShowSuccessMsg('Tags Rules Saved');
                $scope.get_rule_details($scope.current_rule_id,1);
            }
        });
    }

    $scope.set_profile_rule = function() {
        var reqData = { ActivityRuleID: $scope.current_rule_id, CustomizeProfiles: [], ProfilesWithTags: {}, PopularProfiles: {} };

        var data = {};
        angular.forEach($scope.content_rule, function(val, key) {
            data[key] = val;
        });

        if ($('#customprofiles').is(':checked')) {
            reqData['ProfilesWithTags'] = data;

            reqData['ProfilesWithTags']['Tag'] = [];
            angular.forEach($scope.ProfileTags, function(val, key) {
                reqData['ProfilesWithTags']['Tag'].push(val);
            });

            reqData['ProfilesWithTags']['Interest'] = [];
            angular.forEach($scope.ProfileInterests, function(val, key) {
                reqData['ProfilesWithTags']['Interest'].push(val);
            });

            reqData['CustomizeProfiles'] = [];
            angular.forEach($scope.ProfileSpecificUser, function(val, key) {
                reqData['CustomizeProfiles'].push(val.UserID);
            });
        } else {
            reqData['PopularProfiles'] = data;
        }

        RulesService.CallApi(reqData, 'admin_api/rules/rule_profile').then(function(response) {
            if (response.ResponseCode == 200) {
                //$('#ruleSetting').fadeOut();
                //Show success message
                ShowSuccessMsg('Profile Rules Saved');
                $scope.get_rule_details($scope.current_rule_id,1);
            }
        });
    }

    $scope.change_post_rule = function(cls) {
        $('.post-rule').attr('checked', false);
        $(cls).attr('checked', true);
        if (cls == 'rule-public-posts') {
            $scope.show_custom_post = false;
        } else if (cls == 'rule-custom-posts') {
            $scope.show_custom_post = true;
        } else if (cls == 'rule-popular-posts') {
            $scope.show_custom_post = false;
        }
    }


    $scope.ProfileInterests = [];
    $scope.loadProfileInterest = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_interest_suggestions?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.ProfileSpecificUser = [];
    $scope.loadProfileUsers = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_users?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.PostTags = [];
    $scope.loadPostTags = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_tags?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.PostInterests = [];
    $scope.loadPostInterest = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_interest_suggestions?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.PostSpecificUser = [];
    $scope.loadPostUsers = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_users?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.ProfileTags = [];
    $scope.loadProfileTags = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_tags?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };


    $scope.rule.InterestIDs = [];
    $scope.loadInterest = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_interest_suggestions?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.rule.SpecificUser = [];
    $scope.loadUsers = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_users?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.rule.SpecificTags = [];
    $scope.loadSpecificTags = function($query) {
        return $http.get(base_url + 'admin_api/rules/get_tags?Keyword=' + $query, { cache: false }).then(function(response) {
            var interestList = response.data.Data;
            return interestList.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.currentLocationInitialize = function(id) {
        currentLocationInitialize(id);
    }

    $scope.current_rule_id = 0;

    $scope.set_current_rule = function(rule_id) {
        $scope.current_rule_id = rule_id;
        angular.forEach($scope.rules_list, function(val, key) {
            if (rule_id == val.ActivityRuleID) {
                $scope.rule = { ActivityRuleID: rule_id, Name: val.Name, Location: val.Location, AgeGroupID: val.AgeGroupID, InterestIDs: val.InterestData,SpecificUser:val.UserData };
                if (val.Gender == "Male") {
                    $scope.rule['Gender'] = 1;
                } else if (val.Gender == "Female") {
                    $scope.rule['Gender'] = 2;
                } else if (val.Gender == "") {
                    $scope.rule['Gender'] = 0;
                }
                setTimeout(function() {
                    $('.selectpicker').selectpicker('refresh');
                }, 100);
            }
        });
    }

    $scope.clear_current_rule = function() {
        $scope.current_rule_id = 0;
        $scope.rule = {};
        setTimeout(function() {
            $('.selectpicker').selectpicker('refresh');
        }, 100);
    }

    $scope.initCity = function() {
        initGoogleLocation($("#address").get(0), 'rule');
    }

    $scope.getcity = function(city) {
        jQuery.getJSON("http://gd.geobytes.com/GetCityDetails?callback=?&fqcn=" + city, function(data) {
            var reqData = {};
            reqData['CountryCode'] = data.geobytesinternet;
            reqData['Country'] = data.geobytescountry;
            reqData['State'] = data.geobytesregion;
            reqData['StateCode'] = data.geobytescode;
            reqData['City'] = data.geobytescity;
            if (!$scope.rule.Location) {
                $scope.rule.Location = [];
            }
            $scope.rule.Location.push(reqData);
            if (!$scope.$$phase) {
                $scope.$apply();
            }
        });
    }

    $scope.content_rule = { "Location": [], "Gender": 0, "AgeGroupID": 0 };
    $scope.initCity2 = function() {
        initGoogleLocation($("#address2").get(0), 'content_rule');
    }

    $scope.getcity2 = function(city) {
        jQuery.getJSON("http://gd.geobytes.com/GetCityDetails?callback=?&fqcn=" + city, function(data) {
            var reqData = {};
            reqData['CountryCode'] = data.geobytesinternet;
            reqData['Country'] = data.geobytescountry;
            reqData['State'] = data.geobytesregion;
            reqData['StateCode'] = data.geobytescode;
            reqData['City'] = data.geobytescity;
            if (!$scope.content_rule.Location) {
                $scope.content_rule.Location = [];
            }
            $scope.content_rule.Location.push(reqData);
            if (!$scope.$$phase) {
                $scope.$apply();
            }
        });
    }

    $scope.sort_list = function() {
        $('.sortable-table').sortable();
    }

    $scope.sortableOptions = {
        // called after a node is dropped
        stop: function(e, ui) {
            var reqData = { OrderData: [] };

            var i = 1;
            angular.forEach($scope.rules_list, function(val, key) {
                reqData['OrderData'].push({ ActivityRuleID: val.ActivityRuleID, DisplayOrder: i });
                i++;
            });

            RulesService.CallApi(reqData, 'admin_api/rules/change_order').then(function(response) {
                if (response.ResponseCode == 200) {
                    //$('#ruleSetting').fadeOut();
                    //Show success message
                }
            });
            /*console.log(ui);
            console.log($scope.rules_list);*/

        }
    }

    $scope.date_format = function(date)
    {
        var localTime = moment.utc(date).toDate();
        return moment(date).format('D MMM [at] h:mm A');
    }

    $scope.activity_data = [];
    $scope.activity_ids = [];
    $scope.fetch_single_activity = function(activity_link) {
        var activity_link = activity_link.split('/');
        var activity_count = activity_link.length;
        var activity_guid = '';
        if (activity_link[activity_count - 1] !== '') {
            activity_guid = activity_link[activity_count - 1];
        } else if (activity_link[activity_count - 2] !== '') {
            activity_guid = activity_link[activity_count - 2];
        }

        var reqData = { ActivityGUID: activity_guid };
        RulesService.CallApi(reqData, 'admin_api/rules/get_activity').then(function(response) {
            if (response.ResponseCode == 200) {
                var push = true;
                angular.forEach($scope.activity_ids,function(val,key){
                    if(val == response.Data['ActivityID'])
                    {
                        push = false;
                    }
                });
                if(push)
                {
                    $scope.activity_data.push(response.Data);
                    $scope.activity_ids.push(response.Data['ActivityID']);
                }
                else
                {
                    //showResponseMessage('This activity is already added in list.','alert-success');
                }
                $scope.activity_link = '';
            }
        });
    }

    $scope.delete_rule = function(rule_id) {
        showAdminConfirmBox('Delete Rule','Are you sure you want to delete this rule ?',function(e){
            if(e)
            {
                var reqData = { ActivityRuleID: rule_id };
                RulesService.CallApi(reqData, 'admin_api/rules/delete').then(function(response) {
                    if (response.ResponseCode == 200) {
                        angular.forEach($scope.rules_list, function(val, key) {
                            if(val.ActivityRuleID == rule_id)
                            {
                                $scope.rules_list.splice(key,1);
                            }
                        });
                    }
                });
            }
        });
    }


    $scope.update_rule_gender = function(gender)
    {
        $scope.rule.Gender = 0;
        if(gender)
        {
            $scope.rule.Gender = parseInt(gender);
        }
    } 

    $scope.update_rule_age_group = function(age_group_id)
    {
        $scope.rule.AgeGroupID = 0;
        if(age_group_id)
        {
            $scope.rule.AgeGroupID = age_group_id;
        }
    }

    $scope.update_existing_rule = function(rule_id)
    {
        var new_rule_details = {};
        var interest_data = [];
        angular.forEach($scope.rules_list,function(val,key){
            if(val.ActivityRuleID == rule_id)
            {
                if(val.InterestData.length>0)
                {
                    angular.forEach(val.InterestData,function(v,k){
                        interest_data.push(v.CategoryID);
                    });
                }
                new_rule_details = {ActivityRuleID:val.ActivityRuleID,Name:val.Name,Location:$scope.rule.Location,AgeGroupID:$scope.rule.AgeGroupID,InterestIDs:val.interest_data,SpecificUser:val.UserData,Gender:$scope.rule.Gender};
                RulesService.CallApi(new_rule_details, 'admin_api/rules/add_rule').then(function(response) {
                    if (response.ResponseCode == 200) {
                        $('#addExistingRules').modal('hide');
                    }
                });
            }
        });
    }

    $scope.rule_details_custom = [];

    $scope.set_content_rule = function(rule_id,section)
    {
        angular.forEach($scope.rules_list,function(val,key){
            if(val.ActivityRuleID == rule_id)
            {
                $scope.content_rule.Location = val.Location;
                $scope.content_rule.Gender = val.Gender;
                $scope.content_rule.AgeGroupID = val.AgeGroupID;
            }
        });
        $scope.isFilterExistsForTab = 1;
        if(section === undefined) {
            $scope.isFilterExistsForTab = 0;
        }
        
        if(section == 'post')
        {
            var section_details = [];

            if(Object.keys($scope.rule_details_custom.PublicPost).length>0)
            {
                angular.forEach($scope.rule_details_custom.PublicPost,function(val,key){
                    if(key == 'Location' || key == 'Gender' || key == 'AgeGroupID')
                    {
                        $scope.content_rule[key] = val;
                    }
                });
            }
            if(Object.keys($scope.rule_details_custom.PostWithTags).length>0)
            {
                angular.forEach($scope.rule_details_custom.PostWithTags,function(val,key){
                    if(key == 'Location' || key == 'Gender' || key == 'AgeGroupID')
                    {
                        $scope.content_rule[key] = val;
                    }
                });
            }
            if(Object.keys($scope.rule_details_custom.PopularPost).length>0)
            {
                angular.forEach($scope.rule_details_custom.PopularPost,function(val,key){
                    if(key == 'Location' || key == 'Gender' || key == 'AgeGroupID')
                    {
                        $scope.content_rule[key] = val;
                    }
                });
            }
        }

        if(section == 'profile')
        {
            var section_details = [];

            if(Object.keys($scope.rule_details_custom.ProfilesWithTags).length>0)
            {
                angular.forEach($scope.rule_details_custom.ProfilesWithTags,function(val,key){
                    if(key == 'Location' || key == 'Gender' || key == 'AgeGroupID')
                    {
                        $scope.content_rule[key] = val;
                    }
                });
            }
            if(Object.keys($scope.rule_details_custom.CustomizeProfiles).length>0)
            {
                angular.forEach($scope.rule_details_custom.CustomizeProfiles,function(val,key){
                    if(key == 'Location' || key == 'Gender' || key == 'AgeGroupID')
                    {
                        $scope.content_rule[key] = val;
                    }
                });
            }
            if(Object.keys($scope.rule_details_custom.PopularProfiles).length>0)
            {
                angular.forEach($scope.rule_details_custom.PopularProfiles,function(val,key){
                    if(key == 'Location' || key == 'Gender' || key == 'AgeGroupID')
                    {
                        $scope.content_rule[key] = val;
                    }
                });
            }
        }

        if(section == 'tags')
        {
            var section_details = [];
            if(Object.keys($scope.rule_details_custom.TrendingTags).length>0)
            {
                angular.forEach($scope.rule_details_custom.TrendingTags,function(val,key){
                    if(key == 'Location' || key == 'Gender' || key == 'AgeGroupID')
                    {
                        $scope.content_rule[key] = val;
                    }
                });
            }
        }

        if($scope.content_rule.Gender == 'Male')
        {
            $scope.content_rule.Gender = 1;
        }
        else if($scope.content_rule.Gender == 'Female')
        {
            $scope.content_rule.Gender = 2;   
        }
        else if($scope.content_rule.Gender == 'Any')
        {
            $scope.content_rule.Gender = 0;
        }
    }

    $scope.change_post_rule_val = function(val)
    {
        $scope.publicpostrule = val;
    }

    $scope.current_rule_details = [];
    $scope.get_rule_details = function(rule_id,from_save) {

        if(!from_save)
        {
            $scope.set_content_rule(rule_id);
        }

        var reqData = { ActivityRuleID: rule_id };
        RulesService.CallApi(reqData, 'admin_api/rules/get_rule_details').then(function(response) {
            if (response.ResponseCode == 200) {
                setTimeout(function(){
                    var data = response.Data;
                    
                    $scope.rule_details_custom = data;

                    if(data.PostWithTags && data.PostWithTags!=='[]')
                    {
                        $scope.change_post_rule_val(2);
                        data.PostWithTags = JSON.parse(data.PostWithTags);
                        if(data.PostWithTags.Tag)
                        {
                            $scope.PostTags = data.PostWithTags.Tag;
                        }
                        if(data.PostWithTags.Interest)
                        {
                            $scope.PostInterests = data.PostWithTags.Interest;
                        }
                        $scope.PostSpecificUser = data.PostFromUserList;
                        $scope.activity_data = data.CustomizePostList;
                        if(data.CustomizePost)
                        {
                            $scope.activity_ids = data.CustomizePost;
                        }
                        else
                        {
                            $scope.activity_ids = [];
                        }
                    }
                    else if(data.PopularPost && data.PopularPost!=='[]')
                    {
                        $scope.change_post_rule_val(3);
                    }
                    else
                    {
                        $scope.change_post_rule_val(1);
                    }


                    $scope.ruleprofile = 1;
                    if(data.ProfilesWithTags && data.ProfilesWithTags!="[]")
                    {
                        $scope.ruleprofile = 2;
                        data.ProfilesWithTags = JSON.parse(data.ProfilesWithTags);
                        if(data.ProfilesWithTags.Tag)
                        {
                            $scope.ProfileTags = data.ProfilesWithTags.Tag;
                        }
                        if(data.ProfilesWithTags.Interest)
                        {
                            $scope.ProfileInterests = data.ProfilesWithTags.Interest;
                        }
                        $scope.ProfileSpecificUser = data.CustomizeProfilesList;
                    }

                    data.CustomizeTags = (data.CustomizeTags) ? JSON.parse(data.CustomizeTags) : data.CustomizeTags ;
                    data.TrendingTags = (data.TrendingTags) ? JSON.parse(data.TrendingTags) : data.TrendingTags ;
                    if(data.TrendingTags.length>0)
                    {
                        $scope.trendingTags = data.TrendingTags.IsTrending;
                    }
                    
                    $scope.SpecificTags = data.CustomizeTags;

                    $('#Welcome .note-editable').html(data.Welcome);
                },1000);
            }
        });
    }

    $scope.addClass = function(cls,n)
    {
        $('.'+cls).removeClass(cls);
        $('.heading-'+n).addClass(cls);
    }

    $scope.remove_activity = function(activity_id)
    {
        angular.forEach($scope.activity_data,function(val,key){
            if(val.ActivityID = activity_id)
            {
                $scope.activity_data.splice(key,1);
            }
        });
        angular.forEach($scope.activity_ids,function(val,key){
            if(val = activity_id)
            {
                $scope.activity_ids.splice(key,1);
            }
        });
    }

    $scope.textToLink = function(inputText, onlyShortText, count) {
        if (typeof inputText !== 'undefined' && inputText !== null) {
            inputText = inputText.toString();
            inputText = inputText.replace('contenteditable', 'contenteditabletext');
            var replacedText, replacePattern1, replacePattern2, replacePattern3;
            inputText = inputText.replace("contenteditable", "contenteditabletext");
            replacedText = inputText.replace("<br>", " ||| ");
            /*replacedText = replacedText.replace(/</g, '&lt');
            replacedText = replacedText.replace(/>/g, '&gt');
            replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
            replacedText = replacedText.replace(/&lt/g, '<');
            replacedText = replacedText.replace(/&gt/g, '>');*/
            //URLs starting with http://, https://, or ftp://
            replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            replacedText = replacedText.replace(replacePattern1, function($1) {
                var link = $1;
                var link2 = '';
                var href = $1;
                if (link.length > 35) {
                    link2 = link.substr(0, 25);
                    link2 += '...';
                    link2 += link.slice(-5);
                    link = link2;
                }
            });
            //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
            replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
            replacedText = replacedText.replace(replacePattern2, function($1, $2) {

                var link = $1;
                var link2 = '';
                var href = $1;
                if (link.length > 35) {
                    link2 = link.substr(0, 25);
                    link2 += '...';
                    link2 += link.slice(-5);
                    link = link2;
                }
                href = href.trim();

            });
            //Change email addresses to mailto:: links.
            replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
            replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');
            replacedText = replacedText.replace(" ||| ", "<br>");
            replacedText = checkTaggedData(replacedText);
            var repTxt = removeTags(replacedText);
            var totalwords = 200;

            if (repTxt && (repTxt.length > totalwords)) {
                replacedText = '<span class="show-less">' + smart_substr(totalwords, replacedText) + '</span>';
            }
            replacedText = $sce.trustAsHtml(replacedText);
            return replacedText
        } else {
            return '';
        }
    }

    function checkTaggedData(replacedText) {
      if (replacedText) {
        var regex = /<a\shref[\s\S]*>([\s\S]*)<\/a>/g,
                matched,
                highLightedText;
        if ((matched = regex.exec(replacedText)) !== null) {
          replacedText = replacedText.replace(matched[0], '{{:*****:}}');
          if ( matched[1] ) {
            highLightedText = $scope.getHighlighted(matched[1]);
            matched[0] = matched[0].replace(matched[1], highLightedText);
          }
          replacedText = replacedText.replace('{{:*****:}}', matched[0]);
          return replacedText;
        } else {
          return replacedText;
        }
      }
    } 

    $scope.cellWidth = function(){
    $('.rulelist-table tr td').each(function () {
            var cell = $(this); 
            cell.width(cell.width()); 
        });    
     }  
     
    function getLocationDataGoogleObj(place) {
        var address_components = place.address_components;
        
        var reqData = {};
        angular.forEach(address_components, function(obj, index){
            if(obj.types[0] == 'administrative_area_level_2') { // city
                obj.long_name; obj.short_name;
                reqData['City'] = obj.long_name;
            }
            
            if(obj.types[0] == 'administrative_area_level_1') { // state
                reqData['StateCode'] = obj.short_name;
                reqData['State'] = obj.long_name;
            }
            
            if(obj.types[0] == 'country') { // country
                reqData['CountryCode'] = obj.short_name;
                reqData['Country'] = obj.long_name;
            }
        });
        
        return reqData;
    }
     
    function initGoogleLocation(inputEle, scopeProp) {
        var options = {
            types: ['(cities)']
        };
        
        function setScopeLocation(reqData) {
            
            if(!reqData['City']) {
                return;
            }
            
            if (!$scope[scopeProp].Location) {
                $scope[scopeProp].Location = [];
            }
            $scope[scopeProp].Location.push(reqData);
            if (!$scope.$$phase) {
                $scope.$apply();
            }
        }
        
        var googlePlaceAutoComplete = new google.maps.places.Autocomplete(inputEle, options);
        google.maps.event.addListener(googlePlaceAutoComplete, 'place_changed', function() {
            var place = googlePlaceAutoComplete.getPlace();
            var reqData = getLocationDataGoogleObj(place);
            setScopeLocation(reqData);
            inputEle.value = '';
            
        });
    }
    
    function initGeoByteLocation() {
        $("#address").autocomplete({
                source: function(request, response) {
                    $.getJSON(
                        "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + request.term,
                        function(data) {
                            if (data == "") {
                                response();
                            } else {
                                response(data);
                            }
                        }
                    );
                },
                minLength: 3,
                select: function(event, ui) {
                    var selectedObj = ui.item;
                    var countryCode = $scope.getcity(selectedObj.value);
                    $("#address").val('');
                    return false;
                },
                open: function() {
                    $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                },
                close: function() {
                    $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                }
            });
        $("#address").autocomplete("option", "delay", 100);
        
        
        setTimeout(function() {
            $("#address2").autocomplete({
                source: function(request, response) {
                    $.getJSON(
                        "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + request.term,
                        function(data) {
                            if (data == "") {
                                response();
                            } else {
                                response(data);
                            }
                        }
                    );
                },
                minLength: 3,
                select: function(event, ui) {
                    var selectedObj = ui.item;
                    var countryCode = $scope.getcity2(selectedObj.value);
                    $("#address2").val('');
                    return false;
                },
                open: function() {
                    $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                },
                close: function() {
                    $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                }
            });
            $("#address2").autocomplete("option", "delay", 100);
        }, 500);
    }
    
});
 

function currentLocationInitialize(txtId) {
    var options = {
        types: ['(cities)']
    };

    var input = document.getElementById(txtId);
    currentLocation = new google.maps.places.Autocomplete(input, options);
    google.maps.event.addListener(currentLocation, 'place_changed', function() {
        currentLocationFillInPrepare('address');
    });
}

function currentLocationFillInPrepare(txtId) {
    if (txtId == 'hometown') {
        var place = currentLocation2.getPlace();
    } else {
        var place = currentLocation.getPlace();
    }
    locationFillInAddress(txtId, place);
}

function locationFillInAddress(txtId, place) {
    var obj = {};
    obj.unique_id = place.id;
    obj.formatted_address = place.formatted_address;
    obj.lat = place.geometry.location.lat();
    obj.lng = place.geometry.location.lng();
    obj.street_number = "";
    obj.route = "";
    obj.city = "";
    obj.state = "";
    obj.country = "";
    obj.postal_code = "";

    if (LoginSessionKey == '') {
        $('#lat').val(obj.lat);
        $('#lng').val(obj.lng);
        angular.element($('#EventPopupFormCtrl')).scope().getEventNearYou();
    }

    for (var j = 0; j < place.address_components.length; j++) {
        var att = place.address_components[j].types[0];
        var val = place.address_components[j][component_form[att]];
        // street_number
        if (att == 'street_number') {
            obj.street_number = val;
        }
        // route
        if (att == 'route') {
            obj.route = val;
        }
        // city
        if (att == 'locality') {
            obj.city = val;
        }
        // state
        if (att == 'administrative_area_level_1') {
            obj.state = val;
        }
        // country
        if (att == 'country') {
            obj.country = val;
        }
        // zip_code
        if (att == 'postal_code') {
            obj.postal_code = val;
        }
    }
    if (txtId == 'hometown') {
        $scope.HLocation = obj;
        $scope.HLocationEdit = obj.formatted_address;
    } else {
        $scope.location = obj;
    }
    //console.log($scope.location.formatted_address);
    //$scope.LocationEdit = $scope.location.formatted_address;
}




$(document).ready(function() {
    $(document).on('click','#setRule', function() {
        var popupwd = $('#ruleSetting').width() - 33;
        var leftoffset = $(this).offset().left;
        var topoffset = $(this).offset().top;
        $('#ruleSetting').css({ 'left': (leftoffset - popupwd), 'top': (topoffset) }).fadeIn();
    });
    $(document).on('click','#closeRuleSetting', function() {
        $('#ruleSetting').fadeOut();
    });

    $("#summernote").summernote({
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline']]
        ],
        placeholder: 'Set your welcome message here….'

    });
});

function removeTags(txt) {
  if (txt) {
    var rex = /(<([^>]+)>)/ig;
    return txt.replace(rex, "");
  } else {
    return txt;
  }
}
 
function objToArr(obj)
{
    var arr = [];
    for(var x in obj){
      arr.push(obj[x]);
    }
    return arr;
}

function smart_substr(n, s) {
    var m, r = /<([^>\s]*)[^>]*>/g,
        stack = [],
        lasti = 0,
        result = '';

    //for each tag, while we don't have enough characters
    while ((m = r.exec(s)) && n) {
        //get the text substring between the last tag and this one
        var temp = s.substring(lasti, m.index).substr(0, n);
        //append to the result and count the number of characters added
        result += temp;
        n -= temp.length;
        lasti = r.lastIndex;

        if (n) {
            result += m[0];
            if (m[1].indexOf('/') === 0) {
                //if this is a closing tag, than pop the stack (does not account for bad html)
                stack.pop();
            } else if (m[1].lastIndexOf('/') !== m[1].length - 1) {
                //if this is not a self closing tag than push it in the stack
                stack.push(m[1]);
            }
        }
    }

    //add the remainder of the string, if needed (there are no more tags in here)
    result += s.substr(lasti, n);

    if (removeTags(s).length > n) {
        result += '...';
    }
    //fix the unclosed tags
    while (stack.length) {
        result += '</' + stack.pop() + '>';
    }
    result = result.replace(/(<br>)+/g, '<br>');
    result = result.replace(/(<\/br>)+/g, '<br>');
    result = result.replace(/(<br\/>)+/g, '<br>');

    result = result.replace(/(<br>$)/g, "");
    return result;
}