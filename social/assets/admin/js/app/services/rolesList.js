/*
 |--------------------------------------------------------------------------
 | Function for Get Data for roles serivce
 |--------------------------------------------------------------------------
 */
app.factory('getRolesData', function ($q, $http, appInfo) {
    return {
        getRoleslist: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('roles_list');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles', reqData).success(function (data) {
                HideInformationMessage('roles_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        saveRoleInfo: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('save_roles_info');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/roles/save_roles_info', reqData).success(function (data) {
                HideInformationMessage('save_roles_info');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getRolesPermissions: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_role_permissions');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/get_role_permissions', reqData).success(function (data) {
                HideInformationMessage('get_role_permissions');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateRoleStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_status');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/roles/update_status', reqData).success(function (data) {
                HideInformationMessage('update_status');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        SaveManagedRolePermissions: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('save_roles_permissions');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/roles/save_roles_permissions', reqData).success(function (data) {
                HideInformationMessage('save_roles_permissions');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getRoleListExceptSelected: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('rolelistexceptselected');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/rolelistexceptselected', reqData).success(function (data) {
                HideInformationMessage('rolelistexceptselected');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
