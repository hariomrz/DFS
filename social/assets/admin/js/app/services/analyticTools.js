/*
 |--------------------------------------------------------------------------
 | Function for Get Data for analytic tools serivce
 |--------------------------------------------------------------------------
 */
app.factory('analyticToolsData', function ($q, $http, appInfo) {
    return {
        getAnalyticsProviders: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_analytics_providers');
            /* Make HTTP request for smtp setting list */
            $http.post(base_url + 'admin_api/analytics/get_analytics_providers', reqData).success(function (data) {
                HideInformationMessage('get_analytics_providers');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        loadToolsData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_analytics_provider_detail');
            /* Make HTTP request for smtp setting list */
            $http.post(base_url + 'admin_api/analytics/get_analytics_provider_detail', reqData).success(function (data) {
                HideInformationMessage('get_analytics_provider_detail');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        saveAnalyticToolsData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('save_analyticstools_info');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/analytics/save_analyticstools_info', reqData).success(function (data) {
                HideInformationMessage('save_analyticstools_info');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            fetchedData = deferred.promise;
            return fetchedData;
        }
    }
});
