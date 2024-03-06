/*
 |--------------------------------------------------------------------------
 | Function for Get Data for email setting serivce
 | getSmtpEmailList
 |--------------------------------------------------------------------------
 */
app.factory('ipsData', function ($q, $http, appInfo) {
    return {
        getIpList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('ipsetting');
            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'admin_api/ipsetting', reqData).success(function (data) {
                HideInformationMessage('ipsetting');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        CreateIpDetails: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('create_ip_details');
            /* Make HTTP request for update status */
            $http.post(base_url + 'admin_api/ipsetting/create_ip_details', reqData).success(function (data) {
                HideInformationMessage('create_ip_details');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateIPStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('ip_update_status');
            /* Make HTTP request for update status */
            $http.post(base_url + 'admin_api/ipsetting/update_status', reqData).success(function (data) {
                HideInformationMessage('ip_update_status');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});
