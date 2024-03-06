$(document).ready(function() {

    $('.close-alert').click(function() {
        clearTimeout(timeoutVar);
        var cls = $(this).attr('rel');
        $('.message-popup').slideUp();
        $('.message-popup').removeClass(cls);
        $('.popup-message').html('');
    });
    $(document).on("click", ".uploadfrom", function() {
        if (this.value == 'fromcomputer') {
            $("#uploadfromcomputer").show();
            $("#embeddedmedia").hide();
            $("#IdEmbedCode").val("");
            $("#IdUploadType").val("fromcomputer");
        } else {
            $("#uploadfromcomputer").hide();
            $("#embeddedmedia").show();
            $("#id_course_media").val("");
            $("#id_img_course_media").attr("src", "");
            $("#IdUploadType").val("embed");
        }
    });
    $(document).on("click", ".AddMedia", function() {
        $(".close").trigger("click");
    });

    /*---------------------------Ajax global handler to set custom headers--------------------------------*/
    $(document).ajaxSend(function(event, jqXHR, ajaxOptions) {
        if(IsAdminView == '1')
        {
            jqXHR.setRequestHeader('AdminLoginSessionKey', AdminLoginSessionKey);
        }
        else
        {
            jqXHR.setRequestHeader(Auth_Key, LoginSessionKey);
        }
        jqXHR.setRequestHeader('Accept-Language', accept_language);
    });
    /*----------------------------------------------------------------------------------------------------*/

    
    autoCompleteUser();

    $('#PostOwnerSearch').next('.input-group-btn').children('.btn-search').click(function() {
        if ($('#PostOwnerSearch').next('.input-group-btn').children('.btn-search').children('i').hasClass('icon-removeclose')) {
            $('#PostOwnerSearch').next('.input-group-btn').children('.btn-search').children('i').removeClass('icon-removeclose');
            $('#PostOwner').val('');
            $('#PostOwnerSearch').val('');
            angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
        }
    });

    $('#PostOwnerSearch2').next('.input-group-btn').children('.btn-search').click(function() {
        if ($('#PostOwnerSearch2').next('.input-group-btn').children('.btn-search').children('i').hasClass('icon-removeclose')) {
            $('#PostOwnerSearch2').next('.input-group-btn').children('.btn-search').children('i').removeClass('icon-removeclose');
            $('#PostOwner').val('');
            $('#PostOwnerSearch2').val('');
            angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
        }
    });

    if ($("#PostOwnerSearch").length > 0) {
        $("#PostOwnerSearch").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: base_url + 'api/activity/autoSuggestPostOwner',
                    data: { term: request.term, AllActivity: $('#AllActivity').val(), ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val(),FilterType:$('#ActivityFilterType').val() },
                    dataType: "json",
                    success: function(data) {
                        if (data.ResponseCode == 502) {
                            data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                        }
                        if (data.Data.length <= 0) {
                            data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                        }
                        response(data.Data);
                    }
                });
            },
            select: function(event, ui) {
                if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                    $('#PostOwner').val(ui.item.UserGUID);
                    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
                    $('#PostOwnerSearch').next('.input-group-btn').children('.btn-search').children('i').addClass('icon-removeclose');
                    if ($('#PollCtrl').length > 0) {
                        //$('#PostOwner').val('');
                        $('#PostOwnerSearch').val('');
                        poll_scope = angular.element(document.getElementById('PollCtrl')).scope();
                        poll_scope.poll_search_term = ui.item.label;
                        poll_scope.enable_user_filter = false;
                        poll_scope.filter_user = true;
                    }
                }
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            item.label = item.FirstName + " " + item.LastName;
            item.id = item.UserGUID;
            if (item.id !== undefined) {
                item.value = item.FirstName + " " + item.LastName;
            }
            return $("<li>")
                .data("item.autocomplete", item)
                .append("<a>" + item.label + "</a>")
                .appendTo(ul);
        };
    }

    if ($("#PostOwnerSearch2").length > 0) {
        $("#PostOwnerSearch2").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: base_url + 'api/activity/autoSuggestPostOwner',
                    data: { term: request.term, AllActivity: $('#AllActivity').val(), ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val() },
                    dataType: "json",
                    success: function(data) {
                        if (data.ResponseCode == 502) {
                            data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                        }
                        if (data.Data.length <= 0) {
                            data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                        }
                        response(data.Data);
                    }
                });
            },
            select: function(event, ui) {
                if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                    $('#PostOwner').val(ui.item.UserGUID);
                    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
                    $('#PostOwnerSearch2').next('.input-group-btn').children('.btn-search').children('i').addClass('icon-removeclose');
                }
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            item.label = item.FirstName + " " + item.LastName;
            item.id = item.UserGUID;
            if (item.id !== undefined) {
                item.value = item.FirstName + " " + item.LastName;
            }
            return $("<li>")
                .data("item.autocomplete", item)
                .append("<a>" + item.label + "</a>")
                .appendTo(ul);
        };
    }

    if ($("#reportedUser").length > 0) {
        $("#reportedUser").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: base_url + 'api/activity/blocked_user_list',
                    data: { term: request.term },
                    dataType: "json",
                    success: function(data) {
                        if (data.ResponseCode == 502) {
                            data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                        }
                        if (data.Data.length <= 0) {
                            data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                        }
                        response(data.Data);
                    }
                });
            },
            select: function(event, ui) {
                if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                    $('#PostOwner').val(ui.item.UserGUID);
                    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
                }
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            //.data( "item.autocomplete", item )
            return $("<li>")
                .data("item.autocomplete", item)
                .append("<a>" + item.FirstName + " " + item.LastName + "</a>")
                .appendTo(ul);
        };
    }

    $('#reportAbuse .close').click(function() {
        $('.reportAbuseDesc:checkbox').removeAttr('checked');
    });

    $('#userCtrlToggle').on('click', function() {
        if (!$('#userCtrlContent').is(":visible")) {
            $('#userCtrlContent').addClass('show');
        } else {
            $('#userCtrlContent').removeClass('show');
        }
    });

    $('#headerNavToggle').on('click', function() {
        if (!$('#headerNavContent').hasClass("hidden-sm hidden-xs")) {
            $('#headerNavContent').addClass('hidden-sm hidden-xs');
            $(this).removeClass("active");
        } else {
            $('#headerNavContent').removeClass('hidden-sm hidden-xs');
            $(this).addClass("active");
        }
    });

    $(document).click(function(e) {
        var target = e.target;
        if (!$(target).is('#notifToggle') && !$(target).parents().is('#notifContent')) {
            $('#notifContent').removeClass('show');
            $('#notifToggle').removeClass('active');
        }
        if (!$(target).is('#headerNavToggle') && !$(target).parents().is('#headerNavContent')) {
            $('#headerNavContent').addClass('hidden-sm hidden-xs');
            $('#headerNavToggle').removeClass("active");
        }
    });
});

