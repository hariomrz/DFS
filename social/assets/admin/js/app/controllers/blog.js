app.controller('blogController', ['$scope', '$attrs', '$timeout', 'blog_service', function($scope, $attrs, $timeout, blog_service) {
// Initialize scope variables
    $scope.totalRecord = 0;
    $scope.filteredTodos = [],
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = 'CreatedDate';
    $scope.reverseSort = false;

    $scope.searchKey = '';
    $scope.blog = [];
    $scope.sources = {};
    $scope.list_type = 'ALL';
    $scope.blog = {};
    $scope.numPerPage = 20,
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();


    $scope.initialize = function()
    {
        $scope.upload_image = new qq.FineUploaderBasic({
        multiple: false,
        autoUpload: true,
        title: "Attach Photos",
        button: $("#blog_photo")[0],
        request: {
            endpoint: base_url + "api/upload_image",
            /*customHeaders: {
                "Accept-Language": accept_language
            },*/
            params: {
                Type: 'blog',
                unique_id: function() {
                    return '';
                },
                Loginsessionkey:$scope.AdminLoginSessionKey,
                DeviceType:'Native'
            }
        },

        validation: {
            allowedExtensions: ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG'],
            sizeLimit: 4194304 // 4mb
        },
        callbacks: {
            onUpload: function(id, fileName) {
                var html = "<li id='dummy_img'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='"+base_url+"assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                $('.attached-media').prepend(html);
            },
            onProgress: function(id, fileName, loaded, total) {
            },
            onComplete: function(id, fileName, responseJSON) {
                if (responseJSON.Message == 'Success') 
                {
                    $('#dummy_img').remove();
                    click_function = 'remove_image("'+responseJSON.Data.MediaGUID+'");';
                    var html = "<li ><a id='"+responseJSON.Data.MediaGUID+"' class='smlremove' onclick='"+click_function+"'></a>";
                        html+= "<figure><img alt='' class='img-full' media_type='IMAGE' is_cover_media='0' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+responseJSON.Data.ImageServerPath +'/220x220/'+responseJSON.Data.ImageName+"'></figure>";
                        html+= "<span class='radio'><input class='set_cover_pic' type='radio' name='coverpic' id='coverpicId1'><label for='coverpicId1'>COVER PIC</label></span></li>";
                    $('.attached-media').prepend(html);
                    var $items = $('.img-full');
                    if($items.length>4)
                    {
                        $("#blog_photo input[name='file']").prop("disabled", true);
                    }
                    $("#blog_video input[name='file']").prop("disabled", true);
                    $("#embed_code").prop("disabled", true);
                } 
                else if(responseJSON.ResponseCode !== 200)
                {
                   
                }
                
            },

            onSubmit: function(id,fileName){
                //fileCount++;
            },

            onValidate: function(b) {
                var validExtensions = ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG']; //array of valid extensions
                var fileName = b.name;
                var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                if ($.inArray(fileNameExt, validExtensions) == -1) {
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                    PermissionError('Allowed file types only jpeg, jpg, gif and png.');
                    return false;
                }
                if (b.size > 4194304) {
                    $scope.ErrorStatus = true;
                    //$scope.Error.error_Schollyme_Thumbnail = required_song_thumb;
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                    PermissionError('Image should be less than 4 MB.');                   
                }

            },

            onError: function(error){
                //alert(error);
            }
        }
    });

    $scope.blog_video = new qq.FineUploaderBasic({
        multiple: true,
        autoUpload: true,
        title: "Blog Videos",
        button: $("#blog_video")[0],
        request: {
            endpoint: base_url + "api/upload_video",
            params: {
                Loginsessionkey:$scope.AdminLoginSessionKey,
                DeviceType:'Native',
                Type:'blog'
            }
        },

        validation: {
            allowedExtensions: ['mp4','MP4'],
            sizeLimit: 31457280 // 4mb
        },
        callbacks: {
            onUpload: function(id, fileName) {},
            onProgress: function(id, fileName, loaded, total) {
                $('.error').html('');
            },
            onComplete: function(id, fileName, responseJSON) {
                $scope.blog.video_guid = responseJSON.Data.MediaGUID;
                
                click_function = 'remove_image("VIDEO");';
                var html = "<li id='"+responseJSON.Data.MediaGUID+"'><a class='smlremove' onclick='"+click_function+"'></a>";
                html+= "<figure><img alt='' class='img-full' media_type='VIDEO' is_cover_media='0' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+base_url+"assets/admin/img/blog_video.jpeg'></figure>";
                html+= "</li>";
                $('.attached-media').html(html);
                
                $("#blog_photo input[name='file']").prop("disabled", true);
                $("#blog_video input[name='file']").prop("disabled", true);
                $("#embed_code").prop("disabled", true);
                
                //$('.videos').append(responseJSON.Data.file_name+'<br>');
            },
            onSubmit: function(id,fileName){},
            onValidate: function(b) {
                var validExtensions = ['mp4','MP4']; //array of valid extensions
                var fileName = b.name;
                var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                if ($.inArray(fileNameExt, validExtensions) == -1) {
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                    PermissionError('Please make sure that file should be MP4 and less than 30 MB');
                    return false;
                }
                if (b.size > 44194304) {
                    $scope.ErrorStatus = true;
                    //$scope.Error.error_Schollyme_Thumbnail = required_song_thumb;
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                    PermissionError('Please make sure that file should be MP4 and less than 30 MB');                   
                }
            },
            onError: function(){}
        }
    });
    }

    $scope.add_youtube_thumb = function()
    {
        input = $scope.Blog.youtube;
        
        if (input.indexOf('http://www.youtube.com') > -1 || input.indexOf('https://www.youtube.com') > -1) 
        {
            // get video id
            var output = input.substr(input.indexOf("=") + 1);

            click_function = 'remove_image("YOUTUBE");';
            var html = "<li id=''><a class='smlremove' onclick='"+click_function+"'></a>";
                html+= "<figure><img alt='' class='img-full' is_cover_media='0' media_type='YOUTUBE' media_guid='' src='http://img.youtube.com/vi/"+output+"/0.jpg'></figure>";
                html+= "</li>";
            $('.attached-media').html(html);

            $("#blog_photo input[name='file']").prop("disabled", true);
            $("#blog_video input[name='file']").prop("disabled", true);
        } 
        else 
        {
           $("html, body").animate({ scrollTop: 0 }, "slow");
           PermissionError('Please insert valid youtube url !');
        }

        
    }

    // Function to delete single blog
    $scope.delete_blog = function()
    {
        var reqData = {
                BlogGUID: $scope.BlogGUID,
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
        blog_service.delete_blog(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg(response.Message);                    
                    closePopDiv('delete_popup', 'bounceOutUp');        
                    $scope.list();
                    
                }
                else 
                {
                    PermissionError(response.Message);
                }
                
                $("html, body").animate({ scrollTop: 0 }, "slow");
                
                hideLoader();
            });
    }

    // function to search blog by keyword
    $scope.search_blog = function()
    {
        $scope.searchKey = $scope.search_blog_model;
        if($scope.searchKey!='' && $scope.searchKey!=undefined)
        {
            $scope.list();
        }
    }

    // function to reset search box
    $scope.blog_reset_search = function()
    {
        $scope.searchKey = '';
        $scope.list();
    }

    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage',function() 
    {
        if($scope.list_view==1)
        {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
            reqData = {
                Begin: begins,
                End: $scope.numPerPage,
                StartDate: $scope.startDate,
                EndDate: $scope.endDate,
                SearchKey: $scope.searchKey,
                SortBy: $scope.sort_by,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.list();
        }
    });

    

    //Apply Sort by and mamke request data
    $scope.sortBY = function (column_id) {
        if($("table.users-table #noresult_td").length == 0)
        {
            $(".shortdiv").children('.icon-arrowshort').addClass('hide');
            $(".shortdiv").parents('.ui-sort').removeClass('selected');
            if($scope.reverseSort == true){
                $("#"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
            }else{
                $("#"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedUp').addClass('sortedDown').children('.icon-arrowshort').removeClass('hide');                
            }
            
            reqData = {
                Begin: $scope.currentPage,
                End: $scope.numPerPage,
                StartDate: $scope.startDate,
                EndDate: $scope.endDate,
                SearchKey: $scope.searchKey,
                UserStatus: $scope.userStatus,
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.list();
        }
    };

    /**
    * Set li selected
    * @param {type} university
    * @returns {undefined}
    */
    $scope.selectCategory = function (Blog) {
        if (Blog.BlogGUID in $scope.selectedBlog) 
        {
            delete $scope.selectedBlog[Blog.BlogGUID];
        } 
        else 
        {
            $scope.selectedBlog[Blog.BlogGUID] = Blog;            
        }
        
        if(Object.keys($scope.selectedBlog).length > 0) 
        {
            setTimeout(function(){ $scope.globalChecked == true; }, 1);
            $('#ItemCounter').fadeIn();            
        } 
        else 
        {
            $scope.showButtonGroup = false;
            $('#ItemCounter').fadeOut();
        }
        
        setTimeout(function(){
            if($(".blog tr.selected").length == $scope.listData.length){
                setTimeout(function(){ $scope.globalChecked = true; }, 1);
                $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
            }else{
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
            }
        }, 1);
        
        var ItemCount = Object.keys($scope.selectedBlog).length;
        var txtCount = ItemsSelected;
        if(ItemCount == 1)
            txtCount = ItemSelected;
        $('#ItemCounter .counter').html(ItemCount+txtCount);
        $('#add_university').slideUp();                  
        //console.log($scope.selectedUniversities);
    }
    
    /**
     * SHow selected css
     * @param {type} University
     * @returns {undefined}
     */
    $scope.isSelected = function (Blog) {
        if (Blog.BlogGUID in $scope.selectedBlog) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;            
        }        
    };

    // functio to check all the rows 
    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;        
        if ($scope.globalChecked) {
            $scope.selectedBlog = {};
            var listData = $scope.listData;
            angular.forEach(listData, function (val, key) {
                if (typeof $scope.selectedBlog[key]) {                    
                    $scope.selectCategory(val, key);
                }
            });
        } else {
            angular.forEach($scope.selectedBlog, function (val, key) {
                $scope.selectCategory(val, key);
            });
        }    
                
    };

    // Function to fetch university list
    $scope.list = function () {
        
        intilizeTooltip();
        showLoader();
        
        $scope.startDate    = $('#SpnFrom').val();
        $scope.endDate      = $('#SpnTo').val();
        $scope.selectedBlog = {};
        var begins = '';
        
        if ($scope.currentPage == 1) 
        {
            //Make request data parameter for university listing
            begins = 0;//$scope.currentPage;
        } 
        else 
        {
            begins = $scope.currentPage;
        }

        var reqData = {
            PageNo: begins, //$scope.currentPage,
            PageSize: $scope.numPerPage,
            OrderBy: $scope.reverseSort,
            SortBy: $scope.orderByField,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            LoginSessionKey :$scope.AdminLoginSessionKey,
            SearchKeyword:$scope.searchKey,
            ListType:$scope.list_type
        }


        var reqUrl = reqData[1]
        //Call getUniversitylist in services.js file
        blog_service.list(reqData).then(function (response) 
        {
            $scope.listData = [];
            if(response.ResponseCode == 200)
            {
                $scope.noOfObj = response.TotalRecords;
                $scope.total_songs = $scope.totalRecord = $scope.noOfObj;
                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) 
                {
                    $('.download_link,#selectallbox').hide();
                    $('#blogController table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>'+no_record+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData = response.Data;
            }
            else if(response.ResponseCode == 517)
            {
                redirectToBlockedIP();
            }
            else if(response.ResponseCode == 598)
            {
                $('.download_link,#selectallbox').hide();
                $('#UniversityCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>'+response.Message+'</p></div></td></tr>');
                $('.simple-pagination').hide();
            }
            hideLoader();            
            
        }), function (error) {
            hideLoader();
        }
    };
    //Get no. of pages for data
    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };

    $scope.RequestData = {};
    
    // Function to save song of the day
    $scope.set_blog_data = function(Blog)
    {
        $scope.BlogGUID     = Blog.BlogGUID;
    }

    // function to show selected university in edit mode
    $scope.edit_blog_show = function() {
        window.location.href = base_url+"admin/blog/update/"+$scope.BlogGUID;
    };
    
    $scope.details = function(blog_guid)
    {
        var reqData = {
            BlogGUID: blog_guid,
            LoginSessionKey :$scope.AdminLoginSessionKey
        };
        showLoader();
        $scope.Blog = {};
        blog_service.details(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                blog_data = response.Data[0];   
                $scope.Blog.Title       = blog_data.Title;
                $scope.Blog.Description = blog_data.Description;
                $scope.Blog.Media       = blog_data.Media;
                
                if($scope.Blog.Media.length>0)
                {
                    angular.forEach($scope.Blog.Media, function(value, key) 
                    {   
                        if(value.MediaType=='Image')
                        {
                            click_function = 'remove_image("'+value.MediaGUID+'");';
                            var html = "<li><a id='"+value.MediaGUID+"' class='smlremove' onclick='"+click_function+"'></a>";
                                html+= "<figure><img alt='' class='img-full' media_type='IMAGE' is_cover_media='0' media_guid='"+value.MediaGUID+"' src='"+ImageServerPath+'upload/blog/220x220/'+value.ImageName+"'></figure>";
                                html+= "<span class='radio'><input class='set_cover_pic' type='radio' name='coverpic' id='coverpicId1'><label for='coverpicId1'>COVER PIC</label></span></li>";
                            $('.attached-media').append(html); 

                            $("#blog_video input[name='file']").prop("disabled", true);
                            $("#embed_code").prop("disabled", true);
                        }
                        else if(value.MediaType=='Video')
                        {
                            if(value.ConversionStatus=='Finished')
                            {
                                src = ImageServerPath+'upload/blog/video/'+value.ImageName;
                            }
                            else
                            {
                                src = base_url+"assets/admin/img/blog_video.jpeg";   
                            }
                            
                            click_function = 'remove_image("VIDEO");';
                            var html = "<li><a id='"+value.MediaGUID+"' class='smlremove' onclick='"+click_function+"'></a>";
                                html+= "<figure><img alt='' class='img-full' media_type='VIDEO' is_cover_media='0' media_guid='"+value.MediaGUID+"' src='"+src+"'></figure>";
                                html+= "</li>";
                            $('.attached-media').html(html);   
                            
                            $("#blog_photo input[name='file']").prop("disabled", true);
                            $("#embed_code").prop("disabled", true);
                        }
                        else
                        {
                            var output = value.ImageName.substr(value.ImageName.indexOf("=") + 1);
                            $scope.Blog.youtube = value.ImageName;
                            click_function = 'remove_image("YOUTUBE");';
                            var html = "<li id=''><a class='smlremove' onclick='"+click_function+"'></a>";
                                html+= "<figure><img alt='' class='img-full' is_cover_media='0' media_type='YOUTUBE' media_guid='' src='http://img.youtube.com/vi/"+output+"/0.jpg'></figure>";
                                html+= "</li>";
                            $('.attached-media').html(html);  
                            
                            $("#blog_photo input[name='file']").prop("disabled", true);
                            $("#blog_video input[name='file']").prop("disabled", true);
                        }
                    });
                }
                if(!angular.element.isEmptyObject(blog_data.CoverMedia))
                {
                    click_function = 'remove_image("'+blog_data.CoverMedia.MediaGUID+'");';
                    var html = "<li><a id='"+blog_data.CoverMedia.MediaGUID+"' class='smlremove' onclick='"+click_function+"'></a>";
                        html+= "<figure><img alt='' class='img-full' media_type='IMAGE' is_cover_media='0' media_guid='"+blog_data.CoverMedia.MediaGUID+"' src='"+ImageServerPath+'upload/blog/220x220/'+blog_data.CoverMedia.ImageName+"'></figure>";
                        html+= "<span class='radio'><input checked class='set_cover_pic' type='radio' name='coverpic' id='coverpicId1'><label for='coverpicId1'>COVER PIC</label></span></li>";
                    $('.attached-media').prepend(html); 

                    $("#blog_video input[name='file']").prop("disabled", true);
                    $("#embed_code").prop("disabled", true);
                }
            }
            $("html, body").animate({ scrollTop: 0 }, "slow");
            
            hideLoader();
        });
    }

    $scope.change_source = function()
    {
        $scope.Error = {};
        if(angular.element('#Source').find("option:selected").text()=="SchollyMe"){
            angular.element(".box").not(".Schollyme").hide();
            angular.element(".Schollyme").show();
        }
        else if(angular.element('#Source').find("option:selected").text()=="Spotify"){
            angular.element(".box").not(".Spotify").hide();
            angular.element(".Spotify").show();
        }
        else if(angular.element('#Source').find("option:selected").text()=="iTunes"){
            angular.element(".box").not(".itunes").hide();
            angular.element(".itunes").show();
        }
        else{
            angular.element(".box").hide();
        }
    }



    // function to save blog
    $scope.save_blog    = function (type) {
        $scope.ErrorStatus = false;
        $scope.Error = {};

        $scope.Error.error_blog_title = "";
        $scope.Error.error_blog_description = "";
        
        if($scope.Blog!=undefined)
        {
            console.log($scope.Blog);
            var blog_title          = $scope.Blog.Title;
            var blog_description    = $scope.Blog.Description;
            if ($scope.Blog.Title==undefined) 
            {
                $scope.ErrorStatus = true;
                $scope.Error.error_blog_title = required_blog_title;
            }
            if ($scope.Blog.Description==undefined) 
            {
                $scope.ErrorStatus = true;
                $scope.Error.error_blog_description = required_blog_description;
            }    
        }
        else if($scope.Blog==undefined)
        {
            $scope.ErrorStatus = true;
            $scope.Error.error_blog_title = required_blog_title;
            
            $scope.ErrorStatus = true;
            $scope.Error.error_blog_description = required_blog_description;
        }
        
        if($scope.Blog.youtube!=undefined)
        {
            youtube = $scope.Blog.youtube;
        }
        else
        {
            youtube = '';
        }
        
        if(youtube!='')
        {
            if ((youtube.indexOf('http://www.youtube.com') > -1 || youtube.indexOf('https://www.youtube.com') > -1)) 
            {
            }
            else
            {
               $("html, body").animate({ scrollTop: 0 }, "slow");
               PermissionError('Please insert valid youtube url !');
               $scope.ErrorStatus = true;
            }    
        }

        if(!$scope.ErrorStatus) 
        {
            showLoader();
            //send message
            $scope.RequestData.AdminLoginSessionKey = $scope.AdminLoginSessionKey;
            $scope.RequestData.Title = $scope.Blog.Title;
            $scope.RequestData.Description = $scope.Blog.Description;
            $scope.RequestData.Status = type;

            var media = [];

            $('.img-full').each(function(){
                if($(this).attr('media_type')=='YOUTUBE')
                {
                    media.push({Url:$(this).attr('src'),IsCoverMedia:0,'MediaType':'YOUTUBE'});    
                }
                else
                {
                    media.push({MediaGUID:$(this).attr('media_guid'),IsCoverMedia:$(this).attr('is_cover_media'),'MediaType':'IMAGE'});
                }
            });
            $scope.RequestData.Media = media;

            blog_service.create($scope.RequestData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    //closePopDiv('Setsong_popup', 'bounceOutUp');
                    $scope.Blog = {};
                    $scope.blog.video_guid=  "";
                    $('.attached-media').html('');
                    ShowSuccessMsg(response.Message);
                    setTimeout(function(){window.location.href = base_url+"admin/blog",2000});                    
                }
                else 
                {
                    PermissionError(response.Message);
                }
                $("html, body").animate({ scrollTop: 0 }, "slow");
                hideLoader();
            });
        }
        else
        {
            
        }
    };

    // function to update blog
    $scope.update_blog    = function (type,blog_guid) {
        //send message
        $scope.RequestData.AdminLoginSessionKey = $scope.AdminLoginSessionKey;
        $scope.RequestData.Title = $scope.Blog.Title;
        $scope.RequestData.Description = $scope.Blog.Description;
        $scope.RequestData.BlogGUID = blog_guid;
        $scope.RequestData.Status = type;
        var media = [];

        $scope.ErrorStatus = false;
        $scope.Error = {};

        $scope.Error.error_blog_title = "";
        $scope.Error.error_blog_description = "";
        
        if($scope.Blog!=undefined)
        {
            console.log($scope.Blog);
            var blog_title          = $scope.Blog.Title;
            var blog_description    = $scope.Blog.Description;
            if ($scope.Blog.Title==undefined) 
            {
                $scope.ErrorStatus = true;
                $scope.Error.error_blog_title = required_blog_title;
            }
            if ($scope.Blog.Description==undefined) 
            {
                $scope.ErrorStatus = true;
                $scope.Error.error_blog_description = required_blog_description;
            }    
        }
        else if($scope.Blog==undefined)
        {
            $scope.ErrorStatus = true;
            $scope.Error.error_blog_title = required_blog_title;
            
            $scope.ErrorStatus = true;
            $scope.Error.error_blog_description = required_blog_description;
        }
        
        if($scope.Blog.youtube!=undefined)
        {
            youtube = $scope.Blog.youtube;
        }
        else
        {
            youtube = '';
        }
        
        
        if(youtube!='')
        {
            if ((youtube.indexOf('http://www.youtube.com') > -1 || youtube.indexOf('https://www.youtube.com') > -1)) 
            {
            }
            else
            {
               $("html, body").animate({ scrollTop: 0 }, "slow");
               PermissionError('Please insert valid youtube url !');
               $scope.ErrorStatus = true;
            }    
        }
        

        if(!$scope.ErrorStatus) 
        {
            showLoader();
            $('.img-full').each(function(){
                if($(this).attr('media_type')=='YOUTUBE')
                {
                    media.push({Url:$scope.Blog.youtube,IsCoverMedia:0,'MediaType':'YOUTUBE'});    
                }
                else
                {
                    media.push({MediaGUID:$(this).attr('media_guid'),IsCoverMedia:$(this).attr('is_cover_media'),'MediaType':'IMAGE'});
                }
            });
            $scope.RequestData.Media = media;

            blog_service.update($scope.RequestData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    //closePopDiv('Setsong_popup', 'bounceOutUp');
                    $scope.Blog = {};
                    $scope.blog.video_guid=  "";
                    $('.attached-media').html('');
                    ShowSuccessMsg(response.Message);
                    setTimeout(function(){window.location.href = base_url+"admin/blog",2000});                    
                }
                else 
                {
                    PermissionError(response.Message);
                }
                $("html, body").animate({ scrollTop: 0 }, "slow");
                hideLoader();
            });
        }
        else
        {
            
        }
    };

}]);

