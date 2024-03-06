// User Controller for make Email Analytics chart
app.controller('emailAnalyticsCtrl', function ($scope, $rootScope, emailAnalyticsChartData) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = true;
    
    $scope.totalRecordSent = 0;
    $scope.currentPageSent = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByFieldSent = '';
    $scope.reverseSortSent = true;
    $scope.selecteddate = '';
    
    $scope.AnalyticType = 'mandrill';
    $scope.EmailTypes = 2;
    $scope.filterval = 3;
    $scope.LineChartHeading = 'Registration';
    $scope.StatistcsHeading = 'REGISTERED';
    
    $scope.loadEmailAnalyticsData = function(AnalyticType){
        $(".media_right_filter li").removeClass("selected");
        $("#"+AnalyticType).addClass("selected");
        $scope.AnalyticType = AnalyticType;
        $scope.emailAnalyticsChart();
        $scope.emailAnalyticsLineChart();
        $scope.EmailAnalyticsStatistcs();
    };
    
    $scope.ChangeLineChart = function(filter){
        $(".tab-analytics a").removeClass("active");
        $("#flter_"+filter).addClass("active");
        $scope.filterval = filter;
        $scope.emailAnalyticsLineChart();        
    };
    
    $scope.LoadEmailAnalyticsChart = function(EmailType){
        if(EmailType == 1){
            $scope.LineChartHeading = "Communication";
            $scope.StatistcsHeading = 'Communication';
        }else if(EmailType == 2){
            $scope.LineChartHeading = "Registration";
            $scope.StatistcsHeading = 'Registered';
        }else if(EmailType == 3){
            $scope.LineChartHeading = "Forget Password";
            $scope.StatistcsHeading = 'Forget Password';
        }else if(EmailType == 4){
            $scope.LineChartHeading = "Beta Invite";
            $scope.StatistcsHeading = 'Beta Invite';
        }else{
            $scope.LineChartHeading = "Registration";
            $scope.StatistcsHeading = 'Registered';
        }
        $scope.EmailTypes = EmailType;
        $scope.emailAnalyticsLineChart();
        $scope.EmailAnalyticsStatistcs();                
    };
    
    $scope.emailAnalyticsChart = function () {
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AnalyticType: $scope.AnalyticType,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        emailAnalyticsChartData.emailAnalyticsData(reqData).then(function (response) {
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Email analytics  */                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#emailAnalyticsPieChart").html('<div class="no-email-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawEmailAnalyticsPieChart(response.Data));
                }
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                google.setOnLoadCallback(drawEmailAnalyticsChart([]));
                //Show error message
                PermissionError(response.Message);                
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
        });
    };
    
    //Function for get login line chart data
    $scope.emailAnalyticsLineChart = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $scope.filterval;
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            EmailTypes:$scope.EmailTypes,
            AnalyticType: $scope.AnalyticType,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        emailAnalyticsChartData.emailAnalyticsLineChart(reqData).then(function (response) {
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Email analytics  */
                if ($.isEmptyObject(response.Data) == true) {
                    $("#emailAnalyticsLineChart").html('<div class="no-signups-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawEmailAnalyticsLineChart(response.Data, $scope.filter));
                }
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
        });
    };
    
    $scope.EmailAnalyticsStatistcs = function () {
        showLoader();
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Begin: begins,
            End: $scope.numPerPage,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            EmailTypes:$scope.EmailTypes,
            AnalyticType: $scope.AnalyticType,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        $scope.selecteddate = '';
        $scope.totalRecordSent = $scope.noOfObjSent = 0;
        $scope.currentPageSent = 1,
        $scope.listDataSent = [];
        $("#SentEmailsStatistcs").addClass("hide");
        
        //Call getIpList in services.js file
        emailAnalyticsChartData.getEmailAnalyticsStatistcs(reqData).then(function (response) {
            $scope.listData = [];
            $("#emaildenieddiv").html('');
            if(response.ResponseCode == 200){
                $scope.totalRecord = $scope.noOfObj = response.Data.total_records
                
                //If no. of records greater then 0 then show            
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#EmailsStatistcs table>tbody').append('<tr id="noresult_td"><td colspan="6"><div class="no-content text-center"><p>'+ThereIsNoRecordToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjEmail: response.Data.results});
            
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
            $scope.EmailAnalyticsStatistcs();
        }
    };
    //Get no. of pages for data
    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };
    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {
        $scope.EmailAnalyticsStatistcs();
    });
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    };
    
    $scope.LoadSentMessage = function(sentdate){
        if(sentdate == '')
            return false;
        else            
            $scope.selecteddate = sentdate;
        
        showLoader();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $("#SentEmailsStatistcs").removeClass("hide");
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPageSent == 1) {
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPageSent - 1) * $scope.numPerPage)
        }

        var reqData = {
            Begin: begins,
            End: $scope.numPerPage,
            SortBy: $scope.orderByFieldSent,
            OrderBy: $scope.reverseSortSent,
            EmailTypes:$scope.EmailTypes,
            AnalyticType: $scope.AnalyticType,
            SentDate:sentdate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getIpList in services.js file
        emailAnalyticsChartData.getSentEmailsStatistcs(reqData).then(function (response) {
            $scope.listDataSent = [];
            $("#sentemaildenieddiv").html('');
            if(response.ResponseCode == 200){
                $scope.totalRecordSent = $scope.noOfObjSent = response.Data.total_records
                
                //If no. of records greater then 0 then show            
                $('#sent_noresult_td').remove();
                $('#SentEmailsStatistcs .simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObjSent == 0) {
                    $('#SentEmailsStatistcs table>tbody').append('<tr id="sent_noresult_td"><td colspan="5"><div class="no-content text-center"><p>'+ThereIsNoRecordToShow+'</p></div></td></tr>');
                    $('#SentEmailsStatistcs .simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listDataSent.push({ObjSentEmail: response.Data.results});
                
                $('html,body').animate({scrollTop: $("#SentEmailsStatistcs").offset().top}, 1000);
            
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);
                $("#sentemaildenieddiv").html(response.DeniedHtml);
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
            
            hideLoader();
            
            //$scope.$apply();
        }), function (error) {
            hideLoader();
        }
        
    };
    
    //Apply Sort by and mamke request data
    $scope.SentSortBY = function (column_id) {
        if($("#SentEmailsStatistcs table.users-table #sent_noresult_td").length == 0)
        {
            $("#SentEmailsStatistcs .shortdiv").children('.icon-arrowshort').addClass('hide');
            $("#SentEmailsStatistcs .shortdiv").parents('.ui-sort').removeClass('selected');
            if($scope.reverseSortSent == true){
                $("#SentEmailsStatistcs #"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
            }else{
                $("#SentEmailsStatistcs #"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedUp').addClass('sortedDown').children('.icon-arrowshort').removeClass('hide');                
            }
            $scope.LoadSentMessage($scope.selecteddate);
        }
    };
    //Get no. of pages for data
    $scope.numPagesSent = function () {
        return Math.ceil($scope.noOfObjSent / $scope.numPerPage);
    };
    
    //Call function for get pagination data with new request data
    $scope.$watch('currentPageSent + numPerPage', function () {
        $scope.LoadSentMessage($scope.selecteddate);
    });
        
        
    $scope.LoadEmailClickList = function(sentdate,clicks){
        if(clicks == 0)
            return false;
        
        openPopDiv('EmailClickListPopup', 'bounceInDown');
        showLoader();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        var reqData = {
            EmailTypes:$scope.EmailTypes,
            SentDate:sentdate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        //Call getIpList in services.js file
        emailAnalyticsChartData.getEmailsClickUrlList(reqData).then(function (response) {
            $scope.ClickListData = [];
            if(response.ResponseCode == 200){
                $scope.noOfObjClick = response.Data.total_records
                $('#click_noresult_td').remove();
                //If no of records equal 0 then hide
                if ($scope.noOfObjClick == 0) {
                    $('#EmailClickUrlList table>tbody').append('<tr id="click_noresult_td"><td colspan="2"><div class="no-content text-center"><p>'+ThereIsNoRecordToShow+'</p></div></td></tr>');
                }
                
                //Push data into Controller in view file
                $scope.ClickListData.push({ObjClickEmail: response.Data.results});
            
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
            
            //$scope.$apply();
        }), function (error) {
            hideLoader();
        }
        
    };
    
    //Function for set user id
    $scope.SetEmail = function (emaillist) {
        $scope.currentUserStatusId = emaillist.userstatusid;
        $scope.emailSubject = emaillist.Subject;
        $scope.emailDate = emaillist.CreatedDate;
        $scope.emailBody = emaillist.Body;
        $scope.communication_id = emaillist.MessageID;
    };
    
    $scope.summaryPopup = function () {
        angular.element('#summaryPopup').find('#summarySubject').html($scope.emailSubject);
        angular.element('#summaryPopup').find('#summaryCreatedDate').html($scope.emailDate);
        angular.element('#summaryPopup').find('#summaryBody').html($scope.emailBody);
        openPopDiv('summaryPopup', 'bounceInDown');
    };
    
    //Function for resend email to users
    $scope.ResendEmail = function () {
        var CommunicationID = $scope.communication_id;
        if (typeof CommunicationID !== 'undefined') {
            var reqData = {
                CommunicationID: CommunicationID,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            showLoader();
            $("#divLoader").css("top",($("#SentEmailsStatistcs").offset().top + 180)+"px")
            emailAnalyticsChartData.resendCommunication(reqData).then(function (response) {
                if (response.ResponseCode == 200){
                    //Show Success message
                    ShowSuccessMsg("Email send successfully.")
                    $scope.loadEmailAnalyticsData('smtp');
                    
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
                $("html, body").animate({ scrollTop: 0 }, "slow");
                hideLoader();
            });
        }        
    };
    
});