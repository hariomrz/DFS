
!(function (app, angular) {
    //app.controller('crmUserListExtraCtrl', crmUserListExtraCtrl);


    app.factory('crmUserListExtraCtrl', crmUserListExtraCtrl);



    function crmUserListExtraCtrl($http, $q, $rootScope, $window, apiService, CommonService, getData) {

        return {
            crmExtendScope: crmExtendScope
        };

        function crmExtendScope($scope) {





            $('#description').summernote();
            $('#communication_description').summernote();


            $scope.openPopupUserOption = function (Status) {
                var UserId = $("#hdnUserID").val();

                switch (Status) {
                    case 1:
                        $("#hdnChangeStatus").val(2);
                        openPopDiv('approve_popup', 'bounceInDown');
                        break;
                    case 23:
                        $("#hdnChangeStatus").val(2);
                        openPopDiv('suspended_popup', 'bounceInDown');
                        break;

                    case 2:
                        if (Status == 2 && $("#hdnUserStatus").val() == 1) {
                            openPopDiv('block_popup', 'bounceInDown');
                        }

                        if (Status == 2 && $("#hdnUserStatus").val() == 4) {
                            openPopDiv('unblock_popup', 'bounceInDown');
                        }
                        break;

                    case 3:
                        openPopDiv('delete_popup', 'bounceInDown');
                        break;

                    case 4:
                        openPopDiv('block_popup', 'bounceInDown');
                        break;

                    case 5:
                        openPopDiv('change_user_password', 'bounceInDown');
                        break;
                }

            }

            $scope.deleteUser = function () {
                var user = $scope.DeletingUser;
                var selectedUserIds = [];

                if (user.UserID) {
                    selectedUserIds.push(user.UserID);
                } else {
                    var nodeList = $scope.DeletingUsers;
                    angular.forEach(nodeList, function (node) {
                        selectedUserIds.push(node.value);
                    });
                }


                var reqData = {
                    DeleteAll: $scope.allUserSelected,
                    UserId: selectedUserIds,
                    Status: 3,
                    AdminLoginSessionKey: adminLoginSessionKey
                };
                $http.post(base_url + 'admin_api/users/change_status', reqData).success(function (data) {
                    angular.forEach(selectedUserIds, function (userId) {
                        delete $scope.userList['pre' + userId];
                    })
                    $scope.DeletingUser = {};
                    $scope.DeletingUsers = [];
                    $('#delete_popup_confirm_box').modal('hide');

                }).error(function (data) {
                    $scope.DeletingUser = {};
                    $scope.DeletingUsers = [];
                    ShowWentWrongError();
                });
            }

            //Function for set user id
            $scope.SetUser = function (userlist) {

                userlist.userid = userlist.UserID;
                userlist.username = userlist.Name;
                userlist.statusid = userlist.StatusID;
                userlist.userguid = userlist.UserGUID

                $scope.CurrentUserID = userlist.userid;
                $rootScope.currentUserName = userlist.username;
                $scope.currentUserRoleId = userlist.userroleid.split(',');
                $scope.currentUserStatusId = userlist.statusid;

                $scope.DeletingUserTxt = userlist.Name;

                $scope.fabout = userlist.UserWallStatus;

                $scope.selectedUser = userlist;

                $rootScope.$broadcast('getUserEvent', userlist);

                //console.warn(userlist);
                $('#hdnUserID').val(userlist.userid);
                $('#hdnUserGUID').val(userlist.userguid);
                
                $scope.SetUserStatus(userlist.statusid);
                
            }
            
            $scope.resetUserName = function() {
                $rootScope.currentUserName = '';
            }


            $scope.ChangeStatus = function (PopupID) {
                var UserId = $("#hdnUserID").val();
                var Status = $("#hdnChangeStatus").val();
                /* Send AdminLoginSessionKey in every request */
                var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
                $('.button span').addClass('loading');

                var reqData = {
                    UserId: UserId, //$scope.currentPage,
                    Status: Status,
                    AdminLoginSessionKey: AdminLoginSessionKey
                };
                var crmRqObj = $scope.getCrmSelectedUsersRequest();
                if (Object.keys(crmRqObj).length) {
                    crmRqObj.Status = Status;
                    reqData = crmRqObj;
                }

                //console.log(reqData);return;

                getData.ChangeStatus(reqData).then(function (response) {
                    HideInformationMessage('user_change_status');
                    if (response.ResponseCode == 200) {
                        $scope.refreshUserList();
                        $('.button span').removeClass('loading');
                        closeCrmModel(PopupID)
                        ShowSuccessMsg("Status change successfully.");
                    } else if (response.ResponseCode == 598) {
                        closeCrmModel(PopupID)
                        $('.button span').removeClass('loading');
                        //Show error message
                        PermissionError(response.Message);
                    } else if (checkApiResponseError(response)) {
                        ShowWentWrongError();
                        closeCrmModel(PopupID)
                        $('.button span').removeClass('loading');
                    } else {
                        closeCrmModel(PopupID)
                        $('.button span').removeClass('loading');
                    }
                }), function (error) {
                    ShowWentWrongError();
                }
            };

            $scope.sendPush=function(){

                alert('sending')
            }

            $scope.CommunicateMultipleUsers = function () {

                var listData = $scope.getSelectedUsers(0);
                var userArr = [], arrLength;
                var userIds = '';
                var html = '';
                var htmlAll = '';
                $("#dvmorelist").html('');
                $("#dvtipcontent").html('');

                htmlAll += "<i class=\"icon-tiparrow\">&nbsp;</i>";

                angular.forEach(listData, function (user, key) {
                    userArr.push(user);
                    userIds += key + ',';
                });

                arrLength = userArr.length;

                for (var i = 0; i < arrLength; i++) {
                    if (i < 3) {
                        html += "<a href=\"javascript:void(0);\" class=\"name-tag\"><span>" + userArr[i].Name + "</span></a>";
                    }
                    if (i >= 3) {
                        htmlAll += "<a href=\"javascript:void(0);\">" + userArr[i].Name + "</a>";
                    }
                }

                var totalSelectedUsers = 0;
                if ($scope.allUserSelected) {
                    totalSelectedUsers = $scope.getSelectedUsersCount();
                }

                if (arrLength > 3 || totalSelectedUsers > 3) {
                    var htmlUserCount = ($scope.allUserSelected) ? (totalSelectedUsers - 3) : parseInt(arrLength - 3);

                    html += "<a href=\"javascript:void(0);\" class=\"name-tag morelist\" data-tip=\"tooltip\"><span>+ " + htmlUserCount + "  More </span></a>";
                }

                $("#dvmorelist").append(html);
                $("#dvtipcontent").append(htmlAll);
                $("#hdnUsersId").val(userIds);

                $("#subject").val("");
                $("#multipleComu").val("");

                //openPopDiv('communicateMultiple', 'bounceInDown');
                
                $('#communicateMultiple').modal('show');
                
                communicateMorelist();
            };


            //Function for view user profile of a particular user
            $scope.autoLoginUser = function (userid) {

                //If UserID is Undefined
                if (typeof userid === 'undefined') {
                    userid = $('#hdnUserID').val();
                }

                /* Send AdminLoginSessionKey in every request */
                $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

                var reqData = {
                    userid: userid,
                    //Send AdminLoginSessionKey
                    AdminLoginSessionKey: $scope.AdminLoginSessionKey
                };
                
                
                var postData = {UserID : userid, LoginSessionKey : $('#AdminLoginSessionKey').val()};
                $http.post(base_url + 'signup/switchProfile', postData).success(function(response) {
                    window.top.location = base_url+'dashboard';
                });
                
                return;


                //Call autoLoginUser in services.js file
                getData.autoLoginUser(reqData).then(function (response) {

                    if (response.ResponseCode == 200) {
                        $window.open(base_url + 'usersite/signin', '_blank');
                        //$window.location.href = base_url + 'usersite/signin';
                    } else if (response.ResponseCode == 517) {
                        redirectToBlockedIP();
                    } else if (response.ResponseCode == 598) {
                        //Show error message
                        PermissionError(response.Message);
                    } else if (checkApiResponseError(response)) {
                        ShowWentWrongError();
                    } else {
                        ShowErrorMsg(response.Message);
                    }

                }), function (error) {
                    hideLoader();
                }
            }




            function init_userlocation()
            {
                currentLocationInitialize('hometown');
            }

            function currentLocationInitialize(txtId) {
                var options = {
                    types: ['(cities)']
                };

                var input = document.getElementById(txtId);
                if (txtId == 'hometown') {
                    currentLocation2 = new google.maps.places.Autocomplete(input, options);
                    google.maps.event.addListener(currentLocation2, 'place_changed', function () {
                        currentLocationFillInPrepare(txtId);
                    });
                } else {
                    currentLocation = new google.maps.places.Autocomplete(input, options);
                    google.maps.event.addListener(currentLocation, 'place_changed', function () {
                        currentLocationFillInPrepare(txtId);
                    });
                }
            }

            //Pie chart start
            function update_chart()
            {
                google.charts.load('current', {'packages': ['corechart']});
                google.charts.setOnLoadCallback(drawChart);
                function drawChart()
                {
                    var ChartData = [];
                    angular.forEach($scope.InterestPercentage, function (val, key) {
                        ChartData.push([val.Name, val.Percentage]);
                    });

                    //console.log($scope.InterestPercentage);
                    /*ChartData = [
                     ['Swimming', 5],
                     ['Music', 20],
                     ['Travel', 10],
                     ['Technology', 65] 
                     ];*/
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Topping');
                    data.addColumn('number', 'Slices');
                    data.addRows(ChartData);
                    var options = {'title': '',
                        'width': 700,
                        'height': 180,
                        legend: {position: 'left'},
                        pieSliceText: "none",
                        series: {
                            1: {pointShape: 'square'}
                        }
                    };
                    var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                    chart.draw(data, options);
                }
            }

            $scope.setShowActivity = function (value)
            {
                $scope.showActivity = value;
            }

            $scope.close_detail_box = function () {
                $scope.editDetails = 0;
                $scope.editNetworkDetail = 0;
                $scope.editPersonalDetail = 0;
                $scope.updateProfilePic = 0;
            }

            $scope.openwindow = function (user_id)
            {
                window.open(base_url + 'admin/users/print_persona/' + user_id);
            }

            function SetUserStatus(UserStatus) {
                
                UserStatus = parseInt(UserStatus);
                
                $("#hdnUserStatus").val(UserStatus);
                $('#ItemCounter').fadeOut();

                if (UserStatus == 2) {
                    

                    $("#ActionApprove").hide();
                    $("#ActionSuspended").hide();
                    $("#ActionUnblock").hide();
                    $("#ActionDelete").show();
                    $("#ActionLoginThis").show();
                    $("#ActionViewProfile").show();
                    $("#ActionBlock").show();
                    $("#ActionCommunicate").show();
                    $("#ActionSendEmail").hide();
                    $("#ActionChangePwd").show();

                    $("#liregister").addClass("selected");
                    $("#lidelelte").removeClass("selected");
                    $("#liblock").removeClass("selected");
                    $("#lipending").removeClass("selected");
                    $("#lisuspended").removeClass("selected");

                } else if (UserStatus == 3) {
                    

                    $("#ActionApprove").hide();
                    $("#ActionSuspended").hide();
                    $("#ActionUnblock").hide();
                    $("#ActionDelete").hide();
                    $("#ActionLoginThis").hide();
                    $("#ActionViewProfile").show();
                    $("#ActionBlock").hide();
                    $("#ActionCommunicate").show();
                    $("#ActionSendEmail").hide();
                    $("#ActionChangePwd").show();

                    $("#liregister").removeClass("selected");
                    $("#lidelelte").addClass("selected");
                    $("#liblock").removeClass("selected");
                    $("#lipending").removeClass("selected");
                    $("#lisuspended").removeClass("selected");
                } else if (UserStatus == 4) {
                    

                    $("#ActionApprove").hide();
                    $("#ActionSuspended").hide();
                    $("#ActionUnblock").show();
                    $("#ActionDelete").show();
                    $("#ActionLoginThis").hide();
                    $("#ActionViewProfile").show();
                    $("#ActionBlock").hide();
                    $("#ActionCommunicate").show();
                    $("#ActionSendEmail").hide();
                    $("#ActionChangePwd").show();

                    $("#liregister").removeClass("selected");
                    $("#lidelelte").removeClass("selected");
                    $("#liblock").addClass("selected");
                    $("#lipending").removeClass("selected");
                    $("#lisuspended").removeClass("selected");

                } else if (UserStatus == 1) {
                    

                    $("#ActionApprove").show();
                    $("#ActionSuspended").hide();
                    $("#ActionUnblock").hide();
                    $("#ActionDelete").show();
                    $("#ActionLoginThis").hide();
                    $("#ActionViewProfile").show();
                    $("#ActionBlock").hide();
                    $("#ActionCommunicate").show();
                    $("#ActionSendEmail").show();
                    $("#ActionChangePwd").show();

                    $("#liregister").removeClass("selected");
                    $("#lidelelte").removeClass("selected");
                    $("#liblock").removeClass("selected");
                    $("#lipending").addClass("selected");
                } else if (UserStatus == 23) {
                    

                    $("#ActionApprove").hide();
                    $("#ActionSuspended").show();
                    $("#ActionUnblock").hide();
                    $("#ActionDelete").show();
                    $("#ActionLoginThis").hide();
                    $("#ActionViewProfile").show();
                    $("#ActionBlock").hide();
                    $("#ActionCommunicate").show();
                    $("#ActionSendEmail").show();
                    $("#ActionChangePwd").show();

                    $("#liregister").removeClass("selected");
                    $("#lidelelte").removeClass("selected");
                    $("#liblock").removeClass("selected");
                    $("#lipending").removeClass("selected");
                    $("#lisuspended").addClass("selected");
                }

            }

            $scope.SetUserStatus = SetUserStatus;


            //Function for view user profile of a particular user
            $scope.viewUserProfile = function (userguid) {
                //If UserGUID is Undefined
                if (typeof userguid === 'undefined') {
                    userguid = $('#hdnUserGUID').val();
                }
                //Useful for set breadcrumb
                $window.location.href = base_url + 'admin/users/user_profle/' + userguid;
            };








            $scope.getUserPersonaDetail = function (user_id, user_guid, user_name)
            {
                ///showUserPersona
                angular.element(document.getElementById('UserListCtrl')).scope().showUserPersona(user_id, user_guid, user_name);
            }







        }
    }

    crmUserListExtraCtrl.$inject = ['$http', '$q', '$rootScope', '$window', 'apiService', 'CommonService', 'getData'];

    app.directive('ageValidate', function ($parse) {
        return {
            require: 'ngModel',

            link: function (scope, elm, attrs) {
                elm.bind('keypress', function (e) {
                    var keyCode = e.which || e.charCode || e.keyCode;
                    var char = String.fromCharCode(e.which || e.charCode || e.keyCode);

                    var checkChar = parseInt(char);
                    if (checkChar === 0 && keyCode != 8) {
                        return;
                    }

                    if (!checkChar && keyCode != 8) {
                        e.preventDefault();
                        return false;
                    }

                });
            }
        }
    });


})(app, angular);





