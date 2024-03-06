// Module(s)
angular.module("vsocial", ['summernote'])  
.directive("customScroll", function() {
    return {
        restrict: "EA",
        link: function(scope, elem, attrs, ngModelCtrl) {
            elem.mCustomScrollbar();
        }
    }
})
.controller('mainCtrl', ['$scope', function($scope) {
    $scope.hidePostview = function(){
        $scope.postViewopwn = false;
    }
    $scope.options = {
        placeholder: 'Write here and use @ to tag someone.',
        airMode: false,
        popover: {},
        disableDragAndDrop: true,
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
           // ['color', ['color']],
            ['para', ['paragraph']],
            ['insert', ['link', 'picture', 'video']]
        ]
    };
    $scope.slickSlider = function(){ 
        setTimeout(function(){
            $('#parseImg').slick({
                  dots: false,
                  infinite: false,
                  speed: 300,
                  slidesToShow: 1
            });
        }); 
    }

}]) 