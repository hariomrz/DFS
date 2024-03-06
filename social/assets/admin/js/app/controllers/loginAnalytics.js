// User Controller for make Login Analytics chart
app.controller('loginAnalyticsCtrl', function ($scope, loginAnalyticsChartData,$window) {
    
    //Function for get login line chart data
    $scope.loginAnalyticsChart = function () {
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
        
        loginAnalyticsChartData.loginAnalyticsData(reqData).then(function (response) {
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Email analytics  */
                if ($.isEmptyObject(response.Data) == true) {
                    $("#logincount_label").html('0');
                    $("#loginLineChart").html('<div class="no-signups-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    //google.setOnLoadCallback(drawLoginLineChart(response.Data, $scope.filter));
                    $scope.drawAnalyticsLineChart(response.Data, $scope.filter);
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
    var myChart = [];
    $scope.drawAnalyticsLineChart = function (data, filter) {
        data1 = [];
        data2 = [];
        if (filter == 0 || filter == 1) 
        {
            $.each(data, function (i)
            {
                data1.push(data[i].MonthName);
                data2.push(data[i].LoginCount);
            });
        }
        else if (filter == 0 || filter == 2) 
        {
            $.each(data, function (i)
            {
                data1.push('week '+data[i].WeekNumber+'('+data[i].Years+')');
                data2.push(data[i].LoginCount);
            });
        }
        else
        {
            $.each(data, function (i)
            {
                data1.push(data[i].CreatedDate);
                data2.push(data[i].LoginCount);
            });
        }
        if(myChart.length!=0)
        {
            myChart.destroy();
        }

        var ctx = document.getElementById( "loginLineChart" );
        ctx.height = 50;
        myChart = new Chart( ctx, {
            type: 'line',
            data: {
                labels: data1,
                type: 'line',
                defaultFontFamily: 'Montserrat',
                datasets: [ {
                    data: data2,
                    label: "Count",
                    backgroundColor: 'rgba(0,103,255,.15)',
                    borderColor: 'rgba(0,103,255,0.5)',
                    borderWidth: 3.5,
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointBorderColor: 'transparent',
                    pointBackgroundColor: 'rgba(0,103,255,0.5)',
                        }, ]
            },
            options: {
                responsive: true,
                tooltips: {
                    mode: 'index',
                    titleFontSize: 12,
                    titleFontColor: '#000',
                    bodyFontColor: '#000',
                    backgroundColor: '#fff',
                    titleFontFamily: 'Montserrat',
                    bodyFontFamily: 'Montserrat',
                    cornerRadius: 3,
                    intersect: false,
                },
                legend: {
                    display: false,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        fontFamily: 'Montserrat',
                    },


                },
                scales: {
                    xAxes: [ {
                        display: true,
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        scaleLabel: {
                            display: false,
                            labelString: 'Month'
                        }
                            } ],
                    yAxes: [ {
                        display: true,
                        gridLines: {
                            display: true,
                            drawBorder: true
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                            } ]
                },
                title: {
                    display: false,
                }
            }
        } );
        myChart.render();
    }
    $scope.drawGoogleAnalyticsChart = function(data,ctx,title)
    {
        Highcharts.chart(ctx, {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: title
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y} ({point.percentage:.1f}%)</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                name: 'Count',
                colorByPoint: true,
                data: data
            }]
        });
    }
    //Function for get source of login chart data
    $scope.no_data_source = false;
    $scope.loginSourceLoginChart = function () {        
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        ShowAnalyticLoader("SourceLoginChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        loginAnalyticsChartData.SourceLoginChart(reqData).then(function (response) {
            HideAnalyticLoader("SourceLoginChart");
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Source of login chart */
                $scope.no_data_source = true;
                if ($.isEmptyObject(response.Data) == true) {
                    $("#SourceLoginChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $scope.no_data_source = false;
                    $(".loginchart_div").removeClass('top0');
                    data = [];
                    $.each(response.Data, function (i)
                    {
                        data.push({name:response.Data[i].SourceName,y:parseInt(response.Data[i].LoginCount)});
                    });
                    //google.setOnLoadCallback(drawSourceLoginChart(response.Data));
                    var ctx = document.getElementById( "SourceLoginChart" );
                    $scope.drawGoogleAnalyticsChart(data,ctx,"SOURCES OF LOGINS");
                }                
                $scope.loginDeviceChart();
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
    $scope.no_data_device = false;
    //Function for get device chart data
    $scope.loginDeviceChart = function () {        
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        ShowAnalyticLoader("loginDeviceChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        loginAnalyticsChartData.loginDeviceChart(reqData).then(function (response) {
            HideAnalyticLoader("loginDeviceChart");
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Login Device */
                $scope.no_data_device = true;
                if ($.isEmptyObject(response.Data) == true) {
                    $("#loginDeviceChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $scope.no_data_device = false;
                    $(".loginchart_div").removeClass('top0');
                    //google.setOnLoadCallback(drawLoginDeviceChart(response.Data));
                    data = [];
                    $.each(response.Data, function (i)
                    {
                        data.push({name:response.Data[i].DeviceTypeName,y:parseInt(response.Data[i].LoginCount)});
                    });
                    var ctx = document.getElementById( "loginDeviceChart" );
                    $scope.drawGoogleAnalyticsChart(data,ctx,"DEVICES");
                }
                
                $scope.loginUsernameEmailChart();
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


    $scope.no_data_nameemail = false;
    //Function for get Username/Email chart data
    $scope.loginUsernameEmailChart = function () {        
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("loginUsernameEmailChart");
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        loginAnalyticsChartData.loginUsernameEmailChart(reqData).then(function (response) {
            HideAnalyticLoader("loginUsernameEmailChart");
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Login Username/Email */                
                $scope.no_data_nameemail = true;
                if ($.isEmptyObject(response.Data) == true) {
                    $("#loginUsernameEmailChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $scope.no_data_nameemail = false;
                    $(".loginchart_div").removeClass('top0');
                    //google.setOnLoadCallback(drawLoginUsernameEmailChart(response.Data));
                    data = [];
                    $.each(response.Data, function (i)
                    {
                        data.push({name:response.Data[i].UserNameVsEmail,y:parseInt(response.Data[i].LoginCount)});
                    });
                    var ctx = document.getElementById( "loginUsernameEmailChart" );
                    $scope.drawGoogleAnalyticsChart(data,ctx,"LOGIN : USER NAME VS EMAIL");
                }
                $scope.loginFirstTimeChart();
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

    $scope.no_data_firstlogin = false;
    //Function for get FirstTime Login chart data
    $scope.loginFirstTimeChart = function () {
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("loginFirstTimeChart");
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        loginAnalyticsChartData.loginFirstTimeChart(reqData).then(function (response) {
            HideAnalyticLoader("loginFirstTimeChart");
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Login First Time */                
                $scope.no_data_firstlogin = true;
                if ($.isEmptyObject(response.Data) == true) {
                    $("#loginFirstTimeChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $scope.no_data_firstlogin = false;
                    $(".loginchart_div").removeClass('top0');
                    //google.setOnLoadCallback(drawLoginFirstTimeChart(response.Data));
                    data = [];
                    $.each(response.Data, function (i)
                    {
                        data.push({name:response.Data[i].Type,y:parseInt(response.Data[i].LoginCount)});
                    });
                    var ctx = document.getElementById( "loginFirstTimeChart" );
                    $scope.drawGoogleAnalyticsChart(data,ctx,"FIRST TIME LOGIN");
                }
                //$scope.loginPopDaysChart();
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

    //Function for get PopularDays Login chart data
    $scope.loginPopDaysChart = function () {
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("loginPopDaysChart");
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        loginAnalyticsChartData.loginPopDaysChart(reqData).then(function (response) {
            HideAnalyticLoader("loginPopDaysChart");
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Login Popular Days */
                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#loginPopDaysChart").html('<div class="no-timetaken-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $(".populardaychart").removeClass('top0');
                    //google.setOnLoadCallback(drawLoginPopDaysChart(response.Data));
                    var labels = [];
                    var data = [];
                    $.each(response.Data, function (i)
                    {
                        labels.push(response.Data[i].WeekDayName);
                        data.push(parseInt(response.Data[i].LoginCount));
                    });
                    var ctx = document.getElementById( "loginPopDaysChart" );
                    $scope.plotSingleBarChart(labels,data,ctx);
                }
                //$scope.loginPopTimeChart();
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
    var myBarChart = [];
    $scope.plotSingleBarChart = function(labels,data,ctx)
    {
        if(myBarChart.length!=0)
        {
            myBarChart.destroy();
        }
        // single bar chart
        //var ctx = document.getElementById( "singelBarChart" );
        ctx.height = 120;
        myBarChart = new Chart( ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Login Count",
                        data: data,
                        borderColor: "rgba(0, 123, 255, 0.9)",
                        borderWidth: "0",
                        backgroundColor: "rgba(0, 123, 255, 0.5)"
                                }
                            ]
            },
            options: {
                scales: {
                    yAxes: [ {
                        ticks: {
                            beginAtZero: true
                        }
                                    } ]
                }
            }
        } );
    }

    //Function for get PopularTime Login chart data
    $scope.loginPopTimeChart = function () {
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
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
        loginAnalyticsChartData.loginPopTimeChart(reqData).then(function (response) {
            HideAnalyticLoader("loaderdiv");
            /* Draw google chart for Login Popular Time */
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
                        $("#loginPopTimeChart").find(".figwrap").removeClass('hide');
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
                                        $("#spnAM2").html(response.Data[i].LoginCount);
                                    break;

                                    case '2':
                                        $("#spnAM3").html(response.Data[i].LoginCount);
                                    break;

                                    case '3':
                                        $("#spnAM4").html(response.Data[i].LoginCount);
                                    break;

                                    case '4':
                                        $("#spnAM1").html(response.Data[i].LoginCount);
                                    break;
                                }
                            }

                            if (response.Data[i].TimeSlotID > 4)
                            {
                                $("#dvPMChart").show();
                                switch (response.Data[i].TimeSlotID)
                                {
                                    case '5':
                                        $("#spnPM2").html(response.Data[i].LoginCount);
                                    break;

                                    case '6':
                                        $("#spnPM3").html(response.Data[i].LoginCount);
                                    break;

                                    case '7':
                                        $("#spnPM4").html(response.Data[i].LoginCount);
                                    break;

                                    case '8':
                                        $("#spnPM1").html(response.Data[i].LoginCount);
                                    break;
                                }
                            }

                        }
                    }else{
                        $("#dvAMChart").show();
                        $("#dvPMChart").show();

                        $("#spnAM1").html("0"); $("#spnAM2").html("0"); $("#spnAM3").html("0"); $("#spnAM4").html("0");
                        $("#spnPM1").html("0"); $("#spnPM2").html("0"); $("#spnPM3").html("0"); $("#spnPM4").html("0");
                        $("#loginPopTimeChart").find(".figwrap").addClass('hide');
                        $("#populartimechart").removeClass("hide").html('<div class="no-populardays-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                    }
                }
            }
            
            //$scope.loginFailureChart();
        });
    };

    //Function for get Login Failure chart data
    $scope.loginFailureChart = function () {return false;
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("loginFailureChart");
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        loginAnalyticsChartData.loginFailureChart(reqData).then(function (response) {
            HideAnalyticLoader("loginFailureChart");
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Login Popular Days */
                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#loginFailureChart").html('<div class="no-acceptance-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('top0');
                }else{
                    $("#loginFailureChart").removeClass('top0');
                    google.setOnLoadCallback(drawLoginFailureChart(response.Data));
                }
                //$scope.loginGeoChart();
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

    //Function for get Login Geo chart data
    $scope.loginGeoChart = function () {
        //get starting date/end_date from top selected date,day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        ShowAnalyticLoader("loginGeoChart");
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        loginAnalyticsChartData.loginGeoChart(reqData).then(function (response) {
            HideAnalyticLoader("loginGeoChart");
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Login Geo location */
                if ($.isEmptyObject(response.Data) == true) {
                    $("#loginGeoChart").html('<div class="no-location-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawLoginGeoChart(response.Data));
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
