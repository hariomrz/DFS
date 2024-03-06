/*
 |--------------------------------------------------------------------------
 | blog setting section serivce
 |--------------------------------------------------------------------------
 */
app.factory('blog_service', function ($q, $http, appInfo) {
    return {
        create: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for university listing */
            $http.post(base_url + 'admin_api/blog/add', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        list: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request to download conference  listi */
            $http.post(base_url + 'api/blog/list', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        details: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request add new conference */
            $http.post(base_url + 'api/blog/details', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        delete_blog: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to edit existing conference*/
            $http.post(base_url + 'admin_api/blog/delete', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        update: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete a conference */
            $http.post(base_url + 'admin_api/blog/edit', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        get_song_summery: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete a conference */
            $http.post(base_url + 'api/song/get_song_summery', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