function flagValSet(e, type) {
    $('.reportAbuseDesc').prop('checked',false);
    if (type == 'GROUP' || type == 'USER') {
        typeID = e;
    } else {
        typeID = $(e).attr('id').split('tid-')[1];
    }
    $('.flagType').val(type);
    $('.typeID').val(typeID);
}

function autoCompleteUser() {
    //console.log(1);
    if ($("#addUsers").length > 0) {
        $('#addUsers').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=' + $('#selectedUsers').val(),
                    data: { term: request.term },
                    dataType: "json",
                    success: function(data) {
                        if (data.ResponseCode == 502) {
                            data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                        }
                        if (data.Data.length <= 0) {
                            data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                        }
                        response(data.Data);
                    }
                });
            },
            select: function(event, ui) {
                if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                    $('.controls-to').html('<input type="text" placeholder="Username" id="addUsers" class="form-control">');
                    setTimeout(function() { autoCompleteUser(); }, 500);
                    if ($('.add-users').html().trim() == 'No Selection' || $('.add-users').html().length == 0) {
                        $('.add-users').html('<span id="user-' + ui.item.UserGUID + '" class="add-user-id">' + ui.item.FirstName + ' <a href="javascript:void(0);" onclick="removeParentSpanEle($(this).parent(\'span\').attr(\'id\'));"><i class="icon-remove"></i></a> </span>');
                        $('#selectedUsers').val(ui.item.UserGUID);
                    } else {
                        $('.add-users').append('<span id="user-' + ui.item.UserGUID + '" class="add-user-id">' + ui.item.FirstName + ' <a href="javascript:void(0);" onclick="removeParentSpanEle($(this).parent(\'span\').attr(\'id\'));"><i class="icon-remove"></i></a> </span>');
                        $('#selectedUsers').val($('#selectedUsers').val() + ',' + ui.item.UserGUID);
                    }
                }
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            return $("<li>")
                .data("item.autocomplete", item)
                .append("<a>" + item.FirstName + " " + item.LastName + "</a>")
                .appendTo(ul);
        };
    }
}

