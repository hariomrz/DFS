!(function (app, angular) {

    angular.module('saveFrmSubCatMdl', [])
        .controller('saveFrmSubCatCtrl', saveFrmSubCatCtrl);

    function saveFrmSubCatCtrl($scope, $http, WallService, appInfo) {

        $scope.forumData = {};
        $scope.catData = {};
        $scope.category = false;

        $scope.$on('saveFrmSubCatMdlInit', function (event, data) {

            $scope.forumScope = data.forumScope;
            $scope.forumData = data.forumData;
            $scope.category_detail = data.catData;

            $scope.reset_media();

            $scope.get_forum_category_list($scope.forumData.ForumID, $scope.category_detail.ForumCategoryID, $scope.category_detail);

            if (data.subCatData) {
                $scope.subCatData = data.subCatData;
                $scope.prefill_subcat($scope.category_detail, $scope.subCatData.ForumCategoryID);
                $scope.prefill_subcat_data($scope.subCatData, $scope.subCatData.ForumCategoryID);
            } else {
                $scope.resetFormdata();
                $scope.clear_prefill_subcat($scope.forumData.ForumID);
            }
            $("#addSubCategory").modal();
        });

        $scope.$on('onCategoryDetailsGet', function (event, data) {
            $scope.category_detail = data.catData;
        });

        $scope.prefill_url_scat = function (val) {
            var url = val.replace(new RegExp(' ', 'g'), '');
            if (url.length <= 40) {
                $scope.SubCat.URL = url;
            }
        }

        $scope.remove_subcategory_picture = function () {
            $('#SubCatMediaGUID').val('');
            $scope.SubCat.ProfilePicture = '';
        }
        
        
        $scope.$on('onProfilePictureChange', function(evt, data){            
            $scope.SubCat.ProfilePicture = data.ProfilePicture;
        });

        $scope.prefill_subcat = function (CategoryDetails, SubCatID) {
            angular.forEach(CategoryDetails.SubCategory, function (val, key) {
                if (val.ForumCategoryID == SubCatID) {
                    $scope.SubCat = {
                        ForumCategoryID: SubCatID,
                        ProfilePicture: val.ProfilePicture,
                        Name: val.Name,
                        Description: val.Description,
                        URL: val.URL,
                        CanAllMemberPost: val.CanAllMemberPost,
                        Visibility: val.Visibility
                    };
                }
            });
        }
        $scope.prefill_subcat_data = function (SubCategoryDetails, SubCatID) {
            var val = SubCategoryDetails;
            $scope.SubCat = {
                ForumCategoryID: val.ForumCategoryID,
                ProfilePicture: val.ProfilePicture,
                Name: val.Name,
                Description: val.Description,
                URL: val.URL,
                CanAllMemberPost: val.CanAllMemberPost,
                Visibility: val.Visibility
            };
        };

        $scope.clear_prefill_subcat = function (forum_id) {
            $scope.SubCat = {
                ProfilePicture: '',
                Name: '',
                Description: '',
                URL: '',
                CanAllMemberPost: 1,
                Visibility: 2
            };

            $scope.SubCat.ForumID = forum_id;
            $scope.category = false;
            $scope.current_forum_id = forum_id;
            $('#module_entity_guid').val($scope.forumData.ForumGUID);
        };

        $scope.reset_media = function () {
            $('#CatMediaGUID').val('');
            $('#forumcatprofilepic').attr('src', image_server_path + 'upload/profile/220x220/category_default.png');
        };

        $scope.CreateUpdateSubCategory = function (forum_id) {
            var reqData = $scope.SubCat;
            reqData['ForumID'] = $scope.current_forum_id;
            reqData['MediaGUID'] = $('#SubCatMediaGUID').val();
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/create_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($('#IsWall').length > 0) {
                        $scope.forumScope.get_category_details();
                    } else {
                        $scope.forumScope.getForums();
                    }
                    showResponseMessage(response.Message, 'alert-success');
                    $('#addSubCategory').modal('toggle');
                    $scope.resetFormdata();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        };

        $scope.get_forum_category_list = function (forum_id, category_id, category_data) {
            $scope.forum_categories_list = [];
            var reqData = {ForumID: forum_id};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/forum_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    angular.forEach(response.Data, function (val, key) {
                        $scope.forum_categories_list[val.ForumCategoryID] = val.Name;
                    });
                    $scope.set_selected_category(category_id);
                    $scope.set_selected_category_data(category_data);
                    if (!category_data.Visibility) {
                        $scope.SubCat.Visibility = 2;
                    } else {
                        $scope.SubCat.Visibility = category_data.Visibility;
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        };

        $scope.set_selected_category = function (category_id) {
            $scope.SubCat.ParentCategoryID = category_id;
        };

        $scope.set_selected_category_data = function (category) {
            $scope.SubCat.Cat = category;
        };

        $scope.resetFormdata = function(){
            $scope.AddSubCatForm.SubCatName.$setPristine();
            $scope.AddSubCatForm.SubCatDescription.$setPristine();
            $scope.AddSubCatForm.SubCatUrl.$setPristine();
            $scope.AddSubCatForm.CatID.$setPristine();
        };
    }

    saveFrmSubCatCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo'];


})(app, angular);

