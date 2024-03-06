/*
 |--------------------------------------------------------------------------
 | Function for Get Data for role users serivce
 |--------------------------------------------------------------------------
 */
app.factory('getRoleUserData', function ($q, $http, appInfo) {
    return {
        getRoles: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_role_list');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/get_role_list', reqData).success(function (data) {
                HideInformationMessage('get_role_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getRoleUsersList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_role_users');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/get_role_users', reqData).success(function (data) {
                HideInformationMessage('get_role_users');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        SaveUserInfo: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('save_user_info');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/roles/save_user_info', reqData).success(function (data) {
                HideInformationMessage('save_user_info');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateUserStatus: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('change_status_role_user');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/users/change_status', reqData).success(function (data) {
                HideInformationMessage('change_status_role_user');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateMultipleUsersStatus: function (reqData) {
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
        getUserRoles: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_user_roles');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/get_user_roles', reqData).success(function (data) {
                HideInformationMessage('get_user_roles');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
