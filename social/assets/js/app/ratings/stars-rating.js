(function() {
    app.controller('ratingController', ratingController);
    app.directive('starRating', starRating);
 
  function ratingController() {
    
    this.services      = 4;
    this.deadline      = 2;
    this.design        = 3;
    this.communication = 1;
    this.isReadonly    = true;

    this.rateFunction  = function(rating) {
      //console.log('Rating selected: ' + rating);
    };
  }

  function starRating() {
    return {
      restrict: 'EA',
      replace: true,
      template:
      '<ul class="star-all-list pull-left">'+
       '<li class="starRating-{{$index+1}} star-icon" ng-repeat="star in stars" ng-class="{filled: star.filled}" ng-click="toggle($index)">&nbsp;</li>'+
     '</ul>', 
      scope: {
        ratingValue: '=ngModel',
        max: '=?', // optional (default is 5)
        onRatingSelect: '&?',
        readonly: '=?'
      },
      link: function(scope, element, attributes) {
        scope.stars = [];
          for (var i = 0; i < scope.max; i++) { 
            scope.stars.push({  
           
            });
          }
        if (scope.max == undefined) {
            scope.max = 5;
          }
        function updateStars() {
          scope.stars = []; 
          for (var i = 0; i < scope.max; i++) { 
            scope.stars.push({
              filled: i < scope.ratingValue
            });
          } 
        };
        scope.toggle = function(index) {
          if (scope.readonly === undefined || scope.readonly === false){
            scope.ratingValue = index + 1;
//            scope.onRatingSelect({
//              rating: index + 1
//            });
          }
        };
        scope.$watch('ratingValue', function(oldValue, newValue) {
          if (newValue) {
            updateStars();
          }
        });
      }
    };
  }
})();
