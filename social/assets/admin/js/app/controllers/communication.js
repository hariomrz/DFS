
// Communication Controller for show detail on overview tab
app.controller('communicationCtrl', function ($scope, communicationData, $rootScope) {
    $scope.totalRecord = 0;
    $scope.user = {};
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.noOfObj = 0;
    $scope.startWatch = false;
    $scope.listData = []
    //Get user service data
    $scope.$on('getUserEvent', function (event, data) {
        $scope.user = data;
        $scope.startWatch = true;
        $scope.userCommunication();
    });

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    $scope.userCommunication = function () {
        intilizeTooltip();
        showLoader();

        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        //Make request data parameter for users listing
        var reqData = {
            Begin: begins,
            End: $scope.numPerPage,
            userId: $scope.user.userid,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }

        var reqUrl = reqData[1]
        //Call getUserlist in services.js file
        $scope.shownocomrecord = false;
        communicationData.getCommunication(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200)
            {
                $scope.noOfObj = response.Data.total_records;
                $scope.totalRecord = $scope.noOfObj;
                hideLoader();
                //Push data into Controller in view file
                $scope.listData.push({ObjComms: response.Data.results});
                
                if ($scope.noOfObj == 0) {
                    $scope.shownocomrecord = true;
                    $('.simple-pagination').hide();
                }
            
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }

        }), function (error) {
            hideLoader();
        }
    };

    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };

    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {
        if ($scope.startWatch) {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
            reqData = {
                Begin: begins,
                End: $scope.numPerPage,
                userId: $scope.user.userid,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            $scope.userCommunication();
        }
    });

    $scope.summaryPopup = function (index) {
        var commSummary = $scope.listData[0].ObjComms[index];
        angular.element('#summaryPopup').find('#summarySubject').html(commSummary.subject);
        angular.element('#summaryPopup').find('#summaryCreatedDate').html(commSummary.created_date);
        angular.element('#summaryPopup').find('#summaryBody').html(commSummary.body);
        openPopDiv('summaryPopup', 'bounceInDown');
    }
});

// Controller for get Communication Tab Data
app.controller('communicationTabCtrl', function ($scope, communicationData, $rootScope) {
    $scope.totalRecord = 0;
    $scope.user = {};
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.noOfObj = 0;
    $scope.startWatch = false;
    $scope.listData = [];
    //Get user service data
    $scope.$on('getUserEvent', function (event, data) {
        $scope.user = data;
        $scope.startWatch = true;
        //$scope.userCommunication();
    });

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    $scope.userCommunication = function () {
        intilizeTooltip();
        showLoader();

        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        //Make request data parameter for users listing
        var reqData = {
            Begin: begins,
            End: $scope.numPerPage,
            userId: $scope.user.userid,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }

        var reqUrl = reqData[1]
        //Call getUserlist in services.js file
        $scope.shownocomrecord = false;
        communicationData.getCommunication(reqData).then(function (response) {
            $scope.listData = []
            if(response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records;
                $scope.totalRecord = $scope.noOfObj;
                hideLoader();
                //Push data into Controller in view file
                $scope.listData.push({ObjComms: response.Data.results});
                
                if ($scope.noOfObj == 0) {
                    $scope.shownocomrecord = true;
                }
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }

        }), function (error) {
            hideLoader();
        }
    };

    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };

    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {
        if ($scope.startWatch) {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
            reqData = {
                Begin: begins,
                End: $scope.numPerPage,
                userId: $scope.user.userid,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            $scope.userCommunication();
        }
    });

    $scope.showPopup = function (index) {
        var commSummary = $scope.listData[0].ObjComms[index];
        angular.element('#readMessage').find('#readSubject').html(commSummary.subject);
        angular.element('#readMessage').find('#readDate').html(commSummary.created_date);
        angular.element('#readMessage').find('#readBody').html(commSummary.body);
        openPopDiv('readMessage', 'bounceInDown');
    }
});


