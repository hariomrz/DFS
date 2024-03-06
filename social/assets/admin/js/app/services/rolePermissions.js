/*
 |--------------------------------------------------------------------------
 | Function for Get Data for role permissions serivce
 |--------------------------------------------------------------------------
 */
app.factory('getPermissionData', function ($q, $http, appInfo) {
    return {
        getApplicationsList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_applications_list');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/get_applications_list', reqData).success(function (data) {
                HideInformationMessage('get_applications_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getRolePermissionList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_permission_list');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/get_permission_list', reqData).success(function (data) {
                HideInformationMessage('get_permission_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getRightsPermissionRole: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('get_right_permission_roles');
            /* Make HTTP request for roles listing */
            $http.post(base_url + 'admin_api/roles/get_right_permission_roles', reqData).success(function (data) {
                HideInformationMessage('get_right_permission_roles');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        SavePermissionsRole: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('save_permissions_roles');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/roles/save_permissions_roles', reqData).success(function (data) {
                HideInformationMessage('save_permissions_roles');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
