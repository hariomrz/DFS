// Page List Controller
app.controller('EventListCtrl', function ($scope, $rootScope, event_service, $window) {
    
    // Initialize scope variables
    $scope.totalRecord = 0;
    $scope.filteredTodos = [],
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;

    $scope.searchKey = '';
    $scope.university_id = '';
    $scope.universities = [];
    $scope.CreatedBy ="";
    $scope.university_data = {};
    $scope.numPerPage = 10,
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    // Function to fetch page list
    $scope.list = function () {
        
        intilizeTooltip();
        showLoader();
        $scope.selectedPages = {};
        $scope.globalChecked        = false;
        $('#ItemCounter').fadeOut();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate    = $('#SpnFrom').val();
        $scope.endDate      = $('#SpnTo').val();
        
        if ($('#searchEventField').val()) 
        {
            $scope.searchKey = $.trim($('#searchEventField').val());
            $('#searchEventButton').addClass('selected');
        }
        
        $scope.userStatus = '';
        if ($('#hdnUserStatus').val()) 
        {
            $scope.userStatus = $('#hdnUserStatus').val();
        }
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        
        var begins = '';
        
        if ($scope.currentPage == 1) 
        {
            //Make request data parameter for university listing
            begins = 0;//$scope.currentPage;
        } 
        else 
        {
            begins = $scope.currentPage;
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
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        var reqUrl = reqData[1]
        //Call getUniversitylist in services.js file
        event_service.list(reqData).then(function (response) {
            $scope.listData = [];
            //If no. of records greater then 0 then show
            $('.download_link,#selectallbox').show();
            $('#noresult_td').remove();
            $('.simple-pagination').show();

            //$scope.showButtonGroup = false;
            $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");            
            
            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records;
                $scope.total_events = $scope.total_records = $scope.noOfObj;

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#EventListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="5"><div class="no-content text-center"><p>No Record Found</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjPages: response.Data.results});
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                $('.download_link,#selectallbox').hide();
                $('#EventListCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="5"><div class="no-content text-center"><p>'+response.Message+'</p></div></td></tr>');
                $('.simple-pagination').hide();
            }
            hideLoader();            
            
        }), function (error) {
            hideLoader();
        }
    };
    
    // function to search pages by keyword
    $scope.search_pages = function()
    {
        $scope.searchKey = $scope.search_university_model;
        if($scope.searchKey!='' && $scope.searchKey!=undefined)
        {
            $scope.list();
        }
    }

    // function to reset search box
    $scope.pages_reset_search = function()
    {
        $('#searchEventField').val('');
        $scope.searchKey = '';
        $scope.list();
    }

    // function to download page list 
    /*$scope.download_page_list = function () {
        showLoader();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.dateFilterText = $("#dateFilterText").text();
        $scope.searchKey = '';
        if ($('#searchEventField').val()) {
            $scope.searchKey = $('#searchEventField').val();
            $('#searchEventButton').addClass('selected');
        }
        $scope.userStatus = '';
        if ($('#hdnUserStatus').val()) {
            $scope.userStatus = $('#hdnUserStatus').val();
        }
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for university listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            UserStatus: $scope.userStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            dateFilterText:$scope.dateFilterText,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call downloadUniversities in services.js file
        event_service.download_list(reqData).then(function (response) {
            if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.csv_url){
                window.location.href = response.csv_url;
            }
            hideLoader();
            
        }), function (error) {
            hideLoader();
        }
    };*/
    
    //Apply Sort by and mamke request data
    $scope.sortBY = function (column_id) {
        if($("table.users-table #noresult_td").length == 0)
        {
            $(".shortdiv").children('.icon-arrowshort').addClass('hide');
            $(".shortdiv").parents('.ui-sort').removeClass('selected');
            if($scope.reverseSort == true){
                $("#"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
            }else{
                $("#"+column_id).addClass('selected').children('.shortdiv').removeClass('sortedUp').addClass('sortedDown').children('.icon-arrowshort').removeClass('hide');                
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
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.list();
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
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        $scope.list();
    });
    
    //Function for set university data
    $scope.set_page_data  = function (page_data) 
    {
        $scope.search_user    = "";
        $scope.IsFeatured    = page_data.IsFeatured;
        $scope.page_guid    = page_data.EventGUID;
        $scope.CreatedBy    = page_data.CreatedBy.FirstName+' '+page_data.CreatedBy.LastName;
        $scope.selectedPages= {};
        $("#current_group_owner_guid").val(page_data.CreatedBy.UserGUID);
        $("#current_group_guid").val(page_data.EventGUID);
        //console.log(page_data.CreatedBy.UserGUID);
        $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
    }
    
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    }
    
    /**
     * SHow selected css
     * @param {type} University
     * @returns {undefined}
     */
    $scope.isSelected = function (Pages) {
        if (Pages.GroupGUID in $scope.selectedPages) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;            
        }        
    };

    // functio to check all the rows 
    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;        
        if ($scope.globalChecked) {
            $scope.selectedPages = {};
            var listData = $scope.listData[0].ObjPages;
            angular.forEach(listData, function (val, key) {
                if (typeof $scope.selectedPages[key]) {                    
                    $scope.selectCategory(val, key);
                }
            });
        } else {
            angular.forEach($scope.selectedPages, function (val, key) {
                $scope.selectCategory(val, key);
            });
        }    
                
    };
    
    $scope.delete_event = function()
    {
        var reqData = {
                EventGUID: $scope.page_guid,
                ActionType:"admin_delete",
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
        event_service.delete_event(reqData).then(function (response) 
        {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg(response.Message);                    
                    closePopDiv('delete_popup', 'bounceOutUp');        
                    $scope.list();
                    
                }
                else 
                {
                    PermissionError(response.Message);
                }
                
                $("html, body").animate({ scrollTop: 0 }, "slow");
                
                hideLoader();
            });
    }

    $scope.feature_event = function(IsFeatured)
    {
        var reqData = {
                EventGUID: $scope.page_guid,
                ActionType:"admin_feature",
                IsFeatured: IsFeatured,
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
        event_service.feature_event(reqData).then(function (response) 
        {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg(response.Message);  
                    if(IsFeatured == 1){
                        closePopDiv('feature_popup', 'bounceOutUp');        
                    }else{
                        closePopDiv('feature_remove_popup', 'bounceOutUp');        
                    }               
                    
                    $scope.list();
                    
                }
                else 
                {
                    PermissionError(response.Message);
                }
                
                $("html, body").animate({ scrollTop: 0 }, "slow");
                
                hideLoader();
            });
    }

    // Function to delete multiple conferences 
    $scope.delete_multiple_page = function()
    {
        $scope.PageGUIDS = Object.keys($scope.selectedPages);
        var reqData = {
            GroupGUIDS: $scope.PageGUIDS,
            ActionType:"admin_multi_delete",
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        event_service.delete_pages(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                ShowSuccessMsg(response.Message);                    
                closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');        
                $scope.list();
                
            }
            else 
            {
                PermissionError(response.Message);
            }
            
            $("html, body").animate({ scrollTop: 0 }, "slow");
            
            hideLoader();
        });
    }


    $scope.disableControl = function(flag) {
        if (flag == true) {
            $("#RoleListOpt ul li input[type='checkbox']").attr('disabled','disabled');
            $("#btnUserSubmit").hide();
            $("#conference_name").attr('disabled', 'disabled');
        }else {
            $("#RoleListOpt ul li input[type='checkbox']").removeAttr('disabled');
            $('#btnUserSubmit').show();
            $("#conference_name").removeAttr('disabled');
        }
    };
});

