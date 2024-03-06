/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

app.factory('postsService', ['$q', '$http', function ($q, $http) {
        return {
            getList: function (reqData) {
                var deferred = $q.defer();
                /* Make HTTP request for user profile data */ //{UserGUID: UserGUID}
                $http.post(base_url + 'admin_api/flags/list', reqData).success(function (data) {
                    deferred.resolve(data);
                }).error(function (data) {
                    deferred.reject(data);
                });
                return deferred.promise;
            },
            removeFlag: function (reqData) {
                var deferred = $q.defer();
                /* Make HTTP request for user profile data */ //{UserGUID: UserGUID}
                $http.post(base_url + 'admin_api/flags/remove', reqData).success(function (data) {
                    deferred.resolve(data);
                }).error(function (data) {
                    deferred.reject(data);
                });
                return deferred.promise;
            },
            viewFlags: function (reqData) {
                var deferred = $q.defer();
                /* Make HTTP request for user profile data */ //{UserGUID: UserGUID}
                $http.post(base_url + 'admin_api/flags/entityflags', reqData).success(function (data) {
                    deferred.resolve(data);
                }).error(function (data) {
                    deferred.reject(data);
                });
                return deferred.promise;
            },
            deleteActivity: function (reqData) {
                var deferred = $q.defer();
                /* Make HTTP request for user profile data */ //{UserGUID: UserGUID}
                $http.post(base_url + 'admin_api/flags/delete', reqData).success(function (data) {
                    deferred.resolve(data);
                }).error(function (data) {
                    deferred.reject(data);
                });
                return deferred.promise;
            },
        };
    }]);
