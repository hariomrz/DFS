/*
 |--------------------------------------------------------------------------------------------------
 | Functions are used for intilizetooltip, press datepicker submit button,
 | intilize datepicker, search button click, download button press etc on document ready.
 |--------------------------------------------------------------------------------------------------
 |    Document ready start
 */
var ShowLoadingArr = [];
$(document).ready(function () {
    var ShowLoading = 0;
    $("#error_message .icon").click(function () {
        $('#error_message.notifications').removeClass('active');
    });
    $(".RadioLabel").click(function () {
        if (!$(this).hasClass("rdodisabled")) {
            var radio_id = $(this).attr("for");
            $(this).parents('.radio_parent_div').children('.css-radiobox').prop("checked", false);
            $('#' + radio_id).prop("checked", true);
        }
    });

    //For lightbox scroll
    $(window).scroll(function () {
        $("#jquery-lightbox").stop().animate({"top": ($(window).height() / 2 - $("#jquery-lightbox").height() / 2 + getScrollTop()) + "px", opacity: 1.0}, 500);
        $("#jquery-overlay").height($(document).height());
    });

    //Initilize Tooltip on Email Field
    intilizeTooltip();

    //For search data on press enter
    $("#searchField").keyup(function (e) {
        if (e.keyCode == 13)
        {
            $(this).parents('.search-field').find(".search-btn").trigger("click");
        }
    });

    $('#searchField').val('');
    var JqueryDateFormat1 = js_date
    var startweek = week_start_on;

    if (startweek > 6)
        startweek = 6;
    else if (startweek < 0)
        startweek = 0;

    //Initilize Datepicker on Datefrom Field
    $("#dateFrom").datepicker({
        dateFormat: JqueryDateFormat1,
        changeMonth: true,
        changeYear: true,
        maxDate: '0',
        firstDay: startweek,
        yearRange: '2014:+0',
        autoclose: true,
        onSelect: function (selected) {
            $("#dateTo").datepicker("option", "minDate", new Date(selected))
        }
    });


    //Initilize Datepicker on DateTo Field
    $("#dateTo").datepicker({
        dateFormat: JqueryDateFormat1,
        changeMonth: true,
        changeYear: true,
        maxDate: '0',
        firstDay: startweek,
        yearRange: '2014:+0',
        autoclose: true,
        onSelect: function (selected) {
            $("#dateFrom").datepicker("option", "maxDate", new Date(selected))
        }
    });

    if($('#dob12').length>0)
    {
        $("#dob12").datepicker({
            changeMonth: true,
            changeYear: true,
            maxDate: '0'
        });
    }

    $("#dateFrom").prop('readonly', true);
    $("#dateTo").prop('readonly', true);

    $('#ui-datepicker-div').mouseup(function (e) {
        return false;
    });

    /* Call when custom submit button click from datepicker selection */
    $('#submitBtn').click(function () {
        var startDate = $('#dateFrom').val();
        var endDate = $('#dateTo').val();
        var PageName = $('#pageName').val();
        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        var tempStartDate = new Date(startDate);
        var tempEndDate = new Date(endDate);
        
        /* Validation on Dates */
        if (tempEndDate < tempStartDate) {
            $('#date_dropdown').css("display", "block");
            $('.header-wrapper ul li.date-picker').addClass('selected');
            $('.header-wrapper ul.customView').css("display", "block");
            ShowErrorMsg("Invalid date range. Please select valid date range.");
            return false;
        } else {  /* Validation end */

            var jsondata = {"filter":{
                    "startDate": startDate ,
                    "endDate": endDate ,
                    "DateFilter":"custom",
                    "AdminLoginSessionKey": AdminLoginSessionKey 
                }
            };
            $("#SpnFrom").val('');
            $("#SpnTo").val('');
            $.ajax({
                url: base_url + "admin/users/set_session",
                data: jsondata,
                type: "POST",
                dataType: 'json',
                success: function (response) {
                    if (response.status == 1) {
                        $('#SpnFrom').val(response.startDate);
                        $('#SpnTo').val(response.endDate);
                        $("#dateFilterText").text(response.dateFilterText);

                        $("#dateFrom").val(response.startDate);
                        $("#dateTo").val(response.endDate);

                        //Code for check page and according page_name we call controller
                        switch (PageName)
                        {
                            case 'users':
                                angular.element(document.getElementById('UserListCtrl')).scope().registeredUsers();
                                break;

                            case 'user_profile':
                                angular.element(document.getElementById('userCtrl')).scope().getUser();
                                angular.element(document.getElementById('userChartCtrl')).scope().getUserLoginChart();
                                angular.element(document.getElementById('userIpsCtrl')).scope().getUserIps();
                                break;

                            case 'email_analytics':
                                angular.element(document.getElementById('emailAnalyticsCtrl')).scope().emailAnalyticsChart();
                                angular.element(document.getElementById('emailAnalyticsCtrl')).scope().emailAnalyticsLineChart();
                                angular.element(document.getElementById('emailAnalyticsCtrl')).scope().EmailAnalyticsStatistcs();
                                break;

                            case 'email_analytics_old':
                                angular.element(document.getElementById('emailAnalyticsCtrlOld')).scope().emailAnalyticsChart();
                                break;

                            case 'login_analytics':
                                angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginAnalyticsChart();
                                angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginSourceLoginChart();
                                angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginDeviceChart();
                                angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginUsernameEmailChart();
                                angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginFirstTimeChart();
                                //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginPopDaysChart();
                                //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginPopTimeChart();
                                //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginFailureChart();
                                //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginGeoChart();
                                loadLoginAnalyticsChartOnScroll();
                                break;

                            case 'signup_analytics':
                                angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupAnalyticsChart();
                                angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupSourceSignupChart();
                                angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupTypeChart();
                                angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupDeviceChart();
                                angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupVisitSignupChart();
                                //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupTimeChart();
                                //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupPopDaysChart();
                                //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupPopTimeChart();
                                //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupGeoChart();
                                loadSignupAnalyticsChartOnScroll();
                                break;

                            case 'media_analytics':
                                angular.element(document.getElementById('MediaAnalyticsCtrl')).scope().mediaAnalyticsReport();
                                angular.element(document.getElementById('MediaAnalyticsCtrl')).scope().mediaAnalytics();
                                break;

                            case 'emails':
                                angular.element(document.getElementById('EmailListCtrl')).scope().emailAnalyticData();
                                break;

                            case 'most_active_user':
                                angular.element(document.getElementById('MostActiveUserListCtrl')).scope().mostActiveUsers();
                                break;

                            case 'google_analytics':
                                angular.element(document.getElementById('googleAnalyticsCtrl')).scope().loadAllAnalyticsData();
                                loadGoogleAnalyticsChartOnScroll();
                                break;

                            case 'google_analytics_device':
                                angular.element(document.getElementById('googleAnalyticsDevicesCtrl')).scope().loadAllAnalyticsDeviceData();
                                break;

                            case 'support':
                                angular.element(document.getElementById('SupportCtrl')).scope().supportErrorLogs();
                                break;

                            case 'betainvite':
                                angular.element(document.getElementById('BetainviteCtrl')).scope().betaInvitedUsers();
                                break;
                            case 'login_dashboard':
                                angular.element(document.getElementById('loginDashboardCtrl')).scope().updateLoginDashboardAnalytics();
                                break;
                            case 'skill':
                                angular.element(document.getElementById('SkillCtrl')).scope().getPopularSkill();
                                angular.element(document.getElementById('SkillCtrl')).scope().getPendingSkill();
                                break;
                        }
                    } else {
                        return false;
                    }
                }
            });
        }
    });
    /* submitbtn code end here */

    //Call when search button is clicked and user search any phrase
    $('#searchButton').click(function () {
        if ($('#searchField').val() != '')
            angular.element(document.getElementById('UserListCtrl')).scope().registeredUsers();
    });

    //Call when user clear a search phrase
    $('#clearText').click(function () {
        if ($('#searchButton').hasClass('selected'))
        {
            $('#searchButton').removeClass('selected');
            angular.element(document.getElementById('UserListCtrl')).scope().registeredUsers();
        }

        if ($('#mediaAnalyticSearch').hasClass('selected'))
        {
            $('#mediaAnalyticSearch').removeClass('selected');
            angular.element(document.getElementById('MediaAnalyticsCtrl')).scope().mediaAnalytics();
        }
        if ($('#supportErrorSearch').hasClass('selected'))
        {
            $('#supportErrorSearch').removeClass('selected');
            angular.element(document.getElementById('SupportCtrl')).scope().supportErrorLogs();
        }
    });

    //Call when download buuton press-- submit download form
    $('#download_link').click(function () {
        $("#download_form").submit();
    });

    //Call when we mouseeneter in bradcrumb ul li
    $('.bread-crumb ul li.sub-navigation').mouseenter(function () {
        $('.bread-crumb ul li>ul').css("display", "block");
    });

    //Call when we mouseleave from bradcrumb ul li
    $('.bread-crumb ul li.sub-navigation').mouseleave(function () {
        $('.bread-crumb ul li>ul').css("display", "none");
    });
    //mouseeneter/mouseleave events end here

    //Call when we select for change geo location graph
    $('#signup_geo').click(function () {
        $('#RightFilter').val(1);
        $('#signup_geo').addClass('active');
        $('#visit_geo').removeClass('active');
        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupGeoChart();
    });
    $('#visit_geo').click(function () {
        $('#RightFilter').val(2);
        $('#visit_geo').addClass('active');
        $('#signup_geo').removeClass('active');
        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupGeoChart();
    });
    //Change geo location graph events end here

    $(document).on("click", ".removeFields", function () {
        $(this).closest('tr').remove();
    });

    $('.support-search input[type=file]').change(function () {
        var $thisTxt = $(this).parents('.browse').find('.addval');
        $thisTxt.val($(this).val().substr(12));
        var flnmarr = $(this).val().split('\\');
        var arrLen = flnmarr.length;
        $thisTxt.val(flnmarr[arrLen - 1]);
        if (!$thisTxt.val().match(/(?:csv|CSV)$/)) {
            $('#btnUploadCsv').attr('disabled', 'disabled');
            $('#dvUploadError span').html('Selected file is not a CSV file');
            $('#dvUploadError span').show();
            return false;
        }
        else {
            $('#btnUploadCsv').removeAttr('disabled');
            $('#dvUploadError span').hide();
            $('#dvUploadError span').html('');

        }
    });

    $("#btnUploadCsv").bind('click', function () {
        if ($("#csv_file").val() == "") {
            ShowErrorMsg("Please select CSV file.");
        } else {
            var data = new window.FormData($('#importcsvform')[0]);
            $.ajax({
                xhr: function () {
                    return $.ajaxSettings.xhr();
                },
                type: "POST",
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                url: base_url + 'admin/betainvite/importcsvfile',
                success: function (response) {
                    if (response.Result == 1) {
                        angular.element(document.getElementById('SendBetainviteCtrl')).scope().showImportUserList(response.UserArr);
                    } else if (response.ResponseCode == 598) {
                        PermissionError(response.Error);
                    } else {
                        ShowErrorMsg(response.Error);
                    }
                },
                error: function (error) {
                    ShowErrorMsg(error);
                }
            });
        }
    });

});

