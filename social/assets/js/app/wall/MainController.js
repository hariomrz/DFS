angular.module('App').controller('WallPostCtrl', ['$http', 'GlobalService', '$scope', '$rootScope', 'Settings', '$sce', '$timeout', '$q', 'webStorage', 'WallService', 'appInfo', 'setFormatDate', '$interval', '$compile',
    '$window', '$routeParams', '$location', 'lazyLoadCS', 'UtilSrvc', 'profileCover',
    function ($http, GlobalService, $scope, $rootScope, Settings, $sce, $timeout, $q, webStorage, WallService, appInfo, setFormatDate, $interval, $compile, $window, $routeParams, $location, lazyLoadCS, UtilSrvc, profileCover)
    {
        $scope.$on('$routeChangeSuccess', function (event) {
            var pt = {'Value': 0, 'Label': 'All Posts'};
            if ($routeParams.type)
            {
                angular.element($('#WallPostCtrl')).scope().p_type = $routeParams.type;
                if ($routeParams.type == 'discussions')
                {
                    pt = {'Value': 1, 'Label': 'Discussions'};
                }
                if ($routeParams.type == 'announcements')
                {
                    pt = {'Value': 7, 'Label': 'Announcements'};
                }
                if ($routeParams.type == 'questions')
                {
                    pt = {'Value': 2, 'Label': 'Questions'};
                }
                if ($routeParams.type == 'articles')
                {
                    pt = {'Value': 4, 'Label': 'Articles'};
                }
                angular.element($('#WallPostCtrl')).scope().filterPostType(pt);
            } else
            {
                angular.element($('#WallPostCtrl')).scope().p_type = $routeParams.type;
                angular.element($('#WallPostCtrl')).scope().filterPostType(pt);
            }
        });

        $scope.article_post = {ModuleID:'',ModuleEntityGUID:'',ModuleEntityName:'',ActivityGUID:''};
        if($('#ModuleEntityName').length > 0) {
            $scope.article_post = {ModuleID:$('#module_id').val(), ModuleEntityGUID:$('#module_entity_guid').val(), ModuleEntityName:$('#ModuleEntityName').val(),ActivityGUID:$('#ActivityGUID').val()};
        }
        
        if($scope.article_post.ModuleID == 3 || $scope.article_post.ModuleID == 34) {
            $scope.article_post_custom = { ModuleID : 0, ModuleEntityGUID : ''};
            if($scope.article_post.ModuleID == 34) {
                $scope.article_post_custom = { ModuleID : $scope.article_post.ModuleID, ModuleEntityGUID : $scope.article_post.ModuleEntityGUID, ActivityGUID : $scope.article_post.ActivityGUID};
            }
            getForums();                        
        }
        
        
        function getForums() {
            
            var surl = appInfo.serviceUrl + 'forum/forum_name';
            
            WallService.CallPostApi(surl, {}, function (successResp) {  
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.forumList = response.Data;
                    
                    var forumId = $('#ForumID').val();
                    
                    if(forumId) {
                        angular.forEach($scope.forumList, function(forum){
                            if(forumId == forum.ForumID) {
                                $scope.getForumCates(forum);
                            }                            
                        });
                    }
                    
                }
            }, function (error) {
                //showResponseMessage('Unable to update tags.', 'alert-success');
            });
        }
        
        
        
        $scope.getForumCates = function(forum) {
            var surl = appInfo.serviceUrl + 'forum/forum_category';
            
            $scope.selectedForum = forum;
            $scope.selectedCategory = '';
            
            var forumId = $('#ForumID').val();
            if(!forumId) {
                $scope.article_post_custom = { ModuleID : 0, ModuleEntityGUID : ''};
            }
            
            
            WallService.CallPostApi(surl, {ForumID : forum.ForumID}, function (successResp) {  
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.forumCatList = response.Data; 
                    
                    if(forumId) {
                        angular.forEach($scope.forumCatList, function(forumCat){
                            if($scope.article_post_custom.ModuleEntityGUID == forumCat.ForumCategoryGUID) {
                                $scope.selectForumCategory(forumCat);
                            }                            
                        });
                    }
                    
                }
            }, function (error) {
                //showResponseMessage('Unable to update tags.', 'alert-success');
            });
        }
        
        $scope.selectForumCategory = function(selectedCategory) {
            $scope.selectedCategory = selectedCategory;
            
            $scope.article_post_custom.ModuleID = 34;
            $scope.article_post_custom.ModuleEntityGUID = selectedCategory.ForumCategoryGUID;                        
        }
        
        var postArticleIsDraft = false;
        $scope.SubmitWallpostPagePre = function(isDraft) {
            postArticleIsDraft = isDraft;
            $('#addsummary').modal();
        }
        
        
        $scope.SubmitWallpostPage = function(isSummary) {
            
            if($scope.article_post.ModuleID == 3 && !$scope.article_post_custom.ModuleEntityGUID) {
                return;
            }
            
            if(($scope.article_post.ModuleID == 3 || $scope.article_post.ModuleID == 34) && $scope.article_post_custom.ModuleEntityGUID) {
                $('#module_id').val($scope.article_post_custom.ModuleID);
                $('#module_entity_guid').val($scope.article_post_custom.ModuleEntityGUID);
            }
            
            
            
            if(postArticleIsDraft) {
                $scope.saveDraft(isSummary);
            } else {
                $scope.SubmitWallpost(isSummary);
            }   
            
            // Reset title data
            $scope.postTitle = '';
            $scope.noContentToPost = true;
            postArticleIsDraft = false;
            
        }
        
        $scope.SubmitWallpostPagePostBtn = function() {
            
            if($scope.article_post.ModuleID == 3 && !$scope.article_post_custom.ModuleEntityGUID) {
                return true;
            }
            
            return ($scope.isWallAttachementUploading || $scope.noContentToPost);
        }
        
        $scope.ajax_save_crop_image = function () {
            profileCover.ajax_save_crop_image().then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.CoverImage = response.Data.ProfileCover;
                    $scope.CoverExists = 1;
                    $('#coverImgProfile').on('load', function () {
                        $('.cover-picture-loader').hide();
                        $('.change-cover').show();
                        $('#coverViewimg').show();
                        $('#coverDragimg').hide().find('img').css('top', 0);
                        $('.inner-follow-frnds').show();
                    });
                }
            });
        }
        

        // Define variables start
        $scope.checkedMyDeskFiltersCount = 0;
        $scope.IsMyDeskTab = false;
        $scope.IsFirstMyDesk = false;
//        $scope.IsMyDesk = false;
        $scope.myDeskTabFilter = {};
        //$scope.partialURL = base_url + 'assets/partials/wall/';
        $scope.partialURL = AssetBaseUrl + 'partials/wall/';
        $scope.baseURL = base_url;
        $scope.ReminderFilter = 0;
        $scope.IsReminder = 0;
        $scope.stopExecution = 0;
        //NewsFeedCtrl.activityData = new Array();
        $scope.PollFilterType = [];
        $scope.ShowNewPost = 1;
        $scope.WallPageNo = 1;
        $scope.IsFilePage = 0;
        $scope.IsActiveFilter = false;
        $scope.isOverlayActive = false;
        $scope.stickynote = false;
        $scope.ShowWallPostOnFilesTab = false;
        $scope.PostAs = {};
        $scope.postTagList = [];
        $scope.addTagList = false;
        $scope.isActivityPrevented = false;
        $scope.isFileTab = false;
        $scope.isLinkTab = false;
        $scope.isWallPostRequested = false;
        $scope.isContentSearchTab = false;
        $scope.PostContent = '';
        $scope.activePostType = 0;
        $scope.ActivityDetail = {FriendSearchKey: '', Note: ''};
        $scope.recommended_article_list = [];

        $scope.LoggedInFirstName = '';
        $scope.LoggedInLastName = '';
        $scope.LoggedInPicture = '';

        $scope.IsContest = 0;
        $scope.ShowPostType = ('ShowPostType' in window) ? window.ShowPostType : '';

        $scope.opt = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];


        var NewsFeedCtrl = angular.element(document.getElementById('NewsFeedCtrl')).scope();

        if (typeof LoggedInFirstName !== 'undefined')
        {
            $scope.LoggedInFirstName = LoggedInFirstName;
            $scope.LoggedInLastName = LoggedInLastName;
            $scope.LoggedInPicture = LoggedInPicture;
        }

        $scope.updateTagList = function(val)
        {
            $scope.addTagList = val;
        }

        //Added for post privacy
        $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to everyone';
        $scope.selectContactsHelpTxt = 'If blank, message will be visible to everyone.';
        $scope.taggedHelpTxtSuffix = '';
        $scope.taggedEntityInfo = {};
        $scope.taggedEntityInfoCount = 0;
        $scope.selectedPrivacy = 1;
        $scope.selectedPrivacyTooltip = 'Everyone';
        $scope.selectedPrivacyClass = 'icon-every';
        //Added for post privacy

        // Define variables ends
        var LoginGUID = '';
        if (typeof LoggedInUserGUID !== 'undefined')
        {
            LoginGUID = LoggedInUserGUID;
        } else
        {
            LoggedInUserGUID = '';
        }

        $scope.textareaAutosize = function () {
            $('textarea[data-autoresize]').each(function () {
                var max_height = 100;
                var offset = this.offsetHeight - this.clientHeight;

                var resizeTextarea = function (el) {
                    $(el).css('height', 'auto').css({'height': Math.min(max_height, el.scrollHeight - offset - 14), 'padding': '10px'});
                };
                $(this).on('keyup input', function () {
                    resizeTextarea(this);
                }).removeAttr('data-autoresize');
            });
        }


        $scope.showNewbie = true;
        if (typeof webStorage.getStorageData('showNewbie') !== 'undefined')
        {
            $scope.showNewbie = webStorage.getStorageData('showNewbie');
        }

        $scope.toggleShowNewbie = function ()
        {

            var showNewbie = !webStorage.getStorageData('showNewbie');
            webStorage.setStorageData('showNewbie', showNewbie);
        }

        if (webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID)) {
            var defualtMyDeskTabFilter = angular.copy(webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID));
            var defualtMyDeskTabFilter = angular.copy(webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID));
            if (typeof defualtMyDeskTabFilter.Favourite != undefined) {
                delete defualtMyDeskTabFilter.Favourite;
            }
        } else {
            var defualtMyDeskTabFilter = {
                All: 1,
                Favourite: 0,
                WatchList: 1,
                Mention: 1,
                Reminder: 1,
                NotifyMe: 1
            };
        }

        $scope.slickCurrentIndex = 0;
        $scope.slickConfig = {
            dots: true,
            infinite: true,
            method: {},
            slidesToShow: 1,
            slidesToScroll: 1
        };

        /*if(typeof IsNewsFeed=='undefined')
         {
         var IsNewsFeed = 0;
         }*/

        var LoginType = 'user';
        $scope.FromModuleID = '3';
        $scope.FromModuleEntityGUID = LoginGUID;
        $scope.ActivityHistory = [];
        $scope.ActivityFriends = [];
        $scope.RequestFriendList = [];
        $scope.ActivityFriendPageNo = 1;
        $scope.ActivityFriendActivityGUID = '';
        $scope.override_post_permission = [];
        $scope.viewLoader = true;
        $scope.blankScreen = false;

        //Added for post privacy
        $scope.resetPrivacySettings = function () {
            /*$('#IconSelect').prop('class', 'icon-every');
             angular.element('#visible_for').val(1)
             $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to everyone';
             $scope.selectContactsHelpTxt = 'If blank, message will be visible to everyone.';
             $scope.taggedHelpTxtSuffix = '';
             $scope.taggedEntityInfo = {};
             $scope.taggedEntityInfoCount = 0;
             $scope.selectedPrivacy = 1;
             $scope.selectedPrivacyTooltip = 'Everyone';*/
        };

        $scope.hidePostview = function ()
        {
            $scope.postEditormode = false;
        }

        $scope.setActiveIconToPrivacy = function (privacy) {
            privacy = (privacy) ? privacy : angular.element('#visible_for').val();
            switch (true) {
                case (privacy == 1):
                    $scope.selectedPrivacyTooltip = 'Everyone';
                    $scope.selectedPrivacyClass = 'icon-every';
                    break;
                case (privacy == 2):
                    $scope.selectedPrivacyTooltip = 'Friends of Friends' + (($scope.taggedEntityInfoCount > 0) ? ' + Anyone Tagged' : '');
                    $scope.selectedPrivacyClass = 'icon-follwers';
                    break;
                case (privacy == 3):
                    $scope.selectedPrivacyTooltip = 'Friends' + (($scope.taggedEntityInfoCount > 0) ? ' + Anyone Tagged' : '');
                    $scope.selectedPrivacyClass = 'icon-frnds';
                    break;
                case (privacy == 4):
                    $scope.selectedPrivacyTooltip = 'Only Me' + (($scope.taggedEntityInfoCount > 0) ? ' + Anyone Tagged' : '');
                    $scope.selectedPrivacyClass = 'icon-onlyme';
                    break;
                default:
                    $scope.selectedPrivacyTooltip = 'Everyone';
                    $scope.selectedPrivacyClass = 'icon-every';
            }
        }

        $scope.setPrivacyHelpTxt = function (privacy) {
            privacy = (privacy) ? privacy : angular.element('#visible_for').val();
            $scope.taggedHelpTxtSuffix = ($scope.taggedEntityInfoCount > 0) ? ' anyone tagged' : '';
            $scope.selectedPrivacy = privacy;
            switch (true) {
                case (privacy == 1):
                    $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to everyone.';
                    break;
                case (privacy == 2):
                    $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to your friends and their friends' + (($scope.taggedHelpTxtSuffix == '') ? '.' : ' + ' + $scope.taggedHelpTxtSuffix + '.');
                    break;
                case (privacy == 3):
                    $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to your friends' + (($scope.taggedHelpTxtSuffix == '') ? '.' : ' + ' + $scope.taggedHelpTxtSuffix + '.');
                    break;
                case (privacy == 4):
                    $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to only me' + (($scope.taggedHelpTxtSuffix == '') ? '.' : ' + ' + $scope.taggedHelpTxtSuffix + '.');
                    break;
                default:
                    $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to everyone.';
            }
        }

        if (typeof LoggedInUserID !== 'undefined' && $('#module_id').val() == '3')
        {
            var default_privacy = webStorage.getStorageData('defaultPrivacy_' + LoggedInUserID) || 1;
        } else
        {
            var default_privacy = 1;
        }
        if (default_privacy)
        {
            $scope.setActiveIconToPrivacy(default_privacy);
            $scope.setPrivacyHelpTxt(default_privacy);
            setTimeout(function () {
                $('#visible_for').val(default_privacy);
            }, 100);
        }

        $scope.parseTaggedInfo = function () {
            $timeout(function () {
                var taggedContentRegex = /<span.+?(?=class=[\"\']user-(\d+)-(\d+)[\"\'])[^>]+>((?:.(?!\<\/span\>))*.)<\/span>/gi,
                        matchedInfo,
                        contentToFilter = $(".note-editable").html(),
                        taggedObject = {},
                        taggedPromise = [];
                $scope.taggedEntityInfo = {};
                while ((matchedInfo = taggedContentRegex.exec(contentToFilter))) {
                    taggedObject[matchedInfo[2]] = {Name: matchedInfo[3], EntityId: matchedInfo[2], ModuleId: matchedInfo[1]};
                    taggedPromise.push(makeResolvedPromise(taggedObject));
                }

                $q.all(taggedPromise).then(function (data) {
                    $scope.taggedEntityInfo = angular.copy(taggedObject);
                    $scope.taggedEntityInfoCount = Object.keys($scope.taggedEntityInfo).length;
                    $scope.setPrivacyHelpTxt($scope.selectedPrivacy);
                    $scope.setActiveIconToPrivacy($scope.selectedPrivacy);
                });
            }, 1000);
        }
        //Added for post privacy

        $scope.get_privacy_text = function (s)
        {
            var str = '';
            if (s == 1) {
                str = 'Anyone on ' + $scope.lang.web_name;
            } else if (s == 3)
            {
                str = 'Friends of ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix);
            } else
            {
                str = 'Only me + ' + $scope.FirstName + (($scope.taggedHelpTxtSuffix == '') ? '' : ' + ' + $scope.taggedHelpTxtSuffix);
            }
            return str;
        }

        $scope.updateActivePostType = function (val)
        {

            $scope.showErrorMessage = false;

            if ($scope.activePostType != '8' && $scope.activePostType != '9')
            {
                elementLoaded('#postEditor .note-editable',function(el){
                    var pc = $("#postEditor .note-editable").text().trim();
                    var pc_html = $("#postEditor .note-editable").html();
                    if (pc_html.indexOf('img') >= 0 || pc_html.indexOf('iframe') >= 0)
                    {
                        $scope.isMediaInserted = true;
                    }
                    if (pc !== '' || $scope.isMediaInserted || $scope.isWallAttachementUploading || $scope.mediaCount > 0 || $scope.fileCount > 0 || $('#PostTitleInput').val().length > 0)
                    {
                        $scope.showErrorMessage = true;
                    }
                });
            } else
            {
                if ($scope.visualPostImage == 1 || $scope.VisualPost.PostTitle != '' || $scope.VisualPost.PostContent != '' || $scope.VisualPost.Facts != '')
                {
                    $scope.showErrorMessage = true;
                }
            }

            if ($scope.showErrorMessage && $scope.postEditormode && ($scope.activePostType == '8' || val == '8') && $scope.activePostType != val)
            {
                showConfirmBox("Change Post Type ?", "Are you sure you want to change post type? All your content will be lost.", function (e) {
                    if (e) {
                        $scope.clearVisualPost();
                        $scope.clearWallPost(1);
                        $scope.activePostType = val;
                        if (!$scope.$$phase)
                        {
                            $scope.$apply();
                        }
                    }
                });
            } else
            {
                $scope.activePostType = val;
            }
        }
        $scope.updateActivePostTypeDefault = function (data)
        {
            if (IsAdminView == '1')
            {
                var SetDiscussion = true;
                $scope.updateActivePostType(1);
            } else
            {
                if (data.length > 0)
                {
                    var SetDiscussion = false;
                    $.each(data, function () {
                        if (this.Value == 1)
                        {
                            SetDiscussion = true;
                        }
                    })
                    if (SetDiscussion)
                    {
                        $scope.updateActivePostType(1);
                    } else
                    {
                        $scope.updateActivePostType(data[0].Value);
                    }
                }
            }


            runOnAllSumernoteInstances($scope.onSummerNoteChange);

        }

        $scope.showNewsFeedPopup = function () {
            $scope.overlayShow = 1;
            $scope.titleKeyup = 0;
            $scope.postEditormode = 1;
            $('.emoji-panel0').html('');
            $('#postEditor .note-editable').html('');
            if($('#NotifyAllEvent').length>0)
            {
                $('#NotifyAllEvent').prop('checked',false);
            }
            setTimeout(function () {
                // $('#postEditor .note-editable').focus()
                $scope.setFocusToSummernote('#PostContent')
            }, 100);

            $("#postNewsFeedTypeModal").modal();
            $scope.clearVisualPost();
            
            $scope.summernoteBtnDisabler = true;
            
            runOnAllSumernoteInstances($scope.onSummerNoteChange);
        }
        $scope.setEditVariable = function(val){
            $scope.edit_post = val;
        };

        $scope.getModuleID = function ()
        {
            $scope.FromModuleID = 3;
            switch (LoginType)
            {
                case 'page':
                    $scope.FromModuleID = 18;
                    break;
            }
        }
        // Download files
        $scope.hitToDownload = function (MediaGUID, mediaFolder) {
            mediaFolder = (mediaFolder && (mediaFolder != '')) ? mediaFolder : 'wall';
            $window.location.href = $scope.baseURL + 'home/download/' + MediaGUID + '/' + mediaFolder;
        }

//        Tagging Start
        $scope.initTagsItem = function (feedIndex, activityDataRef) {
            var activityData = (activityDataRef) ? activityDataRef : NewsFeedCtrl.activityData[feedIndex];
            var entityTags = angular.copy(activityData.EntityTags);
            activityData['editTags'] = (entityTags && (entityTags.length > 0)) ? entityTags : [];
            activityData['showTags'] = false;
        };

        $scope.toggleTagsItem = function (feedIndex, activity_guid) {
            if (typeof activity_guid !== 'undefined')
            {
                angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                    if (val.ActivityGUID == activity_guid)
                    {
                        var showTags = NewsFeedCtrl.activityData[key]['showTags'];
                        if (showTags) {
                            $scope.initTagsItem(key);
                        } else {
                            NewsFeedCtrl.activityData[key]['showTags'] = true;
                        }
                    }
                });
            } else
            {
                var showTags = NewsFeedCtrl.activityData[feedIndex]['showTags'];
                if (showTags) {
                    $scope.initTagsItem(feedIndex);
                } else {
                    $scope.initTagsItem(feedIndex);
                    NewsFeedCtrl.activityData[feedIndex]['showTags'] = true;
                }
            }
        };

        $scope.toggleTagsItemAnnouncement = function (feedIndex) {
            var showTags = $scope.group_announcements[feedIndex]['showTags'];
            if (showTags) {
                $scope.initTagsItem(feedIndex);
            } else {
                $scope.group_announcements[feedIndex]['showTags'] = true;
            }
        };

        $scope.addedEntityTag = function (newTag, feedIndex) {
            var editTags = (NewsFeedCtrl.activityData[feedIndex]['editTags']) ? angular.copy(NewsFeedCtrl.activityData[feedIndex]['editTags']) : [],
                    lastIndex = editTags.length - 1;
            if (editTags.length) {
                editTags[lastIndex]['TooltipTitle'] = editTags[lastIndex]['Name'];
                NewsFeedCtrl.activityData[feedIndex]['editTags'] = angular.copy(editTags);
            }
        };

        $scope.addPostTag = function (tag, activity_id)
        {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityID == activity_id)
                {
                    if (!NewsFeedCtrl.activityData[key]['AddedPostTag'])
                    {
                        NewsFeedCtrl.activityData[key]['AddedPostTag'] = [];
                    }
                    NewsFeedCtrl.activityData[key]['AddedPostTag'].push(tag);
                    if (val.RemovedPostTag)
                    {
                        angular.forEach(val.RemovedPostTag, function (v, k) {
                            if (v == tag.TagID)
                            {
                                NewsFeedCtrl.activityData[key]['RemovedPostTag'].splice(k, 1);
                            }
                        });
                    }
                }
            });
        }

        $scope.removePostTag = function (tag, activity_id)
        {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityID == activity_id)
                {
                    if (!NewsFeedCtrl.activityData[key]['RemovedPostTag'])
                    {
                        NewsFeedCtrl.activityData[key]['RemovedPostTag'] = [];
                    }
                    NewsFeedCtrl.activityData[key]['RemovedPostTag'].push(tag.TagID);
                    if (val.AddedPostTag)
                    {
                        angular.forEach(val.AddedPostTag, function (v, k) {
                            if (v.TagID == tag.TagID)
                            {
                                NewsFeedCtrl.activityData[key]['AddedPostTag'].splice(k, 1);
                            }
                        });
                    }
                }
            });
        }

        $scope.updatePostTags = function (activity_id)
        {
            var reqData = {TagsList: [], EntityID: activity_id, EntityType: 'ACTIVITY', TagType: 'ACTIVITY', TagsIDs: []};
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityID == activity_id)
                {
                    if (NewsFeedCtrl.activityData[key].AddedPostTag)
                    {
                        reqData['TagsList'] = NewsFeedCtrl.activityData[key].AddedPostTag;
                        NewsFeedCtrl.activityData[key].AddedPostTag = [];
                    }
                    if (NewsFeedCtrl.activityData[key].RemovedPostTag)
                    {
                        reqData['TagsIDs'] = NewsFeedCtrl.activityData[key].RemovedPostTag;
                        NewsFeedCtrl.activityData[key].RemovedPostTag = [];
                    }
                }
            });

            // Remove null ids
            angular.forEach(reqData['TagsIDs'], function (tagId, index) {
                if (!tagId) {
                    reqData['TagsIDs'].splice(index, 1);
                }
            });

            if (reqData['TagsList'].length == 0 && reqData['TagsIDs'].length == 0) {
                return;
            }

            var surl = appInfo.serviceUrl + 'tag/save';
            if (IsAdminView == '1')
            {
                surl = appInfo.serviceUrl + 'api/tag/save';
            }

            WallService.CallPostApi(surl, reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {

                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityID == activity_id)
                        {
                            angular.forEach(val.EntityTags, function (v1, k1) {
                                angular.forEach(reqData['TagsIDs'], function (v2, k2) {
                                    if (v1.TagID == v2)
                                    {
                                        NewsFeedCtrl.activityData[key].EntityTags.splice(k1, 1);
                                    }
                                });
                            });

                            angular.forEach(reqData['TagsList'], function (v1, k1) {
                                var append = true;
                                angular.forEach(val.EntityTags, function (v2, k2) {
                                    if (v1.TagID == v2.TagID)
                                    {
                                        append = false;
                                    }
                                });
                                if (append)
                                {
                                    NewsFeedCtrl.activityData[key].EntityTags.push(v1);
                                }
                            });

                            NewsFeedCtrl.activityData[key].editTags = NewsFeedCtrl.activityData[key].EntityTags;
                            NewsFeedCtrl.activityData[key]['showTags'] = false;
                        }
                    });

                    showResponseMessage('Tags updated successfully.', 'alert-success');
                }
            }, function (error) {
                showResponseMessage('Unable to update tags.', 'alert-success');
            });
        }

        $scope.updateActivityTags = function (newTags, EntityID, feedIndex) {
            var newTagsPromise = [],
                    newTagsToSend = [];
            if (newTags && (newTags.length > 0)) {
                angular.forEach(newTags, function (tagData, tagKey) {
                    newTagsPromise.push(makeResolvedPromise(tagData).then(function (tag) {
                        var newTagObj = tag;
                        newTagObj['Name'] = (tag.TooltipTitle) ? tag.TooltipTitle : tag.Name;
                        newTagsToSend.push(newTagObj);
                    }));
                });
            }

            if (newTagsToSend.length == 0 && NewsFeedCtrl.activityData[feedIndex]['EntityTags'].length == 0) {
                return;
            }

            $q.all(newTagsPromise).then(function (data) {
                var reqData = {TagsList: newTagsToSend, EntityID: EntityID, EntityType: 'ACTIVITY', TagType: 'ACTIVITY'};
                WallService.CallPostApi(appInfo.serviceUrl + 'tag/save', reqData, function (successResp) {
                    var response = successResp.data;
                    if ((response.ResponseCode == 200) && (response.Message == 'Success')) {
                        NewsFeedCtrl.activityData[feedIndex]['EntityTags'] = angular.copy(newTags);
                        NewsFeedCtrl.activityData[feedIndex]['editTags'] = angular.copy(newTags);
                        NewsFeedCtrl.activityData[feedIndex]['showTags'] = false;
                        showResponseMessage('Tags updated successfully.', 'alert-success');
                    }
                }, function (error) {
                    showResponseMessage('Unable to update tags.', 'alert-success');
                });
            });
        };

        $scope.updateActivityTagsAnnouncement = function (newTags, EntityID, feedIndex) {
            var newTagsPromise = [],
                    newTagsToSend = [];
            if (newTags && (newTags.length > 0)) {
                angular.forEach(newTags, function (tagData, tagKey) {
                    newTagsPromise.push(makeResolvedPromise(tagData).then(function (tag) {
                        var newTagObj = tag;
                        newTagObj['Name'] = (tag.TooltipTitle) ? tag.TooltipTitle : tag.Name;
                        newTagsToSend.push(newTagObj);
                    }));
                });
            }


            if (newTags.length == 0 && $scope.group_announcements[feedIndex]['EntityTags'].length == 0) {
                return;
            }

            $q.all(newTagsPromise).then(function (data) {
                var reqData = {TagsList: newTagsToSend, EntityID: EntityID, EntityType: 'ACTIVITY', TagType: 'ACTIVITY'};
                WallService.CallPostApi(appInfo.serviceUrl + 'tag/save', reqData, function (successResp) {
                    var response = successResp.data;
                    if ((response.ResponseCode == 200) && (response.Message == 'Success')) {
                        $scope.group_announcements[feedIndex]['EntityTags'] = angular.copy(newTags);
                        $scope.group_announcements[feedIndex]['editTags'] = angular.copy(newTags);
                        $scope.group_announcements[feedIndex]['showTags'] = false;
                        showResponseMessage('Tags updated successfully.', 'alert-success');
                    }
                }, function (error) {
                    showResponseMessage('Unable to update tags.', 'alert-success');
                });
            });
        };

        $scope.getActivityTags = function ($query) {
            if ($query) {
                var url = appInfo.serviceUrl + 'tag/get_entity_tags?SearchKeyword=' + $query + '&TagType=ACTIVITY&EntityType=ACTIVITY&PageNo=1&PageSize=20';
                if (IsAdminView == '1')
                {
                    url = appInfo.serviceUrl + 'api/tag/get_entity_tags?SearchKeyword=' + $query + '&TagType=ACTIVITY&EntityType=ACTIVITY&PageNo=1&PageSize=20';
                }
                return WallService.CallGetApi(url, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode === 200) {
                        var usreList = response.Data;
                        return usreList.filter(function (flist) {
                            return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                        });
                    }
                }, function (error) {
                    return [];
                });
            } else {
                return [];
            }
        };
//        Tagging End

        function RemoveCommentClass() {
            $('#' + $('#CommentGUID').val()).removeClass('comment-selected');
        }

        $scope.GetWallPostInit = function () {
            $scope.GetwallPost('', '', '', 1);
        }

        $scope.GetwallPostTime = function () {
            setTimeout(function () {
                $scope.GetwallPost('', '', '', 1);
            }, 1000);
        }

        $scope.allow_post_types = {'1': 'Discussion', '2': 'Q & A', '3': 'Polls', '4': 'Article', '5': 'Tasks & Lists', '6': 'Ideas', '7': 'Announcements'};

        $scope.getDefaultPostValue = function (landing_page)
        {
            var k = 0;
            angular.forEach($scope.allow_post_types, function (val, key) {
                if (val == landing_page)
                {
                    k = key;
                }
            });
            return k;
        }

        $scope.get_post_title = function (title, content)
        {
            var new_title = title;
            if (title == '')
            {
                content = content.replace(/<\/?[^>]+(>|$)/g, "");
                if (content.length > 0)
                {
                    if (content.length > 140)
                    {
                        new_title = content.substring(0, 140);
                    } else
                    {
                        new_title = content;
                    }
                }
            }
            return new_title;
        }

        $scope.showOptions = function (data, properyName) {
            data[properyName] = 1;
        }

        $scope.group_announcements = [];
        $scope.group_announcements_single = [];
        $scope.get_announcements = function ()
        {
            var reqData = {ModuleID: $('#module_id').val(), ModuleEntityID: $('#module_entity_id').val()};
            WallService.CallApi(reqData, 'activity/get_announcement').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        response.Data[key]['IsAnnnouncementWidget'] = 1;
                        response.Data[key]['append'] = 1;
                        response.Data[key]['Settings'] = Settings.getSettings();
                        response.Data[key]['ImageServerPath'] = Settings.getImageServerPath();
                        response.Data[key]['SiteURL'] = Settings.getSiteUrl();
                        response.Data[key]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                        response.Data[key]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                        response.Data[key]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        response.Data[key]['ReminderData'] = $scope.prepareReminderData(new Array());
                        response.Data[key]['showReminderOptions'] = 0;
                        response.Data[key].CollapsePostTitle = $scope.get_post_title(response.Data[key].PostTitle, response.Data[key].PostContent);
                        if (response.Data[key]['EntityTags'] && (response.Data[key]['EntityTags'].length > 0)) {
                            response.Data[key]['editTags'] = angular.copy(response.Data[key]['EntityTags']);
                            response.Data[key]['showTags'] = false;
                        } else {
                            response.Data[key]['EntityTags'] = [];
                            response.Data[key]['editTags'] = [];
                            response.Data[key]['showTags'] = false;
                        }
                    });

                    $scope.group_announcements = response.Data;
                    $scope.group_announcements.map(function (repo) {
                        repo.SuggestedFriendList = [];
                        repo.RquestedFriendList = [];
                        repo.SearchFriendList = '';
                        return repo;
                    });
                    $scope.group_announcements_single[0] = response.Data[0];
                }
            });
        }

        $scope.mark_as_feature = function (activity_guid, module_id, module_entity_id, data)
        {
            var reqData = {ModuleID: module_id, ModuleEntityID: module_entity_id, ActivityGUID: activity_guid};
            WallService.CallApi(reqData, 'activity/set_featured_post').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    if (!response.Data)
                    {
                        showResponseMessage(response.Message, 'alert-danger');
                    } else
                    {
                        data.IsFeatured = response.Data.IsFeatured;
                        showResponseMessage(response.Message, 'alert-success');
                    }
                }
            });
        }

        $scope.changeTagList = function (val)
        {
            $scope.addTagList = val;
        }

        $scope.remove_feature = function (activity_guid, module_id, module_entity_id, data)
        {
            $scope.mark_as_feature(activity_guid, module_id, module_entity_id, data);
        }

        $scope.hideAnnouncementFromWidget = function (activity_guid, removeForAll)
        {
            var reqData = {EntityGUID: activity_guid};
            if (removeForAll == '1')
            {
                reqData['RemoveForAll'] = 1;
            }
            WallService.CallApi(reqData, 'activity/hide_announcement').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach($scope.group_announcements, function (val, key) {
                        if (val.ActivityGUID == activity_guid)
                        {
                            $scope.group_announcements.splice(key, 1);
                        }
                    });
                }
            });
        }

        $scope.hideAnnouncement = function (activity_guid, removeForAll)
        {
            var reqData = {EntityGUID: activity_guid};
            if (removeForAll == '1')
            {
                reqData['RemoveForAll'] = 1;
            }
            WallService.CallApi(reqData, 'activity/hide_announcement').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == activity_guid)
                        {
                            NewsFeedCtrl.activityData[key].IsPined = 0;
                        }
                    });
                    if ($('#IsWiki').length > 0)
                    {
                        angular.forEach($scope.article_list, function (val, key) {
                            if (val.ActivityGUID == activity_guid)
                            {
                                $scope.article_list[key].IsPined = 0;
                            }
                        });

                        $scope.get_wiki_widget();
                    }
                    $scope.get_announcements();
                }
            });
        }

        $scope.announcementLimit = 0;
        $scope.getAnnouncementLimit = function (direction)
        {
            if (direction == 'Next')
            {
                if (parseInt($scope.announcementLimit) + 1 == $scope.group_announcements.length)
                {
                    $scope.announcementLimit = 0;
                } else
                {
                    $scope.announcementLimit++;
                }
            }
            if (direction == 'Prev')
            {
                if ($scope.announcementLimit == 0)
                {
                    $scope.announcementLimit = parseInt($scope.group_announcements.length) - 1;
                } else
                {
                    $scope.announcementLimit--;
                }
            }
            $scope.group_announcements_single[0] = $scope.group_announcements[$scope.announcementLimit];
        }

        $scope.checked_articles = [];
        $scope.checkUncheckArticle = function ()
        {
            $scope.checked_articles = [];
            $('.check-article:checked').each(function (e) {
                var article_id = $('.check-article:checked:eq(' + e + ')').attr('id');
                article_id = article_id.split('art-');
                $scope.checked_articles.push(article_id[1]);
            });
        }

        $scope.remove_recommended = function (activity_guid)
        {
            showConfirmBox("Remove Recommended", "Are you sure, you want to remove this article as recommended article ?", function (e) {
                if (e) {
                    var reqData = {Articles: activity_guid};
                    WallService.CallApi(reqData, 'activity/remove_recommended').then(function (response) {
                        if (response.ResponseCode == 200) {
                            angular.forEach($scope.article_list, function (v, k) {
                                if (v.ActivityGUID == activity_guid)
                                {
                                    $scope.article_list[k].IsRecommended = 0;
                                }
                            });
                            $scope.get_wiki_widget();
                        }
                    });
                }
            });
        }

        $scope.recommend_articles = function ()
        {
            showConfirmBox("Recommend Articles", "Are you sure, you want to recommend all selected articles ?", function (e) {
                if (e) {
                    var reqData = {Articles: $scope.checked_articles};
                    WallService.CallApi(reqData, 'activity/recommend_articles').then(function (response) {
                        if (response.ResponseCode == 200) {
                            //console.log($scope.checked_articles);
                            $($scope.article_list).each(function (key, val) {
                                //console.log($scope.article_list[key].ActivityGUID);
                                if (jQuery.inArray($scope.article_list[key].ActivityGUID, $scope.checked_articles) > -1) {
                                    $scope.article_list[key].IsRecommended = 1;
                                }
                            });
                            $scope.get_wiki_widget();
                            $('.wiki-listing-content').removeClass('selected');
                            $scope.checked_articles = [];
                            $('.check-article').attr('checked', false);
                        }
                    });
                }
            });
        }

        $scope.pin_articles = function (activity_guid)
        {
            showConfirmBox("Pin Articles", "Are you sure, you want to pin all selected articles ?", function (e) {
                if (e) {
                    var reqData = {Articles: $scope.checked_articles};
                    WallService.CallApi(reqData, 'activity/pin_articles').then(function (response) {
                        if (response.ResponseCode == 200) {
                            $('.wiki-listing-content').removeClass('selected');
                            $($scope.article_list).each(function (key, val) {
                                if (jQuery.inArray($scope.article_list[key].ActivityGUID, $scope.checked_articles) > -1) {
                                    $scope.article_list[key].IsPined = 1;
                                }
                            });
                            $scope.checked_articles = [];
                            $('.check-article').attr('checked', false);
                        }
                    });
                }
            });
        }

        $scope.remove_articles = function ()
        {
            
            
            showConfirmBox("Remove Articles", "Are you sure, you want to remove all selected articles ?", function (e) {
                if (e) {
                    var reqData = {Articles: $scope.checked_articles};
                    WallService.CallApi(reqData, 'activity/remove_articles').then(function (response) {
                        if (response.ResponseCode == 200) {
//                            $.each($scope.article_list, function (key, val) {
//                                if (jQuery.inArray($scope.article_list[key].ActivityGUID, $scope.checked_articles) > -1) {
//                                    $scope.article_list.splice(key, 1);
//                                }
//                            });
                                                        
                            for(var key in $scope.article_list) {
                                
                                var article = $scope.article_list[key];
                                
                                for(var removedKey in $scope.checked_articles) {
                                    
                                    var removedArticle = $scope.checked_articles[removedKey];
                                                                        
                                    if(removedArticle == article.ActivityGUID) {
                                        $scope.article_list.splice(key, 1);
                                    }
                                }
                            }
                            
                            
                            //$scope.get_wiki_widget();
                            $('.wiki-listing-content').removeClass('selected');
                            $scope.checked_articles = [];
                        }
                    });
                }
            });
        }

        $scope.subscribe_article_widget = function (EntityGUID) {
            var reqData = {
                EntityType: 'ACTIVITY',
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'subscribe/toggle_subscribe').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.article_list).each(function (key, val) {
                        if ($scope.article_list[key].ActivityGUID == EntityGUID) {
                            $scope.article_list[key].IsSubscribed = response.Data.IsSubscribed;
                            setTimeout(function () {
                                $('[data-toggle="tooltip"]').tooltip({
                                    container: "body"
                                });
                            }, 100);
                        }
                    });

                    angular.forEach($scope.widget_articles, function (val, key) {
                        angular.forEach(val.Data, function (v, k) {
                            if ($scope.widget_articles[key]['Data'][k].ActivityGUID == EntityGUID) {
                                $scope.widget_articles[key]['Data'][k].IsSubscribed = response.Data.IsSubscribed;
                                setTimeout(function () {
                                    $('[data-toggle="tooltip"]').tooltip({
                                        container: "body"
                                    });
                                }, 100);
                            }
                        });
                    });
                }
            });
        }

        $scope.subscribe_article = function (EntityGUID, article) {
            var reqData = {
                EntityType: 'ACTIVITY',
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'subscribe/toggle_subscribe').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.article_list).each(function (key, val) {
                        if ($scope.article_list[key].ActivityGUID == EntityGUID) {
                            $scope.article_list[key].IsSubscribed = response.Data.IsSubscribed;
                            setTimeout(function () {
                                $('[data-toggle="tooltip"]').tooltip({
                                    container: "body"
                                });
                            }, 100);
                        }
                    });

                    if (article) {
                        article.IsSubscribed = +response.Data.IsSubscribed;
                    }


                    $($scope.fav_article_list).each(function (key, val) {
                        if ($scope.fav_article_list[key].ActivityGUID == EntityGUID) {
                            $scope.fav_article_list[key].IsSubscribed = response.Data.IsSubscribed;
                            setTimeout(function () {
                                $('[data-toggle="tooltip"]').tooltip({
                                    container: "body"
                                });
                            }, 100);
                        }
                    });
                }
            });
        }

        $scope.slice_string = function (val, count)
        {
            return smart_substr(count, val);
        }

        $scope.stop_article_execution = 0;
        $scope.ISBusyArticleList = true;
        //$scope.article_list = [];
        $scope.show_loader = 0;
        $scope.onWikiPostTypeChangePageNo = 1;
        $scope.$on('onWikiPostTypeChange', function (evt, data) {
            var articleType = $('#ArticleType').val();
            $scope[articleType + 'execution'] = 0;
            $scope.SearchKey = '';
            if (data.SearchKeyword) {
                $scope.SearchKey = data.SearchKeyword;
                $('#srch-filters').val($scope.SearchKey);
            }


            $('#WallPageNo').val(1);
            $scope.WallPageNo = 1;
            $scope.article_list = [];
            $scope.onWikiPostTypeChangePageNo = 1;
            $scope.get_wiki_post('', articleType, $scope.onWikiPostTypeChangePageNo, 12);

        });

        $scope.$on('onWikiPostTypeLoadMore', function (evt, data) {
            var articleType = $('#ArticleType').val();

            if (!articleType) {
                return;
            }

            if ($scope[articleType + 'execution']) {
                return;
            }

            var lastPageNo = $('#WallPageNo').val();
            lastPageNo++;
            $('#WallPageNo').val(lastPageNo);
            $scope.WallPageNo = lastPageNo;
            //$scope.get_wiki_post();
            $scope.onWikiPostTypeChangePageNo++;
            $scope.get_wiki_post('', articleType, $scope.onWikiPostTypeChangePageNo, 12);

        });
        
        $scope.$on('onWikiPostAdmin', function (evt, data) {
            $scope.postEditormode = 1;
            $scope.ShowPreview = 0;
            $scope.IsNewsFeed = '1';
            $scope.edit_post = 0;
        });

        $scope.recommendedArticlePageNo = 1;
        $scope.$on('onWikiPostTypeRecommended', function (evt, data) {

            if ($scope['recommendedexecution']) {
                return;
            }
            
            if (data.reset) {
                $scope.SearchKey = '';
                if (data.SearchKeyword) {
                    $scope.SearchKey = data.SearchKeyword;
                    $('#srch-filters').val($scope.SearchKey);
                }

                $scope.recommendedArticlePageNo = 1;
                $scope.recommended_articles = [];
            } 
            

            if (data.showViewAll) {
                $scope.get_wiki_post('', 'recommended', 1, 5, data);
            } else {

                if (!data.reset) {
                    $scope.recommendedArticlePageNo++;
                } 

                $scope.get_wiki_post('', 'recommended', $scope.recommendedArticlePageNo, 10, data);
            }

        });

        $scope.get_wiki_post = function (activity_id, articleType, pageNo, PageSize, extraData)
        {
            $scope.show_loader = 1;
            $scope.ISBusyArticleList = true;
            $scope.viewLoader = true;
            $scope.EntityGUID = $('#module_entity_guid').val();
            $scope.ModuleID = $('#module_id').val();
            $scope.AllActivity = 0;
            $scope.ActivityGUID = 0;
            //$scope.SearchKey = $('#srch-filters').val();
            if ($('#AllActivity').length > 0) {
                $scope.AllActivity = $('#AllActivity').val();
            }
            $scope.PageNo = $scope.WallPageNo;
            if (($scope.ActivityGUID != '' && $scope.ActivityGUID != 0 && $scope.ActivityGUID != undefined) && isRefreshedFromSticky != 'Sticky')
            {
                $scope.IsSingleActivity = true;
            }
            var ActivityFilterType = $('#ActivityFilterType').val();
            if ($('#ActivityFilter').length > 0) {
                var ActivityFilterVal = $('#ActivityFilter').val();
                Filter = ActivityFilterVal.split(",");
                $scope.ActivityFilter = Filter;
            }
            var mentions = [];
            angular.forEach($scope.suggestPage, function (val, key) {
                mentions.push({
                    ModuleID: val.ModuleID,
                    ModuleEntityGUID: val.ModuleEntityGUID
                });
            });

            var post_by_looked_more = [];
            if ($('#postedby').val() == 'You')
            {
                var loginUserGUIDEle = $('#loginUserGUID');
                if (loginUserGUIDEle.length && loginUserGUIDEle.val()) {
                    post_by_looked_more.push(loginUserGUIDEle.val());
                } else {
                    post_by_looked_more.push(LoggedInUserGUID);
                }
            } else if ($('#postedby').val() == 'Anyone')
            {
                post_by_looked_more = [];
            } else
            {
                angular.forEach($scope.PostedByLookedMore, function (val, key) {
                    post_by_looked_more.push(val.UserGUID);
                });
            }

            var articleType = articleType || $('#ArticleType').val();
            var pageNo = pageNo || $('#WallPageNo').val();
            var PageSize = PageSize || 10;

            var reqData = {
                PageNo: pageNo,
                PageSize: PageSize,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                FeedSortBy: $('#FeedSortBy').val(),
                AllActivity: $scope.AllActivity,
                ActivityGUID: $scope.ActivityGUID,
                SearchKey: $scope.SearchKey,
                IsMediaExists: $('#IsMediaExists').val(),
                FeedUser: post_by_looked_more,
                StartDate: $('#datepicker').val(),
                EndDate: $('#datepicker2').val(),
                ActivityFilterType: ActivityFilterType,
                AsOwner: $('#AsOwner').val(),
                Mentions: mentions,
                ActivityFilter: $scope.ActivityFilter,
                CommentGUID: '',
                ViewEntityTags: 1,
                PostType: $scope.PostType,
                ArticleType: articleType,
                Tags: [],
                ShowFrom: $scope.entity_articles
            };

            if (activity_id)
            {
                reqData['ExcludeActivityID'] = activity_id;
            }

            angular.forEach($scope.search_tags, function (val, key) {
                reqData['Tags'].push(val.TagID);
            });

            //console.log($('#PostOwner').val());
            reqData.PollFilterType = ActivityFilterType;
            if ($scope.filter_expired != null && $scope.filter_expired != undefined) {
                reqData.expired = $scope.filter_expired;
            }
            if ($scope.filter_anonymous != null && $scope.filter_anonymous != undefined) {
                reqData.anonymous = $scope.filter_anonymous;
            }
            reqData.ShowArchiveOnly = 0;
            if ($scope.filter_archive != null && $scope.filter_archive != undefined && $scope.filter_archive != false) {
                reqData.ShowArchiveOnly = 1;
            }
            reqData.PollFilterType = ActivityFilterType;
            //  console.log(reqData.PollFilterType);
            if ($scope.IsReminder == 1) {
                reqData['ActivityFilterType'] = 3;
            }
            if (reqData['ActivityFilterType'] == 7) {
                $scope.ShowNewPost = 0;
            } else {
                $scope.ShowNewPost = 1;
            }
            reqData['ReminderFilterDate'] = $scope.ReminderFilterDate;
            if (reqData['StartDate']) {
                reqData['StartDate'] = $scope.TimeZonetoUTC(reqData['StartDate']);
            }
            if (reqData['EndDate']) {
                reqData['EndDate'] = $scope.TimeZonetoUTC(reqData['EndDate']);
            }

            if ($('#IsWiki').length == 0)
            {
                reqData['ModuleID'] = 3;
                reqData['ModuleEntityID'] = '';
            }

            // Check ajax request for article type
            if ($scope[articleType + 'execution'] == 1) {
                return;
            }

            if (articleType == 'recommended') {
                $scope.recommend_articles_loader = 1;
            } else {
                $scope.articles_loader = 1;
            }


            $scope[articleType + 'execution'] = 1;
            $scope.viewLoader = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/articles', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.viewLoader = false;

                    if (articleType == 'recommended') {
                        $scope.recommend_articles_loader = 0;
                        $scope[articleType + 'execution'] = 0;
                        var recommended_articles = response.Data;

                        if (extraData.showViewAll) {
                            $scope.recommended_articles_length = recommended_articles.length;
                            $scope.recommended_articles = Array.prototype.slice.call(recommended_articles, 0, 4);
                            return;
                        }

                        $scope.recommended_articles_length = 0;
                        $rootScope.$broadcast('onGetArticles', {articleList: response.Data});
                        $scope.recommended_articles = Array.prototype.concat.call($scope.recommended_articles, recommended_articles);

                        return;
                    }

                    $rootScope.$broadcast('onGetArticles', {articleList: response.Data});

                    $scope.articles_loader = 0;

                    if (!response.Data.length) {
                        $scope.blankScreen = true;
                    } else {
                        $scope.blankScreen = false;
                    }
                    $scope.is_first_time = 0;

                    if (typeof $scope.article_list == 'undefined')
                    {
                        $scope.article_list = [];
                    }
                    $scope[articleType + 'execution'] = 0;
                    angular.forEach(response.Data, function (v1, k1) {
                        var append = true;
                        angular.forEach($scope.article_list, function (v2, k2) {
                            if (v1.ActivityGUID == v2.ActivityGUID)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            $scope.article_list.push(v1);
                        }
                    });
                    if (response.Data.length == 0)
                    {
                        $scope[articleType + 'execution'] = 1;
                    }
                    if (!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                    $('.loader-fad').hide();
                    $('#WallPageNo').val(parseInt($('#WallPageNo').val()) + 1);
                    $scope.ISBusyArticleList = false;
                    $scope.show_loader = 0;
                }
                $scope.show_loader = 0;
            });

            $scope.show_loader = 0;
        }

        $scope.widget_articles = [];
        $scope.total_widget_articles = 0;
        $scope.get_wiki_widget = function ()
        {
            $scope.EntityGUID = $('#module_entity_guid').val();
            $scope.ModuleID = $('#module_id').val();
            $scope.AllActivity = 0;
            $scope.ActivityGUID = $('#ActivityGUID').val();
            $scope.SearchKey = $('#srch-filters').val();
            if ($('#AllActivity').length > 0) {
                $scope.AllActivity = $('#AllActivity').val();
            }

            reqData = {
                PageNo: 1,
                PageSize: 4,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                AllActivity: $scope.AllActivity,
                ActivityGUID: $scope.ActivityGUID
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'activity/article_widgets', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.widget_articles = response.Data;
                    $scope.total_widget_articles = response.TotalRecords;
                    if (!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                }
            });

        }

        $scope.fav_article_list = [];
        $scope.fav_total = 0;
        $scope.PageNoFav = 1;
        $scope.get_fav_wiki = function (page_size)
        {
            $scope.EntityGUID = $('#module_entity_guid').val();
            $scope.ModuleID = $('#module_id').val();
            $scope.AllActivity = 0;
            $scope.ActivityGUID = $('#ActivityGUID').val();
            $scope.SearchKey = $('#srch-filters').val();
            if ($('#AllActivity').length > 0) {
                $scope.AllActivity = $('#AllActivity').val();
            }

            reqData = {
                PageNo: $scope.PageNoFav,
                PageSize: page_size,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                AllActivity: $scope.AllActivity,
                ActivityGUID: $scope.ActivityGUID
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'activity/fav_articles', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.fav_article_list = response.Data;
                    $scope.fav_total = response.TotalRecords;
                    if (!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                }
            });

        }

        $scope.get_recommended_article = function ()
        {
            $scope.EntityGUID = $('#module_entity_guid').val();
            $scope.ModuleID = $('#module_id').val();
            $scope.AllActivity = 0;
            $scope.ActivityGUID = $('#ActivityGUID').val();
            $scope.SearchKey = $('#srch-filters').val();
            if ($('#AllActivity').length > 0) {
                $scope.AllActivity = $('#AllActivity').val();
            }

            reqData = {
                PageNo: $scope.PageNoFav,
                PageSize: 10,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                AllActivity: $scope.AllActivity,
                ActivityGUID: $scope.ActivityGUID
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'activity/recommended_articles', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (v1, k1) {
                        var append = true;
                        angular.forEach($scope.recommended_article_list, function (v2, k2) {
                            if (v1.ActivityGUID == v2.ActivityGUID)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            $scope.recommended_article_list.push(v1);
                        }
                    });
                    if (!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                }
            });

        }

        $scope.get_trending_widgets = function ()
        {
            $scope.EntityGUID = $('#module_entity_guid').val();
            $scope.ModuleID = $('#module_id').val();
            $scope.AllActivity = 0;
            $scope.ActivityGUID = $('#ActivityGUID').val();
            $scope.SearchKey = $('#srch-filters').val();
            if ($('#AllActivity').length > 0) {
                $scope.AllActivity = $('#AllActivity').val();
            }

            reqData = {
                PageNo: $scope.PageNoFav,
                PageSize: 5,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                AllActivity: $scope.AllActivity,
                ActivityGUID: $scope.ActivityGUID
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'activity/trending_widget', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (v1, k1) {
                        var append = true;
                        angular.forEach($scope.recommended_article_list, function (v2, k2) {
                            if (v1.ActivityGUID == v2.ActivityGUID)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            $scope.recommended_article_list.push(v1);
                        }
                    });
                    if (!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                }
            });

        }
        $scope.delete_article = function (activity_guid)
        {
            showConfirmBox("Delete Article", "Are you sure, you want to delete this article ?", function (e) {
                if (e) {
                    var reqData = {EntityGUID: activity_guid};
                    WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                        if (response.ResponseCode == 200) {
                            angular.forEach($scope.article_list, function (val, key) {
                                if (val.ActivityGUID == activity_guid)
                                {
                                    $scope.article_list.splice(key, 1);
                                }
                            });
                            $scope.get_wiki_widget();
                        }
                    });
                }
            });
        }

//        my-desk start
        $scope.verifyMyDeskFiltersStatus = function (key) {
            var change = false;
            if (key == 'All')
            {
                if ($scope.myDeskTabFilter[key] == 0)
                {
                    $scope.myDeskTabFilter = {WatchList: 1, Mention: 1, Reminder: 1, NotifyMe: 1, All: 1};
                    change = true;
                }
            } else
            {
                if ($scope.myDeskTabFilter['All'] == 1)
                {
                    $scope.myDeskTabFilter = {WatchList: 0, Mention: 0, Reminder: 0, NotifyMe: 0, All: 0};
                    $scope.myDeskTabFilter[key] = 1;
                    change = true;
                } else
                {
                    if ($scope.myDeskTabFilter[key] == 1)
                    {
                        $scope.myDeskTabFilter[key] = 0;
                        change = true;
                    } else
                    {
                        $scope.myDeskTabFilter[key] = 1;
                        change = true;
                    }
                }
            }
            if (change)
            {

            }
            $scope.WallPageNo = 1;
            $scope.busy = 0;
            $scope.stopExecution = 0;
            $scope.applyMyDeskFilter();

//          console.log('$scope.myDeskTabFilter : ', $scope.myDeskTabFilter);

        };

        $scope.verifyAllMyDeskFiltersStatus = function () {
//          var checkedCount = 0;
            angular.forEach($scope.myDeskTabFilter, function (filterStatus, filterKey) {
                if ($scope.myDeskTabFilter['All'] === 1) {
                    $scope.myDeskTabFilter[filterKey] = (filterKey != 'Favourite') ? 1 : 0;
                } else {
                    $scope.myDeskTabFilter[filterKey] = 0;
                }
            });
            if ($scope.myDeskTabFilter['All'] === 1) {
                $scope.checkedMyDeskFiltersCount = (Object.keys($scope.myDeskTabFilter).length - 1);
            } else {
                $scope.checkedMyDeskFiltersCount = 0;
            }
//          console.log('$scope.myDeskTabFilter : ', $scope.myDeskTabFilter);
            $scope.applyMyDeskFilter();
        };

        $scope.setFilterValues = function () {
            if (Object.keys($scope.myDeskTabFilter).length === 0) {
                $scope.IsFirstMyDesk = true;
                if (webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID)) {
                    $scope.myDeskTabFilter = angular.copy(webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID));

                    angular.forEach($scope.myDeskTabFilter, function (val, key) {
                        if (key === undefined || key === "undefined") {
                            delete $scope.myDeskTabFilter[key];
                        }
                    });

                    if (typeof $scope.myDeskTabFilter.Favourite != undefined) {
                        delete $scope.myDeskTabFilter.Favourite
                    }
                } else {
                    $scope.myDeskTabFilter = angular.copy(defualtMyDeskTabFilter);
                }
                webStorage.setStorageData('defualtMyDeskTabFilter' + LoginGUID, $scope.myDeskTabFilter);
            }
        };

        $scope.resetFilterValues = function () {
            $scope.checkedMyDeskFiltersCount = 0;
            if (!$scope.IsMyDeskTab) {
                $scope.IsFirstMyDesk = false;
                $scope.myDeskTabFilter = {};
                $scope.WallPageNo = 1;
                $scope.stopExecution = 0;
                $scope.busy = false;
                $scope.GetwallPost(false, false, false);
            } else {
                $scope.IsFirstMyDesk = true;
                if (webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID)) {
                    $scope.myDeskTabFilter = angular.copy(webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID));

                    angular.forEach($scope.myDeskTabFilter, function (val, key) {
                        if (key === undefined || key === "undefined") {
                            delete $scope.myDeskTabFilter[key];
                        }
                    });

                    if (typeof $scope.myDeskTabFilter.Favourite != undefined) {
                        delete $scope.myDeskTabFilter.Favourite
                    }
                } else {
                    $scope.myDeskTabFilter = angular.copy(defualtMyDeskTabFilter);
                }
                webStorage.setStorageData('defualtMyDeskTabFilter' + LoginGUID, $scope.myDeskTabFilter);
//            $scope.myDeskTabFilter['All'] = 1;
                $scope.verifyMyDeskFiltersStatus();
                if ($(window).width() > 767) {
                    $('[data-scrollFix="scrollFix"]').scrollFix({
                        fixTop: 60
                    });
                }
            }
            //angular.element(document.getElementById('UserProfileCtrl')).scope().IsMyDeskTab = $scope.IsMyDeskTab;
        };

        $scope.applyMyDeskFilter = function () {
            $scope.IsMyDeskTab = true;
            $scope.IsCurrentPage = 'NewsFeed';
            $scope.IsReminder = 0;
            if (Object.keys($scope.myDeskTabFilter).length === 0) {
                $scope.IsFirstMyDesk = true;
                if (webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID)) {
                    $scope.myDeskTabFilter = angular.copy(webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID));

                    angular.forEach($scope.myDeskTabFilter, function (val, key) {
                        if (key === undefined || key === "undefined") {
                            delete $scope.myDeskTabFilter[key];
                        }
                    });

                    if (typeof $scope.myDeskTabFilter.Favourite != undefined) {
                        delete $scope.myDeskTabFilter.Favourite
                    }
                } else {
                    $scope.myDeskTabFilter = angular.copy(defualtMyDeskTabFilter);
                }
            }
            webStorage.setStorageData('defualtMyDeskTabFilter' + LoginGUID, $scope.myDeskTabFilter);
            $scope.GetwallPost(false, false, false);
        };

//        my-desk end
        $scope.IsFirstCall = 1;
        $scope.IsSingleActivity = false;
        var ContentSearchRequestData;
        var ContentSearchParamUpdated = 0;
        // Wall function starts 

        $scope.view_more = 1;
        $scope.view_more_limit = 11;

        $scope.update_view_more = function (val, limit)
        {
            $scope.view_more = val;
            $scope.view_more_limit = limit;
        }

        $scope.GetwallPost = function (ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload, isInit) {
            function getNewsFeedCtrl() {
                var NewsFeedCtrl = document.getElementById('NewsFeedCtrl');
                if (!NewsFeedCtrl) {
                    return NewsFeedCtrl;
                }

                NewsFeedCtrl = angular.element(NewsFeedCtrl);

                if (!NewsFeedCtrl.length) {
                    return NewsFeedCtrl;
                }

                return NewsFeedCtrl.scope();
            }


            NewsFeedCtrl = getNewsFeedCtrl();
            if (NewsFeedCtrl === null && !isInit) {
                return;
            }

            var interval = null;
            if (!NewsFeedCtrl || !('applyDigestCycle' in NewsFeedCtrl)) {
                interval = window.setInterval(function () {
                    NewsFeedCtrl = getNewsFeedCtrl();
                    if (!NewsFeedCtrl || !('applyDigestCycle' in NewsFeedCtrl)) {
                        return;
                    }

                    clearInterval(interval);
                    if (isInit) {
                        NewsFeedCtrl.GetWallPostInit(ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload);
                    } else {
                        NewsFeedCtrl.GetwallPost(ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload);
                    }

                }, 200);

                return;
            }

            if (isInit) {
                NewsFeedCtrl.GetWallPostInit(ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload);
            } else {
                NewsFeedCtrl.GetwallPost(ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload);
            }
        }

        function logActivity() {
            var jsonData = {
                EntityType: 'Activity'
            };

            if (LoginSessionKey == '')
            {
                return false;
            }

            $.post(base_url + 'api/log/log_activity', jsonData, function (response) {});

            //WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });
        }

        $scope.logActivity = logActivity;

        $scope.showLoginPopup = function ()
        {
            if ($('#beforeLogin').length > 0)
            {
                showLoginPopup();
            } else
            {
                showConfirmBoxLogin('Login Required', 'Please login to perform this action.', function (e) {
                    if (e) {
                        setTimeout(function () {
                            $('#usernameCtrlID').focus();
                        }, 200);
                    }
                });
            }
        }

        $scope.clearReminderFilter = function (d) {
            if (typeof d !== 'undefined') {
                angular.forEach($scope.ReminderFilterDate, function (val, key) {
                    if (val == d) {
                        $scope.ReminderFilterDate.splice(key, 1);
                    }
                });
            } else {
                $scope.ReminderFilterDate = [];
            }
            if ($scope.ReminderFilterDate.length == 0) {
                $scope.ReminderFilter = 0;
            }
            $scope.getFilteredWall();
        }


        $scope.getTemplateUrl = function (data, is_popular) {
            //var partialURL = base_url + 'assets/partials/wall/';
            var partialURL = AssetBaseUrl + 'partials/wall/';
            var ViewTemplate = data.ViewTemplate;
            var ShowPoll = 0;
            if (typeof data.PollData !== 'undefined') {
                if (data.PollData.length > 0) {
                    ShowPoll = 1;
                }
            }
            //console.log(ViewTemplate);
            if (ViewTemplate == 'SuggestedGroups' || ViewTemplate == 'SuggestedPages' || ViewTemplate == 'UpcomingEvents') {
                return partialURL + 'activity/' + ViewTemplate + '.html' + $scope.app_version;
            } else if (data.PostType == '8')
            {
                return partialURL + 'activity/VisualPost.html' + $scope.app_version;
            } else if (data.PostType == '9')
            {
                if (data.ActivityType == 'ContestJoined')
                {
                    return partialURL + 'activity/ContestJoined.html' + $scope.app_version;
                } else
                {
                    return partialURL + 'activity/Contest.html' + $scope.app_version;
                }
            } else if (ViewTemplate == 'Poll' || ShowPoll == '1') {
                return partialURL + 'PollMain.html' + $scope.app_version;
            } else {
                if (is_popular == '1')
                {

                    return partialURL + 'PopularFeed.html' + $scope.app_version;
                } else
                {
                    if (data.PostType == '4' && data.IsSingleActivity == '1')
                    {
                        return partialURL + 'ArticleDetails.html' + $scope.app_version;
                    } else
                    {
                        return partialURL + 'NewsFeed.html' + $scope.app_version;
                    }
                }
            }
        };

        $scope.isDiscussionPost = 0;

        $scope.get_popular_post = function ()
        {
            $scope.isDiscussionPost = 1;
            var reqData = {Limit: 2, ModuleID: 1};
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/get_popular_feeds', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    NewsFeedCtrl.activityData = response.Data;
                    $scope.LoggedInName = response.LoggedInName;
                    $scope.LoggedInProfilePicture = response.LoggedInProfilePicture;
                    NewsFeedCtrl.activityData.map(function (repo) {
                        repo.SuggestedFriendList = [];
                        repo.RquestedFriendList = [];
                        repo.SearchFriendList = '';
                        return repo;
                    });
                }


            }, function (error) {

                // showResponseMessage('Something went wrong.', 'alert-danger');
            });


        }


        $scope.wallRepeatDone = function () {
            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
                $('[data-reminder="close"]').dropdown('toggle');
                cardTooltip();
                if (!$scope.isInit) {
                    ;
                    $scope.isInit = true;
                }
//                $('.inview').each(function (k, v) {
//                    if ($('.inview:eq(' + k + ')').isOnScreen()) {
//                        var EntityGUID = $('.inview:eq(' + k + ')').attr('id');
//                        EntityGUID = EntityGUID.split('act-')[1];
//                        $scope.showMediaFigure(EntityGUID);
//                    }
//                });

                if (LoginSessionKey !== '')
                {
                    //stButtons.makeButtons();
                }
            }, 1000);
        }
        $scope.showMediaFigure = function (EntityGUID) {
            var data = [];
            data['showMedia'] = 1;
            $scope.updateActivityData(EntityGUID, data);
            if (IsNewsFeed == '1')
            {
                $scope.updatePopularData(EntityGUID, data);
            }
            if (!$scope.$$phase) {
                $scope.$apply();
            }
        }
        $scope.updateActivityData = function (activity_guid, data) {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == activity_guid) {
                    for (k in data) {
                        NewsFeedCtrl.activityData[key][k] = data[k];
                    }
                }
            });
        }

        $scope.updatePopularData = function (activity_guid, data) {
            angular.forEach($scope.popularData, function (val, key) {
                if (val.ActivityGUID == activity_guid) {
                    for (k in data) {
                        $scope.popularData[key][k] = data[k];
                    }
                }
            });
        }
        $scope.enableDisableMin = function (data, activity_guid) {
            setTimeout(function () {

                var d = new Date();
                var n = d.getMinutes();
                var today = new Date();
                today.setHours(0);
                today.setMinutes(0);
                today.setSeconds(0);
                $('ul.minutes input').removeAttr('disabled');
                $('ul.minutes span').removeClass('disabled');
                var CalendarPicker = $('[data-time-activityGUID="' + activity_guid + '"]');
                var AmPm = CalendarPicker.children().find(' span.selected input').val();
                var Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
                var Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();
                var month = ("0" + (today.getMonth() + 1)).slice(-2);
                if ($scope.selectedDate[activity_guid] == today.getFullYear() + '-' + month + '-' + today.getDate()) {
                    var candisable = false;
                    if (Hours == d.getHours()) {
                        if (Hours > 12) {
                            if (AmPm == 'pm') {
                                candisable = true;
                            }
                        } else {
                            if (AmPm == 'am') {
                                candisable = true;
                            }
                        }
                    }
                    if (candisable) {
                        for (var k = 0; k <= 3; k++) {
                            if (15 * k <= n) {
                                $('ul.minutes input:eq(' + k + ')').attr('disabled');
                                $('ul.minutes span:eq(' + k + ')').addClass('disabled');
                            }
                        }
                    }
                }
            }, 200);
        }
        $scope.getActivityData = function (activity_guid, field) {
            var data;
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == activity_guid) {
                    if (field) {
                        data = NewsFeedCtrl.activityData[key][field];
                    } else {
                        data = NewsFeedCtrl.activityData[key];
                    }
                }
            });
            return data;
        }

        $scope.getPopularData = function (activity_guid, field) {
            var data;
            angular.forEach($scope.popularData, function (val, key) {
                if (val.ActivityGUID == activity_guid) {
                    if (field) {
                        data = $scope.popularData[key][field];
                    } else {
                        data = $scope.popularData[key];
                    }
                }
            });
            return data;
        }
        $scope.prepareReminderData = function (ReminderData, IsLocal) {

            ReminderDateTime = ReminderData.ReminderDateTime;
            if (IsLocal != undefined) {
                datetime = new Date(ReminderDateTime.replace(/-/gi, ' '));
                utcDateTime = moment(datetime).format('YYYY-MM-DD HH:mm:ss');
                Hour = moment(datetime).format('h');
                Minutes = moment(datetime).format('m');
                displayDate = moment(datetime).format('YYYY-MM-DD hh:mm:ss');
                displayHour = moment(datetime).format('hh');
                UndoDateTime = moment(datetime).format('YYYY-MM-DD h:mm:ss A');
                editDate = moment(datetime).format('YYYY-MM-DD');
                MonthName = moment(datetime).format('MMM');
                ReminderDay = moment(datetime).format('DD');
                EditPopupDate = moment(datetime).format('ddd, DD MMM, hh:mm A');
                Meridian = moment(datetime).format('a');
            } else {
                localTime = moment.utc(ReminderDateTime).toDate();
                utcDateTime = moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
                Hour = moment.tz(localTime, TimeZone).format('h');
                Minutes = moment.tz(localTime, TimeZone).format('m');
                displayDate = moment.tz(localTime, TimeZone).format('YYYY-MM-DD hh:mm:ss');
                displayHour = moment.tz(localTime, TimeZone).format('hh');
                UndoDateTime = moment.tz(localTime, TimeZone).format('YYYY-MM-DD h:mm:ss A');
                editDate = moment.tz(localTime, TimeZone).format('YYYY-MM-DD');
                MonthName = moment.tz(localTime, TimeZone).format('MMM');
                EditPopupDate = moment.tz(localTime, TimeZone).format('ddd, DD MMM, hh:mm A');
                ReminderDay = moment.tz(localTime, TimeZone).format('DD');
                Meridian = moment.tz(localTime, TimeZone).format('a');
            }
            Reminder = {
                ReminderGUID: ReminderData.ReminderGUID ? ReminderData.ReminderGUID : '',
                ReminderDateTime: ReminderData.ReminderGUID ? utcDateTime : '',
                ReminderEditDateTime: editDate,
                Hour: ReminderData.ReminderGUID ? displayHour : '',
                Minutes: ReminderData.ReminderGUID ? Minutes : '',
                Meridian: ReminderData.ReminderGUID ? Meridian : '',
                ServerDateTime: ReminderData.ReminderGUID ? ReminderData.ReminderDateTime : '',
                MonthName: ReminderData.ReminderGUID ? MonthName : '',
                ReminderDay: ReminderData.ReminderGUID ? ReminderDay : '',
                EditPopupDate: ReminderData.ReminderGUID ? EditPopupDate : '',
                UndoDateTime: ReminderData.ReminderGUID ? UndoDateTime : '',
                SelectedClass: ReminderData.ReminderGUID ? 'selected reminderSet' : 'selected',
            }
            return Reminder;
        }

        //        wall files upload 

        $scope.validateFileSize = function (file) {
            var defer = $q.defer();
            var isResolvedToFalse = false;
            var mediaPatt = new RegExp("^image|video");
            var videoPatt = new RegExp("^video");
            if (videoPatt.test(file.type)) {
                if (file.size > 41943040) { // if video size > 41943040 Bytes = 40 Mb
                    file.$error = 'size';
                    file.$error = 'Size Error';
                    file.$errorMessages = file.name + ' is too large. Please upload below 40 MB';
                    defer.resolve(false);
                    isResolvedToFalse = true;
                }
            } else {
                if (file.size > 4194304) { // if image/document size > 4194304 Bytes = 4 Mb
                    file.$error = 'size';
                    file.$error = 'Size Error';
                    file.$errorMessages = file.name + ' is too large. Please upload below 4 MB';
                    defer.resolve(false);
                    isResolvedToFalse = true;
                }
            }

            if (!isResolvedToFalse) {
                defer.resolve(true);
            }
            return defer.promise;
        }

        $scope.mediaInputIndex = '';
        $scope.isWallAttachementUploading = false;
        $scope.noContentToPost = true;
        $scope.medias = {};
        $scope.edit_medias = {};
        $scope.mediaCount = 0;
        $scope.files = {};
        $scope.edit_files = {};
        $scope.fileCount = 0;
        $scope.saySomthingAboutMedia = {ALL: ''};
        var wallMediaCurrentIndex = 0;
        var wallFileCurrentIndex = 0;
        $scope.visualPostStyle = {};
        $scope.visualPostImage = 0;
                
        
        $scope.uploadWallFiles = function (files, errFiles) {
//          $scope.errFiles = errFiles;
            var promises = [];
            if (files && files.length) {
                var patt = new RegExp("^image|audio|video");
                var videoPatt = new RegExp("^video");
                $scope.isWallAttachementUploading = true;
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, mediaIndex, fileIndex) {
                        WallService.setFileMetaData(file);
                        var paramsToBeSent = {
                            Type: 'wall',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        var fileType = 'media';

                        if(settings_data.m40==0)
                        {
                            if(!patt.test(file.type))
                            {
                                showResponseMessage('Only images and video type files are allowed.', 'alert-danger');
                                return false;
                            }
                        }

                        if (patt.test(file.type)) {
                            $scope.medias['media-' + mediaIndex] = file;
                            $scope.mediaCount = Object.keys($scope.medias).length + Object.keys($scope.edit_medias).length;
                        } else {
                            $scope.files['file-' + fileIndex] = file;
                            $scope.fileCount = Object.keys($scope.files).length + Object.keys($scope.edit_files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';

                        }

                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        if (IsAdminView == '1')
                        {
                            url = (videoPatt.test(file.type)) ? 'adminupload_video' : 'adminupload_image';
                        }
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {

                                    WallService.FileUploadProgress({fileType: fileType, scopeObj: $scope, fileIndex: fileIndex, mediaIndex: mediaIndex}, {}, response);

                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.mediaInputIndex = 'ALL';
                                            $scope.medias['media-' + mediaIndex].progress = true;
                                            $scope.visualPostStyle = {'background': 'url(' + response.data.Data.ImageServerPath + '/' + response.data.Data.ImageName + ') no-repeat 0 0', 'background-size': 'cover'};
                                            $scope.visualPostImage = 1;
                                            if (!$scope.$$phase)
                                            {
                                                $scope.$apply();
                                            }
                                        } else {
                                            delete $scope.medias['media-' + mediaIndex];
                                            $scope.mediaCount = Object.keys($scope.medias).length + Object.keys($scope.edit_medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.files['file-' + fileIndex].progress = true;
                                        } else {
                                            delete $scope.files['file-' + fileIndex];
                                            $scope.fileCount = Object.keys($scope.files).length + Object.keys($scope.edit_files).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }
                                    
                                    runOnAllSumernoteInstances($scope.onSummerNoteChange);

                                },
                                function (response) {

                                    if (fileType === 'media') {
                                        delete $scope.medias['media-' + mediaIndex];
                                        $scope.mediaCount = Object.keys($scope.medias).length;
                                    } else {
                                        delete $scope.files['file-' + fileIndex];
                                        $scope.fileCount = Object.keys($scope.files).length;
                                    }
                                },
                                function (evt) {

                                    WallService.FileUploadProgress({fileType: fileType, scopeObj: $scope, fileIndex: fileIndex, mediaIndex: mediaIndex}, evt);
                                });

                        promises.push(promise);

                    })(fileToUpload, wallMediaCurrentIndex, wallFileCurrentIndex);
                    if (patt.test(fileToUpload.type)) {
                        wallMediaCurrentIndex++;
                    } else {
                        wallFileCurrentIndex++;
                    }
                });
                $q.all(promises).then(function (data) {
                    $scope.isWallAttachementUploading = false;
                    $scope.noContentToPost = false;
                });
            } else {
                if (errFiles[0].$error == 'minWidth')
                {
                    showResponseMessage('Minimum width should be ' + errFiles[0].$errorParam, 'alert-danger');
                } else if (errFiles[0].$error == 'minHeight')
                {
                    showResponseMessage('Minimum width should be ' + errFiles[0].$errorParam, 'alert-danger');
                } else
                {
                    var msg = '';
                    angular.forEach(errFiles, function (errFile, key) {
                        msg += '\n' + errFile.$errorMessages;
                        promises.push(makeResolvedPromise(msg));
                    });
                    $q.all(promises).then(function (data) {
                        showResponseMessage(msg, 'alert-danger');
                    });
                }
            }
        };

        $scope.setSaySomethingAboutMedia = function (index) {
            if (!$scope.saySomthingAboutMedia[index]) {
                $scope.saySomthingAboutMedia[index] = '';
            }
            $scope.mediaInputIndex = index;
//          angular.element('#mc-default').focus();
        };

        $scope.removeWallAttachement = function (type, mediaKey, MediaGUID) {
            if ($scope.activePostType != '8' && $scope.activePostType != '9')
            {
                var PostContent = $('#PostContent').val().trim();
            } else
            {
                var PostContent = '';
            }
            if ((type === 'file') && Object.keys($scope.files).length) {
                delete $scope.files[mediaKey];
                $scope.fileCount = Object.keys($scope.files).length + Object.keys($scope.edit_files).length;
            } else if ((type === 'edit_file') && Object.keys($scope.edit_files).length)
            {
                delete $scope.edit_files[mediaKey];
                $scope.fileCount = Object.keys($scope.files).length + Object.keys($scope.edit_files).length;
            } else if ((type === 'media') && Object.keys($scope.medias).length)
            {
                var mediaLength = Object.keys($scope.medias).length + Object.keys($scope.edit_medias).length;
                delete $scope.saySomthingAboutMedia[MediaGUID];
                if (mediaLength < 2) {
                    $scope.saySomthingAboutMedia['ALL'] = '';
                }
                delete $scope.medias[mediaKey];
                $scope.mediaCount = Object.keys($scope.medias).length;
                if (mediaLength) {
                    var lastKey = 'ALL' // "carrot"
                    angular.forEach($scope.medias, function (val, key) {
                        lastKey = key;
                    });
                    $scope.mediaInputIndex = ($scope.medias[lastKey]) ? $scope.medias[lastKey].data.MediaGUID : 'ALL';
                }
            } else if (Object.keys($scope.edit_medias).length) {
                var mediaLength = Object.keys($scope.edit_medias).length;
                delete $scope.saySomthingAboutMedia[MediaGUID];
                if (mediaLength < 2) {
                    $scope.saySomthingAboutMedia['ALL'] = '';
                }
                angular.forEach($scope.edit_medias, function (val, key) {
                    if (val.data.MediaGUID == MediaGUID)
                    {
                        $scope.edit_medias.splice(key, 1);
                    }
                });
                $scope.mediaCount = Object.keys($scope.medias).length + Object.keys($scope.edit_medias).length;
                if (mediaLength) {
                    var lastKey = $scope.edit_medias[Object.keys($scope.edit_medias)[mediaLength - 1]] // "carrot"
                    $scope.mediaInputIndex = ($scope.edit_medias[lastKey]) ? $scope.edit_medias[lastKey].data.MediaGUID : 'ALL';
                }
            }


            if (($scope.fileCount === 0) && ($scope.mediaCount === 0) && !PostContent) {
                $scope.noContentToPost = true;
            }
            $scope.visualPostStyle = {};
            $scope.visualPostImage = 0;
        };

//        wall files upload 

        $scope.mark_best_answer = function (activity_guid, comment_guid)
        {
            var reqData = {ActivityGUID: activity_guid, CommentGUID: comment_guid};
            WallService.CallApi(reqData, 'activity/mark_best_answer').then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == activity_guid)
                        {
                            angular.forEach(NewsFeedCtrl.activityData[key].Comments, function (v, k) {
                                if (v.CommentGUID == comment_guid)
                                {
                                    NewsFeedCtrl.activityData[key].Comments[k].BestAnswer = 1;
                                } else
                                {
                                    NewsFeedCtrl.activityData[key].Comments[k].BestAnswer = 0;
                                }
                            });
                        }
                    });
                }
            });
        }

        $scope.isFriend = function () {
            var isFriendEle = $('#IsFriend');
            if (isFriendEle.length == 0) {
                return true;
            }

            if (isFriendEle.val() == 1) {
                return true;
            }

            return false;
        }

        $scope.editPostAnn = function (activity_guid, event)
        {

            $scope.overlayShow = true;
            $scope.edit_post = true;
            //var postContent = $(".note-editable").text().trim();
            $scope.getSingleActivityAnn(activity_guid);
            var post_details = angular.copy($scope.singleActivity);
            $scope.edit_post_details = post_details;
            $scope.showPostEditor();
            var topreduce = 160;
            if ($('#IsForum').length > 0)
            {
                var topreduce = 160 + parseInt($('#CategoryDetails').height());
            }
            var top = $('.feed-act-' + activity_guid).offset().top - topreduce;
            setTimeout(function () {
                if (post_details.PostContent !== '')
                {
                    $(".note-placeholder").hide();
                }
                $(".note-editable").html(post_details.EditPostContent);
                $scope.PostContent = post_details.EditPostContent;
                $('#PostTitleInput').val(post_details.PostTitle);


                $('.post-editor').css('top', top + 'px');
                $('.post-preview').css('top', top + 'px');
                //$('.post-type-block').css('top',parseInt(top)+40+'px');
                angular.forEach(post_details.EntityMembers, function (val, key) {
                    if (val.UserGUID !== LoggedInUserGUID)
                    {
                        val['name'] = val.FirstName + ' ' + val.LastName;
                        $scope.tagsto.push(val);
                        $scope.group_user_tags.push(val);
                    }
                });

                $scope.edit_medias = [];
                $scope.edit_files = [];
                $scope.mediaCount = 0;
                $scope.fileCount = 0;

                angular.forEach(post_details.mediaData, function (val, key) {
                    val['data'] = post_details.mediaData[key];
                    val['data']['ImageServerPath'] = image_server_path + 'upload/wall';
                    val['progress'] = true;
                    if (val['data'].MediaType == 'Image')
                    {
                        val['data'].MediaType = 'PHOTO';
                    }
                    $scope.edit_medias.push(val);
                });
                angular.forEach(post_details.Files, function (val, key) {
                    val['data'] = post_details.Files[key];
                    val['progress'] = true;
                    $scope.edit_files.push(val);
                });

                $scope.mediaCount = $scope.edit_medias.length;
                $scope.fileCount = $scope.edit_files.length;
                $('#EditActivityGUID').val(activity_guid);
                $scope.postTagList = post_details.editTags;

                if (post_details.PostContent || $scope.fileCount > 0 || $scope.mediaCount) {
                    $scope.noContentToPost = false;
                } else {
                    $scope.noContentToPost = true;
                }
                $scope.activePostType = post_details.PostType;
                $scope.postInGroup = (post_details.ModuleID == 1) ? true : false;
                $scope.selectedPrivacy = post_details.Visibility;
                angular.element('#visible_for').val(post_details.Visibility);
                if (!$scope.$$phase)
                {
                    $scope.$apply();
                }

                $scope.showNewsFeedPopup();

            }, 100);



        }

        $scope.setpostasgroup = function (val)
        {
            $scope.postasgroup = val;
            $scope.PostAs = val;
        }

        $scope.toggle_comment_allowed = function ()
        {
            if ($scope.edit_post_details.CommentsAllowed == '1')
            {
                $scope.edit_post_details.CommentsAllowed = '0';
            } else
            {
                $scope.edit_post_details.CommentsAllowed = '1';
            }
        }

        $scope.change_visibility_settings = function (val)
        {
            $scope.edit_post_details.Visibility = val;
        }

        $scope.postasgroup = [];
        $scope.postInGroup = false;
        $scope.edit_post_details = [];
        $scope.editPost = function (activity_guid, event, isAnnouncement)
        {
            $scope.postEditormode = 1;
            $scope.resetPrivacySettings();
            $scope.overlayShow = true;
            $scope.edit_post = true;
            //var postContent = $(".note-editable").text().trim();
            if (isAnnouncement) {
                $scope.getSingleAnnouncement(activity_guid);
            } else {
                $scope.getSingleActivity(activity_guid);
            }



            var post_details = $scope.singleActivity;
            $scope.edit_post_details = post_details;
            if($scope.edit_post_details.OriginalActivityType == 'Share' || $scope.edit_post_details.OriginalActivityType == 'ShareMedia' || $scope.edit_post_details.OriginalActivityType == 'ShareMediaSelf' || $scope.edit_post_details.OriginalActivityType == 'ShareSelf')
            {
                $scope.shareEmit($scope.edit_post_details.ActivityGUID,'shareEmit');
                elementLoaded('#PCnt',function(el){
                    $(el).val($scope.edit_post_details.OriginalPostContent);
                });
                elementLoaded('#ShareActivityGUID',function(el){
                    $(el).val($scope.edit_post_details.OriginalActivityGUID);
                });
            }
            else
            {
                if(post_details.PostType == '4' && settings_data.m40 == '0')
                {
                    window.top.location = site_url+'user_profile/post_article/'+post_details.ModuleID+'/'+post_details.ModuleEntityGUID+'/'+post_details.ActivityGUID;
                }
                else
                {
                    if (IsAdminView == '1')
                    {
                        var user_scope_ctrl = angular.element(document.getElementById('UserListCtrl')).scope();
                        angular.forEach(user_scope_ctrl.users, function (val, key) {
                            if (val.UserGUID == post_details.UserGUID)
                            {
                                $scope.setpostasuser(val);
                            }
                        });
                    }

                    if ($scope.edit_post_details.Album.length > 0)
                    {
                        angular.forEach($scope.edit_post_details.Album[0].Media, function (val, key) {
                            $scope.saySomthingAboutMedia[val.MediaGUID] = val.Caption;
                        });
                    }

                    $scope.showPostEditor();
                    var topreduce = 160;
                    if ($('#IsForum').length > 0)
                    {
                        var topreduce = 160 + parseInt($('#CategoryDetails').height());
                    }
                    var top = $('.feed-act-' + activity_guid).offset().top - topreduce;
                    setTimeout(function () {
                        if (post_details.PostContent !== '')
                        {
                            $(".note-placeholder").hide();
                        }
                        $(".note-editable").html(post_details.EditPostContent);
                        $scope.PostContent = post_details.EditPostContent;
                        $('#PostTitleInput').val(post_details.PostTitle);
                        var char = (post_details.PostTitle) ? 140 - post_details.PostTitle.length : 140;
                        if (char == 1)
                        {
                            $('#PostTitleLimit').html('1 character');
                        } else
                        {
                            $('#PostTitleLimit').html(char + ' characters');
                        }

                        $('.post-editor').css('top', top + 'px');
                        $('.post-preview').css('top', top + 'px');
                        //$('.post-type-block').css('top',parseInt(top)+40+'px');
                        angular.forEach(post_details.EntityMembers, function (val, key) {
                            if (val.ModuleEntityGUID !== LoggedInUserGUID)
                            {
                                val['name'] = val.FirstName + ' ' + val.LastName;
                                $scope.tagsto.push(val);
                                $scope.group_user_tags.push(val);
                            }
                        });

                        $scope.edit_medias = [];
                        $scope.edit_files = [];
                        $scope.mediaCount = 0;
                        $scope.fileCount = 0;

                        angular.forEach(post_details.mediaData, function (val, key) {
                            val['data'] = post_details.mediaData[key];
                            val['data']['ImageServerPath'] = image_server_path + 'upload/wall';
                            val['progress'] = true;
                            if (val['data'].MediaType == 'Image')
                            {
                                val['data'].MediaType = 'PHOTO';
                            }
                            $scope.edit_medias.push(val);
                        });
                        angular.forEach(post_details.Files, function (val, key) {
                            val['data'] = post_details.Files[key];
                            val['progress'] = true;
                            $scope.edit_files.push(val);
                        });

                        $scope.mediaCount = $scope.edit_medias.length;
                        $scope.fileCount = $scope.edit_files.length;
                        $('#EditActivityGUID').val(activity_guid);
                        $scope.postTagList = post_details.editTags;

                        if (post_details.PostContent || $scope.fileCount > 0 || $scope.mediaCount) {
                            $scope.noContentToPost = false;
                        } else {
                            $scope.noContentToPost = true;
                        }
                        $scope.activePostType = post_details.PostType;
                        $scope.postInGroup = (post_details.ModuleID == 1) ? true : false;
                        $scope.selectedPrivacy = post_details.Visibility;
                        angular.element('#visible_for').val(post_details.Visibility);
                        $scope.parseTaggedInfo();
                        if (!$scope.$$phase)
                        {
                            $scope.$apply();
                        }
                    }, 100);
                    $scope.showNewsFeedPopup();
                }
            }
        }

        $scope.checkWallPost = function()
        {
            if($scope.article_post.ActivityGUID!='' && $scope.article_post.ActivityGUID!='0')
            {
                if(typeof NewsFeedCtrl=='undefined')
                {
                    var NewsFeedCtrl = angular.element(document.getElementById('NewsFeedCtrl')).scope();
                }
                NewsFeedCtrl.GetwallPost($scope.article_post.ActivityGUID, 0, 0);
            }
        }
        $scope.checkEditPost = function()
        {
            $scope.getSingleActivity($scope.article_post.ActivityGUID);
            var post_details = $scope.singleActivity;
            $scope.edit_post = true;
            $scope.edit_post_details = post_details;

            if ($scope.edit_post_details.Album.length > 0)
            {
                angular.forEach($scope.edit_post_details.Album[0].Media, function (val, key) {
                    $scope.saySomthingAboutMedia[val.MediaGUID] = val.Caption;
                });
            }

            setTimeout(function () {
                if (post_details.PostContent !== '')
                {
                    $(".note-placeholder").hide();
                }
                $(".note-editable").html(post_details.EditPostContent);
                $scope.PostContent = post_details.EditPostContent;
                $('#PostTitleInput').val(post_details.PostTitle);
                var char = (post_details.PostTitle) ? 140 - post_details.PostTitle.length : 140;
                if (char == 1)
                {
                    $('#PostTitleLimit').html('1 character');
                } else
                {
                    $('#PostTitleLimit').html(char + ' characters');
                }

                angular.forEach(post_details.EntityMembers, function (val, key) {
                    if (val.ModuleEntityGUID !== LoggedInUserGUID)
                    {
                        val['name'] = val.FirstName + ' ' + val.LastName;
                        $scope.tagsto.push(val);
                        $scope.group_user_tags.push(val);
                    }
                });

                $scope.edit_medias = [];
                $scope.edit_files = [];
                $scope.mediaCount = 0;
                $scope.fileCount = 0;

                angular.forEach(post_details.mediaData, function (val, key) {
                    val['data'] = post_details.mediaData[key];
                    val['data']['ImageServerPath'] = image_server_path + 'upload/wall';
                    val['progress'] = true;
                    if (val['data'].MediaType == 'Image')
                    {
                        val['data'].MediaType = 'PHOTO';
                    }
                    $scope.edit_medias.push(val);
                });
                angular.forEach(post_details.Files, function (val, key) {
                    val['data'] = post_details.Files[key];
                    val['progress'] = true;
                    $scope.edit_files.push(val);
                });

                $scope.mediaCount = $scope.edit_medias.length;
                $scope.fileCount = $scope.edit_files.length;
                $('#EditActivityGUID').val(activity_guid);
                $scope.postTagList = post_details.editTags;

                if (post_details.PostContent || $scope.fileCount > 0 || $scope.mediaCount) {
                    $scope.noContentToPost = false;
                } else {
                    $scope.noContentToPost = true;
                }
                $scope.activePostType = post_details.PostType;
                $scope.postInGroup = (post_details.ModuleID == 1) ? true : false;
                $scope.selectedPrivacy = post_details.Visibility;
                angular.element('#visible_for').val(post_details.Visibility);
                $scope.parseTaggedInfo();
                if (!$scope.$$phase)
                {
                    $scope.$apply();
                }
            }, 100);
        }

        /*$scope.$watch('PostContent',function(a,b){
         //console.log(a);
         // console.log(b);
         },true);*/

        $scope.get_privacy_text = function (privacy, firstname, lastname)
        {
            if (privacy == '1')
            {
                return 'Everyone';
            }
            if (privacy == '3')
            {
                return 'Friends of ' + firstname + ' ' + lastname;
            }
            if (privacy == '4')
            {
                return 'Only Me';
            }
        }

        $scope.SubmitWallpostLoader = false;

        $scope.UpdateBackgroundClass = function (cls)
        {
            $scope.backgroundClass = cls;
        }

        $scope.updateIsContest = function (value)
        {
            $scope.clearContest();
            $scope.IsContest = value;
            if (value == 1)
            {
                $scope.activePostType = 9;
            }
        }
        
        

        $scope.Contest = {PostTitle: '', PostContent: '', ButtonText: '', SubmissionDate: '', ContestDate: '', ContestTime: '', NoOfWinners: 0};
        $scope.VisualPost = {PostTitle: '', PostContent: '', Facts: ''};
        $scope.backgroundClass = 'switch-one';
        $scope.FocusOn = '';
        $scope.SubmitWallpost = function (isSummary) {

            if (IsAdminView == '1')
            {
                var user_list_scope = angular.element(document.getElementById('UserListCtrl')).scope().users;
                angular.forEach(user_list_scope, function (val, key) {
                    if (val.UserID == $('#postasuserid').val())
                    {
                        $scope.postasuser = val;
                    }
                });
            }

            var IsIntro = 0;
            var commentsSettingsVal = ($('#dCommenting').prop('checked')) ? 0 : 1;
            $('#comments_settings').val(commentsSettingsVal);
            $('#comments_settings2').val(commentsSettingsVal);

//          console.log($scope.postTagList); return false;
            //var PostContent = $('#PostContent').val().trim();

            if (IsAdminView == '1')
            {
                if (typeof $scope.postasuser.UserID == 'undefined')
                {
                    ShowErrorMsg('Please select user');
                    return false;
                }
            }

            //var PostContent = $.trim($scope.PostContent);
            var PostContent = $('#postEditor .note-editable').html();
            if ($scope.activePostType == '8' || $scope.activePostType == '9')
            {
                PostContent = $.trim($scope.PostContent);
            }
            if ($('#post_type').val()) {
                var posttypeid = $('#post_type').val();
                $('#post_type_id').val(posttypeid);
            }
            if (PostContent == '' && ($scope.medias.length == 0) && ($scope.files.length == 0)) {
                showResponseMessage('Please add attachement(s) or write something.', 'alert-danger');
                hideButtonLoader('ShareButton');
                //$('#ShareButton').attr('disabled', 'disabled');
                return false;
            }

            var jsonData = {};
            var media = [];
            var files = [];
            var i = 0;

            var attacheMentPromises = [];

            if ($scope.activePostType == '8' && $scope.activePostType == '9')
            {
                $scope.edit_medias = [];
            }

            if ($scope.medias && (Object.keys($scope.medias).length > 0)) {
                angular.forEach($scope.medias, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        var caption = ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] && ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] != '')) ? $scope.saySomthingAboutMedia[dataToAttache.MediaGUID] : $scope.saySomthingAboutMedia['ALL'];
                        media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: caption
                        });
                    }));
                });
            }

            if ($scope.edit_medias && (Object.keys($scope.edit_medias).length > 0)) {
                angular.forEach($scope.edit_medias, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        var caption = ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] && ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] != '')) ? $scope.saySomthingAboutMedia[dataToAttache.MediaGUID] : $scope.saySomthingAboutMedia['ALL'];
                        media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: caption
                        });
                    }));
                });
            }

            if ($scope.files && (Object.keys($scope.files).length > 0)) {
                angular.forEach($scope.files, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        files.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: ''
                        });
                    }));
                });
            }

            if ($scope.edit_files && (Object.keys($scope.edit_files).length > 0)) {
                angular.forEach($scope.edit_files, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        files.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: ''
                        });
                    }));
                });
            }

            $q.all(attacheMentPromises).then(function (data) {
                var formData = $("#wallpostform").serializeArray();
                var m1 = 1;
                var m2 = 2;
                $.each(formData, function () {
                    if (jsonData[this.name]) {
                        if (!jsonData[this.name].push) {
                            jsonData[this.name] = [jsonData[this.name]];
                        }
                        jsonData[this.name].push(this.value || '');
                    } else {
                        if (this.name == 'MediaGUID' || this.name == 'MediaGUID[]') {
                        } else if (this.name == 'FileMediaGUID' || this.name == 'FileMediaGUID[]') {
                        } else if (this.name == 'MediaCaption' || this.name == 'MediaCaption[]') {
                        } else {
                            jsonData[this.name] = this.value || '';
                        }
                    }
                });
                // var PContent = $.trim($('#wallpostform .textntags-beautifier div').html());
                var PContent = PostContent;
                if (PContent != "") {
                    PContent = $.trim(filterPContent(PContent));
                }

                //console.log(PContent);return;
                jsonData['PostContent'] = PContent;
                jsonData['EntityTags'] = $scope.postTagList;
                jsonData['Media'] = media;
                jsonData['Files'] = files;
                jsonData['ModuleID'] = $('#module_id').val();
                jsonData['ModuleEntityGUID'] = $('#module_entity_guid').val();
                jsonData['IsIntro'] = IsIntro;
                $scope.AllActivity = 0;
                if ($('#AllActivity').length > 0) {
                    $scope.AllActivity = $('#AllActivity').val();
                }
                jsonData['AllActivity'] = $scope.AllActivity;
                jsonData['Members'] = $scope.check_group_members();
                jsonData['NotifyAll'] = 0;

                if(jsonData['ModuleID'] == '14')
                {
                    if ($('#NotifyAllEvent').length > 0)
                    {
                        if ($('#NotifyAllEvent').is(':checked'))
                        {
                            jsonData['NotifyAll'] = 1;
                        }
                    }
                }
                else
                {
                    if ($('#NotifyAll').length > 0)
                    {
                        if ($('#NotifyAll').is(':checked'))
                        {
                            jsonData['NotifyAll'] = 1;
                        }
                    }
                }

                if (parseInt(jsonData['ModuleID']) == 34 && $('#PostTitleInput').val() == '' && settings_data.m40 == 1)
                {
                    showResponseMessage('Post title field is required for this type of post.', 'alert-danger');
                    return false;
                }
                if (parseInt($scope.activePostType) !== 9 && parseInt($scope.activePostType) !== 8 && parseInt($scope.activePostType) !== 1 && $('#PostTitleInput').val() == '')
                {
                    if (settings_data.m40 == 1) {
                        showResponseMessage('Post title field is required for this type of post.', 'alert-danger');
                        return false;
                    }
                }
                jsonData.Links = [];
                if ($scope.parseLinks.length > 0) {
                    angular.forEach($scope.parseLinks, function (v, k) {
                        var link = {};
                        link['URL'] = v.URL;
                        link['Title'] = v.Title;
                        link['MetaDescription'] = '';
                        link['ImageURL'] = v.Thumb;
                        link['IsCrawledURL'] = '0';
                        link['TagsCollection'] = [];
                        if (v.HideThumb)
                        {
                            link['ImageURL'] = '';
                        }
                        angular.forEach($scope.linktagsto[v.URL], function (val, key) {
                            link['TagsCollection'].push(val.Name);
                        });
                        jsonData['Links'].push(link);
                    });
                    $scope.parseLinks = [];
                }
                if (jsonData['Members'].length > 0 && !$scope.edit_post) {
                    if ($scope.post_in_group_guid != "" && $scope.post_in_group_guid != undefined) {
                        jsonData['GroupGUID'] = $scope.post_in_group_guid;
                        wallposturl = 'group/post_in_group';
                    } else {
                        wallposturl = 'group/create';
                        if ($scope.group_user_tags[0].Type == "INFORMAL" && $scope.group_user_tags.length == 1) {
                            jsonData['ModuleID'] = 1;
                            jsonData['ModuleEntityGUID'] = $scope.group_user_tags[0].ModuleEntityGUID;
                            wallposturl = 'activity/createWallPost';
                        }
                    }
                } else {
                    wallposturl = 'activity/createWallPost';
                    //jsonData['NotifyAll'] = 0;
                }

                if (typeof $scope.PostAs !== 'undefined')
                {
                    if ("ModuleID" in $scope.PostAs) {
                        jsonData['PostAsModuleID'] = $scope.PostAs.ModuleID;
                    }
                    if ("ModuleEntityGUID" in $scope.PostAs) {
                        jsonData['PostAsModuleEntityGUID'] = $scope.PostAs.ModuleEntityGUID;
                    }
                }

                jsonData['Status'] = 2;
                if ($scope.is_draft == '1')
                {
                    jsonData['Status'] = 10;
                    $scope.is_draft = 0;
                }

                jsonData['ActivityGUID'] = $('#EditActivityGUID').val();

                //            console.log(jsonData);
                showButtonLoader('ShareButton');

                if ($('#IsWiki').length > 0)
                {
                    jsonData['PostType'] = 4;
                }

                if (IsAdminView == '1')
                {
                    jsonData['UserID'] = $scope.postasuser.UserID;
                    jsonData['PostAsModuleID'] = 3;
                    jsonData['PostAsModuleEntityGUID'] = $scope.postasuser.UserGUID;
                    if (typeof $('#PostAsGroupModuleID').val() !== 'undefined' && typeof $('#PostAsGroupModuleEntityID').val() !== 'undefined')
                    {
                        if ($('#PostAsGroupModuleID').val() !== '' && $('#PostAsGroupModuleEntityID').val() !== '')
                        {
                            jsonData['ModuleID'] = $('#PostAsGroupModuleID').val();
                            jsonData['ModuleEntityGUID'] = $('#PostAsGroupModuleEntityID').val();
                        }
                    }
                    if (!$scope.postasuser)
                    {
                        ShowErrorMsg('Please select user');
                        $scope.postPreviemode = 0;
                        $scope.postEditormode = 1;
                        $scope.postTypeview = 0;
                        $scope.overlayShow = 1;
                        hideButtonLoader('ShareButton');
                        return false;
                    }
                }

                $scope.SubmitWallpostLoader = true;

                if ($('#IsLandingPage').length > 0 && $('#IsLandingPage').val() == '1')
                {
                    jsonData['ModuleID'] = 34;
                    if ($scope.tagsto.length == 0 && !$scope.edit_post)
                    {
                        showResponseMessage('Please select category', 'alert-danger');
                        hideButtonLoader('ShareButton');
                        $scope.SubmitWallpostLoader = false;
                        return false;
                    } else if ($scope.tagsto.length == 1)
                    {
                        jsonData['ModuleEntityGUID'] = $scope.tagsto[0].ForumCategoryGUID;
                    } else if (!$scope.edit_post)
                    {
                        showResponseMessage('Please select only one category', 'alert-danger');
                        hideButtonLoader('ShareButton');
                        $scope.SubmitWallpostLoader = false;
                        return false;
                    }
                }


                if (jsonData['PostType'] == '8')
                {
                    jsonData['PostContent'] = $scope.VisualPost.PostContent;
                    jsonData['Params'] = {};
                    jsonData['Params']['BackgroundClass'] = $scope.backgroundClass;
                }
                if (jsonData['PostType'] == '9')
                {
                    jsonData['PostContent'] = $scope.Contest.PostContent;
                    jsonData['Params'] = {};
                    jsonData['Params']['BackgroundClass'] = $scope.backgroundClass;
                    jsonData['Params']['ButtonText'] = $scope.Contest.ButtonText;
                    jsonData['Params']['NoOfWinners'] = $scope.Contest.NoOfWinners;
                    jsonData['ContestDate'] = $scope.Contest.ContestDate + ' ' + $scope.Contest.ContestTime;
                }

                if(isSummary!=1)
                {
                    jsonData['Summary'] = '';
                }

                // Parse it and wrap it in a div
                var frag = $("<div>").append($.parseHTML(jsonData['PostContent']));
                // Find the relevant images
                frag.find("img.tamemoji[emoji]").each(function() {
                  // Replace the image with a text node containing :::[dataattr2]:::
                  var $this = $(this);
                  var unicode_value = unicodeFromImage[$this.attr("emoji").slice(1, -1)];
                  $this.replaceWith($('<div style="'+$this.attr("style")+'">').html(unicode_value).addClass("parseemoji"))
                  //$this.replaceWith(document.createTextNode("< value='"+$this.attr("emoji")+"'></emoji>"));
                });
                // Get the HTML of the result
                jsonData['PostContent'] = frag.html();

                WallService.CallApi(jsonData, wallposturl).then(function (response) {
                    $scope.allreadyProcessedLinks = [];
                    $scope.titleKeyup = 0;
                    $scope.SubmitWallpostLoader = false;
                    if (response.ResponseCode == 200) {

                        if(jsonData['PostType'] == 4)
                        {
                            window.top.location = site_url+response.Data[0].ActivityURL;
                        }

                        webStorage.setStorageData('defaultPrivacy_' + LoggedInUserID, jsonData['Visibility']);
                        default_privacy = jsonData['Visibility'];
                        setTimeout(function () {
                            $scope.setActiveIconToPrivacy(default_privacy);
                            $scope.setPrivacyHelpTxt(default_privacy);
                            $('#visible_for').val(default_privacy);
                        }, 500);
                        if (angular.element('#ForumCtrl').length > 0) {
                            forumScope = angular.element('#ForumCtrl').scope();
                            forumScope.category_detail.NoOfDiscussions = parseInt(forumScope.category_detail.NoOfDiscussions) + 1;
                        }

                        if ($('#module_id').val() == 14 && (Object.keys($scope.medias).length > 0)) {
                            $rootScope.$broadcast('updateEventMediaCount', {
                                mediaCount: Object.keys($scope.medias).length
                            });
                        }
                        $('#welcomeUserPopup').modal('hide');
                        if (IsAdminView == '1')
                        {
                            $('#addPost').modal('hide');
                            ShowSuccessMsg("Post Added");
                        }

                        $scope.postPreviemode = 0;
                        $scope.postEditormode = 0;
                        $scope.postTypeview = 0;
                        $scope.overlayShow = 0;
                        hideButtonLoader('ShareButton');
                        var act_guid = $('#EditActivityGUID').val();
                        if (act_guid !== '')
                        {
                            angular.forEach(NewsFeedCtrl.activityData, function (a, b) {
                                if (a.ActivityGUID == act_guid)
                                {
                                    NewsFeedCtrl.activityData.splice(b, 1);
                                }
                            });
                            $('#EditActivityGUID').val('');
                            $scope.edit_post = false;
                        }
                        $scope.resetFormPost();
                        // $('#PostContent-stop,#PostContent').textntags('reset');
                        $scope.PostContent = '';
                        $('#PostContent').val('');
                        $('input [name="PostTitle"]').val('');
                        $('#posterror').text('');
                        $('#noOfCharPostContent').text('0');
                        $('#wallphotocontainer ul').html('');
                        $('#comments_settings').val(1);
                        $('#wallpostform .textntags-beautifier div').html('');
                        $scope.mediaInputIndex = '';
                        $scope.medias = {};
                        $scope.mediaCount = 0;
                        $scope.files = {};
                        $scope.fileCount = 0;
                        wallMediaCurrentIndex = 0;
                        wallFileCurrentIndex = 0;
                        $scope.saySomthingAboutMedia = {ALL: ''};
                        $scope.postTagList = [];

                        response.Data.map(function (repo) {
                            repo.SuggestedFriendList = [];
                            repo.RquestedFriendList = [];
                            repo.SearchFriendList = '';
                            return repo;
                        });


                        response.Data[0]['IsSingleActivity'] = $scope.IsSingleActivity;
                        response.Data[0]['append'] = 1;
                        response.Data[0]['Settings'] = Settings.getSettings();
                        response.Data[0]['ImageServerPath'] = Settings.getImageServerPath();
                        response.Data[0]['SiteURL'] = Settings.getSiteUrl();
                        response.Data[0]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                        response.Data[0]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                        response.Data[0]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        response.Data[0]['ReminderData'] = $scope.prepareReminderData(new Array());
                        response.Data[0].CollapsePostTitle = $scope.get_post_title(response.Data[0].PostTitle, response.Data[0].PostContent);
                        if (response.Data[0]['EntityTags'] && (response.Data[0]['EntityTags'].length > 0)) {
                            response.Data[0]['editTags'] = angular.copy(response.Data[0]['EntityTags']);
                            response.Data[0]['showTags'] = false;
                        } else {
                            response.Data[0]['EntityTags'] = [];
                            response.Data[0]['editTags'] = [];
                            response.Data[0]['showTags'] = false;
                        }

                        var frag = $("<div>").append($.parseHTML(response.Data[0].PostContent));
                        // Find the relevant images
                        frag.find(".parseemoji").each(function() {
                          // Replace the image with a text node containing :::[dataattr2]:::
                          var $this = $(this);
                          var emoji_value = ':'+imageFromUnicode[$this.html()]+':';
                          $this.replaceWith($('<img src="'+AssetBaseUrl+'img/emoji/blank.gif" style="'+$this.attr("style")+'">').addClass("img tamemoji").attr('emoji',emoji_value).attr('alt',emoji_value));
                          //$this.replaceWith(document.createTextNode("< value='"+$this.attr("emoji")+"'></emoji>"));
                        });
                        // Get the HTML of the result
                        response.Data[0].PostContent = frag.html();

                        $scope.checkInlineImage(response.Data[0]);

                        if (IsAdminView == '1')
                        {
                            var user_list_ctrl = angular.element(document.getElementById('UserListCtrl')).scope();
                            angular.forEach(user_list_ctrl.users, function (v1, k1) {
                                if (response.Data[0].PostAsModuleID == 3 && response.Data[0].UserGUID == v1.UserGUID)
                                {
                                    response.Data[0].actionas = v1;
                                }
                            });
                        }

                        $scope.group_user_tags = [];
                        $scope.tagsto = [];
                        $('.tags input').val('');
                        //$scope.NotifyAll = false;
                        $scope.memTagCount = false;
                        $scope.showNotificationCheck = 0;
                        $(".group-contacts .tags").removeAttr('ng-class');
                        $(".group-contacts .tags input").attr('style', '');
                        if ($('#IsWiki').length > 0)
                        {
                            $scope.article_list.unshift(response.Data[0]);
                            $('#addWiki').modal('hide');
                        } else if (response.Data[0]['PostType'] == '7')
                        {
                            $scope.get_announcements();
                        } else
                        {
                            if (NewsFeedCtrl.activityData.length > 0) {
                                $(NewsFeedCtrl.activityData).each(function (k, v) {
                                    if (NewsFeedCtrl.activityData[k].IsSticky == 0) {
                                        NewsFeedCtrl.activityData.splice(k, 0, response.Data[0]);
                                        $('#ShareButton').removeClass('loader-btn');
                                        return false;
                                    }
                                });
                            } else {
                                NewsFeedCtrl.activityData.push(response.Data[0]);
                            }
                        }

                        $rootScope.$broadcast('onNewPostCreated', {
                            postData: response.Data[0]
                        });

                        if (IsAdminView == '1' && typeof IsAdminDashboard == 'undefined')
                        {
                            $scope.GetwallPost();
                        }

                        $scope.tr++;
                        if (!$scope.IsActiveFilter) {
                            setTimeout(function () {
                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                    $('#FilterButton').show();
                                } else {
                                    $('#FilterButton').hide();
                                }
                            }, 2000);
                        }
                        $('#multipleInstantGroupModal').modal('hide');
                        /*setTimeout(function(){
                         $('#cmt-div-'+response.Data[0].ActivityGUID+' .place-holder-label').show();
                         $('#cmt-div-'+response.Data[0].ActivityGUID+' .comment-section').addClass('hide');
                         },500)*/
                        //$('#cmt-div-'+response.Data[0].ActivityGUID+' .place-holder-label').show();
                        $scope.show_comment_box = "";
                        $('#multipleInstantGroupModal').modal('hide');

                        $scope.parseLinkData('', 0);

                        $('#ShareButton').removeClass('loader-btn');

                        $('#PostTitleInput').val('');
                        if (!$scope.$$phase)
                        {
                            $('#PostTitleInput').keyup();
                        }
                        $scope.override_post_permission = [];
                        $('#module_id').val($('#old_module_id').val());
                        $("#module_entity_guid").val($('#old_module_entity_guid').val());

                    } else if (response.ResponseCode == 595) {
                        $scope.postPreviemode = 0;
                        $scope.postEditormode = 1;
                        $scope.postTypeview = 0;
                        $scope.overlayShow = 1;
                        hideButtonLoader('ShareButton');
                        $scope.multipleInstantGroupData = response.Data;
                        $('#multipleInstantGroupModal').modal('show');
                    } else {
                        $scope.postPreviemode = 0;
                        $scope.postEditormode = 1;
                        $scope.postTypeview = 0;
                        $scope.overlayShow = 1;
                        hideButtonLoader('ShareButton');
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                    //$scope.resetPrivacySettings();

                }, function () {
                    $scope.SubmitWallpostLoader = false;
                });
            });
            $scope.linkProcessing = false;
//            setTimeout(function() {
//                $('.wallloader').hide();
//                $('.wall-content').removeClass('post-content-open');
//                showHidePhotoVideoIcon();
//                $('.files-attached-in-post').html('');
//            }, 500);
        };

        $scope.SubmitWelcomePost = function () {
            var IsIntro = 1;

            var PostContent = $('#WelcomePostEditor .note-editable').html();

            if ($('#welcome_post_type').val()) {
                var posttypeid = $('#welcome_post_type').val();
            }
            if (PostContent == '' && ($scope.medias.length == 0) && ($scope.files.length == 0)) {
                showResponseMessage('Please add attachement(s) or write something.', 'alert-danger');
                hideButtonLoader('ShareButton');
                return false;
            }

            var jsonData = {};
            var media = [];
            var files = [];
            var i = 0;

            var attacheMentPromises = [];

            if ($scope.medias && (Object.keys($scope.medias).length > 0)) {
                angular.forEach($scope.medias, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        var caption = ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] && ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] != '')) ? $scope.saySomthingAboutMedia[dataToAttache.MediaGUID] : $scope.saySomthingAboutMedia['ALL'];
                        media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: caption
                        });
                    }));
                });
            }

            if ($scope.edit_medias && (Object.keys($scope.edit_medias).length > 0)) {
                angular.forEach($scope.edit_medias, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        var caption = ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] && ($scope.saySomthingAboutMedia[dataToAttache.MediaGUID] != '')) ? $scope.saySomthingAboutMedia[dataToAttache.MediaGUID] : $scope.saySomthingAboutMedia['ALL'];
                        media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: caption
                        });
                    }));
                });
            }

            if ($scope.files && (Object.keys($scope.files).length > 0)) {
                angular.forEach($scope.files, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        files.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: ''
                        });
                    }));
                });
            }

            if ($scope.edit_files && (Object.keys($scope.edit_files).length > 0)) {
                angular.forEach($scope.edit_files, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        files.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: ''
                        });
                    }));
                });
            }

            $q.all(attacheMentPromises).then(function (data) {
                var formData = $("#welcomewallpostform").serializeArray();
                var m1 = 1;
                var m2 = 2;
                $.each(formData, function () {
                    if (jsonData[this.name]) {
                        if (!jsonData[this.name].push) {
                            jsonData[this.name] = [jsonData[this.name]];
                        }
                        jsonData[this.name].push(this.value || '');
                    } else {
                        if (this.name == 'MediaGUID' || this.name == 'MediaGUID[]') {
                        } else if (this.name == 'FileMediaGUID' || this.name == 'FileMediaGUID[]') {
                        } else if (this.name == 'MediaCaption' || this.name == 'MediaCaption[]') {
                        } else {
                            jsonData[this.name] = this.value || '';
                        }
                    }
                });

                var PContent = PostContent;
                if (PContent != "") {
                    PContent = $.trim(filterPContent(PContent));
                }

                jsonData['PostContent'] = PContent;
                jsonData['EntityTags'] = $scope.postTagList;
                jsonData['Media'] = media;
                jsonData['Files'] = files;
                jsonData['ModuleID'] = 34;
                jsonData['ModuleEntityGUID'] = $('#module_entity_guid').val();
                jsonData['IsIntro'] = IsIntro;
                $scope.AllActivity = 0;
                if ($('#AllActivity').length > 0) {
                    $scope.AllActivity = $('#AllActivity').val();
                }
                jsonData['AllActivity'] = $scope.AllActivity;
                jsonData['NotifyAll'] = 0;

                if ($('#NotifyAll').length > 0)
                {
                    if ($('#NotifyAll').is(':checked'))
                    {
                        jsonData['NotifyAll'] = 1;
                    }
                }
                if (parseInt(jsonData['ModuleID']) == 34 && $('#WelcomePostTitleInput').val() == '')
                {
                    showResponseMessage('Post title field is required for this type of post.', 'alert-danger');
                    return false;
                }

                jsonData.Links = [];
                if ($scope.parseLinks.length > 0) {
                    angular.forEach($scope.parseLinks, function (v, k) {
                        var link = {};
                        link['URL'] = v.URL;
                        link['Title'] = v.Title;
                        link['MetaDescription'] = '';
                        link['ImageURL'] = v.Thumb;
                        link['IsCrawledURL'] = '0';
                        link['TagsCollection'] = [];
                        if (v.HideThumb)
                        {
                            link['ImageURL'] = '';
                        }
                        angular.forEach($scope.linktagsto[v.URL], function (val, key) {
                            link['TagsCollection'].push(val.Name);
                        });
                        jsonData['Links'].push(link);
                    });
                    $scope.parseLinks = [];
                }

                wallposturl = 'activity/createWallPost';

                if (typeof $scope.PostAs !== 'undefined')
                {
                    if ("ModuleID" in $scope.PostAs) {
                        jsonData['PostAsModuleID'] = $scope.PostAs.ModuleID;
                    }
                    if ("ModuleEntityGUID" in $scope.PostAs) {
                        jsonData['PostAsModuleEntityGUID'] = $scope.PostAs.ModuleEntityGUID;
                    }
                }

                jsonData['Status'] = 2;

                jsonData['ActivityGUID'] = $('#EditActivityGUID').val();

                showButtonLoader('ShareButton');

                $scope.SubmitWallpostLoader = true;

                WallService.CallApi(jsonData, wallposturl).then(function (response) {
                    $scope.allreadyProcessedLinks = [];
                    $scope.titleKeyup = 0;
                    $scope.SubmitWallpostLoader = false;
                    if (response.ResponseCode == 200) {

                        webStorage.setStorageData('defaultPrivacy_' + LoggedInUserID, jsonData['Visibility']);
                        default_privacy = jsonData['Visibility'];
                        setTimeout(function () {
                            $scope.setActiveIconToPrivacy(default_privacy);
                            $scope.setPrivacyHelpTxt(default_privacy);
                            $('#welcome_visible_for').val(default_privacy);
                        }, 500);
                        $('#welcomeUserPopup').modal('hide');

                        $scope.postPreviemode = 0;
                        $scope.postEditormode = 0;
                        $scope.postTypeview = 0;
                        $scope.overlayShow = 0;
                        hideButtonLoader('ShareButton');
                        var act_guid = $('#EditActivityGUID').val();
                        if (act_guid !== '')
                        {
                            angular.forEach(NewsFeedCtrl.activityData, function (a, b) {
                                if (a.ActivityGUID == act_guid)
                                {
                                    NewsFeedCtrl.activityData.splice(b, 1);
                                }
                            });
                            $('#EditActivityGUID').val('');
                            $scope.edit_post = false;
                        }
                        $scope.resetFormPost();
                        $scope.PostContent = '';
                        $('#PostContent').val('');
                        $('input [name="PostTitle"]').val('');
                        $('#posterror').text('');
                        $('#noOfCharPostContent').text('0');
                        $('#wallphotocontainer ul').html('');
                        $('#comments_settings').val(1);
                        $('#welcomewallpostform .textntags-beautifier div').html('');
                        $scope.mediaInputIndex = '';
                        $scope.medias = {};
                        $scope.mediaCount = 0;
                        $scope.files = {};
                        $scope.fileCount = 0;
                        wallMediaCurrentIndex = 0;
                        wallFileCurrentIndex = 0;
                        $scope.saySomthingAboutMedia = {ALL: ''};
                        $scope.postTagList = [];

                        response.Data.map(function (repo) {
                            repo.SuggestedFriendList = [];
                            repo.RquestedFriendList = [];
                            repo.SearchFriendList = '';
                            return repo;
                        });

                        response.Data[0]['append'] = 1;
                        response.Data[0]['Settings'] = Settings.getSettings();
                        response.Data[0]['ImageServerPath'] = Settings.getImageServerPath();
                        response.Data[0]['SiteURL'] = Settings.getSiteUrl();
                        response.Data[0]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                        response.Data[0]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                        response.Data[0]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        response.Data[0]['ReminderData'] = $scope.prepareReminderData(new Array());
                        response.Data[0].CollapsePostTitle = $scope.get_post_title(response.Data[0].PostTitle, response.Data[0].PostContent);
                        if (response.Data[0]['EntityTags'] && (response.Data[0]['EntityTags'].length > 0)) {
                            response.Data[0]['editTags'] = angular.copy(response.Data[0]['EntityTags']);
                            response.Data[0]['showTags'] = false;
                        } else {
                            response.Data[0]['EntityTags'] = [];
                            response.Data[0]['editTags'] = [];
                            response.Data[0]['showTags'] = false;
                        }

                        if (IsNewsFeed == '1')
                        {
                            response.Data[0]['Message'] = response.Data[0]['Message'];
                        }

                        $scope.group_user_tags = [];
                        $scope.tagsto = [];
                        $('.tags input').val('');
                        //$scope.NotifyAll = false;
                        $scope.memTagCount = false;
                        $scope.showNotificationCheck = 0;


                        if (NewsFeedCtrl.activityData.length > 0) {
                            $(NewsFeedCtrl.activityData).each(function (k, v) {
                                if (NewsFeedCtrl.activityData[k].IsSticky == 0) {
                                    NewsFeedCtrl.activityData.splice(k, 0, response.Data[0]);
                                    $('#ShareButton').removeClass('loader-btn');
                                    return false;
                                }
                            });
                        } else {
                            NewsFeedCtrl.activityData.push(response.Data[0]);
                        }

                        // $rootScope.$broadcast('onNewPostCreated', {
                        //     postData: response.Data[0]
                        // });

                        if (IsAdminView == '1' && typeof IsAdminDashboard == 'undefined')
                        {
                            $scope.GetwallPost();
                        }

                        $scope.tr++;
                        if (!$scope.IsActiveFilter) {
                            setTimeout(function () {
                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                    $('#FilterButton').show();
                                } else {
                                    $('#FilterButton').hide();
                                }
                            }, 2000);
                        }
                        $('#multipleInstantGroupModal').modal('hide');
                        $scope.show_comment_box = "";
                        $('#multipleInstantGroupModal').modal('hide');

                        $scope.parseLinkData('', 0);

                        $('#ShareButton').removeClass('loader-btn');

                        $('#WelcomePostTitleInput').val('');
                        if (!$scope.$$phase)
                        {
                            $('#WelcomePostTitleInput').keyup();
                        }
                        $scope.override_post_permission = [];
                        $('#module_id').val($('#old_module_id').val());
                        $("#module_entity_guid").val($('#old_module_entity_guid').val());

                        if ($('#module_id').length > 0 && $('#module_id').val() == '34')
                        {
                            angular.element(document.getElementById('ForumCtrl')).scope().get_category_details();
                        }

                    } else if (response.ResponseCode == 595) {
                        $scope.postPreviemode = 0;
                        $scope.postEditormode = 1;
                        $scope.postTypeview = 0;
                        $scope.overlayShow = 1;
                        hideButtonLoader('ShareButton');
                        $scope.multipleInstantGroupData = response.Data;
                        $('#multipleInstantGroupModal').modal('show');
                    } else {
                        $scope.postPreviemode = 0;
                        $scope.postEditormode = 1;
                        $scope.postTypeview = 0;
                        $scope.overlayShow = 1;
                        hideButtonLoader('ShareButton');
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                    //$scope.resetPrivacySettings();
                }, function () {
                    $scope.SubmitWallpostLoader = false;
                });
            });
            $scope.linkProcessing = false;
        }

        $scope.pin_to_top = function (activity_guid)
        {
            var reqData = {EntityGUID: activity_guid};
            WallService.CallApi(reqData, 'activity/pin_to_top').then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == activity_guid)
                        {
                            NewsFeedCtrl.activityData[key].IsPined = 1;
                            NewsFeedCtrl.activityData.splice(key, 1);
                        }
                    });
                    $scope.get_announcements();
                }
            });
        }

        $scope.reset_post_type = function ()
        {
            var reset = true;
            setTimeout(function () {
                if ($scope.override_post_permission.length > 0)
                {
                    angular.forEach($scope.override_post_permission, function (val, key) {
                        if (val.Value == $scope.activePostType)
                        {
                            reset = false;
                        }
                    });
                } else
                {
                    if (IsAdminView == '1')
                    {
                        if ($scope.postasuser.ContentTypes.length > 0 && $scope.group_user_tags.length == 0)
                        {
                            angular.forEach($scope.postasuser.ContentTypes, function (val, key) {
                                if (val.Value == $scope.activePostType)
                                {
                                    reset = false;
                                }
                            });
                        }
                    } else if ($scope.ContentTypes.length > 0 && $scope.group_user_tags.length == 0)
                    {
                        angular.forEach($scope.ContentTypes, function (val, key) {
                            if (val.Value == $scope.activePostType)
                            {
                                reset = false;
                            }
                        });
                    }
                }

                if (reset)
                {
                    if ($scope.override_post_permission.length > 0)
                    {
                        $scope.updateActivePostType($scope.override_post_permission[0].Value);
                        if (!$scope.$$phase)
                        {
                            $scope.$apply();
                        }
                    } else
                    {
                        $scope.updateActivePostType(1);
                        if (!$scope.$$phase)
                        {
                            $scope.$apply();
                        }
                    }
                }
            }, 500);
        }

        $scope.SettingsFn = function (activity_guid) {
            var data = [];
            data['Settings'] = Settings.getSettings();
            data['ImageServerPath'] = Settings.getImageServerPath();
            data['SiteURL'] = Settings.getSiteUrl();
            data['DateTimeTZ'] = Settings.getCurrentTimeUserTimeZone();
            data['DisplayTomorrowDate'] = DisplayTomorrowDate;
            data['DisplayNextWeekDate'] = DisplayNextWeekDate;
            data['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
            $scope.updateActivityData(activity_guid, data);
            if (IsNewsFeed == '1')
            {
                $scope.updatePopularData(activity_guid, data);
            }
        }
        function removeAllButLast(string, token, is_end) {
            if (!string) {
                return string;
            }
            /* Requires STRING not contain TOKEN */
            var parts = string.split(token);
            if (is_end) {
                return parts.slice(0, -1).join('}}<small>') + token + parts.slice(-1)
            } else {
                return token + parts.slice(1).join('</small>{{')
            }
        }
        $scope.getTitleMessage = function (data) {
            var msz = data.Message;
            msz = removeAllButLast(msz, '}}', true);
            msz = removeAllButLast(msz, '{{', false);

            var EntityURL = base_url;
            var UserURL = base_url + data.UserProfileURL;
            var shareType = 'Post';
            var PhotoMediaGUID = '';
            if (data.ActivityType == 'Share' || data.ActivityType == 'ShareMedia' || data.ActivityType == 'ShareSelf' || data.ActivityType == 'ShareMediaSelf')
            {
                if (data.ShareDetails.Album.length > 0) {
                    PhotoMediaGUID = data.ShareDetails.Album[0].Media[0].MediaGUID;
                    shareType = 'Photo';
                }
            } else
            {
                if (data.Album.length > 0) {
                    PhotoMediaGUID = data.Album[0].Media[0].MediaGUID;
                    shareType = 'Photo';
                }
            }
            var ActivityOwnerLink = base_url;

            if (data.ModuleID == 1) {
                EntityURL += 'group/' + data.EntityProfileURL;
            } else if (data.ModuleID == 3) {
                EntityURL += data.EntityProfileURL;
                ActivityOwnerLink += data.ActivityOwnerLink;
            } else if (data.ModuleID == 14) {
                EntityURL += data.EntityProfileURL;
            } else if (data.ModuleID == 18) {
                EntityURL += 'page/' + data.EntityProfileURL;
            }
            if (typeof msz !== 'undefined') {
                if (data.ActivityType == 'GroupPostAdded' && data.PostType == '7')
                {
                    if (data.EntityName == "")
                    {
                        str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getMembersHTML(data.EntityMembers, data.EntityMembersCount, 1, data.EntityProfileURL, 1) + '</a>');
                    } else
                    {
                        str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + data.EntityName + '</a>');
                    }
                } else if ((data.ActivityType == 'AlbumAdded' || data.ActivityType == 'AlbumUpdated') && data.ModuleEntityOwner == '1' && data.ModuleID == '18')
                {
                    str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' + data.UserName + '</a>');
                } else
                {
                    if (data.ActivityType == 'ForumPost')
                    {
                        if (data.IsAdmin == '1')
                        {
                            msz = msz.replace("{{User}}", '{{User}} <i data-toggle="tooltip" data-original-title="Admin" class="ficon-admin f-green"></i>');
                        } else if (data.IsExpert == '1')
                        {
                            msz = msz.replace("{{User}}", '{{User}} <i data-toggle="tooltip" data-original-title="Expert" class="ficon-expert f-blue"></i>');
                        }
                    }
                    
                    str = msz.replace("{{User}}", '<a class="" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" >' + data.UserName + '</a>');
                }

                if(data.IsVIP == 1) {
                    str = msz.replace("{{SUBJECT}}", '<a class="" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" >' + data.UserName + '</a>&nbsp;<a  data-toggle="tooltip" data-original-title="VIP User" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a>');
                } else {
                    str = str.replace("{{SUBJECT}}", '<a class="" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" >' + data.UserName + '</a>'); //href="' + UserURL + '"
                }

                
                str = str.replace("{{ACTIVITYOWNER}}", '<a class="" target="_self" href="' + base_url + data.ActivityOwnerLink + '">' +
                        data.ActivityOwner + '</a>')
            } else {
                str = '';
            }
            switch (data.ActivityType) {
                case 'ProfilePicUpdated':
                case 'ProfileCoverUpdated':
                    if (data.ModuleID == 1) {
                        str = msz.replace("{{EntityName}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="group/' + data.EntityProfileURL + '">' + data.EntityName + '</a>\'s');
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + data.UserProfileURL +
                                '">' + data.UserName + '</a>');
                    }
                    if (data.ModuleID == 3) {
                        str = msz.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                data.UserName + '</a>');
                        str = str.replace("{{EntityName}}", 'their');
                    }
                    if (data.ModuleID == 14) {
                        str = msz.replace("{{EntityName}}", '<a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL +
                                '">' + data.EntityName + '</a>\'s');
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + data.UserProfileURL +
                                '">' + data.UserName + '</a>');
                    }
                    if (data.ModuleID == 18) {
                        str = msz.replace("{{EntityName}}", 'their');
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                data.EntityName + '</a>');
                    }
                    break;
                case 'RatingAdded':
                case 'RatingUpdated':
                    EntityGUID = data.RatingData.CreatedBy.EntityGUID;
                    if (data.RatingData.CreatedBy.ModuleID == '18') {
                        entitytype = 'page';
                    } else {
                        entitytype = 'user';
                    }
                    str = str.replace("{{REVIEWER}}", '<a class="loadbusinesscard" entitytype="' + entitytype + '" entityguid="' + EntityGUID + '" href="' + site_url + data.RatingData.CreatedBy
                            .ProfileURL + '">' + data.RatingData.CreatedBy.EntityName + '</a>');
                    str = str.replace("{{OBJECT}}", '<span><a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                            data.EntityName + '</a></span>');
                    break;
                case 'PollCreated':
                    if (data.ModuleID == 18) {
                        entitytype = 'page';
                    } else {
                        entitytype = 'user';
                    }
                    str = msz.replace("{{User}}", '<a target="_self" class="loadbusinesscard" entitytype="' + entitytype + '" entityguid="' + data.EntityGUID + '" href="' + EntityURL +
                            '">' + data.EntityName + '</a>');
                    str = str.replace("{{Entity}}", '<a target="_self"  href="' + EntityURL + '/activity/' + data.ActivityGUID + '">' + data.ViewTemplate + '</a>');
                    break;
                case 'AlbumAdded':
                    //str = str.replace("{{Entity}}", '<a href="javascript:void(0);">'+$scope.getHighlighted(data.EntityName)+'</a>');
                    if (typeof data.Album[0] !== 'undefined') {
                        str = str.replace("{{Entity}}", '<a href="' + site_url + data.Album[0].AlbumProfileURL + '/' + data.Album[0].AlbumGUID + '">' + data.EntityName +
                                '</a>');
                    } else {
                        str = str.replace("{{Entity}}", '');
                    }
                    if (data.ModuleID !== '3' && data.AlbumEntityName) {
                        //console.log('hi');
                        if (data.ModuleID == '1')
                        {
                            str += ' in ' + '<a href="' + site_url + 'group/' + data.EntityProfileURL + '">' + data.AlbumEntityName + '</a>'
                        } else if (data.ModuleID == '18')
                        {
                            str += ' in ' + '<a href="' + site_url + 'page/' + data.EntityProfileURL + '">' + data.AlbumEntityName + '</a>'
                        } else if (data.ModuleID == '14')
                        {
                            str += ' in ' + '<a href="' + site_url + '/' + data.EntityProfileURL + '">' + data.AlbumEntityName + '</a>'
                        }
                    }
                    break;
                case 'AlbumUpdated':
                    if (typeof data.Album[0] !== 'undefined') {
                        str = str.replace("{{Entity}}", '<a href="' + site_url + data.Album[0].AlbumProfileURL + '/' + data.Album[0].AlbumGUID + '">' + data.EntityName +
                                '</a>');
                        str = str.replace("{{AlbumType}}", 'Media');
                        str = str.replace("{{count}}", data.Params.count);
                    } else {
                        str = str.replace("{{Entity}}", '');
                        str = str.replace("{{AlbumType}}", '');
                        str = str.replace("{{count}}", '');
                    }
                    if (data.ModuleID !== '3' && data.AlbumEntityName) {
                        if (data.ModuleID == '1')
                        {
                            str += ' in ' + '<a href="' + site_url + 'group/' + data.EntityProfileURL + '">' + data.AlbumEntityName + '</a>'
                        } else if (data.ModuleID == '18')
                        {
                            str += ' in ' + '<a href="' + site_url + 'page/' + data.EntityProfileURL + '">' + data.AlbumEntityName + '</a>'
                        } else if (data.ModuleID == '14')
                        {
                            str += ' in ' + '<a href="' + site_url + '/' + data.EntityProfileURL + '">' + data.AlbumEntityName + '</a>'
                        }

                    }
                    break;
                case 'GroupJoined':
                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + data.EntityName + '</a>')
                    $scope.postCtrl = false; // Post Control
                    break;
                case 'GroupPostAdded':
                    if ($('#module_id').val() !== '1') {
                        if (data.PostType !== '7')
                        {
                            if(data.PostType == '4' && data.IsSingleActivity == '0')
                            {
                                str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + data.UserName +
                                        '</a> ';
                                if (data.IsAdmin == '1')
                                {
                                    str += '<i data-toggle="tooltip" data-original-title="Admin" class="ficon-admin f-green"></i> ';
                                } else if (data.IsExpert == '1')
                                {
                                    str += '<i data-toggle="tooltip" data-original-title="Expert" class="ficon-expert f-blue"></i> ';
                                }

                                str += 'posted an article';
                            }
                            else
                            {
                                if (data.EntityName !== '') {
                                    str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + data.UserName +
                                            '</a> ';
                                    if (data.IsAdmin == '1')
                                    {
                                        str += '<i data-toggle="tooltip" data-original-title="Admin" class="ficon-admin f-green"></i> ';
                                    } else if (data.IsExpert == '1')
                                    {
                                        str += '<i data-toggle="tooltip" data-original-title="Expert" class="ficon-expert f-blue"></i> ';
                                    }

                                    str += 'posted in <a target="_self" class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" href="' + EntityURL + '">' +
                                            data.EntityName + '</a>';

                                } else {
                                    str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + data.UserName +
                                            '</a> posted in ' + $scope.getMembersHTML(data.EntityMembers, data.EntityMembersCount, 1, data.EntityProfileURL, 1);
                                }
                            }
                        }

                    } else
                    {
                        if (data.IsAdmin == '1')
                        {
                            str += ' <i data-toggle="tooltip" data-original-title="Admin" class="ficon-admin f-green"></i> ';
                        } else if (data.IsExpert == '1')
                        {
                            str += ' <i data-toggle="tooltip" data-original-title="Expert" class="ficon-expert f-blue"></i> ';
                        }
                    }
                    break;
                case 'ForumPost':
                    /*if ($('#module_id').val() !== '34') {*/
                    if(data.PostType == '4' && data.IsSingleActivity == '0')
                    {
                        str = str.replace("posted in","posted an article");
                        str = str.replace("{{Entity}}","");
                    }
                    else
                    {
                        str = str.replace("{{Entity}}", '<a target="_self" href="' + data.EntityProfileURL + '">' +
                            data.EntityName + '</a>');
                    }

                    /*}*/
                    break;
                case 'EventWallPost':
                    if ($('#module_id').val() !== '14') {
                        str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + data.UserName +
                                '</a> posted in <a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                data.EntityName + '</a>';
                    }
                    break;
                case 'PagePost':
                    if (msz == "{{User}}") {
                        if (data.ModuleEntityOwner == 1) {
                            str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                    data.EntityName + '</a>');
                        } else {
                            str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' +
                                    data.EntityName + '</a>');
                        }
                    } else {
                        if (data.ModuleEntityOwner == 1) {
                            str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.UserGUID + '" target="_self" href="page/' + data.UserProfileURL + '">' +
                                    data.UserName + '</a>');
                        }
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                data.EntityName + '</a>');
                    }
                    break;
                case 'Follow':
                case 'FriendAdded':
                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' +
                            data.EntityName + '</a>');
                    $scope.postCtrl = false; // Post Control
                    break;
                case 'Share':
                case 'ShareMedia':
                    if(data.PostType == '4' && data.IsSingleActivity == '0')
                    {
                        str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' +
                        data.UserName + '</a> shared an article';
                    }
                    else
                    {
                        if (data.ShareDetails.ActivityModule == 'Users') {
                            data.ShareDetails.ActivityModule = 'user';
                        }
                        if (shareType == 'Photo') {
                            str = str.replace("{{ENTITYTYPE}}", '<a class="loadbusinesscard" entitytype="' + data.ShareDetails.ActivityModule + '" entityguid="' + data.ShareDetails.EntityGUID +
                                    '" onclick="callpopup(\'' + PhotoMediaGUID + '\');" href="javascript:void(0);">' + shareType + '</a>');
                        } else {
                            str = str.replace("{{ENTITYTYPE}}", '<a  target="_self" href="' + base_url + data.UserProfileURL + '/activity/' + data.ActivityGUID + '">' +
                                    shareType + '</a>');
                        }
                        str = str.replace("{{ENTITYTYPE}}", '<a class="loadbusinesscard" entitytype="' + data.SharedActivityModule + '" entityguid="' + data.EntityGUID + '" target="_self" href="' +
                                EntityURL + '">' + data.EntityName + '</a>');

                        if (data.ShareDetails.ActivityModule == 'Page' || data.ShareDetails.ActivityModule == 'Group' || data.ShareDetails.ActivityModule == 'Forum Category' || data.ShareDetails.ActivityModule == 'Event' || data.ShareDetails.ActivityModule == 'Polls')
                        {
                            str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.EntityGUID +
                                    '" target="_self" href="' + data.EntityProfileURL + '">' + data.EntityName + '</a>');
                        } else
                        {
                            str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="' + data.ShareDetails.ActivityModule + '" entityguid="' + data.EntityGUID +
                                    '" target="_self" href="' + data.EntityProfileURL + '">' + data.EntityName + '</a>');
                        }

                        if (data.IsOwner == 1) {
                            $scope.postCtrl = true; // Post Control
                        } else {
                            $scope.postCtrl = false;
                        }
                    }
                    break;
                case 'ShareSelf':
                case 'ShareMediaSelf':
                    if(data.PostType == '4' && data.IsSingleActivity == '0')
                    {
                        str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' +
                        data.UserName + '</a> shared an article';
                    }
                    else
                    {
                        if (data.SharedActivityModule == 'Users') {
                            data.SharedActivityModule = 'user';
                        }
                        if (data.EntityType == 'Photo') {
                            str = str.replace("{{ENTITYTYPE}}", '<a onclick="callpopup(\'' + PhotoMediaGUID + '\');" href="javascript:void(0);">' + data.EntityType +
                                    '</a>');
                        } else {
                            str = str.replace("{{ENTITYTYPE}}", '<a target="_self" href="' + base_url + 'post/title/' + data.ActivityGUID + '">' + data.ShareDetails.EntityType +
                                    '</a>');
                        }
                        if (data.ShareDetails.ActivityModule == 'Page' || data.ShareDetails.ActivityModule == 'Group' || data.ShareDetails.ActivityModule == 'Forum Category' || data.ShareDetails.ActivityModule == 'Event' || data.ShareDetails.ActivityModule == 'Polls')
                        {
                            str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.ShareDetails.UserGUID +
                                    '" target="_self" href="' + data.ShareDetails.UserProfileURL + '">' + data.ShareDetails.UserName + '</a>');
                        } else
                        {
                            str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="' + data.ShareDetails.ActivityModule + '" entityguid="' + data.ShareDetails.EntityGUID +
                                    '" target="_self" href="' + data.ShareDetails.UserProfileURL + '">' + data.ShareDetails.UserName + '</a>');
                        }
                        if (data.IsOwner == 1) {
                            $scope.postCtrl = true; // Post Control
                        } else {
                            $scope.postCtrl = false;
                        }
                    }
                    break;
                case 'Post':
                    str = str.replace("{{OBJECT}}", '<a entitytype="user" entityguid="' + data.EntityGUID + '" target="_self" >' +
                            data.EntityName + '</a>'); //href="' + EntityURL + '"
                    $scope.postCtrl = false;
                    break;
                case 'QuizPostAdded':                        
                        str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + data.UserName +
                                '</a> posted in <a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                data.EntityName + '</a>';
                        
                        break;
                default:
                    if (data.IsOwner == 1) {
                        $scope.postCtrl = true; // Post Control
                    } else {
                        $scope.postCtrl = false;
                    }
                    break;
            }

            if (data.Params != null) {
                var params = data.Params;
                // Params
                paramsKey = Object.keys(params)
                for (var i = 0; i < paramsKey.length; i++) {
                    str = str.replace("{{" + paramsKey[i] + "}}", params[paramsKey[i]])
                }
            }
            str = $sce.trustAsHtml(str);
            return str;
        }
        $scope.getHighlighted = function (str) {
            var advancedSearchKeyword = angular.element('#advancedSearchKeyword').val();
            if ($('#BtnSrch i').hasClass('icon-removeclose') || advancedSearchKeyword) {

                if (!advancedSearchKeyword) {
                    advancedSearchKeyword = $('#srch-filters').val();
                }

                if (typeof str === 'undefined') {
                    str = '';
                }
                if (str.length > 0 && advancedSearchKeyword.length > 0) {

                    function callback(p1, p2) {
                        return ((p2 == undefined) || p2 == '') ? p1 : '<span class="highlightedText">' + p1 + '</span>';
                    }

                    str = str.replace(new RegExp('<[^>]+>|(' + advancedSearchKeyword + ')', 'gi'), callback);

                    //str = str.replace(new RegExp(advancedSearchKeyword, 'gi'), "<span class='highlightedText'>$&</span>");
                }
                return str;
            } else {
                return str;
            }
        }

        $scope.get_img_class = function (media, is_preview)
        {
            if (is_preview == '1')
            {
                media = ObjectToArray(media);
            }
            var class_name = '';
            if (typeof media !== 'undefined')
            {
                if (media.length == 1)
                {
                    class_name = 'single-image';
                } else if (media.length == 2)
                {
                    class_name = 'two-images';
                } else if (media.length == 3)
                {
                    class_name = 'three-images';
                } else if (media.length > 3)
                {
                    class_name = 'four-images';
                }
            }
            return class_name;
        }

        $scope.getTimeFromDate = function (CreatedDate) {
            return moment(CreatedDate).format('dddd, MMM D YYYY hh:mm A');
        }
        $scope.UTCtoTimeZone = function (date, date_format) {
            date_format = date_format || 'YYYY-MM-DD HH:mm:ss';
            var localTime = moment.utc(date).toDate();
            return moment.tz(localTime, TimeZone).format(date_format);
        }
        $scope.date_format = function (date, format) {
            return GlobalService.date_format(date, format);
        }
        $scope.privacy = function (ActivityGuID, privacy) {
            var data = [];
            jsonData = {
                ActivityGuID: ActivityGuID,
                Visibility: privacy
            };
            WallService.CallApi(jsonData, 'activity/privacyChange').then(function (response) {
                if (response.ResponseCode == 200) {
                    data['Visibility'] = privacy;
                    $scope.updateActivityData(ActivityGuID, data);
                    if (IsNewsFeed == '1')
                    {
                        $scope.updatePopularData(ActivityGuID, data);
                    }
                }
            });
        }

        $scope.addToChecked = function (CategoryID)
        {
            angular.forEach($scope.non_loggedin_interest, function (val, key) {
                if (val.CategoryID == CategoryID)
                {
                    $scope.non_loggedin_interest_checked.unshift(val);
                    $scope.non_loggedin_interest.splice(key, 1);
                    return;
                }
            });
        }

        $scope.addToNonChecked = function (CategoryID)
        {
            angular.forEach($scope.non_loggedin_interest_checked, function (val, key) {
                if (val.CategoryID == CategoryID)
                {
                    $scope.non_loggedin_interest.unshift(val);
                    $scope.non_loggedin_interest_checked.splice(key, 1);
                    return;
                }
            });
        }

        $scope.non_loggedin_interest = [];
        $scope.non_loggedin_interest_checked = [];
        var firstCall = true;
        $scope.get_popular_interest = function (search)
        {
            var exclude = [];
            if ($scope.non_loggedin_interest_checked.length > 0)
            {
                angular.forEach($scope.non_loggedin_interest_checked, function (val, key) {
                    exclude.push(val.CategoryID);
                });
            }

            var jsonData = {Keyword: search, PageNo: 1, PageSize: 5, Exclude: exclude};
            WallService.CallApi(jsonData, 'users/get_popular_interest').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (firstCall)
                    {
                        $scope.non_loggedin_interest_checked = response.Data;
                        firstCall = false;
                    } else
                    {
                        $scope.non_loggedin_interest = [];
                        angular.forEach(response.Data, function (v, k) {
                            var append = true;
                            angular.forEach($scope.non_loggedin_interest_checked, function (val, key) {
                                if (v.CategoryID == val.CategoryID)
                                {
                                    append = false;
                                }
                            });
                            if (append)
                            {
                                $scope.non_loggedin_interest.push(v);
                            }
                        });
                    }
                }
            });
        }

        $scope.appendCommentData = function (comment_data)
        {
            var Isspush = true;
            angular.forEach(NewsFeedCtrl.activityData, function (value, key) {

                var ActivityGUID = NewsFeedCtrl.activityData[key].ActivityGUID;
                var newArr = new Array();

                angular.forEach(NewsFeedCtrl.activityData[key].Comments, function (v, k) {
                    if (NewsFeedCtrl.activityData[key].Comments[k].CommentGUID == comment_data.CommentGUID)
                    {
                        Isspush = false;
                        NewsFeedCtrl.activityData[key].Comments[k] = comment_data;
                    }
                })
                if (Isspush)
                {
                    NewsFeedCtrl.activityData[key].Comments.push(comment_data);
                }
                /*$(NewsFeedCtrl.activityData[key].Comments).each(function (k, value) {
                 newArr.push(NewsFeedCtrl.activityData[key].Comments[k]);
                 });
                 newArr.push(comment_data);
                 NewsFeedCtrl.activityData[key].Comments = newArr.reduce(function (o, v, i) {
                 o[i] = v;
                 return o;
                 }, {});*/
                // NewsFeedCtrl.activityData[key].Comments = newArr;
                // NewsFeedCtrl.activityData[key].Comments = NewsFeedCtrl.activityData[key].Comments[0];
                NewsFeedCtrl.activityData[key].NoOfComments = parseInt(NewsFeedCtrl.activityData[key].NoOfComments) + 1;
                NewsFeedCtrl.activityData[key].comntData = $scope.$broadcast('appendComntEmit', NewsFeedCtrl.activityData[key].Comments); //getPostComments(NewsFeedCtrl.activityData[key].Comments);

                $('#upload-btn-' + ActivityGUID).show();
                $('#cm-' + ActivityGUID).html('');

                $('#cm-' + ActivityGUID + ' li').remove();
                $('#cm-' + ActivityGUID).hide();
                $('#act-' + ActivityGUID + ' .attach-on-comment').show();
            });
            if (IsNewsFeed == '1')
            {
                $($scope.popularData).each(function (key, value) {
                    var ActivityGUID = $scope.popularData[key].ActivityGUID;
                    var newArr = new Array();
                    $($scope.popularData[key].Comments).each(function (k, value) {
                        newArr.push($scope.popularData[key].Comments[k]);
                    });
                    newArr.push(comment_data);
                    $scope.popularData[key].Comments = newArr.reduce(function (o, v, i) {
                        o[i] = v;
                        return o;
                    }, {});
                    $scope.popularData[key].Comments = newArr;
                    // NewsFeedCtrl.activityData[key].Comments = NewsFeedCtrl.activityData[key].Comments[0];
                    $scope.popularData[key].NoOfComments = parseInt($scope.popularData[key].NoOfComments) + 1;
                    $scope.popularData[key].comntData = $scope.$broadcast('appendComntEmit', $scope.popularData[key].Comments); //getPostComments(NewsFeedCtrl.activityData[key].Comments);

                    $('#upload-btn-' + ActivityGUID).show();
                    $('#cm-' + ActivityGUID).html('');

                    $('#cm-' + ActivityGUID + ' li').remove();
                    $('#cm-' + ActivityGUID).hide();
                    $('#act-' + ActivityGUID + ' .attach-on-comment').show();
                });
            }
        }
        $scope.restore = function (EntityGUID) {
            var myid = '';
            for (i in NewsFeedCtrl.activityData) {
                if (NewsFeedCtrl.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Restore Post", "Are you sure, you want to restore this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/restoreActivity').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    NewsFeedCtrl.activityData.splice(myid, 1);
                                }
                            });
                        }
                    });
                }
            }
        }
        $scope.delete = function (EntityGUID) {
            var myid = '';
            for (i in NewsFeedCtrl.activityData) {
                if (NewsFeedCtrl.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Delete Post", "Are you sure, you want to delete this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    $scope.tr--;
                                    if (NewsFeedCtrl.activityData[myid].IsFavourite == 1) {
                                        $scope.tfr--;
                                        if ($scope.tfr == 0) {
                                        }
                                    }
                                    NewsFeedCtrl.activityData.splice(myid, 1);
                                    if ($scope.tr == 0) {
                                        $scope.wallReqCnt = 1;
                                    }
                                    if (!$scope.IsActiveFilter) {
                                        setTimeout(function () {
                                            if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                                $('#FilterButton').show();
                                            } else {
                                                $('#FilterButton').hide();
                                            }
                                        }, 1000);
                                    }
                                }
                            });
                            $('.ra-' + EntityGUID).parent('li').remove();
                        }
                    });
                }
            }
        }
        $scope.removeTag = function (ActivityGUID) {
            showConfirmBox("Remove Tag", "Are you sure, you want to remove tag from this post ?", function (e) {
                if (e) {
                    var jsonData = {
                        ActivityGUID: ActivityGUID
                    };
                    WallService.CallApi(jsonData, 'activity/remove_tags').then(function (response) {
                        if (response.ResponseCode == 200) {
                            var data = [];
                            data['IsTagged'] = 0;
                            data['IsSubscribed'] = 0;
                            data['PostContent'] = response.Data.PostContent;
                            $scope.updateActivityData(ActivityGUID, data);
                            if (IsNewsFeed == '1')
                            {
                                $scope.updatePopularData(ActivityGUID, data);
                            }
                        }
                    });
                }
            });
        }
        $scope.sticky = function (ActivityGUID) {
            reqData = {
                EntityGUID: ActivityGUID
            };
            WallService.CallApi(reqData, 'activity/toggle_sticky').then(function (response) {
                if (response.ResponseCode == 200) {
                    var append = false;
                    $(NewsFeedCtrl.activityData).each(function (key, val) {
                        if (ActivityGUID == NewsFeedCtrl.activityData[key].ActivityGUID) {
                            if (NewsFeedCtrl.activityData[key].IsSticky == 0) {
                                NewsFeedCtrl.activityData[key].IsSticky = 1;
                                var newD = NewsFeedCtrl.activityData[key];
                                NewsFeedCtrl.activityData.splice(key, 1);
                                NewsFeedCtrl.activityData.splice(0, 0, newD);
                                return false;
                            } else {
                                NewsFeedCtrl.activityData[key].IsSticky = 0;
                                var newD = NewsFeedCtrl.activityData[key];
                                if (NewsFeedCtrl.activityData.length > 1) {
                                    NewsFeedCtrl.activityData.splice(key, 1);
                                    $(NewsFeedCtrl.activityData).each(function (k, v) {
                                        if (NewsFeedCtrl.activityData[k].IsSticky == 0) {
                                            NewsFeedCtrl.activityData.splice(k, 0, newD);
                                            return false;
                                        }
                                    });
                                    if (!append) {
                                        NewsFeedCtrl.activityData.splice(NewsFeedCtrl.activityData.length, 0, newD);
                                    }
                                }
                                return false;
                            }
                        }
                    });

                    if (IsNewsFeed == '1')
                    {
                        var append = false;
                        $($scope.popularData).each(function (key, val) {
                            if (ActivityGUID == $scope.popularData[key].ActivityGUID) {
                                if ($scope.popularData[key].IsSticky == 0) {
                                    $scope.popularData[key].IsSticky = 1;
                                    var newD = $scope.popularData[key];
                                    $scope.popularData.splice(key, 1);
                                    $scope.popularData.splice(0, 0, newD);
                                    return false;
                                } else {
                                    $scope.popularData[key].IsSticky = 0;
                                    var newD = $scope.popularData[key];
                                    if ($scope.popularData.length > 1) {
                                        $scope.popularData.splice(key, 1);
                                        $($scope.popularData).each(function (k, v) {
                                            if ($scope.popularData[k].IsSticky == 0) {
                                                $scope.popularData.splice(k, 0, newD);
                                                return false;
                                            }
                                        });
                                        if (!append) {
                                            $scope.popularData.splice(NewsFeedCtrl.activityData.length, 0, newD);
                                        }
                                    }
                                    return false;
                                }
                            }
                        });
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.approveFlagActivity = function (ActivityGUID) {
            var reqData = {
                EntityGUID: ActivityGUID
            };
            WallService.CallApi(reqData, 'activity/approveFlagActivity').then(function (response) {
                $(NewsFeedCtrl.activityData).each(function (k, v) {
                    if (NewsFeedCtrl.activityData[k].ActivityGUID == ActivityGUID) {
                        NewsFeedCtrl.activityData[k].FlaggedByAny = 0;
                        showResponseMessage('Flag has been approved successfully.', 'alert-success');
                        if ($('#ActivityFilterType').val() == 2) {
                            NewsFeedCtrl.activityData.splice(k, 1);
                        }
                    }
                });
                if (IsNewsFeed == '1')
                {
                    $($scope.popularData).each(function (k, v) {
                        if ($scope.popularData[k].ActivityGUID == ActivityGUID) {
                            $scope.popularData[k].FlaggedByAny = 0;
                            showResponseMessage('Flag has been approved successfully.', 'alert-success');
                            if ($('#ActivityFilterType').val() == 2) {
                                $scope.popularData.splice(k, 1);
                            }
                        }
                    });
                }
            });
        }
        $scope.subscribe = function (EntityType, EntityGUID) {
            var reqData = {
                EntityType: EntityType,
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'subscribe/toggle_subscribe').then(function (response) {
                if (response.ResponseCode == 200) {
                    var data = [];
                    if (EntityType == 'ACTIVITY') {
                        data['IsSubscribed'] = response.Data.IsSubscribed;
                        $scope.updateActivityData(EntityGUID, data);
                        if (IsNewsFeed == '1')
                        {
                            $scope.updatePopularData(EntityGUID, data);
                        }
                    }
                    setTimeout(function () {
                        $('[data-toggle="tooltip"]').tooltip({
                            container: "body"
                        });
                    }, 100);
                }
            });
        }
        $scope.muteUser = function (ModuleID, ModuleEntityGUID) {
            if (ModuleID != 18) {
                ModuleID = 3;
            }
            showConfirmBox("Mute Source", "Are you sure, you want to mute this source ?", function (e) {
                if (e) {
                    var reqData = {
                        ModuleEntityGUID: ModuleEntityGUID,
                        ModuleID: ModuleID
                    };
                    WallService.CallApi(reqData, 'users/mute_source').then(function (response) {
                        if (response.ResponseCode == 200) {
                            $scope.getFilteredWall();
                        }
                    });
                }
            });
        }
        $scope.getFilteredWall = function () {
            $('body').scrollTop(0);
            //$scope.displayLoader();
            $scope.stopExecution = 0;
            $scope.WallPageNo = 1;
            $scope.busy = false;
            if ($('#IsWiki').length > 0)
            {
                $('#WallPageNo').val(1);
                $scope.stop_article_execution = 0;
                $scope.get_wiki_post();
                $scope.article_list = new Array();
            } else
            {
                $scope.GetwallPost();
                var NewsFeedCtrl = angular.element(document.getElementById('NewsFeedCtrl')).scope();
                if (typeof NewsFeedCtrl !== 'undefined')
                {
                    NewsFeedCtrl.activityData = new Array();
                }
            }
            $scope.busy = false;
            //$('.loader-fad,.loader-view').show();
        }
        $scope.hideLoader = function () {
            $scope.showLoader = 0;
            //$('.loader-fad,.loader-view').css('display', 'none');
        }
        $scope.displayLoader = function () {
            $scope.showLoader = 1;
            //$('.loader-fad,.loader-view').css('display', 'block');
        }
        $scope.blockUserEmit = function (UserGUID, ModuleID, ModuleEntityGUID) {
            var reqData = {
                EntityGUID: UserGUID
            };

            if (ModuleID)
            {
                reqData['ModuleID'] = ModuleID;
            } else
            {
                ModuleID = $('#module_id').val();
            }
            if (ModuleEntityGUID)
            {
                reqData['ModuleEntityGUID'] = ModuleEntityGUID;
            } else
            {
                reqData['ModuleEntityGUID'] = $('#module_entity_guid').val();
            }

            var m = "";
            if (ModuleID == '1')
            {
                m = "Are you sure, you want to block this user from the group?";
            }
            if (ModuleID == '3')
            {
                m = "Are you sure, you want to block this user? After that you won't be able to send or receive friend request or search this user.";
            }
            if (ModuleID == '14')
            {
                m = "Are you sure, you want to block this user from the event?";
            }
            if (ModuleID == '18')
            {
                m = "Are you sure, you want to block this user from the page?";
            }

            showConfirmBox("Block User", m, function (e) {
                if (e) {
                    WallService.CallApi(reqData, 'activity/blockUser').then(function (response) {
                        if (response.ResponseCode == 200) {
                            showResponseMessage('User has been blocked successfully.', 'alert-success');
                            setTimeout(function () {
                                window.location.reload();
                            }, 5000);
                        }
                    });
                }
            });
        }
        $scope.restoreEmit = function (EntityGUID) {
            var myid = '';
            for (i in NewsFeedCtrl.activityData) {
                if (NewsFeedCtrl.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Restore Post", "Are you sure, you want to restore this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/restoreActivity').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    NewsFeedCtrl.activityData.splice(myid, 1);
                                }
                            });
                        }
                    });
                }
            }
        }
        $scope.deleteEmit = function (EntityGUID, IsAnnouncement) {

            var myid = '';
            var ActivityGUID = '';
            if (IsAnnouncement == '1')
            {
                for (i in $scope.group_announcements) {
                    if ($scope.group_announcements[i].ActivityGUID == EntityGUID) {
                        myid = i;
                        ActivityGUID = $scope.group_announcements[i].ActivityGUID;
                        showConfirmBox("Delete Post", "Are you sure, you want to delete this post ?", function (e) {
                            if (e) {
                                var reqData = {
                                    EntityGUID: EntityGUID
                                };
                                WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                                    if (response.ResponseCode == 200) {

                                        $rootScope.$broadcast('onPostRemoved', {
                                            EntityGUID: EntityGUID
                                        });

                                        $('.note-editable').html('');
                                        $('.place-holder-label').show();
                                        $('.comment-section').addClass('hide');
                                        if (angular.element('#ForumCtrl').length > 0) {

                                            forumScope = angular.element('#ForumCtrl').scope();
                                            forumScope.category_detail.NoOfDiscussions = parseInt(forumScope.category_detail.NoOfDiscussions) - 1;
                                        }

                                        $scope.markUnmarkAsSticky(ActivityGUID, 3, 'remove', myid, true);
                                        $scope.tr--;
                                        if ($scope.group_announcements[myid].IsFavourite == 1) {
                                            $scope.tfr--;
                                            if ($scope.tfr == 0) {
                                            }
                                        }
                                        $scope.group_announcements.splice(myid, 1);
                                        if ($scope.tr == 0) {
                                            $scope.wallReqCnt = 1;
                                        }
                                        if (!$scope.IsActiveFilter) {
                                            setTimeout(function () {
                                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                                    $('#FilterButton').show();
                                                } else {
                                                    $('#FilterButton').hide();
                                                }
                                            }, 1000);
                                        }
                                    }
                                });
                                $('.ra-' + EntityGUID).parent('li').remove();
                            }
                        });
                    }
                }
            } else
            {
                for (i in NewsFeedCtrl.activityData) {
                    if (NewsFeedCtrl.activityData[i].ActivityGUID == EntityGUID) {
                        myid = i;
                        ActivityGUID = NewsFeedCtrl.activityData[i].ActivityGUID;
                        showConfirmBox("Delete Post", "Are you sure, you want to delete this post ?", function (e) {
                            if (e) {
                                var reqData = {
                                    EntityGUID: EntityGUID
                                };
                                WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        if ($scope.IsSingleActivity)
                                        {
                                            window.top.location = base_url;
                                        }
                                        $rootScope.$broadcast('onPostRemoved', {
                                            EntityGUID: EntityGUID
                                        });

                                        $('.note-editable').html('');
                                        $('.place-holder-label').show();
                                        $('.comment-section').addClass('hide');
                                        if (angular.element('#ForumCtrl').length > 0) {

                                            forumScope = angular.element('#ForumCtrl').scope();
                                            forumScope.category_detail.NoOfDiscussions = parseInt(forumScope.category_detail.NoOfDiscussions) - 1;
                                        }
                                        $scope.markUnmarkAsSticky(ActivityGUID, 3, 'remove', myid, true);
                                        $scope.tr--;
                                        if (NewsFeedCtrl.activityData[myid].IsFavourite == 1) {
                                            $scope.tfr--;
                                            if ($scope.tfr == 0) {
                                            }
                                        }
                                        NewsFeedCtrl.activityData.splice(myid, 1);
                                        if ($scope.tr == 0) {
                                            $scope.wallReqCnt = 1;
                                        }
                                        if (!$scope.IsActiveFilter) {
                                            setTimeout(function () {
                                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                                    $('#FilterButton').show();
                                                } else {
                                                    $('#FilterButton').hide();
                                                }
                                            }, 1000);
                                        }
                                    }
                                });
                                $('.ra-' + EntityGUID).parent('li').remove();
                            }
                        });
                    }
                }
            }
        }
        $scope.removeTagEmit = function (ActivityGUID) {
            showConfirmBox("Remove Tag", "Are you sure, you want to remove tag from this post ?", function (e) {
                if (e) {
                    var jsonData = {
                        ActivityGUID: ActivityGUID
                    };
                    WallService.CallApi(jsonData, 'activity/remove_tags').then(function (response) {
                        if (response.ResponseCode == 200) {
                            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                                if (val.ActivityGUID == ActivityGUID) {
                                    NewsFeedCtrl.activityData[key].IsTagged = 0;
                                    NewsFeedCtrl.activityData[key].IsSubscribed = 0;
                                    NewsFeedCtrl.activityData[key].PostContent = response.Data.PostContent;
                                    if (IsNewsFeed == '1')
                                    {
                                        $scope.popularData[key].IsTagged = 0;
                                        $scope.popularData[key].IsSubscribed = 0;
                                        $scope.popularData[key].PostContent = response.Data.PostContent;
                                    }
                                }
                            });
                        }
                    });
                }
            });
        };

        $scope.stickyPostIndex = -1;
        $scope.popupType = 'tutorial';
        $scope.toggleStickyPopup = function (action, popupType) { //Open sticky popup
            if (action == 'open') {
                if (popupType != 'tutorial') {
                    $scope.isOverlayActive = true;
                    if (NewsFeedCtrl.activityData[0]) {
                        NewsFeedCtrl.activityData[0]['stickynote'] = true;
                    }
                    $scope.ShowWallPostOnFilesTab = true;
                    if (angular.element("#activityFeedId-0").offset() && (angular.element("#activityFeedId-0").offset().top > 0)) {
                        angular.element('html, body').animate({
                            scrollTop: (parseInt(angular.element("#activityFeedId-0").offset().top - 100))
                        }, 1000);
                    }
                } else {
                    $scope.isOverlayActive = true;
                    $scope.stickynote = true;
//              $timeout(function(){
//                angular.element('html, body').animate({
//                    scrollTop: angular.element("#stickyTutorialBox").offset().top - 100
//                }, 800);
//              }, 500);
                }
                $scope.popupType = popupType;
            } else {
                $scope.isOverlayActive = false;
                $scope.stickynote = false;
                $scope.ShowWallPostOnFilesTab = false;
                if (NewsFeedCtrl.activityData[0]) {
                    NewsFeedCtrl.activityData[0]['stickynote'] = false;
                    $scope.stopExecution = 0;
                    $scope.busy = false;
                    if (!$scope.isFileTab && !$scope.isLinkTab && (popupType !== 'tutorial')) {
                        $scope.GetwallPost(undefined, true);
                    }
                }
                $scope.IsSingleActivity = false;
            }
        };

        $scope.$on('toggleStickyPopup', function (event, stickyData) {
            if (stickyData && stickyData.ActivityGUID) {
                $scope.stopExecution = 0;
                $scope.busy = false;
                if ($scope.isFileTab || $scope.isLinkTab) {
                    $scope.isActivityPrevented = false;
                }
                $scope.GetwallPost(stickyData.ActivityGUID, 'Sticky');
            } else if (stickyData && stickyData.action && stickyData.popupType && (stickyData.popupType === 'tutorial')) {
                $scope.toggleStickyPopup(stickyData.action, stickyData.popupType);
            }
        });

        $scope.markUnmarkAsStickyAnn = function (ActivityGUID, makeStickyFor, stickyAction, feedIndex, fromPostRemoval) { //Create or delete sticky
            var reqData = {
                ActivityGUID: ActivityGUID,
                StickyType: makeStickyFor ? makeStickyFor : 1
            };
            var url = (stickyAction === 'create') ? 'create_sticky' : 'remove_sticky';
            WallService.CallPostApi(appInfo.serviceUrl + 'sticky/' + url, reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    var entity = '';
                    switch (makeStickyFor) {
                        case 1:
                            entity = ' you ';
                            break;
                        case 2:
                            entity = ' group ';
                            break;
                        case 3:
                            entity = ' everyone ';
                            break;
                        default:
                        //default code block
                    }
                    if ($scope.group_announcements[feedIndex]) {
                        var responseJson = response.Data;
                        if ((stickyAction === 'create') && (Object.keys(responseJson).length > 0)) {
                            $scope.group_announcements[feedIndex].SelfSticky = responseJson.SelfSticky;
                            $scope.group_announcements[feedIndex].GroupSticky = responseJson.GroupSticky;
                            $scope.group_announcements[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                            $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson, ModuleID: NewsFeedCtrl.activityData[feedIndex].ModuleID});
                            showResponseMessage('This post has been marked as sticky for' + entity + '.', 'alert-success');
                            //                  angular.element(document.getElementById('StickyPostController')).scope().checkCoverExists();
                        } else if (stickyAction === 'remove') {
                            if (Object.keys(responseJson).length > 0) {
                                $scope.group_announcements[feedIndex].SelfSticky = responseJson.SelfSticky;
                                $scope.group_announcements[feedIndex].GroupSticky = responseJson.GroupSticky;
                                $scope.group_announcements[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                            } else {
                                switch (makeStickyFor) {
                                    case 1:
                                        $scope.group_announcements[feedIndex].SelfSticky = 0;
                                        break;
                                    case 2:
                                        $scope.group_announcements[feedIndex].SelfSticky = 0;
                                        $scope.group_announcements[feedIndex].GroupSticky = 0;
                                        break;
                                    case 3:
                                        $scope.group_announcements[feedIndex].SelfSticky = 0;
                                        $scope.group_announcements[feedIndex].GroupSticky = 0;
                                        $scope.group_announcements[feedIndex].EveryoneSticky = 0;
                                        break;
                                    default:
                                    //default code block
                                }
                            }
                            responseJson = (Object.keys(responseJson).length > 0) ? responseJson : {ActivityGUID: ActivityGUID};
                            $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson});
                            if (!fromPostRemoval) {
                                showResponseMessage('This post is no more sticky for' + entity + '.', 'alert-success');
                            }
                        }
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        };

        $scope.markUnmarkAsSticky = function (ActivityGUID, makeStickyFor, stickyAction, feedIndex, fromPostRemoval) { //Create or delete sticky
            var reqData = {
                ActivityGUID: ActivityGUID,
                StickyType: makeStickyFor ? makeStickyFor : 1
            };
            var url = (stickyAction === 'create') ? 'create_sticky' : 'remove_sticky';
            WallService.CallPostApi(appInfo.serviceUrl + 'sticky/' + url, reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    var entity = '';
                    switch (makeStickyFor) {
                        case 1:
                            entity = ' you ';
                            break;
                        case 2:
                            entity = ' group ';
                            break;
                        case 3:
                            entity = ' everyone ';
                            break;
                        default:
                        //default code block
                    }
                    if (NewsFeedCtrl.activityData[feedIndex]) {
                        var responseJson = response.Data;
                        if ((stickyAction === 'create') && (Object.keys(responseJson).length > 0)) {
                            NewsFeedCtrl.activityData[feedIndex].SelfSticky = responseJson.SelfSticky;
                            NewsFeedCtrl.activityData[feedIndex].GroupSticky = responseJson.GroupSticky;
                            NewsFeedCtrl.activityData[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                            $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson, ModuleID: NewsFeedCtrl.activityData[feedIndex].ModuleID});
                            showResponseMessage('This post has been marked as sticky for' + entity + '.', 'alert-success');
                            //                  angular.element(document.getElementById('StickyPostController')).scope().checkCoverExists();
                        } else if (stickyAction === 'remove') {
                            if (Object.keys(responseJson).length > 0) {
                                NewsFeedCtrl.activityData[feedIndex].SelfSticky = responseJson.SelfSticky;
                                NewsFeedCtrl.activityData[feedIndex].GroupSticky = responseJson.GroupSticky;
                                NewsFeedCtrl.activityData[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                            } else {
                                switch (makeStickyFor) {
                                    case 1:
                                        NewsFeedCtrl.activityData[feedIndex].SelfSticky = 0;
                                        break;
                                    case 2:
                                        NewsFeedCtrl.activityData[feedIndex].SelfSticky = 0;
                                        NewsFeedCtrl.activityData[feedIndex].GroupSticky = 0;
                                        break;
                                    case 3:
                                        NewsFeedCtrl.activityData[feedIndex].SelfSticky = 0;
                                        NewsFeedCtrl.activityData[feedIndex].GroupSticky = 0;
                                        NewsFeedCtrl.activityData[feedIndex].EveryoneSticky = 0;
                                        break;
                                    default:
                                    //default code block
                                }
                            }
                            responseJson = (Object.keys(responseJson).length > 0) ? responseJson : {ActivityGUID: ActivityGUID};
                            $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson});
                            if (!fromPostRemoval) {
                                showResponseMessage('This post is no more sticky for' + entity + '.', 'alert-success');
                            }
                        }
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        };

        $scope.$on('updateStickyToNewsFeed', function (event, sticky) {
            if (NewsFeedCtrl.activityData.length) {
                var responseJson = sticky.stickyDataToUpdate;
                angular.forEach(NewsFeedCtrl.activityData, function (activity, feedIndex) {
                    if (activity.ActivityGUID === responseJson.ActivityGUID) {
                        if (responseJson.SelfSticky) {
                            NewsFeedCtrl.activityData[feedIndex].SelfSticky = responseJson.SelfSticky;
                            NewsFeedCtrl.activityData[feedIndex].GroupSticky = responseJson.GroupSticky;
                            NewsFeedCtrl.activityData[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                        } else {
                            switch (sticky.removeStickyFor) {
                                case 1:
                                    NewsFeedCtrl.activityData[feedIndex].SelfSticky = 0;
                                    break;
                                case 2:
                                    NewsFeedCtrl.activityData[feedIndex].SelfSticky = 0;
                                    NewsFeedCtrl.activityData[feedIndex].GroupSticky = 0;
                                    break;
                                case 3:
                                    NewsFeedCtrl.activityData[feedIndex].SelfSticky = 0;
                                    NewsFeedCtrl.activityData[feedIndex].GroupSticky = 0;
                                    NewsFeedCtrl.activityData[feedIndex].EveryoneSticky = 0;
                                    break;
                                default:
                                //default code block
                            }
                        }
                        return false;
                    }
                });
            }
        });

        $scope.approveFlagActivityEmit = function (ActivityGUID) {
            var reqData = {
                EntityGUID: ActivityGUID
            };
            WallService.CallApi(reqData, 'activity/approveFlagActivity').then(function (response) {
                $(NewsFeedCtrl.activityData).each(function (k, v) {
                    if (NewsFeedCtrl.activityData[k].ActivityGUID == ActivityGUID) {
                        NewsFeedCtrl.activityData[k].FlaggedByAny = 0;
                        showResponseMessage('Flag has been approved successfully.', 'alert-success');
                        if ($('#ActivityFilterType').val() == 2) {
                            NewsFeedCtrl.activityData.splice(k, 1);
                        }
                    }
                });
            });
        }
        $scope.subscribeEmit = function (EntityType, EntityGUID, IsAnnouncement) {
            var reqData = {
                EntityType: EntityType,
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'subscribe/toggle_subscribe').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (IsAnnouncement == '1')
                    {
                        $($scope.group_announcements).each(function (key, val) {
                            if ($scope.group_announcements[key].ActivityGUID == EntityGUID) {
                                $scope.group_announcements[key].IsSubscribed = response.Data.IsSubscribed;
                                setTimeout(function () {
                                    $('[data-toggle="tooltip"]').tooltip({
                                        container: "body"
                                    });
                                }, 100);
                            }
                        });
                    } else
                    {
                        $(NewsFeedCtrl.activityData).each(function (key, val) {
                            if (NewsFeedCtrl.activityData[key].ActivityGUID == EntityGUID) {
                                NewsFeedCtrl.activityData[key].IsSubscribed = response.Data.IsSubscribed;
                                setTimeout(function () {
                                    $('[data-toggle="tooltip"]').tooltip({
                                        container: "body"
                                    });
                                }, 100);
                            }
                        });
                    }
                    if (IsNewsFeed == '1')
                    {
                        $($scope.popularData).each(function (key, val) {
                            if ($scope.popularData[key].ActivityGUID == EntityGUID) {
                                $scope.popularData[key].IsSubscribed = response.Data.IsSubscribed;
                                setTimeout(function () {
                                    $('[data-toggle="tooltip"]').tooltip({
                                        container: "body"
                                    });
                                }, 100);
                            }
                        });
                    }
                }
            });
        }
        $scope.muteUserEmit = function (ModuleID, ModuleEntityGUID) {
            if (ModuleID != 18) {
                ModuleID = 3;
            }
            showConfirmBox("Mute Source", "Are you sure, you want to mute this source ?", function (e) {
                if (e) {
                    var reqData = {
                        ModuleEntityGUID: ModuleEntityGUID,
                        ModuleID: ModuleID
                    };
                    WallService.CallApi(reqData, 'users/mute_source').then(function (response) {
                        if (response.ResponseCode == 200) {
                            $scope.getFilteredWall();
                        }
                    });
                }
            });
        }

        $scope.post_tags = [];
        $scope.updateWallPost = function (tags)
        {
            $scope.Filter.IsSetFilter = true
            $scope.post_tags = tags;
            //if($scope.search_tags)
            if (!angular.isUndefined(tags)) {
                $scope.search_tags = tags;
            }
            //console.log($scope.post_tags);
            $scope.getFilteredWall();
        }

        $scope.commentsSwitchEmit = function (EntityType, EntityGUID, IsAnnouncement) {
            var reqData = {
                EntityType: EntityType,
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'activity/commentStatus').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (IsAnnouncement == '1')
                    {
                        $($scope.group_announcements).each(function (key, val) {
                            if ($scope.group_announcements[key].ActivityGUID == EntityGUID) {
                                //console.log('here');
                                if ($scope.group_announcements[key].CommentsAllowed == 1) {
                                    $scope.group_announcements[key].CommentsAllowed = 0;
                                } else {
                                    $scope.group_announcements[key].CommentsAllowed = 1;
                                }
                            }
                        });
                    } else
                    {
                        $(NewsFeedCtrl.activityData).each(function (key, val) {
                            if (NewsFeedCtrl.activityData[key].ActivityGUID == EntityGUID) {
                                //console.log('here');
                                if (NewsFeedCtrl.activityData[key].CommentsAllowed == 1) {
                                    NewsFeedCtrl.activityData[key].CommentsAllowed = 0;
                                } else {
                                    NewsFeedCtrl.activityData[key].CommentsAllowed = 1;
                                }
                            }
                            if (IsNewsFeed == '1')
                            {
                                $($scope.popularData).each(function (key, val) {
                                    if ($scope.popularData[key].ActivityGUID == EntityGUID) {
                                        if ($scope.popularData[key].CommentsAllowed == 1) {
                                            $scope.popularData[key].CommentsAllowed = 0;
                                        } else {
                                            $scope.popularData[key].CommentsAllowed = 1;
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
            });
        }
        $scope.toggleArchiveEmit = function (ActivityGUID) {
            jsonData = {
                ActivityGUID: ActivityGUID
            };
            WallService.CallApi(jsonData, 'activity/toggle_archive').then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            $scope.tr--;
                            NewsFeedCtrl.activityData.splice(key, 1);
                        }
                    });
                    if (IsNewsFeed == '1')
                    {
                        angular.forEach($scope.popularData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                $scope.tr--;
                                $scope.popularData.splice(key, 1);
                            }
                        });
                    }
                }
            });
            $('.tooltip').remove();
        }
        $scope.changeReminderStatusClick = function (ActivityGUID, ReminderGUID, Status) {
            if (Status == 'DELETED') {
                showConfirmBox("Remove Reminder", "Are you sure, you want to remove this reminder ?", function (e) {
                    if (e) {
                        $scope.deleteReminder(ActivityGUID, ReminderGUID, Status);
                        $scope.trr--;
                        setTimeout(function () {
                            if (!$('.news-feed-listing').is(':visible') && $scope.IsReminder == 1) {
                                $scope.applyFilterType('3');
                            }
                        }, 800);
                    }
                });
            }
            if (Status == 'ARCHIVED' || Status == 'ACTIVE') {
                if (Status == 'ARCHIVED') {
                    var msgTitle = "Reminder Archived";
                    var msgBody = "Are you sure, you want to archive this reminder ?";
                } else if (Status == 'ACTIVE') {
                    var msgTitle = "Restore Reminder";
                    var msgBody = "Are you sure, you want to restore this reminder ?";
                }
                showConfirmBox(msgTitle, msgBody, function (e) {
                    if (e) {
                        $scope.changeReminderStatus(ActivityGUID, ReminderGUID, Status);
                    }
                });
            }
        }
        $scope.FlagUserEmit = function (ActivityGUID) {
            $scope.flagUserData = [];
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'activity/flag_users_detail').then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.flagUserData = response.Data;
                }
            });
        }
        $scope.commentsSwitch = function (EntityType, EntityGUID) {
            var reqData = {
                EntityType: EntityType,
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'activity/commentStatus').then(function (response) {
                if (response.ResponseCode == 200) {
                    $(NewsFeedCtrl.activityData).each(function (key, val) {
                        if (NewsFeedCtrl.activityData[key].ActivityGUID == EntityGUID) {
                            if (NewsFeedCtrl.activityData[key].CommentsAllowed == 1) {
                                NewsFeedCtrl.activityData[key].CommentsAllowed = 0;
                            } else {
                                NewsFeedCtrl.activityData[key].CommentsAllowed = 1;
                            }
                        }
                    });
                    if (IsNewsFeed == '1')
                    {
                        $($scope.popularData).each(function (key, val) {
                            if ($scope.popularData[key].ActivityGUID == EntityGUID) {
                                if ($scope.popularData[key].CommentsAllowed == 1) {
                                    $scope.popularData[key].CommentsAllowed = 0;
                                } else {
                                    $scope.popularData[key].CommentsAllowed = 1;
                                }
                            }
                        });
                    }
                }
            });
        }
        $scope.viewAllComnt = function (i, ActivityGUID) {
            var reqData = {
                EntityGUID: ActivityGUID
            };
            $("#cmt_loader_" + ActivityGUID).show();
            WallService.CallApi(reqData, 'activity/getAllComments').then(function (response) {
                if (response.ResponseCode == 200) {
                    var tempComntData = response.Data;
                    NewsFeedCtrl.activityData[i].Comments = [];
                    for (j in tempComntData) {

                        var frag = $("<div>").append($.parseHTML(j.PostComment));
                        // Find the relevant images
                        frag.find(".parseemoji").each(function() {
                          // Replace the image with a text node containing :::[dataattr2]:::
                          var $this = $(this);
                          var emoji_value = ':'+imageFromUnicode[$this.html()]+':';
                          $this.replaceWith($('<img src="'+AssetBaseUrl+'img/emoji/blank.gif" style="'+$this.attr("style")+'">').addClass("img tamemoji").attr('emoji',emoji_value).attr('alt',emoji_value));
                          //$this.replaceWith(document.createTextNode("< value='"+$this.attr("emoji")+"'></emoji>"));
                        });
                        // Get the HTML of the result
                        j.PostComment = frag.html();

                        NewsFeedCtrl.activityData[i].Comments.push(tempComntData[j]);
                    }
                    $(NewsFeedCtrl.activityData).each(function (k, v) {
                        if (NewsFeedCtrl.activityData[k].ActivityGUID == ActivityGUID) {
                            NewsFeedCtrl.activityData[k].viewStats = 0;
                        }
                    });
                    returnObj = NewsFeedCtrl.activityData[i].Comments
                    $scope.$broadcast('updateComntEmit', returnObj);
                    if (IsNewsFeed == '1')
                    {
                        var tempComntData = response.Data;
                        $scope.popularData[i].Comments = [];
                        for (j in tempComntData) {
                            $scope.popularData[i].Comments.push(tempComntData[j]);
                        }
                        $($scope.popularData).each(function (k, v) {
                            if ($scope.popularData[k].ActivityGUID == ActivityGUID) {
                                $scope.popularData[k].viewStats = 0;
                            }
                        });
                        returnObj = $scope.popularData[i].Comments
                        $scope.$broadcast('updateComntEmit', returnObj);
                    }
                }
            });
        }
        $scope.shareEmit = function (ActivityGUID, fn) {
            lazyLoadCS.loadModule({
                moduleName: 'sharePopupMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/sharePopupMdl.js' + $scope.app_version,
                templateUrl: $scope.partialURL + 'share_popup.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'share_popup_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('sharePopupMdlInit', {
                        params: params,
                        wallScope: $scope,
                        ActivityGUID: ActivityGUID,
                        fn: fn
                    });
                },
            });
        };
        $scope.share = function (ActivityGUID) {
            $scope.shareEmit(ActivityGUID, 'share');
        };

        $scope.shareEmitAnnouncement = function (ActivityGUID) {
            $scope.shareEmit(ActivityGUID, 'shareEmitAnnouncement');
        };

        $scope.getSingleAnnouncement = function (ActivityGUID) {
            $($scope.group_announcements).each(function (k, v) {
                if ($scope.group_announcements[k].ActivityGUID == ActivityGUID) {
                    $scope.singleActivity = $scope.group_announcements[k];
                    $scope.singleActivity['mediaData'] = '';
                    if (typeof $scope.singleActivity.Album[0] !== 'undefined') {
                        if ($scope.singleActivity.Album[0].length > 0) {
                            $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                        }
                        $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                    } else {
                        $scope.singleActivity.mediaData = [];
                    }
                }
            });
        };

        $scope.getSingleActivity = function (ActivityGUID) {
            $(NewsFeedCtrl.activityData).each(function (k, v) {
                if (NewsFeedCtrl.activityData[k].ActivityGUID == ActivityGUID) {
                    if (NewsFeedCtrl.activityData[k]['ActivityType'] == 'Share' || NewsFeedCtrl.activityData[k]['ActivityType'] == 'ShareSelf' || NewsFeedCtrl.activityData[k]['ActivityType'] == 'ShareMedia' || NewsFeedCtrl.activityData[k]['ActivityType'] == 'ShareMediaSelf')
                    {
                        $scope.singleActivity = NewsFeedCtrl.activityData[k].ShareDetails;
                    } else
                    {
                        $scope.singleActivity = NewsFeedCtrl.activityData[k];
                    }
                    $scope.singleActivity['OriginalActivityType'] = NewsFeedCtrl.activityData[k].ActivityType;
                    $scope.singleActivity['OriginalPostContent'] = NewsFeedCtrl.activityData[k].PostContent;
                    $scope.singleActivity['OriginalActivityGUID'] = NewsFeedCtrl.activityData[k].ActivityGUID;

                    $scope.singleActivity['mediaData'] = '';
                    if ('NotifyAll' in $scope.singleActivity) {
                        $scope.singleActivity.NotifyAll = $scope.singleActivity.NotifyAll.toString();
                    }

                    if (typeof $scope.singleActivity.Album[0] !== 'undefined') {
                        if ($scope.singleActivity.Album[0].length > 0) {
                            $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                        }
                        $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                    } else {
                        $scope.singleActivity.mediaData = [];
                    }
                }
            });
        }

        $scope.getSingleActivityAnn = function (ActivityGUID)
        {
            $($scope.group_announcements).each(function (k, v) {
                if ($scope.group_announcements[k].ActivityGUID == ActivityGUID) {
                    $scope.singleActivity = $scope.group_announcements[k];
                    $scope.singleActivity['mediaData'] = '';
                    if (typeof $scope.singleActivity.Album[0] !== 'undefined') {
                        if ($scope.singleActivity.Album[0].length > 0) {
                            $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                        }
                        $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                    } else {
                        $scope.singleActivity.mediaData = [];
                    }
                }
            });
        }

        $scope.textToLinkComment = function (inputText) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                        var wrapped = $("<div>" + inputText + "</div>");                       
                        wrapped.find("a.linkify").contents().unwrap();
                        inputText = wrapped.html().toString();

                        inputText = inputText.replace('contenteditable', 'contenteditabletext');
                        var replacedText, replacePattern1, replacePattern2, replacePattern3;
                        replacedText = inputText.replace("<br>", " ||| ");
                        /*replacedText = replacedText.replace(/</g, '&lt');
                         replacedText = replacedText.replace(/>/g, '&gt');
                         replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                         replacedText = replacedText.replace(/&lt/g, '<');
                         replacedText = replacedText.replace(/&gt/g, '>');*/
                        //URLs starting with http://, https://, or ftp://
                        
                        replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                        replacedText = replacedText.replace(replacePattern1, function ($1) {
                            var link = $1;
                            var link2 = '';
                            var href = $1;
                            if (link.length > 35) {
                                link2 = link.substr(0, 25);
                                link2 += '...';
                                link2 += link.slice(-5);
                                link = link2;
                            }
                            var youtubeid = $scope.parseYoutubeVideo($1);
                            if (youtubeid) {
                                return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                            } else {
                                return href;
                            }
                        });
                        //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
                        replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
                        replacedText = replacedText.replace(replacePattern2, function ($1, $2) {

                            var link = $1;
                            var link2 = '';
                            var href = $1;
                            if (link.length > 35) {
                                link2 = link.substr(0, 25);
                                link2 += '...';
                                link2 += link.slice(-5);
                                link = link2;
                            }
                            href = href.trim();
                            var youtubeid = $scope.parseYoutubeVideo($1);
                            if (youtubeid) {
                                return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                            } else {
                                return href;
                            }

                        });
                        //Change email addresses to mailto:: links.
                        replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                        replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');
                        replacedText = replacedText.replace(" ||| ", "<br>");
                        var repTxt = removeTags(replacedText);
                        if (repTxt && (repTxt.length > 200)) {
                            replacedText = '<span class="show-less">' + smart_sub_str(200, replacedText, false) + '</span><span class="show-more">' +
                            replacedText + '</span>  ';
                        }
                        
                        replacedText = $sce.trustAsHtml(replacedText);
                        return replacedText
                    } else {
                        return '';
                    }
        }

        
        $scope.parseYoutubeVideo = function (url) {
            var videoid = url.match(/((https?|ftps?):\/\/?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([a-zA-Z0-9-]+)+)(?![^<>]*?>|[^<>]*?<\/)/);
            if (videoid != null) {
                return videoid[3];
            } else {
                return false;
            }
        }

        $scope.get_domain = function (url)
        {
            var url_arr = url.split("/");
            return url_arr[2];
        }

        $scope.like = function (EntityGUID, Type, EntityParentGUID) {
            var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                EntityOwner: EntityOwner
            };
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $(NewsFeedCtrl.activityData).each(function (key, value) {
                        if (NewsFeedCtrl.activityData[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                if (NewsFeedCtrl.activityData[key].IsLike == 1) {
                                    NewsFeedCtrl.activityData[key].IsLike = 0;
                                    NewsFeedCtrl.activityData[key].NoOfLikes--;
                                } else {
                                    NewsFeedCtrl.activityData[key].IsLike = 1;
                                    NewsFeedCtrl.activityData[key].NoOfLikes++;
                                }
                                WallService.CallApi({
                                    EntityGUID: EntityGUID,
                                    EntityType: Type,
                                    PageNo: 1,
                                    PageSize: 2
                                }, 'activity/getLikeDetails').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        NewsFeedCtrl.activityData[key].LikeList = response.Data;
                                    }
                                });
                            } else if (Type == 'COMMENT') {
                                $(NewsFeedCtrl.activityData[key].Comments).each(function (k, v) {
                                    if (NewsFeedCtrl.activityData[key].Comments[k].CommentGUID == EntityGUID) {
                                        if (NewsFeedCtrl.activityData[key].Comments[k].IsLike == 1) {
                                            NewsFeedCtrl.activityData[key].Comments[k].IsLike = 0;
                                            NewsFeedCtrl.activityData[key].Comments[k].NoOfLikes--;
                                        } else {
                                            NewsFeedCtrl.activityData[key].Comments[k].IsLike = 1;
                                            NewsFeedCtrl.activityData[key].Comments[k].NoOfLikes++;
                                        }
                                    }
                                });
                            }
                        }
                    });

                    if (IsNewsFeed == '1')
                    {
                        $($scope.popularData).each(function (key, value) {
                            if ($scope.popularData[key].ActivityGUID == EntityParentGUID) {
                                if (Type == 'ACTIVITY') {
                                    if ($scope.popularData[key].IsLike == 1) {
                                        $scope.popularData[key].IsLike = 0;
                                        $scope.popularData[key].NoOfLikes--;
                                    } else {
                                        $scope.popularData[key].IsLike = 1;
                                        $scope.popularData[key].NoOfLikes++;
                                    }
                                    WallService.CallApi({
                                        EntityGUID: EntityGUID,
                                        EntityType: Type,
                                        PageNo: 1,
                                        PageSize: 2
                                    }, 'activity/getLikeDetails').then(function (response) {
                                        if (response.ResponseCode == 200) {
                                            $scope.popularData[key].LikeList = response.Data;
                                        }
                                    });
                                } else if (Type == 'COMMENT') {
                                    $($scope.popularData[key].Comments).each(function (k, v) {
                                        if ($scope.popularData[key].Comments[k].CommentGUID == EntityGUID) {
                                            if ($scope.popularData[key].Comments[k].IsLike == 1) {
                                                $scope.popularData[key].Comments[k].IsLike = 0;
                                                $scope.popularData[key].Comments[k].NoOfLikes--;
                                            } else {
                                                $scope.popularData[key].Comments[k].IsLike = 1;
                                                $scope.popularData[key].Comments[k].NoOfLikes++;
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                } else {
                    // Error
                }
            }, function (error) {
                // Error
            });
        }
        $scope.likeDetails = function (EntityGUID, EntityType) {
            likeDetails(EntityGUID, EntityType, 'likeDetails');
        }
        $scope.deleteComment = function (CommentGUID, ActivityGUID) {
            jsonData = {
                CommentGUID: CommentGUID
            };
            showConfirmBox("Delete Comment", "Are you sure, you want to delete this comment ?", function (e) {
                if (e) {
                    WallService.CallApi(jsonData, 'activity/deleteComment').then(function (response) {
                        var aid = '';
                        var cid = '';
                        if (response.ResponseCode == 200) {
                            $(NewsFeedCtrl.activityData).each(function (key, value) {
                                if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID) {
                                    aid = key;
                                    $(NewsFeedCtrl.activityData[aid].Comments).each(function (ckey, cvalue) {
                                        if (NewsFeedCtrl.activityData[aid].Comments[ckey].CommentGUID == CommentGUID) {
                                            cid = ckey;
                                            NewsFeedCtrl.activityData[aid].Comments.splice(cid, 1);
                                            NewsFeedCtrl.activityData[aid].NoOfComments = parseInt(NewsFeedCtrl.activityData[aid].NoOfComments) - 1;
                                            return false;
                                        }
                                    });
                                }
                            });

                            if (IsNewsFeed == '1')
                            {
                                $($scope.popularData).each(function (key, value) {
                                    if ($scope.popularData[key].ActivityGUID == ActivityGUID) {
                                        aid = key;
                                        $($scope.popularData[aid].Comments).each(function (ckey, cvalue) {
                                            if ($scope.popularData[aid].Comments[ckey].CommentGUID == CommentGUID) {
                                                cid = ckey;
                                                $scope.popularData[aid].Comments.splice(cid, 1);
                                                $scope.popularData[aid].NoOfComments = parseInt($scope.popularData[aid].NoOfComments) - 1;
                                                return false;
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    });
                }
            });
        }
        $scope.getCurrentProfilePic = function (activity_guid) {
            var data = [];
            if (profile_picture == '') {
                data['CurrentProfilePic'] = Settings.getAssetUrl() + '/' + 'img/profiles/user_default.jpg';
            } else {
                data['CurrentProfilePic'] = Settings.getImageServerPath() + 'upload/profile/36x36/' + profile_picture;
            }
            $scope.updateActivityData(activity_guid, data);
        }

        $scope.setFavouriteArticle = function (ActivityGUID, article) {
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'favourite/toggle_favourite').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.article_list).each(function (key, value) {
                        if ($scope.article_list[key].ActivityGUID == ActivityGUID) {
                            if ($scope.article_list[key].IsFavourite == 1) {
                                $scope.article_list[key].IsFavourite = 0;
                                
                                if($('#ArticleType').val() == 'fav') {
                                    $scope.article_list.splice(key, 1);
                                }
                                
                                
                            } else {
                                $scope.article_list[key].IsFavourite = 1;
                            }
                        }
                    });

                    if (article) {
                        article.IsFavourite = +(!(article.IsFavourite));
                    }

                    angular.forEach($scope.widget_articles, function (val, key) {
                        angular.forEach(val.Data, function (v, k) {
                            if (v.ActivityGUID == ActivityGUID) {
                                if (v.IsFavourite == 1) {
                                    $scope.widget_articles[key]['Data'][k].IsFavourite = 0;
                                } else {
                                    $scope.widget_articles[key]['Data'][k].IsFavourite = 1;
                                }
                            }
                        });
                    });
                }
            });
        }

        $scope.setFavourite = function (ActivityGUID) {
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'favourite/toggle_favourite').then(function (response) {
                if (response.ResponseCode == 200) {
                    $(NewsFeedCtrl.activityData).each(function (key, value) {
                        if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID) {
                            if (NewsFeedCtrl.activityData[key].IsFavourite == 1) {
                                NewsFeedCtrl.activityData[key].IsFavourite = 0;
                                $scope.tfr--;
                                //NewsFeedCtrl.activityData.splice(key,1);
                                if ($scope.tfr == 0) {
                                }
                                if ($('#ActivityFilterType').val() == 'Favourite') {
                                    $('#act-' + ActivityGUID).hide();
                                }
                                return false;
                            } else {
                                NewsFeedCtrl.activityData[key].IsFavourite = 1;
                                $scope.tfr++;
                            }
                        }
                    });

                    if (IsNewsFeed == '1')
                    {
                        $($scope.popularData).each(function (key, value) {
                            if ($scope.popularData[key].ActivityGUID == ActivityGUID) {
                                if ($scope.popularData[key].IsFavourite == 1) {
                                    $scope.popularData[key].IsFavourite = 0;
                                    $scope.tfr--;
                                    //NewsFeedCtrl.activityData.splice(key,1);
                                    if ($scope.tfr == 0) {
                                    }
                                    if ($('#ActivityFilterType').val() == 'Favourite') {
                                        $('#act-' + ActivityGUID).hide();
                                    }
                                    return false;
                                } else {
                                    $scope.popularData[key].IsFavourite = 1;
                                    $scope.tfr++;
                                }
                            }
                        });
                    }
                }
            });
        }

        $scope.likeStatus = function (ActivityGUID, Type) {
            if (Type == 'User') {
                var src = $('#act-' + ActivityGUID + ' .user-pic').attr('src');
            } else {
                var src = $('#act-' + ActivityGUID + ' .entity-pic').attr('src');
            }
            $('#act-' + ActivityGUID + ' .show-pic').attr('src', src);
            $('#act-' + ActivityGUID + ' .current-profile-pic').attr('src', src);
            jsonData = {
                Type: Type,
                ActivityGUID: ActivityGUID
            };
            WallService.CallApi(jsonData, 'activity/checkLikeStatus').then(function (response) {
                var data = [];
                data['viewStats'] = 1;
                data['IsLike'] = response.Data.IsLike;
                data['Comments'] = response.Data.Comments;
                $scope.updateActivityData(ActivityGUID, data);
            });
        }
        $scope.callToolTip = function () {
            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
            }, 500);
        }
        $scope.getLikeTooltip = function (LikeList) {
            var str = '';
            angular.forEach(LikeList, function (val, key) {
                str += '<div>' + val.FirstName + ' ' + val.LastName + '</div>';
            });
            $scope.callToolTip();
            return str;
        }

        $scope.tagComment = function (eid, cls, module_id, module_entity_guid) {
            var ajax_request = false;
            setTimeout(function () {
                var sym = '#';
                if (cls == '1')
                {
                    sym = '.';
                }
                $(sym + eid).textntags({
                    onDataRequest: function (mode, query, triggerChar, callback) {
                        if (ajax_request)
                            ajax_request.abort();
                        if (module_entity_guid)
                        {
                            if (module_id == 1) {
                                var type = 'Members';
                            } else {
                                var type = 'NewsFeedTagging';
                            }
                        } else
                        {
                            module_id = $('#module_id').val();
                            module_entity_guid = $('#module_entity_guid').val();
                            if (module_id == 1) {
                                var type = 'Members';
                            } else {
                                var type = 'NewsFeedTagging';
                            }
                        }
                        if (IsAdminView == '1')
                        {
                            ajax_request = $http({
                                method: 'POST',
                                data: {
                                    Type: type,
                                    SearchKey: query,
                                    ModuleID: module_id,
                                    ModuleEntityID: module_entity_guid
                                },
                                url: base_url + 'api/users/list'
                            }).then(function (r) {
                                r = r.data;
                                if (r.ResponseCode == 200) {
                                    var uid = 0;
                                    var d = new Array();
                                    for (var key in r.Data.Members) {
                                        var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                        d[uid] = {
                                            'id': r.Data.Members[key].UserID,
                                            'name': name,
                                            'type': r.Data.Members[key].ModuleID
                                        };
                                        uid++;
                                    }
                                    query = query.toLowerCase();
                                    var found = _.filter(d, function (item) {
                                        query = $.trim(query);
                                        return item.name.toLowerCase().indexOf(query) > -1;
                                    });
                                    callback.call(this, found);
                                    ajax_request = false;
                                }
                            });
                        } else
                        {
                            ajax_request = $.post(base_url + 'api/users/list', {
                                Loginsessionkey: LoginSessionKey,
                                Type: type,
                                SearchKey: query,
                                ModuleID: module_id,
                                ModuleEntityID: module_entity_guid
                            }, function (r) {
                                if (r.ResponseCode == 200) {
                                    var uid = 0;
                                    var d = new Array();
                                    for (var key in r.Data.Members) {
                                        var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                        d[uid] = {
                                            'id': r.Data.Members[key].UserID,
                                            'name': name,
                                            'type': r.Data.Members[key].ModuleID
                                        };
                                        uid++;
                                    }
                                    query = query.toLowerCase();
                                    var found = _.filter(d, function (item) {
                                        query = $.trim(query);
                                        return item.name.toLowerCase().indexOf(query) > -1;
                                    });
                                    callback.call(this, found);
                                    ajax_request = false;
                                }
                            });
                        }
                    }
                });
            }, 500);
        }

        $scope.comment = function (event, ActivityGUID) {
            if (!$('#cm-' + ActivityGUID + ' li').hasClass('loading-class')) {
                if (event.which == 13) {
                    $scope.appendComment = 1;
                    if (!event.shiftKey) {
                        event.preventDefault();
                        var Comment = $('#cmt-' + ActivityGUID).val();
                        Comment = Comment.trim();
                        var MediaGUID = '';
                        var Caption = '';
                        var IsMediaExists = 0;
                        if ($('#cm-' + ActivityGUID + ' li').length > 0) {
                            MediaGUID = $('#cm-' + ActivityGUID + ' input[name="MediaGUID"]').val();
                            Caption = $('#cm-' + ActivityGUID + ' input[name="Caption"]').val();
                            IsMediaExists = 1;
                        }
                        if (IsMediaExists == 0 && Comment == '') {
                            $('#cmt-' + ActivityGUID).val('');
                            return;
                        }
                        var PComments = $('#act-' + ActivityGUID + ' .textntags-beautifier div').html();
                        jQuery('#act-' + ActivityGUID + ' .textntags-beautifier div strong').each(function (e) {
                            var details = $('#act-' + ActivityGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').attr('class');
                            var module_id = details.split('-')[1];
                            var module_entity_id = details.split('-')[2];
                            var name = $('#act-' + ActivityGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').text();
                            PComments = PComments.replace('<strong><span class="user-' + module_id + '-' + module_entity_id + '">' + name + '</span></strong>', '{{' + name + ':' +
                                    module_entity_id + ':' + module_id + '}}');
                        });
                        var Media = [];
                        $('#cmt-' + ActivityGUID).val('');
                        $('#act-' + ActivityGUID).find('textntags-beautifier div').html('');
                        jQuery('#cmt-' + ActivityGUID).textntags('reset');
                        Caption = '';
                        if (Comment.length > 0) {
                            Caption = Comment;
                        }
                        Media.push({
                            'MediaGUID': MediaGUID,
                            'Caption': Caption
                        });
                        Comment = PComments;


                        // Parse it and wrap it in a div
                        var frag = $("<div>").append($.parseHTML(Comment));
                        // Find the relevant images
                        frag.find("img.tamemoji[emoji]").each(function() {
                          // Replace the image with a text node containing :::[dataattr2]:::
                          var $this = $(this);
                          var unicode_value = unicodeFromImage[$this.attr("emoji").slice(1, -1)];
                          $this.replaceWith($('<div style="'+$this.attr("style")+'">').html(unicode_value).addClass("parseemoji"))
                          //$this.replaceWith(document.createTextNode("< value='"+$this.attr("emoji")+"'></emoji>"));
                        });
                        // Get the HTML of the result
                        Comment = frag.html();

                        jsonData = {
                            Comment: Comment,
                            EntityType: 'Activity',
                            EntityGUID: ActivityGUID,
                            Media: Media,
                            EntityOwner: $('#act-' + ActivityGUID + ' .module-entity-owner').val()
                        };
                        WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {
                            $(NewsFeedCtrl.activityData).each(function (key, value) {
                                if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID) {
                                    // NewsFeedCtrl.activityData[key].Comments[0] = response.Data[0];
                                    var newArr = new Array();
                                    $(NewsFeedCtrl.activityData[key].Comments).each(function (k, value) {
                                        newArr.push(NewsFeedCtrl.activityData[key].Comments[k]);
                                    });
                                    newArr.push(response.Data[0]);
                                    NewsFeedCtrl.activityData[key].Comments = reduce_arr(newArr);
                                    NewsFeedCtrl.activityData[key].Comments = newArr;
                                    NewsFeedCtrl.activityData[key].NoOfComments = parseInt(NewsFeedCtrl.activityData[key].NoOfComments) + 1;
                                    NewsFeedCtrl.activityData[key].comntData = $scope.$broadcast('appendComntEmit', NewsFeedCtrl.activityData[key].Comments); //getPostComments(NewsFeedCtrl.activityData[key].Comments);
                                    NewsFeedCtrl.activityData[key].showeditor = false;
                                    $('#upload-btn-' + ActivityGUID).show();
                                    $('#cm-' + ActivityGUID).html('');
                                    $('#cm-' + ActivityGUID + ' li').remove();
                                    $('#cm-' + ActivityGUID).hide();
                                    $('#act-' + ActivityGUID + ' .attach-on-comment').show();
                                    NewsFeedCtrl.activityData[key].IsSubscribed = 1;
                                    setTimeout(function () {
                                        $('#cmt-' + ActivityGUID).trigger('focus');
                                        $scope.show_comment_box = "";
                                    }, 200);
                                }
                            });
                        });
                    }
                }
            }
        }

        $scope.seeMoreLink = function (ActivityGUID) {
            var data = [];
            data['ShowMoreHide'] = '1';
            data['showAllLinks'] = '1';
            $scope.updateActivityData(ActivityGUID, data);
            if (IsNewsFeed == '1')
            {
                $scope.updateActivityData(ActivityGUID, data);
            }
        }
        $scope.layoutClass = function (className) {
            var strClass;
            var doImgFill = true;
            if (className) {
                switch (className.length) {
                    case 0:
                        strClass = "hide";
                        break;
                    case 1:
                        strClass = "single-image";
                        doImgFill = false;
                        break;
                    case 2:
                        strClass = "two-images";
                        break;
                    case 3:
                        strClass = "three-images";
                        break;
                    case 4:
                        strClass = "four-images";
                        break;
                    case 5:
                        strClass = "four-images";
                        break;
                    default:
                        strClass = "four-images";
                        break;
                }
                return strClass;
            } else {
                return 'single-image';
            }
        }

        $scope.clearReminderState = function (ActivityGUID, IsReminderSet, data) {
            if(data)
            {
                $scope.showOptions(data, 'showReminderOptions');
            }

            $('#clearReminder' + ActivityGUID).bind('click', function () {
                $("#reminderCal" + ActivityGUID).datepicker().datepicker('destroy');
            });
            //$('#reminderUl'+ActivityGUID+' .permActive').removeClass('permActive');
            setReminder();
            if (IsReminderSet == '1') {
                $('#backeditReminder' + ActivityGUID).trigger('click');
                $('#act-' + ActivityGUID + ' [data-calendar="reminderCalendar"]').hide();
                $('#act-' + ActivityGUID + ' [data-type="reminderFooter"]').hide();
                $('#act-' + ActivityGUID + ' [data-type="editreminderFooter"]').show();
                $('#act-' + ActivityGUID + ' [data-arrow="backReminder"]').hide();
                fixedTimePicker = $('[data-fixed-activityguid="' + ActivityGUID + '"] a');
                fixedTimePicker.removeClass('active');
                $('[data-fixed-activityguid="' + ActivityGUID + '"] a.permActive').addClass('active');
            } else {
                $('#clearReminder' + ActivityGUID).trigger('click');
                $('#act-' + ActivityGUID + ' [data-type="reminderFooter"]').show();
                fixedTimePicker = $('[data-fixed-activityguid="' + ActivityGUID + '"] a');
                fixedTimePicker.removeClass('active');
            }
            $('[data-arrow="backReminder"]').hide();
            $('[data-calendar="reminderCalendar"]').hide();
        }

        $scope.mentiontabshow = false;
        $scope.hideMentionTab = function ()
        {
            $scope.mentiontabshow = false;
            $('#mentionView').hide();
            $('.live-feed').removeClass('mentionOpen');
        }

        $scope.mentiontab = function ()
        {
            $scope.mentiontabshow = true;
            $('#mentionView').addClass('visible').show();
            if ($('#mentionView').hasClass('visible')) {
                $('.live-feed').addClass('mentionOpen');
                // $('applyed-filter').
            }
            ;
        }

        $scope.selectedDate = {};
        $scope.isInit = false;
        $scope.initDatepicker = function (ActivityGUID, ReminderData) {
            var d = new Date(),
                    datePickerValueAsObject = null,
                    n = d.getMinutes();
            for (var k = 0; k <= 3; k++) {
                if (15 * k <= n) {
                    $('ul.minutes input:eq(' + k + ')').attr('disabled');
                    $('ul.minutes span:eq(' + k + ')').addClass('disabled');
                }
            }
            angular.forEach(NewsFeedCtrl.activityData, function (v1, k1) {
                if (v1.ActivityGUID == ActivityGUID) {
                    //console.log(v1);
                    var datetime = new Date();
                    var Meridian = moment(datetime).format('a');
                    if (typeof NewsFeedCtrl.activityData[k1].ReminderData == 'undefined')
                    {
                        NewsFeedCtrl.activityData[k1]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        NewsFeedCtrl.activityData[k1].ReminderData = prepareReminderData(NewsFeedCtrl.activityData[k1].Reminder);
                    }
                    if (typeof ReminderData == 'undefined')
                    {
                        ReminderData = NewsFeedCtrl.activityData[k1].ReminderData;
                    }
                    NewsFeedCtrl.activityData[k1]['ReminderData'].Meridian = (NewsFeedCtrl.activityData[k1]['ReminderData'].Meridian) ? NewsFeedCtrl.activityData[k1]['ReminderData'].Meridian : Meridian;
                    $('.hours span').removeClass('selected');
                    $('.minutes span').removeClass('selected');
                }
            });

            angular.forEach($scope.group_announcements, function (v1, k1) {
                if (v1.ActivityGUID == ActivityGUID) {
                    var datetime = new Date();
                    var Meridian = moment(datetime).format('a');
                    if (typeof $scope.group_announcements[k1].ReminderData == 'undefined')
                    {
                        $scope.group_announcements[k1]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        $scope.group_announcements[k1].ReminderData = prepareReminderData($scope.group_announcements[k1].Reminder);
                    }
                    if (typeof ReminderData == 'undefined')
                    {
                        ReminderData = $scope.group_announcements[k1].ReminderData;
                    }
                    $scope.group_announcements[k1]['ReminderData'].Meridian = ($scope.group_announcements[k1]['ReminderData'].Meridian) ? $scope.group_announcements[k1]['ReminderData'].Meridian : Meridian;
                    $('.hours span').removeClass('selected');
                    $('.minutes span').removeClass('selected');
                }
            });

            if (IsNewsFeed == '1')
            {
                angular.forEach($scope.popularData, function (v1, k1) {
                    if (v1.ActivityGUID == ActivityGUID) {
                        // console.log(v1);
                        var datetime = new Date();
                        var Meridian = moment(datetime).format('a');
                        if (typeof $scope.popularData[k1].ReminderData == 'undefined')
                        {
                            $scope.popularData[k1]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                            $scope.popularData[k1].ReminderData = prepareReminderData(NewsFeedCtrl.activityData[k1].Reminder);
                        }
                        if (typeof ReminderData == 'undefined')
                        {
                            ReminderData = $scope.popularData[k1].ReminderData;
                        }
                        $scope.popularData[k1]['ReminderData'].Meridian = ($scope.popularData[k1]['ReminderData'].Meridian) ? $scope.popularData[k1]['ReminderData'].Meridian : Meridian;
                        $('.hours span').removeClass('selected');
                        $('.minutes span').removeClass('selected');
                    }
                });
            }

            var OldSelectedDate = '';
            if (ReminderData.ReminderEditDateTime != '') {
                $scope.selectedDate[ActivityGUID] = ReminderData.ReminderEditDateTime;
                defaultDate = new Date(ReminderData.ReminderEditDateTime);
                if (ReminderData.ReminderGUID != '') {
                    OldSelectedDate = ReminderData.ReminderEditDateTime;
                }
            }
            $('#reminderCal' + ActivityGUID).datepicker({
                changeYear: true,
                changeMonth: true,
                showOtherMonths: false,
                dateFormat: 'yy-mm-dd',
                minDate: new Date(),
                setDate: new Date(),
                defaultDate: defaultDate,
                onChangeMonthYear: function (year, month, inst) {
                    var curDate = new Date(),
                            dateText;
                    curDate.setDate(1);
                    curDate.setYear(parseInt(year));
                    curDate.setMonth(parseInt(month) - 1);
                    angular.element(this).datepicker("setDate", curDate);
                    datePickerValueAsObject = angular.element(this).datepicker('getDate');
                    $scope.selectedDate[ActivityGUID] = moment(datePickerValueAsObject).format('YYYY-MM-DD');
                    $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
                },
                onSelect: function (dateText, inst) {
                    datePickerValueAsObject = angular.element(this).datepicker('getDate');
                    $scope.selectedDate[ActivityGUID] = dateText;
                    $('[data-fixed-activityGUID="' + ActivityGUID + '"] a').removeClass('active');
                    $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
                },
                beforeShowDay: function (date) {
                    if (OldSelectedDate) {
                        var curDate = moment().format('YYYY-MM-DD');
                        Dates = moment(date).format('YYYY-MM-DD');
                        if (moment(OldSelectedDate).isSame(Dates)) {
                            return [true, "reminderSet"];
                        }
                    }
                    return [true, ''];
                }
            });
            $timeout(function () {
                if (OldSelectedDate) {
                    var curDateToSet = moment().format('YYYY-MM-DD');
                    if ((moment(OldSelectedDate).isSame(curDateToSet)) || (moment(OldSelectedDate).isAfter(curDateToSet))) {
                        $('#reminderCal' + ActivityGUID).datepicker("setDate", moment(OldSelectedDate).toDate());
                        $scope.selectedDate[ActivityGUID] = OldSelectedDate;
                    } else if (moment(OldSelectedDate).isBefore(curDateToSet)) {
                        $('#reminderCal' + ActivityGUID).datepicker("setDate", moment(curDateToSet).toDate());
                        $scope.selectedDate[ActivityGUID] = curDateToSet;
                    }
                    datePickerValueAsObject = $('#reminderCal' + ActivityGUID).datepicker('getDate');
                }
                $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH]'), function (hourKey, hourField) {
                    angular.element(this).parent().removeClass('selected');
                    angular.element(this).prop('checked', false);
                });
                $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time]').on('change', function () {
                    $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
                });
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH]').on('change', function () {
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH]').parent().removeClass('selected');
                    angular.element(this).parent().addClass('selected');
                    $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
                });
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=MM]').on('change', function () {
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=MM]').parent().removeClass('selected');
                    angular.element(this).parent().addClass('selected');
                    $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
                });
            });
        }

        $scope.validateTimePicker = function (ActivityGUID, datePickerValueAsObject) {
            var momentObj = (datePickerValueAsObject) ? moment(datePickerValueAsObject) : moment(),
                    amOrPm = (angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time]:checked').val()) ? angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time]:checked').val() : momentObj.format('a'),
                    currentDate = moment(),
                    hourSelected = '';
            if (currentDate.format('YYYY MM DD') == momentObj.format('YYYY MM DD')) {
                var reminderHours = moment().format('H'),
                        reminderMinutes = moment().format('m'),
                        amOrPm = (moment().format('a') == 'pm') ? 'pm' : amOrPm;
                if (moment().format('a') == 'pm') {
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').prop('disabled', 'disabled');
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').removeClass('ng-valid-parse');
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().addClass('disabled');
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().removeClass('selected');
                } else {
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').prop('disabled', '');
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().removeClass('disabled');
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().addClass('selected');
                }
            } else {
                var reminderHours = 0,
                        reminderMinutes = 0;
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').prop('disabled', '');
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().removeClass('disabled');
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().addClass('selected');
            }

            angular.forEach(NewsFeedCtrl.activityData, function (v1, k1) {
                if (v1.ActivityGUID == ActivityGUID) {
                    NewsFeedCtrl.activityData[k1]['ReminderData'].Meridian = amOrPm;
                }
            });

            angular.forEach($scope.group_announcements, function (v1, k1) {
                if (v1.ActivityGUID == ActivityGUID) {
                    $scope.group_announcements[k1]['ReminderData'].Meridian = amOrPm;
                }
            });

            if (IsNewsFeed == '1') {
                angular.forEach($scope.popularData, function (v1, k1) {
                    if (v1.ActivityGUID == ActivityGUID) {
                        $scope.popularData[k1]['ReminderData'].Meridian = amOrPm;
                    }
                });
            }
            momentObj.hour(reminderHours);
            momentObj.minute(reminderMinutes);
            if (amOrPm == 'am') {
                hourSelected = parseInt(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForAm:checked').val());
                if (!hourSelected) {
                    hourSelected = reminderHours;
                }
                $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForAm'), function (hourKey, hourField) {
                    var fieldHour = parseInt(angular.element(this).val().trim());
                    if ((fieldHour < reminderHours) || ((fieldHour == reminderHours) && (reminderMinutes > 45))) {
                        angular.element(this).parent().addClass('disabled');
                        angular.element(this).parent().removeClass('selected');
                        angular.element(this).prop('disabled', 'disabled');
                        angular.element(this).prop('checked', false);
                    } else {
                        angular.element(this).parent().removeClass('disabled');
                        angular.element(this).prop('disabled', '');
                    }
                });

                $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm'), function (hourKey, hourField) {
                    angular.element(this).parent().removeClass('selected');
                    angular.element(this).prop('checked', false);
                });

                //        if (angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm').parent().hasClass('selected')) {
                //          angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm').parent().removeClass('selected');
                //          angular.element(this).prop('checked', false);
                //        }

                if (hourSelected >= reminderHours) {
                    $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=MM]'), function (minuteKey, minuteField) {
                        var fieldMinute = parseInt(angular.element(this).val().trim());
                        if ((fieldMinute < reminderMinutes) && (hourSelected == reminderHours)) {
                            angular.element(this).parent().addClass('disabled');
                            angular.element(this).parent().removeClass('selected');
                            angular.element(this).prop('disabled', 'disabled');
                            angular.element(this).prop('checked', false);
                        } else {
                            angular.element(this).parent().removeClass('disabled');
                            angular.element(this).prop('disabled', '');
                        }
                    });
                }
            } else {
                hourSelected = parseInt(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm:checked').val());
                if (!hourSelected) {
                    hourSelected = reminderHours;
                }
                $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm'), function (hourKey, hourField) {
                    var fieldHour = parseInt(angular.element(this).val().trim());
                    if ((reminderHours >= 12) && ((fieldHour < reminderHours) || ((fieldHour == reminderHours) && (reminderMinutes > 45)))) {
                        angular.element(this).parent().addClass('disabled');
                        angular.element(this).parent().removeClass('selected');
                        angular.element(this).prop('disabled', 'disabled');
                        angular.element(this).prop('checked', false);
                    } else {
                        angular.element(this).parent().removeClass('disabled');
                        angular.element(this).prop('disabled', '');
                    }
                });

                $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForAm'), function (hourKey, hourField) {
                    angular.element(this).parent().removeClass('selected');
                    angular.element(this).prop('checked', false);
                });

                if (hourSelected >= reminderHours) {
                    $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=MM]'), function (minuteKey, minuteField) {
                        var fieldMinute = parseInt(angular.element(this).val().trim());
                        if ((fieldMinute < reminderMinutes) && (hourSelected == reminderHours)) {
                            angular.element(this).parent().addClass('disabled');
                            angular.element(this).parent().removeClass('selected');
                            angular.element(this).prop('disabled', 'disabled');
                            angular.element(this).prop('checked', false);
                        } else {
                            angular.element(this).parent().removeClass('disabled');
                            angular.element(this).prop('disabled', '');
                        }
                    });
                }
            }
            if (amOrPm == 'pm') {
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().removeClass('selected');
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').removeClass('ng-valid-parse');
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=pm]').parent().addClass('selected');
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=pm]').prop('checked', true);
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('ul.amHoursBlock').hide();
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('ul.pmHoursBlock').show();
                if (angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForAm').parent().hasClass('selected')) {
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForAm').parent().removeClass('selected');
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForAm').prop('checked', false);
                }
            } else {
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=pm]').parent().removeClass('selected');
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').parent().addClass('selected');
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time][value=am]').prop('checked', true);
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('ul.amHoursBlock').show();
                angular.element('#reminderCalTimePicker' + ActivityGUID).find('ul.pmHoursBlock').hide();
                if (angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm').parent().hasClass('selected')) {
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm').parent().removeClass('selected');
                    angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH].hourForPm').prop('checked', false);
                }
            }
        }
        $scope.filterIsPromoted = 0;
        $scope.applyFilterType = function (val, callService) {
            // Initially reset
            $scope.filterIsPromoted = 0;

            $('#IsMediaExists').val(2);
            $scope.Filter.IsSetFilter = true;
            $scope.IsActiveFilter = true;
            $scope.IsFilePage = 0;
            var val1 = val;
            if (val != 5)
            {
                $scope.hideMentionTab();
            }
            if (val == 5) {
                $scope.suggestPage = [];
                $scope.suggestPage.push({
                    "Title": $scope.FirstName + ' ' + $scope.LastName,
                    "ModuleEntityGUID": $('#module_entity_guid').val(),
                    "ModuleID": "3"
                });
                val = 0;
                $('#ActivityFilterType').val(val);
                $scope.getFilteredWall();
            } else {
                $scope.suggestPage = [];
            }
            $('#ActivityFilterType').val(val);
            if (val == 3) {
                $scope.IsReminder = 1;
                //call reminder Count service for calendar update
                $scope.getRemiderCounts();
            } else {
                $scope.IsReminder = 0;
            }
            if (val == 6) {
                $('#ActivityFilterType').val('Favourite');
            }
            $scope.ReminderFilter = 0;
            $scope.ReminderFilterDate = [];
            if (val !== 4 || val !== 6) {
                $('.clear-filter2').trigger('click');
            }
            if (val1 == 0 || val1 == 3) {
                setTimeout(function () {
                    $('.filterApply').addClass('hide');
                }, 200);
            }


            // Filter for promoted post
            if (val == 'IsPromoted' && $scope.config_detail.IsSuperAdmin) {
                $scope.ResetFilter(0, 1);
                $scope.Filter.IsSetFilter = 1;
                $scope.filterIsPromoted = 1;
                $scope.Filter.typeLabelName = 'Promoted';
            }


            if ($('#IsWiki').length > 0)
            {
                $('#WallPageNo').val(1);
                $scope.stop_article_execution = 0;
                $scope.article_list = [];
                $scope.get_wiki_post();
            } else
            {
                if (callService == '1') {
                    $scope.getFilteredWall();
                }
            }


        }
        //Set reminder data
        $scope.setReminderData = function (ActivityGUID, ReminderData) {
            if (typeof ReminderData !== 'undefined' && typeof ReminderData.ReminderGUID !== 'undefined') {
                var ReminderData = $scope.prepareReminderData(ReminderData);
                $scope.applyReminderData(ActivityGUID, ReminderData);
            } else {
                datetime = new Date();
                hours = parseInt(moment(datetime).format('hh'));
                CurrentMinutes = parseInt(moment(datetime).format('m'));
                if (CurrentMinutes > 0 && CurrentMinutes <= 15) {
                    Minutes = 15;
                } else if (CurrentMinutes > 15 && CurrentMinutes <= 30) {
                    Minutes = 30;
                } else if (CurrentMinutes > 30 && CurrentMinutes <= 45) {
                    Minutes = 45;
                } else {
                    Minutes = 0;
                }
                if (hours < 12 && Minutes > 45) {
                    hours = hours + 1;
                }
                editDate = moment(datetime).format('YYYY-MM-DD');
                Meridian = moment(datetime).format('a');
                Reminder = {
                    ReminderEditDateTime: editDate,
                    Hour: hours,
                    Minutes: Minutes,
                    Meridian: Meridian,
                    ReminderGUID: '',
                    SelectedClass: 'selected'
                }
                $scope.applyReminderData(ActivityGUID, Reminder);
            }
        }
        $scope.applyReminderData = function (ActivityGUID, ReminderData) {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == ActivityGUID) {
                    NewsFeedCtrl.activityData[key]['ReminderData'] = ReminderData;
                    if (!$scope.$$phase) {
                        $scope.$apply();
                    }
                }
            });

            if (IsNewsFeed == '1')
            {
                angular.forEach($scope.popularData, function (val, key) {
                    if (val.ActivityGUID == ActivityGUID) {
                        $scope.popularData[key]['ReminderData'] = ReminderData;
                        if (!$scope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });
            }
        }

        $scope.likeEmitAnnouncement = function (EntityGUID, Type, EntityParentGUID, IsDislike, CommentParentGUID) {

            var id = $('[data-guid="act-' + EntityParentGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };
            if (IsDislike == '1')
            {
                reqData['Dislike'] = 1;
            }
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.group_announcements).each(function (key, value) {
                        if ($scope.group_announcements[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                if (IsDislike == '1')
                                {
                                    if ($scope.group_announcements[key].IsDislike == 1) {
                                        $scope.group_announcements[key].IsDislike = 0;
                                        //$scope.group_announcements[key].NoOfDislikes--;
                                    } else {
                                        if ($scope.group_announcements[key].IsLike == '1')
                                        {
                                            $scope.group_announcements[key].IsLike = 0;
                                            $scope.group_announcements[key].NoOfLikes--;
                                        }
                                        $scope.group_announcements[key].IsDislike = 1;
                                        //$scope.group_announcements[key].NoOfDislikes++;
                                    }
                                } else
                                {
                                    if ($scope.group_announcements[key].IsLike == 1) {
                                        $scope.group_announcements[key].IsLike = 0;
                                        $scope.group_announcements[key].NoOfLikes--;
                                    } else {
                                        if ($scope.group_announcements[key].IsDislike == '1')
                                        {
                                            $scope.group_announcements[key].IsDislike = 0;
                                            //$scope.group_announcements[key].NoOfDislikes--;
                                        }
                                        $scope.group_announcements[key].IsLike = 1;
                                        $scope.group_announcements[key].NoOfLikes++;
                                    }
                                }
                                WallService.CallApi({
                                    EntityGUID: EntityGUID,
                                    EntityType: Type,
                                    PageNo: 1,
                                    PageSize: 10
                                }, 'activity/getLikeDetails').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        $scope.group_announcements[key].LikeList = response.Data;
                                    }
                                });
                            } else if (Type == 'COMMENT') {
                                $($scope.group_announcements[key].Comments).each(function (k, v) {
                                    if ($scope.group_announcements[key].Comments[k].CommentGUID == EntityGUID || $scope.group_announcements[key].Comments[k].CommentGUID == CommentParentGUID) {
                                        if ($scope.group_announcements[key].Comments[k].CommentGUID == EntityGUID)
                                        {
                                            if (IsDislike == '1')
                                            {
                                                if ($scope.group_announcements[key].Comments[k].IsDislike == 1) {
                                                    $scope.group_announcements[key].Comments[k].IsDislike = 0;
                                                    //$scope.group_announcements[key].Comments[k].NoOfDislikes--;
                                                } else {
                                                    if ($scope.group_announcements[key].Comments[k].IsLike == '1')
                                                    {
                                                        $scope.group_announcements[key].Comments[k].IsLike = 0;
                                                        $scope.group_announcements[key].Comments[k].NoOfLikes--;
                                                    }
                                                    $scope.group_announcements[key].Comments[k].IsDislike = 1;
                                                    //$scope.group_announcements[key].Comments[k].NoOfDislikes++;
                                                }
                                            } else
                                            {
                                                if ($scope.group_announcements[key].Comments[k].IsLike == 1) {
                                                    $scope.group_announcements[key].Comments[k].IsLike = 0;
                                                    $scope.group_announcements[key].Comments[k].NoOfLikes--;
                                                } else {
                                                    if ($scope.group_announcements[key].Comments[k].IsDislike == '1')
                                                    {
                                                        $scope.group_announcements[key].Comments[k].IsDislike = 0;
                                                        //$scope.group_announcements[key].Comments[k].NoOfDislikes--;
                                                    }
                                                    $scope.group_announcements[key].Comments[k].IsLike = 1;
                                                    $scope.group_announcements[key].Comments[k].NoOfLikes++;
                                                }
                                            }
                                        } else
                                        {
                                            angular.forEach($scope.group_announcements[key].Comments[k].Replies, function (v2, k2) {
                                                if ($scope.group_announcements[key].Comments[k].Replies[k2].CommentGUID == EntityGUID)
                                                {
                                                    if ($scope.group_announcements[key].Comments[k].Replies[k2].IsLike == 1) {
                                                        $scope.group_announcements[key].Comments[k].Replies[k2].IsLike = 0;
                                                        $scope.group_announcements[key].Comments[k].Replies[k2].NoOfLikes--;
                                                    } else {
                                                        if ($scope.group_announcements[key].Comments[k].Replies[k2].IsDislike == '1')
                                                        {
                                                            $scope.group_announcements[key].Comments[k].Replies[k2].IsDislike = 0;
                                                            //$scope.group_announcements[key].Comments[k].Replies[k2].NoOfDislikes--;
                                                        }
                                                        $scope.group_announcements[key].Comments[k].Replies[k2].IsLike = 1;
                                                        $scope.group_announcements[key].Comments[k].Replies[k2].NoOfLikes++;
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });

                } else {
                    // Error
                }
            }, function (error) {
                // Error
            });
        }

        $scope.likeEmit = function (EntityGUID, Type, EntityParentGUID, IsDislike, CommentParentGUID) {
            
            var id = $('[data-guid="act-' + EntityParentGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };

            console.log('Comment',reqData)
            if (IsDislike == '1')
            {
                reqData['Dislike'] = 1;
            }
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $(NewsFeedCtrl.activityData).each(function (key, value) {
                        if (NewsFeedCtrl.activityData[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                if (IsDislike == '1')
                                {
                                    if (NewsFeedCtrl.activityData[key].IsDislike == 1) {
                                        NewsFeedCtrl.activityData[key].IsDislike = 0;
                                        //NewsFeedCtrl.activityData[key].NoOfDislikes--;
                                    } else {
                                        if (NewsFeedCtrl.activityData[key].IsLike == '1')
                                        {
                                            NewsFeedCtrl.activityData[key].IsLike = 0;
                                        }
                                        NewsFeedCtrl.activityData[key].IsDislike = 1;
                                        //NewsFeedCtrl.activityData[key].NoOfDislikes++;
                                    }
                                } else
                                {
                                    if (NewsFeedCtrl.activityData[key].IsLike == 1) {
                                        NewsFeedCtrl.activityData[key].IsLike = 0;
                                    } else {
                                        if (NewsFeedCtrl.activityData[key].IsDislike == '1')
                                        {
                                            NewsFeedCtrl.activityData[key].IsDislike = 0;
                                            //NewsFeedCtrl.activityData[key].NoOfDislikes--;
                                        }
                                        NewsFeedCtrl.activityData[key].IsLike = 1;
                                    }
                                }
                                NewsFeedCtrl.activityData[key].NoOfLikes = response.Data.NoOfLikes;
                                WallService.CallApi({
                                    EntityGUID: EntityGUID,
                                    EntityType: Type,
                                    PageNo: 1,
                                    PageSize: 10
                                }, 'activity/getLikeDetails').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        NewsFeedCtrl.activityData[key].LikeList = response.Data;
                                        NewsFeedCtrl.activityData[key].NoOfLikes =  response.TotalRecords;
                                    }
                                });
                            } else if (Type == 'COMMENT') {
                                $(NewsFeedCtrl.activityData[key].Comments).each(function (k, v) {
                                    if (NewsFeedCtrl.activityData[key].Comments[k].CommentGUID == EntityGUID || NewsFeedCtrl.activityData[key].Comments[k].CommentGUID == CommentParentGUID) {
                                        if (NewsFeedCtrl.activityData[key].Comments[k].CommentGUID == EntityGUID)
                                        {
                                            if (IsDislike == '1')
                                            {
                                                if (NewsFeedCtrl.activityData[key].Comments[k].IsDislike == 1) {
                                                    NewsFeedCtrl.activityData[key].Comments[k].IsDislike = 0;
                                                    //NewsFeedCtrl.activityData[key].Comments[k].NoOfDislikes--;
                                                } else {
                                                    if (NewsFeedCtrl.activityData[key].Comments[k].IsLike == '1')
                                                    {
                                                        NewsFeedCtrl.activityData[key].Comments[k].IsLike = 0;
                                                        NewsFeedCtrl.activityData[key].Comments[k].NoOfLikes--;
                                                    }
                                                    NewsFeedCtrl.activityData[key].Comments[k].IsDislike = 1;
                                                    //NewsFeedCtrl.activityData[key].Comments[k].NoOfDislikes++;
                                                }
                                            } else
                                            {
                                                if (NewsFeedCtrl.activityData[key].Comments[k].IsLike == 1) {
                                                    NewsFeedCtrl.activityData[key].Comments[k].IsLike = 0;
                                                    NewsFeedCtrl.activityData[key].Comments[k].NoOfLikes--;
                                                } else {
                                                    if (NewsFeedCtrl.activityData[key].Comments[k].IsDislike == '1')
                                                    {
                                                        NewsFeedCtrl.activityData[key].Comments[k].IsDislike = 0;
                                                        //NewsFeedCtrl.activityData[key].Comments[k].NoOfDislikes--;
                                                    }
                                                    NewsFeedCtrl.activityData[key].Comments[k].IsLike = 1;
                                                    NewsFeedCtrl.activityData[key].Comments[k].NoOfLikes++;
                                                }
                                            }
                                        } else
                                        {
                                            angular.forEach(NewsFeedCtrl.activityData[key].Comments[k].Replies, function (v2, k2) {
                                                if (NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].CommentGUID == EntityGUID)
                                                {
                                                    if (NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].IsLike == 1) {
                                                        NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].IsLike = 0;
                                                        NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].NoOfLikes--;
                                                    } else {
                                                        if (NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].IsDislike == '1')
                                                        {
                                                            NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].IsDislike = 0;
                                                            //NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].NoOfDislikes--;
                                                        }
                                                        NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].IsLike = 1;
                                                        NewsFeedCtrl.activityData[key].Comments[k].Replies[k2].NoOfLikes++;
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });


                    if (IsNewsFeed == '1')
                    {
                        $($scope.popularData).each(function (key, value) {
                            if ($scope.popularData[key].ActivityGUID == EntityParentGUID) {
                                if (Type == 'ACTIVITY') {
                                    if ($scope.popularData[key].IsLike == 1) {
                                        $scope.popularData[key].IsLike = 0;
                                        $scope.popularData[key].NoOfLikes--;
                                    } else {
                                        $scope.popularData[key].IsLike = 1;
                                        $scope.popularData[key].NoOfLikes++;
                                    }
                                    WallService.CallApi({
                                        EntityGUID: EntityGUID,
                                        EntityType: Type,
                                        PageNo: 1,
                                        PageSize: 2
                                    }, 'activity/getLikeDetails').then(function (response) {
                                        if (response.ResponseCode == 200) {
                                            $scope.popularData[key].LikeList = response.Data;
                                        }
                                    });
                                } else if (Type == 'COMMENT') {
                                    $($scope.popularData[key].Comments).each(function (k, v) {
                                        if ($scope.popularData[key].Comments[k].CommentGUID == EntityGUID) {
                                            if ($scope.popularData[key].Comments[k].IsLike == 1) {
                                                $scope.popularData[key].Comments[k].IsLike = 0;
                                                $scope.popularData[key].Comments[k].NoOfLikes--;
                                            } else {
                                                $scope.popularData[key].Comments[k].IsLike = 1;
                                                $scope.popularData[key].Comments[k].NoOfLikes++;
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }

                } else {
                    // Error
                }
            }, function (error) {
                // Error
            });
        }

        $scope.likeEmitArticle = function (EntityGUID, Type, EntityParentGUID, IsDislike, CommentParentGUID, article) {

            var id = $('[data-guid="act-' + EntityParentGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };
            if (IsDislike == '1')
            {
                reqData['Dislike'] = 1;
            }

            function likeObjModifiy(articleObj) {
                if (Type == 'ACTIVITY') {

                }

                if (IsDislike == '1')
                {
                    if (articleObj.IsDislike == 1) {
                        articleObj.IsDislike = 0;
                        //NewsFeedCtrl.activityData[key].NoOfDislikes--;
                    } else {
                        if (articleObj.IsLike == '1')
                        {
                            articleObj.IsLike = 0;
                            articleObj.NoOfLikes--;
                        }
                        articleObj.IsDislike = 1;
                        //NewsFeedCtrl.activityData[key].NoOfDislikes++;
                    }
                } else
                {
                    if (articleObj.IsLike == 1) {
                        articleObj.IsLike = 0;
                        articleObj.NoOfLikes--;
                    } else {
                        if (articleObj.IsDislike == '1')
                        {
                            articleObj.IsDislike = 0;
                            //NewsFeedCtrl.activityData[key].NoOfDislikes--;
                        }
                        articleObj.IsLike = 1;
                        articleObj.NoOfLikes++;
                    }
                }
                WallService.CallApi({
                    EntityGUID: EntityGUID,
                    EntityType: Type,
                    PageNo: 1,
                    PageSize: 10
                }, 'activity/getLikeDetails').then(function (response) {
                    if (response.ResponseCode == 200) {
                        articleObj.LikeList = response.Data;
                    }
                });
            }

            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.article_list).each(function (key, value) {
                        if ($scope.article_list[key].ActivityGUID == EntityParentGUID) {
                            likeObjModifiy($scope.article_list[key]);
                        }
                    });
                    
                    if(article) {
                        likeObjModifiy(article);
                    }

                } else {
                    // Error
                }
            }, function (error) {
                // Error
            });
        }

        $scope.getMediaClass = function (media) {
            if (media.length == 1)
            {
                return 'post-media single'
            } else if (media.length == 2)
            {
                return 'post-media two';
            } else
            {
                return 'row gutter-5 post-media morethan-two'
            }
        }

        $scope.likeEmitWidgetArticle = function (EntityGUID, Type, EntityParentGUID, IsDislike, CommentParentGUID) {

            var id = $('[data-guid="act-' + EntityParentGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };
            if (IsDislike == '1')
            {
                reqData['Dislike'] = 1;
            }
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.widget_articles, function (val, key) {
                        angular.forEach(val.Data, function (v, k) {
                            if (v.ActivityGUID == EntityParentGUID) {
                                if (v.IsLike == 1) {
                                    $scope.widget_articles[key]['Data'][k].IsLike = 0;
                                    $scope.widget_articles[key]['Data'][k].NoOfLikes--;
                                } else {
                                    $scope.widget_articles[key]['Data'][k].IsLike = 1;
                                    $scope.widget_articles[key]['Data'][k].NoOfLikes++;
                                }
                            }
                        });
                    });

                } else {
                    // Error
                }
            }, function (error) {
                // Error
            });
        }

        $scope.deleteCommentEmit = function (CommentGUID, ActivityGUID, CommentParentGUID, PostTypeID, IsOwner) {
            
            jsonData = {
                CommentGUID: CommentGUID
            };
            var confirm_title = "Delete Comment";
            var confirm_message = "Are you sure, you want to delete this comment ?";
            if (CommentParentGUID) {
                if (CommentParentGUID !== '') {
                    confirm_title = "Delete Reply";
                    confirm_message = "Are you sure, you want to delete this reply ?";
                }
            }
            if (PostTypeID == '2') {
               // confirm_title = "Delete Answer";
               // confirm_message = "Are you sure, you want to delete this answer ?";
            }
            showInputConfirmBox(confirm_title, confirm_message, IsOwner, function (e) {
                if (e) {
                    var reason = $('#reason').val();
                    jsonData['Reason'] = reason;
                    WallService.CallApi(jsonData, 'activity/deleteComment').then(function (response) {
                        var aid = '';
                        var cid = '';
                        if (response.ResponseCode == 200) {
                            $(NewsFeedCtrl.activityData).each(function (key, value) {
                                if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID) {
                                    aid = key;
                                    $(NewsFeedCtrl.activityData[aid].Comments).each(function (ckey, cvalue) {
                                        if (NewsFeedCtrl.activityData[aid].Comments[ckey].CommentGUID == CommentGUID || NewsFeedCtrl.activityData[aid].Comments[ckey].CommentGUID == CommentParentGUID) {
                                            if (NewsFeedCtrl.activityData[aid].Comments[ckey].CommentGUID == CommentGUID)
                                            {
                                                cid = ckey;
                                                NewsFeedCtrl.activityData[aid].Comments.splice(cid, 1);
                                                NewsFeedCtrl.activityData[aid].NoOfComments = parseInt(NewsFeedCtrl.activityData[aid].NoOfComments) - 1;
                                                return false;
                                            } else
                                            {
                                                angular.forEach(NewsFeedCtrl.activityData[aid].Comments[ckey].Replies, function (v2, k2) {
                                                    if (v2.CommentGUID == CommentGUID)
                                                    {
                                                        cid = k2;
                                                        NewsFeedCtrl.activityData[aid].Comments[ckey].Replies.splice(cid, 1);
                                                        NewsFeedCtrl.activityData[aid].Comments[ckey].NoOfReplies = parseInt(NewsFeedCtrl.activityData[aid].Comments[ckey].NoOfReplies) - 1;
                                                        return false;
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }
                            });

                            $(NewsFeedCtrl.group_announcements).each(function (key, value) {
                                if (NewsFeedCtrl.group_announcements[key].ActivityGUID == ActivityGUID) {
                                    aid = key;
                                    $(NewsFeedCtrl.group_announcements[aid].Comments).each(function (ckey, cvalue) {
                                        if (NewsFeedCtrl.group_announcements[aid].Comments[ckey].CommentGUID == CommentGUID || NewsFeedCtrl.group_announcements[aid].Comments[ckey].CommentGUID == CommentParentGUID) {
                                            if (NewsFeedCtrl.group_announcements[aid].Comments[ckey].CommentGUID == CommentGUID)
                                            {
                                                cid = ckey;
                                                NewsFeedCtrl.group_announcements[aid].Comments.splice(cid, 1);
                                                NewsFeedCtrl.group_announcements[aid].NoOfComments = parseInt(NewsFeedCtrl.group_announcements[aid].NoOfComments) - 1;
                                                return false;
                                            } else
                                            {
                                                angular.forEach(NewsFeedCtrl.group_announcements[aid].Comments[ckey].Replies, function (v2, k2) {
                                                    if (v2.CommentGUID == CommentGUID)
                                                    {
                                                        cid = k2;
                                                        NewsFeedCtrl.group_announcements[aid].Comments[ckey].Replies.splice(cid, 1);
                                                        NewsFeedCtrl.group_announcements[aid].Comments[ckey].NoOfReplies = parseInt(NewsFeedCtrl.group_announcements[aid].Comments[ckey].NoOfReplies) - 1;
                                                        return false;
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }
                            });

                            if (IsNewsFeed == '1')
                            {
                                $($scope.popularData).each(function (key, value) {
                                    if ($scope.popularData[key].ActivityGUID == ActivityGUID) {
                                        aid = key;
                                        $($scope.popularData[aid].Comments).each(function (ckey, cvalue) {
                                            if ($scope.popularData[aid].Comments[ckey].CommentGUID == CommentGUID) {
                                                cid = ckey;
                                                $scope.popularData[aid].Comments.splice(cid, 1);
                                                $scope.popularData[aid].NoOfComments = parseInt(NewsFeedCtrl.activityData[aid].NoOfComments) - 1;
                                                return false;
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    });
                }
            });
        };

        $scope.isAttachementUploading = false;
        var mediaCurrentIndex = 0;
        var fileCurrentIndex = 0;
        $scope.uploadFiles = function (files, errFiles, id, feedIndex, IsAnnouncement, noFiles) {
            var promises = [];
            if (!(errFiles.length > 0)) {
                if (IsAnnouncement == '1')
                {
                    if (!$scope.group_announcements[feedIndex].medias) {
                        $scope.group_announcements[feedIndex]['medias'] = {};
                        $scope.group_announcements[feedIndex]['commentMediaCount'] = 0;
                    }

                    if (!$scope.group_announcements[feedIndex].files) {
                        $scope.group_announcements[feedIndex]['files'] = {};
                        $scope.group_announcements[feedIndex]['commentFileCount'] = 0;
                    }

                    var patt = new RegExp("^image|video");
                    var videoPatt = new RegExp("^video");
                    var promises = [];
                    $scope.isAttachementUploading = true;
                    angular.element('#cmt-' + id).focus();
                    angular.forEach(files, function (fileToUpload, key) {
                        (function (file, fileIndex, mediaIndex) {
                            var fileType = 'media';
                            WallService.setFileMetaData(file);
                            var paramsToBeSent = {
                                Type: 'comments',
                                DeviceType: 'Native',
                                qqfile: file
                            };
                            if (patt.test(file.type)) {
                                $scope.group_announcements[feedIndex].medias['media-' + mediaIndex] = file;
                                $scope.group_announcements[feedIndex]['commentMediaCount'] = Object.keys($scope.group_announcements[feedIndex].medias).length;
                            } else {
                                if(!noFiles) {
                                    $scope.group_announcements[feedIndex].files['file-' + fileIndex] = file;
                                    $scope.group_announcements[feedIndex]['commentFileCount'] = Object.keys($scope.group_announcements[feedIndex].files).length;
                                    fileType = 'file';
                                    paramsToBeSent['IsDocument'] = '1';
                                }                                
                            }
                            var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                            var promise = WallService.CallUploadFilesApi(
                                    paramsToBeSent,
                                    url,
                                    function (response) {

                                        WallService.FileUploadProgress({fileType: fileType, scopeObj: $scope.group_announcements[feedIndex], fileIndex: fileIndex, mediaIndex: mediaIndex}, {}, response);

                                        if (fileType === 'media') {
                                            if (response.data.ResponseCode == 200) {
                                                $scope.group_announcements[feedIndex].medias['media-' + mediaIndex]['data'] = response.data.Data;
                                                $scope.group_announcements[feedIndex].medias['media-' + mediaIndex].progress = true;
                                                hideButtonLoader('PostBtn-' + id);
                                            } else {
                                                delete $scope.group_announcements[feedIndex].medias['media-' + mediaIndex];
                                                $scope.group_announcements[feedIndex]['commentMediaCount'] = Object.keys($scope.group_announcements[feedIndex].medias).length;
                                                showResponseMessage(response.data.Message, 'alert-danger');
                                            }
                                        } else {
                                            if (response.data.ResponseCode == 200) {
                                                $scope.group_announcements[feedIndex].files['file-' + fileIndex]['data'] = response.data.Data;
                                                $scope.group_announcements[feedIndex].files['file-' + fileIndex].progress = true;
                                                hideButtonLoader('PostBtn-' + id);
                                            } else {
                                                delete $scope.group_announcements[feedIndex].files['file-' + fileIndex];
                                                $scope.group_announcements[feedIndex]['commentFileCount'] = Object.keys($scope.group_announcements[feedIndex].files).length;
                                                showResponseMessage(response.data.Message, 'alert-danger');
                                            }
                                        }
                                        if ((Object.keys($scope.group_announcements[feedIndex].files).length === 0) && (Object.keys($scope.group_announcements[feedIndex].medias).length === 0)) {
                                            IsMediaExists = 0;
                                        }
                                    },
                                    function (response) {
                                        if (fileType === 'media') {
                                            delete $scope.group_announcements[feedIndex].medias['media-' + mediaIndex];
                                            $scope.group_announcements[feedIndex]['commentMediaCount'] = Object.keys($scope.group_announcements[feedIndex].medias).length;
                                        } else {
                                            delete $scope.group_announcements[feedIndex].files['file-' + fileIndex];
                                            $scope.group_announcements[feedIndex]['commentFileCount'] = Object.keys($scope.group_announcements[feedIndex].files).length;
                                        }
                                        if ((Object.keys($scope.group_announcements[feedIndex].files).length === 0) && (Object.keys($scope.group_announcements[feedIndex].medias).length === 0)) {
                                            IsMediaExists = 0;
                                        }
                                    },
                                    function (evt) {
                                        WallService.FileUploadProgress({fileType: fileType, scopeObj: $scope.group_announcements[feedIndex], fileIndex: fileIndex, mediaIndex: mediaIndex}, evt, {});
                                    });
                            if (fileType === 'media') {
                                mediaCurrentIndex++;
                            } else {
                                fileCurrentIndex++;
                            }
                            promises.push(promise);

                        })(fileToUpload, fileCurrentIndex, mediaCurrentIndex);
                    });
                    $q.all(promises).then(function (data) {
                        $scope.isAttachementUploading = false;
                    });
                } else
                {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == id)
                        {
                            feedIndex = key;
                            if (!NewsFeedCtrl.activityData[key].medias) {
                                NewsFeedCtrl.activityData[key]['medias'] = {};
                                NewsFeedCtrl.activityData[key]['commentMediaCount'] = 0;
                            }

                            if (!NewsFeedCtrl.activityData[key].files) {
                                NewsFeedCtrl.activityData[key]['files'] = {};
                                NewsFeedCtrl.activityData[key]['commentFileCount'] = 0;
                            }
                        }
                    });

                    var patt = new RegExp("^image|video");
                    var videoPatt = new RegExp("^video");
                    var promises = [];
                    $scope.isAttachementUploading = true;
                    angular.element('#cmt-' + id).focus();
                    angular.forEach(files, function (fileToUpload, key) {
                        (function (file, fileIndex, mediaIndex) {
                            var fileType = 'media';
                            var paramsToBeSent = {
                                Type: 'comments',
                                DeviceType: 'Native',
                                qqfile: file
                            };
                            if (patt.test(file.type)) {
                                NewsFeedCtrl.activityData[feedIndex].medias['media-' + mediaIndex] = file;
                                NewsFeedCtrl.activityData[feedIndex]['commentMediaCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length;
                            } else {
                                if(!noFiles) {
                                    NewsFeedCtrl.activityData[feedIndex].files['file-' + fileIndex] = file;
                                    NewsFeedCtrl.activityData[feedIndex]['commentFileCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length;
                                    fileType = 'file';
                                    paramsToBeSent['IsDocument'] = '1';
                                }
                                
                            }
                            var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                            if (IsAdminView == '1')
                            {
                                url = 'adminupload_image';
                            }
                            var promise = WallService.CallUploadFilesApi(
                                    paramsToBeSent,
                                    url,
                                    function (response) {
                                        WallService.FileUploadProgress({fileType: fileType, scopeObj: NewsFeedCtrl.activityData[feedIndex], fileIndex: fileIndex, mediaIndex: mediaIndex}, {}, response);

                                        if (fileType === 'media') {
                                            if (response.data.ResponseCode == 200) {
                                                NewsFeedCtrl.activityData[feedIndex].medias['media-' + mediaIndex]['data'] = response.data.Data;
                                                NewsFeedCtrl.activityData[feedIndex].medias['media-' + mediaIndex].progress = true;
                                                hideButtonLoader('PostBtn-' + id);
                                            } else {
                                                delete NewsFeedCtrl.activityData[feedIndex].medias['media-' + mediaIndex];
                                                NewsFeedCtrl.activityData[feedIndex]['commentMediaCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length;
                                                showResponseMessage(response.data.Message, 'alert-danger');
                                            }
                                        } else {
                                            if (response.data.ResponseCode == 200) {
                                                NewsFeedCtrl.activityData[feedIndex].files['file-' + fileIndex]['data'] = response.data.Data;
                                                NewsFeedCtrl.activityData[feedIndex].files['file-' + fileIndex].progress = true;
                                                hideButtonLoader('PostBtn-' + id);
                                            } else {
                                                delete NewsFeedCtrl.activityData[feedIndex].files['file-' + fileIndex];
                                                NewsFeedCtrl.activityData[feedIndex]['commentFileCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length;
                                                showResponseMessage(response.data.Message, 'alert-danger');
                                            }
                                        }
                                        if ((Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length === 0) && (Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length === 0)) {
                                            IsMediaExists = 0;
                                        }
                                    },
                                    function (response) {
                                        if (fileType === 'media') {
                                            delete NewsFeedCtrl.activityData[feedIndex].medias['media-' + mediaIndex];
                                            NewsFeedCtrl.activityData[feedIndex]['commentMediaCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length;
                                        } else {
                                            delete NewsFeedCtrl.activityData[feedIndex].files['file-' + fileIndex];
                                            NewsFeedCtrl.activityData[feedIndex]['commentFileCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length;
                                        }
                                        if ((Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length === 0) && (Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length === 0)) {
                                            IsMediaExists = 0;
                                        }
                                    },
                                    function (evt) {
                                        WallService.FileUploadProgress({fileType: fileType, scopeObj: NewsFeedCtrl.activityData[feedIndex], fileIndex: fileIndex, mediaIndex: mediaIndex}, evt, {});
                                    });
                            if (fileType === 'media') {
                                mediaCurrentIndex++;
                            } else {
                                fileCurrentIndex++;
                            }
                            promises.push(promise);

                        })(fileToUpload, fileCurrentIndex, mediaCurrentIndex);
                    });
                    $q.all(promises).then(function (data) {
                        $scope.isAttachementUploading = false;
                    });
                }
            } else {
                var msg = '';
                angular.forEach(errFiles, function (errFile, key) {
                    msg += '\n' + errFile.$errorMessages;
                    promises.push(makeResolvedPromise(msg));
                });
                $q.all(promises).then(function (data) {
                    showResponseMessage(msg, 'alert-danger');
                });
            }
        };
        $scope.EdituploadFiles = function (files, errFiles, commentData, noFiles) {
            var id = commentData.CommentGUID;
//            $scope.errFiles = errFiles;
            var promises = [];
            if (!(errFiles.length > 0)) {

                var patt = new RegExp("^image|video");
                var videoPatt = new RegExp("^video");
                var promises = [];
                $scope.isAttachementUploading = true;
                angular.element('#cmt-' + id).focus();
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, fileIndex, mediaIndex) {
                        var fileType = 'media';
                        WallService.setFileMetaData(file);
                        var paramsToBeSent = {
                            Type: 'comments',
                            DeviceType: 'Native',
                            qqfile: file
                        };

                        if (patt.test(file.type)) {
                            commentData.Media.push({progress: false});
                        } else {
                            if(noFiles) {
                                commentData.Files.push({progress: false});
                                fileType = 'file';
                                paramsToBeSent['IsDocument'] = '1';
                            }                            
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';



                        $scope.EditComment.medias = $scope.EditComment.Media;
                        $scope.EditComment.files = $scope.EditComment.Files;

                        // Check the file index 
                        var newMediaIndex = $scope.EditComment.medias.length - 1;
                        var newFileIndex = $scope.EditComment.files.length - 1;

                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    WallService.FileUploadProgress({fileType: fileType, scopeObj: $scope.EditComment, fileIndex: newFileIndex, mediaIndex: newMediaIndex}, {}, response);
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode == 200) {
                                            var i = 0;
                                            $.each(commentData.Media, function (k) {
                                                if (!this.progress && i == 0)
                                                {
                                                    commentData.Media[k] = response.data.Data;
                                                    commentData.Media[k].progress = true;
                                                    i = i + 1;
                                                }
                                                ;
                                            })
                                            hideButtonLoader('PostBtn-' + id);
                                        } else {
                                            var i = 0;
                                            $.each(commentData.Media, function (k) {
                                                if (!this.progress && i == 0)
                                                {
                                                    commentData.Media.splice(k, 1);
                                                    i = i + 1;
                                                }
                                                ;
                                            })
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {

                                            var i = 0;
                                            $.each(commentData.Files, function (k) {
                                                if (!this.progress && i == 0)
                                                {
                                                    commentData.Files[k] = response.data.Data;
                                                    commentData.Files[k].progress = true;
                                                    i = i + 1;
                                                }
                                                ;
                                            })
                                            hideButtonLoader('PostBtn-' + id);
                                        } else {
                                            var i = 0;
                                            $.each(commentData.Files, function (k) {
                                                if (!this.progress && i == 0)
                                                {
                                                    commentData.Files.splice(k, 1);
                                                    i = i + 1;
                                                }
                                                ;
                                            })
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }

                                },
                                function (response) {
                                },
                                function (evt) {
                                    WallService.FileUploadProgress({fileType: fileType, scopeObj: $scope.EditComment, fileIndex: newFileIndex, mediaIndex: newMediaIndex}, evt, {});
                                });




                        if (fileType === 'media') {
                            mediaCurrentIndex++;
                        } else {
                            fileCurrentIndex++;
                        }
                        promises.push(promise);

                    })(fileToUpload, fileCurrentIndex, mediaCurrentIndex);
                });
                $q.all(promises).then(function (data) {
                    $scope.isAttachementUploading = false;
                });
            } else {
                var msg = '';
                angular.forEach(errFiles, function (errFile, key) {
                    msg += '\n' + errFile.$errorMessages;
                    promises.push(makeResolvedPromise(msg));
                });
                $q.all(promises).then(function (data) {
                    showResponseMessage(msg, 'alert-danger');
                });
            }
        };

        function makeResolvedPromise(data) {
            var deferred = $q.defer();
            deferred.resolve(data);
            return deferred.promise;
        }
        ;

        function createAttachementArray(attachement) {
            var deferred = $q.defer();
            deferred.resolve({
                MediaGUID: attachement.MediaGUID,
                MediaType: attachement.MediaType
            });
            return deferred.promise;
        }
        ;

        $scope.removeAttachement = function (type, index, feedIndex, IsAnnouncement) {
            if (IsAnnouncement == '1') {
                if ((type == 'file') && ($scope.group_announcements[feedIndex].files && (Object.keys($scope.group_announcements[feedIndex].files).length > 0))) {
                    delete $scope.group_announcements[feedIndex].files[index];
                    $scope.group_announcements[feedIndex]['commentFileCount'] = Object.keys($scope.group_announcements[feedIndex].files).length;
                } else if ($scope.group_announcements[feedIndex].medias && (Object.keys($scope.group_announcements[feedIndex].medias).length > 0)) {
                    delete $scope.group_announcements[feedIndex].medias[index];
                    $scope.group_announcements[feedIndex]['commentMediaCount'] = Object.keys($scope.group_announcements[feedIndex].medias).length;
                }
                if ((Object.keys($scope.group_announcements[feedIndex].files).length === 0) && (Object.keys($scope.group_announcements[feedIndex].medias).length === 0)) {
                    IsMediaExists = 0;
                }
                angular.element('#' + $scope.group_announcements[feedIndex].ActivityGUID).focus();
                angular.element('#' + $scope.group_announcements[feedIndex].ActivityGUID).blur();
            } else {
                if ((type == 'file') && (NewsFeedCtrl.activityData[feedIndex].files && (Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length > 0))) {
                    delete NewsFeedCtrl.activityData[feedIndex].files[index];
                    NewsFeedCtrl.activityData[feedIndex]['commentFileCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length;
                } else if (NewsFeedCtrl.activityData[feedIndex].medias && (Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length > 0)) {
                    delete NewsFeedCtrl.activityData[feedIndex].medias[index];
                    NewsFeedCtrl.activityData[feedIndex]['commentMediaCount'] = Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length;
                }
                if ((Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length === 0) && (Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length === 0)) {
                    IsMediaExists = 0;
                    $scope.checkEditorData({}, feedIndex)
                    //showButtonLoader('PostBtn-'+NewsFeedCtrl.activityData[feedIndex].ActivityGUID);
                }
                angular.element('#' + NewsFeedCtrl.activityData[feedIndex].ActivityGUID).focus();
                angular.element('#' + NewsFeedCtrl.activityData[feedIndex].ActivityGUID).blur();
            }
        };

        $scope.removeEditAttachement = function (type, mediaData, index) {
            mediaData.splice(index, 1);
        };

        $scope.addMediaClasses = function (mediaCount) {

            var mediaClass;
            switch (mediaCount) {
                case 1:
                    mediaClass = "post-media single";
                    break;
                case 2:
                    mediaClass = "post-media two";
                    break;
                default:
                    mediaClass = "row gutter-5 post-media morethan-two";
            }
            return mediaClass;
        };

        $scope.replyToComment = function (comment_guid, activity_guid, page_size, comnt, isAnnouncement)
        {
            comnt.ShowReply = !comnt.ShowReply;
            $('.reply-comment').hide();
            $('#r-' + comment_guid).show();
            //$scope.tagComment('rply-' + comment_guid);

            if (comnt.Replies.length == 0) {
                if (isAnnouncement) {
                    $scope.getCommentRepliesAnnouncement(comment_guid, activity_guid, page_size);
                } else {
                    $scope.getCommentReplies(comment_guid, activity_guid, page_size);
                }

            }
        }

        $scope.getCommentRepliesAnnouncement = function (comment_guid, activity_guid, page_size)
        {
            var jsonData = {
                ParentCommentGUID: comment_guid,
                PageNo: 1,
                PageSize: page_size,
                EntityType: 'Activity',
                EntityGUID: activity_guid
            };
            WallService.CallApi(jsonData, 'activity/getAllComments').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach($scope.group_announcements, function (val, key) {
                        if (val.ActivityGUID == activity_guid)
                        {
                            angular.forEach($scope.group_announcements[key].Comments, function (v, k) {
                                if (v.CommentGUID == comment_guid)
                                {
                                    $scope.group_announcements[key].Comments[k].Replies = response.Data;
                                }
                            });
                        }
                    });
                }
            });
        }

        $scope.getCommentReplies = function (comment_guid, activity_guid, page_size)
        {
            var jsonData = {
                ParentCommentGUID: comment_guid,
                PageNo: 1,
                PageSize: page_size,
                EntityType: 'Activity',
                EntityGUID: activity_guid
            };
            WallService.CallApi(jsonData, 'activity/getAllComments').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == activity_guid)
                        {
                            angular.forEach(NewsFeedCtrl.activityData[key].Comments, function (v, k) {
                                if (v.CommentGUID == comment_guid)
                                {
                                    NewsFeedCtrl.activityData[key].Comments[k].Replies = response.Data;
                                }
                            });
                        }
                    });
                }
            });
        }

        $scope.hideCommentReplies = function (comment_guid, activity_guid)
        {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == activity_guid)
                {
                    angular.forEach(NewsFeedCtrl.activityData[key].Comments, function (v, k) {
                        if (v.CommentGUID == comment_guid)
                        {
                            NewsFeedCtrl.activityData[key].Comments[k].Replies = [];
                        }
                    });
                }
            });
        }

        $scope.hideCommentRepliesAnnouncement = function (comment_guid, activity_guid)
        {
            angular.forEach($scope.group_announcements, function (val, key) {
                if (val.ActivityGUID == activity_guid)
                {
                    angular.forEach($scope.group_announcements[key].Comments, function (v, k) {
                        if (v.CommentGUID == comment_guid)
                        {
                            $scope.group_announcements[key].Comments[k].Replies = [];
                        }
                    });
                }
            });
        }

        $scope.replyBusy = false;
        $scope.replyEmit = function (event, comment_guid, activity_guid)
        {
            if (event.which == 13)
            {
                if (!event.shiftKey) {
                    event.preventDefault();
                    var Comment = $('#rply-' + comment_guid).val();
                    if (Comment.trim() == '')
                    {
                        return;
                    }
                    var id = $('[data-guid="act-' + activity_guid + '"]').attr('id');
                    var element = $('#' + id + ' .post-as-data');
                    var jsonData = {
                        ParentCommentGUID: comment_guid,
                        Comment: Comment,
                        EntityType: 'Activity',
                        EntityGUID: activity_guid,
                        PostAsModuleID: element.attr('data-module-id'),
                        PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                    };
                    if (!$scope.replyBusy)
                    {
                        $scope.replyBusy = true;
                        WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {
                            $('#rply-' + comment_guid).val('');
                            if (response.ResponseCode == 200)
                            {
                                angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                                    if (val.ActivityGUID == activity_guid)
                                    {
                                        angular.forEach(NewsFeedCtrl.activityData[key].Comments, function (v, k) {
                                            if (v.CommentGUID == comment_guid)
                                            {
                                                NewsFeedCtrl.activityData[key].Comments[k].NoOfReplies++;
                                                NewsFeedCtrl.activityData[key].Comments[k].Replies.push(response.Data[0]);
                                            }
                                        });
                                    }
                                });
                                $scope.replyBusy = false;
                            } else
                            {
                                $scope.replyBusy = false;
                            }
                        });
                    }
                }
            }
        }

        $scope.replyEmitAnnouncement = function (event, comment_guid, activity_guid)
        {
            if (event.which == 13)
            {
                if (!event.shiftKey) {
                    event.preventDefault();
                    var Comment = $('#rply-' + comment_guid).val();
                    if (Comment.trim() == '')
                    {
                        return;
                    }
                    var id = $('[data-guid="act-' + $scope.replyOnActivityGUID + '"]').attr('id');
                    var element = $('#' + id + ' .post-as-data');
                    var jsonData = {
                        ParentCommentGUID: comment_guid,
                        Comment: Comment,
                        EntityType: 'Activity',
                        EntityGUID: activity_guid,
                        PostAsModuleID: element.attr('data-module-id'),
                        PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                    };
                    WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {
                        $('#rply-' + comment_guid).val('');
                        if (response.ResponseCode == 200)
                        {
                            angular.forEach($scope.group_announcements, function (val, key) {
                                if (val.ActivityGUID == activity_guid)
                                {
                                    angular.forEach($scope.group_announcements[key].Comments, function (v, k) {
                                        if (v.CommentGUID == comment_guid)
                                        {
                                            $scope.group_announcements[key].Comments[k].NoOfReplies++;
                                            $scope.group_announcements[key].Comments[k].Replies.push(response.Data[0]);
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            }
        }

        $scope.commentEmitAnnouncement = function (event, ActivityGUID, feedIndex, cls, activityDataObj)
        {
            if (!$(cls + '#cm-' + ActivityGUID + ' li').hasClass('loading-class')) {
                if (($scope.isAttachementUploading === false)) {
                    $scope.appendComment = 1;

                    event.preventDefault();

                    var PComments = $('#cmt-div-' + ActivityGUID + ' .note-editable').html().trim();

                    var attacheMentPromises = [];
                    var Media = [];

                    if (typeof $scope.group_announcements[feedIndex] !== 'undefined')
                    {
                        if ($scope.group_announcements[feedIndex].medias && (Object.keys($scope.group_announcements[feedIndex].medias).length > 0)) {
                            angular.forEach($scope.group_announcements[feedIndex].medias, function (attachement, key) {
                                attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                                    Media.push({
                                        MediaGUID: dataToAttache.MediaGUID,
                                        MediaType: dataToAttache.MediaType,
                                        Caption: ''
                                    });
                                }));
                            });
                        }
                    }

                    if (typeof $scope.group_announcements[feedIndex] !== 'undefined')
                    {
                        if ($scope.group_announcements[feedIndex].files && (Object.keys($scope.group_announcements[feedIndex].files).length > 0)) {
                            angular.forEach($scope.group_announcements[feedIndex].files, function (attachement, key) {
                                attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                                    Media.push({
                                        MediaGUID: dataToAttache.MediaGUID,
                                        MediaType: dataToAttache.MediaType,
                                        Caption: ''
                                    });
                                }));
                            });
                        }
                    }

                    if (PComments == '' && ($scope.medias.length == 0) && ($scope.files.length == 0)) {
                        showResponseMessage('Please add attachement(s) or write something.', 'alert-danger');
                        showButtonLoader('PostBtn-' + ActivityGUID);
                        return false;
                    }
                    $q.all(attacheMentPromises).then(function (data) {
                        if ((Media.length == 0) && (PComments == '')) {
                            $(cls + '#cmt-' + ActivityGUID).val('');
                            return;
                        }
                        var id = $('[data-guid="act-' + ActivityGUID + '"]').attr('id');
                        var element = $('#' + id + ' .post-as-data');

                        if (PComments != "") {
                            PComments = $.trim(filterPContent(PComments));
                        }
                        var jsonData = {
                            Comment: PComments,
                            EntityType: 'Activity',
                            EntityGUID: ActivityGUID,
                            Media: Media,
                            PostAsModuleID: element.attr('data-module-id'),
                            PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                        };

                        activityDataObj.postingCommentsStatus = 1;

                        WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {

                            //$(cls+'#cmt-' + ActivityGUID).summernote('reset');
                            //$(cls+'#cmt-' + ActivityGUID).summernote('focus');

                            angular.forEach($scope.group_announcements, function (val, key) {
                                if (val.ActivityGUID == ActivityGUID)
                                {
                                    feedIndex = key;
                                }
                            });
                            if (typeof $scope.group_announcements[feedIndex] !== 'undefined')
                            {
                                var newArr = $scope.group_announcements[feedIndex].Comments;
                                newArr.push(response.Data[0]);
                                $scope.group_announcements[feedIndex].Comments = newArr.reduce(function (o, v, i) {
                                    o[i] = v;
                                    return o;
                                }, {});
                                if (typeof $scope.group_announcements[feedIndex].medias !== 'undefined')
                                {
                                    $scope.group_announcements[feedIndex].medias = {};
                                }
                                if (typeof $scope.group_announcements[feedIndex].files !== 'undefined')
                                {
                                    $scope.group_announcements[feedIndex].files = {};
                                }
                                $scope.group_announcements[feedIndex]['commentMediaCount'] = 0;
                                $scope.group_announcements[feedIndex]['commentFileCount'] = 0;
                                mediaCurrentIndex = 0;
                                fileCurrentIndex = 0;
                                $scope.group_announcements[feedIndex].Comments = newArr;
                                $scope.group_announcements[feedIndex].NoOfComments = parseInt($scope.group_announcements[feedIndex].NoOfComments) + 1;
                                $scope.group_announcements[feedIndex].comntData = $scope.$broadcast('appendComntEmit', NewsFeedCtrl.activityData[feedIndex].Comments); //getPostComments(NewsFeedCtrl.activityData[key].Comments);
                                $(cls + '#upload-btn-' + ActivityGUID).show();
                                $(cls + '#cm-' + ActivityGUID).html('');
                                $(cls + '#cm-' + ActivityGUID + ' li').remove();
                                $(cls + '#cm-' + ActivityGUID).hide();
                                $(cls + '#act-' + ActivityGUID + ' .attach-on-comment').show();
                                NewsFeedCtrl.activityData[feedIndex].IsSubscribed = 1;
                                /*setTimeout(function() {
                                 $(cls+cls+'#cmt-' + ActivityGUID).trigger('focus');
                                 }, 200);*/
                                angular.element('#' + $scope.group_announcements[feedIndex].ActivityGUID).focus();
                                angular.element('#' + $scope.group_announcements[feedIndex].ActivityGUID).blur();
                            }
                            $scope.show_comment_box = "";
                            $timeout(function () {
                                $scope.$apply();
                            }, 500);

                            activityDataObj.postingCommentsStatus = 0;
                        });

                    });

                }
            }
        }

        $scope.changeLikeStatus = function (sticky)
        {
            if (sticky.IsLike == 1)
            {
                sticky.IsLike = 0;
                sticky.NoOfLikes--;
            } else
            {
                sticky.IsLike = 1;
                sticky.NoOfLikes++;
            }
        }

        var IsMediaExists = 0;
        $scope.commentEmit = function (event, ActivityGUID, feedIndex, cls, activityDataObj) {
            if (!$(cls + '#cm-' + ActivityGUID + ' li').hasClass('loading-class')) {
                if (($scope.isAttachementUploading === false)) {

                    showButtonLoader('PostBtn-' + ActivityGUID);
                    $scope.appendComment = 1;

                    event.preventDefault();

                    var PComments = $('#cmt-div-' + ActivityGUID + ' .note-editable').html().trim();

                    /* jQuery('#cmt-div-' + ActivityGUID + ' .textntags-beautifier div strong').each(function (e) {
                     var details = $(cls+'#act-' + ActivityGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').attr('class');
                     var module_id = details.split('-')[1];
                     var module_entity_id = details.split('-')[2];
                     var name = $(cls+'#act-' + ActivityGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').text();
                     PComments = PComments.replace('<strong><span class="user-' + module_id + '-' + module_entity_id + '">' + name + '</span></strong>', '{{' + name + ':' +
                     module_entity_id + ':' + module_id + '}}');
                     });*/
                    var Media = [];
                    /* $(cls+'#cmt-' + ActivityGUID).val('');
                     $(cls+'#act-' + ActivityGUID).find('textntags-beautifier div').html('');
                     jQuery('.cmt-' + ActivityGUID).textntags('reset');
                     */
                    var attacheMentPromises = [];
                    var Media = [];


                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID)
                        {
                            feedIndex = key;
                        }
                    });

                    if (typeof NewsFeedCtrl.activityData[feedIndex] !== 'undefined')
                    {
                        if (NewsFeedCtrl.activityData[feedIndex].medias && (Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length > 0)) {
                            angular.forEach(NewsFeedCtrl.activityData[feedIndex].medias, function (attachement, key) {
                                attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                                    Media.push({
                                        MediaGUID: dataToAttache.MediaGUID,
                                        MediaType: dataToAttache.MediaType,
                                        Caption: ''
                                    });
                                }));
                            });
                        }
                    }

                    if (typeof NewsFeedCtrl.activityData[feedIndex] !== 'undefined')
                    {
                        if (NewsFeedCtrl.activityData[feedIndex].files && (Object.keys(NewsFeedCtrl.activityData[feedIndex].files).length > 0)) {
                            angular.forEach(NewsFeedCtrl.activityData[feedIndex].files, function (attachement, key) {
                                attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                                    Media.push({
                                        MediaGUID: dataToAttache.MediaGUID,
                                        MediaType: dataToAttache.MediaType,
                                        Caption: ''
                                    });
                                }));
                            });
                        }
                    }

                    if (PComments == '' && ($scope.medias.length == 0) && ($scope.files.length == 0)) {
                        showResponseMessage('Please add attachement(s) or write something.', 'alert-danger');
                        hideButtonLoader('PostBtn-' + ActivityGUID);
                        return false;
                    }



                    $q.all(attacheMentPromises).then(function (data) {
                        if ((Media.length == 0) && (PComments == '')) {
                            $(cls + '#cmt-' + ActivityGUID).val('');
                            return;
                        }
                        var id = $('[data-guid="act-' + ActivityGUID + '"]').attr('id');
                        var element = $('#' + id + ' .post-as-data');

                        if (PComments != "") {
                            PComments = $.trim(filterPContent(PComments));
                        }


                        // Parse it and wrap it in a div
                        var frag = $("<div>").append($.parseHTML(PComments));
                        // Find the relevant images
                        frag.find("img.tamemoji[emoji]").each(function() {
                          // Replace the image with a text node containing :::[dataattr2]:::
                          var $this = $(this);
                          var unicode_value = unicodeFromImage[$this.attr("emoji").slice(1, -1)];
                          $this.replaceWith($('<div style="'+$this.attr("style")+'">').html(unicode_value).addClass("parseemoji"))
                          //$this.replaceWith(document.createTextNode("< value='"+$this.attr("emoji")+"'></emoji>"));
                        });
                        // Get the HTML of the result
                        PComments = frag.html();

                        // console.log(PComments)

                        // var regex_3 = />[^<]+(\+?\d[\d]{8,12}\d)/gm;
                        // var contentText_3 = PComments.replace(regex_3, "<a href=\"tel:$&\">$&</a>");
                        // console.log(contentText_3);

                        var regex_1 = /(?![^<]*>)\+?\d[\d]{8,12}\d/g;
                        var contentText_1 = PComments.replace(regex_1, "<a href=\"tel:$&\">$&</a>");
                        // console.log(contentText_1);
                        // return false;

                        var jsonData = {
                            Comment: contentText_1,
                            EntityType: 'Activity',
                            EntityGUID: ActivityGUID,
                            Media: Media,
                            //EntityOwner: $(cls+'#act-' + ActivityGUID + ' .module-entity-owner').val(),
                            PostAsModuleID: element.attr('data-module-id'),
                            PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                        };

                        activityDataObj.postingCommentsStatus = 1;

                        WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {
                            if(response.ResponseCode == 200)
                            {
                                $scope.group_user_tags = [];
                                $scope.tagsto = [];
                                $('.tags input').val('');
                                //$scope.NotifyAll = false;
                                $scope.memTagCount = false;
                                $scope.showNotificationCheck = 0;
                                $(".group-contacts .tags").removeAttr('ng-class');
                                $(".group-contacts .tags input").attr('style', '');

                                $('#cmt-' + ActivityGUID).summernote('reset');
                                //$('#cmt-' + ActivityGUID).summernote('focus');

                                angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                                    if (val.ActivityGUID == ActivityGUID)
                                    {
                                        feedIndex = key;
                                    }
                                });
                                if (typeof NewsFeedCtrl.activityData[feedIndex] !== 'undefined')
                                {
                                    var newArr = NewsFeedCtrl.activityData[feedIndex].Comments;
                                    
                                    var frag = $("<div>").append($.parseHTML(response.Data[0].PostComment));
                                    // Find the relevant images
                                    frag.find(".parseemoji").each(function() {
                                      // Replace the image with a text node containing :::[dataattr2]:::
                                      var $this = $(this);
                                      var emoji_value = ':'+imageFromUnicode[$this.html()]+':';
                                      $this.replaceWith($('<img src="'+AssetBaseUrl+'img/emoji/blank.gif" style="'+$this.attr("style")+'">').addClass("img tamemoji").attr('emoji',emoji_value).attr('alt',emoji_value));
                                      //$this.replaceWith(document.createTextNode("< value='"+$this.attr("emoji")+"'></emoji>"));
                                    });
                                    // Get the HTML of the result
                                    response.Data[0].PostComment = frag.html();

                                    newArr.push(response.Data[0]);
                                    NewsFeedCtrl.activityData[feedIndex].Comments = newArr.reduce(function (o, v, i) {
                                        o[i] = v;
                                        return o;
                                    }, {});
                                    if (typeof NewsFeedCtrl.activityData[feedIndex].medias !== 'undefined')
                                    {
                                        NewsFeedCtrl.activityData[feedIndex].medias = {};
                                    }
                                    if (typeof NewsFeedCtrl.activityData[feedIndex].files !== 'undefined')
                                    {
                                        NewsFeedCtrl.activityData[feedIndex].files = {};
                                    }
                                    NewsFeedCtrl.activityData[feedIndex]['commentMediaCount'] = 0;
                                    NewsFeedCtrl.activityData[feedIndex]['commentFileCount'] = 0;
                                    mediaCurrentIndex = 0;
                                    fileCurrentIndex = 0;
                                    NewsFeedCtrl.activityData[feedIndex].Comments = newArr;
                                    NewsFeedCtrl.activityData[feedIndex].NoOfComments = parseInt(NewsFeedCtrl.activityData[feedIndex].NoOfComments) + 1;
                                    NewsFeedCtrl.activityData[feedIndex].comntData = $scope.$broadcast('appendComntEmit', NewsFeedCtrl.activityData[feedIndex].Comments); //getPostComments(NewsFeedCtrl.activityData[key].Comments);
                                    NewsFeedCtrl.activityData[feedIndex].showeditor = false;
                                    $scope.show_comment_box = "";
                                    $(cls + '#upload-btn-' + ActivityGUID).show();
                                    $(cls + '#cm-' + ActivityGUID).html('');
                                    $(cls + '#cm-' + ActivityGUID + ' li').remove();
                                    $(cls + '#cm-' + ActivityGUID).hide();
                                    $(cls + '#act-' + ActivityGUID + ' .attach-on-comment').show();
                                    NewsFeedCtrl.activityData[feedIndex].IsSubscribed = 1;
                                    /*setTimeout(function() {
                                     $(cls+cls+'#cmt-' + ActivityGUID).trigger('focus');
                                     }, 200);*/
                                    angular.element('#' + NewsFeedCtrl.activityData[feedIndex].ActivityGUID).focus();
                                    angular.element('#' + NewsFeedCtrl.activityData[feedIndex].ActivityGUID).blur();
                                }


                                if (IsNewsFeed == '1')
                                {
                                    angular.forEach($scope.popularData, function (val, key) {
                                        if (val.ActivityGUID == ActivityGUID)
                                        {
                                            feedIndex = key;
                                        }
                                    });
                                    if (typeof $scope.popularData[feedIndex] !== 'undefined')
                                    {
                                        var newArr = $scope.popularData[feedIndex].Comments;
                                        newArr.push(response.Data[0]);
                                        $scope.popularData[feedIndex].Comments = newArr.reduce(function (o, v, i) {
                                            o[i] = v;
                                            return o;
                                        }, {});
                                        $scope.popularData[feedIndex].medias = {};
                                        $scope.popularData[feedIndex]['commentMediaCount'] = 0;
                                        $scope.popularData[feedIndex].files = {};
                                        $scope.popularData[feedIndex]['commentFileCount'] = 0;
                                        mediaCurrentIndex = 0;
                                        fileCurrentIndex = 0;
                                        $scope.popularData[feedIndex].Comments = newArr;
                                        $scope.popularData[feedIndex].NoOfComments = parseInt(NewsFeedCtrl.activityData[feedIndex].NoOfComments) + 1;
                                        $scope.popularData[feedIndex].comntData = $scope.$broadcast('appendComntEmit', NewsFeedCtrl.activityData[feedIndex].Comments); //getPostComments(NewsFeedCtrl.activityData[key].Comments);

                                        $(cls + '#upload-btn-' + ActivityGUID).show();
                                        $(cls + '#cm-' + ActivityGUID).html('');
                                        $(cls + '#cm-' + ActivityGUID + ' li').remove();
                                        $(cls + '#cm-' + ActivityGUID).hide();
                                        $(cls + '#act-' + ActivityGUID + ' .attach-on-comment').show();
                                        NewsFeedCtrl.activityData[feedIndex].IsSubscribed = 1;

                                        angular.element('#' + $scope.popularData[feedIndex].ActivityGUID).focus();
                                        angular.element('#' + $scope.popularData[feedIndex].ActivityGUID).blur();
                                        /*$timeout(function() {
                                         $(cls+'#cmt-' + ActivityGUID).trigger('focus');
                                         }, 200);*/
                                    }
                                }

                                $timeout(function () {
                                    $scope.$apply();
                                }, 500);

                                /*angular.forEach(NewsFeedCtrl.activityData,function(val,key){
                                 $('#cmt-div-'+val.ActivityGUID+' .place-holder-label').show();
                                 $('#cmt-div-'+val.ActivityGUID+' .comment-section').addClass('hide');
                                 });*/
                                // $('#cmt-div-'+ActivityGUID+' .place-holder-label').show();
                                $scope.show_comment_box = "";

                                activityDataObj.postingCommentsStatus = 0;
                            }
                            
                        });

                    });

                }
            }
        }

        $scope.objLen = function (obj) {
            if(!obj) {
                return 0;
            }
            return Object.keys(obj).length;
        };

        $scope.EditcommentEmit = function (event, ActivityGUID, commentData, IsAnnouncement, FeedIndex, comnt) {
            event.preventDefault();
            var commentEditData = angular.copy(commentData);

            commentData = '';
            var CommentGUID = commentEditData.CommentGUID;
            var PComments = $('#comment-edit-block-' + CommentGUID + ' .note-editable').html().trim();

            var Media = [];
            var attacheMentPromises = [];

            if (commentEditData.Media && commentEditData.Media.length > 0) {
                angular.forEach(commentEditData.Media, function (attachement, key) {
                    Media.push({
                        MediaGUID: attachement.MediaGUID,
                        MediaType: attachement.MediaType,
                        Caption: ''
                    });
                });
            }

            if (commentEditData.Files && commentEditData.Files.length > 0) {
                angular.forEach(commentEditData.Files, function (attachement, key) {
                    Media.push({
                        MediaGUID: attachement.MediaGUID,
                        MediaType: attachement.MediaType,
                        Caption: ''
                    });
                });
            }

            if (PComments == '' && (commentEditData.Files.length == 0) && (commentEditData.Media.length == 0)) {
                showResponseMessage('Please add attachment(s) or write something.', 'alert-danger');
                showButtonLoader('PostBtn-' + ActivityGUID);
                return false;
            }

            comnt.postingCommentsStatus = 1;

            $q.all(attacheMentPromises).then(function (data) {
                if ((Media.length == 0) && (PComments == '')) {
                    $(cls + '#cmt-' + ActivityGUID).val('');
                    return;
                }
                var id = $('[data-guid="act-' + ActivityGUID + '"]').attr('id');
                var element = $('#' + id + ' .post-as-data');
                if (PComments != "") {
                    PComments = $.trim(filterPContent(PComments));
                }
                var jsonData = {
                    Comment: PComments,
                    CommentGUID: CommentGUID,
                    EntityType: 'Activity',
                    EntityGUID: ActivityGUID,
                    Media: Media,
                    //EntityOwner: $(cls+'#act-' + ActivityGUID + ' .module-entity-owner').val(),
                    PostAsModuleID: element.attr('data-module-id'),
                    PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                };
                WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {
                    if (IsAnnouncement == 1) {
                        angular.forEach($scope.group_announcements, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID)
                            {
                                feedIndex = key;
                                angular.forEach(val.Comments, function (v, k) {
                                    if (v.CommentGUID == CommentGUID)
                                    {
                                        $scope.group_announcements[key].Comments[k] = response.Data[0];
                                    }
                                })
                            }
                        });
                    } else {
                        var val = NewsFeedCtrl.activityData[FeedIndex];
                        angular.forEach(val.Comments, function (v, k) {
                            if (v.CommentGUID == CommentGUID)
                            {
                                NewsFeedCtrl.activityData[FeedIndex].Comments[k] = response.Data[0];
                            }
                        })

                        /*angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                         if (val.ActivityGUID == ActivityGUID)
                         {
                         feedIndex = key;
                         angular.forEach(val.Comments, function (v, k) {
                         if (v.CommentGUID == CommentGUID)
                         {
                         NewsFeedCtrl.activityData[key].Comments[k] = response.Data[0];
                         }
                         })
                         }
                         });*/
                    }

                    // $scope.$apply();
                    //$('#comment-view-block-' + CommentGUID).show();
                    //$('#comment-edit-block-' + CommentGUID).addClass('hide');
                    $scope.edit_comment_box = "";

                    comnt.postingCommentsStatus = 0;

                });

            });
        }

        $scope.cancelEditComment = function () {
            $scope.edit_comment_box = '';
        }

        $scope.cancelPostComment = function (data) {
            if (data) {
                data.showeditor = false;
            }
            $scope.show_comment_box = '';
        }



        $scope.toggleWatchlistStatus = function (ActivityGUID) {
            if (ActivityGUID) {
                WallService.CallApi({ActivityGUID: ActivityGUID}, 'watchlist/toggle_watchlist').then(function (response) {
                    if (response.ResponseCode == 200) {
                        $(NewsFeedCtrl.activityData).each(function (key, value) {
                            if (NewsFeedCtrl.activityData[key] && (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID)) {
                                if (NewsFeedCtrl.activityData[key].IsWatchList == 1) {
                                    NewsFeedCtrl.activityData[key].IsWatchList = 0;
                                    showResponseMessage('Removed from Watchlist.', 'alert-success');
                                } else {
                                    NewsFeedCtrl.activityData[key].IsWatchList = 1;
                                    showResponseMessage('Post has been added to watchlist', 'alert-success');
                                }
                            }
                        });
                    }
                });
            }
        }

        $scope.markAsDoneNotDone = function (ActivityGUID, statusToUpdate) {
            if (ActivityGUID && ((statusToUpdate == 'NOTDONE') || (statusToUpdate == 'DONE'))) {
                WallService.CallApi({ActivityGUID: ActivityGUID, TaskStatus: statusToUpdate}, 'activity/toggle_mydesk_task').then(function (response) {
                    if (response.ResponseCode == 200) {
                        $(NewsFeedCtrl.activityData).each(function (key, value) {
                            if (NewsFeedCtrl.activityData[key] && (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID)) {
                                if (statusToUpdate == 'NOTDONE') {
                                    NewsFeedCtrl.activityData[key].IsTaskDone = 0;
                                    showResponseMessage('Added back to My Desk', 'alert-success');
                                } else if (statusToUpdate == 'DONE') {
                                    NewsFeedCtrl.activityData.splice(key, 1);
                                    if (NewsFeedCtrl.activityData.length == 0) {
                                        NewsFeedCtrl.tr = 0;
                                    }
                                    showResponseMessage('Removed from My Desk', 'alert-success');
                                }
                            }
                        });
                    }
                });
            }
        }
        $scope.setFavouriteEmit = function (ActivityGUID) {
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'favourite/toggle_favourite').then(function (response) {
                if (response.ResponseCode == 200) {
                    $(NewsFeedCtrl.activityData).each(function (key, value) {
                        if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID) {
                            if (NewsFeedCtrl.activityData[key].IsFavourite == 1) {
                                NewsFeedCtrl.activityData[key].IsFavourite = 0;
                                $scope.tfr--;
                                if ($scope.tfr == 0) {
                                }
                                if ($('#ActivityFilterType').val() == 'Favourite') {
                                    NewsFeedCtrl.activityData.splice(key, 1);
                                    //$('#act-' + ActivityGUID).hide();
                                }
                                return false;
                            } else {
                                NewsFeedCtrl.activityData[key].IsFavourite = 1;
                                $scope.tfr++;
                            }
                        }
                    });

                    if ($scope.group_announcements.length > 0)
                    {
                        $($scope.group_announcements).each(function (key, value) {
                            if ($scope.group_announcements[key].ActivityGUID == ActivityGUID) {
                                if ($scope.group_announcements[key].IsFavourite == 1) {
                                    $scope.group_announcements[key].IsFavourite = 0;
                                    $scope.tfr--;
                                    if ($scope.tfr == 0) {
                                    }
                                    if ($('#ActivityFilterType').val() == 'Favourite') {
                                        $scope.group_announcements.splice(key, 1);
                                        //$('#act-' + ActivityGUID).hide();
                                    }
                                    return false;
                                } else {
                                    $scope.group_announcements[key].IsFavourite = 1;
                                    $scope.tfr++;
                                }
                            }
                        });
                    }
                }
            });
        }

        $scope.setFavouriteEmitAnnouncement = function (ActivityGUID) {
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'favourite/toggle_favourite').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.group_announcements).each(function (key, value) {
                        if ($scope.group_announcements[key].ActivityGUID == ActivityGUID) {
                            if ($scope.group_announcements[key].IsFavourite == 1) {
                                $scope.group_announcements[key].IsFavourite = 0;
                                $scope.tfr--;
                                if ($scope.tfr == 0) {
                                }
                                if ($('#ActivityFilterType').val() == 'Favourite') {
                                    $scope.group_announcements.splice(key, 1);
                                    //$('#act-' + ActivityGUID).hide();
                                }
                                return false;
                            } else {
                                $scope.group_announcements[key].IsFavourite = 1;
                                $scope.tfr++;
                            }
                        }
                    });
                }
            });
        }
        $scope.tagsto = [];
        $scope.memberSelect = [];
        $scope.adminSelect = [];
        $scope.fileName = new Array();
        $scope.startExecution = function () {
            $scope.stopExecution = 0;
        }
        $scope.updateFileName = function (fileName) {
            $scope.fileName.push(fileName);
        }
        $scope.showPhotoUpload = function () {
            if ($('.video-itm').length > 0) {
                return false;
            } else {
                return true;
            }
        }
        $scope.setUrl = '';
        $scope.UrlThumbGenerate = false;
        $scope.UrlToCompare = '';
        $scope.isValidURL = function (url)
        {
            var RegExp = /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
            if (RegExp.test(url))
            {
                return true;
            } else
            {
                return false;
            }
        }
        $scope.showEditableVal = function (key, val)
        {
            $scope.showEditable[key] = val;
        }
        $scope.enterUrl = function (event)
        {
            if (event.keyCode == 13)
            {
                $scope.showEditable['Title'] = 0;
            }
        }
        $scope.linktagsto = [];
        $scope.showEditable = {Title: 0, Tags: 0};
        //$scope.showUrlSec = false;
        $scope.linkProcessing = false;
        $scope.loadLinkTags1 = function ($query)
        {
            return $http.get(base_url + 'api/tag/get_entity_tags?&SearchKeyword=' + $query, {cache: true}).then(function (response) {
                var linkTags = response.data.Data;
                return linkTags.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        }
        $scope.parseLinkDataWithDelay = function (event, paste)
        {
            setTimeout(function () {
                $scope.parseLinkData(event, paste);
            }, 200);
        }

        $scope.removeParseLink = function (url)
        {
            angular.forEach($scope.parseLinks, function (v, k) {
                if (v.URL == url)
                {
                    $scope.parseLinks.splice(k, 1);
                    var urlIndex = $scope.allreadyProcessedLinks.indexOf(url);
                    if (urlIndex > -1) {
                        $scope.allreadyProcessedLinks.splice(urlIndex, 1);
                    }
                }
            });
        }


        $scope.update_title_keyup = function (val)
        {
            $scope.titleKeyup = val;
        }

        $scope.parseLinks = [];
        $scope.allreadyProcessedLinks = [];
        $scope.show_privacy = false;
        $scope.parseLinkData = function (url) {
            if ($scope.activePostType != '8' && $scope.activePostType != '9')
            {
                $scope.UrlThumbGenerate = false;
                $scope.UrlToCompare = url;

                var postContent = $(".note-editable").text().trim();
                
                if(postContent.length>0)
                {
                    $scope.show_privacy = true;
                }

                if ($scope.activePostType !== 1 && $('#PostTitleInput').val().length <= 140 && $scope.titleKeyup == 0 && !$scope.edit_post)
                {
                    if (settings_data.m40 == 1) {
                         $('#PostTitleInput').val(postContent);
                    }
                   
                    $('#PostTitleLimit').html(140 - postContent.length + ' characters');
                    if (140 - postContent.length == 1)
                    {
                        $('#PostTitleLimit').html('1 character');
                    }
                    if ($('#PostTitleInput').val().length > 140)
                    {
                        $('#PostTitleInput').val(postContent.substring(0, 140));
                        $('#PostTitleLimit').html('0 characters');
                    }
                }

                if ($('#PostTitleInput').val().length > 0)
                {
                    if (!$('#PostTitleInput').hasClass('title-focus'))
                    {
                        $('#PostTitleInput').addClass('title-focus');
                    }
                } else
                {
                    if ($('#PostTitleInput').hasClass('title-focus'))
                    {
                        $('#PostTitleInput').removeClass('title-focus');
                    }
                }

                if (postContent || $scope.fileCount > 0 || $scope.mediaCount) {
                    //$scope.noContentToPost = false;
                } else {
                    //$scope.noContentToPost = true;
                }
                if (!$scope.isValidURL(url)) {
                    return false;
                } else {
                    if($scope.parseLinks.length>0)
                    {
                        return false;
                    }
                    $scope.linkProcessing = true;
                    $scope.parseLink = {
                        Title: '',
                        URL: '',
                        Tags: [],
                        Thumbs: [],
                        Thumb: '',
                        HideThumb: false
                    };
                    var jsonData = {
                        url: url
                    }
                    var callService = true;
                    var linkPromises = [];
                    angular.forEach($scope.allreadyProcessedLinks, function (linkUrl, linkKey) {
                        linkPromises.push(makeResolvedPromise(linkUrl).then(function (datUrl) {
                            if (datUrl == url) {
                                callService = false;
                            }
                        }));
                    });
                    $q.all(linkPromises).then(function (data) {
                        if (callService) {
                            $scope.allreadyProcessedLinks.push(url);
                            WallService.CallApi(jsonData, 'wallpost/parseLinkData').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    $scope.parseLink.showUrlSec = true;
                                    $scope.parseLink.Title = response.Data.title;
                                    $scope.parseLink.URL = response.Data.url;
                                    $scope.linktagsto[$scope.parseLink.URL] = [];
                                    $scope.parseLink.Thumbs = response.Data.images;
                                    $scope.parseLink.Thumb = response.Data.image;
                                    $scope.parseLink.OrigURL = url;
                                    $scope.parseLinks.push($scope.parseLink);
                                    $scope.linkProcessing = false;
                                } else
                                {
                                    $scope.linkProcessing = false;
                                }
                            });
                        } else {
                            $scope.linkProcessing = false;
                        }
                    });
                }
            }

        }

        $scope.slickSlider = function (sliderId) {
            $scope.IsContest = 0;
            setTimeout(function () {
                $(sliderId).slick({
                    dots: false,
                    infinite: false,
                    speed: 300,
                    slidesToShow: 1
                });
            }, 0);
        }

        $scope.commentpostchange = function ()
        {
            commentpostchange();
        }

        $scope.resetFormPost = function () {
            $('.btn-onoff').addClass('on');
            $('#commentablePost').find('span').text('On');
            $('#commentablePost').find('i').removeClass('icon-off');
            $('#commentablePost').find('i').addClass('icon-on');
            $('#comments_settings').val(0);
            //$('#visible_for').val(1);
            //$('#IconSelect').attr('class', 'icon-every');
            if (IsAdminView == '1')
            {
                $scope.postasuser = [];
                angular.element(document.getElementById('UserListCtrl')).scope().entities = [];
            }
        }

        $scope.changeActionAs = function (value, activity_guid)
        {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == activity_guid)
                {
                    NewsFeedCtrl.activityData[key].actionas = value;
                    WallService.CallApi({UserID: value.UserID, ActivityID: val.ActivityID}, 'activity/is_dummy_user_like').then(function (response) {
                        NewsFeedCtrl.activityData[key].IsLike = response.Data;
                    });
                    if(NewsFeedCtrl.activityData[key].Comments.length > 0) {
                        //console.log(NewsFeedCtrl.activityData[key].Comments.length);
                        setTimeout(function () {
                            $scope.viewAllComntEmit(key, activity_guid);
                        }, 600);
                        
                    }
                }
            });
        }

        $scope.dummy_activity_data = [];
        $scope.dummy_activity_data = function ()
        {
            var reqData = {};
            WallService.CallApi(reqData, appInfo.serviceUrl + 'admin_api/adminactivity/dummy_activities').then(function (response) {

            });
        }

        $scope.setpostasuser = function (val)
        {
            LoggedInUserID = val.UserID;

            $scope.postasuser = val;
            $scope.PostAs = [];
            $scope.postasgroup = [];
            //$scope.get_all_group_of_user($scope.postasuser.UserID);

        }


        $scope.viewActivity = function (EntityGUID) {
            jsonData = {
                EntityType: 'Activity',
                EntityGUID: EntityGUID
            };

            if (LoginSessionKey == '')
            {
                return false;
            }



            $.post(base_url + 'api/log', jsonData, function (response) {
                $(NewsFeedCtrl.activityData).each(function (k, v) {
                    if (NewsFeedCtrl.activityData[k].ActivityGUID == EntityGUID) {
                        NewsFeedCtrl.activityData[k].Viewed = 1;
                    }
                });
            });

//            WallService.CallApi(jsonData, 'log').then(function (response) {
//                $(NewsFeedCtrl.activityData).each(function (k, v) {
//                    if (NewsFeedCtrl.activityData[k].ActivityGUID == EntityGUID) {
//                        NewsFeedCtrl.activityData[k].Viewed = 1;
//                    }
//                });
//            });
        }
        $scope.getSelectedDate = function (reminder_data, type) {
            if (typeof reminder_data === 'undefined' || typeof reminder_data.UndoDateTime === 'undefined') {
                return '';
            }
            if (type == '1') {
                if (reminder_data.UndoDateTime == TomorrowDate) {
                    return 'active permActive';
                }
            } else {
                if (reminder_data.UndoDateTime == NextWeekDate) {
                    return 'active permActive';
                }
            }
        }
        $scope.activeClassAdd = function (activity_guid, type) {
            angular.forEach(NewsFeedCtrl.activityData, function (v, k) {
                if (v.ActivityGUID == activity_guid) {
                    if (typeof NewsFeedCtrl.activityData[k].ReminderData == 'undefined')
                    {
                        NewsFeedCtrl.activityData[k].ReminderData = prepareReminderData(NewsFeedCtrl.activityData[k].Reminder);
                    }
                    if (type == '1') {
                        NewsFeedCtrl.activityData[k].ReminderData.UndoDateTime = TomorrowDate;
                    } else {
                        NewsFeedCtrl.activityData[k].ReminderData.UndoDateTime = NextWeekDate;
                    }
                }
            });
            if (IsNewsFeed == '1')
            {
                angular.forEach($scope.popularData, function (v, k) {
                    if (v.ActivityGUID == activity_guid) {
                        if (typeof $scope.popularData[k].ReminderData == 'undefined')
                        {
                            $scope.popularData[k].ReminderData = prepareReminderData($scope.popularData[k].Reminder);
                        }
                        if (type == '1') {
                            $scope.popularData[k].ReminderData.UndoDateTime = TomorrowDate;
                        } else {
                            $scope.popularData[k].ReminderData.UndoDateTime = NextWeekDate;
                        }
                    }
                });
            }
        }

        $scope.activeClassAddAnnouncement = function (activity_guid, type) {
            angular.forEach($scope.group_announcements, function (v, k) {
                if (v.ActivityGUID == activity_guid) {
                    if (typeof $scope.group_announcements[k].ReminderData == 'undefined')
                    {
                        $scope.group_announcements[k].ReminderData = prepareReminderData($scope.group_announcements[k].Reminder);
                    }
                    if (type == '1') {
                        $scope.group_announcements[k].ReminderData.UndoDateTime = TomorrowDate;
                    } else {
                        $scope.group_announcements[k].ReminderData.UndoDateTime = NextWeekDate;
                    }
                }
            });
        }

        $scope.strip = function(html)
        {
           var tmp = document.createElement("DIV");
           tmp.innerHTML = html;
           return tmp.textContent || tmp.innerText || "";
        }

        $scope.textToLinkStripTags = function(inputText, onlyShortText, count)
        {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                inputText = $scope.strip(inputText);
                inputText = inputText.toString();
                inputText = inputText.replace(new RegExp('contenteditable', 'g'), 'contenteditabletext');
                var replacedText, replacePattern1, replacePattern2, replacePattern3;
                inputText = inputText.replace(new RegExp('contenteditable', 'g'), "contenteditabletext");
                replacedText = inputText.replace("<br>", " ||| ");
                /*replacedText = replacedText.replace(/</g, '&lt');
                 replacedText = replacedText.replace(/>/g, '&gt');
                 replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                 replacedText = replacedText.replace(/&lt/g, '<');
                 replacedText = replacedText.replace(/&gt/g, '>');*/
                //URLs starting with http://, https://, or ftp://
                replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                replacedText = replacedText.replace(replacePattern1, function ($1) {
                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    var youtubeid = $scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return href;
                    }
                });

                //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
                replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
                replacedText = replacedText.replace(replacePattern2, function ($1, $2) {

                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    href = href.trim();
                    var youtubeid = $scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return href;
                    }

                });
                //Change email addresses to mailto:: links.
                replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                replacedText = replacedText.replace("href=\"<iframe width=\"420\" height=\"315\" src=\"", "href=\"");
                replacedText = replacedText.replace("frameborder=\"0\" allowfullscreen></iframe>", "");

                replacedText = replacedText.replace(" ||| ", "<br>");
                replacedText = checkTaggedData(replacedText);
                var repTxt = removeTags(replacedText);
                var totalwords = 200;
                if ($('#IsForum').length > 0)
                {
                    totalwords = 80;
                    if (count)
                    {
                        totalwords = count;
                    }
                }

                if ($scope.IsSinglePost)
                {
                    replacedText = $sce.trustAsHtml(replacedText);
                    return replacedText
                }

                if (repTxt && (repTxt.length > totalwords)) {
                    var smart_text = '<span class="show-less">' + smart_sub_str(totalwords, replacedText, onlyShortText) + '</span>';
                    if (!onlyShortText) {
                        replacedText = smart_text + '<span class="show-more">' + replacedText + '</span>';
                    } else {
                        replacedText = smart_text;
                    }
                }
                replacedText = $sce.trustAsHtml(replacedText);
                return replacedText
            } else {
                return '';
            }
        }

        $scope.textToLink = function (inputText, onlyShortText, count) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                inputText = inputText.toString();
                inputText = inputText.replace(new RegExp('contenteditable', 'g'), 'contenteditabletext');
                var replacedText, replacePattern1, replacePattern2, replacePattern3;
                inputText = inputText.replace(new RegExp('contenteditable', 'g'), "contenteditabletext");
                replacedText = inputText.replace("<br>", " ||| ");
                /*replacedText = replacedText.replace(/</g, '&lt');
                 replacedText = replacedText.replace(/>/g, '&gt');
                 replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                 replacedText = replacedText.replace(/&lt/g, '<');
                 replacedText = replacedText.replace(/&gt/g, '>');*/
                //URLs starting with http://, https://, or ftp://
                replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                replacedText = replacedText.replace(replacePattern1, function ($1) {
                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    var youtubeid = $scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return href;
                    }
                });

                //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
                replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
                replacedText = replacedText.replace(replacePattern2, function ($1, $2) {

                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    href = href.trim();
                    var youtubeid = $scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return href;
                    }

                });
                //Change email addresses to mailto:: links.
                replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                replacedText = replacedText.replace("href=\"<iframe width=\"420\" height=\"315\" src=\"", "href=\"");
                replacedText = replacedText.replace("frameborder=\"0\" allowfullscreen></iframe>", "");

                replacedText = replacedText.replace(" ||| ", "<br>");
                replacedText = checkTaggedData(replacedText);
                var repTxt = removeTags(replacedText);
                var totalwords = 200;
                if ($('#IsForum').length > 0)
                {
                    totalwords = 80;
                    if (count)
                    {
                        totalwords = count;
                    }
                }

                if ($scope.IsSinglePost)
                {
                    replacedText = $sce.trustAsHtml(replacedText);
                    return replacedText
                }

                if (repTxt && (repTxt.length > totalwords)) {
                    var smart_text = '<span class="show-less">' + smart_sub_str(totalwords, replacedText, onlyShortText) + '</span>';
                    if (!onlyShortText) {
                        replacedText = smart_text + '<span class="show-more">' + replacedText + '</span>';
                    } else {
                        replacedText = smart_text;
                    }
                }
                replacedText = $sce.trustAsHtml(replacedText);
                return replacedText
            } else {
                return '';
            }
        }

        function checkTaggedData(replacedText) {
            if (replacedText) {
                var regex = /<a\shref[\s\S]*>([\s\S]*)<\/a>/g,
                        matched,
                        highLightedText;
                if ((matched = regex.exec(replacedText)) !== null) {
                    replacedText = replacedText.replace(matched[0], '{{:*****:}}');
                    replacedText = $scope.getHighlighted(replacedText);
                    if (matched[1]) {
                        highLightedText = $scope.getHighlighted(matched[1]);
                        matched[0] = matched[0].replace(matched[1], highLightedText);
                    }
                    replacedText = replacedText.replace('{{:*****:}}', matched[0]);
                    return replacedText;
                } else {
                    return $scope.getHighlighted(replacedText);
                }
            }
        }
//        function checkTaggedData(replacedText) {
//          if (replacedText) {
//            var regex = /<a\shref[\s\S]*>([\s\S]*)<\/a>/g,
//                    matched,
//                    highLightedText;
//
//            if ((matched = regex.exec(replacedText)) !== null) {
//              highLightedText = $scope.getHighlighted(matched[1]);
//              replacedText = replacedText.replace(matched[1], highLightedText);
//              return $scope.getHighlighted(replacedText);
//            } else {
//              return $scope.getHighlighted(replacedText);
//            }
//          }
//        }

        $scope.hideLoader = function () {
            $scope.showLoader = 0;
            $('.loader-fad,.loader-view').css('display', 'none');
        }
        $scope.displayLoader = function () {
            $scope.showLoader = 1;
            //$('.loader-fad,.loader-view').css('display', 'block');
        }
        $scope.getRemiderCounts = function () {
            if ($scope.IsReminder === 1) {
                Url = 'reminder/get_reminder_count_by_date';
                $scope.ReminderCounts = [];
                var jsonData = {};
                WallService.CallApi(jsonData, Url).then(function (response) {
                    if (response.ResponseCode == 200) {
                        angular.forEach(response.Data, function (val, key) {
                            $scope.ReminderCounts[val.ReminderDate] = {
                                ReminderDate: val.ReminderDate,
                                Counts: val.Count
                            };
                        });
                        $scope.initStoredCalendar();
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                });
            }
        }
        $scope.getRemiderCounts();
        $scope.suggestPage = [];
        $scope.loadPages = function (query) {
            return $http.get(base_url + 'api/users/get_page_user_list?Search=' + query);
        };
        $scope.CloseUndoPopup = function () {
            $scope.UndoReminderData = {};
            $('#undoPopUp').fadeOut();
        }
        $scope.resetWallPageNo = function () {
            $scope.WallPageNo = 1;
        }

        $scope.getReminderDateFormat = function (CreatedDate) {
            return moment(CreatedDate).format('MMM D, YYYY');
        }

        $scope.initStoredCalendar = function () {
            $('#StoredReminderCal').datepicker({
                changeYear: true,
                changeMonth: true,
                showOtherMonths: false,
                dateFormat: 'yy-mm-dd',
                onSelect: function () {
                    var clicked_date = $(this).datepicker('getDate');
                    var date_format = formatDate(clicked_date);
                    var highlight = $scope.ReminderCounts[date_format];
                    if (highlight != undefined) {
                        var Count = $scope.ReminderCounts[date_format].Counts;
                        if (Count > 0) {
                            $scope.ReminderFilter = 1;
                            var append = true;
                            angular.forEach($scope.ReminderFilterDate, function (val, key) {
                                if (val == date_format) {
                                    append = false;
                                }
                            });
                            if (append) {
                                $scope.ReminderFilterDate.push(date_format);
                            }
                            $scope.getFilteredWall();
                        }
                    }
                },
                beforeShowDay: function (date) {
                    date = moment(date).format('YYYY-MM-DD');
                    var highlight = $scope.ReminderCounts[date];
                    if (highlight !== undefined) {
                        var Count = $scope.ReminderCounts[date].Counts;
                        return [true, "reminder rmndr-" + date, Count];
                    }
                    return [true, ''];
                }
            });
            $('#StoredReminderCal').datepicker('refresh');
        }
        $scope.check_group_members = function () {
            var new_arr = [];
            angular.forEach($scope.group_user_tags, function (memberObj, memberIndex) {
                if (memberObj.Type == "INFORMAL") {
                    angular.forEach(memberObj.Members, function (val, key) {
                        new_arr.push(val);
                    });
                } else {
                    new_arr.push(memberObj);
                }
            });
            return new_arr;
        }

        $scope.SubmitInviteGroupFriend = function () {
            if ($scope.inviteGroupFriend.length == 0) {
                showResponseMessage('Please select at least 1 person', 'alert-danger');
            } else {
                var reqData = {
                    GroupGUID: $('#module_entity_guid').val(),
                    UsersGUID: $scope.AddDelUser,
                    AddForceFully: '0'
                };
                WallService.CallApi(reqData, 'group/invite_users').then(function (response) {
                    if (response.ResponseCode == 200) {
                        showResponseMessage('Succesfully Invited', 'alert-success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 5000);
                    }
                });
            }
        }

        $scope.AddDelUser = new Array();
        $scope.totalSelected = 0;
        $scope.AddDeleteUserCheckbox = function () {
            $scope.AddDelUser = new Array();
            $('.add-delete-checkbox').each(function (k, v) {
                if ($('.add-delete-checkbox:eq(' + k + ')').is(':checked')) {
                    $scope.AddDelUser.push($(v).val());
                }
            });
            $scope.totalSelected = $scope.AddDelUser.length;
        }

        $scope.inviteGroupFriend = new Array();
        $scope.SearchGroupMember = '';
        $scope.addField = true;
        $scope.groupLimit = 8;
        $scope.groupOffset = 0;
        $scope.totalGroupRecords;
        $scope.getFriendsForGroup = function () {
            var reqData = {
                GroupGUID: $('#module_entity_guid').val(),
                SearchKey: $('#srch-filters').val(),
                Limit: $scope.groupLimit,
                Offset: $scope.groupOffset,
                SearchKey: $('#srch-filters2').val()
            };
            WallService.CallApi(reqData, 'group/get_friends_for_invite').then(function (response) {
                $scope.totalGroupRecords = response.TotalRecords;
                $(response.Data).each(function (k, v) {
                    $scope.addField = true;
                    $($scope.inviteGroupFriend).each(function (key, val) {
                        if (response.Data[k].UserGUID == $scope.inviteGroupFriend[key].UserGUID) {
                            $scope.addField = false;
                        }
                    });
                    if ($scope.addField) {
                        $scope.inviteGroupFriend.push(response.Data[k]);
                    }
                });
            });
        }

        $scope.undoReminder = function () {
            Action = $scope.UndoReminderData.action;
            switch (Action) {
                case 'delete':
                case 'archive':
                    $scope.UndoDelete();
                    break;
                case 'add':
                    $scope.UndoAddReminder();
                    break;
                case 'edit':
                    $scope.UndoEditReminder();
                case 'unarchive':
                    $scope.UndoUnarchiveReminder();
                    break;
            }
        }
        $scope.UndoDelete = function () {
            var Url = 'reminder/add';
            var ActivityGUID = $scope.UndoReminderData.ActivityGUID;
            var jsonData = {
                ActivityGUID: $scope.UndoReminderData.ActivityGUID,
                ReminderDateTime: $scope.UndoReminderData.UndoDateTime,
                Status: $scope.UndoReminderData.Status,
            };
            WallService.CallApi(jsonData, Url).then(function (response) {
                jsonData.ReminderGUID = response.Data.ReminderGUID;
                if (response.ResponseCode == 200) {
                    //Store temporary data for Undo purpose
                    NewsFeedCtrl.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = prepareReminderData(jsonData, 1);
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('setDate', new Date($scope.UndoReminderData.UndoDateTime));
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('refresh');
                    $scope.CloseUndoPopup();
                    $scope.getRemiderCounts();
                    if ($scope.IsReminder == 1) {
                        $('#act-' + ActivityGUID).show();
                    }
                    $scope.trr++;
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.UndoAddReminder = function () {
            var Url = 'reminder/delete';
            var jsonData = {
                ReminderGUID: $scope.UndoReminderData.ReminderGUID,
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    NewsFeedCtrl.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = [];
                    NewsFeedCtrl.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'].ReminderGUID = '';
                    $('[data-fixed-activityGUID="' + $scope.UndoReminderData.ActivityGUID + '"] a').removeClass('active');
                    $scope.CloseUndoPopup();
                    $scope.getRemiderCounts();
                    $scope.trr--;
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.UndoEditReminder = function () {
            Url = 'reminder/edit';
            var jsonData = {
                ActivityGUID: $scope.UndoReminderData.ActivityGUID,
                ReminderDateTime: $scope.UndoReminderData.UndoDateTime,
                Status: $scope.UndoReminderData.Status,
                ReminderGUID: $scope.UndoReminderData.ReminderGUID
            };
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    //Store temporary data for Undo purpose
                    NewsFeedCtrl.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = prepareReminderData(jsonData, 1);
                    //$("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('setDate', new Date($scope.UndoReminderData.UndoDateTime.replace(/-/gi, ' ')));
                    //$("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('refresh');
                    $scope.CloseUndoPopup();
                    $scope.getRemiderCounts();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.UndoUnarchiveReminder = function () {
            var Url = 'reminder/change_status';
            var jsonData = {
                ReminderGUID: $scope.UndoReminderData.ReminderGUID,
                Status: 'ARCHIVED'
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    if ($scope.UndoReminderData)
                    {
                        if ($scope.UndoReminderData.ActivityKey)
                        {
                            NewsFeedCtrl.activityData[$scope.UndoReminderData.ActivityKey].IsArchive = 1;
                        }
                        $scope.CloseUndoPopup();
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.saveReminder = function (ActivityGUID, Status, ReminderGUID, activityData) {
            if (ReminderGUID == '' || ReminderGUID == undefined) {
                Url = 'reminder/add';
                Action = 'add';
                ReminderGUID = '';
            } else {
                Url = 'reminder/edit';
                Action = 'edit';
            }
            /*
             Selected date from fixed time
             */
            var selectedDateTime = false;
            var FixedTimePicker = $('[data-fixed-activityGUID="' + ActivityGUID + '"] a.active');
            var IsFixedTime = $('[data-fixed-activityGUID="' + ActivityGUID + '"] a').hasClass('active');
            // var CalendarValue = 1;
            var CalendarPicker = $('[data-time-activityGUID="' + ActivityGUID + '"]');
            var AmPm = CalendarPicker.children().find(' span.selected input').val();
            var Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
            var Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();
            if (Minutes == undefined) {
                Minutes = 0;
            }
            if (Hours == 0) {
                Hours = 12;
            }
            if (Hours > 12) {
                Hours = Hours - 12;
            }
            var AmPmVar = 'AM';
            if (AmPm) {
                AmPmVar = AmPm.toUpperCase();
            }
            if (typeof $scope.selectedDate !== 'undefined' && typeof $scope.selectedDate[ActivityGUID] !== 'undefined')
            {
                selectedDateTime = $scope.selectedDate[ActivityGUID] + ' ' + Hours + ':' + Minutes + ':00 ' + AmPmVar;
            }

            if (IsFixedTime) {
                var FixedTimeType = FixedTimePicker.attr('data-fixed-type');
                if (FixedTimeType == 'Tommorrow') {
                    selectedDateTime = TomorrowDate;
                } else if (FixedTimeType == 'NextWeek') {
                    selectedDateTime = NextWeekDate;
                }
                if (!selectedDateTime) {
                    showResponseMessage('Select reminder datetime', 'alert-danger');
                    return;
                }
            } else {
                /*
                 Selected date from calendar
                 */
                var CalendarPicker = $('[data-time-activityGUID="' + ActivityGUID + '"]');
                var AmPm = CalendarPicker.children().find(' span.selected input').val();
                var Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
                var Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();
                if ($scope.selectedDate[ActivityGUID] == undefined) {
                    showResponseMessage('Select date for reminder', 'alert-danger');
                    return;
                }
                if (!((AmPm.toLowerCase() == 'am') || (AmPm.toLowerCase() == 'pm'))) {
                    showResponseMessage('Select Am Or Pm for reminder', 'alert-danger');
                    return;
                }
                if (Hours == undefined) {
                    showResponseMessage('Select time for reminder', 'alert-danger');
                    return;
                }
                if (Minutes == undefined) {
                    Minutes = 00;
                }
                if (Hours == 0) {
                    Hours = 12;
                }
                if (Hours > 12) {
                    Hours = Hours - 12;
                }
                selectedDateTime = $scope.selectedDate[ActivityGUID] + ' ' + Hours + ':' + Minutes + ':00 ' + AmPm.toUpperCase();
            }
            var jsonData = {
                ActivityGUID: ActivityGUID,
                ReminderDateTime: selectedDateTime,
                Status: Status,
                ReminderGUID: ReminderGUID
            };
            selectedDateTime = selectedDateTime.replace(/-/gi, ' ');
            //console.log(selectedDateTime);return;
            var reminderData = {
                ActivityGUID: ActivityGUID,
                ReminderDateTime: selectedDateTime,
                Status: Status,
                ReminderGUID: ReminderGUID
            };

            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    //showResponseMessage(response.Message, 'alert-success');
                    if (Action == 'add') {
                        reminderData.ReminderGUID = response.Data.ReminderGUID;
                        angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                NewsFeedCtrl.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = NewsFeedCtrl.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                            }
                        });




                        angular.forEach($scope.group_announcements, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                $scope.group_announcements[key]['ReminderData'] = prepareReminderData(reminderData);
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.group_announcements[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                            }
                        });

                        if (!$scope.UndoReminderData)
                        {
                            angular.forEach($scope.popularData, function (val, key) {
                                if (val.ActivityGUID == ActivityGUID) {
                                    reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                    $scope.popularData[key]['ReminderData'] = prepareReminderData(reminderData);
                                    //Store temporary data for Undo purpose
                                    $scope.UndoReminderData = $scope.popularData[key]['ReminderData'];
                                    $scope.UndoReminderData.ActivityKey = key;
                                    $scope.UndoReminderData.Status = response.Data.Status;
                                    $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                                }
                            });
                        }


                        if (activityData) {
                            activityData['ReminderData'] = prepareReminderData(reminderData);
                        }

                        $scope.UndoReminderData.Heading = 'Reminder Added';
                        $scope.UndoReminderData.action = 'add';
                        $scope.undoPopUp();
                        $scope.trr++;
                    }
                    if (Action == 'edit') {
                        angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = NewsFeedCtrl.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                                $scope.UndoReminderData.ReminderGUID = ReminderGUID;
                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                NewsFeedCtrl.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
                            }
                        });

                        angular.forEach($scope.group_announcements, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.group_announcements[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                                $scope.UndoReminderData.ReminderGUID = ReminderGUID;
                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                $scope.group_announcements[key]['ReminderData'] = prepareReminderData(reminderData);
                            }
                        });

                        if (!$scope.UndoReminderData)
                        {
                            angular.forEach($scope.popularData, function (val, key) {
                                if (val.ActivityGUID == ActivityGUID) {
                                    //Store temporary data for Undo purpose
                                    $scope.UndoReminderData = $scope.popularData[key]['ReminderData'];
                                    $scope.UndoReminderData.ActivityKey = key;
                                    $scope.UndoReminderData.Status = response.Data.Status;
                                    $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                                    $scope.UndoReminderData.ReminderGUID = ReminderGUID;
                                    reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                    NewsFeedCtrl.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
                                }
                            });
                        }
                        $scope.UndoReminderData.Heading = 'Reminder Edited';
                        $scope.UndoReminderData.action = 'edit';
                        $scope.undoPopUp();
                    }
                    if (Status == 'ARCHIVED') {
                        $scope.UndoReminderData.isArchive = true;
                        $('#act-' + ActivityGUID).hide();
                    }
                    setTimeout(function () {
                        setReminder();
                        $('#backReminder' + ActivityGUID).trigger('click');
                        $('#backeditReminder' + ActivityGUID).trigger('click');
                        $('body').trigger('click');
                        $("#reminderCal" + ActivityGUID).datepicker().datepicker('destroy');
                    }, 50);
                    $scope.getRemiderCounts();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.IsHourDisabled = function (ActivityGUID, Hour) {
            angular.forEach(NewsFeedCtrl.activityData, function (v, k) {
                if (v.ActivityGUID == ActivityGUID) {
                    datetime = new Date();
                    //console.log($('#reminderCal'+scope.data.ActivityGUID).datepicker('getDate'));
                    if ($('#reminderCal' + ActivityGUID).datepicker('getDate') != null) {
                        SelectedDate = moment($('#reminderCal' + ActivityGUID).datepicker('getDate')).format('YYYY-MM-DD');
                    } else {
                        if (typeof v.ReminderData == 'undefined') {
                            SelectedDate = moment(datetime).format('YYYY-MM-DD');
                        } else {
                            SelectedDate = v.ReminderData.ReminderEditDateTime;
                        }
                    }
                    var isToday = moment(SelectedDate).isSame(Date.now(), 'day');
                    CurrentHours = parseInt(moment(datetime).format('HH'));
                    if (CurrentHours > Hour && isToday) {
                        return true;
                    } else {
                        return false;
                    }
                }
            });
        }
        $scope.IsSelectedHour = function (ActivityGUID, Hour) {
            angular.forEach(NewsFeedCtrl.activityData, function (v, k) {
                if (v.ActivityGUID == ActivityGUID) {
                    datetime = new Date();
                    if ($('#reminderCal' + v.ActivityGUID).datepicker('getDate') != null) {
                        SelectedDate = moment($('#reminderCal' + v.ActivityGUID).datepicker('getDate')).format('YYYY-MM-DD');
                    } else {
                        if (typeof v.ReminderData == 'undefined') {
                            SelectedDate = moment(datetime).format('YYYY-MM-DD');
                        } else {
                            SelectedDate = v.ReminderData.ReminderEditDateTime;
                        }
                    }
                    var isToday = moment(SelectedDate).isSame(Date.now(), 'day');
                    CurrentHours = parseInt(moment(datetime).format('HH'));
                    if (typeof v.ReminderData !== 'undefined') {
                        var tempvar1 = v.ReminderData.Hour;
                        var tempvar2 = v.ReminderData.Meridian;
                    } else {
                        var tempvar1 = CurrentHours;
                        var tempvar2 = moment(datetime).format('a')
                    }
                    if (Meridian == 'am') {
                        if (tempvar1 == '12') {
                            tempvar1 = '0';
                        }
                    } else {
                        tempvar1 = parseInt(tempvar1) + 12;
                        if (tempvar1 == '24') {
                            tempvar1 = '12';
                        }
                    }
                    if (tempvar1 == Hour && (v.ReminderData && v.ReminderData.ReminderGUID && v.ReminderData.ReminderGUID != '')) {
                        return 'selected reminderSet';
                    } else if (tempvar1 == Hour) {
                        return 'selected DefaultReminderSet';
                    } else if (CurrentHours > Hour && isToday) {
                        return 'disabled';
                    } else {
                        return '';
                    }
                }
            });
        }
        $scope.changeReminderStatus = function (ActivityGUID, ReminderGUID, Status) {
            var Url = 'reminder/change_status';
            var jsonData = {
                ReminderGUID: ReminderGUID,
                Status: Status,
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            if (Status == 'DELETED' || Status == 'ARCHIVED' || Status == 'ACTIVE') {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = NewsFeedCtrl.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;

                                // blank original activity data
                                if ($scope.IsReminder != 1 || Status == 'DELETED') {
                                    NewsFeedCtrl.activityData.splice(key, 1);
                                } else {
                                    angular.forEach(NewsFeedCtrl.activityData, function (v, k) {
                                        if (v.ActivityGUID == ActivityGUID) {
                                            if (Status == 'ARCHIVED') {
                                                NewsFeedCtrl.activityData[k]['IsArchive'] = 1;
                                            } else {
                                                NewsFeedCtrl.activityData[k]['IsArchive'] = 0;
                                            }
                                        }
                                    });
                                }

                                //NewsFeedCtrl.activityData[key]['ReminderData'] = [];
                                //NewsFeedCtrl.activityData[key]['ReminderData'].ReminderGUID = '';

                                $scope.UndoReminderData.Heading = 'Reminder Removed';
                                $scope.UndoReminderData.action = 'delete';

                            }
                            if (Status == 'ARCHIVED') {
                                $scope.UndoReminderData.Heading = 'Reminder Archived';
                                $scope.UndoReminderData.action = 'archive';
                            }
                            if (Status == 'ACTIVE') {
                                $scope.UndoReminderData.Heading = 'Reminder Unarchived';
                                $scope.UndoReminderData.action = 'unarchive';
                            }
                            $scope.undoPopUp();
                            $('.tooltip').remove();
                        }
                    });


                    angular.forEach($scope.group_announcements, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            if (Status == 'DELETED' || Status == 'ARCHIVED' || Status == 'ACTIVE') {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.group_announcements[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;

                                // blank original activity data
                                if ($scope.IsReminder != 1 || Status == 'DELETED') {
                                    $scope.group_announcements.splice(key, 1);
                                } else {
                                    angular.forEach($scope.group_announcements, function (v, k) {
                                        if (v.ActivityGUID == ActivityGUID) {
                                            if (Status == 'ARCHIVED') {
                                                $scope.group_announcements[k]['IsArchive'] = 1;
                                            } else {
                                                $scope.group_announcements[k]['IsArchive'] = 0;
                                            }
                                        }
                                    });
                                }
                                //$scope.group_announcements[key]['ReminderData'] = [];
                                //$scope.group_announcements[key]['ReminderData'].ReminderGUID = '';

                                $scope.UndoReminderData.Heading = 'Reminder Removed';
                                $scope.UndoReminderData.action = 'delete';

                            }
                            if (Status == 'ARCHIVED') {
                                $scope.UndoReminderData.Heading = 'Reminder Archived';
                                $scope.UndoReminderData.action = 'archive';
                            }
                            if (Status == 'ACTIVE') {
                                $scope.UndoReminderData.Heading = 'Reminder Unarchived';
                                $scope.UndoReminderData.action = 'unarchive';
                            }
                            $scope.undoPopUp();
                            $('.tooltip').remove();
                        }
                    });
                }
            });
        }
        $scope.deleteReminder = function (ActivityGUID, ReminderGUID, Status) {
            var Url = 'reminder/delete';
            var jsonData = {
                ReminderGUID: ReminderGUID,
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            //Store temporary data for Undo purpose
                            $scope.UndoReminderData = NewsFeedCtrl.activityData[key]['ReminderData'];
                            $scope.UndoReminderData.ActivityKey = key;
                            $scope.UndoReminderData.Status = response.Data.Status;
                            $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                            // blank original activity data
                            NewsFeedCtrl.activityData[key]['ReminderData'] = [];
                            NewsFeedCtrl.activityData[key]['ReminderData'].ReminderGUID = '';
                            //console.log('hey there');
                            $scope.UndoReminderData.Heading = 'Reminder Removed';
                            $scope.UndoReminderData.action = 'delete';
                            if ($scope.IsReminder == 1) {
                                $('#act-' + ActivityGUID).hide();
                            }
                            $scope.undoPopUp();
                            $scope.getRemiderCounts();
                            NewsFeedCtrl.activityData[key]['ReminderData'] = $scope.prepareReminderData({});
                            $('.reminder-footere').show();
                            $('.tooltip').remove();
                        }
                    });

                    angular.forEach($scope.group_announcements, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            //Store temporary data for Undo purpose
                            $scope.UndoReminderData = $scope.group_announcements[key]['ReminderData'];
                            $scope.UndoReminderData.ActivityKey = key;
                            $scope.UndoReminderData.Status = response.Data.Status;
                            $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                            // blank original activity data
                            $scope.group_announcements[key]['ReminderData'] = [];
                            $scope.group_announcements[key]['ReminderData'].ReminderGUID = '';
                            //console.log('hey there');
                            $scope.UndoReminderData.Heading = 'Reminder Removed';
                            $scope.UndoReminderData.action = 'delete';
                            if ($scope.IsReminder == 1) {
                                $('#act-' + ActivityGUID).hide();
                            }
                            $scope.undoPopUp();
                            $scope.getRemiderCounts();
                            $('.tooltip').remove();
                        }
                    });
                }
            });
        }
        $scope.undoPopUp = function () {
            $('#undoPopUp').remove();
            /*html = '<div class="reminder-remove fadeInUp" id="undoPopUp">';
             html += '<span class="pull-left">' + $scope.UndoReminderData.Heading + '.</span>';
             html += '<span class="pull-right">';
             html += '<a href="javascript:void(0)" title="" ng-click="undoReminder()">Undo</a>';
             html += '<i class="icon-n-close" ng-click="CloseUndoPopup()"></i>';
             html += '</span>';
             html += '</div>';*/

            html = '<div class="alertify fadeInUp" id="undoPopUp">';
            html += '<a onclick="$(this).parent(\'div\').hide();" class="icon">';
            html += '<i class="ficon-cross"></i>';
            html += '</a>';
            html += '<a class="link-text" ng-click="CloseUndoPopup()">Undo</a>';
            html += '<span class="text">' + $scope.UndoReminderData.Heading + '.</span>';
            html += '</div>';


            var $el = angular.element(html);
            $('body').append($el).fadeIn(200);
            $compile($el)($scope);
            setTimeout(function () {
                $('#undoPopUp').fadeOut();
                if ($scope.UndoReminderData.isArchive && $scope.UndoReminderData.Status != 'ARCHIVED') {
                    NewsFeedCtrl.activityData.splice($scope.UndoReminderData.Key, 1);
                }
                $scope.UndoReminderData = {};
            }, 5000);
        }
        $scope.selectedDatetime = function (name) {
            $(document).on('change', 'input[name="' + name + '"]', function () {
                if (!$(this).hasClass('selected')) {
                    $('input[name="' + name + '"]').parent().removeClass('selected');
                    $(this).parent().toggleClass('selected');
                }
                if (name == 'HH') {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.IsTodayReminder == '1') {
                            var hours = val.DateTimeTZ.getHours();
                            var minutes = val.DateTimeTZ.getMinutes();
                            if (hours > 12) {
                                hours = hours - 12;
                            }

                            if ($('ul.hours li span.selected input').val() == hours) {
                                $('ul.minutes input').each(function (k, v) {
                                    if ($(this).val() < minutes) {
                                        $('ul.minutes li span').removeClass('selected');
                                        $(this).attr('disabled', 'disabled');
                                        $(this).parent('span').addClass('disabled');
                                    }
                                });
                            } else {
                                $('ul.minutes input').removeAttr('disabled');
                                $('ul.minutes span').removeClass('disabled');
                            }
                        }
                    });
                }
            });
        }


        $scope.viewAllComntEmitAnnouncement = function (i, ActivityGUID, filter) { //    Call above webservice here for View All Comments

            var id = $('[data-guid="act-' + ActivityGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            var reqData = {
                EntityGUID: ActivityGUID,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
            };
            if (typeof filter !== 'undefined')
            {
                reqData['Filter'] = filter;
            }
            $("#cmt_loader_" + ActivityGUID).show();
            WallService.CallApi(reqData, 'activity/getAllComments').then(function (response) {
                if (response.ResponseCode === 200) {
                    var tempComntData = response.Data;
                    if ($scope.group_announcements_single && $scope.group_announcements_single[0] && $scope.group_announcements_single[0].Comments) {
                        $scope.group_announcements_single[0].Comments = tempComntData;
                        $scope.group_announcements_single[0].viewStats = 0;
                    }
                }
            });
        }


        // View All Comments
        $scope.viewAllComntEmit = function (i, ActivityGUID, filter) { //    Call above webservice here for View All Comments
            console.log('i', i);
            var id = $('[data-guid="act-' + ActivityGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            var reqData = {
                EntityGUID: ActivityGUID,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
            };
            if (typeof filter !== 'undefined')
            {
                reqData['Filter'] = filter;
            }
            $("#cmt_loader_" + ActivityGUID).show();
            WallService.CallApi(reqData, 'activity/getAllComments').then(function (response) {
                if (response.ResponseCode === 200) {
                    var tempComntData = response.Data;
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID)
                        {
                            NewsFeedCtrl.activityData[key].Comments = tempComntData;
                            NewsFeedCtrl.activityData[i].viewStats = 0;
                        }
                    });
                    if (IsNewsFeed == '1')
                    {
                        var tempComntData = response.Data;
                        if ($scope.popularData && $scope.popularData[i] && $scope.popularData[i].Comments) {
                            //                      if( NewsFeedCtrl.activityData[i].Comments.length > 0 )
                            $scope.popularData[i].Comments = tempComntData;
                            $scope.popularData[i].viewStats = 0;
                        }
                    }
                }
            });
        }

        $scope.likeMessage = '';
        $scope.totalLikes = 0;
        $scope.LastLikeActivityGUID = '';
        $scope.LastLikeEntityType = '';
        $scope.likeDetails = [];
        $scope.isLikeDetailsProcessing = false;

        function likeDetails(EntityGUID, EntityType, fn) {
            //showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'likeDetailsMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/likeDetailsMdl.js' + $scope.app_version,
                templateUrl: $scope.partialURL + 'toggle_like.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'like_details_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('likeDetailsMdlInit', {
                        params: params,
                        wallScope: $scope,
                        EntityGUID: EntityGUID,
                        EntityType: EntityType,
                        fn: fn
                    });
                },
            });
        }
        $scope.likeDetailsEmit = function (EntityGUID, EntityType) {
            likeDetails(EntityGUID, EntityType, 'likeDetailsEmit');
        };

        function seenDetails(EntityGUID, EntityType, fn) {
            lazyLoadCS.loadModule({
                moduleName: 'seenDetailsMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/seenDetailsMdl.js' + $scope.app_version,
                templateUrl: $scope.partialURL + 'toggle_seen.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'seen_details_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('seenDetailsMdlInit', {
                        params: params,
                        wallScope: $scope,
                        EntityGUID: EntityGUID,
                        EntityType: EntityType,
                        fn: fn
                    });
                },
            });
        }
        $scope.seenMessage = '';
        $scope.totalSeen = 0;
        $scope.LastSeenActivityGUID = '';
        $scope.LastSeenEntityType = '';
        $scope.seenDetails = [];
        $scope.isSeenDetailsProcessing = false;

        $scope.seenDetailsEmit = function (EntityGUID, EntityType) {
            seenDetails(EntityGUID, EntityType, 'seenDetailsEmit');
        };

        $scope.participateMessage = '';
        $scope.totalParticipate = 0;
        $scope.LastParticipateActivityGUID = '';
        $scope.LastParticipateEntityType = '';
        $scope.participateDetails = [];
        $scope.isParticipateDetailsProcessing = false;

        $scope.participateDetailsEmit = function (EntityGUID, EntityType) {
            if ((($scope.totalParticipate === 0) || ($scope.participateDetails.length < $scope.totalParticipate)) && !$scope.isParticipateDetailsProcessing) {
                $scope.isParticipateDetailsProcessing = true;
                var participatePageNo = $('#ParticipatePageNo').val(),
                        jsonData = {};
                if ((participatePageNo == 1) && EntityGUID) {
                    $scope.LastParticipateActivityGUID = EntityGUID;
                    $scope.LastParticipateEntityType = EntityType;
                }
                jsonData = {
                    ActivityID: $scope.LastParticipateActivityGUID,
                    PageNo: participatePageNo,
                    PageSize: 8
                };
                WallService.CallApi(jsonData, 'contest/participant_list').then(function (response) {
                    if (response.ResponseCode == 200) {
                        if (!$('#totalParticipate').is(':visible')) {
                            $('#totalParticipate').modal('show');
                        }
                        if (response.Data.length > 0) {
                            if ($scope.participateDetails.length === 0) {
                                $scope.participateDetails = angular.copy(response.Data);
                            } else {
                                $scope.participateDetails = $scope.participateDetails.concat(response.Data);
                            }
                            $scope.totalParticipate = parseInt(response.TotalRecords);
                            $scope.participateMessage = '';
                            $('#ParticipatePageNo').val(parseInt(participatePageNo) + 1);
                        } else if ($scope.participateDetails.length === 0) {
                            $scope.participateDetails = [];
                            $scope.totalParticipate = 0;
                            $scope.participateMessage = 'No participants yet.';
                        }
                    }
                    $scope.isParticipateDetailsProcessing = false;
                });
            }
            if (parseInt($('#ParticipatePageNo').val()) == 1) {
                $('#totalParticipate').on('hide.bs.modal', function () {
                    $scope.participateDetails = [];
                    $scope.totalParticipate = 0;
                    $scope.participateMessage = 'No participants yet.';
                    $('#ParticipatePageNo').val(1);
                });
            }
        };

        $scope.privacyEmit = function (ActivityGuID, privacy, current_privacy) {
            if (current_privacy == privacy)
            {
                return false;
            }
            showConfirmBox("Wait a minute!", "Once anyone comments on this post, you will not be able to change privacy of post to more open. <br><br> Are you sure you want to change privacy for this post? ", function (e) {
                if (e)
                {
                    jsonData = {
                        ActivityGuID: ActivityGuID,
                        Visibility: privacy
                    };
                    WallService.CallApi(jsonData, 'activity/privacyChange').then(function (response) {
                        if (response.ResponseCode == 200) {
                            $(NewsFeedCtrl.activityData).each(function (key, value) {
                                if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGuID) {
                                    NewsFeedCtrl.activityData[key].Visibility = privacy;
                                }
                            });
                        }
                    });
                }
            });
        }
        $scope.likeStatusEmit = function (ActivityGUID, data) {

            var id = $('[data-guid="act-' + ActivityGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            element.attr('data-module-id', data.ModuleID);
            element.attr('data-module-entityid', data.ModuleEntityGUID);
            $('#' + id + ' .show-pic').attr('src', image_server_path + 'upload/profile/36x36/' + data.ProfilePicture);
            $('#' + id + ' .current-profile-pic').attr('src', image_server_path + 'upload/profile/36x36/' + data.ProfilePicture);
            jsonData = {
                PostAsModuleID: data.ModuleID,
                PostAsModuleEntityGUID: data.ModuleEntityGUID,
                ActivityGUID: ActivityGUID
            };
            WallService.CallApi(jsonData, 'activity/checkLikeStatus').then(function (response) {
                $(NewsFeedCtrl.activityData).each(function (k, v) {
                    if (NewsFeedCtrl.activityData[k].ActivityGUID == ActivityGUID) {
                        NewsFeedCtrl.activityData[k].viewStats = 1;
                        NewsFeedCtrl.activityData[k].IsLike = response.Data.IsLike;
                        NewsFeedCtrl.activityData[k].Comments = response.Data.Comments;
                    }
                });
            });
        }
        $scope.stickyEmit = function (ActivityGUID) {
            reqData = {
                EntityGUID: ActivityGUID
            };
            WallService.CallApi(reqData, 'activity/toggle_sticky').then(function (response) {
                if (response.ResponseCode == 200) {
                    var append = false;
                    $(NewsFeedCtrl.activityData).each(function (key, val) {
                        if (ActivityGUID == NewsFeedCtrl.activityData[key].ActivityGUID) {
                            if (NewsFeedCtrl.activityData[key].IsSticky == 0) {
                                NewsFeedCtrl.activityData[key].IsSticky = 1;
                                var newD = NewsFeedCtrl.activityData[key];
                                NewsFeedCtrl.activityData.splice(key, 1);
                                NewsFeedCtrl.activityData.splice(0, 0, newD);
                                return false;
                            } else {
                                NewsFeedCtrl.activityData[key].IsSticky = 0;
                                var newD = NewsFeedCtrl.activityData[key];
                                if (NewsFeedCtrl.activityData.length > 1) {
                                    NewsFeedCtrl.activityData.splice(key, 1);
                                    $(NewsFeedCtrl.activityData).each(function (k, v) {
                                        if (NewsFeedCtrl.activityData[k].IsSticky == 0) {
                                            NewsFeedCtrl.activityData.splice(k, 0, newD);
                                            return false;
                                        }
                                    });
                                    if (!append) {
                                        NewsFeedCtrl.activityData.splice(NewsFeedCtrl.activityData.length, 0, newD);
                                    }
                                }
                                return false;
                            }
                        }
                    });
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.get_users_for_invite = function (PollGUID, keyup)
        {
            angular.element('#PollCtrl').scope().get_users_for_invite(PollGUID, keyup);
        }
        $scope.get_groups_for_invite = function (PollGUID, keyup)
        {
            angular.element('#PollCtrl').scope().get_groups_for_invite(PollGUID, keyup);
        }
        $scope.invite_entity_for_polls = function ()
        {
            angular.element('#PollCtrl').scope().invite_entity_for_polls();
        }
        $scope.get_entities_for_invite = function (PollGUID)
        {
            angular.element('#PollCtrl').scope().get_entities_for_invite(PollGUID);
        }

        $scope.set_entity_info = function (entity)
        {
            $scope.PostAsModuleProfilePicture = entity.ProfilePicture;
            $scope.PostAsModuleID = entity.ModuleID;
            $scope.PostAsModuleEntityGUID = entity.ModuleEntityGUID;
            $scope.PostAsModuleName = entity.Name;
            $scope.FromModuleID = entity.ModuleID;
            $scope.FromModuleEntityGUID = entity.ModuleEntityGUID;
            $('.tooltip').remove();
        }

        $scope.ExpiryDateTime = "";
        $scope.expire_duration_text = "Never";
        $scope.group_and_users_tags = [];

        $scope.set_expire_date = function (duration)
        {
            $scope.ExpiryDateTime = duration;
            if (duration > 1)
            {
                DayString = ' Days';
            } else
            {
                DayString = ' Day';
            }
            if (duration == '')
            {
                DayString = 'Never';
            }
            if (duration == -1)
            {
                DayString = 'Expired';
            }
            $scope.expire_duration_text = duration + DayString;
        }

        $scope.is_busy = false;
        $scope.set_expire_date_polls = function ($event, Duration, ActivityGUID, PollGUID)
        {
            if ($scope.is_busy == true) {
                return false;
            }
            $scope.is_busy = true;
            $scope.getModuleID();
            req_data = {};
            req_data.PollGUID = PollGUID;
            req_data.ExpireDuration = Duration;
            WallService.CallApi(req_data, 'polls/edit_expiry').then(function (response)
            {
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Poll updated successfully.', 'alert-success');
                    $('#' + PollGUID + ' .poll-expiry').addClass('hide');
                    $(NewsFeedCtrl.activityData).each(function (key, value)
                    {
                        if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID)
                        {
                            PollScope = angular.element(document.getElementById('p-' + NewsFeedCtrl.activityData[key].PollData[0].PollGUID)).scope();
                            //NewsFeedCtrl.activityData[key].PollData[0].IsExpired = 0;
                            if (req_data.ExpireDuration == -1)
                            {
                                NewsFeedCtrl.activityData[key].PollData[0].IsExpired = 1;
                            } else
                            {
                                NewsFeedCtrl.activityData[key].PollData[0].ExpiryDateTime = response.ExpireDatetime;
                            }
                        }
                    });
                }
                $scope.is_busy = false;
            });
        }
        $scope.ShowVoteOptionAdminEmit = function (ActivityGUID)
        {
            $(NewsFeedCtrl.activityData).each(function (key, value)
            {
                if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID)
                {
                    PollScope = angular.element(document.getElementById('p-' + NewsFeedCtrl.activityData[key].PollData[0].PollGUID)).scope();
                    if (!NewsFeedCtrl.activityData[key].PollData[0].ShowVoteOptionToAdmin)
                    {
                        NewsFeedCtrl.activityData[key].PollData[0].ShowVoteOptionToAdmin = 1;
                    } else
                    {
                        NewsFeedCtrl.activityData[key].PollData[0].ShowVoteOptionToAdmin = 0;
                    }
                }
            });
        }
        $scope.seeMoreLink = function (ActivityGUID) {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == ActivityGUID)
                {
                    NewsFeedCtrl.activityData[key].ShowMoreHide = '1';
                    NewsFeedCtrl.activityData[key].showAllLinks = '1';
                }
            });
        }
        $scope.vote = function (event, ActivityGUID, PollGUID) {

            $scope.getModuleID();
            req_data = {};
            req_data.ModuleID = $scope.FromModuleID;
            req_data.ModuleEntityGUID = $scope.FromModuleEntityGUID;
            var OptionGUID = $("#" + PollGUID + " input[type='radio'][name='vote']:checked").val();
            req_data.OptionGUID = OptionGUID;
            if (req_data.OptionGUID == '' || req_data.OptionGUID == undefined)
            {
                showResponseMessage('Please choose an option to vote', 'alert-danger');
                return false;
            }

            WallService.CallApi(req_data, 'polls/vote').then(function (response)
            {
                if (response.ResponseCode == 200)
                {
                    $(NewsFeedCtrl.activityData).each(function (key, value)
                    {
                        if (NewsFeedCtrl.activityData[key].ActivityGUID == ActivityGUID)
                        {
                            if (typeof NewsFeedCtrl.activityData[key].PollData[0] !== 'undefined')
                            {
                                $(NewsFeedCtrl.activityData[key].PollData[0].Options).each(function (k, v)
                                {
                                    if (v.OptionGUID == OptionGUID)
                                    {
                                        NewsFeedCtrl.activityData[key].PollData[0].Options[k].NoOfVotes = response.Data.Count.OptionVoted;
                                    }
                                    NewsFeedCtrl.activityData[key].PollData[0].Options[k].pollTotalVotes = response.Data.Count.TotalVotes;
                                });
                                PollScope = angular.element(document.getElementById('p-' + NewsFeedCtrl.activityData[key].PollData[0].PollGUID)).scope();
                                PollScope.createChart(NewsFeedCtrl.activityData[key].PollData[0].PollGUID, NewsFeedCtrl.activityData[key].PollData[0].Options);
                                NewsFeedCtrl.activityData[key].PollData[0].IsVoted = 1;
                                if (NewsFeedCtrl.activityData[key].PollData[0].IsOwner == 1)
                                {
                                    NewsFeedCtrl.activityData[key].PollData[0].ShowVoteOptionToAdmin = 0;
                                }
                            }
                        }
                    });
                    //Update Sidebar poll
                    if ($('#pollscope_' + PollGUID).length > 0)
                    {
                        PollScope = angular.element(document.getElementById('pollscope_' + PollGUID)).scope();
                        if (PollScope.poll_detail)
                        {
                            $(PollScope.poll_detail.PollData[0].Options).each(function (k, v)
                            {
                                if (v.OptionGUID == OptionGUID)
                                {
                                    PollScope.poll_detail.PollData[0].Options[k].NoOfVotes = response.Data.Count.OptionVoted;
                                }
                                PollScope.poll_detail.PollData[0].Options[k].pollTotalVotes = response.Data.Count.TotalVotes;
                            });
                            PollScope.poll_detail.PollData[0].IsVoted = 1;
                        }
                    }
                    showResponseMessage('Your vote has been placed', 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.VotesDetails = [];
        $scope.VoteDetailsEmit = function (PollGUID, init) {
            poll_scope = angular.element(document.getElementById('PollCtrl')).scope();
            if (init == 'init') {
                poll_scope.PollGUID = '';
                poll_scope.VotesDetails = [];
                poll_scope.VotesPageNo = 1;
                poll_scope.VotesPageSize = 8;
                $('body').addClass('loading');
            }

            if ($scope.vote_scroll_busy)
                return;

            $scope.vote_scroll_busy = true;
            poll_scope.PollGUID = PollGUID;
            $scope.PollGUID = PollGUID;

            reqData = {
                PollGUID: $scope.PollGUID,
                VisitorModuleID: $scope.FromModuleID,
                VisitorModuleEntityGUID: $scope.FromModuleEntityGUID,
                PageNo: $scope.VotesPageNo,
                PageSize: $scope.VotesPageSize
            };

            WallService.CallApi(reqData, 'polls/voters_list').then(function (response) {

                if (response.ResponseCode == 200)
                {
                    $scope.totalVotes = response.TotalRecords;
                    angular.forEach(response.Data, function (val, key) {
                        var append = true;
                        angular.forEach($scope.VotesDetails, function (v, k) {
                            if (v.ModuleID == val.ModuleID && v.ModuleEntityGUID == val.ModuleEntityGUID)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            $scope.VotesDetails.push(val);
                        }
                    });

                    $scope.IsVotesLoadMore = 0;
                    if ($scope.VotesDetails.length < $scope.totalVotes) {
                        $scope.IsVotesLoadMore = 1;
                        $scope.ScrollVoteList();
                    }
                    $scope.vote_scroll_busy = false;
                    if ($('#PollCtrl').length > 0)
                    {
                        angular.element(document.getElementById('PollCtrl')).scope().VotesDetails = $scope.VotesDetails;
                        angular.element(document.getElementById('PollCtrl')).scope().totalVotes = $scope.totalVotes;
                        angular.element(document.getElementById('PollCtrl')).scope().IsVotesLoadMore = $scope.IsVotesLoadMore;
                        /*$scope.$apply();*/
                    }

                    if ($('#PollCtrl2').length > 0)
                    {
                        angular.element(document.getElementById('PollCtrl2')).scope().VotesDetails = $scope.VotesDetails;
                        angular.element(document.getElementById('PollCtrl2')).scope().totalVotes = $scope.totalVotes;
                        angular.element(document.getElementById('PollCtrl2')).scope().IsVotesLoadMore = $scope.IsVotesLoadMore;
                    }
                }

                if (init == 'init') {
                    $('#votesModal').modal('show');
                    $('body').removeClass('loading');
                }
            });
        }

        $scope.update_last_date = function (type)
        {
            var reqData = {Type: type};
            WallService.CallApi(reqData, 'users/update_last_date').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    if (type == 'Questions')
                    {
                        $scope.profile_field_questions = [];
                    }
                }
            });
        };

        $scope.getFeedSetting = function () {
            var Url = 'privacy/news_feed_setting_details';
            var jsonData = {};
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.newsFeedSetting = response.Data;
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.saveFeedSetting = function (k, newVal) {
            var Url = 'privacy/save_news_feed_setting';
            var settingArr = {};
            var postArr = [];
            angular.forEach($scope.newsFeedSetting, function (val, key) {
                if (k == key) {
                    if (val == '1') {
                        obj = {
                            "Key": key,
                            "Value": "0"
                        };
                    } else {
                        obj = {
                            "Key": key,
                            "Value": "1"
                        };
                    }
                } else {
                    obj = {
                        "Key": key,
                        "Value": val
                    };
                }
                $scope.newsFeedSetting[key] = obj.Value;
                postArr.push(obj);
            });
            var jsonData = {
                news_feed_setting: postArr
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    if (k !== 'rm') {
                        $scope.getFilteredWall();
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.PostType = 0;
        $scope.PostTypeLabel = 'All Posts';
        $scope.filterPostType = function (post_type)
        {
            $scope.Filter.ShowMe = [];
            if (typeof post_type !== 'undefined')
            {
                post_type['IsSelect'] = true;
            }
            $scope.Filter.ShowMe.push(post_type);
            if (post_type !== $scope.PostType)
            {
                $scope.Filter.IsSetFilter = true;
                if (typeof post_type !== 'undefined')
                {
                    $scope.PostType = post_type.Value;
                    $scope.PostTypeLabel = post_type.Label;
                    $scope.PostTypeName = post_type.Label;
                }
                setTimeout(function () {
                    $scope.getFilteredWall();
                }, 500);
            }
        }

        $scope.callFilterPostType = function ()
        {
            if (window.location.hash) {
                var hash_val = window.location.hash;
                var post_type = hash_val.substr(hash_val.length - 1);
                var p_type = {'Value': 0, 'Label': 'All Posts'};
                if (post_type == 1)
                {
                    p_type = {'Value': 1, 'Label': 'Discussions'};
                }
                if (post_type == 2)
                {
                    p_type = {'Value': 2, 'Label': 'Questions'};
                }
                if (post_type == 4)
                {
                    p_type = {'Value': 4, 'Label': 'Articles'};
                }
                if (post_type == 7)
                {
                    p_type = {'Value': 7, 'Label': 'Announcements'};
                }
            }
            $scope.filterPostType(p_type);
        }

        $scope.imageUpload = function (files) {
            //console.log('files');
        }


        $scope.settingEnabled = function (settingName) {
            if (settingName != undefined && settingName != 0) {
                return true;
            } else {
                return false;
            }
        }

        $scope.pollLoader = false;
        $scope.CreatePoll = function ()
        {
            showButtonLoader('ShareButton');
            var PostContent = $('#PostContent').val().trim();

            if ($('#post_type').val()) {
                var posttypeid = $('#post_type').val();
                $('#post_type_id').val(posttypeid);
            }
            if (PostContent == '') {
                showResponseMessage('Please write poll description.', 'alert-danger');
                $('#PollDescription').val('');
                hideButtonLoader('ShareButton');
                return false;
            }
            if (PostContent.length > 2000) {
                showResponseMessage('Poll description maximum 2000 characters.', 'alert-danger');
                hideButtonLoader('ShareButton');
                return false;
            }

            var optionDetail = [];
            $('.choice-listing > li').each(function ()
            {
                optionDesc = $(this).find('.form-control').val().trim();

                MediaDesc = [];
                $(this).find('.upload-view').each(function () {
                    if ($(this).find('.img-poll-full').attr('media_guid') !== "" && $(this).find('.img-poll-full').attr('media_guid') != undefined)
                    {
                        MediaDesc.push({MediaGUID: $(this).find('.img-poll-full').attr('media_guid')});
                    }
                });
                if ((optionDesc != '' && optionDesc != undefined))
                {
                    if (optionDesc.length > 2000) {
                        showResponseMessage('Choice description maximum 500 characters.', 'alert-danger');
                        hideButtonLoader('ShareButton');
                        return false;
                    }
                    optionDetail.push({OptionDescription: optionDesc, Media: MediaDesc});
                }
                $(this).find('.upload-view').remove();
            });
            if (optionDetail.length < 2)
            {
                hideButtonLoader('ShareButton');
                showResponseMessage('Please write at least two options.', 'alert-danger');
                return false;
            }

            var jsonData = {};
            var media = [];
            var i = 0;
            if ($('.poll_cover').length > 0)
            {
                media.push({MediaGUID: $('.media-holder img').attr('mediaguid')});
            }
            var formData = $("#wallpostform").serializeArray();
            var m1 = 1;
            var m2 = 2;
            $.each(formData, function () {
                if (jsonData[this.name]) {
                    if (!jsonData[this.name].push) {
                        jsonData[this.name] = [jsonData[this.name]];
                    }
                    jsonData[this.name].push(this.value || '');
                } else {
                    if (this.name == 'MediaGUID' || this.name == 'MediaGUID[]') {
                    } else if (this.name == 'MediaCaption' || this.name == 'MediaCaption[]') {
                    } else {
                        jsonData[this.name] = this.value || '';
                    }
                }
            });
            /*var PContent = $.trim($('#wallpostform .textntags-beautifier div').html());
             if (PContent != "")
             {
             PContent = $.trim(filterPContent(PContent, 'Poll'));
             }
             
             $('#wallpostform .textntags-beautifier div').html('');*/

            PContent = $('#PostContent').val();

            jsonData['Description'] = PContent;
            jsonData['Media'] = media;

            jsonData['ExpiryDateTime'] = $scope.ExpiryDateTime;
            if ($scope.ExpiryDateTime == undefined)
            {
                jsonData['ExpiryDateTime'] = '';
            }

            var commentsSettingsVal = ($('#dCommenting').prop('checked')) ? 0 : 1;
            $('#comments_settings').val(commentsSettingsVal);


            jsonData['Commentable'] = $('#comments_settings').val();
            jsonData['Visibility'] = $('#visible_for').val();
            jsonData['IsAnonymous'] = $scope.is_anonymous;
            jsonData['Options'] = optionDetail;
            jsonData['PostAsModuleID'] = $scope.FromModuleID;
            jsonData['PostAsModuleEntityGUID'] = $scope.FromModuleEntityGUID;
            jsonData['PollFor'] = $scope.group_and_users_tags;
            // $('#PollDescription,#PostContent').textntags('reset');
            $scope.pollLoader = true;
            WallService.CallApi(jsonData, 'polls/create').then(function (response) {
                //$scope.resetFormPost();
                $('#PollDescription').val('');
                $scope.Description = "";
                $scope.ExpiryDateTime = "";
                $('.choice-listing > li .form-control').val('');
                $('.dummy_control').remove();
                $('.add-more-link').show();
                PollScope = angular.element('#PollCtrl').scope();
                PollScope.poll_desc_count = 2;
                PollScope.add_more = true;
                PollScope.is_privacy = true;
                hideButtonLoader('ShareButton');
                $('.poll-post').removeClass('active');
                $('.choice-listing .upload-view').html("");
                $('#Wallpostform  .upload-listing').html("");
                $('#wallpostform .textntags-beautifier div').html('');
                $('#toggleCommentable').addClass('on');
                $('#comments_settings').val(1);
                $('#toggleCommentable').attr('title', 'Turn Comment Off');

                /*if ($('#PollPrivacy').length > 0)
                 {
                 $('#PollPrivacy').html("<i class='icon-every'></i><span class='caret'></span>");
                 }*/

                $('#posterror').text('');
                $('#noOfCharPostContent').text('0');
                $('#wallphotocontainer ul').html('');
                $('#comments_settings').val(1);
                $('#wallpostform .textntags-beautifier div').html('');
                $('.media-item').remove();
                $('.upload-listing').hide();
                $('.same-caption').val('');
                $('.same-caption').hide();
                $('.mc').val('');
                $('.wall-post .upload-media').hide();
                $('.capt-num').html();
                $('.all-con').hide();
                $('#addMedia,#addVideo').show();
                $('.all-con').hide();
                if (response.ResponseCode == 200) {
                    response.Data[0]['append'] = 1;
                    response.Data[0]['Settings'] = Settings.getSettings();
                    response.Data[0]['ImageServerPath'] = Settings.getImageServerPath();
                    response.Data[0]['SiteURL'] = Settings.getSiteUrl();
                    response.Data[0]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                    response.Data[0]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                    response.Data[0]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                    response.Data[0]['ReminderData'] = $scope.setReminderData();
                    $scope.is_anonymous = 0;
                    $scope.ExpiryDateTime = '';
                    $scope.expire_duration_text = 'Never';
                    $scope.FromModuleID = '3';
                    $scope.FromModuleEntityGUID = LoggedInUserGUID;
                    $scope.PostAsModuleProfilePicture = profile_picture;
                    $scope.group_and_users_tags = [];
                    $scope.tagsto = [];
                    if (NewsFeedCtrl.activityData.length > 0) {
                        $(NewsFeedCtrl.activityData).each(function (k, v) {
                            if (NewsFeedCtrl.activityData[k].IsSticky == 0) {
                                NewsFeedCtrl.activityData.splice(k, 0, response.Data[0]);

                                return false;
                            }
                        });
                    } else {
                        NewsFeedCtrl.activityData.push(response.Data[0]);
                    }

                    $scope.tr++;

                    if (!$scope.IsActiveFilter) {
                        setTimeout(function () {
                            if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                $('#FilterButton').show();
                            } else {
                                $('#FilterButton').hide();
                            }
                        }, 1000);
                    }

                    $('#visible_for').val(1);

                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                hideButtonLoader('ShareButton');

                $('.upload-view').remove();
                $scope.PostAsModuleID = '3';

                $scope.pollLoader = false;
            });
            setTimeout(function () {
                $('.wallloader').hide();
                showHidePhotoVideoIcon();
            }, 500);
        }

        $scope.admin_messages = [];
        $scope.get_admin_messages = function (EntityType)
        {
            if (LoginSessionKey == '')
                return false;
            var reqData = {EntityType: EntityType, SortBy: 'EntityType', OrderBy: 'ASC'};
            WallService.CallApi(reqData, 'blog/list').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.admin_messages = response.Data;
                }
            });
        }

        $scope.profile_field_questions = [];
        $scope.get_profile_field_questions = function ()
        {
            var reqData = {};
            WallService.CallApi(reqData, 'users/get_profile_field_questions').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.profile_field_questions = response.Data;
                }
            });
        }

        $scope.skipQuestion = function (field_key)
        {
            angular.forEach($scope.profile_field_questions, function (val, key) {
                if (val.FieldKey == field_key)
                {
                    $scope.profile_field_questions.splice(key, 1);
                }
            });
            $scope.questions = {};
        }

        $scope.RelationshipOptions = [{val: '1', Relation: 'Single'}, {val: '2', Relation: 'In a relationship'}, {val: '3', Relation: 'Engaged'}, {val: '4', Relation: 'Married'}, {val: '5', Relation: 'Its complicated'}, {val: '6', Relation: 'Separated'}, {val: '7', Relation: 'Divorced'}];

        var url_regex = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/;

        $scope.questions = {};
        $scope.saveAnswer = function (field_key)
        {
            $('[data-error="hasError"]').removeClass('hasError');
            $scope.questions.Type = field_key;
            var reqData = $scope.questions;
            var callapi = true;
            if (field_key == 'Education' || field_key == 'WorkExperience')
            {
                var start_year = $scope.questions['StartYear'];
                var end_year = $scope.questions['EndYear'];
                if (start_year > end_year)
                {
                    if (field_key !== 'WorkExperience' || !$scope.questions['CurrentlyWorkingHere'])
                    {
                        showResponseMessage('Please insert valid date.', 'alert-danger');
                        callapi = false;
                    }
                }
            }
            angular.forEach($scope.questions, function (val, key) {
                if (field_key == 'SocialProfile')
                {
                    if (val !== '' && !url_regex.test(val) && key !== 'Type')
                    {
                        showResponseMessage('Please insert valid URL', 'alert-danger');
                        callapi = false;
                    }
                    if ($scope.questions.FB == "" && $scope.questions.Twitter == "" && $scope.questions.GooglePlus == "" && $scope.questions.LinkedIn == "")
                    {
                        $('[name="' + key + '"]').parent('[data-error="hasError"]').addClass('hasError');
                        showResponseMessage('This field is mandatory for saving information.', 'alert-danger');
                        callapi = false;
                    }
                } else
                {
                    if (val == '' && key !== 'Location' && key !== 'HomeLocation')
                    {
                        if (key == 'EndMonth' || key == 'EndYear')
                        {
                            if ($scope.questions.CurrentlyWorkingHere)
                            {
                                val = '1';
                            }
                        }
                        if (val == '')
                        {
                            $('[name="' + key + '"]').parent('[data-error="hasError"]').addClass('hasError');
                            showResponseMessage('This field is mandatory for saving information.', 'alert-danger');
                            callapi = false;
                        }
                    }
                }
            });
            if (callapi)
            {
                WallService.CallApi(reqData, 'users/save_user_info').then(function (response) {
                    if (response.ResponseCode == 200)
                    {
                        /*if (field_key == 'Username')
                         {
                         window.location.reload();
                         }*/
                        $scope.skipQuestion(field_key);
                    } else
                    {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                });
            }

        }

        $scope.showWelcomeMessage = function ()
        {
            var reqData = {ShowWelcomeMessage: '0'};
            WallService.CallApi(reqData, 'users/save_user_info').then(function (response) {

            });
        }

        $scope.remove_admin_message = function (BlogGUID)
        {
            angular.forEach($scope.admin_messages, function (val, key) {
                if (val.BlogGUID == BlogGUID)
                {
                    $scope.admin_messages.splice(key, 1);
                    $scope.showWelcomeMessage();
                }
            });
        }

        if (IsNewsFeed == '1')
        {
            setTimeout(function () {
                if ($scope.ShowWelcomeMessage == '1')
                {
                    var EntityType = [3, 4];
                    $scope.get_admin_messages(EntityType);
                    $scope.showWelcomeMessage();
                } else
                {
                    var EntityType = [4];
                    $scope.get_admin_messages(EntityType);
                }
            }, 10000);
        }

        $scope.updateLocationDetails = function (data, id)
        {
            $scope.questions.CountryCode = data.geobytesinternet;
            $scope.questions.Country = data.geobytescountry;
            $scope.questions.State = data.geobytesregion;
            $scope.questions.StateCode = data.geobytescode;
            $scope.questions.City = data.geobytescity;
        }

        $scope.InitRelationTo = function ()
        {
            if ($scope.RelationWithInput != '')
            {
                $scope.showRelationOption = 1;
                $('#RelationTo').val($scope.RelationWithInput);
            }
            $('#RelationTo').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=',
                        data: {term: request.term},
                        dataType: "json",
                        headers: {'Accept-Language': accept_language},
                        success: function (data)
                        {
                            if (data.ResponseCode == 502)
                            {
                                data.Data = {'0': {"FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term}};
                            }

                            if (data.Data.length <= 0)
                            {
                                data.Data = {'0': {"FirstName": "No result found.", "LastName": "", "value": request.term}};
                            }
                            $scope.RelationWithGUID = '';
                            response(data.Data);
                        }
                    });
                },
                select: function (event, ui) {
                    if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.')
                    {
                        $scope.RelationWithGUID = ui.item.UserGUID;

                    }
                }
            }).data("ui-autocomplete")._renderItem = function (ul, item) {
                item.value = item.label = item.FirstName + " " + item.LastName;
                item.id = item.UserGUID;
                return $("<li>")
                        .data("item.autocomplete", item)
                        .append("<a>" + item.label + "</a>")
                        .appendTo(ul);
            };
        }

        $scope.showRelationWith = function ()
        {
            $scope.showRelationOption = 0;
            //console.log($scope.questions.MartialStatus);
            if ($scope.questions.MartialStatus == 2 || $scope.questions.MartialStatus == 3 || $scope.questions.MartialStatus == 4 || $scope.questions.MartialStatus == 5)
            {
                $scope.showRelationOption = 1;

                if ($scope.questions.MartialStatus == 2 || $scope.questions.MartialStatus == 5)
                {
                    $scope.RelationReferenceTxt = 1;
                } else
                {
                    $scope.RelationReferenceTxt = 0;
                }
            } else
            {
                $scope.RelationWithGUID = "";
            }
        }

        $scope.initLocationAuto = function (id)
        {
            var locationEle = document.getElementById(id);
            UtilSrvc.initGoogleLocation(locationEle, function (locationObj) {
                $("#" + id).val(locationObj.CityStateCountry);
                $('#hidden_address_check').val(locationObj.CityStateCountry);
            });
        }

        $scope.redirectToBaseLink = function (link)
        {
            window.top.location = link;
        }





        // $scope.nextPage = function(){
        //   if(IsAdminView == 0)
        //         { 
        //             if ($scope.IsReminder == 1 && $scope.trr == NewsFeedCtrl.activityData.length) {
        //                 return;
        //             }
        //             if($('#IsWiki').length>0)
        //             {
        //              $scope.get_wiki_post(); 
        //             } else if(IsFileTab=='0' && $scope.isDiscussionPost==0)
        //             {
        //                 $scope.GetwallPost();
        //             } 
        //         } 
        // }



        function scrollBindFeed() {

            var IsWikiEle = $('#IsWiki');
            var scrollDistancePercentage = 3;
            scrollDistancePercentage = scrollDistancePercentage * 10;
            var scrollDistance = 0;
            var windowHeight = parseInt($(window).height());
            var documentHeight = 0;

            $(window).scroll(function () {

                documentHeight = parseInt($(document).height());


//                if ($scope.PageNo > 1)
//                {
//                    
//                }
                //scrollDistance = documentHeight/scrollDistancePercentage;
                //console.log('$(document).height()' +parseInt($(document).height()));
                //console.log('parseInt($(window).height())' +parseInt($(window).height()));
                //console.log('scrollInterval' +scrollInterval);

                var pScroll = $(window).scrollTop();
                var pageBottomScroll1 = documentHeight - pScroll;

                var checkVal = (pScroll * 1000) / documentHeight;

                //if (pScroll >= pageBottomScroll1) {
                if (!(pageBottomScroll1 <= checkVal)) {
                    return;
                }

                if ($scope.busy) {
                    return;
                }




                if ($scope.IsReminder == 1 && $scope.trr == NewsFeedCtrl.activityData.length) {
                    return;
                }

                setTimeout(function () {
                    if (IsWikiEle.length > 0)
                    {
                        $scope.get_wiki_post();
                    } else if (IsFileTab == '0' && $scope.isDiscussionPost == 0)
                    {
                        if ((typeof IsAdminDashboard == 'undefined' && typeof IsAdminActivity !== 'undefined') || IsAdminView == 0)
                        {
                            $scope.GetwallPost();
                        }
                    }

                }, 200);

            });
        }



        function window_scroll() {
            var wiki_ele = $('#IsWiki');
            var windowHeight = parseInt($(window).height());
            $(window).scroll(function () {
                /*if(IsAdminView == 0)
                 {*/
                var scrollInterval = 450;
                if ($scope.PageNo > 1)
                {
                    scrollInterval = scrollInterval * ($scope.PageNo / 2);
                }
                windowHeight = parseInt($(window).height());
                var pScroll = $(window).scrollTop();
                var pageBottomScroll1 = parseInt($(document).height()) - windowHeight - scrollInterval;
                if (pScroll >= pageBottomScroll1) {
                    setTimeout(function () {
                        if (pScroll >= pageBottomScroll1 && !$scope.busy) {
                            if ($scope.IsReminder == 1 && $scope.trr == NewsFeedCtrl.activityData.length) {
                                return;
                            }
                            if (wiki_ele.length > 0)
                            {
                                $scope.get_wiki_post();
                            } else if (IsFileTab == '0' && $scope.isDiscussionPost == 0)
                            {
                                if ((typeof IsAdminDashboard == 'undefined' && typeof IsAdminActivity !== 'undefined') || IsAdminView == 0)
                                {
                                    $scope.GetwallPost();
                                }
                            }
                        }
                    }, 200);
                }
                /*}*/
            });

        }








        $(document).ready(function () {
            //window_scroll();
            $(document).on('click', '.networkmediaList .bx-prev,.networkmediaList .bx-next', function () {
                setTimeout(function () {
                    var transform = $('.networkmedia').css('transform');
                    var index = 0;
                    transform = transform.split('matrix(1, 0, 0, 1, -');
                    if (transform[1])
                    {
                        transform = transform[1].split(',');
                        transform = Math.floor(transform[0]);
                        index = transform / 170;
                    } else
                    {
                        transform = 0;
                    }
                    $('.networkmedia li').each(function (key, val) {
                        if (key == index)
                        {
                            var src = $(this).children('img').attr('src');
                            src = src.split('upload/');
                            src = 'upload/' + src[1];
                            $scope.parseLinks[0].Thumb = src;
                        }
                    });
                }, 500);
            });
        });

        if (IsAdminView == '0')
        {
           /* socket.emit('JoinUser', {
                UserGUID: LoggedInUserGUID
            });
            */
        }


     /*   socket.on('RecieveLiveFeed', function (data) {
            if ($scope.isDiscussionPost == 1) {
                return false;
            }

            WallService.CallApi(data, 'activity/get_single_live_feed').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    if (response.Data.length > 0)
                    {
                        angular.forEach($scope.LiveFeeds, function (val, key) {

                            switch (val.Type) {
                                case 'PL':
                                case 'ML':
                                case 'CL':
                                case 'PC':
                                case 'MC':
                                    if (val.Type == data.Type && val.EntityGUID == data.EntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;

                                case 'FA':
                                case 'FU':
                                    if (val.Type == data.Type && val.Users[0]['ModuleEntityGUID'] == response.Data[0].Users[0].ModuleEntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;
                                case 'FP':
                                    if (val.Type == data.Type && val.Users[0]['ModuleEntityGUID'] == response.Data[0].Users[0].ModuleEntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;
                                case 'EJ':
                                case 'GJ':
                                    if (val.Type == data.Type && val.Entities[0]['ModuleEntityGUID'] == response.Data[0].Entities[0].ModuleEntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;
                            }

                            / ** if(val.Type==data.Type && val.Users[0].ProfileURL==response.Data[0].Users[0].ProfileURL)
                             {
                             $scope.LiveFeeds.splice(key,1);
                             } * /
                        });

                        angular.forEach(response.Data, function (val, key) {
                            val['user_tooltip'] = [];
                            val['entity_tooltip'] = [];
                            val['entity_tooltip_img'] = [];
                            if (val['Users'].length > 0)
                            {
                                angular.forEach(val['Users'], function (a, b) {
                                    if (b > 0 && b < 11)
                                    {
                                        val.user_tooltip.push('<div>' + a.FirstName + ' ' + a.LastName + '</div>');
                                    }
                                });
                            }
                            if (val['Entities'].length > 0)
                            {
                                angular.forEach(val['Entities'], function (c, d) {
                                    if (d > 0 && d < 11)
                                    {
                                        val.entity_tooltip.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                    }
                                    if (d > 3 && d < 15)
                                    {
                                        val.entity_tooltip_img.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                    }
                                });
                            }
                            val.user_tooltip = val.user_tooltip.join('');
                            val.entity_tooltip = val.entity_tooltip.join('');
                            val.entity_tooltip_img = val.entity_tooltip_img.join('');
                            $scope.LiveFeeds.unshift(val);
                        });
                    }
                }
            });
        });

        socket.on('RecieveReminder', function (data) {
            if (IsNewsFeed !== '1') {
                return;
            }
            var reqData = {
                PageNo: 1,
                PageSize: 1,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                FeedSortBy: 1,
                AllActivity: 1,
                ActivityGUID: data.ActivityGUID,
                IsMediaExists: 2,
                ActivityFilterType: 0,
                AsOwner: 0,
                StartDate: "",
                EndDate: "",
                FeedUser: ""
            };

            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == data.ActivityGUID) {
                    NewsFeedCtrl.activityData.splice(key, 1);
                }
            });

            WallService.CallApi(reqData, 'activity').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (response.Data.length == 0) {
                        return;
                    }
                    response.Data[0]['append'] = 1;
                    response.Data[0]['Settings'] = Settings.getSettings();
                    response.Data[0]['ImageServerPath'] = Settings.getImageServerPath();
                    response.Data[0]['SiteURL'] = Settings.getSiteUrl();
                    response.Data[0]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                    response.Data[0]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                    response.Data[0]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                    response.Data[0]['ReminderData'] = prepareReminderData(response.Data[0].Reminder);
                    NewsFeedCtrl.activityData.unshift(response.Data[0]);
                    if (!$scope.IsActiveFilter) {
                        setTimeout(function () {
                            if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                $('#FilterButton').show();
                            } else {
                                $('#FilterButton').hide();
                            }
                        }, 1000);
                    }
                }
            });
        });
*/
        $scope.LiveFeedToggle = function ()
        {
            if ($(".live-feed").hasClass("is-visible") == false)
            {
                if ($('#LiveFeedPageNo').val() <= 1)
                {
                    $scope.getLiveFeed();
                }

            }
        };

        $scope.strip = function (html)
        {
            html = html.replace(/lt&lt/g, '<');
            html = html.replace(/gt&gt/g, '>');
            html = html.replace(/<br>/g, '\n');
            html = html.replace(/<br \/>/g, '\n');
            html = html.replace(/<br\/>/g, '\n');
            var tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        };

        $scope.smart_substr = function (val)
        {
            return smart_substr(140, val);
        }

        $scope.LiveFeeds = [];
        $scope.live_feed_call = false;
        $scope.getLiveFeed = function ()
        {
            $('.loader-live-feed').show();
            var reqData = {PageNo: $('#LiveFeedPageNo').val()};
            WallService.CallApi(reqData, 'activity/live_feed').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.live_feed_call = true;
                    angular.forEach(response.Data, function (val, key) {
                        val['user_tooltip'] = [];
                        val['entity_tooltip'] = [];
                        val['entity_tooltip_img'] = [];
                        if (val['Users'].length > 0)
                        {
                            angular.forEach(val['Users'], function (a, b) {
                                if (b > 0 && b < 11)
                                {
                                    val.user_tooltip.push('<div>' + a.FirstName + ' ' + a.LastName + '</div>');
                                }
                            });
                        }
                        if (val['Entities'].length > 0)
                        {
                            angular.forEach(val['Entities'], function (c, d) {
                                if (d > 0 && d < 11)
                                {
                                    val.entity_tooltip.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                }
                                if (d > 3 && d < 15)
                                {
                                    val.entity_tooltip_img.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                }
                            });
                        }
                        val.user_tooltip = val.user_tooltip.join('');
                        val.entity_tooltip = val.entity_tooltip.join('');
                        val.entity_tooltip_img = val.entity_tooltip_img.join('');
                        $scope.LiveFeeds.push(val);
                    });
                    $('#LiveFeedPageNo').val(parseInt($('#LiveFeedPageNo').val()) + 1);
                }
                $('.loader-live-feed').hide();
            });
        }

        $scope.callImageFill = function ()
        {
            /*setTimeout(function () {
             $('.mediaPost figure').imagesLoaded(function () {
             $('.mediaPost:not(.single-image) .media-thumb').imagefill();
             });
             }, 200);*/
        }

        $scope.TimeZonetoUTC = function (date) {

            /*if(!format)
             {
             format = 'YYYY-MM-DD HH:mm:ss';
             }
             var localTime  = moment.utc(date).toDate();
             console.log(localTime);
             return moment(localTime).format(format);*/

            //date = new Date(date).toUTCString();
            var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;

            return [year, month, day].join('-');
        }

        $scope.post_multiple_group = function ()
        {
            showButtonLoader('post_multiple_group');
            $scope.post_in_group_guid = $(".multi_group:checked").val();
            if ($scope.post_in_group_guid !== "" && $scope.post_in_group_guid != undefined)
            {
                $scope.SubmitWallpost();
                setTimeout(function () {
                    $scope.post_in_group_guid = "";
                }, 2000)

            } else
            {
                showResponseMessage('Please select a group', 'alert-danger');
            }
            hideButtonLoader('post_multiple_group');
        }


        $scope.popularData = [];
        $scope.popular_feeds_single = [];
        $scope.get_popular_feeds = function ()
        {
            WallService.CallApi(reqData, 'activity/get_popular_feeds').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        response.Data[key].showNum = 0;
                        response.Data[key].DisplayTomorrowDate = DisplayTomorrowDate;
                        response.Data[key].DisplayNextWeekDate = DisplayNextWeekDate;
                        if (IsNewsFeed == 0) {
                            response.Data[key].sameUser = 0;
                            response.Data[key].lastCount = 0;
                        } else {
                            if ($scope.LastModuleID == val.ModuleID && $scope.LastModuleEntityID == val.ModuleEntityID) {
                                response.Data[key].sameUser = 1;
                                $scope.lastCount = $scope.lastCount + 1;
                                response.Data[key].lastCount = $scope.lastCount;
                                response.Data[key].lastActivityGUID = $scope.lastActivityGUID;
                            } else {
                                if (key > 0) {
                                    response.Data[parseInt(key) - 1].showNum = 1;
                                }
                                response.Data[key].sameUser = 0;
                                response.Data[key].lastCount = 0;
                                $scope.lastCount = -1;
                                response.Data[key].lastActivityGUID = response.Data[key].ActivityGUID;
                                $scope.lastActivityGUID = response.Data[key].lastActivityGUID;
                            }
                        }
                        $scope.LastModuleID = val.ModuleID;
                        $scope.LastModuleEntityID = val.ModuleEntityID;
                    })
                    $scope.LoggedInName = response.LoggedInName;
                    $scope.LoggedInProfilePicture = response.LoggedInProfilePicture;
                    $scope.tr = response.TotalRecords;
                    $scope.tfr = response.TotalFavouriteRecords;
                    $scope.trr = response.TotalReminderRecords;
                    $scope.tflgr = response.TotalFlagRecords;
                    $scope.IsSinglePost = 0;
                    if ($scope.ActivityGUID) {
                        $scope.IsSinglePost = 1;
                    }
                    response.Data['IsPopular'] = 1;
                    /*NewsFeedCtrl.activityData = response.Data;
                     angular.forEach(NewsFeedCtrl.activityData, function(val, key) {
                     if (val['Reminder'] && typeof val['Reminder'].ReminderGUID !== 'undefined') {
                     NewsFeedCtrl.activityData[key]['ReminderData'] = $scope.prepareReminderData(val['Reminder']);
                     }
                     NewsFeedCtrl.activityData[key].ImageServerPath = image_server_path;
                     });*/
                    $scope.popularData = response.Data;
                    $scope.popular_feeds_single[0] = $scope.popularData[0];
                }
            });
        }

        $scope.popularLimit = 0;
        $scope.getPopularLimit = function (direction)
        {
            if (direction == 'Next')
            {
                if (parseInt($scope.popularLimit) + 1 == $scope.popularData.length)
                {
                    $scope.popularLimit = 0;
                } else
                {
                    $scope.popularLimit++;
                }
            }
            if (direction == 'Prev')
            {
                if ($scope.popularLimit == 0)
                {
                    $scope.popularLimit = parseInt($scope.popularData.length) - 1;
                } else
                {
                    $scope.popularLimit--;
                }
            }
            $scope.popular_feeds_single[0] = $scope.popularData[$scope.popularLimit];
        }


        $scope.clearAllFilter = function (v) {
            $('.secondary-nav').removeAttr("style");
            $('#IsMediaExists').val(2);
            $('#datepicker').val('');
            $('#datepicker2').val('');
            $('#PostOwner').val('');
            $('#PostOwnerSearch').val('');
            $('#AsOwner').val(0);
            $('#srch-filters').val('');
            $('.filter-icon').removeClass('filter-active');
            $('#user,#type,#reported,#date,#keyword').addClass('hide');
            $("#datepicker").datepicker("option", "maxDate", 0);
            $("#datepicker2").datepicker("option", "minDate", null);
            $scope.resetWallPageNo();
            //$('.loader-fad,.loader-view').show();
            $('.filterApply').addClass('hide');
            if (v !== 1) {
                $('#ActivityFilterType').val(0);
                //angular.element(document.getElementById('WallPostCtrl')).scope().clearReminderFilter();
            }
            if (v == 1)
            {
                $('.filterApply').removeClass('hide');
            }
            $scope.suggestPage = [];
            $scope.startExecution();
            $scope.hideLoader();
            $scope.getFilteredWall();
        }
        $scope.ResetFilter = function (v, skipCall) {
            NewsFeedCtrl.Ward_id = 1;
            NewsFeedCtrl.WN = 'All';
            $scope.filterIsPromoted = 0;
            $scope.Filter.IsSetFilter = false;
            $scope.Filter.typeLabelName = 'Everything';
            $scope.Filter.ownershipLabelName = 'Anyone';
            $scope.Filter.timeLabelName = '';
            $scope.Filter.sortLabelName = 'Recent Post';
            $scope.Filter.contentLabelName = 'All Posts';
            $scope.keywordLabelName = '';
            if (IsAdminView == '1')
            {
                angular.element(document.getElementById('UserListCtrl')).scope().ResetShowMe();
            }
            $('#FeedSortBy').val('2');
            // Reset Content keyword
            $('#BtnSrch i').removeClass('icon-removeclose');
            $('#srch-filters').val('');

            /*Reset Show Me option and Tag*/
            $('.check-content-filter:checked').each(function (e) {
                $('.check-content-filter').prop('checked', false);
            });
            $scope.search_tags = []
            /**/

            $('#ActivityFilterType').val(0);
            $('.active-with-icon').children('li').removeClass('active');
            $scope.typeLabelName = 'Everything';
            $scope.PostedByLookedMore = [];
            $('#datepicker').val('');
            $('#datepicker2').val('');

            $('#postedby').val('');
            $scope.resetWallPageNo();


            /*$('.secondary-nav').removeAttr("style");
             $('#IsMediaExists').val(2);
             $('#datepicker').val('');
             $('#datepicker2').val('');
             $('#PostOwner').val('');
             $('#PostOwnerSearch').val('');
             $('#AsOwner').val(0);
             $('#srch-filters').val('');
             $('.filter-icon').removeClass('filter-active');
             $('#user,#type,#reported,#date,#keyword').addClass('hide');
             $("#datepicker").datepicker("option", "maxDate", 0);
             $("#datepicker2").datepicker("option", "minDate", null);
             
             $('.loader-fad,.loader-view').show();
             $('.filterApply').addClass('hide');
             if (v !== 1) {
             $('#ActivityFilterType').val(0);
             //angular.element(document.getElementById('WallPostCtrl')).scope().clearReminderFilter();
             }
             if (v == 1)
             {
             $('.filterApply').removeClass('hide');
             }*/
            $scope.suggestPage = [];
            $scope.startExecution();
            $scope.hideLoader();
            $('body').scrollTop(0);
            if (!skipCall) {
                $scope.getFilteredWall();
            }

        }

        $scope.searchWallContent = function () {
            var searchText = $('#srch-filters').val();
            if ($('#BtnSrch i').hasClass('icon-removeclose')) {
                $('#BtnSrch i').removeClass('icon-removeclose');
                $('#srch-filters').val('');
                $scope.getFilteredWall();
            } else {
                if (searchText == "") {
                    $('#BtnSrch i').removeClass('icon-removeclose');
                } else {
                    $scope.getFilteredWall();
                }
            }
        }

        /*$scope.set_act_post_as = function (ActivityGUID,data) {
         var element =$('#act-' + ActivityGUID + ' .post-as-data');
         element.attr('data-module-id',data.ModuleID);
         element.attr('data-module-entityid',data.ModuleEntityGUID);
         $('#act-' + ActivityGUID + ' .show-pic').attr('src', image_server_path + 'upload/profile/36x36/' +data.ProfilePicture);
         $('#act-' + ActivityGUID + ' .current-profile-pic').attr('src', image_server_path + 'upload/profile/36x36/' +data.ProfilePicture);
         }*/

        $scope.entityList = [];
        // * code for welcome popup start here *//
        $scope.popupSettings = {
            popupProcessbarWidth: 1,
            incrementStepsTime: 3,
            incrementStepsWidth: 1,
            steps: 1,
            ForumCategoryGUID: '',
            ForumCategoryID: ''
        };
        var setInt;
        $scope.getCategoryGuid = function () {
            var reqData = {ForumID: 1, Name: 'introduction'};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_category_detail_by_name', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $("#module_entity_owner").val(response.Data.ForumCategoryGUID);
                    $("#welcomeUserPopup #module_entity_owner").val(response.Data.ForumCategoryGUID);
                    $("#module_entity_guid").val(response.Data.ForumCategoryGUID);
                    $('#module_id').val('34');
                    $scope.popupSettings.ForumCategoryGUID = response.Data.ForumCategoryGUID;
                    $scope.popupSettings.ForumCategoryID = response.Data.ForumCategoryID;
                }
            },
                    function (error) {
                        console.log(error);
                        //showResponseMessage('Something went wrong.', 'alert-danger');
                    });
        };

        $scope.followed_categories = [];
        $scope.followedCat = 0;
        $scope.get_only_follow_categories = function ()
        {
            var reqData = {'pageSize': 12, 'pageNo': 1};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_only_follow_category', reqData, function (successResp) {
                var response = successResp.data;
                var localStorageCate = [];
                if (response.ResponseCode == 200)
                {
                    $scope.followed_categories = response.Data;

                    angular.forEach(response.Data, function (val, key) {
                        if ($scope.followed_categories[key].ProfilePicture == '') {
                            $scope.followed_categories[key].ProfilePicture = 'default-img.jpg';
                        }
                        if (val.Permissions.IsMember) {
                            localStorageCate.push(val);
                            $scope.followedCat++;
                        }
                        localStorage.setItem('followedCategories', JSON.stringify(localStorageCate));
                        $scope.followedCategoriesOrInt = localStorageCate;
                        $scope.selectionType = 'profile';
                        $scope.selectionCount = $scope.followedCategoriesOrInt.length;
                        $('#welcomeUserPopup').modal('show');
                        $scope.startProcessbarwidth();
                    });
                }
            });
        };
        $scope.getSubCategory = function (parentID, on_init) {
            var reqData = {};
            var service = 'users/get_popular_interest';

            WallService.CallPostApi(appInfo.serviceUrl + service, reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    $scope.allInterestList = response.Data;
                    var localsotrageInt = [];
                    angular.forEach(response.Data, function (val, key) {
                        if ($scope.allInterestList[key].ImageName == '') {
                            $scope.allInterestList[key].ImageName = 'default-img.jpg';
                        }
                        if (val.IsInterested == 1) {
                            $scope.allInterestList[key].ProfilePicture = $scope.allInterestList[key].ImageName;
                            $scope.selectedCount++;
                            localsotrageInt.push(val);
                        }
                    });
                    localStorage.setItem('selectedInterest', JSON.stringify(localsotrageInt));
                    $scope.followedCategoriesOrInt = localsotrageInt;
                    $scope.selectionType = 'category';
                    $scope.selectionCount = $scope.followedCategoriesOrInt.length;
                    $('#welcomeUserPopup').modal('show');
                    $scope.startProcessbarwidth();
                }
            });
        };

        $scope.followedCategoriesOrInt = [];
        $scope.selectionCount = 0;
        $scope.selectionType = 'profile';
        $scope.showWelcomePopup = function () {
            if(IsNewsFeed == 1)
            {
                if (settings_data.m31 == 1) {
                    $scope.followedCategoriesOrInt = JSON.parse(localStorage.getItem('selectedInterest'));
                    $scope.selectionType = 'category';
                    $scope.getSubCategory();
                } else {
                    $scope.followedCategoriesOrInt = JSON.parse(localStorage.getItem('followedCategories'));
                    $scope.selectionType = 'profile';
                    $scope.get_only_follow_categories();
                }
                if ($scope.followedCategoriesOrInt != null) {
                    $scope.selectionCount = $scope.followedCategoriesOrInt.length;
                    $('#welcomeUserPopup').modal('show');
                    $scope.startProcessbarwidth();
                }
            }
        };
        $scope.startProcessbarwidth = function () {
            if (angular.isDefined(setInt))
                return;
            setInt = $interval(function () {
                if ($scope.popupSettings.popupProcessbarWidth >= 100) {
                    $scope.stopProcessbarwidth();
                    return true;
                } else if ($scope.popupSettings.popupProcessbarWidth < 100) {
                    $scope.popupSettings.popupProcessbarWidth += $scope.popupSettings.incrementStepsWidth;
                }

                //console.log($scope.popupSettings.popupProcessbarWidth);
            }, $scope.popupSettings.incrementStepsTime);

        }
        $scope.stopProcessbarwidth = function () {
            if (setInt) {
                $interval.cancel(setInt);
            }
            $timeout(function () {
                $scope.getWelcomePopupArticle();
                $scope.popupSettings.steps = 2;
            }, 500);

        }

        $scope.getPostTitle = function (data) {

            data.collapsedAttachmentExists = 0;

            if ((data.Album.length > 0 || data.Files.length > 0)) {
                data.collapsedAttachmentExists = 1;
            }

            if (data.PostTitle) {
                return $scope.getHighlighted(data.PostTitle);
            }

            var contentDiv = angular.element('#collapse_post_content_div');
            contentDiv.html(data.PostContent);
            $scope.setCollapseObj(data, contentDiv);

            if (!data.PostTitle && data.collepsedPostContent) {
                return $scope.getHighlighted(data.collepsedPostContent);
            }

            if ((data.Album.length > 0 || data.Files.length > 0) && !data.PostTitle && !data.collepsedPostContent) {
                return $scope.getHighlighted('Attachment with this post');
            }

            // For inline attachment
            if (data.collepsedAttachement && !data.collepsedPostContent) {
                return $scope.getHighlighted('Attachment with this post');
            }

            // For inline embed
            if (data.collepsedEmbed && !data.collepsedPostContent) {
                return $scope.getHighlighted('Media with this post');
            }

            return '';
        }
        $scope.getWelcomePopupArticleIcalled = false;
        $scope.getWelcomePopupArticle = function () {
            if ($scope.getWelcomePopupArticleIcalled) {
                return false;
            }
            var reqData = {ActivityFilterType: "0", ActivityGUID: 0, AllActivity: 0, AsOwner: "0", EndDate: "", EntityGUID: $scope.popupSettings.ForumCategoryGUID, FeedSortBy: "2", FeedUser: [], IsMediaExists: "2", IsPromoted: 0, IsSticky: 0, Mentions: [], ModuleID: "34", PageNo: 1, PageSize: 10, PollFilterType: "0", PostType: 0, SearchKey: "", ShowArchiveOnly: 0, StartDate: "", Tags: [], ViewEntityTags: 1, entity_id: $scope.popupSettings.ForumCategoryID};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_popup_article', reqData, function (successResp) {
                var response = successResp.data;

                if (response.ResponseCode == 200) {
                    $scope.welcome_popup_article = response.Data;
                } else {
                }
                initSliderWelcome();
                $scope.getWelcomePopupArticleIcalled = true;
            },
                    function (error) {
                        console.log(error);
                        //showResponseMessage('Something went wrong.', 'alert-danger');
                    });
        }
        // * code for welcome popup end here *//

        $scope.getEntityList = function () {

            if (settings_data.m18 == 0) { // If page module is disabled then return;
                return;
            }

            var reqData = {};
            if ($scope.LoginSessionKey)
            {
                if ($scope.entityList.length > 0)
                {
                    $scope.PostAs = $scope.entityList[0];
                    return;
                }
                WallService.CallPostApi(appInfo.serviceUrl + 'page/my_pages', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.entityList = response.Data;
                        $scope.PostAs = $scope.entityList[0];
                    } else {
                    }
                },
                        function (error) {
                            //showResponseMessage('Something went wrong.', 'alert-danger');
                        });
            }
        }

        setTimeout(function () {
            if ($('#IsWiki').length == 0)
            {
                $scope.getEntityList();
            }
        }, 500);

        $scope.set_post_as = function (data) {
            $scope.hideImg = true;
            $('.user-img-icon .thumb-alpha').remove();
            $scope.PostAs = data;
            setTimeout(function () {
                $scope.hideImg = false;
            }, 10);
        }
        $scope.ActiveFilter = function () {
            $scope.IsActiveFilter = true;
        }

        $scope.CheckReminderDate = function (ActivityGUID) {
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.ActivityGUID == ActivityGUID)
                {
                    if (!val.ReminderData)
                    {
                        return true;
                    }

                    var date = moment(val.ReminderData.ReminderDateTime);
                    var now = moment();

                    if (date <= now) {
                        return true;
                    } else {
                        return false;
                    }
                }
            });
        }

        $scope.summernoteDropdown = function ()
        {
            $('.dropdown-toggle').dropdown();
        }

        var ajax_request = false;
        var Summer_keyword = '';

        var toolbar_options = [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['paragraph']],
            ['misc', ['emoji']],
            ['insert', ['picture', 'video', 'link']]
            
        ];
        var shortcuts = true;

        if(settings_data.m40==0)
        {
            toolbar_options = [['misc', ['emoji']]];
            shortcuts = false;
        }

        //document.emojiType = 'unicode';
        document.emojiSource = AssetBaseUrl + 'img/emoji';
        $scope.options = {
            placeholder: 'Write here and use @ to tag someone.',
            airMode: false,
            popover: {},
            shortcuts: shortcuts,
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    setTimeout(function () {
                        document.execCommand('insertText', false, bufferText);
                    }, 10);
                }
            },
            toolbar: toolbar_options,
            hint: {
                //mentions: [{ModuleID:3,UserID:18,FirstName:'Vikas',LastName:"Choudhary"},{ModuleID:3,UserID:18,FirstName:'Suresh',LastName:"Choudhary"}],
                //mentions: ['jayden', 'sam', 'alvin', 'david'],
                //match: /\B@(\w*)$/,
                match: /^(?!.*?[^\na-z0-9]{2}).*?\B@(\w*)$/i,
                search: function (keyword, callback) {
                    var ExcludeIds = [];
                    var i = 0;
                    $('#postEditor .note-editable [data-tag="user-tag"]').each(function (e) {
                        var cls = $('#postEditor .note-editable [data-tag="user-tag"]:eq(' + e + ')').attr('class');
                        cls = cls.split('-');
                        ExcludeIds[i] = cls[2];
                        i++;
                    });

                    Summer_keyword = keyword;
                    if ($.trim(keyword).length < 2) {
                        return false;
                    }
                    if (ajax_request) {
                        ajax_request.abort();
                    }

                    var reqData = {
                        ExcludeID: ExcludeIds,
                        PageSize: 10,
                        Type: 'MembersTagging',
                        SearchKey: keyword,
                        ModuleID: $('#module_id').val(),
                        ModuleEntityID: $('#entity_id').val()
                    };

                    if (($scope.tagsto.length === 1) && ($scope.tagsto[0].ModuleID == 1)) {
                        reqData['ModuleID'] = $scope.tagsto[0].ModuleID;
                        reqData['ModuleEntityID'] = $scope.tagsto[0].ModuleEntityGUID;
                    } else {
                        reqData['ModuleID'] = $('#module_id').val();
                        reqData['ModuleEntityID'] = $('#entity_id').val();
                        if (IsNewsFeed == '1') {
                            reqData['Type'] = 'NewsFeedTagging';
                            if ($scope.edit_post)
                            {
                                if ($scope.edit_post_details.ModuleID == 1)
                                {
                                    reqData['Type'] = 'MembersTagging';
                                    reqData['ModuleID'] = 1;
                                    reqData['ModuleEntityID'] = $scope.edit_post_details.EntityGUID;
                                }
                            }
                        }
                    }

                    if (!reqData['ModuleEntityID'])
                    {
                        reqData['ModuleEntityID'] = $('#module_entity_guid').val();
                    }
                    if(reqData['ModuleID']=='34' && reqData['ModuleEntityID']){
                        reqData['Type'] = 'NewsFeedTagging';
                    }

                    if (IsAdminView == '1')
                    {
                        reqData['AdminLoginSessionKey'] = $('#AdminLoginSessionKey').val();
                        $http({
                            method: 'POST',
                            data: reqData,
                            url: base_url + 'api/users/list'
                        }).then(function (r) {
                            r = r.data;
                            if (r.ResponseCode == 200) {
                                var uid = 0;
                                var d = new Array();
                                if (r.Data)
                                {
                                    for (var key in r.Data.Members) {
                                        var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                        d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': r.Data.Members[key].ModuleID, 'ModuleEntityGUID': r.Data.Members[key].UserGUID, 'ModuleID': r.Data.Members[key].ModuleID, 'ProfilePicture': r.Data.Members[key].ProfilePicture, AllowedPostType: r.Data.Members[key].AllowedPostType};
                                        uid++;
                                    }
                                    keyword = keyword.toLowerCase();
                                    callback($.grep(d, function (item) {
                                        keyword = $.trim(keyword);
                                        return item.name.toLowerCase().indexOf(keyword) > -1;
                                    }));
                                }
                            }
                        });
                    } else
                    {
                        reqData['Loginsessionkey'] = LoginSessionKey;
                        ajax_request = $.post(base_url + 'api/users/list', reqData, function (r) {
                            if (r.ResponseCode == 200) {

                                var uid = 0;
                                var d = new Array();
                                if (r.Data)
                                {
                                    for (var key in r.Data.Members) {
                                        var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                        d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': r.Data.Members[key].ModuleID, 'ModuleEntityGUID': r.Data.Members[key].UserGUID, 'ModuleID': r.Data.Members[key].ModuleID, 'ProfilePicture': r.Data.Members[key].ProfilePicture, AllowedPostType: r.Data.Members[key].AllowedPostType};
                                        uid++;
                                    }
                                    keyword = keyword.toLowerCase();

                                    callback($.grep(d, function (item) {
                                        keyword = $.trim(keyword);
                                        return item.name.toLowerCase().indexOf(keyword) > -1;
                                    }));
                                }
                            }
                        });
                    }
                },
                template: function (item) {
                    //angular.element(document.getElementById('UserProfileURL')).scope().ContentType = item.AllowedPostType;
                    return '<tagitem entityid="' + item.id + '" name="' + item.name + '" profilepicture="' + item.ProfilePicture + '" moduleid="' + item.ModuleID + '" moduleentityguid="' + item.ModuleEntityGUID + '">' + item.name.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + Summer_keyword + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<span>$1</span>") + '</tagitem>';
                    //return '<span>'+item.name+'</span>';
                },
                content: function (item) {
                    //return $('<span contenteditable="true" style="padding:0 2px;">').html('<span contenteditable="false" data-tag="user-tag" class="user-'+item.type+'-'+item.id+'">'+ item.name+'</span>')[0];
                    return $("<span contenteditable='true' style='padding:0 2px;'>").html("<span contenteditable='false' data-tag='user-tag' class='user-" + item.type + "-" + item.id + "'>" + item.name + "</span> &nbsp;")[0];
                }
            }/*,
             cleaner:{
             notTime:0, // Time to display Notifications.
             action:'paste', // both|button|paste 'button' only cleans via toolbar button, 'paste' only clean when pasting content, both does both options.
             newline:'<br>', // Summernote's default is to use '<p><br></p>'
             notStyle:'position:absolute;bottom:0;left:2px', // Position of Notification
             keepHtml: false, //Remove all Html formats
             badTags: ['style','script','applet','embed','noframes','noscript', 'html'], //Remove full tags with contents
             badAttributes: ['contenteditable','style','start'] //Remove attributes from remaining tags            
             }*/
        };


        var article_options = angular.copy($scope.options);
        article_options['toolbar'] = [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['paragraph']],
            ['misc', ['emoji']],
            ['insert', ['picture', 'video', 'link']]
        ];
        
        $scope.article_options = article_options;

        $scope.welcomeOptions = {
            placeholder: 'Tell us about you?',
            airMode: false,
            popover: {},
            shortcuts: shortcuts,
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    setTimeout(function () {
                        document.execCommand('insertText', false, bufferText);
                    }, 10);
                }
            },
            toolbar: [
                ['style', ['bold', 'italic', 'underline']],
                ['para', ['paragraph']],
                ['insert', ['picture', 'video', 'link']]
            ]
        };

        var comment_toolbar_options = [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['paragraph']],
            ['insert', ['picture', 'video', 'link']],
            ['misc', ['emoji']]
        ];
        var shortcuts = true;

        if(settings_data.m41==0)
        {
            comment_toolbar_options = [['misc', ['emoji']]];
            shortcuts = false;
        }

        $scope.commentOptions = {
            //placeholder:'What’s on your mind',
            airMode: false,
            focus: true,
            popover: {},
            shortcuts: shortcuts,
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    /*setTimeout(function(){
                     //document.execCommand('insertText', false, bufferText);
                     },10);*/
                }
            },
            toolbar: comment_toolbar_options,
            hint: {
                //match: /\B@(\w*)$/,
                match: /^(?!.*?[^\na-z0-9]{2}).*?\B@(\w*)$/i,
                search: function (keyword, callback) {
                    Summer_keyword = keyword;
                    if ($.trim(keyword).length < 2)
                    {
                        return false;
                    }
                    if (ajax_request)
                    {
                        ajax_request.abort();
                    }

                    var TaggingType = 'MembersTagging';
                    var m_id = $('#module_id').val();
                    if (m_id == '14')
                    {
                        var m_eid = $('#module_entity_guid').val();
                    } else
                    {
                        var m_eid = $('#entity_id').val();
                    }
                    if (IsNewsFeed == '1')
                    {
                        TaggingType = 'NewsFeedTagging';
                        angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                            if (val.ActivityGUID == $('[data-guid]').attr('data-guid'))
                            {
                                if (val.ModuleID == 1)
                                {
                                    TaggingType = 'MembersTagging';
                                    m_id = 1;
                                    m_eid = val.EntityGUID;
                                }
                            }
                        });
                    }

                    var ExcludeIds = [];
                    var i = 0;

                    //comment-edit-block-56b01e93-c4c6-395e-4b08-a504674f6d16

                    var summerNoteBlock = $('#comment-edit-block-' + $('[data-guid]').attr('data-guid') + ' .note-editable');
                    if (!summerNoteBlock.length) {
                        summerNoteBlock = $('#cmt-div-' + $('[data-guid]').attr('data-guid') + ' .note-editable');
                    }

                    summerNoteBlock.find('[data-tag="user-tag"]').each(function (e) {
                        var cls = summerNoteBlock.find('[data-tag="user-tag"]:eq(' + e + ')').attr('class');
                        cls = cls.split('-');
                        ExcludeIds[i] = cls[2];
                        i++;
                    });

                    ajax_request = $.post(base_url + 'api/users/list', {ExcludeID: ExcludeIds, Loginsessionkey: LoginSessionKey, PageSize: 10, Type: TaggingType, SearchKey: keyword, ModuleID: m_id, ModuleEntityID: m_eid}, function (r) {
                        if (r.ResponseCode == 200) {

                            var uid = 0;
                            var d = new Array();
                            if (r.Data)
                            {
                                for (var key in r.Data.Members) {
                                    var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                    d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': '3', 'ModuleEntityGUID': r.Data.Members[key].UserGUID, 'ModuleID': r.Data.Members[key].ModuleID, 'ProfilePicture': r.Data.Members[key].ProfilePicture, AllowedPostType: r.Data.Members[key].AllowedPostType};
                                    uid++;
                                }
                                keyword = keyword.toLowerCase();

                                callback($.grep(d, function (item) {
                                    keyword = $.trim(keyword);
                                    return item.name.toLowerCase().indexOf(keyword) > -1;
                                }));
                            }
                        }
                    });
                },
                template: function (item) {
                    //angular.element(document.getElementById('UserProfileURL')).scope().ContentType = item.AllowedPostType;
                    return '<tagitem name="' + item.name + '" profilepicture="' + item.ProfilePicture + '" moduleid="' + item.ModuleID + '" moduleentityguid="' + item.ModuleEntityGUID + '">' + item.name.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + Summer_keyword + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<span>$1</span>") + '</tagitem>';
                    //return '<span>'+item.name+'</span>';
                },
                content: function (item) {
                    return $('<span contenteditable="true" style="padding:0 2px; display:inline-block;">').html('<span contenteditable="false" data-tag="user-tag" class="user-' + item.type + '-' + item.id + '">' + item.name + '</span>')[0];
                }
            }
        };

        $scope.show_comment_box = "";
        $scope.postCommentEditor = function (ActivityGUID, feedIndex, medianotblank) {
            setTimeout(function () {
                $('.emoji-scroll').mCustomScrollbar();
                if($('#cmt-'+ActivityGUID).length>0)
                {
                    $scope.setFocusToSummernote('#cmt-'+ActivityGUID);
                }
            }, 1000);
            angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                if (val.NoOfComments == 0 && key != feedIndex)
                {
                    if (typeof NewsFeedCtrl.activityData[key]['showeditor'] !== 'undefined')
                    {
                        NewsFeedCtrl.activityData[key]['showeditor'] = false;
                    }
                }
            });

            if (typeof medianotblank !== 'undefined')
            {
                $scope.edit_comment_box = "";
                $scope.show_comment_box = ActivityGUID;
            } else
            {
                $scope.edit_comment_box = "";
                $scope.show_comment_box = ActivityGUID;
                NewsFeedCtrl.activityData[feedIndex].medias = '';
                NewsFeedCtrl.activityData[feedIndex].commentMediaCount = 0;
                NewsFeedCtrl.activityData[feedIndex].commentFileCount = 0;
            }
            
            $scope.postCommentDisableBtn = true;
        };

        $scope.postCommentEditorAnn = function (ActivityGUID, feedIndex, medianotblank) {
            setTimeout(function () {
                $('.emoji-scroll').mCustomScrollbar();
                if($('#cmt-'+ActivityGUID).length>0)
                {
                    $scope.setFocusToSummernote('#cmt-'+ActivityGUID);
                }
            }, 1000);
            angular.forEach(NewsFeedCtrl.group_announcements, function (val, key) {
                if (val.NoOfComments == 0 && key != feedIndex)
                {
                    if (typeof NewsFeedCtrl.group_announcements[key]['showeditor'] !== 'undefined')
                    {
                        NewsFeedCtrl.group_announcements[key]['showeditor'] = false;
                    }
                }
            });

            if (typeof medianotblank !== 'undefined')
            {
                $scope.edit_comment_box = "";
                $scope.show_comment_box = ActivityGUID;
            } else
            {
                $scope.edit_comment_box = "";
                $scope.show_comment_box = ActivityGUID;
                NewsFeedCtrl.group_announcements[feedIndex].medias = '';
                NewsFeedCtrl.group_announcements[feedIndex].commentMediaCount = 0;
                NewsFeedCtrl.group_announcements[feedIndex].commentFileCount = 0;
            }
        };

        $scope.edit_comment_box = "";
        $scope.EditComment = "";
        $scope.commentEditBlock = function (CommentGUID, ActivityGUID, CommentData) {
            $scope.show_comment_box = "";
            $scope.edit_comment_box = CommentGUID;
            $scope.EditComment = angular.copy(CommentData);

            $scope.EditComment.Media.map(function (repo) {
                repo.progress = true;
                return repo;
            });
            $scope.EditComment.Files.map(function (repo) {
                repo.progress = true;
                return repo;
            });
            //NewsFeedCtrl.activityData[feedIndex].  
           // console.log($scope.EditComment.EditPostComment);
            setTimeout(function () {
                $("#comment-edit-block-" + CommentGUID + " .note-editable").html($scope.EditComment.EditPostComment);
                placeCaretAtEnd($("#comment-edit-block-" + CommentGUID + " .note-editable")[0]);

                $("#comment-edit-block-" + CommentGUID + "").find('.note-placeholder').hide();

            }, 800)

            /* CommentData.Media.map( function (repo) {
             repo.progress = true;
             return repo;
             });
             CommentData.Files.map( function (repo) {
             repo.progress = true;
             return repo;
             });
             //NewsFeedCtrl.activityData[feedIndex].  
             //console.log(CommentData.PostComment);
             setTimeout(function(){
             $("#comment-edit-block-"+CommentGUID+" .note-editable").html(CommentData.EditPostComment);
             placeCaretAtEnd($("#comment-edit-block-"+CommentGUID+" .note-editable")[0]);
             },100)
             */

            //$('#comment-view-block-'+CommentGUID).hide();
            //$('#comment-edit-block-'+CommentGUID).removeClass('hide');
            //$('#cmt-'+CommentGUID).summernote('focus');
            //showButtonLoader('PostBtn-'+CommentGUID);
        };
        /*$scope.CheckBlur = function(ActivityGUID) {
         console.log(ActivityGUID);
         $('#cmt-div-'+ActivityGUID+' .place-holder-label').show();
         $('#cmt-div-'+ActivityGUID+' .comment-section').addClass('hide');
         };*/
        $scope.imageUpload = function (files) {
            //console.log('image upload:', files);
        };
        
        function runOnAllSumernoteInstances(fn) {
            
            var allArguments = arguments;            
            
            setTimeout(function(){
                
                $('.summernote').each(function(i, obj) { 
                    allArguments = Array.prototype.concat(allArguments);   
                    var currentTarget = $(obj).next().find('.note-editable').get(0);
                    allArguments.unshift({currentTarget : currentTarget});
                    fn.apply(this, allArguments);
                });     
                
                
            }, 500);
            
            
                   
        }
        
        $scope.summernoteBtnDisabler = false;
        $scope.onSummerNoteChange = function(event) {                                    
            
            if( event.currentTarget instanceof HTMLDivElement && !checkSummerNoteDisability($(event.currentTarget))) {
                $scope.summernoteBtnDisabler = false;
                return;
            }
            
            if($scope.mediaCount || $scope.fileCount) {
                $scope.summernoteBtnDisabler = false;
                return;
            }
            
            $scope.summernoteBtnDisabler = true;
                       
        }
        
        function checkSummerNoteDisability(editorEle) {
            if(editorEle.find('img, iframe').length) {                
                return false;
            }
            
            if(editorEle.get(0).textContent) {
                return false;
            }
            
            return true;
        }
        
        $scope.postCommentDisableBtn = true;
//        $scope.onPostCommentChange = function(event) {                        
//            $scope.postCommentDisableBtn = checkSummerNoteDisability($(event.currentTarget));
//        }
        
        
        $scope.checkEditorData = function (event, feedIndex) {  
//            if (BlockName) {
//                var PComments = $('#' + BlockName + '-' + ActivityGUID + ' .note-editable').html();
//            } else {
//                var PComments = $('#cmt-div-' + ActivityGUID + ' .note-editable').html();
//            }

            // to solve blank comment issue
//            PComments = PComments.replace(/&nbsp;/g, '');
//            PComments = PComments.trim();
            //var isEmpty = $('#cmt-' + ActivityGUID).summernote('isEmpty');
            
            if( event.currentTarget instanceof HTMLDivElement && !checkSummerNoteDisability($(event.currentTarget))) {
                $scope.postCommentDisableBtn = false;
                return;
            }
            
            if ((NewsFeedCtrl.activityData[feedIndex].medias && (Object.keys(NewsFeedCtrl.activityData[feedIndex].medias).length > 0)))
            {
                //hideButtonLoader('PostBtn-' + ActivityGUID);
                
                $scope.postCommentDisableBtn = false;
                return;                
            } 
            
            
            $scope.postCommentDisableBtn = true;
        };
        

        $scope.summernoteUpload = function (evt, editor, welEditable, $attrs) {
            $scope.Desc_loader = true;
            if ($attrs.posttype == 'Post')
            {
                jQuery('#postEditor .postEditorLoader').show();
            } else if ($attrs.posttype == 'Comment')
            {
                jQuery('#cmt-div-' + $attrs.guid + ' .commentEditorLoader').show();
            }

            var reqData = {
                ImageUrl: evt.target.result,
                Type: 'wall',
                DeviceType: 'Native'
            };
            $scope.isWallAttachementUploading = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/saveFileFromUrl', reqData).then(function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    if ($attrs.posttype == 'Post')
                    {
                        jQuery('#PostContent').summernote("insertImage", response.Data.ImageServerPath + '/' + response.Data.ImageName);
                        $timeout(function () {
                            jQuery('#postEditor .postEditorLoader').hide();
                        }, 900);
                    } else if ($attrs.posttype == 'Comment')
                    {
                        jQuery('#' + $attrs.id).summernote("insertImage", response.Data.ImageServerPath + '/' + response.Data.ImageName);
                        $timeout(function () {
                            jQuery('#cmt-div-' + $attrs.guid + ' .commentEditorLoader').hide();
                        }, 900);
                    }
                    /*console.log(welEditable);
                     editor.insertImage(welEditable, response.Data.ImageServerPath+'/'+response.Data.ImageName);*/

                }
                $scope.isWallAttachementUploading = false;
                jQuery('.postEditorLoader').hide();


            }, function () {
                $timeout(function () {
                    showResponseMessage('This file cannot be uploaded', 'alert-danger');
                    jQuery('.postEditorLoader').hide();
                    jQuery('.commenttEditorLoader').hide();
                }, 900);
            });

        }

        $scope.postTypeview = 0;
        $scope.overlayShow = 0;
        $scope.postPreviemode = 0;
        $scope.postEditormode = 0;
        $scope.postypeActive = 0;

        $scope.get_title_class = function ()
        {
            if($('#PostTitleLimit').length > 0)
            {
                if ($scope.edit_post && $('#PostTitleLimit').val().length > 0)
                {
                    return 'ng-valid';
                }
            }
        }

        $scope.toggle_post_view = function ()
        {
            if ($scope.postTypeview == 1)
            {
                $scope.postTypeview = 0;
            } else
            {
                $scope.postTypeview = 1;
            }
        }

        $scope.viewPostType = function () {
            if ($scope.postTypeview == 1)
            {
                $scope.postTypeview = 0;
            } else
            {
                $scope.postTypeview = 1;
                $scope.overlayShow = 1;
            }
        }

        $scope.setEditorPosition = function (px)
        {
            $('#postEditor').css('top', px + 'px');
        }

        $scope.showPostEditor = function (isTypeChanged, showOverlay) {
            if (showOverlay) {
                $scope.overlayShow = 1;
            }
            $scope.taggedEntityInfoCount = 0;
            $scope.taggedHelpTxtSuffix = '';
            $scope.postEditormode = 1;
            $scope.postTypeview = 0;
            $scope.postypeActive = 1;

            $scope.setActiveIconToPrivacy(default_privacy);
            $scope.setPrivacyHelpTxt(default_privacy);
            setTimeout(function () {
                $('#visible_for').val(default_privacy);
            }, 100);

            if (!$scope.edit_post && !isTypeChanged)
            {
                $scope.mediaCount = 0;
                $scope.fileCount = 0;
                $scope.medias = {};
                $scope.edit_medias = {};
                $scope.files = {};
                $scope.edit_files = {};
                $scope.group_user_tags = [];
                $scope.tagsto = [];
                $('.tags input').val('');
            }
            if ($scope.PostContent == '')
            {
                $scope.noContentToPost = true;
            }

            var char = 140 - $('#PostTitleInput').val().length;
            if (char == 1)
            {
                $('#PostTitleLimit').html('1 character');
            } else
            {
                $('#PostTitleLimit').html(char + ' characters');
            }

            setTimeout(function () {
                placeCaretAtEnd($("#postEditor .note-editable")[0]);
            }, 200);
        }

        $scope.setVisualEditPost = function ()
        {
            $scope.VisualPost = {PostTitle: $scope.edit_post_details.PostTitle, PostContent: $scope.edit_post_details.PostContent, Facts: $scope.edit_post_details.Facts};
            $scope.backgroundClass = $scope.edit_post_details.Params['BackgroundClass'];
            $scope.visualPostImage = 0;
            if ($scope.edit_post_details.Album.length > 0)
            {
                $scope.visualPostStyle = {'background': 'url(' + $scope.edit_post_details.Album[0].Media[0].ImageServerPath + '/' + $scope.edit_post_details.Album[0].Media[0].ImageName + ') no-repeat 0 0', 'background-size': 'cover'};
                $scope.visualPostImage = 1;
            }
        }

        $scope.setContestEdit = function ()
        {
            var contest_end_date = $scope.edit_post_details.ContestEndDate.split(' ')[0];
            contest_end_date = contest_end_date.split('-');
            contest_end_date = contest_end_date[1] + '/' + contest_end_date[2] + '/' + contest_end_date[0];
            $scope.Contest = {PostTitle: $scope.edit_post_details.PostTitle, PostContent: $scope.edit_post_details.PostContent, ButtonText: $scope.edit_post_details.Params['ButtonText'], ContestDate: contest_end_date, ContestTime: $scope.edit_post_details.ContestEndDate.split(' ')[1], NoOfWinners: $scope.edit_post_details.Params['NoOfWinners']};
            if (!$scope.$$phase)
            {
                $scope.$apply();
            }
            $scope.backgroundClass = $scope.edit_post_details.Params['BackgroundClass'];
            $scope.visualPostImage = 0;
            if ($scope.edit_post_details.Album.length > 0)
            {
                $scope.visualPostStyle = {'background': 'url(' + $scope.edit_post_details.Album[0].Media[0].ImageServerPath + '/' + $scope.edit_post_details.Album[0].Media[0].ImageName + ') no-repeat 0 0', 'background-size': 'cover'};
                $scope.visualPostImage = 1;
            }
            if ($('#NoOfWinners').length > 0)
            {
                $('#NoOfWinners').val($scope.edit_post_details.Params['NoOfWinners']);
                $('#NoOfWinners').trigger("chosen:updated");
            }
        }

        $scope.postPreview = function () {
            $scope.postPreviemode = 1;
            $scope.postEditormode = 0;
            // $timeout(function(){
            //     $('.mediaPost.two-images .mediaThumb, .mediaPost.three-images .mediaThumb,.mediaPost.four-images .mediaThumb').imagefill();
            // },50);
        }
        $scope.backEditMode = function () {
            $scope.postPreviemode = 0;
            $scope.postEditormode = 1;
        }

        $scope.removeAllview = function () {
            $scope.postPreviemode = 0;
            $scope.postEditormode = 0;
            $scope.postTypeview = 0;
            $scope.overlayShow = 0;
            if (!$scope.$$phase)
            {
                $scope.$apply();
            }
        }

        $scope.clearVisualPost = function ()
        {
            $scope.VisualPost = {PostTitle: '', PostContent: '', Facts: ''};
            $scope.backgroundClass = 'switch-one';
            $scope.FocusOn = '';
            $scope.ShowPreview = 0;
            $scope.medias = {};
            $scope.edit_medias = {};
            $scope.mediaCount = 0;
            $scope.files = {};
            $scope.edit_files = {};
            $scope.fileCount = 0;
            $scope.visualPostImage = 0;
            $scope.visualPostStyle = {};
        }

        $scope.clearContest = function ()
        {
            $scope.Contest = {PostTitle: '', PostContent: '', ButtonText: '', SubmissionDate: '', ContestDate: '', ContestTime: '', NoOfWinners: 0};
            $scope.backgroundClass = 'switch-one';
            $scope.FocusOn = '';
            $scope.ShowPreview = 0;
            $scope.medias = {};
            $scope.edit_medias = {};
            $scope.mediaCount = 0;
            $scope.files = {};
            $scope.edit_files = {};
            $scope.fileCount = 0;
            $scope.visualPostImage = 0;
            $scope.visualPostStyle = {};
        }

        $scope.clearWallPost = function (dont_close)
        {
            $scope.PostContent = '';
            $('#postEditor .note-editable').html('');
            $scope.tagsto = [];
            $scope.postTagList = [];
            $scope.memTagCount = 0;
            $('#PostTitleInput').val('');
            if (!(typeof dont_close !== 'undefined' && dont_close == '1'))
            {
                $scope.removeAllview();
            }
            $(".note-placeholder").show();
            $scope.edit_post = false;
            $scope.override_post_permission = [];
            $scope.parseLinks = [];
            $scope.allreadyProcessedLinks = [];
            $scope.ShowPreview = 0;
            $scope.medias = {};
            $scope.edit_medias = {};
            $scope.mediaCount = 0;
            $scope.files = {};
            $scope.edit_files = {};
            $scope.fileCount = 0;
            $scope.addTagList = false;
            $scope.resetPrivacySettings();
            $('#dCommenting').prop('checked', false);
            $('#comments_settings').val(1);
            $('#comments_settings2').val(1);
            $('.groups-tag input').val('');
            $('#postEditor .note-btn').removeClass('active');
            $('.groups-tag input').css('width', '302px');
        }

        $rootScope.IsNewsFeed = true;

        $scope.confirmCloseEditor = function (event)
        {
            $scope.isMediaInserted = false;
            if (event && event.target.id != 'postNewsFeedTypeModal') {
                return;
            }

            if ($scope.overlayShow == '1' && $scope.activePostType != '8' && $scope.activePostType != '9')
            {
                var pc = $(".note-editable").text().trim();
                var pc_html = $("#postEditor .note-editable").html();
                if (pc_html.indexOf('img') >= 0 || pc_html.indexOf('iframe') >= 0)
                {
                    $scope.isMediaInserted = true;
                }

                if (pc !== '' || $scope.isMediaInserted || $scope.isWallAttachementUploading || $scope.mediaCount > 0 || $scope.fileCount > 0 || $('#PostTitleInput').val().length > 0)
                {
                    //var newsFeedPopupLayer = $('.modal-backdrop.modal-stack').removeClass('modal-backdrop').removeClass('modal-stack');
                    showConfirmBox("Close Editor", "Are you sure you want to close editor? All your content will be lost.", function (e) {
                        if (e) {
                            $scope.clearWallPost();
                            $("#postNewsFeedTypeModal").modal('hide');
                        } else {
                            //newsFeedPopupLayer.addClass('modal-backdrop').addClass('modal-stack');
                        }
                    });
                } else
                {
                    $scope.clearWallPost();
                    $("#postNewsFeedTypeModal").modal('hide');
                }
            } else if ($scope.overlayShow == '1' && $scope.activePostType == '8')
            {
                if ($scope.VisualPost.PostTitle != '' || $scope.VisualPost.PostContent != '' || $scope.VisualPost.Facts != '')
                {
                    //var newsFeedPopupLayer = $('.modal-backdrop.modal-stack').removeClass('modal-backdrop').removeClass('modal-stack');
                    showConfirmBox("Close Editor", "Are you sure you want to close editor? All your content will be lost.", function (e) {
                        if (e) {
                            $scope.clearVisualPost();
                            $("#postNewsFeedTypeModal").modal('hide');
                        } else {
                            //newsFeedPopupLayer.addClass('modal-backdrop').addClass('modal-stack');
                        }
                    });
                } else
                {
                    $scope.clearVisualPost();
                    $("#postNewsFeedTypeModal").modal('hide');
                }
            } else if ($scope.overlayShow == '1' && $scope.activePostType == '9')
            {
                if ($scope.Contest.PostTitle != '' || $scope.Contest.PostContent != '' || $scope.Contest.ContestButtonText != '' || $scope.Contest.ContestDate != '' || $scope.Contest.ContestTime != '' || $scope.Contest.NoOfWinners > 0)
                {
                    //var newsFeedPopupLayer = $('.modal-backdrop.modal-stack').removeClass('modal-backdrop').removeClass('modal-stack');
                    showConfirmBox("Close Editor", "Are you sure you want to close editor? All your content will be lost.", function (e) {
                        if (e) {
                            $scope.clearVisualPost();
                            $("#postNewsFeedTypeModal").modal('hide');
                        } else {
                            //newsFeedPopupLayer.addClass('modal-backdrop').addClass('modal-stack');
                        }
                    });
                } else
                {
                    $scope.clearVisualPost();
                    $("#postNewsFeedTypeModal").modal('hide');
                }
            }
        }

        $scope.$watch('postEditormode', function () {
            if (!$scope.postEditormode) {
                $("#postNewsFeedTypeModal").modal('hide');
            }
        });



        $scope.get_like_name = function (data)
        {
            var str = '';
            var total_records = 0;
            if (data.length > 0)
            {
                total_records = data[0].TotalFriends;
                if (total_records == 1)
                {
                    str = '<span class="semi-bold">' + data[0].FirstName + '</span> <span class="regular">is involved</span>';
                }
                if (total_records == 2)
                {
                    str = '<span class="semi-bold">' + data[0].FirstName + '</span> <span class="regular">and</span> <span class="semi-bold">' + data[1].FirstName + '</span> <span class="regular">are involded</span>';
                }
                if (total_records == 3)
                {
                    str = '<span class="semi-bold">' + data[0].FirstName + '</span><span class="regular">,</span> <span class="semi-bold">' + data[1].FirstName + '</span> <span class="regular">and</span> <span class="semi-bold">1 other</span> are involved';
                }
                if (total_records > 3)
                {
                    str = '<span class="semi-bold">' + data[0].FirstName + '</span><span class="regular">,</span> <span class="semi-bold">' + data[1].FirstName + '</span> <span class="regular">and</span> <span class="semi-bold">' + (parseInt(total_records) - 2) + ' others</span> <span class="regular">are involved</span>';
                }
            }
            return str;
        }

        $scope.get_history = function (ActivityGUID)
        {
            if (!$scope.IsSingleActivity)
                return false;
            var reqData = {ActivityGUID: ActivityGUID};
            WallService.CallApi(reqData, 'activity/get_activity_history').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.ActivityHistory = response.Data;
                }
            });
        }
        $scope.revert_history = function (ActivityGUID, HistoryID)
        {
            var reqData = {ActivityGUID: ActivityGUID, HistoryID: HistoryID};
            WallService.CallApi(reqData, 'activity/change_activity_version').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.busy = false;
                    $scope.stopExecution = 0;
                    $scope.GetwallPost();
                    $scope.ActivityHistory = [];
                }
            });
        }
        $scope.get_activity_friend_list = function (Stage, ActivityGUID, data)
        {

            if (data && (data.RquestedFriendList.length > 0 || data.SuggestedFriendList.length > 0 || data.SearchFriendList != '')) {
                data.RquestedFriendList = [];
                data.SuggestedFriendList = [];
                data.SearchFriendList = '';
                return;
            }

            $scope.ActivityFriendActivityGUID = ActivityGUID;
            var ignoreList = [];
            $.each(NewsFeedCtrl.activityData, function () {
                if (this.ActivityGUID == ActivityGUID)
                {
                    var RquestedFriendList = this.RquestedFriendList;
                    if (RquestedFriendList.length > 0)
                    {
                        $.each(RquestedFriendList, function (k) {
                            ignoreList.push(RquestedFriendList[k].UserID);
                        })
                    }
                }
            })

            $scope.ActivityFriendPageNo = 1;
            $scope.ActivityFriends = [];
            var reqData = {ActivityGUID: ActivityGUID, PageNo: $scope.ActivityFriendPageNo, SearchKey: $('#sr_' + ActivityGUID).val(), IgnoreList: ignoreList};
            WallService.CallApi(reqData, 'activity/get_activity_friend_list').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    var Html = '';
                    $.each(NewsFeedCtrl.activityData, function () {

                        if (this.ActivityGUID == ActivityGUID)
                        {
                            this.SuggestedFriendList = response.Data;
                            this.SuggestedTotalRecords = response.TotalRecords;
                        }
                    });
                    if (response.TotalRecords == 0)
                    {
                        if (settings_data.m10 == 1) {
                            showResponseMessage('Sorry, you have no friends / members to ask this question.', 'alert-danger');
                        } else {
                            showResponseMessage('Sorry, you have no followers / members to ask this question.', 'alert-danger');
                        }
                    }
                    /* $.each(response.Data,function(){
                     //$scope.ActivityFriends=response.Data;
                     Html+='<li>';
                     Html+='<figure> <img ng-src=" '+$rootScope.ImageServerPath+'upload/profile/220x220/'+this.ProfilePicture+'" class="img-circle" alt="" title=""><a></figure>';
                     Html+='</li>';
                     })
                     console.log(Html); 
                     $('#act-'+ActivityGUID+' .rquested-list-view ul').html(Html);*/
                    //$scope.ActivityFriendPageNo = $scope.ActivityFriendPageNo +1;
                }
            });
        }
        $scope.add_request_friend = function (ActivityFriend, $index, ActivityGUID)
        {
            $.each(NewsFeedCtrl.activityData, function () {
                if (this.ActivityGUID == ActivityGUID)
                {
                    this.RquestedFriendList.push(ActivityFriend);
                    this.SuggestedFriendList.splice($index, 1);
                    if (this.SuggestedFriendList.length < 3)
                    {
                        $scope.get_activity_friend_list('', ActivityGUID)
                    }
                }
            })
            /*$scope.RequestFriendList.push(ActivityFriend);
             $scope.ActivityFriends.splice($index,1);
             if($scope.RequestFriendList.length < 3)
             {
             $scope.get_activity_friend_list('',$scope.ActivityFriendActivityGUID)
             }*/
        }

        timeVar = '';
        $scope.SearchActivityFriend = function (ActivityGUID)
        {
            clearTimeout(timeVar);
            timeVar = setTimeout(function () {
                $scope.get_activity_friend_list('init', ActivityGUID);
            }, 500)
        }
        $scope.remove_select_data = function (data, $index, ActivityGUID)
        {
            $.each(NewsFeedCtrl.activityData, function () {
                if (this.ActivityGUID == ActivityGUID)
                {
                    this.SuggestedFriendList.push(data);
                    this.RquestedFriendList.splice($index, 1);
                }
            })

            /*$scope.ActivityFriends.push(data);
             $scope.RequestFriendList.splice($index,1);*/
        }

        $scope.showPrivacyPreview = 1;
        $scope.ShowPreview = 0;
        $scope.previewInGroupWall = false;
        $scope.showPreview = function (isGroupWall)
        {
            $scope.previewInGroupWall = (isGroupWall) ? true : false;
            $scope.showPrivacyPreview = ($scope.selectedPrivacy) ? $scope.selectedPrivacy : $('#visible_for').val();
            var postContent = $(".note-editable").html().trim();
            if (postContent != "") {
                postContent = $.trim(parseTaggedForPreview(postContent));
            }
            $('#PostTypeTitle').html($('#PostTitleInput').val());
            $('#PostTypeContent').html(postContent);
            if (IsAdminView == '1')
            {
                $('#PreviewName').html($scope.postasuser.FirstName + ' ' + $scope.postasuser.LastName);
                $('#PreviewImage').attr('src', image_server_path + 'upload/profile/220x220/' + $scope.postasuser.ProfilePicture);
            } else
            {
                $('#PreviewName').html(LoggedInFirstName + ' ' + LoggedInLastName);
                //$('#PreviewImage').attr('src',image_server_path+'upload/profile/220x220/'+LoggedInPicture);
            }
            $scope.ShowPreview = 1;
            /*$timeout(function(){ 
             if(IsAdminView == '0')
             {
             $('.mediaPost.two-images .mediaThumb, .mediaPost.three-images .mediaThumb,.mediaPost.four-images .mediaThumb').imagefill();
             }
             },50);*/
        }

        $scope.backEditMode = function ()
        {
            $scope.ShowPreview = 0;
        }

        $scope.getCurrentTime = function ()
        {
            var date = new Date();
            return moment(date).format('DD MMM') + ' at ' + moment(date).format('h:mm A');
        }

        $scope.is_draft = 0;
        $scope.saveDraft = function (isSummary)
        {
            $scope.is_draft = 1;
            $scope.SubmitWallpost(isSummary);
        }

        $scope.hideRequest = function (data) {
            data.RquestedFriendList = [];
            data.SuggestedFriendList = [];
        }

        $scope.send_request = function (ActivityGUID)
        {
            var RequestTo = [];
            $.each(NewsFeedCtrl.activityData, function () {
                if (this.ActivityGUID == ActivityGUID)
                {
                    var RquestedFriendList = this.RquestedFriendList;
                    if (RquestedFriendList.length > 0)
                    {
                        $.each(RquestedFriendList, function (k) {
                            RequestTo.push(RquestedFriendList[k].UserGUID);
                        })
                    }
                }
            })

            var reqData = {ActivityGUID: $scope.ActivityFriendActivityGUID, RequestTo: RequestTo, Note: $('#note_' + ActivityGUID).val()};
            WallService.CallApi(reqData, 'activity/request_question_answer_for_activity').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $.each(NewsFeedCtrl.activityData, function () {
                        if (this.ActivityGUID == ActivityGUID)
                        {
                            this.RquestedFriendList = [];
                            this.SuggestedFriendList = [];
                            this.SearchFriendList = '';
                        }
                    })
                    //$scope.ActivityFriends=[];
                    //$scope.RequestFriendList=[];
                    $scope.ActivityDetail.FriendSearchKey = '';
                    $scope.ActivityDetail.Note = '';
                    showResponseMessage(response.Message, 'alert-success');
                }
            });
        }

        $scope.get_tooltip_privacy_title = function (data, v)
        {
            var str = '';
            var anyone_tagged = '';
            var visibility = data.Visibility;
            if (typeof v !== 'undefined')
            {
                visibility = v;
            }
            if (data.IsAnyoneTagged > 0)
            {
                anyone_tagged = ' + Anyone Tagged';
            }
            if (data.ModuleID == 3 && data.ActivityType == 'Post')
            {
                if (visibility == 1)
                {
                    str = 'Everyone';
                }
                if (visibility == 3)
                {
                    if (data.IsEntityOwner == 1)
                    {
                        str = 'My Friends + ' + data.UserName + anyone_tagged;
                    } else
                    {
                        str = 'Only Me + Friends of ' + data.EntityName + anyone_tagged;
                    }
                }
                if (visibility == 4)
                {
                    if (data.IsEntityOwner == 1)
                    {
                        str = 'Only Me + ' + data.UserName + anyone_tagged;
                    } else
                    {
                        str = 'Only Me + ' + data.EntityName + anyone_tagged;
                    }
                }
            } else
            {
                if (visibility == 1)
                {
                    str = 'Everyone';
                }
                if (visibility == 3)
                {
                    if (data.IsEntityOwner == 1)
                    {
                        str = 'My Friends' + anyone_tagged;
                    } else
                    {
                        str = 'Friends of ' + data.UserName + anyone_tagged;
                    }
                }
                if (visibility == 4)
                {
                    if (data.IsEntityOwner == 1)
                    {
                        str = 'Only Me' + anyone_tagged;
                    } else
                    {
                        str = 'Only Me + ' + data.UserName + anyone_tagged;
                    }
                }
            }
            return str;
        }

        $scope.clear_all_filters = function ()
        {
            $('#BtnSrch i').removeClass('icon-removeclose');
            $('#srch-filters').val('');


            $('#srch-filters').val('');
            $scope.search_tags = [];
            $('#IsMediaExists').val('2');
            $('#ActivityFilterType').val('0');
            $scope.filter_archive = false;
            $('#postedby').val('Anyone');
            $('#datepicker').val('');
            $('#datepicker2').val('');
            $('#FeedSortBy').val('1');
            $('.active-with-icon li.active').removeClass('active');
            $scope.getFilteredWall();
        }
        $scope.SetFilter = function ()
        {
            $scope.Filter.IsSetFilter = true;
        }
        $scope.getFilterVal = function ()
        {
            return $scope.Filter.IsSetFilter;
        }

        $scope.getPostTooltip = function (post_type)
        {
            return $scope.allow_post_types[post_type];
        }

        $scope.get_selected_text = function (e, activity_guid)
        {
            var text = getSelectedText();
            $('.tooltip-selection').remove();
            if (text.length > 0)
            {
                var html = '<div ng-click="insert_to_editor(\'' + activity_guid + '\',\'' + text + '\')" class="anim tooltip-selection" id="selectionSharerPopover" style="position:absolute;display:block;top:' + e.pageY + 'px;left:' + e.pageX + 'px;"><div class="tooltip-selection-inner"><ul><li><a class="action"><svg height="16px" width="16px" class="svg-icons"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="'+AssetBaseUrl+'img/sprite.svg#icnQuote"></use></svg></a></li></ul></div></div>';
                angular.element(document.body).append($compile(html)($scope))
            }
        }

        $scope.insert_to_editor = function (activity_guid, text, feedIndex)
        {
            if (!text)
            {
                text = $('#act-' + activity_guid + ' .post-content').html();
            }

            $scope.postCommentEditor(activity_guid, feedIndex, 1);
            var html = '<p>&nbsp;</p>'
            html += '<div class="quote-wrote" contenteditable="true"><span>' + text + '</span></div>';
            html += '<p>&nbsp;</p>';
            if (typeof text !== 'undefined')
            {
                $("#cmt-div-" + activity_guid + " .note-editable").append(html);
                $('#cmt-' + activity_guid).summernote('focus');
                setTimeout(function () {
                    placeCaretAtEnd($("#cmt-div-" + activity_guid + " .note-editable")[0]);
                }, 200);
            }
        }

        $scope.setFocusToSummernote = function (element)
        {
            if(element = '#PostContent')
            {
                $scope.show_privacy = false;
            }
            setTimeout(function () {
                $(element).summernote('focus');
            }, 500);
        }

        $scope.set_mouse = function (el)
        {
            var el = $(el)[0];
            var range = document.createRange();
            var sel = window.getSelection();
            range.setStart(el.childNodes[2], 5);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        }

        $scope.saveRange = function (activity_guid)
        {
            /*var range = $('#cmt-'+activity_guid).summernote('createRange');
             $('#cmt-'+activity_guid).summernote('saveRange');
             console.log($('#cmt-'+activity_guid).summernote('saveRange'));*/
        }

        $scope.entity_articles = [];
        $scope.add_tag_article = function (tag)
        {
            $scope.entity_articles.push({ModuleID: tag.ModuleID, ModuleEntityID: tag.ModuleEntityID});
            $scope.filter_article();
        }

        $scope.remove_tag_article = function (tag)
        {
            angular.forEach($scope.entity_articles, function (val, key) {
                if (val.ModuleID == tag.ModuleID && val.ModuleEntityID == tag.ModuleEntityID)
                {
                    $scope.entity_articles.splice(key, 1);
                }
            });
            $scope.filter_article();
        }

        $scope.related_article_id = 0;
        $scope.related_articles = [];
        $scope.search_tags = [];
        $scope.loadCategorylist = function ($query) {
            var requestPayload = {SearchKeyword: $query};
            var url = appInfo.serviceUrl + 'activity/entity_suggestion';
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                return response.Data.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.loadSearchTagsArticle = function ($query) {
            var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, Offset: 0, Limit: 10};
            var url = appInfo.serviceUrl + 'search/tag?SearchKeyword=' + $query;
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                return response.Data.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.PostedByLookedMore = [];
        $scope.loadSearchUsersArticle = function ($query) {
            var requestPayload = {SearchType: "All", SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 10};
            var url = appInfo.serviceUrl + 'search/user';
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                angular.forEach(response.Data, function (val, key) {
                    response.Data[key].Name = response.Data[key].FirstName + ' ' + response.Data[key].LastName;
                });
                return response.Data.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.filter_article = function ()
        {
            $('#WallPageNo').val(1);
            $('#FeedSortBy').val(2);
            $scope.article_list = [];
            $scope.stop_article_execution = 0;
            $scope.get_wiki_post();
        }


        $scope.local_article_data = [];
        $scope.is_first_time = 1;
        $scope.reset_related_popup = function (data)
        {
            $scope.local_article_data = [];
            $scope.checked_related_articles = [];
            $scope.article_list = [];
            $scope.entity_articles = [];
            $scope.PostedByLookedMore = [];
            $scope.search_tags = [];
            $scope.categorySelect = [];
            $('#WallPageNo').val(1);
            $('#FeedSortBy').val(2);
            $scope.is_first_time = 1;
            $scope.stop_article_execution = 0;
            $scope.get_related_activity(data);
        }

        $scope.get_related_activity = function (data)
        {
            $('.wiki-suggested-listing').mCustomScrollbar({
                callbacks: {
                    onTotalScroll: function () {
                        $scope.get_wiki_post();
                    }
                }
            });

            $scope.checked_related_articles = [];
            var activity_id = data.ActivityID;
            $scope.local_article_data = data;
            $scope.related_article_id = activity_id;
            var reqData = {ActivityID: activity_id};
            WallService.CallApi(reqData, 'activity/get_related_activity').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.related_articles = response.Data;
                    $scope.get_wiki_post(activity_id);
                }
            });
        }

        $scope.update_article_status = function ()
        {
            angular.forEach($scope.related_articles, function (val, key) {
                angular.forEach($scope.article_list, function (v, k) {
                    if (v.ActivityID == $scope.related_article_id)
                    {
                        $scope.article_list.splice(k, 1);
                    }
                });
                $scope.checked_related_articles.push(val.ActivityID);
            });

            angular.forEach($scope.related_articles, function (val, key) {
                angular.forEach($scope.article_list, function (v, k) {
                    if (v.ActivityID == val.ActivityID)
                    {
                        $scope.article_list[k]['IsChecked'] = 1;
                    }
                });
                $scope.checked_related_articles.push(val.ActivityID);
            });
        }

        var wiki_slider = null;
        $scope.slider_init = function ()
        {
            if (wiki_slider)
            {
                wiki_slider.destroySlider();
            }
            wiki_slider = $('#wikislider').bxSlider({mode: 'horizontal', pager: false, minSlides: 1, maxSlides: 3, slideWidth: 300, slideMargin: 10, infiniteLoop: false, hideControlOnEnd: true});
        }

        $scope.select_article = function (article_id)
        {
            var remove = true;
            angular.forEach($scope.article_list, function (val, key) {
                if (val.ActivityID == article_id)
                {
                    remove = false;
                    if ($scope.article_list[key].IsChecked == 0)
                    {
                        $scope.article_list[key].IsChecked = 1;
                        $scope.related_articles.push(val);
                        var append = true;
                        angular.forEach($scope.checked_related_articles, function (v, k) {
                            if (v.ActivityID == article_id)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            $scope.checked_related_articles.push(article_id);
                        }
                    } else
                    {
                        $scope.article_list[key].IsChecked = 0;
                        angular.forEach($scope.checked_related_articles, function (v, k) {
                            if (v == article_id)
                            {
                                $scope.checked_related_articles.splice(k, 1);
                            }
                        });
                        angular.forEach($scope.related_articles, function (v, k) {
                            if (v.ActivityID == article_id)
                            {
                                $scope.related_articles.splice(k, 1);
                            }
                        });
                    }
                }
            });

            if (remove)
            {
                angular.forEach($scope.related_articles, function (val, key) {
                    if (val.ActivityID == article_id)
                    {
                        $scope.related_articles.splice(k, 1);
                    }
                });
            }
        }

        $scope.dismiss_related_activity_popup = function ()
        {
            $('#addRelatedArticles').modal('hide');
        }

        $scope.checked_related_articles = [];
        $scope.add_related_activity = function ()
        {
            var arr = [];
            angular.forEach($scope.related_articles, function (v, k) {
                arr.push(v.ActivityID);
            });
            var reqData = {ActivityID: $scope.related_article_id, RelatedActivity: arr};
            WallService.CallApi(reqData, 'activity/related_activity').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Related articles added successfully.', 'alert-success');
                    $('#addRelatedArticles').modal('hide');
                }
            });
        }

        $scope.copyToClipboard = function (id)
        {
            copyToClipboard('#a-' + id);
        }

        $scope.breakquote = function (e)
        {
            if (e.which == 13)
            {
                //pasteHtmlAtCaret('a');
            }
            //$('#cmt-' + activity_guid).insertAtCaret('text');
            //$('#cmt-div-' + activity_guid + ' .note-editable .quote-wrote').has('br').addClass('quote-wrote-reply');
        }


        $scope.initContestDatepicker = function ()
        {
            $('#ContestDate').datepicker({
                minDate: 0
            });
        }

        $scope.initContestTimepicker = function ()
        {
            $('#ContestTime').timepicker();
        }

        //console.log('Testing '); console.log($scope.config_detail);   data-ng-if="!config_detail.IsSuperAdmin"
        $scope.setPromotionStatus = function (activityId, isPromoted, templateData, event) {
            var requestPayload = {
                ActivityID: activityId,
                IsPromoted: isPromoted
            };
            var interval = null;
            var url = appInfo.serviceUrl + 'activity_helper/set_promotion_status';
            WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    templateData.IsPromoted = (templateData.IsPromoted == '1') ? '0' : '1';
                    showResponseMessage(response.Message, 'alert-success');
                    //console.log(templateData.IsPromoted);


//                    interval = window.setInterval(function(){
//                        if(angular.$$phase) {
//                            return;
//                        }
//                        //console.log(angular.element(event.target).parent().scope());
//                        //angular.element(event.target).parent().scope().$apply();
//                        $scope.$apply();
//                        window.clearInterval(interval);
//                    }, 200);


                }
            });
        };

        $scope.joinPublicGroup = function (GroupGUID, Action)
        {
            var userProfile = angular.element($('#UserProfileCtrl')).scope();
            if (!GroupGUID)
            {
                GroupGUID = $("#module_entity_guid").val();
            }

            reqData = {GroupGUID: GroupGUID};

            WallService.CallPostApi(appInfo.serviceUrl + 'group/join', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if (Action == 'OtherUserProfile')
                    {
                        angular.forEach($scope.MyGrouplist, function (val, key) {
                            if (val.GroupGUID == GroupGUID)
                            {
                                $scope.MyGrouplist[key]['Permission']['IsActiveMember'] = 1;
                                $scope.MyGrouplist[key]['Permission']['DirectGroupMember'] = 1;
                            }
                        });
                    }

                    showResponseMessage(response.Message, 'alert-success');
                    angular.forEach($scope.suggestedlist, function (val, key) {
                        if (val.GroupGUID == GroupGUID)
                        {
                            $scope.suggestedlist[key]['IsJoined'] = 1;
                            $scope.suggestedlist.splice(key, 1);
                        }
                    });

                    $('#join_group_' + GroupGUID).addClass('active');
                    $('#join_group_' + GroupGUID).find('i').addClass('active');
                    $('#join_group_' + GroupGUID).find('span').text('Leave');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                    userProfile.businesscard.btnDisabled = false;
                }
            }, function (error) {
            });
        }

        $scope.toggleFollow = function (PageID, Type, PageGUID) {
            var reqData = {MemberID: PageID, Type: 'page'};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200') {
                    showResponseMessage(response.Message, 'alert-success');
                    $('#suggestion_' + PageID).fadeOut(4000, function () {
                        $(this).remove();
                    });

                    angular.forEach($scope.pageSuggestions, function (val, key) {
                        if (val.PageID == PageID) {
                            $scope.pageSuggestions.splice(key, 1);
                        }
                    })
                    $scope.myFollowPages($scope.SortBy, $scope.OrderBy);
                }
            }, function (error) {
            });

            if ($('#follow_btn_' + PageGUID).length > 0)
            {
                var findText = $('#follow_btn_' + PageGUID).find('span').text(),
                        altText = $('#follow_btn_' + PageGUID).attr('data-alt');

                $('#follow_btn_' + PageGUID).toggleClass('active');
                $('#follow_btn_' + PageGUID).find('i').toggleClass('active');
                $('#follow_btn_' + PageGUID).find('span').text(altText);
                $('#follow_btn_' + PageGUID).attr('data-alt', findText);
            }
        }

        $scope.UpdateUsersPresence = function (TargetPresence, Label, EventGUID, from, Event) {
            RequestFromCard = false;
            if (EventGUID)
            {
                $scope.EventGUID = EventGUID;
                RequestFromCard = true;
            }
            $scope.reqData = {EventGUID: $scope.EventGUID, TargetPresence: TargetPresence}
            // Request to fetch data
            $('.loader-fad,.loader-view').show();
            WallService.CallPostApi(appInfo.serviceUrl + 'events/update_presence', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {

                    if (from == 'search')
                    {
                        if(Event) {
                            if(TargetPresence == 'ATTENDING') {
                                Event.loggedUserPresence = TargetPresence;
                            }
                            
                            if(TargetPresence == 'NOT_ATTENDING') {
                                Event.loggedUserPresence = '';
                            }
                        }
                        var SearchScope = angular.element(document.getElementById('SearchCtrl')).scope();
                        angular.forEach(SearchScope.EventSearch, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                SearchScope.EventSearch[key].MyPresence = Label;
                            }
                        });
                    } else if (from == 'fromSuggestion')
                    {
                        angular.forEach($scope.listSuggestedEvents, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                $scope.listSuggestedEvents[key].loggedUserPresence = Label;
                            }
                        });
                        angular.element($('#UserProfileCtrl')).scope().removebusinessCardCache(EventGUID);
                    } else if (from == 'invited')
                    {
                        angular.forEach($scope.Invitedlist, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                $scope.Invitedlist.splice(key, 1);
                            }
                        });
                    } else
                    {
                        if ($('#suggestionEventCtrl').length > 0)
                        {
                            var suggestionEventCtrl = angular.element($('#suggestionEventCtrl')).scope();
                            if (typeof suggestionEventCtrl.listSuggestedEvents !== 'undefined')
                            {
                                angular.forEach(suggestionEventCtrl.listSuggestedEvents, function (val, key) {
                                    if (val.EventGUID == EventGUID)
                                    {
                                        suggestionEventCtrl.listSuggestedEvents[key].loggedUserPresence = Label;
                                    }
                                });
                            }
                        }

                        $scope.loggedUserPresence = Label;
                        if (RequestFromCard)
                        {
                            if ($scope.data)
                            {
                                $scope.data.Presence = Label;
                            }
                            if ($('#EventListCtrl').length > 0)
                            {
                                angular.element('#EventListCtrl').scope().ListEvents('HOST');
                                angular.element('#EventListCtrl').scope().ListEventsAttend('JOINED');
                                $('.business-card').hide();
                            }
                        }


                        $scope.LoadEventUsers('Member'); // Reload User List 
                        setTimeout(function () {
                            $scope.GetEventDetail($scope.EventGUID); // Reload Event Detail
                        }, 50);

                    }
                    showResponseMessage(response.Message, 'alert-success');
                    $('.loader-fad,.loader-view').hide();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                    $('.loader-fad,.loader-view').hide();
                }
            })
        }

//        $scope.show_poll_option_sidebar = function (poll_detail)
//        {
//            if (!$scope.is_sidebar_option)
//            {
//                $scope.poll_detail = poll_detail;
//                $scope.is_sidebar_option = true;
//            } else
//            {
//                $scope.is_sidebar_option = false;
//            }
//        }

        $scope.set_default_privacy = function (val)
        {
            setTimeout(function () {
                if ($scope.selectedPrivacy < val)
                {
                    $scope.selectedPrivacy = val;
                }
                if ($('#visible_for').val() < val)
                {
                    $('#visible_for').val(val);
                }
            }, 500);
        }

        $scope.setCollapseObj = function (scoppedData, collepsedPostContentEle) {
            //var collepsedPostContentEle = feedListEle.find('.news-feed-post-body-container');
            var collepsedPostContentEleNative = collepsedPostContentEle.get(0);
            if (!collepsedPostContentEleNative) {
                return;
            }
            var collepsedPostContent = collepsedPostContentEleNative.innerText;
            collepsedPostContent = (collepsedPostContent).replace(/^\s+|\s+$/g, '');

            scoppedData.collepsedPostContent = collepsedPostContent;
            if (!scoppedData.PostTitle && !collepsedPostContent) {
                if (collepsedPostContentEle.find('iframe')) {
                    scoppedData.collepsedEmbed = 1;
                } else {
                    scoppedData.collepsedAttachement = 1;
                }
            }
        }

        $scope.getStickyText = function (data) {

            data.collapsedAttachmentExists = 0;

            if ((data.Album.length > 0 || data.Files.length > 0)) {
                data.collapsedAttachmentExists = 1;
            }

            if (data.PostTitle) {
                return data.PostTitle;
            }

            var contentDiv = angular.element('#collapse_post_content_div');
            contentDiv.html(data.PostContent);
            $scope.setCollapseObj(data, contentDiv);

            if (!data.PostTitle && data.collepsedPostContent) {
                return data.collepsedPostContent;
            }

            if ((data.Album.length > 0 || data.Files.length > 0) && !data.PostTitle && !data.collepsedPostContent) {
                return 'Attachment with this post';
            }

            // For inline attachment
            if (data.collepsedAttachement && !data.collepsedPostContent) {
                return 'Attachment with this post';
            }

            // For inline embed
            if (data.collepsedEmbed && !data.collepsedPostContent) {
                return 'Media with this post';
            }

            return '';
        }

        $scope.peopleYouMayKnowConfig = {
            method: {},
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 2,
            responsive:
                    [{
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1
                            }
                        }]
        };

        $scope.widget_call = 0;
        $scope.get_widgets = function ()
        {
            var reqData = {};
            reqData['Type'] = 'Newsfeed';
            if (IsNewsFeed == 0)
            {
                reqData['Type'] = 'Wall';
                if ($('#module_id').val() == 3)
                {
                    reqData['UserGUID'] = $('#module_entity_guid').val();
                }
            }
            //console.log($('#module_id').val());
            if ($('#module_id').val() == 1)
            {
                reqData['Type'] = 'GroupWall';
                reqData['ModuleID'] = 1;
                reqData['ModuleEntityGUID'] = $('#module_entity_guid').val();
            }

            var url = appInfo.serviceUrl + 'activity/get_widgets';
            WallService.CallPostApi(url, reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    var data = response.Data;
                    if (reqData['Type'] == 'Newsfeed')
                    {
                        $scope.newsFeedSetting = data.NewsfeedSettings;

                        if ('TopGroups' in data && 'Data' in data.TopGroups) {
                            $scope.TopGroup = data.TopGroups.Data;
                        }

                        if ('TopPages' in data) {
                            $scope.top_user_pages = data.TopPages;
                            $scope.pages_length = data.TopPages.length;
                        }

                        if ('SuggestedGroups' in data && 'Data' in data.SuggestedGroups) {
                            $scope.suggestedlist = data.SuggestedGroups.Data;
                        }

                        if ('SuggestedPages' in data && 'Data' in data.SuggestedPages) {
                            $scope.pageSuggestions = data.SuggestedPages.Data;
                        }

                        if ('UpcomingEvents' in data) {
                            $scope.eventNearYou = data.UpcomingEvents;
                        }

                        if ('PollsAboutToClose' in data) {
                            $scope.polls_about_to_close = data.PollsAboutToClose;
                        }

                        if ('NewMembers' in data) {
                            $scope.newMember = data.NewMembers;
                        }


                        if ('m10' in $rootScope.Settings && $rootScope.Settings.m10 == 1) { // Check if friend module is enabled

                            $scope.peopleYouMayKnow = data.PeopleYouMayKnow.Data;
                        } else {
                            $scope.peopleYouMayFollow = data.PeopleYouMayFollow.Data;
                        }


                    } else if (reqData['Type'] == 'Wall')
                    {
                        if ('UserInterest' in data) {
                            $scope.userInterest = data.UserInterest;
                        }

                        if ('UpcomingEvents' in data) {
                            $scope.upcomingEvents = data.UpcomingEvents;
                        }

                        if ('Connections' in data) {
                            $scope.userConnection = data.Connections;
                        }

                        if ('TopPages' in data) {
                            $scope.top_user_pages = data.TopPages;
                            $scope.pages_length = data.TopPages.length;
                        }

                        if ('EntitiesIFollow' in data) {
                            $scope.entities_i_follow = data.EntitiesIFollow;
                        }

                        if ('RecentActivities' in data) {
                            $scope.recentActivities = data.RecentActivities;
                            $scope.recentActivitiesCount = $scope.recentActivities.length;
                        }

                        if ('TopGroups' in data && 'Data' in data.TopGroups) {
                            $scope.TopGroup = data.TopGroups.Data;
                        }

                    } else if (reqData['Type'] == 'GroupWall')
                    {
                        if ('Members' in data) {
                            $scope.group_members = data.Members;
                        }

                        if ('MembersCount' in data) {
                            $scope.group_members_total = data.MembersCount;
                        }

                        if ('FriendCount' in data) {
                            $scope.group_members_friends = data.FriendCount;
                        }

                        if ('Discussion' in data) {
                            $scope.popular_discussions = data.Discussion;
                        }

                        if ('SimilarGroup' in data) {
                            $scope.similar_groups = data.SimilarGroup;
                        }
                    }
                }
                $scope.widget_call++;
            });
        }

        $scope.intToString = function (s)
        {
            if (s)
            {
                return s.toString();
            }
        }

        $scope.checkClick = function ()
        {
            console.log('click triggered');
        }

        $scope.timeRemainingForContest = function (date, flag)
        {
            var currentDate = new Date();
            var timezoneOffset = time_zone_offset;
            var utcDate = new Date(currentDate.getTime());

            var localTime = moment.utc(date).toDate();
            var dateDiff = Math.floor((localTime.getTime() / 1000)) - Math.floor((utcDate.getTime() / 1000));

            var fullDays = Math.floor(dateDiff / (60 * 60 * 24));
            var fullHours = Math.floor((dateDiff - (fullDays * 60 * 60 * 24)) / (60 * 60));
            var fullMinutes = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60)) / 60);
            var dayArray = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            var monthArray = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Nov', 'Dec');

            var str = '';
            if (dateDiff > 0 && fullDays > 0)
            {
                str += fullDays + 'D ';
            }
            if (dateDiff > 0 && fullHours > 0)
            {
                str += fullHours + 'H ';
            }
            if (dateDiff > 0 && fullMinutes > 0)
            {
                str += fullMinutes + 'M';
            }

            if (flag == '1')
            {
                if (str == '')
                {
                    return '';
                } else
                {
                    return 'TIME TO ENTER -';
                }
            }

            return str;
        }

        $scope.joinContest = function (ActivityID)
        {
            var reqData = {ActivityID: ActivityID};
            WallService.CallPostApi(appInfo.serviceUrl + 'contest/add_participant', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(NewsFeedCtrl.activityData, function (val, key) {
                        if (val.ActivityID == ActivityID)
                        {
                            NewsFeedCtrl.activityData[key].IsParticipating = 1;
                            if (!NewsFeedCtrl.activityData[key].Params.NoOfParticipants)
                            {
                                NewsFeedCtrl.activityData[key].Params.NoOfParticipants = 1;
                            } else
                            {
                                NewsFeedCtrl.activityData[key].Params.NoOfParticipants++;
                            }
                            if (!$scope.$$phase)
                            {
                                $scope.$apply();
                            }
                        }
                    });
                } else
                {
                    showResponseMessage(response.Message, 'alert-success');
                }
            });
        }

        $scope.get_participants_line = function (data)
        {
            var text = '';
            var names = [];
            var cnt = 0;
            if (data.IsParticipating == '1')
            {
                names.push('You');
                cnt++;
            }
            if (data.Participants.length > 0)
            {
                for (var i = 0; i <= data.Participants.length - 1; i++)
                {
                    names.push('<a>' + data.Participants[i].FirstName + '</a>');
                    cnt++;
                }
            }

            if (typeof data.Params.NoOfParticipants !== 'undefined' && data.Params.NoOfParticipants > 0)
            {
                if (names.length > 0)
                {
                    text += names.join(', ');
                    if ((data.Params.NoOfParticipants - cnt) > 0)
                    {
                        text += ' and ';
                        if ((data.Params.NoOfParticipants - cnt) == 1)
                        {
                            text += data.Params.NoOfParticipants - cnt + ' user';
                        } else
                        {
                            text += data.Params.NoOfParticipants - cnt + ' users';
                        }
                    } else
                    {
                        text = reverseString(text);
                        text = text.replace(',', 'dna ');
                        text = reverseString(text);
                    }
                    text += ' participated';
                } else
                {
                    if (data.Params.NoOfParticipants == 1)
                    {
                        text = data.Params.NoOfParticipants + ' user participated';
                    } else
                    {
                        text = data.Params.NoOfParticipants + ' users participated';
                    }
                }
            }
            return text;
        }

        $scope.showMessage = function (msg)
        {
            if (msg == 'edit')
            {
                showResponseMessage('Users have been participated, you cannot edit it', 'alert-danger');
            } else if (msg == 'delete')
            {
                showResponseMessage('Users have been participated, you cannot delete it', 'alert-danger');
            }
        }

        $scope.getContestDate = function (date)
        {
            return moment(date).format('D MMM YYYY');
        }

        $scope.getWinnerText = function (data)
        {
            var text = '';
            var names = [];
            var cnt = 0;
            if (data.Winners.length > 0)
            {
                for (var i = 0; i <= data.Winners.length - 1; i++)
                {
                    names.push(data.Winners[i].FirstName);
                    cnt++;
                }
            }
            if (names.length > 0)
            {
                text += names.join(', ');
                text = reverseString(text);
                text = text.replace(',', 'dna ');
                text = reverseString(text);
                text += ' for winning “' + data.PostContent + '“ contest.';
            }
            return text;
        }

        /* $scope.Settings = function (activity_guid) {
         var data = [];
         data['Settings'] = Settings.getSettings();
         data['ImageServerPath'] = Settings.getImageServerPath();
         data['SiteURL'] = Settings.getSiteUrl();
         data['DateTimeTZ'] = Settings.getCurrentTimeUserTimeZone();
         data['DisplayTomorrowDate'] = DisplayTomorrowDate;
         data['DisplayNextWeekDate'] = DisplayNextWeekDate;
         data['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
         $scope.updateActivityData(activity_guid, data);
         if (IsNewsFeed == '1')
         {
         $scope.updatePopularData(activity_guid, data);
         }
         } */


        $scope.initJWPlayer = function(video)
        {
            setTimeout(function(){
                var player = jwplayer('v-'+video.MediaGUID).setup({
                file: image_server_path+'upload/'+video.MediaFolder+'/'+video.ImageName.substr(0, video.ImageName.lastIndexOf('.'))+'.mp4',
                image: image_server_path+'upload/'+video.MediaFolder+'/750x500/'+video.ImageName.substr(0, video.ImageName.lastIndexOf('.'))+'.jpg',
                //file: 'http://localhost/jwplayer/video/home-banner.mp4',
                mute: true,
                autostart: false,
                primary: 'flash'
              })
              .addButton(AssetBaseUrl+'img/popin.png','popout video',function(){
                $scope.popout_video('v-'+video.MediaGUID);
              },"popoutvideo")
              .addButton(AssetBaseUrl+'img/popout.png','popout close',function(){
                $scope.popin_video('v-'+video.MediaGUID);
              },"popoutclose");

              player.on('play',function(){
                if($('.videoout').length>0)
                {
                    $scope.pause_other_videos('v-'+video.MediaGUID);
                }
              });
            },100);
        }

        $scope.is_popout_video = 0;

        $scope.popout_video = function(id)
        {
            $('.myvideo').each(function(e){
                if($(this).children('div').attr('id') == id)
                {
                    $(this).addClass('videoout');
                }
                else
                {
                    $scope.pause_video($(this).children('div').attr('id'));
                    $(this).removeClass('videoout');
                }
            });
            $scope.is_popout_video = 1;
        }

        $scope.play_video_by_thumb = function(media_guid)
        {
            var id = 'v-'+media_guid;
            $scope.play_video(id);
            $scope.pause_other_videos(id);
            $scope.popin_video(id);
        }

        $scope.popin_video = function(id)
        {
            $('.myvideo').each(function(e){
                if($(this).children('div').attr('id') == id)
                {
                    if($(this).hasClass('videoout'))
                    {
                        $(this).removeClass('videoout');
                    }
                }
            });
            $scope.is_popout_video = 0;
        }

        $scope.pause_other_videos = function(id)
        {
            $('.myvideo').each(function(e){
                if($(this).children('div').attr('id') !== id)
                {
                    $scope.pause_video($(this).children('div').attr('id'));
                }
            });
        }

        $scope.pause_video = function(id)
        {
            if($('#'+id).length>0)
            {
                jwplayer(id).play(false);
                jwplayer(id).pause(true);
            }
        }

        $scope.play_video = function(id)
        {
            if($('#'+id).length>0)
            {
                jwplayer(id).play(true);
            }
        }

        angular.element($window).bind("scroll", function(e) {
            if(NewsFeedCtrl && NewsFeedCtrl.activityData && NewsFeedCtrl.activityData.length>0 && $scope.VideoAutoplay==1 && $scope.is_popout_video==0)
            {
                clearTimeout($.data(this, 'scrollTimer1'));
                clearTimeout($.data(this, 'scrollTimerMedia'));
                $.data(this, 'scrollTimer1', setTimeout(function () {
                    angular.forEach(NewsFeedCtrl.activityData,function(val,key){
                        if(val.Album.length>0 && val.Album[0].HasVideo==1)
                        {
                            var play_video = 1;
                            var e = $('#NewsFeedCtrl [data-guid="act-'+val.ActivityGUID+'"]');
                            if(e.length>0)
                            {
                                if(e.isOnScreen())
                                {
                                    angular.forEach(val.Album[0].Media,function(v,k){
                                        if(v.MediaType == 'Video')
                                        {
                                            if(play_video == 1 && v.ConversionStatus!='Pending')
                                            {
                                                $scope.play_video('v-'+v.MediaGUID);
                                                play_video = 0;
                                            }
                                        }
                                    });
                                }
                                else
                                {
                                    angular.forEach(val.Album[0].Media,function(v,k){
                                        if(v.MediaType == 'Video')
                                        {
                                            $scope.pause_video('v-'+v.MediaGUID);
                                        }
                                    });
                                }
                            }
                        }
                    });
                },500));
            }
        });

        $scope.get_members_talking = function (members)
        {
            if (!members)
            {
                return;
            }
            var total_members_count = members.length;
            var loopCount = 2;
            var html = '';
            var count = 0;
            if (total_members_count <= loopCount)
            {
                angular.forEach(members, function (val, key) {
                    count++;
                    html += '<span class="text-brand">' + val.Name + '</span>'
                    if (total_members_count == count)
                    {
                        if (total_members_count == 1)
                        {
                            html += ' <span> is talking </span> ';
                        } else
                        {
                            html += ' <span> are talking </span> ';
                        }
                    } else if (total_members_count - 1 == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + '</span>';
                    } else
                    {
                        html += '<span >,</span> ';
                    }
                });
            }else{
                angular.forEach(members, function (val, key) {
                    if (count > loopCount + 1) {
                        return;
                    }
                    count++;
                    if (count <= loopCount)
                    {
                        html += '<span class="text-brand">' + val.Name + '</span>'
                    }
                    if (loopCount + 1 == count)
                    {
                        if (total_members_count - loopCount == 1)
                        {
                            html += ' <span> other is talking </span> ';
                        } else
                        {
                            html += ' <span>others are talking </span> ';
                        }
                    } else if (loopCount == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + (total_members_count - loopCount) + '</span>';
                    } else if (count < loopCount)
                    {
                        html += '<span >,</span> ';
                    }
                });
            }
            return html;
        };

        $scope.checkInlineImage = function(data)
        {
            data['HaveInlineImage'] = false;
            data['InlineImage'] = '';
            data['ArticlePostContent'] = '';

            var d = extractRegex(data.PostContent);
            var article_content = '';
            var img_content = '';
            var emoji_numbers = '';
            if(d)
            {
                var img_content = d['img'];
                var emoji_numbers = d['en'];
            }
            var ac = '';

            if(img_content)
            {
                data['HaveInlineImage'] = true;
                data['InlineImage'] = img_content;
                article_content = data.PostContent;
                article_content = article_content.split("<img");
                for(var i=0;i<=emoji_numbers;i++)
                {
                    if(i>0)
                    {
                        ac += '<img'+article_content[i];
                    }
                    else
                    {
                        ac += article_content[i];
                    }
                }
                article_content = ac;
                if(article_content == '')
                {
                    article_content = 'Inline Image';
                }
            }
            else
            {
                article_content = data.PostContent;
            }

            if(article_content.length>85)
            {
                article_content = smart_sub_str(85, article_content, true);
            }

            data['ArticlePostContent'] = article_content; 
        }

        $scope.moveToBottom = function()
        {
            setTimeout(function(){
                window.scrollTo(0,document.body.scrollHeight);
            },100);
        }

        
    
    }]);

function extractRegex(str) {
    var regex = /<img[^>]*src="([^"]*)"/g;
    var arr, outp = [];
    var d = [];
    var emoji_numbers = 0;
    while ((arr = regex.exec(str))) {
        if(arr[1].indexOf('assets/img/pngs')==-1)
        {
            d['img'] = arr[1];
            d['en'] = emoji_numbers;
            return d;
        }
        else
        {
            emoji_numbers++;
        }
    }
}

function reverseString(str) {
    return str.split("").reverse().join("");
}

function showLoginPopup()
{
    if ($('#beforeLogin').length > 0)
    {
        $('#beforeLogin').parent('li').addClass('open');
    }
}

$(document).ready(function () {
    $(document).click(function () {
        var WallPostCtrl = angular.element(document.getElementById('WallPostCtrl')).scope();
        if (typeof WallPostCtrl !== 'undefined')
        {
            if (WallPostCtrl.postTypeview == '1')
            {
                WallPostCtrl.toggle_post_view();
            }
        }
    });
});

function getSelectedText() {
    var text = '';
    if (window.getSelection) {
        var selection = window.getSelection();
        text = selection.toString();
    }
    return text;
}


$(document).ready(function () {
    $(document).click(function () {
        $('.tooltip-selection').remove();
    });
    $('.post-content').click(function (e) {
        e.stopImmediatePropagation();
    });

    $(".quote-wrote").initialize(function () {
        $(".quote-wrote").each(function (e) {
            if ($(".quote-wrote:eq(" + e + ")").text() == "")
            {
                $(".quote-wrote:eq(" + e + ")").addClass("quote-wrote-reply");
            }
        });
    });


    $(document).delegate('.inner-wall-post', 'mouseenter mouseleave', function (event) {
        if (event.type === 'mouseenter') {
            $(this).find('.slide_out_content_header').hide();
            $(this).find('.slide_in_content_header').removeClass('moved-right').addClass('current');
        } else {
            $(this).find('.slide_out_content_header').show();
            $(this).find('.slide_in_content_header').removeClass('current').addClass('moved-right');
        }

    });

});


function placeCaretAtEnd(el) {
    if (!el)
    {
        return;
    }
    el.focus();
    if (typeof window.getSelection != "undefined"
            && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.collapse(false);
        textRange.select();
    }
}

function smart_substr(n, s, removeHtmlEntities, onlyContent) {
    var m, r = /<([^>\s]*)[^>]*>/g,
            stack = [],
            lasti = 0,
            result = '';



    if ((removeHtmlEntities && removeHtmlEntities instanceof  Array) || onlyContent) {
        var content = $('.tempProccesingHtml');
        if ($('.tempProccesingHtml').length == 0) {
            content = document.createElement('DIV');
            content = $(content);
            content.addClass('tempProccesingHtml');
            content.addClass('hide');
            $('body').append(content);
        }

        content.html(s);

        if (onlyContent) {
            s = content.text();
        } else {
            for (var index in removeHtmlEntities) {
                content.find(removeHtmlEntities[index]).remove();
            }

            s = content.html();
        }


    }




    //for each tag, while we don't have enough characters
    while ((m = r.exec(s)) && n) {
        //get the text substring between the last tag and this one
        var temp = s.substring(lasti, m.index).substr(0, n);
        //append to the result and count the number of characters added
        result += temp;
        n -= temp.length;
        lasti = r.lastIndex;

        if (n) {
            result += m[0];
            if (m[1].indexOf('/') === 0) {
                //if this is a closing tag, than pop the stack (does not account for bad html)
                stack.pop();
            } else if (m[1].lastIndexOf('/') !== m[1].length - 1) {
                //if this is not a self closing tag than push it in the stack
                stack.push(m[1]);
            }
        }
    }

    //add the remainder of the string, if needed (there are no more tags in here)
    result += s.substr(lasti, n);

    if (removeTags(s).length > n) {
        result += '...';
    }
    //fix the unclosed tags
    while (stack.length) {
        result += '</' + stack.pop() + '>';
    }
    result = result.replace(/(<br>)+/g, '<br>');
    result = result.replace(/(<\/br>)+/g, '<br>');
    result = result.replace(/(<br\/>)+/g, '<br>');

    result = result.replace(/(<br>$)/g, "");



    return result;
}


function smart_sub_str(n, s, onlyShortText) {
    var m, r = /<([^>\s]*)[^>]*>/g,
            stack = [],
            lasti = 0,
            result = '';

    //for each tag, while we don't have enough characters
    while ((m = r.exec(s)) && n) {
        //get the text substring between the last tag and this one
        var temp = s.substring(lasti, m.index).substr(0, n);
        //append to the result and count the number of characters added
        result += temp;
        n -= temp.length;
        lasti = r.lastIndex;

        if (n) {
            result += m[0];
            if (m[1].indexOf('/') === 0) {
                //if this is a closing tag, than pop the stack (does not account for bad html)
                stack.pop();
            } else if (m[1].lastIndexOf('/') !== m[1].length - 1) {
                //if this is not a self closing tag than push it in the stack
                stack.push(m[1]);
            }
        }
    }

    //add the remainder of the string, if needed (there are no more tags in here)
    result += s.substr(lasti, n);

    if (removeTags(s).length > n) {
        // result += '...';
    }

    if (onlyShortText) {
        result += '...';
    } else {
        result = result + '... <a onclick="showMoreComment(this);">See More</a>';
    }

    //fix the unclosed tags
    while (stack.length) {
        result += '</' + stack.pop() + '>';
    }
    result = result.replace(/(<br>)+/g, '<br>');
    result = result.replace(/(<\/br>)+/g, '<br>');
    result = result.replace(/(<br\/>)+/g, '<br>');

    result = result.replace(/(<br>$)/g, "");
    return result;
}

function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).val()).select();
    document.execCommand("copy");
    $temp.remove();
}

function ObjectToArray(o)
{
    var k = Object.getOwnPropertyNames(o);
    var v = Object.values(o);

    var c = function (l)
    {
        this.k = [];
        this.v = [];
        this.length = l;
    };

    var r = new c(k.length);

    for (var i = 0; i < k.length; i++)
    {
        r.k[i] = k[i];
        r.v[i] = v[i];
    }

    return r;
}


function commentpostchange()
{
    $('#commentablePost3').click(function ()
    {
        if ($('#commentablePost3').hasClass('on'))
        {
            $('#commentablePost3 i').attr('class', 'icon-off');
            $('#commentablePost3').removeClass('on');
        } else
        {
            $('#commentablePost3 i').attr('class', 'icon-on');
            $('#commentablePost3').addClass('on');
        }

        if ($('#comments_settings').val() == 0)
        {
            $('#comments_settings').val(1);
        } else
        {
            $('#comments_settings').val(0);
        }
    });
}

function elementLoaded(el, cb) {
    if ($(el).length) {
        // Element is now loaded.
        cb($(el));
    } else {
        // Repeat every 500ms.
        setTimeout(function() {
        elementLoaded(el, cb)
      }, 500);
    }
};
