/*
 |--------------------------------------------------------------------------
 | Function for Get Data for emails comunication serivce
 | getEmailList
 |--------------------------------------------------------------------------
 */
app.factory('getData', function ($q, $http, appInfo) {
    return {
        getEmailList: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('emails_list');
            /* Make HTTP request for comunication email listing */
            $http.post(base_url + 'admin_api/emails', reqData).success(function (data) {
                HideInformationMessage('emails_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        resendCommunication: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('resend_communication');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/communication/resend_communication', reqData).success(function (data) {
                HideInformationMessage('resend_communication');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            fetchedData = deferred.promise;
            return fetchedData;
        }
    }
});
