/*
 |--------------------------------------------------------------------------
 | organization section serivce
 |--------------------------------------------------------------------------
 */
app.factory('pages_service', function ($q, $http, appInfo) {
    return {
        Pageslist: function (reqData, Url) { // Common Function to Call Api on given Url with request params
            var deferred = $q.defer();
            $http.post(base_url + 'admin_api/pages/list', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getOrganizationMemberDetail: function (reqData, Url) { // Common Function to Call Api on given Url with request params
            var deferred = $q.defer();
            $http.post(base_url + 'admin_api/pages/getOrganizationMemberDetail', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        get_users_tags: function (reqData, Url) { // Common Function to Call Api on given Url with request params
            var deferred = $q.defer();
            $http.post(base_url + 'admin_api/pages/get_users_tags', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        add_users: function (reqData, Url) { // Common Function to Call Api on given Url with request params
            var deferred = $q.defer();
            $http.post(base_url + 'admin_api/pages/add_users', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        remove_page: function (reqData, Url) { // Common Function to Call Api on given Url with request params
            var deferred = $q.defer();
            $http.post(base_url + 'admin_api/pages/remove_page', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        ChangeVerifyStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('autologin_user');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/pages/change_verify_status', reqData).success(function (data) {
                HideInformationMessage('autologin_user');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});