// User Controller for get Message
app.controller('messageCtrl', function ($scope, $timeout, communicationData) {
    $scope.showError = false;
    $scope.errorMessage = null;
    $scope.user = {};
    //Get user service data
    $scope.$on('getUserEvent', function (event, data) {
        $scope.user = data;
    });

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    
    /**
     * Send Communication mesage
     * @param {type} user
     * @returns {undefined}
     */
    $scope.sendEmail = function (user,permissiontype) {
        var subject = angular.element('#emailSubject').val();
        //var message = CKEDITOR.instances.description.getData('description');
        //var message = $('#description').code();
        var message = $('#description').summernote('code');
        var userID = $('#hdnUserID').val();
        subject = $.trim(subject);        
        var isValid = 1;
        
        if (subject.length <= 0) {
            $scope.showError = true;
            $scope.errorMessage = 'Please enter Subject';
            isValid = 0;
        }else if (subject.length >= 100) {
            $scope.showError = true;
            $scope.errorMessage = 'The subject field can not exceed 100 characters in length.';
            isValid = 0;
        }
        
        if (message.length <= 0 || message == '<p><br></p>' || message == '<span><br></span>') {
            $scope.showMessageError = true;
            $scope.errorBodyMessage = 'Please enter Message';
            isValid = 0;
        } 
        if(isValid == 1){
            $('.loader_communication').show();
            //send message
            $scope.showError = false;
            $scope.errorMessage = null;
            $scope.showMessageError = false;
            $scope.errorBodyMessage = null;
            var reqData = {
                subject: subject,
                message: message,
                permissiontype:permissiontype,
                UserID: userID,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            communicationData.sendCommunication(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    $("#spn_noti").html("");
                    sucessMsz();
                    $("#spn_noti").html("  Message send successfully.");
                    angular.element('#emailSubject').val('');
                    //CKEDITOR.instances.description.setData('');
                    //$('#description').code('');
                    $('#description').summernote('code','');
                    $('.loader_communication').hide();
                    $('#communicate_single_user').modal('hide');
                    //closePopDiv('communicate_single_user', 'bounceOutUp');
                    if($('#communicationTabCtrl').length>0)
                    {
                        angular.element(document.getElementById('communicationTabCtrl')).scope().userCommunication();
                    }
                    if($('#communicate_li').length>0)
                    {
                        angular.element(document.getElementById('communicate_li')).scope().userCommunication();
                    }    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    angular.element('#emailSubject').val('');
                    //CKEDITOR.instances.description.setData('');
                    $('.communicate-footer .note-editable').html('');
                    $('.loader_communication').hide();
                    $('#communicate_single_user').modal('hide');
                    //closePopDiv('communicate_single_user', 'bounceOutUp');
                    //Show error message
                    PermissionError(response.Message);                
                }else if(response.ResponseCode == 104){
                    angular.element('#emailSubject').val('');
                    //CKEDITOR.instances.description.setData('');
                    //$('#description').code('');
                    $('#description').summernote('code','');
                    $('.loader_communication').hide();
                    $('#communicate_single_user').modal('hide');
                    //closePopDiv('communicate_single_user', 'bounceOutUp');
                    ShowErrorMsg(response.Message);
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.showError = true;
                    $scope.errorMessage = response.Message;
                    $scope.showMessageError = true;
                }
                $('.loader_communication').hide();
            });
        }
        
        $timeout(function () {
            $scope.showError = false;
            $scope.errorMessage = null;
            $scope.showMessageError = false;
            $scope.errorBodyMessage = null;
        }, 5000);
    };
    
    /**
     * Send Communication mesage
     * @param {type} user
     * @returns {undefined}
     */
    $scope.sendEmailToMultipleUsers = function (permissiontype) {
        var subject = angular.element('#Subject').val();
        //var message = CKEDITOR.instances.communication_description.getData('communication_description');
        //var message = $('#communication_description').code();
        var message = $('#communication_description').summernote('code');
        subject = $.trim(subject);
        var users = $('#hdnUsersId').val();
        var isValid = 1;
        
        if (subject.length <= 0) {
            $scope.showError = true;
            $scope.errorMessage = 'Please enter Subject';
            isValid = 0;
        }else if (subject.length >= 100) {
            $scope.showError = true;
            $scope.errorMessage = 'The subject field can not exceed 100 characters in length.';
            isValid = 0;
        }
        
        if (message.length <= 0 || message == '<p><br></p>' || message == '<span><br></span>') {
            $scope.showMessageError = true;
            $scope.errorBodyMessage = 'Please enter Message';
            isValid = 0;
        } 
        if(isValid == 1){
            $('.loader_communication').show();
            //send message
            $scope.showError = false;
            $scope.errorMessage = null;
            $scope.showMessageError = false;
            $scope.errorBodyMessage = null;
            var reqData = {
                subject: subject,
                message: message,
                users: users,
                permissiontype:permissiontype,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            
            var CrmUserListCtrl = angular.element('#CrmUserListCtrl').scope();
            if(CrmUserListCtrl !== null && CrmUserListCtrl && 'getCrmSelectedUsersRequest' in CrmUserListCtrl) {
                var crmRqObj = CrmUserListCtrl.getCrmSelectedUsersRequest();
                if(Object.keys(crmRqObj).length) {
                    crmRqObj = angular.extend({}, crmRqObj, reqData);
                    reqData = crmRqObj;
                }
            }
            
            
            communicationData.sendMultipleCommunication(reqData).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    //Show Success message
                    $("#spn_noti").html("");
                    sucessMsz();
                    $("#spn_noti").html("  Message send successfully.");
                    angular.element('#Subject').val('');
                    //CKEDITOR.instances.communication_description.setData('');
                    //$('#communication_description').code('');
                    $('#communication_description').summernote('code','');
                    
                    if('closeCrmModel' in window) {
                        closeCrmModel('communicateMultiple')
                    } else {
                    closePopDiv('communicateMultiple', 'bounceOutUp');  
                    }
                    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    angular.element('#emailSubject').val('');
                    //CKEDITOR.instances.description.setData('');
                    //$('#communication_description').code('');
                    $('#communication_description').summernote('code','');
                    if('closeCrmModel' in window) {
                        closeCrmModel('communicateMultiple')
                    } else {
                    closePopDiv('communicateMultiple', 'bounceOutUp');
                    }
                    //Show error message
                    PermissionError(response.Message);                
                }else if(response.ResponseCode == 104){
                    angular.element('#emailSubject').val('');
                    //CKEDITOR.instances.description.setData('');
                    //$('#communication_description').code('');
                    $('#communication_description').summernote('code','');
                    if('closeCrmModel' in window) {
                        closeCrmModel('communicateMultiple')
                    } else {
                    closePopDiv('communicateMultiple', 'bounceOutUp');
                    }
                    ShowErrorMsg(response.Message);
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.showError = true;
                    $scope.errorMessage = response.Message;
                    $scope.showMessageError = true;
                }
                $('.loader_communication').hide();
            });
        }
        
        $timeout(function () {
            $scope.showError = false;
            $scope.errorMessage = null;
            $scope.showMessageError = false;
            $scope.errorBodyMessage = null;
        }, 5000);
    }
});
