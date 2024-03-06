/*
 |------------------------------------------------
 | Function for Get Data for Email Analytics Data
 |------------------------------------------------
 */
app.factory('emailAnalyticsChartData', function ($q, $http, appInfo) {
    return {
        emailAnalyticsData: function (reqData) {
            var deferred = $q.defer();
            
            /* Make HTTP request for get Email Anlytics data */
            $http.post(base_url + 'admin_api/analytics/email_analytics',reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});