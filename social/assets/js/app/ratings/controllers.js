app.directive('starRating', function () {

    return {
        restrict: 'EA',
        replace: true,
        template: '<ul class="star-all-list pull-left">' +
                '<li class="starRating-{{$index+1}} star-icon" ng-repeat="star in stars" ng-class="{filled: star.filled}" ng-click="toggle($index)">&nbsp;</li>' +
                '</ul>',
        scope: {
            ratingValue: '=ngModel',
            max: '=?', // optional (default is 5)
            onRatingSelect: '&?',
            readonly: '=?'
        },
        link: function (scope, element, attributes) {
            scope.stars = [];
            for (var i = 0; i < scope.max; i++) {
                scope.stars.push({
                    filled: 0
                });
            }
            if (scope.max == undefined) {
                scope.max = 5;
            }

            function updateStars() {
                scope.stars = [];
                for (var i = 0; i < scope.max; i++) {
                    scope.stars.push({
                        filled: i < scope.ratingValue
                    });
                }
            }
            ;
            scope.toggle = function (index) {
                if (scope.readonly === undefined || scope.readonly === false) {
                    scope.ratingValue = index + 1;
//                    scope.onRatingSelect({
//                        rating: index + 1
//                    });
                }
            };

            function starHover() {
                setTimeout(function () {
                    $('.star-all-list li').hover(function () {
                        $(this).addClass('hover-filled').prevAll('li').addClass('hover-filled');
                    }, function () {
                        $(this).removeClass('hover-filled').prevAll('li').removeClass('hover-filled');
                    });
                }, 1000)
            }

            scope.$watch('ratingValue', function (oldValue, newValue) {
                if (oldValue) {
                    updateStars();
                    starHover();
                }
            });
        }
    };

});

