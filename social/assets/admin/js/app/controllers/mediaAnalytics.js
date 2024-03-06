// UserList Controller
app.controller('MediaAnalyticsCtrl', function ($scope, $rootScope, mediaAnalyticData, $window) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $rootScope.totalMediaCount = 0;
    $scope.mediaReport = {};
    $scope.currentUserRoleId = {};
    $scope.currentUserStatusId = {}
    $scope.notSelectCount = {};
        
    $scope.globalChecked = false;
    $scope.selectedUsers = {};
    $scope.selectedUsersIndex = {};
    $scope.confirmationMessage = '';
    
    $scope.mediaAnalytics = function () {
        intilizeTooltip();
        showLoader();
        $scope.selectedUsers = {};
        $scope.notSelectCount = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.searchKey = '';
        if ($('#searchField').val()) {
            $scope.searchKey = $('#searchField').val();
            $('#mediaAnalyticSearch').addClass('selected');
        }
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getMediaAnalyticsList in services.js file
        mediaAnalyticData.getMediaAnalyticsList(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200) {
                $scope.noOfObj = response.Data.total_records
                $rootScope.totalMediaCount = $scope.totalRecord = $scope.noOfObj;

                //$scope.showButtonGroup = false;
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");

                //If no. of records greater then 0 then show
                $('.download_link,#selectallbox').show();
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#MediaAnalyticsCtrl table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>'+ThereIsNoRecordToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                //Push data into Controller in view file
                $scope.listData.push({ObjUsers: response.Data.results});
            
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
            hideLoader();            
            
        }), function (error) {
            hideLoader();
        }
    };
    
    $scope.mediaAnalyticsReport = function () {
        showLoader();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        
        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getMediaAnalyticsList in services.js file
        mediaAnalyticData.getMediaAnalyticsReport(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.mediaReport = response.Data.media_report;
                //console.log($scope.mediaReport);
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
            hideLoader();
        }), function (error) {
            hideLoader();
        }
    };
    
    $scope.downloadMediaAnalyticsData = function () {
        showLoader();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.dateFilterText = $("#dateFilterText").text();
        $scope.searchKey = '';
        if ($('#searchField').val()) {
            $scope.searchKey = $('#searchField').val();
        }
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            dateFilterText:$scope.dateFilterText,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getMediaAnalyticsList in services.js file
        mediaAnalyticData.downloadMediaAnalyticsData(reqData).then(function (response) {
            if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.csv_url){
                window.location.href = response.csv_url;
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }
            hideLoader();
        }), function (error) {
            hideLoader();
        }
    };
    
    //Apply Sort by and mamke request data
    $scope.sortBY = function (column_id) {
        if($("table.users-table #noresult_td").length == 0)
        {
            $(".shortdiv").children('.icon-arrowshort').addClass('hide');
            $(".shortdiv").parents('.ui-sort').removeClass('selected');
            if($scope.reverseSort == true){
                $("#"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
            }else{
                $("#"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedUp').addClass('sortedDown').children('.icon-arrowshort').removeClass('hide');                
            }
            
            reqData = {
                Begin: $scope.currentPage,
                End: $scope.numPerPage,
                StartDate: $scope.startDate,
                EndDate: $scope.endDate,
                SearchKey: $scope.searchKey,
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            $scope.mediaAnalytics();
        }
    };
    //Get no. of pages for data
    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };
    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {
        begins = (($scope.currentPage - 1) * $scope.numPerPage)
        reqData = {
            Begin: begins,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            SortBy: $scope.sort_by,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        $scope.mediaAnalytics();
    });
    
    //Function for set class for each TR
    $scope.cls = function (idx) {onclick
        return idx % 2 === 0 ? 'odd' : 'even';
    };
    
    //Function for search users on media analytics page
    $scope.searchMediaUsers = function(){
        if($('#searchField').val() != ''){
            $scope.mediaAnalytics();
        }
    };
    
    //Function for set media detail
    $scope.SetUser = function (medialist) {
        $rootScope.currentUserName = medialist.username;
        $scope.currentUserRoleId = medialist.userroleid.split(',');
        $scope.currentUserStatusId = medialist.statusid;
        $scope.userid = medialist.userid;
        $('#hdnUserID').val(medialist.userid);
        $('#hdnUserGUID').val(medialist.userguid);
    };
    
    //Function for view user profile of a particular user
    $scope.viewUserProfile = function (userguid) {
        //If UserGUID is Undefined
        if (typeof userguid === 'undefined') {
            userguid = $('#hdnUserGUID').val();
        }
        //Useful for set breadcrumb
        $window.location.href = base_url + 'admin/users/user_profle/' + userguid;
    };
    
    /**
     * Set li selected
     * @param {type} user
     * @returns {undefined}
     */
    $scope.selectCategory = function (user) {
        var currentUserRoleIdArr = user.userroleid.split(',');
        if(user.statusid == '3' || user.statusid == '4' || currentUserRoleIdArr.indexOf(''+admin_role_id+'')>-1 || $("#hdnSelectallPermission").val() == 0){
            if (user.userid in $scope.notSelectCount) {
                delete $scope.notSelectCount[user.userid];
            } else {
                $scope.notSelectCount[user.userid] = user;
            }            
        }else{
            if (user.userid in $scope.selectedUsers) {
                delete $scope.selectedUsers[user.userid];
            } else {
                $scope.selectedUsers[user.userid] = user;
            }
            if (Object.keys($scope.selectedUsers).length > 0) {
                setTimeout(function(){ $scope.globalChecked == true; }, 1);
                $('#ItemCounter').fadeIn();            
            } else {
                $scope.globalChecked = false;
                $('#ItemCounter').fadeOut();
            }    


            setTimeout(function(){
                if($(".registered-user tr.selected").length == ($scope.listData[0].ObjUsers.length - $(".registered-user tr.notselected").length)){
                    setTimeout(function(){ $scope.globalChecked = true; }, 1);
                    $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
                }else{
                    $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                }
            }, 1);

            var ItemCount = Object.keys($scope.selectedUsers).length;
            var txtCount = ItemsSelected;
            if(ItemCount == 1)
                txtCount = ItemSelected;
            $('#ItemCounter .counter').html(ItemCount+txtCount);
            //console.log($scope.selectedUsers);
        }
        
    }
    
    /**
     * SHow selected css
     * @param {type} user
     * @returns {undefined}
     */
    $scope.isSelected = function (user) {
        if (user.userid in $scope.selectedUsers) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;
        }        
    };
    
    $scope.isNotSelected = function (user) {
        var currentUserRoleIdArr = user.userroleid.split(',');
        if(user.statusid == '3' || user.statusid == '4'  || currentUserRoleIdArr.indexOf(''+admin_role_id+'')>-1){
            return true;
        } else {
            return false;
        }        
    };

    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;
        if ($scope.globalChecked) {
            $scope.selectedUsers = [];
            var listData = $scope.listData[0].ObjUsers;
            angular.forEach(listData, function (val, key) {
                if (typeof $scope.selectedUsers[key]) {
                    $scope.selectCategory(val, key);
                }
            });
        } else {
            angular.forEach($scope.selectedUsers, function (val, key) {
                $scope.selectCategory(val, key);
            });
        }       
    };
    
    $scope.SetSingleUserStatus = function (action) {
        var userstatus = '';
        if (action == "block") {
            userstatus = 4;
            $scope.confirmationMessage = Sure_Block+' ?';
        }else if (action == "delete") {
            userstatus = 3;
            $scope.confirmationMessage = Sure_Delete+' ?';
        }
        
        $scope.statusUserIds = [];
        $scope.statusUserIds.push($scope.userid);
        openPopDiv('confirmeMultipleUserPopup', 'bounceInDown');
        $scope.userstatus = userstatus;
        $scope.useraction = action;
        
    };
    
    $scope.SetMultipleUserStatus = function (action) {
        var userstatus = '';
        if (action == "block") {
            userstatus = 4;
            $scope.confirmationMessage = Sure_Block+' ?';
        } else if (action == "delete"){
            userstatus = 3;
            $scope.confirmationMessage = Sure_Delete+' ?';
        }
        openPopDiv('confirmeMultipleUserPopup', 'bounceInDown');
        $scope.statusUserIds = {};
        $scope.indexToUpdate = {};
        $scope.statusUserIds = Object.keys($scope.selectedUsers);//$scope.selectedMedia;
        $scope.userstatus = userstatus;
        $scope.useraction = action;
        
        angular.forEach($scope.selectedUsers, function (user, key) {
            var mIndex = $scope.listData[0].ObjUsers.indexOf(user);
            $scope.indexToUpdate[mIndex] = mIndex;
        });
    };
    
    $scope.updateUsersStatus = function () {
        var reqData = {
            users: $scope.statusUserIds,
            userstatus: $scope.userstatus,
            permission:'Analytics',
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');
        showLoader();
        mediaAnalyticData.updateUsersStatus(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                //Reset all
                $scope.indexToUpdate = {};
                $scope.statusUserIds = {};
                $scope.globalChecked = true;
                $scope.globalCheckBox();
                $scope.selectedUsers = {};
                $scope.selectedUsersIndex = {};
                
                var msg =  $scope.useraction;            
                msg = ucwords(msg);
                ShowSuccessMsg(msg+" successfully.");
                
                $scope.mediaAnalytics();
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
            
            hideLoader();
            
        }), function (error) {
            hideLoader();
        }
    };
        
});