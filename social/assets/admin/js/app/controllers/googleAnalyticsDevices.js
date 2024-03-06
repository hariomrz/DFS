// google analytics device info Controller for make google Analytics chart
app.controller('googleAnalyticsDevicesCtrl', function ($scope, googleAnalyticsDevicesData,$window) {
    $scope.SelectedValueforMetric = 'newUsers';
    $scope.visits = '';
    $scope.pageviews = '';
    $scope.users = '';
    $scope.newusers = '';
    $scope.sessions = '';
       
    $scope.loadAllAnalyticsDeviceData = function(){
        $scope.googleAnalyticsOSChart();
        $scope.googleAnalyticsBrowserChart();
        $scope.googleAnalyticDeviceDataReport();
        $scope.googleAnalyticsDeviceTypeChart();
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
        googleAnalyticsDevicesData.googleAnalyticsOSChartData(reqData).then(function (response) {
            $("#oschartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics OS */
                google.setOnLoadCallback(drawGoogleAnalyticsOSChart(response.Data.osData));
                
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
        googleAnalyticsDevicesData.googleAnalyticsBrowserChart(reqData).then(function (response) {
            $("#browserchartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics Browser */
                google.setOnLoadCallback(drawGoogleAnalyticsBrowserChart(response.Data.BrowserData));
                
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
    
    $scope.googleAnalyticDeviceDataReport = function () {
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
        $scope.pageviews = '';
        $scope.users = '';
        $scope.newusers = '';
        $scope.sessions = '';
        $("#reportLoaderDiv").show();
        googleAnalyticsDevicesData.googleAnalyticDeviceDataReport(reqData).then(function (response) {
            $("#reportLoaderDiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                var GaReport = response.Data.reportData;
                $scope.visits = GaReport.visits;
                $scope.pageviews = GaReport.pageviews;
                $scope.users = GaReport.users;                
                $scope.newusers = GaReport.newusers;
                $scope.sessions = GaReport.sessions;
                
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
        googleAnalyticsDevicesData.googleAnalyticsDeviceTypeChart(reqData).then(function (response) {
            $("#devicetypechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics Device Type */
                google.setOnLoadCallback(drawGoogleAnalyticsDeviceTypeChart(response.Data.DeviceData));
                
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