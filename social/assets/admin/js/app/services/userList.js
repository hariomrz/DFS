/*
 |--------------------------------------------------------------------------
 | Function for Get Data for users serivce
 | getUserlist
 |--------------------------------------------------------------------------
 */
app.factory('getData', function ($q, $http, appInfo) {
    return {
        getUserlist: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('users_list');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/users', reqData).success(function (data) {
                HideInformationMessage('users_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        downloadUsers: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('download_users');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/users/download_users', reqData).success(function (data) {
                HideInformationMessage('download_users');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateUsersStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_status_user');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/users/update_status', reqData).success(function (data) {
                HideInformationMessage('update_status_user');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        ChangeStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_status_user');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/users/change_status', reqData).success(function (data) {
                HideInformationMessage('update_status_user');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        autoLoginUser: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('autologin_user');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/users/autologin_user', reqData).success(function (data) {
                HideInformationMessage('autologin_user');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getUserPersonaDetail: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('autologin_user');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/user/profile', reqData).success(function (data) {
                HideInformationMessage('autologin_user');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        CallApi : function(reqData,Url){ // Common Function to Call Api on given Url with request params
            var deferred = $q.defer();
            serviceUrl = 'admin_api/';
            $http.post(base_url +serviceUrl+Url,reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        CallFrontApi : function(reqData,Url){ // Common Function to Call Api on given Url with request params
            var deferred = $q.defer();
            serviceUrl = 'api/';
            $http.post(base_url +serviceUrl+Url,reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getUsageData: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/login/getUsageData', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
