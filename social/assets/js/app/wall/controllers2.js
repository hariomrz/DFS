angular.module('App').controller('WallPostCtrl', ['$http', 'GlobalService', '$scope', '$rootScope', 'Settings', '$sce', '$timeout', '$q', 'webStorage', 'WallService', 'appInfo', 'setFormatDate', '$interval', '$compile', 'socket',
    '$window',
    function ($http, GlobalService, $scope, $rootScope, Settings, $sce, $timeout, $q, webStorage, WallService, appInfo, setFormatDate, $interval, $compile, socket, $window)
    {
        // Define variables start
        $scope.checkedMyDeskFiltersCount = 0;
        $scope.IsMyDeskTab = false;
        $scope.IsFirstMyDesk = false;
//        $scope.IsMyDesk = false;
        $scope.myDeskTabFilter = {};
        $scope.partialURL = base_url + 'assets/partials/wall/';
        $scope.baseURL = base_url;
        $scope.ReminderFilter = 0;
        $scope.IsReminder = 0;
        $scope.stopExecution = 0;
        $scope.activityData = new Array();
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
        $scope.ActivityDetail={FriendSearchKey:'',Note:''};
        $scope.recommended_article_list=[];

        $scope.LoggedInFirstName = '';
        $scope.LoggedInLastName = '';
        $scope.LoggedInPicture = '';
        if(typeof LoggedInFirstName !=='undefined')
        {
          $scope.LoggedInFirstName = LoggedInFirstName;
          $scope.LoggedInLastName = LoggedInLastName;
          $scope.LoggedInPicture = LoggedInPicture;
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
        if(typeof LoggedInUserGUID !=='undefined')
        {
          LoginGUID = LoggedInUserGUID;
        }
        else
        {
          LoggedInUserGUID = '';
        }
        if (webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID)) {
          var defualtMyDeskTabFilter = angular.copy( webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID) );
        } else {
          var defualtMyDeskTabFilter = {
              All: 1,
              Favourite: 1,
              Mention: 1,
              Reminder: 1,
              NotifyMe: 1
          };
        }
        
        if(typeof IsNewsFeed=='undefined')
        {
          var IsNewsFeed = 0;
        }
        var LoginType = 'user';
        $scope.FromModuleID = '3';
        $scope.FromModuleEntityGUID = LoginGUID; 
        $scope.ActivityHistory=[];
        $scope.ActivityFriends=[];
        $scope.RequestFriendList=[];
        $scope.ActivityFriendPageNo = 1;
        $scope.ActivityFriendActivityGUID='';        
        $scope.override_post_permission = [];
        $scope.viewLoader = true;
        $scope.blankScreen = false;

        //Added for post privacy
        $scope.resetPrivacySettings = function () {
          $('#IconSelect').prop('class', 'icon-every');
          angular.element('#visible_for').val(1)
          $scope.selectPrivacyHelpTxt = 'If blank, message will be visible to everyone';
          $scope.selectContactsHelpTxt = 'If blank, message will be visible to everyone.';
          $scope.taggedHelpTxtSuffix = '';
          $scope.taggedEntityInfo = {};
          $scope.taggedEntityInfoCount = 0;
          $scope.selectedPrivacy = 1;
          $scope.selectedPrivacyTooltip = 'Everyone';
        };

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
          privacy = ( privacy ) ? privacy : angular.element('#visible_for').val();
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
    //              console.log('$scope.taggedEntityInfo : ', $scope.taggedEntityInfo);
    //              console.log('$scope.taggedEntityInfoCount : ', $scope.taggedEntityInfoCount);
            });
          }, 1000);
        }
        //Added for post privacy
        
        
        $scope.updateActivePostType = function(val)
        {
          $scope.activePostType = val;
        }
        $scope.updateActivePostTypeDefault = function(data)
        {
            if(IsAdminView == '1')
            {
              var SetDiscussion=true;
              $scope.updateActivePostType(1);
            }
            else
            {
              if(data.length > 0)
              {
                  var SetDiscussion =false;
                  $.each(data,function(){
                      if(this.Value==1)
                      {
                          SetDiscussion=true;
                      }
                  })
                  if(SetDiscussion)
                  {
                      $scope.updateActivePostType(1);
                  }
                  else
                  {
                      $scope.updateActivePostType(data[0].Value);  
                  }
              }
            }
        }

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
        $scope.initTagsItem = function(feedIndex) {
          var entityTags = angular.copy($scope.activityData[feedIndex].EntityTags);
          $scope.activityData[feedIndex]['editTags'] = ( entityTags && ( entityTags.length > 0 ) ) ? entityTags : [] ;
          $scope.activityData[feedIndex]['showTags'] = false;
        };
        
        $scope.toggleTagsItem = function(feedIndex,activity_guid) {
          if(typeof activity_guid!=='undefined')
          {
            angular.forEach($scope.activityData,function(val,key){
              if(val.ActivityGUID == activity_guid)
              {
                var showTags = $scope.activityData[key]['showTags'];
                if(showTags){
                  $scope.initTagsItem(key);
                } else {
                  $scope.activityData[key]['showTags'] = true;
                }
              }
            });
          }
          else
          {
            var showTags = $scope.activityData[feedIndex]['showTags'];
            if(showTags){
              $scope.initTagsItem(feedIndex);
            } else {
              $scope.activityData[feedIndex]['showTags'] = true;
            }
          }
        };

        $scope.toggleTagsItemAnnouncement = function(feedIndex) {
          var showTags = $scope.group_announcements[feedIndex]['showTags'];
          if(showTags){
            $scope.initTagsItem(feedIndex);
          } else {
            $scope.group_announcements[feedIndex]['showTags'] = true;
          }
        };
        
        $scope.addedEntityTag = function(newTag, feedIndex) {
          var editTags = ( $scope.activityData[feedIndex]['editTags'] ) ? angular.copy($scope.activityData[feedIndex]['editTags']) : [],
          lastIndex = editTags.length - 1;
          if (editTags.length) {
//            console.log(editTags[lastIndex]);
            editTags[lastIndex]['TooltipTitle'] = editTags[lastIndex]['Name'];
            $scope.activityData[feedIndex]['editTags'] = angular.copy(editTags);
          }
        };

        $scope.addPostTag = function(tag,activity_id)
        {
          angular.forEach($scope.activityData,function(val,key){
            if(val.ActivityID == activity_id)
            {
              if(!$scope.activityData[key]['AddedPostTag'])
              {
                $scope.activityData[key]['AddedPostTag'] = []; 
              }
              $scope.activityData[key]['AddedPostTag'].push(tag);
              if(val.RemovedPostTag)
              {
                angular.forEach(val.RemovedPostTag,function(v,k){
                  if(v == tag.TagID)
                  {
                    $scope.activityData[key]['RemovedPostTag'].splice(k,1);
                  }
                });
              }
            }
          });
        }

        $scope.removePostTag = function(tag,activity_id)
        {
          angular.forEach($scope.activityData,function(val,key){
            if(val.ActivityID == activity_id)
            {
              if(!$scope.activityData[key]['RemovedPostTag'])
              {
                $scope.activityData[key]['RemovedPostTag'] = []; 
              }
              $scope.activityData[key]['RemovedPostTag'].push(tag.TagID);
              if(val.AddedPostTag)
              {
                angular.forEach(val.AddedPostTag,function(v,k){
                  if(v.TagID == tag.TagID)
                  {
                    $scope.activityData[key]['AddedPostTag'].splice(k,1);
                  }
                });
              }
            }
          }); 
        }

        $scope.updatePostTags = function(activity_id)
        {
          var reqData = {TagsList : [], EntityID : activity_id, EntityType : 'ACTIVITY', TagType : 'ACTIVITY',TagsIDs:[]};
          angular.forEach($scope.activityData,function(val,key){
            if(val.ActivityID == activity_id)
            {
              if($scope.activityData[key].AddedPostTag)
              {
                reqData['TagsList'] = $scope.activityData[key].AddedPostTag;
                $scope.activityData[key].AddedPostTag = [];
              }
              if($scope.activityData[key].RemovedPostTag)
              {
                reqData['TagsIDs'] = $scope.activityData[key].RemovedPostTag;
                $scope.activityData[key].RemovedPostTag = [];
              }
            }
          });
          
          var surl = appInfo.serviceUrl + 'tag/save';
          if(IsAdminView == '1')
          {
            surl = appInfo.serviceUrl + 'api/tag/save';
          }

          WallService.CallPostApi(surl, reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
              
              angular.forEach($scope.activityData,function(val,key){
                if(val.ActivityID == activity_id)
                {
                  angular.forEach(val.EntityTags,function(v1,k1){
                    angular.forEach(reqData['TagsIDs'],function(v2,k2){
                      if(v1.TagID == v2)
                      {
                        $scope.activityData[key].EntityTags.splice(k1,1);
                      }
                    });
                  });

                  angular.forEach(reqData['TagsList'],function(v1,k1){
                    var append = true;
                    angular.forEach(val.EntityTags,function(v2,k2){
                      if(v1.TagID == v2.TagID)
                      {
                        append = false;
                      }
                    });
                    if(append)
                    {
                      $scope.activityData[key].EntityTags.push(v1);
                    }
                  });

                  $scope.activityData[key].editTags = $scope.activityData[key].EntityTags;
                  $scope.activityData[key]['showTags'] = false;
                }
              });

              showResponseMessage('Tags updated successfully.', 'alert-success');
            }
          }, function (error) {
              showResponseMessage('Unable to update tags.', 'alert-success');
          });
        }

        $scope.updateActivityTags = function(newTags, EntityID, feedIndex) {
            var newTagsPromise = [],
            newTagsToSend = [];
            if (newTags && (newTags.length > 0)) {
              angular.forEach(newTags, function (tagData, tagKey) {
                newTagsPromise.push(makeResolvedPromise(tagData).then(function (tag) {
                  var newTagObj = tag;
                  newTagObj['Name'] = ( tag.TooltipTitle ) ? tag.TooltipTitle : tag.Name ;
                  newTagsToSend.push(newTagObj);
                }));
              });
            }

            $q.all(newTagsPromise).then(function (data) {
              var reqData = { TagsList : newTagsToSend, EntityID : EntityID, EntityType : 'ACTIVITY', TagType : 'ACTIVITY' };
              WallService.CallPostApi(appInfo.serviceUrl + 'tag/save', reqData, function (successResp) {
                var response = successResp.data;
                if ( (response.ResponseCode == 200 ) && ( response.Message == 'Success' ) ) {
                  $scope.activityData[feedIndex]['EntityTags'] = angular.copy(newTags);
                  $scope.activityData[feedIndex]['editTags'] = angular.copy(newTags);
                  $scope.activityData[feedIndex]['showTags'] = false;
                  showResponseMessage('Tags updated successfully.', 'alert-success');
                }
              }, function (error) {
                  showResponseMessage('Unable to update tags.', 'alert-success');
              });
            });
        };

        $scope.updateActivityTagsAnnouncement = function(newTags, EntityID, feedIndex) {
            var newTagsPromise = [],
            newTagsToSend = [];
            if (newTags && (newTags.length > 0)) {
              angular.forEach(newTags, function (tagData, tagKey) {
                newTagsPromise.push(makeResolvedPromise(tagData).then(function (tag) {
                  var newTagObj = tag;
                  newTagObj['Name'] = ( tag.TooltipTitle ) ? tag.TooltipTitle : tag.Name ;
                  newTagsToSend.push(newTagObj);
                }));
              });
            }

            $q.all(newTagsPromise).then(function (data) {
              var reqData = { TagsList : newTagsToSend, EntityID : EntityID, EntityType : 'ACTIVITY', TagType : 'ACTIVITY' };
              WallService.CallPostApi(appInfo.serviceUrl + 'tag/save', reqData, function (successResp) {
                var response = successResp.data;
                if ( (response.ResponseCode == 200 ) && ( response.Message == 'Success' ) ) {
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

        $scope.getActivityTags = function($query) {            
            if( $query ) {
              var url = appInfo.serviceUrl + 'tag/get_entity_tags?SearchKeyword=' + $query + '&TagType=ACTIVITY&EntityType=ACTIVITY&PageNo=1&PageSize=20';
              if(IsAdminView == '1')
              {
                url = appInfo.serviceUrl + 'api/tag/get_entity_tags?SearchKeyword=' + $query + '&TagType=ACTIVITY&EntityType=ACTIVITY&PageNo=1&PageSize=20';
              }
              return WallService.CallGetApi( url, function (successResp) {
              var response = successResp.data;
                  if(response.ResponseCode === 200){
                      var usreList = response.Data;               
                      return usreList.filter(function(flist) {
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

        $scope.GetwallPostTime = function () {
            setTimeout(function () {
                $scope.GetwallPost();
            }, 1000);
        }

        $scope.allow_post_types = {'1':'Discussion','2':'Q & A','3':'Polls','4':'Article','5':'Tasks & Lists','6':'Ideas','7':'Announcements'};

        $scope.getDefaultPostValue = function(landing_page)
        {
            var k = 0;
            angular.forEach($scope.allow_post_types,function(val,key){
                if(val == landing_page)
                {
                    k = key;
                }
            });
            return k;
        }

        $scope.get_post_title = function(title,content)
        {
          var new_title = title;
          if(title == '')
          {
            content = content.replace(/<\/?[^>]+(>|$)/g, "");
            if(content.length>0)
            {
              if(content.length>140)
              {
                new_title = content.substring(0,140);
              }
              else
              {
                new_title = content;
              }
            }
          }
          return new_title;
        }

        $scope.group_announcements = [];
        $scope.group_announcements_single = [];
        $scope.get_announcements = function()
        {
          var reqData = {ModuleID:$('#module_id').val(),ModuleEntityID:$('#module_entity_id').val()};
          WallService.CallApi(reqData, 'activity/get_announcement').then(function (response) {
            if(response.ResponseCode == 200)
            {
              angular.forEach(response.Data,function(val,key){
                response.Data[key]['append'] = 1;
                response.Data[key]['Settings'] = Settings.getSettings();
                response.Data[key]['ImageServerPath'] = Settings.getImageServerPath();
                response.Data[key]['SiteURL'] = Settings.getSiteUrl();
                response.Data[key]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                response.Data[key]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                response.Data[key]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                response.Data[key]['ReminderData'] = $scope.prepareReminderData(new Array());
                response.Data[key].CollapsePostTitle = $scope.get_post_title(response.Data[key].PostTitle,response.Data[key].PostContent);
                if( response.Data[key]['EntityTags'] && ( response.Data[key]['EntityTags'].length > 0 ) ){
                  response.Data[key]['editTags'] = angular.copy( response.Data[key]['EntityTags'] );
                  response.Data[key]['showTags'] = false;
                } else {
                  response.Data[key]['EntityTags'] = [];
                  response.Data[key]['editTags'] = [];
                  response.Data[key]['showTags'] = false;
                }
              });

              $scope.group_announcements = response.Data;
              $scope.group_announcements.map( function (repo) {
                repo.SuggestedFriendList = [];
                repo.RquestedFriendList = [];
                repo.SearchFriendList = '';
                return repo;
              });
              $scope.group_announcements_single[0] = response.Data[0];
            }
          });
        }

        $scope.mark_as_feature = function(activity_guid,module_id,module_entity_id)
        {
          var reqData = {ModuleID:module_id,ModuleEntityID:module_entity_id,ActivityGUID:activity_guid};
          WallService.CallApi(reqData, 'activity/set_featured_post').then(function (response) {
            if(response.ResponseCode == 200)
            {
              if(!response.Data)
              {
                showResponseMessage(response.Message,'alert-danger');
              }
              else
              {
                angular.forEach($scope.activityData,function(val,key){
                  if(val.ActivityGUID == activity_guid)
                  {
                    $scope.activityData[key].IsFeatured = 1;
                  }
                });
                showResponseMessage(response.Message,'alert-success');
              }
            }
          });
        }

        $scope.changeTagList = function(val)
        {
          $scope.addTagList = val;
        }

        $scope.remove_feature = function(activity_guid,module_id,module_entity_id)
        {
          var reqData = {ModuleID:module_id,ModuleEntityID:module_entity_id,ActivityGUID:activity_guid};
          WallService.CallApi(reqData, 'activity/remove_featured_post').then(function (response) {
            if(response.ResponseCode == 200)
            {
              if(!response.Data)
              {
                showResponseMessage(response.Message,'alert-danger');
              }
              else
              {
                angular.forEach($scope.activityData,function(val,key){
                  if(val.ActivityGUID == activity_guid)
                  {
                    $scope.activityData[key].IsFeatured = 0;
                  }
                });
                showResponseMessage(response.Message,'alert-success');
              }
            }
          });
        }

        $scope.hideAnnouncementFromWidget = function(activity_guid,removeForAll)
        {
          var reqData = {EntityGUID:activity_guid};
          if(removeForAll=='1')
          {
            reqData['RemoveForAll'] = 1;
          }
          WallService.CallApi(reqData, 'activity/hide_announcement').then(function (response) {
            if(response.ResponseCode == 200)
            {
              angular.forEach($scope.group_announcements,function(val,key){
                if(val.ActivityGUID == activity_guid)
                {
                  $scope.group_announcements.splice(key,1);
                }
              });
            }
          });
        }

        $scope.hideAnnouncement = function(activity_guid,removeForAll)
        {
          var reqData = {EntityGUID:activity_guid};
          if(removeForAll=='1')
          {
            reqData['RemoveForAll'] = 1;
          }
          WallService.CallApi(reqData, 'activity/hide_announcement').then(function (response) {
            if(response.ResponseCode == 200)
            {
              angular.forEach($scope.activityData,function(val,key){
                if(val.ActivityGUID == activity_guid)
                {
                  $scope.activityData[key].IsPined = 0;
                }
              });
              if($('#IsWiki').length>0)
              {
                angular.forEach($scope.article_list,function(val,key){
                  if(val.ActivityGUID == activity_guid)
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
        $scope.getAnnouncementLimit = function(direction)
        {
          if(direction == 'Next')
          {
            if(parseInt($scope.announcementLimit)+1 == $scope.group_announcements.length)
            {
              $scope.announcementLimit = 0;
            }
            else
            {
              $scope.announcementLimit++;
            }
          }
          if(direction == 'Prev')
          {
            if($scope.announcementLimit == 0)
            {
              $scope.announcementLimit = parseInt($scope.group_announcements.length)-1;
            }
            else
            {
              $scope.announcementLimit--;
            }
          }
          $scope.group_announcements_single[0] = $scope.group_announcements[$scope.announcementLimit];
        }

        $scope.checked_articles = [];
        $scope.checkUncheckArticle = function()
        {
          $scope.checked_articles = [];
          $('.check-article:checked').each(function(e){
            var article_id = $('.check-article:checked:eq('+e+')').attr('id');
            article_id = article_id.split('art-');
            $scope.checked_articles.push(article_id[1]);
          });
        }

        $scope.remove_recommended = function(activity_guid)
        {
          showConfirmBox("Remove Recommended", "Are you sure, you want to remove this article as recommended article ?", function (e) {
            if(e){
              var reqData = {Articles:activity_guid};
              WallService.CallApi(reqData, 'activity/remove_recommended').then(function (response) {
                if (response.ResponseCode == 200) {
                  angular.forEach($scope.article_list,function(v,k){
                    if(v.ActivityGUID == activity_guid)
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

        $scope.recommend_articles = function()
        {
          showConfirmBox("Recommend Articles", "Are you sure, you want to recommend all selected articles ?", function (e) {
            if(e){
              var reqData = {Articles:$scope.checked_articles};
              WallService.CallApi(reqData, 'activity/recommend_articles').then(function (response) {
                if (response.ResponseCode == 200) {
                  //console.log($scope.checked_articles);
                  $($scope.article_list).each(function (key, val) {
                    //console.log($scope.article_list[key].ActivityGUID);
                    if (jQuery.inArray($scope.article_list[key].ActivityGUID,$scope.checked_articles)>-1) {
                        $scope.article_list[key].IsRecommended = 1;
                    }
                  });
                  $scope.get_wiki_widget();
                  $('.wiki-listing-content').removeClass('selected');
                  $scope.checked_articles = [];
                  $('.check-article').attr('checked',false);
                }
              });
            }
          });
        }

        $scope.pin_articles = function(activity_guid)
        {
          showConfirmBox("Pin Articles", "Are you sure, you want to pin all selected articles ?", function (e) {
            if(e){
              var reqData = {Articles:$scope.checked_articles};
              WallService.CallApi(reqData, 'activity/pin_articles').then(function (response) {
                if (response.ResponseCode == 200) {
                  $('.wiki-listing-content').removeClass('selected');
                  $($scope.article_list).each(function (key, val) {
                    if (jQuery.inArray($scope.article_list[key].ActivityGUID,$scope.checked_articles)>-1) {
                        $scope.article_list[key].IsPined = 1;
                    }
                  });
                  $scope.checked_articles = [];
                  $('.check-article').attr('checked',false);
                }
              });
            }
          });
        }

        $scope.remove_articles = function()
        {
          showConfirmBox("Remove Articles", "Are you sure, you want to remove all selected articles ?", function (e) {
            if(e){
              var reqData = {Articles:$scope.checked_articles};
              WallService.CallApi(reqData, 'activity/remove_articles').then(function (response) {
                if (response.ResponseCode == 200) {
                  $($scope.article_list).each(function (key, val) {
                    if (jQuery.inArray($scope.article_list[key].ActivityGUID,$scope.checked_articles)>-1) {
                        $scope.article_list.splice(key,1);
                    }
                  });
                  $scope.get_wiki_widget();
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

                    angular.forEach($scope.widget_articles,function(val,key){
                      angular.forEach(val.Data,function(v,k){
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

        $scope.subscribe_article = function (EntityGUID) {
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

        $scope.slice_string = function(val,count)
        {
          return smart_substr(count,val);
        }

        $scope.stop_article_execution = 0;
        $scope.ISBusyArticleList = true;
        //$scope.article_list = [];
        $scope.show_loader = 0;
        $scope.get_wiki_post = function(activity_id)
        {
            $scope.show_loader = 1;
            $scope.ISBusyArticleList = true;
            $scope.viewLoader = true;
            $scope.EntityGUID = $('#module_entity_guid').val();
                $scope.ModuleID = $('#module_id').val();
                $scope.AllActivity = 0;
                $scope.ActivityGUID = 0;
                $scope.SearchKey = $('#srch-filters').val();
                if ($('#AllActivity').length > 0) {
                    $scope.AllActivity = $('#AllActivity').val();
                }
                $scope.PageNo = $scope.WallPageNo;
                if(($scope.ActivityGUID != '' && $scope.ActivityGUID !=0 && $scope.ActivityGUID != undefined) && isRefreshedFromSticky !='Sticky' )
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
                
                var post_by_looked_more=[];
                if($('#postedby').val()=='You' )
                {
                    post_by_looked_more.push($('#loginUserGUID').val());
                }
                else if($('#postedby').val()=='Anyone' )
                {
                    post_by_looked_more=[];
                }
                else
                {
                   angular.forEach($scope.PostedByLookedMore,function(val,key){
                    post_by_looked_more.push(val.UserGUID);
                }); 
                }
          reqData = {
              PageNo: $('#WallPageNo').val(),
              PageSize: 10,
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
              ViewEntityTags : 1,
              PostType:$scope.PostType,
              ArticleType:$('#ArticleType').val(),
              Tags:[],
              ShowFrom:$scope.entity_articles
          };

          if(activity_id)
          {
            reqData['ExcludeActivityID'] = activity_id;
          }

          angular.forEach($scope.search_tags,function(val,key){
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

          if($('#IsWiki').length==0)
          {
            reqData['ModuleID'] = 3;
            reqData['ModuleEntityID'] = '';
          }

          if($scope.stop_article_execution == 0)
          {
            $scope.stop_article_execution = 1; 
            $scope.viewLoader = true; 
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/articles', reqData, function (successResp) { 
                var response = successResp.data; 
                if(response.ResponseCode == 200)
                {
                  $scope.viewLoader = false;
                    if (!response.Data.length) {
                      $scope.blankScreen = true;
                    }
                    else{
                      $scope.blankScreen = false; 
                    }
                  $scope.is_first_time = 0;
                  
                  if(typeof $scope.article_list == 'undefined')
                  {
                    $scope.article_list = [];
                  }
                  $scope.stop_article_execution = 0;
                    angular.forEach(response.Data,function(v1,k1){
                      var append = true;
                      angular.forEach($scope.article_list,function(v2,k2){
                        if(v1.ActivityGUID == v2.ActivityGUID)
                        {
                          append = false;
                        }
                      });
                      if(append)
                      {
                        $scope.article_list.push(v1);
                      }
                    });
                    if(response.Data.length==0)
                    {
                      $scope.stop_article_execution = 1;
                    }
                    if(!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                  $('.loader-fad').hide();
                  $('#WallPageNo').val(parseInt($('#WallPageNo').val())+1);
                  $scope.ISBusyArticleList = false;
                  $scope.show_loader = 0;
                }
                $scope.show_loader = 0;
            });
          }
          $scope.show_loader = 0;
        }

        $scope.widget_articles = [];
        $scope.total_widget_articles = 0;
        $scope.get_wiki_widget = function()
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
              if(response.ResponseCode == 200)
              {
                $scope.widget_articles = response.Data;
                $scope.total_widget_articles = response.TotalRecords;
                  if(!$scope.$$phase)
                  {
                      $scope.$apply();
                  }
              }
          });

        }

        $scope.fav_article_list = [];
        $scope.fav_total = 0;
        $scope.PageNoFav = 1;
        $scope.get_fav_wiki = function(page_size)
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
              if(response.ResponseCode == 200)
              {
                $scope.fav_article_list = response.Data;
                $scope.fav_total = response.TotalRecords;
                  if(!$scope.$$phase)
                  {
                      $scope.$apply();
                  }
              }
          });

        }
        
        $scope.get_recommended_article = function()
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
              if(response.ResponseCode == 200)
              {
                  angular.forEach(response.Data,function(v1,k1){
                    var append = true;
                    angular.forEach($scope.recommended_article_list,function(v2,k2){
                      if(v1.ActivityGUID == v2.ActivityGUID)
                      {
                        append = false;
                      }
                    });
                    if(append)
                    {
                      $scope.recommended_article_list.push(v1);
                    }
                  });
                  if(!$scope.$$phase)
                  {
                      $scope.$apply();
                  }
              }
          });

        }
        
        $scope.get_trending_widgets = function()
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
              if(response.ResponseCode == 200)
              {
                  angular.forEach(response.Data,function(v1,k1){
                    var append = true;
                    angular.forEach($scope.recommended_article_list,function(v2,k2){
                      if(v1.ActivityGUID == v2.ActivityGUID)
                      {
                        append = false;
                      }
                    });
                    if(append)
                    {
                      $scope.recommended_article_list.push(v1);
                    }
                  });
                  if(!$scope.$$phase)
                  {
                      $scope.$apply();
                  }
              }
          });

        }
        $scope.delete_article = function(activity_guid)
        {
          showConfirmBox("Delete Article", "Are you sure, you want to delete this article ?", function (e) {
            if(e){
              var reqData = {EntityGUID: activity_guid};
              WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                if (response.ResponseCode == 200) {
                  angular.forEach($scope.article_list,function(val,key){
                    if(val.ActivityGUID == activity_guid)
                    {
                      $scope.article_list.splice(key,1);
                    }
                  });
                  $scope.get_wiki_widget();
                }
              });
            }
          });
        }
        
//        my-desk start
         $scope.verifyMyDeskFiltersStatus = function ( ) {
          var checkedCount = 0;
          angular.forEach($scope.myDeskTabFilter, function (filterStatus, filterKey) {
            if ((filterKey !== 'All') && filterStatus) {
              checkedCount++;
            }
          });
          if ( checkedCount === ( Object.keys( $scope.myDeskTabFilter).length - 1 ) ) {
            $scope.myDeskTabFilter['All'] = 1;
          } else {
            $scope.myDeskTabFilter['All'] = 0;
          }
          $scope.checkedMyDeskFiltersCount = checkedCount;
//          console.log('$scope.myDeskTabFilter : ', $scope.myDeskTabFilter);
          $scope.applyMyDeskFilter();
        };
        
        $scope.verifyAllMyDeskFiltersStatus = function () {
//          var checkedCount = 0;
          angular.forEach($scope.myDeskTabFilter, function (filterStatus, filterKey) {
            if ($scope.myDeskTabFilter['All'] === 1) {
              $scope.myDeskTabFilter[filterKey] = 1;
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
            } else {
              $scope.myDeskTabFilter = angular.copy(defualtMyDeskTabFilter);
            }
            webStorage.setStorageData('defualtMyDeskTabFilter' + LoginGUID, $scope.myDeskTabFilter);
          }
        };
        
        $scope.resetFilterValues = function () {
          $scope.checkedMyDeskFiltersCount = 0;
          if ( !$scope.IsMyDeskTab ) {
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
            } else {
              $scope.myDeskTabFilter = angular.copy(defualtMyDeskTabFilter);
            }
            webStorage.setStorageData('defualtMyDeskTabFilter' + LoginGUID, $scope.myDeskTabFilter);
//            $scope.myDeskTabFilter['All'] = 1;
            $scope.verifyMyDeskFiltersStatus();
          }
        };

        $scope.applyMyDeskFilter = function () {
          $scope.IsMyDeskTab = true;
          $scope.IsCurrentPage = 'NewsFeed';
          $scope.IsReminder = 0;
          if ( Object.keys($scope.myDeskTabFilter).length === 0 ) {
            $scope.IsFirstMyDesk = true;
            if (webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID)) {
              $scope.myDeskTabFilter = angular.copy(webStorage.getStorageData('defualtMyDeskTabFilter' + LoginGUID));
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
        $scope.GetwallPost = function (ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload) {
            
            if ( ContentSearchRequestPayload || ( ( Object.keys($scope.myDeskTabFilter).length > 0 ) && $scope.IsFirstMyDesk ) ) {
              $scope.WallPageNo = 1;
              if ( ContentSearchRequestPayload ) {
                ContentSearchRequestData = ContentSearchRequestPayload;
              }
              $scope.stopExecution = 0;
              $scope.busy = false;
            }

            if ( ( $scope.IsFilePage === 0 ) || ActivityGUID || isRefreshedFromSticky || ( Object.keys($scope.myDeskTabFilter).length > 0 ) ) {
                
                if ( $scope.busy || $scope.isActivityPrevented )
                    return;
                $scope.busy = true;
                //Define Variables Starts
                $scope.EntityGUID = $('#module_entity_guid').val();
                $scope.ModuleID = $('#module_id').val();
                $scope.AllActivity = 0;
                $scope.ActivityGUID = 0;
                $scope.SearchKey = $('#srch-filters').val();
                if ($('#AllActivity').length > 0) {
                    $scope.AllActivity = $('#AllActivity').val();
                }
                if ($('#ActivityGUID').length > 0) {
                    $scope.ActivityGUID = $('#ActivityGUID').val();
                }
                if (ActivityGUID) {
                    $scope.ActivityGUID = ActivityGUID;
                    $scope.WallPageNo = 1;
                    $scope.AllActivity = 0;
                }
                $scope.PageNo = $scope.WallPageNo;
                if(($scope.ActivityGUID != '' && $scope.ActivityGUID !=0 && $scope.ActivityGUID != undefined) && isRefreshedFromSticky !='Sticky' )
                {
                    $scope.IsSingleActivity = true;
                }
                if(isRefreshedFromSticky !='Sticky')
                {
                    isRefreshedFromSticky='';
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
                
                var post_by_looked_more=[];
                if($('#postedby').val()=='You' )
                {
                  if($('#loginUserGUID').length>1)
                  {
                    post_by_looked_more.push($('#loginUserGUID').val());
                  }
                  else
                  {
                    post_by_looked_more.push(LoggedInUserGUID);
                  }
                }
                else if($('#postedby').val()=='Anyone' )
                {
                    post_by_looked_more=[];
                }
                else
                {
                   angular.forEach($scope.PostedByLookedMore,function(val,key){
                    if(IsAdminView == '1')
                    {
                      post_by_looked_more.push(val.ModuleEntityGUID);
                    }
                    else
                    {
                      post_by_looked_more.push(val.UserGUID);
                    }
                }); 
                }
                 
                var CommentGUID = $('#CommentGUID').val();
                var reqData = {};
                if ( !ContentSearchRequestData ) {

                  var break_loop = false;
                  var p_type = [];
                  angular.forEach($scope.Filter.ShowMe,function(s1,s2){
                    if(s1.Value == 0 && s1.IsSelect)
                    {
                      p_type.push(0);
                      break_loop = true;
                    }
                    if(!break_loop)
                    {
                      if(s1.IsSelect)
                      {
                        p_type.push(s1.Value);
                      }
                    }
                  });

                  if(p_type.length>0)
                  {
                    $scope.PostType = p_type;
                  }

                  reqData = {
                      PageNo: $scope.PageNo,
                      PageSize: 10,
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
                      CommentGUID: CommentGUID,
                      ViewEntityTags : 1,
                      PostType:$scope.PostType,
                      Tags:[],
                      IsPromoted : filterIsPromoted
                  };

                  angular.forEach($scope.search_tags,function(val,key){
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
                } else {
                  $scope.IsNewsFeed = 0;
                  reqData = angular.copy(ContentSearchRequestData);
                  reqData['PageNo'] = $scope.PageNo;
                  reqData['PageSize'] = 10;
                  reqData['EntityGUID'] = $scope.EntityGUID;
                  reqData['ModuleID'] = $scope.ModuleID;
                }

                if ( ( $scope.PageNo > 1 ) || isRefreshedFromSticky || ( Object.keys($scope.myDeskTabFilter).length > 0 ) ) {
                  if ( isRefreshedFromSticky || ( ( Object.keys($scope.myDeskTabFilter).length > 0 ) && $scope.IsFirstMyDesk ) ) {
                    $scope.activityData = [];
                    $scope.displayLoader();
                  } else {
                    $('.wallloader').show();                    
                  }
//                  if ( ( Object.keys($scope.myDeskTabFilter).length > 0 ) && $scope.IsFirstMyDesk ) {
//                    $scope.displayLoader();
//                  } else {
//                    $('.wallloader').show();
//                  }
                }
                //Defining Variables Ends
                if ($scope.stopExecution == 0) {
                    if (!$scope.is_poll) {
                        service_url = 'activity';
                    } else {
                        service_url = 'polls';
                    }
                    if (!LoginSessionKey && IsAdminView == '0') {
                        if($scope.ActivityGUID == '')
                        {
                            service_url = 'activity/public_posts';
                            reqData = {};
                            reqData['ModuleID'] = $('#module_id').val();
                            reqData['ModuleEntityGUID'] = $('#module_entity_guid').val();
                            reqData['PageNo'] = $scope.PageNo;
                        }
                        else
                        {
                            service_url = 'activity/public_feed';
                            reqData = {};
                            reqData['ActivityGUID'] = $scope.ActivityGUID;
                        }
                    }
                    if ( ContentSearchRequestData ) {
                      service_url = 'search';
                      reqData['SearchKey'] = $('#Keyword').val();
                    }
                    
                    if(IsAdminView == '1')
                    {
                      service_url = 'activity';
                    }
                    
                    if ( Object.keys($scope.myDeskTabFilter).length > 0 ) {
                      service_url = 'activity/mydesk';
                      reqData['MyDesk'] = $scope.myDeskTabFilter;
                    }

                    if ( ContentSearchRequestPayload ) {
                      $scope.displayLoader();
                      $scope.isWallPostRequested = true;
                    }

                    if($('#module_id').val()=='1')
                    {
                      if($('#LandingPage').length>0 && $('#LandingPage').val()!=='')
                      {
                        var l_page = $('#LandingPage').val();
                        reqData['PostType'] = $scope.getDefaultPostValue(l_page);
                        $scope.PostType = reqData['PostType'];
                        $('#LandingPage').val('');
                      }

                      reqData['PostedBy'] = $('#postedby').val();
                    }

                    if(IsNewsFeed=='1' || IsAdminView=='1')
                    {
                      reqData['PostType'] = [];
                      $.each($scope.Filter.ShowMe,function(){
                           if(this.IsSelect)
                            {
                                if(this.Value!=0)
                                {
                                  reqData['PostType'].push(this.Value);  
                                }
                            }
                            
                        })
                    }

                    if(IsAdminView == '1')
                    {
                      reqData['DummyUsersOnly'] = 1;
                      showLoader();
                    }
                    WallService.CallApi(reqData, service_url).then(function (response) {
                        $scope.wallReqCnt++;
                        if ($scope.PageNo == 1) {
                            $scope.activityData = new Array();
                            $scope.tempActivityData = new Array();
                        }
                        if (response.ResponseCode == 200) {
                            angular.forEach(response.Data, function (val, key) {
                                response.Data[key].showNum = 0;
                                response.Data[key].stickynote = false;
                                response.Data[key].DisplayTomorrowDate = DisplayTomorrowDate;
                                response.Data[key].DisplayNextWeekDate = DisplayNextWeekDate;
                                response.Data[key].CollapsePostTitle = $scope.get_post_title(response.Data[key].PostTitle,response.Data[key].PostContent);
                                response.Data[key].ImageServerPath = image_server_path;
                                
                                if(IsAdminView == '1')
                                {
                                  var user_list_ctrl = angular.element(document.getElementById('UserListCtrl')).scope();
                                  angular.forEach(user_list_ctrl.users,function(v1,k1){
                                    if(val.PostAsModuleID==3 && val.UserGUID==v1.UserGUID)
                                    {
                                      response.Data[key].actionas = v1;
                                    }
                                  });
                                }

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
                            if($scope.PageNo=='1')
                            {
                                $scope.tr = response.TotalRecords;
                            }
                            $scope.tfr = response.TotalFavouriteRecords;
                            $scope.trr = response.TotalReminderRecords;
                            $scope.tflgr = response.TotalFlagRecords;
                            $scope.IsSinglePost = 0;
                            if ($scope.ActivityGUID) {
                                $scope.IsSinglePost = 1;
                            }
                            newData = response.Data;
                            var counts = 0;
                            if (newData.length > 0) {
                                $scope.activityData = $scope.activityData.concat(newData);
                            }
                            //console.log($scope.activityData);
                            /*if (response.Data.length > 0) {
                                $(window).scrollTop(parseInt($(window).scrollTop()) - 50);
                            }*/
                            setTimeout(function () {
                                if (!$scope.IsActiveFilter) {
                                    if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                        $('#FilterButton').show();
                                    } else {
                                        $('#FilterButton').hide();
                                    }
                                }
                            }, 1000);
                            // $scope.showLoader = 1;
                            var pNo = Math.ceil($scope.tr / response.PageSize);
                            if (pNo > $scope.PageNo) {
                                newPageNo = parseInt(response.PageNo) + 1;
                                $scope.WallPageNo = newPageNo;
                            } else {
                                $scope.stopExecution = 1;
                            }
                            $scope.busy = false;
                            setTimeout(function () {
                                taggedPerson();
                            }, 500);
                            angular.forEach($scope.activityData, function (val, key) {
                                if (val['Reminder'] && typeof val['Reminder'].ReminderGUID !== 'undefined') {
                                    $scope.activityData[key]['ReminderData'] = $scope.prepareReminderData(val['Reminder']);
                                }
                                $scope.activityData[key].ImageServerPath = image_server_path;
                            });
                            setTimeout(function () {
                                $('.comment-text').val('');
                                $('.wallloader').hide();
                                if (CommentGUID != '')
                                {
                                  if( $('#' + CommentGUID).offset() && $('#' + CommentGUID).offset().top ){
                                    $('html,body').animate({scrollTop: $('#' + CommentGUID).offset().top - 300}, 'slow');
                                  }
                                  $timeout(RemoveCommentClass, 2000);
                                }
                            }, 2000);
                            
                             $scope.activityData.map( function (repo) {
                                repo.SuggestedFriendList = [];
                                repo.RquestedFriendList = [];
                                repo.SearchFriendList = '';
                                return repo;
                              });
                            if($scope.IsFirstCall=='1' && IsNewsFeed=='1')
                            {
                                if(response.Data.length<3)
                                {
                                    $scope.get_popular_feeds();
                                }
                            }

                            if(ActivityGUID) {
                              $scope.toggleStickyPopup( 'open', 'activityHighlight');
                            }
                            $scope.IsFirstCall = 0; 
                            
                            //Activity Feed listing viewed.
                            if($scope.PageNo == 1)
                                logActivity();
                            
                        } else {
                          $scope.hideLoader();
                          $('.wallloader').hide();
                        }
                        $scope.hideLoader();
                        $('.wallloader').hide();
                        if(IsAdminView == '1')
                        {
                          hideLoader();
                        }
                        $scope.isWallPostRequested = false;
                        $scope.IsFirstMyDesk = false
                    }, function (error) {
                        // Error
                        $scope.hideLoader();
                        $('.wallloader').hide();
                        $scope.isWallPostRequested = false;
                        $scope.IsFirstMyDesk = false
                    });
                } else {
                    $('.wallloader').hide();
                }
            } else {
              $scope.hideLoader();
              $('.wallloader').hide();
            }
        }
        
        function logActivity() {
            var jsonData = {
                EntityType: 'Activity'
            };
            
            if(LoginSessionKey=='')
            {
                return false;
            }
            WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });
        }
        
        $scope.showLoginPopup = function()
        {
            if($('#beforeLogin').length>0)
            {
                showLoginPopup();
            }
        }

        $scope.toggle_collapse = function()
        {
          $('.collapse-content').removeAttr('style');
          var user_scope = angular.element(document.getElementById('UserProfileCtrl')).scope();
          if(user_scope.config_detail.IsCollapse=='1')
          {
           user_scope.config_detail.IsCollapse='0';
          }
          else
          {
            user_scope.config_detail.IsCollapse='1';
          }
          var reqData = {IsCollapse:user_scope.config_detail.IsCollapse};
          WallService.CallPostApi(appInfo.serviceUrl + 'users/update_collapse', reqData, function (successResp) {
          });
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


        $scope.getTemplateUrl = function (data,is_popular) {
            var partialURL = base_url + 'assets/partials/wall/';
            var ViewTemplate = data.ViewTemplate;
            var ShowPoll = 0;
            if (typeof data.PollData !== 'undefined') {
                if (data.PollData.length > 0) {
                    ShowPoll = 1;
                }
            }
            //console.log(ViewTemplate);
            if (ViewTemplate == 'SuggestedGroups' || ViewTemplate == 'SuggestedPages' || ViewTemplate == 'UpcomingEvents') {
                return partialURL + 'activity/' + ViewTemplate + '.html?v=4.8';
            } else if (ViewTemplate == 'Poll' || ShowPoll == '1') {
                return partialURL + 'PollMain.html?v=4.8';
            } else {
                if(is_popular=='1')
                {
                    return partialURL + 'PopularFeed.html?v=4.8';
                }
                else
                {
                    return partialURL + 'NewsFeed.html?v=4.8';
                }
            }
        };

        $scope.isDiscussionPost = 0;
        
        $scope.get_popular_post = function()
        {   
            $scope.isDiscussionPost = 1;
            var reqData = {Limit:2,ModuleID:1};
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/get_popular_feeds', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.activityData = response.Data;
                    $scope.LoggedInName = response.LoggedInName;
                    $scope.LoggedInProfilePicture = response.LoggedInProfilePicture;
                    $scope.activityData.map( function (repo) {
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
                $('.inview').each(function (k, v) {
                    if ($('.inview:eq(' + k + ')').isOnScreen()) {
                        var EntityGUID = $('.inview:eq(' + k + ')').attr('id');
                        EntityGUID = EntityGUID.split('act-')[1];
                        $scope.showMediaFigure(EntityGUID);
                    }
                });
            }, 1000);
        }
        $scope.showMediaFigure = function (EntityGUID) {
            var data = [];
            data['showMedia'] = 1;
            $scope.updateActivityData(EntityGUID, data);
            if(IsNewsFeed=='1')
            {
              $scope.updatePopularData(EntityGUID, data);
            }
            if (!$scope.$$phase) {
              $scope.$apply();
            }
        }
        $scope.updateActivityData = function (activity_guid, data) {
            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == activity_guid) {
                    for (k in data) {
                        $scope.activityData[key][k] = data[k];
                    }
                }
            });
        }

        $scope.updatePopularData = function(activity_guid, data) {
            angular.forEach($scope.popularData, function(val, key) {
                if (val.ActivityGUID == activity_guid) {
                    for (k in data) {
                        $scope.popularData[key][k] = data[k];
                    }
                }
            });
        }
        $scope.enableDisableMin = function(data, activity_guid) {
            setTimeout(function() {

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
            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == activity_guid) {
                    if (field) {
                        data = $scope.activityData[key][field];
                    } else {
                        data = $scope.activityData[key];
                    }
                }
            });
            return data;
        }

        $scope.getPopularData = function(activity_guid, field) {
            var data;
            angular.forEach($scope.popularData, function(val, key) {
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
        $scope.prepareReminderData = function(ReminderData, IsLocal) {

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
                    file.$errorMessages = file.name + ' is too large.';
                    defer.resolve(false);
                    isResolvedToFalse = true;
                }
            } else {
                if (file.size > 4194304) { // if image/document size > 4194304 Bytes = 4 Mb
                    file.$error = 'size';
                    file.$error = 'Size Error';
                    file.$errorMessages = file.name + ' is too large.';
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
        $scope.uploadWallFiles = function (files, errFiles) {
//          $scope.errFiles = errFiles;
            var promises = [];
            if (!(errFiles.length > 0)) {
                var patt = new RegExp("^image|audio|video");
                var videoPatt = new RegExp("^video");
                $scope.isWallAttachementUploading = true;
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, mediaIndex, fileIndex) {
                        var paramsToBeSent = {
                            Type: 'wall',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        var fileType = 'media';
                        if (patt.test(file.type)) {
                            $scope.medias['media-' + mediaIndex] = file;
                            $scope.mediaCount = Object.keys($scope.medias).length+Object.keys($scope.edit_medias).length;
                        } else {
                            $scope.files['file-' + fileIndex] = file;
                            $scope.fileCount = Object.keys($scope.files).length+Object.keys($scope.edit_files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';

                        }

                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        if(IsAdminView == '1')
                        {
                          url = (videoPatt.test(file.type)) ? 'adminupload_video' : 'adminupload_image';
                        }
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.mediaInputIndex = 'ALL';
                                            $scope.medias['media-' + mediaIndex].progress = true;
                                        } else {
                                            delete $scope.medias['media-' + mediaIndex];
                                            $scope.mediaCount = Object.keys($scope.medias).length+Object.keys($scope.edit_medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.files['file-' + fileIndex].progress = true;
                                        } else {
                                            delete $scope.files['file-' + fileIndex];
                                            $scope.fileCount = Object.keys($scope.files).length+Object.keys($scope.edit_files).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }
                                    // $timeout(function () {
                                    //     file.result = response;
                                    // });
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

        $scope.setSaySomethingAboutMedia = function (index) {
            if (!$scope.saySomthingAboutMedia[index]) {
                $scope.saySomthingAboutMedia[index] = '';
            }
            $scope.mediaInputIndex = index;
//          angular.element('#mc-default').focus();
        };

        $scope.removeWallAttachement = function (type, mediaKey, MediaGUID) {
          var PostContent = $('#PostContent').val().trim();
            if ((type === 'file') && Object.keys($scope.files).length) {
                delete $scope.files[mediaKey];
                $scope.fileCount = Object.keys($scope.files).length+Object.keys($scope.edit_files).length;
            }
            else if((type === 'edit_file') && Object.keys($scope.edit_files).length)
            {
              delete $scope.edit_files[mediaKey];
              $scope.fileCount = Object.keys($scope.files).length+Object.keys($scope.edit_files).length;
            }
            else if((type === 'media') && Object.keys($scope.medias).length)
            {
              var mediaLength = Object.keys($scope.medias).length+Object.keys($scope.edit_medias).length;
                delete $scope.saySomthingAboutMedia[MediaGUID];
                if (mediaLength < 2) {
                    $scope.saySomthingAboutMedia['ALL'] = '';
                }
                delete $scope.medias[mediaKey];
                $scope.mediaCount = Object.keys($scope.medias).length;
                if (mediaLength) {
                    var lastKey = $scope.medias[Object.keys($scope.medias)[mediaLength - 1]] // "carrot"
                    $scope.mediaInputIndex = ($scope.medias[lastKey]) ? $scope.medias[lastKey].data.MediaGUID : 'ALL';
                }
            }
             else if (Object.keys($scope.edit_medias).length) {
                var mediaLength = Object.keys($scope.edit_medias).length;
                delete $scope.saySomthingAboutMedia[MediaGUID];
                if (mediaLength < 2) {
                    $scope.saySomthingAboutMedia['ALL'] = '';
                }
                angular.forEach($scope.edit_medias,function(val,key){
                  if(val.data.MediaGUID == MediaGUID)
                  {
                    $scope.edit_medias.splice(key,1);
                  }
                });
                $scope.mediaCount = Object.keys($scope.medias).length+Object.keys($scope.edit_medias).length;
                if (mediaLength) {
                    var lastKey = $scope.edit_medias[Object.keys($scope.edit_medias)[mediaLength - 1]] // "carrot"
                    $scope.mediaInputIndex = ($scope.edit_medias[lastKey]) ? $scope.edit_medias[lastKey].data.MediaGUID : 'ALL';
                }
             }

            
            if ( ( $scope.fileCount === 0) && ( $scope.mediaCount === 0 ) && !PostContent ) {
                $scope.noContentToPost = true;
            }
        };

//        wall files upload 
        
        $scope.mark_best_answer = function(activity_guid,comment_guid)
        {
          var reqData = {ActivityGUID:activity_guid,CommentGUID:comment_guid};
          WallService.CallApi(reqData, 'activity/mark_best_answer').then(function (response) {
            if (response.ResponseCode == 200) {
              angular.forEach($scope.activityData,function(val,key){
                if(val.ActivityGUID == activity_guid)
                {
                  angular.forEach($scope.activityData[key].Comments,function(v,k){
                    if(v.CommentGUID == comment_guid)
                    {
                      $scope.activityData[key].Comments[k].BestAnswer = 1;
                    }
                    else
                    {
                      $scope.activityData[key].Comments[k].BestAnswer = 0;
                    }
                  });
                }
              });
            }
          });
        }

        $scope.editPostAnn = function(activity_guid,event)
        {

          $scope.overlayShow = true;
          $scope.edit_post = true;
          //var postContent = $(".note-editable").text().trim();
          $scope.getSingleActivityAnn(activity_guid);
          var post_details = angular.copy($scope.singleActivity);
          $scope.showPostEditor();
          var topreduce = 160;
          if($('#IsForum').length>0)
          {
            var topreduce = 160+parseInt($('#CategoryDetails').height());
          }
          var top = $('.feed-act-'+activity_guid).offset().top-topreduce;
          setTimeout(function(){
            if(post_details.PostContent!=='')
            {
              $(".note-placeholder").hide();
            }
            $(".note-editable").html(post_details.EditPostContent);
            $scope.PostContent = post_details.EditPostContent;
            $('#PostTitleInput').val(post_details.PostTitle);
           

            $('.post-editor').css('top',top+'px');
            $('.post-preview').css('top',top+'px');
            //$('.post-type-block').css('top',parseInt(top)+40+'px');
            angular.forEach(post_details.EntityMembers,function(val,key){
              if(val.UserGUID!==LoggedInUserGUID)
              {
                val['name'] = val.FirstName+' '+val.LastName;
                $scope.tagsto.push(val);
                $scope.group_user_tags.push(val);
              }
            });

            $scope.edit_medias = [];
            $scope.edit_files = [];
            $scope.mediaCount = 0;
            $scope.fileCount = 0;

            angular.forEach(post_details.mediaData,function(val,key){
              val['data'] = post_details.mediaData[key];
              val['data']['ImageServerPath'] = image_server_path+'upload/wall';
              val['progress'] = true;
              if(val['data'].MediaType == 'Image')
              {
                val['data'].MediaType = 'PHOTO'; 
              }
              $scope.edit_medias.push(val);
            });
            angular.forEach(post_details.Files,function(val,key){
              val['data'] = post_details.Files[key];
              val['progress'] = true;
              $scope.edit_files.push(val);
            });

            $scope.mediaCount = $scope.edit_medias.length;
            $scope.fileCount = $scope.edit_files.length;
            $('#EditActivityGUID').val(activity_guid);
            $scope.postTagList = post_details.editTags;

            if (post_details.PostContent  || $scope.fileCount>0 || $scope.mediaCount){
                $scope.noContentToPost = false;
            } else {
                $scope.noContentToPost = true;
            }
            $scope.activePostType = post_details.PostType;
            if(!$scope.$$phase)
            {
              $scope.$apply();
            }
          },100);
        
        }

        $scope.setpostasgroup = function(val)
        {
          $scope.postasgroup = val;
          $scope.PostAs = val;
        }

        $scope.toggle_comment_allowed = function()
        {
          if($scope.edit_post_details.CommentsAllowed=='1')
          {
            $scope.edit_post_details.CommentsAllowed = '0'; 
          }
          else
          {
            $scope.edit_post_details.CommentsAllowed = '1';
          }
        }

        $scope.change_visibility_settings = function(val)
        {
          $scope.edit_post_details.Visibility = val;
        }

        $scope.postasgroup = [];
        $scope.postInGroup = false;
        $scope.edit_post_details = [];
        $scope.editPost = function(activity_guid,event)
        {
          $scope.resetPrivacySettings();
          $scope.overlayShow = true;
          $scope.edit_post = true;
          //var postContent = $(".note-editable").text().trim();
          $scope.getSingleActivity(activity_guid);
          var post_details = angular.copy($scope.singleActivity);
          $scope.edit_post_details = post_details;
          console.log('post_details : ', post_details);
          if(IsAdminView == '1')
          {
            var user_scope_ctrl = angular.element(document.getElementById('UserListCtrl')).scope();
            angular.forEach(user_scope_ctrl.users,function(val,key){
              if(val.UserGUID == post_details.UserGUID)
              {
                $scope.setpostasuser(val);
              }
            });
          }

          if($scope.edit_post_details.Album.length>0)
          {
            angular.forEach($scope.edit_post_details.Album[0].Media,function(val,key){
              $scope.saySomthingAboutMedia[val.MediaGUID] = val.Caption;
            });
          }

          $scope.showPostEditor();
          var topreduce = 160;
          if($('#IsForum').length>0)
          {
            var topreduce = 160+parseInt($('#CategoryDetails').height());
          }
          var top = $('.feed-act-'+activity_guid).offset().top-topreduce;
          setTimeout(function(){
            if(post_details.PostContent!=='')
            {
              $(".note-placeholder").hide();
            }
            $(".note-editable").html(post_details.EditPostContent);
            $scope.PostContent = post_details.EditPostContent;
            $('#PostTitleInput').val(post_details.PostTitle);
            var char = ( post_details.PostTitle ) ? 140-post_details.PostTitle.length : 140;
            if(char == 1)
            {
              $('#PostTitleLimit').html('1 character');
            }
            else
            {
              $('#PostTitleLimit').html(char+' characters');
            }

            $('.post-editor').css('top',top+'px');
            $('.post-preview').css('top',top+'px');
            //$('.post-type-block').css('top',parseInt(top)+40+'px');
            angular.forEach(post_details.EntityMembers,function(val,key){
              if(val.ModuleEntityGUID!==LoggedInUserGUID)
              {
                val['name'] = val.FirstName+' '+val.LastName;
                $scope.tagsto.push(val);
                $scope.group_user_tags.push(val);
              }
            });

            $scope.edit_medias = [];
            $scope.edit_files = [];
            $scope.mediaCount = 0;
            $scope.fileCount = 0;

            angular.forEach(post_details.mediaData,function(val,key){
              val['data'] = post_details.mediaData[key];
              val['data']['ImageServerPath'] = image_server_path+'upload/wall';
              val['progress'] = true;
              if(val['data'].MediaType == 'Image')
              {
                val['data'].MediaType = 'PHOTO'; 
              }
              $scope.edit_medias.push(val);
            });
            angular.forEach(post_details.Files,function(val,key){
              val['data'] = post_details.Files[key];
              val['progress'] = true;
              $scope.edit_files.push(val);
            });

            $scope.mediaCount = $scope.edit_medias.length;
            $scope.fileCount = $scope.edit_files.length;
            $('#EditActivityGUID').val(activity_guid);
            $scope.postTagList = post_details.editTags;

            if (post_details.PostContent  || $scope.fileCount>0 || $scope.mediaCount){
                $scope.noContentToPost = false;
            } else {
                $scope.noContentToPost = true;
            }
            $scope.activePostType = post_details.PostType;
            $scope.postInGroup = ( post_details.ModuleID == 1 ) ? true : false;
            $scope.selectedPrivacy = post_details.Visibility;
            angular.element('#visible_for').val(post_details.Visibility);
            $scope.parseTaggedInfo();
            if(!$scope.$$phase)
            {
              $scope.$apply();
            }
          },100);
        }

        /*$scope.$watch('PostContent',function(a,b){
          //console.log(a);
         // console.log(b);
        },true);*/

        $scope.SubmitWallpost = function () {
          
          if(IsAdminView == '1')
          {
            var user_list_scope = angular.element(document.getElementById('UserListCtrl')).scope().users;
            angular.forEach(user_list_scope,function(val,key){
              if(val.UserID == $('#postasuserid').val())
              {
                $scope.postasuser = val;
              }
            });
          }

//          console.log($scope.postTagList); return false;
            //var PostContent = $('#PostContent').val().trim();

            if(IsAdminView == '1')
            {
              if(typeof $scope.postasuser.UserID=='undefined')
              {
                ShowErrorMsg('Please select user');
                return false;
              }
            }

            var PostContent = $.trim($scope.PostContent);
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
                $scope.AllActivity = 0;
                if ($('#AllActivity').length > 0) {
                    $scope.AllActivity = $('#AllActivity').val();
                }
                jsonData['AllActivity'] = $scope.AllActivity;
                jsonData['Members'] = $scope.check_group_members();
                jsonData['NotifyAll'] = $scope.NotifyAll;
                if(parseInt(jsonData['ModuleID'])==34 && $('#PostTitleInput').val()=='')
                {
                  showResponseMessage('Post title field is required for this type of post.','alert-danger');
                  return false;
                }
                if(parseInt($scope.activePostType)!==1 && $('#PostTitleInput').val()=='')
                {
                  showResponseMessage('Post title field is required for this type of post.','alert-danger');
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
                        if(v.HideThumb)
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

                if(typeof $scope.PostAs!=='undefined')
                {
                  if("ModuleID" in $scope.PostAs) {
                      jsonData['PostAsModuleID'] = $scope.PostAs.ModuleID;
                  }
                  if("ModuleEntityGUID" in $scope.PostAs) {
                      jsonData['PostAsModuleEntityGUID'] = $scope.PostAs.ModuleEntityGUID;
                  }
                }

                jsonData['Status'] = 2;
                if($scope.is_draft == '1')
                {
                  jsonData['Status'] = 10;
                  $scope.is_draft = 0;
                }

                jsonData['ActivityGUID'] = $('#EditActivityGUID').val();

                //            console.log(jsonData);
                showButtonLoader('ShareButton');

                if($('#IsWiki').length>0)
                {
                  jsonData['PostType'] = 4;
                }

                if(IsAdminView == '1')
                {
                  jsonData['UserID'] = $scope.postasuser.UserID;
                  jsonData['PostAsModuleID'] = 3;
                  jsonData['PostAsModuleEntityGUID'] = $scope.postasuser.UserGUID;
                  if(typeof $('#PostAsGroupModuleID').val()!=='undefined' && typeof $('#PostAsGroupModuleEntityID').val()!=='undefined')
                  {
                    if($('#PostAsGroupModuleID').val()!=='' && $('#PostAsGroupModuleEntityID').val()!=='')
                    {
                      jsonData['ModuleID'] = $('#PostAsGroupModuleID').val();
                      jsonData['ModuleEntityGUID'] = $('#PostAsGroupModuleEntityID').val();
                    }
                  }
                  if(!$scope.postasuser)
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

                WallService.CallApi(jsonData, wallposturl).then(function (response) { 
                    $scope.allreadyProcessedLinks = [];
                    $scope.titleKeyup = 0;
                    if (response.ResponseCode == 200) {

                      if(angular.element('#ForumCtrl').length>0){
                        forumScope = angular.element('#ForumCtrl').scope(); 
                        forumScope.category_detail.NoOfDiscussions = parseInt(forumScope.category_detail.NoOfDiscussions)+1;
                      }
                       
                        if(IsAdminView == '1')
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
                        if(act_guid!=='')
                        {
                          angular.forEach($scope.activityData,function(a,b){
                            if(a.ActivityGUID==act_guid)
                            {
                              $scope.activityData.splice(b,1);
                            }
                          });
                          $('#EditActivityGUID').val('');
                          $scope.edit_post = false;
                        }
                        $scope.resetFormPost(); 
                       // $('#PostContent-stop,#PostContent').textntags('reset');
                        $scope.PostContent='';
                        $('#PostContent').val('');
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
                        
                        response.Data.map( function (repo) {
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
                        response.Data[0].CollapsePostTitle = $scope.get_post_title(response.Data[0].PostTitle,response.Data[0].PostContent);
                        if( response.Data[0]['EntityTags'] && ( response.Data[0]['EntityTags'].length > 0 ) ){
                          response.Data[0]['editTags'] = angular.copy( response.Data[0]['EntityTags'] );
                          response.Data[0]['showTags'] = false;
                        } else {
                          response.Data[0]['EntityTags'] = [];
                          response.Data[0]['editTags'] = [];
                          response.Data[0]['showTags'] = false;
                        }
                        
                        if(IsAdminView == '1')
                        {
                          var user_list_ctrl = angular.element(document.getElementById('UserListCtrl')).scope();
                          angular.forEach(user_list_ctrl.users,function(v1,k1){
                            if(response.Data[0].PostAsModuleID==3 && response.Data[0].UserGUID==v1.UserGUID)
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
                        if($('#IsWiki').length>0)
                        {
                          $scope.article_list.unshift(response.Data[0]);
                          $('#addWiki').modal('hide');
                        } else if(response.Data[0]['PostType'] == '7')
                        {
                          $scope.get_announcements();
                        }
                        else
                        {
                          if ($scope.activityData.length > 0) {
                              $($scope.activityData).each(function (k, v) {
                                  if ($scope.activityData[k].IsSticky == 0) {
                                      $scope.activityData.splice(k, 0, response.Data[0]);
                                      $('#ShareButton').removeClass('loader-btn');
                                      return false;
                                  }
                              });
                          } else {
                              $scope.activityData.push(response.Data[0]);
                          }
                        }
                        
                        if(IsAdminView == '1' && typeof IsAdminDashboard=='undefined')
                        {
                          $scope.GetwallPost();
                        }

                        $scope.tr++;
                        setTimeout(function () {
                            if (!$scope.IsActiveFilter) {
                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                    $('#FilterButton').show();
                                } else {
                                    $('#FilterButton').hide();
                                }
                            }
                        }, 2000);
                        /*setTimeout(function(){
                            $('#cmt-div-'+response.Data[0].ActivityGUID+' .place-holder-label').show();
                            $('#cmt-div-'+response.Data[0].ActivityGUID+' .comment-section').addClass('hide');
                        },500)*/
                        //$('#cmt-div-'+response.Data[0].ActivityGUID+' .place-holder-label').show();
                        $scope.show_comment_box = "";
                        $('#multipleInstantGroupModal').modal('hide');
                        
                        $scope.parseLinkData('',0);
                        
                        $('#ShareButton').removeClass('loader-btn');

                        $('#PostTitleInput').val('');
                        if(!$scope.$$phase)
                        {
                          $('#PostTitleInput').keyup();
                        }
                        $scope.override_post_permission = [];
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
                    $scope.resetPrivacySettings();
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

        $scope.pin_to_top = function(activity_guid)
        {
          var reqData = {EntityGUID:activity_guid};
          WallService.CallApi(reqData, 'activity/pin_to_top').then(function (response) {
            if (response.ResponseCode == 200) {
              angular.forEach($scope.activityData,function(val,key){
                if(val.ActivityGUID == activity_guid)
                {
                  $scope.activityData[key].IsPined = 1;
                }
              });
              $scope.get_announcements();
            }
          });
        }

        $scope.reset_post_type = function()
        {
          var reset = true;
          setTimeout(function(){
            if($scope.override_post_permission.length>0)
            {
                angular.forEach($scope.override_post_permission,function(val,key){
                    if(val.Value == $scope.activePostType)
                    {
                        reset = false;
                    }
                });
            }
            else
            {
              if(IsAdminView == '1')
              {
                if($scope.postasuser.ContentTypes.length>0 && $scope.group_user_tags.length == 0)
                {
                    angular.forEach($scope.postasuser.ContentTypes,function(val,key){
                        if(val.Value == $scope.activePostType)
                        {
                            reset = false;
                        }
                    });
                }
              }
              else if($scope.ContentTypes.length>0 && $scope.group_user_tags.length == 0)
              {
                  angular.forEach($scope.ContentTypes,function(val,key){
                      if(val.Value == $scope.activePostType)
                      {
                          reset = false;
                      }
                  });
              }
            }
              
            if(reset)
            {
                if($scope.override_post_permission.length>0)
                {
                    $scope.updateActivePostType($scope.override_post_permission[0].Value);
                    if(!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                }
                else
                {
                    $scope.updateActivePostType(1);
                    if(!$scope.$$phase)
                    {
                        $scope.$apply();
                    }
                }
            }
          },500);
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
            if(IsNewsFeed=='1')
            {
              $scope.updatePopularData(activity_guid, data);
            }
        }
        $scope.getTitleMessage = function (data) {
            var msz = data.Message;
            var EntityURL = base_url;
            var UserURL = base_url + data.UserProfileURL;
            var shareType = 'Post';
            var PhotoMediaGUID = '';
            if (data.Album.length > 0) {
                PhotoMediaGUID = data.Album[0].Media[0].MediaGUID;
                shareType = 'Photo';
            }
            var ActivityOwnerLink = base_url;

            if (data.ModuleID == 1) {
                EntityURL += 'group/' + data.EntityProfileURL;
            } else if (data.ModuleID == 3) {
                EntityURL += data.EntityProfileURL;
                ActivityOwnerLink += data.ActivityOwnerLink;
            } else if (data.ModuleID == 14) {
                EntityURL += 'events/' + data.EntityProfileURL + '/wall';
            } else if (data.ModuleID == 18) {
                EntityURL += 'page/' + data.EntityProfileURL;
            }
            if (typeof msz !== 'undefined') {
                if(data.ActivityType == 'GroupPostAdded' && data.PostType == '7')
                {
                  if(data.EntityName == "")
                  {
                       str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getMembersHTML(data.EntityMembers, data.EntityMembersCount, 1,data.EntityProfileURL,1) + '</a>');
                  }
                  else
                  {
                    str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getHighlighted(data.EntityName) + '</a>');
                  }
                }
                else if ((data.ActivityType == 'AlbumAdded' || data.ActivityType == 'AlbumUpdated') && data.ModuleEntityOwner == '1' && data.ModuleID == '18')
                {
                    str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getHighlighted(data.UserName) + '</a>');
                } else
                {
                    str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + $scope.getHighlighted(data.UserName) + '</a>');
                }
                str = str.replace("{{SUBJECT}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + $scope.getHighlighted(
                        data.UserName) + '</a>');
                str = str.replace("{{ACTIVITYOWNER}}",'<a class="" target="_self" href="' + base_url+data.ActivityOwnerLink + '">' + $scope.getHighlighted(
                        data.ActivityOwner) + '</a>')
            } else {
                str = '';
            }
            switch (data.ActivityType) {
                case 'ProfilePicUpdated':
                case 'ProfileCoverUpdated':
                    if (data.ModuleID == 1) {
                        str = msz.replace("{{EntityName}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="group/' + data.EntityProfileURL + '">' + $scope.getHighlighted(data.EntityName) + '</a>\'s');
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + data.UserProfileURL +
                                '">' + $scope.getHighlighted(data.UserName) + '</a>');
                    }
                    if (data.ModuleID == 3) {
                        str = msz.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                $scope.getHighlighted(data.UserName) + '</a>');
                        str = str.replace("{{EntityName}}", 'their');
                    }
                    if (data.ModuleID == 14) {
                        str = msz.replace("{{EntityName}}", '<a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL +
                                '">' + $scope.getHighlighted(data.EntityName) + '</a>\'s');
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + data.UserProfileURL +
                                '">' + $scope.getHighlighted(data.UserName) + '</a>');
                    }
                    if (data.ModuleID == 18) {
                        str = msz.replace("{{EntityName}}", 'their');
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                $scope.getHighlighted(data.EntityName) + '</a>');
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
                            .ProfileURL + '">' + $scope.getHighlighted(data.RatingData.CreatedBy.EntityName) + '</a>');
                    str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getHighlighted(
                            data.EntityName) + '</a>');
                    break;
                case 'PollCreated':
                    if (data.ModuleID == 18) {
                        entitytype = 'page';
                    } else {
                        entitytype = 'user';
                    }
                    str = msz.replace("{{User}}", '<a target="_self" class="loadbusinesscard" entitytype="' + entitytype + '" entityguid="' + data.EntityGUID + '" href="' + EntityURL +
                            '">' + $scope.getHighlighted(data.EntityName) + '</a>');
                    str = str.replace("{{Entity}}", '<a target="_self"  href="' + EntityURL + '/activity/' + data.ActivityGUID + '">' + $scope.getHighlighted(data.ViewTemplate) + '</a>');
                    break;
                case 'AlbumAdded':
                    //str = str.replace("{{Entity}}", '<a href="javascript:void(0);">'+$scope.getHighlighted(data.EntityName)+'</a>');
                    if (typeof data.Album[0] !== 'undefined') {
                        str = str.replace("{{Entity}}", '<a href="' + site_url + data.Album[0].AlbumProfileURL + '/' + data.Album[0].AlbumGUID + '">' + $scope.getHighlighted(data.EntityName) +
                                '</a>');
                    } else {
                        str = str.replace("{{Entity}}", '');
                    }
                    if (data.ModuleID !== '3' && data.AlbumEntityName) {
                        //console.log('hi');
                        if(data.ModuleID == '1')
                        {
                            str += ' in ' + '<a href="' + site_url + 'group/' + data.EntityProfileURL + '">' + $scope.getHighlighted(data.AlbumEntityName) + '</a>'
                        }
                        else if(data.ModuleID == '18')
                        {
                            str += ' in ' + '<a href="' + site_url + 'page/'+data.EntityProfileURL+ '">' + $scope.getHighlighted(data.AlbumEntityName) + '</a>'    
                        }
                        else if(data.ModuleID == '14')
                        {
                            str += ' in ' + '<a href="' + site_url + 'events/' + data.EntityGUID + '">' + $scope.getHighlighted(data.AlbumEntityName) + '</a>'
                        }
                    }
                    break;
                case 'AlbumUpdated':
                    if (typeof data.Album[0] !== 'undefined') {
                        str = str.replace("{{Entity}}", '<a href="' + site_url + data.Album[0].AlbumProfileURL + '/' + data.Album[0].AlbumGUID + '">' + $scope.getHighlighted(data.EntityName) +
                                '</a>');
                        str = str.replace("{{AlbumType}}", 'Media');
                        str = str.replace("{{count}}", $scope.getHighlighted(data.Params.count));
                    } else {
                        str = str.replace("{{Entity}}", '');
                        str = str.replace("{{AlbumType}}", '');
                        str = str.replace("{{count}}", '');
                    }
                    if (data.ModuleID !== '3' && data.AlbumEntityName) {
                        if(data.ModuleID == '1')
                        {
                            str += ' in ' + '<a href="' + site_url + 'group/' + data.EntityProfileURL + '">' + $scope.getHighlighted(data.AlbumEntityName) + '</a>'
                        }
                        else if(data.ModuleID == '18')
                        {
                            str += ' in ' + '<a href="' + site_url + 'page/'+data.EntityProfileURL+'">' + $scope.getHighlighted(data.AlbumEntityName) + '</a>'    
                        }
                        else if(data.ModuleID == '14')
                        {
                            str += ' in ' + '<a href="' + site_url + 'events/' + data.EntityGUID + '">' + $scope.getHighlighted(data.AlbumEntityName) + '</a>'
                        }
                        
                    }
                    break;
                case 'GroupJoined':
                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + $scope
                            .getHighlighted(data.EntityName) + '</a>')
                    $scope.postCtrl = false; // Post Control
                    break;
                case 'GroupPostAdded':
                    if ($('#module_id').val() !== '1') {
                        
                        if(data.PostType !== '7')
                        {
                          if (data.EntityName !== '') {
                              str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + $scope.getHighlighted(data.UserName) +
                                      '</a> posted in <a target="_self" class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" href="' + EntityURL + '">' + $scope.getHighlighted(
                                              data.EntityName) + '</a>';
                          } else {
                              str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + $scope.getHighlighted(data.UserName) +

                                      '</a> posted in ' + $scope.getMembersHTML(data.EntityMembers, data.EntityMembersCount, 1,data.EntityProfileURL,1);
                          }
                        }

                    }
                    break;
                case 'ForumPost':
                    /*if ($('#module_id').val() !== '34') {*/
                        
                          str = str.replace("{{Entity}}", '<a target="_self" href="' + data.EntityProfileURL + '">' +
                                $scope.getHighlighted(data.EntityName) + '</a>');

                    /*}*/
                    break;
                case 'EventWallPost':
                    if ($('#module_id').val() !== '14') {
                        str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + $scope.getHighlighted(data.UserName) +
                                '</a> posted in <a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getHighlighted(
                                        data.EntityName) + '</a>';
                    }
                    break;
                case 'PagePost':
                    if (msz == "{{User}}") {
                        if (data.ModuleEntityOwner == 1) {
                            str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                    $scope.getHighlighted(data.EntityName) + '</a>');
                        } else {
                            str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' +
                                    $scope.getHighlighted(data.EntityName) + '</a>');
                        }
                    } else {
                        if (data.ModuleEntityOwner == 1) {
                            str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.UserGUID + '" target="_self" href="page/' + data.UserProfileURL + '">' +
                                    $scope.getHighlighted(data.UserName) + '</a>');
                        }
                        str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                $scope.getHighlighted(data.EntityName) + '</a>');
                    }
                    break;
                case 'Follow':
                case 'FriendAdded':
                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getHighlighted(
                            data.EntityName) + '</a>');
                    $scope.postCtrl = false; // Post Control
                    break;
                case 'Share':
                case 'ShareMedia':
                    if (data.SharedActivityModule == 'Users') {
                        data.SharedActivityModule = 'user';
                    }
                    if (shareType == 'Photo') {
                        str = str.replace("{{ENTITYTYPE}}", '<a class="loadbusinesscard" entitytype="' + data.SharedActivityModule + '" entityguid="' + data.SharedEntityGUID +
                                '" onclick="callpopup(\'' + PhotoMediaGUID + '\');" href="javascript:void(0);">' + $scope.getHighlighted(shareType) + '</a>');
                    } else {
                        str = str.replace("{{ENTITYTYPE}}", '<a  target="_self" href="' + base_url+data.UserProfileURL + '/activity/' + data.ActivityGUID + '">' + $scope.getHighlighted(
                                shareType) + '</a>');
                    }
                    str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="' + data.SharedActivityModule + '" entityguid="' + data.EntityGUID + '" target="_self" href="' +
                            EntityURL + '">' + $scope.getHighlighted(data.EntityName) + '</a>');
                    if (data.IsOwner == 1) {
                        $scope.postCtrl = true; // Post Control
                    } else {
                        $scope.postCtrl = false;
                    }
                    break;
                case 'ShareSelf':
                case 'ShareMediaSelf':
                    if (data.SharedActivityModule == 'Users') {
                        data.SharedActivityModule = 'user';
                    }
                    if (data.EntityType == 'Photo') {
                        str = str.replace("{{ENTITYTYPE}}", '<a onclick="callpopup(\'' + PhotoMediaGUID + '\');" href="javascript:void(0);">' + $scope.getHighlighted(data.EntityType) +
                                '</a>');
                    } else {
                        str = str.replace("{{ENTITYTYPE}}", '<a target="_self" href="' + base_url+data.UserProfileURL + '/activity/' + data.ActivityGUID + '">' + $scope.getHighlighted(data.EntityType) +
                                '</a>');
                    }
                    if(data.SharedActivityModule == 'Page' || data.SharedActivityModule == 'Group' || data.SharedActivityModule == 'Forum Category' || data.SharedActivityModule == 'Event' || data.SharedActivityModule == 'Polls')
                    {
                        str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.OriginalActivityUserGUID +
                            '" target="_self" href="' + ActivityOwnerLink + '">' + $scope.getHighlighted(data.OriginalActivityFirstName+' '+data.OriginalActivityLastName) + '</a>'); 
                    }
                    else
                    {
                        str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="' + data.SharedActivityModule + '" entityguid="' + data.SharedEntityGUID +
                            '" target="_self" href="' + ActivityOwnerLink + '">' + $scope.getHighlighted(data.ActivityOwner) + '</a>');
                    }
                    if (data.IsOwner == 1) {
                        $scope.postCtrl = true; // Post Control
                    } else {
                        $scope.postCtrl = false;
                    }
                    break;
                case 'Post':
                    str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + $scope.getHighlighted(
                            data.EntityName) + '</a>');
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
            if ( $('#BtnSrch i').hasClass('icon-removeclose') || advancedSearchKeyword) {

                if ( !advancedSearchKeyword ) {
                  advancedSearchKeyword = $('#srch-filters').val();
                }

                if (typeof str === 'undefined') {
                    str = '';
                }
                if (str.length > 0 && advancedSearchKeyword.length > 0) {
                    str = str.replace(new RegExp(advancedSearchKeyword, 'gi'), "<span class='highlightedText'>$&</span>");
                }
                return str;
            } else {
                return str;
            }
        }

        $scope.get_img_class = function(media,is_preview)
        {
          if(is_preview == '1')
          {
            media = ObjectToArray(media);
          }
          var class_name  = '';
          if(typeof media!=='undefined')
          {
            if(media.length == 1)
            {
              class_name  = 'single-image';
            }
            else if(media.length == 2)
            {
              class_name  = 'two-images';
            }
            else if(media.length == 3)
            {
              class_name  = 'three-images';
            }
            else if(media.length > 3)
            {
              class_name  = 'four-images';
            }
          }
          return class_name;
        }

        $scope.getTimeFromDate = function (CreatedDate) {
            return moment(CreatedDate).format('dddd, MMM D YYYY hh:mm A');
        }
        $scope.UTCtoTimeZone = function (date) {
            var localTime = moment.utc(date).toDate();
            return moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
        }
        $scope.date_format = function (date) {
            return GlobalService.date_format(date);
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
                    if(IsNewsFeed=='1')
                    {
                      $scope.updatePopularData(ActivityGuID, data);
                    }
                }
            });
        }

        $scope.addToChecked = function(CategoryID)
        {
            angular.forEach($scope.non_loggedin_interest,function(val,key){
                if(val.CategoryID == CategoryID)
                {
                    $scope.non_loggedin_interest_checked.unshift(val);
                    $scope.non_loggedin_interest.splice(key,1);
                    return;
                }
            });
        }

        $scope.addToNonChecked = function(CategoryID)
        {
            angular.forEach($scope.non_loggedin_interest_checked,function(val,key){
                if(val.CategoryID == CategoryID)
                {
                    $scope.non_loggedin_interest.unshift(val);
                    $scope.non_loggedin_interest_checked.splice(key,1);
                    return;
                }
            });
        }

        $scope.non_loggedin_interest = [];
        $scope.non_loggedin_interest_checked = [];
        var firstCall = true;
        $scope.get_popular_interest = function(search)
        {
            var exclude = [];
            if($scope.non_loggedin_interest_checked.length>0)
            {
                angular.forEach($scope.non_loggedin_interest_checked,function(val,key){
                    exclude.push(val.CategoryID);
                });
            }

            var jsonData = {Keyword:search,PageNo:1,PageSize:5,Exclude:exclude};
            WallService.CallApi(jsonData, 'users/get_popular_interest').then(function (response) {
                if (response.ResponseCode == 200) {
                    if(firstCall)
                    {
                        $scope.non_loggedin_interest_checked = response.Data;
                        firstCall = false;
                    }
                    else
                    {
                        $scope.non_loggedin_interest = [];
                        angular.forEach(response.Data,function(v,k){
                            var append = true;
                            angular.forEach($scope.non_loggedin_interest_checked,function(val,key){
                                if(v.CategoryID == val.CategoryID)
                                {
                                    append = false;
                                }
                            });
                            if(append)
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
            var Isspush=true;
            angular.forEach($scope.activityData,function (value ,key) {
               
                var ActivityGUID = $scope.activityData[key].ActivityGUID;
                var newArr = new Array();
                
                angular.forEach($scope.activityData[key].Comments,function(v,k){
                   if($scope.activityData[key].Comments[k].CommentGUID==comment_data.CommentGUID)
                   {
                       Isspush=false;
                       $scope.activityData[key].Comments[k]=comment_data;
                   }
                })
                if(Isspush)
                {
                    $scope.activityData[key].Comments.push(comment_data);
                }
                /*$($scope.activityData[key].Comments).each(function (k, value) {
                    newArr.push($scope.activityData[key].Comments[k]);
                });
                newArr.push(comment_data);
                $scope.activityData[key].Comments = newArr.reduce(function (o, v, i) {
                    o[i] = v;
                    return o;
                }, {});*/
               // $scope.activityData[key].Comments = newArr;
                // $scope.activityData[key].Comments = $scope.activityData[key].Comments[0];
                $scope.activityData[key].NoOfComments = parseInt($scope.activityData[key].NoOfComments) + 1;
                $scope.activityData[key].comntData = $scope.$broadcast('appendComntEmit', $scope.activityData[key].Comments); //getPostComments($scope.activityData[key].Comments);

                $('#upload-btn-' + ActivityGUID).show();
                $('#cm-' + ActivityGUID).html('');

                $('#cm-' + ActivityGUID + ' li').remove();
                $('#cm-' + ActivityGUID).hide();
                $('#act-' + ActivityGUID + ' .attach-on-comment').show();
            });
            if(IsNewsFeed=='1')
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
                  // $scope.activityData[key].Comments = $scope.activityData[key].Comments[0];
                  $scope.popularData[key].NoOfComments = parseInt($scope.popularData[key].NoOfComments) + 1;
                  $scope.popularData[key].comntData = $scope.$broadcast('appendComntEmit', $scope.popularData[key].Comments); //getPostComments($scope.activityData[key].Comments);

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
            for (i in $scope.activityData) {
                if ($scope.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Restore Post", "Are you sure, you want to restore this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/restoreActivity').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    $scope.activityData.splice(myid, 1);
                                }
                            });
                        }
                    });
                }
            }
        }
        $scope.delete = function (EntityGUID) {
            var myid = '';
            for (i in $scope.activityData) {
                if ($scope.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Delete Post", "Are you sure, you want to delete this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    $scope.tr--;
                                    if ($scope.activityData[myid].IsFavourite == 1) {
                                        $scope.tfr--;
                                        if ($scope.tfr == 0) {
                                        }
                                    }
                                    $scope.activityData.splice(myid, 1);
                                    if ($scope.tr == 0) {
                                        $scope.wallReqCnt = 1;
                                    }
                                    setTimeout(function () {
                                        if (!$scope.IsActiveFilter) {
                                            if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                                $('#FilterButton').show();
                                            } else {
                                                $('#FilterButton').hide();
                                            }
                                        }
                                    }, 1000);
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
                            if(IsNewsFeed=='1')
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
                    $($scope.activityData).each(function (key, val) {
                        if (ActivityGUID == $scope.activityData[key].ActivityGUID) {
                            if ($scope.activityData[key].IsSticky == 0) {
                                $scope.activityData[key].IsSticky = 1;
                                var newD = $scope.activityData[key];
                                $scope.activityData.splice(key, 1);
                                $scope.activityData.splice(0, 0, newD);
                                return false;
                            } else {
                                $scope.activityData[key].IsSticky = 0;
                                var newD = $scope.activityData[key];
                                if ($scope.activityData.length > 1) {
                                    $scope.activityData.splice(key, 1);
                                    $($scope.activityData).each(function (k, v) {
                                        if ($scope.activityData[k].IsSticky == 0) {
                                            $scope.activityData.splice(k, 0, newD);
                                            return false;
                                        }
                                    });
                                    if (!append) {
                                        $scope.activityData.splice($scope.activityData.length, 0, newD);
                                    }
                                }
                                return false;
                            }
                        }
                    });

                    if(IsNewsFeed=='1')
                    {
                      var append = false;
                      $($scope.popularData).each(function(key, val) {
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
                                      $($scope.popularData).each(function(k, v) {
                                          if ($scope.popularData[k].IsSticky == 0) {
                                              $scope.popularData.splice(k, 0, newD);
                                              return false;
                                          }
                                      });
                                      if (!append) {
                                          $scope.popularData.splice($scope.activityData.length, 0, newD);
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
                $($scope.activityData).each(function (k, v) {
                    if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                        $scope.activityData[k].FlaggedByAny = 0;
                        showResponseMessage('Flag has been approved successfully.', 'alert-success');
                        if ($('#ActivityFilterType').val() == 2) {
                            $scope.activityData.splice(k, 1);
                        }
                    }
                });
                if(IsNewsFeed=='1')
                {
                  $($scope.popularData).each(function(k, v) {
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
                        if(IsNewsFeed=='1')
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
            $scope.displayLoader();
            $scope.stopExecution = 0;
            $scope.WallPageNo = 1;
            $scope.busy = false;
            if($('#IsWiki').length>0)
            {
              $('#WallPageNo').val(1);
              $scope.stop_article_execution = 0;
              $scope.get_wiki_post();
              $scope.article_list = new Array();
            }
            else
            {
              $scope.GetwallPost();
              $scope.activityData = new Array();
            }
            $scope.busy = false;
            $('.loader-fad,.loader-view').show();
        }
        $scope.hideLoader = function () {
            $scope.showLoader = 0;
            $('.loader-fad,.loader-view').css('display', 'none');
        }
        $scope.displayLoader = function () {
            $scope.showLoader = 1;
            $('.loader-fad,.loader-view').css('display', 'block');
        }
        $scope.blockUserEmit = function (UserGUID) {
          if($('#module_id').val()=='18')
          {
            var m = "Are you sure, you want to block this user ?";
          }
          else
          {
            var m = "Are you sure, you want to block this user ? After that you won't be able to send or receive friend request or search this user.";
          }
            showConfirmBox("Block User", m, function (e) {
                if (e) {
                    var reqData = {
                        EntityGUID: UserGUID,
                        ModuleID: $('#module_id').val(),
                        ModuleEntityGUID: $('#module_entity_guid').val()
                    };
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
            for (i in $scope.activityData) {
                if ($scope.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Restore Post", "Are you sure, you want to restore this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/restoreActivity').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    $scope.activityData.splice(myid, 1);
                                }
                            });
                        }
                    });
                }
            }
        }
        $scope.deleteEmit = function (EntityGUID,IsAnnouncement) {
            var myid = '';
            var ActivityGUID = '';
            if(IsAnnouncement == '1')
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
                                    $('.note-editable').html('');
                                    $('.place-holder-label').show();
                                    $('.comment-section').addClass('hide');
                                    if(angular.element('#ForumCtrl').length>0){

                                      forumScope = angular.element('#ForumCtrl').scope(); 
                                      forumScope.category_detail.NoOfDiscussions = parseInt(forumScope.category_detail.NoOfDiscussions)-1;
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
                                      setTimeout(function () {
                                          if (!$scope.IsActiveFilter) {
                                              if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                                  $('#FilterButton').show();
                                              } else {
                                                  $('#FilterButton').hide();
                                              }
                                          }
                                      }, 1000);
                                  }
                              });
                              $('.ra-' + EntityGUID).parent('li').remove();
                          }
                      });
                  }
              }
            }
            else
            {
              for (i in $scope.activityData) {
                  if ($scope.activityData[i].ActivityGUID == EntityGUID) {
                      myid = i;
                      ActivityGUID = $scope.activityData[i].ActivityGUID;
                      showConfirmBox("Delete Post", "Are you sure, you want to delete this post ?", function (e) {
                          if (e) {
                              var reqData = {
                                  EntityGUID: EntityGUID
                              };
                              WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                                  if (response.ResponseCode == 200) {
                                      $('.note-editable').html('');
                                      $('.place-holder-label').show();
                                      $('.comment-section').addClass('hide');
                                      if(angular.element('#ForumCtrl').length>0){

                                        forumScope = angular.element('#ForumCtrl').scope(); 
                                        forumScope.category_detail.NoOfDiscussions = parseInt(forumScope.category_detail.NoOfDiscussions)-1;
                                      }
                                      $scope.markUnmarkAsSticky(ActivityGUID, 3, 'remove', myid, true);
                                      $scope.tr--;
                                      if ($scope.activityData[myid].IsFavourite == 1) {
                                          $scope.tfr--;
                                          if ($scope.tfr == 0) {
                                          }
                                      }
                                      $scope.activityData.splice(myid, 1);
                                      if ($scope.tr == 0) {
                                          $scope.wallReqCnt = 1;
                                      }
                                      setTimeout(function () {
                                          if (!$scope.IsActiveFilter) {
                                              if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                                  $('#FilterButton').show();
                                              } else {
                                                  $('#FilterButton').hide();
                                              }
                                          }
                                      }, 1000);
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
                            angular.forEach($scope.activityData, function (val, key) {
                                if (val.ActivityGUID == ActivityGUID) {
                                    $scope.activityData[key].IsTagged = 0;
                                    $scope.activityData[key].IsSubscribed = 0;
                                    $scope.activityData[key].PostContent = response.Data.PostContent;
                                    if(IsNewsFeed=='1')
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
          if( action == 'open' ){
            if( popupType != 'tutorial' ){
              $scope.isOverlayActive = true;
              if($scope.activityData[0]) {
                $scope.activityData[0]['stickynote'] = true;
              }
              $scope.ShowWallPostOnFilesTab = true;
              if( angular.element("#activityFeedId-0").offset() && ( angular.element("#activityFeedId-0").offset().top > 0 ) ){
                angular.element('html, body').animate({
                    scrollTop: ( parseInt( angular.element("#activityFeedId-0").offset().top - 100)  )
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
            if($scope.activityData[0]) {
              $scope.activityData[0]['stickynote'] = false;
              $scope.stopExecution = 0;
              $scope.busy = false;
              if( !$scope.isFileTab && !$scope.isLinkTab && ( popupType !== 'tutorial') ) {
                $scope.GetwallPost(undefined, true);
              } 
            }
            $scope.IsSingleActivity = false;
          }
        };
        
        $scope.$on('toggleStickyPopup', function (event, stickyData) {
          if( stickyData && stickyData.ActivityGUID ){
            $scope.stopExecution = 0;
            $scope.busy = false;
            if( $scope.isFileTab || $scope.isLinkTab ) {
              $scope.isActivityPrevented = false;
            }
            $scope.GetwallPost(stickyData.ActivityGUID,'Sticky'); 
          } else if( stickyData && stickyData.action && stickyData.popupType && ( stickyData.popupType === 'tutorial' ) ){
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
                if ( (stickyAction === 'create') && ( Object.keys(responseJson).length > 0 ) ) {
                  $scope.group_announcements[feedIndex].SelfSticky = responseJson.SelfSticky;
                  $scope.group_announcements[feedIndex].GroupSticky = responseJson.GroupSticky;
                  $scope.group_announcements[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                  $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson, ModuleID : $scope.activityData[feedIndex].ModuleID});
                  showResponseMessage('This post has been marked as sticky for' + entity + '.', 'alert-success');
    //                  angular.element(document.getElementById('StickyPostController')).scope().checkCoverExists();
                } else if (stickyAction === 'remove') {
                  if ( Object.keys(responseJson).length > 0 ) {
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
                  responseJson = ( Object.keys(responseJson).length > 0 ) ? responseJson : { ActivityGUID : ActivityGUID } ;
                  $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson});
                  if(!fromPostRemoval){
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
              if ($scope.activityData[feedIndex]) {
                var responseJson = response.Data;
                if ( (stickyAction === 'create') && ( Object.keys(responseJson).length > 0 ) ) {
                  $scope.activityData[feedIndex].SelfSticky = responseJson.SelfSticky;
                  $scope.activityData[feedIndex].GroupSticky = responseJson.GroupSticky;
                  $scope.activityData[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                  $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson, ModuleID : $scope.activityData[feedIndex].ModuleID});
                  showResponseMessage('This post has been marked as sticky for' + entity + '.', 'alert-success');
    //                  angular.element(document.getElementById('StickyPostController')).scope().checkCoverExists();
                } else if (stickyAction === 'remove') {
                  if ( Object.keys(responseJson).length > 0 ) {
                    $scope.activityData[feedIndex].SelfSticky = responseJson.SelfSticky;
                    $scope.activityData[feedIndex].GroupSticky = responseJson.GroupSticky;
                    $scope.activityData[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                  } else {
                    switch (makeStickyFor) {
                      case 1:
                        $scope.activityData[feedIndex].SelfSticky = 0;
                        break;
                      case 2:
                        $scope.activityData[feedIndex].SelfSticky = 0;
                        $scope.activityData[feedIndex].GroupSticky = 0;
                        break;
                      case 3:
                        $scope.activityData[feedIndex].SelfSticky = 0;
                        $scope.activityData[feedIndex].GroupSticky = 0;
                        $scope.activityData[feedIndex].EveryoneSticky = 0;
                        break;
                      default:
                        //default code block
                    }
                  }
                  responseJson = ( Object.keys(responseJson).length > 0 ) ? responseJson : { ActivityGUID : ActivityGUID } ;
                  $scope.$broadcast('updateStickyToStickyWidget', {stickyAction: stickyAction, newSticky: responseJson});
                  if(!fromPostRemoval){
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
          if ( $scope.activityData.length ) {
            var responseJson = sticky.stickyDataToUpdate;
            angular.forEach($scope.activityData, function (activity, feedIndex) {
              if (activity.ActivityGUID === responseJson.ActivityGUID) {
                if ( responseJson.SelfSticky ) {
                  $scope.activityData[feedIndex].SelfSticky = responseJson.SelfSticky;
                  $scope.activityData[feedIndex].GroupSticky = responseJson.GroupSticky;
                  $scope.activityData[feedIndex].EveryoneSticky = responseJson.EveryoneSticky;
                } else {
                  switch (sticky.removeStickyFor) {
                    case 1:
                      $scope.activityData[feedIndex].SelfSticky = 0;
                      break;
                    case 2:
                      $scope.activityData[feedIndex].SelfSticky = 0;
                      $scope.activityData[feedIndex].GroupSticky = 0;
                      break;
                    case 3:
                      $scope.activityData[feedIndex].SelfSticky = 0;
                      $scope.activityData[feedIndex].GroupSticky = 0;
                      $scope.activityData[feedIndex].EveryoneSticky = 0;
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
                $($scope.activityData).each(function (k, v) {
                    if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                        $scope.activityData[k].FlaggedByAny = 0;
                        showResponseMessage('Flag has been approved successfully.', 'alert-success');
                        if ($('#ActivityFilterType').val() == 2) {
                            $scope.activityData.splice(k, 1);
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
                    if(IsAnnouncement == '1')
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
                    }
                    else
                    {
                      $($scope.activityData).each(function (key, val) {
                          if ($scope.activityData[key].ActivityGUID == EntityGUID) {
                              $scope.activityData[key].IsSubscribed = response.Data.IsSubscribed;
                              setTimeout(function () {
                                  $('[data-toggle="tooltip"]').tooltip({
                                      container: "body"
                                  });
                              }, 100);
                          }
                      });
                    }
                    if(IsNewsFeed=='1')
                    {
                      $($scope.popularData).each(function(key, val) {
                          if ($scope.popularData[key].ActivityGUID == EntityGUID) {
                              $scope.popularData[key].IsSubscribed = response.Data.IsSubscribed;
                              setTimeout(function() {
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
        $scope.updateWallPost = function(tags)
        {
          $scope.Filter.IsSetFilter=true
          $scope.post_tags = tags;
          $scope.getFilteredWall();
        }

        $scope.commentsSwitchEmit = function (EntityType, EntityGUID, IsAnnouncement) {
            var reqData = {
                EntityType: EntityType,
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'activity/commentStatus').then(function (response) {
                if (response.ResponseCode == 200) {
                    if(IsAnnouncement == '1')
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
                    }
                    else
                    {
                      $($scope.activityData).each(function (key, val) {
                          if ($scope.activityData[key].ActivityGUID == EntityGUID) {
                              //console.log('here');
                              if ($scope.activityData[key].CommentsAllowed == 1) {
                                  $scope.activityData[key].CommentsAllowed = 0;
                              } else {
                                  $scope.activityData[key].CommentsAllowed = 1;
                              }
                          }
                          if(IsNewsFeed=='1')
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
                    angular.forEach($scope.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            $scope.tr--;
                            $scope.activityData.splice(key, 1);
                        }
                    });
                    if(IsNewsFeed=='1')
                    {
                      angular.forEach($scope.popularData, function(val, key) {
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
                    $($scope.activityData).each(function (key, val) {
                        if ($scope.activityData[key].ActivityGUID == EntityGUID) {
                            if ($scope.activityData[key].CommentsAllowed == 1) {
                                $scope.activityData[key].CommentsAllowed = 0;
                            } else {
                                $scope.activityData[key].CommentsAllowed = 1;
                            }
                        }
                    });
                    if(IsNewsFeed=='1')
                    {
                      $($scope.popularData).each(function(key, val) {
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
                    $scope.activityData[i].Comments = [];
                    for (j in tempComntData) {
                        $scope.activityData[i].Comments.push(tempComntData[j]);
                    }
                    $($scope.activityData).each(function (k, v) {
                        if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                            $scope.activityData[k].viewStats = 0;
                        }
                    });
                    returnObj = $scope.activityData[i].Comments
                    $scope.$broadcast('updateComntEmit', returnObj);
                    if(IsNewsFeed=='1')
                    {
                      var tempComntData = response.Data;
                      $scope.popularData[i].Comments = [];
                      for (j in tempComntData) {
                          $scope.popularData[i].Comments.push(tempComntData[j]);
                      }
                      $($scope.popularData).each(function(k, v) {
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
        $scope.shareEmit = function (ActivityGUID) {
            lazyLoadCS.loadModule({
                moduleName: 'sharePopupMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/sharePopupMdl.js' + $scope.app_version,
                templateUrl: base_url + 'assets/partials/wall/share_popup.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'share_popup_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('sharePopupMdlInit', {
                        params: params,
                        wallScope: $scope,
                        ActivityGUID: ActivityGUID,
                        fn:'shareEmit'
                    });
                },
            });
        };

        $scope.shareEmitAnnouncement = function (ActivityGUID) {
            $scope.shareEmit(ActivityGUID,'shareEmitAnnouncement');
        }

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
        }

        $scope.getSingleActivity = function (ActivityGUID) {
            $($scope.activityData).each(function (k, v) {
                if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                    $scope.singleActivity = $scope.activityData[k];
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

        $scope.getSingleActivityAnn = function(ActivityGUID)
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
                var replacedText, replacePattern1, replacePattern2, replacePattern3;

                inputText=inputText.replace('contenteditable', 'contenteditabletext');

                replacedText = inputText.replace("<br>", " ||| ");
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
                replacedText = checkTaggedData(replacedText);
                var repTxt = removeTags(replacedText);
                if(repTxt)
                {
                    if (repTxt.length > 200) {
                        replacedText = '<span class="show-less">' + smart_substr(200, replacedText) + '... <a onclick="showMoreComment(this);">See More</a></span><span class="show-more">' +
                                replacedText + '</span>';
                    }
                }
                replacedText = $sce.trustAsHtml(replacedText);
                return replacedText
            } else {
                return '';
            }
        }

        $scope.parseYoutubeVideo = function (url) {
            var videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
            if (videoid != null) {
                return videoid[1];
            } else {
                return false;
            }
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
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                if ($scope.activityData[key].IsLike == 1) {
                                    $scope.activityData[key].IsLike = 0;
                                    $scope.activityData[key].NoOfLikes--;
                                } else {
                                    $scope.activityData[key].IsLike = 1;
                                    $scope.activityData[key].NoOfLikes++;
                                }
                                WallService.CallApi({
                                    EntityGUID: EntityGUID,
                                    EntityType: Type,
                                    PageNo: 1,
                                    PageSize: 2
                                }, 'activity/getLikeDetails').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        $scope.activityData[key].LikeList = response.Data;
                                    }
                                });
                            } else if (Type == 'COMMENT') {
                                $($scope.activityData[key].Comments).each(function (k, v) {
                                    if ($scope.activityData[key].Comments[k].CommentGUID == EntityGUID) {
                                        if ($scope.activityData[key].Comments[k].IsLike == 1) {
                                            $scope.activityData[key].Comments[k].IsLike = 0;
                                            $scope.activityData[key].Comments[k].NoOfLikes--;
                                        } else {
                                            $scope.activityData[key].Comments[k].IsLike = 1;
                                            $scope.activityData[key].Comments[k].NoOfLikes++;
                                        }
                                    }
                                });
                            }
                        }
                    });

                    if(IsNewsFeed=='1')
                    {
                      $($scope.popularData).each(function(key, value) {
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
                                  }, 'activity/getLikeDetails').then(function(response) {
                                      if (response.ResponseCode == 200) {
                                          $scope.popularData[key].LikeList = response.Data;
                                      }
                                  });
                              } else if (Type == 'COMMENT') {
                                  $($scope.popularData[key].Comments).each(function(k, v) {
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
            $scope.LastLikeActivityGUID = EntityGUID;
            jsonData = {
                EntityGUID: EntityGUID,
                EntityType: EntityType,
                PageNo: $('#LikePageNo').val(),
                PageSize: 8
            };
            WallService.CallApi(jsonData, 'activity/getLikeDetails').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (!$('#totalLikes').is(':visible')) {
                        $('#totalLikes').modal('show');
                        $('#LikePageNo').val(0);
                        $scope.likeDetails = [];
                        if (response.Data == '') {
                            $scope.likeDetails = [];
                            $scope.totalLikes = 0;
                            $scope.likeMessage = 'No likes yet.';
                        }
                    }
                    if (response.Data !== '') {
                        $(response.Data).each(function (k, v) {
                            var append = true;
                            $($scope.likeDetails).each(function (key, val) {
                                if (v.ProfileURL == val.ProfileURL) {
                                    append = false;
                                }
                            });
                            if (append) {
                                $scope.likeDetails.push(response.Data[k]);
                            }
                        });
                        $scope.totalLikes = response.TotalRecords;
                        $scope.likeMessage = '';
                        $('#LikePageNo').val(parseInt($('#LikePageNo').val()) + 1);
                    }
                }
            });
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
                            $($scope.activityData).each(function (key, value) {
                                if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                                    aid = key;
                                    $($scope.activityData[aid].Comments).each(function (ckey, cvalue) {
                                        if ($scope.activityData[aid].Comments[ckey].CommentGUID == CommentGUID) {
                                            cid = ckey;
                                            $scope.activityData[aid].Comments.splice(cid, 1);
                                            $scope.activityData[aid].NoOfComments = parseInt($scope.activityData[aid].NoOfComments) - 1;
                                            return false;
                                        }
                                    });
                                }
                            });

                            if(IsNewsFeed=='1')
                            {
                              $($scope.popularData).each(function(key, value) {
                                  if ($scope.popularData[key].ActivityGUID == ActivityGUID) {
                                      aid = key;
                                      $($scope.popularData[aid].Comments).each(function(ckey, cvalue) {
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
                data['CurrentProfilePic'] = Settings.getSiteUrl() + '/' + 'assets/img/profiles/user_default.jpg';
            } else {
                data['CurrentProfilePic'] = Settings.getImageServerPath() + 'upload/profile/36x36/' + profile_picture;
            }
            $scope.updateActivityData(activity_guid, data);
        }
        
        $scope.setFavouriteArticle = function (ActivityGUID) {
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
                            } else {
                                $scope.article_list[key].IsFavourite = 1;
                            }
                        }
                    });
                    
                    angular.forEach($scope.widget_articles,function(val,key){
                      angular.forEach(val.Data,function(v,k){
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
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                            if ($scope.activityData[key].IsFavourite == 1) {
                                $scope.activityData[key].IsFavourite = 0;
                                $scope.tfr--;
                                //$scope.activityData.splice(key,1);
                                if ($scope.tfr == 0) {
                                }
                                if ($('#ActivityFilterType').val() == 'Favourite') {
                                    $('#act-' + ActivityGUID).hide();
                                }
                                return false;
                            } else {
                                $scope.activityData[key].IsFavourite = 1;
                                $scope.tfr++;
                            }
                        }
                    });

                    if(IsNewsFeed=='1')
                    {
                      $($scope.popularData).each(function(key, value) {
                        if ($scope.popularData[key].ActivityGUID == ActivityGUID) {
                            if ($scope.popularData[key].IsFavourite == 1) {
                                $scope.popularData[key].IsFavourite = 0;
                                $scope.tfr--;
                                //$scope.activityData.splice(key,1);
                                if ($scope.tfr == 0) {}
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
        $scope.share = function (ActivityGUID) {
            $scope.shareEmit(ActivityGUID,'share');
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

        $scope.tagComment = function (eid,cls,module_id,module_entity_guid) {
            var ajax_request = false;
            setTimeout(function () {
                var sym = '#';
                if(cls=='1')
                {
                    sym = '.';
                }
                $(sym + eid).textntags({
                    onDataRequest: function (mode, query, triggerChar, callback) {
                        if (ajax_request)
                            ajax_request.abort();
                        if(module_entity_guid)
                        {
                          if (module_id == 1) {
                              var type = 'Members';
                          } else {
                              var type = 'NewsFeedTagging';
                          }
                        }
                        else
                        {
                          module_id = $('#module_id').val();
                          module_entity_guid = $('#module_entity_guid').val();
                          if (module_id == 1) {
                              var type = 'Members';
                          } else {
                              var type = 'NewsFeedTagging';
                          }
                        }
                        if(IsAdminView == '1')
                        {
                          ajax_request = $http({
                            method: 'POST',
                            data:{
                                Type: type,
                                SearchKey: query,
                                ModuleID: module_id,
                                ModuleEntityID: module_entity_guid
                            },
                            url: base_url + 'api/users/list'
                          }).then(function(r) {
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
                        }
                        else
                        {
                          ajax_request = $.post(base_url + 'api/users/list', {
                            Loginsessionkey:LoginSessionKey,
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
                        jsonData = {
                            Comment: Comment,
                            EntityType: 'Activity',
                            EntityGUID: ActivityGUID,
                            Media: Media,
                            EntityOwner: $('#act-' + ActivityGUID + ' .module-entity-owner').val()
                        };
                        WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {
                            $($scope.activityData).each(function (key, value) {
                                if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                                    // $scope.activityData[key].Comments[0] = response.Data[0];
                                    var newArr = new Array();
                                    $($scope.activityData[key].Comments).each(function (k, value) {
                                        newArr.push($scope.activityData[key].Comments[k]);
                                    });
                                    newArr.push(response.Data[0]);
                                    $scope.activityData[key].Comments = reduce_arr(newArr);
                                    $scope.activityData[key].Comments = newArr;
                                    $scope.activityData[key].NoOfComments = parseInt($scope.activityData[key].NoOfComments) + 1;
                                    $scope.activityData[key].comntData = $scope.$broadcast('appendComntEmit', $scope.activityData[key].Comments); //getPostComments($scope.activityData[key].Comments);
                                    $('#upload-btn-' + ActivityGUID).show();
                                    $('#cm-' + ActivityGUID).html('');
                                    $('#cm-' + ActivityGUID + ' li').remove();
                                    $('#cm-' + ActivityGUID).hide();
                                    $('#act-' + ActivityGUID + ' .attach-on-comment').show();
                                    $scope.activityData[key].IsSubscribed = 1;
                                    setTimeout(function () {
                                        $('#cmt-' + ActivityGUID).trigger('focus');
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
            if(IsNewsFeed=='1')
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
        $scope.clearReminderState = function (ActivityGUID, IsReminderSet) {
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
                $('#reminderDropDownBox' + ActivityGUID + ' [data-type="editreminderFooter"]').hide();
                fixedTimePicker = $('[data-fixed-activityguid="' + ActivityGUID + '"] a');
                fixedTimePicker.removeClass('active');
            }
        }

        $scope.mentiontabshow = false;
        $scope.hideMentionTab = function()
        {
            $scope.mentiontabshow = false;
            $('#mentionView').hide();
            $('.live-feed').removeClass('mentionOpen');
        }

        $scope.mentiontab = function()
        {
            $scope.mentiontabshow = true;
            $('#mentionView').addClass('visible').show();
            if ($('#mentionView').hasClass('visible')) {
                $('.live-feed').addClass('mentionOpen');
               // $('applyed-filter').
            };
        }

        $scope.selectedDate = {};
        $scope.isInit = false;
        $scope.initDatepicker = function (ActivityGUID, ReminderData) {
          var d = new Date(),
                  datePickerValueAsObject = null,
                  n = d.getMinutes();
//          for (var k = 0; k <= 3; k++) {
//            if (15 * k <= n) {
//              $('ul.minutes input:eq(' + k + ')').attr('disabled');
//              $('ul.minutes span:eq(' + k + ')').addClass('disabled');
//            }
//          }
          angular.forEach($scope.activityData, function (v1, k1) {
            if (v1.ActivityGUID == ActivityGUID) {
              //console.log(v1);
              var datetime = new Date();
              var Meridian = moment(datetime).format('a');
              if (typeof $scope.activityData[k1].ReminderData == 'undefined')
              {
                $scope.activityData[k1]['ReminderHours'] = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                $scope.activityData[k1].ReminderData = prepareReminderData($scope.activityData[k1].Reminder);
              }
              if (typeof ReminderData == 'undefined')
              {
                ReminderData = $scope.activityData[k1].ReminderData;
              }
              $scope.activityData[k1]['ReminderData'].Meridian = ( $scope.activityData[k1]['ReminderData'].Meridian ) ? $scope.activityData[k1]['ReminderData'].Meridian : Meridian;
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
              $scope.group_announcements[k1]['ReminderData'].Meridian = ( $scope.group_announcements[k1]['ReminderData'].Meridian ) ? $scope.group_announcements[k1]['ReminderData'].Meridian : Meridian;
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
                  $scope.popularData[k1].ReminderData = prepareReminderData($scope.activityData[k1].Reminder);
                }
                if (typeof ReminderData == 'undefined')
                {
                  ReminderData = $scope.popularData[k1].ReminderData;
                }
                $scope.popularData[k1]['ReminderData'].Meridian = ( $scope.popularData[k1]['ReminderData'].Meridian ) ? $scope.popularData[k1]['ReminderData'].Meridian : Meridian;
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
              if ( OldSelectedDate ) {
                var curDate = moment().format( 'YYYY-MM-DD' );
                Dates = moment(date).format('YYYY-MM-DD');
                if ( moment( OldSelectedDate ).isSame( Dates ) ) {
                  return [true, "reminderSet"];
                }
              }
              return [true, ''];
            }
          });
          $timeout(function () {
            if ( OldSelectedDate ) {
              var curDateToSet = moment().format( 'YYYY-MM-DD' );
              if ( ( moment( OldSelectedDate ).isSame( curDateToSet ) ) || ( moment( OldSelectedDate ).isAfter( curDateToSet ) ) ) {
                $('#reminderCal' + ActivityGUID).datepicker("setDate", moment(OldSelectedDate).toDate());
                $scope.selectedDate[ActivityGUID] = OldSelectedDate;
//                console.log(' dateSet ', $('#reminderCal' + ActivityGUID).datepicker("getDate"));
              } else if ( moment( OldSelectedDate ).isBefore( curDateToSet ) ) {
                $('#reminderCal' + ActivityGUID).datepicker("setDate", moment(curDateToSet).toDate());
                $scope.selectedDate[ActivityGUID] = curDateToSet;
//                console.log(' dateSet ', $('#reminderCal' + ActivityGUID).datepicker("getDate"));
              }
              datePickerValueAsObject = $('#reminderCal' + ActivityGUID).datepicker('getDate');
            }
            $.each(angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=HH]'), function (hourKey, hourField) {
                angular.element(this).parent().removeClass('selected');
                angular.element(this).prop('checked', false);
            });
            $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
            angular.element('#reminderCalTimePicker' + ActivityGUID).find('input[name=time]').on('change', function () {
//              console.log('changed : ', angular.element(this).val());
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
  //          angular.element('#reminderCalTimePicker' + ActivityGUID).on('mouseenter', function () {
  //            $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
  //          });
  //          angular.element('#reminderCalTimePicker' + ActivityGUID).on('mouseleave', function () {
  //            $scope.validateTimePicker(ActivityGUID, datePickerValueAsObject);
  //          });
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
                amOrPm = ( moment().format('a') == 'pm' ) ? 'pm' : amOrPm;
                if ( moment().format('a') == 'pm' ) {
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

          angular.forEach($scope.activityData, function (v1, k1) {
            if (v1.ActivityGUID == ActivityGUID) {
              $scope.activityData[k1]['ReminderData'].Meridian = amOrPm;
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
          if ( amOrPm == 'pm' ) {
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
        var filterIsPromoted = 0;
        $scope.applyFilterType = function (val, callService) { 
            // Initially reset
            filterIsPromoted = 0;
            
            $('#IsMediaExists').val(2);
            $scope.Filter.IsSetFilter= true;
            $scope.IsActiveFilter = true;
            $scope.IsFilePage = 0;
            var val1 = val;
            if(val!==5)
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
            if(val == 'IsPromoted' && $scope.config_detail.IsSuperAdmin) {
                $scope.ResetFilter(0, 1);
                $scope.Filter.IsSetFilter = 1;
                filterIsPromoted = 1;
                $scope.Filter.typeLabelName = 'Promoted';
            }
            
            
            if($('#IsWiki').length>0)
            {
              $('#WallPageNo').val(1);
              $scope.stop_article_execution = 0;
              $scope.article_list = [];
              $scope.get_wiki_post();
            }
            else
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
            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == ActivityGUID) {
                    $scope.activityData[key]['ReminderData'] = ReminderData;
                    if (!$scope.$$phase) {
                        $scope.$apply();
                    }
                }
            });

            if(IsNewsFeed=='1')
            {
              angular.forEach($scope.popularData, function(val, key) {
                  if (val.ActivityGUID == ActivityGUID) {
                      $scope.popularData[key]['ReminderData'] = ReminderData;
                      if (!$scope.$$phase) {
                          $scope.$apply();
                      }
                  }
              });
            }
        }

        $scope.likeEmitAnnouncement = function (EntityGUID, Type, EntityParentGUID,IsDislike,CommentParentGUID) {
            
            var element =$('#act-' + EntityParentGUID + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };
            if(IsDislike=='1')
            {
              reqData['Dislike'] = 1;
            }
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.group_announcements).each(function (key, value) {
                        if ($scope.group_announcements[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                if(IsDislike=='1')
                                {
                                  if ($scope.group_announcements[key].IsDislike == 1) {
                                      $scope.group_announcements[key].IsDislike = 0;
                                      //$scope.group_announcements[key].NoOfDislikes--;
                                  } else {
                                      if($scope.group_announcements[key].IsLike=='1')
                                      {
                                        $scope.group_announcements[key].IsLike = 0;
                                        $scope.group_announcements[key].NoOfLikes--;
                                      }
                                      $scope.group_announcements[key].IsDislike = 1;
                                      //$scope.group_announcements[key].NoOfDislikes++;
                                  }
                                }
                                else
                                {
                                  if ($scope.group_announcements[key].IsLike == 1) {
                                      $scope.group_announcements[key].IsLike = 0;
                                      $scope.group_announcements[key].NoOfLikes--;
                                  } else {
                                      if($scope.group_announcements[key].IsDislike=='1')
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
                                        if($scope.group_announcements[key].Comments[k].CommentGUID == EntityGUID)
                                        {
                                          if(IsDislike=='1')
                                          {
                                            if ($scope.group_announcements[key].Comments[k].IsDislike == 1) {
                                                $scope.group_announcements[key].Comments[k].IsDislike = 0;
                                                //$scope.group_announcements[key].Comments[k].NoOfDislikes--;
                                            } else {
                                                if($scope.group_announcements[key].Comments[k].IsLike=='1')
                                                {
                                                  $scope.group_announcements[key].Comments[k].IsLike = 0;
                                                  $scope.group_announcements[key].Comments[k].NoOfLikes--;
                                                }
                                                $scope.group_announcements[key].Comments[k].IsDislike = 1;
                                                //$scope.group_announcements[key].Comments[k].NoOfDislikes++;
                                            }
                                          }
                                          else
                                          {
                                            if ($scope.group_announcements[key].Comments[k].IsLike == 1) {
                                                $scope.group_announcements[key].Comments[k].IsLike = 0;
                                                $scope.group_announcements[key].Comments[k].NoOfLikes--;
                                            } else {
                                                if($scope.group_announcements[key].Comments[k].IsDislike=='1')
                                                {
                                                  $scope.group_announcements[key].Comments[k].IsDislike = 0;
                                                  //$scope.group_announcements[key].Comments[k].NoOfDislikes--;
                                                }
                                                $scope.group_announcements[key].Comments[k].IsLike = 1;
                                                $scope.group_announcements[key].Comments[k].NoOfLikes++;
                                            }
                                          }
                                        }
                                    else
                                    {
                                      angular.forEach($scope.group_announcements[key].Comments[k].Replies,function(v2,k2){
                                        if($scope.group_announcements[key].Comments[k].Replies[k2].CommentGUID == EntityGUID)
                                        {
                                          if ($scope.group_announcements[key].Comments[k].Replies[k2].IsLike == 1) {
                                              $scope.group_announcements[key].Comments[k].Replies[k2].IsLike = 0;
                                              $scope.group_announcements[key].Comments[k].Replies[k2].NoOfLikes--;
                                          } else {
                                              if($scope.group_announcements[key].Comments[k].Replies[k2].IsDislike=='1')
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

        $scope.likeEmit = function (EntityGUID, Type, EntityParentGUID,IsDislike,CommentParentGUID) {
            
            var element =$('#act-' + EntityParentGUID + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };
            if(IsDislike=='1')
            {
              reqData['Dislike'] = 1;
            }
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                if(IsDislike=='1')
                                {
                                  if ($scope.activityData[key].IsDislike == 1) {
                                      $scope.activityData[key].IsDislike = 0;
                                      //$scope.activityData[key].NoOfDislikes--;
                                  } else {
                                      if($scope.activityData[key].IsLike=='1')
                                      {
                                        $scope.activityData[key].IsLike = 0;
                                      }
                                      $scope.activityData[key].IsDislike = 1;
                                      //$scope.activityData[key].NoOfDislikes++;
                                  }
                                }
                                else
                                {
                                  if ($scope.activityData[key].IsLike == 1) {
                                      $scope.activityData[key].IsLike = 0;
                                  } else {
                                      if($scope.activityData[key].IsDislike=='1')
                                      {
                                        $scope.activityData[key].IsDislike = 0;
                                        //$scope.activityData[key].NoOfDislikes--;
                                      }
                                      $scope.activityData[key].IsLike = 1;
                                  }
                                }
                                $scope.activityData[key].NoOfLikes = response.Data.NoOfLikes;
                                WallService.CallApi({
                                    EntityGUID: EntityGUID,
                                    EntityType: Type,
                                    PageNo: 1,
                                    PageSize: 10
                                }, 'activity/getLikeDetails').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        $scope.activityData[key].LikeList = response.Data;
                                    }
                                });
                            } else if (Type == 'COMMENT') {
                                $($scope.activityData[key].Comments).each(function (k, v) {
                                    if ($scope.activityData[key].Comments[k].CommentGUID == EntityGUID || $scope.activityData[key].Comments[k].CommentGUID == CommentParentGUID) {
                                        if($scope.activityData[key].Comments[k].CommentGUID == EntityGUID)
                                        {
                                          if(IsDislike=='1')
                                          {
                                            if ($scope.activityData[key].Comments[k].IsDislike == 1) {
                                                $scope.activityData[key].Comments[k].IsDislike = 0;
                                                //$scope.activityData[key].Comments[k].NoOfDislikes--;
                                            } else {
                                                if($scope.activityData[key].Comments[k].IsLike=='1')
                                                {
                                                  $scope.activityData[key].Comments[k].IsLike = 0;
                                                  $scope.activityData[key].Comments[k].NoOfLikes--;
                                                }
                                                $scope.activityData[key].Comments[k].IsDislike = 1;
                                                //$scope.activityData[key].Comments[k].NoOfDislikes++;
                                            }
                                          }
                                          else
                                          {
                                            if ($scope.activityData[key].Comments[k].IsLike == 1) {
                                                $scope.activityData[key].Comments[k].IsLike = 0;
                                                $scope.activityData[key].Comments[k].NoOfLikes--;
                                            } else {
                                                if($scope.activityData[key].Comments[k].IsDislike=='1')
                                                {
                                                  $scope.activityData[key].Comments[k].IsDislike = 0;
                                                  //$scope.activityData[key].Comments[k].NoOfDislikes--;
                                                }
                                                $scope.activityData[key].Comments[k].IsLike = 1;
                                                $scope.activityData[key].Comments[k].NoOfLikes++;
                                            }
                                          }
                                        }
                                    else
                                    {
                                      angular.forEach($scope.activityData[key].Comments[k].Replies,function(v2,k2){
                                        if($scope.activityData[key].Comments[k].Replies[k2].CommentGUID == EntityGUID)
                                        {
                                          if ($scope.activityData[key].Comments[k].Replies[k2].IsLike == 1) {
                                              $scope.activityData[key].Comments[k].Replies[k2].IsLike = 0;
                                              $scope.activityData[key].Comments[k].Replies[k2].NoOfLikes--;
                                          } else {
                                              if($scope.activityData[key].Comments[k].Replies[k2].IsDislike=='1')
                                              {
                                                $scope.activityData[key].Comments[k].Replies[k2].IsDislike = 0;
                                                //$scope.activityData[key].Comments[k].Replies[k2].NoOfDislikes--;
                                              }
                                              $scope.activityData[key].Comments[k].Replies[k2].IsLike = 1;
                                              $scope.activityData[key].Comments[k].Replies[k2].NoOfLikes++;
                                          }
                                        }
                                      });
                                    }
                                  }
                                });
                            }
                        }
                    });


                    if(IsNewsFeed=='1')
                    {
                      $($scope.popularData).each(function(key, value) {
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
                                  }, 'activity/getLikeDetails').then(function(response) {
                                      if (response.ResponseCode == 200) {
                                          $scope.popularData[key].LikeList = response.Data;
                                      }
                                  });
                              } else if (Type == 'COMMENT') {
                                  $($scope.popularData[key].Comments).each(function(k, v) {
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

        $scope.likeEmitArticle = function (EntityGUID, Type, EntityParentGUID,IsDislike,CommentParentGUID) {
            
            var element =$('#act-' + EntityParentGUID + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };
            if(IsDislike=='1')
            {
              reqData['Dislike'] = 1;
            }
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.article_list).each(function (key, value) {
                        if ($scope.article_list[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                if(IsDislike=='1')
                                {
                                  if ($scope.article_list[key].IsDislike == 1) {
                                      $scope.article_list[key].IsDislike = 0;
                                      //$scope.activityData[key].NoOfDislikes--;
                                  } else {
                                      if($scope.article_list[key].IsLike=='1')
                                      {
                                        $scope.article_list[key].IsLike = 0;
                                        $scope.article_list[key].NoOfLikes--;
                                      }
                                      $scope.article_list[key].IsDislike = 1;
                                      //$scope.activityData[key].NoOfDislikes++;
                                  }
                                }
                                else
                                {
                                  if ($scope.article_list[key].IsLike == 1) {
                                      $scope.article_list[key].IsLike = 0;
                                      $scope.article_list[key].NoOfLikes--;
                                  } else {
                                      if($scope.article_list[key].IsDislike=='1')
                                      {
                                        $scope.article_list[key].IsDislike = 0;
                                        //$scope.activityData[key].NoOfDislikes--;
                                      }
                                      $scope.article_list[key].IsLike = 1;
                                      $scope.article_list[key].NoOfLikes++;
                                  }
                                }
                                WallService.CallApi({
                                    EntityGUID: EntityGUID,
                                    EntityType: Type,
                                    PageNo: 1,
                                    PageSize: 10
                                }, 'activity/getLikeDetails').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        $scope.article_list[key].LikeList = response.Data;
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

        $scope.likeEmitWidgetArticle = function (EntityGUID, Type, EntityParentGUID,IsDislike,CommentParentGUID) {
            
            var element =$('#act-' + EntityParentGUID + ' .post-as-data');
            //var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                //EntityOwner: EntityOwner
            };
            if(IsDislike=='1')
            {
              reqData['Dislike'] = 1;
            }
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.widget_articles,function(val,key){
                      angular.forEach(val.Data,function(v,k){
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

        $scope.deleteCommentEmit = function (CommentGUID, ActivityGUID,CommentParentGUID,PostTypeID) {
            jsonData = {
                CommentGUID: CommentGUID
            };
            var confirm_title = "Delete Comment";
            var confirm_message = "Are you sure, you want to delete this comment ?";
            if(CommentParentGUID)
            {
              if(CommentParentGUID!=='')
              {
                confirm_title = "Delete Reply";
                confirm_message = "Are you sure, you want to delete this reply ?";
              }
            }
            if(PostTypeID == '2')
            {
              confirm_title = "Delete Answer";
              confirm_message = "Are you sure, you want to delete this answer ?";
            }
            showConfirmBox(confirm_title, confirm_message, function (e) {
                if (e) {
                    WallService.CallApi(jsonData, 'activity/deleteComment').then(function (response) {
                        var aid = '';
                        var cid = '';
                        if (response.ResponseCode == 200) {
                            $($scope.activityData).each(function (key, value) {
                                if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                                    aid = key;
                                    $($scope.activityData[aid].Comments).each(function (ckey, cvalue) {
                                        if ($scope.activityData[aid].Comments[ckey].CommentGUID == CommentGUID || $scope.activityData[aid].Comments[ckey].CommentGUID == CommentParentGUID) {
                                            if($scope.activityData[aid].Comments[ckey].CommentGUID == CommentGUID)
                                            {
                                              cid = ckey;
                                              $scope.activityData[aid].Comments.splice(cid, 1);
                                              $scope.activityData[aid].NoOfComments = parseInt($scope.activityData[aid].NoOfComments) - 1;
                                              return false;
                                            }
                                            else
                                            {
                                              angular.forEach($scope.activityData[aid].Comments[ckey].Replies,function(v2,k2){
                                                if(v2.CommentGUID == CommentGUID)
                                                {
                                                  cid = k2;
                                                  $scope.activityData[aid].Comments[ckey].Replies.splice(cid, 1);
                                                  $scope.activityData[aid].Comments[ckey].NoOfReplies = parseInt($scope.activityData[aid].Comments[ckey].NoOfReplies) - 1;
                                                  return false;
                                                }
                                              });
                                            }
                                        }
                                    });
                                }
                            });

                            if(IsNewsFeed=='1')
                            {
                              $($scope.popularData).each(function(key, value) {
                                  if ($scope.popularData[key].ActivityGUID == ActivityGUID) {
                                      aid = key;
                                      $($scope.popularData[aid].Comments).each(function(ckey, cvalue) {
                                          if ($scope.popularData[aid].Comments[ckey].CommentGUID == CommentGUID) {
                                              cid = ckey;
                                              $scope.popularData[aid].Comments.splice(cid, 1);
                                              $scope.popularData[aid].NoOfComments = parseInt($scope.activityData[aid].NoOfComments) - 1;
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
        $scope.uploadFiles = function (files, errFiles, id, feedIndex,IsAnnouncement) {
//            $scope.errFiles = errFiles;
            var promises = [];
            if (!(errFiles.length > 0)) {
              if(IsAnnouncement == '1')
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
                        var paramsToBeSent = {
                            Type: 'comments',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        if (patt.test(file.type)) {
                            $scope.group_announcements[feedIndex].medias['media-' + mediaIndex] = file;
                            $scope.group_announcements[feedIndex]['commentMediaCount'] = Object.keys($scope.group_announcements[feedIndex].medias).length;
                        } else {
                            $scope.group_announcements[feedIndex].files['file-' + fileIndex] = file;
                            $scope.group_announcements[feedIndex]['commentFileCount'] = Object.keys($scope.group_announcements[feedIndex].files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.group_announcements[feedIndex].medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.group_announcements[feedIndex].medias['media-' + mediaIndex].progress = true;
                                            hideButtonLoader('PostBtn-'+id);
                                        } else {
                                            delete $scope.group_announcements[feedIndex].medias['media-' + mediaIndex];
                                            $scope.group_announcements[feedIndex]['commentMediaCount'] = Object.keys($scope.group_announcements[feedIndex].medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.group_announcements[feedIndex].files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.group_announcements[feedIndex].files['file-' + fileIndex].progress = true; 
                                            hideButtonLoader('PostBtn-'+id);
//                                   console.log($scope.group_announcements[feedIndex].files);
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
              else
              {
                angular.forEach($scope.activityData,function(val,key){
                  if(val.ActivityGUID == id)
                  {
                    feedIndex = key;
                    if (!$scope.activityData[key].medias) {
                        $scope.activityData[key]['medias'] = {};
                        $scope.activityData[key]['commentMediaCount'] = 0;
                    }

                    if (!$scope.activityData[key].files) {
                        $scope.activityData[key]['files'] = {};
                        $scope.activityData[key]['commentFileCount'] = 0;
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
                            $scope.activityData[feedIndex].medias['media-' + mediaIndex] = file;
                            $scope.activityData[feedIndex]['commentMediaCount'] = Object.keys($scope.activityData[feedIndex].medias).length;
                        } else {
                            $scope.activityData[feedIndex].files['file-' + fileIndex] = file;
                            $scope.activityData[feedIndex]['commentFileCount'] = Object.keys($scope.activityData[feedIndex].files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        if(IsAdminView == '1')
                        {
                          url = 'adminupload_image';
                        }
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.activityData[feedIndex].medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.activityData[feedIndex].medias['media-' + mediaIndex].progress = true;
                                            hideButtonLoader('PostBtn-'+id);
                                        } else {
                                            delete $scope.activityData[feedIndex].medias['media-' + mediaIndex];
                                            $scope.activityData[feedIndex]['commentMediaCount'] = Object.keys($scope.activityData[feedIndex].medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.activityData[feedIndex].files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.activityData[feedIndex].files['file-' + fileIndex].progress = true; 
                                            hideButtonLoader('PostBtn-'+id);
//                                   console.log($scope.activityData[feedIndex].files);
                                        } else {
                                            delete $scope.activityData[feedIndex].files['file-' + fileIndex];
                                            $scope.activityData[feedIndex]['commentFileCount'] = Object.keys($scope.activityData[feedIndex].files).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }
                                    if ((Object.keys($scope.activityData[feedIndex].files).length === 0) && (Object.keys($scope.activityData[feedIndex].medias).length === 0)) {
                                        IsMediaExists = 0;
                                    }
                                },
                                function (response) {
                                    if (fileType === 'media') {
                                        delete $scope.activityData[feedIndex].medias['media-' + mediaIndex];
                                        $scope.activityData[feedIndex]['commentMediaCount'] = Object.keys($scope.activityData[feedIndex].medias).length;
                                    } else {
                                        delete $scope.activityData[feedIndex].files['file-' + fileIndex];
                                        $scope.activityData[feedIndex]['commentFileCount'] = Object.keys($scope.activityData[feedIndex].files).length;
                                    }
                                    if ((Object.keys($scope.activityData[feedIndex].files).length === 0) && (Object.keys($scope.activityData[feedIndex].medias).length === 0)) {
                                        IsMediaExists = 0;
                                    }
                                },
                                function (evt) {
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
        $scope.EdituploadFiles = function (files, errFiles, commentData) {
            var id=commentData.CommentGUID;
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
                        var paramsToBeSent = {
                            Type: 'comments',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        
                        if (patt.test(file.type)) {
                            commentData.Media.push({progress:false});
                        } else {
                            commentData.Files.push({progress:false});
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode == 200) {
                                            var i=0;
                                            $.each(commentData.Media,function(k){
                                              if(!this.progress && i==0)
                                              {
                                                 commentData.Media[k]= response.data.Data;
                                                 commentData.Media[k].progress=true;
                                                 i=i+1;
                                              };
                                            })
                                           hideButtonLoader('PostBtn-'+id);
                                        } else {
                                            var i=0;
                                            $.each(commentData.Media,function(k){
                                              if(!this.progress && i==0)
                                              {
                                                 commentData.Media.splice(k, 1);
                                                 i=i+1;
                                              };
                                            })
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {
                                            
                                            var i=0;
                                            $.each(commentData.Files,function(k){
                                              if(!this.progress && i==0)
                                              {
                                                 commentData.Files[k]= response.data.Data;
                                                 commentData.Files[k].progress=true;
                                                 i=i+1;
                                              };
                                            })
                                            hideButtonLoader('PostBtn-'+id);
                                        } else {
                                            var i=0;
                                            $.each(commentData.Files,function(k){
                                              if(!this.progress && i==0)
                                              {
                                                commentData.Files.splice(k, 1);
                                                 i=i+1;
                                              };
                                            })
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }

                                },
                                function (response) {
                                },
                                function (evt) {
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
          if ( IsAnnouncement == '1' ) {
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
            if ((type == 'file') && ($scope.activityData[feedIndex].files && (Object.keys($scope.activityData[feedIndex].files).length > 0))) {
                delete $scope.activityData[feedIndex].files[index];
                $scope.activityData[feedIndex]['commentFileCount'] = Object.keys($scope.activityData[feedIndex].files).length;
            } else if ($scope.activityData[feedIndex].medias && (Object.keys($scope.activityData[feedIndex].medias).length > 0)) {
                delete $scope.activityData[feedIndex].medias[index];
                $scope.activityData[feedIndex]['commentMediaCount'] = Object.keys($scope.activityData[feedIndex].medias).length;
            }
            if ((Object.keys($scope.activityData[feedIndex].files).length === 0) && (Object.keys($scope.activityData[feedIndex].medias).length === 0)) {
                IsMediaExists = 0;
                $scope.checkEditorData($scope.activityData[feedIndex].ActivityGUID,feedIndex)
                //showButtonLoader('PostBtn-'+$scope.activityData[feedIndex].ActivityGUID);
            }
            angular.element('#' + $scope.activityData[feedIndex].ActivityGUID).focus();
            angular.element('#' + $scope.activityData[feedIndex].ActivityGUID).blur();
          }
        };

        $scope.removeEditAttachement = function (type, mediaData, index) {
            mediaData.splice(index,1);
        };
        
        $scope.addMediaClasses = function (mediaCount) {
            var mediaClass;
            switch (mediaCount) {
                case 1:
                    mediaClass = "post-media single";
                    break;
                case 2:
                    mediaClass = "post-media two";
                    break;;
                default:
                    mediaClass = "row gutter-5 post-media morethan-two";
            }
            return mediaClass;
        };

        $scope.replyToComment = function(comment_guid)
        {
          $('.reply-comment').hide();
          $('#r-'+comment_guid).show();
          //$scope.tagComment('rply-' + comment_guid);
        }

        $scope.getCommentRepliesAnnouncement = function(comment_guid,activity_guid,page_size)
        {
          var jsonData = {
            ParentCommentGUID:comment_guid,
            PageNo:1,
            PageSize:page_size,
            EntityType:'Activity',
            EntityGUID:activity_guid
          };
          WallService.CallApi(jsonData, 'activity/getAllComments').then(function(response) {
            if(response.ResponseCode == 200)
            {
              angular.forEach($scope.group_announcements,function(val,key){
                if(val.ActivityGUID == activity_guid)
                {
                  angular.forEach($scope.group_announcements[key].Comments,function(v,k){
                    if(v.CommentGUID == comment_guid)
                    {
                      $scope.group_announcements[key].Comments[k].Replies = response.Data;
                    }
                  });
                }
              });
            }
          });
        }

        $scope.getCommentReplies = function(comment_guid,activity_guid,page_size)
        {
          var jsonData = {
            ParentCommentGUID:comment_guid,
            PageNo:1,
            PageSize:page_size,
            EntityType:'Activity',
            EntityGUID:activity_guid
          };
          WallService.CallApi(jsonData, 'activity/getAllComments').then(function(response) {
            if(response.ResponseCode == 200)
            {
              angular.forEach($scope.activityData,function(val,key){
                if(val.ActivityGUID == activity_guid)
                {
                  angular.forEach($scope.activityData[key].Comments,function(v,k){
                    if(v.CommentGUID == comment_guid)
                    {
                      $scope.activityData[key].Comments[k].Replies = response.Data;
                    }
                  });
                }
              });
            }
          });
        }

        $scope.hideCommentReplies = function(comment_guid,activity_guid)
        {
          angular.forEach($scope.activityData,function(val,key){
            if(val.ActivityGUID == activity_guid)
            {
              angular.forEach($scope.activityData[key].Comments,function(v,k){
                if(v.CommentGUID == comment_guid)
                {
                  $scope.activityData[key].Comments[k].Replies = [];
                }
              });
            }
          });
        }

        $scope.hideCommentRepliesAnnouncement = function(comment_guid,activity_guid)
        {
          angular.forEach($scope.group_announcements,function(val,key){
            if(val.ActivityGUID == activity_guid)
            {
              angular.forEach($scope.group_announcements[key].Comments,function(v,k){
                if(v.CommentGUID == comment_guid)
                {
                  $scope.group_announcements[key].Comments[k].Replies = [];
                }
              });
            }
          });
        }

        $scope.replyEmit = function(event,comment_guid,activity_guid)
        {
          if (event.which == 13)
          {
            if (!event.shiftKey) {
              event.preventDefault();
              var Comment = $('#rply-'+comment_guid).val();
              if(Comment.trim()=='')
              {
                return;
              }
              var element =$('#act-' + activity_guid + ' .post-as-data');
              var jsonData = {
                  ParentCommentGUID: comment_guid,
                  Comment: Comment,
                  EntityType: 'Activity',
                  EntityGUID: activity_guid,
                  PostAsModuleID: element.attr('data-module-id'),
                  PostAsModuleEntityGUID: element.attr('data-module-entityid'),
              };
              WallService.CallApi(jsonData, 'activity/addComment').then(function(response) {
                $('#rply-'+comment_guid).val('');
                if(response.ResponseCode == 200)
                {
                  angular.forEach($scope.activityData,function(val,key){
                    if(val.ActivityGUID == activity_guid)
                    {
                      angular.forEach($scope.activityData[key].Comments,function(v,k){
                        if(v.CommentGUID == comment_guid)
                        {
                          $scope.activityData[key].Comments[k].NoOfReplies++;
                          $scope.activityData[key].Comments[k].Replies.push(response.Data[0]);
                        }
                      });
                    }
                  });
                }
              });
            }
          }
        }

        $scope.replyEmitAnnouncement = function(event,comment_guid,activity_guid)
        {
          if (event.which == 13)
          {
            if (!event.shiftKey) {
              event.preventDefault();
              var Comment = $('#rply-'+comment_guid).val();
              if(Comment.trim()=='')
              {
                return;
              }
              var element =$('#act-' + $scope.replyOnActivityGUID + ' .post-as-data');
              var jsonData = {
                  ParentCommentGUID: comment_guid,
                  Comment: Comment,
                  EntityType: 'Activity',
                  EntityGUID: activity_guid,
                  PostAsModuleID: element.attr('data-module-id'),
                  PostAsModuleEntityGUID: element.attr('data-module-entityid'),
              };
              WallService.CallApi(jsonData, 'activity/addComment').then(function(response) {
                $('#rply-'+comment_guid).val('');
                if(response.ResponseCode == 200)
                {
                  angular.forEach($scope.group_announcements,function(val,key){
                    if(val.ActivityGUID == activity_guid)
                    {
                      angular.forEach($scope.group_announcements[key].Comments,function(v,k){
                        if(v.CommentGUID == comment_guid)
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

        $scope.commentEmitAnnouncement = function(event, ActivityGUID, feedIndex,cls)
        {
            if (!$(cls+'#cm-' + ActivityGUID + ' li').hasClass('loading-class')) {
                if (($scope.isAttachementUploading === false)) {
                    $scope.appendComment = 1;
                    
                        event.preventDefault();
                      
                        var PComments = $('#cmt-div-' + ActivityGUID + ' .note-editable').html().trim();
                        
                        var attacheMentPromises = [];
                        var Media = [];

                        if(typeof $scope.group_announcements[feedIndex]!=='undefined')
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

                        if(typeof $scope.group_announcements[feedIndex]!=='undefined')
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
                            showButtonLoader('PostBtn-'+ActivityGUID);
                            return false;
                        }
                        $q.all(attacheMentPromises).then(function(data) {
                          if ( ( Media.length == 0 ) && ( PComments == '' ) ) {
                              $(cls+'#cmt-' + ActivityGUID).val('');
                              return;
                          }
                        var element =$('#act-' + ActivityGUID + ' .post-as-data');
                        
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
                          WallService.CallApi(jsonData, 'activity/addComment').then(function(response) {
                              
                              //$(cls+'#cmt-' + ActivityGUID).summernote('reset');
                              //$(cls+'#cmt-' + ActivityGUID).summernote('focus');
                              
                              angular.forEach($scope.group_announcements,function(val,key){
                                if(val.ActivityGUID == ActivityGUID)
                                {
                                    feedIndex = key;
                                }
                              });
                              if(typeof $scope.group_announcements[feedIndex]!=='undefined')
                              {
                                var newArr = $scope.group_announcements[feedIndex].Comments;
                                  newArr.push(response.Data[0]);
                                  $scope.group_announcements[feedIndex].Comments = newArr.reduce(function(o, v, i) {
                                      o[i] = v;
                                      return o;
                                  }, {});
                                  if(typeof $scope.group_announcements[feedIndex].medias!=='undefined')
                                  {
                                    $scope.group_announcements[feedIndex].medias = {};
                                  }
                                  if(typeof $scope.group_announcements[feedIndex].files!=='undefined')
                                  {
                                    $scope.group_announcements[feedIndex].files = {};
                                  }
                                  $scope.group_announcements[feedIndex]['commentMediaCount'] = 0;
                                  $scope.group_announcements[feedIndex]['commentFileCount'] = 0;
                                  mediaCurrentIndex = 0;
                                  fileCurrentIndex = 0;
                                  $scope.group_announcements[feedIndex].Comments = newArr;
                                  $scope.group_announcements[feedIndex].NoOfComments = parseInt($scope.group_announcements[feedIndex].NoOfComments) + 1;
                                  $scope.group_announcements[feedIndex].comntData = $scope.$broadcast('appendComntEmit', $scope.activityData[feedIndex].Comments); //getPostComments($scope.activityData[key].Comments);
                                  $(cls+'#upload-btn-' + ActivityGUID).show();
                                  $(cls+'#cm-' + ActivityGUID).html('');
                                  $(cls+'#cm-' + ActivityGUID + ' li').remove();
                                  $(cls+'#cm-' + ActivityGUID).hide();
                                  $(cls+'#act-' + ActivityGUID + ' .attach-on-comment').show();
                                  $scope.activityData[feedIndex].IsSubscribed = 1;
                                  /*setTimeout(function() {
                                      $(cls+cls+'#cmt-' + ActivityGUID).trigger('focus');
                                  }, 200);*/
                                  angular.element('#' + $scope.group_announcements[feedIndex].ActivityGUID).focus();
                                  angular.element('#' + $scope.group_announcements[feedIndex].ActivityGUID).blur();
                              }
                              $scope.show_comment_box = "";
                              $timeout(function() {
                                $scope.$apply();
                              }, 500);
                          });

                        });
                    
                }
            }
        }

        $scope.changeLikeStatus = function(sticky)
        {
          if(sticky.IsLike==1)
          {
            sticky.IsLike = 0;
            sticky.NoOfLikes--;
          }
          else
          {
            sticky.IsLike = 1;
            sticky.NoOfLikes++;
          }
        }

        var IsMediaExists = 0;
        $scope.commentEmit = function (event, ActivityGUID, feedIndex,cls) {
            if (!$(cls+'#cm-' + ActivityGUID + ' li').hasClass('loading-class')) {
                if (($scope.isAttachementUploading === false)) {
                    
                    showButtonLoader('PostBtn-'+ActivityGUID);
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


                        angular.forEach($scope.activityData,function(val,key){
                          if(val.ActivityGUID == ActivityGUID)
                          {
                            feedIndex = key;
                          }
                        });

                        if(typeof $scope.activityData[feedIndex]!=='undefined')
                        {
                            if ($scope.activityData[feedIndex].medias && (Object.keys($scope.activityData[feedIndex].medias).length > 0)) {
                                angular.forEach($scope.activityData[feedIndex].medias, function (attachement, key) {
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

                        if(typeof $scope.activityData[feedIndex]!=='undefined')
                        {
                            if ($scope.activityData[feedIndex].files && (Object.keys($scope.activityData[feedIndex].files).length > 0)) {
                                angular.forEach($scope.activityData[feedIndex].files, function (attachement, key) {
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
                            hideButtonLoader('PostBtn-'+ActivityGUID);
                            return false;
                        }
                        $q.all(attacheMentPromises).then(function(data) {
                          if ( ( Media.length == 0 ) && ( PComments == '' ) ) {
                              $(cls+'#cmt-' + ActivityGUID).val('');
                              return;
                          }
                        var element =$('#act-' + ActivityGUID + ' .post-as-data');
                        
                        if (PComments != "") {
                            PComments = $.trim(filterPContent(PComments));
                        }
                          var jsonData = {
                              Comment: PComments,
                              EntityType: 'Activity',
                              EntityGUID: ActivityGUID,
                              Media: Media,
                              //EntityOwner: $(cls+'#act-' + ActivityGUID + ' .module-entity-owner').val(),
                              PostAsModuleID: element.attr('data-module-id'),
                              PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                          };
                          WallService.CallApi(jsonData, 'activity/addComment').then(function(response) {
                              
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
                              
                              angular.forEach($scope.activityData,function(val,key){
                                if(val.ActivityGUID == ActivityGUID)
                                {
                                    feedIndex = key;
                                }
                              });
                              if(typeof $scope.activityData[feedIndex]!=='undefined')
                              {
                                var newArr = $scope.activityData[feedIndex].Comments;
                                  newArr.push(response.Data[0]);
                                  $scope.activityData[feedIndex].Comments = newArr.reduce(function(o, v, i) {
                                      o[i] = v;
                                      return o;
                                  }, {});
                                  if(typeof $scope.activityData[feedIndex].medias!=='undefined')
                                  {
                                    $scope.activityData[feedIndex].medias = {};
                                  }
                                  if(typeof $scope.activityData[feedIndex].files!=='undefined')
                                  {
                                    $scope.activityData[feedIndex].files = {};
                                  }
                                  $scope.activityData[feedIndex]['commentMediaCount'] = 0;
                                  $scope.activityData[feedIndex]['commentFileCount'] = 0;
                                  mediaCurrentIndex = 0;
                                  fileCurrentIndex = 0;
                                  $scope.activityData[feedIndex].Comments = newArr;
                                  $scope.activityData[feedIndex].NoOfComments = parseInt($scope.activityData[feedIndex].NoOfComments) + 1;
                                  $scope.activityData[feedIndex].comntData = $scope.$broadcast('appendComntEmit', $scope.activityData[feedIndex].Comments); //getPostComments($scope.activityData[key].Comments);
                                  $(cls+'#upload-btn-' + ActivityGUID).show();
                                  $(cls+'#cm-' + ActivityGUID).html('');
                                  $(cls+'#cm-' + ActivityGUID + ' li').remove();
                                  $(cls+'#cm-' + ActivityGUID).hide();
                                  $(cls+'#act-' + ActivityGUID + ' .attach-on-comment').show();
                                  $scope.activityData[feedIndex].IsSubscribed = 1;
                                  /*setTimeout(function() {
                                      $(cls+cls+'#cmt-' + ActivityGUID).trigger('focus');
                                  }, 200);*/
                                  angular.element('#' + $scope.activityData[feedIndex].ActivityGUID).focus();
                                  angular.element('#' + $scope.activityData[feedIndex].ActivityGUID).blur();
                              }


                              if(IsNewsFeed=='1')
                              {
                                angular.forEach($scope.popularData,function(val,key){
                                if(val.ActivityGUID == ActivityGUID)
                                    {
                                        feedIndex = key;
                                    }
                                });
                                if(typeof $scope.popularData[feedIndex]!=='undefined')
                                {
                                  var newArr = $scope.popularData[feedIndex].Comments;
                                  newArr.push(response.Data[0]);
                                  $scope.popularData[feedIndex].Comments = newArr.reduce(function(o, v, i) {
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
                                  $scope.popularData[feedIndex].NoOfComments = parseInt($scope.activityData[feedIndex].NoOfComments) + 1;
                                  $scope.popularData[feedIndex].comntData = $scope.$broadcast('appendComntEmit', $scope.activityData[feedIndex].Comments); //getPostComments($scope.activityData[key].Comments);

                                  $(cls+'#upload-btn-' + ActivityGUID).show();
                                  $(cls+'#cm-' + ActivityGUID).html('');
                                  $(cls+'#cm-' + ActivityGUID + ' li').remove();
                                  $(cls+'#cm-' + ActivityGUID).hide();
                                  $(cls+'#act-' + ActivityGUID + ' .attach-on-comment').show();
                                  $scope.activityData[feedIndex].IsSubscribed = 1;

                                  angular.element('#' + $scope.popularData[feedIndex].ActivityGUID).focus();
                                  angular.element('#' + $scope.popularData[feedIndex].ActivityGUID).blur();
                                  /*$timeout(function() {
                                      $(cls+'#cmt-' + ActivityGUID).trigger('focus');
                                  }, 200);*/
                                }
                              }
                              
                              $timeout(function() {
                                $scope.$apply();
                              }, 500);

                              /*angular.forEach($scope.activityData,function(val,key){
                                $('#cmt-div-'+val.ActivityGUID+' .place-holder-label').show();
                                $('#cmt-div-'+val.ActivityGUID+' .comment-section').addClass('hide');
                              });*/
                             // $('#cmt-div-'+ActivityGUID+' .place-holder-label').show();
                              $scope.show_comment_box = "";
                          });

                        });
                    
                }
            }
        }
        $scope.EditcommentEmit = function (event, ActivityGUID, commentData, IsAnnouncement, FeedIndex) {
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
          $q.all(attacheMentPromises).then(function (data) {
            if ((Media.length == 0) && (PComments == '')) {
              $(cls + '#cmt-' + ActivityGUID).val('');
              return;
            }
            var element = $('#act-' + ActivityGUID + ' .post-as-data');
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
              if ( IsAnnouncement == 1 ) {
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
                var val = $scope.activityData[FeedIndex];
                angular.forEach(val.Comments, function (v, k) {
                  if (v.CommentGUID == CommentGUID)
                  {
                    $scope.activityData[FeedIndex].Comments[k] = response.Data[0];
                  }
                })

                /*angular.forEach($scope.activityData, function (val, key) {
                  if (val.ActivityGUID == ActivityGUID)
                  {
                    feedIndex = key;
                    angular.forEach(val.Comments, function (v, k) {
                      if (v.CommentGUID == CommentGUID)
                      {
                        $scope.activityData[key].Comments[k] = response.Data[0];
                      }
                    })
                  }
                });*/
              }
              
              // $scope.$apply();
              //$('#comment-view-block-' + CommentGUID).show();
              //$('#comment-edit-block-' + CommentGUID).addClass('hide');
              $scope.edit_comment_box = "";



            });

          });
        }
        $scope.setFavouriteEmit = function (ActivityGUID) {
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'favourite/toggle_favourite').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                            if ($scope.activityData[key].IsFavourite == 1) {
                                $scope.activityData[key].IsFavourite = 0;
                                $scope.tfr--;
                                if ($scope.tfr == 0) {
                                }
                                if ($('#ActivityFilterType').val() == 'Favourite') {
                                    $scope.activityData.splice(key,1);
                                    //$('#act-' + ActivityGUID).hide();
                                }
                                return false;
                            } else {
                                $scope.activityData[key].IsFavourite = 1;
                                $scope.tfr++;
                            }
                        }
                    });

                    if($scope.group_announcements.length>0)
                    {
                      $($scope.group_announcements).each(function (key, value) {
                        if ($scope.group_announcements[key].ActivityGUID == ActivityGUID) {
                            if ($scope.group_announcements[key].IsFavourite == 1) {
                                $scope.group_announcements[key].IsFavourite = 0;
                                $scope.tfr--;
                                if ($scope.tfr == 0) {
                                }
                                if ($('#ActivityFilterType').val() == 'Favourite') {
                                    $scope.group_announcements.splice(key,1);
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
                                    $scope.group_announcements.splice(key,1);
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
        $scope.loadLinkTags = function ($query)
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


        $scope.parseLinks = [];
        $scope.allreadyProcessedLinks = [];
        $scope.parseLinkData = function (url) {
                $scope.UrlThumbGenerate = false;
                $scope.UrlToCompare = url;

                var postContent = $(".note-editable").text().trim();
                if($scope.activePostType!==1 && $('#PostTitleInput').val().length<=140 && $scope.titleKeyup==0 && !$scope.edit_post)
                {
                  $('#PostTitleInput').val(postContent);
                  $('#PostTitleLimit').html(140-postContent.length+' characters');
                  if(140-postContent.length == 1)
                  {
                    $('#PostTitleLimit').html('1 character');
                  }
                  if($('#PostTitleInput').val().length>140)
                  {
                    $('#PostTitleInput').val(postContent.substring(0,140));
                    $('#PostTitleLimit').html('0 characters');
                  }
                }

                if($('#PostTitleInput').val().length>0)
                {
                  if(!$('#PostTitleInput').hasClass('title-focus'))
                  {
                    $('#PostTitleInput').addClass('title-focus');
                  }
                }
                else
                {
                  if($('#PostTitleInput').hasClass('title-focus'))
                  {
                    $('#PostTitleInput').removeClass('title-focus');
                  }
                }

                if (postContent  || $scope.fileCount>0 || $scope.mediaCount){
                    //$scope.noContentToPost = false;
                } else {
                    //$scope.noContentToPost = true;
                }
                if (!$scope.isValidURL(url)) {
                    return false;
                } else {
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
                      linkPromises.push(makeResolvedPromise(linkUrl).then(function(datUrl){
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
                              }
                              else
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

        $scope.commentpostchange = function()
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
            $('#IconSelect').attr('class', 'icon-every');
            if(IsAdminView == '1')
            {
              $scope.postasuser = [];
              angular.element(document.getElementById('UserListCtrl')).scope().entities = [];
            }
        }

        $scope.changeActionAs = function(value,activity_guid)
        {
          angular.forEach($scope.activityData,function(val,key){
            if(val.ActivityGUID == activity_guid)
            {
              $scope.activityData[key].actionas = value;
              WallService.CallApi({UserID:value.UserID,ActivityID:val.ActivityID}, 'activity/is_dummy_user_like').then(function (response) {
                $scope.activityData[key].IsLike = response.Data;
              });
            }
          });
        }

        $scope.dummy_activity_data = [];
        $scope.dummy_activity_data = function()
        {
          var reqData = {};
          WallService.CallApi(reqData, appInfo.serviceUrl + 'admin_api/adminactivity/dummy_activities').then(function (response) {

          });
        }

        $scope.setpostasuser = function(val)
        {
          $scope.postasuser = val;
          $scope.PostAs = [];
          $scope.postasgroup = [];
          $scope.get_all_group_of_user($scope.postasuser.UserID);
        }

        $scope.showMediaFigure = function (EntityGUID) {
            $($scope.activityData).each(function (k, v) {
                if (v.ActivityGUID == EntityGUID) {
                    $scope.activityData[k].showMedia = 1;
                    $scope.$apply();
                }
            })
        }
        $scope.viewActivity = function (EntityGUID) {
            jsonData = {
                EntityType: 'Activity',
                EntityGUID: EntityGUID
            };
            
            if(LoginSessionKey=='')
            {
                return false;
            }
            WallService.CallApi(jsonData, 'log').then(function (response) {
                $($scope.activityData).each(function (k, v) {
                    if ($scope.activityData[k].ActivityGUID == EntityGUID) {
                        $scope.activityData[k].Viewed = 1;
                    }
                });
            });
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
            angular.forEach($scope.activityData, function (v, k) {
                if (v.ActivityGUID == activity_guid) {
                    if (typeof $scope.activityData[k].ReminderData == 'undefined')
                    {
                        $scope.activityData[k].ReminderData = prepareReminderData($scope.activityData[k].Reminder);
                    }
                    if (type == '1') {
                        $scope.activityData[k].ReminderData.UndoDateTime = TomorrowDate;
                    } else {
                        $scope.activityData[k].ReminderData.UndoDateTime = NextWeekDate;
                    }
                }
            });
            if(IsNewsFeed=='1')
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

        $scope.textToLink = function (inputText, onlyShortText,count) {
          if (typeof inputText !== 'undefined' && inputText !== null) {
            inputText = inputText.toString();
            inputText=inputText.replace(new RegExp('contenteditable', 'g'), 'contenteditabletext');
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
            replacedText = replacedText.replace(" ||| ", "<br>");
            replacedText = checkTaggedData(replacedText);
            var repTxt = removeTags(replacedText);
            var totalwords = 200;
            if($('#IsForum').length>0)
            {
              totalwords = 80;
              if(count)
              {
                totalwords = count;
              }
            }

            if($scope.IsSinglePost)
            {
              replacedText = $sce.trustAsHtml(replacedText);
              return replacedText
            }

            if ( repTxt && ( repTxt.length > totalwords ) ) {
              if (onlyShortText) {
                replacedText = '<span class="show-less">' + smart_substr(totalwords, replacedText) + '... </span>';
              } else {
                replacedText = '<span class="show-less">' + smart_substr(totalwords, replacedText) + '... <a onclick="showMoreComment(this);">See More</a></span><span class="show-more">' +
                        replacedText + '</span>';
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
              if ( matched[1] ) {
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
            $('.loader-fad,.loader-view').css('display', 'block');
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
                SearchKey:$('#srch-filters2').val()
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
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = prepareReminderData(jsonData, 1);
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
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = [];
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'].ReminderGUID = '';
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
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = prepareReminderData(jsonData, 1);
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('setDate', new Date($scope.UndoReminderData.UndoDateTime.replace(/-/gi, ' ')));
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('refresh');
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
                  if($scope.UndoReminderData)
                  {

                    $scope.activityData[$scope.UndoReminderData.ActivityKey].IsArchive = 1;
                    $scope.CloseUndoPopup();
                  }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        $scope.saveReminder = function (ActivityGUID, Status, ReminderGUID) {
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
                var Hours = CalendarPicker.children('ul.hours').find('li.reminderSet span input').val();
                var Minutes = CalendarPicker.children('ul.minutes').find('li.reminderSet span input').val();
                
                if ( CalendarPicker.children('ul.hours').find('li span.selected input').val() ) {
                  Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
                }
                if ( CalendarPicker.children('ul.minutes').find('li span.selected input').val() ) {
                  Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();
                }
//                Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
//                Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();
                if ($scope.selectedDate[ActivityGUID] == undefined) {
                    showResponseMessage('Select date for reminder', 'alert-danger');
                    return;
                }
                if ( !( ( AmPm.toLowerCase() == 'am' ) || ( AmPm.toLowerCase() == 'pm' ) ) ) {
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
            var curDateToCompare = moment().format('YYYY-MM-DD h:m:s A');
            if ( !moment(selectedDateTime).isAfter(curDateToCompare) ) {
              showResponseMessage('Invalid time for reminder', 'alert-danger');
              return;
            }
            var jsonData = {
                ActivityGUID: ActivityGUID,
                ReminderDateTime: selectedDateTime,
                Status: Status,
                ReminderGUID: ReminderGUID
            };
            selectedDateTime = selectedDateTime.replace(/-/gi, ' ');
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
                        angular.forEach($scope.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                $scope.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
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

                        if(!$scope.UndoReminderData)
                        {
                          angular.forEach($scope.popularData, function(val, key) {
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
                        $scope.UndoReminderData.Heading = 'Reminder Added';
                        $scope.UndoReminderData.action = 'add';
                        $scope.undoPopUp();
                        $scope.trr++;
                    }
                    if (Action == 'edit') {
                        angular.forEach($scope.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                                $scope.UndoReminderData.ReminderGUID = ReminderGUID;
                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                $scope.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
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
                        
                        if(!$scope.UndoReminderData)
                        {
                          angular.forEach($scope.popularData, function(val, key) {
                              if (val.ActivityGUID == ActivityGUID) {
                                  //Store temporary data for Undo purpose
                                  $scope.UndoReminderData = $scope.popularData[key]['ReminderData'];
                                  $scope.UndoReminderData.ActivityKey = key;
                                  $scope.UndoReminderData.Status = response.Data.Status;
                                  $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                                  $scope.UndoReminderData.ReminderGUID = ReminderGUID;
                                  reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                  $scope.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
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
        $scope.IsHourDisabled = function (ActivityGUID, Hour) {
            angular.forEach($scope.activityData, function (v, k) {
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
            angular.forEach($scope.activityData, function (v, k) {
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
                    angular.forEach($scope.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            if (Status == 'DELETED' || Status == 'ARCHIVED' || Status == 'ACTIVE') {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;

                                // blank original activity data
                                if ($scope.IsReminder != 1 || Status == 'DELETED') {
                                    $scope.activityData.splice(key, 1);
                                } else {
                                    angular.forEach($scope.activityData, function (v, k) {
                                        if (v.ActivityGUID == ActivityGUID) {
                                            if (Status == 'ARCHIVED') {
                                                $scope.activityData[k]['IsArchive'] = 1;
                                            } else {
                                                $scope.activityData[k]['IsArchive'] = 0;
                                            }
                                        }
                                    });
                                }
                                //$scope.activityData[key]['ReminderData'] = [];
                                //$scope.activityData[key]['ReminderData'].ReminderGUID = '';

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
//            console.log('reminderDropDownBox : ', angular.element('#reminderDropDownBox' + ActivityGUID).find('[data-type="reminderFooter"]'));
//            return false;
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            //Store temporary data for Undo purpose
                            $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
                            $scope.UndoReminderData.ActivityKey = key;
                            $scope.UndoReminderData.Status = response.Data.Status;
                            $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                            // blank original activity data
//                            $scope.activityData[key]['ReminderData'] = [];
                            $scope.activityData[key]['ReminderData'] = $scope.prepareReminderData(new Array());
                            $scope.activityData[key]['ReminderData'].ReminderGUID = '';
                            angular.element('#reminderDropDownBox' + ActivityGUID).find('[data-type="reminderFooter"]').show();
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
            html = '<div class="reminder-remove fadeInUp" id="undoPopUp">';
            html += '<span class="pull-left">' + $scope.UndoReminderData.Heading + '.</span>';
            html += '<span class="pull-right">';
            html += '<a href="javascript:void(0)" title="" ng-click="undoReminder()">Undo</a>';
            html += '<i class="icon-n-close" ng-click="CloseUndoPopup()"></i>';
            html += '</span>';
            html += '</div>';
            var $el = angular.element(html);
            $('body').append($el).fadeIn(200);
            $compile($el)($scope);
            setTimeout(function () {
                $('#undoPopUp').fadeOut();
                if ($scope.UndoReminderData.isArchive && $scope.UndoReminderData.Status != 'ARCHIVED') {
                    $scope.activityData.splice($scope.UndoReminderData.Key, 1);
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
                    angular.forEach($scope.activityData, function (val, key) {
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


        $scope.viewAllComntEmitAnnouncement = function (i, ActivityGUID,filter) { //    Call above webservice here for View All Comments
            
            var element =$('#act-' + ActivityGUID + ' .post-as-data');
            var reqData = {
                EntityGUID: ActivityGUID,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
            };
            if(typeof filter!=='undefined')
            {
              reqData['Filter'] = filter;
            }
            $("#cmt_loader_" + ActivityGUID).show();
            WallService.CallApi(reqData, 'activity/getAllComments').then(function (response) { 
                if (response.ResponseCode === 200) {
                    var tempComntData = response.Data;
                    if ( $scope.group_announcements_single && $scope.group_announcements_single[0] && $scope.group_announcements_single[0].Comments ) {
                        $scope.group_announcements_single[0].Comments = tempComntData;
                        $scope.group_announcements_single[0].viewStats = 0;
                    }
                }
            });
        }


        // View All Comments
        $scope.viewAllComntEmit = function (i, ActivityGUID,filter) { //    Call above webservice here for View All Comments
            
            var element =$('#act-' + ActivityGUID + ' .post-as-data');
            var reqData = {
                EntityGUID: ActivityGUID,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
            };
            if(typeof filter!=='undefined')
            {
              reqData['Filter'] = filter;
            }
            $("#cmt_loader_" + ActivityGUID).show();
            WallService.CallApi(reqData, 'activity/getAllComments').then(function (response) { 
                if (response.ResponseCode === 200) {
                    var tempComntData = response.Data;
                    angular.forEach($scope.activityData,function(val,key){
                      if(val.ActivityGUID == ActivityGUID)
                      {
                        $scope.activityData[key].Comments = tempComntData;
                        $scope.activityData[i].viewStats = 0;
                      }
                    });
                    if(IsNewsFeed=='1')
                    {
                      var tempComntData = response.Data;
                      if( $scope.popularData && $scope.popularData[i] && $scope.popularData[i].Comments ){
  //                      if( $scope.activityData[i].Comments.length > 0 )
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
        
        $scope.likeDetailsEmit = function (EntityGUID, EntityType) {
          if ( ( ( $scope.totalLikes === 0 ) || ( $scope.likeDetails.length < $scope.totalLikes ) ) && !$scope.isLikeDetailsProcessing ) {
            $scope.isLikeDetailsProcessing = true;
            var likePageNo = $('#LikePageNo').val(),
                jsonData = {};
            if ((likePageNo == 1) && EntityGUID) {
              $scope.LastLikeActivityGUID = EntityGUID;
              $scope.LastLikeEntityType = EntityType;
            }
            jsonData = {
              EntityGUID: $scope.LastLikeActivityGUID,
              EntityType: $scope.LastLikeEntityType,
              PageNo: likePageNo,
              PageSize: 8
            };
            WallService.CallApi(jsonData, 'activity/getLikeDetails').then(function (response) {
              if (response.ResponseCode == 200) {
                if (!$('#totalLikes').is(':visible')) {
                  $('#totalLikes').modal('show');
                }
                if (response.Data.length > 0) {
                  if ($scope.likeDetails.length === 0) {
                    $scope.likeDetails = angular.copy(response.Data);
                  } else {
                    $scope.likeDetails = $scope.likeDetails.concat(response.Data);
                  }
                  $scope.totalLikes = parseInt(response.TotalRecords);
                  $scope.likeMessage = '';
                  $('#LikePageNo').val(parseInt(likePageNo) + 1);
                } else if ($scope.likeDetails.length === 0) {
                  $scope.likeDetails = [];
                  $scope.totalLikes = 0;
                  $scope.likeMessage = 'No liked yet.';
                }
              }
              $scope.isLikeDetailsProcessing = false;
            });
          }
          if (parseInt($('#LikePageNo').val()) == 1) {
            $('#totalLikes').on('hide.bs.modal', function () {
              $scope.likeDetails = [];
              $scope.totalLikes = 0;
              $scope.likeMessage = 'No liked yet.';
              $('#LikePageNo').val(1);
            });
          }
        }

        $scope.seenMessage = '';
        $scope.totalSeen = 0;
        $scope.LastSeenActivityGUID = '';
        $scope.LastSeenEntityType = '';
        $scope.seenDetails = [];
        $scope.isSeenDetailsProcessing = false;


        $scope.privacyEmit = function (ActivityGuID, privacy) {
            jsonData = {
                ActivityGuID: ActivityGuID,
                Visibility: privacy
            };
            WallService.CallApi(jsonData, 'activity/privacyChange').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == ActivityGuID) {
                            $scope.activityData[key].Visibility = privacy;
                        }
                    });
                }
            });
        }
        $scope.likeStatusEmit = function (ActivityGUID,data) {
            
            var element =$('#act-' + ActivityGUID + ' .post-as-data');
            element.attr('data-module-id',data.ModuleID);
            element.attr('data-module-entityid',data.ModuleEntityGUID);
             $('#act-' + ActivityGUID + ' .show-pic').attr('src', image_server_path + 'upload/profile/36x36/' +data.ProfilePicture);
            $('#act-' + ActivityGUID + ' .current-profile-pic').attr('src', image_server_path + 'upload/profile/36x36/' +data.ProfilePicture);
            jsonData = {
                PostAsModuleID: data.ModuleID,
                PostAsModuleEntityGUID: data.ModuleEntityGUID,
                ActivityGUID: ActivityGUID
            };
            WallService.CallApi(jsonData, 'activity/checkLikeStatus').then(function (response) {
                $($scope.activityData).each(function (k, v) {
                    if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                        $scope.activityData[k].viewStats = 1;
                        $scope.activityData[k].IsLike = response.Data.IsLike;
                        $scope.activityData[k].Comments = response.Data.Comments;
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
                    $($scope.activityData).each(function (key, val) {
                        if (ActivityGUID == $scope.activityData[key].ActivityGUID) {
                            if ($scope.activityData[key].IsSticky == 0) {
                                $scope.activityData[key].IsSticky = 1;
                                var newD = $scope.activityData[key];
                                $scope.activityData.splice(key, 1);
                                $scope.activityData.splice(0, 0, newD);
                                return false;
                            } else {
                                $scope.activityData[key].IsSticky = 0;
                                var newD = $scope.activityData[key];
                                if ($scope.activityData.length > 1) {
                                    $scope.activityData.splice(key, 1);
                                    $($scope.activityData).each(function (k, v) {
                                        if ($scope.activityData[k].IsSticky == 0) {
                                            $scope.activityData.splice(k, 0, newD);
                                            return false;
                                        }
                                    });
                                    if (!append) {
                                        $scope.activityData.splice($scope.activityData.length, 0, newD);
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

        /*$scope.SubmitShareByEmail = function(){
          console.log('here one');
          var val = checkstatus('ShareByEmailForm');
          console.log('here two');
          if(val===false) {return;}
          console.log('here three');
          var emails=$scope.ShareByEmail.emails.split(',');
          var emailarray=new Array();
          if(emails.length>0)
          {
              for(i=0; i<emails.length; i++)
              {
                      emailarray.push(emails[i].trim());
              }
          }
          showButtonLoader('nativesendinvitaion');
          var reqData = {emails:emails, message:$scope.ShareByEmail.message,link:$scope.ShareByEmail.link};
          WallService.CallApi(reqData, 'activity/share_post_by_email').then(function(response) {
              hideButtonLoader('nativesendinvitaion');
              if (response.ResponseCode == 200) {
                  showResponseMessage(response.Message, 'alert-success');
                  $('#emailServiceModal').modal('hide');
              } else {
                  showResponseMessage(response.Message, 'alert-danger');
              }
          });
        }*/

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
                $scope.is_busy = false;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Poll updated successfully.', 'alert-success');
                    $('#' + PollGUID + ' .poll-expiry').addClass('hide');
                    $($scope.activityData).each(function (key, value)
                    {
                        if ($scope.activityData[key].ActivityGUID == ActivityGUID)
                        {
                            PollScope = angular.element(document.getElementById('p-' + $scope.activityData[key].PollData[0].PollGUID)).scope();
                            //$scope.activityData[key].PollData[0].IsExpired = 0;
                            if (req_data.ExpireDuration == -1)
                            {
                                $scope.activityData[key].PollData[0].IsExpired = 1;
                            } else
                            {
                                $scope.activityData[key].PollData[0].ExpiryDateTime = response.ExpireDatetime;
                            }
                        }
                    });
                }
            });
        }
        $scope.ShowVoteOptionAdminEmit = function (ActivityGUID)
        {
            $($scope.activityData).each(function (key, value)
            {
                if ($scope.activityData[key].ActivityGUID == ActivityGUID)
                {
                    PollScope = angular.element(document.getElementById('p-' + $scope.activityData[key].PollData[0].PollGUID)).scope();
                    if (!$scope.activityData[key].PollData[0].ShowVoteOptionToAdmin)
                    {
                        $scope.activityData[key].PollData[0].ShowVoteOptionToAdmin = 1;
                    } else
                    {
                        $scope.activityData[key].PollData[0].ShowVoteOptionToAdmin = 0;
                    }
                }
            });
        }
        $scope.seeMoreLink = function (ActivityGUID) {
            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == ActivityGUID)
                {
                    $scope.activityData[key].ShowMoreHide = '1';
                    $scope.activityData[key].showAllLinks = '1';
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
                    $($scope.activityData).each(function (key, value)
                    {
                        if ($scope.activityData[key].ActivityGUID == ActivityGUID)
                        {
                            if(typeof $scope.activityData[key].PollData[0]!=='undefined')
                            {
                                $($scope.activityData[key].PollData[0].Options).each(function (k, v)
                                {
                                    if (v.OptionGUID == OptionGUID)
                                    {
                                        $scope.activityData[key].PollData[0].Options[k].NoOfVotes = response.Data.Count.OptionVoted;
                                    }
                                    $scope.activityData[key].PollData[0].Options[k].pollTotalVotes = response.Data.Count.TotalVotes;
                                });
                                PollScope = angular.element(document.getElementById('p-' + $scope.activityData[key].PollData[0].PollGUID)).scope();
                                PollScope.createChart($scope.activityData[key].PollData[0].PollGUID, $scope.activityData[key].PollData[0].Options);
                                $scope.activityData[key].PollData[0].IsVoted = 1;
                                if ($scope.activityData[key].PollData[0].IsOwner == 1)
                                {
                                    $scope.activityData[key].PollData[0].ShowVoteOptionToAdmin = 0;
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
        };
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
        $scope.filterPostType = function(post_type)
        {
          if(post_type!==$scope.PostType)
          {
            $scope.Filter.IsSetFilter=true;
            $scope.PostType = post_type.Value;
            $scope.PostTypeLabel = post_type.Label;
            $scope.PostTypeName = post_type.Label;
            setTimeout(function(){
            $scope.getFilteredWall();
            },500);
          }
        }

        $scope.imageUpload = function(files) {
          //console.log('files');
        }


        $scope.settingEnabled = function (settingName) {
            if (settingName != undefined && settingName != 0) {
                return true;
            } else {
                return false;
            }
        }
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
            var PContent = $.trim($('#wallpostform .textntags-beautifier div').html());
            if (PContent != "")
            {
                PContent = $.trim(filterPContent(PContent,'Poll'));
            }

            $('#wallpostform .textntags-beautifier div').html('');
            jsonData['Description'] = PContent;
            jsonData['Media'] = media;

            jsonData['ExpiryDateTime'] = $scope.ExpiryDateTime;
            if ($scope.ExpiryDateTime == undefined)
            {
                jsonData['ExpiryDateTime'] = '';
            }
            jsonData['Commentable'] = $('#comments_settings').val();
            jsonData['Visibility'] = $('#visible_for').val();
            jsonData['IsAnonymous'] = $scope.is_anonymous;
            jsonData['Options'] = optionDetail;
            jsonData['PostAsModuleID'] = $scope.FromModuleID;
            jsonData['PostAsModuleEntityGUID'] = $scope.FromModuleEntityGUID;
            jsonData['PollFor'] = $scope.group_and_users_tags;
           // $('#PollDescription,#PostContent').textntags('reset');
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

                if ($('#PollPrivacy').length > 0)
                {
                    $('#PollPrivacy').html("<i class='icon-every'></i><span class='caret'></span>");
                }

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
                    if ($scope.activityData.length > 0) {
                        $($scope.activityData).each(function (k, v) {
                            if ($scope.activityData[k].IsSticky == 0) {
                                $scope.activityData.splice(k, 0, response.Data[0]);

                                return false;
                            }
                        });
                    } else {
                        $scope.activityData.push(response.Data[0]);
                    }

                    $scope.tr++;

                    setTimeout(
                            function () {
                                if (!$scope.IsActiveFilter) {
                                    if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                        $('#FilterButton').show();
                                    } else {
                                        $('#FilterButton').hide();
                                    }
                                }
                            }, 1000
                            );

                    $('#visible_for').val(1);

                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                hideButtonLoader('ShareButton');

                $('.upload-view').remove();
                $scope.PostAsModuleID = '3';
            });
            setTimeout(function () {
                $('.wallloader').hide();
                showHidePhotoVideoIcon();
            }, 500);
        }

        $scope.admin_messages = [];
        $scope.get_admin_messages = function (EntityType)
        {
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
            if(field_key == 'Education' || field_key == 'WorkExperience')
            {
              var start_year  = $scope.questions['StartYear'];
              var end_year    = $scope.questions['EndYear'];
              if(start_year>end_year)
              {
                showResponseMessage('Please insert valid date.', 'alert-danger');
                callapi = false;
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
            },10000);
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
            $("#" + id).autocomplete({
                source: function (request, response) {
                    $.getJSON(
                            "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + request.term,
                            function (data) {
                                if (data == "") {
                                    response();
                                } else {
                                    response(data);
                                }
                            }
                    );
                },
                minLength: 3,
                select: function (event, ui) {
                    var selectedObj = ui.item;
                    var countryCode = getcitydetails(selectedObj.value, id);
                    $("#" + id).val(selectedObj.value);
                    $('#hidden_address_check').val(selectedObj.value);
                    return false;
                },
                open: function () {
                    $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                },
                close: function () {
                    $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                }
            });
            $("#" + id).autocomplete("option", "delay", 100);
        }

        $scope.redirectToBaseLink = function(link)
        {
            window.top.location = link;
        }





        // $scope.nextPage = function(){
        //   if(IsAdminView == 0)
        //         { 
        //             if ($scope.IsReminder == 1 && $scope.trr == $scope.activityData.length) {
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

        $(document).ready(function () {
            $(window).scroll(function () { 
                /*if(IsAdminView == 0)
                {*/
                  var scrollInterval = 450;
                  if ($scope.PageNo > 1)
                  {
                      scrollInterval = scrollInterval * ($scope.PageNo / 2);
                  }
                  var pScroll = $(window).scrollTop();
                  var pageBottomScroll1 = parseInt($(document).height()) - parseInt($(window).height()) - scrollInterval;
                  if (pScroll >= pageBottomScroll1) {
                      setTimeout(function () {
                          if (pScroll >= pageBottomScroll1 && !$scope.busy) {
                              if ($scope.IsReminder == 1 && $scope.trr == $scope.activityData.length) {
                                  return;
                              }
                              if($('#IsWiki').length>0)
                              {
                               $scope.get_wiki_post(); 
                              } else if(IsFileTab=='0' && $scope.isDiscussionPost==0)
                              {
                                  if((typeof IsAdminDashboard=='undefined' && typeof IsAdminActivity!=='undefined') || IsAdminView==0)
                                  {
                                    if(IsAdminView==0)
                                    {
                                      $scope.GetwallPost();
                                    }
                                  }
                              }
                          }
                      }, 200);
                  }
                /*}*/
            });

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

        if(IsAdminView == '0')
        {
          socket.emit('JoinUser', {
              UserGUID: LoggedInUserGUID
          });
        }


        socket.on('RecieveLiveFeed', function (data) {
            if($scope.isDiscussionPost ==1){
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

                            /*if(val.Type==data.Type && val.Users[0].ProfileURL==response.Data[0].Users[0].ProfileURL)
                             {
                             $scope.LiveFeeds.splice(key,1);
                             }*/
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

            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == data.ActivityGUID) {
                    $scope.activityData.splice(key, 1);
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
                    $scope.activityData.unshift(response.Data[0]);
                    setTimeout(
                            function () {
                                if (!$scope.IsActiveFilter) {
                                    if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                        $('#FilterButton').show();
                                    } else {
                                        $('#FilterButton').hide();
                                    }
                                }
                            }, 1000
                            );
                }
            });
        });

        $scope.LiveFeedToggle = function ()
        {
            if ($(".live-feed").hasClass("is-visible") == false)
            {
                if ($('#LiveFeedPageNo').val() <= 1)
                {
                    $scope.getLiveFeed();
                }

            }
        }

        $scope.$on('FacebookShareEmit', function (obj, href, description, name, picture) {
            FB.ui({
                method: 'share',
                href: href,
                caption: base_url,
                description: $scope.strip(description),
                quote: $scope.strip(name),
                picture: picture,
            }, function (response) {
            });
        });

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
        }
        $scope.smart_substr = function(val)
        {
          return smart_substr(140,val);
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
        $scope.get_popular_feeds = function()
        {
          WallService.CallApi(reqData, 'activity/get_popular_feeds').then(function(response) {
            if(response.ResponseCode == 200)
            {
              angular.forEach(response.Data, function(val, key) {
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
            /*$scope.activityData = response.Data;
            angular.forEach($scope.activityData, function(val, key) {
                if (val['Reminder'] && typeof val['Reminder'].ReminderGUID !== 'undefined') {
                    $scope.activityData[key]['ReminderData'] = $scope.prepareReminderData(val['Reminder']);
                }
                $scope.activityData[key].ImageServerPath = image_server_path;
            });*/
              $scope.popularData = response.Data;
              $scope.popular_feeds_single[0] = $scope.popularData[0];
            }
          });
        }

        $scope.popularLimit = 0;
        $scope.getPopularLimit = function(direction)
        {
          if(direction == 'Next')
          {
            if(parseInt($scope.popularLimit)+1 == $scope.popularData.length)
            {
              $scope.popularLimit = 0;
            }
            else
            {
              $scope.popularLimit++;
            }
          }
          if(direction == 'Prev')
          {
            if($scope.popularLimit == 0)
            {
              $scope.popularLimit = parseInt($scope.popularData.length)-1;
            }
            else
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
            $('.loader-fad,.loader-view').show();
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
            
            filterIsPromoted = 0;
            $scope.Filter.IsSetFilter=false;
            $scope.Filter.typeLabelName='Everything';
            $scope.Filter.ownershipLabelName='Anyone';
            $scope.Filter.timeLabelName='';
            $scope.Filter.sortLabelName='Recent Post';
            if(IsAdminView == '1')
            {
              angular.element(document.getElementById('UserListCtrl')).scope().ResetShowMe();
            }
            $('#FeedSortBy').val('2');
            // Reset Content keyword
            $('#BtnSrch i').removeClass('icon-removeclose');
            $('#srch-filters').val('');
             
            /*Reset Show Me option and Tag*/
                $('.check-content-filter:checked').each(function(e){
                    $('.check-content-filter').prop('checked',false);
                });
                $scope.search_tags=[]
            /**/
            
            $('#ActivityFilterType').val('');
            $('.active-with-icon').children('li').removeClass('active');
            $scope.typeLabelName = 'Everything';
            $scope.PostedByLookedMore=[];
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
            if(!skipCall){
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
        $scope.getEntityList = function () {
            var reqData = {};
            if($scope.LoginSessionKey)
            {
              if($scope.entityList.length>0)
              {
                $scope.PostAs=$scope.entityList[0];
                return;
              }
              WallService.CallPostApi(appInfo.serviceUrl + 'page/my_pages', reqData, function (successResp) {
                  var response = successResp.data;    
                  if (response.ResponseCode == 200) {
                      $scope.entityList = response.Data;
                      $scope.PostAs=$scope.entityList[0];
                  } else {
                  }
              }, 
              function (error) {
                  //showResponseMessage('Something went wrong.', 'alert-danger');
              });
            }
        }

        setTimeout(function(){
          if($('#IsWiki').length==0)
          {
           $scope.getEntityList();
          }
        },500);

        $scope.get_len = function(obj)
        {
          return Object.keys(obj).length;
        }

        $scope.set_post_as = function (data) {
            $scope.hideImg = true;
            $('.user-img-icon .thumb-alpha').remove();
            $scope.PostAs=data;
            setTimeout(function(){
              $scope.hideImg = false;
            },10);
        }
         $scope.ActiveFilter = function () {
            $scope.IsActiveFilter = true; 
         }

        $scope.CheckReminderDate = function (ActivityGUID) {
            angular.forEach($scope.activityData,function(val,key){
                if(val.ActivityGUID == ActivityGUID)
                {
                    if(typeof val.ReminderData == 'undefined')
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
        var ajax_request = false;
        var Summer_keyword='';
        $scope.options = {
            placeholder:'Write here and use @ to tag someone.',
            airMode: false,
            popover:{},
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    setTimeout(function(){
                      document.execCommand('insertText', false, bufferText);
                    },10);
                }
            },
            toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['para', ['paragraph']],
                    ['insert', ['picture','video','link']]
                ],
            hint: {
                    //mentions: [{ModuleID:3,UserID:18,FirstName:'Vikas',LastName:"Choudhary"},{ModuleID:3,UserID:18,FirstName:'Suresh',LastName:"Choudhary"}],
                    //mentions: ['jayden', 'sam', 'alvin', 'david'],
                    //match: /\B@(\w*)$/,
                    match: /^(?!.*?[^\na-z0-9]{2}).*?\B@(\w*)$/i,
                    search: function (keyword, callback) {
                        var ExcludeIds = [];
                        var i = 0;
                        $('#postEditor .note-editable [data-tag="user-tag"]').each(function(e){
                          var cls = $('#postEditor .note-editable [data-tag="user-tag"]:eq('+e+')').attr('class');
                          cls = cls.split('-');
                          ExcludeIds[i] = cls[2];
                          i++;
                        });

                        Summer_keyword=keyword;
                        if ($.trim(keyword).length < 2)
                        {
                            return false;
                        }
                        if (ajax_request)
                        {
                            ajax_request.abort();
                        }

                        var TaggingType = 'MembersTagging';
                        if(IsNewsFeed=='1')
                        {
                            TaggingType = 'NewsFeedTagging';
                        }
                        var reqData = { 
                          ExcludeID:ExcludeIds,
                          PageSize: 10,
                          Type: TaggingType,
                          SearchKey: keyword,
                          ModuleID: $('#module_id').val(),
                          ModuleEntityID: $('#module_entity_guid').val()
                        };
                        if ( ( $scope.tagsto.length === 1 ) && ( $scope.tagsto[0].ModuleID == 1 ) ) {
                          reqData['ModuleID'] = $scope.tagsto[0].ModuleID; 
                          reqData['ModuleEntityID'] = $scope.tagsto[0].ModuleEntityGUID; 
                        } else {
                          reqData['ModuleID'] = $('#module_id').val(); 
                          reqData['ModuleEntityID'] = $('#module_entity_guid').val();
                        }
                        if(IsAdminView == '1')
                        {
                          reqData['AdminLoginSessionKey'] = $('#AdminLoginSessionKey').val();
                          $http({
                            method: 'POST',
                            data:reqData,
                            url: base_url + 'api/users/list'
                          }).then(function(r) {
                              r = r.data;
                              if (r.ResponseCode == 200) {
                                  var uid = 0;
                                  var d = new Array();
                                  if(r.Data)
                                  {
                                    for (var key in r.Data.Members) {
                                        var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                        d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': r.Data.Members[key].ModuleID,'ModuleEntityGUID':r.Data.Members[key].UserGUID,'ModuleID':r.Data.Members[key].ModuleID,'ProfilePicture':r.Data.Members[key].ProfilePicture,AllowedPostType:r.Data.Members[key].AllowedPostType};
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
                        else
                        {
                          reqData['Loginsessionkey'] = LoginSessionKey;
                          ajax_request = $.post(base_url + 'api/users/list', reqData, function (r) {
                              if (r.ResponseCode == 200) {

                                  var uid = 0;
                                  var d = new Array();
                                  if(r.Data)
                                  {
                                    for (var key in r.Data.Members) {
                                        var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                        d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': r.Data.Members[key].ModuleID,'ModuleEntityGUID':r.Data.Members[key].UserGUID,'ModuleID':r.Data.Members[key].ModuleID,'ProfilePicture':r.Data.Members[key].ProfilePicture,AllowedPostType:r.Data.Members[key].AllowedPostType};
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
                           return '<tagitem entityid="'+item.id+'" name="'+item.name+'" profilepicture="'+item.ProfilePicture+'" moduleid="'+item.ModuleID+'" moduleentityguid="'+item.ModuleEntityGUID+'">'+item.name.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + Summer_keyword + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<span>$1</span>")+'</tagitem>';
                            //return '<span>'+item.name+'</span>';
                          },
                    content: function (item) {
                       //return $('<span contenteditable="true" style="padding:0 2px;">').html('<span contenteditable="false" data-tag="user-tag" class="user-'+item.type+'-'+item.id+'">'+ item.name+'</span>')[0];
                       return $("<span contenteditable='true' style='padding:0 2px;'>").html("<span contenteditable='false' data-tag='user-tag' class='user-"+item.type+"-"+item.id+"'>"+ item.name+"</span> &nbsp;")[0];
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
          
        $scope.commentOptions = {
            //placeholder:'What’s on your mind',
            airMode: false,
            focus: true,
            popover:{},
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    setTimeout(function(){
                      //document.execCommand('insertText', false, bufferText);
                    },10);
                }
            },
            toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['para', ['paragraph']],
                    ['insert', ['picture','video','link']]
                ],
            hint: {
                    //match: /\B@(\w*)$/,
                    match: /^(?!.*?[^\na-z0-9]{2}).*?\B@(\w*)$/i,
                    search: function (keyword, callback) {
                        Summer_keyword=keyword;
                        if ($.trim(keyword).length < 2)
                        {
                            return false;
                        }
                        if (ajax_request)
                        {
                            ajax_request.abort();
                        }

                        var TaggingType = 'MembersTagging';
                        if(IsNewsFeed=='1')
                        {
                            TaggingType = 'NewsFeedTagging';
                        }
                        ajax_request = $.post(base_url + 'api/users/list', {Loginsessionkey:LoginSessionKey,PageSize: 10, Type: TaggingType, SearchKey: keyword, ModuleID: $('#module_id').val(), ModuleEntityID: $('#module_entity_guid').val()}, function (r) {
                            if (r.ResponseCode == 200) {

                                var uid = 0;
                                var d = new Array();
                                if(r.Data)
                                {
                                  for (var key in r.Data.Members) {
                                      var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                      d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': '3','ModuleEntityGUID':r.Data.Members[key].UserGUID,'ModuleID':r.Data.Members[key].ModuleID,'ProfilePicture':r.Data.Members[key].ProfilePicture,AllowedPostType:r.Data.Members[key].AllowedPostType};
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
                           return '<tagitem name="'+item.name+'" profilepicture="'+item.ProfilePicture+'" moduleid="'+item.ModuleID+'" moduleentityguid="'+item.ModuleEntityGUID+'">'+item.name.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + Summer_keyword + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<span>$1</span>")+'</tagitem>';
                            //return '<span>'+item.name+'</span>';
                          },
                    content: function (item) {
                       return $('<span contenteditable="true" style="padding:0 2px; display:inline-block;">').html('<span contenteditable="false" data-tag="user-tag" class="user-'+item.type+'-'+item.id+'">'+ item.name+'</span>')[0];
                    }    
                  }
          };
        
        $scope.show_comment_box = "";
        $scope.postCommentEditor = function(ActivityGUID,feedIndex) {
          
          $scope.activityData[feedIndex].medias = '';
          $scope.activityData[feedIndex].commentMediaCount = 0;
          $scope.activityData[feedIndex].commentFileCount  = 0;

          $scope.edit_comment_box = "";
          $scope.show_comment_box = ActivityGUID;
             // $('#cmt-div-'+ActivityGUID+' .place-holder-label').hide();
              //$('#cmt-div-'+ActivityGUID+' .comment-section').removeClass('hide');
              //$('#cmt-div-'+ActivityGUID+' .note-editable').html('');
             // $('#cmt-'+ActivityGUID).summernote('focus');
             // showButtonLoader('PostBtn-'+ActivityGUID);

              /*$.each($scope.activityData,function(){
                if(this.ActivityGUID!=ActivityGUID)
                {
                    var PComments = $.trim($('#cmt-div-' + this.ActivityGUID + ' .note-editable').html());
                    if((!PComments) && (!this.medias))
                    {
                        $('#cmt-div-'+this.ActivityGUID+' .place-holder-label').show();
                        $('#cmt-div-'+this.ActivityGUID+' .comment-section').addClass('hide');
                    }
                }
            });*/
            
            //$('#cmt-' + ActivityGUID).summernote('reset');
        };
        $scope.checkEditorData = function(ActivityGUID,feedIndex, BlockName) {
          if(BlockName) {
            var PComments = $('#'+BlockName+'-'+ ActivityGUID + ' .note-editable').html();
          } else {
             var PComments = $('#cmt-div-' + ActivityGUID + ' .note-editable').html();
          }

             // to solve blank comment issue
             PComments = PComments.replace(/&nbsp;/g,'');
             PComments = PComments.trim();
             //var isEmpty = $('#cmt-' + ActivityGUID).summernote('isEmpty');
             
            if(PComments || ($scope.activityData[feedIndex].medias && (Object.keys($scope.activityData[feedIndex].medias).length > 0)))
            {
              hideButtonLoader('PostBtn-'+ActivityGUID);
            }
            else
            {
                showButtonLoader('PostBtn-'+ActivityGUID);
            }
        };
        $scope.edit_comment_box = "";
        $scope.EditComment = "";
        $scope.commentEditBlock = function(CommentGUID, ActivityGUID, CommentData) {
                $scope.show_comment_box = "";
                $scope.edit_comment_box = CommentGUID;
                $scope.EditComment = angular.copy(CommentData);

                $scope.EditComment.Media.map( function (repo) {
                            repo.progress = true;
                            return repo;
                          });
                $scope.EditComment.Files.map( function (repo) {
                            repo.progress = true;
                            return repo;
                          });
              //$scope.activityData[feedIndex].  
              //console.log(CommentData.PostComment);
              setTimeout(function(){
                $("#comment-edit-block-"+CommentGUID+" .note-editable").html($scope.EditComment.EditPostComment);
                placeCaretAtEnd($("#comment-edit-block-"+CommentGUID+" .note-editable")[0]);
              },100)
               
                /* CommentData.Media.map( function (repo) {
                            repo.progress = true;
                            return repo;
                          });
                CommentData.Files.map( function (repo) {
                            repo.progress = true;
                            return repo;
                          });
              //$scope.activityData[feedIndex].  
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
          $scope.imageUpload = function(files) {
              //console.log('image upload:', files);
            };

          $scope.summernoteUpload= function(evt,editor, welEditable,$attrs){
            $scope.Desc_loader = true;
            if($attrs.posttype=='Post')
                {
                   jQuery('#postEditor .postEditorLoader').show();
                }
                else if($attrs.posttype=='Comment')
                {
                   jQuery('#cmt-div-'+$attrs.guid+' .commentEditorLoader').show();
                }
            
            var reqData = {
              ImageUrl: evt.target.result,
              Type: 'wall',
              DeviceType: 'Native'
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/saveFileFromUrl',reqData).then(function (response) {
              response = response.data;
              if (response.ResponseCode == 200) { 
                  if($attrs.posttype=='Post')
                  {
                     jQuery('#PostContent').summernote("insertImage", response.Data.ImageServerPath+'/'+response.Data.ImageName);
                     $timeout(function() {
                        jQuery('#postEditor .postEditorLoader').hide();
                      }, 900);
                        
                  }
                  else if($attrs.posttype=='Comment')
                  {
                     jQuery('#'+$attrs.id).summernote("insertImage", response.Data.ImageServerPath+'/'+response.Data.ImageName);
                     $timeout(function() {
                        jQuery('#cmt-div-'+$attrs.guid+' .commentEditorLoader').hide();
                      }, 900);
                  }
                /*console.log(welEditable);
                editor.insertImage(welEditable, response.Data.ImageServerPath+'/'+response.Data.ImageName);*/

              }
              
           
            },function(){
              $timeout(function() {
                  showResponseMessage('This file cannot be uploaded','alert-danger');
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
        
        $scope.get_title_class = function()
        {
          if($scope.edit_post && $('#PostTitleLimit').val().length>0)
          {
            return 'ng-valid';
          }
        }

        $scope.toggle_post_view = function()
        {
          if($scope.postTypeview == 1)
          {
            $scope.postTypeview = 0;              
          }
          else
          {
            $scope.postTypeview = 1;
          }
        }

        $scope.viewPostType = function(){
            if($scope.postTypeview == 1)
            {
              $scope.postTypeview = 0;              
            }
            else
            {              
              $scope.postTypeview = 1;
              $scope.overlayShow = 1; 
            }
        } 

        $scope.setEditorPosition = function(px)
        {
          $('#postEditor').css('top',px+'px');
        }

        $scope.showPostEditor = function(isTypeChanged){
            $scope.postEditormode = 1;
            $scope.postTypeview = 0;
            $scope.postypeActive = 1;
            if( !$scope.edit_post && !isTypeChanged)
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
            if($scope.PostContent == '')
            {
              $scope.noContentToPost = true;
            }

            var char = 140-$('#PostTitleInput').val().length;
            if(char == 1)
            {
              $('#PostTitleLimit').html('1 character');
            }
            else
            {
              $('#PostTitleLimit').html(char+' characters');
            }

            setTimeout(function(){
              placeCaretAtEnd($("#postEditor .note-editable")[0]);
            },200);
        } 

        $scope.postPreview = function(){
            $scope.postPreviemode = 1;
            $scope.postEditormode = 0;
            // $timeout(function(){
            //     $('.mediaPost.two-images .mediaThumb, .mediaPost.three-images .mediaThumb,.mediaPost.four-images .mediaThumb').imagefill();
            // },50);
        }
        $scope.backEditMode = function(){
            $scope.postPreviemode = 0;
            $scope.postEditormode = 1; 
        }

        $scope.removeAllview = function(){            
            $scope.postPreviemode = 0;
            $scope.postEditormode = 0;
            $scope.postTypeview = 0;
            $scope.overlayShow = 0;
            if(!$scope.$$phase)
            {
              $scope.$apply();
            }
        }

        $scope.clearWallPost = function()
        {
          $(".note-editable").text('');
          $scope.tagsto = [];
          $scope.postTagList = [];
          $scope.memTagCount = 0;
          $('#PostTitleInput').val('');
          $scope.removeAllview();
          $(".note-placeholder.normal-placeholder").removeClass('active-placeholder');
          $(".note-placeholder").show();
//          $("#PostTitleInput").prop('class', 'form-control post-placeholder ng-dirty ng-touched');
//          $("#PostTitleInput").focusin();
//          $("#PostTitleInput").focusout();
          $scope.edit_post = false;
          $scope.override_post_permission = [];
          $scope.parseLinks = [];
          $scope.allreadyProcessedLinks = [];
          $scope.ShowPreview = 0;
          $scope.resetPrivacySettings();
        }

        $scope.confirmCloseEditor = function()
        {
          if($scope.overlayShow == '1')
          {
            var pc = $(".note-editable").text().trim();
            if(pc!=='' || $scope.isWallAttachementUploading || $scope.mediaCount>0 || $scope.fileCount>0 || $('#PostTitleInput').val().length>0)
            {
              showConfirmBox("Close Editor", "Are you sure you want to close editor? All your content will be lost.", function (e) {
                  if (e) {
                      $scope.clearWallPost();
                  }
              });
            }
            else
            {
              $scope.clearWallPost();
            }
          }
        }

        $scope.get_like_name = function(data)
        {
          var str = '';
          var total_records = 0;
          if(data.length>0)
          {
            total_records = data[0].TotalFriends;
            if(total_records == 1)
            {
              str = '<span class="semi-bold">'+data[0].FirstName+'</span> <span class="regular">is involved</span>';
            }
            if(total_records == 2)
            {
              str = '<span class="semi-bold">'+data[0].FirstName+'</span> <span class="regular">and</span> <span class="semi-bold">'+data[1].FirstName+'</span> <span class="regular">are involded</span>';
            }
            if(total_records == 3)
            {
              str = '<span class="semi-bold">'+data[0].FirstName+'</span><span class="regular">,</span> <span class="semi-bold">'+data[1].FirstName+'</span> <span class="regular">and</span> <span class="semi-bold">1 other</span> are involved';
            }
            if(total_records > 3)
            {
              str = '<span class="semi-bold">'+data[0].FirstName+'</span><span class="regular">,</span> <span class="semi-bold">'+data[1].FirstName+'</span> <span class="regular">and</span> <span class="semi-bold">'+(parseInt(total_records)-2)+' others</span> <span class="regular">are involved</span>';
            }
          }
          return str;
        }

        $scope.get_history = function (ActivityGUID)
        {
            if(!$scope.IsSingleActivity)
                return false;
            var reqData = {ActivityGUID: ActivityGUID};
            WallService.CallApi(reqData, 'activity/get_activity_history').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.ActivityHistory=response.Data;
                }
            });
        }
        $scope.revert_history = function (ActivityGUID,HistoryID)
        {
            var reqData = {ActivityGUID: ActivityGUID,HistoryID:HistoryID};
            WallService.CallApi(reqData, 'activity/change_activity_version').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.busy=false;
                    $scope.stopExecution=0;
                    $scope.GetwallPost();
                    $scope.ActivityHistory=[];
                }
            });
        }
        $scope.get_activity_friend_list = function (Stage,ActivityGUID)
        {
             $scope.ActivityFriendActivityGUID=ActivityGUID;
             var ignoreList=[];
              $.each($scope.activityData,function(){
                if(this.ActivityGUID==ActivityGUID)
                {
                    var RquestedFriendList=this.RquestedFriendList;
                    if(RquestedFriendList.length>0)
                    {
                        $.each(RquestedFriendList,function(k){
                            ignoreList.push(RquestedFriendList[k].UserID);
                        })
                    }
                }
            })
             
                 $scope.ActivityFriendPageNo=1;
                 $scope.ActivityFriends=[];
            var reqData = {ActivityGUID: ActivityGUID,PageNo:$scope.ActivityFriendPageNo,SearchKey:$('#sr_'+ActivityGUID).val(),IgnoreList:ignoreList};
            WallService.CallApi(reqData, 'activity/get_activity_friend_list').then(function (response) {
                if (response.ResponseCode == 200)
                {   var Html='';
                    $.each($scope.activityData,function(){
                        
                        if(this.ActivityGUID==ActivityGUID)
                        {
                            this.SuggestedFriendList=response.Data;
                            this.SuggestedTotalRecords=response.TotalRecords;
                        }
                    })
                    if(response.TotalRecords == 0)
                    {
                      showResponseMessage('Sorry, you have no friends / members to ask this question.','alert-danger');
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
        $scope.add_request_friend = function (ActivityFriend,$index,ActivityGUID)
        {
            $.each($scope.activityData,function(){
                if(this.ActivityGUID==ActivityGUID)
                {
                    this.RquestedFriendList.push(ActivityFriend);
                    this.SuggestedFriendList.splice($index,1);
                    if(this.SuggestedFriendList.length<3)
                    {
                        $scope.get_activity_friend_list('',ActivityGUID)
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
        
        timeVar='';
        $scope.SearchActivityFriend = function (ActivityGUID)
        {
             clearTimeout(timeVar);
                 timeVar = setTimeout(function(){
                    $scope.get_activity_friend_list('init', ActivityGUID);
                },500)
        }
        $scope.remove_select_data = function (data,$index,ActivityGUID)
        {
            $.each($scope.activityData,function(){
                if(this.ActivityGUID==ActivityGUID)
                {
                    this.SuggestedFriendList.push(data);
                    this.RquestedFriendList.splice($index,1);
                }
            })
            
            /*$scope.ActivityFriends.push(data);
            $scope.RequestFriendList.splice($index,1);*/
        }
       
       $scope.showPrivacyPreview = 1;
       $scope.ShowPreview = 0;
       $scope.showPreview = function()
       {
        $scope.showPrivacyPreview = $('#visible_for').val();
        var postContent = $(".note-editable").html().trim();
        $('#PostTypeTitle').html($('#PostTitleInput').val());
        $('#PostTypeContent').html(postContent);
        if(IsAdminView == '1')
        {
          $('#PreviewName').html($scope.postasuser.FirstName+' '+$scope.postasuser.LastName);
          $('#PreviewImage').attr('src',image_server_path+'upload/profile/220x220/'+$scope.postasuser.ProfilePicture);
        }
        else
        {
          $('#PreviewName').html(LoggedInFirstName+' '+LoggedInLastName);
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

       $scope.backEditMode = function()
       {
        $scope.ShowPreview = 0;
       }

       $scope.getCurrentTime = function()
       {
        var date = new Date();
        return moment(date).format('DD MMM')+' at '+moment(date).format('h:mm A');
       }

       $scope.is_draft = 0;
       $scope.saveDraft = function()
       {
        $scope.is_draft = 1;
        $scope.SubmitWallpost();
       }

       
        $scope.send_request = function (ActivityGUID)
        { 
             var RequestTo=[];
             $.each($scope.activityData,function(){
                if(this.ActivityGUID==ActivityGUID)
                {
                    var RquestedFriendList=this.RquestedFriendList;
                    if(RquestedFriendList.length>0)
                    {
                        $.each(RquestedFriendList,function(k){
                            RequestTo.push(RquestedFriendList[k].UserGUID);
                        })
                    }
                }
            })
        
            var reqData = {ActivityGUID: $scope.ActivityFriendActivityGUID,RequestTo:RequestTo,Note:$('#note_'+ActivityGUID).val()};
            WallService.CallApi(reqData, 'activity/request_question_answer_for_activity').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $.each($scope.activityData,function(){
                        if(this.ActivityGUID==ActivityGUID)
                        {
                            this.RquestedFriendList=[];
                            this.SuggestedFriendList=[];
                            this.SearchFriendList='';
                        }
                    })
                   //$scope.ActivityFriends=[];
                   //$scope.RequestFriendList=[];
                   $scope.ActivityDetail.FriendSearchKey='';
                   $scope.ActivityDetail.Note='';
                   showResponseMessage(response.Message, 'alert-success');
                }
            });
        }

        $scope.clear_all_filters = function()
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
        $scope.SetFilter = function()
        {
            $scope.Filter.IsSetFilter= true;
        }
        $scope.getFilterVal = function()
        {
            return $scope.Filter.IsSetFilter;
        }

        $scope.getPostTooltip = function(post_type)
        {
          return $scope.allow_post_types[post_type];
        }

        $scope.get_selected_text = function(e,activity_guid)
        {
          var text=getSelectedText();            
          $('.tooltip-selection').remove();
          if(text.length>0)
          {
            var html = '<div ng-click="insert_to_editor(\''+activity_guid+'\',\''+text+'\')" class="anim tooltip-selection" id="selectionSharerPopover" style="position:absolute;display:block;top:'+e.pageY+'px;left:'+e.pageX+'px;"><div class="tooltip-selection-inner"><ul><li><a class="action"><svg height="16px" width="16px" class="svg-icons"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#icnQuote"></use></svg></a></li></ul></div></div>';
            angular.element(document.body).append( $compile(html)($scope) )
          }
        }

        $scope.insert_to_editor = function(activity_guid,text,feedIndex)
        {
          if(!text)
          {
            text = $('#act-'+activity_guid+' .post-content').html();
          }

          $scope.postCommentEditor(activity_guid,feedIndex);
          var html = '<p>&nbsp;</p>'
          html += '<div class="quote-wrote" contenteditable="true"><span>'+text+'</span></div>';
          html+= '<p>&nbsp;</p>';
          if(typeof text!=='undefined')
          {
            console.log(html);
            $("#cmt-div-"+activity_guid+" .note-editable").append(html);
            $('#cmt-'+activity_guid).summernote('focus');
            setTimeout(function(){
              placeCaretAtEnd($("#cmt-div-"+activity_guid+" .note-editable")[0]);
            },200);
          }
        }

        $scope.set_mouse = function(el)
        {
          var el = $(el)[0];
          var range = document.createRange();
          var sel = window.getSelection();
          range.setStart(el.childNodes[2], 5);
          range.collapse(true);
          sel.removeAllRanges();
          sel.addRange(range);
        }

        $scope.saveRange = function(activity_guid)
        {
          /*var range = $('#cmt-'+activity_guid).summernote('createRange');
          $('#cmt-'+activity_guid).summernote('saveRange');
          console.log($('#cmt-'+activity_guid).summernote('saveRange'));*/
        }

        $scope.entity_articles = [];
        $scope.add_tag_article = function(tag)
        {
          $scope.entity_articles.push({ModuleID:tag.ModuleID,ModuleEntityID:tag.ModuleEntityID});
          $scope.filter_article();
        }

        $scope.remove_tag_article = function(tag)
        {
          angular.forEach($scope.entity_articles,function(val,key){
            if(val.ModuleID == tag.ModuleID && val.ModuleEntityID == tag.ModuleEntityID)
            {
              $scope.entity_articles.splice(key,1);
            }
          });
          $scope.filter_article();
        }

        $scope.related_article_id = 0;
        $scope.related_articles = [];
        $scope.search_tags = [];
        $scope.loadCategorylist = function($query) {
          var requestPayload = { SearchKeyword: $query}; 
          var url = appInfo.serviceUrl + 'activity/entity_suggestion';
          return WallService.CallPostApi(url, requestPayload, function(successResp) {
              var response = successResp.data; 
              return response.Data.filter(function(flist) {
                  return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
              });
          });
        };

        $scope.loadSearchTagsArticle = function($query) {
          var requestPayload = { SearchKeyword: $query, ShowFriend: 0, Location: {}, Offset: 0, Limit: 10 };
          var url = appInfo.serviceUrl + 'search/tag?SearchKeyword=' + $query;
          return WallService.CallPostApi(url, requestPayload, function(successResp) {
              var response = successResp.data;
              return response.Data.filter(function(flist) {
                  return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
              });
          });
        };

        $scope.PostedByLookedMore = [];
        $scope.loadSearchUsersArticle = function($query) {
            var requestPayload = { SearchType:"All",SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 10 };
            var url = appInfo.serviceUrl + 'search/user';
            return WallService.CallPostApi(url, requestPayload, function(successResp) {
                var response = successResp.data;
                angular.forEach(response.Data, function(val, key) {
                    response.Data[key].Name = response.Data[key].FirstName + ' ' + response.Data[key].LastName;
                });
                return response.Data.filter(function(flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.filter_article = function()
        {
          $('#WallPageNo').val(1);
          $('#FeedSortBy').val(2);
          $scope.article_list = [];
          $scope.stop_article_execution = 0;
          $scope.get_wiki_post();
        }


        $scope.local_article_data = [];
        $scope.is_first_time = 1;
        $scope.reset_related_popup = function(data)
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
        
        $scope.get_related_activity = function(data)
        {
          $('.wiki-suggested-listing').mCustomScrollbar({
              callbacks: {
                  onTotalScroll: function() {
                      $scope.get_wiki_post();
                  }
              }
          });

          $scope.checked_related_articles = [];
          var activity_id = data.ActivityID;
          $scope.local_article_data = data;
          $scope.related_article_id = activity_id;
          var reqData = {ActivityID:activity_id};
          WallService.CallApi(reqData, 'activity/get_related_activity').then(function (response) {
              if (response.ResponseCode == 200)
              {
                  $scope.related_articles=response.Data;
                  $scope.get_wiki_post(activity_id);
              }
          });
        }

        $scope.update_article_status = function()
        {
          angular.forEach($scope.related_articles,function(val,key){
            angular.forEach($scope.article_list,function(v,k){
              if(v.ActivityID == $scope.related_article_id)
              {
                $scope.article_list.splice(k,1);
              }
            });
            $scope.checked_related_articles.push(val.ActivityID);
          });

          angular.forEach($scope.related_articles,function(val,key){
            angular.forEach($scope.article_list,function(v,k){
              if(v.ActivityID == val.ActivityID)
              {
                $scope.article_list[k]['IsChecked'] = 1;
              }
            });
            $scope.checked_related_articles.push(val.ActivityID);
          });
        }

        var wiki_slider = null;
        $scope.slider_init = function()
        {
          if(wiki_slider)
          {
            wiki_slider.destroySlider();
          }
          wiki_slider = $('#wikislider').bxSlider({mode:'horizontal', pager:false, minSlides:1, maxSlides:3, slideWidth:300, slideMargin:10, infiniteLoop: false, hideControlOnEnd: true});
        }

        $scope.select_article = function(article_id)
        {
          var remove = true;
          angular.forEach($scope.article_list,function(val,key){
            if(val.ActivityID == article_id)
            {
              remove = false;
              if($scope.article_list[key].IsChecked == 0)
              {
                $scope.article_list[key].IsChecked = 1;
                $scope.related_articles.push(val);
                var append = true;
                angular.forEach($scope.checked_related_articles,function(v,k){
                  if(v.ActivityID == article_id)
                  {
                    append = false;
                  }
                });
                if(append)
                {
                  $scope.checked_related_articles.push(article_id);
                }
              }
              else
              {
                $scope.article_list[key].IsChecked = 0;
                angular.forEach($scope.checked_related_articles,function(v,k){
                  if(v == article_id)
                  {
                    $scope.checked_related_articles.splice(k,1);
                  }
                });
                angular.forEach($scope.related_articles,function(v,k){
                  if(v.ActivityID == article_id)
                  {
                    $scope.related_articles.splice(k,1);
                  }
                });
              }
            }
          });

          if(remove)
          { 
            angular.forEach($scope.related_articles,function(val,key){
              if(val.ActivityID == article_id)
              {
                $scope.related_articles.splice(k,1);
              }
            });
          }
        }

        $scope.dismiss_related_activity_popup = function()
        {
          $('#addRelatedArticles').modal('hide');
        }

        $scope.checked_related_articles = [];
        $scope.add_related_activity = function()
        {
          var arr = [];
          angular.forEach($scope.related_articles,function(v,k){
            arr.push(v.ActivityID);
          });
          var reqData = {ActivityID: $scope.related_article_id,RelatedActivity:arr};
          WallService.CallApi(reqData, 'activity/related_activity').then(function (response) {
              if (response.ResponseCode == 200)
              {
                showResponseMessage('Related articles added successfully.','alert-success');
                $('#addRelatedArticles').modal('hide');
              }
          });
        }

        $scope.copyToClipboard = function(id)
        {
          copyToClipboard('#a-'+id);
        }

        $scope.breakquote = function(e)
        {
          if(e.which == 13)
          {
            //pasteHtmlAtCaret('a');
          }
          //$('#cmt-' + activity_guid).insertAtCaret('text');
          //$('#cmt-div-' + activity_guid + ' .note-editable .quote-wrote').has('br').addClass('quote-wrote-reply');
        }

        $scope.EntityTags = function(EntityTags){
          returnTags = [];
          showTags = [];
          hiddenTags = [];
          hiddenTagsName = '';
          if(EntityTags.length>4){
            angular.forEach(EntityTags,function(value,key){
              if(showTags.length<=4){
                showTags.push(value);  
              }else{
                hiddenTags.push(value);
                hiddenTagsName+=value.Name+'<br>';
              }
            });
          }else{
            showTags = EntityTags;
          }
          returnTags.showTags = showTags;
          returnTags.hiddenTags = hiddenTags;
          returnTags.hiddenTagsName = hiddenTagsName;
          returnTags.hiddenTagsLength = hiddenTags.length;
          return returnTags;
        }
        //console.log('Testing '); console.log($scope.config_detail);   data-ng-if="!config_detail.IsSuperAdmin"
        $scope.setPromotionStatus = function(activityId, isPromoted, templateData, event){
            var requestPayload = { 
                ActivityID: activityId,
                IsPromoted : isPromoted
            }; 
            var interval = null;
            var url = appInfo.serviceUrl + 'activity_helper/set_promotion_status';
            WallService.CallPostApi(url, requestPayload, function(successResp) {
                var response = successResp.data; 
                if(response.ResponseCode == 200) {
                    templateData.IsPromoted = (templateData.IsPromoted == '1') ? '0' : '1';
                    showResponseMessage(response.Message,'alert-success');
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

    }]); 

function showLoginPopup()
{
    if($('#beforeLogin').length>0)
    {
        $('#beforeLogin').parent('li').addClass('open');
    }
}
  
$(document).ready(function(){
  $(document).click(function(){
    var WallPostCtrl = angular.element(document.getElementById('WallPostCtrl')).scope();
    if(typeof WallPostCtrl!=='undefined')
    {
      if(WallPostCtrl.postTypeview == '1')
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

  $(document).ready(function(){
      $(document).click(function(){
        $('.tooltip-selection').remove();
      });
      $('.post-content').click(function(e) {
          e.stopImmediatePropagation();
      });
    
    var ele = $(".quote-wrote");
    if('initialize' in ele) {
        ele.initialize( function(){
            $(".quote-wrote").each(function(e){
              if($(".quote-wrote:eq("+e+")").text()=="")
              {
                $(".quote-wrote:eq("+e+")").addClass("quote-wrote-reply");
              }
            });
        });
    }
  });


function placeCaretAtEnd(el) {
    if(!el)
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

function smart_substr(n, s) {
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

    var c = function(l)
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
        if($('#commentablePost3').hasClass('on'))
        {
          $('#commentablePost3 i').attr('class','icon-off');
          $('#commentablePost3').removeClass('on');
        }
        else
        {
          $('#commentablePost3 i').attr('class','icon-on');
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
