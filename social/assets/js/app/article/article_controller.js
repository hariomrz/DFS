$(document).ready(function(){
	angular.bootstrap(document, ['App']);
});


var app = angular.module('App' , ['ui.bootstrap']);
/*	Controller(s)
===================================*/

/**** Article Controller ***/

app.controller('ArticlePageCtrl', function($scope, articleService){
				
	//$scope.filteredTodos = [],
	$scope.currentPage = 1,
	$scope.numPerPage = 2,
	$scope.maxSize = 3;
	$scope.grpStatus = ''; 
    $scope.orderByField = '';
    $scope.reverseSort = '';
	$scope.noOfObj='';
      
        $scope.registeredArticle = function(){
                  
					$scope.searchKey = '' ; 
					if( $('#searchgrp').val() ){
                       $scope.searchKey = $('#searchgrp').val();
                    }
					
					$scope.grpStatus ='';
					if( $('#cardType').val() ){
                       $scope.grpStatus = $('#cardType').val();
                    }
					
					$scope.hdngrpid= '' ; 
					if( $('#module_entity_id').val() ){
                       $scope.hdngrpid = $('#module_entity_id').val();
                    }
					
					$scope.articleStatus= '' ; 
					if( $('#articleStatus').val() ){
                       $scope.articleStatus = $('#articleStatus').val();
                    }
					
					$scope.serachArticle= '' ;
					if( $('#serachArticle').val() ){
                       $scope.serachArticle = $('#serachArticle').val();
                    }			
					
					 var reqData = { Blog:{begin: $scope.currentPage, end:$scope.numPerPage, startDate:$scope.startDate,Status:$scope.articleStatus, search_key:$scope.serachArticle, status:$scope.grpStatus ,sort_by:$scope.orderByField ,order_by:$scope.reverseSort ,userType : $scope.usertype,alertstatus:$scope.alertstatus, id:$scope.hdngrpid} }
                    var reqUrl = reqData[1] ;
					articleService.get_articleList(reqData).then(function(response){
					 $('#articleStatus').val("");
					 $('#serachArticle').val("");
                    $scope.listData = [] 
                    $scope.noOfObj = response.Data.TotalRecords ; 
					$scope.ttl = response.ttl ; 
                    $('#hdnQuery').val(response['Data']['query']);
					
					//If no of records > 0 then show
                    $('#grpContainer').show();
                    $('#GroupPageCtrl').show();
                     $('#grpHasNoResult').hide();
						 
                    //If no of records == 0 then hide
                    if($scope.ttl == 0){
						
                     $('#grpContainer').hide();
                    $('#GroupPageCtrl').hide();
                      $('#grpHasNoResult').css('display','block');
                    }
					
					if($scope.ttl > 0){
  						$scope.listData = response.Data; 
						console.log($scope.listData);
						
					}
                    $('#hdnQuery').val(response.last_query);

               }), function(error){
	              }
				  
				//Function for set group id
				$scope.SetArt = function (group) {
				 $('#btngrpid').val(group.GroupID); 
				}
	}
	
	
	$scope.likepost = function()
	{
		$scope.GroupID = ''
		$scope.setValue = '';
		
		$scope.articleid = ''; 
		if($('#articleid').val())
		{
			$scope.articleid = $('#articleid').val() ; 
		}
		
		reqData = {Blog:{PostGuID:$scope.postguid,type:'like',BlogID:$scope.articleid}};
		
			articleService.like(reqData).then(function(response){
											//console.log();
								$('#likecount').html(response.Message);				   
							 //$scope.registeredArticle() ; 
			//$scope.GetwallPost() ; 
		})	
	}
	
	$scope.articleDetail = function()
	{
		$scope.GroupID = ''
		$scope.setValue = '';
		
		$scope.artcleid = ''; 
		if($('#artcleid').val())
		{
			$scope.artcleid = $('#artcleid').val() ; 
		}
		
		$scope.BlogComments = [] ; 
		 reqData = {ArticleID:$scope.artcleid};
			articleService.detail(reqData).then(function(response){
												//console.log(response); return ; 
				$scope.BlogTitle = response.BlogData.BlogTitle;
				$scope.BlogDescription = response.BlogData.BlogDescription;
				$scope.Timestamp = response.BlogData.Timestamp;
				$scope.createdName = response.BlogData.createdName;
                                $scope.createdUserImage = response.BlogData.createdUserImage;
                                $scope.UserWallStatus = response.BlogData.UserWallStatus;
				$scope.BlogID = response.BlogData.BlogID;
				$scope.Technology = response.BlogData.Type;
				
				$scope.name = response.LoginUser.FirstName +' '+ response.LoginUser.LastName ;
				
				$scope.BlogComments = response.Comments ; 
				$scope.totlcomments = response.ttlrows ; 
				//console.log($scope.BlogComments);
				$scope.cond = response.cond ; 
				$scope.follow = response.follow ;
				
				$scope.profilepic = response.profilepic ; 
				
				
										
							// $scope.registeredArticle() ; 
			//$scope.GetwallPost() ; 
		})	
	}
	
	$scope.postBlogComment = function()
	{		
		$scope.artcleid = ''; 
		if($('#artcleid').val())
		{
			$scope.artcleid = $('#artcleid').val() ; 
		}
		
		$scope.comment = '' ; 
		if($('#comment_textarea').val())
		{
			$scope.comment = $('#comment_textarea').val() ; 	
		}
		
		
		reqData = {Blog:{BlogID:$scope.artcleid,IPAddress:'122.0.0.0',Comment:$scope.comment}};
			articleService.comment(reqData).then(function(response){
								var name = response.Blog.Message.name ; 
								var comment = response.Blog.Message.comment ; 
								var date = response.Blog.Message.date ; 
								var id = response.Blog.Message.insertid ; 
								var commentCount = response.Blog.Message.commentCount ; 
								var pp = response.Blog.pp ; console.log(pp);
						$('#comment_textarea').val('');
						$('#comment-btn').html(commentCount + ' Comments') ; 
						$('#ttlcoments').html(commentCount ) ; 
					$('#blog_comment_detail').append('<div id="commentBox'+id+'" class="p-t-15 clearfix ng-scope"> <div class="col-lg-7 no-padding"><div class="review-region col-lg-12 tiles white  p-l-20 p-r-20"> <div class="user-profile-pic-2x pull-left"><img alt="" src="'+pp+'"> </div> <div class="overflow p-l-15"><div class="username overflow"> <div class="font16 semi-bold color-orange ng-binding">'+name+'</div>  <div class="p-t-5 p-b-5 color-grey ng-binding">'+comment+'</div> <div class="color-fa ng-binding">'+date+' <a lang="'+id+'" onclick="setcommentid(this)" class="color-grey" href="javascript:void(0);"><i class="fa fa-trash-o font18 m-l-10"></i></a></div> </div> </div>  <div class="clearfix"></div> </div> </div> </div>');
					
						//$scope.articleDetail();
						 
								//console.log(response);						 
					
		})	
	}
	
	$scope.deleteComment = function()
	{	
		$scope.artcleid = ''; 
		if($('#artcleid').val())
		{
			$scope.artcleid = $('#artcleid').val() ; 
		}
		
		$scope.commntID = '' ; 
		if($('#commntID').val())
		{
			$scope.commntID = $('#commntID').val() ; 	
		}
		
		
		reqData = {Blog:{BlogID:$scope.artcleid,IPAddress:'122.0.0.0',CommentID:$scope.commntID}};
			articleService.delete_comment(reqData).then(function(response){
						$('#comment_textarea').val('');
						if(response.Message!='' ){$('#commentBox'+response.Message).remove();}
						$('#ttlcoments').html(response.Commentcount) ; 
						$('#comment-btn').html(response.Commentcount+ ' Comments') ; 
						
					     //$scope.articleDetail();
						// $('html,body').scrollTop(100000000000000000);
								//console.log(response);						 
					
		})	
	}
	
	
	$scope.deleteArticle = function()
	{		
		$scope.artcleid = ''; 
		if($('#artcleid').val())
		{
			$scope.artcleid = $('#artcleid').val() ; 
		}
		
		reqData = {Blog:{BlogID:$scope.artcleid}};
			articleService.deleteBlog(reqData).then(function(response){
						$('.close').trigger('click');
						 window.location.href = base_url + 'article';
						//$('#comment_textarea').val('');
								//console.log(response);						 
					
		})	
	}
	
	$scope.follow = function()
	{		
		$scope.artcleid = ''; 
		if($('#artcleid').val())
		{
			$scope.artcleid = $('#artcleid').val() ; 
		}
		
		reqData = {Blog:{BlogID:$scope.artcleid}};
			articleService.deleteBlog(reqData).then(function(response){
						$('.close').trigger('click');
						 window.location.href = base_url + 'article';
						//$('#comment_textarea').val('');
								//console.log(response);		
		})	
	}
	
	$scope.create_article = function(status)
	{		
		
		$('#type').val(status);
		var formData = $("#articleform").serializeArray();
		
		 var jsonData = {};
		
		 $.each(formData, function() {
		 if (jsonData[this.name]) {
		 if (!jsonData[this.name].push) {
		 jsonData[this.name] = [jsonData[this.name]];
		 }
		 jsonData[this.name].push(this.value || '');
		 } else {
		 jsonData[this.name] = this.value || '';
		 }
		
		 });
		 
		//console.log(jsonData); return ; 
			articleService.createBlog(jsonData).then(function(response){
						$('.close').trigger('click');
						window.location.href = base_url + 'article';
						//$('#comment_textarea').val('');
								//console.log(response);		
		})	
	}
	
	
	$scope.update_article = function(status)
	{		
		
		$('#type').val(status);
		var formData = $("#articlupdateform").serializeArray();
		
		 var jsonData = {};
		
		 $.each(formData, function() {
		 if (jsonData[this.name]) {
		 if (!jsonData[this.name].push) {
		 jsonData[this.name] = [jsonData[this.name]];
		 }
		 jsonData[this.name].push(this.value || '');
		 } else {
		 jsonData[this.name] = this.value || '';
		 }
		
		 });
		 
		//console.log(jsonData); return ; 
			articleService.updateBlog(jsonData).then(function(response){
							//console.log(response);
						//$('.close').trigger('click');
						window.location.href = base_url + 'article';
						//$('#comment_textarea').val('');
								//console.log(response);		
		})	
	}
	
	
	$scope.shortarticleDetail = function()
	{		
		
		$scope.articleid = $('#artcleid').val() ; 
		 reqData = {BlogID:$scope.articleid} ; 
			articleService.shortArticleDetail(reqData).then(function(response){
						console.log(response.blog);
						$scope.BlogTitle = response.blog.BlogTitle ; 
						$scope.BlogImage = response.blog.BlogImage ; 
						$scope.BlogDescription = response.blog.BlogDescription ; 
						$scope.IsPublic = response.blog.IsPublic ;  
						$scope.BlogID = response.blog.BlogID ; 
						$scope.image = response.blog.image ; 
						$('#add_article_photo').css('display','none');
						 //window.location.href = base_url + 'article';
						//$('#comment_textarea').val('');
								//console.log(response);		
		})	
	}
	
});


function removeThisMedia(ths){
 $(ths).parent().html('');
 $('#add_article_photo').css('display','block');
}

function setAtricleStatus(status)
{
	$('#articleStatus').val(status);	
	angular.element(document.getElementById('ArticlePageCtrl')).scope().registeredArticle();
}

function searchingArticle()
{
	angular.element(document.getElementById('ArticlePageCtrl')).scope().registeredArticle();
}
function likeAction(prop)
{
	$('#articleid').val(prop.lang);
	angular.element(document.getElementById('ArticlePageCtrl')).scope().likepost();
}

function setcommentid(prop)
{
	$('#commntID').val(prop.lang);
	angular.element(document.getElementById('ArticlePageCtrl')).scope().deleteComment();
}

function deltepopup()
{
	angular.element(document.getElementById('ArticlePageCtrl')).scope().deleteArticle();
}
function donotdo(){ $('.close').trigger('click');}
function changeloation(loc)
{
		var articleid = $('#artcleid').val() ; 
		window.location.href = base_url + loc +'/'+articleid;
}