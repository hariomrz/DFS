/* Notification directive */
app.directive('html', [function() {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            element.html(attrs.html);
        }
    }
}]);

/* Notification controller  */
app.controller('NotificationCtrl', ['$sce', 'GlobalService', '$rootScope', '$scope', '$interval', 'socket', 'appInfo', 'WallService', function($sce, GlobalService, $rootScope, $scope, $interval, socket, appInfo, WallService) {
    $scope.notification_count = 0;
    $scope.notification = new Array();
    $scope.AllNotifications = new Array();
    $scope.offset = 0;
    $scope.show_more_visible = true;
    $scope.PageNo = 1;
    $scope.PageNoAll = 1;
    $scope.PageSize = 10;
    $scope.NPS = 5;
    $scope.nloader = 1;
    $scope.mloader = 1;
    $scope.mlen = 1;
    $scope.nlen = 1;
    $scope.scroll_busy = false;
    $scope.all_scroll_busy = false;
    $scope.image_server_path = image_server_path;
    $scope.type = '';
    $scope.show_all_notify = 0;
    $scope.show_all_notifys = 0;
    $scope.BaseUrl = base_url;
    $scope.getNotification = function(Type) {
        if ($scope.scroll_busy)
            return;

        if (($scope.PageNo > 3 && $scope.type !== 'unread') || (($scope.type == 'unread' && ($scope.PageNo - 1) * $scope.PageSize) > $scope.TotalUnread)) {
            $scope.nloader = 0;
            return;
        }
        $scope.scroll_busy = true;
        var requestData = {
            PageNo: $scope.PageNo,
            PageSize: $scope.PageSize,
            Type: $scope.type
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/get_notifications_count', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {

                /*$(response.Data).each(function(k, v) {
                 
                 var append = true;
                 $($scope.notification).each(function(k1, v1) {
                 if (v.NotificationGUID == v1.NotificationGUID) {
                 append = false;
                 }
                 });
                 if (append) {
                 links = '<a href="'+base_url+response.Data[k].Link+'">Post</a>';
                 response.Data[k].NotificationText = response.Data[k].NotificationText.replace('#Post#',links);
                 $scope.notification.push(response.Data[k]);
                 }
                 })*/

                angular.forEach(response.Data, function(v, k) {
                    response.Data[k] = $scope.get_notification_text(v);
                    var append = true;
                    $($scope.notification).each(function(k1, v1) {
                        if (v.NotificationGUID == v1.NotificationGUID) {
                            append = false;
                        }
                    });
                    if (append) {
                        $scope.notification.push(response.Data[k]);
                    }
                });

                //$scope.notification       = response.Data;
                /*$scope.notification_count = response.TotalNotificationRecords;
                $scope.message_count = response.TotalMessageRecords;
                $scope.total_count = response.TotalRecords;*/
                if ($scope.PageNo == 1) {
                    $scope.UpdateReadNotification();
                }
                $scope.PageNo = $scope.PageNo + 1;
                //$scope.PageSize           = response.PageSize;
                $scope.NPS = response.PageSize;
                $scope.nloader = 0;
                $scope.nlen = $scope.notification.length;
                $scope.scroll_busy = false;
                /*if(response.TotalUnread==0){
                 $scope.TotalUnread = '';
                 }else{
                 $scope.TotalUnread = response.TotalUnread;
                 }*/

                //$scope.TotalUnread = response.TotalUnread;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
        //console.log($scope.notification);
    }

    $scope.bubble_notification_list = [];

    $scope.get_bubble_notification = function(current_url, notification_guid, notification_type_id) {
        var reqData = { CurrentURL: current_url, NotificationGUID: notification_guid, NotificationTypeID: notification_type_id };
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/bubble_notifications', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                if (response.Data.Type == 'Append') {
                    switch (notification_type_id) {
                        case 2:
                        case 21:
                        case 55:
                            angular.element(document.getElementById('WallPostCtrl')).scope().appendCommentData(response.Data.Data[0]);
                            break;
                        case 3:
                        case 54:
                            angular.element(document.getElementById('WallPostCtrl')).scope().appendLikeDetails('ACTIVITY', response.Data.Data);
                            break;
                    }
                } else {
                    response['Data']['Data'] = $scope.get_notification_text(response.Data.Data);
                    $scope.bubble_notification_list.unshift(response.Data.Data);
                }
                $('#bubbleNotify [data-toggle="collapse"]').collapse();
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.removeBubbleNotification = function(notification_guid) {
        if (notification_guid == '') {
            $scope.bubble_notification_list = [];
        } else {
            angular.forEach($scope.bubble_notification_list, function(val, key) {
                if (val.NotificationGUID == notification_guid) {
                    $scope.bubble_notification_list.splice(key, 1);
                }
            });
        }
    }

    socket.on('bubbleNotifications', function(data) {
        var current_url = document.URL;
        current_url = current_url.split(base_url);
        current_url = current_url[1];
        $scope.get_bubble_notification(current_url, data.NotificationGUID, data.NotificationTypeID);
    });

    $scope.get_unread_notification = function(type) {
        $scope.type = 'unread';
        if (typeof type === 'undefined') {
            $scope.show_all_notify = 1;
            $scope.PageNo = 1;
            $scope.notification = new Array();
            $scope.getNotification();
        } else {
            $scope.show_all_notifys = 1;
            $scope.PageNoAll = 1;
            $scope.AllNotifications = new Array();
            $scope.getAllNotifications();
        }
    }


    $scope.get_notification_text = function(obj) {
        var p1 = '';
        var p2 = '';
        var p3 = '';
        var p1_module_id = '';
        var p2_module_id = '';
        var p1_module_entity_id = '';
        var p2_module_entity_id = '';
        //console.log(obj.NotificationTypeID);
        if (typeof obj.P1 !== 'undefined') {
            var p1len = obj.P1.length;
            p1_module_id = obj.P1[0].ModuleID;
            p1_module_entity_id = obj.P1[0].ModuleEntityGUID;
            if (p1len > 0) {
                if (obj.P1[0].ModuleID == 3) {
                    entitytype = 'user';
                } else if (obj.P1[0].ModuleID == 18) {
                    entitytype = 'page';
                } else if (obj.P1[0].ModuleID == 1) {
                    entitytype = 'group';
                } else if (obj.P1[0].ModuleID == 14) {
                    entitytype = 'event';
                } else if (obj.P1[0].ModuleID == 29) {
                    entitytype = 'skill';
                } else if (obj.P1[0].ModuleID == 27) {
                    entitytype = 'category';
                } else if (obj.P1[0].ModuleID == 33) {
                    entitytype = 'forum';
                } else if (obj.P1[0].ModuleID == 34) {
                    entitytype = 'forumcategory';
                }
                
                card_class = "";
                if (obj.P1[0].ModuleEntityGUID != '') {
                    card_class = "loadbusinesscard";
                }

                p1 += '<a entitytype="' + entitytype + '" entityguid="' + obj.P1[0].ModuleEntityGUID + '" class="' + card_class + '">' + obj.P1[0].FirstName + ' ' + obj.P1[0].LastName + '</a>';
            }
            if (p1len > 1) {
                p1 += ' and ';

                if (obj.P1[1].ModuleID == 3) {
                    entitytype = 'user';
                } else if (obj.P1[1].ModuleID == 18) {
                    entitytype = 'page';
                } else if (obj.P1[1].ModuleID == 1) {
                    entitytype = 'group';
                } else if (obj.P1[1].ModuleID == 14) {
                    entitytype = 'event';
                }

                card_class = "";
                if (obj.P1[1].ModuleEntityGUID != '') {
                    card_class = "loadbusinesscard";
                }

                if (p1len == 2) {
                    p1 += '<a entitytype="' + entitytype + '" entityguid="' + obj.P1[1].ModuleEntityGUID + '" class="' + card_class + '">' + obj.P1[1].FirstName + ' ' + obj.P1[1].LastName + '</a>';
                } else {
                    p1 += '<a>' + (parseInt(p1len) - 1) + ' others' + '</a>';
                }

                if (obj.NotificationTypeID == 5 || obj.NotificationTypeID == 45 || obj.NotificationTypeID == 31 || obj.NotificationTypeID == 32) {
                    obj.NotificationText = obj.NotificationText.replace("is", "are");
                }
                if (obj.NotificationTypeID == 24) {
                    obj.NotificationText = obj.NotificationText.replace("has", "have");
                }
            }

        }

        if (typeof obj.P2 !== 'undefined') {
            var p2len = obj.P2.length;
            p2_module_id = obj.P2[0].ModuleID;
            p2_module_entity_id = obj.P2[0].ModuleEntityGUID;

            if (obj.P2[0].ModuleID == 3) {
                entitytype = 'user';
            } else if (obj.P2[0].ModuleID == 18) {
                entitytype = 'page';
            } else if (obj.P2[0].ModuleID == 1) {
                entitytype = 'group';
            } else if (obj.P2[0].ModuleID == 14) {
                entitytype = 'event';
            }

            card_class = "";
            if (obj.P2[0].ModuleEntityGUID != '') {
                card_class = "loadbusinesscard";
            }

            if (p2len > 0) {
                p2 += '<a entitytype="' + entitytype + '" entityguid="' + obj.P2[0].ModuleEntityGUID + '" class="' + card_class + '">' + obj.P2[0].FirstName + ' ' + obj.P2[0].LastName + '</a>';
            }
            if (p2len > 1) {
                p2 += ' and ';

                if (obj.P2[1].ModuleID == 3) {
                    entitytype = 'user';
                } else if (obj.P2[1].ModuleID == 18) {
                    entitytype = 'page';
                } else if (obj.P2[1].ModuleID == 1) {
                    entitytype = 'group';
                } else if (obj.P2[1].ModuleID == 14) {
                    entitytype = 'event';
                }

                card_class = "";
                if (obj.P2[1].ModuleEntityGUID != '') {
                    card_class = "loadbusinesscard";
                }
                if (obj.NotificationTypeID == 100) {
                    obj.NotificationText = obj.NotificationText.replace("new skill", "new skills");
                }
                if (p2len == 2) {
                    p2 += '<a entitytype="' + entitytype + '" entityguid="' + obj.P2[1].ModuleEntityGUID + '" class="' + card_class + '">' + obj.P2[1].FirstName + ' ' + obj.P2[1].LastName + '</a>';
                } else {
                    if ((obj.NotificationTypeID == 97 || obj.NotificationTypeID == 100) && p2len > 2) {
                        p2 += '<a entitytype="' + entitytype + '" entityguid="' + obj.P2[1].ModuleEntityGUID + '" class="' + card_class + '">' + obj.P2[1].FirstName + ' ' + obj.P2[1].LastName + '</a> and ';
                        p2 += '<a>' + (parseInt(p2len) - 2) + ' others' + '</a>';
                    } else {
                        p2 += '<a>' + (parseInt(p2len) - 1) + ' others' + '</a>';
                    }
                }
            }
        } else {
            if (obj.NotificationTypeID == 86) {
                obj.NotificationText = obj.NotificationText.replace(",", "");
            }

        }

        if (typeof obj.P3 !== 'undefined') {
            var p3len = obj.P3.length;

            if (obj.P3[0].ModuleID == 3) {
                entitytype = 'user';
            } else if (obj.P3[0].ModuleID == 18) {
                entitytype = 'page';
            } else if (obj.P3[0].ModuleID == 1) {
                entitytype = 'group';
            } else if (obj.P3[0].ModuleID == 14) {
                entitytype = 'event';
            }

            card_class = "";
            if (obj.P3[0].ModuleEntityGUID != '') {
                card_class = "loadbusinesscard";
            }

            if (p3len > 0) {
                if(obj.NotificationTypeID == 115)
                {
                    if(obj.P3[0].FirstName !='' && obj.P3[0].FirstName != undefined)
                    {
                        p3 += obj.P3[0].FirstName;
                    }else{
                        
                    }p3 += '';
                    
                }
                else{
                    p3 += '<a  entitytype="' + entitytype + '" entityguid="' + obj.P3[0].ModuleEntityGUID + '" class="' + card_class + '">' + obj.P3[0].FirstName + ' ' + obj.P3[0].LastName + '</a>';
                }
            }
            if (p3len > 1 && obj.NotificationTypeID != 115) {
                p3 += ' and ';
                if (p3len == 2) {
                    if (obj.P3[1].ModuleID == 3) {
                        entitytype = 'user';
                    } else if (obj.P3[1].ModuleID == 18) {
                        entitytype = 'page';
                    } else if (obj.P3[1].ModuleID == 1) {
                        entitytype = 'group';
                    } else if (obj.P3[1].ModuleID == 14) {
                        entitytype = 'event';
                    }
                    card_class = "";
                    if (obj.P3[1].ModuleEntityGUID != '') {
                        card_class = "loadbusinesscard";
                    }
                    p3 += '<a entitytype="' + entitytype + '" entityguid="' + obj.P3[1].ModuleEntityGUID + '" class="' + card_class + '">' + obj.P3[1].FirstName + ' ' + obj.P3[1].LastName + '</a>';
                } else {
                    p3 += '<a>' + (parseInt(p3len) - 1) + ' others' + '</a>';
                }
            }
            
        }

        if (obj.NotificationTypeID == 48 && p1_module_id == p2_module_id && p1_module_entity_id == p2_module_entity_id) {
            obj.NotificationText = obj.NotificationText.replace("#p2#'s", "their");
        }


        obj.NotificationText = obj.NotificationText.replace("#p1#", p1);
        if (obj.NotificationTypeID == 115)
        {
            obj.NotificationText = obj.NotificationText.replace("\"#p2#\"", p2);
        }
        else{
            obj.NotificationText = obj.NotificationText.replace("#p2#", p2);
        }
        if (obj.NotificationTypeID == 115 && p3=='')
        {
            obj.NotificationText = obj.NotificationText.replace("#p3#", p3);
        }
        else{
            obj.NotificationText = obj.NotificationText.replace("#p3#", p3);
        }
        
        
        //str = msz.replace("{{User}}", '<a target="_self" href="' + UserURL + '">' + scope.getHighlighted(data.UserName) + '</a>');
        return obj;
    }

    socket.on('NotificationCount', function(data) {
        $scope.getNotificationCount();
    });

    $scope.redirectTo = function(link) {
        window.top.location = link;
    }

    $scope.getThumbImage = function(filename) {
        if (filename == undefined) {
            return;
        }
        var ext = filename.substr(filename.lastIndexOf('.') + 1);
        var fname = filename.substr(0, filename.lastIndexOf('.'));
        if (ext == 'jpg' || ext == 'JPG' || ext == 'png' || ext == 'PNG' || ext == 'bmp' || ext == 'BMP' || ext == 'gif' || ext == 'GIF' || ext == 'jpeg' || ext == 'JPEG') {
            return fname + '.' + ext;
        } else {
            return fname + '.jpg';
        }
    }

    $scope.getMsgBodyHTML = function(Body, flag) {
        if (flag == 1) {
            Body = Body.replace(/</g, '&lt');
            Body = Body.replace(/>/g, '&gt');
            Body = Body.replace(/&ltbr&gt/g, ' ');
            Body = Body.replace(/&ltbr \/&gt/g, ' ');
        } else {
            Body = Body.replace(/</g, '&lt');
            Body = Body.replace(/>/g, '&gt');
            Body = Body.replace(/&ltbr&gt/g, ' <br> ');
            Body = Body.replace(/&ltbr \/&gt/g, ' <br> ');
        }
        return Body;
    }

    $scope.removeAllBubbleNotification = function() {
        $scope.bubble_notification_list = [];
    }

    $scope.html_parse = function(string, no_limit) {
        if (no_limit !== 1) {
            string = smart_sub_str(50, string, true); //smart_substr(50, string); 
        }
        return $sce.trustAsHtml(string);
    }

    $scope.to_trusted = function(html_code, NotificationTypeID) {
      if ( NotificationTypeID == 131 ) {
        var ymdhisDatePattern = /\d{4}\-\d{1,2}\-\d{1,2}\s\d{2}\:\d{2}\:\d{2}/gm,
        dateArray = html_code.match(ymdhisDatePattern),
        displayDateObject = '',
        systemDateObject = '';
        html_code = html_code.replace(ymdhisDatePattern, function(match){
          systemDateObject = $scope.UTCtoTimeZone(match.trim());
          return moment(systemDateObject).format("D MMM [at] h:mm:ss a");
        });
      }
      return $sce.trustAsHtml(html_code);
    }
    
    var totalAllNotifications = -1 ;
    $scope.getAllNotifications = function(Type) {
        if(totalAllNotifications != -1 && $scope.AllNotifications.length >= totalAllNotifications) {
            $scope.all_scroll_busy = false;
            return;
        }
        
        if ($scope.all_scroll_busy)
            return;
        
        
        
        
        $scope.all_scroll_busy = true;
        /*if(typeof Type ==='undefined')
         {
         $scope.type = '';
         }
         else
         {
         $scope.type = Type;
         $scope.AllNotifications = new Array();
         }*/
        var requestData = {
            PageNo: $scope.PageNoAll,
            PageSize: $scope.PageSize,
            Type: $scope.type
        };

        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/list', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                
                if('TotalNotificationRecords' in response) {
                    totalAllNotifications = response.TotalNotificationRecords;
                }
                
                angular.forEach(response.Data, function(v, k) {

                    response.Data[k] = $scope.get_notification_text(v);

                    if (response.Data[k].Album !== '') {
                        if (typeof response.Data[k].Album[firstKeyOfObj(response.Data[k].Album)] !== 'undefined') {
                            response.Data[k].Album = response.Data[k].Album[firstKeyOfObj(response.Data[k].Album)].Media;
                        }
                    }
                    var append = true;
                    $($scope.AllNotifications).each(function(k1, v1) {
                        if (v.NotificationGUID == v1.NotificationGUID) {
                            append = false;
                        }
                    });
                    if (append) {
                        links = '<a href="' + base_url + response.Data[k].Link + '">Post</a>';
                        response.Data[k].NotificationText = response.Data[k].NotificationText.replace('#Post#', links);
                        //response.Data[k] = $scope.get_notification_text(v);
                        $scope.AllNotifications.push(response.Data[k]);
                    }
                });

                $scope.prevDate = '';
                $($scope.AllNotifications).each(function(k, v) {
                    if (typeof $scope.AllNotifications[k].CreatedDate !== 'undefined') {
                        var t = $scope.AllNotifications[k].CreatedDate.split(/[- :]/);
                        var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                        date = moment(date).format('MMM DD');
                        if ($scope.prevDate !== date) {
                            $scope.prevDate = date;
                            var newArr = { 'NewDate': $scope.prevDate, 'DateOnly': 1, 'StatusID': '17' };
                            if (typeof newArr === 'object') {
                                $scope.AllNotifications.splice(k, 0, newArr);
                            }
                            $scope.AllNotifications[k]['NewDate'] = $scope.prevDate;
                        } else {
                            $scope.AllNotifications[k]['NewDate'] = '';
                        }
                    } else if ($scope.AllNotifications[k].NewDate !== 'undefined') {
                        $scope.prevDate = $scope.AllNotifications[k].NewDate;
                    }
                });
                //console.log($scope.AllNotifications);
                $scope.PageNoAll = $scope.PageNoAll + 1;
                
                $('.notify-loader').hide();
                $scope.TotalUnreadAll = response.TotalUnread;
            }
            
            if(response.Data.length == 0) {
                $scope.all_scroll_busy = false;
            } else {
                setTimeout(function(){
                    $scope.all_scroll_busy = false;
                }, 100);
            }
            
            
            
        }, function(error) {
            $scope.all_scroll_busy = false;
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.isObj = function(obj) {
        if (typeof obj === 'object') {
            return true;
        } else {
            return false;
        }
    }

    $scope.NotificationRepeatDone = function() {
        setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            });
        }, 50);

    }

    $scope.getNotificationCount = function() {
        $scope.prevTotalUnread = $scope.TotalUnread;
        var requestData = {
            CountOnly: 1
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/get_notifications_count', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.notification_count = response.TotalNotificationRecords;
                $scope.message_count = response.TotalMessageRecords;
                $scope.total_count = response.TotalRecords;
                $rootScope.TotalNotificationCount = response.TotalRecords;
                $scope.TotalUnread = response.TotalUnread;
                if ($scope.TotalUnread > $scope.prevTotalUnread) {
                    chatSound();
                }
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.checkNotificationToggle = function() {
        if (!$('.notify-content').is(':visible')) {

            if ($scope.total_count <= 0 && $scope.notification.length > 0) {
                return false;
            }
            $scope.show_all_notify = 0;
            $scope.notification = new Array();
            $scope.PageNo = 1;
            $scope.type = '';
            $scope.getNotification();
        }
    }

    $scope.show_all_notification = function(val) {
        $scope.type = '';
        if (typeof val === 'undefined') {
            $scope.notification = new Array();
            $scope.PageNo = 1;
            $scope.show_all_notify = 0;
            $scope.getNotification();
        } else {
            $scope.AllNotifications = new Array();
            $scope.PageNoAll = 1;
            $scope.show_all_notifys = 0;
            $scope.getAllNotifications();
        }
    }

    $scope.updateUnseenStatus = function() {
        var requestData = {};
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/change_unseen_to_seen', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.total_count = $scope.total_count - $scope.message_count;
                $scope.message_count = 0;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }


    $scope.LoggedInUserGUID = LoggedInUserGUID;
    $scope.getThreadBody = function(data) {
        var SenderUserName = 'Someone';
        if (data.Body == '') {
            if ($scope.LoggedInUserGUID == data.SenderUserGUID) {
                SenderUserName = 'You';
            } else {
                $(data.Recipients).each(function(rk, rv) {
                    if (rv.UserGUID == data.SenderUserGUID) {
                        SenderUserName = rv.FirstName + ' ' + rv.LastName;
                    }
                })
            }
            if (data.AttachmentCount == 1) {
                return SenderUserName + ' attached 1 file';
            } else if (data.AttachmentCount > 1) {
                return SenderUserName + ' attached ' + data.AttachmentCount + ' files';
            } else {
                return data.Body;
            }
        } else {
            return data.Body;
        }
    }

    $scope.getMessages = function() {
        var PageSize = $scope.message_count;
        if (PageSize < 5) {
            PageSize = 5;
        }
        var requestData = {
            PageNo: 1,
            PageSize: PageSize
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/inbox', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.notify_thread_list = [];
                $scope.mloader = 0;

                $(response.Data).each(function(k, v) {
                    var exists = 0;
                    $($scope.notify_thread_list).each(function(kk, vv) {
                        if (vv.ThreadGUID == v.ThreadGUID) {
                            exists = 1;
                        }
                    });
                    if (exists == 0) {
                        var rcpl = v.Recipients;
                        v.ThreadSubject = $scope.getThreadSubject(v.ThreadSubject, v.EditableThread, v.Recipients);
                        v.Recipients = rcpl;
                        v.Body = $scope.getThreadBody(v);
                        $scope.notify_thread_list.push(v);
                    }
                });
                $scope.mlen = $scope.notify_thread_list.length;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.getThreadSubject = function(ThreadSubject, EditableThread, Recipients) {

        var UserGUID = LoggedInUserGUID;
        if (ThreadSubject == '' || EditableThread == 0) {
            if (Recipients.length > 0) {
                var rcp = Recipients;
                $(rcp).each(function(k, v) {
                    if (UserGUID == v.UserGUID) {
                        rcp.splice(k, 1);
                    }
                });
                var rc = rcp.length;
                if (rc > 0) {
                    if (rc == 1) {
                        ThreadSubject = rcp[0]['FirstName'] + ' ' + rcp[0]['LastName'];
                    } else if (rc == 2) {
                        ThreadSubject = rcp[0]['FirstName'] + ' and ' + rcp[1]['FirstName'];
                    } else if (rc > 2) {
                        if (rc == 3) {
                            var other = 'other';
                        } else {
                            var other = 'others';
                        }
                        ThreadSubject = rcp[0]['FirstName'] + ', ' + rcp[1]['FirstName'] + ' and ' + parseInt(rc - 2) + ' ' + other;
                    }
                    if (EditableThread == 0) {
                        ThreadSubject = rcp[0]['FirstName'] + ' ' + rcp[0]['LastName'];
                    }
                }
            }
        }
        return ThreadSubject;
    }

    $scope.trimText = function(text) {
        return trim(text);
    }

    $scope.prevent_event = function(e) {
        setTimeout(function() {
            $('.notify-icon-n-read,.notify-n-deny,.notify-n-accept').click(function(e) {
                e.stopImmediatePropagation();
            });
        }, 1000);
    }

    $scope.readNotification = function(obj, index, isBell) {
        $('.tooltip').remove();
        var requestData = {
            NotificationGUID: obj.NotificationGUID
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/mark_as_read', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                if (typeof index !== 'undefined') {
                    //console.log('a1');
                    if (typeof isBell !== 'undefined') {
                        $scope.notification[index].StatusID = '17';
                        $scope.notification[index].ShowAcceptDeny = 0;
                    } else {
                        $scope.AllNotifications[index].StatusID = '17';
                    }
                } else {
                    //console.log('b2');
                    if(obj.NotificationTypeID!=='16')
                    {
                        if (obj.IsLink == '1') {
                            window.top.location = base_url + obj.Link;
                        } else {
                            $scope.$emit("showMediaPopupEmit", obj.Link, '');
                        }
                    }
                }
                $scope.total_count--;
                $scope.notification_count--;

                $rootScope.TotalNotificationCount--;
                if ($scope.TotalUnread > 0) {
                    $scope.TotalUnread--;
                }
                if ($scope.TotalUnreadAll > 0) {
                    $scope.TotalUnreadAll--;
                }
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.markAllAsRead = function() {
        var requestData = {};
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/mark_all_notifications_as_read', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                angular.forEach($scope.AllNotifications, function(val, key) {
                    $scope.AllNotifications[key].StatusID = '17';
                })
                $('.notification-list > li').removeClass('unread');
                $('.notification-list > li .icon-n-read').remove();

                $scope.TotalUnread = 0;
                $scope.TotalUnreadAll = 0;
                $scope.show_all_notify = 0;
                $scope.show_all_notifys = 0;
                $scope.total_count = $scope.total_count - $scope.notification_count;
                $scope.notification_count = 0;
                $rootScope.TotalNotificationCount = $scope.total_count;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.UTCtoTimeZone = function(date) {
        var localTime = moment.utc(date).toDate();
        return moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
    }

    $scope.date_format = function(date, msg) {
        return GlobalService.date_format(date, msg);
    }


    $scope.UpdateReadNotification = function() {
        var requestData = {};
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/mark_as_seen', requestData, function(successResp) {
            var response = successResp.data;
            /*$scope.total_count = parseInt($scope.total_count) - parseInt($scope.notification_count);
             $scope.notification_count = 0;
             $rootScope.TotalNotificationCount = $scope.total_count;*/
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.HasMoreItem = function() {
        if ($scope.offset % 5 != 0 || $scope.offset == 0) {
            $scope.show_more_visible = false;
        } else {
            $scope.show_more_visible = true;
        }
    }

    $scope.LoadMoreNotification = function() {
        var PageNo = Math.ceil($scope.notification.length / $scope.NPS) + 1;
        if ($scope.notification_count == $scope.notification) {
            $scope.show_more_visible = false;
            return;
        }
        var requestData = {
            "PageNo": PageNo
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/get_notifications_count', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $(response.Data).each(function(k, v) {
                    $scope.notification.push(response.Data[k]);
                });
                $scope.notification_count = response.TotalRecords;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.all_notifications = '';
    $scope.module_settings = [];
    $scope.notification_settings = [];
    $scope.enabledModules = {md29 : 0};
    $scope.getNotificationSettings = function() {
        var requestData = {};
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/get_user_notification_settings', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.all_notifications = response.Data.AllNotifications;
                $scope.module_settings = response.Data.Modules;
                $scope.notification_settings = response.Data.Notifications; 
                
                angular.forEach($scope.notification_settings, function(stt){                                        
                    for(var index in $scope.module_settings) {
                        if($scope.module_settings[index].ModuleID == stt.ModuleID) {
                            $scope.module_settings[index].ModuleIDEnabled = 1;                            
                        }
                    }                    
                });

                $scope.email_notifications = response.Data.EmailNotifications;
                $scope.mobile_notifications = response.Data.MobileNotifications;
                $scope.checkOnload();
            } else {
                showResponseMessage(response.Message, 'alert-danger');
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.updateNotificationValue = function(Key, Value, Type) {
        // Do some action
    }

    $scope.setNotificationSettings = function() {

        var reqData = {
            AllNotifications: $scope.all_notifications,
            EmailNotifications: $scope.email_notifications,
            MobileNotifications: $scope.mobile_notifications,
            Modules: $scope.module_settings,
            Notifications: $scope.notification_settings
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'notifications/set_user_notification_settings', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.getNotificationSettings();
                showResponseMessage(response.Message, 'alert-success');
            } else {
                showResponseMessage(response.Message, 'alert-danger');
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.confirmAllNotificationOff = function() {
        if (!$scope.all_notifications) {
            showConfirmBoxNotifications("Turn off all notifications", "Are you sure you want to turn off all notifications ?", function(e) {
                if (!e) {
                    $scope.all_notifications = true;
                    $scope.$apply();
                }
            });
        }
    }

    $scope.setNotificationOptions = function() {

    }

    $scope.checkNotificationValue = function(value) {
        console.log(value);
    }

    $scope.checkAllCheckbox = function(entity) {
        angular.forEach($scope.notification_settings, function(value, key) {
            if (entity == 'email') {
                if (!$('#CommentedE-' + value.NotificationTypeKey).is(':disabled')) {
                    if ($scope.emailCheckAll) {
                        $scope.notification_settings[key].Email = true;
                    } else {
                        $scope.notification_settings[key].Email = false;
                    }
                }
            }
            if (entity == 'mobile') {
                if (!$('#CommentedM-' + value.NotificationTypeKey).is(':disabled')) {
                    if ($scope.mobileCheckAll) {
                        $scope.notification_settings[key].Mobile = true;
                    } else {
                        $scope.notification_settings[key].Mobile = false;
                    }
                }
            }
        });
    }

    $scope.checkOnload = function() {
        $scope.emailCheckAll = true;
        $scope.mobileCheckAll = true;
        angular.forEach($scope.notification_settings, function(val, key) {
            if (!$scope.notification_settings[key].Mobile) {
                $scope.mobileCheckAll = false;
            }
            if (!$scope.notification_settings[key].Email) {
                $scope.emailCheckAll = false;
            }
        });
    }

    $scope.denyRequestNote = function(obj, index, isBell) {
        $('.tooltip').remove();
        if (typeof isBell !== 'undefined') {
            friendid = obj['P1'][0].ModuleEntityGUID;
        } else {
            friendid = obj.UserGUID;
        }
        if ($('#UserListCtrl').length > 0) {
            angular.element(document.getElementById('UserListCtrl')).scope().denyRequest(friendid, 1);
            $scope.readNotification(obj, index, isBell);
        } else {
            var reqData = { FriendGUID: friendid }
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/denyFriend', reqData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($('.accept-' + friendid).length > 0) {
                        $('.accept-' + friendid).hide();
                    }
                    $scope.readNotification(obj, index, isBell);
                    showResponseMessage(response.Message, 'alert-success');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    }

    $scope.acceptRequestNote = function(obj, index, isBell) {
        $('.tooltip').remove();
        if (typeof isBell !== 'undefined') {
            friendid = obj['P1'][0].ModuleEntityGUID;
        } else {
            friendid = obj.UserGUID;
        }
        if ($('#UserListCtrl').length > 0) {
            angular.element(document.getElementById('UserListCtrl')).scope().acceptRequest(friendid, 1);
            $scope.readNotification(obj, index, isBell);
        } else {
            var reqData = { FriendGUID: friendid }
            url = 'friends/acceptFriend';
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/acceptFriend', reqData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($('.accept-' + friendid).length > 0) {
                        $('.accept-' + friendid).hide();
                    }
                    $scope.readNotification(obj, index, isBell);
                    showResponseMessage(response.Message, 'alert-success');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    }

    // Notification Settings Ends
    $(document).ready(function() {

        $(window).scroll(function() {
            if ($scope.PageNoAll > 0 && $('.notification-wrapper').is(':visible')) {
                var pScroll = $(window).scrollTop();
                var pageBottomScroll1 = parseInt($(document).height()) - parseInt($(window).height()) - 400;
                if (pScroll >= pageBottomScroll1) {
                    setTimeout(function() {
                        //console.log("pScroll = "+pScroll+" pageBottomScroll1 = "+pageBottomScroll1+" all_scroll_busy = "+$scope.all_scroll_busy);
                        if (pScroll >= pageBottomScroll1 && !$scope.all_scroll_busy) {
                            $('.notify-loader').show();
                            $scope.getAllNotifications();
                        }
                    }, 500);
                }
            }
        });

        $("#notifyscroll").mCustomScrollbar({
            callbacks: {
                onTotalScroll: function() {
                    if ($scope.PageNo > 0) {
                        setTimeout(function() {
                            if (!$scope.scroll_busy) {
                                $scope.nloader = 1;
                                $scope.getNotification();
                            }
                        }, 100);
                    }
                },
                onTotalScrollOffset: 500
            }
        });
    });

    $scope.notificationRepeatDone = function() {
        //console.log('wallRepeatDone');
        
        
        
        setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            });
        }, 600);
    }
}]);

window.onbeforeunload = function(e) {
    $('#NotificationPageNo').val(1);
};


function acceptFriendRequest(UserGUID) {
    angular.element(document.getElementById('UserListCtrl')).scope().acceptRequest(UserGUID, 1);
}

function denyFriendRequest(UserGUID) {
    angular.element(document.getElementById('UserListCtrl')).scope().denyRequest(UserGUID, 1);
}

function chatSound() {
    $('#chatAudio')[0].play();
}
