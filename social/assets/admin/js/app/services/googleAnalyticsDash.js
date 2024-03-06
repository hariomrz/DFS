/*
 |------------------------------------------------
 | Function for Get Data for google Analytics Data
 |------------------------------------------------
 */
app.factory('googleAnalyticsChartData', function ($q, $http, appInfo) {
    return {
        //Get data for Login Line chart
        googleAnalyticsLineChartData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('google_line_chart');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/line_chart',reqData).success(function (data) {
                HideInformationMessage('google_line_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        googleAnalyticDataReport: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('report_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/report_data',reqData).success(function (data) {
                HideInformationMessage('report_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        googleAnalyticsOSChartData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('device_os_chart');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/device_os_chart',reqData).success(function (data) {
                HideInformationMessage('device_os_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        googleAnalyticsBrowserChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('browser_analytics_chart');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/browser_analytics_chart',reqData).success(function (data) {
                HideInformationMessage('browser_analytics_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        googleAnalyticsDeviceTypeChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('devices_type_chart');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/devices_type_chart',reqData).success(function (data) {
                HideInformationMessage('devices_type_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        googleAnalyticPopularPages: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('popular_pages');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/popular_pages',reqData).success(function (data) {
                HideInformationMessage('popular_pages');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        googleAnalyticsGeoChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/geo_location_data',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        
        googleAnalyticsRegisteredUsers: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_total_users_count',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //api calling to get top influencer/contributers
        topContributors: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_contributors',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         topInfluencers: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_influencers',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         getSummary: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_summary',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         getUserGraphData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_user_graph_data',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         getVisitorsGraphData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_visitors_graph_data',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         getActiveusersGraphData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_activeusers_graph_data',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         getNewpostsGraphData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_newposts_graph_data',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         getNewcommentsGraphData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_newcomments_graph_data',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
         getNewlikesGraphData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('geo_location_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/get_newlikes_graph_data',reqData).success(function (data) {
                HideInformationMessage('geo_location_data');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
