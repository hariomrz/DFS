/*
 |--------------------------------------------------------------------------
 | Function for Get Data for support and error log serivce
 |--------------------------------------------------------------------------
 */
app.factory('supportData', function ($q, $http, appInfo) {
    return {
        getErrorLoglist: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('support');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/support', reqData).success(function (data) {
                HideInformationMessage('support');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateErrorLogStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_status_support');
            /* Make HTTP request for update status */
            $http.post(base_url + 'admin_api/support/update_status', reqData).success(function (data) {
                HideInformationMessage('update_status_support');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        downloadErrorLogs: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('download_error_logs');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/support/download_error_logs', reqData).success(function (data) {
                HideInformationMessage('download_error_logs');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getErrorLogDetail: function(reqData){
            var deferred = $q.defer(reqData);
            ShowInformationMessage('error_log_detail');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/support/error_log_detail', reqData).success(function (data) {
                HideInformationMessage('error_log_detail');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
