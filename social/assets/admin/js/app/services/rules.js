// JavaScript Document
app.factory('RulesService',[ '$http', '$q' ,'appInfo',  function( $http, $q, appInfo) {
	// Return public API.
	return {
      CallApi : function(reqData,Url){ // Common Function to Call Api on given Url with request params
    			var deferred = $q.defer();
    			$http.post(appInfo.serviceUrl+Url,reqData).success(function (data) {
    				deferred.resolve(data);
    			}).error(function (data) {
    				deferred.reject(data);
    			});
    			return deferred.promise;
    	}
    };
}]);