/*Summernote Controller*/
app.controller('SummernoteController', ['$scope', '$attrs', '$timeout', function($scope, $attrs, $timeout) {
'use strict';

var currentElement,
	summernoteConfig = $scope.summernoteConfig || {
		toolbar: [
		  ['style', ['bold', 'italic', 'underline']]
		  ]
	};

if (angular.isDefined($attrs.height)) { summernoteConfig.height = $attrs.height; }
if (angular.isDefined($attrs.focus)) { summernoteConfig.focus = true; }
if (angular.isDefined($attrs.airmode)) { summernoteConfig.airMode = false; }
if (angular.isDefined($attrs.lang)) {
  if (!angular.isDefined($.summernote.lang[$attrs.lang])) {
	throw new Error('"' + $attrs.lang + '" lang file must be exist.');
  }
  summernoteConfig.lang = $attrs.lang;
}

summernoteConfig.onInit = $scope.init;
summernoteConfig.onEnter = function(evt) { $scope.enter({evt:evt}); };
summernoteConfig.onFocus = function(evt) { $scope.focus({evt:evt}); };
summernoteConfig.onPaste = function(evt) { $scope.paste({evt:evt}); };
summernoteConfig.onKeyup = function(evt) { $scope.keyup({evt:evt}); };
summernoteConfig.onKeydown = function(evt) { $scope.keydown({evt:evt}); };
if (angular.isDefined($attrs.onImageUpload)) {
  summernoteConfig.onImageUpload = function(files) {
	$scope.imageUpload({files:files, editable: $scope.editable});
  };
}

this.activate = function(scope, element, ngModel) {
  var updateNgModel = function() {
	var newValue = element.code();
	if (ngModel && ngModel.$viewValue !== newValue) {
	  $timeout(function() {
		ngModel.$setViewValue(newValue);
	  }, 0);
	}
  };

  summernoteConfig.onChange = function(contents) {
	updateNgModel();
	$scope.change({contents:contents, editable: $scope.editable});
  };
  summernoteConfig.onBlur = function(evt) {
	(!summernoteConfig.airMode) && element.blur();
	$scope.blur({evt:evt});
  };
  if (angular.isDefined($attrs.onToolbarClick)) {
	element.on('summernote.toolbar.click', function (evt) {
	  $scope.toolbarClick({evt: evt});
	});
  }

  element.summernote(summernoteConfig);

  var editor$ = element.next('.note-editor'),
	  unwatchNgModel;
  editor$.find('.note-toolbar').click(function() {
	updateNgModel();

	// sync ngModel in codeview mode
	if (editor$.hasClass('codeview')) {
	  editor$.on('keyup', updateNgModel);
	  if (ngModel) {
		unwatchNgModel = scope.$watch(function () {
		  return ngModel.$modelValue;
		}, function(newValue) {
		  editor$.find('.note-codable').val(newValue);
		});
	  }
	} else {
	  editor$.off('keyup', updateNgModel);
	  if (angular.isFunction(unwatchNgModel)) {
		unwatchNgModel();
	  }
	}
  });

  if (ngModel) {
	ngModel.$render = function() {
	  element.code(ngModel.$viewValue || '');
	};
  }

  // set editable to avoid error:isecdom since Angular v1.3
  if (angular.isDefined($attrs.editable)) {
	$scope.editable = editor$.find('.note-editable');
  }
  if (angular.isDefined($attrs.editor)) {
	$scope.editor = element;
  }

  currentElement = element;
  // use jquery Event binding instead $on('$destroy') to preserve options data of DOM
  element.on('$destroy', function() {
	element.destroy();
	$scope.summernoteDestroyed = true;
  });
};

$scope.$on('$destroy', function () {
  // when destroying scope directly
  if (!$scope.summernoteDestroyed) {
	currentElement.destroy();
  }
});
}])

