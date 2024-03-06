
!(function (app, angular, moment) {

    angular.module('newsletterUserModule', [])
            .controller('newsletterUserController', newsletterUserController);

    function newsletterUserController($scope, $http, UtilSrvc) {

        $scope.userProfileForm = {};
        $scope.userGenderOptions = [
            {value: 0, label: 'Select Gender'},
            {value: 1, label: 'Male'},
            {value: 2, label: 'Female'},
            {value: 3, label: 'Other'},
        ];

        $scope.$on('newsletterUserModuleInit', function (event, data) {
            $scope.userProfile = data.user;
            init(data.params);

        });

        $scope.saveUserProfile = function () {
            var reqData = $scope.userProfileData;
            var isFormValid = true;
            var fieldNames = ['FirstName', 'LastName', 'Gender', 'DOB', 'Email'];
            angular.forEach(fieldNames, function(fieldName){
                var field = $scope.userProfileForm[fieldName];
                field.$pristine = false;
                
                if(!reqData[fieldName]) {
                    isFormValid = false;
                }
                
            });
            
            if(!isFormValid) {
                return;
            }
            
            showLoader();
            
            $http.post(base_url + 'admin_api/newsletter_users/edit_user', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    hideLoader();
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    $('#editProfile').modal('hide');
                    ShowSuccessMsg('Profile updated successfully.');
                    $scope.$emit('refreshNewsletterUserList', {});
                    
                    //$scope.userProfile = angular.copy($scope.userProfileData);
                }
                
                hideLoader();

            }).error(function (data) {
                hideLoader();
                ShowWentWrongError();
            });
        }

        $scope.onTagsGet = function (query, entity_type_set_val) {
            var url = base_url + 'api/tag/get_entity_tags?EntityType=USER&SearchKeyword=' + query + '&entity_type_set=1&entity_type_set_val=' + entity_type_set_val;
            return $http.get(url).then(function (response, status) {
                var tags = [];
                angular.forEach(response.data.Data, function (tagObj) {
                    tagObj.text = tagObj.Name;
                    tags.push(tagObj);
                });

                return tags;
            });
        }


        function getUserGroups() {
            var reqData = {
                NewsLetterSubscriberID: $scope.userProfile.NewsLetterSubscriberID
            };
            $http.post(base_url + 'admin_api/newsletter_users/get_user_groups', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    $scope.userProfile.groups = response.Data;

                    $('[data-toggle="popover"]').popover({
                        placement: 'bottom',
                        trigger: 'hover'
                    });

                }

            }).error(function (data) {
                ShowWentWrongError();
            });
        }

        function init(params) {
            $scope.userProfileData = angular.copy($scope.userProfile);
            $scope.userProfileData.DOB = moment($scope.userProfileData.DOB).format('MM/DD/YYYY');
            getUserGroups();

            hideLoader();
            if (!params.isInit) {
                return;
            }

            var userProfileLocationEle = document.getElementById('userProfileLocation');
            UtilSrvc.initGoogleLocation(userProfileLocationEle, function (locationData) {
                $scope.userProfileData.Location = locationData;
                $scope.userProfileData.LocationStr = UtilSrvc.formatAddress(locationData);
                userProfileLocationEle.value = $scope.userProfileData.LocationStr;

            });

            $("#dob1").datepicker({
                changeMonth: true,
                changeYear: true,
                maxDate: '0'
            });
        }

    }

    newsletterUserController.$inject = ['$scope', '$http', 'UtilSrvc'];


})(app, angular, moment);

