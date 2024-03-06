// User Controller for get Media Data
app.controller('mediaAbuseCtrl', function ($scope, mediaAbuseData, $rootScope, $document, $window, $filter) {
    $scope.mediaSummary = {};
    $scope.items = {};
    $scope.user = {};
    $scope.mediaListData = [];
    $scope.mediaList = [];
    $scope.IsAdminApproved = 0;
    //$scope.unApproveAct = 'selected';
    $scope.popup = {};
    $scope.selectedMedia = {};
    $scope.selectedMediaIndex = {};
    $scope.indexToUpdate = {};
    $scope.statusMediaIds = [];
    $scope.mediastatus = null;
    $scope.globalChecked = false;
    $scope.loadMediaOnce = false;
    $scope.filteredMedia = {};
    $scope.allLoaded = false;
    $scope.searchBox = {};
    $scope.isSearchSelected = [];
    $scope.criteria ={} ;
    $scope.criteriaList = [];
    $scope.filt = {};
    $scope.filt.IsAdminApprovedFilter = 0;
    $scope.filterData = {};
    $scope.filterData.Devices = [];
    $scope.filterData.Extensions = [];
    $scope.filterData.Sources = [];
    $scope.filterData.Types = [];
    $scope.filterData.Sizes = [];
    $scope.totalRecords = 0;    
    $scope.totalAbuseMedia = 0;
    $scope.filterSections = ['upload_devices','media_extensions','media_sections','media_size','media_source'];
    
    $scope.sortOrder = '-CreatedDate';
    $scope.CreatedDateOrder = '-';
    $scope.SizeOrder = '-';
    $scope.AbuseCountOrder = '-';
    $scope.DateSortFirst = 0;

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    
    $scope.getMediaSummary = function () {
        //Make request data parameter for users listing
        var reqData = {
            criteriaList: $scope.criteriaList,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        mediaAbuseData.getSummary(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                $scope.mediaSummary = response.Data.totalPictures;
                if ($scope.loadMediaOnce == false) {
                    $scope.loadMedia('noChange');
                    $scope.loadMediaOnce = true;
                }
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
        });
    }
    
    $scope.sortMedia = function(Name,Order){
        if(Name == "CreatedDate" && Order == "-"){
            $scope.CreatedDateOrder = '+';
            if($scope.DateSortFirst == 0)
                Order = '+';
        }else{            
            $scope.CreatedDateOrder = '-';
            
            if($scope.DateSortFirst == 0)
                Order = '-';
        }
        
        if(Name == "Size" && Order == "-")
            $scope.SizeOrder = '+';
        else
            $scope.SizeOrder = '-';
        
        if(Name == "AbuseCount" && Order == "-")
            $scope.AbuseCountOrder = '+';
        else
            $scope.AbuseCountOrder = '-';
        
        $scope.sortOrder = Order+Name;
        
        if(Name == "Size" || Name == "AbuseCount")
            $scope.DateSortFirst = 1;
    };
    
    $scope.$on('getUserEvent', function (event, data) {
        $scope.getMediaSummary();
        $scope.getSearchBox();
    });
    
    
    //Attache media load on scroll
    angular.element($document).bind("scroll", function () {
        var listLen = Object.keys($scope.mediaList).length;
        if (listLen < $scope.totalRecords) {
            if ($window.getScrollTop() + $window.innerHeight === $document.height()) {
                $scope.loadMedia();
            }
        }
    });

    //load media
    //Parameter is used for check this function is call from status change event of images
    $scope.loadMedia = function (Status) {
        $scope.filterDataCheck = $scope.filterData;
        $scope.filterData = {};
        $scope.filterData.Devices = [];
        $scope.filterData.Extensions = [];
        $scope.filterData.Sources = [];
        $scope.filterData.Types = [];
        $scope.filterData.Sizes = [];
        intilizeTooltip();
        showLoader();
        $scope.selectedMedia = {};
        $scope.globalChecked = false;
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = Object.keys($scope.mediaList).length;
        if(Status == 'Change'){
            var begins = 0;
            $scope.mediaList = [];
        }

        if($('.selected-devices').length>0){
            $('.selected-devices').each(function(e){
                if($('.selected-devices:eq('+e+')').hasClass('selected')){
                    $scope.filterData.Devices.push($('.selected-devices:eq('+e+')').attr('id').split('-')[1]);
                }
            });
        }
        if($('.selected-extensions').length>0){
            $('.selected-extensions').each(function(e){
                if($('.selected-extensions:eq('+e+')').hasClass('selected')){
                    $scope.filterData.Extensions.push($('.selected-extensions:eq('+e+')').attr('id').split('-')[1]);
                }
            });
        }
        if($('.selected-source').length>0){
            $('.selected-source').each(function(e){
                if($('.selected-source:eq('+e+')').hasClass('selected')){
                    $scope.filterData.Sources.push($('.selected-source:eq('+e+')').attr('id').split('-')[1]);
                }
            });
        }
        if($('.selected-sections').length>0){
            $('.selected-sections').each(function(e){
                if($('.selected-sections:eq('+e+')').hasClass('selected')){
                    $scope.filterData.Types.push($('.selected-sections:eq('+e+')').attr('id').split('-')[1]);
                }
            });
        }
        if($('.selected-sizes').length>0){
            $('.selected-sizes').each(function(e){
                if($('.selected-sizes:eq('+e+')').hasClass('selected')){
                    $scope.filterData.Sizes.push($('.selected-sizes:eq('+e+')').attr('id').split('-')[1]);
                }
            });
        }

        //Make request data parameter for users listing
        var reqData = {
            Begin: begins,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey,
            FilterData: $scope.filterData
        }

        //Call getUserlist in services.js file
        mediaAbuseData.getMedia(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                $scope.noOfObj = response.Data.total_records;
                $scope.totalAbuseMedia = response.Data.total_records;
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                //Push data into Controller in view file
                $scope.mediaListData = response.Data.results;//({ObjMedia: response.Data.results});

                console.log($scope.mediaListData);
                if ($.isEmptyObject(response.Data.results) == false) {
                    $scope.mediaList.push.apply($scope.mediaList, response.Data.results);
                }
                $scope.totalRecords = response.Data.total_records;
                
                setTimeout(
                    function () {
                        $('.icon-zoomlist').lightBox();
                    }, 1000
                );

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

    /**
     * Search by approve or not
     * @param {type} field
     * @param {type} val
     * @returns {undefined}
     */
    $scope.searchBy = function (field, val) {
        $scope[field] = val;
        $scope.globalChecked = !$scope.globalChecked;
        $scope.globalCheckBox();
    }

    /**
     * Open Image in model
     * @param {type} media
     * @returns {undefined}
     */
    $scope.openLightBox = function (media) {
        $scope.popup = media;
        openPopDiv('mediaImagePopup', 'bounceInDown');
    }

    /**
     * Set li selected
     * @param {type} media
     * @returns {undefined}
     */
    $scope.selectCategory = function (media) {
        var mIndex = $scope.mediaList.indexOf(media);
        if (media.MediaID in $scope.selectedMedia) {
            delete $scope.selectedMedia[media.MediaID];
            delete $scope.selectedMediaIndex[mIndex];
        } else {
            $scope.selectedMedia[media.MediaID] = media;
            $scope.selectedMediaIndex[mIndex] = mIndex;
        }
        if (Object.keys($scope.selectedMedia).length > 0) {
            setTimeout(function(){ $scope.globalChecked == true; }, 1);
            $('#buttonGroup').fadeIn();
        } else {
            $scope.globalChecked = false;
            $('#buttonGroup').fadeOut();
        }
        
        setTimeout(function(){
            if($(".view-listing li.selected").length == $scope.mediaList.length){
                setTimeout(function(){ $scope.globalChecked = true; }, 1);
                $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
            }else{
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
            }
        }, 1);
    }

    /**
     * SHow selected css
     * @param {type} media
     * @returns {undefined}
     */
    $scope.isSelected = function (media) {
        if (media.MediaID in $scope.selectedMedia) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;
        }
    };

    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;        
        if ($scope.globalChecked) {
            $scope.selectedMedia = [];
            var mediaLists = $scope.mediaListData;
            angular.forEach(mediaLists, function (val, key) {
                if (typeof $scope.selectedMedia[key]) {
                    $scope.selectCategory(val, key);
                }
            });
        } else {
            angular.forEach($scope.selectedMedia, function (val, key) {
                $scope.selectCategory(val, key);
            });
        }
    }

    /**
     * Update media status
     * @media {object} media
     * @param {string} action delete/approve 
     * @returns {undefined}
     */
    $scope.updateMedia = function (media, action) {
        if (action == "approve") {
            $scope.confirmationMessage = Sure_Approve+'?';
        } else {
            $scope.confirmationMessage = Sure_Delete+'?';
        }
        openPopDiv('confirmeMediaPopup', 'bounceInDown');
        $scope.statusMediaIds = [];
        $scope.indexToUpdate = {};
        $scope.statusMediaIds.push(media.MediaID);
        $scope.mediastatus = action;
        var mIndex = $scope.mediaList.indexOf(media);
        $scope.indexToUpdate[mIndex] = mIndex;
    }

    $scope.updateMultipleMedia = function (action) {
        if (action == "approve") {
            $scope.confirmationMessage = Sure_Approve+'?';
        } else {
            $scope.confirmationMessage = Sure_Delete+'?';
        }
        openPopDiv('confirmeMediaPopup', 'bounceInDown');
        $scope.statusMediaIds = {};
        $scope.indexToUpdate = {};
        $scope.statusMediaIds = Object.keys($scope.selectedMedia);
        $scope.mediastatus = action;

        angular.forEach($scope.selectedMedia, function (media, key) {
            var mIndex = $scope.mediaList.indexOf(media);
            $scope.indexToUpdate[mIndex] = mIndex;
        })
    }

    $scope.setStatus = function () {
        var reqData = {
                media: $scope.statusMediaIds,
                action: $scope.mediastatus, 
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
        closePopDiv('confirmeMediaPopup', 'bounceOutUp');
        showLoader();
        mediaAbuseData.setStatus(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                //on delete remove images
                if ($scope.mediastatus == 'delete') {
                    angular.forEach($scope.indexToUpdate, function (val, key) {
                        $scope.mediaList.splice(key, 1);
                        $scope.getMediaSummary();
                        $scope.getSearchBox();
                    })
                } else if ($scope.mediastatus == 'approve') {
                    angular.forEach($scope.indexToUpdate, function (val, key) {
                        $scope.mediaList[val].IsAdminApproved = 1;
                        $scope.mediaList.splice(key, 1);
                        $scope.getMediaSummary();
                        $scope.getSearchBox();
                    })
                }
                //Reset all
                $scope.indexToUpdate = {};
                $scope.statusMediaIds = {};
                $scope.globalChecked = true;
                $scope.globalCheckBox();
                $scope.selectedMedia = {};
                $scope.selectedMediaIndex = {};
                
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
    }
    
    $scope.getSearchBox = function(){
        $scope.criteria = {};
        $scope.criteriaList = [];
        var reqData = {
            approved: $scope.IsAdminApproved,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        mediaAbuseData.getCount(reqData).then(function (response) {
            $scope.searchBox = response.Data;
        });
    }



    $scope.addToSearch = function(key, val, add, index, dataKey){
        val.index = index;
        val.key = key;
        val.dataKey = dataKey;
        if(add){
            val.selected = true;
            if(typeof $scope.criteria[key]!='undefined'){
                $scope.criteria[key][val[key]]= val;
            }else{
                $scope.criteria[key] = new Array();
                $scope.criteria[key][val[key]] = val;
            }
            $scope.criteriaList.push(val);
        }else{
            if(typeof $scope.criteria[key]!='undefined'){
                keyIndex = $scope.criteria[key].indexOf(val);
                $scope.criteria[key].splice(keyIndex, 1);
            }
            keyInd = $scope.criteriaList.indexOf(val);
            $scope.criteriaList.splice(keyInd, 1);
        }
        //$scope.getMediaSummary();
    }
    
    $scope.applyFilter = function (action) {
        if(action=='search'){
            $scope.filteredMedia = $filter('searchMedia')($scope.mediaList, $scope.criteria);
        }else{
            $scope.criteria = {};
            $scope.criteriaList = [];
        }
        angular.element('.filter-view').slideUp()
    };
    
    $scope.resetFilter = function(){
        $('.filter-view .selected').removeClass('selected');
        $scope.criteria = {};
        $scope.criteriaList = [];
        $scope.loadMediaWithFilter();         
        $('#showHidefilter').trigger('click');
    }
    
    $scope.removeFromCriteria = function (criteria, index){
        $scope.criteriaList.splice(index, 1);
        if(typeof $scope.criteria[criteria.key]!='undefined'){
            var key = criteria.key;
            var inedex = criteria.index;
            if(angular.isUndefined($scope.criteria[key][inedex])){$scope.criteria[key].splice(inedex, 1);}
            $scope.criteria[key].splice(inedex, 1);
            dataKey = criteria.dataKey;
            $scope.searchBox[dataKey][inedex].selected = false;
        }
    }

    $scope.loadMediaWithFilter = function(){
        $scope.mediaList = [];
        $scope.loadMedia();
    }
    
    
    $scope.initMedia = function(){
        pathname = location.pathname.split('/');
        if(pathname[pathname.length-1]=='media'){
            $rootScope.tabSelected = 'media';
            $scope.getMediaSummary();
            $scope.getSearchBox();
        }
    }
    
    //Function for view user profile of a particular user
    $scope.viewUserProfile = function (userguid) {
        //If UserGUID is Undefined
        if (typeof userguid === 'undefined') {
            userguid = $('#hdnUserGUID').val();
        }
        //Useful for set breadcrumb
        $window.location.href = base_url + 'admin/users/user_profle/' + userguid;
    }
    
    $scope.getMediaSummary();
    $scope.getSearchBox();
    $scope.initMedia();
});


app.filter('searchMedia', [function () {
        return function (mediaList, criteria) {
            var tempList = [];

            if (!angular.isUndefined(criteria) && !angular.isUndefined(mediaList) && Object.keys(criteria).length > 0) {
                angular.forEach(criteria, function (obj, keyField) {
                    angular.forEach(obj, function (val, key) {
                        //console.log(keyField);console.log(val);console.log(key);
                        angular.forEach(mediaList, function (media, keyIndex) {
                            if (media[keyField] == val[keyField]) {
                                //.console.log(media);
                                tempList.push(media);
                            }
                        });
                    });
                });
                if (tempList.length <= 0) {
                    tempList = mediaList;
                }
            } else {
                tempList = mediaList;
            }
            return tempList;
        };
    }]);


app.controller('mediaAbuseDetailCtrl', function ($scope, mediaAbuseData, $rootScope, $document, $window, $filter) {
    $scope.mediaDetail = {};
    $scope.MediaId = '';
    $scope.mediaComments = [];
    $scope.statusMediaIds = [];
    $scope.mediastatus = null;
    
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    
    //for get abuse media details
    $scope.getAbuseDetail = function () {
        $scope.MediaId = $("#hdnMediaId").val();
        
        //Make request data parameter for users listing
        var reqData = {
            MediaId: $scope.MediaId,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getUserlist in services.js file
        mediaAbuseData.getAbuserMediaDetail(reqData).then(function (response) {    
            if (response.ResponseCode == 200) {
                //Push data into Controller in view file
                $scope.mediaDetail = response.Data.results;//({ObjMedia: response.Data.results});
                
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
        }), function (error) {
            alert('Invalid Operation!!');
        }
    };
    
    //for get abuse media comments
    $scope.getAbuseMediaComments = function () {
        $scope.MediaId = $("#hdnMediaId").val();
        
        //Make request data parameter for users listing
        var reqData = {
            MediaId: $scope.MediaId,
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        
        //Call getUserlist in services.js file
        mediaAbuseData.getAbuserMediaComments(reqData).then(function (response) {    
            if (response.ResponseCode == 200) {
                //Push data into Controller in view file
                $scope.mediaComments = response.Data.results;//({ObjMedia: response.Data.results});
                //addListScroll('commentList');
                //console.log($scope.mediaComments);
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
            
        }), function (error) {
            alert('Invalid Operation!!');
        }
    };
    
    /**
     * Update media status
     * @media {object} media
     * @param {string} action delete/approve 
     * @returns {undefined}
     */
    $scope.updateMedia = function (media, action) {
        if (action == "approve") {
            $scope.confirmationMessage = 'Are you sure you want to approve';
        } else {
            $scope.confirmationMessage = 'Are you sure you want to delete';
        }
        openPopDiv('confirmeMediaPopup', 'bounceInDown');
        $scope.statusMediaIds = [];
        $scope.statusMediaIds.push(media.MediaID);
        $scope.mediastatus = action;
    };
    
    $scope.setStatus = function () {
        var reqData = {
                media: $scope.statusMediaIds,
                action: $scope.mediastatus, 
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
        closePopDiv('confirmeMediaPopup', 'bounceOutUp');
        showLoader();
        mediaAbuseData.setStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
                //on delete remove images
                if ($scope.mediastatus == 'delete') {
                    ShowSuccessMsg(" Delete successfully.");
                } else if ($scope.mediastatus == 'approve') {
                    ShowSuccessMsg(" Approve successfully.");
                }

                setTimeout(
                    function () {
                        $window.location.href = base_url + 'admin/media/media_abused';
                    }, 5000
                );
                //Reset all
                $scope.statusMediaIds = {};
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
    }
    
    $scope.initAbuseDetail = function(){
        $scope.getAbuseDetail();
        $scope.getAbuseMediaComments();
    }
    
    //Function for view user profile of a particular user
    $scope.viewUserProfile = function (userguid) {
        //If UserGUID is Undefined
        if (typeof userguid === 'undefined') {
            userguid = $('#hdnUserGUID').val();
        }
        //Useful for set breadcrumb
        $window.location.href = base_url + 'admin/users/user_profle/' + userguid;
    }
    
    $scope.initAbuseDetail();
});