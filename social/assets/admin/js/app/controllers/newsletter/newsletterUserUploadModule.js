
!(function (app, angular) {

    angular.module('newsletterUserUploadModule', ['ngFileUpload'])
            .controller('newsletterUserUploadController', newsletterUserUploadController);

    function newsletterUserUploadController($scope, $http, UtilSrvc, Upload, $q, $window) {
        
        $scope.userUploadsErrors = [];
        $scope.userUploadsfile = {};
        
        $scope.$on('newsletterUserUploadModuleInit', function (event, data) {
            $scope.userUploadsErrors = [];
            $scope.userUploadsRowsErrors = [];
            $scope.userUploadsfile = {};
            hideLoader();
        });

        $scope.hitToDownloadUserFormat = function () {
            $window.location.href = base_url + 'admin/newsletter/download_user_format';
        };

        $scope.validateFileSize = function (file) {
            var defer = $q.defer();
            var isResolvedToFalse = false;
            var fileName = file.name;
            var validExtensions = ['xls', 'xlsx']; //array of valid extensions
            var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
            if ($.inArray(fileNameExt, validExtensions) == -1) {
                ShowErrorMsg('File type ' + fileNameExt + ' not allowed.');
                defer.resolve(false);
                isResolvedToFalse = true;
            }

            var maxFileSize = 4194304 /*4194304 Bytes = 4Mb*/;
            if (parseInt(file.size) > maxFileSize) { // if image/document size > 4194304 Bytes = 4 Mb
                file.$error = 'size';
                file.$error = 'Size Error';
                ShowErrorMsg(file.name + ' is too large.');
                defer.resolve(false);
                isResolvedToFalse = true;
            }
            if (!isResolvedToFalse) {
                defer.resolve(true);
            }
            return defer.promise;
        }

        $scope.resetUploadUsers = function (action) {
            $scope.userUploadsErrors = [];
            $scope.userUploadsRowsErrors = [];
            $scope.userUploadsfile = {};
            if (action && (action == 'open')) {
                openPopDiv('uploadUser', 'bounceInDown');
            } else if (action && (action == 'close')) {
                closePopDiv('uploadUser', 'bounceOutUp');
            }
        };

        $scope.uploadUsers = function (file, errFiles) {
            $scope.userUploadsfile = (file) ? file : {};
            if (!(errFiles.length > 0) && file && !$scope.isProfilePicUploadProcessing) {
                $scope.userUploadsErrors = [];
                var patt = new RegExp("^image");
                $scope.isUsersUploadProcessing = true;
                var paramsToBeSent = {
                    qqfile: file,
                    AdminLoginSessionKey: angular.element('#AdminLoginSessionKey').val()
                };
                showLoader();

                Upload.upload({url: base_url + 'admin_api/newsletter_users/upload_users', data: paramsToBeSent})
                        .then(uploadSuccessCall,uloadErrorCall,uploadProgressCall);

            } else {
                hideLoader();
                $scope.isUsersUploadProcessing = false;
            }
        };

        function uloadErrorCall(response) {
            $scope.isUsersUploadProcessing = false;
            hideLoader();
        }
        
        function uploadProgressCall(evt) {
            
        }

        function uploadSuccessCall(response) {
            var responseJSON = response.data;
            if (responseJSON.ResponseCode === 200) {
                if (responseJSON.Message == 'Success') {
                    $scope.resetUploadUsers('close');
                    var msg = (responseJSON.MessageShow) ? responseJSON.MessageShow : 'User(s) added into the system successfully.'
                    ShowSuccessMsg(msg);                    
                    $scope.userUploadsRowsErrors = responseJSON.excel_errors_fixes;
                    if($scope.userUploadsRowsErrors.length == 0) {
                        $('#uploadList').modal('hide');                    
                    }                                        
                } else {
                    $scope.userUploadsErrors = responseJSON.Error;
                    ShowErrorMsg(responseJSON.Message);
                }
            } else {
                $scope.userUploadsErrors = responseJSON.Error;
                ShowErrorMsg(responseJSON.Message);
            }

            $scope.isUsersUploadProcessing = false;
            hideLoader();
        }

    }

    newsletterUserUploadController.$inject = ['$scope', '$http', 'UtilSrvc', 'Upload', '$q', '$window'];


})(app, angular);

