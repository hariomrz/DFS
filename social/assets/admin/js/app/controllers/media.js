// Media Controller for get Media Data
app.controller('mediaCtrl', function ($scope, mediaData, $rootScope, $document, $window, $filter) {
    $scope.mediaSummary = {};
    $scope.items = {};
    $scope.user = {};
    $scope.mediaListData = [];
    $scope.mediaList = [];
    $scope.IsAdminApproved = 0;
    $scope.unApproveAct = 'selected';
    $scope.popup = {};
    $scope.selectedMedia = {};
    $scope.selectedMediaIndex = {};
    $scope.indexToUpdate = {};
    $scope.statusMediaIds = [];
    $scope.mediastatus = null;
    $scope.globalChecked = false;
    $scope.filteredMedia = {};
    $scope.allLoaded = false;
    $scope.searchBox = {};
    $scope.isSearchSelected = [];
    $scope.criteria = {};
    $scope.criteriaList = [];
    $scope.filt = {};
    $scope.filt.IsAdminApprovedFilter = 0;
    $scope.filterData = {};
    $scope.filterData.Devices = [];
    $scope.filterData.Extensions = [];
    $scope.filterData.Sources = [];
    $scope.filterData.Types = [];
    $scope.filterData.Sizes = [];
    $scope.service_call = false;
    $scope.sort = "MediaID";
    $scope.totalRecords = 0;
    $scope.filterSections = ['upload_devices','image_extensions','video_extensions','youtube_extensions','media_sections','media_size','media_source'];
    $scope.tempMedia = [];
    $scope.VideoMediaData = {};
    
    $scope.sortOrder = '-CreatedDate';
    $scope.CreatedDateOrder = '-';
    $scope.SizeOrder = '-';
    $scope.AbuseCountOrder = '-';
    $scope.DateSortFirst = 0;
    $scope.showapprovebtn = true;    

    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

    $scope.getMediaSummary = function () {
        userId = $scope.user.userid;
        var MediaPageName = $("#mediaPageName").val();
        
        mediaData.getSummary(userId,$scope.AdminLoginSessionKey,MediaPageName).then(function (response) {
            if (response.ResponseCode == 200){
                $scope.mediaSummary = response.Data;
                if($scope.service_call == false){
                    $scope.loadMedia();
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

    $scope.$on('getUserEvent', function (event, data) {
        $scope.user = data;
        //$scope.getMediaSummary();
        //$scope.getSearchBox();
    });
    
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


    //Attache media load on scroll
    angular.element($document).bind("scroll", function () {
        var listLen = Object.keys($scope.mediaList).length;
        var mediaTotalCount = $scope.mediaSummary.totalMedia;
        if($scope.filt.IsAdminApprovedFilter == 0)
            var mediaTotalCount = $scope.mediaSummary.totalUnapproved;
        else if($scope.filt.IsAdminApprovedFilter == 1)
            var mediaTotalCount = $scope.mediaSummary.totalApproved;
        else if($scope.filt.IsAdminApprovedFilter == 2)
            var mediaTotalCount = $scope.mediaSummary.totalMedia;
        
        if ($rootScope.tabSelected == 'media' && listLen < mediaTotalCount && listLen < $scope.totalRecords) {
            if ($window.getScrollTop() + $window.innerHeight === $document.height()) {
                //begins = (($scope.currentPage - 1) * $scope.numPerPage)
                if($scope.service_call == false){
                    $scope.loadMedia();
                }
                //$scope.searchBy()
            }
        }
    });

    $scope.loadMediaWithFilter = function(){
        $scope.mediaList = [];
        if($scope.service_call == false){
            $scope.tempMedia = [];
            $scope.loadMedia();
        }
    };

    //load media
    $scope.loadMedia = function () {
        $scope.filterDataCheck = $scope.filterData;
        $scope.filterData = {};
        $scope.filterData.Devices = [];
        $scope.filterData.Extensions = [];
        $scope.filterData.Sources = [];
        $scope.filterData.Types = [];
        $scope.filterData.Sizes = [];
        intilizeTooltip();
               
        $scope.selectedMedia = {};
        $scope.globalChecked = false;
        $scope.service_call = true;
        $('#buttonGroup').fadeOut();
        $(".media_loader").show();
        
        var mediaPageName = $("#mediaPageName").val();
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = Object.keys($scope.tempMedia).length;

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
            userId: $scope.user.userid,
            Begin: begins,
            FilterData: $scope.filterData,
            IsAdminApproved: $scope.filt.IsAdminApprovedFilter,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey,
            MediaPageName : mediaPageName
        }
        
        //Call getUserlist in services.js file        
        mediaData.getMedia(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                $scope.mediaList = $scope.tempMedia;
                $scope.noOfObj = response.Data.total_records;
                $scope.totalRecords = response.Data.total_records;

                //$scope.showButtonGroup = false;
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
                
                //Push data into Controller in view file
                $(response.Data.results).each(function(key,value){
                    response.Data.results[key].IsAdminApprovedFilter = $scope.filt.IsAdminApprovedFilter;
                });
                $scope.mediaListData = ({ObjMedia: response.Data.results});
                //console.log($scope.mediaListData);
                if ($.isEmptyObject(response.Data.results) == false) {
                    $scope.mediaList.push.apply($scope.mediaList, response.Data.results);
                }
                
                if($scope.mediaList.length == 0){
                    $scope.shownomediarecord = true;
                }else{
                    $scope.shownomediarecord = false;
                }
                
                setTimeout(
                    function () {
                        $('.icon-zoomlist').lightBox();
                    }, 1000
                );
        
                //$scope.tempMedia = [];
    
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
            //hideLoader();
            $(".media_loader").hide();
            $scope.service_call = false;
        }), function (error) {
            hideLoader();
        }
    };
        

    /**
     * Search by approve or nor
     * @param {type} field
     * @param {type} val
     * @returns {undefined}
     */
    $scope.searchBy = function (field, val,cls) {
        $scope.shownomediarecord = true;
        if($('.'+cls).hasClass('selected')){
           $('.'+cls).removeClass('selected'); 
        } else {
           $('.'+cls).addClass('selected');  
        }
        var i = 0;
        var val = 3;
        if($('.selected-approve').hasClass('selected')){
            val = 1;
            i++;
            $scope.showapprovebtn = false;
        }
        
        if($('.selected-reject').hasClass('selected')){
            val = 0;
            i++;
            $scope.showapprovebtn = true;
        }
        
        if($('.selected-approve').hasClass('selected') && $('.selected-reject').hasClass('selected')){
            $scope.showapprovebtn = false;
        }
        
        if(i==2){
            val = 2;
            $($scope.mediaList).each(function(key,value){
                $scope.mediaList[key].IsAdminApprovedFilter = val;
                $scope.tempMedia = $scope.mediaList;
            });
        } else {
            $($scope.mediaList).each(function(key,value){
                $scope.mediaList[key].IsAdminApprovedFilter = $scope.mediaList[key].IsAdminApproved;
                $scope.tempMedia = $scope.mediaList;
            });
        }
        if(val==3){
            $scope.mediaList = [];
            $('#showHidefilter,.filter-view,.sub-nav,#mediaCtrl .info-row-right').hide();
        } else {
            $('#showHidefilter,.sub-nav,#mediaCtrl .info-row-right').show();
            if($('#showHidefilter').text()== Media_ShowAdvanceFilters){
                $('.filter-view').hide();
            } else {
                $('.filter-view').show();
            }
            if($scope['filt'].IsAdminApprovedFilter!=val){
                $scope.tempMedia = [];                
            }
            
            $scope[field] = val;
            $scope['filt'].IsAdminApprovedFilter = val;
            if($scope.service_call == false){
                $scope.loadMedia();
            }
        }
        //$scope.globalChecked = !$scope.globalChecked;
        //$scope.globalCheckBox();
    }

    /**
     * Open Image in model
     * @param {type} media
     * @returns {undefined}
     */
    $scope.openLightBox = function (media) {
        $scope.popup = media;
        openPopDiv('mediaImagePopup', 'bounceInDown');
    };
    
    $scope.videoMediaStatus = 0;
    $scope.playVideo = function(media){
        $scope.videoMediaStatus = 0;
        if(typeof media.ImageName!=='undefined')
        {
            var ext = media.ImageName.substr(media.ImageName.lastIndexOf('.') + 1);
            var fname = media.ImageName.substr(0, media.ImageName.lastIndexOf('.'));
            media.ImageName = fname;
        }
        $scope.videoMedia = media;
        $scope.videoMediaStatus = 1;
        openPopDiv('mediaVideoPopup', 'bounceInDown');
    };

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
        
        if(Object.keys($scope.selectedMedia).length == $scope.mediaList.length){
            setTimeout(function(){ $scope.globalChecked = true; }, 1);
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
            var mediaLists = $scope.filteredMedia;
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
        $scope.statusMediaIds = Object.keys($scope.selectedMedia);//$scope.selectedMedia;
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
        mediaData.setStatus(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                //on delete remove images
                if ($scope.mediastatus == 'delete') {
                    /*angular.forEach($scope.indexToUpdate, function (val, key) {
                        $scope.mediaList.splice(key, 1);
                        $scope.getMediaSummary();
                    })*/
                    $scope.tempMedia = [];
                    $scope.mediaList = [];
                    $scope.getMediaSummary();
                    
                    if($("#hdnPageName").val() == "UserProfile"){
                        angular.element(document.getElementById('userCtrl')).scope().getUser();
                    }
                    
                    //Update Media Count
                    $scope.getDBCount(reqData.media,reqData.action);
                    
                    ShowSuccessMsg(" Delete successfully.");
                } else if ($scope.mediastatus == 'approve') {
                    angular.forEach($scope.indexToUpdate, function (val, key) {
                        $scope.mediaList[val].IsAdminApproved = 1;
                        if($scope.filt.IsAdminApprovedFilter!==2){
                            $scope.mediaList.splice(key, 1);
                        }
                        $scope.getMediaSummary();
                        //Update Media Count
                        $scope.getDBCount(reqData.media,reqData.action);
                    })
                    ShowSuccessMsg(" Approve successfully.");
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

    $scope.getSearchBox = function () {
        $scope.totalRecords = 0;
        if($scope.user.userid){
            $scope.getMediaCountUser();
        } else {
            $scope.getMediaCountAll();
        }

    }

    $scope.getDBCount = function(MediaIDs,action){
        var reqData = {
            media:MediaIDs,
            action:action,
            userid:$scope.user.userid,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        mediaData.getDBCount(reqData).then(function (response) {
            //response = {"upload_devices":[{"counts":"61","DeviceID":"1","Name":"Native"},{"counts":"32","DeviceID":"2","Name":"IPhone"}]}
            if(typeof response.Data.upload_devices !== undefined){
                $(response.Data.upload_devices).each(function(key,val){
                    $($scope.searchBox.upload_devices).each(function(k,v){
                        if(response.Data.upload_devices[key].DeviceID == $scope.searchBox.upload_devices[k].DeviceID){
                            $scope.searchBox.upload_devices[k].ApproveCount = response.Data.upload_devices[key].approved_count;
                            $scope.searchBox.upload_devices[k].YetToApproveCount = response.Data.upload_devices[key].yet_to_approve_count;
                        }
                    });
                });
            }
            if(typeof response.Data.media_extensions !== undefined){
                $(response.Data.media_extensions).each(function(key,val){
                    $($scope.searchBox.media_extensions).each(function(k,v){
                        if(response.Data.media_extensions[key].MediaExtensionID == $scope.searchBox.media_extensions[k].MediaExtensionID){
                            $scope.searchBox.media_extensions[k].ApproveCount = response.Data.media_extensions[key].approved_count;
                            $scope.searchBox.media_extensions[k].YetToApproveCount = response.Data.media_extensions[key].yet_to_approve_count;
                        }
                    });
                });
            }
            if(typeof response.Data.media_source !== undefined){
                $(response.Data.media_source).each(function(key,val){
                    $($scope.searchBox.media_source).each(function(k,v){
                        if(response.Data.media_source[key].SourceID == $scope.searchBox.media_source[k].SourceID){
                            $scope.searchBox.media_source[k].ApproveCount = response.Data.media_source[key].approved_count;
                            $scope.searchBox.media_source[k].YetToApproveCount = response.Data.media_source[key].yet_to_approve_count;
                        }
                    });
                });
            }
            if(typeof response.Data.media_sections !== undefined){
                $(response.Data.media_sections).each(function(key,val){
                    $($scope.searchBox.media_sections).each(function(k,v){
                        if(response.Data.media_sections[key].MediaSectionID == $scope.searchBox.media_sections[k].MediaSectionID){
                            $scope.searchBox.media_sections[k].ApproveCount = response.Data.media_sections[key].approved_count;
                            $scope.searchBox.media_sections[k].YetToApproveCount = response.Data.media_sections[key].yet_to_approve_count;
                        }
                    });
                });
            }
            if(typeof response.Data.media_size !== undefined){
                $(response.Data.media_size).each(function(key,val){
                    $($scope.searchBox.media_size).each(function(k,v){
                        if(response.Data.media_size[key].MediaSizeID == $scope.searchBox.media_size[k].MediaSizeID){
                            $scope.searchBox.media_size[k].ApproveCount = response.Data.media_size[key].approved_count;
                            $scope.searchBox.media_size[k].YetToApproveCount = response.Data.media_size[key].yet_to_approve_count;
                        }
                    });
                });
            }
            $scope.filterCount();
        });
    }

    $scope.getMediaCount = function(){
        if($.isEmptyObject($scope.searchBox)){
            var reqData = {
                userId: $scope.user.userid,
                approved: $scope['filt'].IsAdminApprovedFilter,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            mediaData.getCount(reqData).then(function (response) {
                $scope.searchBox = response.Data;
            });
            $scope.filterCount();
        } else {
            $scope.filterCount();
        }
    }

    //$scope.updateUserMediaCount = function(media,action){

    //}

    $scope.getMediaCountAll = function(){
        if($.isEmptyObject($scope.searchBox)){
            var reqData = {AdminLoginSessionKey :$scope.AdminLoginSessionKey};
            mediaData.getCountAll(reqData).then(function (response) {
                $scope.searchBox = response.Data;
                $scope.filterCount();
            });
        } else {
                $scope.filterCount();
        }
    }

    $scope.getMediaCountUser = function(){
        if($.isEmptyObject($scope.searchBox)){
            var reqData = {
                userId: $scope.user.userid,
                approved: $scope['filt'].IsAdminApprovedFilter,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            }
            mediaData.getCount(reqData).then(function (response) {
                $scope.searchBox = response.Data;
                $scope.filterCount();
            });
        } else {
            $scope.filterCount();
        }
    }

    $scope.filterCount = function(){
        $($scope.filterSections).each(function(key,val){
            $($scope.searchBox[val]).each(function(k,v){
                if($scope.filt.IsAdminApprovedFilter==0){
                    $scope.searchBox[val][k].counts = $scope.searchBox[val][k].YetToApproveCount;
                } else if($scope.filt.IsAdminApprovedFilter==1){
                    $scope.searchBox[val][k].counts = $scope.searchBox[val][k].ApproveCount;
                } else {
                    $scope.searchBox[val][k].counts = parseInt($scope.searchBox[val][k].ApproveCount)+parseInt($scope.searchBox[val][k].YetToApproveCount);
                }
            });
        });
    }

    $scope.addToSearch = function (key, val, add, index, dataKey) {
        val.index = index;
        val.key = key;
        val.dataKey = dataKey;
        if (add) {
            val.selected = true;
            if (typeof $scope.criteria[key] != 'undefined') {
                //$scope.criteria[key].push(val[key]);
                $scope.criteria[key][val[key]] = val;
            } else {
                $scope.criteria[key] = new Array();
                $scope.criteria[key][val[key]] = val;
            }
            $scope.criteriaList.push(val);
        } else {
            if (typeof $scope.criteria[key] != 'undefined') {
                keyIndex = $scope.criteria[key].indexOf(val);
                $scope.criteria[key].splice(keyIndex, 1);
            }
            keyInd = $scope.criteriaList.indexOf(val);
            $scope.criteriaList.splice(keyInd, 1);
        }
    }

    $scope.applyFilter = function (action) {
        if (action == 'search') {
            $scope.filteredMedia = $filter('searchMedia')($scope.mediaList, $scope.criteria);
        } else {
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

    $scope.removeFromCriteria = function (criteria, index) {
        $scope.criteriaList.splice(index, 1);
        if (typeof $scope.criteria[criteria.key] != 'undefined') {
            var key = criteria.key;
            var inedex = criteria.index;
            if (angular.isUndefined($scope.criteria[key][inedex])) {
                $scope.criteria[key].splice(inedex, 1);
            }
            $scope.criteria[key].splice(inedex, 1);
            dataKey = criteria.dataKey;
            $scope.searchBox[dataKey][inedex].selected = false;
        }
    }


    $scope.initMedia = function () {
        pathname = location.pathname.split('/');
        if (pathname[pathname.length - 1] == 'media') {
            $rootScope.tabSelected = 'media';
            $scope.user.userid = null;
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
    
    $scope.initMedia();
});

app.filter('searchMedia', [function () {
        return function (mediaList, criteria) {
            var tempList = [];

            if (!angular.isUndefined(criteria) && !angular.isUndefined(mediaList) && Object.keys(criteria).length > 0) {
                angular.forEach(criteria, function (obj, keyField) {
                    angular.forEach(obj, function (val, key) {
                        angular.forEach(mediaList, function (media, keyIndex) {
                            if (media[keyField] == val[keyField]) {
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

app.directive('dynamicUrl', [function () {
    return {
        restrict: 'A',
        link: function postLink(scope, element, attr) {
            console.log(attr);
            element.attr('src', attr.dynamicUrlSrc);
        }
    };
}]);