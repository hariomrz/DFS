app.controller('DrawGraphCtrl', function ($scope,DrawGraphService) {
$scope.DrawGraph=function () 
{
	ExerciseType=0;ExerciseTime=0;
	var ExerciseType=$("#IdExerciseType option:selected").val();
	var ExerciseTime=$("#IdExerciseTime option:selected").val();
	var requestData={ExerciseType:ExerciseType,ExerciseTime:ExerciseTime};
	DrawGraphService.DrawGraphServiceFunction(requestData).then(function(response){ 
		if(response.ResponseCode==200)
		{
			console.log(response.AddiontalInfo);
			$("#UserTotalMinutes").text(response.AddiontalInfo.UserTotalMinutes);
			$("#UserSession").text(response.AddiontalInfo.UserSession);
			$("#UserResultPer").text(response.AddiontalInfo.UserResultPer);
			$("#TotalUSers").text(response.AddiontalInfo.TotalUSers);
			$("#TotalMinutes").text(response.AddiontalInfo.TotalMinutes);

			fullarray=new Array();
			colorarray=new Array();

			if(ExerciseType==1)
			{
				headingarray=new Array();
				headingarray.push('Genre');
				headingarray.push('Mind Focus');
				fullarray.push(headingarray);
				jQuery.each(response.Data,function(index,value) {
					innerarray=new Array();
					innerarray.push(value.month+"-"+value.year);
					innerarray.push(parseInt(value.themesum));
					fullarray.push(innerarray);
				});
				colorarray.push('#00B7EB');
			} 
			else if(ExerciseType==2)
			{
				headingarray=new Array();
				headingarray.push('Genre');
				headingarray.push('Guided Audio');
				fullarray.push(headingarray);
				jQuery.each(response.Data,function(index,value) {
					innerarray=new Array();
					innerarray.push(value.month+"-"+value.year);
					innerarray.push(parseInt(value.audiosum));
					fullarray.push(innerarray);
				});
				colorarray.push('#F36D5F');
			}
			else
			{
				headingarray=new Array();
				headingarray.push('Genre');
				headingarray.push('Mind Focus');
				headingarray.push('Guided Audio');
				fullarray.push(headingarray);
				jQuery.each(response.Data,function(index,value) {
					innerarray=new Array();
					innerarray.push(value.month+"-"+value.year);
					innerarray.push(parseInt(value.themesum));
					innerarray.push(parseInt(value.audiosum));
					fullarray.push(innerarray);
				});
				colorarray.push("#00B7EB");
				colorarray.push("#F36D5F");
			}
			
			
			var data = google.visualization.arrayToDataTable(fullarray);
			var options = {
			colors : colorarray,
			vAxis: {title: 'Minutes', titleTextStyle: {color: 'red'}},
			hAxis: {title: 'Months', titleTextStyle: {color: 'red'}},
			legend: { position: 'right', maxLines: 3 },
			bar: { groupWidth: '75%' },
			isStacked: true,
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
			chart.draw(data, options);
		}
	})
};

$scope.GetPeopleExercising=function(pid)
{
	var jsonData={};
	DrawGraphService.GetPeopleExercisingService(jsonData).then(function(response){
		$scope.PeopleExercisingCount=response.PeopleExercising.length;
		$scope.listDataUser=[];
		$scope.listDataUser.push(response.PeopleExercising);
		console.log($scope.listDataUser);
	})	
}

})


$(document).ready(function(){
	$('#IdExerciseTime').change(function(){
		angular.element(document.getElementById('DrawGraphCtrl')).scope().DrawGraph();
	});

	$('#IdExerciseType').change(function(){
		angular.element(document.getElementById('DrawGraphCtrl')).scope().DrawGraph();
	});
});