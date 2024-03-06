// JavaScript Document
app.factory('WallService', ['$http', '$q', 'Upload', 'appInfo', 'apiService', function ($http, $q, Upload, appInfo, apiService) {
        // Return public API.
        var serviceUrl = appInfo.serviceUrl
        if (IsAdminView == 1)
        {
            serviceUrl = base_url + 'api/';
        }
        return {
            CallApi: function (reqData, Url) { // Common Function to Call Api on given Url with request params
                var deferred = $q.defer();
                if (Url.toLowerCase().indexOf("activity") >= 0 && IsAdminView == 1)
                {
                    serviceUrl = base_url + 'admin_api/';
                    Url = Url.replace('activity', 'adminactivity');
                }
                if (Url == 'group/create')
                {
                    serviceUrl = base_url + 'api/';
                }
                $http.post(serviceUrl + Url, reqData)
                        .then(function onSuccess(response) {
                            var response = response.data;
                            deferred.resolve(response);
                        }, function onError(response) {
                            var data = response.data;
                            deferred.reject(data);
                        });

                return deferred.promise;
            },

            CallPostApi: function (Url, payLoadData, success, error) { // Common Function to Call Post Api on given Url with request params.
                return $http.post(Url, payLoadData).then(success, error);
            },

            CallGetApi: function (Url, success, error) { // Common Function to Call Get Api on given Url.
                return $http.get(Url).then(success, error);
            },

            CallUploadFilesApi: function (data, url, success, error, progress) {
                if (IsAdminView == 1)
                {
                    serviceUrl = base_url + 'admin_api/';
                }
                
                data.fileHashId = data.qqfile.fileHashId;
                
                return uploadLocalServer(data, url, success, error, progress);
            },

            FileUploadProgress: apiService.FileUploadProgress,
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
        };



        function uploadAws($scope) {
            
            var file;

            Upload.upload({
                url: 'https://angular-file-upload.s3.amazonaws.com/', //S3 upload url including bucket name
                method: 'POST',
                data: {
                    key: file.name, // the key to store the file on S3, could be file name or customized
                    AWSAccessKeyId: '<YOUR AWS AccessKey Id>',
                    acl: 'private', // sets the access to the uploaded file in the bucket: private, public-read, ...
                    policy: $scope.policy, // base64-encoded json policy (see article below)
                    signature: $scope.signature, // base64-encoded signature based on policy string (see article below)
                    "Content-Type": file.type != '' ? file.type : 'application/octet-stream', // content type of the file (NotEmpty)
                    filename: file.name, // this is needed for Flash polyfill IE8-9
                    file: file
                }
            });

        }

        function uploadLocalServer(data, url, success, error, progress) {
            return Upload.upload({url: serviceUrl + url, data: data}).then(success, error, progress);
        }



    }]);