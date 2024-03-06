
/*	Service(s)
===================================*/

// API URL
app.factory('appInfo', function () {
    return{
        serviceUrl: base_url
    }
});
//// for article 

app.factory('articleService', ['$http','$q','appInfo', function( $http, $q ,appInfo) {
									 return {
		
		get_articleList: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/blogs.json',reqData).then(function (data) {
                                data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		like: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/like.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		detail: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/blog_detail.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		comment: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/comment.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		delete_comment: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/delete_comment.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		
		deleteBlog: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/delete_blog.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		createBlog: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/create_blog.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		shortArticleDetail: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/BlogDetail.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
		updateBlog: function(reqData){
			var d = $q.defer();
			
			$http.post(base_url + 'api_blogs/update_blog.json',reqData).then(function (data) {
                            data = data.data;
				d.resolve(data);
				//console.log("Success: "+data);
			}, function(data){
                            data = data.data;
				d.reject(data);
				//console.log("Error: "+data);
			});
			return d.promise;			
		},
		
	};
		 
}]);