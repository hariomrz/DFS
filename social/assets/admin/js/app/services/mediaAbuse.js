/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 |----------------------------------------------------
 | Function for Media Abused Data for abused dashboard
    getCommunication, sendCommunication
 |----------------------------------------------------
 */
app.factory('mediaAbuseData', function ($q, $http) {
    return {
        getSummary: function (reqData) {
            var deferred = $q.defer();
            ShowInformationMessage('abused_total_count');
            /* Make HTTP request for abused media count */
            $http.post(base_url + 'admin_api/media/abused_total_count', reqData).success(function (data) {
                HideInformationMessage('abused_total_count');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        
        getMedia: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('abused_list');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/abused_list', reqData).success(function (data) {
                HideInformationMessage('abused_list');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        
        setStatus: function (reqData) {
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_abuse');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/update_abuse', reqData).success(function (data) {
                HideInformationMessage('update_abuse');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getDBCount: function (reqData){
            var deferred = $q.defer(reqData);
            ShowInformationMessage('update_media_abuse');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/update_media', reqData).success(function (data) {
                HideInformationMessage('update_media_abuse');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getCount: function(reqData){
            var deferred = $q.defer(reqData);
            ShowInformationMessage('abused_media_count');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/abused_media_count', reqData).success(function (data) {
                HideInformationMessage('abused_media_count');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getAbuserMediaDetail: function(reqData){
            var deferred = $q.defer(reqData);
            ShowInformationMessage('abused_media_detail');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/abused_media_detail', reqData).success(function (data) {
                HideInformationMessage('abused_media_detail');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getAbuserMediaComments: function(reqData){
            var deferred = $q.defer(reqData);
            ShowInformationMessage('abused_media_comments');
            /* Make HTTP request for user listing */
            $http.post(base_url + 'admin_api/media/abused_media_comments', reqData).success(function (data) {
                HideInformationMessage('abused_media_comments');
                deferred.resolve(data);
            }).error(function (data) {
                ShowWentWrongError();
                deferred.reject(data);
            });
            return deferred.promise;
        }
    }
});