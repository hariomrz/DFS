/*
 |--------------------------------------------------------------------------
 | page section serivce
 |--------------------------------------------------------------------------
 */
app.factory('page_service', function ($q, $http, appInfo) {
    return {
        list: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for university listing */
            $http.post(base_url + 'admin_api/team', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        download_list: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request to download conference  listi */
            $http.post(base_url + 'admin_api/team/download_list', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        change_owner: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request add new conference */
            $http.post(base_url + 'admin_api/team/change_owner', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        edit_university: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to edit existing conference*/
            $http.post(base_url + 'admin_api/university/edit_university', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        delete_page: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete a conference */
            $http.post(base_url + 'admin_api/team/delete_page', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
        ,
        delete_pages: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete multiple conference */
            $http.post(base_url + 'admin_api/team/delete_pages', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
        ,
        get_all_conferences: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete multiple conference */
            $http.post(base_url + 'admin_api/conference/get_all_conferences', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
        ,
        verify_university_toggle: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete multiple conference */
            $http.post(base_url + 'admin_api/university/verify_university_toggle', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
