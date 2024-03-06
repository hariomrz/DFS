/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

app.controller('postsCtrl', ['$scope', 'postsService', '$rootScope', '$window', function ($scope, postsService, $rootScope, $window) {
        $scope.listData = [];

        $scope.totalRecord = 0;
        $scope.filteredTodos = [];
        $scope.currentPage = 1;
        $scope.numPerPage = pagination;
        $scope.maxSize = pagination_links;
        $scope.orderByField = '';
        $scope.reverseSort = true;
        $scope.isImageUploaded = false;
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $scope.ShowDescriptionLimit = 100;

        $scope.getPosts = function () {
            intilizeTooltip();
            showLoader();
            $scope.globalChecked = false;
            $('#ItemCounter').fadeOut();

            //get starting date and end date from top selected date and apply in query
            $scope.startDate = $('#SpnFrom').val();
            $scope.endDate = $('#SpnTo').val();
            $scope.searchKey = '';
            if ($('#searchField').val()) {
                $scope.searchKey = $.trim($('#searchField').val());
                $('#searchButtonA').addClass('selected');
            }

            /* Here we check if current page is not equal 1 then set new value for var begin */
            var begins = '';
            if ($scope.currentPage == 1) {
                //Make request data parameter for users listing
                begins = 0;//$scope.currentPage;
            } else {
                begins = (($scope.currentPage - 1) * $scope.numPerPage)
            }

            /* Send AdminLoginSessionKey in every request */
            $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

            var reqData = {
                EntityType: 'Activity',
                Begin: begins, //$scope.currentPage,
                End: $scope.numPerPage,
                StartDate: $scope.startDate,
                EndDate: $scope.endDate,
                SearchKey: $scope.searchKey,
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey: $scope.AdminLoginSessionKey
            }
            var reqUrl = reqData[1]
            //Call getUserlist in services.js file
            postsService.getList(reqData).then(function (response) {
                $scope.listData = [];
                //If no. of records greater then 0 then show
                $('.download_link,#selectallbox').show();
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //$scope.showButtonGroup = false;
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");

                if (response.ResponseCode == 200) {
                    $scope.noOfObj = response.Data.total_records
                    $scope.totalRecord = $scope.noOfObj;

                    //If no of records equal 0 then hide
                    if ($scope.noOfObj == 0) {
                        $('.download_link,#selectallbox').hide();
                        $('#postsCtrl table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>' + ThereIsNoRecordToShow + '</p></div></td></tr>');
                        $('.simple-pagination').hide();
                    }

                    //Push data into Controller in view file
                    //$scope.listData.push({ObjUsers: response.Data.results});
                    $scope.listData = response.Data.results;

                } else if (response.ResponseCode == 517) {
                    redirectToBlockedIP();
                } else if (response.ResponseCode == 598) {
                    $('.download_link,#selectallbox').hide();
                    $('#postsCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>' + response.Message + '</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                hideLoader();

            }), function (error) {
                hideLoader();
            }
        };
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
                    //UserStatus: $scope.userStatus,
                    SortBy: $scope.orderByField,
                    OrderBy: $scope.reverseSort,
                    //Send AdminLoginSessionKey
                    AdminLoginSessionKey: $scope.AdminLoginSessionKey
                }
                $scope.getPosts();
            }
        };

        $scope.SetItem = function (item) {
            $scope.data = item;
        };
        $scope.remove_flag = function () {
            var reqData = {
                EntityType: 'Activity',
                AdminLoginSessionKey: $scope.AdminLoginSessionKey,
                EntityGUID: $scope.data.activity_guid
            }
            postsService.removeFlag(reqData).then(function (response) {
                closePopDiv('delete_popup', 'bounceOutUp');
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg(response.Message, 'success');
                    $scope.getPosts();
                } else {
                    ShowErrorMsg(response.Message, 'danger');
                }
            });
        };
        $scope.delete_post = function () {
            var reqData = {
                AdminLoginSessionKey: $scope.AdminLoginSessionKey,
                EntityGUID: $scope.data.activity_guid,
                EntityType: 'ACTIVITY',
            }
            postsService.deleteActivity(reqData).then(function (response) {
                closePopDiv('delete_popup', 'bounceOutUp');
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg(response.Message, 'success');
                    $scope.getPosts();
                } else {
                    ShowErrorMsg(response.Message, 'danger');
                }
            });
        };
        $scope.FlagList = [];
        $scope.view_flag_details = function () {
            $scope.FlagList = [];
            var reqData = {
                EntityType: 'Activity',
                AdminLoginSessionKey: $scope.AdminLoginSessionKey,
                EntityGUID: $scope.data.activity_guid
            }
            postsService.viewFlags(reqData).then(function (response) {
                openPopDiv('flag_popup');
                if (response.ResponseCode == 200) {
                    $scope.FlagList = response.Data;
                } else {
                    ShowErrorMsg(response.Message, 'danger');
                }
            });
        }
		$scope.removeLastChar = function(str){
			return str.slice(0, -1);
		}

        $scope.view_post = function(username, activity_guid){
            var url = base_url+username+'/activity/'+activity_guid;
            window.open(url,'_blank');
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
                //Send AdminLoginSessionKey
                AdminLoginSessionKey: $scope.AdminLoginSessionKey
            }
        });
    }]);

$(document).ready(function () {
    $('#StartDate').datepicker();
    $('#EndDate').datepicker();
    $('#ui-datepicker-div').mouseup(function (e) {
        return false;
    });
    //console.log($('#StartDate').datepicker())

    $('#searchButtonA').on('click', function () {
        if ($('#searchButtonA').hasClass('selected')) {
            angular.element($('#postsCtrl')).scope().getPosts();
        } else {
            $('#searchButtonA').addClass('selected');
            $('.search-block').show();
        }
    });
    $('#clearText').click(function () {
        if ($('#searchButtonA').hasClass('selected')) {
            $('#searchButtonA').removeClass('selected');
            $('#searchField').val('').trigger('change');
            angular.element($('#postsCtrl')).scope().getPosts();
        }
    });
});