function SetStatusCrmModel(Status) {

    //1-waitingforApproval,
    //2-unblock,approve,
    //3-delete,
    //4-block
    //5-Change password
    Status = parseInt(Status);
    
    $("#hdnChangeStatus").val(Status);
    var UserId = $("#hdnUserID").val();

    switch (Status) {
        case 1:
            $("#hdnChangeStatus").val(2);
            //openPopDiv('approve_popup', 'bounceInDown');
            $('#approve_popup').modal('show');
            break;
        case 23:
            $("#hdnChangeStatus").val(2);
            //openPopDiv('suspended_popup', 'bounceInDown');
            $('#suspended_popup').modal('show');
            break;

        case 2:
            if (Status == 2 && $("#hdnUserStatus").val() == 1) {
                //openPopDiv('block_popup', 'bounceInDown');
                $('#block_popup').modal('show');
            }

            if (Status == 2 && $("#hdnUserStatus").val() == 4) {
                //openPopDiv('unblock_popup', 'bounceInDown');
                $('#unblock_popup').modal('show');
            }
            break;

        case 3:
            //openPopDiv('delete_popup', 'bounceInDown');
            $('#delete_popup').modal('show');
            break;

        case 4:
            //openPopDiv('block_popup', 'bounceInDown');
            $('#block_popup').modal('show');
            break;

        case 5:
            openPopDiv('change_user_password', 'bounceInDown');
            //$('#change_user_password').modal('show');
            break;
        case 6:
            openPopDiv('change_user_password', 'bounceInDown');
            //$('#change_user_password').modal('show');
            break;
        case 7:
            $('#ward_feature').modal('show'); 
            setTimeout(function(){
                $('#ward_feature_chk').prop('checked', false);
                $('#fabout').val('');        
            }, 100);            
            break;  
        case 8:
            $('#vip_user').modal('show'); 
            setTimeout(function(){
                $('#fabout').val('');        
            }, 100);            
            break; 
        case 9:
            $('#association_user').modal('show'); 
            setTimeout(function(){
                $('#fabout').val('');        
            }, 100);            
            break; 
    }
}

function closeCrmModel(popupId) {
    $('#'+popupId).modal('hide');
    //$('body').removeClass('modal-open');
    //$('.modal-backdrop').remove();
}