if ($("#friend-src").length > 0) {
    $('#friend-src').autocomplete({
        //appendTo: "#FriendSearchResult",
        source: function(request, response) {
            $.ajax({
                url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=',
                data: { term: request.term },
                dataType: "json",
                success: function(data) {
                    $('#ShareEntityUserGUID').val('');
                    if (data.ResponseCode == 502) {
                        data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                    }
                    if (data.Data.length <= 0) {
                        data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                    }
                    response(data.Data);
                }
            });
        },
        select: function(event, ui) {
            if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                $('#ShareEntityUserGUID').val(ui.item.UserGUID);

            }
        }
    }).data("ui-autocomplete")._renderItem = function(ul, item) {
        item.value = item.label = item.FirstName + " " + item.LastName;
        item.id = item.UserGUID;
        return $("<li>")
            .data("item.autocomplete", item)
            .append("<a>" + item.label + "</a>")
            .appendTo(ul);
    };
}

if ($("#media-friend-src").length > 0) {
    $('#media-friend-src').autocomplete({
        //appendTo: "#FriendSearchResult",
        source: function(request, response) {
            $.ajax({
                url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=',
                data: { term: request.term },
                dataType: "json",
                success: function(data) {
                    $('#MediaShareEntityUserGUID').val('');
                    if (data.ResponseCode == 502) {
                        data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                    }
                    if (data.Data.length <= 0) {
                        data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                    }
                    response(data.Data);
                }
            });
        },
        select: function(event, ui) {
            if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                $('#MediaShareEntityUserGUID').val(ui.item.UserGUID);

            }
        }
    }).data("ui-autocomplete")._renderItem = function(ul, item) {
        item.value = item.label = item.FirstName + " " + item.LastName;
        item.id = item.UserGUID;
        return $("<li>")
            .data("item.autocomplete", item)
            .append("<a>" + item.label + "</a>")
            .appendTo(ul);
    };
}


function removeParentSpanEle(UserGuID) {
    var selectedUsers = '';
    var i = 1;
    UserGuID = UserGuID.split('user-')[1];
    $('.add-user-id').each(function(k, v) {
        var UserId = $('.add-user-id:eq(' + k + ')').attr('id').split('user-')[1];
        if (UserGuID !== UserId) {
            if (i == 1) {
                selectedUsers += UserId;
            } else {
                selectedUsers += ',' + UserId;
            }
            i++;
        }
    });
    $('#selectedUsers').val(selectedUsers);
    setTimeout(function() {
        $('#user-' + UserGuID).remove();
        autoCompleteUser();
        if ($('.add-user-id').length == 0) {
            angular.element(document.getElementById('messageSectionCtrl')).scope().changeIsNewMessage(1);
        }
    }, 500);
}

/*var unloadEvent = function (e) {
        $('#GroupListPageNo').val(1);
    };
    window.addEventListener("beforeunload", unloadEvent);*/

function firstKeyOfObj(obj) {
    for (var a in obj) return a;
}

var timeoutVar;

function showResponseMessage(message, cls) {
    if (message == '') {
        return false;
    }
    //$('.message-popup > .content-alert').removeClass('alert-danger');
    //$('.message-popup > .content-alert').removeClass('alert-success');
    $('.message-popup > .content-alert').addClass(cls);
    $('#alertmessage').html(message); 
    $('.message-popup').show();
    $('.close-alert').attr('rel', cls);

    timeoutVar = setTimeout(function() { 
        setTimeout(function() {
            $('.message-popup').hide();
            $('#alertmessage').html('');
        }, 1000);
    }, 4000);
}


function showConfirmCallback(val) {
    //console.log(val);
}

