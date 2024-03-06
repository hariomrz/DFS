// google analytics Controller for make google Analytics chart
app.controller('googleAnalyticsCtrl', function ($scope, googleAnalyticsChartData,$window) {
    $scope.subfilter = 'month';
    $scope.visits = '';
    $scope.visitors = '';
    $scope.pageviews = '';
    $scope.bounceRate = '';
    $scope.percentNewSessions = '';
    $scope.popularPages = [];
    $scope.SelectedValueforMetric = 'newUsers';
    
    $scope.loadAllAnalyticsData = function(){
        $scope.googleAnalyticDataReport();
        $scope.googleAnalyticsLineChart();        
        $scope.googleAnalyticsOSChart();
        $scope.googleAnalyticsBrowserChart();
        $scope.googleAnalyticsDeviceTypeChart();
        $scope.googleAnalyticsRegisteredUsers();
    };
    
    $scope.ChangeLineChart = function(filter){
        $(".tab-analytics a").removeClass("active");
        $("#"+filter).addClass("active");
        $scope.subfilter = filter;
        $scope.googleAnalyticsLineChart();
    };
        
    //Function for get google line chart data
    $scope.googleAnalyticsLineChart = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            SubFilter:$scope.subfilter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        $("#linechartloaderdiv").show();
        googleAnalyticsChartData.googleAnalyticsLineChartData(reqData).then(function (response) {
            $("#linechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Email analytics  */
                if ($.isEmptyObject(response.Data.lineData) == true) {
                    $("#googleLineChart").html('<div class="no-signups-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawGoogleAnalyticsLineChart(response.Data.lineData, $scope.subfilter));
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
    
    $scope.googleAnalyticDataReport = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        $scope.visits = '';
        $scope.visitors = '';
        $scope.pageviews = '';
        $scope.bounceRate = '';
        $scope.percentNewSessions = '';
        $("#reportLoaderDiv").show();
        googleAnalyticsChartData.googleAnalyticDataReport(reqData).then(function (response) {
            $("#reportLoaderDiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                var GaReport = response.Data.reportData;
                $scope.visits = GaReport.visits;
                $scope.visitors = GaReport.visitors;
                $scope.pageviews = GaReport.pageviews;
                $scope.bounceRate = GaReport.bounceRate;
                $scope.percentNewSessions = GaReport.percentNewSessions;
                
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
    
    //Function for get google analytics OS chart data
    $scope.googleAnalyticsOSChart = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            SubFilter:$scope.subfilter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        $("#oschartloaderdiv").show();
        googleAnalyticsChartData.googleAnalyticsOSChartData(reqData).then(function (response) {
            $("#oschartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics OS */
                if ($.isEmptyObject(response.Data.osData) == true) {
                    $("#googleAnalyticOSChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('martop0');
                }else{
                    $("#googleAnalyticOSChart").removeClass('martop0');
                    google.setOnLoadCallback(drawGoogleAnalyticsOSChart(response.Data.osData));
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
    
    //Function for get google analytics browser chart data
    $scope.googleAnalyticsBrowserChart = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            SubFilter:$scope.subfilter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        $("#browserchartloaderdiv").show();
        googleAnalyticsChartData.googleAnalyticsBrowserChart(reqData).then(function (response) {
            $("#browserchartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics Browser */
                if ($.isEmptyObject(response.Data.BrowserData) == true) {
                    $("#googleAnalyticsBrowserChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('martop0');
                }else{
                    $("#googleAnalyticsBrowserChart").removeClass('martop0');
                    google.setOnLoadCallback(drawGoogleAnalyticsBrowserChart(response.Data.BrowserData));
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
    
    //Function for get google analytics device type chart data
    $scope.googleAnalyticsDeviceTypeChart = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            SubFilter:$scope.subfilter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        $("#devicetypechartloaderdiv").show();
        googleAnalyticsChartData.googleAnalyticsDeviceTypeChart(reqData).then(function (response) {
            $("#devicetypechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics Device Type */
                if ($.isEmptyObject(response.Data.DeviceData) == true) {
                    $("#googleAnalyticsDeviceTypeChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('martop0');
                }else{
                    $("#googleAnalyticsDeviceTypeChart").removeClass('martop0');
                    google.setOnLoadCallback(drawGoogleAnalyticsDeviceTypeChart(response.Data.DeviceData));
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
    
    $scope.googleAnalyticPopularPages = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        $scope.popularPages = [];
        
        $("#pagesLoaderDiv").show();
        googleAnalyticsChartData.googleAnalyticPopularPages(reqData).then(function (response) {
            $("#pagesLoaderDiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.popularPages = response.Data.popularPages;
                
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
    
    $scope.googleAnalyticsGeoChart = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
                
        $("#geoChartLoaderdiv").show();
        googleAnalyticsChartData.googleAnalyticsGeoChart(reqData).then(function (response) {
            $("#geoChartLoaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){                
                /* Draw google chart for google analytics Geo location */
                if ($.isEmptyObject(response.Data.geoLocation) == true) {
                    $("#googleAnalyticsGeoChart").html('<div class="no-location-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawGoogleAnalyticsGeoChart(response.Data.geoLocation));
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
    
    
    $scope.googleAnalyticsRegisteredUsers = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
                
        //$("#geoChartLoaderdiv").show();
        googleAnalyticsChartData.googleAnalyticsRegisteredUsers(reqData).then(function (response) {
            //$("#pagesLoaderDiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.registeredNumberOfUsers = response.Data.registeredNumberOfUsers;
                
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

});