app.controller('ratingController', ['GlobalService', '$scope', '$sce', '$q', '$window', 'appInfo', 'WallService', 'lazyLoadCS', 'UtilSrvc',
    function (GlobalService, $scope, $sce, $q, $window, appInfo, WallService, lazyLoadCS, UtilSrvc)
    {
        $scope.noContentToPost = true;
        $scope.services = 4;
        $scope.deadline = 2;
        $scope.design = 3;
        $scope.communication = 1;
        $scope.isReadonly = true;
        $scope.ImageServerPath = image_server_path;

        $scope.parameter = [];
        $scope.entityListRating = [];
        $scope.RateValue = 0;
        $scope.RateClassName = 'badgerate-1';

        $scope.ModuleID = $('#module_id').val();
        $scope.ModuleEntityGUID = $('#module_entity_guid').val();
        $scope.d = {Title: '', Description: ''};
        $scope.fresh_params = [];
        $scope.getParameters = function () {
            if ($scope.fresh_params.length > 0)
            {
                $scope.parameter = $scope.fresh_params;
            } else
            {
                var reqData = {CategoryID: $scope.MainCategoryID};
                WallService.CallPostApi(appInfo.serviceUrl + 'rating/parameter', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $(response.Data).each(function (k, v) {
                            var a = {};
                            a['RateValue'] = 0;
                            a['RatingParameterID'] = v.RatingParameterID;
                            a['ParameterName'] = v.ParameterName;
                            $scope.fresh_params.push(a);
                            $scope.parameter = $scope.fresh_params;
                        });
                    }
                },
                        function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
            }
        }

        $scope.postCommentEditor = function (ActivityGUID, feedIndex, medianotblank) {
            setTimeout(function () {
                $('.emoji-scroll').mCustomScrollbar();
            }, 1000);
            $scope.show_comment_box = ActivityGUID;
        }

        $scope.cancelPostComment = function (data) {
            if (data) {
                data.showeditor = false;
            }
            $scope.show_comment_box = '';
        }


        $scope.CanWriteReview = 1;
        $scope.entityList = [];
        $scope.PostAs = {};
        $scope.getEntityList = function () {
            var reqData = {ModuleID: $scope.ModuleID, ModuleEntityGUID: $scope.ModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'rating/entitylist', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.entityList = response.Data;
                    var entLen = $scope.entityList.length;
                    if (entLen > 0) {
                        $scope.PostAsModuleProfilePicture = $scope.entityList[entLen - 1].ProfilePicture;
                        $scope.PostAsModuleID = $scope.entityList[entLen - 1].ModuleID;
                        $scope.PostAsModuleEntityGUID = $scope.entityList[entLen - 1].ModuleEntityGUID;
                        $scope.PostAsModuleName = $scope.entityList[entLen - 1].Name;
                        $scope.CanWriteReview = 1;
                    } else {
                        $scope.CanWriteReview = 0;
                    }
                    $scope.getResetParameters();
                } else {
                    $scope.CanWriteReview = 0;
                }
            },
                    function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
        }

//        $scope.Album = [];
//        $scope.Album.push({Media: [], AlbumName: '', AlbumGUID: '', AlbumType: '', ConversionStatus: ''});
        $scope.Album = [];
        $scope.Album.push({Media: {}, AlbumName: '', AlbumGUID: '', AlbumType: '', ConversionStatus: ''});
        $scope.Editing = 0;
        $scope.EditRatingGUID = '';
        $scope.editRating = function (RatingGUID) {
            $scope.EditRatingGUID = RatingGUID;
            $($scope.ratingList).each(function (k, v) {
                if (v.RatingGUID == RatingGUID) {
                    $scope.Editing = 1;
                    $scope.CanWriteReview = 1;
                    $scope.d.Title = v.Review.Title;
                    $scope.d.Description = v.Review.Description;
                    $scope.RateValue = v.RateValue;
                    $scope.parameter = v.RatingParameterValue;
                    if (v.Album && v.Album[0] && (v.Album[0].Media.length > 0)) {
                        var medias = v.Album[0].Media;
                        $scope.Album = v.Album;
                        $scope.Album[0]['Media'] = {};
                        angular.forEach(medias, function (media, index) {
                            $scope.Album[0].Media['media-' + index] = media;
                        });
                        ratingMediaCurrentIndex = $scope.UploadedMediaVal = Object.keys($scope.Album[0].Media).length;
                    } else {
                        $scope.Album = [{Media: {}, AlbumName: '', AlbumGUID: '', AlbumType: '', ConversionStatus: ''}];
                    }
                    $("html, body").animate({scrollTop: 280}, "slow");
                    setTimeout(function () {
                        $('#writeReview').hide();
                        $('.eidt-write-review').show();
                        checkAttachedRatingMedia();
                    }, 500);
                }
            });
        }

        $scope.hideSearch = function () {
            setTimeout(function () {
                $('.chosen-search').hide();
            }, 1000);
        }

        if (!$scope.IsNewsFeed) {
            $scope.getEntityList();
        }

        $scope.entityProfileChange = function (obj) {
            $scope.PostAsModuleProfilePicture = obj.ProfilePicture;
            $scope.PostAsModuleID = obj.ModuleID;
            $scope.PostAsModuleEntityGUID = obj.ModuleEntityGUID;
            $scope.PostAsModuleName = obj.Name;
        }

        $scope.getResetParameters = function () {
            $scope.parameter = [];
            $scope.getParameters();
        }

        $scope.rateFunction = function () {
            var paramlen = $scope.parameter.length;
            var RateValue = 0;
            $($scope.parameter).each(function (k, v) {
                RateValue += parseFloat(v.RateValue);
            });
            $scope.RateValue = RateValue / paramlen;
        }

        $scope.addRating = function () {

            var errMsg = false;
            if ($scope.d.Title == '') {
                errMsg = true;
            }
            if ($scope.d.Description == '') {
                errMsg = true;
            }
            $($scope.parameter).each(function (k, v) {
                if (v.RateValue < 1) {
                    errMsg = true;
                }
            });

            if (errMsg) {
                showResponseMessage('All fields are required.', 'alert-danger');
                return false;
            }

            var Media = [];
            if ($('input[name="PhotoMediaGUID[]"]').length > 0) {
                $('input[name="PhotoMediaGUID[]"]').each(function (k, v) {
                    Media.push({"MediaGUID": $('input[name="PhotoMediaGUID[]"]:eq(' + k + ')').val(), "Caption": ""});
                });
            }
            if ($('input[name="VideoMediaGUID[]"]').length > 0) {
                $('input[name="VideoMediaGUID[]"]').each(function (k, v) {
                    Media.push({"MediaGUID": $('input[name="VideoMediaGUID[]"]:eq(' + k + ')').val(), "Caption": ""});
                });
            }

            var ratingMediaPromises = [];

            if ($scope.Album && $scope.Album[0] && $scope.Album[0].Media && ($scope.UploadedMediaVal > 0)) {
                angular.forEach($scope.Album[0].Media, function (ratingMedia, key) {
                    ratingMediaPromises.push(createRatingMediaArray(ratingMedia).then(function (mediaData) {
                        Media.push({
                            MediaGUID: mediaData.MediaGUID,
                            Caption: ''
                        });
                    }));
                });
            }

            $q.all(ratingMediaPromises).then(function (data) {
                var PostAsModuleID = $scope.PostAsModuleID;
                var PostAsModuleEntityGUID = $scope.PostAsModuleEntityGUID;
                if (PostAsModuleID == 3) {
                    PostAsModuleID = '';
                    PostAsModuleEntityGUID = '';
                }
                if ($scope.Editing == 1) {
                    var reqData = {RatingGUID: $scope.EditRatingGUID, RateValue: $scope.RateValue, RatingParameterValue: $scope.parameter, Title: $scope.d.Title, Description: $scope.d.Description, Media: Media};
                    var url = 'rating/edit';
                    var successMsg = 'Review Updated.';
                } else {
                    var reqData = {ModuleID: $scope.ModuleID, ModuleEntityGUID: $scope.ModuleEntityGUID, RateValue: $scope.RateValue, RatingParameterValue: $scope.parameter, Title: $scope.d.Title, Description: $scope.d.Description, PostAsModuleID: PostAsModuleID, PostAsModuleEntityGUID: PostAsModuleEntityGUID, Media: Media};
                    var url = 'rating/add';
                    var successMsg = 'Review Added.';
                }
                WallService.CallPostApi(appInfo.serviceUrl + url, reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.resetWriteReview();
                        $scope.getResetRatingList();
                        $scope.getEntityList();
                        $scope.getOverallRating();
                        $scope.getStarCount();
                        $scope.getParameterSummary();
                        setTimeout(function () {
                            var myCls = 'badgerate-1';
                            if ($scope.avgRateValue >= 0.26 && $scope.avgRateValue <= 0.5) {
                                myCls = 'badgerate-1';
                            } else if ($scope.avgRateValue > 0.5 && $scope.avgRateValue <= 1.25) {
                                myCls = 'badgerate-1';
                            } else if ($scope.avgRateValue >= 1.26 && $scope.avgRateValue <= 1.75) {
                                myCls = 'badgerate-1';
                            } else if ($scope.avgRateValue >= 1.76 && $scope.avgRateValue <= 2.25) {
                                myCls = 'badgerate-2';
                            } else if ($scope.avgRateValue >= 2.26 && $scope.avgRateValue <= 2.75) {
                                myCls = 'badgerate-2';
                            } else if ($scope.avgRateValue >= 2.76 && $scope.avgRateValue <= 3.25) {
                                myCls = 'badgerate-3';
                            } else if ($scope.avgRateValue >= 3.26 && $scope.avgRateValue <= 3.75) {
                                myCls = 'badgerate-3';
                            } else if ($scope.avgRateValue >= 3.76 && $scope.avgRateValue <= 4.25) {
                                myCls = 'badgerate-4';
                            } else if ($scope.avgRateValue >= 4.26 && $scope.avgRateValue <= 4.75) {
                                myCls = 'badgerate-4';
                            } else if ($scope.avgRateValue >= 4.76) {
                                myCls = 'badgerate-5';
                            }
                            $('.profile-rating .rating-class').remove();
                            $('.profile-rating-2').append('<span class="rating-class ' + myCls + '">RATED ' + $scope.avgRateValue + '</span>');
                        }, 1000);
                        showResponseMessage(successMsg, 'alert-success');
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                },
                        function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
            });
        }

        $scope.writeReview = function () {
            checkAttachedRatingMedia();
            $scope.Editing = 0;
            $scope.getEntityList();
        }

        $scope.deleteRating = function (RatingGUID) {
            showConfirmBox("Delete Rating", "Are you sure, you want to delete this rating?", function (e) {
                if (e) {
                    var reqData = {RatingGUID: RatingGUID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'rating/delete', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $($scope.ratingList).each(function (k, v) {
                                if (v.RatingGUID == RatingGUID) {
                                    $scope.ratingList.splice(k, 1);
                                }
                            });
                            $scope.getOverallRating();
                            $scope.getStarCount();
                            $scope.getParameterSummary();

                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
            });
        }

        $scope.resetWriteReview = function () {
            $('#writeReview').fadeIn();
            $('.eidt-write-review').hide();
            $('ul.attached-media-list li.fine-uploader-append').remove();
            $scope.d.Title = '';
            $scope.d.Description = '';
            $scope.PostAsModuleID = '';
            $scope.PostAsModuleName = '';
            $scope.PostAsModuleEntityGUID = '';
            $scope.PostAsModuleProfilePicture = '';
            $scope.Album = [{Media: {}, AlbumName: '', AlbumGUID: '', AlbumType: '', ConversionStatus: ''}];
            checkAttachedRatingMedia();
        }

        $scope.ageGroupList = {};
        $scope.getAgeGroupList = function () {
            var reqData = {};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/get_age_group_list', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    response.Data.unshift({'Name': 'Any', 'AgeGroupID': '0'});
                    $scope.ageGroupList = response.Data;
                    setTimeout(function () {
                        $('#AgeGroupFilter').trigger('chosen:updated');
                    }, 500);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        if ($scope.IsNewsFeed !== '1') {
            $scope.getAgeGroupList();
        }

        $scope.avgRateValue = 0;
        $scope.getTotalRateValue = function (TotalRateValue, TotalRecords) {
            var value = 0;
            if (typeof TotalRateValue !== 'undefined') {
                RateValue = TotalRateValue;
            } else {
                RateValue = (parseFloat($scope.overallRating.TotalRateValue) / parseFloat($scope.overallRating.TotalRecords));
            }
            if (typeof TotalRecords !== 'undefined') {
                if (TotalRecords > 1) {
                    RateValue = (parseFloat(TotalRateValue) / parseFloat(TotalRecords));
                }
            }

            if (RateValue >= 0.26 && RateValue <= 0.5) {
                value = 0.5;
            } else if (RateValue > 0.5 && RateValue <= 1.25) {
                value = 1;
            } else if (RateValue >= 1.26 && RateValue <= 1.75) {
                value = 1.5;
            } else if (RateValue >= 1.76 && RateValue <= 2.25) {
                value = 2;
            } else if (RateValue >= 2.26 && RateValue <= 2.75) {
                value = 2.5;
            } else if (RateValue >= 2.76 && RateValue <= 3.25) {
                value = 3;
            } else if (RateValue >= 3.26 && RateValue <= 3.75) {
                value = 3.5;
            } else if (RateValue >= 3.76 && RateValue <= 4.25) {
                value = 4;
            } else if (RateValue >= 4.26 && RateValue <= 4.75) {
                value = 4.5;
            } else if (RateValue >= 4.76) {
                value = 5;
            }
            ;
            if (typeof TotalRateValue !== 'undefined') {
                return value;
            } else {
                $scope.avgRateValue = value;
                if ($scope.avgRateValue >= 2 && $scope.avgRateValue < 3)
                {
                    $scope.RateClassName = 'badgerate-2';
                } else if ($scope.avgRateValue >= 3 && $scope.avgRateValue < 4)
                {
                    $scope.RateClassName = 'badgerate-3';
                } else if ($scope.avgRateValue >= 4 && $scope.avgRateValue < 5)
                {
                    $scope.RateClassName = 'badgerate-4';
                } else if ($scope.avgRateValue >= 5)
                {
                    $scope.RateClassName = 'badgerate-5';
                }

            }
        }

        $scope.FilterRateValue = 0;
        $scope.filter = {SortBy: '', AgeGroup: '', Gender: '', Location: {City: '', State: '', Country: ''}, AdminOnly: '0', Duration: '', StartDate: '', EndDate: ''};
        $scope.PageNo = 0;
        $scope.ratingList = [];
        $scope.Busy = 0;
        $scope.IsFilter = 0;
        $scope.getRatingList = function () {
            $scope.IsFilter = 0;
            PageNo = $('#PageNo').val();
            if ($scope.PageNo == PageNo) {
                setTimeout(function () {
                    $scope.Busy = 0;
                }, 500);
                return false;
            }
            $scope.PageNo = PageNo;
            var reqData = {ModuleID: $scope.ModuleID, ModuleEntityGUID: $scope.ModuleEntityGUID, PageNo: PageNo, RatingGUID: $('#RatingGUID').val()};
            if ($scope.filter.AgeGroup) {
                reqData['AgeGroup'] = $scope.filter.AgeGroup;
                $scope.IsSFilter = 1;
                $scope.IsFilter = 1;
            }
            if ($scope.filter.Gender) {
                reqData['Gender'] = $scope.filter.Gender;
                $scope.IsSFilter = 1;
                $scope.IsFilter = 1;
            }
            if ($scope.filter.Location.City) {
                reqData['Location'] = $scope.filter.Location;
                $scope.IsSFilter = 1;
                $scope.IsFilter = 1;
            }
            if ($scope.filter.AdminOnly) {
                reqData['AdminOnly'] = $scope.filter.AdminOnly;
            }
            if ($scope.filter.StartDate) {
                reqData['StartDate'] = $scope.filter.StartDate;
                $scope.IsSFilter = 1;
                $scope.IsFilter = 1;
            }
            if ($scope.filter.EndDate) {
                reqData['EndDate'] = $scope.filter.EndDate;
                $scope.IsSFilter = 1;
                $scope.IsFilter = 1;
            }
            if ($scope.filter.SortBy) {
                $scope.IsSFilter = 1;
                reqData['SortBy'] = $scope.filter.SortBy;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'rating/list', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    //$scope.ratingList = response.Data;
                    $(response.Data).each(function (key, val) {
                        var append = true;
                        $($scope.ratingList).each(function (k, v) {
                            if (val.RatingGUID == v.RatingGUID) {
                                append = false;
                            }
                        });
                        if (append) {
                            $scope.ratingList.push(val);
                        }
                    });
                    if (!$scope.TotalReview) {
                        $scope.TotalReview = response.TotalRecords;
                    }
                    var RVal = 0;
                    $($scope.ratingList).each(function (k, v) {
                        $scope.ratingList[k].TotalVoteCount = parseInt($scope.ratingList[k].PositiveVoteCount) + parseInt($scope.ratingList[k].NegativeVoteCount);
                        RVal += parseFloat(v.RateValue);
                    });
                    $scope.FilterRateValue = $scope.getTotalRateValue(RVal, response.TotalRecords);
                    $('#PageNo').val(parseInt(PageNo) + 1);
                }
                setTimeout(function () {
                    $('.comment-text').val('');
                }, 400);
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            //setTimeout(function(){ $scope.Busy = 0; },500);
            $scope.Busy = 0;
        }

        $scope.AgeGroupName = '';
        $scope.getResetRatingList = function () {
            $scope.Busy = 1;
            $scope.PageNo = 0;
            $('#PageNo').val(1);
            if ($scope.filter.AgeGroup) {
                $($scope.ageGroupList).each(function (k, v) {
                    if (v.AgeGroupID == $scope.filter.AgeGroup) {
                        if (v.AgeGroupID == 0) {
                            $scope.AgeGroupName = '';
                            $scope.clearSingleFilter('AgeGroup');
                        } else {
                            $scope.AgeGroupName = v.Name;
                        }
                    }
                });
            }
            $scope.ratingList = [];
            $scope.getRatingList();
        }

        $scope.clearSingleFilter = function (filter) {
            var currentFilter = {SortBy: '', AgeGroup: '', Gender: '', Location: {City: '', State: '', Country: ''}, AdminOnly: '0', Duration: '', StartDate: '', EndDate: ''};
            if (filter == 'AgeGroup') {
                $scope.filter.AgeGroup = '';
            } else if (filter == 'Location') {
                $('#location').val('');
                $scope.filter.Location = {City: '', State: '', Country: ''};
            } else if (filter == 'Duration') {
                $scope.filter.Duration = '';
                $scope.filter.StartDate = '';
                $scope.filter.EndDate = '';
            } else if (filter == 'Gender') {
                $scope.filter.Gender = '';
            } else if (filter == 'AdminOnly') {
                $scope.filter.AdminOnly = '0';
            }
            if (JSON.stringify($scope.filter) == JSON.stringify(currentFilter)) {
                $scope.IsSFilter = 0;
            }
            $scope.getResetRatingList();
        }
        $scope.IsSFilter = 0;
        $scope.clearFilters = function () {
            $('#location').val('');
            var currentFilter = {SortBy: '', AgeGroup: '', Gender: '', Location: {City: '', State: '', Country: ''}, AdminOnly: '0', Duration: '', StartDate: '', EndDate: ''};
            $scope.IsSFilter = 0;
            $scope.filter = {SortBy: '', AgeGroup: '', Gender: '', Location: {City: '', State: '', Country: ''}, AdminOnly: '0', Duration: '', StartDate: '', EndDate: ''};
            $scope.getResetRatingList();
        }

        $scope.getUserData = function () {
            $scope.LoggedInProfilePicture = profile_picture;
        }

        $scope.callLightGallery = function (id) {
            var gallery = $("#lg-" + id).lightGallery();
            if (!gallery.isActive()) {
                gallery.destroy();
            }

            $('#lg-' + id).lightGallery({
                showThumbByDefault: false,
                addClass: 'showThumbByDefault',
                hideControlOnEnd: true,
                preload: 2,
                onOpen: function () {
                    var nextthmb = $('.thumb.active').next('.thumb').html();
                    var prevthmb = $('.thumb.active').prev('.thumb').html();

                    $('#lg-prev').append(prevthmb);
                    $('#lg-next').append(nextthmb);
                    $('.cl-thumb').remove();

                },
                onSlideNext: function (plugin) {
                    var nextthmb = $('.thumb.active').next('.thumb').html();
                    var prevthmb = $('.thumb.active').prev('.thumb').html();

                    $('#lg-prev').html(prevthmb);
                    $('#lg-next').html(nextthmb);
                },
                onSlidePrev: function (plugin) {
                    var nextthmb = $('.thumb.active').next('.thumb').html();
                    var prevthmb = $('.thumb.active').prev('.thumb').html();

                    $('#lg-prev').html(prevthmb);
                    $('#lg-next').html(nextthmb);
                }
            });
        }

        $scope.getUserData();

        $scope.flagRating = function (RatingGUID) {
            flagValSet(RatingGUID, 'RATING', 18, 1);
        }

        $scope.CustomDate = '';
        $scope.changeDuration = function () {
            $scope.CustomDate = '';
            var date = new Date();
            if ($scope.filter.Duration == 'This Week') {
                var first = (date.getDate() - date.getDay()) + 1;
                var last = first + 6;

                $scope.filter.StartDate = new Date(date.setDate(first)).toISOString().slice(0, 10);
                $scope.filter.EndDate = new Date(date.setDate(last)).toISOString().slice(0, 10);
            } else if ($scope.filter.Duration == 'This Month') {
                $scope.filter.StartDate = new Date(date.getFullYear(), date.getMonth(), 2).toISOString().slice(0, 10);
                $scope.filter.EndDate = new Date(date.getFullYear(), date.getMonth() + 1, 1).toISOString().slice(0, 10);
            } else if ($scope.filter.Duration == 'Custom') {
                if ($('#startDatePicker').length > 0 && $('#endDatePicker').length > 0) {
                    $scope.filter.StartDate = '';
                    $scope.filter.EndDate = '';
                    $scope.sd = $('#startDatePicker').val();
                    $scope.ed = $('#endDatePicker').val();
                    if ($scope.sd !== '' && $scope.ed !== '') {
                        $scope.sdArr = $scope.sd.split('/');
                        $scope.edArr = $scope.ed.split('/');
                        $scope.CustomDate = $scope.sd + ' - ' + $scope.ed;
                        $scope.filter.StartDate = $scope.sdArr[2] + '-' + $scope.sdArr[0] + '-' + $scope.sdArr[1];
                        $scope.filter.EndDate = $scope.edArr[2] + '-' + $scope.edArr[0] + '-' + $scope.edArr[1];
                        $scope.getResetRatingList();
                    }
                }
                //Datepicker dates to Start & End date
            } else {
                $scope.filter.StartDate = '';
                $scope.filter.EndDate = '';
            }
            if ($scope.filter.Duration !== 'Custom') {
                $scope.getResetRatingList();
            }
        }

        $scope.setSortBy = function (val) {
            $scope.filter.SortBy = val;
            $scope.getResetRatingList();
        }

        $scope.getMutualFriends = function (obj) {
            var str = '';
            if (obj.TotalRecords == 1) {
                str = '<a href="' + base_url + obj.Friends[0].ProfileURL + '">' + obj.Friends[0].Name + '</a>';
            } else if (obj.TotalRecords == 2) {
                str = '<a href="' + base_url + obj.Friends[0].ProfileURL + '">' + obj.Friends[0].Name + '</a> and ' + '<a href="' + base_url + obj.Friends[1].ProfileURL + '">' + obj.Friends[1].Name + '</a>';
            } else {
                str = '<a href="' + base_url + obj.Friends[0].ProfileURL + '">' + obj.Friends[0].Name + '</a>, ' + '<a href="' + base_url + obj.Friends[1].ProfileURL + '">' + obj.Friends[1].Name + '</a> and ' + (obj.TotalRecords - 2) + ' others';
            }
            str += ' are mutual friends with this reviewer';
            str = $sce.trustAsHtml(str);
            return str;
        }

        $scope.getOverallRating = function () {
            var reqData = {ModuleID: $scope.ModuleID, ModuleEntityGUID: $scope.ModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'rating/overall', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.overallRating = response.Data;
                    $scope.getTotalRateValue();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.getStarCount = function () {
            var reqData = {ModuleID: $scope.ModuleID, ModuleEntityGUID: $scope.ModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'rating/star_count', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.starCount = response.Data;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.showHideDetails = function (RatingGUID) {
            var e = $('#r-' + RatingGUID + ' button[data-type="see-less-more"]');
            var dataVal = $(e).children('bdi').text(),
                    seeMore = 'See More',
                    seeLess = 'See Less';
            if (dataVal == seeMore) {
                $(e).addClass('active');
                $(e).children('bdi').text(seeLess);
                $(e).parent().prev('[data-type="more-content"]').fadeIn();

            } else if (dataVal == seeLess) {
                $(e).removeClass('active');
                $(e).children('bdi').text(seeMore);
                $(e).parent().prev('[data-type="more-content"]').fadeOut();

            }
        }

        $scope.vote = function (EntityGUID, Vote) {
            var reqData = {Vote: Vote, EntityGUID: EntityGUID, EntityType: 'RATING'};
            WallService.CallPostApi(appInfo.serviceUrl + 'rating/vote', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (IsNewsFeed == '1')
                    {
                        WallPostScope = angular.element(document.getElementById('WallPostCtrl')).scope();
                        console.log(WallPostScope.activityData);
                        $(WallPostScope.activityData).each(function (k, v) {
                            //console.log(v.RatingData);
                            if ("RatingData" in v)
                            {
                                if ("RatingGUID" in v.RatingData)
                                {
                                    if (v.RatingData.RatingGUID == EntityGUID) {
                                        WallPostScope.activityData[k].RatingData.IsVoted = 1;
                                        WallPostScope.activityData[k].RatingData.JustVoted = 1;
                                        if (Vote == 'YES') {
                                            WallPostScope.activityData[k].RatingData.PositiveVoteCount++;
                                        } else {
                                            WallPostScope.activityData[k].RatingData.NegativeVoteCount++;
                                        }
                                        WallPostScope.activityData[k].RatingData.TotalVoteCount = parseInt(WallPostScope.activityData[k].RatingData.PositiveVoteCount) + parseInt(WallPostScope.activityData[k].RatingData.NegativeVoteCount);
                                    }
                                }
                            }
                        });
                    } else
                    {
                        $($scope.ratingList).each(function (k, v) {
                            if (v.RatingGUID == EntityGUID) {
                                $scope.ratingList[k].IsVoted = 1;
                                $scope.ratingList[k].JustVoted = 1;
                                if (Vote == 'YES') {
                                    $scope.ratingList[k].PositiveVoteCount++;
                                } else {
                                    $scope.ratingList[k].NegativeVoteCount++;
                                }
                                $scope.ratingList[k].TotalVoteCount = parseInt($scope.ratingList[k].PositiveVoteCount) + parseInt($scope.ratingList[k].NegativeVoteCount);
                            }
                        });
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.getParameterSummary = function () {
            var reqData = {ModuleID: $scope.ModuleID, ModuleEntityGUID: $scope.ModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'rating/parameter_summary', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.parameterSummary = response.Data;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        /* Comments Function Start */

        // Download files
        $scope.hitToDownload = function (MediaGUID, mediaFolder) {
            mediaFolder = (mediaFolder && (mediaFolder != '')) ? mediaFolder : 'wall';
            $window.location.href = base_url + 'home/download/' + MediaGUID + '/' + mediaFolder;
        }

        $scope.validateFileSize = function (file) {
            var defer = $q.defer();
            var isResolvedToFalse = false;
            var mediaPatt = new RegExp("^image|video");
            var videoPatt = new RegExp("^video");
            if (videoPatt.test(file.type)) {
                if (file.size > 41943040) { // if video size > 41943040 Bytes = 40 Mb
                    file.$error = 'size';
                    file.$error = 'Size Error';
                    file.$errorMessages = file.name + ' is too large.';
                    defer.resolve(false);
                    isResolvedToFalse = true;
                }
            } else {
                if (file.size > 4194304) { // if image/document size > 4194304 Bytes = 4 Mb
                    file.$error = 'size';
                    file.$error = 'Size Error';
                    file.$errorMessages = file.name + ' is too large.';
                    defer.resolve(false);
                    isResolvedToFalse = true;
                }
            }

            if (!isResolvedToFalse) {
                defer.resolve(true);
            }
            return defer.promise;
        }

        $scope.isAttachementUploading = false;
        var mediaCurrentIndex = 0;
        var fileCurrentIndex = 0;
        $scope.uploadFiles = function (files, errFiles, id, ratingIndex) {
//            $scope.errFiles = errFiles;
            var promises = [];
            if (!(errFiles.length > 0)) {
                if (!$scope.ratingList[ratingIndex].medias) {
                    $scope.ratingList[ratingIndex]['medias'] = {};
                    $scope.ratingList[ratingIndex]['commentMediaCount'] = 0;
                }

                if (!$scope.ratingList[ratingIndex].files) {
                    $scope.ratingList[ratingIndex]['files'] = {};
                    $scope.ratingList[ratingIndex]['commentFileCount'] = 0;
                }

                var patt = new RegExp("^image|video");
                var videoPatt = new RegExp("^video");
                $scope.isAttachementUploading = true;
                angular.element('#cmt-' + id).focus();
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, fileIndex, mediaIndex) {
                        WallService.setFileMetaData(file);
                        var fileType = 'media';
                        var paramsToBeSent = {                            
                            Type: 'comments',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        if (patt.test(file.type)) {
                            $scope.ratingList[ratingIndex].medias['media-' + mediaIndex] = file;
                            $scope.ratingList[ratingIndex]['commentMediaCount'] = Object.keys($scope.ratingList[ratingIndex].medias).length;
                        } else {
                            $scope.ratingList[ratingIndex].files['file-' + fileIndex] = file;
                            $scope.ratingList[ratingIndex]['commentFileCount'] = Object.keys($scope.ratingList[ratingIndex].files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.ratingList[ratingIndex], fileIndex : fileIndex, mediaIndex : mediaIndex}, {}, response);
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode === 200) {
                                            $scope.ratingList[ratingIndex].medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.ratingList[ratingIndex].medias['media-' + mediaIndex].progress = true;
                                        } else {
                                            delete $scope.ratingList[ratingIndex].medias['media-' + mediaIndex];
                                            $scope.ratingList[ratingIndex]['commentMediaCount'] = Object.keys($scope.ratingList[ratingIndex].medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode === 200) {
                                            $scope.ratingList[ratingIndex].files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.ratingList[ratingIndex].files['file-' + fileIndex].progress = true;
//                                    console.log($scope.ratingList[ratingIndex].files);
                                        } else {
                                            delete $scope.ratingList[ratingIndex].files['file-' + fileIndex];
                                            $scope.ratingList[ratingIndex]['commentFileCount'] = Object.keys($scope.ratingList[ratingIndex].files).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }
                                    IsMediaExists = 1;
                                },
                                function (response) {
                                    if (fileType === 'media') {
                                        delete $scope.ratingList[ratingIndex].medias['media-' + mediaIndex];
                                        $scope.ratingList[ratingIndex]['commentMediaCount'] = Object.keys($scope.ratingList[ratingIndex].medias).length;
                                    } else {
                                        delete $scope.ratingList[ratingIndex].files['file-' + fileIndex];
                                        $scope.ratingList[ratingIndex]['commentFileCount'] = Object.keys($scope.ratingList[ratingIndex].files).length;
                                    }
                                },
                                function (evt) {
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.ratingList[ratingIndex], fileIndex : fileIndex, mediaIndex : mediaIndex}, evt);
                                });
                        if (fileType === 'media') {
                            mediaCurrentIndex++;
                        } else {
                            fileCurrentIndex++;
                        }
                        promises.push(promise);

                    })(fileToUpload, fileCurrentIndex, mediaCurrentIndex);
                });
                $q.all(promises).then(function (data) {
                    $scope.isAttachementUploading = false;
                });
            } else {
                var msg = '';
                angular.forEach(errFiles, function (errFile, key) {
                    msg += '\n' + errFile.$errorMessages;
                    promises.push(makeResolvedPromise(msg));
                });
                $q.all(promises).then(function (data) {
                    showResponseMessage(msg, 'alert-danger');
                });
            }
        };

        function makeResolvedPromise(data) {
            var deferred = $q.defer();
            deferred.resolve(data);
            return deferred.promise;
        }
        ;

        function createAttachementArray(attachement) {
            var deferred = $q.defer();
            deferred.resolve({
                MediaGUID: attachement.MediaGUID,
                MediaType: attachement.MediaType
            });
            return deferred.promise;
        }
        ;

        $scope.removeAttachement = function (type, index, ratingIndex) {
            if ((type === 'file') && ($scope.ratingList[ratingIndex].files && Object.keys($scope.ratingList[ratingIndex].files).length)) {
                delete $scope.ratingList[ratingIndex].files[index];
                $scope.ratingList[ratingIndex]['commentFileCount'] = Object.keys($scope.ratingList[ratingIndex].files).length;
            } else if ($scope.ratingList[ratingIndex].medias && Object.keys($scope.ratingList[ratingIndex].medias).length) {
                delete $scope.ratingList[ratingIndex].medias[index];
                $scope.ratingList[ratingIndex]['commentMediaCount'] = Object.keys($scope.ratingList[ratingIndex].medias).length;
            }
            if ((Object.keys($scope.ratingList[ratingIndex].files).length === 0) && (Object.keys($scope.ratingList[ratingIndex].medias).length === 0)) {
                IsMediaExists = 0;
            }
            angular.element('#' + $scope.ratingList[ratingIndex].RatingGUID).focus();
            angular.element('#' + $scope.ratingList[ratingIndex].RatingGUID).blur();
        };

        $scope.addMediaClasses = function (mediaCount) {
            var mediaClass;
            switch (mediaCount) {
                case 1:
                    mediaClass = "post-media single";
                    break;
                case 2:
                    mediaClass = "post-media two";
                    break;
                    ;
                default:
                    mediaClass = "row gutter-5 post-media morethan-two";
            }
            return mediaClass;
        };

        var IsMediaExists = 0;
        $scope.submitComment = function (event, RatingGUID, IsOwner, PostAsModuleID, ratingIndex, ActivityGUID) {
            $scope.appendComment = 1;
            var Comment = $('#cmt-div-' + ActivityGUID + ' .note-editable').html();
            Comment = Comment.trim();
            var PComments = $('#cmt-div-' + ActivityGUID + ' .note-editable').html();
            var Media = [];
//                        Media.push({'MediaGUID': MediaGUID, 'Caption': Caption});
            var attacheMentPromises = [];

//                    if($scope.ratingList[ratingIndex].medias && ($scope.ratingList[ratingIndex].medias.length > 0)){
//                      attacheMentArray = $scope.ratingList[ratingIndex].medias;
//                    }
//
//                    if($scope.ratingList[ratingIndex].files && ($scope.ratingList[ratingIndex].files.length > 0)){
//                      attacheMentArray = attacheMentArray.concat($scope.ratingList[ratingIndex].files);
//                    }
            if ($scope.ratingList[ratingIndex].medias && Object.keys($scope.ratingList[ratingIndex].medias).length > 0) {
                angular.forEach($scope.ratingList[ratingIndex].medias, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        Media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: ''
                        });
                    }));
                });
            }
            if ($scope.ratingList[ratingIndex].files && Object.keys($scope.ratingList[ratingIndex].files).length > 0) {
                angular.forEach($scope.ratingList[ratingIndex].files, function (attachement, key) {
                    attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                        Media.push({
                            MediaGUID: dataToAttache.MediaGUID,
                            MediaType: dataToAttache.MediaType,
                            Caption: ''
                        });
                    }));
                });
            }
            if ((Media.length == 0) && (Comment == '')) {
                //showResponseMessage('Please upload image or type any text','alert-danger');
                $('#cmt-' + RatingGUID).val('');
                return;
            }
            Comment = PComments;
            $scope.EntityOwner = 0;
            if (IsOwner == '1' && PostAsModuleID == '18') {
                $scope.EntityOwner = 1;
            }
            $('#cmt-' + RatingGUID).val('');
            var element = $('#r-' + RatingGUID + ' .post-as-data');
            var jsonData = {Comment: Comment, EntityType: 'Rating', EntityGUID: RatingGUID, Media: Media, EntityOwner: $scope.EntityOwner, PostAsModuleID: element.attr('data-module-id'), PostAsModuleEntityGUID: element.attr('data-module-entityid'), };
            console.log(jsonData);
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/addComment', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.ratingList[ratingIndex].medias = {};
                    $scope.ratingList[ratingIndex].files = {};
                    $scope.ratingList[ratingIndex]['commentMediaCount'] = 0;
                    $scope.ratingList[ratingIndex]['commentFileCount'] = 0;
                    mediaCurrentIndex = 0;
                    fileCurrentIndex = 0;
                    $scope.ratingList[ratingIndex].Comments.push(response.Data[0]);
                    $scope.ratingList[ratingIndex].NoOfComments = parseInt($scope.ratingList[ratingIndex].NoOfComments) + 1;
                    $('#upload-btn-' + RatingGUID).show();
                    $('#cm-' + RatingGUID).html('');
                    $('#cm-' + RatingGUID + ' li').remove();
                    $('#cm-' + RatingGUID).hide();
                    $('#r-' + RatingGUID + ' .icon-camera-post').show();
                    setTimeout(function () {
                        $('#cmt-' + RatingGUID).trigger('focus');
                    }, 200);
                    setTimeout(function () {
                        $('#r-' + RatingGUID + ' .mCustomScrollbar').mCustomScrollbar("scrollTo", 'bottom');
                    }, 500);
                    angular.element('#' + $scope.ratingList[ratingIndex].RatingGUID).focus();
                    angular.element('#' + $scope.ratingList[ratingIndex].RatingGUID).blur();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                $scope.show_comment_box = "";
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        // View All Comments
        $scope.viewAllComntEmit = function (i, ActivityGUID, filter) { //    Call above webservice here for View All Comments

            var id = $('[data-guid="act-' + ActivityGUID + '"]').attr('id');
            var element = $('#' + id + ' .post-as-data');
            var reqData = {
                EntityGUID: ActivityGUID,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
            };
            if (typeof filter !== 'undefined')
            {
                reqData['Filter'] = filter;
            }
            $("#cmt_loader_" + ActivityGUID).show();
            WallService.CallApi(reqData, 'activity/getAllComments').then(function (response) {
                if (response.ResponseCode === 200) {
                    var tempComntData = response.Data;
                    angular.forEach($scope.ratingList, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID)
                        {
                            $scope.ratingList[key].Comments = tempComntData;
                            $scope.ratingList[i].viewStats = 0;
                        }
                    });
                }
            });
        }

        $scope.uploadFiles = function (files, errFiles, id, feedIndex, IsAnnouncement) {
            var promises = [];
            if (!(errFiles.length > 0)) {
                angular.forEach($scope.ratingList, function (val, key) {
                    if (val.ActivityGUID == id)
                    {
                        feedIndex = key;
                        if (!$scope.ratingList[key].medias) {
                            $scope.ratingList[key]['medias'] = {};
                            $scope.ratingList[key]['commentMediaCount'] = 0;
                        }

                        if (!$scope.ratingList[key].files) {
                            $scope.ratingList[key]['files'] = {};
                            $scope.ratingList[key]['commentFileCount'] = 0;
                        }
                    }
                });

                var patt = new RegExp("^image|video");
                var videoPatt = new RegExp("^video");
                var promises = [];
                $scope.isAttachementUploading = true;
                angular.element('#cmt-' + id).focus();
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, fileIndex, mediaIndex) {
                        var fileType = 'media';
                        WallService.setFileMetaData(file);
                        var paramsToBeSent = {                            
                            Type: 'comments',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        if (patt.test(file.type)) {
                            $scope.ratingList[feedIndex].medias['media-' + mediaIndex] = file;
                            $scope.ratingList[feedIndex]['commentMediaCount'] = Object.keys($scope.ratingList[feedIndex].medias).length;
                        } else {
                            $scope.ratingList[feedIndex].files['file-' + fileIndex] = file;
                            $scope.ratingList[feedIndex]['commentFileCount'] = Object.keys($scope.ratingList[feedIndex].files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';
                        }
                        if (!$scope.$$phase)
                        {
                            $scope.$apply();
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.ratingList[feedIndex], fileIndex : fileIndex, mediaIndex : mediaIndex}, {}, response);
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.ratingList[feedIndex].medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.ratingList[feedIndex].medias['media-' + mediaIndex]['list'] = response.data.Data;
                                            $scope.ratingList[feedIndex].medias['media-' + mediaIndex].progress = true;
                                            hideButtonLoader('PostBtn-' + id);
                                        } else {
                                            delete $scope.ratingList[feedIndex].medias['media-' + mediaIndex];
                                            $scope.ratingList[feedIndex]['commentMediaCount'] = Object.keys($scope.ratingList[feedIndex].medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode == 200) {
                                            $scope.ratingList[feedIndex].files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.ratingList[feedIndex].files['file-' + fileIndex]['list'] = response.data.Data;
                                            $scope.ratingList[feedIndex].files['file-' + fileIndex].progress = true;
                                            hideButtonLoader('PostBtn-' + id);
                                        } else {
                                            delete $scope.ratingList[feedIndex].files['file-' + fileIndex];
                                            $scope.ratingList[feedIndex]['commentFileCount'] = Object.keys($scope.ratingList[feedIndex].files).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }
                                    if ((Object.keys($scope.ratingList[feedIndex].files).length === 0) && (Object.keys($scope.ratingList[feedIndex].medias).length === 0)) {
                                        IsMediaExists = 0;
                                    }
                                },
                                function (response) {
                                    if (fileType === 'media') {
                                        delete $scope.ratingList[feedIndex].medias['media-' + mediaIndex];
                                        $scope.ratingList[feedIndex]['commentMediaCount'] = Object.keys($scope.ratingList[feedIndex].medias).length;
                                    } else {
                                        delete $scope.ratingList[feedIndex].files['file-' + fileIndex];
                                        $scope.ratingList[feedIndex]['commentFileCount'] = Object.keys($scope.ratingList[feedIndex].files).length;
                                    }
                                    if ((Object.keys($scope.ratingList[feedIndex].files).length === 0) && (Object.keys($scope.ratingList[feedIndex].medias).length === 0)) {
                                        IsMediaExists = 0;
                                    }
                                },
                                function (evt) {
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.ratingList[feedIndex], fileIndex : fileIndex, mediaIndex : mediaIndex}, evt);
                                });
                        if (fileType === 'media') {
                            mediaCurrentIndex++;
                        } else {
                            fileCurrentIndex++;
                        }
                        promises.push(promise);

                    })(fileToUpload, fileCurrentIndex, mediaCurrentIndex);
                });
                $q.all(promises).then(function (data) {
                    $scope.isAttachementUploading = false;
                });
            } else {
                var msg = '';
                angular.forEach(errFiles, function (errFile, key) {
                    msg += '\n' + errFile.$errorMessages;
                    promises.push(makeResolvedPromise(msg));
                });
                $q.all(promises).then(function (data) {
                    showResponseMessage(msg, 'alert-danger');
                });
            }
        };

        $scope.validateFileSize = function (file) {
            var defer = $q.defer();
            var isResolvedToFalse = false;
            var mediaPatt = new RegExp("^image|video");
            var videoPatt = new RegExp("^video");
            if (videoPatt.test(file.type)) {
                if (file.size > 41943040) { // if video size > 41943040 Bytes = 40 Mb
                    file.$error = 'size';
                    file.$error = 'Size Error';
                    file.$errorMessages = file.name + ' is too large.';
                    defer.resolve(false);
                    isResolvedToFalse = true;
                }
            } else {
                if (file.size > 4194304) { // if image/document size > 4194304 Bytes = 4 Mb
                    file.$error = 'size';
                    file.$error = 'Size Error';
                    file.$errorMessages = file.name + ' is too large.';
                    defer.resolve(false);
                    isResolvedToFalse = true;
                }
            }

            if (!isResolvedToFalse) {
                defer.resolve(true);
            }
            return defer.promise;
        }

        $scope.deleteCommentEmit = function (CommentGUID, ActivityGUID, CommentParentGUID, PostTypeID) {
            jsonData = {
                CommentGUID: CommentGUID
            };
            var confirm_title = "Delete Comment";
            var confirm_message = "Are you sure, you want to delete this comment ?";
            if (CommentParentGUID)
            {
                if (CommentParentGUID !== '')
                {
                    confirm_title = "Delete Reply";
                    confirm_message = "Are you sure, you want to delete this reply ?";
                }
            }
            if (PostTypeID == '2')
            {
                confirm_title = "Delete Answer";
                confirm_message = "Are you sure, you want to delete this answer ?";
            }
            showConfirmBox(confirm_title, confirm_message, function (e) {
                if (e) {
                    WallService.CallApi(jsonData, 'activity/deleteComment').then(function (response) {
                        var aid = '';
                        var cid = '';
                        if (response.ResponseCode == 200) {
                            $($scope.ratingList).each(function (key, value) {
                                if ($scope.ratingList[key].ActivityGUID == ActivityGUID) {
                                    aid = key;
                                    $($scope.ratingList[aid].Comments).each(function (ckey, cvalue) {
                                        if ($scope.ratingList[aid].Comments[ckey].CommentGUID == CommentGUID || $scope.ratingList[aid].Comments[ckey].CommentGUID == CommentParentGUID) {
                                            if ($scope.ratingList[aid].Comments[ckey].CommentGUID == CommentGUID)
                                            {
                                                cid = ckey;
                                                $scope.ratingList[aid].Comments.splice(cid, 1);
                                                $scope.ratingList[aid].NoOfComments = parseInt($scope.ratingList[aid].NoOfComments) - 1;
                                                return false;
                                            } else
                                            {
                                                angular.forEach($scope.ratingList[aid].Comments[ckey].Replies, function (v2, k2) {
                                                    if (v2.CommentGUID == CommentGUID)
                                                    {
                                                        cid = k2;
                                                        $scope.ratingList[aid].Comments[ckey].Replies.splice(cid, 1);
                                                        $scope.ratingList[aid].Comments[ckey].NoOfReplies = parseInt($scope.ratingList[aid].Comments[ckey].NoOfReplies) - 1;
                                                        return false;
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        };

        function likeDetails(EntityGUID, EntityType, fn) {
            //showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'likeDetailsMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/likeDetailsMdl.js' + $scope.app_version,
                templateUrl: AssetBaseUrl + 'partials/wall/toggle_like.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'like_details_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('likeDetailsMdlInit', {
                        params: params,
                        wallScope: $scope,
                        EntityGUID: EntityGUID,
                        EntityType: EntityType,
                        fn: fn
                    });
                },
            });
        }

        $scope.$on('likeDetailsEmit', function (obj, EntityGUID, EntityType) {
            likeDetails(EntityGUID, EntityType, 'likeDetailsEmit');
        });

        $scope.$on('viewAllComntEmit', function (event, RatingGUID) { //    Call above webservice here for View All Comments
            var element = $('#r-' + RatingGUID + ' .post-as-data');
            var reqData = {EntityGUID: RatingGUID, EntityType: 'Rating', PostAsModuleID: element.attr('data-module-id'), PostAsModuleEntityGUID: element.attr('data-module-entityid'), };
            $("#cmt_loader_" + RatingGUID).show();
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/getAllComments', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $($scope.ratingList).each(function (i, v) {
                        if ($scope.ratingList[i].RatingGUID == RatingGUID) {
                            $scope.ratingList[i].Comments = response.Data;
                        }
                    });
                }
                //$("#cmt_loader_"+ActivityGUID).hide();
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        });

        $scope.$on('likeEmit', function (event, EntityGUID, Type, EntityParentGUID) {

            var element = $('#r-' + EntityParentGUID + ' .post-as-data');
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                PostAsModuleID: element.attr('data-module-id'),
                PostAsModuleEntityGUID: element.attr('data-module-entityid'),
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/toggleLike', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $($scope.ratingList).each(function (key, value) {
                        if ($scope.ratingList[key].RatingGUID == EntityParentGUID) {
                            $($scope.ratingList[key].Comments).each(function (k, v) {
                                if ($scope.ratingList[key].Comments[k].CommentGUID == EntityGUID) {
                                    if ($scope.ratingList[key].Comments[k].IsLike == 1) {
                                        $scope.ratingList[key].Comments[k].IsLike = 0;
                                        $scope.ratingList[key].Comments[k].NoOfLikes--;
                                    } else {
                                        $scope.ratingList[key].Comments[k].IsLike = 1;
                                        $scope.ratingList[key].Comments[k].NoOfLikes++;
                                    }
                                }
                            });
                        }
                    });
                } else {
                    // Error 
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        });

        $scope.getCommentTitle = function (name, link, ModuleID) {
            if (ModuleID == 18) {
                name = '<a class="taggedb" href="' + base_url + 'page/' + link + '">' + name + '</a>';
            } else if (ModuleID == 3) {
                name = '<a class="taggedb" href="' + base_url + link + '">' + name + '</a>';
            }
            return name;
        }

        $scope.UTCtoTimeZone = function (date) {
            var localTime = moment.utc(date).toDate();
            return moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
        }

        $scope.date_format = function (date) {
            return GlobalService.date_format(date);
        }

        $scope.textToLinkComment = function (inputText) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                var replacedText, replacePattern1, replacePattern2, replacePattern3;
                replacedText = inputText.replace("<br>", " ||| ");

                /* replacedText = replacedText.replace(/<br \/>/g, ' <br> ');
                 replacedText = replacedText.replace(/</g, '&lt');
                 replacedText = replacedText.replace(/>/g, '&gt');
                 replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                 replacedText = replacedText.replace(/lt&lt/g, '<');
                 replacedText = replacedText.replace(/gt&gt/g, '>');*/

                //URLs starting with http://, https://, or ftp://
                replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                replacedText = replacedText.replace(replacePattern1, function ($1) {
                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    var youtubeid = scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return '<a href="' + href + '" title="' + href + '" class="chat-anchor" target="_blank">' + link + '</a>';
                    }
                });

                //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
                replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
                replacedText = replacedText.replace(replacePattern2, function ($1, $2) {
                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    href = href.trim();
                    var youtubeid = scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                    }
                });

                //Change email addresses to mailto:: links.
                replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                replacedText = replacedText.replace(" ||| ", "<br>");

                var repTxt = removeTags(replacedText);
                if (repTxt.length > 200) {
                    //replacedText = '<span class="show-less">' + smart_substr(200, replacedText) + '... <a onclick="showMoreComment(this);">See More</a></span><span class="show-more">' + replacedText + '</span>';
                    replacedText = '<span class="show-less">' + smart_sub_str(200, replacedText, false) + '</span><span class="show-more">' +
                            replacedText + '</span>  ';
                }
                replacedText = $sce.trustAsHtml(replacedText);
                return replacedText
            } else {
                return '';
            }

        }
        /* Comments Function Ends */

        $scope.getImagePath = function (MediaType, ImageName, Original) {
            if (Original == 'original') {
                var path = image_server_path + 'upload/ratings/';
            } else {
                var path = image_server_path + 'upload/ratings/220x220/';
            }
            if (MediaType == 'Video') {
                var ext = ImageName.substr(ImageName.lastIndexOf('.') + 1);
                ImageName = ImageName.slice(0, parseInt(ext.length) * -1);
                path += ImageName + 'jpg';
            } else {
                path += ImageName;
            }
            return path;
        }

        $scope.getVideoPath = function (ImageName, thumb) {
            if (thumb == 1) {
                var path = image_server_path + 'upload/ratings/220x220/';
            } else {
                var path = image_server_path + 'upload/ratings/';
            }
            var ext = ImageName.substr(ImageName.lastIndexOf('.') + 1);
            ImageName = ImageName.slice(0, parseInt(ext.length) * -1);
            path += ImageName;
            return path;
        }

        $scope.getCityDetails = function (data) {
            $scope.filter.Location['City'] = data.City;
            $scope.filter.Location['Country'] = data.Country;
            $scope.filter.Location['State'] = data.State;
            $scope.getResetRatingList();
        }

        $scope.autoSuggestLocation = function () {

            var input = document.getElementById('location');
            UtilSrvc.initGoogleLocation(input, function (locationObj) {
                input.value = locationObj.CityStateCountry;
                $scope.getCityDetails(locationObj);
            });
        }
        $scope.textautoSize = function () {
            $('.textNtags').autosize();
        }

        $(document).ready(function () {

            $(window).scroll(function () {
                var pScroll = $(window).scrollTop();
                var pageBottomScroll1 = parseInt($(document).height()) - parseInt($(window).height());
                if (pScroll >= pageBottomScroll1) {
                    setTimeout(function () {
                        if (pScroll >= pageBottomScroll1 && !$scope.busy) {
                            $scope.getRatingList();
                        }
                    }, 200);
                }
            });
            $('.auto-size').autosize();
        });

        $scope.initHoverEffect = function () {
            setTimeout(function () {
                $('.star-all-list li').hover(function () {
                    $(this).addClass('hover-filled').prevAll('li').addClass('hover-filled');
                }, function () {
                    $(this).removeClass('hover-filled').prevAll('li').removeClass('hover-filled');
                });
            }, 1000)
        }

        $scope.initDatePicker = function () {
            $("#startDatePicker").datepicker({
                maxDate: '0',
                onSelect: function (selected) {
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#endDatePicker").datepicker("option", "minDate", dt);
                    $scope.changeDuration();
                }
            });
            $("#endDatePicker").datepicker({
                maxDate: '0',
                onSelect: function (selected) {
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#startDatePicker").datepicker("option", "maxDate", dt);
                    $scope.changeDuration();
                }
            });
        }


//        Rating Attachement Starts

        $scope.isRatingMediaUploading = false;
        var ratingMediaCurrentIndex = 0;
        $scope.UploadedMediaVal = 0;
        $scope.uploadRatingMedia = function (files, errFiles, mediaType) {
//            $scope.errFiles = errFiles;
            var promises = [];
            if (!mediaType) {
                mediaType = 'PHOTO';
            }

            if (!(errFiles.length > 0)) {
                var imagePattern = new RegExp("^image");
                var videoPattern = new RegExp("^video");
                var promises = [];
                $scope.isRatingMediaUploading = true;
                if (!Array.isArray) {
                    Array.isArray = function (arg) {
                        return Object.prototype.toString.call(arg) === '[object Array]';
                    };
                }
                if (!Array.isArray(files)) {
                    files = [files];
                }
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, mediaIndex) {
                        WallService.setFileMetaData(file);
                        var paramsToBeSent = {                            
                            Type: 'ratings',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        if (((mediaType === 'PHOTO') && imagePattern.test(file.type)) || ((mediaType === 'VIDEO') && videoPattern.test(file.type))) {
                            $scope.Album[0].Media['media-' + mediaIndex] = {MediaType: mediaType, IsLoader: false, ConversionStatus: 'Pending'};
                            $scope.Album[0].Media['media-' + mediaIndex]['IsLoader'] = true;
                            $scope.UploadedMediaVal = Object.keys($scope.Album[0].Media).length;
                        } else {
                            showResponseMessage('File type is not allowed.', 'alert-danger');
                            return false;
                        }
                        var url = (videoPattern.test(file.type)) ? 'upload_video' : 'upload_image';
                        
                        $scope.Album[0].medias = $scope.Album[0].Media;
                        
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    WallService.FileUploadProgress({fileType : 'media', scopeObj : $scope.Album[0], fileIndex : 0, mediaIndex : mediaIndex}, {}, response);
                                    if (response.data.ResponseCode == 200) {
                                        $scope.Album[0].Media['media-' + mediaIndex] = response.data.Data;
                                        $scope.Album[0].Media['media-' + mediaIndex]['IsLoader'] = false;
                                    } else {
                                        delete $scope.Album[0].Media['media-' + mediaIndex];
                                        $scope.UploadedMediaVal = Object.keys($scope.Album[0].Media).length;
                                        showResponseMessage(response.data.Message, 'alert-danger');
                                    }
                                },
                                function (response) {
                                    delete $scope.Album[0].Media['media-' + mediaIndex];
                                    $scope.UploadedMediaVal = Object.keys($scope.Album[0].Media).length;
                                },
                                function (evt) {
                                    WallService.FileUploadProgress({fileType : 'media', scopeObj : $scope.Album[0], fileIndex : 0, mediaIndex : mediaIndex}, evt, {});
                                });
                        ratingMediaCurrentIndex++;
                        promises.push(promise);

                    })(fileToUpload, ratingMediaCurrentIndex);
                });
                $q.all(promises).then(function (data) {
                    $scope.isRatingMediaUploading = false;
                });
            } else {
                var msg = '';
                angular.forEach(errFiles, function (errFile, key) {
                    msg += '\n' + errFile.$errorMessages;
                    promises.push(makeResolvedPromise(msg));
                });
                $q.all(promises).then(function (data) {
                    showResponseMessage(msg, 'alert-danger');
                });
            }
        };

        function makeResolvedPromise(data) {
            var deferred = $q.defer();
            deferred.resolve(data);
            return deferred.promise;
        }
        ;

        function createRatingMediaArray(attachement) {
            var deferred = $q.defer();
            deferred.resolve({
                MediaGUID: attachement.MediaGUID,
                MediaType: attachement.MediaType
            });
            return deferred.promise;
        }
        ;

        $scope.removeRatingMedia = function (index) {
            if ($scope.Album[0].Media && ($scope.UploadedMediaVal > 0) && $scope.Album[0].Media[index]) {
                delete $scope.Album[0].Media[index];
                $scope.UploadedMediaVal = Object.keys($scope.Album[0].Media).length;
            }
        };

        $scope.UploadedMedia = function () {
            $scope.UploadedMediaVal = Object.keys($scope.Album[0].Media).length;
            if ($scope.UploadedMediaVal > 0) {
                $scope.$apply();
            }
        }

        $scope.removeAllMedia = function () {
            $scope.Album = [{Media: {}, AlbumName: '', AlbumGUID: '', AlbumType: '', ConversionStatus: ''}];
            $scope.UploadedMediaVal = 0;
            ratingMediaCurrentIndex = 0;
        }
