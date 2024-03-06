// RoleUsers Controller
app.controller('RoleUsersCtrl', function ($scope, $timeout, getRoleUserData) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.totalRoleUsers = 0;
    $scope.showError = false;
    $scope.errorMessage = null;
    $scope.rolename = '';
    $scope.selectedRole = 1;
    
    $scope.globalChecked = false;
    $scope.selectedUsers = {};
    $scope.currentUserRoleId = {};

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
    $scope.getRolesOption = function(){
        var reqData = {
            AdminLoginSessionKey:$scope.AdminLoginSessionKey
        };
        
        //Call getRoles in services.js file
        getRoleUserData.getRoles(reqData).then(function (response) {
            $scope.roleOptData = [];
            if(response.ResponseCode == 200){
                
                //Push data into Controller in view file
                $scope.roleOptData = response.Data;
                $scope.selectedRole = $scope.roleOptData[0];                
                $('#PermissionRole').trigger('chosen:updated');
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
    
    $scope.selectChangeEvent = function(){
        $scope.roleUsersList();
    };
    
    $scope.SetUserDetail = function(userdata){
        $scope.userDetail = userdata;
        $scope.currentUserRoleId = userdata.RoleID;
    };
    
    $scope.getRoles = function(){
        var reqData = {
            AdminLoginSessionKey:$scope.AdminLoginSessionKey
        };
        
        //Call getRoles in services.js file
        getRoleUserData.getRoles(reqData).then(function (response) {
            $scope.roleListData = [];
            if(response.ResponseCode == 200){
                
                //Push data into Controller in view file
                $scope.roleListData = response.Data;
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
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
    
    $scope.roleUsersList = function () {
        showLoader();
        $scope.globalChecked = false;
        $scope.selectedUsers = {};
        $('#ItemCounter').fadeOut();
        
        var RoleID = $("#PermissionRole").val();
        if(RoleID == null || RoleID == '')
            RoleID = $scope.selectedRole;
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        var reqData = {
            Begin: begins,
            End: $scope.numPerPage,
            RoleID : RoleID,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getRoleUsersList in services.js file
        getRoleUserData.getRoleUsersList(reqData).then(function (response) {
            $scope.listData = [];
            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $scope.totalRoleUsers = $scope.totalRecord = $scope.noOfObj;

                //If no. of records greater then 0 then show
                $('#noresult_td').remove();
                $('.simple-pagination,#selectallbox').show();
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#RoleUsersCtrl table.rolelisttable>tbody').append('<tr id="noresult_td"><td colspan="5"><div class="no-content text-center"><p>'+ThereIsNoUserToShow+'</p></div></td></tr>');
                    $('.simple-pagination,#selectallbox').hide();
                }
                
                if(RoleID == admin_role_id){
                    $('#selectallbox').hide();
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
    
    $scope.selectCategory = function (user) {
        if(user.RoleID != admin_role_id){
            if (user.UserID in $scope.selectedUsers) {
                delete $scope.selectedUsers[user.UserID];
            } else {
                $scope.selectedUsers[user.UserID] = user;            
            }
            if (Object.keys($scope.selectedUsers).length > 0) {
                setTimeout(function(){ $scope.globalChecked == true; }, 1);
                $('#ItemCounter').fadeIn();            
            } else {
                $scope.showButtonGroup = false;
                $('#ItemCounter').fadeOut();
            }

            setTimeout(function(){
                if($(".registered-user tr.selected").length == $scope.listData[0].ObjUsers.length){
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
        }
    }
    
    /**
     * SHow selected css
     * @param {type} user
     * @returns {undefined}
     */
    $scope.isSelected = function (user) {
        if (user.UserID in $scope.selectedUsers) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;            
        }        
    };

    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;        
        if ($scope.globalChecked) {
            $scope.selectedUsers = {};
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
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }        
        $scope.roleUsersList();
    });
    
    $scope.SaveRoleUser = function () {
        var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
        var FirstName = $('#FirstName').val();
        var LastName = $('#LastName').val();
        var Email = $('#Email').val();
        var Username = $('#Username').val();
        var hdnUserID = $('#hdnUserID').val();
        
        var RoleArr = new Array()
        $("#RoleListOpt input[type='checkbox']:checked").each(function () {
            RoleArr.push({ RoleID: $(this).val() });
        });
                
        $scope.Error = false;
        $scope.errorFName = null;
        $scope.errorLName = null;
        $scope.errorEmail = null;
        $scope.errorUsername = null;
        $scope.errorRole = null;
        if (FirstName.length <= 0) {
            $scope.Error = true;
            $scope.errorFName = 'Please enter first name.';
        }
        if (LastName.length <= 0) {
            $scope.Error = true;
            $scope.errorLName = 'Please enter last name.';
        }
        if (Email.length <= 0) {
            $scope.Error = true;
            $scope.errorEmail = 'Please enter email.';
        }else if (pattern.test(Email) == false) {
            $scope.Error = true;
            $scope.errorEmail = 'Invalid email.';
        }        
        if (Username.length <= 0) {
            $scope.Error = true;
            $scope.errorUsername = 'Please enter username.';
        }
        
        if (RoleArr.length <= 0){
            $scope.Error = true;
            $scope.errorRole = 'Please select role.';
        }
        if (!$scope.Error) {
            showLoader();
            //send message
            $scope.Error = false;
            $scope.errorFName = null;
            $scope.errorLName = null;
            $scope.errorEmail = null;
            $scope.errorUsername = null;
            $scope.errorRole = null;
            
            var reqData = {
                UserID:hdnUserID,
                FirstName: FirstName,
                LastName: LastName,
                Email: Email,
                Username: Username,
                RoleArr:RoleArr,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            
            getRoleUserData.SaveUserInfo(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg("Save successfully.");                    
                    $('#AddRoleUser').slideUp();                    
                    $scope.roleUsersList();
                    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);
                    $('#AddRoleUser').slideUp();
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.errorRole = response.Message;
                }
                $("html, body").animate({ scrollTop: 0 }, "slow");
                hideLoader();
            });
            
        }
    };
    
    $scope.AddNewUser = function() {
        $scope.Error = false;
        $scope.errorFName = null;
        $scope.errorLName = null;
        $scope.errorEmail = null;
        $scope.errorUsername = null;
        $scope.errorRole = null;
        
        $("#hdnUserID").val('');
        $("#FirstName").val('');
        $('#LastName').val('');
        $("#Email").val('');
        $('#Username').val('');
        
        $('#btnRoleSubmit').show();
        $scope.disableControl(false);
        $scope.userRoles = [];   
        $scope.getRoles();
    };
    
    $scope.ViewEditUser = function(userdata, todo) {
        $scope.Error = false;
        $scope.errorFName = null;
        $scope.errorLName = null;
        $scope.errorEmail = null;
        $scope.errorUsername = null;
        $scope.errorRole = null;
        
        $('#AddRoleUser').slideDown();
        $("#hdnUserID").val(userdata.UserID);
        $("#FirstName").val(userdata.FirstName);
        $('#LastName').val(userdata.LastName);
        $("#Email").val(userdata.Email);
        $('#Username').val(userdata.Username);
                
        $scope.getRoles();
        
        var reqData = {
            UserID:userdata.UserID,
            AdminLoginSessionKey:$scope.AdminLoginSessionKey
        };
        
        //Call getRoles in services.js file
        getRoleUserData.getUserRoles(reqData).then(function (response) {
            $scope.userRoles = [];
            if(response.ResponseCode == 200){                
                //Push data into Controller in view file
                $scope.userRoles = response.Data;
                
                if (todo == 'view'){
                    $scope.disableControl(true);
                }else {
                    $scope.disableControl(false);
                }
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
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
    
    $scope.disableControl = function(flag) {
        if (flag == true) {
            $("#RoleListOpt ul li input[type='checkbox']").attr('disabled','disabled');
            $("#btnUserSubmit").hide();
            
            $("#FirstName").attr('disabled', 'disabled');
            $("#LastName").attr('disabled', 'disabled');
            $("#Email").attr('disabled', 'disabled');
            $("#Username").attr('disabled', 'disabled');             
        }else {
            $("#RoleListOpt ul li input[type='checkbox']").removeAttr('disabled');
            $('#btnUserSubmit').show();
            
            $("#FirstName").removeAttr('disabled');
            $("#LastName").removeAttr('disabled');
            $("#Email").removeAttr('disabled');
            $("#Username").removeAttr('disabled');       
        }
    };
    
    $scope.deleteUser = function (userdata) {
        openPopDiv('confirmeUserPopup', 'bounceInDown');
        $scope.currentUserID = userdata.UserID;
        $scope.userstatus = 3;
    };
    
    $scope.updateUserStatus = function () {
        var reqData = {
            UserId: $scope.currentUserID,
            Status: $scope.userstatus,
            permissiontype:'roleusers',
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeUserPopup', 'bounceOutUp');
        showLoader();
        getRoleUserData.updateUserStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                //Show Success message
                $("#spn_noti").html("");
                sucessMsz();
                $("#spn_noti").html("  Delete successfully.");
                $('#AddRole').slideUp();
                $('#role_name').val('');
                $scope.roleUsersList();
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
    
    $scope.SetMultipleUserStatus = function (action) {
        var userstatus = '';
        if (action == "delete"){
            userstatus = 3;
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
    
    $scope.updateMultipleUsersStatus = function () {
        var reqData = {
            users: $scope.statusUserIds,
            userstatus: $scope.userstatus,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');
        showLoader();
        getRoleUserData.updateMultipleUsersStatus(reqData).then(function (response) {
            
            if (response.ResponseCode == 200) {
                //Reset all
                $scope.indexToUpdate = {};
                $scope.statusUserIds = {};
                $scope.selectedUsers = {};
                $scope.selectedUsersIndex = {};

                var msg =  $scope.useraction;            
                msg = ucwords(msg);
                ShowSuccessMsg(msg+" successfully.");
                
                $('#AddRole').slideUp();
                $('#role_name').val('');
                $scope.roleUsersList();
                
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
    
    $scope.getRolesOption();
    
});
