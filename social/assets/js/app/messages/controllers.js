app.controller('messageSectionCtrl', ['GlobalService', '$compile', '$scope', '$window', '$sce', '$q', 'setFormatDate', '$timeout', '$controller', 'appInfo', 'WallService', function(GlobalService, $compile, $scope, $window, $sce, $q, setFormatDate, $timeout, $controller, appInfo, WallService) {
    $scope.image_server_path = image_server_path;
    $scope.mnum = 1;
    $scope.nomsg = 'No conversations in the Trash.';
    $scope.LoggedInUserGUID = LoggedInUserGUID;
    // layout Height Start : Start
    var w = angular.element($window);
    $scope.getHeight = function() {
        return w.height()
    };
    if ($('#module_id').length == 0 || $('#module_id').val() !== '3') {
        $scope.$watch($scope.getHeight, function(newValue, oldValue) {
            $scope.mszListStyle = function() {
                var mszListHeight;
                if (newValue > 700) {
                    mszListHeight = { height: (newValue - mszlist - pagesBlock) + 'px' };
                    //console.log(mszListHeight);
                } else {
                    mszListHeight = { height: 300 };
                }
                return mszListHeight;
            };
            $scope.mszBoxStyle = function() {
                var mszBoxHeight;
                if (newValue > 700) {
                    mszBoxHeight = { height: (newValue - mszBox - mszComposer) + 'px' };
                } else {
                    mszBoxHeight = { height: 344 };
                }
                return mszBoxHeight;
            };
        });

        w.bind('resize', function() {
            $scope.$apply();
        });
    }
    // layout Height Start : End

    // Date Formate
    $scope.getDateFormate = function(date, msg) {
            return setFormatDate.getTime(date, msg)
        }
        // is read flag check
    $scope.isReadFn = function(isReadFlag) {
        if (isReadFlag > 1) {
            return 1
        } else {
            return 2
        }
    }

    $scope.ProfileName = '';
    /*$scope.getUserDetails = function(){
            setTimeout(function(){
                $scope.ProfileName = $scope.getUserName();
            },2000);
}*/

    $scope.MessageTxt = '';
    $scope.submitMessage = function() {
        var Recipients = [];
        var Media = [];
        Recipients.push({ 'UserGUID': $('#module_entity_guid').val() });
        var reqData = { ModuleID: '', ModuleEntityGUID: '', Subject: '', Body: $scope.MessageTxt, Media: Media, Recipients: Recipients, Replyable: '1' };

        WallService.CallPostApi(appInfo.serviceUrl + 'messages/compose', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.MessageTxt = '';
                $('#newMsg').modal('toggle');
                showResponseMessage('Message has been sent successfully.', 'alert-success');
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.MessageTxtViaCard = '';
    $scope.submitMessageViaCard = function() {
        var Recipients = [];
        var Media = [];
        Recipients.push({ 'UserGUID': $('#ToMssgFrmCardGUID').val() });
        var reqData = { ModuleID: '', ModuleEntityGUID: '', Subject: '', Body: $scope.MessageTxtViaCard, Media: Media, Recipients: Recipients, Replyable: '1' };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/compose', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.MessageTxt = '';
                $('#MsgFromCard').modal('toggle');
                showResponseMessage('Message has been sent successfully.', 'alert-success');
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }
    $('#MsgFromCard').on('show.bs.modal', function(e) {
        $('#textareaIDCardMsg').val('');
        $('#noOfChartextareaIDCardMsg').html(200);
        $('#MsgFromCard .error-block-overlay').hide();
        $('#MsgFromCard .form-group').removeClass('hasError');
    })

    // 
    $scope.isTrashFn = function(isTrashFlag) {
        return 12
    }
    $scope.searchKey = 0;
    // Trigger get message on search
    var timeoutClear;
    $scope.getMessageSearch = function() {
        var Type = 'Inbox';
        if ($('.titleTrash').hasClass('active')) {
            Type = 'Trash';
        }
        $('#PageNo').val(1);
        if (timeoutClear)
            clearTimeout(timeoutClear);
        timeoutClear = setTimeout(function() {
            $scope.MszList = [];
            $scope.getMessage(Type, '192');
        }, 500);
    }

    $scope.MszList = []

    // Get Message
    $scope.getMessage = function(type, dn) {
        if (type == 'InboxF') {
            $('#PageNo').val(1);
            $scope.MszList = [];
            type = 'Inbox';
        } else if (type == 'TrashF') {
            $('#PageNo').val(1);
            $scope.MszList = [];
            type = 'Trash';
            // Clear message details
            $scope.isNewMessage = true;
            $scope.messageDetails = new Array();
            $('.msgbody').val('');
        }
        $scope.nomsg = 'No conversations in the ' + type + '.';
        $('.messages-title').removeClass('active');
        $('.title' + type).addClass('active');

        var reqData = {
            "Type": type,
            "PageSize": "10",
            "PageNo": $('#PageNo').val(),
            "SearchKey": $scope.searchKey
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/getMessages', reqData, function(successResp) {
            var response = successResp.data;
            $('#PageNo').val(parseInt($('#PageNo').val()) + 1);
            var data = response.Data
            for (i in data) {
                var obj = {
                    'CreatedDate': data[i].CreatedDate,
                    'MessageGUID': data[i].MessageGUID,
                    'ModifiedDate': data[i].ModifiedDate,
                    'CreatedDate': data[i].CreatedDate,
                    'Subject': data[i].Subject,
                    'UnreadCount': data[i].UnreadCount,
                    'Status': data[i].Status,
                    'Users': []
                }
                $($scope.MszList).each(function(k, v) {
                    if ($scope.MszList[k].MessageGUID == data[i].MessageGUID) {
                        $scope.searchKeyFlag = 1;
                    }
                });
                if ($scope.searchKeyFlag !== 1) {
                    $scope.MszList.push(obj);
                }
                $scope.searchKeyFlag = 0;
                for (ii in data[i].Users) {
                    if (data[i].Users[ii].ProfilePicture == AssetBaseUrl + 'img/profiles/user_default.jpg') {
                        data[i].Users[ii].ProfilePicture = AssetBaseUrl + 'img/profiles/user_default.jpg'
                    } else {
                        var profilePicture = data[i].Users[ii].ProfilePicture.split('/')
                        profilePictureUpdate = dn + 'x' + dn + '/' + profilePicture[profilePicture.length - 1]
                        profilePicture[profilePicture.length - 1] = profilePictureUpdate
                        profilePicture = profilePicture.join('/')
                        data[i].Users[ii].ProfilePicture = image_server_path + 'upload/profile/' + profilePicture
                    }
                    obj.Users.push(data[i].Users[ii]);
                }
            }
            $scope.mnum = $scope.MszList.length;
            setTimeout(function() {
                $('.msg-loadr').hide();
            }, 500);
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    $scope.UTCtoTimeZone = function(date) {
        var localTime = moment.utc(date).toDate();
        return moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
    }

    $scope.date_format = function(date, msg) {
        return GlobalService.date_format(date, msg);
    }


    $scope.resetMessageDetails = function(guid, dn) {
        $scope.messageDetails = new Array();
        $('#MessagePageNo').val(1);
        $scope.getMessageDetails(guid, dn);
    }

    $scope.messageDetails = new Array();
    // Get Message details 
    $scope.LastMessageScrollGUID = '';
    $scope.getMessageDetails = function(guid, dn) {
        $scope.LastMessageScrollGUID = guid;
        $scope.isNewMessage = false;
        $('.message-form-group').hide();
        $scope.UsersName = '';
        var messages = new Array();
        var reqData = {
            "MessageGUID": guid,
            PageNo: $('#MessagePageNo').val()
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/messageDetails', reqData, function(successResp) {
            var res = successResp.data;
            if (res.ResponseCode == 200) {
                var i = 1;
                $('#MessagePageNo').val(parseInt($('#MessagePageNo').val()) + 1);
                $(res.Data.Messages).each(function(k, v) {
                    var arr = {};
                    arr['Body'] = v.Body;
                    arr['ModifiedDate'] = v.ModifiedDate;
                    arr['CreatedDate'] = v.CreatedDate;
                    arr['UserGUID'] = v.UserGUID;
                    arr['MessageGUID'] = v.MessageGUID;
                    var j = 1;
                    $(res.Data.Users).each(function(key, val) {
                        if (i == 1) {
                            //if(j>1){
                            if (res.Data.Users.length > 2) {
                                if (res.Data.Users.length == j + 1) {
                                    $scope.UsersName += val.FirstName + ' & ';
                                } else {
                                    $scope.UsersName += val.FirstName + ', ';
                                }
                            } else {
                                if (res.Data.Users.length == j + 1) {
                                    $scope.UsersName += val.FirstName + ' ' + val.LastName + ' & ';
                                } else {
                                    $scope.UsersName += val.FirstName + ' ' + val.LastName + ', ';
                                }
                            }
                            //}
                            j++;
                        }
                        if (val.UserGUID == v.UserGUID) {
                            arr['FirstName'] = val.FirstName;
                            arr['LastName'] = val.LastName;
                            arr['ProfileURL'] = $scope.SiteURL + val.ProfileURL;
                            // arr['ProfilePicture'] = val.ProfilePicture;
                            if (val.ProfilePicture == AssetBaseUrl + 'img/profiles/user_default.jpg') {
                                arr['ProfilePicture'] = val.ProfilePicture;
                            } else {
                                var profilePicture = val.ProfilePicture.split('/')
                                profilePictureUpdate = dn + 'x' + dn + '/' + profilePicture[profilePicture.length - 1]
                                profilePicture[profilePicture.length - 1] = profilePictureUpdate
                                profilePicture = profilePicture.join('/');
                                arr['ProfilePicture'] = image_server_path + 'upload/profile/' + profilePicture;
                            }
                        }
                        if ($('#MessagePageNo').val() == 2) {
                            setTimeout(function() {
                                var scrollHeight = $('.msg-lisitings')[0].scrollHeight;
                                $('.mCustomScrollbar-right').mCustomScrollbar("scrollTo", 'bottom');
                            }, 1000);
                        }
                    });
                    i++;
                    $scope.messageDetails.unshift(arr);
                });
                $scope.changeFlagStatus(guid, 1);
                $scope.UsersName = $scope.UsersName.substr(0, $scope.UsersName.length - 2);
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
        //$scope.messageDetails = messages;
    }

    // Move to Trash
    $scope.moveTrash = function(guid, trashStatus) {
        console.log(trashStatus);
        var alertTitle = "Delete Message";
        if ($('.titleTrash').hasClass('active')) {
            var alertMessage = "Are you sure you want to delete this message permanently ?";
        } else {
            var alertMessage = "Are you sure you want to delete this message ?";
        }
        var successMessage = "Message deleted successfully.";
        if (trashStatus == 9) {
            alertTitle = "Move to Inbox";
            alertMessage = "Are you sure you want to move this message to Inbox again ?";
            successMessage = "Message moved to inbox successfully.";
        }


        showConfirmBox('Delete Message', alertMessage, function(e) {
            if (e) {

                var reqData = {
                    "MessageGUID": guid,
                    "MessageReceiverGUID": "",
                    "Status": trashStatus
                };
                WallService.CallPostApi(appInfo.serviceUrl + 'messages/changeMessageStatus', reqData, function(successResp) {
                    var response = successResp.data;
                    $($scope.MszList).each(function(k, v) {
                        if (guid == $scope.MszList[k]['MessageGUID']) {
                            $scope.MszList.splice(k, 1);
                            if ($('.titleTrash').hasClass('active')) {
                                $scope.getMessage('Trash', '192');
                            } else {
                                $scope.getMessage('Inbox', '192');
                            }
                            return false;
                        }
                    });
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
                $scope.newMessageCompose();
                showResponseMessage(successMessage, 'alert-success');
            }
        });
    }

    // Change Flag Status
    $scope.changeFlagStatus = function(guid, flagstatus) {
        if (flagstatus > 0) {
            flagstatus = 1;
        } else {
            flagstatus = 2;
        }
        console.log(flagstatus);
        var reqData = {
            "MessageGUID": guid,
            "MessageReceiverGUID": "",
            "FlagStatus": flagstatus
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/changeMessageFlagStatus', reqData, function(successResp) {
            var response = successResp.data;
            $($scope.MszList).each(function(k, v) {
                if (guid == $scope.MszList[k]['MessageGUID']) {
                    $scope.MszList[k].UnreadCount = res.Data.UnreadCount;
                }
            });
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    // Send Message
    $scope.sendMessage = function(Subject, Body, PreviousMessageGUID) {
        $('#selectedUsers').val('');
        $timeout(function() {
            autoCompleteUser();
        }, 500);

        var Receivers = new Array();
        var dn = '192';
        if (PreviousMessageGUID == '') {
            $('.add-user-id').each(function(k) {
                var userid = $('.add-user-id:eq(' + k + ')').attr('id');
                //userid = userid.split('-')[1];
                userid = userid.split('user-')[1];
                Receivers.push(userid);
            });
        }

        if (PreviousMessageGUID == '') {
            PreviousMessageGUID = '0';
        }

        var reqData = {
            "Subject": Subject,
            "Body": Body,
            "PreviousMessageGUID": PreviousMessageGUID,
            "Receivers": Receivers
        };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/sendMessage', reqData, function(successResp) {
            var res = successResp.data;
            if (res.ResponseCode == 201) {
                $('#PageNo').val(1);
                $scope.MszList = [];
                if (res.Data.ProfilePicture == AssetBaseUrl + 'img/profiles/user_default.jpg') {
                    res.Data.ProfilePicture = res.Data.ProfilePicture;
                } else {
                    var profilePicture = res.Data.ProfilePicture.split('/')
                    profilePictureUpdate = dn + 'x' + dn + '/' + profilePicture[profilePicture.length - 1]
                    profilePicture[profilePicture.length - 1] = profilePictureUpdate
                    profilePicture = profilePicture.join('/');
                    res.Data.ProfilePicture = image_server_path + 'upload/profile/' + profilePicture;
                }
                res.Data.FirstName = 'You';
                res.Data.LastName = '';
                $scope.messageDetails.push(res.Data);
                $('.msgbody').val('');
                $timeout(function() {
                    $('.mCustomScrollbar-right').mCustomScrollbar("scrollTo", 'last');
                    $scope.getMessage('Inbox', '192');
                    if ($scope.isNewMessage == true) {
                        $scope.getMessageDetails(res.Data.MessageGUID, '192');
                    }
                }, 500);
            }
            if (res.ResponseCode == 412) {
                showResponseMessage(res.Message, 'alert-danger');
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    // Change isNewMessage
    $scope.changeIsNewMessage = function(status) {
        if (status == 1) {
            $scope.isNewMessage = true;
            if ($('.add-users').html() == '') {
                $('.add-users').html('No Selection');
            }
        } else {
            $scope.isNewMessage = false;
        }
    }

    // New Message Compose
    $scope.newMessageCompose = function() {
        $scope.isNewMessage = true;
        $scope.messageDetails = new Array();
        $('.msgbody').val('');
        $('.message-form-group').show();
        $('.add-users').html('No Selection');
        $scope.getMessage('InboxF', '192');
    }

    $scope.newMessageCompose2 = function() {
        $scope.isNewMessage = true;
        $scope.messageDetails = new Array();
        $('.msgbody').val('');
        $('.message-form-group').show();
        console.log($scope.isNewMessage);
    }

    $scope.parseYoutubeVideo = function(url) {
        var videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
        if (videoid != null) {
            return videoid[1];
        } else {
            return false;
        }
    }

    $scope.textToLink = function(inputText) {
        if (inputText !== undefined) {
            var replacedText, replacePattern1, replacePattern2, replacePattern3;

            replacedText = inputText.replace("<br>", " ||| ");

            /*replacedText = replacedText.replace(/</g,'&lt');
            replacedText = replacedText.replace(/>/g,'&gt');
            replacedText = replacedText.replace(/&ltbr&gt/g,'<br>');
            replacedText = replacedText.replace(/lt&lt/g,'<');
            replacedText = replacedText.replace(/gt&gt/g,'>');*/

            //URLs starting with http://, https://, or ftp://
            replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            replacedText = replacedText.replace(replacePattern1, function($1) {
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
                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
                } else {
                    return '<a href="' + href + '" title="' + href + '" class="chat-anchor" target="_blank">' + link + '</a>';
                }
            });

            //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
            replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
            replacedText = replacedText.replace(replacePattern2, function($1, $2) {
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
                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
                } else {
                    return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                }
            });

            //Change email addresses to mailto:: links.
            replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
            replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');
            return replacedText;
        } else {
            return '';
        }
    }

    $(document).ready(function() {
        $('.defaultScroller').scroll(function() {
            // /console.log($('.defaultScroller').scrollTop());
            if (parseInt($('.defaultScroller ul').height()) - parseInt($('.defaultScroller').scrollTop()) == '299') {
                var type = 'Inbox';
                if ($('.titleTrash').hasClass('active')) {
                    type = 'Trash';
                }
                $('.msg-loadr').show();
                $scope.getMessage(type, '192');
            }
        });

        $('.m-conversation-block').scroll(function() {
            var scrollTop = $('.m-conversation-block').scrollTop();
            if (scrollTop == 0) {
                $scope.message_details($scope.Messages.ThreadGUID);
            }
        });

        $('.m-left-scroll').scroll(function() {
            var scrollTop = $('.m-left-scroll').scrollTop();
            var outerHeight = $('.m-left-scroll ul').outerHeight();
            if (outerHeight - scrollTop == 438) {
                $scope.get_threads();
            }
        });
    });

    $scope.PageJustLoaded = 1;

    $scope.Filter = '';
    $scope.thread_list = [];
    $scope.Messages = [];
    $scope.MessageList = [];
    $scope.ShowSettings = 0;
    
    $scope.TotalThreadRecords = 0;
    
    $scope.ComposingMessage = 1;
    $scope.get_threads = function() {
        var PageNo = $('#LeftPageNo').val();
        if (PageNo > 1) {
            var threadOffset = parseInt(PageNo - 1) * 10;
            if ($scope.TotalThreadRecords <= threadOffset) {
                return false;
            }
        }
        var PageSize = 10;
        if (IsNewsFeed == 1) {
            PageSize = 3;
        }
        $('.m-left-loader').show();
        var reqData = { ModuleID: '', ModuleEntityGUID: '', SearchKeyword: $scope.SearchKeyword, PageNo: PageNo, PageSize: PageSize, Filter: $scope.Filter };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/inbox', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                if (response.TotalRecords == 0) {
                    $scope.compose_new_message();
                }
                $scope.TotalThreadRecords = response.TotalRecords;
                $('#LeftPageNo').val(parseInt($('#LeftPageNo').val()) + 1);
                $(response.Data).each(function(k, v) {
                    var exists = 0;
                    $($scope.thread_list).each(function(kk, vv) {
                        if (vv.ThreadGUID == v.ThreadGUID) {
                            exists = 1;
                        }
                    });
                    if (exists == 0) {
                        var rcpl = v.Recipients;
                        v.ThreadSubject = $scope.getThreadSubject(v.ThreadSubject, v.EditableThread, v.Recipients, 'left');
                        v.Recipients = rcpl;
                        v.Body = $scope.getThreadBody(v);
                        $scope.thread_list.push(v);
                    }
                });

                if (PageNo == 1) {
                    if ($scope.thread_list.length > 0 && $scope.PageJustLoaded == 1) {
                        $scope.PageJustLoaded = 0;
                        setTimeout(function() {
                            if (MsgType == '' || MsgGUID == '') {
                                $scope.get_new_thread_details($scope.thread_list[0].ThreadGUID);
                            } else {
                                if (MsgType == 'thread') {
                                    $scope.get_new_thread_details(MsgGUID);
                                }
                            }
                        }, 1000);
                    } else {
                        setTimeout(function() {
                            $('.one-to-one').removeClass('one-to-one');
                            $('#thread-' + $scope.Messages.ThreadGUID).addClass('one-to-one');
                        }, 1000);
                    }
                }
            }
            $('.m-left-loader').hide();
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.compmsg = function(val) {
        $scope.ComposingMessage = val;
    }

    $scope.getThreadBody = function(data) {
        SenderUserName = 'Someone';
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

    $scope.getMsgBodyHTML = function(Body, flag) {
        if (typeof Body == 'undefined') {
            return '';
        }

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

            var replacedText = Body;

            //URLs starting with http://, https://, or ftp://
            replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            replacedText = replacedText.replace(replacePattern1, function($1) {
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
                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
                } else {
                    return '<a href="' + href + '" title="' + href + '" class="chat-anchor" target="_blank">' + link + '</a>';
                }
            });

            //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
            replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
            replacedText = replacedText.replace(replacePattern2, function($1, $2) {
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
                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
                } else {
                    return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                }
            });

            //Change email addresses to mailto:: links.
            replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
            replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

            Body = replacedText;
        }
        Body = $scope.to_trusted(Body);
        return Body;
    }

    $scope.get_new_thread_details = function(ThreadGUID) {
        if ($('.msgbody').val() !== '') {
            showFBLikeConfirmBox('Don\'t Send Message?', 'If you leave this Page, your message won\'t be sent.', function(e) {
                if (e) {
                    return false;
                } else {
                    $('.msgbody').val('');
                    $scope.Messages = [];
                    $scope.MessageList = [];
                    $scope.ShowSettings = 1;
                    $('.one-to-one').removeClass('one-to-one');
                    $('#thread-' + ThreadGUID).addClass('one-to-one');
                    $scope.get_thread_details(ThreadGUID);
                }
            });
        } else {
            $('.msgbody').val('');
            $scope.Messages = [];
            $scope.MessageList = [];
            $scope.ShowSettings = 1;
            $('.one-to-one').removeClass('one-to-one');
            $('#thread-' + ThreadGUID).addClass('one-to-one');
            $scope.get_thread_details(ThreadGUID);
        }
    }

    $scope.add_people = function(ThreadGUID) {
        if ($scope.tags.length > 0) {
            var Recipients = [];
            $($scope.tags).each(function(k, v) {
                Recipients.push({ 'UserGUID': v.UserGUID });
            });
            var reqData = { ThreadGUID: ThreadGUID, Recipients: Recipients };
            WallService.CallPostApi(appInfo.serviceUrl + 'messages/add_participant', reqData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $($scope.thread_list).each(function(key, val) {
                        if (val.ThreadGUID == ThreadGUID) {
                            $scope.thread_list[key].Recipients = response.Data;
                        }
                    });
                    $scope.get_thread_details(ThreadGUID, '1');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $('#addPeople').modal('toggle');
        } else {
            showResponseMessage('Please add at least 1 recipient.', 'alert-danger');
        }
    }

    $scope.check_date_grouping = function(OldMsgDate, NewMsgDate) {
        if (typeof OldMsgDate !== 'undefined') {
            if (OldMsgDate == NewMsgDate) {
                return false;
            }
        }
    }

    $scope.getPlaceHolder = function() {
        if ($scope.tags.length == 0) {
            if ($('#sendMszto ul.tag-list').hasClass('hide-placeholder')) {
                $('#sendMszto ul.tag-list').removeClass('hide-placeholder');
                $('#sendMszto ul.tag-list').next('input').attr('placeholder', 'Name');
            }
        } else {
            if (!$('#sendMszto ul.tag-list').hasClass('hide-placeholder')) {
                $('#sendMszto ul.tag-list').addClass('hide-placeholder');
                $('#sendMszto ul.tag-list').next('input').removeAttr('placeholder')
            }
        }
    }

    $scope.message_details = function(ThreadGUID) {
        $scope.prevDate = '';
        var id = $('.m-conversation-list li:eq(0)').attr('id');
        var PageNo = $('#RightPageNo').val();
        if (typeof ThreadGUID === 'undefined') {
            return false;
        }
        var MsgOffset = parseInt(PageNo - 1) * 10;
        if ($scope.TotalThreadMessages <= MsgOffset) {
            return false;
        }
        $('.m-conversation-loader').show();
        var reqData = { ThreadGUID: ThreadGUID, PageNo: PageNo, PageSize: 10 };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/message_details', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                if (response.Data.Messages.length > 0) {
                    setTimeout(function() {
                        $('.mCustomScrollbar-right').mCustomScrollbar("scrollTo", "#" + id);
                    }, 500);
                }
                $('#RightPageNo').val(parseInt(PageNo) + 1);
                var new_messages = response.Data.Messages;
                $(new_messages).each(function(k, v) {
                    if (v.Type == 'AUTO') {
                        v.Body = '';
                        switch (v.ActionName) {
                            case 'ADDED':
                                v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                v.Body += ' added ';
                                if (v.ActionValue.length == 1) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a>';
                                } else if (v.ActionValue.length == 2) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and <a href="' + site_url + v.ActionValue[1].ProfileURL + '">' + v.ActionValue[1].FirstName + ' ' + v.ActionValue[1].LastName + '</a>';
                                } else if (v.ActionValue.length > 2) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and ';
                                    var tooltiptitle = '<tooltip>';
                                    for (var i = 1; i <= v.ActionValue.length - 1; i++) {
                                        tooltiptitle += '<div>' + v.ActionValue[i].FirstName + ' ' + v.ActionValue[i].LastName + '</div>';
                                    }
                                    tooltiptitle += '</tooltip>';
                                    v.Body += '<a data-toggle="tooltip" href="javascript:void(0);" title="' + tooltiptitle + '">' + (parseInt(v.ActionValue.length) - 1) + ' more</a>';
                                }
                                break;
                            case 'REMOVED':
                                v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                v.Body += ' removed ';
                                if (v.ActionValue.length == 1) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a>';
                                } else if (v.ActionValue.length == 2) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and <a href="' + site_url + v.ActionValue[1].ProfileURL + '">' + v.ActionValue[1].FirstName + ' ' + v.ActionValue[1].LastName + '</a>';
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and ';
                                    var tooltiptitle = '<tooltip>';
                                    for (var i = 1; i <= v.ActionValue.length - 1; i++) {
                                        tooltiptitle += '<div>' + v.ActionValue[i].FirstName + ' ' + v.ActionValue[i].LastName + '</div>';
                                    }
                                    tooltiptitle += '</tooltip>';
                                    v.Body += '<a data-toggle="tooltip" href="javascript:void(0);" title="' + tooltiptitle + '">' + (parseInt(v.ActionValue.length) - 1) + ' more</a>';
                                }
                                break;
                            case 'THREAD_CREATED':
                                v.Body += 'Conversation started ' + v.ActionValue;
                                break;
                            case 'CONVERSATION_NAME':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' named the conversation: ';
                                v.Body += v.ActionValue;
                                break;
                            case 'CONVERSATION_NAME_REMOVED':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' removed the conversation name.';
                                break;
                            case 'CONVERSATION_DATE':
                                v.Body = v.ActionValue;
                                break;
                            case 'CONVERSATION_IMAGE':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' changed the conversation picture : <br/>';
                                v.Body += '<ul id="lg-' + v.MessageGUID + '" class="m-msz-attached"><li data-ng-src="' + image_server_path + 'upload/messages/' + v.ActionValue + '" class="attached-list"><img src="' + image_server_path + 'upload/messages/220x220/' + v.ActionValue + '" /></li></ul>';
                                setTimeout(function() {
                                    $scope.callLightGallery(v.MessageGUID);
                                }, 1000);
                                break;
                        }
                    } else {
                        if (v.Media.length > 0) {
                            $(v.Media).each(function(mk, mv) {
                                if (mv.MediaType == 'Video') {
                                    v.Media[mk].ImageName = $scope.getVideoUrl(mv.ImageName);
                                }
                            });
                        }
                    }
                })
                $($scope.MessageList).each(function(k, v) {
                    var exists = 0;
                    $(new_messages).each(function(kk, vv) {
                        if (vv.MessageGUID == v.MessageGUID) {
                            exists = 1;
                        }
                    });
                    if (exists == 0) {
                        new_messages.push(v);
                    }
                });
                $scope.MessageList = new_messages;

                $($scope.MessageList).each(function(mk, mv) {
                    var t = mv.CreatedDate.split(/[- :]/);
                    var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                    date = moment(date).format('DD MMM YYYY');
                    if (date == moment().format('DD MMM YYYY')) {
                        date = 'Today';
                    }
                    if ($scope.prevDate !== date) {
                        if ($scope.prevDate == '') {
                            $scope.prevDate = date;
                            $scope.MessageList[mk]['NewDate'] = '';
                        } else {
                            $scope.prevDate = date;
                            $scope.MessageList[mk]['NewDate'] = $scope.prevDate;
                        }
                    } else {
                        $scope.MessageList[mk]['NewDate'] = '';
                    }
                });

                $timeout(function() {
                    $('[data-toggle="tooltip"]').tooltip({
                        container: 'body',
                        html: true
                    });
                    $('.m-conversation-loader').hide();
                    //$scope.callLightGallery();                       
                }, 500); // wait...
            }
        });
    }

    $scope.to_trusted = function(html) {
        return $sce.trustAsHtml(html);
    }

    $scope.getVideoUrl = function(imagename) {
        console.log(imagename);
        var ext = imagename.substr(imagename.lastIndexOf('.') + 1);
        imagename = imagename.slice(0, parseInt(ext.length) * -1);
        return imagename;
    }

    $scope.TotalThreadMessages = 0;

    $scope.get_thread_details = function(ThreadGUID, AppendLast) {
        $scope.ComposingMessage = 0;
        $scope.MsgBody = '';
        $('.msgbody').val('');
        resetAttachements();
        $('#RightPageNo').val(2);
        $scope.prevDate = '';
        $('.m-conversation-loader').show();
        var reqData = { ThreadGUID: ThreadGUID };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/thread_details', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.Messages = response.Data;
                $scope.TotalThreadMessages = response.TotalMessages;
                $scope.EditSubject = $scope.Messages.ThreadSubject;
                $scope.Messages.ThreadSubject = $scope.getThreadSubject($scope.Messages.ThreadSubject, $scope.Messages.EditableThread, $scope.Messages.Recipients, 'Right');
                if ( response.Data.ThreadImageName ) {
                  $scope.threadImage = { ImageServerPath : image_server_path + 'upload/messages', ImageName : response.Data.ThreadImageName };
                }
                $(response.Data.Messages).each(function(k, v) {
                    if (v.Type == 'AUTO') {
                        v.Body = '';
                        switch (v.ActionName) {
                            case 'ADDED':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' added ';
                                if (v.ActionValue.length == 1) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a>';
                                } else if (v.ActionValue.length == 2) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and <a href="' + site_url + v.ActionValue[1].ProfileURL + '">' + v.ActionValue[1].FirstName + ' ' + v.ActionValue[1].LastName + '</a>';
                                } else if (v.ActionValue.length > 2) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and ';
                                    var tooltiptitle = '<tooltip>';
                                    for (var i = 1; i <= v.ActionValue.length - 1; i++) {
                                        tooltiptitle += '<div>' + v.ActionValue[i].FirstName + ' ' + v.ActionValue[i].LastName + '</div>';
                                    }
                                    tooltiptitle += '</tooltip>';
                                    v.Body += '<a data-toggle="tooltip" href="javascript:void(0);" title="' + tooltiptitle + '">' + (parseInt(v.ActionValue.length) - 1) + ' more</a>';
                                }
                                break;
                            case 'REMOVED':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' removed ';
                                if (v.ActionValue.length == 1) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a>';
                                } else if (v.ActionValue.length == 2) {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and <a href="' + site_url + v.ActionValue[1].ProfileURL + '">' + v.ActionValue[1].FirstName + ' ' + v.ActionValue[1].LastName + '</a>';
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionValue[0].ProfileURL + '">' + v.ActionValue[0].FirstName + ' ' + v.ActionValue[0].LastName + '</a> and ';
                                    var tooltiptitle = '<tooltip>';
                                    for (var i = 1; i <= v.ActionValue.length - 1; i++) {
                                        tooltiptitle += '<div>' + v.ActionValue[i].FirstName + ' ' + v.ActionValue[i].LastName + '</div>';
                                    }
                                    tooltiptitle += '</tooltip>';
                                    v.Body += '<a data-toggle="tooltip" href="javascript:void(0);" title="' + tooltiptitle + '">' + (parseInt(v.ActionValue.length) - 1) + ' more</a>';
                                }
                                break;
                            case 'THREAD_CREATED':
                                v.Body += 'Conversation started ' + v.ActionValue;
                                break;
                            case 'CONVERSATION_NAME':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' named the conversation: ';
                                v.Body += v.ActionValue;
                                break;
                            case 'CONVERSATION_NAME_REMOVED':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' removed the conversation name.';
                                break;
                            case 'CONVERSATION_DATE':
                                v.Body = v.ActionValue;
                                break;
                            case 'CONVERSATION_IMAGE':
                                if (v.ActionCreator.FirstName == 'You') {
                                    v.Body += v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName;
                                } else {
                                    v.Body += '<a href="' + site_url + v.ActionCreator.ProfileURL + '">' + v.ActionCreator.FirstName + ' ' + v.ActionCreator.LastName + '</a>';
                                }
                                v.Body += ' changed the conversation picture : <br/>';
                                v.Body += '<ul id="lg-' + v.MessageGUID + '" class="m-msz-attached"><li ng-init="callLightGallery(\'' + v.MessageGUID + '\')" ng-data-src="' + image_server_path + 'upload/messages/' + v.ActionValue + '" class="attached-list"><img src="' + image_server_path + 'upload/messages/220x220/' + v.ActionValue + '" /></li></ul>';
                                v.Body = v.Body;
                                setTimeout(function() {
                                    $scope.callLightGallery(v.MessageGUID);
                                }, 2000);
                                break;
                        }
                    } else {
                        if (v.Media.length > 0) {
                            $(v.Media).each(function(mk, mv) {
                                if (mv.MediaType == 'Video') {
                                    v.Media[mk].ImageName = $scope.getVideoUrl(mv.ImageName);
                                }
                            });
                        }
                    }
                    /*if(AppendLast !== '1'){
                        $scope.MessageList.push(v);                            
                    }*/

                    v.CreatedDate = $scope.UTCtoTimeZone(v.CreatedDate);
                    var t = v.CreatedDate.split(/[- :]/);
                    var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                    date = moment(date).format('DD MMM YYYY');
                    if (date == moment().format('DD MMM YYYY')) {
                        date = 'Today';
                    }
                    if ($scope.prevDate !== date) {
                        if ($scope.prevDate == '') {
                            $scope.prevDate = date;
                            v['NewDate'] = '';
                        } else {
                            $scope.prevDate = date;
                            v['NewDate'] = $scope.prevDate;
                        }
                    } else {
                        v['NewDate'] = '';
                    }

                    var exists = 0;
                    $($scope.MessageList).each(function(mykey, myval) {
                        if (myval.MessageGUID == v.MessageGUID) {
                            exists = 1;
                        }
                    });
                    if (exists == 0) {
                        $scope.MessageList.push(v);
                    }
                });

                /*if(AppendLast == '1' && response.Data.Messages.length>0){
                    $scope.MessageList.push(response.Data.Messages[response.Data.Messages.length-1]);
                }*/
                $scope.tags = [];
                $($scope.thread_list).each(function(k, v) {
                    if (v.ThreadGUID == $scope.Messages.ThreadGUID) {
                        $scope.RecipientsList = v.Recipients;
                        $(v.Recipients).each(function(key, val) {
                            //$scope.tags.push({'name':val.FirstName+' '+val.LastName,'UserGUID':val.UserGUID});
                        });
                        $scope.thread_list[k].InboxNewMessageCount = 0;
                    }
                });
                setTimeout(function() {
                    $('.mCustomScrollbar-right').mCustomScrollbar("scrollTo", 10000000000000);
                    $('.m-conversation-loader').hide();
                }, 1200);
            }
        });
    }

    $scope.getFormattedTime = function(CreatedDate, Format) {
        var t = CreatedDate.split(/[- :]/);
        var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
        date = moment(date).format(Format);
        return date;
    }

    $scope.remove_recipient = function(UserGUID) {
        var Recipients = [];
        Recipients.push({ 'UserGUID': UserGUID });
        var reqData = { ThreadGUID: $scope.Messages.ThreadGUID, Recipients: Recipients };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/remove_participant', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $($scope.RecipientsList).each(function(k, v) {
                    if (v.UserGUID == UserGUID) {
                        $scope.RecipientsList.splice(k, 1);
                        $scope.get_thread_details($scope.Messages.ThreadGUID, '1');
                    }
                });
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.update_last_message = function(ThreadGUID) {
        var reqData = { ThreadGUID: ThreadGUID };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/update_last_message', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $($scope.thread_list).each(function(key, val) {
                    if (val.ThreadGUID == ThreadGUID) {
                        $scope.thread_list[key].InboxUpdated = response.Data.InboxUpdated;
                        $scope.thread_list[key].Body = $scope.getThreadBody(response.Data);

                    }
                });
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.showHideMsgSection = function() {
        /*if($('#EnableMessage').is(':checked'))
        {
            $('#MsgPanelBody,#seeAlllink').show();
        }
        else
        {
            $('#MsgPanelBody,#seeAlllink').hide();
        }*/
    }

    $scope.change_thread_status = function(ThreadGUID, Status) {
        $('[data-toggle="tooltip"]').tooltip('destroy');
        if (Status == 'DELETED') {
            showDelConfirmBox("Delete This Entire Conversation?", "Once you delete your copy of this conversation, it cannot be undone.", function(e) {
                if (e) {
                    var reqData = { ThreadGUID: ThreadGUID, Status: Status };
                    WallService.CallPostApi(appInfo.serviceUrl + 'messages/change_thread_status', reqData, function(successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $($scope.thread_list).each(function(k, v) {
                                if (v.ThreadGUID == ThreadGUID) {
                                    if (Status == 'DELETED') {
                                        if ($scope.Messages.ThreadGUID == ThreadGUID) {
                                            $scope.prevThreadGUID = '';
                                            $scope.nextThreadGUID = '';
                                            $scope.StopPrevThreadGUID = 0;
                                            $($scope.thread_list).each(function(tk, tv) {
                                                if (tv.ThreadGUID !== ThreadGUID) {
                                                    if ($scope.StopPrevThreadGUID == 0) {
                                                        $scope.prevThreadGUID = tv.ThreadGUID;
                                                    } else {
                                                        if ($scope.nextThreadGUID == '') {
                                                            $scope.nextThreadGUID = tv.ThreadGUID;
                                                        }
                                                    }
                                                } else {
                                                    $scope.StopPrevThreadGUID = 1;
                                                }
                                            });


                                            if ($scope.nextThreadGUID !== '') {
                                                $scope.get_new_thread_details($scope.nextThreadGUID);
                                            } else if ($scope.prevThreadGUID !== '') {
                                                $scope.get_new_thread_details($scope.prevThreadGUID);
                                            } else {
                                                $scope.compose_new_message();
                                            }
                                        }
                                    } else if (Status == 'ARCHIVED' && $scope.Filter !== 'ARCHIVED') {
                                        $scope.thread_list.splice(k, 1);
                                    } else if (Status == 'READ') {
                                        $scope.thread_list[k].InboxNewMessageCount = 0;
                                        if ($scope.Filter == 'UN_READ') {
                                            $scope.thread_list.splice(k, 1);
                                        }
                                    } else if (Status == 'UN_READ') {
                                        $scope.thread_list[k].InboxNewMessageCount = 1;
                                    } else if (Status == 'UN_ARCHIVE' && $scope.Filter == 'ARCHIVED') {
                                        console.log('asdsad');
                                        $scope.thread_list.splice(k, 1);
                                    }
                                $scope.thread_list.splice(k, 1);
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                    if (IsNewsFeed == 1) {
                        angular.forEach($scope.thread_list, function(val, key) {
                            if (val.ThreadGUID == ThreadGUID) {
                                $scope.thread_list.splice(key, 1);
                            }
                        });
                        $scope.get_threads();
                    }
                }
            });
        } else {
            var reqData = { ThreadGUID: ThreadGUID, Status: Status };
            WallService.CallPostApi(appInfo.serviceUrl + 'messages/change_thread_status', reqData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $($scope.thread_list).each(function(k, v) {
                        if (v.ThreadGUID == ThreadGUID) {
                            if (Status == 'DELETED') {
                                $scope.thread_list.splice(k, 1);
                                if ($scope.Messages.ThreadGUID == ThreadGUID) {
                                    $scope.prevThreadGUID = '';
                                    $scope.nextThreadGUID = '';
                                    $scope.StopPrevThreadGUID = 0;
                                    $($scope.thread_list).each(function(tk, tv) {
                                        if (tv.ThreadGUID !== ThreadGUID) {
                                            if ($scope.StopPrevThreadGUID == 0) {
                                                $scope.prevThreadGUID = tv.ThreadGUID;
                                            } else {
                                                if ($scope.nextThreadGUID == '') {
                                                    $scope.nextThreadGUID = tv.ThreadGUID;
                                                }
                                            }
                                        } else {
                                            $scope.StopPrevThreadGUID = 1;
                                        }
                                    });
                                    if ($scope.nextThreadGUID !== '') {
                                        $scope.get_new_thread_details($scope.nextThreadGUID);
                                    } else if ($scope.prevThreadGUID !== '') {
                                        $scope.get_new_thread_details($scope.prevThreadGUID);
                                    } else {
                                        $scope.compose_new_message();
                                    }
                                }
                            } else if (Status == 'ARCHIVED' && $scope.Filter !== 'ARCHIVED') {
                                if (ThreadGUID == $scope.Messages.ThreadGUID) {
                                    $scope.prevThreadGUID = '';
                                    $scope.nextThreadGUID = '';
                                    $scope.StopPrevThreadGUID = 0;
                                    $($scope.thread_list).each(function(tk, tv) {
                                        if (tv.ThreadGUID !== ThreadGUID) {
                                            if ($scope.StopPrevThreadGUID == 0) {
                                                $scope.prevThreadGUID = tv.ThreadGUID;
                                            } else {
                                                if ($scope.nextThreadGUID == '') {
                                                    $scope.nextThreadGUID = tv.ThreadGUID;
                                                }
                                            }
                                        } else {
                                            $scope.StopPrevThreadGUID = 1;
                                        }
                                    });
                                    if ($scope.nextThreadGUID !== '') {
                                        $scope.get_new_thread_details($scope.nextThreadGUID);
                                    } else if ($scope.prevThreadGUID !== '') {
                                        $scope.get_new_thread_details($scope.prevThreadGUID);
                                    } else {
                                        $scope.compose_new_message();
                                    }
                                }
                                $scope.thread_list.splice(k, 1);
                            } else if (Status == 'READ') {
                                $scope.thread_list[k].InboxNewMessageCount = 0;
                                if ($scope.Filter == 'UN_READ' || $scope.Filter == 'ARCHIVED') {
                                    if (ThreadGUID == $scope.Messages.ThreadGUID) {
                                        $scope.prevThreadGUID = '';
                                        $scope.nextThreadGUID = '';
                                        $scope.StopPrevThreadGUID = 0;
                                        $($scope.thread_list).each(function(tk, tv) {
                                            if (tv.ThreadGUID !== ThreadGUID) {
                                                if ($scope.StopPrevThreadGUID == 0) {
                                                    $scope.prevThreadGUID = tv.ThreadGUID;
                                                } else {
                                                    if ($scope.nextThreadGUID == '') {
                                                        $scope.nextThreadGUID = tv.ThreadGUID;
                                                    }
                                                }
                                            } else {
                                                $scope.StopPrevThreadGUID = 1;
                                            }
                                        });
                                        if ($scope.nextThreadGUID !== '') {
                                            $scope.get_new_thread_details($scope.nextThreadGUID);
                                        } else if ($scope.prevThreadGUID !== '') {
                                            $scope.get_new_thread_details($scope.prevThreadGUID);
                                        } else {
                                            $scope.compose_new_message();
                                        }
                                    }
                                    if ($scope.Filter == 'UN_READ') {
                                        $scope.thread_list.splice(k, 1);
                                    }
                                }
                            } else if (Status == 'UN_READ') {
                                $scope.thread_list[k].InboxNewMessageCount = 1;
                            } else if (Status == 'UN_ARCHIVE' && $scope.Filter == 'ARCHIVED') {
                                if (ThreadGUID == $scope.Messages.ThreadGUID) {
                                    $scope.prevThreadGUID = '';
                                    $scope.nextThreadGUID = '';
                                    $scope.StopPrevThreadGUID = 0;
                                    $($scope.thread_list).each(function(tk, tv) {
                                        if (tv.ThreadGUID !== ThreadGUID) {
                                            if ($scope.StopPrevThreadGUID == 0) {
                                                $scope.prevThreadGUID = tv.ThreadGUID;
                                            } else {
                                                if ($scope.nextThreadGUID == '') {
                                                    $scope.nextThreadGUID = tv.ThreadGUID;
                                                }
                                            }
                                        } else {
                                            $scope.StopPrevThreadGUID = 1;
                                        }
                                    });
                                    if ($scope.nextThreadGUID !== '') {
                                        $scope.get_new_thread_details($scope.nextThreadGUID);
                                    } else if ($scope.prevThreadGUID !== '') {
                                        $scope.get_new_thread_details($scope.prevThreadGUID);
                                    } else {
                                        $scope.compose_new_message();
                                    }
                                }
                                $scope.thread_list.splice(k, 1);
                            }
                        }
                    });
                }
            });
            if (IsNewsFeed == 1) {
                $scope.get_threads();
            }
        }
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body',
            html: true
        });
    }

    $scope.removeSearch = function() {
        $scope.SearchKeyword = '';
        $scope.get_search_thread();
    }

    $scope.get_search_thread = function() {
        if ($scope.SearchKeyword.length > 2 || $scope.SearchKeyword == 0) {
            if ($('.icon-m-search').hasClass('icon-removeclose')) {
                if ($scope.SearchKeyword == 0) {
                    $('.icon-m-search').removeClass('icon-removeclose');
                }
            } else {
                if ($scope.SearchKeyword.length > 0) {
                    $('.icon-m-search').addClass('icon-removeclose');
                }
            }
            $('#LeftPageNo').val(1);
            $scope.thread_list = [];
            $scope.get_threads();
        }
    }

    $scope.get_filter_thread = function(filter) {
        $scope.PageJustLoaded = 1;
        $('#LeftPageNo').val(1);
        $scope.Filter = filter;
        $scope.thread_list = [];
        $scope.get_threads();
    };
    
//    message attachements module start
      $scope.validateFileSize = function (file, fileType) {
        var defer = $q.defer();
        var isResolvedToFalse = false;
        var imagePatt = new RegExp("^image");
        var videoPatt = new RegExp("^video");
//        var validExtensions = ['avi', 'AVI', 'flv', 'FLV', 'mpeg', 'MPEG', 'mpg', 'MPG', 'wmv', 'WMV', 'swf', 'SWF', 'asf', 'ASF', 'mov', 'mp4', 'MP4', 'webm', 'WEBM', 'ogg', 'OGG', 'bmp', 'jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG'];
        var validExtensions = ['avi', 'AVI', 'M4A', 'M4P', 'MMF', 'MP3', 'RA', 'RM', 'WAV', 'WMA', 'MIDI', 'm4a', 'm4p', 'mmf', 'mp3', 'ra', 'rm', 'wav', 'wma', 'midi', 'flv', 'FLV', 'mpeg', 'MPEG', 'mpg', 'MPG', 'wmv', 'WMV', 'swf', 'SWF', 'asf', 'ASF', 'mov', 'MOV', 'mp4', 'MP4', 'webm', 'WEBM', 'ogg', 'OGG', 'doc', 'docx', 'txt', 'ppt', 'xls', 'odt', 'xlsx', 'jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG', 'DOC', 'DOCX', 'TXT', 'PPT', 'XLS', 'ODT', 'XLSX', 'pptx', 'PPTX', 'pdf', 'PDF', 'bmp', 'BMP', 'docm', 'DOCM', 'pps', 'PPS', 'ppsx', 'PPSX', 'ods', 'ODS', 'odp', 'ODP', 'csv', 'CSV', 'rtf', 'RTF'];
        var fileNameExt = file.name.substr(file.name.lastIndexOf('.') + 1);
        
        if ($.inArray(fileNameExt, validExtensions) == -1) {
          file.$error = 'Type Error';
          file.$errorMessages = 'Allowed file types only avi, flv, mpeg, mpg, wmv, swf, asf, mov, mp4, ogg, webm, jpeg, jpg, gif and png.';
          defer.resolve(false);
          isResolvedToFalse = true;
        }

        if ( ( fileType == 'image' ) && !imagePatt.test(file.type) && !isResolvedToFalse ) {
          file.$error = 'Size Error';
          file.$errorMessages = 'Only image files are allowed.';
          defer.resolve(false);
          isResolvedToFalse = true;
        }
        
        if ( ( fileType == 'file' ) && imagePatt.test(file.type)) {
          file.$error = 'Size Error';
          file.$errorMessages = 'Image files are not allowed.';
          defer.resolve(false);
          isResolvedToFalse = true;
        }
        
        if ( videoPatt.test(file.type) && !isResolvedToFalse ) {
          if (file.size > 41943040) { // if video size > 41943040 Bytes = 40 Mb
            file.$error = 'Size Error';
            file.$errorMessages = file.name + ' is too large.';
            defer.resolve(false);
            isResolvedToFalse = true;
          }
        } else if ( !isResolvedToFalse ) {
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
      };

        $scope.isMsgAttachementUploading = false;
        $scope.medias = {};
        $scope.mediaCount = 0;
        $scope.files = {};
        $scope.fileCount = 0;
        var msgMediaCurrentIndex = 0;
        var msgFileCurrentIndex = 0;
        $scope.uploadMsgFiles = function (files, errFiles) {
            var promises = [];
            if (!(errFiles.length > 0)) {
                var patt = new RegExp("^image");
                var videoPatt = new RegExp("^video");
                $scope.isMsgAttachementUploading = true;
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, mediaIndex, fileIndex) {
                        WallService.setFileMetaData(file);
                        var paramsToBeSent = {                            
                            Type: 'messages',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        var fileType = 'media';
                        if (patt.test(file.type)) {
                            $scope.medias['media-' + mediaIndex] = file;
                            $scope.mediaCount = Object.keys($scope.medias).length;
                            attachedmediaWd();
                            checkAttachmentView();
                        } else {
                            $scope.files['file-' + fileIndex] = file;
                            $scope.fileCount = Object.keys($scope.files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';

                        }

                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope, fileIndex : fileIndex, mediaIndex : mediaIndex}, {}, response);
                                    if (fileType === 'media') {                                        
                                        if (response.data.ResponseCode == 200) {
                                            $scope.medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.medias['media-' + mediaIndex].progress = true;
                                        } else {
                                            delete $scope.medias['media-' + mediaIndex];
                                            $scope.mediaCount = Object.keys($scope.medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.files['file-' + fileIndex].progress = true;
                                        } else {
                                            delete $scope.files['file-' + fileIndex];
                                            $scope.fileCount = Object.keys($scope.files).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }
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
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope, fileIndex : fileIndex, mediaIndex : mediaIndex}, evt);
                                });

                        promises.push(promise);

                    })(fileToUpload, msgMediaCurrentIndex, msgFileCurrentIndex);
                    if (patt.test(fileToUpload.type)) {
                        msgMediaCurrentIndex++;
                    } else {
                        msgFileCurrentIndex++;
                    }
                });
                $q.all(promises).then(function (data) {
                    $scope.isMsgAttachementUploading = false;
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
        
        function resetAttachements() {
            $scope.medias = {};
            $scope.mediaCount = 0;
            $scope.files = {};
            $scope.fileCount = 0;
            msgMediaCurrentIndex = 0;
            msgFileCurrentIndex = 0;
        }

        $scope.removeMsgAttachement = function (type, mediaKey, MediaGUID) {
            if ((type === 'file') && Object.keys($scope.files).length) {
                delete $scope.files[mediaKey];
                $scope.fileCount = Object.keys($scope.files).length;
            } else if (Object.keys($scope.medias).length) {
                delete $scope.medias[mediaKey];
                $scope.mediaCount = Object.keys($scope.medias).length;
            }
        };    
//    message attachements module end

    $scope.compose = function() {
        if ($scope.tags.length > 0) {

            var Recipients = [];
            $($scope.tags).each(function(k, v) {
                Recipients.push({ "UserGUID": v.UserGUID });
            });
            var Media = [];
            
            
//            if ($('input[name="PhotoMediaGUID[]"]').length > 0) {
//                $('input[name="PhotoMediaGUID[]"]').each(function(k, v) {
//                    Media.push({ 'MediaGUID': v.value, 'Caption': '' });
//                });
//            }
//            if ($('input[name="FileMediaGUID[]"]').length > 0) {
//                $('input[name="FileMediaGUID[]"]').each(function(k, v) {
//                    Media.push({ 'MediaGUID': v.value, 'Caption': '' });
//                });
//            }
            
            var attacheMentPromises = [];

            if ($scope.medias && (Object.keys($scope.medias).length > 0)) {
                angular.forEach($scope.medias, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        Media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            Caption: ''
                        });
                    }));
                });
            }

            if ($scope.files && (Object.keys($scope.files).length > 0)) {
                angular.forEach($scope.files, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        Media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            Caption: ''
                        });
                    }));
                });
            }

            $q.all(attacheMentPromises).then(function (data) {
              if (Media.length == 0 && $.trim($('.msgbody').val()) == '') {
                  showResponseMessage('Please add some content.', 'alert-danger');
                  return false;
              }
              var reqData = { ModuleID: '', ModuleEntityGUID: '', Subject: '', Body: $('.msgbody').val(), Media: Media, Recipients: Recipients, Replyable: '1' };
              WallService.CallPostApi(appInfo.serviceUrl + 'messages/compose', reqData, function (successResp) {
                  var response = successResp.data;
                  if (response.ResponseCode == 200) {
                      MsgType = '';
                      MsgGUID = '';
                      $('.msgbody').val('');
                      $('#LeftPageNo').val(1);
                      $scope.get_filter_thread();
                      $scope.get_new_thread_details(response.Data.ThreadGUID);
                      $scope.tags = [];
                      resetAttachements();
                      checkAttachmentView();
                  }
              }, function (error) {
                  // showResponseMessage('Something went wrong.', 'alert-danger');
              });
            });
        } else {
            showResponseMessage('Please add at least 1 recipient.', 'alert-danger');
        }
    }

    $scope.getThreadSubject = function(ThreadSubject, EditableThread, Recipients, Section) {

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
        } else if (ThreadSubject !== '' && Section == 'Right') {
            ThreadSubject = ThreadSubject + ' (' + Recipients.length + ' People)';
        }
        return ThreadSubject;
    }

    $scope.reply = function() {
        var Recipients = [];
        $($scope.thread_list).each(function(k, v) {
            if (v.ThreadGUID == $scope.Messages.ThreadGUID) {
                $(v.Recipients).each(function(key, val) {
                    Recipients.push({ "UserGUID": val.UserGUID });
                });
            }
        });
        if (Recipients.length > 0) {
            var Media = [];

//            if ($('input[name="PhotoMediaGUID[]"]').length > 0) {
//                $('input[name="PhotoMediaGUID[]"]').each(function(k, v) {
//                    Media.push({ 'MediaGUID': v.value, 'Caption': '' });
//                });
//            }
//            if ($('input[name="FileMediaGUID[]"]').length > 0) {
//                $('input[name="FileMediaGUID[]"]').each(function(k, v) {
//                    Media.push({ 'MediaGUID': v.value, 'Caption': '' });
//                });
//            }

              var attacheMentPromises = [];

            if ($scope.medias && (Object.keys($scope.medias).length > 0)) {
                angular.forEach($scope.medias, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        Media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            Caption: ''
                        });
                    }));
                });
            }

            if ($scope.files && (Object.keys($scope.files).length > 0)) {
                angular.forEach($scope.files, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        Media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            Caption: ''
                        });
                    }));
                });
            }

            $q.all(attacheMentPromises).then(function (data) {
              if (Media.length == 0 && $.trim($('.msgbody').val()) == '') {
                  showResponseMessage('Please add some content.', 'alert-danger');
                  return false;
              }
              var reqData = { ThreadGUID: $scope.Messages.ThreadGUID, Body: $('.msgbody').val(), Media: Media, Recipients: Recipients };
              WallService.CallPostApi(appInfo.serviceUrl + 'messages/reply', reqData, function (successResp) {
                  var response = successResp.data;
                  if (response.ResponseCode == 200) {
                      MsgType = '';
                      MsgGUID = '';
                      $scope.get_filter_thread();
                      $('.msgbody').val('');
                      response.Data.CreatedDate = $scope.UTCtoTimeZone(response.Data.CreatedDate);
                      $scope.MessageList.push(response.Data);
                      resetAttachements();
//                      $('.m-media-attached-list ul,.m-file-attached-wrapper').html('');
//                      $('.m-media-attached-list,.m-file-attached-wrapper').hide();

                      $($scope.MessageList).each(function(mk, mv) {
                          //mv.CreatedDate = $scope.UTCtoTimeZone(mv.CreatedDate);
                          var t = mv.CreatedDate.split(/[- :]/);
                          var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                          date = moment(date).format('DD MMM YYYY');
                          if (date == moment().format('DD MMM YYYY')) {
                              date = 'Today';
                          }
                          if ($scope.prevDate !== date) {
                              if ($scope.prevDate == '') {
                                  $scope.prevDate = date;
                                  $scope.MessageList[mk]['NewDate'] = '';
                              } else {
                                  $scope.prevDate = date;
                                  $scope.MessageList[mk]['NewDate'] = $scope.prevDate;
                              }
                          } else {
                              $scope.MessageList[mk]['NewDate'] = '';
                          }
                      });
                      setTimeout(function() {
                          $('.mCustomScrollbar-right').mCustomScrollbar("scrollTo", 10000000000);
                      }, 500);
                      checkAttachmentView();
                  }
              }, function (error) {
                  // showResponseMessage('Something went wrong.', 'alert-danger');
              });
            });
        } else {
            showResponseMessage('Please add at least 1 recipient.', 'alert-danger');
        }
    }

    $scope.removeMessage = function(MessageGUID) {
        showDelConfirmBox("Delete Message", "Once you delete your copy of this message, it cannot be undone.", function(e) {
            if (e) {
                var reqData = { MessageGUID: MessageGUID };
                WallService.CallPostApi(appInfo.serviceUrl + 'messages/delete', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $($scope.MessageList).each(function(k, v) {
                            if (v.MessageGUID == MessageGUID) {
                                $scope.MessageList.splice(k, 1);
                                $scope.update_last_message($scope.Messages.ThreadGUID);
                            }
                        });
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        });
    }

    $scope.get_date_difference = function(pDate, nDate) {
        if (typeof pDate !== 'undefined') {
            var pt = pDate.split(/[- :]/);
            var pd = new Date(pt[0], pt[1] - 1, pt[2], pt[3], pt[4], pt[5]);

            var nt = nDate.split(/[- :]/);
            var nd = new Date(nt[0], nt[1] - 1, nt[2], nt[3], nt[4], nt[5]);

            if (pd.getYear() == nd.getYear() && pd.getMonth() == nd.getMonth() && pd.getDate() == nd.getDate()) {
                return false;
            } else {
                return true;
            }
        }
    }

    $scope.edit_thread = function(ThreadGUID) {
        var Subject = $scope.EditSubject;
        var Media = {};
        if ( Object.keys($scope.threadImage).length && $scope.threadImage.MediaGUID ) {
          Media = { 'MediaGUID': $scope.threadImage.MediaGUID, 'Caption': '' };
        }
        var reqData = { ThreadGUID: ThreadGUID, Subject: Subject, Media: Media };
        WallService.CallPostApi(appInfo.serviceUrl + 'messages/edit_thread', reqData, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.Messages = response.Data;
                $scope.EditSubject = $scope.Messages.ThreadSubject;
                $($scope.thread_list).each(function(k, v) {
                    if (v.ThreadGUID == ThreadGUID) {
                        $scope.thread_list[k].ThreadSubject = $scope.getThreadSubject($scope.Messages.ThreadSubject, $scope.Messages.EditableThread, $scope.RecipientsList, 'Left');
                        $scope.thread_list[k].ThreadImageName = $scope.Messages.ThreadImageName;
                        $scope.get_thread_details(ThreadGUID, '1');
                    }
                });
                $scope.threadImage = {};
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.compose_new_message = function() {
        $scope.tags = [];
        $scope.Messages = [];
        $scope.MessageList = [];
        $scope.ShowSettings = 0;
        $scope.RecipientsList = [];
        $('.msgbody').val('');
        $('.one-to-one').removeClass('one-to-one');
        resetAttachements();
        setTimeout(function() { $('.m-conversation-loader').hide(); }, 500);
    }

    $scope.tags = [];
    $scope.loadFriends = function($query) {
        var reqData = { SearchKeyword: $query, Hide: $scope.RecipientsList };
        return WallService.CallPostApi(appInfo.serviceUrl + 'messages/search_user', reqData, function (successResp) {
            var response = successResp.data;
            var friends = [];
            $(response.Data).each(function(k, v) {
                var name = v.FirstName + ' ' + v.LastName;
                friends.push({ name: name, thumb: image_server_path + 'upload/profile/' + v.ProfilePicture, UserGUID: v.UserGUID });
            });
            return friends.filter(function(friend) {
                return friend.name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    }

    $scope.callLightGallery = function(id) {
        var gallery = $("#lg-" + id).lightGallery();
        if (!gallery.isActive()) {
            gallery.destroy();
        }

        $('#lg-' + id).lightGallery({
            showThumbByDefault: false,
            addClass: 'showThumbByDefault',
            hideControlOnEnd: true,
            preload: 2,
            onOpen: function() {
                var nextthmb = $('.thumb.active').next('.thumb').html();
                var prevthmb = $('.thumb.active').prev('.thumb').html();

                $('#lg-prev').append(prevthmb);
                $('#lg-next').append(nextthmb);
                $('.cl-thumb').remove();

            },
            onSlideNext: function(plugin) {
                var nextthmb = $('.thumb.active').next('.thumb').html();
                var prevthmb = $('.thumb.active').prev('.thumb').html();

                $('#lg-prev').html(prevthmb);
                $('#lg-next').html(nextthmb);
            },
            onSlidePrev: function(plugin) {
                var nextthmb = $('.thumb.active').next('.thumb').html();
                var prevthmb = $('.thumb.active').prev('.thumb').html();

                $('#lg-prev').html(prevthmb);
                $('#lg-next').html(nextthmb);
            }
        });
    }

    $scope.layoutDone = function() {
        $timeout(function() {
            $('[data-toggle="tooltip"]').tooltip({
                container: 'body',
                html: true
            });
            messageColresize();
        }, 1000); // wait...
    }

    $scope.resetAddPeople = function() {
        $scope.tags = [];
        $('#addPeopleTags input').val('');
    }

    window.onload = function() {
        $('.mCustomScrollbar-right').mCustomScrollbar({
            callbacks: {
                onTotalScrollBack: function() {
                    $scope.message_details($scope.Messages.ThreadGUID);
                }
            }
        });

        $('.mCustomScrollbar-left').mCustomScrollbar({
            callbacks: {
                onTotalScroll: function() {
                    //console.log('I am at last');
                    angular.element('#messageSectionCtrl').scope().get_threads();
                    //$scope.get_threads();
                }
            }
        });
    }

    $(document).ready(function() {

        $('.m-write-msz textarea').keydown(function() {
            var str = $('.m-write-msz textarea').val();
            if (str && str.length >= 1) {
                var firstChar = str.charAt(0);
                var remainingStr = str.slice(1);
                str = firstChar.toUpperCase() + remainingStr;
                $('.m-write-msz textarea').val(str);
            }
        });

    });
    
    //Thread Image start
    $scope.isThreadImageUploading = false;
    $scope.threadImage = {};
    $scope.uploadThreadImage = function (file, errFiles) {
    if (!(errFiles.length > 0)) {
      var patt = new RegExp("^image");
      $scope.isThreadImageUploading = true;
      var paramsToBeSent = {
        Type: 'messages',
        DeviceType: 'Native',
        qqfile: file
      };
      if (!patt.test(file.type)) {
        showResponseMessage('Only image files are allowed.', 'alert-danger');
        return false;
      }

      WallService.CallUploadFilesApi(
              paramsToBeSent,
              'upload_image',
              function (response) {
                if (response.data.ResponseCode === 200) {
                  var responseJSON = response.data;
                  if (responseJSON.Message == 'Success') {
                    $scope.threadImage = { ImageServerPath : responseJSON.Data.ImageServerPath, ImageName : responseJSON.Data.ImageName, MediaGUID : responseJSON.Data.MediaGUID };
                  } else {
                    showResponseMessage(responseJSON.Message, 'alert-danger');
                  }
                }
                $scope.isThreadImageUploading = false;
              },
              function (response) {
                //showResponseMessage('The uploaded image does not seem to be in a valid image format.', 'alert-danger');
              });
    } else {
//            showResponseMessage(errFiles[0].$errorMessages, 'alert-danger');
    }
  };
  
  $scope.removeThreadImage = function (){
    $scope.threadImage = {};
  };
  //Thread Image start
    
}]);

app.directive('makeUlWidth', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attrs, ngModel) {
      var totalLiof = angular.element('.m-media-attached-list > ul > li').size(),
      totalulWd = totalLiof * 108;
      angular.element('.m-media-attached-list > ul.attachedList').width(totalulWd);
    }
  };
});

app.directive('dynamicUrl', [function() {
    return {
        restrict: 'A',
        link: function postLink(scope, element, attr) {
            element.attr('src', attr.dynamicUrlSrc);
        }
    };
}]);

function checkAttachmentView() {
//    if ($('.m-file-attached-wrapper').html() !== '' || $('.m-media-attached-list ul').html() !== '') {
//        $('.m-attachment-view').show();
//    } else {
//        $('.m-attachment-view').hide();
//    }
    //mszSectionHeight();
    setTimeout(function() {
        messageColresize();
    }, 600);
}
$(document).ready(function() {
    window.onbeforeunload = function(e) {
        $('#LeftPageNo').val(1);
        $('#RightPageNo').val(2);
    };
});