// .directive('summernote', [function() {
// 'use strict';

// return {
//   restrict: 'EA',
//   transclude: true,
//   replace: true,
//   require: ['summernote', '^?ngModel'],
//   controller: 'SummernoteController',
//   scope: {
// 	summernoteConfig: '=config',
// 	editable: '=',
// 	editor: '=',
// 	init: '&onInit',
// 	enter: '&onEnter',
// 	focus: '&onFocus',
// 	blur: '&onBlur',
// 	paste: '&onPaste',
// 	keyup: '&onKeyup',
// 	keydown: '&onKeydown',
// 	change: '&onChange',
// 	toolbarClick: '&onToolbarClick',
// 	imageUpload: '&onImageUpload'
//   },
//   template: '<div class="summernote"></div>',
//   link: function(scope, element, attrs, ctrls) {
// 	var summernoteController = ctrls[0],
// 		ngModel = ctrls[1];

// 	summernoteController.activate(scope, element, ngModel);
//   }
// };

// }]);

/*Summernote end*/
/*--------Function to remove uploaded image----------*/
function remove_image (element)
{
    
    if(element=='VIDEO' || element=='YOUTUBE')
    {
        $('.attached-media').html('');
        if(element=='YOUTUBE')
        {
            $('#embed_code').val('');
        }
    }
    else
    {

        $('#'+element).parent().remove();
        var $items = $('.img-full');
        if($items.length<5)
        {
            $("#blog_photo input[name='file']").prop("disabled", false);
        }
    }
    var $items = $('.img-full');
    if($items.length<1)
    {
        $("#blog_photo input[name='file']").prop("disabled", false);
        $("#blog_video input[name='file']").prop("disabled", false);
        $("#embed_code").prop("disabled", false);
    }
}

$(document).ready(function(){
    $(document).delegate('.set_cover_pic','click',function(){
        $(this).parent().parent().find('.img-full').attr('is_cover_media',1);
    });
})