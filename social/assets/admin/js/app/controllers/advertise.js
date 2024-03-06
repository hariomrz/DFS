// UserList Controller
//app.requires.push('ngAutocomplete');
app.controller('AdvertiseCtrl', function ($scope, $rootScope, $timeout, $location, getArticleData, $window, $attrs, $sce) {

    $scope.totalRecord = 0;
    $scope.filteredTodos = [],
            $scope.currentPage = 1,
            $scope.numPerPage = pagination,
            $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.currentUserRoleId = {};
    $scope.currentUserStatusId = {};
    $rootScope.currentUserName = '';
    $rootScope.totalUsers = 0;
    $scope.useraction = '';

    $scope.globalChecked = false;
    $scope.showButtonGroup = false;
    $scope.selectedUsers = {};
    $scope.selectedUsersIndex = {};
    $scope.confirmationMessage = '';

    $scope.SCategory = '';
    $rootScope.PTitle = '';
    $rootScope.Astatus = '';
    $scope.upload_new_image = false;
    $scope.ErrorLocation = [];

    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    $scope.existingCarousel = function () {
        $timeout(function () {
            $('#existing-jcarousel').jcarousel();
            $('.jcarousel-control .prev').on('jcarouselcontrol:active', function () {
                $(this).removeClass('inactive');
            }).on('jcarouselcontrol:inactive', function () {
                $(this).addClass('inactive');
            }).jcarouselControl({
                target: '-=1'
            });

            $('.jcarousel-control .next').on('jcarouselcontrol:active', function () {
                $(this).removeClass('inactive');
            }).on('jcarouselcontrol:inactive', function () {
                $(this).addClass('inactive');
            }).jcarouselControl({
                target: '+=1'
            });
        }, 0); // wait...
    };

    //content data
    $scope.Blog = {};

    $scope.articleList = function () {
        intilizeTooltip();
        showLoader();
        $scope.selectedUsers = {};
        $scope.globalChecked = false;

        $scope.searchKey = '';
        if ($('#AsearchField').val()) {
            $scope.searchKey = $('#AsearchField').val();
            $('#AsearchButton').addClass('selected');
        }

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').text();
        $scope.endDate = $('#SpnTo').text();

        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage);
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
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey,
        }
        if ($scope.Type != undefined && $scope.Type == 'banner') {
            reqData.Type = 'banner'
        }
        //var reqUrl = reqData[1]
        //Call getUserlist in services.js file


        //return false;
        getArticleData.articleList(reqData).then(function (response) {
            $scope.listData = [];
            //If no. of records greater then 0 then show
            $('.simple-pagination').show();


            if (response.ResponseCode == 200) {
                $scope.noOfObj = response.Data.total_records
                $rootScope.totalUsers = $scope.totalRecord = $scope.noOfObj;

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link').hide();
                    $('#ArticleListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="7"><span style="color:rgb(255, 0, 0);">No records found.</span></td></tr>');
                    $('.simple-pagination').hide();
                }

                //Push data into Controller in view file
                $.each(response.Data.results, function () {
                    this.created_by = $sce.trustAsHtml(this.created_by);
                    //alert(this.created_by);
                });
                $scope.listData.push({ObjUsers: response.Data.results});

            } else if (response.ResponseCode == 598) {
                PermissionError(response.Message);
            }
            hideLoader();

        }), function (error) {
            hideLoader();
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
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
        //$scope.articleList();
        if ($scope.PageType == 'List') {
            $scope.bannerList();
        }
    });

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
                StartDate: $scope.startDate,
                EndDate: $scope.endDate,
                SearchKey: $scope.searchKey,
                UserStatus: $scope.userStatus,
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey: $scope.AdminLoginSessionKey
            }
            $scope.articleList();
        }
    };


    /*================================================================================================
     Content Management Start     
     ================================================================================================*/

    $scope.SetBlog = function (data) {
        $scope.Blog = data;
    }

    $scope.getArticleDetails = function (BlogID) {
        var requestData = {BlogID: BlogID, AdminLoginSessionKey: $scope.AdminLoginSessionKey};
        getArticleData.getArticleDetails(requestData).then(function (response) {
            $scope.Blog = response.Data.results;
        });
    };

    $scope.removeHashParam = function () {
        $location.path('/');
    }

    $scope.redirectToPage = function (Page) {
        window.location = base_url + Page;
    }

    /*
     * Check for various url params 
     * and execute accordingly
     * @param Object
     * @Author Sudhir Parmar
     */
    $scope.checkUrl = function () {
        urlHash = window.location.hash;
        var hashArr = urlHash.split('/');
        switch (hashArr[1]) {
            case 'added':
                ShowSuccessMsg('Record added successfully');
                break;
            case 'updated':
                ShowSuccessMsg('Record updated successfully');
                break;
        }
        $scope.removeHashParam();

    }
    //$scope.checkUrl();

    /*
     * Update content of static pages (aboutUs, privacy etc)
     * @returns 
     * @Author Sudhir Parmar
     */
    $scope.UpdateBlog = function ()
    {
        //console.log($scope.Blog);
        //return;
        $('.loader_smtp').show();
        var requestData = {
            BlogID: $scope.Blog.BlogID,
            BlogDescription: $('#Description').val(),
            BlogTitle: $scope.Blog.BlogTitle,
            BlogImage: $scope.Blog.BlogImage,
            Type: $scope.Blog.Type,
            AdminLoginSessionKey: $scope.AdminLoginSessionKey

        }
        getArticleData.UpdateBlogData(requestData).then(function (response) {

            if (response.ResponseCode == 200) {
                $scope.redirectToPage('admin/advertise/article#updated');
            } else {
                ShowErrorMsg(response.Message);
            }
            $('.loader_smtp').hide();
        });
    };

    /*================================================================================================
     Banner Management Start     
     ================================================================================================
     */

    $scope.searchKey = '';
    $scope.searchBannerStatus = '';
    $scope.searchBannerModule = '';

    $scope.BannerModule = {
        //'home_page_sidebar1': 'LHS Banner',
        'home_page_sidebar1': '300 X 600 Ad',
        //'home_page_sidebar2': 'RHS Banner',
        'home_page_sidebar2': '300 X 250 Ad',
        /*'article_detail': 'Article Ad',
         'chat_detail': 'Chat Ad', 
         'photo_detail': 'Photo Ad', 
         'video_detail': 'Video Ad',
         'profile': 'Profile Ad',
         'dashboard': 'Dashboard Ad',
         'monthly_competition': 'MC Ad',
         'home_page_carousel': 'Home Page Banner',
         'home_page_sidebar1': 'Home Page RHS 1',
         'home_page_sidebar2': 'Home Page RHS 2',
         'notification': 'Notification Ad',
         'manics': 'Maniacs Ad',
         'race_event_carousel': 'Race Discount Banner',
         'race_event_sidebar1': 'Race Discount RHS 1',
         'race_event_sidebar2': 'Race Discount RHS 2',
         'race_event_sidebar3': 'Race Discount RHS 3',
         'race_event_sidebar4': 'Race Discount RHS 4',
         'race_event_sidebar5': 'Race Discount RHS 5',
         'race_event_sidebar6': 'Race Discount RHS 6',
         'race_event_sidebar7': 'Race Discount RHS 7',
         'race_event_sidebar8': 'Race Discount RHS 8',*/
    };

    $scope.BannerStatus = {
        'Active': 'Active',
        'Inactive': 'Inactive',
        'Expire': 'Expired'
    };


    $scope.BannerDurations = [
        {dKey: '1', dVal: '1 Second'},
        {dKey: '2', dVal: '2 Seconds'},
        {dKey: '3', dVal: '3 Seconds'},
        {dKey: '4', dVal: '4 Seconds'},
        {dKey: '5', dVal: '5 Seconds'},
        {dKey: '6', dVal: '6 Seconds'},
        {dKey: '7', dVal: '7 Seconds'},
        {dKey: '8', dVal: '8 Seconds'},
        {dKey: '9', dVal: '9 Seconds'},
        {dKey: '10', dVal: '10 Seconds'},
        {dKey: '11', dVal: '11 Seconds'},
        {dKey: '12', dVal: '12 Seconds'},
        {dKey: '13', dVal: '13 Seconds'},
        {dKey: '14', dVal: '14 Seconds'},
        {dKey: '15', dVal: '15 Seconds'}
    ];

    $scope.BannerData = {
        'BlogID': '',
        'BlogUniqueID': '', // banner module
        'BlogTitle': '',
        'Advertiser': '',
        'BannerSource': '1',
        'BannerSize': '',
        'BlogDescription': '',
        'URL': '',
        'Duration': '5',
        'StartDate': '',
        'EndDate': '',
        'AdvertiserContact': '',
        'NoOfHits': '0',
        'BlogImage': '',
        'SourceScript': '',
        'Locations': [],
        'Location': '',
    };

    $scope.DefaultBannerData = {
        'BlogID': '',
        'BannerSize': '',
        'BlogDescription': '',
        'URL': '',
        'NoOfHits': '0',
        'BlogImage': '',
        'SourceScript': ''
    };

    $scope.DefaultBannerData1 = {
        'BlogID': '',
        'BannerSize': '',
        'BlogDescription': '',
        'URL': '',
        'NoOfHits': '0',
        'BlogImage': '',
        'SourceScript': ''
    };

    $scope.DefaultBannerData2 = {
        'BlogID': '',
        'BannerSize': '',
        'BlogDescription': '',
        'URL': '',
        'NoOfHits': '0',
        'BlogImage': '',
        'SourceScript': ''
    };

    $scope.getImageListFlag = true;
    $scope.getAdvertiserList = function (request, response) {
        //console.log('test', request.term);

        $scope.getImageListFlag = true;

        var requestData = {
            AdminLoginSessionKey: $scope.AdminLoginSessionKey,
            SearchText: request.term
        };
        getArticleData.getAdvertiserList(requestData).then(function (result) {
            if (result.ResponseCode == 200) {
                response(result.Data);
            } else if (result.ResponseCode == 519) {
                PermissionError(result.Message);
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }

    };

    $scope.SelectAdvertiser = function (event, ui) {
        //console.log('select', ui.item);
        //$("#Advertiser").val(ui.item.value);
        $scope.BannerData.Advertiser = ui.item.value;
        $scope.$apply();
        $scope.getBannerImageList();
        $scope.getImageListFlag = false;
        $scope.BannerData.SelectedBlogImage = '';
        //console.log('Advertiser: ',$scope.BannerData.Advertiser);
        return false;
    }
    $scope.SearchAdvertiser = function () {
        $("#Advertiser").autocomplete({
            source: $scope.getAdvertiserList,
            select: $scope.SelectAdvertiser,
            minLength: 1,
            change: function () {
                //$("#Advertiser").val("").css("display", 2);
                //console.log('change', $scope.getImageListFlag);
                if ($scope.getImageListFlag) {
                    $scope.getBannerImageList();
                }
            }
        });
    };

    $scope.SetCurretBannerData = function (data) {
        $scope.CurretBannerData = data;
    };

    // Set selected banner size in case of existing image
    $scope.setSelectedBannerSize = function () {

        $scope.OldSelectedBannerSize = $scope.BannerData.SelectedBannerSize;

        if ($scope.BannerData.BlogUniqueID == 'home_page_carousel' || $scope.BannerData.BlogUniqueID == 'race_event_carousel') {
            $scope.BannerData.SelectedBannerSize = '1600x400';
        } else if ($scope.BannerData.BlogUniqueID == 'home_page_sidebar1') {
            $scope.BannerData.SelectedBannerSize = '300x600';
        } else {
            $scope.BannerData.SelectedBannerSize = '300x250';
        }
        // if size gets change then reset selected ad image
        if ($scope.OldSelectedBannerSize != $scope.BannerData.SelectedBannerSize) {
            $scope.BannerData.SelectedBlogImage = '';
            $scope.BannerData.BlogImage = '';
            $scope.myImageBanner = '';
            $scope.myCroppedImageBanner = '';
        }
    };

    $scope.getBannerImageList = function () {
        var requestData = {
            AdminLoginSessionKey: $scope.AdminLoginSessionKey,
            Advertiser: $scope.BannerData.Advertiser,
            BannerModule: $scope.BannerData.BlogUniqueID
        };
        getArticleData.getBannerImageList(requestData).then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.BannerImageList = response.Data;
                //$scope.BannerData.SelectedBlogImage = '';
            } else if (response.ResponseCode == 519) {
                PermissionError(response.Message);
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    }

    $scope.getBannerDetails = function (BlogID) {
        var requestData = {BlogID: BlogID, AdminLoginSessionKey: $scope.AdminLoginSessionKey};
        getArticleData.getBannerDetails(requestData).then(function (response) {

            var BannerData = response.Data.results;
            //console.log(response.Data.results);
            if (BannerData != '' && BannerData != 'false') {
                $scope.BannerData = {
                    'BlogID': BannerData.BlogID,
                    'BlogUniqueID': BannerData.BlogUniqueID, // banner module
                    'BlogTitle': BannerData.BlogTitle,
                    'Advertiser': BannerData.Advertiser,
                    'BannerSource': BannerData.BannerSource,
                    'BannerSize': BannerData.BannerSize,
                    'BlogDescription': BannerData.BlogDescription,
                    'URL': BannerData.URL,
                    'Duration': BannerData.Duration,
                    'StartDate': BannerData.StartDate,
                    'EndDate': BannerData.EndDate,
                    'AdvertiserContact': BannerData.AdvertiserContact,
                    'NoOfHits': BannerData.NoOfHits,
                    'BlogImage': BannerData.BlogImage,
                    'SelectedBlogImage': BannerData.BlogImage,
                    'SelectedBannerSize': BannerData.BannerSize,
                    'SourceScript': BannerData.SourceScript,
                    'Locations': BannerData.Locations,
                };
                $scope.getBannerImageList();
                $scope.setSelectedBannerSize();
            }
        });
    };

    $scope.save_banner = function () {
        if ($scope.busy == true) {
            return false;
        }

        //send message
        $scope.isValidate = true;

        showLoader();

        var requestData = $scope.BannerData;
        requestData.AdminLoginSessionKey = $scope.AdminLoginSessionKey;

        requestData.rawImageBanner = $('#CroppedImgData').attr('ng-src'); //$scope.myCroppedImageBanner;

        if ((requestData.rawImageBanner == '' || requestData.rawImageBanner == undefined)) {
            ShowErrorMsg('Please select banner image');
            hideLoader();
            $("html, body").animate({scrollTop: 0}, "slow");
            return false;
        }

        $scope.busy = true;
        getArticleData.save_banner(requestData).then(function (response) {
            if (response.ResponseCode == 200) {
                ShowSuccessMsg(response.Message);
                setTimeout(function () {
                    window.location.href = base_url + 'admin/banner';
                }, 500);
            } else if (response.ResponseCode == 598) {
                //Show error message
                $scope.busy = false;
                PermissionError(response.Message);
            } else if (response.ResponseCode == 517) {
                $scope.busy = false;
                redirectToBlockedIP();
            } else {
                $scope.busy = false;
                ShowErrorMsg(response.Message);
            }
            $("html, body").animate({scrollTop: 0}, "slow");
            hideLoader();
        });
    };

    $scope.SaveBanner = function () {
        if ($scope.busy == true) {
            return false;
        }

        //send message
        $scope.isValidate = true;
        var CheckValidate = checkstatus('formBanner');

        var Location = ';';
        angular.forEach($scope.BannerData.Locations, function (val, index) {
            //if (val.address == '') {
            //    $scope.ErrorLocation[index] = 'Required';
            //    CheckValidate = false;
            //} else {
            //    $scope.ErrorLocation[index] = '';
            //    Location = Location + val.address + ';';
            //}
            if (val.address != '') {
                Location = Location + val.address + ';';
            }
        });
        if (Location == ';') {
            Location = '';
        }

        if (CheckValidate == false) {
            return false;
        }
        //return false;
        //console.log('BannerData: ',JSON.stringify($scope.BannerData));
        showLoader();

        $scope.BannerData.Location = Location;
        var requestData = $scope.BannerData;
        requestData.AdminLoginSessionKey = $scope.AdminLoginSessionKey;

        if ($scope.BannerData.BannerSource == '1')
        {
            requestData.BlogImage = $scope.BannerData.BlogImage;
            requestData.rawImageBanner = $('#CroppedImgData').attr('ng-src'); //$scope.myCroppedImageBanner;

            // if editing banner
            if ((requestData.rawImageBanner == '' || requestData.rawImageBanner == undefined) &&
                    (requestData.BlogImage == '' || requestData.BlogImage == undefined) &&
                    ($scope.BannerData.SourceScript == '' || $scope.BannerData.SourceScript == undefined))
            {
                ShowErrorMsg('Please select ad image or enter source script');
                hideLoader();
                $("html, body").animate({scrollTop: 0}, "slow");
                return false;
            }
        } else if ($scope.BannerData.BannerSource == '2')
        {
            if (($scope.BannerData.SelectedBlogImage == '' || $scope.BannerData.SelectedBlogImage == undefined) &&
                    ($scope.BannerData.SourceScript == '' || $scope.BannerData.SourceScript == undefined))
            {
                ShowErrorMsg('Please select ad image or enter source script');
                hideLoader();
                $("html, body").animate({scrollTop: 0}, "slow");
                return false;
            }
            requestData.BlogImage = $scope.BannerData.SelectedBlogImage;
            requestData.BannerSize = $scope.BannerData.SelectedBannerSize;
        }
        //console.log(requestData.BannerSize);

        /*if (requestData.BlogID == undefined || requestData.BlogID == '') {
         // if creating banner
         if (requestData.rawImageBanner == '' || requestData.rawImageBanner == undefined) {
         ShowErrorMsg('Please select ad image');
         hideLoader();
         $("html, body").animate({scrollTop: 0}, "slow");
         return false;
         }
         } else {
         // if editing banner
         if ((requestData.rawImageBanner == '' || requestData.rawImageBanner == undefined) && (requestData.BlogImage == '' || requestData.BlogImage == undefined)) {
         ShowErrorMsg('Please select ad image');
         hideLoader();
         $("html, body").animate({scrollTop: 0}, "slow");
         return false;
         }
         }*/

        $scope.busy = true;

        getArticleData.SaveBanner(requestData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                ShowSuccessMsg(response.Message);
                setTimeout(function () {
                    window.location.href = base_url + 'admin/advertise/banner';
                }, 500);
            } else if (response.ResponseCode == 598) {
                //Show error message
                $scope.busy = false;
                PermissionError(response.Message);
            } else if (response.ResponseCode == 517) {
                $scope.busy = false;
                redirectToBlockedIP();
            } else {
                $scope.busy = false;
                ShowErrorMsg(response.Message);
            }
            $("html, body").animate({scrollTop: 0}, "slow");
            hideLoader();
        });
    };
    $scope.add_location = function () {
        $scope.BannerData.Locations.push({'address': ''});
    };
    $scope.remove_location = function (index) {
        $scope.BannerData.Locations.splice(index, 1);
    };
    $scope.getDefaultBannerDetails = function () {

        var requestData = {AdminLoginSessionKey: $scope.AdminLoginSessionKey};

        getArticleData.getDefaultBannerDetails(requestData).then(function (response) {


            var DefaultBannerData = response.Data.ResultSmall;
            //console.log(response.Data.results);
            if (DefaultBannerData != '' && DefaultBannerData != 'false') {
                $scope.DefaultBannerData = {
                    'BlogID': DefaultBannerData.BlogID,
                    'BannerSize': DefaultBannerData.BannerSize,
                    'BlogDescription': DefaultBannerData.BlogDescription,
                    'URL': DefaultBannerData.URL,
                    'NoOfHits': DefaultBannerData.NoOfHits,
                    'BlogImage': DefaultBannerData.BlogImage,
                    'SourceScript': DefaultBannerData.SourceScript
                };
            }
            var DefaultBannerData1 = response.Data.ResultLarge;
            //console.log(response.Data.results);
            if (DefaultBannerData1 != '' && DefaultBannerData1 != 'false') {
                $scope.DefaultBannerData1 = {
                    'BlogID': DefaultBannerData1.BlogID,
                    'BannerSize': DefaultBannerData1.BannerSize,
                    'BlogDescription': DefaultBannerData1.BlogDescription,
                    'URL': DefaultBannerData1.URL,
                    'NoOfHits': DefaultBannerData1.NoOfHits,
                    'BlogImage': DefaultBannerData1.BlogImage,
                    'SourceScript': DefaultBannerData1.SourceScript
                };
            }

            var DefaultBannerData2 = response.Data.ResultHomeSidebar;
            //console.log(response.Data.results);
            if (DefaultBannerData2 != '' && DefaultBannerData2 != 'false') {
                $scope.DefaultBannerData2 = {
                    'BlogID': DefaultBannerData2.BlogID,
                    'BannerSize': DefaultBannerData2.BannerSize,
                    'BlogDescription': DefaultBannerData2.BlogDescription,
                    'URL': DefaultBannerData2.URL,
                    'NoOfHits': DefaultBannerData2.NoOfHits,
                    'BlogImage': DefaultBannerData2.BlogImage,
                    'SourceScript': DefaultBannerData2.SourceScript
                };
            }
        });
    }

    $scope.SaveDefaultBanner = function () {

        if ($scope.busy == true) {
            return false;
        }
        //send message
        $scope.isValidate = true;
        var CheckValidate = checkstatus('formBanner');
        if (CheckValidate == false) {
            return false;
        }

        //console.log('BannerData: ',JSON.stringify($scope.BannerData));

        // Sidebar Default Ad
        var reqData = $scope.DefaultBannerData;

        reqData.BlogImage = $scope.DefaultBannerData.BlogImage;
        reqData.rawImageBanner = $('#CroppedImgData').attr('ng-src'); //$scope.myCroppedImageBanner;

        // if editing banner
        if ((reqData.rawImageBanner == '' || reqData.rawImageBanner == undefined) &&
                (reqData.BlogImage == '' || reqData.BlogImage == undefined) &&
                (reqData.SourceScript == '' || reqData.SourceScript == undefined))
        {
            ShowErrorMsg('Please select ad image or enter source script');
            hideLoader();
            $("html, body").animate({scrollTop: 0}, "slow");
            return false;
        }

        // Home Page Default Ad
        /* var reqData1 = $scope.DefaultBannerData1;                
         
         reqData1.BlogImage = $scope.DefaultBannerData1.BlogImage;
         reqData1.rawImageBanner = $('#CroppedImgData1').attr('ng-src'); //$scope.myCroppedImageBanner;
         
         // if editing banner
         if ((reqData1.rawImageBanner == '' || reqData1.rawImageBanner == undefined) && 
         (reqData1.BlogImage == '' || reqData1.BlogImage == undefined) && 
         (reqData1.SourceScript == '' || reqData1.SourceScript == undefined)) 
         {
         ShowErrorMsg('Please select ad image or enter source script');
         hideLoader();
         $("html, body").animate({scrollTop: 0}, "slow");
         return false;
         }*/

        // Home Page Default Ad
        var reqData2 = $scope.DefaultBannerData2;

        reqData2.BlogImage = $scope.DefaultBannerData2.BlogImage;
        reqData2.rawImageBanner = $('#CroppedImgData2').attr('ng-src'); //$scope.myCroppedImageBanner;

        // if editing banner
        if ((reqData2.rawImageBanner == '' || reqData2.rawImageBanner == undefined) &&
                (reqData2.BlogImage == '' || reqData2.BlogImage == undefined) &&
                (reqData2.SourceScript == '' || reqData2.SourceScript == undefined))
        {
            ShowErrorMsg('Please select ad image or enter source script1');
            hideLoader();
            $("html, body").animate({scrollTop: 0}, "slow");
            return false;
        }

        var requestData = {};
        requestData.AdminLoginSessionKey = $scope.AdminLoginSessionKey;
        requestData.reqData = reqData;
        // requestData.reqData1 = reqData1;
        requestData.reqData2 = reqData2;

        //console.log(requestData);
        showLoader();

        $scope.busy = true;

        getArticleData.SaveDefaultBanner(requestData).then(function (response) {
            if (response.ResponseCode == 200)
            {
                ShowSuccessMsg(response.Message);
                setTimeout(function () {
                    window.location.href = base_url + 'admin/advertise/banner';
                }, 500);
            } else if (response.ResponseCode == 598) {
                //Show error message
                $scope.busy = false;
                PermissionError(response.Message);
            } else if (response.ResponseCode == 517) {
                $scope.busy = false;
                redirectToBlockedIP();
            } else {
                $scope.busy = false;
                ShowErrorMsg(response.Message);
            }
            $("html, body").animate({scrollTop: 0}, "slow");
            hideLoader();
        });
    };

    // function to reset search box
    $scope.reset_text_search = function () {
        $scope.searchKey = '';
        $scope.bannerList();
    };

    $scope.FilterByText = function () {
        if ($scope.searchKey != '') {
            $scope.bannerList();
        }
    };

    $scope.FilterBanner = function () {

        $scope.bannerList();

    };

    $scope.bannerList = function () {
        intilizeTooltip();
        showLoader();
        $scope.selectedUsers = {};
        $scope.globalChecked = false;

        /*$scope.searchKey = '';
         if ($('#bannerSearchField').val()) {
         $scope.searchKey = $('#bannerSearchField').val();
         $('#searchButton').addClass('selected');
         }*/

        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage);
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            //StartDate: $scope.startDate,
            //EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey,
            SearchBannerStatus: $scope.searchBannerStatus,
            SearchBannerModule: $scope.searchBannerModule,
        }
        //var reqUrl = reqData[1]
        //Call getUserlist in services.js file

        //return false;
        getArticleData.bannerList(reqData).then(function (response) {
            $scope.listData = [];
            //If no. of records greater then 0 then show
            $('.simple-pagination').show();

            $('#ArticleListCtrl table>tbody #noresult_td').remove();

            if (response.ResponseCode == 200) {
                $scope.noOfObj = response.Data.total_records;
                $scope.totalRecord = $scope.noOfObj;

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#ArticleListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="8"><span style="color:rgb(255, 0, 0);">No records found.</span></td></tr>');
                    $('.simple-pagination').hide();
                }

                //Push data into Controller in view file
                $.each(response.Data.results, function () {
                    this.created_by = $sce.trustAsHtml(this.created_by);
                    //alert(this.created_by);
                });
                $scope.listData = response.Data.results;

            } else if (response.ResponseCode == 598) {
                PermissionError(response.Message);
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };
    //Apply Sort by and mamke request data
    $scope.sortBannerBY = function (column_id) {
        if ($("table.users-table #noresult_td").length == 0)
        {
            $(".shortdiv").children('.icon-arrowshort').addClass('hide');
            $(".shortdiv").parents('.ui-sort').removeClass('selected');
            if ($scope.reverseSort == true) {
                $("#" + column_id).addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
            } else {
                $("#" + column_id).addClass('selected').children('.shortdiv').removeClass('sortedUp').addClass('sortedDown').children('.icon-arrowshort').removeClass('hide');
            }
            $scope.bannerList();
        }
    };

    $scope.SetStatus = function (status) {

        if (status == 2) {
            $scope.confirmationMessage = Sure_Active + ' ?';
        } else if (status == 4) {
            $scope.confirmationMessage = Sure_Inactive + ' ?';
        } else if (status == 3) {
            $scope.confirmationMessage = Sure_Delete + ' ?';
        } else {
            return false;
        }
        $scope.currentIsActive = status;
        openPopDiv('confirmeCommissionPopup', 'bounceInDown');
    };

    $scope.updateBannerStatus = function () {

        var reqData = {
            Status: $scope.currentIsActive,
            BlogID: $scope.CurretBannerData.BlogID,
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        };

        closePopDiv('confirmeCommissionPopup', 'bounceOutUp');
        showLoader();

        getArticleData.ChangeBannerStatus(reqData).then(function (response) {
            if (response.ResponseCode == 200) {
                //Show Success message
                ShowSuccessMsg(response.Message);
                $scope.bannerList();

            } else {
                PermissionError(response.Message);
            }

            $("html, body").animate({scrollTop: 0}, "slow");

            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };

    $scope.imageAllowType = ['image/png', 'image/jpeg', 'image/JPEG', 'image/PNG', 'image/jpg', 'image/JPG'];

    var handleFileSelectBanner = function (evt) {
        var file = evt.currentTarget.files[0];

        if (file.type == '')
        {
            $('#ErrorValideImage').show();
            return false;
        } else {
            if ($.inArray(file.type, $scope.imageAllowType) == -1) {
                $('#ErrorValideImage').show();
                return false;
            } else {
                $('#ErrorValideImage').hide();
            }
        }

        var reader = new FileReader();
        reader.onload = function (evt) {
            $scope.$apply(function ($scope) {
                $scope.myImageBanner = evt.target.result;
                $scope.upload_new_image = false;
            });
        };
        reader.readAsDataURL(file);
    };

    /*
     * This is used in default ad image (1600X400)
     * @param {type} evt
     * @returns {Boolean}
     */
    var handleFileSelectBanner1 = function (evt) {
        var file = evt.currentTarget.files[0];

        if (file.type == '')
        {
            $('#ErrorValideImage1').show();
            return false;
        } else {
            if ($.inArray(file.type, $scope.imageAllowType) == -1) {
                $('#ErrorValideImage1').show();
                return false;
            } else {
                $('#ErrorValideImage1').hide();
            }
        }

        var reader = new FileReader();
        reader.onload = function (evt) {
            $scope.$apply(function ($scope) {
                $scope.myImageBanner1 = evt.target.result;
            });
        };
        reader.readAsDataURL(file);
    };

    /*
     * This is used in home sidebar default ad image (267X335)
     * @param {type} evt
     * @returns {Boolean}
     */
    var handleFileSelectBanner2 = function (evt) {
        var file = evt.currentTarget.files[0];

        if (file.type == '')
        {
            $('#ErrorValideImage2').show();
            return false;
        } else {
            if ($.inArray(file.type, $scope.imageAllowType) == -1) {
                $('#ErrorValideImage2').show();
                return false;
            } else {
                $('#ErrorValideImage2').hide();
            }
        }

        var reader = new FileReader();
        reader.onload = function (evt) {
            $scope.$apply(function ($scope) {
                $scope.myImageBanner2 = evt.target.result;
            });
        };
        reader.readAsDataURL(file);
    };

    $scope.initializeCropper = function () {
        $timeout(function () {
            angular.element(document.querySelector('#fileInputBanner')).on('change', handleFileSelectBanner);
            angular.element(document.querySelector('#fileInputBanner1')).on('change', handleFileSelectBanner1); // used in default ad image (1600X400)
            angular.element(document.querySelector('#fileInputBanner2')).on('change', handleFileSelectBanner2); // used in default ad image (267X335)
        }, 100);
    }
    $scope.myImageBanner = '';
    $scope.myCroppedImageBanner = '';
    $scope.myImageBanner1 = '';     // used in default ad image (1600X400)
    $scope.myCroppedImageBanner1 = ''; //used in default ad image (1600X400)
    $scope.myImageBanner2 = '';     // used in home sidebar default ad image (267X335)
    $scope.myCroppedImageBanner2 = ''; //used in home sidebar default ad image (267X335)


    $scope.toTrustedHTML = function (html) {
        return $sce.trustAsHtml(html);
    }
});
$(document).ready(function () {
    var JqueryDateFormat1 = js_date
    var startweek = week_start_on;

    $("#BannerStartDate").datepicker({
        dateFormat: JqueryDateFormat1,
        changeMonth: true,
        changeYear: true,
        //maxDate: '0',
        firstDay: startweek,
        yearRange: '2016:+2',
        onSelect: function (selected) {
            $("#BannerEndDate").datepicker("option", "minDate", new Date(selected));
            //$("#BannerEndDate").trigger('c');
            $("#BannerStartDate").scope().BannerData.StartDate = selected;
            $("#BannerStartDate").scope().BannerData.EndDate = selected;

            // Hide client side validation error msg 
            if ($('#spnError' + $(this).attr('id')).html() != '') {
                $('#spnError' + $(this).attr('id')).html('');

                var mszLoca = $(this).attr('data-msglocation')
                $('#' + mszLoca).html('');
                $(this).parents('[data-error]').removeClass('hasError')
            }
        }
    });

    //Initilize Datepicker on BannerEndDate Field
    $("#BannerEndDate").datepicker({
        dateFormat: JqueryDateFormat1,
        changeMonth: true,
        changeYear: true,
        firstDay: startweek,
        yearRange: '2016:+2',
        onSelect: function (selected) {
            //$("#BannerStartDate").datepicker("option", "maxDate", new Date(selected));

            $("#BannerEndDate").scope().BannerData.EndDate = selected;

            // Hide client side validation error msg 
            if ($('#spnError' + $(this).attr('id')).html() != '') {
                $('#spnError' + $(this).attr('id')).html('');

                var mszLoca = $(this).attr('data-msglocation')
                $('#' + mszLoca).html('');
                $(this).parents('[data-error]').removeClass('hasError')
            }
        }
    });
})