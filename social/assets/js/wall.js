if(typeof IsNewsFeed=='undefined')
{
  var IsNewsFeed = 0;
}

function makeEditable(cls)
{
    setTimeout(function () {
        $('.' + cls).attr('contentEditable', true);
        if (!$('.' + cls).hasClass('editable'))
        {
            $('.' + cls).addClass('editable');
        }
        $('.' + cls).blur();
        $('.' + cls).focus();
    }, 50);
}

$(document).ready(function () {
    $(document).dblclick(function () {
        $('.atc_title,.atc_desc').removeClass('editable');
        $('.atc_title,.atc_desc').attr('contentEditable', false);
    });
});

function urlify(text)
{
    var link = '';
    replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
    replacedText = text.replace(replacePattern1, function ($1) {
        link = $1;
    });

    if (!link)
    {
        //console.log(link);
        replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
        replacedText = text.replace(replacePattern2, function ($1) {
            link = $1;
            //console.log(link);
        });
    }
    return link;
}

function resetAllFilter(id) {
    if (id == 'userAct') {
        $('#IsMediaExists').val(2);
        $('#datepicker').val('');
        $('#datepicker2').val('');
        $('#AsOwner').val(0);
    }
    if (id == 'typeAct') {
        $('#datepicker').val('');
        $('#datepicker2').val('');
        $('#PostOwner').val('');
        $('#PostOwnerSearch').val('');
        $('#AsOwner').val(0);
    }
    if (id == 'dateAct') {
        $('#IsMediaExists').val(2);
        $('#PostOwner').val('');
        $('#PostOwnerSearch').val('');
        $('#AsOwner').val(0);
    }
    if (id == 'pageAct') {
        $('#IsMediaExists').val(2);
        $('#datepicker').val('');
        $('#datepicker2').val('');
        $('#PostOwner').val('');
        $('#PostOwnerSearch').val('');
    }
}

