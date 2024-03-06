/*
 |--------------------------------------------------------------------------
 | Function for Get Data for beta nvite users serivce
 |--------------------------------------------------------------------------
 */
app.factory('BetainviteData',['$q', '$http', 'appInfo', function ($q, $http, appInfo) {
    return {
        BetaInviteVerify: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for users listing */
            $http.post(base_url + 'api/betainvite/verifycode', reqData).then(function (data) {
                data = data.data;
                deferred.resolve(data);
            }, function (data) {
                data = data.data;
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
}]);