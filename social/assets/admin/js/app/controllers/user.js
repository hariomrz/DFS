// User Controller for Profile Page
app.controller('userCtrl', function ($scope, userData, $rootScope, $window) {
    $rootScope.overviewTabLoad = '1';
    $rootScope.communicateTabLoad = '0';
    $rootScope.mediaTabLoad = '0';
    $scope.user = {};
    
    $scope.ChangeSingleUserStatus = function(PopupID,Status){
        var UserID = $("#hdnUserID").val();
        var Status = Status;
        var status_action = Status;
        if(PopupID == "approve_popup")
            status_action = 1;
        
        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $('.button span').addClass('loading');
        
        var reqData = {
            UserID: UserID, //$scope.currentPage,
            Status: Status,
            AdminLoginSessionKey: AdminLoginSessionKey,
            status_action: status_action
        };
        userData.ChangeSingleUserStatus(reqData).then(function (response) {
            HideInformationMessage('change_user_status');
            if(response.ResponseCode == 200)
            {
                var message = '';
                if (Status == 3) {
                    message = "Deleted successfully.";
                } else if (Status == 4) {
                    message = "Blocked successfully.";
                }
                else if (Status == 2) {
                    message = "Approved successfully.";
                }
                else if (Status == 2) {
                    message = "Unblocked successfully.";
                }
                closePopDiv(PopupID,'bounceOutUp');

                ShowSuccessMsg(message);
                setTimeout(function () {
                    location.reload();
                }, 1500);
            }else if(response.ResponseCode == 598){
                closePopDiv(PopupID,'bounceOutUp');
                $('.button span').removeClass('loading');
                //Show error message
                PermissionError(response.Message);
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                closePopDiv(PopupID,'bounceOutUp');
                $('.button span').removeClass('loading');
            }
        }), function (error) {
            ShowWentWrongError();
        }
    };
    
    $scope.getUser = function () {
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.userID = $('#hdnUserID').val();
        
        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            UserID: $scope.userID,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        userData.getUser(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                $scope.user = response.Data;
                if($rootScope.tabSelected != "media"){
                    $rootScope.$broadcast('getUserEvent', response.Data);
                }
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
        });
    };
    //Function for set StatusClass, Tooltip on profile page
    $scope.statusClass = function (id) {
        var cls = 'verified';
        switch (id) {
            case '1':
                cls = 'pending';
                break;
            case '4':
                cls = 'blocked';
                break;
            case '2':
            case '5':
                cls = 'verified';
                break;
            case '3':
                cls = 'deleted';
                break;
            default :
                cls = 'verified';
        }
        return cls;
    };
    
    $scope.statusTitle = function (id) {
        var title = 'Verified';
        switch (id) {
            case '1':
                title = 'Pending';
                break;
            case '4':
                title = 'Blocked';
                break;
            case '2':
            case '5':
                title = 'Verified';
                break;
            case '3':
                title = 'Deleted';
                break;
            default :
                title = 'Verified';
        }
        return title;
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
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        //Call autoLoginUser in services.js file
        userData.autoLoginUser(reqData).then(function (response) {
            
            if (response.ResponseCode == 200) {
                $window.open(base_url + 'usersite/signin','_blank');
                //$window.location.href = base_url + 'usersite/signin';
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);                
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }

        }), function (error) {
            hideLoader();
        }
    }

    
});

app.controller('usrTabController', function ($scope, $rootScope) {
    $scope.tabSelected = null;
    
    $scope.loadUserProfileTab = function(tabl_id){
        if(tabl_id != ""){
            setTimeout(function(){
                $("#"+tabl_id).trigger("click");
            }, 500);
        }
    };
    
    $scope.selectTab = function (tabSelected) {
        changeTabs(tabSelected);
        $rootScope.tabSelected = tabSelected;
        
        if($rootScope.tabSelected == "overview" && $rootScope.overviewTabLoad == 0){
            $rootScope.overviewTabLoad = 1;
        }
        
        if($rootScope.tabSelected == "communicate" && $rootScope.communicateTabLoad == 0){
            $rootScope.communicateTabLoad = 1;
            if($("#allowCommunicationTab").val() != 0){
                angular.element(document.getElementById('communicationTabCtrl')).scope().userCommunication();
            }
        }
        
        if($rootScope.tabSelected == "media" && $rootScope.mediaTabLoad == 0){
            $rootScope.mediaTabLoad = 1;
            angular.element(document.getElementById('mediaCtrl')).scope().getMediaSummary();
            angular.element(document.getElementById('mediaCtrl')).scope().getSearchBox();
        }
        $rootScope.$emit('getTabEvent', tabSelected);
    }
});