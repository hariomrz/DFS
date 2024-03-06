/*
 |--------------------------------------------------------------------------
 | Function for Get Data for users serivce
 | getUserlist
 |--------------------------------------------------------------------------
 */
app.factory('getArticleData', function ($q, $http, appInfo) {
    return {
       
      
        bannerList: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/banner', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getBannerImageList: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/getBannerImageList', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getBannerDetails: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/getBannerDetails', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        SaveBanner: function (reqData) {
            var deferred = $q.defer();
            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/saveBanner', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        save_banner: function (reqData) {
            var deferred = $q.defer();
            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/save_banner', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getDefaultBannerDetails: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/getDefaultBannerDetails', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        SaveDefaultBanner: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/saveDefaultBanner', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        ChangeBannerStatus: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for users listing */
            $http.post(base_url + 'admin_api/advertise/banner_status', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getAdvertiserList: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp setting details */
            $http.post(base_url + 'admin_api/advertise/getAdvertiserList', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
    }
});
