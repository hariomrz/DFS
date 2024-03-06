// Configuration Controller
app.controller('ConfigurationCtrl', function ($scope, configurationData) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.totalConfiguration = 0;
    $scope.IsInputValid = false;
    $scope.TrueFalseOptions = ["true","false"];
    $scope.AutoLogOutTime = ["30","60","90","120","150","180","210","240","270","300","330","360"];
    $scope.DateFormat = ["d M, Y","M d, Y","Y-M-d","d-M-Y","Y/m/d","m/d/Y"];
    $scope.TimeFormat = ["h:i A","h:i:s A","H:i","H:i:s"];
    $scope.DecimalPlaces = ["0","1","2","3","4"];
    $scope.WeekStartOn = ["0","1","2","3","4","5","6"];
    $scope.CultureInfo = ["en"];
    $scope.selectedConfigValue = false;
            
    $scope.getConfigurationList = function () {
        intilizeTooltip();
        showLoader();

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
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        //Call getConfigurationList in services.js file
        configurationData.getConfigurationList(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records
                $scope.totalConfiguration = $scope.totalRecord = $scope.noOfObj;

                //If no. of records greater then 0 then show            
                $('#noresult_td').remove();
                $('.simple-pagination').show();

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#ConfigurationCtrl table>tbody').append('<tr id="noresult_td"><td colspan="4"><div class="no-content text-center"><p>'+ThereIsNoRecordToShow+'</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }
                //Push data into Controller in view file
                $scope.listData.push({ObjConfig: response.Data.results});
                
                $scope.CultureInfo = response.Data.cultureInfo;
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
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
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            $scope.getConfigurationList();
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
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        $scope.getConfigurationList();
    });
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    };   
    
    //Function for set user id
    $scope.changeConfigValue = function (configlist) {
        $scope.ConfigurationName = configlist.ConfigurationName;
        $scope.BUConfigID = configlist.BUConfigID;
        $scope.ConfigValue = configlist.ConfigValue;
        $scope.DataType = configlist.DataTypeName;
        $scope.DataTypeID = configlist.DataTypeID;
        $scope.Description = configlist.Description;
        
        $scope.showConfigError = false;
        $scope.errorConfigMessage = null;
        
        if($scope.DataType == "Boolean"){
            $scope.ConfigSelectOptions = $scope.TrueFalseOptions;
            $scope.showSelectbox = true;
            $scope.selectedConfigValue = configlist.ConfigValue;
        }else{
            $scope.showSelectbox = false;
            $scope.selectedConfigValue = "";
            $("#CurrentConfigValue").val(configlist.ConfigValue);
        }
        
        if($scope.ConfigurationName == "AutoLogOutTime"){
            $scope.ConfigSelectOptions = $scope.AutoLogOutTime;
            $scope.showSelectbox = true;
            $scope.selectedConfigValue = configlist.ConfigValue;
        }else if($scope.ConfigurationName == "DateFormat"){
            $scope.ConfigSelectOptions = $scope.DateFormat;
            $scope.showSelectbox = true;
            $scope.selectedConfigValue = configlist.ConfigValue;
        }else if($scope.ConfigurationName == "DecimalPlaces"){
            $scope.ConfigSelectOptions = $scope.DecimalPlaces;
            $scope.showSelectbox = true;
            $scope.selectedConfigValue = configlist.ConfigValue;
        }else if($scope.ConfigurationName == "TimeFormat"){
            $scope.ConfigSelectOptions = $scope.TimeFormat;
            $scope.showSelectbox = true;
            $scope.selectedConfigValue = configlist.ConfigValue;
        }else if($scope.ConfigurationName == "WeekStartOn"){
            $scope.ConfigSelectOptions = $scope.WeekStartOn;
            $scope.showSelectbox = true;
            $scope.selectedConfigValue = configlist.ConfigValue;
        }else if($scope.ConfigurationName == "CultureInfo"){
            $scope.ConfigSelectOptions = $scope.CultureInfo;
            $scope.showSelectbox = true;
            $scope.selectedConfigValue = configlist.ConfigValue;
        }
        
        openPopDiv('updateConfigurationSettingPopup', 'bounceInDown');
    };
    
    $scope.IsEnter = 0;
    
    $scope.UpdateConfigurationDetails = function () {        
        //For validate inpute w.r.t. data types
        $scope.ValidateConfigInput()
            
        if ($scope.IsInputValid == true) {
            var BUConfigID = $scope.BUConfigID;
            var DataType = $scope.DataType;
            var ConfigValue = $("#CurrentConfigValue").val();
            
            if($scope.showSelectbox == true){
                ConfigValue = $scope.selectedConfigValue;
            }
            
            if ($scope.DataTypeID == '3') {
                ConfigValue = $scope.selectedConfigValue;
                ConfigValue = ConfigValue == "true" ? "1" : "0";
            }
            
            if (ConfigValue == '') {
                $scope.showConfigError = true;
                $scope.errorConfigMessage = 'Please enter Current Value';
            } else {
                $('.loader_ele').show();
                //send message
                $scope.showConfigError = false;
                $scope.errorConfigMessage = null;
                var reqData = {
                    BUConfigID: BUConfigID,
                    DataType: DataType,
                    ConfigValue: ConfigValue,
                    AdminLoginSessionKey :$scope.AdminLoginSessionKey
                };

                configurationData.UpdateConfigurationDetails(reqData).then(function (response) {
                    if (response.ResponseCode == 200){
                        //Show Success message
                        ShowSuccessMsg(response.Message);
                        
                        $scope.getConfigurationList();
                        closePopDiv('updateConfigurationSettingPopup', 'bounceOutUp');
                        
                    }else if(response.ResponseCode == 517){
                        redirectToBlockedIP();
                    }else if(response.ResponseCode == 598){
                        //Show error message
                        PermissionError(response.Message);
                        closePopDiv('updateConfigurationSettingPopup', 'bounceOutUp');
                    }else if(checkApiResponseError(response)){
                        ShowWentWrongError();
                    } else {
                        $scope.showConfigError = true;
                        $scope.errorConfigMessage = response.Message;
                    }
                    $('.loader_ele').hide();
                });
            }
        }else{
            $scope.showConfigError = true;
            $scope.errorConfigMessage = 'Please enter correct value.';
        }
    };
    
    $scope.ValidateConfigInput = function() {
        $scope.IsInputValid = false;
        var CurrentConfigValue = $('#CurrentConfigValue').val();
        if($scope.DataTypeID == '3'){
            if (CurrentConfigValue == "true" || CurrentConfigValue == "false")
                $scope.IsInputValid = true;
        }else if($scope.DataTypeID == '2'){
            $scope.IsInputValid = true;
        }else if($scope.DataTypeID == '1'){
            
            var isXml;
            try{
                isXml = $.parseXML(CurrentConfigValue);
            }catch(e){
                isXml = false;
            }
            
            if(isXml !== false){
                $scope.IsInputValid = true;
            }else{
                $scope.IsInputValid = false;
            }            
            
        }else{
            $scope.IsInputValid = false;
        }        
    };
    
});