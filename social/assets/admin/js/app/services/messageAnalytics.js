app.factory('messageAnalyticData', function ($q, $http, appInfo) {
    return {
        getMessageAnalytics: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('message_analytics');
            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/analytics/message', reqData).success(function (data) {
                HideInformationMessage('message_analytics');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
