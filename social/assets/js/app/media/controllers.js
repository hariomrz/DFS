app.controller('mediaController', ['$scope','$http',function($scope,$http){
   $scope.hideMediaPopup = function(){
        $('.media-popup').hide();
    }

    $scope.mediaDetails = [];
    
    $scope.submitComment = function(){
        var reqData = {};
    }

    $(document).ready(function(){
        $('[data-type="postRegion"]').mCustomScrollbar();
        $('[data-type="autoSize"]').autosize(); 
     
        $(window).resize(function(){
            if($(window).width() >=767){
                thWindow(); 
            }
        }); 
        if($(window).width() >=767){
            thWindow(); 
        }
    });
}]);