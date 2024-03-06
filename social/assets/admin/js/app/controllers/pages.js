var no_record = 'No pages found';
app.controller('pagesCtrl', function ($scope, $rootScope, pages_service, $window) {


// Initialize scope variables
    $scope.totalRecord = 0;
    $scope.filteredTodos = [];
    $scope.currentPage = 1;
    $scope.numPerPage = pagination;
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.searchKey = '';
    $scope.numPerPage = 50;
    $scope.selectedOrganization = {};
    $scope.Tags = '';


// Function to organization list
    $scope.OrganizationList = function ()
    {
        intilizeTooltip();
        showLoader();
        $scope.selectedPages = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.searchKey = '';
        if ($('#searchPagesField').val())
        {
            $scope.searchKey = $.trim($('#searchPagesField').val());
            $('#searchPagesButton').addClass('selected');
        }

        /* Here we check if current page is not equal 1 then set new value for var begin */

        var begins = '';

        if ($scope.currentPage == 1)
        {
            //Make request data parameter for university listing
            begins = 0;//$scope.currentPage;
        } else
        {
            begins = (($scope.currentPage - 1) * $scope.numPerPage);
        }

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            UserStatus: $scope.userStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            PageType: 2,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }

        var reqUrl = reqData[1]
        //Call organizationList in services.js file
        pages_service.Pageslist(reqData).then(function (response) {
            $scope.OrganizationlistData = [];
            //If no. of records greater then 0 then show
            $('.download_link,#selectallbox').show();
            $('#noresult_td').remove();
            $('.simple-pagination').show();

            //$scope.showButtonGroup = false;
            $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");

            if (response.ResponseCode == 200) {
                $scope.noOfObj = response.Data.total_records;
                $scope.totalRecord = response.Data.total_records;
                $scope.total_pages = $scope.total_records = $scope.noOfObj;
                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#pagesCtrl table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>' + no_record + '</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }


                //Push data into Controller in view file
                $scope.OrganizationlistData.push({ObjPages: response.Data.results});

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                $('.download_link,#selectallbox').hide();
                $('#pagesCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>' + response.Message + '</p></div></td></tr>');
                $('.simple-pagination').hide();
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    }



    /**
     * Set li selected
     * @param {type} user
     * @returns {undefined}
     */
    $scope.selectOrganization = function (organization) {
        if (organization.PageID in $scope.selectedOrganization) {
            delete $scope.selectedOrganization[organization.PageID];
        } else {
            $scope.selectedOrganization[organization.PageID] = organization;
        }
        if (Object.keys($scope.selectedOrganization).length > 0) {
            setTimeout(function () {
                $scope.globalChecked == true;
            }, 1);
            $('#ItemCounter').fadeIn();
        } else {
            $scope.showButtonGroup = false;
            $('#ItemCounter').fadeOut();
        }

        setTimeout(function () {
            if ($(".universities tr.selected").length == $scope.OrganizationlistData[0].ObjPages.length) {
                setTimeout(function () {
                    $scope.globalChecked = true;
                }, 1);
                $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
            } else {
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
            }
        }, 1);

        var ItemCount = Object.keys($scope.selectedOrganization).length;
        var txtCount = ItemsSelected;
        if (ItemCount == 1)
            txtCount = ItemSelected;
        $('#ItemCounter .counter').html(ItemCount + txtCount);
        //console.log($scope.selectedUsers);
    }


    /**
     * SHow selected css
     * @param {type} sport
     * @returns {undefined}
     */
    $scope.isSelectedOrganization = function (organization) {
        if (organization.PageID in $scope.selectedOrganization) {
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
                //Send AdminLoginSessionKey
                AdminLoginSessionKey: $scope.AdminLoginSessionKey
            }
            $scope.OrganizationList();
        }
    };


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
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }

        $scope.OrganizationList();

    });


    //Function for set sport id
    $scope.set_organization_data = function (organizationData) {

        $scope.OrganizationPopUpName = 'UPDATE ORGANIZATION';
        $scope.OrganizationAddBtnTxt = 'UPDATE';
        $scope.OrganizationID = organizationData.PageID;
    }
    $scope.getOrganizationMemberDetail = function (organizationData) {
        var reqData = {
            PageID: $scope.OrganizationID,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        };
        organization_service.getOrganizationMemberDetail(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.PageCreator = response.Data.Creator;
                $scope.PageUser = response.Data.Users;
                $scope.SuccessMsg = '';

            }


        }), function (error) {
            hideLoader();
        }

    }

    $scope.loadUsersTags = function (query) {

        var reqData = {
            'AdminLoginSessionKey': $scope.AdminLoginSessionKey,
            'query': query,
            'PageAddedMembers': $scope.PageCreator,
            'PageID': $scope.OrganizationID,
        }

        return organization_service.get_users_tags(reqData);
    };

    $scope.SuccessMsg = '';
    $scope.IsError = 0;
    $scope.add_users = function (tags) {

        if (tags == '')
        {
            $scope.ErrorMsg = 'Please add user';
            $scope.IsError = 1;
        } else {
            $scope.ErrorMsg = '';
            $scope.IsError = 0;
        }

        var reqData = {
            Tags: tags,
            PageID: $scope.OrganizationID,
        };
        if ($scope.IsError == 0)
        {
            organization_service.add_users(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.Tags = '';
                    $scope.SuccessMsg = 'User added successfully';
                    //ShowSuccessMsg(response.Message);
                    setTimeout(function () {
                        $scope.getOrganizationMemberDetail();
                    }, 1000);

                }
            });
        }
    }


    $scope.setcreatorData = function (creatorData) {
        $scope.CreatorData = creatorData;
    }

    $scope.remove_user = function (userData)
    {

        var reqData = {
            PageID: $scope.CreatorData.PageID,
            PageGUID: $scope.CreatorData.PageGUID,
            UserGUID: $scope.CreatorData.UserGUID,
            UserID: $scope.CreatorData.UserID,
            ModuleRoleID: $scope.CreatorData.ModuleRoleID,
        };

        organization_service.remove_user(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
                closePopDiv('delete_popup', 'bounceOutUp');
                $scope.SuccessMsg = 'User removed successfully';

                setTimeout(function () {
                    $scope.getOrganizationMemberDetail();
                }, 1000);

            }
        });

    }


    $scope.remove_page = function (userData)
    {

        var reqData = {
            PageID: $scope.OrganizationID,
        };

        pages_service.remove_page(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
                closePopDiv('delete_popup', 'bounceOutUp');
                $scope.SuccessMsg = 'Page removed successfully';

                $scope.OrganizationList();
            }
        });

    }
    $scope.confirmVerify = function () {
        openPopDiv('verify_popup', 'bounceOutDown');
    }

    $scope.confirmUnVerify = function () {
        openPopDiv('unverify_popup', 'bounceOutDown');
    }

    $scope.ChangeVerifyStatus = function (PopupID, IsVerified) {

        var UserId = $("#hdnUserID").val();
        var Status = $("#hdnChangeStatus").val();
        /* Send AdminLoginSessionKey in every request */
        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $('.button span').addClass('loading');

        var reqData = {
            PageID: $scope.OrganizationID, //$scope.currentPage,
            Status: Status,
            AdminLoginSessionKey: AdminLoginSessionKey,
            IsVerified: IsVerified
        };

        pages_service.ChangeVerifyStatus(reqData).then(function (response) {
            HideInformationMessage('user_change_status');
            if (response.ResponseCode == 200) {
                $scope.OrganizationList();
                $('.button span').removeClass('loading');
                closePopDiv(PopupID, 'bounceOutUp');
                ShowSuccessMsg("Verification status changed successfully.");
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
        }), function (error) {
            ShowWentWrongError();
        }
    }

});

$(document).ready(function () {
    $('#searchPagesButton').click(function () {
        $('#searchPagesField').parent('.search-block').show();
        if ($('#searchPagesField').val() != '') {
            angular.element(document.getElementById('pagesCtrl')).scope().OrganizationList();
        }
    });
    //Call when user clear a search phrase
    $('#clearText').click(function () {
        if ($('#searchPagesButton').hasClass('selected'))
        {
            $('#searchPagesField').val('');
            $('#searchPagesButton').removeClass('selected');
            angular.element(document.getElementById('pagesCtrl')).scope().OrganizationList();
        }
    });
    //For search data on press enter
    $("#searchPagesField").keyup(function (e) {
        if (e.keyCode == 13)
        {
            $(this).parents('.search-field').find(".search-btn").trigger("click");
        }
    });
});