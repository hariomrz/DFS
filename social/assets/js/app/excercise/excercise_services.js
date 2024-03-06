/*	Service(s)
===================================*/
// API URL
app.factory('appInfo', function () {
return{serviceUrl: base_url}
});

app.factory('DrawGraphService', function( $http, $q ,appInfo) {
		 // Return public API.
		 return {
    	DrawGraphServiceFunction:function(reqData){
			var deferred = $q.defer();
				$http.post(base_url + 'api_excercise/GraphData',reqData).then(function (data) {
                                    data = data.data;
				    deferred.resolve(data);
				}, function (data) {
                                    data = data.data;
				    deferred.reject(data);
				});
				return deferred.promise;
    	},
		
		GetPeopleExercisingService:function(reqData){
			var d = $q.defer();
			$http.post(base_url + 'api_excercise/GetPeopleExercising.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
			}, function(data){
                            data = data.data;
				d.reject(data);
			});
			return d.promise;			
		},
		
		
    }
});