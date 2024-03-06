app.factory('userAnalyticData', function ($q, $http, appInfo) {
    return {
        getUserAnalytics: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('user_analytics');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/analytics/user', reqData).success(function (data) {
                HideInformationMessage('user_analytics');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
