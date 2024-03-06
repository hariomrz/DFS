!(function (app, angular) {

    app.controller('ActivityDetailsCtrl', ActivityDetailsCtrl);

    function ActivityDetailsCtrl($scope, $http, WallService, appInfo, UtilSrvc, $rootScope) {



        function setWallData(activityData) {

            /* wall data start */
            $scope.wlEttDt = {
                EntityType: activityData.EntityType,
                ModuleID: activityData.ModuleID,
                IsNewsFeed: 0,
                hidemedia: 1,
                IsForumPost: 0,
                page_name: 'userprofile',
                pname: 'wall',
                IsGroup: 0,
                IsPage: 0,
                //Type: "GroupWall",
                LoggedInUserID: UserID,

                ModuleEntityGUID: activityData.ActivityGUID,
                ActivityGUID: activityData.ActivityGUID,
                CreaterUserID: activityData.CreatedByUserID,

            };

            $scope.ModuleID = $scope.wlEttDt.ModuleID;
            $scope.IsAdmin = activityData.IsAdmin;
            $scope.default_privacy = $scope.DefaultPrivacy = activityData.DefaultPrivacy;
            $scope.CommentGUID = '';
            $scope.ActivityGUID = $scope.wlEttDt.ActivityGUID;
            /* wall data end */


            $scope.get_bradcrumbs_details(activityData);
        }

        $scope.getRecommendedArticlesTypes = function () {
            $rootScope.$broadcast('onWikiPostTypeRecommended', {showViewAll: true})
        }

        $scope.$on('onGetPostDetials', function (evt, data) {

            var activities = data.activities;
            var activity = activities[0] || {};


            if (activity.PostType == 4) {
                if ($(window).width() > 767) {
                    setTimeout(function(){                        
                        $('[data-scrollfix="scrollFix2"]').scrollFix({
                            fixTop: 60
                        });
                    },2000);
                }
            }
            setWallData(activity);

        });

        $scope.initActivityDetails = function () {

            var activityGuid = UtilSrvc.getUrlLocationSegment(3, '');

            setWallData({
                ActivityGUID: activityGuid,
                CreatedByUserID: 0,
                DefaultPrivacy: 1,
                ModuleID: 3,
                EntityType: 'User'
            });


        }


        $scope.bradcrumbs_details = [];
        $scope.get_bradcrumbs_details = function (activityData)
        {
            if (!activityData.ModuleEntityID) {
                return;
            }

            var reqData = {
                ModuleID: activityData.ModuleID,
                ModuleEntityID: activityData.ModuleEntityID
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'activity_helper/get_entity_bradcrumbs', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $rootScope.bradcrumbs_details = response.Data;
                    $scope.bradcrumbs_details = response.Data;
                } else
                {
                    if (response.ResponseCode == 412)
                    {
                        //$('#ForumCtrl').remove();
                    }
                    showResponseMessage(response.Message, 'alert-danger');
                    setTimeout(function () {
                        //$window.location.href = base_url+'forum';
                    }, 500)

                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        }

    }

    ActivityDetailsCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo', 'UtilSrvc', '$rootScope'];

})(app, angular);

