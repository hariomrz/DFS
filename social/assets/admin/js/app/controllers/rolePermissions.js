// ManagePermission Controller
app.controller('RolePermissionCtrl', function ($scope, $timeout, getPermissionData) {
    $scope.totalRolePermission = 0;
    $scope.showError = false;
    $scope.errorMessage = null;
    $scope.selectedApplication = 1;
        
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
    $scope.getApplications = function(){
        var reqData = {
            AdminLoginSessionKey:$scope.AdminLoginSessionKey
        };
        
        //Call getApplicationsList in services.js file
        getPermissionData.getApplicationsList(reqData).then(function (response) {
            $scope.appListData = {};
            if(response.ResponseCode == 200){
                
                //Push data into Controller in view file
                $scope.appListData = response.Data;
                $scope.selectedApplication = $scope.appListData[0];                
                $('#RoleApplication').trigger('chosen:updated');
                
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
    
    $scope.selectChangeEvent = function(){
        $scope.rolePermissionList();
    };
    
    $scope.rolePermissionList = function () {
        showLoader();
        
        var ApplicationID = $("#RoleApplication").val();   
        if(ApplicationID == null)
            ApplicationID = $scope.selectedApplication;
        
        var reqData = {
            ApplicationID:ApplicationID,
            AdminLoginSessionKey:$scope.AdminLoginSessionKey
        };
        
        //Call getRoleslist in services.js file
        getPermissionData.getRolePermissionList(reqData).then(function (response) {
            $scope.listData = [];
            if(response.ResponseCode == 200){
                $scope.noOfObj = $scope.totalRolePermission = response.Data.length;
                if($scope.noOfObj > 0){
                    $scope.totalRolePermission = $scope.noOfObj - 1;
                }
                
                //If no. of records greater then 0 then show
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#RolePermissionCtrl table>tbody').append('<tr id="noresult_td"><td colspan="4"><div class="no-content text-center"><p>'+ThereIsNoPermissionToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjRoles: response.Data});                
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
        
    $scope.changeRolePermissionForm = function(permissiondata){   
        $scope.application_name = permissiondata.application_text;
        $scope.app_action = permissiondata.action;
        $scope.application_id = permissiondata.application_id;
        $scope.right_id = permissiondata.right_id;
        
        var reqData = {
            ApplicationID:permissiondata.application_id,
            RightID:permissiondata.right_id,
            AdminLoginSessionKey:$scope.AdminLoginSessionKey
        };
        
        //Call getRightsPermissionRole in services.js file
        getPermissionData.getRightsPermissionRole(reqData).then(function (response) {
            $scope.permissionRoleData = {};
            if(response.ResponseCode == 200){                
                //Push data into Controller in view file
                $scope.permissionRoleData = response.Data;
                //console.log($scope.permissionRoleData);
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
        };
        
        openPopDiv('changePermissions', 'bounceInDown');
    };
    
    $scope.SaveRolePermissions = function(){
        var tmpArr = new Array();
        $("#RoleList input[type='checkbox']:checked").each(function () {
            tmpArr.push({ RoleID: $(this).val() });
        });
        
        if (tmpArr.length > 0){            
            showLoader();
            //send message
            $scope.errorRoleMessage = null;
            var reqData = {
                RightID : $scope.right_id,
                ApplicationID : $scope.application_id,
                RoleArr : tmpArr,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            
            getPermissionData.SavePermissionsRole(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    $("#spn_noti").html("");
                    sucessMsz();
                    $("#spn_noti").html("  Save successfully.");
                    $('#ManagePermissions').slideUp();
                    $scope.rolePermissionList();
                    $scope.permissionRoleData = {};
                    closePopDiv('changePermissions', 'bounceOutUp');
                    $('html,body').animate({scrollTop: 0}, 800);
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);
                    closePopDiv('changePermissions', 'bounceOutUp');
                    $('html,body').animate({scrollTop: 0}, 800);
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.errorRoleMessage = response.Message;
                }
                hideLoader();
            });
            
        } else {
            $scope.errorRoleMessage = 'Please select role.';
        }
        
        $timeout(function () {
            $scope.errorRoleMessage = null;
        }, 5000);
    };

    $scope.getApplications();
    $scope.rolePermissionList();    
    
});