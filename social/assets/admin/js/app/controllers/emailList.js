// EmailList Controller
app.controller('EmailListCtrl', function ($scope, $rootScope, getData, $window) {
    $scope.totalRecord = 0;
    $scope.filteredTodos = [],
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = true;
    $scope.emailSubject = {};
    $scope.emailDate = {};
    $scope.emailBody = {};
    $scope.currentUserStatusId = {};
    $rootScope.totalEmails = 0;
        
    $scope.emailAnalyticData = function () {
        intilizeTooltip();
        showLoader();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        
        $scope.emailType = '';
        if ($('#hdnEmailTypeID').val()) {
            $scope.emailType = $('#hdnEmailTypeID').val();
        }
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for communitcation listing
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
            EmailType: $scope.emailType,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        //Call getEmaillist in services.js file
        getData.getEmailList(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $rootScope.totalEmails =  $scope.totalRecord = $scope.noOfObj;

                //If no. of records greater then 0 then show
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#EmailListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="5"><div class="no-content text-center"><p>'+ThereIsNoEmailToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                //Push data into Controller in view file
                $scope.listData.push({ObjEmails: response.Data.results});
            
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
    //Apply Sort by and mamke request data
    $scope.sortBY = function (column_id) {
        if($("table.email_table #noresult_td").length == 0)
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
                EmailType: $scope.emailType,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.emailAnalyticData();
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
        }
        $scope.emailAnalyticData()
    });
    
    //Function for set user id
    $scope.SetEmail = function (emaillist) {
        //console.warn(emaillist);
        $scope.currentUserStatusId = emaillist.userstatusid;
        $scope.emailSubject = emaillist.subject;
        $scope.emailDate = emaillist.created_date;
        $scope.emailBody = emaillist.body;
        $("#hdnCommunicationID").val(emaillist.communication_id);
    }
    
    $scope.summaryPopup = function () {
        angular.element('#summaryPopup').find('#summarySubject').html($scope.emailSubject);
        angular.element('#summaryPopup').find('#summaryCreatedDate').html($scope.emailDate);
        angular.element('#summaryPopup').find('#summaryBody').html($scope.emailBody);
        openPopDiv('summaryPopup', 'bounceInDown');
    }
    
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    }
    
    //Function for view user profile of a particular user
    $scope.viewUserProfile = function (userguid) {
        //If UserGUID is Undefined
        if (typeof userguid !== 'undefined') {
            //Useful for set breadcrumb
            $window.location.href = base_url + 'admin/users/user_profle/' + userguid;
        }
        
    }
    
    //Function for resend email to users
    $scope.ResendEmail = function () {
        var CommunicationID = $("#hdnCommunicationID").val();        
        if (typeof CommunicationID !== 'undefined') {
            var reqData = {
                CommunicationID: CommunicationID,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            showLoader();
            getData.resendCommunication(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    $("#spn_noti").html("");
                    sucessMsz();
                    $("#spn_noti").html("  Email send successfully."); 
                    
                    $scope.emailAnalyticData();
                    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);                
                }else if(response.ResponseCode == 104){
                    ShowErrorMsg(response.Message);
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    //For show default error message
                    ShowErrorMsg("Error Please try again.");
                }
                hideLoader();
            });
        }
        
    }
});