/* Document ready end */

function CheckEmailDuplicacy(Sender) {
    if ($(Sender).val() != "") {
        var isValid = true;
        if (!isEmail($(Sender).val())) {
            isValid = false;
            $(Sender).parent().next().find('span').show();
            $(Sender).parent().next().find('span').html(" Please enter correct email.");
        } else {
            isValid = true;
            $(Sender).parent().next().find('span').hide();
            $(Sender).parent().next().find('span').html("");
        }

        if (isValid == true) {
            angular.element(document.getElementById('SendBetainviteCtrl')).scope().CheckEmailExist($(Sender).val(), $(Sender));
        }


    }
    else {
        $(Sender).parent().next().find('span').hide();
        $(Sender).parent().next().find('span').html("");
    }
}

function GetValues() {
    var Isvalid = 0;
    var NoRecord = 0;
    var arr = new Array();
    $('#manualInvite tr').find('td').find('.betausername').parent().next().find('span').hide();
    $('#manualInvite tr').each(function () {
        var FirstName = "", FirstEmail = "", SecondName = "", SecondEmail = "";

        FirstName = $(this).find('td:nth-child(1)').find('.betausername').val();
        FirstEmail = $(this).find('td:nth-child(2)').find('.betauseremail').val();
        SecondName = $(this).find('td:nth-child(3)').find('.betausername').val();
        SecondEmail = $(this).find('td:nth-child(4)').find('.betauseremail').val();

        if (FirstName != "" && FirstEmail != "") {
            var userarr = new Array();
            userarr = {"name": FirstName, "email": FirstEmail};
            arr.push(userarr);
            Isvalid = 1;
            NoRecord = 1;
        } else if (FirstName == "" && FirstEmail != "") {
            $(this).find('td:nth-child(1)').find('.betausername').parent().next().find('span').show();
            $(this).find('td:nth-child(1)').find('.betausername').parent().next().find('span').html("Please enter name");
            Isvalid = 0;
            NoRecord = 1;
        }

        if (SecondName != "" && SecondEmail != "") {
            var userarr = new Array();
            userarr = {"name": SecondName, "email": SecondEmail};
            arr.push(userarr);
            Isvalid = 1;
            NoRecord = 1;
        } else if (SecondName == "" && SecondEmail != "") {
            $(this).find('td:nth-child(3)').find('.betausername').parent().next().find('span').show();
            $(this).find('td:nth-child(3)').find('.betausername').parent().next().find('span').html("Please enter name");
            Isvalid = 0;
            NoRecord = 1;
        }

        if (FirstEmail != "" && !isEmail(FirstEmail)) {
            $(this).find('td:nth-child(2)').find('.betauseremail').parent().next().find('span').show();
            $(this).find('td:nth-child(2)').find('.betauseremail').parent().next().find('span').html("Please enter correct email.");
            Isvalid = 0;
            NoRecord = 1;
            return false;
        } else if (FirstEmail == "" && FirstName != "") {
            $(this).find('td:nth-child(2)').find('.betauseremail').parent().next().find('span').show();
            $(this).find('td:nth-child(2)').find('.betauseremail').parent().next().find('span').html("Please enter email");
            Isvalid = 0;
            NoRecord = 1;
        }

        if (SecondEmail != "" && !isEmail(SecondEmail)) {
            $(this).find('td:nth-child(4)').find('.betauseremail').parent().next().find('span').show();
            $(this).find('td:nth-child(4)').find('.betauseremail').parent().next().find('span').html("Please enter correct email.");
            Isvalid = 0;
            NoRecord = 1;
            return false;
        } else if (SecondEmail == "" && SecondName != "") {
            $(this).find('td:nth-child(4)').find('.betauseremail').parent().next().find('span').show();
            $(this).find('td:nth-child(4)').find('.betauseremail').parent().next().find('span').html("Please enter email");
            Isvalid = 0;
            NoRecord = 1;
        }

    });

    $('#manualInvite tr').each(function () {
        var FEmail = "", SEmail = ""

        if ($(this).find('td:nth-child(2)').find('.betauseremail').parent().next().find('span').html() == "Email address already exists".trim())
            Isvalid = 0;

        if ($(this).find('td:nth-child(4)').find('.betauseremail').parent().next().find('span').html() == "Email address already exists".trim())
            Isvalid = 0;

    });

    if (NoRecord == 0) {
        $(".defaulttext").addClass("hide");
        ShowErrorMsg("Please enter name and email.");
    }

    // alert(arr + "    Isvalid =  " + Isvalid);

    if (arr != null && Isvalid == 1) {
        angular.element(document.getElementById('SendBetainviteCtrl')).scope().SendInvite(arr);
    }
}

function isEmail(address) {
    // Trim whitespace        
    address = address.replace(/^\s+|\s+$/g, '');
    var pattern = /^[a-zA-Z0-9-_\.]+@[a-zA-Z0-9-_\.]+?\.[a-zA-Z]{2,4}$/;
    return (pattern.test(address));
}

function HideShowTab(tabName) {
    if (tabName == "manual") {
        $('#dvManualInvite').removeClass("hide");
        $('#dvImportFile').addClass("hide");
        $('#lnkManualInvite').addClass('selected');
        $('#lnkImportFile').removeClass('selected');
    }
    else if (tabName == "importfile") {
        $('#dvManualInvite').addClass("hide");
        $('#dvImportFile').removeClass("hide");
        $('#lnkManualInvite').removeClass('selected');
        $('#lnkImportFile').addClass('selected');
    }
}



/*
 |--------------------------------------------------------------------------
 | Function is used for signout a loggedin user.
 |--------------------------------------------------------------------------
 */
function signout() {
    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    var jsondata = {"AdminLoginSessionKey": AdminLoginSessionKey, "isAdmin": 1};
    if (AdminLoginSessionKey && AdminLoginSessionKey != "") {
        $.ajax({
            url: base_url + "admin_api/login/logout",
            type: "POST",
            dataType: 'json',
            data: jsondata,
            success: function (response) {
                if (response.ResponseCode == 200) {
                    window.location = base_url + "admin";
                    $('#AdminLoginSessionKey').val("");
                } else {
                    alert('Error occured, Please try after some time.');
                    return false;
                }
            }
        });
    }
}

function updateadminusertime() {
    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    if (AdminLoginSessionKey && AdminLoginSessionKey != "") {
        var jsondata = {"AdminLoginSessionKey": AdminLoginSessionKey};
        $.ajax({
            url: base_url + "admin_api/login/updateadminusertime",
            type: "POST",
            dataType: 'json',
            data: jsondata,
            success: function (response) {
                if (response.ResponseCode != 200) {
                    alert('Error occured, Please try after some time.');
                    return false;
                }
            }
        });
    }
}

/*
 |--------------------------------------------------------------------------
 | Function is used for signout a loggedin user.
 |--------------------------------------------------------------------------
 */
function usersignout() {
    var LoginSessionKey = $('#loginSessionKey').val();
    var jsondata = {"LoginSessionKey": LoginSessionKey};
    if (LoginSessionKey && LoginSessionKey != "") {
        $.ajax({
            url: base_url + "usersite_api/signout",
            type: "POST",
            dataType: 'json',
            data: jsondata,
            success: function (response) {
                if (response.ResponseCode == 200) {
                    window.location = base_url + 'usersite/signin';
                } else {
                    alert('Error occured, Please try after some time.');
                    return false;
                }
            }
        });
    }
}

function updateloggedinusertime() {
    var LoginSessionKey = $('#loginSessionKey').val();
    if (LoginSessionKey && LoginSessionKey != "") {
        var jsondata = {"LoginSessionKey": LoginSessionKey};
        $.ajax({
            url: base_url + "usersite_api/signout/updateloggedinusertime",
            type: "POST",
            dataType: 'json',
            data: jsondata,
            success: function (response) {
                if (response.ResponseCode != 200) {
                    alert('Error occured, Please try after some time.');
                    return false;
                }
            }
        });
    }
}

/*
 |--------------------------------------------------------------------------
 | Function is used for save dates and show on top naviagation in header.
 |--------------------------------------------------------------------------
 */
