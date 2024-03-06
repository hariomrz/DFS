/*
 |--------------------------------------------------------------------------
 | Function for Get Data for users serivce
 | getUserlist
 |--------------------------------------------------------------------------
 */
app.factory('getData', function ($q, $http, appInfo) {
    return {
        getMostActiveUserlist: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('most_active_users');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/users/most_active_users', reqData).success(function (data) {
                HideInformationMessage('most_active_users');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        downloadMostActiveUsers: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('download_most_active_users');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/users/download_most_active_users', reqData).success(function (data) {
                HideInformationMessage('download_most_active_users');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateUsersStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('user_update_status');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/users/update_status', reqData).success(function (data) {
                HideInformationMessage('user_update_status');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
