/*
 |------------------------------------------------------------------
 | Function for Get Data for user Ips
 | getUserIps
 |------------------------------------------------------------------
 */
app.factory('userIpsData', function ($q, $http, appInfo) {
    return {
        getUserIps: function (reqData) {
            var deferred = $q.defer(); 
            ShowInformationMessage('ips_info');
            /* Make HTTP request for get user ip's info */ //{UserGUID: UserGUID,FromDate:FromDate, ToDate:ToDate}
            $http.post(base_url + 'admin_api/user/ips_info', reqData).success(function (data) {
                HideInformationMessage('ips_info');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});