function SaveDates(DateFilter) {  console.log('calling savedate')
    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    var jsondata = {
        filter: {
            DateFilter: DateFilter,
            AdminLoginSessionKey: AdminLoginSessionKey}
    }
    var PageName = $('#pageName').val();

    $.ajax({
        url: base_url + "admin/users/set_session",
        data: jsondata,
        type: "POST",
        dataType: 'json',
        success: function (response) {
            if (response.status == 1) {
                $('#SpnFrom').val(response.startDate);
                $('#SpnTo').val(response.endDate);
                $("#dateFilterText").text(response.dateFilterText);

                $("#dateFrom").val(response.startDate);
                $("#dateTo").val(response.endDate);

                $("#SpnFromCompare").val(response.startDateCompare);
                $("#SpnToCompare").val(response.endDateCompare);

                //Code for check page and according page_name we call controller
                switch (PageName)
                {
                    case 'users':
                        angular.element(document.getElementById('UserListCtrl')).scope().registeredUsers();
                        break;

                    case 'user_profile':
                        angular.element(document.getElementById('userCtrl')).scope().getUser();
                        angular.element(document.getElementById('userChartCtrl')).scope().getUserLoginChart();
                        angular.element(document.getElementById('userIpsCtrl')).scope().getUserIps();
                        break;

                    case 'email_analytics':
                        angular.element(document.getElementById('emailAnalyticsCtrl')).scope().emailAnalyticsChart();
                        angular.element(document.getElementById('emailAnalyticsCtrl')).scope().emailAnalyticsLineChart();
                        angular.element(document.getElementById('emailAnalyticsCtrl')).scope().EmailAnalyticsStatistcs();
                        break;

                    case 'email_analytics_old':
                        angular.element(document.getElementById('emailAnalyticsCtrlOld')).scope().emailAnalyticsChart();
                        break;

                    case 'login_analytics':
                        angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginAnalyticsChart();
                        angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginSourceLoginChart();
                        angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginDeviceChart();
                        angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginUsernameEmailChart();
                        angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginFirstTimeChart();
                        //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginPopDaysChart();
                        //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginPopTimeChart();
                        //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginFailureChart();
                        //angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginGeoChart();
                        loadLoginAnalyticsChartOnScroll();
                        break;

                    case 'signup_analytics':
                        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupAnalyticsChart();
                        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupSourceSignupChart();
                        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupTypeChart();
                        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupDeviceChart();
                        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupVisitSignupChart();
                        //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupTimeChart();
                        //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupPopDaysChart();
                        //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupPopTimeChart();
                        //angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupGeoChart();
                        loadSignupAnalyticsChartOnScroll();
                        break;

                    case 'media_analytics':
                        angular.element(document.getElementById('MediaAnalyticsCtrl')).scope().mediaAnalyticsReport();
                        angular.element(document.getElementById('MediaAnalyticsCtrl')).scope().mediaAnalytics();
                        break;

                    case 'emails':
                        angular.element(document.getElementById('EmailListCtrl')).scope().emailAnalyticData();
                        break;

                    case 'most_active_user':
                        angular.element(document.getElementById('MostActiveUserListCtrl')).scope().mostActiveUsers();
                        break;

                    case 'google_analytics':
                        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().loadAllAnalyticsData();
                        loadGoogleAnalyticsChartOnScroll();
                        break;
                    case 'google_analytics_dash':
                        
                       
                        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().googleAnalyticDataReport();
                        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().googleAnalyticsGeoChart1();
                        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().googleAnalyticsRegisteredUsers();
                        //angular.element(document.getElementById('googleAnalyticsCtrl')).scope().googleAnalyticDataReport();
                        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().getSummary();
                        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().topInfluencers();
                        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().topContributors();
                        //angular.element(document.getElementById('googleAnalyticsCtrl')).scope().googleAnalyticsRegisteredUsersCompare();
                        break;

                    case 'google_analytics_device':
                        angular.element(document.getElementById('googleAnalyticsDevicesCtrl')).scope().loadAllAnalyticsDeviceData();
                        break;

                    case 'support':
                        angular.element(document.getElementById('SupportCtrl')).scope().supportErrorLogs();
                        break;

                    case 'betainvite':
                        angular.element(document.getElementById('BetainviteCtrl')).scope().betaInvitedUsers();
                        break;
                    case 'login_dashboard':
                        angular.element(document.getElementById('loginDashboardCtrl')).scope().updateLoginDashboardAnalytics();
                        break;
                    case 'skill':
                        angular.element(document.getElementById('SkillCtrl')).scope().getPopularSkill();
                        angular.element(document.getElementById('SkillCtrl')).scope().getPendingSkill();
                        break;

                }
            } else {
                return false;
            }
        }
    });
}

/*
 |--------------------------------------------------------------------------
 | Function is used for set user status from slecting dropdown
 |--------------------------------------------------------------------------
 */
function SetUserStatus(UserStatus) {
    $("#hdnUserStatus").val(UserStatus);
    $('#ItemCounter').fadeOut();

    if (UserStatus == 2) {
        $("#spnUser").html(User_Index_RegisteredUsers);
        $("#spnh2").html(User_Index_RegisteredUsers);
        $("#hdnFileName").val(User_Index_RegisteredUsers);

        $("#ActionApprove").hide();
        $("#ActionSuspended").hide();
        $("#ActionUnblock").hide();
        $("#ActionDelete").show();
        $("#ActionLoginThis").show();
        $("#ActionViewProfile").show();
        $("#ActionBlock").show();
        $("#ActionCommunicate").show();
        $("#ActionSendEmail").hide();
        $("#ActionChangePwd").show();

        $("#liregister").addClass("selected");
        $("#lidelelte").removeClass("selected");
        $("#liblock").removeClass("selected");
        $("#lipending").removeClass("selected");
        $("#lisuspended").removeClass("selected");

    }
    else if (UserStatus == 3) {
        $("#spnUser").html(User_Index_DeletedUsers);
        $("#spnh2").html(User_Index_DeletedUsers);
        $("#hdnFileName").val(User_Index_DeletedUsers);

        $("#ActionApprove").hide();
        $("#ActionSuspended").hide();
        $("#ActionUnblock").hide();
        $("#ActionDelete").hide();
        $("#ActionLoginThis").hide();
        $("#ActionViewProfile").show();
        $("#ActionBlock").hide();
        $("#ActionCommunicate").show();
        $("#ActionSendEmail").hide();
        $("#ActionChangePwd").show();

        $("#liregister").removeClass("selected");
        $("#lidelelte").addClass("selected");
        $("#liblock").removeClass("selected");
        $("#lipending").removeClass("selected");
        $("#lisuspended").removeClass("selected");
    }
    else if (UserStatus == 4) {
        $("#spnUser").html(User_Index_BlockedUsers);
        $("#spnh2").html(User_Index_BlockedUsers);
        $("#hdnFileName").val(User_Index_BlockedUsers);

        $("#ActionApprove").hide();
        $("#ActionSuspended").hide();
        $("#ActionUnblock").show();
        $("#ActionDelete").show();
        $("#ActionLoginThis").hide();
        $("#ActionViewProfile").show();
        $("#ActionBlock").hide();
        $("#ActionCommunicate").show();
        $("#ActionSendEmail").hide();
        $("#ActionChangePwd").show();

        $("#liregister").removeClass("selected");
        $("#lidelelte").removeClass("selected");
        $("#liblock").addClass("selected");
        $("#lipending").removeClass("selected");
        $("#lisuspended").removeClass("selected");

    }
    else if (UserStatus == 1) {
        $("#spnUser").html(User_Index_WaitingForApproval);
        $("#spnh2").html(User_Index_WaitingForApproval);
        $("#hdnFileName").val(User_Index_WaitingForApproval);

        $("#ActionApprove").show();
        $("#ActionSuspended").hide();
        $("#ActionUnblock").hide();
        $("#ActionDelete").show();
        $("#ActionLoginThis").hide();
        $("#ActionViewProfile").show();
        $("#ActionBlock").hide();
        $("#ActionCommunicate").show();
        $("#ActionSendEmail").show();
        $("#ActionChangePwd").show();

        $("#liregister").removeClass("selected");
        $("#lidelelte").removeClass("selected");
        $("#liblock").removeClass("selected");
        $("#lipending").addClass("selected");
    }
    else if (UserStatus == 23) {
        $("#spnUser").html('Suspended Users');
        $("#spnh2").html('Suspended Users');
        $("#hdnFileName").val('Suspended Users');

        $("#ActionApprove").hide();
        $("#ActionSuspended").show();
        $("#ActionUnblock").hide();
        $("#ActionDelete").show();
        $("#ActionLoginThis").hide();
        $("#ActionViewProfile").show();
        $("#ActionBlock").hide();
        $("#ActionCommunicate").show();
        $("#ActionSendEmail").show();
        $("#ActionChangePwd").show();

        $("#liregister").removeClass("selected");
        $("#lidelelte").removeClass("selected");
        $("#liblock").removeClass("selected");
        $("#lipending").removeClass("selected");
        $("#lisuspended").addClass("selected");
    }
    $('.bread-crumb ul li>ul').hide();
    angular.element(document.getElementById('UserListCtrl')).scope().registeredUsers();
}

/*
 |--------------------------------------------------------------------------
 | Function is used for set Action Methods for Edit, Delete and View All Users.
 |--------------------------------------------------------------------------
 */
