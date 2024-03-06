
/*
 |------------------------------------------------------------------
 | Function for Get Data for user Login cahrt and device chart data
 | getUserLoginChart
 |------------------------------------------------------------------
 */
app.factory('userLoginChartData', function ($q, $http, appInfo) {
    return {
        getUserLoginChart: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('login_graph_info');
            /* Make HTTP request for get user login graph and device garph info */  //{UserGUID: UserGUID,FromDate:FromDate, ToDate:ToDate}
            $http.post(base_url + 'admin_api/user/login_graph_info',reqData).success(function (data) {
                HideInformationMessage('login_graph_info');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});