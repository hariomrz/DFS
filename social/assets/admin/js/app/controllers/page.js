// Page List Controller
app.controller('PageListCtrl', function ($scope, $rootScope, page_service, $window) {
    
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
        
        if ($('#searchField').val()) 
        {
            //$scope.searchKey = $.trim($('#searchField').val());
            $('#searchButton').addClass('selected');
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
        page_service.list(reqData).then(function (response) {
            $scope.listData = [];
            //If no. of records greater then 0 then show
            $('.download_link,#selectallbox').show();
            $('#noresult_td').remove();
            $('.simple-pagination').show();

            //$scope.showButtonGroup = false;
            $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");            
            
            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records;
                $scope.total_pages = $scope.total_records = $scope.noOfObj;

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#PageListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>'+no_record+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjPages: response.Data.results});
                console.log($scope.listData);
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                $('.download_link,#selectallbox').hide();
                $('#PageListCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>'+response.Message+'</p></div></td></tr>');
                $('.simple-pagination').hide();
            }
            hideLoader();            
            
        }), function (error) {
            hideLoader();
        }
    };
    
    //Function to fetch conference list
    $scope.get_conference_list = function()
    {
        var reqData = {
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        page_service.get_all_conferences(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                $scope.Conferences =  response.Data;
                
            }
        }), function (error) {
        }
    }

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
        $scope.searchKey = '';
        $scope.list();
    }

    // function to download page list 
    $scope.download_page_list = function () {
        showLoader();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.dateFilterText = $("#dateFilterText").text();
        $scope.searchKey = '';
        if ($('#searchField').val()) {
            $scope.searchKey = $('#searchField').val();
            $('#searchButton').addClass('selected');
        }
        $scope.userStatus = '';
        if ($('#hdnUserStatus').val()) {
            $scope.userStatus = $('#hdnUserStatus').val();
        }
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for university listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /* Send AdminLoginSessionKey in every request */
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
        page_service.download_list(reqData).then(function (response) {
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
    };
    
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
        $scope.page_type    = page_data.Category.CategoryID;
        $scope.page_guid    = page_data.GroupGUID;
        $scope.CreatedBy    = page_data.CreatedBy.FirstName+' '+page_data.CreatedBy.LastName;
        $scope.selectedPages= {};
        $("#current_group_owner_guid").val(page_data.CreatedBy.UserGUID);
        $("#current_group_guid").val(page_data.GroupGUID);
        //console.log(page_data.CreatedBy.UserGUID);
        $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
    }
    

    // Function to set page owner
    $scope.owner = {};
    $scope.change_owner = function()
    {
        $scope.owner.AdminLoginSessionKey       = $scope.AdminLoginSessionKey;
        $scope.owner.OwnerGUID                  = $('#ownerguid').val();
        $scope.owner.GroupGUID                  = $scope.page_guid;
        console.log($scope.owner.OwnerGUID);
        if($scope.owner.OwnerGUID!="")
        {
            page_service.change_owner($scope.owner).then(function (response) {
                if(response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg(response.Message);                    
                    $scope.list();
                    closePopDiv('Setsong_popup', 'bounceOutUp');
                }
                else 
                {
                    PermissionError(response.Message);
                }
                
                $("html, body").animate({ scrollTop: 0 }, "slow");
                
                hideLoader();
            });    
        }
        else
        {
            PermissionError("Please enter user which you want to make owner.");
        }
    }

    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    }
    
    /**
    * Set li selected
    * @param {type} university
    * @returns {undefined}
    */
    $scope.selectCategory = function (Pages) {
        if (Pages.GroupGUID in $scope.selectedPages) {
            delete $scope.selectedPages[Pages.GroupGUID];
        } else {
            $scope.selectedPages[Pages.GroupGUID] = Pages;            
        }
        if (Object.keys($scope.selectedPages).length > 0) {
            setTimeout(function(){ $scope.globalChecked == true; }, 1);
            $('#ItemCounter').fadeIn();            
        } else {
            $scope.showButtonGroup = false;
            $('#ItemCounter').fadeOut();
        }
        
        setTimeout(function(){
            if($(".universities tr.selected").length == $scope.listData[0].ObjPages.length){
                setTimeout(function(){ $scope.globalChecked = true; }, 1);
                $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
            }else{
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
            }
        }, 1);
        
        var ItemCount = Object.keys($scope.selectedPages).length;
        var txtCount = ItemsSelected;
        if(ItemCount == 1)
            txtCount = ItemSelected;
        $('#ItemCounter .counter').html(ItemCount+txtCount);
        $('#add_university').slideUp();                  
        //console.log($scope.selectedUniversities);
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
    
    // function to show add new university form 
    $scope.show_university_form = function() {
        $scope.Error = false;
        $scope.error_name = null;
        $scope.error_email = null;
        $scope.error_contact_name = null;
        $scope.error_phone_number = null;
        $scope.error_phone_number = null;
        $scope.error_position = null;
        $scope.error_conference=  null;
        $scope.university = {};
        $scope.university_data = {};
        $scope.university_id    = '';
        $scope.selectedPages= {};
        $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
    };
    
    // function to add university
    $scope.add_university = function () {
        $scope.Error = false;
        $scope.error_name = null;
        $scope.error_email = null;
        $scope.error_contact_name = null;
        $scope.error_phone_number = null;
        $scope.error_phone_number = null;
        $scope.error_position = null;
        $scope.error_conference=  null;
        var Name = $('#Name').val();
        var Email = $('#Email').val();
        var contact_name = $('#contact_name').val();
        //var phone_number = $('#phone_number').val();
        var position = $('#position').val();
        var conference = $scope.university.ConferenceID;
        if (Name.length <= 0) {
            $scope.Error = true;
            $scope.error_name = required_name;
        }
        if (Email.length <= 0) {
            $scope.Error = true;
            $scope.error_email = required_email;
        }
        if (contact_name.length <= 0) {
            $scope.Error = true;
            $scope.error_contact_name = required_contact;
        }
        /*if (phone_number == '') {
            $scope.Error = true;
            $scope.error_phone_number = "Phone Number Required";
        }*/
        if (position.length <= 0) {
            $scope.Error = true;
            $scope.error_position = required_position;
        }
        if (conference == '' || conference == undefined) {
            $scope.Error = true;
            $scope.error_conference = required_conference;
        }
        if (!$scope.Error) {
            showLoader();
            //send message
            $scope.Error = false;
            $scope.error_university_name = null;
            
            $scope.university.AdminLoginSessionKey = $scope.AdminLoginSessionKey;
            $scope.university.ConferenceID = conference;
            page_service.add_university($scope.university).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg(response.Message);                    
                    $('#add_university').slideUp();                    
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
        else
        {
            //PermissionError($scope.Error);
        }
    };


    // function to show selected university in edit mode
    $scope.edit_university_show = function() {
        $scope.Error = false;
        $scope.errorFName = null;
        $scope.university = $scope.university_data;
        $('#add_university').slideDown();
        $('#university_name').val($scope.university_name);
    };
    
    // function to edit university
    $scope.edit_university = function () {
        $scope.Error = false;
        $scope.error_name = null;
        $scope.error_email = null;
        $scope.error_contact_name = null;
        $scope.error_phone_number = null;
        $scope.error_phone_number = null;
        $scope.error_position = null;
        $scope.error_conference=  null;
        var Name = $('#Name').val();
        var Email = $('#Email').val();
        var contact_name = $('#contact_name').val();
        //var phone_number = $('#phone_number').val();
        var position = $('#position').val();
        var conference = $scope.university.ConferenceID;
        if (Name.length <= 0) {
            $scope.Error = true;
            $scope.error_name = required_name;
        }
        if (Email.length <= 0) {
            $scope.Error = true;
            $scope.error_email = required_email;
        }
        if (contact_name.length <= 0) {
            $scope.Error = true;
            $scope.error_contact_name = required_contact;
        }
        /*if (phone_number == '') {
            $scope.Error = true;
            $scope.error_phone_number = "Phone Number Required";
        }*/
        if (position.length <= 0) {
            $scope.Error = true;
            $scope.error_position = required_position;
        }
        if (conference == '' || conference == undefined) {
            $scope.Error = true;
            $scope.error_conference = required_conference;
        }
        if (!$scope.Error) {
            showLoader();
            //send message
            $scope.Error = false;
            $scope.error_university_name = null;
            
            $scope.university.AdminLoginSessionKey = $scope.AdminLoginSessionKey;
            $scope.university.ConferenceID = conference;
            $scope.university.UniversityID = $scope.university_data.UniversityID;
            page_service.edit_university($scope.university).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg(response.Message);                    
                    $('#add_university').slideUp();                    
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
        else
        {
            //PermissionError($scope.Error);
        }
    };

    // Function to delete single university
    $scope.delete_page = function()
    {
        var reqData = {
                GroupGUID: $scope.page_guid,
                ActionType:"admin_delete",
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
        page_service.delete_page(reqData).then(function (response) 
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

    // Function to delete multiple conferences 
    $scope.delete_multiple_page = function()
    {
        $scope.PageGUIDS = Object.keys($scope.selectedPages);
        var reqData = {
            GroupGUIDS: $scope.PageGUIDS,
            ActionType:"admin_multi_delete",
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        page_service.delete_pages(reqData).then(function (response) {
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

    // Function to verify/unverify university 
    $scope.verify_university_toggle = function()
    {
        var reqData = {
            university_id:$scope.university_data.UniversityID,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };

        page_service.verify_university_toggle(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                $scope.university_data.StatusID  = response.StatusID;
                ShowSuccessMsg(response.Message);    
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