function SetStatus(Status) {

    //1-waitingforApproval,
    //2-unblock,approve,
    //3-delete,
    //4-block
    //5-Change password

    $("#hdnChangeStatus").val(Status);
    var UserId = $("#hdnUserID").val();

    switch (Status) {
        case 1:
            $("#hdnChangeStatus").val(2);
            openPopDiv('approve_popup', 'bounceInDown');
            break;
        case 23:
            $("#hdnChangeStatus").val(2);
            openPopDiv('suspended_popup', 'bounceInDown');
            break;

        case 2:
            if (Status == 2 && $("#hdnUserStatus").val() == 1) {
                openPopDiv('block_popup', 'bounceInDown');
            }

            if (Status == 2 && $("#hdnUserStatus").val() == 4) {
                openPopDiv('unblock_popup', 'bounceInDown');
            }
            break;

        case 3:
            openPopDiv('delete_popup', 'bounceInDown');
            break;

        case 4:
            openPopDiv('block_popup', 'bounceInDown');
            break;

        case 5:
            openPopDiv('change_user_password', 'bounceInDown');
            break;
    }
}

/*
 |-----------------------------------------------
 | Function is used for change status of an user.
 |-----------------------------------------------
 */
function ChangeStatus(PopupID)
{
    //1-waitingforApproval,
    //2-unblock,approve,
    //3-delete,
    //4-block
    var UserId = $("#hdnUserID").val();
    var Status = $("#hdnChangeStatus").val();

    /* Send AdminLoginSessionKey in every request */
    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    $('.button span').addClass('loading');

    var actionURL = base_url + 'admin_api/users/change_status';
    var jsondata = '{"UserId":"' + UserId + '","Status":"' + Status + '","AdminLoginSessionKey":"' + AdminLoginSessionKey + '"}';
    ShowInformationMessage('user_change_status');    
    $.ajax({
        url: actionURL,                
        data: jsondata,
        type: 'POST',
        dataType: 'json',
        contentType: "application/json",
        //data: '{"UserId":"'+UserId+'","Status":"'+Status+'","AdminLoginSessionKey":"'+AdminLoginSessionKey+'"}',
        success: function (response) {
            HideInformationMessage('user_change_status');
            if (response.ResponseCode == 200) {
                angular.element(document.getElementById('UserListCtrl')).scope().registeredUsers();
                $('.button span').removeClass('loading');
                closePopDiv(PopupID, 'bounceOutUp');
            } else if (response.ResponseCode == 598) {
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
                //Show error message
                PermissionError(response.Message);
            } else if (checkApiResponseError(response)) {
                ShowWentWrongError();
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
            } else {
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
            }
        },
        error: function () {
            ShowWentWrongError();
        }

    });
}

/*
 |-----------------------------------------------
 | Function is used for change password of a user
 | on User listings page and profile page
 |-----------------------------------------------
 */
function ChangeUserPassword(PopupID)
{
    var status = true;
    var UserID = $("#hdnUserID").val();
    var NewPassword = $("#new_password").val();
    var RetypeNewPassword = $("#retype_new_password").val();

    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    $('#button_user_pwd span').addClass('loading');

    if (NewPassword.length < 4) {
        $("#spn_new_password").html("The length must be at least 4 characters long.").show();
        $("#spn_retype_new_password").hide();
        status = false;
    }
    else if (RetypeNewPassword != NewPassword) {
        $("#spn_retype_new_password").html("Retype password does not match").show();
        $("#spn_new_password").hide();
        status = false;
    }

    if (status == true)
    {
        var actionURL = base_url + 'admin_api/user/change_user_password';        
        var jsondata = {UserID:UserID,NewPassword:NewPassword,AdminLoginSessionKey:AdminLoginSessionKey};
        ShowInformationMessage('change_user_password');
        $.ajax({
            url: actionURL,
            data: jsondata,
            type: 'post',
            dataType: 'json',
            success: function (response) {
                HideInformationMessage('change_user_password');
                if (response.ResponseCode == 200) {

                    $("#new_password").val("");
                    $("#retype_new_password").val("");
                    $("#spn_new_password").hide();
                    $("#spn_retype_new_password").hide();
                    closePopDiv(PopupID, 'bounceOutUp');

                    //Show Success message
                    $("#spn_noti").html("");
                    sucessMsz();
                    $("#spn_noti").html("  Password Change successfully.");
                } else if (response.ResponseCode == 598 || response.ResponseCode == 412) {
                    closePopDiv(PopupID, 'bounceOutUp');
                    //Show error message
                    PermissionError(response.Message);
                } else if (checkApiResponseError(response)) {
                    ShowWentWrongError();
                } else {

                    $("#new_password").val("");
                    $("#retype_new_password").val("");
                    $("#spn_new_password").hide();
                    $("#spn_retype_new_password").hide();
                    closePopDiv(PopupID, 'bounceOutUp');
                }
            },
            error: function () {
                ShowWentWrongError();
            }

        });
    }
    $('#button_user_pwd span').removeClass('loading');
    return status;


}



/*
 |-----------------------------------------
 | Function for intilize Tooltip on anchor
 |-----------------------------------------
 */
function intilizeTooltip() {
    setTimeout(function () {
        $('a[rel=tipsy]').tipsy({fade: true, gravity: 'nw'});
        $('i[rel=tipsy]').tipsy({fade: true, gravity: 's'});
        $('[rel=tipsynw]').tipsy({fade: true, gravity: 'nw'});
        $('[rel=tipsyse]').tipsy({fade: true, gravity: 'se'});
    }, 1500);
}

/*
 |-------------------------------------
 | Function for refresh captcha fields
 |-------------------------------------
 */
function refreshCaptcha()
{
    var $icon = $(this).find(".captcha-refresh"),
            animateClass = "captcha-refresh-animate";

    $icon.addClass(animateClass);
    var firstrandomnumber = Math.floor(Math.random() * 20);
    var secondrandomnumber = Math.floor(Math.random() * 90);

    $('#num1, #num2, #captcha').val('');
    $('#errCaptcha').html('');

    $('#num1').val(firstrandomnumber);
    $('#num2').val(secondrandomnumber);

    // setTimeout is to indicate some async operation
    window.setTimeout(function () {
        $icon.removeClass(animateClass);
    }, 1000);

}

function refreshCICaptcha() {
    $.ajax({
        url: base_url + "admin/login/refreshCaptcha",
        data: {},
        type: "POST",
        dataType: 'json',
        success: function (response) {
            if (response.image) {
                $("#captchaimg").html(response.image);
            }
        }
    });
}

function ShowInformationMessage(key) {
    ShowLoadingArr.push(key);
    setTimeout(function () {
        if (ShowLoadingArr.length > 0) {
            ShowWarningError(WeAreWorking);
        }
    }, WeAreWorkingTime + '000');

    setTimeout(function () {
        if (ShowLoadingArr.length > 0) {
            ShowWarningError(StillWeAreWorking);
        }
    }, StillWeAreWorkingTime + '000');

    setTimeout(function () {
        if (ShowLoadingArr.length > 0) {
            ShowWarningError(SeemsSomethingWrongRefresh);
        }
        HidewarningErrorMessage();
    }, SeemsSomethingWrongRefreshTime + '000');

}

function HideInformationMessage(key) {
    ShowLoadingArr = $.grep(ShowLoadingArr, function (value) {
        return value != key;
    });

    if (ShowLoadingArr.length < 1) {
        $('#warning_message.notifications').removeClass('active');
    }
}

function ShowWarningError(ErrorMessage) {
    $("#warning_message #spn_noti").html(" " + ErrorMessage);
    $('#warning_message.notifications').addClass('active');
}

function HidewarningErrorMessage() {
    setTimeout(function () {
        $('#warning_message.notifications').removeClass('active');
    }, 3000);
}

function ShowWentWrongError() {
    hideLoader();
    ShowLoadingArr = [];
    $("#error_message #spn_noti").html("");
    $('#error_message.notifications').addClass('active');
    $("#error_message #spn_noti").html(" " + SeemsSomethingWrong);
}

//Function for show loader on HTTP Request
function showLoader() {
    $("#divLoader").removeClass("hide");
}

//Function for hide loader on HTTP Request End
function hideLoader() {
    $("#divLoader").addClass("hide");
}

function checkApiResponseError(response) {
    if (typeof (response.ResponseCode) === 'undefined') {
        return true;
    } else {
        return false;
    }
}

// Function for show and hide the div 
// according tab pressed on user profile page
function changeTabs(tab_info)
{
    switch (tab_info)
    {
        /* Case overview */
        case 'overview':
            $('#communicate_tab, #media_tab').removeClass('selected');
            $('#' + tab_info + '_tab').addClass('selected');

            $('#communicate_div, #media_div').removeClass('show').addClass('hide');
            $('#' + tab_info + '_div').removeClass('hide').addClass('show');
            break;

            /* Case communicate */
        case 'communicate':
            $('#overview_tab, #media_tab').removeClass('selected');
            $('#' + tab_info + '_tab').addClass('selected');

            $('#overview_div, #media_div').removeClass('show').addClass('hide');
            $('#' + tab_info + '_div').removeClass('hide').addClass('show');
            break;

            /* Case media */
        case 'media':
            $('#communicate_tab, #overview_tab').removeClass('selected');
            $('#' + tab_info + '_tab').addClass('selected');

            $('#communicate_div, #overview_div').removeClass('show').addClass('hide');
            $('#' + tab_info + '_div').removeClass('hide').addClass('show');
            break;
    }
}

