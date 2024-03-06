/*
 |--------------------------------------------------------------------------
 | Function for Get Data for email setting serivce
 | getSmtpEmailList
 |--------------------------------------------------------------------------
 */
app.factory('configurationData', function ($q, $http, appInfo) {
    return {
        getConfigurationList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('configuration');
            /* Make HTTP request for smtp setting list */
            $http.post(base_url + 'admin_api/configuration', reqData).success(function (data) {
                HideInformationMessage('configuration');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        UpdateConfigurationDetails: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_config_setting');
            /* Make HTTP request for update status */
            $http.post(base_url + 'admin_api/configuration/update_config_setting', reqData).success(function (data) {
                HideInformationMessage('update_config_setting');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
