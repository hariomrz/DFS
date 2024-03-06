// google analytics Controller for make google Analytics chart
app.controller('googleAnalyticsCtrl', function ($scope, googleAnalyticsChartData,$window,loginDashboard) {
    $scope.subfilter = 'month';
    $scope.visits = '';
    $scope.visitors = '0';
    $scope.pageviews = '';
    $scope.bounceRate = '';
    $scope.percentNewSessions = '';
    $scope.popularPages = [];
    $scope.SelectedValueforMetric = 'newUsers';
    $scope.ImageServerPath=image_server_path;
    $scope.is_registered_tab = false;
    $scope.dateRangeFilterOptions = [
            {label : 'All',  fval:"all"},
            {label : 'Today', fval:"today"},
            {label : 'Yesterday', fval:"yesterday"},            
            {label : 'This week', fval:"thisweek"},            
            {label : 'This month', fval:"thismonth"},
            {label : 'Last 3 Months', fval:"threemonths"},
            {label : 'This year', fval:"thisyear"}            
        ];
     $scope.getDefaultImgPlaceholder = function(name) {
          name = name.split(' ');
          if(name.length == 1)
          {
              name = name[0];
          }
          if(name.length > 1)
          {
            name = name[0].substring(1, 0) + name[1].substring(1, 0);
          }
          return name.toUpperCase();
      }    
    $scope.loadAllAnalyticsData = function(){ console.log('calling filter date');
        if($scope.load_data_report==true)
        {
            $scope.googleAnalyticDataReport();
        }
        $scope.load_data_report=true;
        /*$scope.googleAnalyticsLineChart();        
        $scope.googleAnalyticsOSChart();
        $scope.googleAnalyticsBrowserChart();
        $scope.googleAnalyticsDeviceTypeChart();
        $scope.googleAnalyticsRegisteredUsers();
        $scope.getUserEngagementData();*/
        $scope.googleAnalyticsGeoChart1();

        $scope.googleAnalyticsRegisteredUsers();
        $scope.googleAnalyticDataReport();
        $scope.topInfluencers();
        $scope.topContributors();
        $scope.getSummary();
    };
    $scope.load_data_report = true;
    $scope.filterStats = function(filterType)
    {
        $("#filter_val").val(filterType);
        $scope.filter = filterType;
        $scope.load_data_report = false;
        if(filterType=='registeredUsers'){
            $scope.is_registered_tab = true;
        }
        else
        {
            $scope.is_registered_tab = false;
        }
        //console.log($("#filter_val").val());
        $scope.loadAllAnalyticsData();
    }
    
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
                    $scope.drawGoogleAnalyticsLineChart(response.Data.lineData, $scope.subfilter);
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

    //Function for Draw google analytics Line chart on GoogleAnalytic Page
    var myChart = [];
    var osChart = [];
    $scope.drawGoogleAnalyticsLineChart = function (data, subfilter) { 
       /* data1 = [];
        data2 = [];
        $.each(data, function (i)
        {
            data1.push(data[i].date);
            data2.push(data[i].pageview);
        });
        if(myChart.length!=0)
        {
            myChart.destroy();
        }

        var ctx = document.getElementById( "team-chart" );
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
        myChart.render();*/

    }
                
    
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
    $scope.show_engagement_chart = false;
    $scope.getUserEngagementData = function()
    {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        var reqData = {
            FromDate : $scope.startDate,
            ToDate : $scope.endDate,
            Filter : $scope.filter,
            AdminLoginSessionKey : $scope.AdminLoginSessionKey,
            TypeExtra:1
        };
        $("#engagementchartloaderdiv").show();
        loginDashboard.getLoginDashboard(reqData).then(function (response) {
            $("#engagementchartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics OS */
                if ($.isEmptyObject(response.Data.user_engagement) == true || (response.Data.user_engagement[0]['ActiveUsers'] == 0 && response.Data.user_engagement[0]['EngageUsers'] == 0)) {
                    $("#googleAnalyticEngagementChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('martop0');
                    $scope.show_engagement_chart = false;
                }else{
                    $scope.show_engagement_chart = true;
                    //google.setOnLoadCallback(drawGoogleAnalyticsOSChart(response.Data.osData));
                    var ctx = document.getElementById( "userEngagementPieChart" );
                    $scope.UserEngagementData = response.Data.user_engagement[0];
                    $scope.TotalUsersEng = parseInt($scope.UserEngagementData['TotalUsers']);
                    $scope.TotalUsersCountEng = parseInt($scope.UserEngagementData['TotalUsersCount']);
                    $scope.ActiveUsersEng = parseInt($scope.UserEngagementData['ActiveUsers']);
                    $scope.InActiveUsersEng = parseInt($scope.TotalUsersEng-$scope.ActiveUsersEng);
                    $scope.EngageUsers = parseInt($scope.UserEngagementData['EngageUsers']);
                    data = [{name:"Inactive Users",y:parseInt($scope.InActiveUsersEng)},{name:"Engaged Users",y:parseInt($scope.EngageUsers)},{name:"Logged In Users",y:parseInt($scope.ActiveUsersEng)}];
                    /*if($scope.engagedChart!=undefined)
                    {
                        $scope.engagedChart.destroy();
                    }*/
                    $scope.engagedChart = $scope.drawGoogleAnalyticsChart(data,'userEngagementPieChart',"USER ENGAGEMENT");
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
    }
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
        $scope.show_os_chart = false;
        googleAnalyticsChartData.googleAnalyticsOSChartData(reqData).then(function (response) {
            $("#oschartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for google analytics OS */
                if ($.isEmptyObject(response.Data.osData) == true) {
                    $("#googleAnalyticOSChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').addClass('martop0');
                    $scope.show_os_chart = false;
                }else{
                    $scope.show_os_chart = true;
                    $("#googleAnalyticOSChart").removeClass('martop0');
                    //google.setOnLoadCallback(drawGoogleAnalyticsOSChart(response.Data.osData));
                    var ctx = document.getElementById( "osPieChart" );
                    data = [];
                    $.each(response.Data.osData, function (i)
                    {
                        data.push({name:response.Data.osData[i].operatingsystem,y:parseInt(response.Data.osData[i].Count)});
                    });
                    /*if($scope.osChart!=undefined)
                    {
                        $scope.osChart.destroy();
                    }*/
                    $scope.osChart = $scope.drawGoogleAnalyticsChart(data,'osPieChart','OS VERSION');
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
    $scope.drawGoogleAnalyticsChart = function(data,ctx,title,show_percentage)
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
        return osChart;
    }
    


    //Function for get google analytics browser chart data
    $scope.show_browser_chart = false;
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
                    $scope.show_browser_chart = false;
                }else{
                    $scope.show_browser_chart = true;
                    //google.setOnLoadCallback(drawGoogleAnalyticsBrowserChart(response.Data.BrowserData));
                    var ctx = document.getElementById( "browserPieChart" );
                    data = [];
                    $.each(response.Data.BrowserData, function (i)
                    {
                        data.push({name:response.Data.BrowserData[i].Browser,y:parseInt(response.Data.BrowserData[i].Count)});
                    });
                    console.log(data);
                    /*if($scope.browserChart!=undefined)
                    {
                        $scope.browserChart.destroy();
                    }*/
                    $scope.browserChart = $scope.drawGoogleAnalyticsChart(data, "browserPieChart",'BROWSER VERSION');
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
    $scope.show_device_chart = false;
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
                    $scope.show_device_chart = false;
                }else{
                    $scope.show_device_chart = true;
                    //google.setOnLoadCallback(drawGoogleAnalyticsDeviceTypeChart(response.Data.DeviceData));
                    var ctx = document.getElementById( "devicePieChart" );
                    data = [];
                    $.each(response.Data.DeviceData, function (i)
                    {
                        data.push({name:response.Data.DeviceData[i].Device,y:parseInt(response.Data.DeviceData[i].Count)});
                    });
                    /*if($scope.deviceChart!=undefined)
                    {
                        $scope.deviceChart.destroy();
                    }*/
                    $scope.deviceChart = $scope.drawGoogleAnalyticsChart(data,'devicePieChart',"DEVICE TYPE");
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
    
    $scope.googleAnalyticsGeoChart1 = function () {
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
            //$("#geoChartLoaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){                
                /* Draw google chart for google analytics Geo location */
                if ($.isEmptyObject(response.Data.geoLocation) == true) {
                    $("#googleAnalyticsGeoChart").html('<div class="no-location-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                   // google.setOnLoadCallback($scope.drawGoogleAnalyticsGeoChart(response.Data.geoLocation));
                    //location map added
                    visitorsData = response.Data.geoLocation;
                    /*console.log('visitorsData1',visitorsData1)
                     var visitorsData = {
                        'US': 398, //USA
                        'SA': 400, //Saudi Arabia
                        'CA': 1000, //Canada
                        'DE': 500, //Germany
                        'FR': 760, //France
                        'CN': 300, //China
                        'AU': 700, //Australia
                        'BR': 600, //Brazil
                        'IN': 1234, //India
                        'GB': 320, //Great Britain
                        'RU': 3000 //Russia
                      }*/
                     // console.log('visitorsData',visitorsData)
                      // World map by jvectormap
                      $('#world-map-countrywise').html('');
                      
                      $('#world-map-countrywise').vectorMap({
                        map              : 'world_mill_en',
                        backgroundColor  : 'transparent',
                        regionStyle      : {
                          initial: {
                            fill            : 'rgba(255, 255, 255, 0.7)',
                            'fill-opacity'  : 1,
                            stroke          : 'rgba(0,0,0,.2)',
                            'stroke-width'  : 1,
                            'stroke-opacity': 1
                          }
                        },
                        series           : {
                          regions: [{
                            values           : visitorsData,
                            scale            : ['#ffffff', '#0154ad'],
                            normalizeFunction: 'polynomial'
                          }]
                        },
                        onRegionLabelShow: function (e, el, code) {
                          if (typeof visitorsData[code] != 'undefined')
                            el.html(el.html())
                        }
                      })


                    //location map added

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
    //Function for Draw google analytics GeoLocation Chart on google analytics page
    $scope.drawGoogleAnalyticsGeoChart = function(data) {

        var data1 = [];
        $.each(data, function (i)
        {
            data1.push({
                Location: data[i].Location,
                Count: data[i].pageviews
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'Location');
        tdata.addColumn('number', 'Count');

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([data1[i].Location, parseInt(data1[i].Count)]);
        }

        var options = {
            backgroundColor: 'white',
            datalessRegionColor: 'F5F5F5',
            colorAxis: {colors: ['#3090C7', '#438D80']},
            sizeAxis: {minValue: 0, maxValue: 100, minSize: 5},
            displayMode: 'markers',
        };
        tooltip: {
            trigger: 'none'
        }
        ;

        var geochart = new google.visualization.GeoChart(document.getElementById('googleAnalyticsGeoChart'));
        geochart.draw(tdata, options);
    }
    
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
                $scope.TotalLikes = response.Data.TotalLikes;
                $scope.TotalComments = response.Data.TotalComments;
                $scope.TotalPosts = response.Data.TotalPosts;
                $scope.TotalActiveUsers = response.Data.TotalActiveUsers;
                $scope.TotalVisiters = response.Data.TotalVisiters;
                $scope.googleAnalyticsRegisteredUsersCompare();
                
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
    $scope.googleAnalyticsRegisteredUsersCompare = function () { 

        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFromCompare').val();
        $scope.endDate = $('#SpnToCompare').val();
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
                $scope.registeredNumberOfUsersCompare =Math.round((($scope.registeredNumberOfUsers-response.Data.registeredNumberOfUsers)/response.Data.registeredNumberOfUsers)*100);
                $scope.TotalLikesCompare = Math.round((($scope.TotalLikes-response.Data.TotalLikes)/response.Data.TotalLikes)*100);
                $scope.TotalCommentsCompare = Math.round((($scope.TotalComments-response.Data.TotalComments)/response.Data.TotalComments)*100);
                $scope.TotalPostsCompare = Math.round((($scope.TotalPosts-response.Data.TotalPosts)/response.Data.TotalPosts)*100);
                $scope.TotalActiveUsersCompare = Math.round((($scope.TotalActiveUsers-response.Data.TotalActiveUsers)/response.Data.TotalActiveUsers)*100);
                $scope.TotalVisitersCompare = Math.round((($scope.TotalVisiters-response.Data.TotalVisiters)/response.Data.TotalVisiters)*100);
                //=============================================================================
                // Get context with jQuery - using jQuery's .get() method.
              var pieChartCanvas = $('#pieChart-Visitors').get(0).getContext('2d')
              var pieChart       = new Chart(pieChartCanvas)
              var PieData        = [
                {
                  value    :  Math.round(($scope.TotalVisiters-$scope.TotalActiveUsers)),
                  color    : '#dc3545',
                  highlight: '#dc3545',
                  label    : 'Lurkers'
                },
                {
                  value    : $scope.TotalActiveUsers,
                  color    : '#28a745',
                  highlight: '#28a745',
                  label    : 'Active Users'
                },
                
              ]

                   
               $scope.Visitorspaicolors = [{'colorText': 'Lurkers' ,'colorcode':'#dc3545'},{'colorText': 'Active Users' ,'colorcode':'#28a745'}];


              var pieOptions     = {
                //Boolean - Whether we should show a stroke on each segment
                segmentShowStroke    : true,
                //String - The colour of each segment stroke
                segmentStrokeColor   : '#fff',
                //Number - The width of each segment stroke
                segmentStrokeWidth   : 1,
                //Number - The percentage of the chart that we cut out of the middle
                percentageInnerCutout: 50, // This is 0 for Pie charts
                //Number - Amount of animation steps
                animationSteps       : 100,
                //String - Animation easing effect
                animationEasing      : 'easeOutBounce',
                //Boolean - Whether we animate the rotation of the Doughnut
                animateRotate        : true,
                //Boolean - Whether we animate scaling the Doughnut from the centre
                animateScale         : false,
                //Boolean - whether to make the chart responsive to window resizing
                responsive           : true,
                // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                maintainAspectRatio  : false,
                //String - A legend template
                legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
                //String - A tooltip template
                tooltipTemplate      : '<%=value %> <%=label%>'
              }
              //Create pie or douhnut chart
              // You can switch between pie and douhnut using the method below.
              pieChart.Doughnut(PieData, pieOptions)
              //=============================================================================
                
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
    $scope.statFilterName = 'All';
    $scope.onSelectDateRange = function(dateRangeFilterOption) {
        //$scope.startDate = dateRangeFilterOption.fromDate;
        //$scope.endDate = dateRangeFilterOption.toDate;
        $scope.dateRangeFilterOption = dateRangeFilterOption;
        /*if($scope.startDate || $scope.endDate) 
        {
            if($scope.dateRangeFilterOption) {
                $scope.statFilterName =  $scope.dateRangeFilterOption.label;
                SaveDates($scope.dateRangeFilterOption.fval);
            }
            else
            {
                $scope.statFilterName =  $scope.StartDate +' - '+ $scope.EndDate;
            }
        }
        else
        {
            $scope.statFilterName = 'All';
        }*/
        $scope.statFilterName =  $scope.dateRangeFilterOption.label;
        SaveDatesAnalytics($scope.dateRangeFilterOption.fval);
        $scope.loadAllAnalyticsData();
    }
    //top influnecer & contributors
    $scope.topContributors = function () {
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
        
        $scope.topContributorsRowData = '';
        $("#reportLoaderDiv").show();
        googleAnalyticsChartData.topContributors(reqData).then(function (response) {
            $("#reportLoaderDiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.topContributorsRowData = response.Data;
                
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

    $scope.topInfluencers = function () {
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
        
        $scope.topInfluencersRowData = '';
        $("#reportLoaderDiv").show();
        googleAnalyticsChartData.topInfluencers(reqData).then(function (response) {
            $("#reportLoaderDiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.topInfluencersRowData = response.Data;
                
                
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

    $scope.getSummary = function () {
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
        
        
        $("#reportLoaderDiv").show();
        googleAnalyticsChartData.getSummary(reqData).then(function (response) {
            $("#reportLoaderDiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                $scope.summaryData=[];
                $scope.summaryData.Timeslots = response.Data.Timeslots;
                $scope.summaryData.AndroidAppVersion = response.Data.AndroidAppVersion;
                $scope.summaryData.IOSAppVersion = response.Data.IOSAppVersion;
                $scope.summaryData.alexarank = response.Data.alexarank;

                //--------------------timeofloginchart and appverison chart-------------------
                 $(function () {
                    /* ChartJS
                     * -------
                     * Here we will create a few charts using ChartJS
                     */

                    
                    var areaChartData = {
                      labels  : ['12 AM - 3 AM', '3 AM - 6 AM', '6 AM - 9 AM', '9 AM - 12 PM', '12 PM - 3 PM', '3 PM- 6 PM', '6 PM - 9 PM','9 PM - 12 AM'],
                      datasets: [
                        {
                          label               : 'Electronics',
                          fillColor           : 'rgba(210, 214, 222, 1)',
                          strokeColor         : 'rgba(210, 214, 222, 1)',
                          pointColor          : 'rgba(210, 214, 222, 1)',
                          pointStrokeColor    : '#c1c7d1',
                          pointHighlightFill  : '#fff',
                          pointHighlightStroke: 'rgba(220,220,220,1)',
                          data                : $scope.summaryData.Timeslots
                        },
                        /*{
                          label               : 'Digital Goods',
                          fillColor           : 'rgba(60,141,188,0.9)',
                          strokeColor         : 'rgba(60,141,188,0.8)',
                          pointColor          : '#3b8bba',
                          pointStrokeColor    : 'rgba(60,141,188,1)',
                          pointHighlightFill  : '#fff',
                          pointHighlightStroke: 'rgba(60,141,188,1)',
                          data                : [28, 48, 40, 19, 86, 27, 90]
                        }*/
                      ]
                    }
                    var dynamicColors = function() {
                        var r = Math.floor(Math.random() * 255);
                        var g = Math.floor(Math.random() * 255);
                        var b = Math.floor(Math.random() * 255);
                        return "rgb(" + r + "," + g + "," + b + ")";
                    };
                    PieData = []; Androidpaicolors=[];
                    $.each(response.Data.AndroidAppVersion, function (i)
                    {       
                            var colorcode = dynamicColors();
                            Androidpaicolors.push({'colorText': response.Data.AndroidAppVersion[i].AndroidAppVersion ,'colorcode':colorcode});
                            PieData.push( 
                                {
                                    value    : response.Data.AndroidAppVersion[i].UsersCount,
                                    color    : colorcode,
                                    highlight: colorcode,
                                    label    : 'App Version  '+response.Data.AndroidAppVersion[i].AndroidAppVersion
                                }
                            );
                    });
                    $scope.Androidpaicolors = Androidpaicolors;

                    


                    PieDataIOS = []; IOSpaicolors=[];
                    $.each(response.Data.IOSAppVersion, function (i)
                    {       
                            var colorcode = dynamicColors();
                            IOSpaicolors.push({'colorText': response.Data.IOSAppVersion[i].IOSAppVersion ,'colorcode':colorcode});
                            PieDataIOS.push( 
                                {
                                    value    : response.Data.IOSAppVersion[i].UsersCount,
                                    color    : dynamicColors(),
                                    highlight: dynamicColors(),
                                    label    : 'App Version  '+response.Data.IOSAppVersion[i].IOSAppVersion
                                }
                            );
                    });
                    $scope.IOSpaicolors = IOSpaicolors;
                    //-------------
                    //- PIE CHART -
                    //-------------
                    // Get context with jQuery - using jQuery's .get() method.
                    var pieChartCanvas = $('#pieChart-Appversion').get(0).getContext('2d')
                    var pieChart       = new Chart(pieChartCanvas)
                    /*var PieData11        = [
                      {
                        value    : 700,
                        color    : dynamicColors(),
                        highlight: dynamicColors(),
                        label    : 'Chrome'
                      },
                      {
                        value    : 500,
                        color    : dynamicColors(),
                        highlight: dynamicColors(),
                        label    : 'IE'
                      },
                      {
                        value    : 400,
                        color    : dynamicColors(),
                        highlight: dynamicColors(),
                        label    : 'FireFox'
                      },
                      {
                        value    : 600,
                        color    : dynamicColors(),
                        highlight: dynamicColors(),
                        label    : 'Safari'
                      },
                      {
                        value    : 300,
                        color    : dynamicColors(),
                        highlight: dynamicColors(),
                        label    : 'Opera'
                      },
                      {
                        value    : 100,
                        color    : dynamicColors(),
                        highlight: dynamicColors(),
                        label    : 'Navigator'
                      }
                    ]*/
                    
                    var pieOptions     = {
                      //Boolean - Whether we should show a stroke on each segment
                      segmentShowStroke    : true,
                      //String - The colour of each segment stroke
                      segmentStrokeColor   : '#fff',
                      //Number - The width of each segment stroke
                      segmentStrokeWidth   : 2,
                      //Number - The percentage of the chart that we cut out of the middle
                      percentageInnerCutout: 50, // This is 0 for Pie charts
                      //Number - Amount of animation steps
                      animationSteps       : 100,
                      //String - Animation easing effect
                      animationEasing      : 'easeOutBounce',
                      //Boolean - Whether we animate the rotation of the Doughnut
                      animateRotate        : true,
                      //Boolean - Whether we animate scaling the Doughnut from the centre
                      animateScale         : false,
                      //Boolean - whether to make the chart responsive to window resizing
                      responsive           : true,
                      // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                      maintainAspectRatio  : true,
                      //String - A legend template
                      legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
                    }
                    //Create pie or douhnut chart
                    // You can switch between pie and douhnut using the method below.
                    pieChart.Doughnut(PieData, pieOptions)
                    var pieChartCanvasIOS = $('#pieChart-IOSAppversion').get(0).getContext('2d')
                    var pieChartIOS       = new Chart(pieChartCanvasIOS)
                    //ios pie chart start
                    var pieOptionsIOS     = {
                      //Boolean - Whether we should show a stroke on each segment
                      segmentShowStroke    : true,
                      //String - The colour of each segment stroke
                      segmentStrokeColor   : '#fff',
                      //Number - The width of each segment stroke
                      segmentStrokeWidth   : 2,
                      //Number - The percentage of the chart that we cut out of the middle
                      percentageInnerCutout: 50, // This is 0 for Pie charts
                      //Number - Amount of animation steps
                      animationSteps       : 100,
                      //String - Animation easing effect
                      animationEasing      : 'easeOutBounce',
                      //Boolean - Whether we animate the rotation of the Doughnut
                      animateRotate        : true,
                      //Boolean - Whether we animate scaling the Doughnut from the centre
                      animateScale         : false,
                      //Boolean - whether to make the chart responsive to window resizing
                      responsive           : true,
                      // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                      maintainAspectRatio  : true,
                      //String - A legend template
                      legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
                    }
                    //Create pie or douhnut chart
                    // You can switch between pie and douhnut using the method below.
                    pieChartIOS.Doughnut(PieDataIOS , pieOptionsIOS)
                    //ios pie chart end

                    //-------------
                    //- BAR CHART -
                    //-------------
                    var barChartCanvas                   = $('#barChart-Timeofuserslogin').get(0).getContext('2d')
                    var barChart                         = new Chart(barChartCanvas)
                    var barChartData                     = areaChartData
                    barChartData.datasets[0].fillColor   = '#00a65a'
                    barChartData.datasets[0].strokeColor = '#00a65a'
                    barChartData.datasets[0].pointColor  = '#00a65a'
                    var barChartOptions                  = {
                      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
                      scaleBeginAtZero        : true,
                      //Boolean - Whether grid lines are shown across the chart
                      scaleShowGridLines      : true,
                      //String - Colour of the grid lines
                      scaleGridLineColor      : 'rgba(0,0,0,.05)',
                      //Number - Width of the grid lines
                      scaleGridLineWidth      : 1,
                      //Boolean - Whether to show horizontal lines (except X axis)
                      scaleShowHorizontalLines: true,
                      //Boolean - Whether to show vertical lines (except Y axis)
                      scaleShowVerticalLines  : true,
                      //Boolean - If there is a stroke on each bar
                      barShowStroke           : true,
                      //Number - Pixel width of the bar stroke
                      barStrokeWidth          : 2,
                      //Number - Spacing between each of the X value sets
                      barValueSpacing         : 5,
                      //Number - Spacing between data sets within X values
                      barDatasetSpacing       : 1,
                      //String - A legend template
                      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
                      //Boolean - whether to make the chart responsive
                      responsive              : true,
                      maintainAspectRatio     : true
                    }

                    barChartOptions.datasetFill = false
                    barChart.Bar(barChartData, barChartOptions)
                  })
                //--------------------timeofloginchart and appverison chart-------------------

                
                
                
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
    //to get total active/visitor
    $scope.openGraph=function(module){ 
        
        $('#graphdetails').modal(); 
        $("#linechartloaderdiv").show();
        $('#line-chartA').empty();
        setTimeout(function () {
        $scope.graphName=module;
        switch(module) {
          case 'users': 
            $scope.graphtitle="New Registrations";
             $scope.getUserGraphData(); 
            break;
          case 'visitors':
            $scope.graphtitle="Visitors";
            $scope.getVisitorsGraphData();
            break;
          case 'activeusers':
            $scope.graphtitle="Active users";
            $scope.getActiveusersGraphData();
            break;
          case 'newposts':
            $scope.graphtitle="New Posts";
            $scope.getNewpostsGraphData();
            break;  
          case 'newcomments':
            $scope.graphtitle="New Comments";
            $scope.getNewcommentsGraphData();
            break;
          case 'newlikes':
            $scope.graphtitle="New Likes";
            $scope.getNewlikesGraphData();
            break;
          default:
            // code block
        }  
       
        

        }, 500); // wait...
    }

    $scope.getUserGraphData = function () {

        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.dateFilterText = $("#dateFilterText").html();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            dateFilterText:$scope.dateFilterText,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        
        $("#reportLoaderDiv").show();
        
        $('#line-chartA').html("");  $scope.graphData=[];
        googleAnalyticsChartData.getUserGraphData(reqData).then(function (response) {
             $("#linechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.graphData = response.Data.Users;
                //console.log('graph',$scope.graphData);
                var line = new Morris.Line({
                                element          : 'line-chartA',
                                resize           : true,
                                data             : $scope.graphData,
                                xkey             : 'monthyear',
                                ykeys            : ['totalclicks'],
                                labels           : ['Users'],
                                lineColors       : ['#efefef'],
                                lineWidth        : 1,
                                hideHover        : 'auto',
                                gridTextColor    : '#fff',
                                gridStrokeWidth  : 0.4,
                                pointSize        : 2,
                                pointStrokeColors: ['#efefef'],
                                gridLineColor    : '#efefef',
                                gridTextFamily   : 'Open Sans',
                                gridTextSize     : 10,
                                /*parseTime:false,*/
                                xLabels:"day",
                                xLabelFormat: function(d) {
                                return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov', 'Dec'][d.getMonth()] + ' ' + d.getDate()+' '+d.getFullYear();
                                },
                              })
                //  line.redraw()
                //--------------------sources paichart-------------------
                //$scope.sources = response.Data.Sources;
                
                 $(function () {
                    /* ChartJS
                     * -------
                     * Here we will create a few charts using ChartJS
                     */
                    var dynamicColors = function() {
                        var r = Math.floor(Math.random() * 255);
                        var g = Math.floor(Math.random() * 255);
                        var b = Math.floor(Math.random() * 255);
                        return "rgb(" + r + "," + g + "," + b + ")";
                    };
                    
                    PieDataSources = []; paicolors=[];
                    $.each(response.Data.Sources, function (i)
                    {   var colorcode = dynamicColors();
                        paicolors.push({'colorText': response.Data.Sources[i].Sources ,'colorcode':colorcode});
                            PieDataSources.push( 
                                {   
                                    value    : response.Data.Sources[i].UsersCount,
                                    color    : colorcode,
                                    highlight: colorcode,
                                    label    : 'Source Compaign  '+response.Data.Sources[i].Sources
                                }
                            );
                    });
                    $scope.paicolors = paicolors;

                    //-------------
                    //- PIE CHART -
                    //-------------
                    // Get context with jQuery - using jQuery's .get() method.
                    var pieChartCanvasIOS = $('#pieChart-Sources').get(0).getContext('2d')
                    var pieChartIOS       = new Chart(pieChartCanvasIOS)
                    //ios pie chart start
                    var pieOptionsIOS     = {
                      //Boolean - Whether we should show a stroke on each segment
                      segmentShowStroke    : true,
                      //String - The colour of each segment stroke
                      segmentStrokeColor   : '#fff',
                      //Number - The width of each segment stroke
                      segmentStrokeWidth   : 2,
                      //Number - The percentage of the chart that we cut out of the middle
                      percentageInnerCutout: 50, // This is 0 for Pie charts
                      //Number - Amount of animation steps
                      animationSteps       : 100,
                      //String - Animation easing effect
                      animationEasing      : 'easeOutBounce',
                      //Boolean - Whether we animate the rotation of the Doughnut
                      animateRotate        : true,
                      //Boolean - Whether we animate scaling the Doughnut from the centre
                      animateScale         : false,
                      //Boolean - whether to make the chart responsive to window resizing
                      responsive           : true,
                      // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                      maintainAspectRatio  : true,
                      //String - A legend template
                      legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
                    }
                    //Create pie or douhnut chart
                    // You can switch between pie and douhnut using the method below.
                    pieChartIOS.Doughnut(PieDataSources , pieOptionsIOS)
                    //ios pie chart end
                })
                //--------------------sources paichart-------------------


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
    $scope.getVisitorsGraphData = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.dateFilterText = $("#dateFilterText").html();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            dateFilterText:$scope.dateFilterText,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        
        $("#reportLoaderDiv").show();
        
        $('#line-chartA').html("");  $scope.graphData=[];
        googleAnalyticsChartData.getVisitorsGraphData(reqData).then(function (response) {
            $("#linechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.graphData = response.Data.visitors;
                //console.log('graph',$scope.graphData);
                

                var line = new Morris.Line({
                                element          : 'line-chartA',
                                resize           : true,
                                data             : $scope.graphData,
                                xkey             : 'monthyear',
                                ykeys            : ['totalclicks'],
                                labels           : ['Visitors'],
                                lineColors       : ['#efefef'],
                                lineWidth        : 1,
                                hideHover        : 'auto',
                                gridTextColor    : '#fff',
                                gridStrokeWidth  : 0.4,
                                pointSize        : 2,
                                pointStrokeColors: ['#efefef'],
                                gridLineColor    : '#efefef',
                                gridTextFamily   : 'Open Sans',
                                gridTextSize     : 10,
                                /*parseTime:false,*/
                                xLabels:"day",
                                xLabelFormat: function(d) {
                                return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov', 'Dec'][d.getMonth()] + ' ' + d.getDate()+' '+d.getFullYear();
                                },
                              })

                            //  line.redraw()

                //--------------------devices paichart-------------------
                //$scope.sources = response.Data.Sources;
                
                 $(function () {
                    /* ChartJS
                     * -------
                     * Here we will create a few charts using ChartJS
                     */
                    var dynamicColors = function() {
                        var r = Math.floor(Math.random() * 255);
                        var g = Math.floor(Math.random() * 255);
                        var b = Math.floor(Math.random() * 255);
                        return "rgb(" + r + "," + g + "," + b + ")";
                    };
                    
                    
                    PieDataSources = []; paicolors=[];
                    $.each(response.Data.devices, function (i)
                    {   var colorcode = dynamicColors();
                        paicolors.push({'colorText': response.Data.devices[i].DeviceInfo ,'colorcode':colorcode});
                            PieDataSources.push( 
                                {   
                                    value    : response.Data.devices[i].clicks,
                                    color    : colorcode,
                                    highlight: colorcode,
                                    label    : 'Device  '+response.Data.devices[i].DeviceInfo
                                }
                            );
                    });
                    $scope.paicolors = paicolors;

                    //-------------
                    //- PIE CHART -
                    //-------------
                    // Get context with jQuery - using jQuery's .get() method.
                    var pieChartCanvasIOS = $('#pieChart-Sources').get(0).getContext('2d')
                    var pieChartIOS       = new Chart(pieChartCanvasIOS)
                    //ios pie chart start
                    var pieOptionsIOS     = {
                      //Boolean - Whether we should show a stroke on each segment
                      segmentShowStroke    : true,
                      //String - The colour of each segment stroke
                      segmentStrokeColor   : '#fff',
                      //Number - The width of each segment stroke
                      segmentStrokeWidth   : 2,
                      //Number - The percentage of the chart that we cut out of the middle
                      percentageInnerCutout: 50, // This is 0 for Pie charts
                      //Number - Amount of animation steps
                      animationSteps       : 100,
                      //String - Animation easing effect
                      animationEasing      : 'easeOutBounce',
                      //Boolean - Whether we animate the rotation of the Doughnut
                      animateRotate        : true,
                      //Boolean - Whether we animate scaling the Doughnut from the centre
                      animateScale         : false,
                      //Boolean - whether to make the chart responsive to window resizing
                      responsive           : true,
                      // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                      maintainAspectRatio  : true,
                      //String - A legend template
                      legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
                    }
                    //Create pie or douhnut chart
                    // You can switch between pie and douhnut using the method below.
                    pieChartIOS.Doughnut(PieDataSources , pieOptionsIOS)
                    //ios pie chart end
                })
                //--------------------devices paichart-------------------

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
    $scope.getActiveusersGraphData = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.dateFilterText = $("#dateFilterText").html();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            dateFilterText:$scope.dateFilterText,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        
        $("#reportLoaderDiv").show();
        
        $('#line-chartA').html("");  $scope.graphData=[];
        googleAnalyticsChartData.getActiveusersGraphData(reqData).then(function (response) {
            $("#linechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.graphData = response.Data;
                //console.log('graph',$scope.graphData);
                

                var line = new Morris.Line({
                                element          : 'line-chartA',
                                resize           : true,
                                data             : $scope.graphData,
                                xkey             : 'monthyear',
                                ykeys            : ['totalclicks'],
                                labels           : ['Active Users'],
                                lineColors       : ['#efefef'],
                                lineWidth        : 1,
                                hideHover        : 'auto',
                                gridTextColor    : '#fff',
                                gridStrokeWidth  : 0.4,
                                pointSize        : 2,
                                pointStrokeColors: ['#efefef'],
                                gridLineColor    : '#efefef',
                                gridTextFamily   : 'Open Sans',
                                gridTextSize     : 10,
                                /*parseTime:false,*/
                                xLabels:"day",
                                xLabelFormat: function(d) {
                                return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov', 'Dec'][d.getMonth()] + ' ' + d.getDate()+' '+d.getFullYear();
                                },
                              })

                            //  line.redraw()
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
    $scope.getNewpostsGraphData = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.dateFilterText = $("#dateFilterText").html();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            dateFilterText:$scope.dateFilterText,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        
        $("#reportLoaderDiv").show();
        
        $('#line-chartA').html("");  $scope.graphData=[];
        googleAnalyticsChartData.getNewpostsGraphData(reqData).then(function (response) {
            $("#linechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.graphData = response.Data;
                //console.log('graph',$scope.graphData);
                

                var line = new Morris.Line({
                                element          : 'line-chartA',
                                resize           : true,
                                data             : $scope.graphData,
                                xkey             : 'monthyear',
                                ykeys            : ['totalclicks'],
                                labels           : ['Posts'],
                                lineColors       : ['#efefef'],
                                lineWidth        : 1,
                                hideHover        : 'auto',
                                gridTextColor    : '#fff',
                                gridStrokeWidth  : 0.4,
                                pointSize        : 2,
                                pointStrokeColors: ['#efefef'],
                                gridLineColor    : '#efefef',
                                gridTextFamily   : 'Open Sans',
                                gridTextSize     : 10,
                                /*parseTime:false,*/
                                xLabels:"day",
                                xLabelFormat: function(d) {
                                return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov', 'Dec'][d.getMonth()] + ' ' + d.getDate()+' '+d.getFullYear();
                                },
                              })

                            //  line.redraw()
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
    $scope.getNewcommentsGraphData = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.dateFilterText = $("#dateFilterText").html();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            dateFilterText:$scope.dateFilterText,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        
        $("#reportLoaderDiv").show();
        
        $('#line-chartA').html("");  $scope.graphData=[];
        googleAnalyticsChartData.getNewcommentsGraphData(reqData).then(function (response) {
            $("#linechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.graphData = response.Data;
                //console.log('graph',$scope.graphData);
                

                var line = new Morris.Line({
                                element          : 'line-chartA',
                                resize           : true,
                                data             : $scope.graphData,
                                xkey             : 'monthyear',
                                ykeys            : ['totalclicks'],
                                labels           : ['Comments'],
                                lineColors       : ['#efefef'],
                                lineWidth        : 1,
                                hideHover        : 'auto',
                                gridTextColor    : '#fff',
                                gridStrokeWidth  : 0.4,
                                pointSize        : 2,
                                pointStrokeColors: ['#efefef'],
                                gridLineColor    : '#efefef',
                                gridTextFamily   : 'Open Sans',
                                gridTextSize     : 10,
                                /*parseTime:false,*/
                                xLabels:"day",
                                xLabelFormat: function(d) {
                                return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov', 'Dec'][d.getMonth()] + ' ' + d.getDate()+' '+d.getFullYear();
                                },
                              })

                            //  line.redraw()
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
    $scope.getNewlikesGraphData = function () {
        //get starting date/end_date from top selected date, day filter from page and AdminLoginSessionKey
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.filter = $("#filter_val").val();
        $scope.dateFilterText = $("#dateFilterText").html();
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            Filter:$scope.filter,
            dateFilterText:$scope.dateFilterText,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        
        $("#reportLoaderDiv").show();
        
        $('#line-chartA').html("");  $scope.graphData=[];
        googleAnalyticsChartData.getNewlikesGraphData(reqData).then(function (response) {
            $("#linechartloaderdiv").hide();
            if (response.ResponseCode == 200 || response.ResponseCode == 672){
                
                $scope.graphData = response.Data;
                //console.log('graph',$scope.graphData);
                

                var line = new Morris.Line({
                                element          : 'line-chartA',
                                resize           : true,
                                data             : $scope.graphData,
                                xkey             : 'monthyear',
                                ykeys            : ['totalclicks'],
                                labels           : ['Likes'],
                                lineColors       : ['#efefef'],
                                lineWidth        : 1,
                                hideHover        : 'auto',
                                gridTextColor    : '#fff',
                                gridStrokeWidth  : 0.4,
                                pointSize        : 2,
                                pointStrokeColors: ['#efefef'],
                                gridLineColor    : '#efefef',
                                gridTextFamily   : 'Open Sans',
                                gridTextSize     : 10,
                                /*parseTime:false,*/
                                xLabels:"day",
                                xLabelFormat: function(d) {
                                return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov', 'Dec'][d.getMonth()] + ' ' + d.getDate()+' '+d.getFullYear();
                                },
                              })

                            //  line.redraw()
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

/*
 |--------------------------------------------------------------------------
 | Function is used for save dates and show on top naviagation in header.
 |--------------------------------------------------------------------------
 */
function SaveDatesAnalytics(DateFilter) {
    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    var jsondata = {
        filter: {
            DateFilter: DateFilter,
            AdminLoginSessionKey: AdminLoginSessionKey,
            isGoogleAnalytic: 1
        }
    }
    var PageName = $('#pageName').val();
    $.ajax({
        url: base_url + "admin/users/set_session",
        data: jsondata,
        type: "POST",
        dataType: 'json',
        success: function (response) {
            if (response.status == 1) {
                $('#SpnFrom').val(response.startDate);
                $('#SpnTo').val(response.endDate);
                //$("#dateFilterText").text(response.dateFilterText);

                $("#dateFrom").val(response.startDate);
                $("#dateTo").val(response.endDate);
                //Code for check page and according page_name we call controller
                switch (PageName)
                {
                    case 'google_analytics_dash':
                        $scope.loadAllAnalyticsData();
                        //loadGoogleAnalyticsChartOnScroll();
                        break;    
                }
            } else {
                return false;
            }
        }
    });
}
// Init process
function initFn() {
    $('#dateFrom').datepicker();
    $('#dateTo').datepicker();
    $('.datepicker').datepicker();
    
    
    $(document).on('click', '.customDateAnalytic', function() {
        $('.dropdown-day').slideUp('fast');
        $('.dropdown-custom-analytic').slideDown();
        $('.dropdown-time').addClass('open');
    });
    $(document).on('click', '#submit_analytic_date', function() {
        $('#SpnFrom').val($('#dateFrom').val());
        $('#SpnTo').val($('#dateTo').val());
        //$("#dateFilterText").text(response.dateFilterText);

        //$("#dateFrom").val($('#dateFrom').val());
        //$("#dateTo").val($('#dateTo').val());
        $('#date_dropdown').slideUp();
        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().statFilterName =  $('#SpnFrom').val()+' - '+$('#SpnTo').val();
        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().loadAllAnalyticsData();
    });
    $('[data-dropdown="hide"]').on('hide.bs.dropdown', function () {        
      $('.dropdown-custom-analytic').slideUp();
      $('.dropdown-day').show(); 
    });
}
$(document).ready(function(){
    initFn();
});