$(document).ready(function(){
    if ($("#search-user").length > 0) {
        $( "#search-user" ).autocomplete({
            appendTo: "#searchResult",
            source: function( request, response ) {
                if(request.term.length>2){
                    $.ajax({
                       // url: base_url+'api/users/get_user_list?LoginSessionKey='+$('#LoginSessionKey').val(),
                        url: base_url+'admin_api/team/search_group_user',
                        data: {SearchKeyword: request.term, AdminLoginSessionKey:$('#AdminLoginSessionKey').val(),GroupGUID:$("#current_group_guid").val(),GroupOwnerGUID:$("#current_group_owner_guid").val(), PageSize: 40, PageNo: 0},
                        dataType: "json",
                        method: "POST",
                        success: function( data ) {
                            
                            if(data.ResponseCode==502)
                            {
                                data.Data = {'0':{"FirstName":"Invalid LoginSessionKey.", "LastName":"", "value":request.term}};
                            }

                            if(data.Data.length <= 0) 
                            {
                                data.Data = {'0':{"FirstName":"No result found.", "LastName":"", "value":request.term}};
                            }
                            
                            response(data.Data);
                        }
                    });
                }
            },
            select: function(event, ui) {   
                
                if(ui.item.FirstName!=='No result found.' && ui.item.FirstName!=='Invalid LoginSessionKey.')
                {
                    $('#ownerguid').val(ui.item.UserGUID);
                    $('#search-user').val(ui.item.FirstName + " " + ui.item.LastName);
                    $('.icon-removed').removeClass('hide');
                    //angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
                    //$('#search-user').next('.input-group-btn').children('.btn-search').children('i').addClass('icon-removeclose');
                }
            
            }
        }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            item.label = item.FirstName + " " + item.LastName;
            item.id=item.UserGUID;
            if(item.id !== undefined) {
                item.value = item.FirstName + " " + item.LastName;
            }
            return $( "<li>" )
            .data( "item.autocomplete", item )
            .append( "<a>" + item.FirstName + " " + item.LastName + "</a>" )
            .appendTo( ul );        
        };
    }

    $('.remove-owner').click(function(){
        $('#ownerguid').val("");
        $('#search-user').val("");
        $('.icon-removed').addClass('hide');
    });
});
// Initialize Chosen Directive to update dynamic values.
app.directive('chosen', function() {
    var linker = function(scope, element, attr) {
    // update the select when data is loaded
    scope.$watch(attr.chosen, function(oldVal, newVal) {
        element.trigger('chosen:updated');
    });
    // update the select when the model changes
    scope.$watch(attr.ngModel, function() {
        element.trigger('chosen:updated');
    });
    element.chosen();
    };
    return {
        restrict: 'A',
        link: linker
    };
});


$(document).ready(function () {
    $('#searchEventButton').click(function () {
        $('#searchEventField').parent('.search-block').show();
        if ($('#searchEventField').val() != '') {
            angular.element(document.getElementById('EventListCtrl')).scope().list();
        }
    });
    //Call when user clear a search phrase
    $('#clearEventText').click(function () {
        if ($('#searchEventButton').hasClass('selected'))
        {
            $('#searchEventField').val('');
            $('#searchEventField').removeClass('selected');
            angular.element(document.getElementById('EventListCtrl')).scope().list();
        }
    });
    //For search data on press enter
    $("#searchEventField").keyup(function (e) {
        if (e.keyCode == 13)
        {
            $(this).parents('.search-field').find(".search-btn").trigger("click");
        }
    });
});