/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// IP Controller
app.controller('AlbumListCtrl', function ($scope, $rootScope,$routeParams,albumData, $window, $timeout, apiService, lazyLoadCS, Upload, DashboardService) {
    $scope.totalRecord = 0;
    $scope.currentPage = 1,
    $scope.numPerPage = 20,
    $scope.maxSize = 3;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.sort_by = "Name";    
    $scope.currentData = {};
    $scope.currentIsActive = 0;
    $scope.image_path = image_path;
    $scope.type = $routeParams.type;
    console.log('$routeParams =',$routeParams)
    $scope.pagination = {
        currentPage: 1,
        maxSize: 3,
        totalRecord: 0
    };
    $scope.LocalityOptions = [{Name:'All', MKey:''},{Name:'Tulsi Nagar', MKey:'1'},{Name:'Mahalaxmi Nagar', MKey:'2'},{Name:'Sai Kripa', MKey:'3'},{Name:'Suncity', MKey:'4'},{Name:'Chikitsak Nagar', MKey:'5'}]
    
    
    // $scope.DataList = function (searchBtn) {
    //     alert(0)
    //     if (searchBtn != undefined) {
    //         if (!openSearch() || $scope.SearchKeyword == undefined) {
    //             return;
    //         }
    //     }
    //     intilizeTooltip();
    //     showLoader();

        
    //     $scope.startDate = $('#SpnFrom').val();
    //     $scope.endDate = $('#SpnTo').val();

        
    //     var begins = '';
    //     if ($scope.currentPage == 1) {
        
            
    //         begins = 1;//$scope.currentPage;
    //     } else {
    //          begins = $scope.currentPage + 1
    //     }

    //     /* Send AdminLoginSessionKey in every request */
    //     $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
    //     var reqData = {
    //         PageNo: $scope.pagination, //$scope.currentPage,
    //         PageSize: $scope.numPerPage,
    //         SortBy: 1,
    //         OrderBy: $scope.reverseSort,
    //         //IpFor: $scope.IpFor,
    //         SearchKeyword: $scope.SearchKeyword,
    //         //Send AdminLoginSessionKey
    //         //AdminLoginSessionKey: $scope.AdminLoginSessionKey
    //     }

    //     $scope.ablum_type = $("#album_type").val();
    //     if($scope.ablum_type=='new') {
    //         reqData.SortBy=1
    //         reqData.IsFeatured=0;
        
    //     } else if ($scope.ablum_type=='featured') {
    //         reqData.IsFeatured=1; 
    //         reqData.SortBy=1; 

    //     } else if ($scope.ablum_type=='popular') {
    //         reqData.SortBy=2; 
    //         reqData.IsFeatured=0;

    //     } 
    //     //Call getIpList in services.js file
    //     albumData.getList(reqData).then(function (response) {
            
    //         $scope.listData = response.Data;
    //         $scope.totalRecord =  response.TotalRecords
            
    //         hideLoader();

    //     }), function (error) {
    //         hideLoader();
    //     }
    // };



    function onDataListSuccess(reqData, response) {
                       
        $scope.listData = response.Data;
        if(reqData.PageNo == 1) {
            $scope.pagination.totalRecord = response.TotalRecords;  
                        
        }
        $scope.pagination.currentPage = reqData.PageNo;
                    
    }

    function getRequestObj(newObj, reset, requestType) {
        var reqData = {
            PageNo: 1,
            PageSize: 20,

        };
        $scope.ablum_type = $("#album_type").val();
        
        if($scope.ablum_type=='all') {
            reqData.SortBy=1
            reqData.IsFeatured=0;
        
        } else if ($scope.ablum_type=='featured') {
            reqData.IsFeatured=1; 
            reqData.SortBy=1; 

        } else if ($scope.ablum_type=='popular') {
            reqData.SortBy=2; 
            reqData.IsFeatured=0;

        }

        requestType = (requestType) ? requestType : 'Normal';
        if (reset) {
            getRequestObj[requestType] = angular.extend(angular.copy(reqData), newObj);
            return getRequestObj[requestType];
        }
        getRequestObj[requestType] = getRequestObj[requestType] || angular.copy(reqData);
        getRequestObj[requestType] = angular.extend(getRequestObj[requestType], newObj);
        return getRequestObj[requestType];
    }

    $scope.getThisPage = function () {
        
        console.log('getThisPage', $scope.pagination.currentPage);
        var requestObj = getRequestObj({
            PageNo: $scope.pagination.currentPage
        });

        getDailyDigestList(requestObj);
    }

    function getDailyDigestList(reqData)
    {
        //alert(0)
        DashboardService.CallPostApi('api/album/list', reqData, function (response) {
            var response = response.data;
            if (response.ResponseCode == 200) {  
                onDataListSuccess(reqData, response);                      
               
            }
            else
            {
                ShowErrorMsg(response.Message);
            }
        });
    }

    $scope.initDailyDigestFn = function () {  
        var reqData = {
            PageNo: 1,
            PageSize: 20,

        };
        $scope.ablum_type = $("#album_type").val();
        
        if($scope.ablum_type=='all') {
            reqData.SortBy=1
            reqData.IsFeatured=0;
        
        } else if ($scope.ablum_type=='featured') {
            reqData.IsFeatured=1; 
            reqData.SortBy=1; 

        } else if ($scope.ablum_type=='popular') {
            reqData.SortBy=2; 
            reqData.IsFeatured=0;

        }
        getDailyDigestList(reqData);
    }

    
  
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
      //  $scope.DataList();
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

    $scope.openDescriptionPopUp = function (des) {
        $scope.showDescriptionText = des; alert(des)
        openPopDiv('showDescription', 'bounceInDown');
    };


    $scope.AddDetailsPopUp = function () {
        $scope.ModuleID = $scope.currentData.module_id;
       
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
            $("html, body").animate({scrollTop: 0}, "slow");

        }), function (error) {
            hideLoader();
        }
    };

    $scope.AddEditAlbum = function () {

        var AlbumName = $scope.currentData.AlbumName;
        var Description = $scope.currentData.Description;
        var module_id = $scope.currentData.module_id;

        var media = {};
        var i = 0;
        if ($('input[name="MediaGUID[]"]').length > 0) {
            $('input[name="MediaGUID[]"]').each(function (k, v) {
                media[i] = {};
                media[i]['MediaGUID'] = $('input[name="MediaGUID[]"]:eq(' + i + ')').val();
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
                'AlbumName': $scope.currentData.AlbumName,
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
                    closePopDiv('addIpPopup', 'bounceOutUp');
                } else if (response.ResponseCode == 517) {
                    redirectToBlockedIP();
                } else if (response.ResponseCode == 598) {
                    //Show error message
                    closePopDiv('addIpPopup', 'bounceOutUp');
                    PermissionError(response.Message);
                } else {
                    $scope.showAlbumNameError = true;
                    $scope.errorAlbumNameMessage = response.Message;
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

    $scope.GotoDetail = function (url) {
        window.location = "album_detail?album_guid"+'='+url
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
                    allowedExtensions: attributes.uploadExtensions.split(',')
                },
                failedUploadTextDisplay: {
                    mode: 'none'
                },
                callbacks: {
                    onUpload: function (id, fileName) {
                        var html = "<li id='dummy_img"+ id +"'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='"+base_url+"assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                        $('#attached-media-'+$(element).attr('unique-id')).append(html);
                    },
                    onProgress: function (id, fileName, loaded, total) {
                    },
                    onComplete: function (id, fileName, responseJSON) {
                        if (responseJSON.Message == 'Success')
                        {
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
                                click_function = 'remove_image("'+responseJSON.Data.MediaGUID+'");';
                                 var html = "<li class='catImgList' id='"+responseJSON.Data.MediaGUID+"'><a class='smlremove' onclick='$(this).parent(\"li\").remove();'></a>";
                                 html+= "<figure><img alt='' width='98px' class='img-"+$(element).attr('image-type')+"-full' media_type='IMAGE' is_cover_media='0' media_name='"+responseJSON.Data.ImageName+"' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+responseJSON.Data.ImageServerPath +'/'+responseJSON.Data.ImageName+"'></figure>";
                                 html+= "<span class='radio'></span><input type='hidden' name='MediaGUID[]' value='" + responseJSON.Data.MediaGUID + "'/></li>";

                                 $('#attached-media-'+$(element).attr('unique-id')).append(html);
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
}
 
