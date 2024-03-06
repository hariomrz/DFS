!(function (app, angular) {

    angular.module('relatedArticlePopupMdl', [])
        .controller('relatedArticleCtrl', relatedArticleCtrl);

    function relatedArticleCtrl($scope, $http, WallService, appInfo,Settings) {

        $scope.wallScope = {};
        $scope.SiteURL = Settings.getSiteUrl();
        
        $scope.$on('relatedArticlePopupMdlInit', function (event, data) {
            $scope.wallScope = data.wallScope;            
            $scope.relatedArticleDetails(data);
        });
        
        $scope.relatedArticleDetails = function (data) {
            $('.wiki-suggested-listing').mCustomScrollbar({
                callbacks: {
                    onTotalScroll: function () {
                        $scope.get_wiki_post();
                    }
                }
            });

            $scope.checked_related_articles = [];
            var activity_id = data.ActivityID;
            $scope.local_article_data = data;
            $scope.related_article_id = activity_id;
            var reqData = {ActivityID: activity_id};
            WallService.CallApi(reqData, 'activity/get_related_activity').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.related_articles = response.Data;
                    $scope.get_wiki_post(activity_id);
                }
            });
        }
        
    }

    relatedArticleCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo','Settings'];

})(app, angular);