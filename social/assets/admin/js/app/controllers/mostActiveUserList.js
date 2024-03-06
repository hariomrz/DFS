// MostActiveUserList Controller
app.controller('MostActiveUserListCtrl', function ($scope, getData, $window, $rootScope) {    
    $scope.totalRecord = 0;
    $scope.filteredTodos = [],
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.user = {};
    $scope.notSelectCount = {};
        
    $scope.globalChecked = false;
    $scope.selectedUsers = {};
    $scope.selectedUsersIndex = {};
    $scope.confirmationMessage = '';
    
    $scope.mostActiveUsers = function () {
        intilizeTooltip();
        showLoader();
        $scope.selectedUsers = {};
        $scope.notSelectCount = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        
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
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        var reqUrl = reqData[1]
        //Call getUserlist in services.js file
        getData.getMostActiveUserlist(reqData).then(function (response) {
            $scope.listData = [];
            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records     
                $scope.totalRecord = $scope.noOfObj;
                
                //$scope.showButtonGroup = false;
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");

                //If no. of records greater then 0 then show
                $('.download_link,#selectallbox').show();
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#MostActiveUserListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="5"><div class="no-content text-center"><p>'+ThereIsNoUserToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjUsers: response.Data.results});
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                $('.download_link,#selectallbox').hide();
                $('#MostActiveUserListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="5"><div class="no-content text-center"><p>'+response.Message+'</p></div></td></tr>');
                $('.simple-pagination').hide();
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
    
    $scope.downloadMostActiveUsers = function () {
        showLoader();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.dateFilterText = $("#dateFilterText").text();
        
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
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            dateFilterText:$scope.dateFilterText,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call downloadMostActiveUsers in services.js file
        getData.downloadMostActiveUsers(reqData).then(function (response) {
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
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.mostActiveUsers();
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
            SortBy: $scope.sort_by,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        $scope.mostActiveUsers()
    });
    
    //Function for set user id
    $scope.SetUser = function (userlist) {
        $rootScope.$broadcast('getUserEvent', userlist);
        //console.warn(userlist);
        $('#hdnUserID').val(userlist.userid);
        $('#hdnUserGUID').val(userlist.userguid);
    };
    
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
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
        if($("#hdnSelectallPermission").val() == 1){
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
        
    };
    
    /**
     * SHow selected css
     * @param {type} user
     * @returns {undefined}
     */
    $scope.isSelected = function (user) {
        if (user.userid in $scope.selectedUsers) {
            return true;
        } else {
            $scope.showButtonGroup = false;
            $scope.globalChecked = false;
            return false;
        }        
    };
    
    $scope.isNotSelected = function (user) {
        if(user.statusid == '3' || user.statusid == '4'){
            //return true;
        } else {
            //return false;
        }        
    };

    $scope.globalCheckBox = function () {
        $scope.showButtonGroup = false;
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
    
    $scope.SetMultipleUserStatus = function (action) {
        var userstatus = '';
        if (action == "block") {
            userstatus = 4;
            $rootScope.confirmationMessage = Sure_Block+' ?';
        } else if (action == "delete"){
            userstatus = 3;
            $rootScope.confirmationMessage = Sure_Delete+' ?';
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
        })

    };
    
    $rootScope.updateUsersStatus = function () {
        var reqData = {
            users: $scope.statusUserIds,
            userstatus: $scope.userstatus,
            permission:'Analytics',
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');
        showLoader();
        getData.updateUsersStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
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
                $scope.mostActiveUsers();
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
    
    $scope.CommunicateMultipleUsers = function () {        
        
        var listData = $scope.selectedUsers;
        var userArr = [],arrLength;
        var userIds = '';
        var html = '';
        var htmlAll = '';        
        $("#dvmorelist").html(''); 
        $("#dvtipcontent").html('');
                
        htmlAll += "<i class=\"icon-tiparrow\">&nbsp;</i>";
        
        angular.forEach(listData, function (user, key) {
            userArr.push(user);
            userIds += key+',';
        });
        
        arrLength = userArr.length;
        
        for (var i = 0; i < arrLength; i++) {
            if (i < 3) {
                html += "<a href=\"javascript:void(0);\" class=\"name-tag\"><span>" + userArr[i].username + "</span></a>";                    
            }
            if (i >= 3) {
                htmlAll += "<a href=\"javascript:void(0);\">" + userArr[i].username + "</a>";
            }
        }
        
        if (arrLength > 3) {
            html += "<a href=\"javascript:void(0);\" class=\"name-tag morelist\" data-tip=\"tooltip\"><span>+ " + parseInt(arrLength - 3) + "  More </span></a>";
        }
        
        $("#dvmorelist").append(html);
        $("#dvtipcontent").append(htmlAll);
        $("#hdnUsersId").val(userIds);

        $("#subject").val("");
        $("#multipleComu").val("");
        
        openPopDiv('communicateMultiple', 'bounceInDown');
        communicateMorelist();
    };
    
});