function showConfirmBox(title, message, callback) {
    $('body').append('<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="ConfirmModal" class="modal fade conFirmpopup"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="myModalLabel" class="modal-title">' + title + '</h4></div><div class="modal-body"><div class="modal-innercontent"><p>' + message + '</p></div></div><div class="modal-footer"><div class="pull-right wall-btns"><button id="cancelBtn" class="btn btn-default">No</button><button id="confirmBtn" class="btn btn-primary">Yes</button></div></div></div></div></div>');


    $('#ConfirmModal').modal('show');

    $('#ConfirmModal').on('hidden.bs.modal', function () {
//      $('div.modal.conFirmpopup#ConfirmModal').remove();
//      $('div.modal-backdrop.fade.modal-stack').remove();
    });

    $('#confirmBtn').click(function() {
        callback(true);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });
    $('#cancelBtn').click(function() {
        $('#ConfirmModal').modal('hide');
        callback(false);
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);

    });
    setTimeout(function() {

        $('#confirmBtn').focus();
    }, 500);
    //console.log(confirmButton);
}
function showInputConfirmBox(title, message, inputField, callback) {    
    var str = '<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="ConfirmModal" class="modal fade conFirmpopup"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="myModalLabel" class="modal-title">' + title + '</h4></div><div class="modal-body"><div class="modal-innercontent"><p>' + message + '</p>';
    if(inputField == 0) {
        str = str+'<div class="form-group"><label for="" class="label"><b>Reason</b> </label><textarea class="form-control" name="reason" id="reason" placeholder="Reason" maxlength="250"></textarea></div>';
    }
    str = str+'</div></div><div class="modal-footer"><div class="pull-right wall-btns"><button id="cancelBtn" class="btn btn-default">No</button><button id="confirmBtn" class="btn btn-primary">Yes</button></div></div></div></div>';

    $('body').append(str);

    $('#ConfirmModal').modal('show');

    $('#ConfirmModal').on('hidden.bs.modal', function () {
//      $('div.modal.conFirmpopup#ConfirmModal').remove();
//      $('div.modal-backdrop.fade.modal-stack').remove();
    });

    $('#confirmBtn').click(function() {
        callback(true);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });
    $('#cancelBtn').click(function() {
        $('#ConfirmModal').modal('hide');
        callback(false);
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);

    });
    setTimeout(function() {

        $('#confirmBtn').focus();
    }, 500);
    //console.log(confirmButton);
}

function showConfirmBoxLogin(title, message, callback) {
    $('body').append('<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="ConfirmModal" class="modal fade conFirmpopup"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="myModalLabel" class="modal-title">' + title + '</h4></div><div class="modal-body"><div class="modal-innercontent"><p>' + message + '</p></div></div><div class="modal-footer"><div class="pull-right wall-btns"><button id="cancelBtn" class="btn btn-default">Cancel</button><button id="confirmBtn" class="btn btn-primary">Ok</button></div></div></div></div></div>');


    $('#ConfirmModal').modal('show');

    $('#confirmBtn').click(function() {
        callback(true);
        window.top.location = base_url+'signin';
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });
    $('#cancelBtn').click(function() {
        callback(false);
        //$('.modal').modal('hide');
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);

    });
    setTimeout(function() {

        $('#confirmBtn').focus();
    }, 500);
    //console.log(confirmButton);
}

function showConfirmBoxNotifications(title, message, callback) {
    $('body').append('<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="ConfirmModal" class="modal fade conFirmpopup"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="myModalLabel" class="modal-title">' + title + '</h4></div><div class="modal-body"><div class="modal-innercontent"><p>' + message + '</p></div></div><div class="modal-footer"><div class="pull-right wall-btns"><a href="javascript:void(0);" id="cancelBtn" class="btn btn-default">Cancel</a><button id="confirmBtn" class="btn btn-primary">TURN OFF</button></div></div></div></div></div>');

    $('#ConfirmModal').modal('show');
    $('#confirmBtn').click(function() {
        callback(true);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });
    $('#cancelBtn').click(function() {
        callback(false);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });

    //console.log(confirmButton);
}

