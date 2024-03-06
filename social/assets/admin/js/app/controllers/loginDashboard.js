// google analytics Controller for make google Analytics chart
app.controller('loginDashboardCtrl',['$scope','loginDashboard', function ($scope, loginDashboard) {
	
	$scope.FromDate = $('#SpnFrom').val();
	$scope.ToDate = $('#SpnTo').val();
	$scope.Filter = $("#filter_val").val();
	$scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
	$scope.LoginDashboardData = [];
    $scope.CountryChart = '';
    $scope.CountryChartPrefix = 'https://chart.googleapis.com/chart?cht=map:fixed=-60,-169,80,180&chs=600x350&chco=A6A7AB|0053A1|FF0000|FFC726|885E80&chtt=Top+Five+Countries';
    $scope.CountryColors = new Array('0053A1','FF0000','FFC726','885E80','f3f3f3');
    $scope.DateFilterRange = $scope.FromDate+' - '+$scope.ToDate;
    $scope.dateFilterText = $('#dateFilterText').text();

	$scope.getLoginDashboardAnalytics = function(){
        $scope.TotalPosts,$scope.TotalPages,$scope.TotalGroups,$scope.TotalEvents,$scope.TotalUsers,$scope.TotalMedia = 0;
        $scope.PrevPosts,$scope.PrevPages,$scope.PrevGroups,$scope.PrevEvents,$scope.PrevUsers,$scope.PrevMedia = 0;
        $scope.PostCls,$scope.PageCls,$scope.GroupCls,$scope.EventCls,$scope.UserCls,$scope.MediaCls = 'view-up';
        var reqData = {
        	FromDate : $scope.FromDate,
        	ToDate : $scope.ToDate,
        	Filter : $scope.Filter,
        	AdminLoginSessionKey : $scope.AdminLoginSessionKey
        };
        loginDashboard.getLoginDashboard(reqData).then(function (response) {
        	$scope.LoginDashboardData = response.Data;
        	if($scope.LoginDashboardData['post'].length>0){
        		$scope.TotalPosts = $scope.LoginDashboardData.post[0].total_post;
        		$scope.PrevPosts = $scope.LoginDashboardData.post[0].previous_total_post;
        		if(parseInt($scope.TotalPosts)>parseInt($scope.PrevPosts)){
        			$scope.PostsPercent = $scope.TotalPosts-$scope.PrevPosts;
        			$scope.PostsPercent = Math.floor($scope.PostsPercent/$scope.PrevPosts*100)+'%';
        			$scope.PostCls = 'view-up';
        		} else {
        			$scope.PostsPercent = $scope.PrevPosts-$scope.TotalPosts;
        			$scope.PostsPercent = Math.floor($scope.PostsPercent/$scope.PrevPosts*100)+'%';
        			$scope.PostCls = 'view-down';
        		}

        		if($scope.TotalPosts==0 || $scope.PrevPosts==0){
        			$scope.PostsPercent = '100%';
                    $scope.PostCls = 'view-down';
        		}
        	} else {
                $scope.TotalPosts = 0;
                $scope.PostsPercent = '100%';
                $scope.PostCls = 'view-up';
            }

        	if($scope.LoginDashboardData['page'].length>0){
        		$scope.TotalPages = $scope.LoginDashboardData.page[0].total_pages;
        		$scope.PrevPages = $scope.LoginDashboardData.page[0].previous_total_pages;
        		if(parseInt($scope.TotalPages)>parseInt($scope.PrevPages)){
        			$scope.PagesPercent = $scope.TotalPages-$scope.PrevPages;
        			$scope.PagesPercent = Math.floor($scope.PagesPercent/$scope.PrevPages*100)+'%';
        			$scope.PageCls = 'view-up';
        		} else {
        			$scope.PagesPercent = $scope.PrevPages-$scope.TotalPages;
        			$scope.PagesPercent = Math.floor($scope.PagesPercent/$scope.PrevPages*100)+'%';
        			$scope.PageCls = 'view-down';
        		}

        		if($scope.TotalPages==0 || $scope.PrevPages==0){
                    $scope.PagesPercent = '100%';
                    $scope.PageCls = 'view-down';
        		}
        	} else {
                $scope.TotalPages = 0;
                $scope.PagesPercent = '100%';
                $scope.PageCls = 'view-up';
            }

        	if($scope.LoginDashboardData['group'].length>0){
        		$scope.TotalGroups = $scope.LoginDashboardData.group[0].total_groups;
        		$scope.PrevGroups = $scope.LoginDashboardData.group[0].previous_total_groups;
        		if(parseInt($scope.TotalGroups)>parseInt($scope.PrevGroups)){
        			$scope.GroupsPercent = $scope.TotalGroups-$scope.PrevGroups;
        			$scope.GroupsPercent = Math.floor($scope.GroupsPercent/$scope.PrevGroups*100)+'%';
        			$scope.GroupCls = 'view-up';
        		} else {
        			$scope.GroupsPercent = $scope.PrevGroups-$scope.TotalGroups;
        			$scope.GroupsPercent = Math.floor($scope.GroupsPercent/$scope.PrevGroups*100)+'%';
        			$scope.GroupCls = 'view-down';
        		}

        		if($scope.TotalGroups==0 || $scope.PrevGroups==0){
        			$scope.GroupsPercent = '100%';
                    $scope.GroupCls = 'view-down';
        		}
        	} else {
                $scope.TotalGroups = 0;
                $scope.GroupsPercent = '100%';
                $scope.GroupCls = 'view-up';
            }

        	if($scope.LoginDashboardData['event'].length>0){
        		$scope.TotalEvents = $scope.LoginDashboardData['event'][0].total_events;
        		$scope.PrevEvents = $scope.LoginDashboardData['event'][0].previous_total_events;
        		if(parseInt($scope.TotalEvents)>parseInt($scope.PrevEvents)){
        			$scope.EventsPercent = $scope.TotalEvents-$scope.PrevEvents;
        			$scope.EventsPercent = Math.floor($scope.EventsPercent/$scope.PrevEvents*100)+'%';
        			$scope.EventCls = 'view-up';
        		} else {
        			$scope.EventsPercent = $scope.PrevEvents-$scope.TotalEvents;
        			$scope.EventsPercent = Math.floor($scope.EventsPercent/$scope.PrevEvents*100)+'%';
        			$scope.EventCls = 'view-down';
        		}

        		if($scope.TotalEvents==0 || $scope.PrevEvents==0){
        			$scope.EventsPercent = '100%';
                    $scope.EventCls = 'view-down';
        		}
        	} else {
                $scope.TotalEvents = 0;
                $scope.EventsPercent = '100%';
                $scope.EventCls = 'view-up';
            }

        	if($scope.LoginDashboardData['user'].length>0){
        		$scope.TotalUsers = $scope.LoginDashboardData.user[0].total_users;
        		$scope.PrevUsers = $scope.LoginDashboardData.user[0].previous_total_users;
        		if(parseInt($scope.TotalUsers)>parseInt($scope.PrevUsers)){
        			$scope.UsersPercent = $scope.TotalUsers-$scope.PrevUsers;
        			$scope.UsersPercent = Math.floor($scope.UsersPercent/$scope.PrevUsers*100)+'%';
        			$scope.UserCls = 'view-up';
        		} else {
        			$scope.UsersPercent = $scope.PrevUsers-$scope.TotalUsers;
        			$scope.UsersPercent = Math.floor($scope.UsersPercent/$scope.PrevUsers*100)+'%';
        			$scope.UserCls = 'view-down';
        		}

        		if($scope.TotalUsers==0){
        			$scope.UsersPercent = '100%';
                    $scope.UserCls = 'view-down';
        		}
                if($scope.PrevUsers==0){
                    $scope.UsersPercent = '100%';
                    $scope.UserCls = 'view-up';   
                }
        	} else {
                $scope.TotalUsers = 0;
                $scope.UsersPercent = '100%';
                $scope.UserCls = 'view-up';
            }

        	if($scope.LoginDashboardData['media'].length>0){
        		$scope.TotalMedia = $scope.LoginDashboardData['media'][0].total_media;
        		$scope.PrevMedia = $scope.LoginDashboardData['media'][0].previous_total_media;
        		if(parseInt($scope.TotalMedia)>parseInt($scope.PrevMedia)){
        			$scope.MediaPercent = $scope.TotalMedia-$scope.PrevMedia;
        			$scope.MediaPercent = Math.floor($scope.MediaPercent/$scope.PrevMedia*100)+'%';
        			$scope.MediaCls = 'view-up';
        		} else {
        			$scope.MediaPercent = $scope.PrevMedia-$scope.TotalMedia;
        			$scope.MediaPercent = Math.floor($scope.MediaPercent/$scope.PrevMedia*100)+'%';
        			$scope.MediaCls = 'view-down';
        		}

        		if($scope.TotalMedia==0 || $scope.PrevMedia==0){
        			$scope.MediaPercent = '100%';
                    $scope.MediaCls = 'view-down';
        		}
        	} else {
                $scope.TotalMedia = 0;
                $scope.MediaPercent = '100%';
                $scope.MediaCls = 'view-up';
            }

            if($scope.LoginDashboardData['engagement_score'].length>0){
                $scope.TotalEngage = $scope.LoginDashboardData['engagement_score'][0].total_engagement;
                $scope.PrevEngage = $scope.LoginDashboardData['engagement_score'][0].previous_total_engagement;
                if(parseInt($scope.TotalEngage)>parseInt($scope.PrevEngage)){
                    $scope.EngagePercent = $scope.TotalEngage-$scope.PrevEngage;
                    $scope.EngagePercent = Math.floor($scope.EngagePercent/$scope.PrevEngage*100)+'%';
                    $scope.EngageCls = 'view-up';
                } else {
                    $scope.EngagePercent = $scope.PrevEngage-$scope.TotalEngage;
                    $scope.EngagePercent = Math.floor($scope.EngagePercent/$scope.PrevEngage*100)+'%';
                    $scope.EngageCls = 'view-down';
                }

                if($scope.TotalEngage==0 || $scope.PrevEngage==0){
                    $scope.EngagePercent = '100%';
                    $scope.EngageCls = 'view-down';
                }
            } else {
                $scope.TotalEngage = 0;
                $scope.EngagePercent = '100%';
                $scope.EngageCls = 'view-up';
            }

            $scope.CountryChart = $scope.CountryChartPrefix;
            $scope.CountryData = [];
            if($scope.LoginDashboardData['top_countries'].length>0){
                $scope.CountryChart +='&chld=';
                $scope.TotalLogins = $scope.LoginDashboardData['top_countries'][0].TotalLogins;
                $($scope.LoginDashboardData['top_countries']).each(function(k,v){
                    $scope.CountryChart += $scope.LoginDashboardData['top_countries'][k].CountryCode+'|';
                    $scope.CountryPercent  = Math.floor(($scope.LoginDashboardData['top_countries'][k].Logins/$scope.TotalLogins)*100);
                    $scope.LoginDashboardData['top_countries'][k]['Percent'] = $scope.CountryPercent;
                    $scope.CountryData.push($scope.LoginDashboardData['top_countries'][k]);
                });
            }

            $scope.TotalUsersEng = 0;
            $scope.InActiveUsersEng = 0;
            $scope.ActiveUsersEng = 0;
            $scope.EngageUsers = 0;
            if($scope.LoginDashboardData['user_engagement'].length>0){
                $scope.UserEngagementData = $scope.LoginDashboardData['user_engagement'][0];
                $scope.TotalUsersEng = parseInt($scope.UserEngagementData['TotalUsers']);
                $scope.TotalUsersCountEng = parseInt($scope.UserEngagementData['TotalUsersCount']);
                $scope.ActiveUsersEng = parseInt($scope.UserEngagementData['ActiveUsers']);
                $scope.InActiveUsersEng = parseInt($scope.TotalUsersEng-$scope.ActiveUsersEng);
                $scope.EngageUsers = parseInt($scope.UserEngagementData['EngageUsers']);
            }

        	$scope.generateChart();
        });
    }

    $scope.CountryRank = function(i){
        return parseInt(i)+1;
    }

    $scope.getUsageData = function(){
        //$scope.usageData = ['Desktop':[],'Tablet':[],'Mobile':[]];
        var reqData = {
            FromDate : $scope.FromDate,
            ToDate : $scope.ToDate,
            Filter : $scope.Filter,
            AdminLoginSessionKey : $scope.AdminLoginSessionKey
        };
        loginDashboard.getUsageData(reqData).then(function (response) {
            $scope.usageData = response.Data;
            $scope.TotalUsageRecords = 0;
            $scope.TotalUsageDesktop = 0;
            $scope.TotalUsageTablet = 0;
            $scope.TotalUsageMobile = 0;

            var data = [];
            $($scope.usageData.Desktop).each(function(k,v){
                data.push($scope.usageData.Desktop[k]);
                data[k]['Percent'] = ((parseInt(data[k].Count)/parseInt(data[k].TotalCount))*100).toFixed(2);
                if(isNaN(data[k]['Percent'])){
                    data[k]['Percent'] = '0.00';
                }
                if(k == 0){
                    $scope.TotalUsageRecords = $scope.TotalUsageRecords+data[k].TotalCount;
                    $scope.TotalUsageDesktop = data[k].TotalCount;
                }
                switch(data[k].BrowserName){
                    case 'Firefox':
                        data[k]['Icon'] = 'icons-mozilla'
                    break;
                    case 'Safari':
                        data[k]['Icon'] = 'icons-safari'
                    break;
                    case 'Chrome':
                        data[k]['Icon'] = 'icons-chrome'
                    break;
                    case 'Internet Explorer':
                        data[k]['Icon'] = 'icons-ie'
                    break;
                    default:
                        data[k]['Icon'] = 'icons-otherwin'
                    break;
                }
            });
            $scope.usageData.Desktop = data;
            
            data = [];
            $($scope.usageData.Tablet).each(function(k,v){
                data.push($scope.usageData.Tablet[k]);
                data[k]['Percent'] = ((parseInt(data[k].Count)/parseInt(data[k].TotalCount))*100).toFixed(2);
                if(isNaN(data[k]['Percent'])){
                    data[k]['Percent'] = '0.00';
                }
                if(k == 0){
                    $scope.TotalUsageRecords = $scope.TotalUsageRecords+data[k].TotalCount;
                    $scope.TotalUsageTablet = data[k].TotalCount;
                }
                switch(data[k].BrowserName){
                    case 'AndroidTablet':
                        data[k]['Icon'] = 'icons-android'
                    break;
                    case 'Ipad':
                        data[k]['Icon'] = 'icons-mac'
                    break;
                    case 'WindowsTablet':
                        data[k]['Icon'] = 'icons-window'
                    break;
                    default:
                        data[k]['Icon'] = 'icons-device'
                    break;
                }
            });
            $scope.usageData.Tablet = data;
            
            data = [];
            $($scope.usageData.Mobile).each(function(k,v){
                data.push($scope.usageData.Mobile[k]);
                data[k]['Percent'] = ((parseInt(data[k].Count)/parseInt(data[k].TotalCount))*100).toFixed(2);
                if(isNaN(data[k]['Percent'])){
                    data[k]['Percent'] = '0.00';
                }
                if(k == 0){
                    $scope.TotalUsageRecords = $scope.TotalUsageRecords+data[k].TotalCount;
                    $scope.TotalUsageMobile = data[k].TotalCount;
                }
                switch(data[k].BrowserName){
                    case 'AndroidPhone':
                        data[k]['Icon'] = 'icons-android'
                    break;
                    case 'IPhone':
                        data[k]['Icon'] = 'icons-mac'
                    break;
                    case 'WindowsPhone':
                        data[k]['Icon'] = 'icons-window'
                    break;
                    default:
                        data[k]['Icon'] = 'icons-device'
                    break;
                }
            });
            $scope.usageData.Mobile = data;
        });
        setTimeout(function(){
            $scope.generateUsageChart();
        },2000);
    }

    $scope.generateUsageChart = function(){
        var UsageChartData = new google.visualization.DataTable();
        UsageChartData.addColumn('string','Device');
        UsageChartData.addColumn('number','Usage');
        UsageChartData.addRow(['Destop',parseInt($scope.TotalUsageDesktop)]);
        UsageChartData.addRow(['Tablet',parseInt($scope.TotalUsageTablet)]);
        UsageChartData.addRow(['Mobile',parseInt($scope.TotalUsageMobile)]);

        var options = {
            width: '100%',
            height: 280,
            title : '',
            slices: {  1: {offset: 0.05}},
            legend: { position: 'labeled' }
        };
        
        var chart = new google.visualization.PieChart(document.getElementById('UsageChart'));
        chart.draw(UsageChartData, options);
    }

    $scope.generateChart =function(){
    	//if($scope.TotalPosts>0){
    		var PostData = [];
    		$($scope.LoginDashboardData['post']).each(function(k,v){
    			PostData.push({
    				Date: $scope.LoginDashboardData['post'][k].date,
    				Posts: $scope.LoginDashboardData['post'][k].total
    			});
    		});
    		var PostChartData = new google.visualization.DataTable();
    		PostChartData.addColumn('string','Date');
    		PostChartData.addColumn('number','Posts');
 
            for(var i = 0; i < PostData.length; i++){
    			PostChartData.addRow([PostData[i].Date, parseInt(PostData[i].Posts)]);
            }

    		var options = {
                vAxis: {
                    format: '#',
                    minValue: 0,
                    gridlines: {
                        color: 'transparent'
                    },
                    viewWindowMode: "explicit",
                    viewWindow:{ min: 0 }
                },
                hAxis: { textPosition: 'none' },
                legend: { position: 'none' },
                colors:[{color:'#A8D1FF', darker:'#97B9D8'}]
	        };
	        
	        var chart = new google.visualization.AreaChart(document.getElementById('PostChart'));
	        chart.draw(PostChartData, options);
    	//}

    	//if($scope.TotalPages>0){
            var PageData = [];
            $($scope.LoginDashboardData['page']).each(function(k,v){
                PageData.push({
                    Date: $scope.LoginDashboardData['page'][k].date,
                    Pages: $scope.LoginDashboardData['page'][k].total
                });
            });
            var PageChartData = new google.visualization.DataTable();
            PageChartData.addColumn('string','Date');
            PageChartData.addColumn('number','Pages');
 
            for(var i = 0; i < PageData.length; i++){
                PageChartData.addRow([PageData[i].Date, parseInt(PageData[i].Pages)]);
            }

            var options = {
                vAxis: {
                    format: '#',
                    minValue: 0,
                    gridlines: {
                        color: 'transparent'
                    },
                    viewWindowMode: "explicit",
                    viewWindow:{ min: 0 }
                },
                hAxis: { textPosition: 'none' },
                legend: { position: 'none' },
                colors:[{color:'#A8D1FF', darker:'#97B9D8'}]
            };
            
            var chart = new google.visualization.AreaChart(document.getElementById('PageChart'));
            chart.draw(PageChartData, options);
        //}

        //if($scope.TotalGroups>0){
            var GroupData = [];
            $($scope.LoginDashboardData['group']).each(function(k,v){
                GroupData.push({
                    Date: $scope.LoginDashboardData['group'][k].date,
                    Groups: $scope.LoginDashboardData['group'][k].total
                });
            });
            var GroupChartData = new google.visualization.DataTable();
            GroupChartData.addColumn('string','Date');
            GroupChartData.addColumn('number','Groups');
 
            for(var i = 0; i < GroupData.length; i++){
                GroupChartData.addRow([GroupData[i].Date, parseInt(GroupData[i].Groups)]);
            }

            var options = {
                vAxis: {
                    format: '#',
                    minValue: 0,
                    gridlines: {
                        color: 'transparent'
                    },
                    viewWindowMode: "explicit",
                    viewWindow:{ min: 0 }
                },
                hAxis: { textPosition: 'none' },
                legend: { position: 'none' },
                colors:[{color:'#A8D1FF', darker:'#97B9D8'}]
            };
            
            var chart = new google.visualization.AreaChart(document.getElementById('GroupChart'));
            chart.draw(GroupChartData, options);
        //}

        //if($scope.TotalEvents>0){
            var EventData = [];
            $($scope.LoginDashboardData['event']).each(function(k,v){
                EventData.push({
                    Date: $scope.LoginDashboardData['event'][k].date,
                    Events: $scope.LoginDashboardData['event'][k].total
                });
            });
            var EventChartData = new google.visualization.DataTable();
            EventChartData.addColumn('string','Date');
            EventChartData.addColumn('number','Events');
 
            for(var i = 0; i < EventData.length; i++){
                EventChartData.addRow([EventData[i].Date, parseInt(EventData[i].Events)]);
            }

            var options = {
                vAxis: {
                    format: '#',
                    minValue: 0,
                    gridlines: {
                        color: 'transparent'
                    },
                    viewWindowMode: "explicit",
                    viewWindow:{ min: 0 }
                },
                hAxis: { textPosition: 'none' },
                legend: { position: 'none' },
                colors:[{color:'#A8D1FF', darker:'#97B9D8'}]
            };
            
            var chart = new google.visualization.AreaChart(document.getElementById('EventChart'));
            chart.draw(EventChartData, options);
        //}

        //if($scope.TotalUsers>0){
            var UserData = [];
            $($scope.LoginDashboardData['user']).each(function(k,v){
                UserData.push({
                    Date: $scope.LoginDashboardData['user'][k].date,
                    Users: $scope.LoginDashboardData['user'][k].total
                });
            });
            var UserChartData = new google.visualization.DataTable();
            UserChartData.addColumn('string','Date');
            UserChartData.addColumn('number','Users');
 
            for(var i = 0; i < UserData.length; i++){
                UserChartData.addRow([UserData[i].Date, parseInt(UserData[i].Users)]);
            }

            var options = {
                vAxis: {
                    format: '#',
                    minValue: 0,
                    gridlines: {
                        color: 'transparent'
                    },
                    viewWindowMode: "explicit",
                    viewWindow:{ min: 0 }
                },
                hAxis: { textPosition: 'none' },
                legend: { position: 'none' },
                colors:[{color:'#A8D1FF', darker:'#97B9D8'}]
            };
            
            var chart = new google.visualization.AreaChart(document.getElementById('UserChart'));
            chart.draw(UserChartData, options);
        //}

        //if($scope.TotalMedia>0){
            var MediaData = [];
            $($scope.LoginDashboardData['media']).each(function(k,v){
                MediaData.push({
                    Date: $scope.LoginDashboardData['media'][k].date,
                    Media: $scope.LoginDashboardData['media'][k].total
                });
            });
            var MediaChartData = new google.visualization.DataTable();
            MediaChartData.addColumn('string','Date');
            MediaChartData.addColumn('number','Media');
 
            for(var i = 0; i < MediaData.length; i++){
                MediaChartData.addRow([MediaData[i].Date, parseInt(MediaData[i].Media)]);
            }

            var options = {
                vAxis: {
                    format: '#',
                    minValue: 0,
                    gridlines: {
                        color: 'transparent'
                    },
                    viewWindowMode: "explicit",
                    viewWindow:{ min: 0 }
                },
                hAxis: { textPosition: 'none' },
                legend: { position: 'none' },
                colors:[{color:'#A8D1FF', darker:'#97B9D8'}]
            };
            
            var chart = new google.visualization.AreaChart(document.getElementById('MediaChart'));
            chart.draw(MediaChartData, options);
        //}

        //if($scope.TotalEngage>0){
            var EngageData = [];
            $($scope.LoginDashboardData['engagement_score']).each(function(k,v){
                EngageData.push({
                    Date: $scope.LoginDashboardData['engagement_score'][k].date,
                    Engage: $scope.LoginDashboardData['engagement_score'][k].total
                });
            });
            var EngageChartData = new google.visualization.DataTable();
            EngageChartData.addColumn('string','Date');
            EngageChartData.addColumn('number','Engage');
 
            for(var i = 0; i < EngageData.length; i++){
                EngageChartData.addRow([EngageData[i].Date, parseInt(EngageData[i].Engage)]);
            }

            var options = {
                vAxis: {
                    format: '#',
                    minValue: 0,
                    gridlines: {
                        color: 'transparent'
                    },
                    viewWindowMode: "explicit",
                    viewWindow:{ min: 0 }
                },
                hAxis: { 
                    textPosition: 'none'
                },
                legend: { position: 'none' },
                colors:[{color:'#A8D1FF', darker:'#97B9D8'}]
            };
            
            var chart = new google.visualization.AreaChart(document.getElementById('EngageChart'));
            chart.draw(EngageChartData, options);
        //}

        //if($scope.TotalUsersEng>0){
            var UserEngData = [];

            var UserEngChartData = google.visualization.arrayToDataTable([
            ['Genre', 'Inactive Users', 'Logged In Users', 'Engaged Users', { role: 'annotation' } ],
            ['Users', $scope.InActiveUsersEng,$scope.ActiveUsersEng, $scope.EngageUsers, '']
          ]);
            var options = { 
                width: '100%',
                height: 100,
                legend: { position: 'none' },
                bar: { groupWidth: '75%' },
                isStacked: true,
                hAxis: {
                    gridlines: {
                        color: 'transparent'
                    },
                    textPosition: 'none'
                },
                vAxis: {  viewWindowMode: "explicit", viewWindow:{ min: 0 }, textPosition: 'none' },
                colors:[{color:'#344F7E', darker:'#344F7E'},{color:'#FF7C81', darker:'#FF7C81'},{color:'#29D0C8', darker:'#29D0C8'}]
            };
            
            var chart = new google.visualization.BarChart(document.getElementById('UserEngageChart'));
            chart.draw(UserEngChartData, options);
        //}
    }

    $scope.updateLoginDashboardAnalytics = function(){
    	$scope.FromDate = $('#SpnFrom').val();
		$scope.ToDate = $('#SpnTo').val();
		$scope.Filter = $("#filter_val").val();
        $scope.dateFilterText = $('#dateFilterText').text();
		$scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
		$scope.getLoginDashboardAnalytics();
        $scope.getUsageData();
        $scope.DateFilterRange = $scope.FromDate+' - '+$scope.ToDate;
    }
}]);