!(function () {
  'use strict';
  app.directive('initEntiyPopover', ['$timeout', '$q', '$http', '$templateCache', '$compile', function ($timeout, $q, $http, $templateCache, $compile) {
      return {
        link: function (scope, elem, attrs, ngModel) {
          // {{ partialsUrl }}{{ ( entityList.ModuleID == 3 ) ? 'profilePopover.html' : 'pageGroupPopover.html'; }}
//          console.log(attrs.popoverTemplateUrl);
          var template = '',
              popoverTemplateUrl = attrs.popoverTemplateUrl;
          var getTemplate = function () {
            var def = $q.defer();
            template = $templateCache.get("templatePopoverId.html");
//            console.log('template : ', template);
            if (typeof template === "undefined") {
              $http.get(popoverTemplateUrl).then(function (resp) {
                if (resp.status === 200) {
                  $templateCache.put("templatePopoverId.html", resp.data);
                  def.resolve(resp.data);
                }
              });
            } else {
              def.resolve(template);
            }
            return def.promise;
          };
          getTemplate().then(function (template) {
//            var options = {
//                content: $compile(template)(scope),
//                container: 'body',
//                html: true
//            };
//            $(elem).popover(options);
//            $(elem).on('$destroy', function(){
//              $(elem).popover('destroy');
//            })

            var options = {
              content: $compile(template)(scope),
              trigger: 'manual',
              placement: "bottom",
              html: true
            };
            $(elem).popover(options).on("mouseenter", function () {
              var _this = this;
              $(_this).popover("show");
              $(".popover").on("mouseleave", function () {
                $(_this).popover('hide');
              });
            }).on("mouseleave", function () {
              var _this = this;
              setTimeout(function () {
                if (!$(".popover:hover").length) {
                  $(_this).popover("hide");
                }
              }, 40);
            });
            $(elem).on('$destroy', function () {
              $(elem).popover('destroy');
            });

          });
        }
      };
    }]);

  /*app.directive("createTitleMessage", ['$compile', '$window', '$sce', '$parse' , function ($compile, $window, $sce, $parse) {
      return{
        restrict: 'A',
        scope: {
          activityLogDetails: '=',
          subjectUser: '=',
          activityUser: '=',
          parentCommentUser: '=',
          activity: '=',
          parentActivity: '=',
          parentActivityUser: '=',
          activityTitleMessage: '=',
          activityPostType: '=',
          isBlockquote: '=',
          parentCommentId: '=',
          groupProfile: '=',
          pageProfile: '=',
          userProfile: '=',
          eventProfile: '=',
          pollData: '=',
        },
        link: function (scope, elem, attrs) {
          var messageTitlteString = '',
              messageTitlteElement = '',
              openGroupDetailModalPopup = 'ng-click="$emit(\'openGroupDetailModalPopup\', { ModuleID: ' + scope.activity.ModuleID + ', ModuleEntityID: ' + scope.activity.ModuleEntityID + ' });"';
          messageTitlteString = createTitleMessage(scope.activityLogDetails.ActivityTypeID);
          elem.html(messageTitlteString);
          $compile(elem.contents())(scope);
          function createTitleMessage(ActivityTypeID) {
            var activityTitleMessage = '';
            if (scope.activityLogDetails && ActivityTypeID) {
              switch (true) {
                case (ActivityTypeID == 1):
                  activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a>';
                  if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                    activityTitleMessage += ' posted in ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                  }
                  break;
                case (ActivityTypeID == 21):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> viewed ';
                  if(scope.activityLogDetails.ModuleID == 1)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.groupProfile.EntityName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 3)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.userProfile.UserName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 14)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.eventProfile.EntityName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 18)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.pageProfile.EntityName + '<\/a>';
                  }
                  break;
                case (ActivityTypeID == 33):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> voted to <a>Poll</a> created by <a>'+scope.pollData.PollData[0].CreatedBy.Name+'</a>';
                  break;
                case (ActivityTypeID == 27):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> searched '+scope.activityLogDetails.ActivityData;
                  break;
                case (ActivityTypeID == 7):
                case (ActivityTypeID == 8):
                case (ActivityTypeID == 11):
                case (ActivityTypeID == 12):
                case (ActivityTypeID == 26):
                  if (scope.subjectUser && scope.subjectUser.UserName) {
                    if (ActivityTypeID == 12) {
                      activityTitleMessage = '<a>' + scope.activity.EntityName + '<\/a>';
                    } else {
                      activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a>';
                      if ( scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) ) {
                        activityTitleMessage += ' posted in  <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                      }
                    }
                  }
                  break;
                case (ActivityTypeID == 9):
                case (ActivityTypeID == 10):
                case (ActivityTypeID == 14):
                case (ActivityTypeID == 15):
                  if (!scope.isBlockquote) {
                    if (scope.subjectUser && scope.subjectUser.UserName && (scope.subjectUser.UserGUID == scope.parentActivityUser.UserGUID)) {
                      activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> shared own ';
                    } else {
                      activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> shared <a>' + scope.parentActivityUser.UserName + '<\/a>\'s ';
                    }
                    if ( ( ActivityTypeID == 9 ) || ( ActivityTypeID == 10 ) ) {
                      activityTitleMessage += 'post';
                    } else {
                      activityTitleMessage += 'media';
                    }
                    if (scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') )) {
                      activityTitleMessage += ' in  <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                    }
                    if ((ActivityTypeID == 9) && scope.activity.EntityName && (scope.activity.EntityType == 'USER')) {
                      activityTitleMessage += ' with <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                    }
                  } else {
                    if (scope.subjectUser && scope.subjectUser.UserName && (scope.subjectUser.UserGUID == scope.parentActivityUser.UserGUID)) {
                      activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a>';
                    } else {
                      activityTitleMessage = '<a>' + scope.parentActivityUser.UserName + '<\/a>';
                    }
                    if (scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') )) {
                      activityTitleMessage += ' posted in  <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                    }
                  }
                  break;
                case (ActivityTypeID == 20):
                  if (!scope.isBlockquote) {
                    if (!scope.parentCommentId) { // if its a comment
                      if (scope.subjectUser && scope.subjectUser.UserName && scope.activityUser && scope.activityUser.UserName) {
                        if (scope.subjectUser.UserGUID && scope.activityUser.UserGUID && (scope.subjectUser.UserGUID == scope.activityUser.UserGUID)) {
                          activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> commented on own post';
                        } else {
                          activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> commented on post by <a>' + scope.activityUser.UserName + '<\/a>';
                        }
                        if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                          activityTitleMessage += ' in <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                        }
                      }
                    } else { // else its a reply
                      if (scope.subjectUser.UserGUID && scope.activityUser.UserGUID && (scope.subjectUser.UserGUID == scope.parentCommentUser.UserGUID)) {
                        activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> replied on own comment';
                      } else {
                        activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> replied on comment by <a>' + scope.parentCommentUser.UserName + '<\/a>';
                      }
                      if ( ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                        activityTitleMessage += ' in <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                      }
                    }
                  } else {
                    if (!scope.parentCommentId) { // if its a comment
                      activityTitleMessage = createTitleMessage(scope.activity.ActivityTypeID);
                    } else {
                      if (scope.parentCommentUser && scope.parentCommentUser.UserName && scope.activityUser && scope.activityUser.UserName) {
                        if (scope.parentCommentUser.UserGUID && scope.activityUser.UserGUID && (scope.parentCommentUser.UserGUID == scope.activityUser.UserGUID)) {
                          activityTitleMessage = '<a>' + scope.parentCommentUser.UserName + '<\/a> commented on own post';
                        } else {
                          activityTitleMessage = '<a>' + scope.parentCommentUser.UserName + '<\/a> commented on post by <a>' + scope.activityUser.UserName + '<\/a>';
                        }
                        if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                          activityTitleMessage += ' in <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                        }
                      }
                    }
                  }
                  break;
                case (ActivityTypeID == 16):
                  activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> posted a review on <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                  break;
                case (ActivityTypeID == 25):
                  activityTitleMessage = '<a>' + scope.subjectUser.UserName + '<\/a> created a new poll';
                  if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                    activityTitleMessage += ' in <a ' + openGroupDetailModalPopup + '>' + scope.activity.EntityName + '<\/a>';
                  }
                  break;
              }
            }
            //console.log(activityTitleMessage);
            return activityTitleMessage;
          }
        }
      }
    }]);*/

    app.directive("createTitleMessage", ['$compile', '$window', '$sce', '$parse' , function ($compile, $window, $sce, $parse) {
      return{
        
        restrict: 'A',
        scope: {
          activityLogDetails: '=',
          subjectUser: '=',
          activityUser: '=',
          parentCommentUser: '=',
          activity: '=',
          parentActivity: '=',
          parentActivityUser: '=',
          activityTitleMessage: '=',
          activityPostType: '=',
          isBlockquote: '=',
          parentCommentId: '=',
          groupProfile: '=',
          pageProfile: '=',
          userProfile: '=',
          eventProfile: '=',
          pollData: '=',
          originalPost: '=',
        },
        link: function (scope, elem, attrs) {
          var messageTitlteString = '',
              messageTitlteElement = '',
              openGroupDetailModalPopup = '';
          openGroupDetailModalPopup = 'ng-click="$emit(\'openGroupDetailModalPopup\', { ModuleID: ' + scope.activity.ModuleID + ', ModuleEntityID: ' + scope.activity.ModuleEntityID + ' });"';
          messageTitlteString = createTitleMessage(scope.activityLogDetails.ActivityTypeID);
          elem.html(messageTitlteString);
          $compile(elem.contents())(scope);
          function createTitleMessage(ActivityTypeID) {
            var activityTitleMessage = '';
            //var suser = scope.subjectUser;
            //var suser = {UserID:scope.subjectUser.UserID,UserName:scope.subjectUser.UserName};
            var user_id = scope.subjectUser.UserID;
            var user_name = scope.subjectUser.UserName;
            var user_guid = scope.subjectUser.UserGUID;
            if (scope.activityLogDetails && ActivityTypeID) {
              switch (true) {
                case (ActivityTypeID == 1):
                  //activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>';

                  if(scope.subjectUser.IsVIP == 1) {                    
                    activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="VIP User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a>';
                  } if(scope.subjectUser.IsAssociation == 1) {                    
                    activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="Association User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a>';
                  } else {
                    activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>';
                  }

                  if(scope.activity.Album.length>0 && scope.activity.PostContent=="")
                  {
                    var mediatype = "media";
                    var prev = 0;
                    angular.forEach(scope.activity.Album[0].Media,function(val,key){
                      if(val.MediaType == 'Image')
                      {
                        if(prev == 1 || prev == 0)
                        {
                          prev = 1;
                        }
                        else
                        {
                          prev = 3;
                        }
                      }
                      else
                      {
                        if(prev == 2 || prev == 0)
                        {
                          prev = 2;
                        }
                        else
                        {
                          prev = 3;
                        }
                      }
                    });

                    if(prev == 1)
                    {
                      mediatype = 'photo';
                    }
                    else if(prev == 2)
                    {
                      mediatype = 'video';
                    }

                    if(scope.subjectUser.IsVIP == 1) {   
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="VIP User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> added '+ scope.activity.Album[0].Media.length +' new '+mediatype                 
                      
                    } if(scope.subjectUser.IsAssociation == 1) {                    
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="Association User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> added '+ scope.activity.Album[0].Media.length +' new '+mediatype
                    } else {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> added '+ scope.activity.Album[0].Media.length +' new '+mediatype;
                    }                    
                  }
                  if(scope.activity.Files.length>0)
                  {
                    if(scope.activity.Album.length>0)
                    {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> added '+ (scope.activity.Album[0].Media.length+scope.activity.Files.length) +' new media';
                    }
                    else
                    {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> added '+ scope.activity.Files.length +' new media';
                    }
                  }
                  break;
                case (ActivityTypeID == 21):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> viewed ';
                  if(scope.activityLogDetails.ModuleID == 1)
                  {
                    activityTitleMessage += '<a class="" entitytype="group" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.groupProfile.EntityName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 3)
                  {
                    activityTitleMessage += '<a class="" entitytype="user" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.userProfile.UserName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 14)
                  {
                    activityTitleMessage += '<a class="" entitytype="event" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.eventProfile.EntityName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 18)
                  {
                    activityTitleMessage += '<a class="" entitytype="page" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.pageProfile.EntityName + '<\/a>';
                  }
                  break;
                case (ActivityTypeID == 33):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> voted to <a onclick="javascript:void(0);">Poll</a> created by <a onclick="javascript:void(0);">'+scope.pollData.PollData[0].CreatedBy.Name+'</a>';
                  break;
                case (ActivityTypeID == 27):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> searched '+scope.activityLogDetails.ActivityData;
                  break;
                case (ActivityTypeID == 23):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> updated their profile picture';
                  break;
                case (ActivityTypeID == 24):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> updated their cover picture';
                  break;
                case (ActivityTypeID == 7):
                case (ActivityTypeID == 8):
                case (ActivityTypeID == 11):
                case (ActivityTypeID == 12):
                case (ActivityTypeID == 26):
                case (ActivityTypeID == 49):
                  if (scope.subjectUser && scope.subjectUser.UserName) {
                    if (ActivityTypeID == 12) {
                      if(scope.activity.PostAsModuleID == '18')
                      {
                        activityTitleMessage = '<a class="" entitytype="page" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                      }
                      else
                      {
                        activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> posted in <a  class="" entitytype="page" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                      }
                    } else if(ActivityTypeID == 49)  {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>';
                      if ( scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT')  || (scope.activity.EntityType == 'FORUMCATEGORY') || (scope.activity.EntityType == 'QUIZ') ) ) {
                        activityTitleMessage += ' posted in  <a class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'">' + scope.activity.EntityName + '<\/a>';
                      }
                      else
                      {
                        activityTitleMessage += ' >  <a class="" entitytype="user" entityguid="'+scope.activity.EntityGUID+'">' + scope.activity.EntityName + '<\/a>';
                      }
                    } else {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>';
                      if ( scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT')  || (scope.activity.EntityType == 'FORUMCATEGORY') || (scope.activity.EntityType == 'QUIZ') ) ) {
                        activityTitleMessage += ' posted in  <a class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                      }
                      else
                      {
                        activityTitleMessage += ' >  <a class="" entitytype="user" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                      }
                    }
                  }
                  break;
                case (ActivityTypeID == 9):
                case (ActivityTypeID == 10):
                case (ActivityTypeID == 14):
                case (ActivityTypeID == 15):
                  if (!scope.isBlockquote) {
                    if (scope.subjectUser && scope.subjectUser.UserName && (scope.subjectUser.UserGUID == scope.parentActivityUser.UserGUID)) {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> shared own ';
                    } else {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> shared <a onclick="javascript:void(0);">' + scope.parentActivityUser.UserName + '<\/a>\'s ';
                    }
                    if ( ( ActivityTypeID == 9 ) || ( ActivityTypeID == 10 ) ) {
                      activityTitleMessage += 'post';
                    } else {
                      activityTitleMessage += 'media';
                    }
                    if (scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') )) {
                      activityTitleMessage += ' in  <a class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                    }
                    if ((ActivityTypeID == 9) && scope.activity.EntityName && (scope.activity.EntityType == 'USER')) {
                      activityTitleMessage += ' with <a class="" entitytype="user" entityguid="'+scope.activity.EntityGUID+'" onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                    }
                  } else {
                    if (scope.subjectUser && scope.subjectUser.UserName && (scope.subjectUser.UserGUID == scope.parentActivityUser.UserGUID)) {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>';
                    } else {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.parentActivityUser.UserName + '<\/a>';
                    }
                    if (scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') )) {
                      activityTitleMessage += ' posted in  <a  class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                    }
                  }
                  break;
                case (ActivityTypeID == 19):
                    if(scope.activityLogDetails.ModuleID == 19)
                    {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> liked a post';
                    }
                    else 
                    {
                      activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> liked a comment';
                    }
                  break;
                case (ActivityTypeID == 20):
                  //console.log('scope.originalPost', scope.originalPost+' '+scope.activityUser.UserName);
                  //console.log('scope.isBlockquote', scope.isBlockquote);
                  if(scope.originalPost) {
                    if(scope.activityUser.IsVIP == 1) {                    
                      activityTitleMessage = '<a entitytype="user" entityguid="'+scope.activityUser.UserGUID+'" onclick="showUserPersona(\''+scope.activityUser.UserID+'\',\''+scope.activityUser.UserGUID+'\',\''+scope.activityUser.UserName+'\')">' + scope.activityUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="VIP User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a>';
                    } if(scope.activityUser.IsAssociation == 1) {                    
                      activityTitleMessage = '<a entitytype="user" entityguid="'+scope.activityUser.UserGUID+'" onclick="showUserPersona(\''+scope.activityUser.UserID+'\',\''+scope.activityUser.UserGUID+'\',\''+scope.activityUser.UserName+'\')">' + scope.activityUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="Association User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a>';
                    } else {
                      activityTitleMessage = '<a entitytype="user" entityguid="'+scope.activityUser.UserGUID+'" onclick="showUserPersona(\''+scope.activityUser.UserID+'\',\''+scope.activityUser.UserGUID+'\',\''+scope.activityUser.UserName+'\')">' + scope.activityUser.UserName + '<\/a>';
                    }
                    
                    //activityTitleMessage += '<a  class="" entitytype="user" entityguid="'+scope.activityUser.UserGUID+'" '+openGroupDetailModalPopup+'>' + scope.activityUser.UserName + '<\/a>';
                  }
                  else
                  {

                    if (!scope.isBlockquote) {
                      if (!scope.parentCommentId) { // if its a comment
                        if (scope.subjectUser && scope.subjectUser.UserName && scope.activityUser && scope.activityUser.UserName) {
                          if (scope.subjectUser.UserGUID && scope.activityUser.UserGUID && (scope.subjectUser.UserGUID == scope.activityUser.UserGUID)) {
                            if(scope.subjectUser.IsVIP == 1) {                    
                              activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="VIP User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> commented on own post';
                            } if(scope.subjectUser.IsAssociation == 1) {                    
                              activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="Association User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> commented on own post';
                            } else {
                              activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> commented on own post';
                            }
                            
                          } else {
                            if(scope.subjectUser.IsVIP == 1) {                    
                              activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="VIP User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> commented on post by <a onclick="javascript:void(0);">' + scope.activityUser.UserName + '<\/a>';
                            } if(scope.subjectUser.IsAssociation == 1) {      
                              activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="Association User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> commented on post by <a onclick="javascript:void(0);">' + scope.activityUser.UserName + '<\/a>';
                            } else {
                              activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> commented on post by <a onclick="javascript:void(0);">' + scope.activityUser.UserName + '<\/a>';
                            }
                            
                          }
                          if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                            activityTitleMessage += ' in <a  class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                          }
                        }
                      } else { // else its a reply
                        if (scope.subjectUser.UserGUID && scope.activityUser.UserGUID && (scope.subjectUser.UserGUID == scope.parentCommentUser.UserGUID)) {
                          if(scope.subjectUser.IsVIP == 1) {                    
                            activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="VIP User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> replied on own post';
                          } if(scope.subjectUser.IsAssociation == 1) {      
                            activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="Association User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> replied on own post';
                          } else {
                            activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> replied on own post';
                          }
                          //activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> replied on own comment';
                        } else {
                          if(scope.subjectUser.IsVIP == 1) {                    
                            activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="VIP User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> replied on comment by <a onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a>';
                          } if(scope.subjectUser.IsAssociation == 1) {      
                            activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a>&nbsp;<a  uib-tooltip="Association User" tooltip-append-to-body="true" class="icn circle-icn circle-primary"><i class="ficon-check"></i></a> replied on comment by <a onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a>';
                          } else {
                            activityTitleMessage = '<a entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> replied on comment by <a onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a>';
                          }                          
                        }
                        if ( ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                          activityTitleMessage += ' in <a  class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                        }
                      }
                    } else {
                      if (!scope.parentCommentId) { // if its a comment
                        activityTitleMessage = createTitleMessage(scope.activity.ActivityTypeID);
                      } else {
                        if (scope.parentCommentUser && scope.parentCommentUser.UserName && scope.activityUser && scope.activityUser.UserName) {
                          if (scope.parentCommentUser.UserGUID && scope.activityUser.UserGUID && (scope.parentCommentUser.UserGUID == scope.activityUser.UserGUID)) {
                            activityTitleMessage = '<a  class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a> commented on own post';
                          } else {
                            activityTitleMessage = '<a  class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a> commented on post by <a>' + scope.activityUser.UserName + '<\/a>';
                          }
                          if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                            activityTitleMessage += ' in <a  class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                          }
                        }
                      }
                    }
                  }
                  break;
                case (ActivityTypeID == 16):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> posted a review on <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                  break;
                case (ActivityTypeID == 17):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> updated a review on <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                  break;
                case (ActivityTypeID == 25):
                  activityTitleMessage = '<a class="" entitytype="user" entityguid="'+scope.subjectUser.UserGUID+'" onclick="showUserPersona(\''+user_id+'\',\''+user_guid+'\',\''+user_name+'\')">' + scope.subjectUser.UserName + '<\/a> created a new poll';
                  if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                    activityTitleMessage += ' in <a  class="" entitytype="'+scope.activity.EntityType.toLowerCase()+'" entityguid="'+scope.activity.EntityGUID+'" '+openGroupDetailModalPopup+'>' + scope.activity.EntityName + '<\/a>';
                  }
                  break;
              }
            }
            //console.log(activityTitleMessage);
            return activityTitleMessage;
          }
        }
      
      }
    }]);

  app.directive("createTitleMessagePersona", ['$compile', '$window', '$sce', '$parse' , function ($compile, $window, $sce, $parse) {
      return{
        restrict: 'A',
        scope: {
          activityLogDetails: '=',
          subjectUser: '=',
          activityUser: '=',
          parentCommentUser: '=',
          activity: '=',
          parentActivity: '=',
          parentActivityUser: '=',
          activityTitleMessage: '=',
          activityPostType: '=',
          isBlockquote: '=',
          parentCommentId: '=',
          groupProfile: '=',
          pageProfile: '=',
          userProfile: '=',
          eventProfile: '=',
          pollData: '=',
        },
        link: function (scope, elem, attrs) {
          var messageTitlteString = '',
              messageTitlteElement = '',
              openGroupDetailModalPopup = '';
          messageTitlteString = createTitleMessage(scope.activityLogDetails.ActivityTypeID);
          elem.html(messageTitlteString);
          $compile(elem.contents())(scope);
          function createTitleMessage(ActivityTypeID) {
            var activityTitleMessage = '';
            if (scope.activityLogDetails && ActivityTypeID) {
              switch (true) {
                case (ActivityTypeID == 1):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a>';
                  if(scope.activity.Album.length>0 && scope.activity.PostContent=="")
                  {
                    var mediatype = "media";
                    var prev = 0;
                    angular.forEach(scope.activity.Album[0].Media,function(val,key){
                      if(val.MediaType == 'Image')
                      {
                        if(prev == 1 || prev == 0)
                        {
                          prev = 1;
                        }
                        else
                        {
                          prev = 3;
                        }
                      }
                      else
                      {
                        if(prev == 2 || prev == 0)
                        {
                          prev = 2;
                        }
                        else
                        {
                          prev = 3;
                        }
                      }
                    });

                    if(prev == 1)
                    {
                      mediatype = 'photo';
                    }
                    else if(prev == 2)
                    {
                      mediatype = 'video';
                    }

                    activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> added '+ scope.activity.Album[0].Media.length +' new '+mediatype;
                  }
                  if(scope.activity.Files.length>0)
                  {
                    if(scope.activity.Album.length>0)
                    {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> added '+ (scope.activity.Album[0].Media.length+scope.activity.Files.length) +' new media';
                    }
                    else
                    {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> added '+ scope.activity.Files.length +' new media';
                    }
                  }
                  break;
                case (ActivityTypeID == 21):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> viewed ';
                  if(scope.activityLogDetails.ModuleID == 1)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.groupProfile.EntityName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 3)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.userProfile.UserName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 14)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.eventProfile.EntityName + '<\/a>';
                  }
                  else if(scope.activityLogDetails.ModuleID == 18)
                  {
                    activityTitleMessage += '<a onclick="javascript:void(0);">' + scope.pageProfile.EntityName + '<\/a>';
                  }
                  break;
                case (ActivityTypeID == 33):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> voted to <a onclick="javascript:void(0);">Poll</a> created by <a onclick="javascript:void(0);">'+scope.pollData.PollData[0].CreatedBy.Name+'</a>';
                  break;
                case (ActivityTypeID == 27):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> searched '+scope.activityLogDetails.ActivityData;
                  break;
                case (ActivityTypeID == 23):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> updated their profile picture';
                  break;
                case (ActivityTypeID == 24):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> updated their cover picture';
                  break;
                case (ActivityTypeID == 5):
                case (ActivityTypeID == 7):
                case (ActivityTypeID == 8):
                case (ActivityTypeID == 9):
                case (ActivityTypeID == 11):
                case (ActivityTypeID == 12):
                case (ActivityTypeID == 26):
                case (ActivityTypeID == 40):
                  if (scope.subjectUser && scope.subjectUser.UserName) {
                    if (ActivityTypeID == 12) {
                      if(scope.activity.PostAsModuleID == '18')
                      {
                        activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                      }
                      else
                      {
                        activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> posted in <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                      }
                    } else {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a>';
                      if ( scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT')  || (scope.activity.EntityType == 'FORUMCATEGORY') ) ) {
                        activityTitleMessage += ' posted in  <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                      }
                      else
                      {
                          if(scope.activity.EntityName) {
                              activityTitleMessage += ' >  <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                          } 
                        
                      }
                    }
                  }
                  break;
                case (ActivityTypeID == 9):
                case (ActivityTypeID == 10):
                case (ActivityTypeID == 14):
                case (ActivityTypeID == 15):
                  if (!scope.isBlockquote) {
                    if (scope.subjectUser && scope.subjectUser.UserName && (scope.subjectUser.UserGUID == scope.parentActivityUser.UserGUID)) {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> shared own ';
                    } else {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> shared <a onclick="javascript:void(0);">' + scope.parentActivityUser.UserName + '<\/a>\'s ';
                    }
                    if ( ( ActivityTypeID == 9 ) || ( ActivityTypeID == 10 ) ) {
                      activityTitleMessage += 'post';
                    } else {
                      activityTitleMessage += 'media';
                    }
                    if (scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') )) {
                      activityTitleMessage += ' in  <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                    }
                    if ((ActivityTypeID == 9) && scope.activity.EntityName && (scope.activity.EntityType == 'USER')) {
                      activityTitleMessage += ' with <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                    }
                  } else {
                    if (scope.subjectUser && scope.subjectUser.UserName && (scope.subjectUser.UserGUID == scope.parentActivityUser.UserGUID)) {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a>';
                    } else {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.parentActivityUser.UserName + '<\/a>';
                    }
                    if (scope.activity.EntityName && ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') )) {
                      activityTitleMessage += ' posted in  <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                    }
                  }
                  break;
                case (ActivityTypeID == 19):
                    if(scope.activityLogDetails.ModuleID == 19)
                    {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> liked a post';
                    }
                    else 
                    {
                      activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> liked a comment';
                    }
                  break;
                case (ActivityTypeID == 20):
                  if (!scope.isBlockquote) {
                    if (!scope.parentCommentId) { // if its a comment
                      if (scope.subjectUser && scope.subjectUser.UserName && scope.activityUser && scope.activityUser.UserName) {
                        if (scope.subjectUser.UserGUID && scope.activityUser.UserGUID && (scope.subjectUser.UserGUID == scope.activityUser.UserGUID)) {
                          activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> commented on own post';
                        } else {
                          activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> commented on post by <a onclick="javascript:void(0);">' + scope.activityUser.UserName + '<\/a>';
                        }
                        if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                          activityTitleMessage += ' in <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                        }
                      }
                    } else { // else its a reply
                      if (scope.subjectUser.UserGUID && scope.activityUser.UserGUID && (scope.subjectUser.UserGUID == scope.parentCommentUser.UserGUID)) {
                        activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> replied on own comment';
                      } else {
                        activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> replied on comment by <a onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a>';
                      }
                      if ( ( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                        activityTitleMessage += ' in <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                      }
                    }
                  } else {
                    if (!scope.parentCommentId) { // if its a comment
                      activityTitleMessage = createTitleMessage(scope.activity.ActivityTypeID);
                    } else {
                      if (scope.parentCommentUser && scope.parentCommentUser.UserName && scope.activityUser && scope.activityUser.UserName) {
                        if (scope.parentCommentUser.UserGUID && scope.activityUser.UserGUID && (scope.parentCommentUser.UserGUID == scope.activityUser.UserGUID)) {
                          activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a> commented on own post';
                        } else {
                          activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.parentCommentUser.UserName + '<\/a> commented on post by <a>' + scope.activityUser.UserName + '<\/a>';
                        }
                        if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                          activityTitleMessage += ' in <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                        }
                      }
                    }
                  }
                  break;
                case (ActivityTypeID == 16):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> posted a review on <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                  break;
                case (ActivityTypeID == 17):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> updated a review on <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                  break;
                case (ActivityTypeID == 25):
                  activityTitleMessage = '<a onclick="javascript:void(0);">' + scope.subjectUser.UserName + '<\/a> created a new poll';
                  if (( ( scope.activity.EntityType == 'GROUP' ) || (scope.activity.EntityType == 'PAGE') || (scope.activity.EntityType == 'EVENT') ) && scope.activity.EntityName) {
                    activityTitleMessage += ' in <a onclick="javascript:void(0);">' + scope.activity.EntityName + '<\/a>';
                  }
                  break;
              }
            }
            //console.log(activityTitleMessage);
            return activityTitleMessage;
          }
        }
      }
    }]);

  app.directive("initScrollFix", ['$timeout', '$sce', function ($timeout, $sce) {
    return {
      restrict: "A",
      link: function (scope, elem, attrs, ngModelCtrl) {
        angular.element(elem).scrollFix({
          fixTop: (attrs.initScrollFix === 'scrollFix') ? 0 : 80
        });
      }
    }
  }]);
  
  app.directive("dropdownStopPropagation", ['$timeout', '$document', function ($timeout, $document) {
    return {
      restrict: "A",
      link: function (scope, elem, attrs, ngModelCtrl) {
        angular.element($document).on('click','[data-type="stopPropagation"]', function (event) {
            event.stopImmediatePropagation()
        });
      }
    }
  }]);
  
  app.directive("makeContentHighlighted", ['$timeout', '$sce', function ($timeout, $sce) {
      return {
        restrict: "A",
        scope: {
          'makeContentHighlighted': '='
        },
        link: function (scope, elem, attrs, ngModelCtrl) {
          var searchFieldId = attrs.searchfieldid,
                  searchFieldValue = angular.element('#' + searchFieldId).val(),
                  contentToProcess = scope.makeContentHighlighted;
          if (contentToProcess && searchFieldValue) {
            scope.makeContentHighlighted = $sce.trustAsHtml(contentToProcess.replace(new RegExp(searchFieldValue, 'gi'), "<abbr class='highlightedText'>$&<\/abbr>"));
          }
        }
      }
    }]);

  app.directive("initFilterDatepicker", ['$timeout', function ($timeout) {
    return {
      restrict: "A",
      require: "ngModel",
      link: function (scope, elem, attrs, ngModelCtrl) {
        var updateModel = function (dateText) {
                scope.$apply(function () {
                  ngModelCtrl.$setViewValue(dateText);
                });
            }, dateFormat = "mm/dd/yy",
  //      var dateFormat = 'dd/mm/yy',
          options = {
          dateFormat: dateFormat,
          maxDate: 0,
          defaultDate: "+1w",
          changeMonth: false,
          changeYear: false,
          numberOfMonths: 1,
          onSelect: function (dateText) {
            if( elem.attr('pickerType') ===  'from' ) {
              to.datepicker("option", "minDate", getDate(this));
            } else {
              from.datepicker("option", "maxDate", getDate(this));
            }
            updateModel(dateText);
            // applySearchFilter('Datepicker', '0');
          },
        },
        fromId = "#" + attrs.fromid,
        toId = "#" + attrs.toid,
        from = angular.element(fromId),
        to = angular.element(toId);
        if( elem.attr('pickerType') ===  'from' ) {
          from.datepicker(options);
        } else {
          to.datepicker(options);
        }

        function getDate(element) {
          var date;
          try {
            if ( element && element.value ) {
              date = $.datepicker.parseDate(dateFormat, element.value);
            } else {
              var d = new Date();
              date = $.datepicker.parseDate(dateFormat, d);
            }
          } catch (error) {
            date = null;
          }

          return date;
        }
      }
    }
  }]);

  app.directive('googlePlace', ['$timeout', function ($timeout) {
    return {
      require: 'ngModel',
      scope: {
        ngModel: '=',
        details: '=?'
      },
      link: function (scope, element, attrs, model) {
        var options = {
          types: ['(cities)'],
//          componentRestrictions: {}
        };

        scope.gPlace = new google.maps.places.Autocomplete(element[0], options);

        google.maps.event.addListener(scope.gPlace, 'place_changed', function () {
          var geoComponents = scope.gPlace.getPlace(),
              latitude = geoComponents.geometry.location.lat(),
              longitude = geoComponents.geometry.location.lng(),
              addressComponents = geoComponents.address_components;

          addressComponents = addressComponents.filter(function (component) {
//            console.log(component);
            switch (component.types[0]) {
              case "locality": // city
                return true;
              case "administrative_area_level_1": // state
                return true;
              case "country": // country
                return true;
              default:
                return false;
            }
          }).map(function (obj) {
            switch (obj.types[0]) {
              case "locality": // city
                return { CityName: obj.long_name, CityCode: obj.short_name };
              case "administrative_area_level_1": // state
                return { StateName: obj.long_name, StateCode: obj.short_name };
              case "country": // country
                return { CountryName: obj.long_name, CountryCode: obj.short_name };
              default:
                return false;
            }
          });

//          addressComponents.push(latitude, longitude);

          scope.$apply(function () {
            scope.details = addressComponents; // array containing each location component
            $timeout(function(){
              scope.$apply(function () {
                model.$setViewValue(element.val());
              });
            });
          });
        });
      }
    };
  }]);

app.directive('dynamicUrl', [function () {
    return {
        restrict: 'A',
        link: function postLink(scope, element, attr) {
            // console.log(attr);
            element.attr('src', attr.dynamicUrlSrc);
        }
    };
}]);

// we create a simple directive to call a function on scrolled to bottom
app.directive("whenScrolled", function () {
    return{
        restrict: 'A',
        link: function (scope, elem, attrs) {
            // we get a list of elements of size 1 and need the first element
            var raw = elem[0];
            // we load more elements when scrolled past a limit
            elem.bind("scroll", function () {
                if (raw.scrollTop + raw.offsetHeight + 5 >= raw.scrollHeight) {
                    // we can give any function which loads more elements into the list
                    scope.$apply(attrs.whenScrolled);
                }
            });
        }
    }
});


})();