function showFBLikeConfirmBox(title, message, callback) {
    $('body').append('<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="ConfirmModal" class="modal fade conFirmpopup"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="myModalLabel" class="modal-title">' + title + '</h4></div><div class="modal-body"><div class="modal-innercontent"><p>' + message + '</p></div></div><div class="modal-footer"><div class="pull-right wall-btns"><button id="cancelBtn" class="btn btn-default">Don\'t Send</button><button id="confirmBtn" class="btn btn-primary">Stay on Message</button></div></div></div></div></div>');

    $('#ConfirmModal').modal('show');
    $('#confirmBtn').click(function() {
        callback(true);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });
    $('#cancelBtn').click(function() {
        callback(false);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });

    //console.log(confirmButton);
}

function showDelConfirmBox(title, message, callback) {

    $('body').append('<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="ConfirmModal" class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="myModalLabel" class="modal-title">' + title + '</h4></div><div class="modal-body"><div class="modal-innercontent"><p>' + message + '</p></div></div><div class="modal-footer"><div class="pull-right wall-btns"><button id="cancelBtn" class="btn btn-default">CANCEL</button><button id="confirmBtn" class="btn btn-primary">DELETE</button></div></div></div></div></div>');

    $('#ConfirmModal').modal('show');
    $('#confirmBtn').click(function() {
        callback(true);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });
    $('#cancelBtn').click(function() {
        callback(false);
        $('#ConfirmModal').modal('hide');
        setTimeout(function() {
            $('#ConfirmModal').remove();
        }, 500);
    });

    //console.log(confirmButton);
}

function showAlertBox(title, message, callback) {
    $('body').append('<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="AlertModal" class="modal fade conFirmpopup"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="myModalLabel" class="modal-title">' + title + '</h4></div><div class="modal-body"><div class="modal-innercontent"><p>' + message + '</p></div></div><div class="modal-footer"><div class="pull-right wall-btns"><button id="okBtn" class="btn btn-primary">Ok</button></div></div></div></div></div>');

    $('#AlertModal').modal('show');
    $('#okBtn').click(function() {
        callback(true);
        $('#AlertModal').modal('hide');
        setTimeout(function() {
            $('#AlertModal').remove();
        }, 500);
    });
}

/*Function for Library Tab Active*/
function setGroupTab(tabIndex) {
    var allLinks = $('.secondary-nav li');
    allLinks.removeClass('active');
    $(allLinks[tabIndex - 1]).addClass('active');
}

function changeLanguage(lang) {
    $.post(base_url + 'ajax/change_language', { lang: lang, UserGUID: LoggedInUserGUID }, function() {
        window.location.reload();
    });
}

function changeAutoplay(autoplay)
{
    autoplay = autoplay.split('string:');
    autoplay = autoplay[1];
    $.post(base_url + 'ajax/change_autoplay', { autoplay: autoplay, UserGUID: LoggedInUserGUID }, function() {
        window.location.reload();
    });
}

if(IsAdminView=='0')
{
    $(document).ready(function() {
        $(window).resize();
        $('.imgFill').imagefill();
    });
}

function showButtonLoader(buttonId) {
    $("#" + buttonId).attr('disabled', 'disabled');
    $("#" + buttonId).addClass('loader-btn');
    $("#" + buttonId + " .btn-loader").show();

}

function hideButtonLoader(buttonId) {
    $("#" + buttonId).removeAttr('disabled');
    $("#" + buttonId + " .btn-loader").hide();
    $("#" + buttonId).removeClass('loader-btn');
}

function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}

function showProfileLoader() {
    if ($('.profile-picture-loader').hasClass('hide')) {
        $('.profile-picture-loader').removeClass('hide');
    }
}

function hideProfileLoader() {
    if (!$('.profile-picture-loader').hasClass('hide')) {
        $('.profile-picture-loader').addClass('hide');
    }
}

function redirectUserName(URL) {
    window.top.location = base_url + URL;
}

function taggedPerson() {
    if ($('.tagged-person-click').length > 0) {
        $('.tagged-person-click').each(function() {
            var attr = $(this).attr('onclick');
            if (typeof attr !== 'undefined') {
                attr = attr.replace("<span class='highlightedText'>", '');
                attr = attr.replace("</span>", '');
                $(this).attr('onclick', attr);
            }
        });
    }
}

