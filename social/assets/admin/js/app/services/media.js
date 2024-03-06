/*
 |-------------------------------------------
 | Function for media Data for a user profile
 getSummary, getMedia, setStatus
 |-------------------------------------------
 */
app.factory('mediaData', function ($q, $http) {
    return {
        getSummary: function (userId, AdminLoginSessionKey,MediaPageName) {
            var deferred = $q.defer();
            ShowInformationMessage('media_total_count');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/total_count', {userId: userId,AdminLoginSessionKey:AdminLoginSessionKey,MediaPageName:MediaPageName}).success(function (data) {
                HideInformationMessage('media_total_count');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getMedia: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('media_list');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/list', reqData).success(function (data) {
                HideInformationMessage('media_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        setStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('media_update');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/update', reqData).success(function (data) {
                HideInformationMessage('media_update');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getCount: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('media_count');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/media_count', reqData).success(function (data) {
                HideInformationMessage('media_count');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getDBCount: function (reqData){
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_media');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/update_media', reqData).success(function (data) {
                HideInformationMessage('update_media');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getCountAll: function(reqData){
            var deferred = $q.defer(reqData);
            ShowInformationMessage('media_count_all');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/media_count_all', reqData).success(function (data) {
                HideInformationMessage('media_count_all');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});