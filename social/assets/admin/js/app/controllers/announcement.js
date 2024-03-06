app.controller('announcementController', ['$scope', '$attrs', '$timeout', 'apiService', '$q', function ($scope, $attrs, $timeout, apiService, $q) {
// Initialize scope variables
        $scope.totalRecord = 0;
        $scope.filteredTodos = [];
        $scope.currentPage = 1;
        $scope.numPerPage = pagination;
        $scope.maxSize = pagination_links;
        $scope.orderByField = 'BlogID';    
        $scope.reverseSort = 'DESC';
        $scope.SelectedData = '';
        $scope.searchKey = '';
        $scope.announcement = {Description: '', Type: 2, Url: '', Title: '', ActivityGUID: '', UserGUID: '', Tag:[], CustomURL:'', Quiz:[]};
        $scope.UrlOptions = 0;
        $scope.sources = {};
        $scope.list_type = 'ALL';
        $scope.numPerPage = 20,
        $scope.CanIgnore = 1,
        $scope.imageServerPath = image_server_path;
                /* Send AdminLoginSessionKey in every request */
                $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        $scope.TypeOptions = [{Name:'Image', MKey:2}]; //{Name:'Text', MKey:1},
        $scope.Urls = [{Name:'Select URL', MKey:''},{Name:'Daily Digest', MKey:'DAILY_DIGEST'},{Name:'Discover', MKey:'DISCOVER'},{Name:'Directory', MKey:'DIRECTORY'},{Name:'Update APP', MKey:'UPDATE_APP'},{Name:'Post', MKey:'POST'},{Name:'Post Tag', MKey:'POST_TAG'},{Name:'Classified Category', MKey:'CLASSIFIED_CATEGORY'},{Name:'Custom Url', MKey:'CUSTOM_URL'},{Name:'Feedback', MKey:'FEEDBACK'},{Name:'Quiz', MKey:'QUIZ'}]; //{Name:'Question Category', MKey:'QUESTION_CATEGORY'}
        $scope.Urls_arr = {'DAILY_DIGEST':'Daily Digest', 'DISCOVER':'Discover', 'DIRECTORY':'Directory', 'UPDATE_APP':'Update APP', 'POST':'Post', 'POST_TAG':'Post Tag', 'CLASSIFIED_CATEGORY':'Classified Category', 'CUSTOM_URL':'Custom URL', 'FEEDBACK':'Feedback', 'QUIZ':'Quiz'}; //, 'QUESTION_CATEGORY':'Question Category'
        
        $scope.show_url_option = function() {
            $scope.UrlOptions = 0;
            $scope.announcement.Tag = [];
            $scope.announcement.Quiz = [];
            $scope.Error = {};
            if($scope.announcement.Url == 'POST') {
                $scope.UrlOptions = 1;
            }
            if($scope.announcement.Url == 'POST_TAG') {
                $scope.UrlOptions = 2;
            }
            if($scope.announcement.Url == 'QUESTION_CATEGORY') {
                $scope.UrlOptions = 3;
            }
            if($scope.announcement.Url == 'CLASSIFIED_CATEGORY') {
                $scope.UrlOptions = 4;
            }
            if($scope.announcement.Url == 'CUSTOM_URL') {
                $scope.UrlOptions = 5;
            }
            if($scope.announcement.Url == 'FEEDBACK') {
                $scope.UrlOptions = 6;
            }
            if($scope.announcement.Url == 'QUIZ') {
                $scope.UrlOptions = 7;
            }
        }

        $scope.getQuiz = function ($query) {
            var url = base_url + 'admin_api/quiz/suggestion';
            $query = $query.trim();
            url += '?SearchKeyword=' + $query;

            
            return apiService.CallGetApi(url, function (resp) {
                var postTagList = resp.data.Data;
                
                return postTagList.filter(function (tlist) {
                    return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.getActivityTags = function ($query, TagType) {
            var url = base_url + 'api/tag/get_entity_tags';
            $query = $query.trim();
            url += '?SearchKeyword=' + $query;



            url += '&TagType=' + TagType;
            url += '&EntityType=ACTIVITY';
            
            return apiService.CallGetApi(url, function (resp) {
                var postTagList = resp.data.Data;
                angular.forEach(postTagList, function (val, key) {
                    postTagList[key].AddedBy = 1;
                });
                return postTagList.filter(function (tlist) {
                    return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.loadTagCategories = function ($query, TagID) {            
            var url = base_url + 'api/tag/tag_categories_suggestion';
            $query = $query.trim();
            url += '?SearchKeyword=' + $query;               

            url += '&TagID=' + TagID;

            return apiService.CallGetApi(url, function (resp) {
                var tagCategoryList = resp.data.Data;
                angular.forEach(tagCategoryList, function (val, key) {
                    tagCategoryList[key].AddedBy = 1;
                });
                return tagCategoryList.filter(function (tlist) {
                    return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });         
            
        };
        
        $scope.addTagAdded = function (UrlTag) {
            //$scope.showError = false;
            //console.log("UrlTag.length", $scope.announcement.Tag.length);
            if ($scope.announcement.Tag.length > 1) {
                $scope.showError = true;
                $scope.Error.error_tag = 'Please select only one value.';
            } else {
                $scope.showError = false;
                $scope.Error.error_tag = '';
            }
        };

        
        $scope.addQuizAdded = function (UrlTag) {
            //$scope.showError = false;
           // console.log("UrlTag.length", $scope.announcement.Quiz.length);
            if ($scope.announcement.Quiz.length > 1) {
                $scope.showError = true;
                $scope.Error.error_quiz = 'Please select only one value.';
            } else {
                $scope.showError = false;
                $scope.Error.error_quiz = '';
            }
        };

        $scope.initialize = function ()
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
                        unique_id: function () {
                            return '';
                        },
                        LoginSessionKey: $scope.AdminLoginSessionKey,
                        DeviceType: 'Native'
                    }
                },
                validation: {
                    allowedExtensions: ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG'],
                    sizeLimit: 4194304 // 4mb
                },
                callbacks: {
                    onUpload: function (id, fileName) {
                        var html = "<li id='dummy_img'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='" + base_url + "assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                        $('.attached-media').prepend(html);
                    },
                    onProgress: function (id, fileName, loaded, total) {
                    },
                    onComplete: function (id, fileName, responseJSON) {
                        if (responseJSON.Message == 'Success')
                        {
                            $('#dummy_img').remove();
                            click_function = 'remove_image("' + responseJSON.Data.MediaGUID + '");';
                            var html = "<li ><a id='" + responseJSON.Data.MediaGUID + "' class='smlremove' onclick='" + click_function + "'></a>";
                            html += "<figure><img alt='' class='img-full' media_type='IMAGE' is_cover_media='0' media_guid='" + responseJSON.Data.MediaGUID + "' src='" + responseJSON.Data.ImageServerPath + '/196x196/' + responseJSON.Data.ImageName + "'></figure>";
                            html += "<span class='radio'><input class='set_cover_pic' type='radio' name='coverpic' id='coverpicId1'><label for='coverpicId1'>COVER PIC</label></span></li>";
                            $('.attached-media').prepend(html);
                            var $items = $('.img-full');
                            if ($items.length > 4)
                            {
                                $("#blog_photo input[name='file']").prop("disabled", true);
                            }
                            $("#blog_video input[name='file']").prop("disabled", true);
                            $("#embed_code").prop("disabled", true);
                        } else if (responseJSON.ResponseCode !== 200)
                        {

                        }

                    },
                    onSubmit: function (id, fileName) {
                        //fileCount++;
                    },
                    onValidate: function (b) {
                        var validExtensions = ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG']; //array of valid extensions
                        var fileName = b.name;
                        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                        if ($.inArray(fileNameExt, validExtensions) == -1) {
                            $("html, body").animate({scrollTop: 0}, "slow");
                            PermissionError('Allowed file types only jpeg, jpg, gif and png.');
                            return false;
                        }
                        if (b.size > 4194304) {
                            $scope.ErrorStatus = true;
                            //$scope.Error.error_Schollyme_Thumbnail = required_song_thumb;
                            $("html, body").animate({scrollTop: 0}, "slow");
                            PermissionError('Image should be less than 4 MB.');
                        }

                    },
                    onError: function (error) {
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
                        LoginSessionKey: $scope.AdminLoginSessionKey,
                        DeviceType: 'Native',
                        Type: 'blog'
                    }
                },
                validation: {
                    allowedExtensions: ['mp4', 'MP4'],
                    sizeLimit: 31457280 // 4mb
                },
                callbacks: {
                    onUpload: function (id, fileName) {},
                    onProgress: function (id, fileName, loaded, total) {
                        $('.error').html('');
                    },
                    onComplete: function (id, fileName, responseJSON) {
                        $scope.blog.video_guid = responseJSON.Data.MediaGUID;

                        click_function = 'remove_image("VIDEO");';
                        var html = "<li id='" + responseJSON.Data.MediaGUID + "'><a class='smlremove' onclick='" + click_function + "'></a>";
                        html += "<figure><img alt='' class='img-full' media_type='VIDEO' is_cover_media='0' media_guid='" + responseJSON.Data.MediaGUID + "' src='" + base_url + "assets/admin/img/blog_video.jpeg'></figure>";
                        html += "</li>";
                        $('.attached-media').html(html);

                        $("#blog_photo input[name='file']").prop("disabled", true);
                        $("#blog_video input[name='file']").prop("disabled", true);
                        $("#embed_code").prop("disabled", true);

                        //$('.videos').append(responseJSON.Data.file_name+'<br>');
                    },
                    onSubmit: function (id, fileName) {},
                    onValidate: function (b) {
                        var validExtensions = ['mp4', 'MP4']; //array of valid extensions
                        var fileName = b.name;
                        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                        if ($.inArray(fileNameExt, validExtensions) == -1) {
                            $("html, body").animate({scrollTop: 0}, "slow");
                            PermissionError('Please make sure that file should be MP4 and less than 30 MB');
                            return false;
                        }
                        if (b.size > 44194304) {
                            $scope.ErrorStatus = true;
                            //$scope.Error.error_Schollyme_Thumbnail = required_song_thumb;
                            $("html, body").animate({scrollTop: 0}, "slow");
                            PermissionError('Please make sure that file should be MP4 and less than 30 MB');
                        }
                    },
                    onError: function () {}
                }
            });
        }

        $scope.add_youtube_thumb = function ()
        {
            input = $scope.Blog.youtube;

            if (input.indexOf('http://www.youtube.com') > -1 || input.indexOf('https://www.youtube.com') > -1)
            {
                // get video id
                var output = input.substr(input.indexOf("=") + 1);

                click_function = 'remove_image("YOUTUBE");';
                var html = "<li id=''><a class='smlremove' onclick='" + click_function + "'></a>";
                html += "<figure><img alt='' class='img-full' is_cover_media='0' media_type='YOUTUBE' media_guid='' src='http://img.youtube.com/vi/" + output + "/0.jpg'></figure>";
                html += "</li>";
                $('.attached-media').html(html);

                $("#blog_photo input[name='file']").prop("disabled", true);
                $("#blog_video input[name='file']").prop("disabled", true);
            } else
            {
                $("html, body").animate({scrollTop: 0}, "slow");
                PermissionError('Please insert valid youtube url !');
            }


        }

        // Function to delete single blog
        $scope.delete_blog = function ()
        {
            var reqData = {
                BlogGUID: $scope.announcement.BlogGUID,
            };
            apiService.call_api(reqData, 'admin_api/announcement/delete').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg(response.Message);
                    closePopDiv('delete_popup', 'bounceOutUp');
                    $scope.list();

                } else
                {
                    PermissionError(response.Message);
                }

                $("html, body").animate({scrollTop: 0}, "slow");

                hideLoader();
            });
        }

        // function to search blog by keyword
        $scope.search_blog = function ()
        {
            $scope.searchKey = $scope.search_blog_model;
            if ($scope.searchKey != '' && $scope.searchKey != undefined)
            {
                $scope.list();
            }
        }

        // function to reset search box
        $scope.blog_reset_search = function ()
        {
            $scope.searchKey = '';
            $scope.search_blog_model = '';
            $scope.list();
        }

        //Call function for get pagination data with new request data
        $scope.$watch('currentPage + numPerPage', function ()
        {
            if ($scope.list_view == 1)
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
                    AdminLoginSessionKey: $scope.AdminLoginSessionKey
                }
                $scope.list();
            }
        });



        //Apply Sort by and mamke request data
        $scope.sortBY = function (column_id) {
            if ($("table.users-table #noresult_td").length == 0)
            {
                $(".shortdiv").children('.icon-arrowshort').addClass('hide');
                $(".shortdiv").parents('.ui-sort').removeClass('selected');
                if ($scope.reverseSort == true) {
                    $("#" + column_id).addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
                } else {
                    $("#" + column_id).addClass('selected').children('.shortdiv').removeClass('sortedUp').addClass('sortedDown').children('.icon-arrowshort').removeClass('hide');
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
                    AdminLoginSessionKey: $scope.AdminLoginSessionKey
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
            } else
            {
                $scope.selectedBlog[Blog.BlogGUID] = Blog;
            }

            if (Object.keys($scope.selectedBlog).length > 0)
            {
                setTimeout(function () {
                    $scope.globalChecked == true;
                }, 1);
                $('#ItemCounter').fadeIn();
            } else
            {
                $scope.showButtonGroup = false;
                $('#ItemCounter').fadeOut();
            }

            setTimeout(function () {
                if ($(".blog tr.selected").length == $scope.listData.length) {
                    setTimeout(function () {
                        $scope.globalChecked = true;
                    }, 1);
                    $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
                } else {
                    $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                }
            }, 1);

            var ItemCount = Object.keys($scope.selectedBlog).length;
            var txtCount = ItemsSelected;
            if (ItemCount == 1)
                txtCount = ItemSelected;
            $('#ItemCounter .counter').html(ItemCount + txtCount);
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

            $scope.startDate = $('#SpnFrom').val();
            $scope.endDate = $('#SpnTo').val();
            $scope.selectedBlog = {};
            var begins = '';

            if ($scope.currentPage == 1)
            {
                //Make request data parameter for university listing
                begins = 0;//$scope.currentPage;
            } else
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
                LoginSessionKey: $scope.AdminLoginSessionKey,
                SearchKeyword: $scope.searchKey,
                ListType: $scope.list_type
            }


            var reqUrl = reqData[1]
            //Call getUniversitylist in services.js file
            apiService.call_api(reqData, 'admin_api/announcement/list').then(function (response)
            {
                $scope.listData = [];
                if (response.ResponseCode == 200)
                {
                    $scope.noOfObj = response.TotalRecords;
                    $scope.total_songs = $scope.totalRecord = $scope.noOfObj;
                    //If no of records equal 0 then hide
                    if ($scope.noOfObj == 0)
                    {
                        $('.download_link,#selectallbox').hide();
                        //$('#announcementController table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>' + no_record + '</p></div></td></tr>');
                        $('.simple-pagination').hide();
                    }

                    //Push data into Controller in view file
                    $scope.listData = response.Data;
                } else if (response.ResponseCode == 517)
                {
                    redirectToBlockedIP();
                } else if (response.ResponseCode == 598)
                {
                    $('.download_link,#selectallbox').hide();
                   // $('#UniversityCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>' + response.Message + '</p></div></td></tr>');
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
        $scope.set_data = function (Data)
        {
            $scope.announcement = Data;
        }
        $scope.add_new_message = function ()
        {
            $scope.UrlOptions = 0;
            //$scope.remove_announcement_image();
            $scope.announcement={Description:'', EntityType:4, BlogGUID:'', Status:'', Type: 2, Url:'', Title: '', ActivityGUID: '', UserGUID: '', Tag:[], CustomURL:''};
            $scope.Error = {};
            $scope.Error.error_title = '';
            $scope.Error.error_description = '';
            $scope.Error.error_image = '';
            $scope.Error.error_activity = '';
            $scope.Error.error_custom_url = '';
            //openPopDiv('addNewMessage');
            $('#addNewMessage').modal();
        }

        $scope.reset_form = function ()
        {
            if($scope.announcement.Type == 2) {
                $scope.announcement.Title = '';
                $scope.announcement.Description = '';

            }
            $scope.Error = {};
            $scope.Error.error_title = '';
            $scope.Error.error_description = '';
            $scope.Error.error_activity = '';
            $scope.Error.error_image = '';
            $scope.Error.error_custom_url = '';
        }

        
        // function to show selected university in edit mode
        $scope.edit_data = function () {
            //openPopDiv('addNewMessage');
            $('#addNewMessage').modal();
        };

        $scope.details = function (blog_guid)
        {
            var reqData = {
                BlogGUID: blog_guid,
                LoginSessionKey: $scope.AdminLoginSessionKey
            };
            showLoader();
            $scope.Blog = {};
            apiService.details(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    blog_data = response.Data[0];
                    $scope.Blog.Title = blog_data.Title;
                    $scope.Blog.Description = blog_data.Description;
                    $scope.Blog.Media = blog_data.Media;

                    if ($scope.Blog.Media.length > 0)
                    {
                        angular.forEach($scope.Blog.Media, function (value, key)
                        {
                            if (value.MediaType == 'Image')
                            {
                                click_function = 'remove_image("' + value.MediaGUID + '");';
                                var html = "<li><a id='" + value.MediaGUID + "' class='smlremove' onclick='" + click_function + "'></a>";
                                html += "<figure><img alt='' class='img-full' media_type='IMAGE' is_cover_media='0' media_guid='" + value.MediaGUID + "' src='" + ImageServerPath + 'upload/blog/196x196/' + value.ImageName + "'></figure>";
                                html += "<span class='radio'><input class='set_cover_pic' type='radio' name='coverpic' id='coverpicId1'><label for='coverpicId1'>COVER PIC</label></span></li>";
                                $('.attached-media').append(html);

                                $("#blog_video input[name='file']").prop("disabled", true);
                                $("#embed_code").prop("disabled", true);
                            } else if (value.MediaType == 'Video')
                            {
                                if (value.ConversionStatus == 'Finished')
                                {
                                    src = ImageServerPath + 'upload/blog/video/' + value.ImageName;
                                } else
                                {
                                    src = base_url + "assets/admin/img/blog_video.jpeg";
                                }

                                click_function = 'remove_image("VIDEO");';
                                var html = "<li><a id='" + value.MediaGUID + "' class='smlremove' onclick='" + click_function + "'></a>";
                                html += "<figure><img alt='' class='img-full' media_type='VIDEO' is_cover_media='0' media_guid='" + value.MediaGUID + "' src='" + src + "'></figure>";
                                html += "</li>";
                                $('.attached-media').html(html);

                                $("#blog_photo input[name='file']").prop("disabled", true);
                                $("#embed_code").prop("disabled", true);
                            } else
                            {
                                var output = value.ImageName.substr(value.ImageName.indexOf("=") + 1);
                                $scope.Blog.youtube = value.ImageName;
                                click_function = 'remove_image("YOUTUBE");';
                                var html = "<li id=''><a class='smlremove' onclick='" + click_function + "'></a>";
                                html += "<figure><img alt='' class='img-full' is_cover_media='0' media_type='YOUTUBE' media_guid='' src='http://img.youtube.com/vi/" + output + "/0.jpg'></figure>";
                                html += "</li>";
                                $('.attached-media').html(html);

                                $("#blog_photo input[name='file']").prop("disabled", true);
                                $("#blog_video input[name='file']").prop("disabled", true);
                            }
                        });
                    }
                    if (!angular.element.isEmptyObject(blog_data.CoverMedia))
                    {
                        click_function = 'remove_image("' + blog_data.CoverMedia.MediaGUID + '");';
                        var html = "<li><a id='" + blog_data.CoverMedia.MediaGUID + "' class='smlremove' onclick='" + click_function + "'></a>";
                        html += "<figure><img alt='' class='img-full' media_type='IMAGE' is_cover_media='0' media_guid='" + blog_data.CoverMedia.MediaGUID + "' src='" + ImageServerPath + 'upload/blog/196x196/' + blog_data.CoverMedia.ImageName + "'></figure>";
                        html += "<span class='radio'><input checked class='set_cover_pic' type='radio' name='coverpic' id='coverpicId1'><label for='coverpicId1'>COVER PIC</label></span></li>";
                        $('.attached-media').prepend(html);

                        $("#blog_video input[name='file']").prop("disabled", true);
                        $("#embed_code").prop("disabled", true);
                    }
                }
                $("html, body").animate({scrollTop: 0}, "slow");

                hideLoader();
            });
        }

        $scope.change_source = function ()
        {
            $scope.Error = {};
            if (angular.element('#Source').find("option:selected").text() == "SchollyMe") {
                angular.element(".box").not(".Schollyme").hide();
                angular.element(".Schollyme").show();
            } else if (angular.element('#Source').find("option:selected").text() == "Spotify") {
                angular.element(".box").not(".Spotify").hide();
                angular.element(".Spotify").show();
            } else if (angular.element('#Source').find("option:selected").text() == "iTunes") {
                angular.element(".box").not(".itunes").hide();
                angular.element(".itunes").show();
            } else {
                angular.element(".box").hide();
            }
        }


        $scope.resetPopup = function () {
            $('#addNewMessage').modal('hide')
            //$scope.remove_announcement_image();
            $scope.Error = {};
            $scope.myImageBanner = '';
            $('#CroppedImgData').attr('ng-src', '');
            $('#CroppedImgData').attr('src', '');
        }

        // function to save blog
        $scope.save_announcement = function (type) {
            $scope.showError = false;
            $scope.Error = {};
            $scope.Error.error_title = '';
            $scope.Error.error_description = '';
            $scope.Error.error_activity = '';
            $scope.Error.error_image = '';
            $scope.Error.error_tag = '';
            var media_guid = $scope.AnnouncementMediaGUID;           
            var rawImage = '';
            var QuizGUID = '';
            if($scope.announcement.Type == 1) {
                if ($scope.announcement.Title == '') {
                    $scope.showError = true;
                    $scope.Error.error_title = 'Please enter title.';
                }

                if ($scope.announcement.Description == '') {
                    $scope.showError = true;
                    $scope.Error.error_description = 'Please enter description.';
                }
            } else {
                rawImage = $('#CroppedImgData').attr('ng-src'); //$scope.myCroppedImageBanner;

                if ((rawImage == '' || rawImage == undefined)) {
                    $scope.showError = true;
                    $scope.Error.error_image = 'Please select image.';
                }

               /* if(media_guid == '') {
                    $scope.showError = true;
                    $scope.Error.error_image = 'Please select image.';
                }
                */
            }
            //console.log('UrlOptions', $scope.UrlOptions);
            //console.log('ActivityGUID', $scope.announcement.ActivityGUID);
            var TagID = 0;
            if($scope.UrlOptions==1 && $scope.announcement.ActivityGUID == '') {
                $scope.showError = true;
                $scope.Error.error_activity = 'Please enter activity ID.';                
            } else if($scope.UrlOptions==2 || $scope.UrlOptions==3 || $scope.UrlOptions==4) {
                if ($scope.announcement.Tag.length == 0) {
                    $scope.showError = true;
                    $scope.Error.error_tag = 'Please select value.';
                } else if ($scope.announcement.Tag.length > 1) {
                    $scope.showError = true;
                    $scope.Error.error_tag = 'Please select only one value.';
                }

                if($scope.UrlOptions==2) {
                    TagID = $scope.announcement.Tag[0].TagID;
                } else if($scope.UrlOptions==3 || $scope.UrlOptions==4) {
                    TagID = $scope.announcement.Tag[0].TagCategoryID;
                }                 
            } else if($scope.UrlOptions==5 && $scope.announcement.CustomURL == '') {
                $scope.showError = true;
                $scope.Error.error_custom_url = 'Please enter url.';   
            } else if($scope.UrlOptions==6  && $scope.announcement.UserGUID == '') {
                $scope.showError = true;
                $scope.Error.error_user = 'Please enter user ID.';
            } else if($scope.UrlOptions==7) {
                if ($scope.announcement.Quiz.length == 0) {
                    $scope.showError = true;
                    $scope.Error.error_quiz = 'Please select value.';
                } else if ($scope.announcement.Quiz.length > 1) {
                    $scope.showError = true;
                    $scope.Error.error_quiz = 'Please select only one value.';
                }

                QuizGUID = $scope.announcement.Quiz[0].QuizGUID;
                  
            }
           // console.log('Tag', $scope.announcement.Tag);
           // console.log('Tag ID', TagID);
           // return;
            if (!$scope.showError)
            {
                showLoader();
                //send message
                var reqData = {
                    Description: $scope.announcement.Description
                    , Title: $scope.announcement.Title
                    , Type: $scope.announcement.Type
                    , Status: type
                    , EntityType: $scope.announcement.EntityType
                    , Url: $scope.announcement.Url
                    , ActionText: $scope.announcement.ActionText
                    , MediaGUID: media_guid
                    , ActivityGUID: $scope.announcement.ActivityGUID
                    , UserGUID: $scope.announcement.UserGUID
                    , TagID: TagID
                    , QuizGUID: QuizGUID
                    , CustomUrl: $scope.announcement.CustomURL
                    , rawImage: rawImage                   
                };

                if ($('#CanIgnore').prop('checked') === true) {
                    reqData.CanIgnore = 1;
                } else {
                    reqData.CanIgnore = 0;
                }

                apiService.call_api(reqData, 'admin_api/announcement/add').then(function (response) {
                    if (response.ResponseCode == 200) {
                        ShowSuccessMsg(response.Message);
                        $scope.resetPopup();
                        $scope.list();
                        //closePopDiv('addNewMessage', 'bounceOutUp');   
                        $('#addNewMessage').modal('hide');   
                        $('#CanIgnore').prop('checked', true);                    
                    } else {
                        PermissionError(response.Message);
                    }
                    $("html, body").animate({scrollTop: 0}, "slow");
                    hideLoader();
                });
            } else
            {

            }
        }


        $scope.cance_action = function(){
            $scope.list();
        }
        // function to update blog
        $scope.update_blog = function (type, blog_guid) {
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

            if ($scope.Blog != undefined)
            {
                console.log($scope.Blog);
                var blog_title = $scope.Blog.Title;
                var blog_description = $scope.Blog.Description;
                if ($scope.Blog.Title == undefined)
                {
                    $scope.ErrorStatus = true;
                    $scope.Error.error_blog_title = required_blog_title;
                }
                if ($scope.Blog.Description == undefined)
                {
                    $scope.ErrorStatus = true;
                    $scope.Error.error_blog_description = required_blog_description;
                }
            } else if ($scope.Blog == undefined)
            {
                $scope.ErrorStatus = true;
                $scope.Error.error_blog_title = required_blog_title;

                $scope.ErrorStatus = true;
                $scope.Error.error_blog_description = required_blog_description;
            }

            if ($scope.Blog.youtube != undefined)
            {
                youtube = $scope.Blog.youtube;
            } else
            {
                youtube = '';
            }


            if (youtube != '')
            {
                if ((youtube.indexOf('http://www.youtube.com') > -1 || youtube.indexOf('https://www.youtube.com') > -1))
                {
                } else
                {
                    $("html, body").animate({scrollTop: 0}, "slow");
                    PermissionError('Please insert valid youtube url !');
                    $scope.ErrorStatus = true;
                }
            }


            if (!$scope.ErrorStatus)
            {
                showLoader();
                $('.img-full').each(function () {
                    if ($(this).attr('media_type') == 'YOUTUBE')
                    {
                        media.push({Url: $scope.Blog.youtube, IsCoverMedia: 0, 'MediaType': 'YOUTUBE'});
                    } else
                    {
                        media.push({MediaGUID: $(this).attr('media_guid'), IsCoverMedia: $(this).attr('is_cover_media'), 'MediaType': 'IMAGE'});
                    }
                });
                $scope.RequestData.Media = media;

                apiService.update($scope.RequestData).then(function (response) {
                    if (response.ResponseCode == 200)
                    {
                        //Show Success message
                        //closePopDiv('Setsong_popup', 'bounceOutUp');
                        $scope.Blog = {};
                        $scope.blog.video_guid = "";
                        $('.attached-media').html('');
                        ShowSuccessMsg(response.Message);
                        setTimeout(function () {
                            window.location.href = base_url + "admin/blog", 2000
                        });
                    } else
                    {
                        PermissionError(response.Message);
                    }
                    $("html, body").animate({scrollTop: 0}, "slow");
                    hideLoader();
                });
            } else
            {

            }
        };

        // for image compresion 
        $scope.isLoadingImage = false,
        $scope.showImage = false;
        $scope.AnnouncementMediaGUID='';
        $scope.AnnouncementImageName='';
        $scope.remove_announcement_image = function () {
            $('#uploadimageBTN').show();
            $scope.showImage = false; 
            $scope.isLoadingImage = false,
            $scope.AnnouncementMediaGUID='';
            $scope.AnnouncementImageName='';           
        }
        $scope.uploadProfilePicture = function (file, errFiles) {            
            angular.forEach(errFiles, function(errFile){
                showResponseMessage(errFile.$errorMessages, 'alert-danger');
            });
            
            if(!file) {
                return;
            }     

            var filename = file.name;
            var mimeString = file.type;
            var c = 0;
            var serr = 1;
            
            var URL = window.URL || window.webkitURL;
            var url = URL.createObjectURL(file);
            var image = new Image();
            image.onload = function() {
                var options ={
                    resizeMaxHeight: 700,
                    resizeMaxWidth: 700,
                    resizeQuality: '80',
                    resizeType: mimeString
                };
                $scope.jicCompress(image, options).then(function(dataURLcompressed){
                    $scope.dataURItoBlob(dataURLcompressed.src,mimeString).then(function(blobData){
                        var blob = blobData.file;
                        var file = new File([blob], filename,{type: mimeString});
                        $scope.uploadProfilePic(errFiles,file,serr,c);
                    });
                });
            }
            image.src = url;
        };

        $scope.uploadProfilePic = function(errFiles,file,serr,c){
            $scope.isLoadingImage = true;
            $('.dis-cret-m').addClass('disble-btn-cus');
            $('#uploadimageBTN').hide();
            if (!(errFiles.length > 0)) {
                var patt = new RegExp("^image");                
                var paramsToBeSent = {           
                    qqfile: file,
                    Type: 'blog',
                    DeviceType: 'Native',
                    LoginSessionKey: $('#AdminLoginSessionKey').val()
                };
                
                if (!patt) {
                    PermissionError('Only image files are allowed.', 'alert-danger');
                        $scope.isLoadingImage= false;
                    $('#uploadimageBTN').show();
                    $('.dis-cret-m').removeClass('disble-btn-cus');
                    return false;
                } else if (!patt.test(file.type)) {
                    PermissionError('Only image files are allowed.', 'alert-danger');
                    $scope.isLoadingImage= false;
                    $('#uploadimageBTN').show();
                    $('.dis-cret-m').removeClass('disble-btn-cus');
                    return false;
                }
                
                apiService.CallUploadFilesApi(
                    paramsToBeSent,
                    'api/upload_image',
                    function(response) {
                    if (response.data.ResponseCode === 200) {
                        var responseJSON = response.data;
                        if (responseJSON.Message == 'Success') {
                            $scope.isLoadingImage = false;
                            $scope.showImage = true;
                            $scope.AnnouncementMediaGUID=responseJSON.Data.MediaGUID;
                            $scope.AnnouncementImageName=responseJSON.Data.ImageName;

                            $('.upload-image').hide();
                            $('.dis-cret-m').removeClass('disble-btn-cus');
                            
                        } else {
                            PermissionError(responseJSON.Message);
                            serr++;
                            console.log(serr);
                            $scope.isLoadingImage= false;
                            $('#uploadimageBTN').show();
                            $('.dis-cret-m').removeClass('disble-btn-cus');
                        }
                    } else {
                        console.log(serr);
                        if (serr == 1) {
                            PermissionError('The uploaded image does not seem to be in a valid image format.');
                            $scope.isLoadingImage= false;
                            $('#uploadimageBTN').show();
                            $('.dis-cret-m').removeClass('disble-btn-cus');
                        } else {
                            serr = 1;
                        }
                    }
                    },
                    function(response) {
                        console.log(serr);
                        if (serr == 1) {
                            //alertify.error('The uploaded image does not seem to be in a valid image format.');
                        } else {
                            serr = 1;
                        }
                    },
                    function(evt) {
                        /*c = parseInt($('#image_counter').val());
                        c = c + 1;
                        $('#image_counter').val(c);*/
                    });

            } else {
                showResponseMessage(errFiles[0].$errorMessages, 'alert-danger');
            }
        };

        $scope.jicCompress = function(sourceImgObj, options) {
            var deferred = $q.defer();
            var outputFormat = options.resizeType;
            var quality = options.resizeQuality * 100 || 70;
            var mimeType = outputFormat;

            var maxHeight = options.resizeMaxHeight || 300;
            var maxWidth = options.resizeMaxWidth || 250;

            var height = sourceImgObj.height;
            var width = sourceImgObj.width;

            // calculate the width and height, constraining the proportions
            if (width > height) {
                if (width > maxWidth) {
                        height = Math.round(height *= maxWidth / width);
                        width = maxWidth;
                }
            }
        else {
                if (height > maxHeight) {
                        width = Math.round(width *= maxHeight / height);
                        height = maxHeight;
                }
            }

            var cvs = document.createElement('canvas');
            cvs.width = width; //sourceImgObj.naturalWidth;
            cvs.height = height; //sourceImgObj.naturalHeight;
            var ctx = cvs.getContext('2d').drawImage(sourceImgObj, 0, 0, width, height);
            var newImageData = cvs.toDataURL(mimeType, quality / 100);
            var resultImageObj = new Image();
            resultImageObj.src = newImageData;
            deferred.resolve({
                src: newImageData
            });
        // return resultImageObj.src;
            return deferred.promise;
        };

        $scope.dataURItoBlob =function(dataURI,mimeString) {
            var deferred = $q.defer();
            // convert base64/URLEncoded data component to raw binary data held in a string
            var byteString;
            if (dataURI.split(',')[0].indexOf('base64') >= 0)
                byteString = atob(dataURI.split(',')[1]);
            else
                byteString = unescape(dataURI.split(',')[1]);
    
            // write the bytes of the string to a typed array
            var ia = new Uint8Array(byteString.length);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            
            // write the ArrayBuffer to a blob, and you're done
            var blob = new Blob([ia], {type: mimeString});
             deferred.resolve({
                file: blob
            });
            return deferred.promise;
      };


        $scope.BannerData = {        
            'BannerSize': ''        
        };
        $scope.imageAllowType = ['image/png', 'image/jpeg', 'image/JPEG', 'image/PNG', 'image/jpg', 'image/JPG'];

        var handleFileSelectBanner = function (evt) {
            console.log('sd');
            var file = evt.currentTarget.files[0];

            if (file.type == '') {
                $('#ErrorValideImage').show();
                return false;
            } else {
                if ($.inArray(file.type, $scope.imageAllowType) == -1) {
                    $('#ErrorValideImage').show();
                    return false;
                } else {
                    $('#ErrorValideImage').hide();
                }
            }

            var reader = new FileReader();
            reader.onload = function (evt) {
                $scope.$apply(function ($scope) {
                    $scope.myImageBanner = evt.target.result;
                });
            };
            reader.readAsDataURL(file);
        };
        $scope.initializeCropper = function () {
            $timeout(function () {
                angular.element(document.querySelector('#fileInputBanner')).on('click', function () {
                    this.value = null;
                });
                
                angular.element(document.querySelector('#fileInputBanner')).on('change', handleFileSelectBanner);               
            }, 100);
        }
        $scope.myImageBanner = '';


    }]);

    app.directive('fineUploader', function () {
        return {
            restrict: 'A',
            require: '?ngModel',
            scope: {model: '='},
            replace: false,
            link: function ($scope, element, attributes, ngModel) {
                var serr = 1;
                $scope.uploader = new qq.FineUploader({
                    element: element[0],
                    multiple: false,
                    title: "Attach a Photo",
                    request: {
                        endpoint: base_url + "api/upload_image",
                        customHeaders: {
                        "APPVERSION": 'v3'
                        },
                        params: {
                            Type: attributes.sectionType,
                            unique_id: function () {
                                return '';
                            },
                            LoginSessionKey: $('#AdminLoginSessionKey').val(),
                            DeviceType: 'Native'
                        }
                    },
                    validation: {
                        allowedExtensions: attributes.uploadExtensions.split(',')
                    },
                    failedUploadTextDisplay: {
                        mode: 'none'
                    },
                    callbacks: {
                        onUpload: function (id, fileName) {
                            var html = "<li id='dummy_img"+ id +"'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='"+base_url+"assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                            $('#attached-media-'+$(element).attr('unique-id')).append(html);
                        },
                        onProgress: function (id, fileName, loaded, total) {
                        },
                        onComplete: function (id, fileName, responseJSON) {
                            if (responseJSON.Message == 'Success')
                            {
                                if ($(element).attr('image-type') == "landscape")
                                {
                                    $('#attached-media-' + $(element).attr('unique-id')).html("<label>" + responseJSON.Data.ImageName + "</label>");
                                } else
                                {
                                   // var CategoryCtrl = angular.element('#CategoryCtrl').scope();
    
                                    // CategoryCtrl.$apply(function () {
                                    //     CategoryCtrl.currentData.ImageName = responseJSON.Data.ImageName;
                                    //     CategoryCtrl.currentData.MediaGUID = responseJSON.Data.MediaGUID;
                                    // });
                                    click_function = 'remove_image("'+responseJSON.Data.MediaGUID+'");';
                                     var html = "<li class='catImgList' id='"+responseJSON.Data.MediaGUID+"'><a class='smlremove' onclick='"+ click_function+" $(this).parent(\"li\").remove();'></a>";
                                     html+= "<figure><img alt='' width='98px' class='img-"+$(element).attr('image-type')+"-full' media_type='IMAGE' is_cover_media='0' media_name='"+responseJSON.Data.ImageName+"' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+responseJSON.Data.ImageServerPath +'/'+responseJSON.Data.ImageName+"'></figure>";
                                     html+= "<span class='radio'></span><input type='hidden' name='MediaGUID' value='" + responseJSON.Data.MediaGUID + "'/></li>";
    
                                     $('#attached-media-'+$(element).attr('unique-id')).append(html);
                                     $('.upload-image').hide();
                                     
                                     // var $items = $('.img-full');
                                }
                                $('#dummy_img'+ id).remove();
                            } else if (responseJSON.ResponseCode !== 200)
                            {
                                $('#attached-media-' + $(element).attr('unique-id')).html("");
                            }
                        },
                        onValidate: function (b)
                        {
                            var allowed_extension = $(element).attr('upload-extensions');
                            var temp = new Array();
                            validExtensions = allowed_extension.split(",");
                            var fileName = b.name;
                            var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                            if ($.inArray(fileNameExt, validExtensions) == -1)
                            {
                                $("html, body").animate({scrollTop: 0}, "slow");
                                if ($(element).attr('image-type') == "landscape")
                                {
                                    PermissionError('Allowed file types only doc, docx, pdf and xls.');
                                } else
                                {
                                    PermissionError('Allowed file types only jpeg, jpg, gif and png.');
                                }
                                return false;
                            }
                        },
                        onError: function () {
                            $('#cm-' + attributes.uniqueId + ' .loading-class').remove();
                        }
                    },
                    showMessage: function (message) {
                        //showResponseMessage(message,'alert-danger');
                    },
                    text: {
                        uploadButton: '<i class="icon-upload icon-white"></i> Upload File(s)'
                    },
                    template: ' <a class="qq-upload-button"  title="Attach a Photo"><button>Upload</button></a><span class="qq-drop-processing qq-upload-drop-area" style="display:none;"></span>' +
                            '<ul class="qq-upload-list" style="display:none;margin-top: 10px; text-align: center;"></ul>',
                    chunking: {
                        //enabled: false
                        //onclick=$(\'#cmt-'+attributes.uniqueId+'\').trigger(\'focus\');
                    }
                });
            }
        };
    });
    
    
    function remove_image(MediaGUID) {
        $('#' + MediaGUID).remove();
        $('.upload-image').show();
    }