$(document).ready(function() {
    //$('#image-cropper').cropit();
    $('.select-image-btn').click(function() {
        $('#profile-picture input').trigger('click');
    });
});

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

function redirectToSearch(e) {
    var keyword = encodeURIComponent($.trim($(e).val()));
    if (keyword.length >= 1) {
        setTimeout(function() {
            window.top.location = base_url + 'search/top/' + keyword;
        }, 200);
    }
}

$(document).ready(function() {
    $('#search-input input').keyup(function(e) {
        if (e.which == 13) {
            setTimeout(function(){
                redirectToSearch('#search-input input');
            },2000);
        }
    });

    $('.remove-confirm-strip').click(function() {
        $('body').attr('data-type', '');
    });
});

function isVideo(url, callback) {
    return new Promise(function(res, rej) {
        var video = document.createElement('video');
        video.preload = 'metadata';
        video.onloadedmetadata = function(evt) {
            callback(!!(video.videoHeight && video.videoWidth));
            video.src = null;
        };
        video.src = url;
    });
}

function changePopupShare(cls) {
    if (cls == 'own-wall' || cls == 'media-own-wall') {
        $('.about-name').addClass('hide');
        $('.own-wall-settings').removeClass('hide');
    } else {
        $('.about-name').removeClass('hide');
        $('.own-wall-settings').addClass('hide');
        $('#mediaShareVisibleFor').val(1);
        $('#mediaShareCommentSettings').val(0);
        $('#shareVisibleFor').val(1);
        $('#shareCommentSettings').val(0);
    }
    setTimeout(function() {
        //$('#sharemediamodal').find('imgFill').imagefill();
    }, 100);
}

function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}

function convertTo24Hour(time) {
    var hours = parseInt(time.substr(0, 2));
    if (time.indexOf(' AM') != -1 && hours == 12) {
        time = time.replace('12', '0');
    }
    if (time.indexOf(' PM') != -1 && hours < 12) {
        time = time.replace(hours, (hours + 12));
    }
    return time.replace(/( AM| PM)/, '');
}

function popupCenter(url, title, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
} 

$(window).on('keyup.media-popup-content', function(e) {
    if ($('.media-popup-content').is(':visible')) {

        if ((e.keyCode || e.which) == 37) {
            $('.icon-th-prev').trigger('click');
        }

        if ((e.keyCode || e.which) == 39) {
            $('.icon-th-next').trigger('click');
        }
    }
});
function flagValSet(e, type) {
    $('.reportAbuseDesc').prop('checked',false);
    if (type == 'GROUP' || type == 'USER' || type == 'RATING') {
        typeID = e;
    } 
    if(type == 'User')
    {
        typeID = $(e).attr('id').split('tid-user-')[1];
    }
    else 
    {                    
        typeID = $(e).attr('id').split('tid-')[1];
    }
    $('.flagType').val(type);
    $('.typeID').val(typeID);
}

function checkValDatepicker() {
    var dp1 = $('#datepicker').val();
    var dp2 = $('#datepicker2').val();
    applySearchFilter('Datepicker', '0');
    var user_profile_ctrl = angular.element(document.getElementById('UserProfileCtrl')).scope();
    user_profile_ctrl.Filter.timeLabelName = '';
    user_profile_ctrl.Filter.IsSetFilter = true;
    if(dp1!=='' && dp2=='')
    {
        user_profile_ctrl.Filter.timeLabelName = dp1;
    }
    if(dp1=='' && dp2!=='')
    {
        user_profile_ctrl.Filter.timeLabelName = dp2;   
    }
    if(dp1!=='' && dp2!=='')
    {
        if(dp1==dp2)
        {
            user_profile_ctrl.Filter.timeLabelName = dp1;
        }
        else
        {
            user_profile_ctrl.Filter.timeLabelName = dp1+' - '+dp2;
        }
    }
}

$(document).ready(function () {
    window.onbeforeunload = function(e) {
        angular.element($('#UserProfileCtrl')).scope().callBeforeUnload();
    };
 });
 function initSliderWelcome(){
    setTimeout(function()  {
      $('#similar-feed-copy').slick({
      infinite: false,
      slidesToShow: 1,
      dots: true,
      arrows: false,
      appendDots: $('.sliderdots')
      });
    },800);
  }