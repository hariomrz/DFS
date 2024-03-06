/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


// IP Controller
app.controller('AlbumPhotoListCtrl', function ($scope, $rootScope,$routeParams,albumData, $window, $timeout, apiService, lazyLoadCS, Upload, DashboardService,$q) {
    $scope.PostedByLookedMore = [];
    $scope.SelectedPhotoList = [];
    $rootScope.scroll_disable = false;
    $scope.showAddphoto = false;
    $scope.isMoveAlbum = true;
    $scope.SelectedAlbumData = '';
    $scope.isEmptyAlbum = '';
    $scope.albumPrevData = [];
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = 20,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.sort_by = "Name";    
    $scope.currentData = {};
    $scope.currentIsActive = 0;
    $scope.image_path = image_path;
    $scope.type = $routeParams.type;
    console.log($routeParams)
    $scope.LocalityOptions = [{Name:'All', MKey:''},{Name:'Tulsi Nagar', MKey:'1'},{Name:'Mahalaxmi Nagar', MKey:'2'},{Name:'Sai Kripa', MKey:'3'},{Name:'Suncity', MKey:'4'},{Name:'Chikitsak Nagar', MKey:'5'}]
    $scope.AlbumGUID= $("#album_guid").val();//'6653a444-ca76-5b91-8c2f-7dbf1dafc2e9';
    $scope.ReadMoreText = ''



    var AlbumMediaList_reqData_default = {
        "AlbumGUID":$scope.AlbumGUID,
        "PageNo": 1, //$scope.currentPage,
        "PageSize": 10,
        "SortBy": 1,
        "OrderBy": 'DESC',

    };
    $scope.requestObj = angular.copy(AlbumMediaList_reqData_default);
    $scope.questionDataListLoader = false;
    $rootScope.scroll_disable = false;
    $scope.AlbumMediaDataList = [];
    $scope.questionTotalRecord = 0;
    $scope.getAlbumMediaList = function()
    {
       // showLoader();
        if (!$scope.questionDataListLoader && (($scope.AlbumMediaDataList.length <= $scope.questionTotalRecord) || ($scope.requestObj.PageNo === 1)))
        {
            $scope.questionDataListLoader = true;
            $scope.requestParamsQuestions = {
                "AlbumGUID":$scope.requestObj.AlbumGUID,
                "PageNo": $scope.requestObj.PageNo, //$scope.currentPage,
                "PageSize": $scope.requestObj.PageSize,
                "SortBy": $scope.requestObj.SortBy,
                "OrderBy": $scope.requestObj.OrderBy
            }
            DashboardService.CallPostApi('api/album/list_media', $scope.requestParamsQuestions, function (response) {
                var response = response.data;
               // hideLoader();
                if (response.ResponseCode == 200)
                {
                    if ($scope.requestObj.PageNo > 1) {
                        console.log("ccccccc")
                        $scope.AlbumMediaDataList = $scope.AlbumMediaDataList.concat(response.Data);
                    } else {
                        console.log('AAAAAA')
                        $scope.albumPrevData = response.Album;
                        $scope.questionTotalRecord = parseInt(response.Album.MediaCount);
                        $scope.AlbumMediaDataList = angular.copy(response.Data);
                    }
                    console.log($scope.AlbumMediaDataList);
                    if (response.Album.MediaCount === $scope.AlbumMediaDataList.length || response.Data.length < $scope.requestObj.PageSize)
                    {
                        console.log("ffffffffffff")
                        $rootScope.scroll_disable = true;
                    }

                    $scope.questionDataListLoader = false;
                    $scope.requestObj.PageNo++;
                } else {
                    ShowErrorMsg(response.Message);
                }

            }, function () {
                $scope.questionDataListLoader = false;
            });
        }
    }

    
    $scope.DataList = function (searchBtn) {
        if (searchBtn != undefined) {
            if (!openSearch() || $scope.SearchKeyword == undefined) {
                return;
            }
        }
        intilizeTooltip();
        // showLoader();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();

        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for smtp listing
            begins = 1;//$scope.currentPage;
        } else {
             begins++
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        var reqData = {
            AlbumGUID:$scope.AlbumGUID,
            PageNo: begins, //$scope.currentPage,
            PageSize: $scope.numPerPage,
            SortBy: 1,
            OrderBy: 'ASC',
           // Verified:2
            //Send AdminLoginSessionKey
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
        //Call getIpList in services.js file
        // albumData.getMediaList(reqData).then(function (response) {
            
        //     if(begins > 1){
                
        //         $scope.listData = $scope.listData.concat(response.Data);
        //     }
        //     else {
        //         $scope.albumPrevData = response.Album;
        //         $scope.listData = angular.copy(response.Data);
                
        //     }
        //     if (response.TotalRecords === $scope.listData.length || response.Data.length < $scope.numPerPage)
        //                     {
                                
        //                         $rootScope.scroll_disable = true;
        //                     }
        //     //$scope.totalRecord = $scope.noOfObj = response.Data.total_records
        //     hideLoader();

        // }), function (error) {
        //     hideLoader();
        // }
    };

    $scope.AlbumDataList = function (searchBtn) {
        if (searchBtn != undefined) {
            if (!openSearch() || $scope.SearchKeyword == undefined) {
                return;
            }
        }
        intilizeTooltip();
        showLoader();

        
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
            SortBy: 1,
            OrderBy: $scope.reverseSort,
            //IpFor: $scope.IpFor,
            SearchKeyword: $scope.SearchKeyword,
            //Send AdminLoginSessionKey
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
        //Call getIpList in services.js file
        albumData.getList(reqData).then(function (response) {
            $scope.listData = response.Data;
            $scope.totalRecord = $scope.noOfObj = response.Data.total_records

            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };

    
  
    //Apply Sort by and mamke request data
    $scope.sortingList = [
        {
            label:'New',
            value:1
        },{
            label:'Popular',
            value:2
        }
    ]
    var initialFilter = {
        label:'New',
        value:1
    };
    $scope.sortByOption = '';

    $scope.RefreshData = function (){
    $scope.requestObj.PageNo = 1
    $rootScope.scroll_disable = false;
    $("html, body").animate({scrollTop: 0}, "slow");
    $scope.getAlbumMediaList();
 }

    $scope.sortBY = function () {
        $scope.requestObj.SortBy = $scope.sortByOption
        $scope.requestObj.PageNo = 1
        $rootScope.scroll_disable = false;
        $("html, body").animate({scrollTop: 0}, "slow");
        $scope.getAlbumMediaList();
        
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
            ModuleID: $scope.ModuleID,
            //Send AdminLoginSessionKey
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
        $scope.DataList();
    });
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    };

    //Function for set Ip details in scope variables
    $scope.SetDetail = function (data) {
        $scope.currentData = {};
        $scope.currentData = data;
        $scope.currentData['media'] = data.CoverMedia;
        console.log('data',data)
    };

    $scope.resetPopup = function () {
        if ($('input[name="MediaGUID[]"]').length > 0) {
            $('input[name="MediaGUID[]"]').each(function (k, v) {
                $('.catImgList').remove();
            });
        }
        $scope.currentData = {'category_id': "", 'locality_id': "", "name": "", "parent_id": "0", "module_id": "", "description": "", "media": {}};
        $scope.parent_hide = false;
        // $('#attached-media-1').children().find('li').removeAttr('id');
    }
    


    $scope.getAllCategories = function (category_id) {
        if (typeof category_id == 'undefined') {
            category_id = '';
        }
        reqData = {
            ModuleID: $scope.ModuleID,
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey,
            CategoryId: category_id
        }
        albumData.getAllCategory(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.allCategories = response.Data.results;
                $scope.currentData.parent_id = $scope.allCategories[0];
            }
        });
    }

    $scope.AddDetailsPopUp = function () {
        $scope.ModuleID = $scope.currentData.module_id;
        $scope.getAllCategories();
        // $('#attached-media-1 li').remove();
        $scope.resetPopup();

        $("#chkActive").attr('checked', true).parent('span').addClass('icon-checked');
        $scope.showIpError = false;
        $scope.errorIpMessage = null;
        $scope.showAlbumNameError = false;
        $scope.showDescriptionError = false;
        openPopDiv('addIpPopup', 'bounceInDown');
    };

    $scope.EditDetailsPopUp = function () {

        if ($('input[name="MediaGUID[]"]').length > 0) {
            $('input[name="MediaGUID[]"]').each(function (k, v) {
                $('.catImgList').remove();
            });
        }
        
                
            
            $scope.showIpError = false;
            $scope.errorIpMessage = null;
            $scope.showCategoryError = false;
            $scope.showDescriptionError = false;
            openPopDiv('addIpPopup', 'bounceInDown');
            hideLoader();
        

    };

    $scope.filter_module = function (type = false)
    {
        if (type == true)
        {
            $scope.LocalityID = $scope.localityID;
            $scope.DataList();       
        }
        else
        {
            $scope.ModuleID = $scope.category.filter_module;
            $scope.DataList();            
        }
    }
    $scope.filter_module_parent = function ()
    {
        $scope.ModuleID = $scope.currentData.module_id;
        $scope.getAllCategories();
    }

    $scope.clearCategorySearch = function ()
    {
        if ($scope.SearchKeyword !== undefined && $scope.SearchKeyword !== '') {
            $scope.SearchKeyword = '';
            $scope.DataList();
        }
    }





    $scope.set_parent_selected = function (category_id) {
        if (category_id == null || category_id == 0) {
            $scope.currentData.parent_id = $scope.allCategories[0];
        } else {
            //console.log(category_id,$scope.allCategories);
            angular.forEach($scope.allCategories, function (val, index) {
                if (val.category_id == category_id) {
                    $scope.currentData.parent_id = $scope.allCategories[index];
                }
            });
        }
    }
    $scope.markFeature = function (status) { 
        
        showLoader();
        var reqData = {
            'AlbumGUID': $scope.currentData.AlbumGUID,
            'IsFeatured': ($scope.currentData.IsFeatured==0)?1:0,
            'AdminLoginSessionKey': $scope.AdminLoginSessionKey
        };
        
        showLoader();
        albumData.markFeature(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                hideLoader();
                ShowSuccessMsg(response.Message);
                $scope.getAlbumMediaList()

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
            $("html, body").animate({scrollTop: 0}, "slow");

        }), function (error) {
            hideLoader();
        }
    };
    $scope.removeFeature = function (status) { 
        
        showLoader();
        var reqData = {
            'AlbumGUID': $scope.currentData.AlbumGUID,
            'IsFeatured': 0,
            'AdminLoginSessionKey': $scope.AdminLoginSessionKey
        };
        
        showLoader();
        albumData.removeFeature(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                hideLoader();
                ShowSuccessMsg(response.Message);
                $scope.getAlbumMediaList();

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
            $("html, body").animate({scrollTop: 0}, "slow");

        }), function (error) {
            hideLoader();
        }
    };
    $scope.setVisibility = function (status) { 
        
        showLoader();
        var reqData = {
            'AlbumGUID': $scope.currentData.AlbumGUID,
            'Visibility': status,
            'AdminLoginSessionKey': $scope.AdminLoginSessionKey
        };
        
        showLoader();
        albumData.setVisibility(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                hideLoader();
                ShowSuccessMsg(response.Message);
                $scope.getAlbumMediaList();

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
            $("html, body").animate({scrollTop: 0}, "slow");

        }), function (error) {
            hideLoader();
        }
    };

    


    $scope.SetStatus = function (action) {
        if (action == 2) {
            $rootScope.confirmationMessage = Sure_Active + ' ?';
        } else if (action == 4) {
            $rootScope.confirmationMessage = Sure_Inactive + ' ?';
        } else if (action == 3) {
            $rootScope.confirmationMessage = Sure_Delete + ' ?';
        }
        $scope.currentIsActive = action;
        if ($scope.currentData.ModuleID == 29 && action == 3) {
            $scope.SkillCategoryConfirmation();
            openPopDiv('removeCategoryPopup', 'bounceInDown');
        } else {
            openPopDiv('confirmeCommissionPopup', 'bounceInDown');
        }

    };

    $scope.SkillCategoryConfirmation = function () {

        reqData = {CategoryID: $scope.currentData.category_id}
        apiService.call_api(reqData, 'admin_api/skills/category_profile_count').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.RemovealbumData = response;
            }
        });
    }

    
    $scope.delete_cat_image = function (MediaGUID) {
        angular.element(document.getElementById('cat_img_'+MediaGUID.MediaGUID)).remove();
        //$('#cat_img_'+MediaGUID).remove();
        // $scope.currentData.MediaGUID = '';
        // $scope.currentData.ImageName = '';
    }

    $scope.VerifyAlbum = function (id,verify,idx) {
        showLoader();
        var VerifyVal = verify == 0 ? 1 : 0;
        var reqData = {
            MediaGUID:id,
            Verify:verify == 0 ? 1 : 0
        }
        //Call VerifyAlbum  in services.js file
        albumData.VerifyAlbum(reqData).then(function (response) {
            ShowSuccessMsg(response.Message);
            var obj = $scope.AlbumMediaDataList
            for (var i in obj) {
                if (obj[i].MediaGUID == id) {
                   obj[i].Verified = VerifyVal
                   break; //Stop this loop, we found it!
                }
              }

            $timeout(function () {
             $scope.AlbumMediaDataList = obj
            }, 1000);
          //  $scope.requestObj.PageNo = 1
           // $rootScope.scroll_disable = false;
           // $scope.getAlbumMediaList();
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };
    
    $scope.deleteAlbumMedia = function (id) {
        showLoader();
        
        var reqData = {
            MediaGUID:id,
        }
        
        albumData.deleteAlbumMedia(reqData).then(function (response) {
            ShowSuccessMsg(response.Message);
            var obj = $scope.AlbumMediaDataList
            for (var i in obj) {
                if (obj[i].MediaGUID == id) {
                   obj.splice(i, 1);
                   break; //Stop this loop, we found it!
                }
              }

            $timeout(function () {
             $scope.AlbumMediaDataList = obj
            }, 1000);
           // $scope.requestObj.PageNo = 1
          //  $rootScope.scroll_disable = false;
           // $scope.getAlbumMediaList();
          //  $("html, body").animate({scrollTop: 0}, "slow");
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };

    $scope.sendNotification = function (id) {
        
        showLoader();
        
        var reqData = {
            MediaGUID:id,
        }
        
        albumData.sendNotification(reqData).then(function (response) {
            hideLoader();
            ShowSuccessMsg(response.Message);
            

        }), function (error) {
            hideLoader();
        }
    };

    $scope.SetCoverMedia = function (id,Aid) {
        
        showLoader();
        
        var reqData = {
            MediaGUID:id,
            AlbumGUID:$scope.albumPrevData.AlbumGUID
        }
        
        albumData.setCoverMedia(reqData).then(function (response) {
            hideLoader();
            $scope.getAlbumMediaList();
            ShowSuccessMsg(response.Message);
            

        }), function (error) {
            hideLoader();
        }
    };


    $scope.deleteAlbumConfrim = function (id) {
        var title = "Delete Media";
        var message = "Are you sure, you want to delete this Media ?";
        showAdminConfirmBox(title, message, function (e) {
                     if (e) {
                         $scope.deleteAlbumMedia(id)
                    }
        });
    }

    $scope.sendNotificationAlbum = function (id) {
        var title = "Notification";
        var message = "Are you sure, you want to send notification ?";
        showAdminConfirmBox(title, message, function (e) {
                     if (e) {
                         $scope.sendNotification(id)
                    }
        });
    }

    $scope.ChangeAlbumPopUp = function (data) {
        $scope.removeSelectedAlbum();
        $scope.resetPopup();
        $scope.currentData = {};
        $scope.currentData = data
        openPopDiv('addIpPopup', 'bounceInDown');
    };

    $scope.ChangeLocationPopUp = function (data) {
        $scope.resetPopup();
        $scope.currentData = {};
        $scope.currentData = data
        openPopDiv('locationPopup', 'bounceInDown');
    };

    $scope.ChangeLocationMedia = function () {

        var AlbumName = $scope.currentData.Location;
        var Description = $scope.currentData.Description;
        

        $scope.category = {};
        var show_error = 0;
        $scope.hasError = false;
        //$scope.errorAlbumNameMessage = '';
        //$scope.errorDescriptionMessage = '';
        
        // if (AlbumName == "") {
        //     $scope.showAlbumNameError = true;
        //     $scope.errorAlbumNameMessage = 'Please enter valid  location.';
        //     $scope.hasError = true;
        // }

        // if (Description == "" ) {
        //     $scope.showDescriptionError = true;
        //     $scope.errorDescriptionMessage = 'Please enter description.';
        //     $scope.hasError = true;
        // }
        

        
        if ($scope.hasError == true)
        {
            return false;
        } else
        {
            $('.loader_ip').show();
            //send message
            $scope.showAlbumNameError = false;
            $scope.errorAlbumNameMessage = null;
            $scope.showDescriptionError = false;
            $scope.errorDescriptionMessage = null
            var reqData = {
                MediaGUID:$scope.currentData.MediaGUID,
                Location:AlbumName,
                Description: $scope.currentData.Description,
               
            };
            
            albumData.updateMediaLocation(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    //Show Success message
                    ShowSuccessMsg(response.Message);
                    $scope.resetPopup();
                    $scope.getAlbumMediaList();
                    closePopDiv('locationPopup', 'bounceOutUp');
                }
                 else {
                    $scope.showAlbumNameError = true;
                    $scope.errorAlbumNameMessage = response.Message;
                }
                $('.loader_ip').hide();
            });
        }

        $timeout(function () {
            $scope.showIpError = false;
            $scope.errorIpMessage = null;
        }, 5000);

    };

    $scope.loadSearchAlbum = function ($query) {
        var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 100};
        // $scope.AlbumDataList($query)
        return DashboardService.CallPostApi('api/album/list', requestPayload, function (resp) {
          var response = resp.data;
          if ((response.ResponseCode == 200) && (response.Data.length > 0) && !$scope.isEmptyAlbum) { 
            return response.Data.filter(function (flist) {
              return flist.AlbumName.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
          } else {
            return [];
          }
        });
       
      };

    $scope.getSelectedAlbum = function (TagData) {
        
        $scope.SelectedAlbumData = TagData;
            $scope.isEmptyAlbum = true;
        if(TagData.AlbumGUID){
            $scope.isMoveAlbum = false;
        } 
       
        
        
        
    };
    
    $scope.removeSelectedAlbum = function () {
        $scope.SelectedAlbumData = '';
        $scope.isEmptyAlbum = false;
        $scope.isMoveAlbum = true;
        $scope.PostedByLookedMore = [];
    };

    $scope.ChangeAlbumMedia = function () {
        showLoader();
        
        var reqData = {
            AlbumGUID:$scope.SelectedAlbumData.AlbumGUID,
            MediaGUID:$scope.currentData.MediaGUID,
        }
        
        albumData.ChangeAlbumMedia(reqData).then(function (response) {
            hideLoader();
            ShowSuccessMsg(response.Message);

            var obj = $scope.AlbumMediaDataList
            for (var i in obj) {
                if (obj[i].MediaGUID == $scope.currentData.MediaGUID) {
                   obj.splice(i, 1);
                   break; //Stop this loop, we found it!
                }
              }

            $timeout(function () {
             $scope.AlbumMediaDataList = obj
            }, 1000);

            //$scope.getAlbumMediaList();
            closePopDiv('addIpPopup', 'bounceOutUp');
            $scope.resetPopupAlbum()

        }), function (error) {
            hideLoader();
        }
    };

    $scope.resetPopupAlbum = function () {
        $scope.isMoveAlbum = true;
        $('#CheckBtnShow').removeClass('disble-btn-cus');
        $scope.currentData = {'category_id': "", 'locality_id': "", "name": "", "parent_id": "0", "module_id": "", "description": "", "media": {}};
        $scope.parent_hide = false;
        $scope.SelectedAlbumData = '';
        $scope.PostedByLookedMore = [];
        $scope.isEmptyAlbum = false;

        // $('#attached-media-1').children().find('li').removeAttr('id');
    }

  

    $scope.AddEditAlbum = function () {
        
           
           
            
            var reqData = {
                'AlbumGUID': $scope.albumPrevData.AlbumGUID,
                'Media': $scope.SelectedPhotoList,
            };
            
            albumData.addMediaToAlbum(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    //Show Success message
                    ShowSuccessMsg(response.Message);
                    $scope.resetPhotoPopup();
                    $scope.requestObj.PageNo = 1
                    $rootScope.scroll_disable = false;
                    $("html, body").animate({scrollTop: 0}, "slow");
                    $scope.getAlbumMediaList();
                    closePopDiv('addphotoPopup', 'bounceOutUp');
                } else if (response.ResponseCode == 517) {
                    redirectToBlockedIP();
                } else if (response.ResponseCode == 598) {
                    //Show error message
                    closePopDiv('addphotoPopup', 'bounceOutUp');
                    PermissionError(response.Message);
                } else {
                    $scope.showAlbumNameError = true;
                    $scope.errorAlbumNameMessage = response.Message;
                    //$scope.showDescriptionError = true;
                    //$scope.errorDescriptionMessage = response.Message;
                }
                $('.loader_ip').hide();
            });
        

        $timeout(function () {
            $scope.showIpError = false;
            $scope.errorIpMessage = null;
        }, 5000);

    };

    // for image multiple compresion
    
    var wallMediaCurrentIndex = 0;
    var wallFileCurrentIndex = 0;
    $scope.medias = {};
    $scope.edit_medias = {};
    $scope.mediaCount = 0;
    $scope.files = {};
    $scope.edit_files = {};
    $scope.fileCount = 0;
    $scope.isWallAttachementUploading = false;
    $scope.uploadWallFiles = function (files, errFiles) {
        var promises = [];
        
        console.log(files,"filefilefilefilefilefile")
        var validImageTypes = ["image/gif", "image/jpeg", "image/png"];
        if(files.length>0) {
            for(var i = 0; i < files.length; i++) 
            {
            if ($.inArray(files[i].type, validImageTypes) < 0) {
                PermissionError('Only image files are allowed.', 'alert-danger');
                return;  
               }
              
            }
           
            
        } 
        if (files && files.length) {
            console.log(files,"filesfiles")
            //openPopDiv('addphotoPopup', 'bounceInDown');
            $('#addPhotoAlbum').modal()
            var patt = new RegExp("^image|audio|video");
            var videoPatt = new RegExp("^video");
            $scope.isWallAttachementUploading = true;
            $('.dis-cret-m').addClass('disble-btn-cus');
            angular.forEach(files, function (fileToUpload, key) {
                (function (file, mediaIndex, fileIndex) {
                    var filename = file.name;
                    var mimeString = file.type;
                    
                    if (!(videoPatt.test(mimeString)) && mimeString !== 'image/gif' && mimeString!=='application/pdf') {
                        var URL = window.URL || window.webkitURL;
                        var url = URL.createObjectURL(file);
                        var image = new Image();
                        image.onload = function() {
                            var options ={
                                resizeMaxHeight: 700,
                                resizeMaxWidth: 700,
                                resizeQuality: '80',
                                resizeType: mimeString
                            };
                            $scope.jicCompress(image, options).then(function(dataURLcompressed){
                                $scope.dataURItoBlob(dataURLcompressed.src,mimeString).then(function(blobData){
                                    var blob = blobData.file;
                                    var file = new File([blob], filename,{type: mimeString});
                                    var promise = $scope.uploadFileInLoop(file,patt,mediaIndex,fileIndex,videoPatt);
                                    promises.push(promise);
                                });
                            });
                        }
                        image.src = url;
                    }else{
                        var promise = $scope.uploadFileInLoop(file,patt,mediaIndex,fileIndex,videoPatt);
                        promises.push(promise);
                    }
                })(fileToUpload, wallMediaCurrentIndex, wallFileCurrentIndex);
                if (patt.test(fileToUpload.type)) {
                    wallMediaCurrentIndex++;
                } else {
                    wallFileCurrentIndex++;
                }
            });
            $q.all(promises).then(function (data) {
               // $scope.isWallAttachementUploading = false;
                $scope.noContentToPost = false;
            });
        } else {
            if (typeof errFiles[0] !== "undefined" && errFiles[0].$error == 'minWidth')
            {
                showResponseMessage('Minimum width should be ' + errFiles[0].$errorParam, 'alert-danger');
            } else if (typeof errFiles[0] !== "undefined" && errFiles[0].$error == 'minHeight')
            {
                showResponseMessage('Minimum width should be ' + errFiles[0].$errorParam, 'alert-danger');
            } else
            {
                var msg = '';
                angular.forEach(errFiles, function (errFile, key) {
                    msg += '\n' + errFile.$errorMessages;
                    promises.push(makeResolvedPromise(msg));
                });
                $q.all(promises).then(function (data) {
//                    showResponseMessage(msg, 'alert-danger');
                });
            }
        }
    };
    
    $scope.uploadFileInLoop = function(file,patt,mediaIndex,fileIndex,videoPatt){
        albumData.setFileMetaData(file);
        var paramsToBeSent = {
            qqfile: file,
            Type: 'album',
            DeviceType: 'Native',
            LoginSessionKey: $('#AdminLoginSessionKey').val()
        };
        var fileType = 'media';

        if(settings_data.m40==0)
        {
            if(!patt.test(file.type))
            {
                showResponseMessage('Only images and video type files are allowed.', 'alert-danger');
                return false;
            }
        }

        if (patt.test(file.type)) {
            
            $scope.medias['media-' + mediaIndex] = file;
            $scope.mediaCount = Object.keys($scope.medias).length + Object.keys($scope.edit_medias).length;
        } else {
            $scope.files['file-' + fileIndex] = file;
            $scope.fileCount = Object.keys($scope.files).length + Object.keys($scope.edit_files).length;
            fileType = 'file';
            paramsToBeSent['IsDocument'] = '1';

        }

        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
       
        var promise = apiService.CallUploadFilesApi(
            paramsToBeSent,
            'api/upload_image',
            function (response) {

                apiService.FileUploadProgress({fileType: fileType, scopeObj: $scope, fileIndex: fileIndex, mediaIndex: mediaIndex}, {}, response);
                
                if (fileType === 'media') {
                   
                    if (response.data.ResponseCode == 200) {

                        $scope.SelectedPhotoList.push(response.data.Data)
                        $scope.medias['media-' + mediaIndex]['data'] = response.data.Data;
                        $scope.mediaInputIndex = 'ALL';
                        $scope.medias['media-' + mediaIndex].progress = true;
                        $scope.visualPostStyle = {'background': 'url(' + response.data.Data.ImageServerPath + '/' + response.data.Data.ImageName + ') no-repeat 0 0', 'background-size': 'cover'};
                        $scope.visualPostImage = 1;
                        if($scope.SelectedPhotoList.length == $scope.mediaCount){
                            $scope.isWallAttachementUploading = false;
                            $('.dis-cret-m').removeClass('disble-btn-cus');
                        }
                     
                      
                        if (!$scope.$$phase)
                        {
                            $scope.$apply();
                        }
                    } else {
                        PermissionError(response.data.Message, 'alert-danger');
                        $scope.mediaCount = 0;
                        $scope.medias = {}
                        $scope.SelectedPhotoList = [];
                        $scope.showAddphoto = false;
                        $scope.isWallAttachementUploading = false;
                        $('.dis-cret-m').removeClass('disble-btn-cus');
                        $('#addPhotoAlbum').modal('hide')
                        
                    }
                } else {
                    if (response.data.ResponseCode == 200) {
                        $scope.files['file-' + fileIndex]['data'] = response.data.Data;
                        $scope.files['file-' + fileIndex].progress = true;
                    } else {
                        delete $scope.files['file-' + fileIndex];
                        $scope.fileCount = Object.keys($scope.files).length + Object.keys($scope.edit_files).length;
                        showResponseMessage(response.data.Message, 'alert-danger');
                        
                    }
                }
            },
            function (response) {

                if (fileType === 'media') {
                    delete $scope.medias['media-' + mediaIndex];
                  //  $scope.mediaCount = Object.keys($scope.medias).length;
                } else {
                    delete $scope.files['file-' + fileIndex];
                    $scope.fileCount = Object.keys($scope.files).length;
                }
            },
            function (evt) {

                apiService.FileUploadProgress({fileType: fileType, scopeObj: $scope, fileIndex: fileIndex, mediaIndex: mediaIndex}, evt);
            });
        return promise;
    }

    $scope.jicCompress = function(sourceImgObj, options) {
        var deferred = $q.defer();
        var outputFormat = options.resizeType;
        var quality = options.resizeQuality * 100 || 70;
        var mimeType = outputFormat;

        var maxHeight = options.resizeMaxHeight || 300;
        var maxWidth = options.resizeMaxWidth || 250;

        var height = sourceImgObj.height;
        var width = sourceImgObj.width;

        // calculate the width and height, constraining the proportions
        if (width > height) {
            if (width > maxWidth) {
                    height = Math.round(height *= maxWidth / width);
                    width = maxWidth;
            }
        }
       else {
            if (height > maxHeight) {
                    width = Math.round(width *= maxHeight / height);
                    height = maxHeight;
            }
        }

        var cvs = document.createElement('canvas');
        cvs.width = width; //sourceImgObj.naturalWidth;
        cvs.height = height; //sourceImgObj.naturalHeight;
        var ctx = cvs.getContext('2d').drawImage(sourceImgObj, 0, 0, width, height);
        var newImageData = cvs.toDataURL(mimeType, quality / 100);
        var resultImageObj = new Image();
        resultImageObj.src = newImageData;
        deferred.resolve({
            src: newImageData
        });
       // return resultImageObj.src;
        return deferred.promise;
    };
    $scope.dataURItoBlob =function(dataURI,mimeString) {
        var deferred = $q.defer();
        // convert base64/URLEncoded data component to raw binary data held in a string
        var byteString;
        if (dataURI.split(',')[0].indexOf('base64') >= 0)
            byteString = atob(dataURI.split(',')[1]);
        else
            byteString = unescape(dataURI.split(',')[1]);

        // write the bytes of the string to a typed array
        var ia = new Uint8Array(byteString.length);
        for (var i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        
        // write the ArrayBuffer to a blob, and you're done
        var blob = new Blob([ia], {type: mimeString});
         deferred.resolve({
            file: blob
        });
        return deferred.promise;
    };

    $scope.validateFileSize = function (file, config) {
      
    var defer = $q.defer();
    var isResolvedToFalse = false;
    var fileName = file.name;
    var mediaPatt = new RegExp("^image|video");
    var videoPatt = new RegExp("^video");
    config = (config) ? config : {};

    if (config.validExtensions) {
        var validExtensions = (config.validExtensions.constructor === Array) ? config.validExtensions : ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG']; //array of valid extensions
        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
        if ($.inArray(fileNameExt, validExtensions) == -1) {
            showResponseMessage('File type ' + fileNameExt + ' not allowed.', 'alert-danger');
            defer.resolve(false);
            isResolvedToFalse = true;
        }
    }

    var maxFileSize = (config.maxFileSize) ? config.maxFileSize : 41943040 /*16194304 Bytes = 40Mb*/;
    

    if (videoPatt.test(file.type)) {
        maxFileSize = (config.maxFileSize) ? config.maxFileSize : 41943040 /*41943040 Bytes = 40 Mb*/;
        if (file.size > maxFileSize) { // if video size > 41943040 Bytes = 40 Mb
            file.$error = 'size';
            file.$error = 'Size Error';
            showResponseMessage(file.name + ' is too large.', 'alert-danger');
            defer.resolve(false);
            isResolvedToFalse = true;
        }
    } else {
        if (file.size > maxFileSize) { // if image/document size > 4194304 Bytes = 4 Mb
            file.$error = 'size';
            file.$error = 'Size Error';
            //              file.$errorMessages = file.name + ' is too large.';
            showResponseMessage(file.name + ' is too large.', 'alert-danger');
            defer.resolve(false);
            isResolvedToFalse = true;
        }
    }

    if (!isResolvedToFalse) {
        defer.resolve(true);
    }
    return defer.promise;
  }

  $scope.resetPhotoPopup = function () {
        $scope.mediaCount = 0;
        $scope.medias = {}
        $scope.SelectedPhotoList = [];
        $scope.showAddphoto = false;
        $('.dis-cret-m').removeClass('disble-btn-cus');
        $('#addPhotoAlbum').modal('hide')
    };

    $scope.ReadMoreAlbum = function (data) {
       $scope.ReadMoreText = data ;
       openPopDiv('ReadMorePopup', 'bounceInDown');
    }

    $scope.locality_list  = [];
    $scope.getLocalityList = function () {
        apiService.call_api({},'api/locality/list').then(function(response) {
            if (response.ResponseCode != 200) {
                ShowErrorMsg(response.Message);
                return;
            }

            if (response.ResponseCode == 200)
            {
                $scope.locality_list = response.Data;
            }
        });
    }

    $scope.setShowActivity = function (value)
    {
        $scope.showActivity = value;
    }
    $scope.close_detail_box = function () {
        $scope.editDetails = 0;
        $scope.editNetworkDetail = 0;
        $scope.editPersonalDetail = 0;
        $scope.updateProfilePic = 0;
    }

    $scope.setPersonaData = function (user_id, user_guid, user_name)
    {
        angular.element(document.getElementById('UserListCtrl')).scope().showUserPersona(user_id, user_guid, user_name);
    }

});

