// IP Controller
app.controller('IpsCtrl', function ($scope, $rootScope, ipsData, $window, $timeout) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $rootScope.totalIps = 0;
    $scope.isDefaultIP = 0;
    $scope.IpFor = 1;
    $scope.pageHeading = Allow_IPs;
    $scope.globalChecked = false;    
    $scope.IpStatus = {};
    $scope.ip_status = 2;
            
    $scope.IpsList = function () {
        intilizeTooltip();
        showLoader();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for smtp listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }
        
        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            IpFor: $scope.IpFor,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getIpList in services.js file
        ipsData.getIpList(reqData).then(function (response) {
            $scope.listData = [];
            $("#ipdenieddiv").html('');
            if(response.ResponseCode == 200){
                $scope.totalRecord = $scope.noOfObj = response.Data.total_records
                if($scope.IpFor == 1){
                    $scope.pageHeading = Allow_IPs;
                }else{
                    $scope.pageHeading = Blocked_IPs;
                }
                $rootScope.totalIps = $scope.noOfObj;

                //If no. of records greater then 0 then show            
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#IpsCtrl table>tbody').append('<tr id="noresult_td"><td colspan="3"><div class="no-content text-center"><p>'+ThereIsNoIPsToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjIP: response.Data.results});
            
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);
                $("#ipdenieddiv").html(response.DeniedHtml);
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
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.IpsList();
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
            SortBy: $scope.sort_by,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        if($scope.IpFor == 1){
            if ($('#adminips').length < 1 ) {
                $("#userips").addClass("selected");
                $scope.loadIPs(0);
            }else{
                $scope.IpsList();
            }
        }
        
    });
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    };    
    
    //Function for set Ip details in scope variables
    $scope.SetIpDetail = function (iplist) {
        $scope.isDefaultIP = 0;
        
        $scope.IpStatus = iplist.statusid;
        $scope.allowedipid = iplist.allowedipid;
        $scope.ip_address = iplist.ip;
        $scope.description = iplist.description;
        $scope.ip_status = $scope.IpStatus = iplist.statusid;
        if(iplist.isdefault == 1){
            $scope.isDefaultIP = 1;
        }        
        
    };
    
    $scope.AddIpDetailsPopUp = function () {
        $("#ip_address").val('');
        $("#description").val('');
        $("#allowedipid").val('');
        $("#chkActive").attr('checked',true).parent('span').addClass('icon-checked');
        $scope.showIpError = false;
        $scope.errorIpMessage = null;
        openPopDiv('addIpPopup', 'bounceInDown');        
    };
    
    $scope.EditIpDetailsPopUp = function(){
        $("#ip_address").val($scope.ip_address);
        $("#description").val($scope.description);
        $("#allowedipid").val($scope.allowedipid);
        $scope.showIpError = false;
        $scope.errorIpMessage = null;
        openPopDiv('addIpPopup', 'bounceInDown');
        if($scope.ip_status == 2){
            $("#chkActive").attr('checked',true).parent('span').addClass('icon-checked');
        }else{
            $("#chkActive").attr('checked',false).parent('span').removeClass('icon-checked');
        }
    };
    
    $scope.SetIp = function(){
        var hostaddress = $('#lblHostAddress').text();
        $('#ip_address').val(hostaddress);        
    };
    
    $scope.loadIPs = function(ip_type){
        $scope.IpFor = ip_type;
        $scope.IpsList();
    };
        
    $scope.CreateIpDetails = function () {
        
        var AllowedIpID = $("#allowedipid").val();
        var IpAddress = $("#ip_address").val();
        IpAddress = $.trim(IpAddress);  
        $("#ip_address").val(IpAddress);
        var Description = $("#description").val();
        /*var Status = 2;
        if($("#chkActive").is(':checked') || $("#chkActive").parent('span').hasClass('icon-checked')){
            Status = 2;
        }*/
        
        var IpFor = $scope.IpFor;
        var ipReg = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        if (IpAddress.length <= 0) {
            $scope.showIpError = true;
            $scope.errorIpMessage = 'Please enter IP Address';
        }else if (!ipReg.test(IpAddress)){
            $scope.showIpError = true;
            $scope.errorIpMessage = 'Please enter Valid IP Address';
        }else {
            $('.loader_ip').show();
            //send message
            $scope.showIpError = false;
            $scope.errorIpMessage = null;
            var reqData = {
                AllowedIpID: AllowedIpID,
                IpAddress: IpAddress,
                Description: Description,
                //Status: Status,
                IpFor: IpFor,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            
            ipsData.CreateIpDetails(reqData).then(function (response) {                
                if (response.ResponseCode == 200){
                    //Show Success message
                    ShowSuccessMsg(response.Message);
                    
                    $scope.IpsList();
                    closePopDiv('addIpPopup', 'bounceOutUp');
                    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    closePopDiv('addIpPopup', 'bounceOutUp');
                    PermissionError(response.Message);                
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.showIpError = true;
                    $scope.errorIpMessage = response.Message;
                }
                $('.loader_ip').hide();
            });
        }
        
        $timeout(function () {
            $scope.showIpError = false;
            $scope.errorIpMessage = null;
        }, 5000);
        
    };
    
    $scope.SetIpStatus = function (action) {
        var ipstatus = '';
        if (action == "active") {
            ipstatus = 2;
            $rootScope.confirmationMessage = Sure_Active+' ?';
        }else if (action == "inactive") {
            ipstatus = 1;
            $rootScope.confirmationMessage = Sure_Inactive+' ?';
        }else if (action == "delete") {
            ipstatus = 3;
            $rootScope.confirmationMessage = Sure_Delete+' ?';
        }
        
        $scope.ipIds = [];
        $scope.ipIds.push($scope.allowedipid);
        openPopDiv('confirmeIpSettingPopup', 'bounceInDown');
        $scope.ipstatus = ipstatus;
        $scope.ipaction = action;
        
    };
    
    $rootScope.updateIPStatus = function () {
        showLoader();
        var reqData = {
            allowed_ip_ids: $scope.ipIds,
            status: $scope.ipstatus,
            IpFor: $scope.IpFor,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeIpSettingPopup', 'bounceOutUp');
        showLoader();
        ipsData.updateIPStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                //Reset all
                $scope.ipIds = [];
                hideLoader();
                ShowSuccessMsg(response.Message);
                $scope.IpsList();
                
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
            $("html, body").animate({ scrollTop: 0 }, "slow");
            
        }), function (error) {
            hideLoader();
        }
    };
    
});