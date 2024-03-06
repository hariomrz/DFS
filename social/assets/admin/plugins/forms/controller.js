// Module(s)

angular.module("uixInput", [
	'localytics.directives',
])

.config(
	[		 '$parseProvider',
    function ($parseProvider) {
        return $parseProvider.unwrapPromises(true);
    	}
	])

// Directive(s)
	.directive('uixInput',uixInput)
	.directive('match',uixMatch)
	.directive('uixTextarea',uixTextarea)
	.directive('multipleEmails',multipleEmails)

	function uixInput () {
	return {
		restrict: 'EA',
		replace: true,
		template: '<input>',
		link: function($scope, iElm, iAttrs) {
		        iElm.loadControl();
			}
	}
	}

	function uixTextarea () {
	return {
		restrict: 'EA',
		replace: true,
		template: '<textarea />',
		link: function($scope, iElm, iAttrs) {
			iElm.loadControl();
			}
	}
	}

// uixConfirmPassword Common Control (Password confirmation Field);
	function uixMatch(){
	return {
	    require: 'ngModel',
	    restrict: 'A',
	    scope: {
	        match: '='
	    },
	    link: function(scope, elem, attrs, ctrl) {
	        scope.$watch(function() {
	            return (ctrl.$pristine && angular.isUndefined(ctrl.$modelValue)) || scope.match === ctrl.$modelValue;
	        }, function(currentValue) {
	            ctrl.$setValidity('match', currentValue);
	        });
	    }
	};
	}

// Control Name - UixBlacklist
// Badword list get from database
angular.module("uixBlacklist", [])

	.factory('srBadwordlist', function($q, $http){
		return {
			getList : function(){
				var deferred = $q.defer();

				$http.post('data/badwordlist.json').success(function (data) { // Changes Required in Action URL
				    deferred.resolve(data);
				}).error(function (data) {
				    deferred.reject(data);
				});

				return deferred.promise;
			}
		};
	 })

	.directive('blacklist', function ($timeout, $parse) {
		return {
			restrict: 'A',
			require: 'ngModel',
			scope: { },
			link: function (scope, elem, attrs, ngModel) {
			var str;
	 		  $timeout(function () {
					str = $parse(attrs['blacklist'])();
					Obj = str.trim().split('|');
					return	Obj;
				},100);

				//For DOM -> model validation
				ngModel.$parsers.unshift(function (value) {
					return validate(value);
				});

				//For model -> DOM validation
				ngModel.$formatters.unshift(function (value) {
					return validate(value);
				});

				function validate(value) {
					var valid = true;
					if (typeof value === "undefined") value = "";
					if (angular.isDefined(value) && value.length > 0) {
						value = value.toLowerCase()
						var strRegExPattern = '\\b(' + str + ')(s|es|ed|ing)?\\b';
						var n = value.match(new RegExp(strRegExPattern, 'g'));
						var badwords = "";
					        if (n != null) {
					            for (var i = 0; i < n.length; i++)
					                badwords += n[i] + ",";
					        }
					        var badWordsCount = (badwords.split(',').length);
					        if (badWordsCount > 1) {
					           valid = false; 
					        }
					}
					ngModel.$setValidity('Obj', valid);
					return value;
				}
			}
		};
})
	.controller("blacklistController", function($scope, srBadwordlist) {
		// Badword list get from Service
		srBadwordlist.getList().then(function(data){
			$scope.badwordsList = data//.trim().split('|');
	        return $scope.badwordsList;
		});
	})




.controller("selectOptCtrl", function($scope, $http) {
		$http.get('data/sidebar-items.json').then(function(res){
			$scope.getSelectModals = res.data.Data
		});
		$scope.actionSelect = function(){
			console.log($scope.getSelect)
		}
	});



function multipleEmails() {
        var EMAIL_REGEXP = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;                                       
        function validateAll(ctrl, validatorName, value) {
            var validity = ctrl.$isEmpty(value) || value.split(',').every(
                function (email) {
                    return EMAIL_REGEXP.test(email.trim());
                }
            );

            ctrl.$setValidity(validatorName, validity);
            return validity ? value : undefined;
        }

        return {
            restrict: 'A',
            require: 'ngModel',
            link: function postLink(scope, elem, attrs, modelCtrl) {
                function multipleEmailsValidator(value) {
                    return validateAll(modelCtrl, 'multipleEmails', value);
                };

                modelCtrl.$formatters.push(multipleEmailsValidator);
                modelCtrl.$parsers.push(multipleEmailsValidator);
            } 
        };
}