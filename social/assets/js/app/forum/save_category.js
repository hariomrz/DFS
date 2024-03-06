
!(function (app, angular) {

    angular.module('saveFrmCatMdl', [])
            .controller('saveFrmCatCtrl', saveFrmCatCtrl);

    function saveFrmCatCtrl($scope, $http, WallService, appInfo) {

        $scope.forumData = {};
        $scope.catData = {};
        $scope.category = false;

        $scope.$on('saveFrmCatMdlInit', function (event, data) {

            $scope.forumScope = data.forumScope;
            $scope.forumData = data.forumData;
            
            $scope.reset_media();
            
            if (data.catData) {
                $scope.category_detail = data.catData;
                $scope.prefill_category($scope.forumData.ForumID, $scope.category_detail);
            } else {
                $scope.resetFormdata();
                $scope.clear_prefill_category($scope.forumData.ForumID);
            }
            $("#addCategory").modal();
        });
        
        
        $scope.$on('onCategoryDetailsGet', function (event, data) {
            $scope.category_detail = data.catData;
        });

        $scope.reset_media = function () {
            $('#CatMediaGUID').val('');
            $('#forumcatprofilepic').attr('src', image_server_path + 'upload/profile/220x220/category_default.png');
        }
        
        $scope.prefill_url_cat = function (val) {
            var url = val.replace(new RegExp(' ', 'g'), '');
            if (url.length <= 40)
            {
                $scope.CreateUpdateCat.URL = url;
            }
        }
        
        $scope.remove_category_picture = function ()
        {
            $('#CatMediaGUID').val('');
            $scope.CreateUpdateCat.ProfilePicture = '';
        }
        
        $scope.$on('onProfilePictureChange', function(evt, data){            
            $scope.CreateUpdateCat.ProfilePicture = data.ProfilePicture;
        });
        
        $scope.clear_prefill_category = function (forum_id) {
            $scope.CreateUpdateCat = {
                IsDiscussionAllowed: 2,
                CanAllMemberPost: 2,
                Visibility: 1,
                Name: '',
                Description: forum_id,
                URL: '',
                Description: '',
                ProfilePicture: ''
            };
            
            $scope.CreateUpdateCat.ForumID = forum_id;
            $scope.category = false;
            $scope.current_forum_id = forum_id;
            $('#module_entity_guid').val($scope.forumData.ForumGUID);
        }

        $scope.CreateUpdateCategory = function () {
            var reqData = $scope.CreateUpdateCat;
            reqData['MediaGUID'] = $('#CatMediaGUID').val();
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/create_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if ($('#IsWall').length > 0)
                    {
                        $scope.forumScope.get_category_details();
                    } else
                    {
                        $scope.forumScope.getForums();
                    }
                    $('#addCategory').modal('toggle');
                    $scope.resetFormdata();
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        }

        $scope.prefill_category = function (forum_id, category, wall) {

            if (wall == '1') {
                $scope.CreateUpdateCat = {
                    IsDiscussionAllowed: $scope.category_detail.IsDiscussionAllowed,
                    CanAllMemberPost: $scope.category_detail.CanAllMemberPost,
                    Visibility: $scope.category_detail.Visibility,
                    Name: $scope.category_detail.Name,
                    Description: $scope.category_detail.Description,
                    URL: $scope.category_detail.URL,
                    ForumCategoryID: category.ForumCategoryID,
                    ProfilePicture: $scope.category_detail.ProfilePicture,
                    ForumID: $scope.category_detail.ForumID
                };
                if ($scope.category_detail.ProfilePicture !== '') {
                    $('#forumcatprofilepic').attr('src', image_server_path + 'upload/profile/220x220/' + $scope.category_detail.ProfilePicture);
                }
                $('#CatMediaGUID').val($scope.category_detail.MediaGUID);
            } else {
                var v = category;
                $scope.CreateUpdateCat = {
                    IsDiscussionAllowed: v.IsDiscussionAllowed.toString(),
                    CanAllMemberPost: v.CanAllMemberPost,
                    Visibility: v.Visibility,
                    Name: v.Name,
                    Description: forum_id,
                    URL: v.URL,
                    Description: v.Description,
                    ForumCategoryID: category.ForumCategoryID,
                    ProfilePicture: v.ProfilePicture,
                    ForumID: v.ForumID
                };
                if (v.ProfilePicture !== '') {
                    $('#forumcatprofilepic').attr('src', image_server_path + 'upload/profile/220x220/' + v.ProfilePicture);
                }
                $('#CatMediaGUID').val(v.MediaGUID);
            }
        };

        $scope.resetFormdata = function(){
            $scope.AddCatForm.CatName.$setPristine();
            $scope.AddCatForm.CatDescription.$setPristine();
            $scope.AddCatForm.CatUrl.$setPristine();
            $scope.AddCatForm.ForumID.$setPristine();
        };

    }

    saveFrmCatCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo'];


})(app, angular);

