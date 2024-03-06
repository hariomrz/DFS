
!(function (app, angular) {

    angular.module('saveForumModule', [])
            .controller('saveForumController', saveForumController);

    function saveForumController($scope, $http, WallService, appInfo) {
        
        $scope.forumData = {};

        $scope.$on('saveForumModuleInit', function (event, data) {

            if (data.forumData) {
                $scope.forumData = data.forumData;
                $scope.prefill_forum();
            }  else {
                $scope.resetFormdata();
                $scope.clear_forum();
            }          
            $scope.forumScope = data.forumScope;
            $("#addForum").modal();
        });

        $scope.clear_forum = function () {
            $scope.addEditForumPopupTitle = 'Add Forum';
            $scope.CreateUpdate = {
                Name: '',
                Description: '',
                URL: '',
                ForumID: ''
            };
        };
        
        $scope.prefill_forum = function () {
            $scope.addEditForumPopupTitle = 'Edit Forum';
            var val = $scope.forumData;
            $scope.CreateUpdate = {
                Name: val.Name, 
                Description: val.Description, 
                URL: val.URL, 
                ForumID: val.ForumID
            };
        };
        
        $scope.prefill_url_forum = function (val) {
            var url = val.replace(new RegExp(' ', 'g'), '');
            if (url.length <= 40)
            {
                $scope.CreateUpdate.URL = url;
            }
        };

        $scope.CreateUpdateForum = function ()
        {
            var reqData = $scope.CreateUpdate;
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/create', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if (reqData.ForumID)
                    {
                        $scope.forumScope.getForums();
                    } else
                    {
                        $scope.forumScope.get_forum_names();
                        $scope.forumScope.forums.push(response.Data[0]);
                        $scope.forumScope.forums_reorder.push(response.Data[0]);
                        $("html, body").animate({scrollTop: $(document).height()}, 1000);
                    }
                    $('#addForum').modal('toggle');
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        };

        $scope.resetFormdata = function(){
            $scope.createUpdateForum.ForumName.$setPristine();
            $scope.createUpdateForum.ForumDescription.$setPristine();
            $scope.createUpdateForum.ForumUrl.$setPristine();
        };
    }

    saveForumController.$inject = ['$scope', '$http', 'WallService', 'appInfo'];


})(app, angular);