//Function for Draw login Chart on Profile page
function drawLoginChartDonut(responseData) {

    var data = [];
    $.each(responseData, function (i) {
        data.push({
            SourceName: responseData[i].SourceName,
            LoginCount: responseData[i].LoginCount
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'SourceName');
    tdata.addColumn('number', 'LoginCount');

    for (var i = 0; i < data.length; i++) {
        tdata.addRow([data[i].SourceName, parseInt(data[i].LoginCount)]);
    }

    var options = {
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none',
        titleTextStyle: {fontSize: 14}
    };

    var chart = new google.visualization.PieChart(document.getElementById('userLoginChart'));
    chart.draw(tdata, options);
}

//Function for Draw device Chart on Profile page
function drawDeviceChartDonut(responseData) {

    var data = [];
    $.each(responseData, function (i) {
        data.push({
            DeviceName: responseData[i].DeviceName,
            DeviceTypeCount: responseData[i].DeviceTypeCount
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'DeviceName');
    tdata.addColumn('number', 'DeviceTypeCount');

    for (var i = 0; i < data.length; i++) {
        tdata.addRow([data[i].DeviceName, parseInt(data[i].DeviceTypeCount)]);
    }

    var options = {
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none',
        titleTextStyle: {fontSize: 14}
    };

    var chart = new google.visualization.PieChart(document.getElementById('userDeviceChart'));
    chart.draw(tdata, options);
}


//Function for Show/hide options of a select box on profile page.
function showHideOption()
{
    var userStatus = $('#hdnUserStatus').val();
    var userRoleID = $("#hdnUserRoleID").val();
    if (userRoleID != "" && userRoleID != undefined) {
        userRoleID = userRoleID.split(',');
    } else {
        userRoleID = '';
    }
    switch (userStatus) {

        case '1'://Pending
            $("#csutomSelect option[value=3]").remove();//block
            $("#csutomSelect option[value=4]").remove();//Unblock
            break;

        case '2'://Registerd
            $("#csutomSelect option[value=4]").remove();//Unblock
            $("#csutomSelect option[value=7]").remove();//Approve
            break;

        case '3'://Deleted
            $("#csutomSelect option[value=2]").remove();//Delete
            $("#csutomSelect option[value=3]").remove();//block
            $("#csutomSelect option[value=4]").remove();//Unblock
            $("#csutomSelect option[value=6]").remove();//Change password
            $("#csutomSelect option[value=7]").remove();//Approve
            $("#csutomSelect option[value=8]").remove();//Login as user
            break;

        case '4'://Blocked
            $("#csutomSelect option[value=3]").remove();//block
            $("#csutomSelect option[value=7]").remove();//Approve
            break;

        default://Default
            $("#csutomSelect option[value=4]").remove();//Unblock
            $("#csutomSelect option[value=7]").remove();//Approve
    }

    if (userRoleID.indexOf('' + admin_role_id + '') > -1) {//admin user
        $("#csutomSelect option[value=2]").remove();//Delete
        $("#csutomSelect option[value=3]").remove();//block
        $("#csutomSelect option[value=4]").remove();//Unblock
        $("#csutomSelect option[value=7]").remove();//Approve
        $("#csutomSelect option[value=8]").remove();//Login as user
    }

    //Hide selectbox if user not have any permissions
    if ($('#csutomSelect').find('option').length < 2) {
        $("#csutomSelect_chosen").hide();
    }

    $('#csutomSelect').trigger('chosen:updated');
}

//Function for Change status of a user on Profile Page
function ChangeSingleUserStatus(PopupID, Status)
{
    //1-waitingforApproval,
    //2-unblock,approve,
    //3-delete,
    //4-block
    var UserID = $("#hdnUserID").val();
    var Status = Status;
    var status_action = Status;
    if (PopupID == "approve_popup")
        status_action = 1;

    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    $('.button span').addClass('loading');

    var actionURL = base_url + 'admin_api/user/change_user_status';
    var jsondata = '{"UserID":"' + UserID + '","Status":"' + Status + '","AdminLoginSessionKey":"' + AdminLoginSessionKey + '","status_action":"' + status_action + '"}';
    ShowInformationMessage('change_user_status');
    $.ajax({
        url: actionURL,
        data: jsondata,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            HideInformationMessage('change_user_status');
            if (response.ResponseCode == 200)
            {
                sucessMsz();
                if (Status == 3) {
                    $("#spn_noti").html("  Deleted successfully.");
                } else if (Status == 4) {
                    $("#spn_noti").html("  Blocked successfully.");
                }
                else if (Status == 2) {
                    $("#spn_noti").html("  Approved successfully.");
                }
                else if (Status == 2) {
                    $("#spn_noti").html("  Unblocked successfully.");
                }
                closePopDiv(PopupID, 'bounceOutUp');

                //Change URL according Status
                /*var url      = window.location.href;  
                 var value = url.substring(url.lastIndexOf('&') + 1);
                 url = url.replace(value, 'UserStatus='+Status)*/

                //Window location on modified URL
                //window.location.href = url;

                setTimeout(function () {
                    location.reload();
                }, 1500);
            } else if (response.ResponseCode == 598) {
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
                //Show error message
                PermissionError(response.Message);
            } else if (checkApiResponseError(response)) {
                ShowWentWrongError();
            } else {
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
            }
        },
        error: function () {
            ShowWentWrongError();
        }

    });
}


/*
 |-----------------------------------------------
 | Function is used for change password of admin
 | on every page from Topheader
 |-----------------------------------------------
 */
function ChangeAdminPassword(PopupID)
{
    var status = true;
    var OldPassword = $("#admin_old_password").val();
    var NewPassword = $("#admin_new_password").val();
    var RetypePassword = $("#admin_retype_password").val();
    var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    $('#button_admin_pwd span').addClass('loading');

    if (OldPassword.length < 4) {
        $("#spn_admin_old_password").html("The length must be at least 4 characters long.").show();
        $("#spn_admin_new_password").hide();
        $("#spn_admin_retype_password").hide();
        status = false;
    } else if (OldPassword == NewPassword) {
        $("#spn_admin_old_password").html("Please enter different passwords.").show();
        $("#spn_admin_new_password").hide();
        $("#spn_admin_retype_password").hide();
        status = false;
    } else if (NewPassword.length < 4) {
        $("#spn_admin_new_password").html("The length must be at least 4 characters long.").show();
        $("#spn_admin_old_password").hide();
        $("#spn_admin_retype_password").hide();
        status = false;
    }
    else if (RetypePassword != NewPassword) {
        $("#spn_admin_retype_password").html("Retype password does not match").show();
        $("#spn_admin_old_password").hide();
        $("#spn_admin_new_password").hide();
        status = false;
    }

    if (status == true)
    {
        var actionURL = base_url + 'admin_api/user/change_admin_password';
        var jsondata = {OldPassword:OldPassword,NewPassword:NewPassword,AdminLoginSessionKey:AdminLoginSessionKey};
        $.ajax({
            url: actionURL,
            data: jsondata,
            type: 'post',
            dataType: 'json',
            success: function (response) {
                if (response.ResponseCode == 200) {
                    $("#admin_old_password").val("");
                    $("#admin_new_password").val("");
                    $("#admin_retype_password").val("");
                    $("#spn_admin_old_password").hide();
                    $("#spn_admin_new_password").hide();
                    $("#spn_admin_retype_password").hide();
                    
                    closePopDiv(PopupID, 'bounceOutUp');

                    //Show Success message
                    $("#spn_noti").html("");
                    sucessMsz();
                    $("#spn_noti").html("  Password Change successfully.");
                } else {
                    $("#spn_admin_old_password").html(response.Message).show();
                    return false;
                }
            }

        });
    }
    $('#button_admin_pwd span').removeClass('loading');
    return status;
}

//Function for Draw Email Analytics Chart on EmailAnalytic Page
function drawEmailAnalyticsChart(responseData)
{
    var data = [];
    $.each(responseData, function (i) {
        data.push({
            EmailType: responseData[i].EmailType,
            CommunicationCount: responseData[i].CommunicationCount,
            EmailTypeID: responseData[i].EmailTypeID,
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'EmailType');
    tdata.addColumn('number', 'CommunicationCount');
    tdata.addColumn('number', 'EmailTypeID');

    for (var i = 0; i < data.length; i++)
    {
        tdata.addRow([data[i].EmailType, parseInt(data[i].CommunicationCount), parseInt(data[i].EmailTypeID)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.88,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: true
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('emailAnalyticsChart'));
    // The select handler. Call the chart's getSelection() method
    function selectHandler()
    {
        var selection = Piechart.getSelection();
        if (selection.length)
        {
            var value = tdata.getValue(selection[0].row, 2);
            //$("#hdnEmailType").val(2);
            window.location.href = base_url + "admin/analytics/emails?id=" + value;
        }
    }
    // Listen for the 'select' event, and call my function selectHandler() when
    // the user selects something on the chart.
    google.visualization.events.addListener(Piechart, 'select', selectHandler);
    Piechart.draw(tdata, options);
}

//Function for Draw Login Line chart on LoginAnalytic Page
function drawLoginLineChart(data, filter) {
    var TotalLogins = 0;
    data1 = [];
    $("#logincount_label").html('0');
    if (filter == 0 || filter == 1) {

        $.each(data, function (i)
        {
            data1.push({
                MonthName: data[i].MonthName,
                LoginCount: data[i].LoginCount
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'MonthName')
        tdata.addColumn('number', 'LoginCount');
        tdata.addColumn('number', 'LoginCount');
        tdata.addColumn('number', 'LoginCount');

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([data1[i].MonthName, parseInt(data1[i].LoginCount),
                parseInt(data1[i].LoginCount), parseInt(data1[i].LoginCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].LoginCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    } else if (filter == 2)
    {
        $.each(data, function (i)
        {
            data1.push({
                weeks: data[i].Weeks,
                LoginCount: data[i].LoginCount,
                WeekNumber: data[i].WeekNumber,
                Years: data[i].Years
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'weeks')
        tdata.addColumn('number', 'LoginCount');
        tdata.addColumn('number', 'LoginCount');
        tdata.addColumn('number', 'LoginCount');
        var s = "week";

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([s + " " + data1[i].WeekNumber + "(" + data1[i].Years + ")", parseInt(data1[i].LoginCount),
                parseInt(data1[i].LoginCount), parseInt(data1[i].LoginCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].LoginCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {format: '#'},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    } else {
        $.each(data, function (i)
        {
            data1.push({
                CreatedDate: data[i].CreatedDate,
                LoginCount: data[i].LoginCount
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'CreatedDate')
        tdata.addColumn('number', 'LoginCount');
        tdata.addColumn('number', 'LoginCount');
        tdata.addColumn('number', 'LoginCount');

        for (var i = 0; i < data1.length; i++)
        {
            /*tdata.addRow([$.datepicker.formatDate(JqueryDateFormat, ParseJsonDate(data1[i].CreatedDate)), data1[i].LoginCount, data1[i].LoginCount, data1[i].LoginCount]);
             TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].LoginCount);*/

            tdata.addRow([data1[i].CreatedDate, parseInt(data1[i].LoginCount),
                parseInt(data1[i].LoginCount), parseInt(data1[i].LoginCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].LoginCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {format: '#'},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    }
    $("#logincount_label").html(TotalLogins);

    var linechart = new google.visualization.LineChart(document.getElementById('loginLineChart'));
    linechart.draw(tdata, options);
}

//Function for Draw Login Source of login chart on LoginAnalytic Page
function drawSourceLoginChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            SourceName: data[i].SourceName,
            LoginCount: data[i].LoginCount
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'SourceName');
    tdata.addColumn('number', 'LoginCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].SourceName, parseInt(data1[i].LoginCount)]);
    }


    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none'
                //legend:{alignment:'center',position:'labeled',x:'150'}
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('SourceLoginChart'));
    Piechart.draw(tdata, options);
}

//Function for Draw Device chart on LoginAnalytic Page
function drawLoginDeviceChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            DeviceTypeName: data[i].DeviceTypeName,
            LoginCount: data[i].LoginCount
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'DeviceTypeName');
    tdata.addColumn('number', 'LoginCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].DeviceTypeName, parseInt(data1[i].LoginCount)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('loginDeviceChart'));
    Piechart.draw(tdata, options);
}

//Function for Draw Login Username/Email Chart on LoginAnalytic Page
function drawLoginUsernameEmailChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            UserNameVsEmail: data[i].UserNameVsEmail,
            LoginCount: data[i].LoginCount
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'UserNameVsEmail');
    tdata.addColumn('number', 'LoginCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].UserNameVsEmail, parseInt(data1[i].LoginCount)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('loginUsernameEmailChart'));
    Piechart.draw(tdata, options);
}

//Function for Draw FirstTime login Chart LoginAnalytic Page
function drawLoginFirstTimeChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            Type: data[i].Type,
            LoginCount: data[i].LoginCount
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Type');
    tdata.addColumn('number', 'LoginCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].Type, parseInt(data1[i].LoginCount)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('loginFirstTimeChart'));
    Piechart.draw(tdata, options);
}

//Function for Draw Popular Dyas login Chart LoginAnalytic Page
function drawLoginPopDaysChart(data) {

    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            WeekDayName: data[i].WeekDayName,
            LoginCount: data[i].LoginCount
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'WeekDayName');
    tdata.addColumn('number', 'LoginCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].WeekDayName, parseInt(data1[i].LoginCount)]);
    }

    var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
    var options = {
        titleTextStyle: {fontSize: 14},
        hAxis: {titleTextStyle: {color: 'Black'}},
        bar: {groupWidth: '40%'},
        colors: ['#73C8F6'],
        vAxis: {gridlines: {color: 'transparent'}, viewWindow: {min: 0}, maxValue: max, format: '#'},
        chartArea: {right: 5},
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Barchart = new google.visualization.ColumnChart(document.getElementById('loginPopDaysChart'));
    Barchart.draw(tdata, options);
}

//Function for Draw login Failure Chart LoginAnalytic Page
function drawLoginFailureChart(data) {

    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            Description: data[i].Description,
            ErrorCount: data[i].ErrorCount
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Failure');
    tdata.addColumn('number', 'Count');

    for (var i = 0; i < data1.length; i++) {
        tdata.addRow([data1[i].Description, parseInt(data1[i].ErrorCount)]);
    }

    var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
    var options = {
        titleTextStyle: {fontSize: 14},
        hAxis: {titleTextStyle: {color: 'Black'}},
        bar: {groupWidth: '20%'},
        colors: ['#73C8F6'],
        vAxis: {gridlines: {color: 'transparent'}, maxValue: max, format: '#'},
        chartArea: {right: 5},
        tooltip: {isHtml: true},
        legend: 'none'

    };

    var Barchart = new google.visualization.ColumnChart(document.getElementById('loginFailureChart'));
    Barchart.draw(tdata, options);
}

//Function for Draw login GeoLocation Chart LoginAnalytic Page
function drawLoginGeoChart(data) {

    var data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            CityName: data[i].CityStateCountry,
            LoginCount: data[i].LoginCount
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'City');
    tdata.addColumn('number', 'LoginCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].CityName, parseInt(data1[i].LoginCount)]);
    }

    var options = {
        backgroundColor: 'white',
        datalessRegionColor: 'F5F5F5',
        colorAxis: {colors: ['#3090C7', '#438D80']},
        sizeAxis: {minValue: 0, maxValue: 100},
        displayMode: 'markers'
    };

    var geochart = new google.visualization.GeoChart(document.getElementById('loginGeoChart'));
    geochart.draw(tdata, options);
}

//Function for Draw Signup Line chart on SignupAnalytic Page
function drawSignupLineChart(data, filter) {
    var TotalLogins = 0;
    data1 = [];

    if (filter == 0 || filter == 1) {

        $.each(data, function (i)
        {
            data1.push({
                MonthName: data[i].MonthName,
                SignUpCount: data[i].SignUpCount
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'MonthName')
        tdata.addColumn('number', 'SignUpCount');
        tdata.addColumn('number', 'SignUpCount');
        tdata.addColumn('number', 'SignUpCount');

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([data1[i].MonthName, parseInt(data1[i].SignUpCount),
                parseInt(data1[i].SignUpCount), parseInt(data1[i].SignUpCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].SignUpCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    } else if (filter == 2)
    {
        $.each(data, function (i)
        {
            data1.push({
                weeks: data[i].Weeks,
                SignUpCount: data[i].SignUpCount,
                WeekNumber: data[i].WeekNumber,
                Years: data[i].Years
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'weeks')
        tdata.addColumn('number', 'SignUpCount');
        tdata.addColumn('number', 'SignUpCount');
        tdata.addColumn('number', 'SignUpCount');
        var s = "week";

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([s + " " + data1[i].WeekNumber + "(" + data1[i].Years + ")", parseInt(data1[i].SignUpCount),
                parseInt(data1[i].SignUpCount), parseInt(data1[i].SignUpCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].SignUpCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {format: '#'},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    } else {
        $.each(data, function (i)
        {
            data1.push({
                CreatedDate: data[i].CreatedDate,
                SignUpCount: data[i].SignUpCount
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'CreatedDate')
        tdata.addColumn('number', 'SignUpCount');
        tdata.addColumn('number', 'SignUpCount');
        tdata.addColumn('number', 'SignUpCount');

        for (var i = 0; i < data1.length; i++)
        {
            /*tdata.addRow([$.datepicker.formatDate(JqueryDateFormat1, ParseJsonDate(data1[i].CreatedDate)),
             parseInt(data1[i].SignUpCount), parseInt(data1[i].SignUpCount), parseInt(data1[i].SignUpCount)]);
             TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].SignUpCount);*/

            tdata.addRow([data1[i].CreatedDate, parseInt(data1[i].SignUpCount),
                parseInt(data1[i].SignUpCount), parseInt(data1[i].SignUpCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].SignUpCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {format: '#'},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    }
    $("#signupcount_label").html(TotalLogins);

    var linechart = new google.visualization.LineChart(document.getElementById('signupLineChart'));
    linechart.draw(tdata, options);
}


function ParseJsonDate(dateString)
{
    var milli = dateString.replace(/\/Date\((-?\d+)\)\//, '$1');
    var date = new Date(parseInt(milli));
    return date;
}

//Function for Draw Source of signup chart on SignupAnalytic Page
function drawSourceSignupChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            SourceName: data[i].SourceName,
            SignUpCount: data[i].SignUpCount
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'SourceName');
    tdata.addColumn('number', 'SignUpCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].SourceName, parseInt(data1[i].SignUpCount)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('signupSourceSignupChart'));
    Piechart.draw(tdata, options);
}

//Function for Draw signup type chart on SignupAnalytic Page
function drawSignupTypeChart(data)
{
    var data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            Type: data[i].TypeName,
            SignUpCount: data[i].SignUpCount

        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Type');
    tdata.addColumn('number', 'SignUpCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].Type, parseInt(data1[i].SignUpCount)]);
    }
    var options = {
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none',
        titleTextStyle: {fontSize: 14}
    };

    var chart = new google.visualization.PieChart(document.getElementById('signupTypeChart'));
    chart.draw(tdata, options);
}

//Function for Draw Device chart on SignupAnalytic Page
function drawSignupDeviceChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            DeviceTypeName: data[i].DeviceTypeName,
            SignUpCount: data[i].SignUpCount
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'DeviceTypeName');
    tdata.addColumn('number', 'SignUpCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].DeviceTypeName, parseInt(data1[i].SignUpCount)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('signupDeviceChart'));
    Piechart.draw(tdata, options);
}

//Function for Draw Signup Username/Email Chart on SignupAnalytic Page
function drawSignupVisitSignupChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            Type: data[i].Type,
            Counts: data[i].Counts
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Type');
    tdata.addColumn('number', 'Counts');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].Type, parseInt(data1[i].Counts)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('signupVisitSignupChart'));
    Piechart.draw(tdata, options);
}

//Function for Draw Signup Time Chart SignpAnalytic Page
function drawSignupTimeChart(data)
{
    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            TimeRange: data[i].TimeRange,
            SignUpCount: data[i].SignUpCount
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'TimeRange');
    tdata.addColumn('number', 'SignUpCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].TimeRange, parseInt(data1[i].SignUpCount)]);
    }

    var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);

    var options = {
        titleTextStyle: {fontSize: 16},
        hAxis: {title: 'Minutes', titleTextStyle: {color: 'Black'}},
        bar: {groupWidth: '30%'},
        colors: ['#73C8F6'],
        vAxis: {gridlines: {color: 'transparent'}, viewWindow: {min: 0}, maxValue: max, format: '#'},
        legend: 'none',
        fontSize: 10,
        tooltip: {isHtml: true}
    };
    var Barchart = new google.visualization.ColumnChart(document.getElementById('signupTimeChart'));
    Barchart.draw(tdata, options);
}

//Function for Draw Popular Dyas Signup Chart SignupAnalytic Page
function drawSignupPopDaysChart(data) {

    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            WeekDayName: data[i].WeekDayName,
            SignUpCount: data[i].SignUpCount
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'WeekDayName');
    tdata.addColumn('number', 'SignUpCount');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].WeekDayName, parseInt(data1[i].SignUpCount)]);
    }

    var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
    var options = {
        titleTextStyle: {fontSize: 14},
        hAxis: {titleTextStyle: {color: 'Black'}},
        bar: {groupWidth: '40%'},
        colors: ['#73C8F6'],
        vAxis: {gridlines: {color: 'transparent'}, viewWindow: {min: 0}, maxValue: max, format: '#'},
        chartArea: {right: 5},
        tooltip: {isHtml: true},
        legend: 'none'
    };

    var Barchart = new google.visualization.ColumnChart(document.getElementById('signupPopDaysChart'));
    Barchart.draw(tdata, options);
}

//Function for Draw Signup GeoLocation Chart signupAnalytic Page
function drawSignupGeoChart(data, geotype) {

    var data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            CityName: data[i].CityStateCountry,
            VisitCount: data[i].VisitCount
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'City');
    tdata.addColumn('number', geotype);

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].CityName, parseInt(data1[i].VisitCount)]);
    }

    var options = {
        backgroundColor: 'white',
        datalessRegionColor: 'F5F5F5',
        colorAxis: {colors: ['#3090C7', '#438D80']},
        sizeAxis: {minValue: 0, maxValue: 100},
        displayMode: 'markers'
    };

    var geochart = new google.visualization.GeoChart(document.getElementById('signupGeoChart'));
    geochart.draw(tdata, options);
}

//Function for change line graph according filters
function changeLineGraph(value, pageName)
{
    $('#filter_val').val(value);

    if (pageName == 'signup_analytics')
        angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupAnalyticsChart();
    else if (pageName == 'login_analytics')
        angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginAnalyticsChart();
}

function ucwords(str) {
    return (str + '')
            .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
                return $1.toUpperCase();
            });
}

function childchecks(objCheck) {
    if ($(objCheck).attr("checked") == "checked") {
        $(objCheck).removeAttr('checked');
        $(objCheck).parent().removeClass("icon-checked");
        $(objCheck).parent().parent().removeClass("focus");
    } else {
        $(objCheck).attr('checked', 'checked');
        $(objCheck).parent().addClass("icon-checked");
        $(objCheck).parent().parent().addClass("focus");
    }
}
;

function checkPermission(objCheck) {
    var tempLevel = $(objCheck).attr("level");

    if ($(objCheck).attr("isparent") == 1) {
        var checked = $(objCheck).attr("checked") == "checked" ? true : false;
        $(".level" + tempLevel + " input[type='checkbox']").each(function () {
            if (checked == true) {
                $(this).removeAttr('checked');
                $(this).parent().removeClass("icon-checked");
                $(this).parent().parent().removeClass("focus");
            } else {
                $(this).attr('checked', 'checked');
                $(this).parent().addClass("icon-checked");
                $(this).parent().parent().addClass("focus");
            }
        });

        //new code begin
        if ($(objCheck).attr("ischild") == 1) {
            var tmpParent;
            $(".level" + tempLevel + " input[type='checkbox']").each(function () {
                if ($(this).val() == $(objCheck).attr("applicationId") && $(this).attr("isparent") == 1 && $(this).attr("ischild") == 0) {
                    tmpParent = $(objCheck).attr("id");
                }
            });
            if (tmpParent != 'undefined') {
                $("#" + tmpParent).removeAttr('checked');
            }

            var tmpFlag = true;
            $("input[type='checkbox']").each(function () {
                if ($(this).val() == $(objCheck).attr("applicationId")) {
                    if (checked == false) {
                        tmpFlag = false;
                    }
                }
            });

            if (tmpFlag && tmpParent != 'undefined') {
                $("#" + tmpParent).attr('checked', 'checked');
            }
        }
        //new code end

    }
    else {
        setTimeout(function () {
            if ($(".level" + tempLevel).find(".checkspan.icon-checked").length == $(".level" + tempLevel).find(".checkspan").length) {
                $("#parentId" + tempLevel).attr('checked', 'checked');
                $("#parentId" + tempLevel).parent().addClass("icon-checked");
                $("#parentId" + tempLevel).parent().parent().addClass("focus");
            } else {
                $("#parentId" + tempLevel).removeAttr('checked');
                $("#parentId" + tempLevel).parent().removeClass("icon-checked");
                $("#parentId" + tempLevel).parent().parent().removeClass("focus");
            }
        }, 100);
    }


    setTimeout(function () {
        if ($(".level1").find(".checkspan.icon-checked").length == $(".level1").find(".checkspan").length) {
            $("#parentId1").attr('checked', 'checked');
            $("#parentId1").parent().addClass("icon-checked");
            $("#parentId1").parent().parent().addClass("focus");
        } else {
            $("#parentId1").removeAttr('checked');
            $("#parentId1").parent().removeClass("icon-checked");
            $("#parentId1").parent().parent().removeClass("focus");
        }
    }, 100);

    // new code begin
    /*if ($(".level1 input[type='checkbox']:checked").length > 0) {
     $("#parentId1").parent().addClass("icon-checked");
     $("#parentId1").parent().parent().addClass("focus");
     }else{
     $("#parentId1").parent().removeClass("icon-checked");
     $("#parentId1").parent().parent().removeClass("focus");
     }*/
    //  new code end

}
;

function ShowSuccessMsg(successMsg) {
    $("#spn_noti").html("");
    sucessMsz();
    $("#spn_noti").html("  " + successMsg);
}

function ShowErrorMsg(errorMsg) {
    //Show error message
    $("#error_message #spn_noti").html("");
    failureMsz();
    $("#error_message #spn_noti").html("  " + errorMsg);
}

function PermissionError(errorMsg) {
    //Show error message
    $("#error_message #spn_noti").html("");
    failureMsz();
    $("#error_message #spn_noti").html("  " + errorMsg);
}

function ShowAnalyticLoader(SectionId) {
    $("#" + SectionId).html("<p class='loading_p'><img src='" + base_url + "/assets/admin/img/loader.gif'/></p>");
}

function HideAnalyticLoader(SectionId) {
    $("#" + SectionId).html("");
}


//Function for Draw google analytics Line chart on GoogleAnalytic Page
function drawGoogleAnalyticsLineChart(data, subfilter) {
    data1 = [];
    $.each(data, function (i)
    {
        data1.push({
            Name: data[i].date,
            Count: data[i].pageview
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Name')
    tdata.addColumn('number', 'Count');
    tdata.addColumn('number', 'Count');
    tdata.addColumn('number', 'Count');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].Name, parseInt(data1[i].Count),
            parseInt(data1[i].Count), parseInt(data1[i].Count)]);
    }

    var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
    var options = {
        fontName: "arial",
        fontSize: 8,
        pointSize: 9,
        vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
        hAxis: {},
        chartArea: {left: 20, top: 5, width: 2000, height: 180},
        tooltip: {isHtml: true},
        series: {
            0: {
                color: '#73C8F6',
                lineWidth: 2
            },
            1: {
                color: '#FF7A7A',
                lineWidth: 0,
                pointSize: 9
            },
            2: {
                color: '#FFBF00',
                lineWidth: 0,
                pointSize: 4
            }
        },
        legend: 'none'
    };

    var linechart = new google.visualization.LineChart(document.getElementById('googleLineChart'));
    linechart.draw(tdata, options);
}

//Function for Draw google analytics GeoLocation Chart on google analytics page
function drawGoogleAnalyticsGeoChart(data) {

    var data1 = [];
    $.each(data, function (i)
    {
        data1.push({
            Location: data[i].Location,
            Count: data[i].pageviews
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Location');
    tdata.addColumn('number', 'Count');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].Location, parseInt(data1[i].Count)]);
    }

    var options = {
        backgroundColor: 'white',
        datalessRegionColor: 'F5F5F5',
        colorAxis: {colors: ['#3090C7', '#438D80']},
        sizeAxis: {minValue: 0, maxValue: 100, minSize: 5},
        displayMode: 'markers',
    };
    tooltip: {
        trigger: 'none'
    }
    ;

    var geochart = new google.visualization.GeoChart(document.getElementById('googleAnalyticsGeoChart'));
    geochart.draw(tdata, options);
}

//Function for Draw google analytics OS Chart on google analytics devices page
function drawGoogleAnalyticsOSChart(data) {

    data1 = [];

    $.each(data, function (i)
    {
        data1.push({
            OperatingSystem: data[i].operatingsystem,
            Count: data[i].Count
        });
    });
    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Operating System');
    tdata.addColumn('number', 'Count');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].OperatingSystem, parseInt(data1[i].Count)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#0489B1', '#04B431', '#e0440e', '#DA70D6', '#98FB98', '#40E0D0', '#87CEEB', '#FFC0CB'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: {
            position: 'none',
            maxLines: 10,
            textStyle: {fontSize: 14}
        },
        sliceVisibilityThreshold: 0

    };

    var legend_html = getLegendHtml(tdata);
    $("#os_pie_chart_legend").html(legend_html);

    var Piechart = new google.visualization.PieChart(document.getElementById('googleAnalyticOSChart'));
    Piechart.draw(tdata, options);

}

//Function for Draw google analytics Browser Chart on google analytics devices page
function drawGoogleAnalyticsBrowserChart(data) {

    var data1 = [];
    $.each(data, function (i)
    {
        data1.push({
            Browser: data[i].Browser,
            Count: data[i].Count
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'Browser');
    tdata.addColumn('number', 'Count');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].Browser, parseInt(data1[i].Count)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#0489B1', '#04B431', '#e0440e', '#DA70D6', '#98FB98', '#40E0D0', '#87CEEB', '#FFC0CB'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: {
            position: 'none',
            maxLines: 10,
            textStyle: {fontSize: 14}
        }

    };

    var legend_html = getLegendHtml(tdata);
    $("#browser_chart_legend").html(legend_html);

    var geochart = new google.visualization.PieChart(document.getElementById('googleAnalyticsBrowserChart'));
    geochart.draw(tdata, options);
}

//Function for Draw google analytics Browser Chart on google analytics devices page
function drawGoogleAnalyticsDeviceTypeChart(data) {

    var data1 = [];
    $.each(data, function (i)
    {
        data1.push({
            DeviceType: data[i].Device,
            Count: data[i].Count
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'DeviceType');
    tdata.addColumn('number', 'Count');

    for (var i = 0; i < data1.length; i++)
    {
        tdata.addRow([data1[i].DeviceType, parseInt(data1[i].Count)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.87,
        colors: ['#0489B1', '#04B431', '#e0440e', '#DA70D6', '#98FB98', '#40E0D0', '#87CEEB', '#FFC0CB'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: {
            position: 'none',
            maxLines: 10,
            textStyle: {fontSize: 14}
        }

    };

    var legend_html = getLegendHtml(tdata);
    $("#device_chart_legend").html(legend_html);

    var geochart = new google.visualization.PieChart(document.getElementById('googleAnalyticsDeviceTypeChart'));
    geochart.draw(tdata, options);
}

function getLegendHtml(tdata) {
    var total = 0;
    var legend_html = '<ul>';
    var colors = ['#0489B1', '#04B431', '#e0440e', '#DA70D6', '#98FB98', '#40E0D0', '#87CEEB', '#FFC0CB', '#0489B1', '#04B431', '#e0440e', '#DA70D6', '#98FB98', '#40E0D0', '#87CEEB', '#FFC0CB', '#0489B1', '#04B431', '#e0440e', '#DA70D6', '#98FB98', '#40E0D0', '#87CEEB', '#FFC0CB', '#0489B1', '#04B431', '#e0440e', '#DA70D6'];
    for (var i = 0; i < tdata.getNumberOfRows(); i++) {

        total += tdata.getValue(i, 1);

        // get the data
        var label = tdata.getValue(i, 0);
        var value = tdata.getValue(i, 1);
        var percent = Math.ceil(1000 * value / total) / 10;

        // This will create legend list for the display
        legend_html += '<li><div class="legendMarker" style="background-color:' + colors[i] + ';"></div> ' + label + '</li>';
    }
    legend_html += '</ul>';

    return legend_html;
}

//Function for Draw Email Analytics Chart on EmailAnalytic Page
function drawEmailAnalyticsPieChart(responseData)
{
    var data = [];
    $.each(responseData, function (i) {
        data.push({
            EmailType: responseData[i].EmailType,
            MessageCount: responseData[i].MessageCount,
            EmailTypeID: responseData[i].EmailTypeID,
        });
    });

    var tdata = new google.visualization.DataTable();
    tdata.addColumn('string', 'EmailType');
    tdata.addColumn('number', 'MessageCount');
    tdata.addColumn('number', 'EmailTypeID');

    for (var i = 0; i < data.length; i++)
    {
        tdata.addRow([data[i].EmailType, parseInt(data[i].MessageCount), parseInt(data[i].EmailTypeID)]);
    }

    var options = {
        titleTextStyle: {fontSize: 14},
        pieHole: 0.88,
        colors: ['#FF7A7A', '#3F9FFF', '#FFBB3F', '#77EE9A'],
        slices: [{offset: 0}],
        pieSliceBorderColor: '#E1E1DB',
        tooltip: {isHtml: true},
        legend: {position: 'none'}
    };

    var Piechart = new google.visualization.PieChart(document.getElementById('emailAnalyticsPieChart'));
    // The select handler. Call the chart's getSelection() method
    function selectHandler()
    {
        var selection = Piechart.getSelection();
        if (selection.length)
        {
            var value = tdata.getValue(selection[0].row, 2);
            //$("#hdnEmailType").val(2);
            //window.location.href = base_url + "admin/analytics/emails?id="+value;
            angular.element(document.getElementById('emailAnalyticsCtrl')).scope().LoadEmailAnalyticsChart(value);
        }
    }
    // Listen for the 'select' event, and call my function selectHandler() when
    // the user selects something on the chart.
    google.visualization.events.addListener(Piechart, 'select', selectHandler);
    Piechart.draw(tdata, options);
}

//Function for Draw Login Line chart on LoginAnalytic Page
function drawEmailAnalyticsLineChart(data, filter) {
    var TotalLogins = 0;
    data1 = [];
    $("#logincount_label").html('0');
    if (filter == 0 || filter == 1) {

        $.each(data, function (i)
        {
            data1.push({
                MonthName: data[i].MonthName,
                MessageCount: data[i].MessageCount
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'MonthName')
        tdata.addColumn('number', 'MessageCount');
        tdata.addColumn('number', 'MessageCount');
        tdata.addColumn('number', 'MessageCount');

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([data1[i].MonthName, parseInt(data1[i].MessageCount),
                parseInt(data1[i].MessageCount), parseInt(data1[i].MessageCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].MessageCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    } else if (filter == 2)
    {
        $.each(data, function (i)
        {
            data1.push({
                weeks: data[i].Weeks,
                MessageCount: data[i].MessageCount,
                WeekNumber: data[i].WeekNumber,
                Years: data[i].Years
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'weeks')
        tdata.addColumn('number', 'MessageCount');
        tdata.addColumn('number', 'MessageCount');
        tdata.addColumn('number', 'MessageCount');
        var s = "week";

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([s + " " + data1[i].WeekNumber + "(" + data1[i].Years + ")", parseInt(data1[i].MessageCount),
                parseInt(data1[i].MessageCount), parseInt(data1[i].MessageCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].MessageCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {format: '#'},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    } else {
        $.each(data, function (i)
        {
            data1.push({
                CreatedDate: data[i].CreatedDate,
                MessageCount: data[i].MessageCount
            });
        });

        var tdata = new google.visualization.DataTable();
        tdata.addColumn('string', 'CreatedDate')
        tdata.addColumn('number', 'MessageCount');
        tdata.addColumn('number', 'MessageCount');
        tdata.addColumn('number', 'MessageCount');

        for (var i = 0; i < data1.length; i++)
        {
            tdata.addRow([data1[i].CreatedDate, parseInt(data1[i].MessageCount),
                parseInt(data1[i].MessageCount), parseInt(data1[i].MessageCount)]);
            TotalLogins = parseInt(TotalLogins) + parseInt(data1[i].MessageCount);
        }

        var max = (tdata.getColumnRange(1).max < 3 ? (parseInt(tdata.getColumnRange(1).max) + 5) : tdata.getColumnRange(1).max);
        var options = {
            fontName: "arial",
            fontSize: 8,
            pointSize: 9,
            vAxis: {gridlines: {color: 'grey'}, title: '', viewWindow: {min: 0}, maxValue: max, format: '#'},
            hAxis: {format: '#'},
            chartArea: {left: 20, top: 5, width: 2000, height: 180},
            tooltip: {isHtml: true},
            series: {
                0: {
                    color: '#73C8F6',
                    lineWidth: 2
                },
                1: {
                    color: '#FF7A7A',
                    lineWidth: 0,
                    pointSize: 9
                },
                2: {
                    color: '#FFBF00',
                    lineWidth: 0,
                    pointSize: 4
                }
            },
            legend: 'none'
        };
    }
    $("#logincount_label").html(TotalLogins);

    var linechart = new google.visualization.LineChart(document.getElementById('emailAnalyticsLineChart'));
    linechart.draw(tdata, options);
}

function openSearch() {
    if ($('.openClose').hasClass('open')) {
        return true;
    } else {
        $('.openClose').addClass('open');
        $('.search-block').show();
        return false;
    }
}

$('#clearSearch').click(function () {
    $('.openClose').removeClass('open');
    $('.search-block').hide();
});
function init_datepicker()
{
    $("#date").datepicker({
        dateFormat: js_date,
        changeMonth: true,
        changeYear: true,
        maxDate: '0',
        firstDay: week_start_on,
        yearRange: '2014:+0',
        onSelect: function (selected) {
            //$("#dateTo").datepicker("option", "minDate", new Date(selected))
        }
    });
}