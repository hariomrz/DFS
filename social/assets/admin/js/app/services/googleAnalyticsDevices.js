/*
 |------------------------------------------------
 | Function for Get Data for google Analytics Data
 |------------------------------------------------
 */
app.factory('googleAnalyticsDevicesData', function ($q, $http, appInfo) {
    return {
        //Get data for device os chart
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
        googleAnalyticDeviceDataReport: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('devices_report_data');
            /* Make HTTP request for get Login Anlytics data */
            $http.post(base_url + 'admin_api/googleanalytics/devices_report_data',reqData).success(function (data) {
                HideInformationMessage('devices_report_data');
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
        }
    }
});