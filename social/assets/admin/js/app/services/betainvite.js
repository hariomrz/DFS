/*
 |--------------------------------------------------------------------------
 | Function for Get Data for beta nvite users serivce
 |--------------------------------------------------------------------------
 */
app.factory('getBetainviteData', function ($q, $http, appInfo) {
    return {
        getBetaInvitedUsersList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('betainvite');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/betainvite', reqData).success(function (data) {
                HideInformationMessage('betainvite');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateBetaInviteUserStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('beta_update_status');
            /* Make HTTP request for update status */
            $http.post(base_url + 'admin_api/betainvite/update_status', reqData).success(function (data) {
                HideInformationMessage('beta_update_status');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        CheckEmailExist: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('checkemailexist');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/betainvite/checkemailexist', reqData).success(function (data) {
                HideInformationMessage('checkemailexist');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        SendBetaInvite: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('sendbetainvite');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/betainvite/sendbetainvite', reqData).success(function (data) {
                HideInformationMessage('sendbetainvite');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        downloadBetaUsers: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('download_beta_users');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/betainvite/download_beta_users', reqData).success(function (data) {
                HideInformationMessage('download_beta_users');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        deleteCsvFiles: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('beta_delete_csv_file');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/betainvite/delete_csv_file', reqData).success(function (data) {
                HideInformationMessage('beta_delete_csv_file');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
