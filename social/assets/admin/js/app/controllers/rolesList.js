// ManageRoles Controller
app.controller('ManageRolesCtrl', function ($scope, $timeout, getRolesData) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.totalRoles = 0;
    $scope.showError = false;
    $scope.errorMessage = null;
    $scope.rolename = '';

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
    $scope.rolesList = function () {
        showLoader();
                
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
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getRoleslist in services.js file
        getRolesData.getRoleslist(reqData).then(function (response) {
            $scope.listData = [];
            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $scope.totalRoles = $scope.totalRecord = $scope.noOfObj;

                //If no. of records greater then 0 then show
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#ManageRolesCtrl table>tbody').append('<tr id="noresult_td"><td colspan="3"><div class="no-content text-center"><p>'+ThereIsNoRoleToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjRoles: response.Data.results});
                
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
    
    $scope.SetRoleDetail = function(roledata){
        $scope.roleDetail = roledata;
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
        $scope.rolesList()
    });
    
    $scope.createRole = function () {
        var RoleName = $('#RoleName').val();
        //var RoleStatus = $('#RoleStatus').val();
        var RoleStatus = $("input:radio[name=RoleStatus]:checked").val();
        var hdnRoleID = $('#hdnRoleID').val();

        if (RoleName.length <= 0) {
            $scope.showError = true;
            $scope.errorMessage = 'Please enter role name.';
        } else if (RoleStatus == '') {
            $scope.showError = true;
            $scope.errorMessage = 'Please select status.';
        } else {
            showLoader();
            //send message
            $scope.showError = false;
            $scope.errorMessage = null;
            var reqData = {
                RoleName: RoleName,
                RoleStatus: RoleStatus,
                RoleId:hdnRoleID,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            
            getRolesData.saveRoleInfo(reqData).then(function (response) {
                if (response.ResponseCode == 200){
                    //Show Success message
                    ShowSuccessMsg("Save successfully.");
                    
                    $('#AddRole').slideUp();
                    $('#role_name').val('');
                    $scope.rolesList();
                    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);
                    $('#AddRole').slideUp();
                    $('#role_name').val('');
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.showError = true;
                    $scope.errorMessage = response.Message;
                }
                hideLoader();
            });
        }
        
        $timeout(function () {
            $scope.showError = false;
            $scope.errorMessage = null;
        }, 5000);
    };
    
    $scope.SaveManagedRolePermissions = function(){
        var tmpArr = new Array()
        $("#permissionList .rolecheck input[type='checkbox']").each(function () {
            if($(this).attr("checked") == "checked"){
                tmpArr.push({ ApplicationID: $(this).attr("applicationId"), RightID: $(this).val(), RoleRightID: $(this).attr("RoleRightID") });
            }
        });
        
        if (tmpArr.length > 0){
            showLoader();
            //send message
            $scope.showError = false;
            $scope.errorMessage = null;
            var reqData = {
                RoleId : $scope.roleid,
                PermissionsArr : tmpArr,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            
            getRolesData.SaveManagedRolePermissions(reqData).then(function (response) {
                if (response.ResponseCode == 200){
                    //Show Success message
                    ShowSuccessMsg("Save successfully.");
                    
                    $('#ManagePermissions').slideUp();
                    $scope.rolesList();
                    $scope.permissionsData = {};
                    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);
                    $scope.permissionsData = {};
                    $('#ManagePermissions').slideUp();
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.errorPermissionMessage = response.Message;
                }
                $("html, body").animate({ scrollTop: 0 }, "slow");
                hideLoader();
            });
            
        } else {
            $scope.errorPermissionMessage = 'Please select permissions.';
        }
        
        $timeout(function () {
            $scope.errorPermissionMessage = null;
        }, 5000);
    };
        
    $scope.deleteRole = function (roledata) {        
        $scope.currentRoleID = roledata.roleid;
        $scope.rolestatus = '3';
        $scope.showRoleError = false;
        $scope.errorRoleMessage = null;
        var reqData = {
            RoleId: $scope.currentRoleID,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        $scope.RoleList = {};
        $scope.userCount = 0;
        getRolesData.getRoleListExceptSelected(reqData).then(function (response) {
            
            if (response.ResponseCode == 200){
                $scope.RoleList = response.Data.result;
                $scope.userCount = response.Data.userCount;
                $('#newRole').trigger('chosen:updated');                
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }
            if($scope.userCount > 0){
                openPopDiv('confirmeDeleteRolePopup', 'bounceInDown');
            }else{                
                openPopDiv('confirmeRolePopup', 'bounceInDown');
            }
            
        }), function (error) {
            hideLoader();
        }
    };
            
    $scope.updateRoleStatus = function (type) {
        var Error = 0;        
        if(type == "assign"){
            var NewRole = $("#newRole").val();
            if (NewRole.length <= 0) {
                Error = 1;
                $scope.showRoleError = true;
                $scope.errorRoleMessage = 'Please select Role';
            }
        }else{
            var NewRole = '';
        }
              
        if(Error == 0){
            var reqData = {
                RoleId: $scope.currentRoleID,
                NewRole: NewRole,
                RoleStatus: $scope.rolestatus,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
                
            showLoader();
            getRolesData.updateRoleStatus(reqData).then(function (response) {
                if (response.ResponseCode == 200){
                    //Show Success message
                    ShowSuccessMsg("Delete successfully.");
                    if(type == "delete"){
                        closePopDiv('confirmeRolePopup', 'bounceOutUp');
                    }else{
                        closePopDiv('confirmeDeleteRolePopup', 'bounceOutUp');
                    }

                    $('#AddRole').slideUp();
                    $('#role_name').val('');
                    $scope.rolesList();

                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);
                    $('#AddRole').slideUp();
                    $('#role_name').val('');
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                }else{
                    ShowErrorMsg(response.Message);
                }

                hideLoader();

            }), function (error) {
                hideLoader();
            }
        }
        
        $timeout(function () {
            $scope.showRoleError = false;
            $scope.errorRoleMessage = null;
        }, 5000);
    };
    
    $scope.ViewEditRole = function(roledata, todo) {        
        $('#AddRole').slideDown();
        $("#hdnRoleID").val(roledata.roleid);
        $('#RoleName').val(roledata.rolename);
        //$('#RoleStatus').val(roledata.rolestatusid);
        $('#ManagePermissions').slideUp();
        $(".RadioLabel").removeClass("rdodisabled");//Radio Code
        
        if(roledata.rolestatusid == 2){
            $("#ActiveRadio").prop("checked", true);
        }else{
            $("#InactiveRadio").prop("checked", true);
        }
        
        if (todo == 'edit') {
            $('#btnRoleSubmit').show();
            $("#RoleName").prop("disabled", false);      
            //$("#RoleStatus").prop("disabled", false).trigger("chosen:updated");
            $(".role_radio").prop("disabled", false);//Radio Code
        }
        else if (todo == 'view'){
            $("#btnRoleSubmit").hide();
            $("#RoleName").prop("disabled", true);     
           // $("#RoleStatus").prop("disabled", true).trigger("chosen:updated");
            $(".role_radio").prop("disabled", true);//Radio Code
            $(".RadioLabel").addClass("rdodisabled");//Radio Code
        }
        else {
            $('#btnRoleSubmit').show();
            $("#RoleName").prop("disabled", false);
            //$("#RoleStatus").prop("disabled", false).trigger("chosen:updated");
            $(".role_radio").prop("disabled", false);//Radio Code
        }
        
        $scope.showError = false;
        $scope.errorMessage = null;
        $("html, body").animate({ scrollTop: 0 }, "slow");
    };
    
    $scope.selectRolePermissions = function(){
        $timeout(function () {
            if ($(".level1").find(".checkspan.icon-checked").length == $(".level1").find(".checkspan").length) {
                $("#parentId1").attr('checked','checked');
                $("#parentId1").parent().addClass("icon-checked");
                $("#parentId1").parent().parent().addClass("focus");
            }else{
                $("#parentId1").removeAttr('checked');
                $("#parentId1").parent().removeClass("icon-checked");
                $("#parentId1").parent().parent().removeClass("focus");        
            }
        }, 100);
    };
    
    $scope.AddNewRole = function() {
        $('#ManagePermissions').slideUp();
        $("#hdnRoleID").val('');
        $('#btnRoleSubmit').show();
        $("#RoleName").prop("disabled", false);
        //$("#RoleStatus").prop("disabled", false).trigger("chosen:updated");        
        $('#RoleName').val("");
        //$('#RoleStatus').val('2');   
        
        $(".role_radio").prop("disabled", false).removeAttr("checked");//Radio Code
        $("#ActiveRadio").prop("checked", true);
        $(".RadioLabel").removeClass("rdodisabled");//Radio Code
        $scope.showError = false;
        $scope.errorMessage = null;
    };
    
    $scope.ManageRolePermissions = function(roledata) {
        $('#ManagePermissions').slideDown();
        $('#AddRole').slideUp();
        $scope.rolename = roledata.rolename;
        $scope.roleid = roledata.roleid;
        
        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            RoleId : roledata.roleid,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        showLoader();
        //Call getRolesPermissions in services.js file
        getRolesData.getRolesPermissions(reqData).then(function (response) {
            $scope.permissionsData = {};
            if(response.ResponseCode == 200){                
                //Push data into Controller in view file
                $scope.permissionsData = response.Data;
                //console.log($scope.permissionsData);
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
            hideLoader();       
            $("html, body").animate({ scrollTop: 0 }, "slow");
            
        }), function (error) {
            hideLoader();
        }
    };
    
});