function clearReminderFilter(d) {
    angular.element(document.getElementById('WallPostCtrl')).scope().clearReminderFilter(d);
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function clearAllFilter(v) {
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
    angular.element(document.getElementById('WallPostCtrl')).scope().resetWallPageNo();
    $('.loader-fad,.loader-view').show();
    $('.filterApply').addClass('hide');
    if (v !== 1) {
        $('#ActivityFilterType').val(0);
        angular.element(document.getElementById('WallPostCtrl')).scope().suggestPage = [];
        //angular.element(document.getElementById('WallPostCtrl')).scope().clearReminderFilter();
    }
    if (v == 1)
    {
        $('.filterApply').removeClass('hide');
    }

    angular.element(document.getElementById('WallPostCtrl')).scope().IsActiveFilter = false ;
    angular.element(document.getElementById('WallPostCtrl')).scope().startExecution();
    angular.element(document.getElementById('WallPostCtrl')).scope().hideLoader();
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function filterPContent(PContent,Type) {
    if(Type=='Poll')
    {
       jQuery('#wallpostform .textntags-beautifier div strong').each(function (e) {
        var details = $('#wallpostform .textntags-beautifier div strong:eq(' + e + ') span').attr('class');
        var module_id = details.split('-')[1];
        var module_entity_id = details.split('-')[2];
        var name = $('#wallpostform .textntags-beautifier div strong:eq(' + e + ') span').text();
        PContent = PContent.replace('<strong><span class="user-' + module_id + '-' + module_entity_id + '">' + name + '</span></strong>', '{{' + name + ':' + module_entity_id + ':' + module_id + '}}'); 
        });
    }
    else
    {
        /*$(PContent).find('[data-tag="user-tag"]').each(function (e) {
            var details = $('[data-tag="user-tag"]:eq(' + e + ')').attr('class');
            var module_id = details.split('-')[1];
            var module_entity_id = details.split('-')[2];
            var name = $('[data-tag="user-tag"]:eq(' + e + ')').text();
            //PContent = PContent.replace($('[data-tag="user-tag"]:eq(' + e + ')').html(), '{{' + name + ':' + module_entity_id + ':' + module_id + '}}');
            PContent = PContent.replace('<span contenteditable="false" data-tag="user-tag" class="user-' + module_id + '-' + module_entity_id + '">'+name+'</span>', '{{' + name + ':' + module_entity_id + ':' + module_id + '}}'); 
        });*/
 
        var taggedContentRegex = /<span.+?(?=class=[\"\']user-(\d+)-(\d+)[\"\'])[^>]+>((?:.(?!\<\/span\>))*.)<\/span>/gi,
        matchedTags = PContent.match(taggedContentRegex),
        matchedInfo,
        contentToFilter = PContent;
        while ((matchedInfo = taggedContentRegex.exec(contentToFilter))) {
            var stringToBePlaced = '{{' + matchedInfo[3] + ':' + matchedInfo[2] + ':' + matchedInfo[1] + '}}',
            capturedTag = matchedInfo[0];
            capturedTag = capturedTag.replace(matchedInfo[3], stringToBePlaced); //{{Abhishek Gouhar:209:3}}
            PContent = PContent.replace(matchedInfo[0], capturedTag); //{{Abhishek Gouhar:209:3}}
        }
    }
    return PContent;
}

function parseTaggedForPreview(PContent) {
    var taggedContentRegex = /<span.+?(?=class=[\"\']user-(\d+)-(\d+)[\"\'])[^>]+>((?:.(?!\<\/span\>))*.)<\/span>/gi,
    matchedTags = PContent.match(taggedContentRegex),
    matchedInfo,
    contentToFilter = PContent;
    while ((matchedInfo = taggedContentRegex.exec(contentToFilter))) {
        var stringToBePlaced = '<a href="javascript:void(0);" class="tagged-person">' + matchedInfo[3] + '</a>',//<a href="javascript:void(0);" class="tagged-person">Abhijeet test</a>
        capturedTag = matchedInfo[0];
        capturedTag = capturedTag.replace(matchedInfo[3], stringToBePlaced); //{{Abhishek Gouhar:209:3}}
        PContent = PContent.replace(matchedInfo[0], capturedTag); //{{Abhishek Gouhar:209:3}}
    }
    return PContent;
}

function applyPageSearchFilter(pageGUID) {
    $('#AsOwner').val(1);
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function applyActivitySearchFilter(filter) {
    $('#ActivityFilterType').val(filter);
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function applySearchFilter(type, val) {
    if (type == 'Datepicker') {
    }
    else
    {
        if (type !== 'Fav') {
            //$('#IsMediaExists').val(2);
            $('#PostOwner').val('');
            $('#ActivityFilterType').val(0);
        }

        if (type !== 'Datepicker') {
            //$('#datepicker').val('');
            //$('#datepicker2').val('');
        }
        if (type == 'IsMediaExists') {
            $('#IsMediaExists').val(val);
        }
        else
        {
            $('#IsMediaExists').val(2);
        }
        if (type == 'Fav') {
            $('#mytabs li').removeClass('active');
            if (val == '1') {
                $('.fav-post').addClass('active');
            } else {
                $('.all-post').addClass('active');
            }
            $('#ActivityFilterType').val(val);
        }
    }
    if (type == 'Flg') {
        $('#mytabs li').removeClass('active');
        if (val == '2') {
            $('.flg-post').addClass('active');
        } else {
            $('.all-post').addClass('active');
        }
        $('#ActivityFilterType').val(val);
    }
    if (type == 'Reported') {
        $('#ActivityFilterType').val(2);
        $('.filters-search > div').addClass('hide');
    }
    if ($('#IsPoll').length > 0)
    {
        if ($('#datepicker2').val() != '' || $('#datepicker').val() != '')
        {
            PollScope = angular.element('#PollCtrl').scope();
            if($('#datepicker').val()!='')
            {
                PollScope.poll_date_search_term = $('#datepicker').val() ;
            }
            if($('#datepicker2').val()!='')
            {
                PollScope.poll_date_search_term = $('#datepicker2').val() ;
            }
            
            //PollScope.enable_postdate_filter = false;
            PollScope.filter_post_date = true;
        }
        if ($('#datepicker2').val() != '' && $('#datepicker').val() != '')
        {
            PollScope.poll_date_search_term = $('#datepicker').val() + ' - ' + $('#datepicker2').val();
            PollScope.enable_postdate_filter = false;
        }
        

    }
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function checkRemainingMedia() {
    var medialiLength = $('#listingmedia li.media-item').length;
    var fileliLength = $('.files-attached-in-post > li').length;
    if (medialiLength < 2) {
        $('.all-con').hide();
    }
    $('.capt-num').html(liLength);
    if ( (medialiLength === 0 ) && ( fileliLength === 0 ) ) {
        $('.wall-content .upload-media').hide();
        $('.wall-content .same-caption').hide();
    } else if( medialiLength === 0 ) {
        $('.wall-content .same-caption').hide();
    }
    
    if (medialiLength === 1) {
        $('#mc-default').attr('placeholder', 'Say something about this picture');
    }
    $('.mc').hide();
    $('#mc-default').show();
    showHidePhotoVideoIcon();
}

function toggleMediaCaption(id) {
    $('.mc').hide();
    $('#mc-' + id).show();
    $('.selected-capt').removeClass('selected');
    $('#m-' + id).parent('div').parent('li').addClass('selected');
}

function showHidePhotoVideoIcon() {

    if ($('.video-itm').length > 0) {
        $('#addVideo').hide();
        $('#addMedia').hide();
    } else if ($('.photo-itm').length > 0) {
        $('#addVideo').hide();
        $('#addMedia').show();
    } else {
        $('#addVideo').show();
        $('#addMedia').show();
    }
}

if(IsAdminView=='0')
{   
    $(function () {

        var VideoIDs = [];

        /*new qq.FineUploaderBasic({
            multiple: true,
            autoUpload: true,
            title: "Upload Videos",
            button: $("#UploadVideo")[0],
            request: {
                endpoint: site_url + "api/upload_video",
                params: {
                    DeviceType: 'Native'
                },
                customHeaders: Custom_Headers
            },
            validation: {
                allowedExtensions: ['mp4', 'MP4'],
                sizeLimit: 31457280 // 4mb
            },
            callbacks: {
                onUpload: function (id, fileName) {
                },
                onProgress: function (id, fileName, loaded, total) {
                    $('.error').html('');
                },
                onComplete: function (id, fileName, responseJSON) {
                    VideoIDs.push(responseJSON.VideoID);
                    $('.videos').append(responseJSON.Data.file_name + '<br>');
                },
                onSubmit: function (id, fileName) {
                },
                onValidate: function (b) {
                    $('.error').html('Please make sure that file should be MP4 and less than 30 MB');
                },
                onError: function () {
                }
            }
        });*/
        $('#SavePlaylist').click(function () {
            $.post('save_playlist.php', {
                VideoIDs: VideoIDs,
                Playlist: $('#Playlist').val()
            }, function (r) {
                window.top.location = 'list.php';
            });
        });

        $(document).delegate('#commentablePost', 'click', function ()
        {
            if ($('#comments_settings').val() == 0)
            {
                $('#comments_settings').val(1);
            } else
            {
                $('#comments_settings').val(0);
            }
        });
    });
}


$.fn.isOnScreen = function () {
    var win = $(window);
    var viewport = {
        top: win.scrollTop(),
        left: win.scrollLeft()
    };
    viewport.right = viewport.left + win.width();
    viewport.bottom = viewport.top + win.height();

    var bounds = this.offset();
    bounds.right = bounds.left + this.outerWidth();
    bounds.bottom = bounds.top + this.outerHeight();

    return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));

};

$(document).ready(function () {
    /*$(window).scroll(function(){
     $('.inview').each(function(k,v){
     if($('.inview:eq('+k+')').isOnScreen()){
     var EntityGUID = $('.inview:eq('+k+')').attr('id');
     EntityGUID = EntityGUID.split('act-')[1];
     angular.element(document.getElementById('WallPostCtrl')).scope().viewActivity(EntityGUID);
     }
     });
     });*/

    $(window).scroll(function () {
        clearTimeout($.data(this, 'scrollTimer'));
        clearTimeout($.data(this, 'scrollTimerMedia'));
        $.data(this, 'scrollTimer', setTimeout(function () {
            $('.activitywrapper').each(function (k, v) {
                if ($('.activitywrapper:eq(' + k + ')').isOnScreen()) {
                    var EntityGUID = $('.activitywrapper:eq(' + k + ')').data('guid');
                    if(EntityGUID) {
                        EntityGUID = EntityGUID.split('act-')[1];
                        angular.element(document.getElementById('WallPostCtrl')).scope().viewActivity(EntityGUID);
                    }
                    
                }
            });
        }, 5000));
    });

    /*var WallPostCtrl;
    $(window).scroll(function () {
        
        if(!WallPostCtrl)
        {
            WallPostCtrl = angular.element(document.getElementById('WallPostCtrl')).scope();
        }

        clearTimeout($.data(this, 'scrollTimer1'));
        clearTimeout($.data(this, 'scrollTimerMedia'));
        $.data(this, 'scrollTimer1', setTimeout(function () {
            if($('.videoout').length==0)
            {
                

                if(!WallPostCtrl.videoList || !WallPostCtrl.videoList.length) {
                    WallPostCtrl.videoList = $('.activitywrapper:has("video")')
                }
                
                WallPostCtrl.videoList.each(function (k, v) {
                    var EntityGUID = $('.activitywrapper:has("video"):eq(' + k + ')').data('guid');
                    if(EntityGUID) {
                        EntityGUID = EntityGUID.split('act-')[1];
                        if ($('.activitywrapper:has("video"):eq(' + k + ')').isOnScreen()) {
                            WallPostCtrl.play_video_activity(EntityGUID);
                        }
                        else
                        {
                            WallPostCtrl.pause_video_activity(EntityGUID);
                        }
                    }
                });
            }
        }, 500));
    });*/
});

if(IsAdminView=='0')
{
    $(document).ready(function () {
        $("#liveFeeds").mCustomScrollbar({
            callbacks: {
                onTotalScroll: function () {
                    angular.element(document.getElementById('WallPostCtrl')).scope().getLiveFeed();
                },
                onTotalScrollOffset: 1000
            }
        });
    });
}

function showMore(e) {
    $(e).parent('span').parent('p').children('span.show-more').show();
    $(e).parent('span').parent('p').children('span.show-less').hide();
}

function showLess(e) {
    $(e).parent('span').parent('p').children('span.show-more').hide();
    $(e).parent('span').parent('p').children('span.show-less').show();
    var showLessScroll = $(e).parent('span').parent('p').children('span.show-less').offset().top;
    if ($(e).parent('span').parent('p').parent('div').hasClass('tagging')) {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 100;
    } else {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 150;
    }
    $('html,body').animate({
        scrollTop: showLessScroll
    });
}

function showMoreComment(e) { 
    $(e).closest('.feed-content').find('span.show-more').show();
    $(e).closest('.feed-content').find('span.show-less').hide();

    $(e).closest('.commented-content').find('span.show-more').show();
    $(e).closest('.commented-content').find('span.show-less').hide();
    
    $(e).closest('.post-desctiption').find('span.show-more').show();
    $(e).closest('.post-desctiption').find('span.show-less').hide();

    $(e).closest('.share-content').find('span.show-more').show();
    $(e).closest('.share-content').find('span.show-less').hide();
    $(e).hide();
    $(e).parents('.show-less').siblings('.show-more').show();
    $(e).parents('.show-less').hide();
    
  
/*
    $(e).parent('span,p').parent('span,p').children('span.show-more').show();
    $(e).parent('span,p').parent('span,p').children('span.show-less').hide();
    
    $(e).parent('span,p').parent('span').parent('span').children('span.show-more').show();
    $(e).parent('span,p').parent('span').parent('span').children('span.show-less').hide();

    $(e).parent('span').children('span.show-more').show();
    $(e).parent('span').children('span.show-less').hide(); */

    /*$(e).closest('.feed-content').parent('p,div').children('span.show-more').show();
    $(e).closest('.feed-content').parent('p,div').children('span.show-less').hide();*/
}

function showLessComment(e) {
    $(e).parent('span').parent('span').children('span.show-more').hide();
    $(e).parent('span').parent('span').children('span.show-less').show();
    
    $(e).parent('span').parent('span').find('span.show-less a').show();
    
    var showLessScroll = $(e).parent('span').parent('span').children('span.show-less').offset().top;
    if ($(e).parent('span').parent('span').parent('div').hasClass('tagging')) {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 100;
    } else {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 150;
    }
    $('html,body').animate({
        scrollTop: showLessScroll
    });
}

function removeTags(txt) {
  if (txt) {
    var rex = /(<([^>]+)>)/ig;
    return txt.replace(rex, "");
  } else {
    return txt;
  }
}

function srchFilter(e) {
    var searchText = $('#srch-filters').val();
    if (e.which == 13) {
        angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
        angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter=true;
        if(IsAdminView == '1')
        {
            angular.element(document.getElementById('UserListCtrl')).scope().keywordLabelName=searchText;
        }
        else
        {
            angular.element(document.getElementById('UserProfileCtrl')).scope().keywordLabelName=searchText;
        }
        $('#BtnSrch i').addClass('icon-removeclose');
    } else {
        /*if($('#BtnSrch i').hasClass('icon-removeclose') && searchText == ""){          
         $('#BtnSrch i').removeClass('icon-removeclose');
         }*/
    }
}

function showIconCamera(id) {
    var id = id.split('m-')[1];
    $('#act-' + id + ' .attach-on-comment').show();
    setTimeout(function () {
        $('#cmt-' + id).trigger('blur');
    }, 200);
}


function prepareReminderData(ReminderData, IsLocal) {
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


function destroyCalendar(ActivityGUID) {
    $("#reminderCal" + ActivityGUID).datepicker().datepicker('destroy');
}

function reduce_arr(array)
{
    return array.reduce(function (o, v, i) {
        o[i] = v;
        return o;
    }, {});
}

if(IsNewsFeed=='1')
{
    function updateCheckBoxStatus(e) {
        checkTillDate(e);
    }

    function checkTillDate(e) {
        if (!$(e).parent('span').children('input[type="checkbox"]').is(':checked')) {
            var parent = $(e).parent('span').parent('h3').parent('div');
            var currentTime = new Date();
            var year = currentTime.getFullYear();
            var month = currentTime.getMonth() + 1;
            $('div.till-date-mon').children('select').val(month);
            $('div.till-date-mon').children('select').trigger("chosen:updated");
            $('div.till-date-year').children('select').val(year);
            $('div.till-date-year').children('select').trigger("chosen:updated");

            $('div.till-date-mon select').attr('disabled', true).trigger("chosen:updated");
            $('div.till-date-year select').attr('disabled', true).trigger("chosen:updated");
        } else {
            $('div.till-date-mon select').removeAttr('disabled').trigger("chosen:updated");
            $('div.till-date-year select').removeAttr('disabled').trigger("chosen:updated");
        }
    }
}

$(document).ready(function(){
    $('.close-filter').on('click',function(){
        var wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
        if(wall_scope.getFilterVal())
        {
            wall_scope.ResetFilter();
        }
        
    });
});

function add_to_tag(e)
{
    // var AllowedPostType = $(e).attr('allowposttype');
    // var ModuleID = $(e).attr('moduleid');
    // var ModuleEntityGUID = $(e).attr('moduleentityguid');
    // var ProfilePicture = $(e).attr('profilepicture');
    // var name = $(e).attr('name');
    // var type = 'User';
    // if(ModuleID == '3')
    // {
    //     type = 'User';
    // }
    // else
    // {
    //     type = 'Group';
    // }
    // input = {AllowedPostType:AllowedPostType,GroupDescription:'',ModuleEntityGUID:ModuleEntityGUID,ModuleID:ModuleID,Type:type,name:name,ProfilePicture:ProfilePicture};
    var wall_scope = angular.element('#WallPostCtrl').scope();
    // wall_scope.wallTagAdded(input);
    wall_scope.parseTaggedInfo();
    wall_scope.$apply();
}