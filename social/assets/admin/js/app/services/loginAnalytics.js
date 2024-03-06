/*
 |------------------------------------------------
 | Function for Get Data for Login Analytics Data
 |------------------------------------------------
 */
app.factory('loginAnalyticsChartData', function ($q, $http, appInfo) {
    return {
        //Get data for Login Line chart
        loginAnalyticsData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_line_chart');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/analytics/login_line_chart',reqData).success(function (data) {
                HideInformationMessage('login_line_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data source of Login chart
        SourceLoginChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_sourcelogin_chart');
            /* Make HTTP request for get source of Login data */
            $http.post(base_url + 'admin_api/analytics/login_sourcelogin_chart',reqData).success(function (data) {
                HideInformationMessage('login_sourcelogin_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data device chart
        loginDeviceChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_device_chart');
            /* Make HTTP request for get Device chart data */
            $http.post(base_url + 'admin_api/analytics/login_device_chart',reqData).success(function (data) {
                HideInformationMessage('login_device_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data for username v/s email chart
        loginUsernameEmailChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_username_email_chart');
            /* Make HTTP request for get Device chart data */
            $http.post(base_url + 'admin_api/analytics/login_username_email_chart',reqData).success(function (data) {
                HideInformationMessage('login_username_email_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data for First time Login chart
        loginFirstTimeChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_first_time_chart');
            /* Make HTTP request for get Device chart data */
            $http.post(base_url + 'admin_api/analytics/login_first_time_chart',reqData).success(function (data) {
                HideInformationMessage('login_first_time_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data for popular Days login chart
        loginPopDaysChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_popular_days_chart');
            /* Make HTTP request for get Device chart data */
            $http.post(base_url + 'admin_api/analytics/login_popular_days_chart',reqData).success(function (data) {
                HideInformationMessage('login_popular_days_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data for popular Time login chart
        loginPopTimeChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_popular_time_chart');
            /* Make HTTP request for get Device chart data */
            $http.post(base_url + 'admin_api/analytics/login_popular_time_chart',reqData).success(function (data) {
                HideInformationMessage('login_popular_time_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data for login Failure chart
        loginFailureChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_failure_chart');
            /* Make HTTP request for get Failure chart data */
            $http.post(base_url + 'admin_api/analytics/login_failure_chart',reqData).success(function (data) {
                HideInformationMessage('login_failure_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },

        //Get data for login Geo chart
        loginGeoChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_geo_chart');
            /* Make HTTP request for get Failure chart data */
            $http.post(base_url + 'admin_api/analytics/login_geo_chart',reqData).success(function (data) {
                HideInformationMessage('login_geo_chart');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
    }
});