//        Rating Attachement Ends


        $scope.getEntityListPage = function () {

            if (settings_data.m18 == 0) { // If page module is disabled then return;
                return;
            }

            var reqData = {ModuleID: $scope.config_detail.ModuleID, ModuleEntityGUID: $scope.config_detail.ModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/my_pages', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.entityListRating = response.Data;
                    $scope.PostAs = $scope.entityListRating[0];
                } else {
                }
            },
                    function (error) {
                        //showResponseMessage('Something went wrong.', 'alert-danger');
                    });
        }
        $scope.set_post_as = function (data) {
            $scope.PostAs = data;
        }

        $scope.likeStatusEmit = function (RatingGUID, data, ActivityGUID) {
            var element = $('#r-' + RatingGUID + ' .post-as-data');
            element.attr('data-module-id', data.ModuleID);
            element.attr('data-module-entityid', data.ModuleEntityGUID);
            $('#r-' + RatingGUID + ' .show-pic').attr('src', image_server_path + 'upload/profile/36x36/' + data.ProfilePicture);
            $('#r-' + RatingGUID + ' .current-profile-pic').attr('src', image_server_path + 'upload/profile/36x36/' + data.ProfilePicture);
            jsonData = {
                PostAsModuleID: data.ModuleID,
                PostAsModuleEntityGUID: data.ModuleEntityGUID,
                ActivityGUID: ActivityGUID
            };
            WallService.CallApi(jsonData, 'activity/checkLikeStatus').then(function (response) {
                $($scope.ratingList).each(function (k, v) {
                    if ($scope.ratingList[k].ActivityGUID == ActivityGUID) {
                        $scope.ratingList[k].IsLike = response.Data.IsLike;
                        $scope.ratingList[k].Comments = response.Data.Comments;
                    }
                });
            });
        }

    }]);

app.directive('dynamicUrl', [function () {
        return {
            restrict: 'A',
            link: function postLink(scope, element, attr) {
                element.attr('src', attr.dynamicUrlSrc);
            }
        };
    }]);

function checkAttachedRatingMedia() {
    angular.element(document.getElementById('RatingCtrl')).scope().UploadedMedia();
}

function triggerUploadImage() {
//    angular.element(document.getElementById('RatingCtrl')).scope().initFineUploader();
}
