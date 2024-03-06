// Support Controller
app.controller('SupportCtrl', function ($scope, supportData, $window) {
    $scope.totalRecord = 0;
    $scope.totalLogs = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = true;
    $scope.pageLoad = 0;
    
    $scope.errorStatus = 1;
    $scope.errorTypeId = 0;
    $scope.errorSectionText = "Pending";
    
    $scope.globalChecked = false;
    $scope.selectedLogs = {};
    $scope.selectedLogsIndex = {};
    $scope.confirmationMessage = '';
    
    $scope.loadErrorLogByType = function(log_type){
        $scope.pageLoad = 1;
        if(log_type == "pending"){
            $scope.errorSectionText = "Pending";
            $scope.errorStatus = 1;
        }else if(log_type == "completed"){
            $scope.errorSectionText = "Completed";
            $scope.errorStatus = 2;
        }else if(log_type == "ignored"){
            $scope.errorSectionText = "Ignored";
            $scope.errorStatus = 4;
        }
    };
    
    $scope.setErrorLogStatus = function (status) {
        $scope.errorStatus = status;
        $(".erroroptul li").removeClass("selected");
        
        if (status == "1") {
            $scope.errorSectionText = "Pending";
        }else if (status == "2") {
            $scope.errorSectionText = "Completed";
        }else if (status == "4") {
            $scope.errorSectionText = "Ignored";
        }
        
        $scope.globalChecked = false;
        $scope.showButtonGroup = false;
        $('#ItemCounter').fadeOut();
        
        $scope.supportErrorLogs();
    };
    
    $scope.filterErrorLogs = function(errorTypeId){
        $scope.errorTypeId = errorTypeId;
        $scope.supportErrorLogs();
    };
    
    $scope.supportErrorLogs = function () {
        showLoader();
        $scope.selectedLogs = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();
        
        $scope.searchKey = '';
        if ($('#searchField').val()) {
            $scope.searchKey = $('#searchField').val();
            $('#supportErrorSearch').addClass('selected');
        }

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
            SearchKey: $scope.searchKey,
            ErrorStatus: $scope.errorStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            ErrorTypeId: $scope.errorTypeId,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        $("#accessdenieddiv").html('');
        
        //Call getErrorLoglist in services.js file
        supportData.getErrorLoglist(reqData).then(function (response) {
            $scope.listData = [];
            //If no. of records greater then 0 then show
            $('#noresult_td').remove();
            $("#selectallbox,.download_link,.search-field,.simple-pagination").show();

            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $scope.totalLogs = $scope.totalRecord = $scope.noOfObj;

                //$scope.showButtonGroup = false;
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                
                $("#errorloglistdiv").show();
                
                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $("#selectallbox,.download_link,.simple-pagination").hide();
                    $('#SupportCtrl table>tbody').append('<tr id="noresult_td"><td colspan="8"><div class="no-content text-center"><p>'+ThereIsNoRecordToShow+'</p></div></td></tr>');
                    if($scope.searchKey == ""){
                        $(".search-field").hide();
                    }
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjErrors: response.Data.results});
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                PermissionError(response.Message);
                $("#accessdenieddiv").html(response.DeniedHtml);
                $("#errorloglistdiv").hide();
                $('.download_link').hide();
                $(".support_action_div").hide();
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
            
            $scope.supportErrorLogs();
        }
    };
    
    //Get no. of pages for data
    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };
    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {  
        if ($('#errorStatus').val() != "") {
            $scope.errorStatus = $('#errorStatus').val();
            $scope.setErrorLogStatus($scope.errorStatus);
        }
        if($scope.pageLoad == 1){
            $scope.supportErrorLogs();
        }
    });
    
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    }
    
    //Function for search users on media analytics page
    $scope.searchSupportError = function(){
        if($('#searchField').val() != ''){
            $scope.supportErrorLogs();
        }
    };
    
    $scope.downloadErrorLogs = function () {
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
            ErrorStatus: $scope.errorStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            ErrorTypeId: $scope.errorTypeId,
            dateFilterText:$scope.dateFilterText,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
                
        //Call downloadErrorLogs in services.js file
        supportData.downloadErrorLogs(reqData).then(function (response) {
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
    
    /**
     * Set li selected
     * @param {type} user
     * @returns {undefined}
     */
    $scope.selectCategory = function (errorlog) {
        if($("#hdnSelectallPermission").val() == 1){
            if (errorlog.ErrorLogID in $scope.selectedLogs) {
                delete $scope.selectedLogs[errorlog.ErrorLogID];
            } else {
                $scope.selectedLogs[errorlog.ErrorLogID] = errorlog;
            }
            if (Object.keys($scope.selectedLogs).length > 0) {
                setTimeout(function(){ $scope.globalChecked == true; }, 1);
                $('#ItemCounter').fadeIn();            
            } else {
                $scope.globalChecked = false;
                $('#ItemCounter').fadeOut();
            }    

            setTimeout(function(){
                if($(".registered-user tr.selected").length == $scope.listData[0].ObjErrors.length){
                    setTimeout(function(){ $scope.globalChecked = true; }, 1);
                    $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
                }else{
                    $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                }
            }, 1);

            var ItemCount = Object.keys($scope.selectedLogs).length;
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
    $scope.isSelected = function (errorlog) {
        if (errorlog.ErrorLogID in $scope.selectedLogs) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;
        }        
    };

    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;
        if ($scope.globalChecked) {
            $scope.selectedLogs = [];
            var listData = $scope.listData[0].ObjErrors;
            angular.forEach(listData, function (val, key) {
                if (typeof $scope.selectedLogs[key]) {                    
                    $scope.selectCategory(val, key);
                }
            });
        } else {
            angular.forEach($scope.selectedLogs, function (val, key) {
                $scope.selectCategory(val, key);
            });
        }       
    };
    
    //Function for set error logs
    $scope.SetErrorLog = function (errorlog) {
        $scope.currentLogStatusId = errorlog.StatusID;
        $scope.currentLogId = errorlog.ErrorLogID;
    };
    
    $scope.changeErrorLogStatus = function (action) {
        var logstatus = '';
        if (action == "delete") {
            logstatus = 3;
            $scope.confirmationMessage = Sure_Delete+' ?';
        }else if (action == "complete") {
            logstatus = 2;
            $scope.confirmationMessage = Sure_Complete+' ?';
        }else if (action == "ignore") {
            logstatus = 4;
            $scope.confirmationMessage = Sure_Ignore+' ?';
        }else if (action == "unignore") {
            logstatus = 1;
            $scope.confirmationMessage = Sure_Pending+' ?';
        }
        
        $scope.errorLogIds = [];
        $scope.errorLogIds.push($scope.currentLogId);
        openPopDiv('confirmeErrorLogPopup', 'bounceInDown');
        $scope.logstatus = logstatus;
        $scope.logaction = action;
        
    };
    
    $scope.changeMultipleErrorLogStatus = function (action) {
        var logstatus = '';
        if (action == "delete") {
            logstatus = 3;
            $scope.confirmationMessage = Sure_Delete+' ?';
        }else if (action == "complete") {
            logstatus = 2;
            $scope.confirmationMessage = Sure_Complete+' ?';
        }else if (action == "ignore") {
            logstatus = 4;
            $scope.confirmationMessage = Sure_Ignore+' ?';
        }else if (action == "unignore") {
            logstatus = 1;
            $scope.confirmationMessage = Sure_Pending+' ?';
        }
        
        openPopDiv('confirmeMultipleErrorLogPopup', 'bounceInDown');
        $scope.errorLogIds = [];
        $scope.errorLogIds = Object.keys($scope.selectedLogs);
        $scope.logstatus = logstatus;
        $scope.logaction = action; 
        //console.log($scope.errorLogIds);
    };
    
    $scope.updateErrorLogStatus = function (PopupId) {
        showLoader();
        var reqData = {
            errorLogIds: $scope.errorLogIds,
            status: $scope.logstatus,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        showLoader();
        supportData.updateErrorLogStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                //Reset all
                $scope.errorLogIds = [];

                hideLoader();
                ShowSuccessMsg(response.Message);

                $scope.supportErrorLogs();
                
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
            
            closePopDiv(PopupId, 'bounceOutUp');
            
        }), function (error) {
            hideLoader();
        }
    };
    
    //Function for view user profile of a particular user
    $scope.viewErrorLog = function () {
        var ErrorLogId = $scope.currentLogId;
        var errorStatus = $scope.errorStatus;
        
        //Useful for set breadcrumb
        $window.location.href = base_url + 'admin/support/supportrequestview?logId=' + ErrorLogId+'&errorStatus='+errorStatus;
    };
    
});

app.controller('SupportViewCtrl', function ($scope, supportData, $timeout) {
    $scope.errorLogDetail = {};
    $scope.ErrorLogID = '';
    
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
    //for get error log details
    $scope.getErrorLogDetail = function () {
        $scope.ErrorLogID = $("#ErrorLogID").val();
        
        //Make request data parameter for users listing
        var reqData = {
            ErrorLogID: $scope.ErrorLogID,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        $("#errorlogdetaildiv").hide();
        $("#accessdenieddiv").html('');
        //Call getErrorLogDetail in services.js file
        supportData.getErrorLogDetail(reqData).then(function (response) {    
            if (response.ResponseCode == 200) {
                //Push data into Controller in view file
                $scope.errorLogDetail = response.Data;
                $("#errorlogdetaildiv").show();
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);
                $("#accessdenieddiv").html(response.DeniedHtml);
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
        }), function (error) {
            alert('Invalid Operation!!');
        }
    };
    
    $scope.layoutDone = function() {
        $timeout(function() {
            $('.icon-zoomlist').lightBox();
        }, 0); // wait...
    };
    
    $scope.getErrorLogDetail();
});