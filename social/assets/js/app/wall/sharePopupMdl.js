!(function (app, angular) {

    angular.module('sharePopupMdl', [])
        .controller('shareCtrl', shareCtrl);

    function shareCtrl($scope, $http, WallService, appInfo,Settings) {

        $scope.wallScope = {};
        $scope.SiteURL = Settings.getSiteUrl();
        $scope.ShareByEmail = {};
        $scope.mediaShareData ={};

        $scope.$on('sharePopupMdlInit', function (event, data) {
            $scope.wallScope = data.wallScope;

            if (!$('#sharemodal').is(':visible')) {
                $('#sharemodal').modal('show');
            }
            $scope.shareEmit(data.ActivityGUID,data.fn);

        });

        $scope.$on('shareMediaPopupMdlInit',function (event, data) {
            $scope.wallScope = data.wallScope;

            if (!$('#sharemediamodal').is(':visible')) {
                $('#sharemediamodal').modal('show');
            }
            $scope.shareMediaDetails(data.MediaGUID);

        });

        $scope.shareMediaDetails = function(MediaGUID){
            var reqData = {
                MediaGUID: MediaGUID
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'media/share_details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.mediaShareData = response.Data;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.shareEmit = function (ActivityGUID,fn) {
            $scope.singleActivity = [];
            if (!$scope.$$phase) {
                $scope.$apply();
            }
            $('#ShareModuleEntityGUID').val(ActivityGUID);
            $('#ShareEntityUserGUID').val($('#module_entity_guid').val());
            if(fn == 'shareEmitAnnouncement'){
                $scope.wallScope.getSingleAnnouncement(ActivityGUID);
            }else{
                $scope.wallScope.getSingleActivity(ActivityGUID);
            }

            $scope.singleActivity = $scope.wallScope.singleActivity;
            $('.about-name input').val('');
            $('#PCnt').val('');
            $('#ShareActivityGUID').val('');
            $('#sharemodal .text-field-select select').val('own-wall');
            $('#sharemodal .text-field-select select').trigger("chosen:updated");
            if (!$('.about-name').hasClass('hide')) {
                $('.about-name').addClass('hide');
            }
        };

        $scope.shareActivity = function () {
            $scope.ShareEntityType = 'Activity';
            var Error = false;
            var sharetype = $("#sharetype").val();
            var EntityUserGUID = $("#ShareEntityUserGUID").val();
            var ModuleEntityGUID = $('#ShareModuleEntityGUID').val();
            var ShareActivityGUID = $('#ShareActivityGUID').val();
            if ($('#friend-src').val() == '') {
                EntityUserGUID = '';
            }
            if (sharetype == "friend-wall") {
                if (EntityUserGUID == "") {
                    Error = true;
                    showResponseMessage('Please select one of your  friend.', 'alert-danger');
                }
            }
            if (Error == false) {
                var PostContent = '';
                if ($('#PCnt').length > 0 && $('#PCnt').val().length > 0) {
                    PostContent = $('#PCnt').val();
                }
                if ($('#PCnt2').length > 0 && $('#PCnt2').val().length > 0) {
                    PostContent = $('#PCnt2').val();
                }
                if ($('.own-wall').is(':visible')) {
                    var shareVisibleFor = $('#shareVisibleFor').val();
                    if ($('#shareComment').hasClass('on')) {
                        $('#shareComment').removeClass('on');
                    }
                } else {
                    var shareCommentSettings = '';
                    var shareVisibleFor = '';
                }
                var shareCommentSettings = $('#shareCommentSettings').val();

                var id = $('[data-guid="act-' + ModuleEntityGUID + '"]').attr('id');
                var element = $('#' + id + ' .post-as-data');

                var reqData = {
                    EntityGUID: ModuleEntityGUID,
                    ModuleEntityGUID: EntityUserGUID,
                    ModuleID: 3,
                    PostContent: PostContent,
                    Commentable: shareCommentSettings,
                    Visibility: shareVisibleFor,
                    EntityType: $scope.ShareEntityType,
                    PostAsModuleID: element.attr('data-module-id'),
                    PostAsModuleEntityGUID: element.attr('data-module-entityid'),
                    ActivityGUID: ShareActivityGUID
                };
                WallService.CallApi(reqData, 'activity/sharePost').then(function (response) {
                    if (response.ResponseCode == 200) {
                        if(reqData['ActivityGUID']!='')
                        {
                            var NewsFeedCtrl = angular.element(document.getElementById('NewsFeedCtrl')).scope();
                            angular.forEach(NewsFeedCtrl.activityData,function(val,key){
                                if(val.ActivityGUID == reqData['ActivityGUID'])
                                {
                                    NewsFeedCtrl.activityData[key]['PostContent'] = response.Data[0].PostContent;
                                }
                            });
                        }
                        showResponseMessage('Post has been shared successfully.', 'alert-success');
                        $('#sharemodal').modal('toggle');
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }

                });
                $(".ShareEntityType:checked").prop('checked', false);
            }
        };

        $scope.$on('FacebookShareEmit', function (obj, href, description, name, picture) {
            FB.ui({
                method: 'share',
                href: href,
                caption: base_url,
                description: $scope.wallScope.strip(description),
                quote: $scope.wallScope.strip(name),
                picture: picture,
            }, function (response) {
            });
        });

        $scope.$on('showEmailPopupEmitOnShare', function (obj, singleActivity)
        {
            shareContent = $('#act-' + singleActivity.ActivityGUID + ' .feed-body').html();
            $scope.gmailShareLink = encodeURI('https://mail.google.com/mail/u/0/?view=cm&su='+ $scope.lang.web_name +' - News Feed&to&body=' + shareContent + ' ' + $scope.SiteURL+singleActivity.ActivityURL);
            $scope.yahooShareLink = encodeURI('http://compose.mail.yahoo.com/?subject='+ $scope.lang.web_name +' - News Feed&body=' + shareContent + ' ' + $scope.SiteURL+singleActivity.ActivityURL);
            $scope.outlookShareLink = encodeURI('https://outlook.live.com/owa/#subject='+ $scope.lang.web_name +' - News Feed&body=' + shareContent + ' ' + $scope.SiteURL+singleActivity.ActivityURL + '&to=&path=%2fmail%2faction%2fcompose');
            $scope.externalShareLink = 'mailto:?body=' + shareContent + ' ' + $scope.SiteURL+singleActivity.ActivityURL + '&subject='+ $scope.lang.web_name +' - News Feed';
            $scope.ShareByEmail = {emails: '', message: shareContent, link: $scope.SiteURL+singleActivity.ActivityURL};
            //console.log('ShareByEmail ',$scope.ShareByEmail);
        });

        $scope.SubmitShareByEmail = function () {
            var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
            var val = checkstatus('ShareByEmailForm');
            if (val === false) {
                return;
            }
            var err = false;
            var emails = $scope.ShareByEmail.emails.split(',');
            var emailarray = new Array();
            if (emails.length > 0)
            {
                for (i = 0; i < emails.length; i++)
                {
                    if(re.test(emails[i].trim()))
                    {
                        emailarray.push(emails[i].trim());
                    }
                    else
                    {
                        err = true;
                    }
                }
            }

            if(err)
            {
                showResponseMessage('Please check email address','alert-danger');
                return false;
            }

            showButtonLoader('nativesendinvitaion');
            var reqData = {emails: emails, message: $scope.ShareByEmail.message, link: $scope.ShareByEmail.link};
            WallService.CallApi(reqData, 'activity/share_post_by_email').then(function (response) {
                hideButtonLoader('nativesendinvitaion');
                if (response.ResponseCode == 200) {
                    showResponseMessage(response.Message, 'alert-success');
                    $('#emailServiceModal').modal('hide');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.shareMedia = function () {
            $scope.ShareEntityType = $scope.mediaShareData.EntityType;
            var Error = false;
            var sharetype = $("#mediasharetype").val();
            var EntityUserGUID = $("#ShareEntityUserGUID").val();
            var ModuleEntityGUID = $('#MediaShareModuleEntityGUID').val();
            if ($('#media-friend-src').val() == '') {
                EntityUserGUID = '';
            }

            if (sharetype == "media-friend-wall") {
                if (EntityUserGUID == "") {
                    Error = true;
                    showResponseMessage('Please select one of your friend.', 'alert-danger');
                }
            }
            if (Error == false) {
                var PostContent = '';
                if ($('#MPCnt').length > 0 && $('#MPCnt').val().length > 0) {
                    PostContent = $('#MPCnt').val();
                }
                if ($('#MPCnt').length > 0 && $('#MPCnt').val().length > 0) {
                    PostContent = $('#MPCnt').val();
                }
                if ($('.media-own-wall').is(':visible')) {
                    var shareVisibleFor = $('#mediaShareVisibleFor').val();
                    if ($('#mediaShareComment').hasClass('on')) {
                        $('#mediaShareComment').removeClass('on');
                    }
                } else {
                    var shareVisibleFor = '';
                }
                var shareCommentSettings = $('#mediaShareCommentSettings').val();

                var reqData = {
                    EntityGUID: $scope.mediaShareData.EntityGUID,
                    ModuleEntityGUID: EntityUserGUID,
                    ModuleID: 3,
                    PostContent: PostContent,
                    Commentable: shareCommentSettings,
                    Visibility: shareVisibleFor,
                    EntityType: $scope.ShareEntityType
                };

                WallService.CallPostApi(appInfo.serviceUrl + 'activity/sharepost', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        showResponseMessage('Media has been shared successfully.', 'alert-success');
                        $('#sharemediamodal').modal('toggle');
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
                $(".ShareEntityType:checked").prop('checked', false);
            }
        }

        $scope.shareCommentToggle =function() {
            //console.log("dfgdfgdg");
            if ($('#shareComment').hasClass('on')) {
                $('#shareCommentSettings').val(0);
                $('#shareComment').removeClass('on');
                $('#shareComment span').text('Off');
                $('#shareComment .icon-on').addClass('icon-off');
                $('#shareComment .icon-on').removeClass('icon-on');
            } else {
                $('#shareCommentSettings').val(1);
                $('#shareComment').addClass('on');
                $('#shareComment span').text('On');
                $('#shareComment .icon-off').addClass('icon-on');
                $('#shareComment .icon-off').removeClass('icon-off');
            }
        }

    }

    shareCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo','Settings'];

})(app, angular);