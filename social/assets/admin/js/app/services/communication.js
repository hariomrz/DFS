/*
 |-------------------------------------------
 | Function for communicationData for a user profile
    getCommunication, sendCommunication
 |-------------------------------------------
 */
app.factory('communicationData', function ($q, $http) {
    var fetchedData = {};
    return {
        getCommunication: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('communication_list');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/communication/', reqData).success(function (data) {
                HideInformationMessage('communication_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            fetchedData = deferred.promise;
            return fetchedData;
        },
        sendCommunication: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('send_communication');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/communication/send_communication', reqData).success(function (data) {
                HideInformationMessage('send_communication');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            fetchedData = deferred.promise;
            return fetchedData;
        },
        sendMultipleCommunication: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('send_multiple_communication');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/communication/send_multiple_communication', reqData).success(function (data) {
                HideInformationMessage('send_multiple_communication');
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