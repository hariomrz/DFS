/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// IP Controller
app.controller('AlbumCtrl', function ($scope, $rootScope, albumData, $window, $timeout, apiService, lazyLoadCS, Upload, $q) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = pagination,
    $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.sort_by = "Name";    
    $scope.currentData = {};
    $scope.currentIsActive = 0;
    $scope.CreateAlbumData = '';
    $scope.image_path = image_path;
    $scope.isLoadingImage = false,
    $scope.AlbumNewName=''
    
    $scope.LocalityOptions = [{Name:'All', MKey:''},{Name:'Tulsi Nagar', MKey:'1'},{Name:'Mahalaxmi Nagar', MKey:'2'},{Name:'Sai Kripa', MKey:'3'},{Name:'Suncity', MKey:'4'},{Name:'Chikitsak Nagar', MKey:'5'}]
    
    
    $scope.DataList = function (searchBtn) {
        if (searchBtn != undefined) {
            if (!openSearch() || $scope.SearchKeyword == undefined) {
                return;
            }
        }
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

    
    $scope.DataList2 = function (searchBtn) {
        if (searchBtn != undefined) {
            if (!openSearch() || $scope.SearchKeyword == undefined) {
                return;
            }
        }
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
            SortBy: '',
            OrderBy: $scope.reverseSort,
            SearchKeyword: $scope.SearchKeyword,
            IsFeatured:1
            //Send AdminLoginSessionKey
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
        

        //Call getIpList in services.js file
        albumData.getList(reqData).then(function (response) {
            $scope.listData2 = response.Data;
            

            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };
    $scope.DataList3 = function (searchBtn) {
        if (searchBtn != undefined) {
            if (!openSearch() || $scope.SearchKeyword == undefined) {
                return;
            }
        }
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
            SortBy: 2,
            OrderBy: $scope.reverseSort,
            SearchKeyword: $scope.SearchKeyword,
            //Send AdminLoginSessionKey
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
    

        //Call getIpList in services.js file
        albumData.getList(reqData).then(function (response) {
            $scope.listData3 = response.Data;
            

            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };
    $scope.DataList4 = function (searchBtn) {
        if (searchBtn != undefined) {
            if (!openSearch() || $scope.SearchKeyword == undefined) {
                return;
            }
        }
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
        if ($scope.ModuleID == "")
        {
            $scope.ModuleID = [45,46];   
        }
        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            SortBy: '',
            OrderBy: $scope.reverseSort,
            IsFeatured:0,
            SearchKeyword: $scope.SearchKeyword,
            //Send AdminLoginSessionKey
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }

        //Call getIpList in services.js file
        albumData.getList(reqData).then(function (response) {
            $scope.listData4 = response.Data;
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };
    //Apply Sort by and mamke request data
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
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                //AdminLoginSessionKey: $scope.AdminLoginSessionKey
            }
            $scope.DataList();
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
            ModuleID: $scope.ModuleID,
            //Send AdminLoginSessionKey
            //AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
        $scope.DataList();
        
        $scope.DataList2();
        $scope.DataList3();
      //  $scope.DataList4();
    });
    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    };

    //Function for set Ip details in scope variables
    $scope.SetDetail = function (data) {
        $scope.currentData = {};
        $scope.currentData = data;
        $scope.AlbumNewName = data.AlbumName;
        $scope.currentData['media'] = data.CoverMedia;
        
    };

    $scope.resetPopup = function () {
        if ($('input[name="MediaGUID[]"]').length > 0) {
            $('input[name="MediaGUID[]"]').each(function (k, v) {
                $('.catImgList').remove();
            });
        }
        $scope.currentData = {'category_id': "", 'locality_id': "", "name": "", "parent_id": "0", "module_id": "", "description": "", "media": {}};
        $scope.parent_hide = false;
        $scope.AlbumNewName=''
        $('#uploadimageBTN').show();
        
    }

    $scope.CloseAlbumPopup = function () {
        $('#addIpPopup').modal('hide')
        $scope.CreateAlbumData = '';
        $scope.isLoadingImage = false;
        $('.view-curn-img').show();
        $scope.currentData = {'category_id': "", 'locality_id': "", "name": "", "parent_id": "0", "module_id": "", "description": "", "media": {}};
        $scope.parent_hide = false;
        $('#uploadimageBTN').show();
        
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
        $('#addIpPopup').modal();
        $scope.CreateAlbumData = '';
        $scope.isLoadingImage = false
        $scope.ModuleID = $scope.currentData.module_id;
        
        // $('#attached-media-1 li').remove();
        $scope.resetPopup();

        $("#chkActive").attr('checked', true).parent('span').addClass('icon-checked');
        $scope.showIpError = false;
        $scope.errorIpMessage = null;
        $scope.showAlbumNameError = false;
        $scope.showDescriptionError = false;
        
        //openPopDiv('addIpPopup', 'bounceInDown');
        
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
            //openPopDiv('addIpPopup', 'bounceInDown');
            $('#addIpPopup').modal();
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
                $scope.DataList();
                $scope.DataList2();
                $scope.DataList3();
              //  $scope.DataList4();

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
          //  $("html, body").animate({scrollTop: 0}, "slow");

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
                //$scope.DataList();
                $scope.DataList2();

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
       //     $("html, body").animate({scrollTop: 0}, "slow");

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
                $scope.DataList();

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
         //   $("html, body").animate({scrollTop: 0}, "slow");

        }), function (error) {
            hideLoader();
        }
    };

    $scope.DeleteAlbum = function (status) { 
        
        showLoader();
        var reqData = {
            'AlbumGUID': $scope.currentData.AlbumGUID,
        };
        
        showLoader();
        albumData.DeleteAlbum(reqData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                hideLoader();
                ShowSuccessMsg(response.Message);
                $scope.DataList();

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
        //    $("html, body").animate({scrollTop: 0}, "slow");

        }), function (error) {
            hideLoader();
        }
    };

    $scope.AddEditAlbum = function () {

        var AlbumName = $scope.AlbumNewName;
        var Description = $scope.currentData.Description;
        var module_id = $scope.currentData.module_id;
     //   $('#uploadimageBTN').show();
        var media = {};
        var i = 0;
        if ($scope.CreateAlbumData !== '') {
            $($scope.CreateAlbumData).each(function (k, v) {
                media[i] = {};
                media[i]['MediaGUID'] = v.MediaGUID
                i++;
            });
        }

        $scope.currentData.media = media;

        var media_guid = $('.img-category-full').attr('media_guid');
        var media_name = $('.img-category-full').attr('media_name');
        if (media_guid == undefined) {
            media_guid = '';
        }
        $scope.category = {};
        var show_error = 0;
        $scope.hasError = false;
        $scope.errorAlbumNameMessage = '';
        $scope.errorDescriptionMessage = '';
        
        if (AlbumName == "") {
            $scope.showAlbumNameError = true;
            $scope.errorAlbumNameMessage = 'Please enter valid  name.';
            $scope.hasError = true;
        }
        
        if (Description == "" ) {
            $scope.showDescriptionError = true;
            $scope.errorDescriptionMessage = 'Please enter description.';
            $scope.hasError = true;
        }

        
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
            $scope.errorDescriptionMessage = null;
            var reqData = {
                //'CommisionGUID':$scope.currentCommission.CommisionGUID,
                'AlbumGUID': $scope.currentData.AlbumGUID,
                'AlbumName': $scope.AlbumNewName,
                'Description': $scope.currentData.Description,
                'AdminLoginSessionKey': $scope.AdminLoginSessionKey,
                'Media': media,
                'Location':'',
                'isCoverPic' :(media)?1:0
            };
            if ($scope.currentData.AlbumGUID) {
                reqData.Url = 'api/album/edit';
            } else {
                reqData.Url = 'api/album/add';
            }
            albumData.Save(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    //Show Success message
                    ShowSuccessMsg(response.Message);
                    $scope.resetPopup();
                    $scope.category.filter_module = module_id;
                    $scope.DataList();
                    $scope.DataList2();
                    $scope.DataList3();
                    $scope.CreateAlbumData = '';
                    //closePopDiv('addIpPopup', 'bounceOutUp');
                    $('#addIpPopup').modal('hide');
                } else if (response.ResponseCode == 517) {
                    redirectToBlockedIP();
                } else if (response.ResponseCode == 598) {
                    //Show error message
                   // closePopDiv('addIpPopup', 'bounceOutUp');
                    $('#addIpPopup').modal('hide');
                    PermissionError(response.Message);
                } else {
                    $scope.showAlbumNameError = true;
                    $scope.errorAlbumNameMessage = response.Message;
                    
                    $('#uploadimageBTN').hide();
                    //$scope.showDescriptionError = true;
                    //$scope.errorDescriptionMessage = response.Message;
                }
                $('.loader_ip').hide();
            });
        }

        $timeout(function () {
            $scope.showIpError = false;
            $scope.errorIpMessage = null;
        }, 5000);

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
    $scope.delete_album_image = function () {
        $scope.CreateAlbumData = '';
        $('#uploadimageBTN').show();
        $('.view-curn-img').show();
        
    }

    $scope.GotoDetail = function (url) {
        window.location = "album/album_detail?album_guid"+'='+url
    }

    // for image compresion 
    $scope.uploadProfilePicture = function (file, errFiles) {
        //console.log('Uploading via ngf uploader');
        
        angular.forEach(errFiles, function(errFile){
            showResponseMessage(errFile.$errorMessages, 'alert-danger');
        });
        
        if(!file) {
            return;
        }     

        var filename = file.name;
        var mimeString = file.type;
        var c = 0;
        var serr = 1;
        
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
                    $scope.uploadProfilePic(errFiles,file,serr,c);
                });
            });
        }
        image.src = url;
    };

    $scope.uploadProfilePic = function(errFiles,file,serr,c){
        $scope.isLoadingImage = true;
        $('.view-curn-img').hide();
        $('.dis-cret-m').addClass('disble-btn-cus');
        $('#uploadimageBTN').hide();
        if (!(errFiles.length > 0)) {
            var patt = new RegExp("^image");
            $scope.isProfilePicUploading = true;
            albumData.setFileMetaData(file);
            
            var paramsToBeSent = {           
                qqfile: file,
                Type: 'album',
                DeviceType: 'Native',
                LoginSessionKey: $('#AdminLoginSessionKey').val()
            };
            // if (file.type === 'image/gif') {
            //     PermissionError('GIF image files are not allowed.', 'alert-danger');
            //     $scope.isLoadingImage= false;
            //      $('#uploadimageBTN').show();
            //      $('.dis-cret-m').removeClass('disble-btn-cus');
                
            //     return false;
            // }
            if (!patt) {
                PermissionError('Only image files are allowed.', 'alert-danger');
                     $scope.isLoadingImage= false;
                $('#uploadimageBTN').show();
                 $('.dis-cret-m').removeClass('disble-btn-cus');
                return false;
            } else if (!patt.test(file.type)) {
                PermissionError('Only image files are allowed.', 'alert-danger');
                $scope.isLoadingImage= false;
                $('#uploadimageBTN').show();
                 $('.dis-cret-m').removeClass('disble-btn-cus');
                return false;
            }

            

            
            
            apiService.CallUploadFilesApi(
                paramsToBeSent,
                'api/upload_image',
                function(response) {
                if (response.data.ResponseCode === 200) {
                    var responseJSON = response.data;
                    if (responseJSON.Message == 'Success') {
                        $scope.isLoadingImage = false;
                          $scope.CreateAlbumData = responseJSON.Data;
                          $('.dis-cret-m').removeClass('disble-btn-cus');
                          
                        } else {
                            PermissionError(responseJSON.Message);
                            serr++;
                            console.log(serr);
                            $scope.isLoadingImage= false;
                            $('#uploadimageBTN').show();
                             $('.dis-cret-m').removeClass('disble-btn-cus');
                        }
                    } else {
                        console.log(serr);
                        if (serr == 1) {
                            PermissionError('The uploaded image does not seem to be in a valid image format.');
                            $scope.isLoadingImage= false;
                            $('#uploadimageBTN').show();
                             $('.dis-cret-m').removeClass('disble-btn-cus');
                        } else {
                            serr = 1;
                        }
                    }
                    $scope.isProfilePicUploading = false;
                },
                function(response) {
                    console.log(serr);
                    if (serr == 1) {
                        //alertify.error('The uploaded image does not seem to be in a valid image format.');
                    } else {
                        serr = 1;
                    }
                },
                function(evt) {
                    c = parseInt($('#image_counter').val());
                    c = c + 1;
                    $('#image_counter').val(c);
                });

        } else {
            showResponseMessage(errFiles[0].$errorMessages, 'alert-danger');
        }
    };

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
                multiple: false,
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
                    allowedExtensions: attributes.uploadExtensions.split(',')
                },
                failedUploadTextDisplay: {
                    mode: 'none'
                },
                callbacks: {
                    onUpload: function (id, fileName) {
                        $('.dis-cret-m').addClass('disble-btn-cus');
                        var html = "<li id='dummy_img"+ id +"'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='"+base_url+"assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                        $('#attached-media-'+$(element).attr('unique-id')).append(html);
                    },
                    onProgress: function (id, fileName, loaded, total) {
                        $('#uploadimageBTN').hide();
                    },
                    onComplete: function (id, fileName, responseJSON) {
                        if (responseJSON.Message == 'Success')
                        {  $('.dis-cret-m').removeClass('disble-btn-cus');
                            $('#uploadimageBTN').hide();
                            if ($(element).attr('image-type') == "landscape")
                            {
                                $('#attached-media-' + $(element).attr('unique-id')).html("<label>" + responseJSON.Data.ImageName + "</label>");
                            } else
                            {
                               // var CategoryCtrl = angular.element('#CategoryCtrl').scope();

                                // CategoryCtrl.$apply(function () {
                                //     CategoryCtrl.currentData.ImageName = responseJSON.Data.ImageName;
                                //     CategoryCtrl.currentData.MediaGUID = responseJSON.Data.MediaGUID;
                                // });
                                var click_function = 'remove_image("'+responseJSON.Data.MediaGUID+'");';
                                 var html = "<li class='catImgList' id='"+responseJSON.Data.MediaGUID+"'><a class='smlremove' onclick='"+click_function+"'></a>";
                                 html+= "<figure><img alt='' width='98px' class='img-"+$(element).attr('image-type')+"-full' media_type='IMAGE' is_cover_media='0' media_name='"+responseJSON.Data.ImageName+"' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+responseJSON.Data.ImageServerPath +'/'+responseJSON.Data.ImageName+"'></figure>";
                                 html+= "<span class='radio'></span><input type='hidden' name='MediaGUID[]' value='" + responseJSON.Data.MediaGUID + "'/></li>";

                                 $('#attached-media-'+$(element).attr('unique-id')).append(html);
                                 // var $items = $('.img-full');
                                 $('.view-curn-img').remove();
                                 
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
                        {   $('#uploadimageBTN').show();
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
                        $('#uploadimageBTN').show();
                        $('#cm-' + attributes.uniqueId + ' .loading-class').remove();
                    }
                },
                showMessage: function (message) {
                    //showResponseMessage(message,'alert-danger');
                },
                text: {
                    uploadButton: '<i class="icon-upload icon-white"></i> Upload File(s)'
                },
                template: ' <a class="qq-upload-button"  title="Attach a Photo"><button>Upload</button></a><span class="qq-drop-processing qq-upload-drop-area" style="display:none;"></span>' +
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
    $('#uploadimageBTN').show();
    $('.view-curn-img').show();
}
 
