var no_record = 'No Record found';
app.controller('flagCtrl', function ($scope, $rootScope, flag_service, $window) {

    // Initialize scope variables
    $scope.totalRecord = 0;
    $scope.filteredTodos = [],
            $scope.currentPage = 1,
            $scope.numPerPage = pagination,
            $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.searchKey = '';
    $scope.numPerPage = 100,
            $scope.selectedFlaggedUser = {};
    $scope.selectedAchievement = {};
    $scope.EntityType = '';
    $scope.pageType = '2';

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    if ($("#hdnFlagType").val() == "UserFlag") {
        $scope.EntityType = 'User';
    } else if ($("#hdnFlagType").val() == "PageFlag") {
        $scope.EntityType = 'Page';
    } else if ($("#hdnFlagType").val() == "ActivityFlag") {
        $scope.EntityType = 'ACTIVITY';
    }


    $scope.FlaggedUserList = function () {

        intilizeTooltip();
        showLoader();
        $scope.selectedSport = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.searchKey = '';
        if ($('#searchFlaggedUserField').val())
        {
            $scope.searchKey = $.trim($('#searchFlaggedUserField').val());
            $('#searchFlaggedUserButton').addClass('selected');
        }


        /* Here we check if current page is not equal 1 then set new value for var begin */

        var begins = '';

        if ($scope.currentPage == 1)
        {
            //Make request data parameter for university listing
            begins = 0;//$scope.currentPage;
        } else
        {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /*if($("#hdnFlagType").val() == "UserFlag"){
         $scope.EntityType = 'User';
         }else if($("#hdnFlagType").val() == "PageFlag"){
         $scope.EntityType = 'Page';
         }*/

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            UserStatus: $scope.userStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            EntityType: $scope.EntityType,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey,
            pageType: $scope.pageType,
        }



        var reqUrl = reqData[1]
        //Call getUniversitylist in services.js file
        flag_service.FlaggedUserList(reqData).then(function (response) {
            $scope.FlaggedUserlistData = [];
            //If no. of records greater then 0 then show
            $('.download_link,#selectallbox').show();
            $('#noresult_td').remove();
            $('.simple-pagination').show();

            //$scope.showButtonGroup = false;
            $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");

            if (response.ResponseCode == 200) {
                $scope.noOfObj = response.Data.total_records;
                $scope.total_pages = $scope.total_records = $scope.noOfObj;
                //$("#divLoader").addClass("hide");
                hideLoader();
                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#flagCtrl table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>' + no_record + '</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }

                //Push data into Controller in view file
                $scope.FlaggedUserlistData.push({ObjPages: response.Data.results});

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                $('.download_link,#selectallbox').hide();
                $('#flagCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>' + response.Message + '</p></div></td></tr>');
                $('.simple-pagination').hide();
            }

            //hideLoader();            

        }), function (error) {
            hideLoader();
        }
    };

    /**
     * SHow selected css
     * @param {type} sport
     * @returns {undefined}
     */
    $scope.isSelected = function (FlaggedUserData) {
        if (FlaggedUserData.FlagID in $scope.selectedFlaggedUser) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;
        }
    };

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
                EntityType: $scope.EntityType,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey: $scope.AdminLoginSessionKey
            }
            $scope.FlaggedUserList();
        }
    };

    /**
     * Set li selected
     * @param {type} user
     * @returns {undefined}
     */
    $scope.selectFlaggedUser = function (FlaggedUserData) {
        if (FlaggedUserData.FlagID in $scope.selectedFlaggedUser) {
            delete $scope.selectedFlaggedUser[FlaggedUserData.FlagID];
        } else {
            $scope.selectedFlaggedUser[FlaggedUserData.FlagID] = FlaggedUserData;
        }
        if (Object.keys($scope.selectedFlaggedUser).length > 0) {
            setTimeout(function () {
                $scope.globalChecked == true;
            }, 1);
            $('#ItemCounter').fadeIn();
        } else {
            $scope.showButtonGroup = false;
            $('#ItemCounter').fadeOut();
        }

        setTimeout(function () {
            if ($(".universities tr.selected").length == $scope.FlaggedUserlistData[0].ObjUsers.length) {
                setTimeout(function () {
                    $scope.globalChecked = true;
                }, 1);
                $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
            } else {
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
            }
        }, 1);

        var ItemCount = Object.keys($scope.selectedFlaggedUser).length;
        var txtCount = ItemsSelected;
        if (ItemCount == 1)
            txtCount = ItemSelected;
        $('#ItemCounter .counter').html(ItemCount + txtCount);
        //console.log($scope.selectedUsers);
    }

    //Get no. of pages for data
    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };

    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {
        begins = (($scope.currentPage - 1) * $scope.numPerPage)
        reqData = {
            Begin: begins,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            SortBy: $scope.sort_by,
            EntityType: $scope.EntityType,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }

        $scope.FlaggedUserList();
        /*if($("#hdnSportPageType").val() == "Position"){
         $scope.SportPositionlist();
         }else if($("#hdnSportPageType").val() == "Sport"){
         $scope.Sportlist();
         }else if($("#hdnSportPageType").val() == "Skill"){
         $scope.SportSkillslist();
         }*/
    });

    $scope.UserLoginKeyword = '';

    //Function for set EntityID
    $scope.set_user_flagged_data = function (flaggedData) {
        
        if ($("#hdnFlagType").val() == "UserFlag") {
            console.log(flaggedData)
            $scope.FlaggedUser = flaggedData.Username;
            $scope.FlaggedUserProfileURL = flaggedData.ProfileURL;
            $scope.FlaggedUserGUID = flaggedData.UserGUID;
            $scope.EntityGUID = flaggedData.UserGUID;

        } else if ($("#hdnFlagType").val() == "PageFlag") {
            $scope.EntityID = flaggedData.EntityID;
            $scope.FlaggedUser = flaggedData.Title;
            $scope.FlaggedPageGUID = flaggedData.page_guid;
            $scope.FlaggedPageURL = flaggedData.PageURL;
            $scope.EntityGUID = flaggedData.page_guid;
        } else if ($("#hdnFlagType").val() == "ActivityFlag") {
            $scope.EntityID = flaggedData.EntityID;
            $scope.FlaggedUser = flaggedData.activity_post;
            $scope.FlaggedActivityGUID = flaggedData.activity_guid;
            $scope.EntityGUID = flaggedData.activity_guid;
        }

        $scope.EntityID = flaggedData.EntityID;
    }

    $scope.FlagList = [];
    $scope.view_flag_details = function () {
        $scope.FlagList = [];
        openPopDiv('flag_popup');
        var reqData = {
            EntityType: $scope.EntityType,
            EntityID: $scope.EntityID,
            EntityGUID: $scope.EntityGUID,
            AdminLoginSessionKey: $scope.AdminLoginSessionKey,
        }
        flag_service.view_flag_details(reqData).then(function (response) {
            openPopDiv('flag_popup');
            if (response.ResponseCode == 200) {
                $scope.FlagList = response.Data;
            } else {
                ShowErrorMsg(response.Message, 'danger');
            }
        });
    }

    $scope.view_flagged_user = function (username, user_guid) {

        var url = base_url + username + '/about';
        //var url = base_url+'users/'+user_guid;
        window.open(url, '_blank');
    }

    $scope.view_flagged_page = function (pagetitle, page_guid) {
        var url = base_url + 'page/' + page_guid;
        window.open(url, '_blank');
    }




    $scope.change_flagged_user_status = function (PopupID, Status) {
        var AthleticAchievementTypeID = $('#hdnAthleticAchievementTypeID').val();
        var Status = Status;
        var status_action = Status;

        if ($scope.EntityType == 'User')
        {
            $scope.EntityGUID = $scope.FlaggedUserGUID;
        } else if ($scope.EntityType == 'Page')
        {
            $scope.EntityGUID = $scope.FlaggedPageGUID;
        } else if ($scope.EntityType == 'ACTIVITY')
        {
            $scope.EntityGUID = $scope.FlaggedActivityGUID;
        }

        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $('.button span').addClass('loading');

        var reqData = {
            Status: Status,
            AdminLoginSessionKey: AdminLoginSessionKey,
            status_action: status_action,
            EntityGUID: $scope.EntityGUID,
            EntityType: $scope.EntityType

        };

        flag_service.change_flagged_user_status(reqData).then(function (response) {

            if (response.ResponseCode == 200)
            {
                var message = '';
                /*if (Status == 3) {
                 message = "Athletic Type Deleted successfully.";
                 }*/
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
                ShowSuccessMsg(response.Message);
                $scope.FlaggedUserList();

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
        }), function (error) {
            ShowWentWrongError();
        }
    };
    
    $scope.bloack_flagged_user = function (PopupID, Status) {
        var AthleticAchievementTypeID = $('#hdnAthleticAchievementTypeID').val();
        var Status = Status;
        var status_action = Status;

        if ($scope.EntityType == 'User')
        {
            $scope.EntityGUID = $scope.FlaggedUserGUID;
        } else if ($scope.EntityType == 'Page')
        {
            $scope.EntityGUID = $scope.FlaggedPageGUID;
        } else if ($scope.EntityType == 'ACTIVITY')
        {
            $scope.EntityGUID = $scope.FlaggedActivityGUID;
        }

        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $('.button span').addClass('loading');

        var reqData = {
            Status: Status,
            AdminLoginSessionKey: AdminLoginSessionKey,
            status_action: status_action,
            EntityGUID: $scope.EntityGUID,
            EntityType: $scope.EntityType

        };

        flag_service.bloack_flagged_user(reqData).then(function (response) {

            if (response.ResponseCode == 200)
            {
                var message = '';
                /*if (Status == 3) {
                 message = "Athletic Type Deleted successfully.";
                 }*/
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
                ShowSuccessMsg(response.Message);
                $scope.FlaggedUserList();

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
        }), function (error) {
            ShowWentWrongError();
        }
    };

    $scope.change_flag_status = function (PopupID, Status) {
        var AthleticAchievementTypeID = $('#hdnAthleticAchievementTypeID').val();
        var Status = Status;
        var status_action = Status;


        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $('.button span').addClass('loading');

        var reqData = {
            Status: Status,
            AdminLoginSessionKey: AdminLoginSessionKey,
            status_action: status_action,
            EntityID: $scope.EntityID,
            EntityGUID: $scope.EntityGUID,
            EntityType: $scope.EntityType

        };

        flag_service.change_flag_status(reqData).then(function (response) {

            if (response.ResponseCode == 200)
            {
                var message = '';
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
                ShowSuccessMsg(response.Message);
                $scope.FlaggedUserList();

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
        }), function (error) {
            ShowWentWrongError();
        }
    };

});