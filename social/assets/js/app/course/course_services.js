/*	Service(s)
===================================*/
// API URL
app.factory('appInfo', function () {
return{
	serviceUrl: base_url
}
});
//// for course

app.factory('CourseService', function( $http, $q ,appInfo) {
return{
	GetCourseLecturesService:function(reqData){
		var deferred = $q.defer();
		$http.post(base_url+'api_course/GetCourseDetails.json',reqData).then(function (data) {
                    data = data.data;
		deferred.resolve(data);
		},  function (data) {
                    data = data.data;
		deferred.reject(data);
		});
		return deferred.promise;
	},
	
	GetLectureMediaType:function(reqData){
		var deferred = $q.defer();
		$http.post(base_url+'api_course/GetLectureMediaType.json',reqData).then(function (data) {
                    data = data.data;
		deferred.resolve(data);
		},  function (data) {
                    data = data.data;
		deferred.reject(data);
		});
		return deferred.promise;
	},

	CreateCourseSectionService:function(reqData){
		var deferred = $q.defer();
		$http.post(base_url+'api_course/CreateCourseSection.json',reqData).then(function (data) {
                    data = data.data;
		deferred.resolve(data);
		},  function (data) {
                    data = data.data;
		deferred.reject(data);
		});
		return deferred.promise;
	},
	
	CreateLectureService:function(reqData){
		var deferred = $q.defer();
		$http.post(base_url+'api_course/CreateSectionLecture',reqData).then(function(data) {
                    data = data.data;
		deferred.resolve(data);
		},  function (data) {
                    data = data.data;
		deferred.reject(data);
		});
		return deferred.promise;
	},
	
	GetCourseReviewService:function(reqData){
		var deferred = $q.defer();
		$http.post(base_url+'api_review/CourseReview',reqData).then(function(data) {
                    data = data.data;
		deferred.resolve(data);
		},  function (data) {
                    data = data.data;
		deferred.reject(data);
		});
		return deferred.promise;
	},

	get_CourseList: function(reqData){
		var d = $q.defer();
		$http.post(base_url+'api_course/Courses.json',reqData).then(function(data) {
                    data = data.data;
		d.resolve(data);
			console.log("Success: "+data);
		},  function(data){
                    data = data.data;
			d.reject(data);
			console.log("Error: "+data);
		});
		return d.promise;			
	},

	CreateCourse:function(reqData){
	var deferred=$q.defer();
	$http.post(base_url+'api_course/createcourse',reqData).then(function(data) {
            data = data.data;
	deferred.resolve(data);
	},  function (data) {
            data = data.data;
	deferred.reject(data);
	});
	return deferred.promise;
	},

	CreateCourseBillingAddress:function(reqData){
	var deferred = $q.defer();
	/*$http.post(base_url + 'api_course/createcourse',reqData).then(function (data) {
         *data = data.data;
	deferred.resolve(data);
	},  function (data) {
            data = data.data;
	deferred.reject(data);
	});*/
	return deferred.promise;
	},

	GetCourseDetails:function(reqData){
	var deferred = $q.defer();
	$http.post(base_url+'api_course/GetCourseDetails.json',reqData).then(function (data) {
            data = data.data;
	deferred.resolve(data);
	},  function (data) {
            data = data.data;
	deferred.reject(data);
	});
	return deferred.promise;
	},

}
});