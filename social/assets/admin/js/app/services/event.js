/*
 |--------------------------------------------------------------------------
 | page section serivce
 |--------------------------------------------------------------------------
 */
app.factory('event_service', function ($q, $http, appInfo) {
    return {
        list: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for university listing */
            $http.post(base_url + 'admin_api/event', reqData).success(function (data) {
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
        delete_event: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete a conference */
            $http.post(base_url + 'admin_api/event/delete_event', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
        ,
        feature_event: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request to delete a conference */
            $http.post(base_url + 'admin_api/event/feature_event', reqData).success(function (data) {
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
            $http.post(base_url + 'admin_api/event/delete_pages', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
        
    }
});
