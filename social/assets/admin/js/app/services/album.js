/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/*
 |--------------------------------------------------------------------------
 | Function for Get Data for email setting serivce
 | getSmtpEmailList
 |--------------------------------------------------------------------------
 */
app.factory('albumData', function ($q, $http, appInfo) {
    return {
        getList: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/list', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        Save: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request for update status */
            $http.post(base_url + reqData.Url, reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        markFeature: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request for update status */
            $http.post(base_url + 'api/album/mark_as_feature', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        removeFeature: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request for update status */
            $http.post(base_url + 'api/album/remove_as_feature', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        setVisibility: function (reqData) {
            var deferred = $q.defer(reqData);
            /* Make HTTP request for update status */
            $http.post(base_url + 'api/album/set_privacy', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        getMediaList: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/list_media', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        DeleteAlbum: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/delete', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        VerifyAlbum: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/toggle_verify', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }, 
        deleteAlbumMedia: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/delete_media', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        sendNotification: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/send_notification', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }, 
        setCoverMedia: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/set_cover_media', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        updateMediaLocation: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/update_media_location', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        ChangeAlbumMedia: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/change_media_album', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }, 
        addMediaToAlbum: function (reqData) {
            var deferred = $q.defer();

            /* Make HTTP request for smtp email type list */
            $http.post(base_url + 'api/album/add_media', reqData).success(function (data) {
                deferred.resolve(data);
            }).error(function (data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },
        setFileMetaData : function(file) {
            if(!file) {
                return;
            }
            var fileHashId = (new Date()).getTime();
           file.fileHashId = fileHashId;
            file.ext = file.name.split('.');
            if(file.ext.length) {
                file.ext = file.ext[file.ext.length - 1];
            } else {
                file.ext = '';

            }                
            
        }
    }
}); 
