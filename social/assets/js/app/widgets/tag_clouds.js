!(function (app, angular) {

    app.factory('tagCloudSrvc', tagCloudSrvc);

    function tagCloudSrvc(appInfo, WallService) {
        
        return {
            extendScope : extendScope
        };

        function extendScope(scope) {
            scope.popularTags = [];
            scope.loadPopularTags = function () {

                var requestPayload = {
                    ModuleID: 34,
                    ModuleEntityID: $('#ForumCategoryID').val(),
                    Loginsessionkey: LoginSessionKey
                };
                var url = appInfo.serviceUrl + 'tag/get_popular_tags';
                WallService.CallPostApi(url, requestPayload, function (successResp) {
                    var response = successResp.data;
                    scope.popularTags = response.Data;
                });
            };

            scope.filterByPopularTags = function (tag) {
                angular.element('#WallPostCtrl').scope().updateWallPost([tag]);
                scope.filterFixed = 1;
            }

            scope.$on('onNewPostCreated', function (event, data) {
                scope.loadPopularTags();
            });

            scope.$on('onPostRemoved', function (event, data) {
                scope.loadPopularTags();
            });
        }
    }

    tagCloudSrvc.$inject = ['appInfo', 'WallService'];

})(app, angular);