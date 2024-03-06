// Betainvite Controller
app.controller('BetainviteCtrl', function ($scope, $rootScope, getBetainviteData, $window) {
    $scope.totalRecord = 0;
    $scope.totalUsers = 0;
    $scope.filteredTodos = [],
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = true;
    
    $scope.inviteUserStatus = 2;
    $scope.InviteUserText = BetaInvite_JoinedUsers;
    
    $scope.globalChecked = false;
    $scope.showButtonGroup = false;
    $scope.selectedInviteUser = {};
    $scope.selectedInviteUserIndex = {};
    $scope.confirmationMessage = '';    
    
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    
    $scope.loadUsersByType = function(user_type){
        if(user_type == "invited_users"){
            $scope.InviteUserText = BetaInvite_JoinedUsers;
            $scope.inviteUserStatus = 2;
        }else if(user_type == "not_joined_yet"){
            $scope.InviteUserText = BetaInvite_NotJoinedYet;
            $scope.inviteUserStatus = 1;
        }else if(user_type == "deleted_users"){
            $scope.InviteUserText = BetaInvite_DeletedUsers;
            $scope.inviteUserStatus = 3;
        }else if(user_type == "removed_access_users"){
            $scope.InviteUserText = BetaInvite_RemovedAccessUsers;
            $scope.inviteUserStatus = 4;
        }
    };
    
    $scope.setInviteUserStatus = function (status) {
        $scope.inviteUserStatus = status;
        $(".useroptul li").removeClass("selected");
        
        if (status == "1") {
            $scope.InviteUserText = BetaInvite_NotJoinedYet;
            $("#linotjoined").addClass("selected");
        }else if (status == "2") {
            $scope.InviteUserText = BetaInvite_JoinedUsers;
            $("#lijoined").addClass("selected");
        }else if (status == "3") {
            $scope.InviteUserText = BetaInvite_DeletedUsers;
            $("#lideleted").addClass("selected");
        }else if (status == "4") {
            $scope.InviteUserText = BetaInvite_RemovedAccessUsers;
            $("#liremovedaccess").addClass("selected");
        }
        
        $scope.globalChecked = false;
        $scope.showButtonGroup = false;
        $('#ItemCounter').fadeOut();
        
        $(".shortdiv").children('.icon-arrowshort').addClass('hide');
        $(".shortdiv").parents('.ui-sort').removeClass('selected');
        $("#created_date").addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
        $scope.orderByField = 'created_date';
        $scope.reverseSort = true;
        
        $scope.betaInvitedUsers();
    };
    
    $scope.searchData = function(){
        if($('#searchField').val() != ""){
            $scope.betaInvitedUsers();
        }
    }
    
    $scope.clearBetaSearch = function(){
        $('.search-block').hide();
        if($('#searchField').val() != ""){            
            $('#searchField').val('');
            $scope.betaInvitedUsers();
        }
    }
    
    $scope.betaInvitedUsers = function () {
        showLoader();
        $scope.selectedInviteUser = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();
        
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.searchKey = '';
        if ($('#searchField').val()) {
            $scope.searchKey = $('#searchField').val();
            $('#searchButton').addClass('selected');
        }
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            UserStatus: $scope.inviteUserStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        
        $("#accessdenieddiv").html('');
        $("#selectallbox").show();
        
        
        //Call getBetaInvitedUsersList in services.js file
        getBetainviteData.getBetaInvitedUsersList(reqData).then(function (response) {
            $scope.listData = [];
            //If no. of records greater then 0 then show
            $('.download_link,#selectallbox').show();
            $('#noresult_td').remove();
            $('.simple-pagination,#search_box_div').show();
            if($scope.inviteUserStatus == "3"){
                $("#selectallbox").hide();
            }

            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $scope.totalUsers = $scope.totalRecord = $scope.noOfObj;
                
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                $('#ItemCounter').fadeOut();
                $("#inviteuserlistdiv").show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#BetainviteCtrl table>tbody').append('<tr id="noresult_td"><td colspan="8"><div class="no-content text-center"><p>'+ThereIsNoUserToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjUsers: response.Data.results});
                //console.log(response.Data.results);
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                PermissionError(response.Message);
                $("#accessdenieddiv").html(response.DeniedHtml);
                $("#inviteuserlistdiv").hide();
                $('.download_link,#selectallbox,#search_box_div').hide();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
            hideLoader();            
            
        }), function (error) {
            hideLoader();
        }
    };
    
    $scope.downloadBetaUsers = function () {
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
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            UserStatus: $scope.inviteUserStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            dateFilterText:$scope.dateFilterText,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call downloadBetaUsers in services.js file
        getBetainviteData.downloadBetaUsers(reqData).then(function (response) {
            if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.csv_url){
                if(window.location.href = response.csv_url){
                    var reqData = {
                        FilePath: response.csv_path,
                        //Send AdminLoginSessionKey
                        AdminLoginSessionKey :$scope.AdminLoginSessionKey
                    }
                    setTimeout(function(){
                        getBetainviteData.deleteCsvFiles(reqData).then(function (response) {

                        });
                    }, 2000);
                }
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
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
            
            $scope.betaInvitedUsers();
        }
    };
    
    //Get no. of pages for data
    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };
    
    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {        
        $scope.betaInvitedUsers();
    });
    
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    }
    
    $scope.selectCategory = function (betainvite) {
        if($scope.inviteUserStatus != "3" && $("#hdnSelectallPermission").val() == 1){
            if (betainvite.betainviteid in $scope.selectedInviteUser) {
                delete $scope.selectedInviteUser[betainvite.betainviteid];
            } else {
                $scope.selectedInviteUser[betainvite.betainviteid] = betainvite;
            }
            if (Object.keys($scope.selectedInviteUser).length > 0) {
                setTimeout(function(){ $scope.globalChecked == true; }, 1);
                $('#ItemCounter').fadeIn();            
            } else {
                $scope.globalChecked = false;
                $('#ItemCounter').fadeOut();
            }
            
            setTimeout(function(){
                if($(".registered-user tr.selected").length == $scope.listData[0].ObjUsers.length){
                    setTimeout(function(){ $scope.globalChecked = true; }, 1);
                    $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
                }else{
                    $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                }
            }, 1);

            var ItemCount = Object.keys($scope.selectedInviteUser).length;
            var txtCount = ItemsSelected;
            if(ItemCount == 1)
                txtCount = ItemSelected;
            $('#ItemCounter .counter').html(ItemCount+txtCount);
            //console.log($scope.selectedInviteUser);
        }
    }
    
    $scope.isSelected = function (betainvite) {
        if($scope.inviteUserStatus != "3"){
            if (betainvite.betainviteid in $scope.selectedInviteUser) {
                return true;
            } else {
                $scope.globalChecked = false;
                return false;            
            }
        }
    };

    $scope.globalCheckBox = function () {
        if($scope.inviteUserStatus != "3"){
            $scope.globalChecked = ($scope.globalChecked == false) ? true : false;        
            if ($scope.globalChecked) {
                $scope.selectedInviteUser = [];
                var listData = $scope.listData[0].ObjUsers;
                angular.forEach(listData, function (val, key) {
                    if (typeof $scope.selectedInviteUser[key]) {                    
                        $scope.selectCategory(val, key);
                    }
                });
            } else {
                angular.forEach($scope.selectedInviteUser, function (val, key) {
                    $scope.selectCategory(val, key);
                });
            }    
        }
    };
        
    //Function for set user id
    $scope.SetBetaInvite = function (userlist) {
        $scope.currentBetaInviteID = userlist.betainviteid;
    };
    
    $scope.changeBetaInviteUserStatus = function (action) {
        var invitestatus = '';
        if (action == "delete") {
            invitestatus = 3;
            $scope.confirmationMessage = Sure_Delete+' ?';
        }else if (action == "removeaccess") {
            invitestatus = 4;
            $scope.confirmationMessage = Sure_RemoveAccess+' ?';
        }else if (action == "grantaccess") {
            invitestatus = 2;
            $scope.confirmationMessage = Sure_GrantAccess+' ?';
        }else if (action == "reinvite") {
            invitestatus = 1;
            $scope.confirmationMessage = Sure_Reinvite+' ?';
        }
        
        $scope.BetaInviteIds = [];
        $scope.BetaInviteIds.push($scope.currentBetaInviteID);
        openPopDiv('confirmeBetaInvitePopup', 'bounceInDown');
        $scope.invitestatus = invitestatus;
        $scope.inviteaction = action;
        
    };
    
    $scope.changeMultipleBetaInviteUserStatus = function (action) {
        var invitestatus = '';
        if (action == "delete") {
            invitestatus = 3;
            $scope.confirmationMessage = Sure_Delete+' ?';
        }else if (action == "removeaccess") {
            invitestatus = 4;
            $scope.confirmationMessage = Sure_RemoveAccess+' ?';
        }else if (action == "grantaccess") {
            invitestatus = 2;
            $scope.confirmationMessage = Sure_GrantAccess+' ?';
        }else if (action == "reinvite") {
            invitestatus = 1;
            $scope.confirmationMessage = Sure_Reinvite+' ?';
        }
        
        openPopDiv('confirmeBetaInvitePopup', 'bounceInDown');
        $scope.BetaInviteIds = [];
        $scope.BetaInviteIds = Object.keys($scope.selectedInviteUser);
        $scope.invitestatus = invitestatus;
        $scope.inviteaction = action;
    };
    
    $scope.updateBetaInviteUserStatus = function (PopupId) {
        
        var reqData = {
            BetaInviteIds: $scope.BetaInviteIds,
            status: $scope.invitestatus,
            inviteaction: $scope.inviteaction,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv(PopupId, 'bounceOutUp');
        showLoader();
        getBetainviteData.updateBetaInviteUserStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                //Reset all
                $scope.BetaInviteIds = [];

                hideLoader();
                var msg =  $scope.inviteaction;
                msg = ucwords(msg);
                ShowSuccessMsg(msg+" successfully.");

                $scope.betaInvitedUsers();
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);                
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
            hideLoader();
            
        }), function (error) {
            hideLoader();
        }
    };
    
    //$scope.betaInvitedUsers();
});

