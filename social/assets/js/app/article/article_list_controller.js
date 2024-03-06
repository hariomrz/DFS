/**** Article Controller ***/

!(function (app, angular) {



    app.controller('ArticleListCtrl', ArticleListCtrl);

    ArticleListCtrl.$inject = ['$scope', '$http', '$q', '$rootScope', 'WallService'];
    function ArticleListCtrl($scope, $http, $q, $rootScope, WallService) {                  
        $('.parallax-layer').vParallax();
        var articleTypes = {
            all: $scope.lang.article_all,
            fav: $scope.lang.article_fav,
            myCreated: $scope.lang.article_crtd_by_me
        };
        var articleListTypeLast = 'all';
        var articleListOrder = {
            Popularity : $scope.lang.article_popularity,
            recent : $scope.lang.article_recent,
            Name : $scope.lang.article_name,
        };

        $scope.articleListOrder = articleListOrder['Popularity'];
        $scope.changeArticleOrder = function (articleListOrder) {
            $scope.articleLoadIsBusy = 0;            
            $scope.articleListOrder = articleListOrder['Popularity'];
            
            if(articleListOrder == 'Popularity') {
                $('#FeedSortBy').val('3');            
            } else if(articleListOrder == 'recent') {
                $('#FeedSortBy').val('2');            
            } else {
                $('#FeedSortBy').val(articleListOrder);            
            }
            
            $rootScope.$broadcast('onWikiPostTypeChange', {articleListType: articleListTypeLast})
        }

        $scope.articleListType = articleTypes['all'];
        $scope.changeArticleType = function (articleListType) {
            $scope.articleLoadIsBusy = 0;
            $scope.articleListType = articleTypes[articleListType];
            $('#ArticleType').val(articleListType);
            $scope.articleListTypeLast = articleListTypeLast = articleListType;

            $rootScope.$broadcast('onWikiPostTypeChange', {articleListType: articleListType})
        }

        $scope.getRecommendedArticlesTypes = function () {
            $rootScope.$broadcast('onWikiPostTypeRecommended', {showViewAll : true})
        }
        
        $scope.viewAllRArticles = false;
        $scope.viewAllRecommendedArticles = function() {
            $scope.articleLoadIsBusy = 0;
            $scope.viewAllRArticles = true;
            $rootScope.$broadcast('onWikiPostTypeRecommended', {showViewAll : false, reset : true})
        }
        
        $scope.articleLoadIsBusy = false;
        $scope.loadMoreArticles = function() {
            
            if($scope.viewAllRArticles) {
                $rootScope.$broadcast('onWikiPostTypeRecommended', {showViewAll : false});
                return;
            }
            
            $rootScope.$broadcast('onWikiPostTypeLoadMore', {})
        }
        
        $scope.onArticleListInit = function(onBack) {
            
            if(onBack) {    
                $scope.searchKey = '';
                $scope.articleLoadIsBusy = 0;
                $scope.viewAllRArticles = false;
                $rootScope.$broadcast('onWikiPostTypeRecommended', {showViewAll : true, reset : true})
            }
            
            
            $('#FeedSortBy').val('3');
            $rootScope.$broadcast('onWikiPostTypeChange', {articleListType: articleListTypeLast});
            
            $rootScope.$broadcast('onWikiPostAdmin', {});
            
        }
        
        
        
        $scope.$on('onGetArticles', function(evt, data){
            if(data.articleList && data.articleList.length == 0) {
                $scope.articleLoadIsBusy = 1;
            }
        });

        $scope.searchArticles = function(evt, reset) {  
            var srchLength = ($scope.searchKey).length;
            if (srchLength < 2 && !reset) {
                return;
            }
            
            if(reset) {
                $scope.searchKey = '';
            }
            
            $scope.articleLoadIsBusy = 0;            
            if($scope.viewAllRArticles) {
                $rootScope.$broadcast('onWikiPostTypeRecommended', {showViewAll : false, reset : true, SearchKeyword : $scope.searchKey});
                return;
            }
            
            $rootScope.$broadcast('onWikiPostTypeChange', {articleListType: articleListTypeLast, SearchKeyword : $scope.searchKey});
        }

        $scope.get_members_talking = function (members)
        {
            if (!members)
            {
                return;
            }
            var total_members_count = members.length;
            var loopCount = 2;
            var html = '';
            var count = 0;
            if (total_members_count <= loopCount)
            {
                angular.forEach(members, function (val, key) {
                    count++;
                    html += '<span class="text-brand">' + val.Name + '</span>'
                    if (total_members_count == count)
                    {
                        if (total_members_count == 1)
                        {
                            html += ' <span> is talking </span> ';
                        } else
                        {
                            html += ' <span> are talking </span> ';
                        }
                    } else if (total_members_count - 1 == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + '</span>';
                    } else
                    {
                        html += '<span >,</span> ';
                    }
                });
            }else{
                angular.forEach(members, function (val, key) {
                    if (count > loopCount + 1) {
                        return;
                    }
                    count++;
                    if (count <= loopCount)
                    {
                        html += '<span class="text-brand">' + val.Name + '</span>'
                    }
                    if (loopCount + 1 == count)
                    {
                        if (total_members_count - loopCount == 1)
                        {
                            html += ' <span> other is talking </span> ';
                        } else
                        {
                            html += ' <span>others are talking </span> ';
                        }
                    } else if (loopCount == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + (total_members_count - loopCount) + '</span>';
                    } else if (count < loopCount)
                    {
                        html += '<span >,</span> ';
                    }
                });
            }
            return html;
        };
        
        $scope.getEntityURL = function(article){
            if(article.ModuleID == 1) {
                return base_url + 'group/' + article.EntityProfileURL; 
            }
            
            if(article.ModuleID == 3) {
                return base_url +  article.EntityProfileURL; 
            }
            
            if(article.ModuleID == 34) {
                return base_url +  article.EntityProfileURL; 
            }
        }

    }


})(app, angular);


