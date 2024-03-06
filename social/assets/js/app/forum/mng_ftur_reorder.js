
!(function (app, angular) {

    angular.module('mngFturReorderMdl', [])
            .controller('mngFturReorderCtrl', mngFturReorderCtrl);

    function mngFturReorderCtrl($scope, $http, WallService, appInfo) {
        
        $scope.forum_categories = [];
        $scope.forum_categories_list = [];
        $scope.current_category_id = 0;
        $scope.featured_categories = [];
        
        $scope.$on('mngFturReorderMdlInit', function (event, data) {            
            $scope.forumScope = data.forumScope;            
            $scope.forums_reorder = data.forums_reorder;
            
            if(data.modalId == 'manageFeature' || data.modalId == 'reOrderCategory') {
                var ForumCategoryID = data.ForumCategoryID || 0;
                $scope.get_forum_categories(data.forumId, ForumCategoryID);
            }
                        
            $("#" + data.modalId).modal();
            
        });    
        
        $scope.change_category_order = function () {
            var reqData = {ForumID: $scope.current_forum_id, ForumCategoryID: $scope.current_category_id, OrderData: []};
            var count = 1;
            angular.forEach($scope.forum_categories, function (val, key) {
                reqData.OrderData.push({ForumCategoryID: val.ForumCategoryID, DisplayOrder: count});
                count++;
            });
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/change_category_order', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $('#reOrderCategory').modal('toggle');
                    $scope.forumScope.getForums();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        }
        
        $scope.disable_checkbox = function (forum_category_id)
        {
            var count = 0;
            angular.forEach($scope.forum_categories, function (val, key) {
                if (val.IsFeatured == 1)
                {
                    if (val.ForumCategoryID == forum_category_id)
                    {
                        console.log(1);
                        return false;
                    }
                    count++;
                }
            });

            if (count >= 3)
            {
                return true;
            } else
            {
                return false;
            }
        }
        
        
        $scope.make_featured = function (forum_id)
        {
            angular.forEach($scope.forum_categories, function (val, key) {
                if (val.ForumCategoryID == forum_id)
                {
                    if ($scope.forum_categories[key].IsFeatured == 1)
                    {
                        $scope.forum_categories[key].IsFeatured = 0;
                    } else
                    {
                        $scope.forum_categories[key].IsFeatured = 1;
                    }
                }
            });
        }
        
        
        
        $scope.set_forum_categories = function ()
        {
            var reqData = {ForumID: $scope.current_forum_id, FeatureData: []};

            angular.forEach($scope.forum_categories, function (val, key) {
                if (val.IsFeatured == 1)
                {
                    reqData.FeatureData.push({ForumCategoryID: val.ForumCategoryID});
                }
            });

            WallService.CallPostApi(appInfo.serviceUrl + 'forum/set_feature_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $('#manageFeature').modal('toggle');
                    $scope.forumScope.getForums();
                    showResponseMessage('Success', 'alert-success');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }
        
        
        $scope.change_forum_order = function ()
        {
            var reqData = {OrderData: []};
            var count = 1;
            angular.forEach($scope.forums_reorder, function (val, key) {
                reqData.OrderData.push({ForumID: val.ForumID, DisplayOrder: count});
                count++;
            });
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/change_order', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $('#reOrderForum').modal('toggle');
                    $scope.forumScope.getForums();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        }
        
        
        
        $scope.get_forum_categories = function (forum_id, category_id) {
            $scope.current_forum_id = forum_id;
            $scope.forum_categories = [];
            $scope.featured_categories = [];
            $scope.forum_categories_list = [];
            if (!category_id)
            {
                category_id = 0;
            }
            var reqData = {ForumID: forum_id, ForumCategoryID: category_id};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/manage_feature_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.forum_categories = response.Data;
                    $scope.current_category_id = category_id;
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        }
        
        
    }

    mngFturReorderCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo'];


})(app, angular);

