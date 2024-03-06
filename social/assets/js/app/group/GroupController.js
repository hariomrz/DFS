var app = angular.module('App');


//item-pop-up-slick
/*app.directive('itemPopUpSlick', [ 
    '$timeout', '$rootScope',
    function($timeout, $rootScope){
    return {
        link: function($scope, $element, attrs){

            $scope.showslick = false;
            $scope.imgs = angular.extend([], $scope.item.global.galery);

            function startSlick(toshow){
                $timeout((function() {

                    $('#slider-nav').on('init', function(event){
                        $scope.showslick = true;
                    });
                    $('#slider-for').slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: false,
                        fade: true,
                        asNavFor: '#slider-nav',
                    });


                    $('#slider-nav').slick({
                        slidesToShow: 4,
                        slidesToScroll: 1,
                        asNavFor: '#slider-for',
                        focusOnSelect: true,
                        arrows: true,
                        infinite: false,
                    });

                    $scope.currentSlide = 0;
                    $('#slider-for').on('beforeChange', function(event, slick, currentSlide, nextSlide){
                        $scope.currentSlide = nextSlide;
                        $scope.$apply();
                    });

                }), 1);
            }
            $scope.showslick = true;
            startSlick()

            $rootScope.$on("itemimgschanged", function(val1, val2) {
                if($scope.showslick){
                    $('#slider-for').slick('unslick');
                    $('#slider-nav').slick('unslick');
                }
                $scope.showslick = false;
                $timeout((function() {
                    $scope.imgs = angular.extend([], $scope.gitem.global.galery);
                    startSlick()
                }), 1);
            });

        }
    };
}]);*/


app.config(['$routeProvider', '$locationProvider',
    function($routeProvider, $locationProvider) {
        $routeProvider
          .when('/group', {
            /*resolve: resolveController(AssetBaseUrl + 'js/app/group/MyGroupController.js'),
            templateUrl: base_url + 'assets/partials/group/mygroup.html'*/
          })
          .when('/group/discover', {
            /*resolve: resolveController(AssetBaseUrl + 'js/app/group/DiscoverCtrl.js'),
            templateUrl: base_url + 'assets/partials/group/discover.html'*/
          })
          .when('/group/discover/:slug/:id', {
            /*resolve: resolveController(AssetBaseUrl + 'js/app/group/DiscoverCtrl.js'),
            templateUrl: base_url + 'assets/partials/group/discover.html'*/
          })
          .otherwise({
            
          });

        $locationProvider.html5Mode(true);
}]);

app.controller('GroupCtrl',GroupCtrl);
GroupCtrl.$inject = ['$location', '$route', '$routeParams', '$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService','lazyLoadCS'];

function GroupCtrl($location, $route, $routeParams, $rootScope, $scope, appInfo, $http, profileCover, WallService,lazyLoadCS)
{
    $scope.BaseUrl = base_url;
    if($location.path() == '/group')
    {
        $scope.currentPage = 'mygroup';
        if($scope.LoginSessionKey=='')
        {
            window.top.location = base_url+'group/discover';
        }
    }
    else
    {
        $scope.currentPage = 'discover';
    }


    $scope.groupSuggestionConfig = {
        method: {},
        infinite: true,
        slidesToShow:4,
        slidesToScroll:4,
        responsive: 
        [{
            breakpoint: 1200,
            settings: {
                slidesToShow:1
            }
        },
        {
            breakpoint: 992,
            settings: {
                slidesToShow: 1
            }
        },
        {
            breakpoint: 768,
            settings: {
                slidesToShow: 1
            }
        }]
    };

    $scope.suggested_group = AssetBaseUrl + 'partials/widgets/suggested_groups_forum.html'+$scope.app_version;

    $scope.loadMyGroups = function() {
        lazyLoadCS.loadModule({
            moduleName: 'MyGroupModule',
            moduleUrl: AssetBaseUrl + 'js/app/group/MyGroupController.js'+$scope.app_version,
            templateUrl: AssetBaseUrl + 'partials/group/mygroup.html'+$scope.app_version,
            scopeObj: $scope,
            scopeTmpltProp: 'my_group',
            callback: function () {
            }
        });
    }

    $scope.loadDiscover = function () {
        lazyLoadCS.loadModule({
            moduleName: 'DiscoverModule',
            moduleUrl: AssetBaseUrl + 'js/app/group/DiscoverController.js'+$scope.app_version,
            templateUrl: AssetBaseUrl + 'partials/group/discover.html'+$scope.app_version,
            scopeObj: $scope,
            scopeTmpltProp: 'discover',
            callback: function () {
                $scope.$broadcast('onDefaultState', {});
            }
        });
    }

    /*$scope.loadCreateGroup = function () {
        lazyLoadCS.loadModule({
            moduleName: 'CreateGroupModule',
            moduleUrl: AssetBaseUrl + 'js/app/group/CreateGroupController.js'+$scope.app_version,
            templateUrl: base_url + 'assets/partials/group/creategroup.html'+$scope.app_version,
            scopeObj: $scope,
            scopeTmpltProp: 'create_group',
            callback: function () {
                $scope.$broadcast('onCreateGroup',{});
            }
        });
    }*/
    
    $scope.redirectToUrl = function(url)
    {
        $location.path(url);
    }

    if($scope.currentPage == 'discover')
    {
        $scope.loadDiscover();
    }
    else
    {
        $scope.loadMyGroups();
    }

    $scope.changeGroupTabClass = function(e)
    {
        $('.group-tab').removeClass('active');
        $(e).addClass('active');
    }
}