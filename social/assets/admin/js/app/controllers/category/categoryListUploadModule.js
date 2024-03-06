
!(function (app, angular) {

    angular.module('categoryListUploadModule', ['ngFileUpload'])
            .controller('categoryUploadController', categoryUploadController);

    function categoryUploadController($scope, $http, UtilSrvc, Upload, $q, $window) {
        
        $scope.userUploadsErrors = [];
        $scope.userUploadsfile = {};
        
        $scope.$on('categoryListUploadModuleInit', function (event, data) {
            $scope.userUploadsErrors = [];
            $scope.userUploadsRowsErrors = [];
            $scope.userUploadsfile = {};
            hideLoader();
        });

        $scope.hitToDownloadUserFormat = function () {
            $window.location.href = base_url + 'admin/category/download_category_format';
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
                openPopDiv('uploadCategoryList', 'bounceInDown');
            } else if (action && (action == 'close')) {
               // closePopDiv('uploadCategoryList', 'bounceOutUp');
            }
        };

        $scope.uploadUsers = function (file, errFiles) {
            $scope.userUploadsfile = (file) ? file : {};
            if (!(errFiles.length > 0) && file && !$scope.isProfilePicUploadProcessing) {
                $scope.userUploadsErrors = [];
                var patt = new RegExp("^image");
                $scope.isUsersUploadProcessing = true;
                var paramsToBeSent = {
                    ModuleID: $scope.uploadModuleID,
                    LocalityID: $scope.uploadLocalityID,
                    qqfile: file,
                    AdminLoginSessionKey: angular.element('#AdminLoginSessionKey').val()
                };
                showLoader();

                Upload.upload({url: base_url + 'admin_api/category/upload_category', data: paramsToBeSent})
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
                    var msg = (responseJSON.MessageShow) ? responseJSON.MessageShow : 'Category(s) added into the system successfully.'
                    ShowSuccessMsg(msg);                    
                    $scope.userUploadsRowsErrors = responseJSON.excel_errors_fixes;
                    if($scope.userUploadsRowsErrors.length == 0) {
                        $('#uploadCategoryList').modal('hide');                    
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

    categoryUploadController.$inject = ['$scope', '$http', 'UtilSrvc', 'Upload', '$q', '$window'];


})(app, angular);

