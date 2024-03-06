/*
 |--------------------------------------------------------------------------
 | Function for Get Data for users serivce
 | getUserlist
 |--------------------------------------------------------------------------
 */
app.factory('mediaAnalyticData', function ($q, $http, appInfo) {
    return {
        getMediaAnalyticsList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('media_analytics');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/media/media_analytics', reqData).success(function (data) {
                HideInformationMessage('media_analytics');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        downloadMediaAnalyticsData: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('download_media_analytics');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/media/download_media_analytics', reqData).success(function (data) {
                HideInformationMessage('download_media_analytics');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getMediaAnalyticsReport: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('media_analytics_report');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/media/media_analytics_report', reqData).success(function (data) {
                HideInformationMessage('media_analytics_report');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateUsersStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('media_analytic_update_status');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/users/update_status', reqData).success(function (data) {
                HideInformationMessage('media_analytic_update_status');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
