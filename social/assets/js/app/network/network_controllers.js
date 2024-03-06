/*	Controller(s)
 ===================================*/
angular.module('App').controller('InviteFriendCtrl', ['$scope', 'apiService', 'appInfo', 'WallService', function ($scope, apiService, appInfo, WallService) {
        $scope.personalmessage = '';
        $scope.fnSendInvitation = function () {
            $('#nativesendinvitaion').attr('disabled', 'disabled');
            if ($("#form1email").val() == '') {
                var friendsemail = '';
            } else {
                var friendsemail = $scope.friendsemail;
            }
            var personalmessage = $scope.personalmessage;
            emails = friendsemail.split(',');
            if (emails.length > 0)
            {
                emailarray = new Array();
                for (i = 0; i < emails.length; i++)
                {
                    emailarray.push(emails[i].trim());
                }
            }

            $("#erroremailarray").text('');
            var requestData = {
                UserSocialId: emailarray,
                Message: personalmessage
            };


            WallService.CallPostApi(appInfo.serviceUrl + 'build_network/send_native_invitations', requestData, function (successResp) {
              var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $("#form1email").val('');
                    $("#textareaID").val('');
                    $("#errorinvitaionmessage").html('');
                    alertify.success(response.Message);
                } else if (response.ResponseCode == 763) {
                    $("#errorinvitaionmessage").html(response.Message);
                } else if (response.ResponseCode == 506) {
                    $("#erroremailarray").text(response.Message);
                } else {
                    if (response.ResponseCode == 412) {
                        if (response.Message == 'Please enter valid emailid.' || response.Message == 'Email ID is required') {
                            $("#errorinvitaionmessage").html('');
                            $("#erroremailarray").html(response.Message);
                        } else {
                            $("#errorinvitaionmessage").html(response.Message);
                        }
                    } else {
                        $("#errorinvitaionmessage").html(response.Message);
                    }
                }

                $('#nativesendinvitaion').removeAttr('disabled');
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $(document).ready(function () {
            $(document).on("click", "#nativesendinvitaion", function () {
                $scope.fnSendInvitation();
            });
        });


        // 8 December 2015

        $scope.interest_list = [];
        $scope.social_login = false;

        $scope.get_interest = function ()
        {
            var reqData = {};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/interest', reqData, function (successResp) {
              var response = successResp.data;
                if (response.ResponseCode == 412)
                {
                    showResponseMessage(response.Message, 'alert-danger');
                    setTimeout(function () {
                        window.top.location = base_url;
                    }, 2000);
                } else
                {
                    $scope.interest_list = response.Data;
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.repeatDoneBCard = function () {
            ;
        }

        $scope.followers_list = [];
        $scope.TotalFollowers = 0;
        $scope.get_followers_list = function (UserGUID)
        {
            var reqData = {Type: 'followers', UserGUID: UserGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/connections', reqData, function (successResp) {
              var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.followers_list = response.Data.Members;
                    $scope.TotalFollowers = response.Data.TotalRecords;
                    $('#totalFollowers').modal('show');
                    console.log($scope.followers_list);
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.save_categories = function (noredirect)
        {
            var CategoryIDs = []
            $('input[name="CategoryIDs[]"]').each(function (k, v) {
                if ($('input[name="CategoryIDs[]"]:eq(' + k + ')').is(':checked'))
                {
                    CategoryIDs.push($(v).val());
                }
            });
            /*if(CategoryIDs.length>0)
             {*/
            var reqData = {CategoryIDs: CategoryIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/save_interest', reqData, function (successResp) {
                if (!noredirect)
                {
                    window.top.location = base_url + 'Network/grow_your_network';
                } else
                {
                    showResponseMessage('Your interest have been saved successfully.', 'alert-success');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            /*} else
             {
             if(!noredirect)
             {
             window.top.location = base_url+'network/grow_your_network';
             }
             }*/
        };

        $scope.user_page_no = 12;
        $scope.group_page_no = 12;
        $scope.popular_profile_page_no = 12;
        $scope.user_details = [];
        $scope.popular_user = [];
        $scope.is_busy = 0;
        $scope.is_p_busy = 0;

        $scope.grow_user_network = function ()
        {
            $scope.is_busy = 1;
            var reqData = {PageSize: $scope.user_page_no};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/grow_user_network', reqData, function (successResp) {
              var response = successResp.data;
              if (response.ResponseCode == 200)
              {
                  logActivity();
                  $scope.users_list = response.Data;
                  $scope.is_busy = 0;
              } else
              {
                  showResponseMessage(response.Message, 'alert-danger');
                  setTimeout(function () {
                      window.top.location = base_url;
                  }, 5000);
                  $scope.is_busy = 0;
              }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
        
        $scope.grow_user_network_follow = function ()
        {
            $scope.is_busy = 1;
            var reqData = {limit: $scope.user_page_no};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/get_users_to_follow', reqData, function (successResp) {
              var response = successResp.data;
              if (response.ResponseCode == 200)
              {
                  logActivity();
                  $scope.users_list_follow = response.Data;
                  $scope.is_busy = 0;
              } else
              {
                  showResponseMessage(response.Message, 'alert-danger');
                  setTimeout(function () {
                      window.top.location = base_url;
                  }, 5000);
                  $scope.is_busy = 0;
              }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
        
        
        function logActivity() {
            var jsonData = {
                EntityType: 'User'
            };
            
            if(LoginSessionKey=='') {
                return false;
            }
            WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });
        }
        
        $scope.get_popular_profile = function ()
        {
            console.log('here');
            $scope.is_p_busy = 1;
            var reqData = {PageSize: $scope.popular_profile_page_no};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/get_popular_profile',reqData,function (response) {
                response = response.data;
                if (response.ResponseCode == 200)
                {
                    $scope.popular_user = response.Data;
                    $scope.is_p_busy = 0;
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                    setTimeout(function () {
                        window.top.location = base_url;
                    }, 5000);
                    $scope.is_p_busy = 0;
                }
            });
        }

        $scope.grow_group_network = function ()
        {
            $scope.is_busy = 1;
            var reqData = {PageSize: $scope.group_page_no};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/grow_group_network', reqData, function (successResp) {
              var response = successResp.data;
                $scope.groups_list = response.Data;
                $scope.is_busy = 0;
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.get_user_by_guid = function (UserGUID)
        {
            var reqData = {PageNo: $scope.user_page_no, PageSize: 1, UserGUID: UserGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/grow_user_network', reqData, function (successResp) {
              var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if (response.Data.length > 0)
                    {
                        response.Data[0]['FullName'] = response.Data[0].FirstName + ' ' + response.Data[0].LastName;
                        var append = true;
                        angular.forEach($scope.existing_users,function(v,k){
                            if(v.UserGUID==response.Data[0].UserGUID)
                            {
                                append = false;
                            }
                        });
                        if(append)
                        {
                            $scope.existing_users.push(response.Data[0]);
                        }
                    }
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.get_new_user = function (UserGUID)
        {
            if ($scope.social_login)
            {
                angular.forEach($scope.existing_users, function (value, key) {
                    if (value.UserGUID == UserGUID)
                    {
                        $scope.existing_users.splice(key, 1);
                    }
                });
                return;
            } else
            {
                $scope.user_page_no = $scope.user_page_no + 1;
                var rData = {"EntityGUID": UserGUID, "EntityType": "User"};
                WallService.CallPostApi(appInfo.serviceUrl + 'ignore', rData, function (successResp) {
                  var reqData = {PageNo: $scope.user_page_no, PageSize: 1};
                  WallService.CallPostApi(appInfo.serviceUrl + 'friends/grow_user_network', reqData, function (successResp) {
                    var response = successResp.data;
                    angular.forEach($scope.users_list, function (value, key) {
                        if (value.UserGUID == UserGUID)
                        {
                            if (response.Data.length > 0)
                            {
                                $scope.users_list[key] = response.Data[0];
                            } else
                            {
                                $scope.users_list.splice(key, 1);
                            }
                        }
                    });
                    angular.forEach($scope.users_list_follow, function (value, key) {
                        if (value.UserGUID == UserGUID)
                        {
                            if (response.Data.length > 0)
                            {
                                $scope.users_list_follow[key] = response.Data[0];
                            } else
                            {
                                $scope.users_list_follow.splice(key, 1);
                            }
                        }
                    });
                  }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                  });
                }, function (error) {
                  // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        }

        $scope.get_user_details = function (UserGUID)
        {
            angular.forEach($scope.users_list, function (val, key) {
                if (val.UserGUID == UserGUID)
                {
                    $scope.user_details = val;
                }
            });
        }

        $scope.removeSocialLogin = function ()
        {
            $scope.social_login = false;
        }

        $scope.get_new_group = function (GroupGUID)
        {
            $scope.group_page_no = $scope.group_page_no + 1;
            var reqData = {PageNo: $scope.group_page_no, PageSize: 1};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/grow_group_network', reqData, function (successResp) {
                var response = successResp.data;
                angular.forEach($scope.groups_list, function (value, key) {
                    if (value.GroupGUID == GroupGUID)
                    {
                        if (response.Data.length > 0)
                        {
                            $scope.groups_list[key] = response.Data[0];
                        } else
                        {
                            $scope.groups_list.splice(key, 1);
                        }
                    }
                });
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.initGrid = function ()
        {

            var $prvw = $('#listDetail'),
                    $gall = $('.gridView'),
                    $li = $gall.find(">li"),
                    $full = $("<li />", {"class": "full", html: $prvw});

            $(document).on("click", '.gridView > li.col-sm-3', function (evt) {
                var $el = $(this),
                        d = $el.data(),
                        $clone = $full.clone();
                $el.toggleClass("active").siblings().removeClass("active");
                $prvw.hide();
                $full.after($clone);
                $clone.find(" > div").slideUp(function () {
                    $clone.remove();
                });

                if (!$el.hasClass("active"))
                    return;
                $li.filter(function (i, el) {
                    return el.getBoundingClientRect().top < evt.clientY;
                }).last().after($full);
                $prvw.slideDown();
            });

            $(document).on('click', '.profiles-listing .dropdown-menu > li,.profiles-listing li .icon-remove', function (evt) {
                evt.stopImmediatePropagation();
            });

            $(window).on("resize", function () {
                $full.remove();
                $li.removeClass("active");
            });

        }

        $scope.get_profile_cover = function (profile_cover)
        {
            if (profile_cover)
            {
                return {'background-image': 'url(' + image_server_path + 'upload/profilebanner/1200x300/' + profile_cover + ')'};
            }
        }

        $scope.sendRequest = function (UserGUID)
        {
            var reqData = {FriendGUID: UserGUID}
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/addFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.users_list, function (value, key) {
                        if (value.UserGUID == UserGUID)
                        {
                            $scope.users_list[key].SentRequest = 1;
                        }
                    });
                    angular.forEach($scope.existing_users, function (value, key) {
                        if (value.UserGUID == UserGUID)
                        {
                            $scope.existing_users[key].SentRequest = 1;
                        }
                    });
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.rejectRequest = function (UserGUID)
        {
            var reqData = {FriendGUID: UserGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/rejectFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.users_list, function (value, key) {
                        if (value.UserGUID == UserGUID)
                        {
                            $scope.users_list[key].SentRequest = 0;
                        }
                    });
                    angular.forEach($scope.existing_users, function (value, key) {
                        if (value.UserGUID == UserGUID)
                        {
                            $scope.existing_users[key].SentRequest = 0;
                        }
                    });
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.follow = function (UserGUID, user, followStatus)
        {
            var reqData = {GUID: 1, MemberID: UserGUID, Type: "User"};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    
                    if(user) {
                        user.SentRequest = followStatus;
                    }
                    
                    
                    angular.forEach($scope.users_list, function (value, key) {
                        if (value.UserGUID == UserGUID)
                        {
                            if ($scope.users_list[key].IsFollowing == 0)
                            {
                                $scope.users_list[key].IsFollowing = 1;
                            } else
                            {
                                $scope.users_list[key].IsFollowing = 0;
                            }
                        }
                    });
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.follow_entity = function (ModuleID,ModuleEntityGUID)
        {
            var reqData = {GUID: 1, MemberID: ModuleEntityGUID, Type: "user"}
            if(ModuleID == '18')
            {
                reqData.Type = 'page';
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                response = successResp.data;
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.popular_user, function (value, key) {
                        if (value.ModuleEntityGUID == ModuleEntityGUID && value.ModuleID == ModuleID)
                        {
                            if ($scope.popular_user[key].IsFollowing == 0)
                            {
                                $scope.popular_user[key].IsFollowing = 1;
                            } else
                            {
                                $scope.popular_user[key].IsFollowing = 0;
                            }
                        }
                    });
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        };

        $scope.searchText = '';
        $scope.search_users = function ()
        {
            $scope.searchText = $('#searchText').val();
            if ($scope.searchText.length > 0)
            {
                $('.icon-search-gray').addClass('icon-removeclose');
            } else
            {
                $('.icon-search-gray').removeClass('icon-removeclose');
            }
        }

        $scope.clear_search = function ()
        {
            if ($('.icon-search-gray').hasClass('icon-removeclose'))
            {
                $('#searchText').val('');
                $scope.search_users();
            }
        }

        $scope.callBackLoginStatus = function ()
        {
            $scope.existing_users = [];
            $scope.social_login = 1;
            $scope.social_icon = 'icon-fb-title';
            $scope.source_type = 'facebook';
            fb_build_network.getUsersFbFriends();
        }

        $scope.source_type = 'native';
        $scope.social_icon = 'icon-fb-title';
        $scope.grow_network = function (social_type)
        {
            if (social_type == 'facebook')
            {
                if (typeof fb_build_network.getUsersFbFriends() !== 'undefined')
                {
                    $scope.existing_users = [];
                    $scope.social_login = 1;
                    $scope.social_icon = 'icon-fb-title';
                    $scope.source_type = 'facebook';
                    fb_build_network.getUsersFbFriends();
                } else
                {
                    fb_build_network.checkFbLoginStatus(1);
                }
            } else if (social_type == 'twitter')
            {
                $scope.social_icon = 'icon-tw-title';
                $scope.source_type = 'twitter';
                twt_build_network.get_twitter_friend();
            } else if (social_type == 'linkedin')
            {
                $scope.social_icon = 'icon-linkd-title';
                $scope.source_type = 'linkedin';
                linkedin_network.dolinkedinLogin();
            } else if (social_type == 'google')
            {
                $scope.existing_users = [];
                $scope.social_login = 1;
                $scope.social_icon = 'icon-gp-title';
                $scope.source_type = 'google';
                getFriendListGoogle();
            } else if (social_type == 'yahoo')
            {
                $scope.social_login = 1;
                $scope.social_icon = '';
                $scope.source_type = 'yahoo';
                window.open(site_url + "api/yahoo/yahoo_signin", 'Yahoo', 'width=500,height=500,scrollbars=yes');
            } else if (social_type == 'outlook')
            {
                $scope.social_login = 1;
                $scope.social_icon = '';
                $scope.source_type = 'outlook';
                network_signin.prototype.outlook_import();

            }
        }

        $scope.existing_users = [];
        $scope.new_users = [];

        $scope.invite_friend = function (type, id)
        {
            if (type == 'twitter')
            {
                twt_build_network.invite_non_OYH_friends(id);
            }

            if (type == 'google')
            {
                shareOnGoogle();
            }

            if (type == 'facebook')
            {
                fb_build_network.FbMultiSelectFriend();
            }

        }
        $scope.invite_friend_email = function (user_data,type)
        {
            user_data.invite_type=type;
            reqData = {user_data: user_data};
            WallService.CallPostApi(appInfo.serviceUrl + 'build_network/send_invitation', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $.each($scope.new_users, function (k) {
                        if (this.email == user_data.email) {
                            $scope.new_users[k].IsInvited = 1;
                        }
                    })
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });

        };
        $scope.invited_user = function (type, id)
        {
            angular.forEach($scope.new_users, function (val, key) {
                if (id == val['id'])
                {
                    $scope.new_users[key].IsInvited = 1;
                    $scope.$apply();
                }
            });
        }

        $scope.social_list = function (type, friends)
        {
            $scope.is_busy = 1;
            var ids = [];
            $scope.existing_users = [];
            $scope.new_users = [];
            var source_type = 0;
            if (type == 'Facebook')
            {
                source_type = 2;
            }

            if (type == 'Google')
            {
                source_type = 4;
            }

            if (type == 'Twitter')
            {
                source_type = 3;
            }
            if (type == 'Yahoo')
            {
                source_type = 5;
            }
            if (type == 'Outlook')
            {
                source_type = 6;
            }

            if (typeof friends !== 'undefined')
            {
                if (friends.length > 0)
                {
                    angular.forEach(friends, function (val, key) {
                        ids.push(val.id);
                    });
                    if (ids.length > 0)
                    {
                        var reqData = {social_type: source_type, friend_ids: ids};
                        WallService.CallPostApi(appInfo.serviceUrl + 'build_network/check_friends_list', reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200)
                            {
                                response['Data'] = $.map(response['Data'], function (v, i) {
                                    return [v];
                                });
                                if (response.Data.length > 0)
                                {
                                    angular.forEach(friends, function (val, key) {
                                        angular.forEach(response.Data, function (v, k) {
                                            if (v.id == val.id)
                                            {
                                                if (v.status == 1)
                                                {
                                                    $scope.user_page_no=1;
                                                    $scope.get_user_by_guid(v.user_guid);
                                                } else
                                                {
                                                    val['IsInvited'] = 0;
                                                    if (v.status == 3)
                                                    {
                                                        val['IsInvited'] = 1;
                                                    }
                                                    $scope.new_users.push(val);
                                                }
                                            }
                                        });
                                    });
                                }
                            }
                        }, function (error) {
                          // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    }
                }
            }
            $scope.social_login = true;
            $scope.is_busy = 0;
        }

        $scope.join = function (GroupGUID)
        {
            reqData = {GroupGUID: GroupGUID, UserGUID: LoggedInUserGUID};

            WallService.CallPostApi(appInfo.serviceUrl + 'group/join', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.get_new_group(GroupGUID);
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

    }]);





var network_signin = function () {
    network_sign = this;
};

$.extend(network_signin.prototype, {
    response_user_data: function (user_data) {
        console.log(user_data);
        user_data.map(function (repo) {
            repo.profile_image_url = AssetBaseUrl + 'img/profiles/user_default.jpg';
        });
        angular.element(document.getElementById('InviteFriendCtrl')).scope().social_list('Yahoo', user_data);
    },
    outlook_import: function () {
        WL.login({
            scope: ["wl.basic", "wl.contacts_emails"]
        }).then(function (response)
        {
            WL.api({
                path: "me/contacts",
                method: "GET"
            }).then(
                    function (response) {
                        var user_data = [];
                        var data = response.data;
                        //your response data with contacts 
                        $.each(data, function () {
                            profile_image_url= AssetBaseUrl + 'img/profiles/user_default.jpg';
                            user_temp = {id: this.emails.personal, name: this.name, email: this.emails.personal,profile_image_url:profile_image_url};
                            user_data.push(user_temp);
                        });
                        angular.element(document.getElementById('InviteFriendCtrl')).scope().social_list('Outlook', user_data);
                    },
                    function (responseFailed) {
                        console.log(responseFailed);
                    }
            );

        },
                function (responseFailed)
                {
                    console.log("Error signing in: " + responseFailed.error_description);
                });

    },
});