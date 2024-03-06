// User Controller for make Signup Analytics chart
app.controller('signupAnalyticsCtrl', function ($scope, signupAnalyticsChartData,$window) {
    
    //Function for get Signup line chart data
    $scope.signupAnalyticsChart = function () {
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
        }
       
        signupAnalyticsChartData.signupAnalyticsData(reqData).then(function (response) {
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for analytics  */
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupcount_label").html('0');
                    $("#signupLineChart").html('<div class="no-signups-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawSignupLineChart(response.Data, $scope.filter));
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

    //Function for get source of signups chart data
    $scope.signupSourceSignupChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("signupSourceSignupChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        signupAnalyticsChartData.signupSourceSignupChart(reqData).then(function (response) {
            HideAnalyticLoader("signupSourceSignupChart");
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Source of signup chart */
                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupSourceSignupChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $("#signupSourceSignupChart").removeClass('top0');
                    google.setOnLoadCallback(drawSourceSignupChart(response.Data));
                }
                $scope.signupTypeChart();
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

    //Function for get type chart data
    $scope.signupTypeChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("signupTypeChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        signupAnalyticsChartData.signupTypeChart(reqData).then(function (response) {
            HideAnalyticLoader("signupTypeChart");
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Signup type */
                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupTypeChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $("#signupTypeChart").removeClass('top0');
                    google.setOnLoadCallback(drawSignupTypeChart(response.Data));
                }
                $scope.signupDeviceChart();
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
    
    //Function for get device chart data
    $scope.signupDeviceChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        ShowAnalyticLoader("signupDeviceChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        signupAnalyticsChartData.signupDeviceChart(reqData).then(function (response) {
            HideAnalyticLoader("signupDeviceChart");
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Signup Device */
                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupDeviceChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $("#signupDeviceChart").removeClass('top0');
                    google.setOnLoadCallback(drawSignupDeviceChart(response.Data));
                }
                $scope.signupVisitSignupChart();
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

    //Function for get Visits/Signups chart data
    $scope.signupVisitSignupChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("signupVisitSignupChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        signupAnalyticsChartData.signupVisitSignupChart(reqData).then(function (response) {
            HideAnalyticLoader("signupVisitSignupChart");
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Signup Visits/Signup */
                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupVisitSignupChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $("#signupVisitSignupChart").removeClass('top0');
                    google.setOnLoadCallback(drawSignupVisitSignupChart(response.Data));
                }
                //$scope.signupTimeChart();
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

    //Function for Signup Time chart data
    $scope.signupTimeChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("signupTimeChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        signupAnalyticsChartData.signupTimeChart(reqData).then(function (response) {
            HideAnalyticLoader("signupTimeChart");
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Signup Time */
                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupTimeChart").html('<div class="no-timetaken-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $("#signupTimeChart").removeClass('top0');
                    google.setOnLoadCallback(drawSignupTimeChart(response.Data));
                }
                //$scope.signupPopDaysChart();
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

    //Function for get PopularDays Signup chart data
    $scope.signupPopDaysChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("signupPopDaysChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        signupAnalyticsChartData.signupPopDaysChart(reqData).then(function (response) {
            HideAnalyticLoader("signupPopDaysChart");
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Signup Popular Days */                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupPopDaysChart").html('<div class="no-timetaken-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $("#signupPopDaysChart").removeClass('top0');
                    google.setOnLoadCallback(drawSignupPopDaysChart(response.Data));
                }
                //$scope.signupPopTimeChart();
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

    //Function for get PopularTime Signup chart data
    $scope.signupPopTimeChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("loaderdiv");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        signupAnalyticsChartData.signupPopTimeChart(reqData).then(function (response) {
            /* Draw google chart for Signup Popular Time */
            HideAnalyticLoader("loaderdiv");
            if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);                
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                if (response != null)
                {
                    if (response.Data.length > 0)
                    {
                        $("#signupPopTimeChart").find(".figwrap").removeClass('hide');
                        $("#populartimechart").addClass("hide");
                        $("#dvAMChart").show();

                        $("#spnAM1").html("0"); $("#spnAM2").html("0"); $("#spnAM3").html("0"); $("#spnAM4").html("0");
                        $("#spnPM1").html("0"); $("#spnPM2").html("0"); $("#spnPM3").html("0"); $("#spnPM4").html("0");

                        for (var i = 0; i < response.Data.length; i++)
                        {
                            if (response.Data[i].TimeSlotID < 5)
                            {
                                switch (response.Data[i].TimeSlotID)
                                {
                                    case '1':
                                        $("#spnAM2").html(response.Data[i].SignUpCount);
                                    break;

                                    case '2':
                                        $("#spnAM3").html(response.Data[i].SignUpCount);
                                    break;

                                    case '3':
                                        $("#spnAM4").html(response.Data[i].SignUpCount);
                                    break;

                                    case '4':
                                        $("#spnAM1").html(response.Data[i].SignUpCount);
                                    break;
                                }
                            }

                            if (response.Data[i].TimeSlotID > 4)
                            {
                                $("#dvPMChart").show();
                                switch (response.Data[i].TimeSlotID)
                                {
                                    case '5':
                                        $("#spnPM2").html(response.Data[i].SignUpCount);
                                    break;

                                    case '6':
                                        $("#spnPM3").html(response.Data[i].SignUpCount);
                                    break;

                                    case '7':
                                        $("#spnPM4").html(response.Data[i].SignUpCount);
                                    break;

                                    case '8':
                                        $("#spnPM1").html(response.Data[i].SignUpCount);
                                    break;
                                }
                            }

                        }
                    }else{
                        $("#dvAMChart").show();
                        $("#dvPMChart").show();

                        $("#spnAM1").html("0"); $("#spnAM2").html("0"); $("#spnAM3").html("0"); $("#spnAM4").html("0");
                        $("#spnPM1").html("0"); $("#spnPM2").html("0"); $("#spnPM3").html("0"); $("#spnPM4").html("0");
                        $("#signupPopTimeChart").find(".figwrap").addClass('hide');
                        $("#populartimechart").removeClass("hide").html('<div class="no-populardays-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                    }
                }
            }
            //$scope.signupGeoChart();
        });
    };

    //Function for get Signup Geo chart data
    $scope.signupGeoChart = function () {
        //get starting date/end_date from top selected date and AdminLoginSessionKey and a filter on right side
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        //1 for signup_geo, 2 for visit_geo
        $scope.RightFilter = $('#RightFilter').val();
        var geotype = "Sign Up Count";
        if($scope.RightFilter == 2){
            geotype = "Visit Count";
        }
        ShowAnalyticLoader("signupGeoChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey,
            RightFilter :$scope.RightFilter
        }
        signupAnalyticsChartData.signupGeoChart(reqData).then(function (response) {
            HideAnalyticLoader("signupGeoChart");
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Signup Geo location */                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#signupGeoChart").html('<div class="no-location-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawSignupGeoChart(response.Data,geotype));
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

});