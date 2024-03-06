// JavaScript Document
app.factory('CommonHttpService',[ '$http', 'Upload', function( $http, Upload ) {
	// Return public API.
	return {        
        
        CallPostApi : function(Url, payLoadData, success, error){ // Common Function to Call Post Api on given Url with request params.
          return $http.post(Url, payLoadData).then(success, error);
    	},
        
        CallGetApi : function(Url, success, error){ // Common Function to Call Get Api on given Url.
          return $http.get(Url).then(success, error);
    	},

        CallUploadFilesApi : function(data, Url, success, error, progress) {
          return Upload.upload( { url: Url, data: data } ).then(success, error, progress);
        }
    };
}]);