app.filter('htmlString', function () {
    return function (text) {
        return text.replace(/&nbsp;/g, " ");
    }
});

app.directive('fineUploader', function () {
    return {
        restrict: 'A',
        require: '?ngModel',
        scope: {model: '='},
        replace: false,
        link: function ($scope, element, attributes, ngModel) {
            var serr = 1;
            $scope.uploader = new qq.FineUploader({
                element: element[0],
                multiple: true,
                title: "Attach a Photo",
                request: {
                    endpoint: base_url + "api/upload_image",
                    params: {
                        Type: attributes.sectionType,
                        unique_id: function () {
                            return '';
                        },
                        LoginSessionKey: $('#AdminLoginSessionKey').val(),
                        DeviceType: 'Native'
                    }
                },
                validation: {
                    allowedExtensions: attributes.uploadExtensions.split(','),
                    sizeLimit: 4194304 // 4mb
                },
                failedUploadTextDisplay: {
                    mode: 'none'
                },
                callbacks: {
                    
                    onUpload: function (id, fileName) {
                        openPopDiv('addphotoPopup', 'bounceInDown');
                        $('#CheckBtnShow').addClass('disble-btn-cus');
                       // showLoader();
                         var html = "<li id='dummy_img"+ id +"'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='"+base_url+"assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                         $('#attached-media-'+$(element).attr('unique-id')).append(html);
                    },
                    onProgress: function (id, fileName, loaded, total) {
                        
                    },
                    onComplete: function (id, fileName, responseJSON) {
                      //  hideLoader();
                      $('#CheckBtnShow').removeClass('disble-btn-cus');
                        if (responseJSON.Message == 'Success')
                        {
                            
                            //openPopDiv('addphotoPopup', 'bounceInDown', responseJSON.Data);
                            var AlbumPhotoListCtrl = angular.element('#AlbumPhotoListCtrl').scope();
                         
                            AlbumPhotoListCtrl.$apply(function () {  
                                AlbumPhotoListCtrl.SelectedPhotoList.push(responseJSON.Data)
                                
                            });
                            if(!AlbumPhotoListCtrl.showAddphoto){
                                hideLoader();
                              //  openPopDiv('addphotoPopup', 'bounceInDown');
                                AlbumPhotoListCtrl.showAddphoto = true
                            }
                            

                            
                            
                            //AlbumPhotoListCtrl.AddPhotoPopUp(responseJSON.Data)
                            
                            if ($(element).attr('image-type') == "landscape")
                            {
                                $('#attached-media-' + $(element).attr('unique-id')).html("<label>" + responseJSON.Data.ImageName + "</label>");
                            } else
                            {
                                


                                
                                //AddPhotoPopUp()
                                //$scope.SelectedPhotoList = responseJSON.Data
                                //console.log($scope.SelectedPhotoList,"$scope.SelectedPhotoList")
                               // var CategoryCtrl = angular.element('#CategoryCtrl').scope();

                                // CategoryCtrl.$apply(function () {
                                //     CategoryCtrl.currentData.ImageName = responseJSON.Data.ImageName;
                                //     CategoryCtrl.currentData.MediaGUID = responseJSON.Data.MediaGUID;
                                // });
                                //mdstart
                                // click_function = 'remove_image("'+responseJSON.Data.MediaGUID+'");';
                                //  var html = "<li class='catImgList' id='"+responseJSON.Data.MediaGUID+"'><a class='smlremove' onclick='$(this).parent(\"li\").remove();'></a>";
                                //  html+= "<figure><img alt='' width='98px' class='img-"+$(element).attr('image-type')+"-full' media_type='IMAGE' is_cover_media='0' media_name='"+responseJSON.Data.ImageName+"' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+responseJSON.Data.ImageServerPath +'/'+responseJSON.Data.ImageName+"'></figure>";
                                //  html+= "<span class='radio'></span><input type='hidden' name='MediaGUID[]' value='" + responseJSON.Data.MediaGUID + "'/></li>";

                                //  $('#attached-media-'+$(element).attr('unique-id')).append(html);
                                 //mdend
                                 // var $items = $('.img-full');
                            }
                            $('#dummy_img'+ id).remove();
                        } else if (responseJSON.ResponseCode !== 200)
                        {
                            $('#attached-media-' + $(element).attr('unique-id')).html("");
                        }
                    },
                    onValidate: function (b)
                    {
                        var allowed_extension = $(element).attr('upload-extensions');
                        var temp = new Array();
                        validExtensions = allowed_extension.split(",");
                        var fileName = b.name;
                        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                        if ($.inArray(fileNameExt, validExtensions) == -1)
                        {
                            $("html, body").animate({scrollTop: 0}, "slow");
                            if ($(element).attr('image-type') == "landscape")
                            {
                                PermissionError('Allowed file types only doc, docx, pdf and xls.');
                            } else
                            {
                                PermissionError('Allowed file types only jpeg, jpg, gif and png.');
                            }
                            return false;
                        }
                        if (b.size > 4194304) {
                            $scope.ErrorStatus = true;
                            //$scope.Error.error_Schollyme_Thumbnail = required_song_thumb;
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                            PermissionError('Image should be less than 4 MB.');                   
                        }
                    },
                    onError: function () {
                        $('#cm-' + attributes.uniqueId + ' .loading-class').remove();
                    }
                },
                showMessage: function (message) {
                    //showResponseMessage(message,'alert-danger');
                },
                text: {
                    uploadButton: '<i class="icon-upload icon-white"></i> Upload File(s)'
                },
                template: ' <a class="qq-upload-button"  title="Attach a Photo"><button>Add Photo</button></a><span class="qq-drop-processing qq-upload-drop-area" style="display:none;"></span>' +
                        '<ul class="qq-upload-list" style="display:none;margin-top: 10px; text-align: center;"></ul>',
                chunking: {
                    //enabled: false
                    //onclick=$(\'#cmt-'+attributes.uniqueId+'\').trigger(\'focus\');
                }
            });
        }
    };
});


app.directive('enterPress', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if (event.which === 13) {
                scope.$apply(function () {
                    scope.$eval(attrs.enterPress);
                });

                event.preventDefault();
            }
        });
    };
});

function remove_image(MediaGUID) {
    $('#' + MediaGUID).remove();
}
 
