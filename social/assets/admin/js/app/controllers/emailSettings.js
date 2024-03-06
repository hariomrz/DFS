// Email Setting Controller
app.controller('EmailSettingCtrl', function ($scope, $rootScope, emailSettingData, $window) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.smtpaction = '';
    $scope.isDefaultSMTP = 0;
    $scope.SmtpStatus = {};
    $rootScope.smtpDetails = {};
    $rootScope.totalSettings = 0;
        
    $scope.globalChecked = false;
    $scope.selectedSettings = {};
    $scope.selectedSettingsIndex = {};
    $scope.confirmationMessage = '';
    
    $scope.smtpEmails = function () {
        intilizeTooltip();
        showLoader();
        
        $scope.selectedSettings = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for smtp listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        var reqUrl = reqData[1]
        //Call getSmtpSettingEmailList in services.js file
        emailSettingData.getSmtpSettingEmailList(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $rootScope.totalSettings = $scope.totalRecord = $scope.noOfObj;

                //$scope.showButtonGroup = false;
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                
                //If no. of records greater then 0 then show            
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#EmailSettingCtrl table>tbody').append('<tr id="noresult_td"><td colspan="6"><div class="no-content text-center"><p>'+ThereIsNoEmailSettingToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjSMTP: response.Data.results});
            
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
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.smtpEmails();
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
            SortBy: $scope.sort_by,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        $scope.smtpEmails();
    });
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    };    
    
    //Function for set user id
    $scope.SetSmtpDetail = function (smtplist) {
        $scope.isDefaultSMTP = 0;
        //console.warn(emaillist);
        $scope.SmtpStatus = smtplist.statusid;
        $scope.emailsettingid = smtplist.emailsettingid;
                
        if(smtplist.isdefault == 1){
            $scope.isDefaultSMTP = 1;
        }
    };
    
    //Function for edit smtp setting details
    $scope.editSmtpSetting = function () {
        //Useful for set breadcrumb
        $window.location.href = base_url + 'admin/emailsetting/email_setting_authentication?id=' + $scope.emailsettingid;
    };
    
    //Function for get smtp setting detail on page load
    $scope.getSettingDetails = function(){
        var EmailSettingId = $("#hdnEmailSettingID").val();
        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        if(EmailSettingId){
            var requestData = {EmailSettingId:EmailSettingId,AdminLoginSessionKey:AdminLoginSessionKey};
            emailSettingData.getSmtpSettingDetails(requestData).then(function (response) {                
                $rootScope.smtpDetails = response.Data.results;
                $scope.mod = $rootScope.smtpDetails;
                console.log($rootScope.smtpDetails);
            });
        }
    };
    
    //Function for create new smtp setting
    $scope.createSmtpSetting = function ()
    {
        $('.loader_smtp').show();
        var Name = $scope.mod.Name;
        var FromName = $scope.mod.FromName;
        var FromEmail = $scope.mod.FromEmail;
        var ServerName = $scope.mod.ServerName;
        var SPortNo = $scope.mod.SPortNo;
        var UserName = $scope.mod.UserName;
        var Password = $scope.mod.Password;
        var ReplyTo = $scope.mod.ReplyTo;
        var IsSSLRequire = $scope.mod.IsSSLRequire;
        var EmailSettingId = $('#hdnEmailSettingID').val();
        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        var requestData = {EmailSettingId:EmailSettingId, Name: Name, FromName: FromName, FromEmail: FromEmail, ServerName: ServerName, SPortNo: SPortNo, UserName: UserName, Password: Password, ReplyTo: ReplyTo, IsSSLRequire: IsSSLRequire, AdminLoginSessionKey:AdminLoginSessionKey};
        emailSettingData.CreateSmtpSetting(requestData).then(function (response) {

            if (response.ResponseCode == 200) {                
                ShowSuccessMsg(response.Message);
                
                if(!EmailSettingId){
                    $('#smtp_form')[0].reset();
                }
                
                setTimeout(function () {
                    window.location = base_url + 'admin/emailsetting/index';
                }, 2500);
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);
            }else if(response.ResponseCode == 104){
                ShowErrorMsg(response.Message);
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            } else {         
                ShowErrorMsg(response.Message);
            }            
            $("html, body").animate({ scrollTop: 0 }, "slow");
            $('.loader_smtp').hide();
        });
    };
    
    
    /**
     * Set li selected
     * @param {type} setting
     * @returns {undefined}
     */
    $scope.selectCategory = function (smtpsetting) {
        if(smtpsetting.isdefault != 1 && $("#hdnSelectallPermission").val() == 1){
            if (smtpsetting.emailsettingid in $scope.selectedSettings) {
                delete $scope.selectedSettings[smtpsetting.emailsettingid];
            } else {
                $scope.selectedSettings[smtpsetting.emailsettingid] = smtpsetting;
            }
            if (Object.keys($scope.selectedSettings).length > 0) {
                setTimeout(function(){ $scope.globalChecked == true; }, 1);
                $('#ItemCounter').fadeIn();            
            } else {
                $scope.globalChecked = false;
                $('#ItemCounter').fadeOut();
            }    

            setTimeout(function(){
                if($(".smtp_table tr.selected").length == ($scope.listData[0].ObjSMTP.length - $(".smtp_table tr.notselected").length)){
                    setTimeout(function(){ $scope.globalChecked = true; }, 1);
                    $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
                }else{
                    $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                }
            }, 1);
            
            var ItemCount = Object.keys($scope.selectedSettings).length;
            var txtCount = ItemsSelected;
            if(ItemCount == 1)
                txtCount = ItemSelected;
            $('#ItemCounter .counter').html(ItemCount+txtCount);
            //console.log($scope.selectedSettings);
        }
    };
    
    /**
     * SHow selected css
     * @param {type} user
     * @returns {undefined}
     */
    $scope.isSelected = function (smtpsetting) {
        if (smtpsetting.emailsettingid in $scope.selectedSettings) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;
        }        
    };
    
    $scope.isNotSelected = function (smtpsetting) {
        if(smtpsetting.isdefault == '1'){
            return true;
        } else {
            return false;
        }        
    };

    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;
        if ($scope.globalChecked) {
            $scope.selectedSettings = [];
            var listData = $scope.listData[0].ObjSMTP;
            angular.forEach(listData, function (val, key) {
                if (typeof $scope.selectedSettings[key]) {
                    $scope.selectCategory(val, key);
                }
            });
        } else {
            angular.forEach($scope.selectedSettings, function (val, key) {
                $scope.selectCategory(val, key);
            });
        }    
                
    };
    
    $scope.SetSmtpSettingStatus = function (action) {
        var settingstatus = '';
        if (action == "active") {
            settingstatus = 2;
            $rootScope.confirmationMessage = Sure_Active+' ?';
        }else if (action == "inactive") {
            settingstatus = 1;
            $rootScope.confirmationMessage = Sure_Inactive+' ?';
        }else if (action == "delete") {
            settingstatus = 3;
            $rootScope.confirmationMessage = Sure_Delete+' ?';
        }
        
        $scope.smtpSettingIds = [];
        $scope.smtpSettingIds.push($scope.emailsettingid);
        openPopDiv('confirmeSingleSmtpSetting', 'bounceInDown');
        $scope.settingstatus = settingstatus;
        $scope.smtpaction = action;
        
    };
    
    $rootScope.updateSingleSmtpSettingStatus = function () {
        var reqData = {
            setting_ids: $scope.smtpSettingIds,
            status: $scope.settingstatus,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeSingleSmtpSetting', 'bounceOutUp');
        showLoader();
        emailSettingData.updateSmtpSettingStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                //Reset all
                $scope.smtpSettingIds = [];

                var msg =  $scope.smtpaction;
                msg = ucwords(msg);
                ShowSuccessMsg(msg+" successfully.");

                $scope.smtpEmails();
            
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
    
    $scope.SetMultipleSmtpSettingStatus = function (action) {
        var settingstatus = '';
        if (action == "delete") {
            settingstatus = 3;
            $rootScope.confirmationMessage = Sure_Delete+' ?';
        }
        
        openPopDiv('confirmeMultipleSettingPopup', 'bounceInDown');
        $scope.smtpSettingIds = [];
        $scope.smtpSettingIds = Object.keys($scope.selectedSettings);//$scope.selectedMedia;
        $scope.settingstatus = settingstatus;
        $scope.smtpaction = action;  
        //console.log($scope.smtpSettingIds);
    };
    
    $rootScope.updateMultipleSmtpSettingStatus = function () {
        var reqData = {
            setting_ids: $scope.smtpSettingIds,
            status: $scope.settingstatus,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeMultipleSettingPopup', 'bounceOutUp');
        showLoader();
        emailSettingData.updateSmtpSettingStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                //Reset all
                $scope.indexToUpdate = {};
                $scope.smtpSettingIds = {};
                $scope.globalChecked = true;
                $scope.globalCheckBox();
                $scope.selectedSettings = {};

                var msg =  $scope.smtpaction;            
                msg = ucwords(msg);
                ShowSuccessMsg(msg+" successfully.");

                $scope.smtpEmails();
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
    
});


// Smtp Emails Controller
app.controller('SmtpEmailsCtrl', function ($scope, $rootScope, emailSettingData, $window, $timeout) {
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.emailaction = '';
    $scope.EmailStatus = {};
    $rootScope.smtpDetails = {};
    $rootScope.totalEmails = 0;
    $rootScope.emaildata = {};
    $scope.selectedSettingId = 1;
    $scope.totalRecord = 0;
    $scope.emailSetting = {};
        
    $scope.smtpEmailList = function () {
        intilizeTooltip();
        showLoader();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for smtp listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getEmailTypeList in services.js file
        emailSettingData.getEmailTypeList(reqData).then(function (response) {
            $scope.listData = [];
            if (response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $rootScope.totalEmails = $scope.totalRecord = $scope.noOfObj;

                //If no. of records greater then 0 then show            
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#SmtpEmailsCtrl table>tbody').append('<tr id="noresult_td"><td colspan="6"><div class="no-content text-center"><p>'+ThereIsNoEmailToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                
                //Push data into Controller in view file
                $scope.listData.push({ObjSMTP: response.Data.results});
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
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.smtpEmailList();
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
            SortBy: $scope.sort_by,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        $scope.smtpEmailList();
    });
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    };    
    
    //Function for set emails details in scope variables
    $scope.SetEmailDetail = function (emaillist) {
        //console.warn(emaillist);
        $scope.EmailStatus = emaillist.statusid;
        $scope.emailtypeid = emaillist.emailtypeid;
        $rootScope.mod = emaillist;
        $scope.selectedSettingId = emaillist.emailsettingid;
        //console.log(emaillist);
    };
    
        
    $scope.SetSmtpEmailStatus = function (action) {
        var emailstatus = '';
        if (action == "active") {
            emailstatus = 2;
            $rootScope.confirmationMessage = Sure_Active+'?';
        }else if (action == "inactive") {
            emailstatus = 1;
            $rootScope.confirmationMessage = Email_Sure_Inactive+'?';
        }
        
        $scope.smtpEmailIds = [];
        $scope.smtpEmailIds.push($scope.emailtypeid);
        openPopDiv('confirmeSingleSmtpEmailPopup', 'bounceInDown');
        $scope.emailstatus = emailstatus;
        $scope.emailaction = action;
        
    };
    
    $scope.updateSmtpEmailStatus = function () {
        var reqData = {
            emailtype_ids: $scope.smtpEmailIds,
            status: $scope.emailstatus,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeSingleSmtpEmailPopup', 'bounceOutUp');
        showLoader();
        emailSettingData.updateSmtpEmailsStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                //Reset all
                $scope.smtpSettingIds = [];

                var msg =  $scope.emailaction;
                msg = ucwords(msg);
                ShowSuccessMsg(msg+" successfully.");
                
                $scope.smtpEmailList();
            
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
    
    $scope.EmailSettingParams = function(){
        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        var reqData = {
            //Send AdminLoginSessionKey
            AdminLoginSessionKey : AdminLoginSessionKey
        };
        emailSettingData.getEmailDataService(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {                
                $scope.emailSetting = response.Data.result.email_setting;
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
        });
    };
    
    $scope.editSmtpEmailDetails = function () {
        openPopDiv('editEmailTypePopup', 'bounceInDown');
        
        $($scope.emailSetting).each(function(k,v){
            if(v.EmailSettingID == $scope.selectedSettingId){
                $scope.selectedSettingId = v;
                $('#EmailSettingID').trigger("chosen:updated");
            }
        });
    };
   
    $scope.updateEmailDetails = function (emailtypeid) {
                
        var EmailTypeId = $scope.mod.emailtypeid;
        var Subject = $scope.mod.subject;
        var EmailSettingID = $("#EmailSettingID").val();
        
        if (Subject.length <= 0) {
            $scope.showSubjectError = true;
            $scope.errorSubjectMessage = 'Please enter Subject';
        } else if (EmailSettingID == 0) {
            $scope.showTypeError = true;
            $scope.errorTypeMessage = 'Please select Email';
        } else {
            $('.loader_email').show();
            //send message
            $scope.showSubjectError = false;
            $scope.errorSubjectMessage = null;
            $scope.showTypeError = false;
            $scope.errorTypeMessage = null;
            var reqData = {
                Subject: Subject,
                EmailSettingID: EmailSettingID,
                EmailTypeId: EmailTypeId,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            emailSettingData.updateEmailDetails(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    ShowSuccessMsg("Detail update successfully.");
                    
                    $scope.smtpEmailList();
                    closePopDiv('editEmailTypePopup', 'bounceOutUp');
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);
                    closePopDiv('editEmailTypePopup', 'bounceOutUp');
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.showTypeError = true;
                    $scope.errorTypeMessage = response.Message;
                }
                $('.loader_email').hide();
            });

            $timeout(function () {
                $scope.showSubjectError = false;
                $scope.errorSubjectMessage = null;
                $scope.showTypeError = false;
                $scope.errorTypeMessage = null;
            }, 5000);
        }
    };
    
});