app.controller('SendBetainviteCtrl', function ($scope, getBetainviteData, $window) { 
    $scope.importUserList = [];
    $scope.importText = '';
    
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    
    $scope.loadSendInviteTab = function(tabl_id){
        if(tabl_id != ""){
            $("#"+tabl_id).trigger("click");
        }
    };
    
    $scope.addMoreField = function(){
        var tableRow = $('#manualInvite'); 
        var newField = [
            '<tr>',
              '<td>',
                  '<div class="form-control">',
                     '<div class="text-field large" data-type="focus">',
                      '<input class="betausername" type="text" name="username[]" value="" placeholder="Please enter name of the user">',
                    '</div>',
                    '<div class="error-holder"><span>Error</span></div>',
                  '</div>',
              '</td>',
              '<td>',
                  '<div class="form-control">',
                     '<div class="text-field large" data-type="focus">',
                      '<input class="betauseremail" type="text" name="user_email[]" value="" onblur="CheckEmailDuplicacy(this);" placeholder="Please enter email of the user">',
                    '</div>',
                    '<div class="error-holder"><span>Error</span></div>',
                  '</div> ',
              '</td>',
              '<td>',
                  '<div class="form-control">',
                     '<div class="text-field large" data-type="focus">',
                      '<input class="betausername" type="text" name="username[]" value="" placeholder="Please enter name of the user">',
                    '</div>',
                    '<div class="error-holder"><span>Error</span></div>',
                  '</div>',
              '</td>',
              '<td>',
                  '<div class="form-control">',
                     '<div class="text-field large" data-type="focus">',
                      '<input class="betauseremail" type="text" name="user_email[]" value="" onblur="CheckEmailDuplicacy(this);" placeholder="Please enter email of the user">',
                    '</div>',
                    '<div class="error-holder"><span>Error</span></div>',
                  '</div> ',
              '</td>',
              '<td>',
                 '<div class="addmoreName removeFields">',
                    '<i class="icon-remove">&nbsp;</i>',
                  '</div>',
              '</td>',
            '</tr>'
         ].join('');
        tableRow.append(newField);
        placeHolder();
    };
    
    $scope.CheckEmailExist = function(email,objectele){
        var reqData = {
            Email: email,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        objectele.parent().next().find('span').hide();
        objectele.parent().next().find('span').html("");
        //Call CheckEmailExist in services.js file
        getBetainviteData.CheckEmailExist(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                if(response.results == "exist"){
                    objectele.parent().next().find('span').show();
                    objectele.parent().next().find('span').html(response.Message);
                }
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                objectele.parent().next().find('span').show();
                objectele.parent().next().find('span').html(response.Message);
            }            
        });
    };
    
    $scope.SendInvite = function(userArr) {
        $("#btnsendinvite").attr("disabled","disabled");
        
        var reqData = {
            UserArr: userArr,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        showLoader();
        //Call SendBetaInvite in services.js file
        getBetainviteData.SendBetaInvite(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                $("#btnsendinvite").removeAttr("disabled");
                ShowSuccessMsg(response.Message);
                $('#manualInvite tr').each(function () {
                    $(this).find('td').find('.betausername').val("");
                    $(this).find('td').find('.betauseremail').val("");
                });
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }            
            hideLoader();
        });
    };
    
    
    $scope.showImportUserList = function(listArr){
        $("#UploadField").hide();
        $("#btnUploadCsv").hide();
        $("#uploadedList").show();
        $("#start_import").show();
        //$("#noteDiv").show();
        $scope.importUserList = listArr;
        $scope.importListCount = $scope.importUserList.length;
        if($scope.importListCount == 1){
            $scope.importText = $scope.importListCount+" "+User_Upload_Success;
        }else{
            $scope.importText = $scope.importListCount+" "+Users_Upload_Success;
        }
        if($scope.importListCount < 1){
            $scope.importText = "";
            $("#start_import").hide();
            $("#cancelUploadbtn").show();
        }
        $scope.$apply();
    };
    
    $scope.deleteConfirmUploadedUser = function(eleIndex){
        $scope.currentElementIndex = eleIndex;
        openPopDiv('confirmeDeletePopup', 'bounceInDown');
    };
    
    $scope.deleteUploadedUser = function(){
        $scope.importUserList.splice($scope.currentElementIndex, 1);
        ShowSuccessMsg("User delete successfully.");
        $scope.importListCount = $scope.importUserList.length;
        if($scope.importListCount == 1){
            $scope.importText = $scope.importListCount+" "+User_Upload_Success;
        }else{
            $scope.importText = $scope.importListCount+" "+Users_Upload_Success;
        }
        
        if($scope.importListCount < 1){
            $scope.importText = "";
        }
        
        closePopDiv('confirmeDeletePopup', 'bounceOutUp');
        if($scope.importListCount <= 0){
            $("#UploadField").show();
            $("#btnUploadCsv").show();
            $("#uploadedList").hide();
            $("#start_import").hide();
            $("#cancelUploadbtn").hide();
            //$("#noteDiv").hide();
            $("#csv_file").val("");
            $("#Upload").val("");
        }
    };
    
    $scope.resetImportSection = function(){
        $("#UploadField").show();
        $("#btnUploadCsv").show();
        $("#uploadedList").hide();
        $("#start_import").hide();
        $("#cancelUploadbtn").hide();
        $("#csv_file").val("");
        $("#Upload").val("");
    }
    
    $scope.ImportFinalUsers = function(){
        if($scope.importListCount < 1){
            ShowErrorMsg(ThereIsNoRecordToImport);
            return false;
        }
        $("#start_import").attr("disabled","disabled");
        var userArr = $scope.importUserList;
        var reqData = {
            UserArr: userArr,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        showLoader();
        //Call SendBetaInvite in services.js file
        getBetainviteData.SendBetaInvite(reqData).then(function (response) {
            $("#start_import").removeAttr("disabled");
            if(response.ResponseCode == 200){                
                ShowSuccessMsg(response.Message);                
                setTimeout(function () {
                    $scope.importUserList = [];
                    window.location = base_url + 'admin/betainvite';
                }, 3000);
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }   
            hideLoader();
        });        
    };
    
});