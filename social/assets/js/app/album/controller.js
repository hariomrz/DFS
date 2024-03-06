angular.module('App').controller('AlbumCtrl', ['GlobalService', '$scope', '$sce', 'Settings', '$compile', '$q', 'appInfo', 'WallService', '$rootScope', 'UtilSrvc', 'lazyLoadCS',
    
    function (GlobalService, $scope, $sce, Settings, $compile, $q, appInfo, WallService, $rootScope, UtilSrvc, lazyLoadCS) {
        $scope.AlbumList = [];
        $scope.TotalAlbum = 0;
        $scope.LoginSessionKey = LoginSessionKey;
        $scope.stopExecution = 0;
        $scope.SortBy = 'CreatedDate';
        $scope.OrderBy = 'DESC';
        $scope.IsCommentable = 1;
        $scope.CommentsAllowed = 1;
        $scope.AlbumType = "PHOTO";
        $scope.SelectedAlbumName = "";
        $scope.modalbum = {};
        $scope.modalbum.Location = {};
        $scope.media = {};
        $scope.mediaToBeAdded={};
        $scope.mediaToBeRemoved=[];
        $scope.modalbum.Visibility = 1;
        $scope.modyoutube = {};
        $scope.modalbum.AlbumGUID = '';
        $scope.albumpageview = 'create';
        $scope.albumDetails = {};
        $scope.albumMediaList = [];
        $scope.TotalAlbumMedia = 0;
        $scope.AlbumGUID = '';
        $scope.TabId = 'photo_album';
        $scope.AlbumCreateEditHeading = "CREATE ALBUM";
        $scope.AlbumCreateEditBtn = "CREATE";
        $scope.album_action = 'create';
        $scope.mediadetailindex = '';
        $scope.LoggedInName = '';
        $scope.LoggedInProfilePicture = '';

        $scope.AlbumOffset = 1;
        $scope.AlbumLimit = 10;

        $scope.AlbumDetailOffset = 1;
        $scope.AlbumDetailLimit = 25;
        $scope.DisableSubmit = true;
        $scope.IsUploading = false;
        $scope.modalbum.Description = '';
        $scope.modalbum.AlbumName = '';
        $scope.mediaIndexStart = 0;
        $scope.MediaListPageNo = 1;
        $scope.IsEdit = false;
        $scope.moduleSection = 'user';

        //Google location suggest
        var curLocation, currentLocation;        

        // function for user current location in profile section
        $scope.currentLocationInitialize = function (txtId, mediaEl) {
            $('.pac-container').hide();
            var input = document.getElementById(txtId);
            
            UtilSrvc.initGoogleLocation(input, function(locationObj){
                
                if (mediaEl == undefined) {
                     $scope.locationFillInAddress(txtId, locationObj);
                }
                
            });

        }

        $scope.locationFillInAddress = function (txtId, locationObj) {
            unique_id = locationObj.UniqueID;
            formatted_address = locationObj.FormattedAddress;
            lat = locationObj.Latitude;
            lng = locationObj.Longitude;
            street_number = locationObj.StreetNumber;
            route = locationObj.Route;
            city = locationObj.City;
            state = locationObj.State;
            country = locationObj.Country;
            postal_code = locationObj.PostalCode;
                        
            
            if (txtId == 'AlbumLocation') {
                $scope.modalbum.Location.UniqueID = unique_id;
                //$scope.modalbum.Location.FormattedAddress = formatted_address;
                $scope.modalbum.Location.FormattedAddress = $('#AlbumLocation').val();
                $scope.modalbum.Location.Latitude = lat;
                $scope.modalbum.Location.Longitude = lng;
                $scope.modalbum.Location.StreetNumber = street_number;
                $scope.modalbum.Location.Route = route;
                $scope.modalbum.Location.City = city;
                $scope.modalbum.Location.State = state;
                $scope.modalbum.Location.Country = country;
                $scope.modalbum.Location.PostalCode = postal_code;
                $scope.modalbum.Location.LocationID = unique_id;
                //console.log($scope.modalbum.Location);
                //add album location to all media not having location set
                $scope.addMediaLocation();

            } else {
                $scope.media['media-' + txtId].Location = {};
                $scope.media['media-' + txtId].Location.UniqueID = unique_id;
                //$scope.media['media-' + txtId].Location.FormattedAddress = formatted_address;
                $scope.media['media-' + txtId].Location.FormattedAddress = $('#' + txtId).val();
                $scope.media['media-' + txtId].Location.Latitude = lat;
                $scope.media['media-' + txtId].Location.Longitude = lng;
                $scope.media['media-' + txtId].Location.StreetNumber = street_number;
                $scope.media['media-' + txtId].Location.Route = route;
                $scope.media['media-' + txtId].Location.City = city;
                $scope.media['media-' + txtId].Location.State = state;
                $scope.media['media-' + txtId].Location.Country = country;
                $scope.media['media-' + txtId].Location.PostalCode = postal_code;
                $scope.media['media-' + txtId].Location.LocationID = unique_id;
                var $el = $('#location' + txtId).html('-at <a class="tag"><span>' + $scope.media['media-' + txtId].Location.FormattedAddress + '</span> <i class="icon-remove" ng-click="removeMediaLocation(' + txtId + ')"></i></a>');
                $compile($el)($scope);
                $('.editDropdown').hide();
                $('#' + txtId).val('');
            }
        }
        
        
        if ($('#AlbumLocation').length > 0) {
            $scope.currentLocationInitialize('AlbumLocation');
        }
        //Google location suggest ends  

        $scope.addMediaLocation = function () {
            $.each($scope.media, function (k, val) {
//                if ($scope.media[k] != undefined && $scope.media[k].Location.UniqueID == undefined) {
                if ( $scope.media[k] && ( !$scope.media[k].Location ) ) {
                    $scope.media[k]['Location'] = $scope.modalbum.Location;
                    var $el = $('#location' + $scope.media[k].mediaIndex).html('-at <a  class="tag"> <span>' + $scope.media[k].Location.FormattedAddress + '</span> <i class="icon-remove" ng-click="removeMediaLocation(' + $scope.media[k].mediaIndex + ')"></i></a>');
                    $compile($el)($scope);
                }
            });
        }

        $scope.isEmpty = function (obj) {
            for (var i in obj)
                if (obj.hasOwnProperty(i))
                    return false;
            return true;
        };

        $scope.loadAlbumTabData = function (AlbumType, TabId) {
            $scope.modalbum = {};
            $scope.albumpageview = 'list';
            $scope.album_action = 'create';
            $scope.AlbumGUID = '';

            //For show active tab
            var AlbumTabId = '';
            if (typeof TabId == 'undefined' || TabId != "") {
                AlbumTabId = TabId;
            }
            $scope.TabId = AlbumTabId;
            $(".media-gallery-tab li").removeClass("active");
            $("#" + AlbumTabId).addClass("active");
            $scope.AlbumType = AlbumType;
            $scope.AlbumOffset = 1;
            $scope.AlbumList = [];
            $scope.albumListing();
        };

        var AlbumPageNo = 1;
        $scope.albumListing = function () {
            var AlbumType = $scope.AlbumType;
            var ModuleID = $("#hdn_module_id").val();
            var ModuleEntityID = $("#hdn_module_guid").val();

            $scope.albumpageview = 'list';
            $scope.album_action = 'create';
            $scope.AlbumGUID = '';

            if ($scope.stopExecution == 0) {
                var reqData = {
                    AlbumType: AlbumType,
                    ModuleEntityGUID: ModuleEntityID,
                    ModuleID: ModuleID,
                    PageNo: AlbumPageNo,
                    PageSize: $scope.AlbumLimit,
                    SortBy: $scope.SortBy,
                    OrderBy: $scope.OrderBy
                };
                AlbumPageNo = AlbumPageNo+1;
                $scope.stopExecution = 1;
                //showPageLoader();
                WallService.CallPostApi(appInfo.serviceUrl + 'album/list', reqData, function (successResp) {
                    var response = successResp.data;                
                    $scope.stopExecution = 0;
                    if (response.ResponseCode == 200) {
                        $scope.TotalAlbum = response.TotalRecords;

                        if (AlbumType == "VIDEO") {
                            var albumArr = [];
                            $(response.Data).each(function (k1, v1) {
                                v1.CoverVideoThumb = '';
                                var videoid = $scope.parseYoutubeVideo(v1.CoverMedia);
                                v1.CoverVideoThumb = $scope.getYoutubeVideoThumb(videoid);
                                albumArr.push(v1);
                                $scope.AlbumList.push(v1);
                            });

                        } else {
                            $(response.Data).each(function (k1, v1) {
                                $scope.AlbumList.push(v1);
                            });
                        }
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                    // HidePageLoader();
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };

        $scope.hasMoreItemsToAlbums = function () {
            return $scope.TotalAlbum > $scope.AlbumList.length;
        };

        $scope.loadMoreAlbums = function () {
            $scope.AlbumOffset++;
            $scope.albumListing();
        };
        $scope.setVisibility = function (Visibility) {
            if (Visibility != undefined) {
                $scope.modalbum.Visibility = Visibility;
            }
        };

        $scope.createAlbum = function () {
            passErrorRemove();
            $scope.album_action = 'create';
            $scope.AlbumCreateEditHeading = "CREATE ALBUM";
            $scope.AlbumCreateEditBtn = "CREATE";
            $scope.modalbum = {};
            $scope.modalbum.AlbumGUID = '';
        };

        $scope.editAlbum = function () {
            $scope.album_action = 'edit';
            $scope.albumpageview = 'edit';
            $scope.AlbumCreateEditHeading = "EDIT ALBUM";
            $scope.AlbumCreateEditBtn = "UPDATE";
            var albumDetail = $scope.albumDetails;
            $scope.modalbum.AlbumGUID = albumDetail.AlbumGUID;
            $scope.modalbum.AlbumName = albumDetail.AlbumName;
            $scope.modalbum.Description = albumDetail.Description;
            $scope.modalbum.CommentsAllowed = albumDetail.CommentsAllowed;
            $scope.CommentsAllowed = albumDetail.CommentsAllowed;
            $scope.IsCommentable = albumDetail.CommentsAllowed;
            $scope.modalbum.Visibility = albumDetail.Visibility;
        };

        $scope.deleteAlbum = function (AlbumGUID, redirect)
        {            
            var album_index = this.$index;
            var reqData = {AlbumGUID: AlbumGUID};
            showConfirmBox('Delete Confirmation', 'Are you sure you want to delete this album?', function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'album/delete', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            showResponseMessage(response.Message+' '+$scope.SelectedAlbumName, 'alert-success');
                            if (redirect == 1)
                            {
                                setTimeout(function () {
                                    $scope.redirectToSlug('');
                                }, 500);
                            } else
                            {
                                $scope.AlbumList.splice(album_index, 1);
                                $scope.TotalAlbum--;
                            }
                        } else {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
                return;
            });

        };

        $scope.MediaCaptions = function ()
        {
          if( $scope.media && ( Object.keys( $scope.media ).length > 0 ) ) {
            angular.forEach($scope.media, function(v, k) {
                var CoverPic = $(this).find('.makeCover').hasClass('CoverPic');
                if ($scope.media[k] != undefined) {
                    if (CoverPic) {
                        $scope.media[k].isCoverPic = 1;
                    } else {
                        $scope.media[k].isCoverPic = 0;
                    }
                    //$scope.media['media-' + k].Caption = MediaCaption;
                }
            });
          }
        }

        $scope.setAlbumCover = function (index)
        {
            $.each($scope.media, function (k, v) {
                if (index == k) {
                    $scope.media[k].isCoverPic = 1;
                    $scope.media[k].IsCoverMedia = 1;
                } else {
                    $scope.media[k].isCoverPic = 0;
                    $scope.media[k].IsCoverMedia = 0;
                }
            });
        }

        $scope.setAsAlbumCover = function (MediaGUID, AlbumGUID, index)
        {
            var reqData = {
                MediaGUID: MediaGUID,
                AlbumGUID: AlbumGUID
            };
            var Url = 'album/set_cover_media';
            WallService.CallPostApi(appInfo.serviceUrl + Url, reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $.each($scope.albumMediaList, function (k, v) {
                        if (index == k) {
                            $scope.albumMediaList[k].IsCoverMedia = 1;
                        } else {
                            $scope.albumMediaList[k].IsCoverMedia = 0;
                        }
                    });
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.setIsCommentable = function ()
        {
            if ($scope.modalbum.CommentsAllowed == 1) {
                $scope.modalbum.CommentsAllowed = 0;
                $('#commentableAlbum').attr('title', 'Turn Comment on');
            } else {
                $scope.modalbum.CommentsAllowed = 1;
                $('#commentableAlbum').attr('title', 'Turn Comment Off');
            }
        }

        $scope.setProfileUrl = function (profile_url)
        {
            $scope.ProfileUrl = profile_url;

        }

//    $scope.checkBlank = function(){
//        if($scope.modalbum.Description==''|| $scope.modalbum.AlbumName==''){
//            $scope.DisableSubmit = true;
//        }else{
//            $scope.DisableSubmit = false;
//        }
//    }
//    
        $scope.$watch('modalbum.Description', function () {
            if ($scope.modalbum.Description == '' || $scope.modalbum.AlbumName == '') {
                $scope.DisableSubmit = true;
            } else {
                $scope.DisableSubmit = false;
            }
        }, true);

        $scope.SubmitCreateAlbumForm = function ()
        {
            showButtonLoader('createalbumbtn');
            var val = checkstatus('formAlbum');
            if (val === false)
                return;
            var ModuleID = $("#hdn_module_id").val();
            var ModuleEntityGUID = $("#hdn_module_guid").val();
            passErrorRemove();
            //Add media captions to the media array.
            // $scope.MediaCaptions();

            if ($scope.moduleSection == 'user') {
                Visibility = $scope.modalbum.Visibility;
            } else {
                Visibility = 1;
            }
            var jsonData = {
                AlbumName: $scope.modalbum.AlbumName,
                Description: $scope.modalbum.Description,
                Visibility: Visibility,
                AlbumType: $scope.AlbumType,
                ModuleID: ModuleID,
                ModuleEntityGUID: ModuleEntityGUID,
                AlbumGUID: $scope.modalbum.AlbumGUID,
                Location: $scope.modalbum.Location,
                Media: [],
                DeletedMedia: [],
                Commentable: $scope.modalbum.CommentsAllowed
            };
            
            
            var albumMediaPromises = [];

            if( $scope.media && ( Object.keys($scope.media).length > 0 ) ){
              angular.forEach( $scope.media, function(media, key) {
                  albumMediaPromises.push(createAlbumMediaArray(media).then(function(dataToAssign){
                    if ($scope.modalbum.AlbumGUID != "") {
                        jsonData.Media.push(dataToAssign);
                    }else{
                        jsonData.Media.push(dataToAssign);
                    }
                  }));
              });
            }
            if ($scope.modalbum.AlbumGUID != "") {
                if( $scope.mediaToBeRemoved && ( $scope.mediaToBeRemoved.length > 0 ) ){
                  angular.forEach( $scope.mediaToBeRemoved, function(media, key) {
                      albumMediaPromises.push(createAlbumMediaArray(media).then(function(dataToAssign){
                        jsonData.DeletedMedia.push(dataToAssign);
                      }));
                  });
                }
            }
                       
            $q.all(albumMediaPromises).then(function(data) {
//              console.log(jsonData); return false;
              if ($scope.modalbum.AlbumGUID != "") {
                  WallService.CallPostApi(appInfo.serviceUrl + 'album/edit', jsonData, function (successResp) {
                      var response = successResp.data;
                      if (response.ResponseCode == '200')
                      {
                          showResponseMessage(response.Message+' '+$scope.modalbum.AlbumName, 'alert-success');
//                          $scope.media = {};
//                          $scope.mediaCount = $scope.mediaIndexStart = 0;
                          setTimeout(function () {
                            $scope.redirectToAlbumDetails($scope.modalbum.AlbumGUID);
                          }, 1000);
                      } else {
                        showResponseMessage(response.Message, 'alert-danger');
                        hideButtonLoader('createalbumbtn');
                      }
                  }, function (error) {
                      // showResponseMessage('Something went wrong.', 'alert-danger');
                  });
              } else {
                  WallService.CallPostApi(appInfo.serviceUrl + 'album/add', jsonData, function (successResp) {
                      var response = successResp.data;
                      if (response.ResponseCode == '200')
                      {
                          showResponseMessage(response.Message+' '+$scope.modalbum.AlbumName, 'alert-success');
//                          $scope.media = {};
//                          $scope.mediaCount = $scope.mediaIndexStart = 0;
                          setTimeout(function () {
                            $scope.redirectToSlug();
                          }, 1000);
                      } else {
                        showResponseMessage(response.Message, 'alert-danger');
                        hideButtonLoader('createalbumbtn');
                      }
                  }, function (error) {
                      // showResponseMessage('Something went wrong.', 'alert-danger');
                  });
              }
            });
        };
        
        function createAlbumMediaArray (media) {
          var deferred = $q.defer();
          var mediaInfo = angular.copy(media);
          delete mediaInfo['data'];
          var object = angular.extend({}, mediaInfo, media.data);
//          var mediaData = media.data;
//          delete media['data'];
//          var object = angular.extend({}, media, mediaData);
          deferred.resolve(object);
          return deferred.promise;
        };

        $scope.timeAgo = function (date) {
            return GlobalService.date_format(date);
        }

        $scope.getAlbumCover = function (filename)
        {
            var ext = filename.substr(filename.lastIndexOf('.') + 1);
            var fname = filename.substr(0, filename.lastIndexOf('.'));
            if (ext == 'jpg' || ext == 'JPG' || ext == 'png' || ext == 'PNG' || ext == 'bmp' || ext == 'BMP' || ext == 'gif' || ext == 'GIF' || ext == 'jpeg' || ext == 'JPEG')
            {
                return fname + '.' + ext;
            } else
            {
                return fname + '.jpg';
            }
        }

        $scope.redirectToAlbumDetails = function (AlbumGUID)
        {
            window.top.location = $scope.albumBaseURL() + '/' + AlbumGUID;
        }

        $scope.redirectToSlug = function (slug)
        {
            if (slug == undefined) {
                window.top.location = $scope.albumBaseURL();
            } else {
                window.top.location = $scope.albumBaseURL() + '/' + slug;
            }
        }

        $scope.removeMediaLocation = function (index)
        {
            $scope.media['media-' + index].Location = {};
            $('#location' + index).html('');
        }

        $scope.getAlbumDetails = function (AlbumGUID, isEdit) {
            if (isEdit == undefined) {
                isEdit = false;
            } else {
                $scope.albumpageview = 'edit';
            }
            $scope.AlbumGUID = AlbumGUID;
            var jsonData = {
                AlbumGUID: AlbumGUID
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'album/details', jsonData, function (successResp) {
              var response = successResp.data;
              if (response.ResponseCode == '200')
              {
                if (response.Data.Location.length == 0) {
                  Location = {};
                } else {
                  Location = response.Data.Location;
                }
                $scope.LoggedInName = response.LoggedInName;
                $scope.LoggedInProfilePicture = response.LoggedInProfilePicture;
                $scope.albumDetails = response.Data;
                $scope.modalbum.Location = Location;
                $scope.albumDetails['Settings'] = Settings.getSettings();
                $scope.albumDetails['ImageServerPath'] = Settings.getImageServerPath();
                $scope.albumDetails['SiteURL'] = Settings.getSiteUrl();

                $scope.SelectedAlbumName = $scope.albumDetails.AlbumName;
                if (isEdit) {
                  $scope.editAlbum();
                }
                //$scope.resetLoadMoreMediaData();
                $scope.getAlbumMediaList(AlbumGUID);
//                    console.log($scope.albumDetails);
              } else {
                showResponseMessage(response.Message, 'alert-danger');
              }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.tagComment = function (eid) {
            var ajax_request = false;
            setTimeout(function () {
                $('#' + eid).textntags({
                    onDataRequest: function (mode, query, triggerChar, callback) {
                        if (ajax_request)
                            ajax_request.abort();
                        if ($('#module_id').val() == 1) {
                            var type = 'Members';
                        } else {
                            var type = 'Friends';
                        }
                        ajax_request = $.post(base_url + 'api/users/list', {Type: type,SearchKey: query, ModuleID: $('#module_id').val(), ModuleEntityID: $('#module_entity_guid').val()}, function (r) {
                            if (r.ResponseCode == 200) {
                                var uid = 0;
                                var d = new Array();
                                for (var key in r.Data.Members) {
                                    var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                    d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': '3'};
                                    uid++;
                                }
                                query = query.toLowerCase();
                                var found = _.filter(d, function (item) {
                                    query = $.trim(query);
                                    return item.name.toLowerCase().indexOf(query) > -1;
                                });

                                callback.call(this, found);
                                ajax_request = false;
                            }
                        });
                    }
                });
            }, 500);
        }

        $scope.isAttachementUploading = false;
        var IsMediaExists = 0;
        var mediaCurrentIndex = 0;
        var fileCurrentIndex = 0;
        
        //$scope.albumDetailLoaders = {};
        $scope.uploadFiles = function(files, errFiles, id) {
//            $scope.errFiles = errFiles;
            var promises = [];
            if(! (errFiles.length > 0 ) ) {
                if(!$scope.albumDetails.medias) {
                  $scope.albumDetails['medias'] = {};
                  $scope.albumDetails['commentMediaCount'] = 0;
                }
                
                if(!$scope.albumDetails.files) {
                  $scope.albumDetails['files'] = {}; 
                  $scope.albumDetails['commentFileCount'] = 0;
                }

                var patt = new RegExp("^image|video");
                var videoPatt = new RegExp("^video");
                $scope.isAttachementUploading = true;
                angular.element('#cmt-' + id).focus();
                angular.forEach(files, function(fileToUpload, key) {
                    (function (file, fileIndex, mediaIndex) {
                        var fileType = 'media';
                        
                        WallService.setFileMetaData(file);
                        var paramsToBeSent = {                                
                                Type: 'comments',
                                DeviceType: 'Native',
                                qqfile: file
                        };
                        if(patt.test(file.type)) {
                            $scope.albumDetails.medias['media-' + mediaIndex] = file;
                            $scope.albumDetails['commentMediaCount'] = Object.keys($scope.albumDetails.medias).length;
                        } else {
                            $scope.albumDetails.files['file-' + fileIndex] = file;
                            $scope.albumDetails['commentFileCount'] = Object.keys($scope.albumDetails.files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        var promise = WallService.CallUploadFilesApi(
                        paramsToBeSent,
                        url,
                        function (response) {
                            WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.albumDetails, fileIndex : fileIndex, mediaIndex : mediaIndex}, {}, response);
                            if(fileType === 'media') {
                              if(response.data.ResponseCode === 200) {
                                $scope.albumDetails.medias['media-' + mediaIndex]['data'] = response.data.Data;
                                $scope.albumDetails.medias['media-' + mediaIndex].progress = true;
                              } else {
                                delete $scope.albumDetails.medias['media-' + mediaIndex];
                                $scope.albumDetails['commentMediaCount'] = Object.keys($scope.albumDetails.medias).length;
                                showResponseMessage(response.data.Message, 'alert-danger');
                              }
                            } else {
                              if(response.data.ResponseCode === 200) {
                                $scope.albumDetails.files['file-' + fileIndex]['data'] = response.data.Data;
                                $scope.albumDetails.files['file-' + fileIndex].progress = true;
                              } else {
                                delete $scope.albumDetails.files['file-' + fileIndex];
                                $scope.albumDetails['commentFileCount'] = Object.keys($scope.albumDetails.files).length;
                                showResponseMessage(response.data.Message, 'alert-danger');
                              }
                            }
                            IsMediaExists = 1;
                        },
                        function (response) {
                            
                            if(fileType === 'media') {
                                delete $scope.albumDetails.medias['media-' + mediaIndex];
                                $scope.albumDetails['commentMediaCount'] = Object.keys($scope.albumDetails.medias).length;
                            } else {
                                delete $scope.albumDetails.files['file-' + fileIndex];
                                $scope.albumDetails['commentFileCount'] = Object.keys($scope.albumDetails.files).length;
                            }
                        },
                        function (evt) {
                            
                            //var extraObj = {fileType : fileType, scopeObj : $scope.albumDetailLoaders, fileIndex : key, mediaIndex : key};
                            
                            WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.albumDetails, fileIndex : fileIndex, mediaIndex : mediaIndex}, evt);
                        });
                        if(fileType === 'media') {
                            mediaCurrentIndex++;
                        } else {
                            fileCurrentIndex++;
                        }
                        promises.push(promise);

                    })(fileToUpload, fileCurrentIndex, mediaCurrentIndex);
                });
                $q.all(promises).then(function(data) {
                    $scope.isAttachementUploading = false;
                });
            } else {
              var msg = '';
              angular.forEach(errFiles, function(errFile, key) {
                  msg += '\n' + errFile.$errorMessages;
                  promises.push(makeResolvedPromise(msg));
              });
              $q.all(promises).then(function(data) {
                  showResponseMessage(msg, 'alert-danger');
              });
            }
        };
        
        function makeResolvedPromise (data) {
          var deferred = $q.defer();
          deferred.resolve(data);
          return deferred.promise;
        };
        
        function createAttachementArray (attachement) {
          var deferred = $q.defer();
          deferred.resolve({
            MediaGUID : attachement.MediaGUID,
            MediaType : attachement.MediaType
          });
          return deferred.promise;
        };
        
        $scope.removeAttachement = function(type, index) {
          if((type === 'file') && ( $scope.albumDetails.files && Object.keys($scope.albumDetails.files).length ) ) {
            delete $scope.albumDetails.files[index];
            $scope.albumDetails['commentFileCount'] = Object.keys($scope.albumDetails.files).length;
          } else if($scope.albumDetails.medias && Object.keys($scope.albumDetails.medias).length){
            delete $scope.albumDetails.medias[index];
            $scope.albumDetails['commentMediaCount'] = Object.keys($scope.albumDetails.medias).length;
          }
          if( ( Object.keys( $scope.albumDetails.files ).length === 0 ) && ( Object.keys( $scope.albumDetails.medias ).length === 0 ) ) {
            IsMediaExists = 0;
          }
          angular.element('#' + $scope.albumDetails.AlbumGUID).focus();
          angular.element('#' + $scope.albumDetails.AlbumGUID).blur();
        };
        
        $scope.getMediaClass = function (media) {
            if (media.length == 1)
            {
                return 'post-media single'
            } else if (media.length == 2)
            {
                return 'post-media two';
            } else
            {
                return 'row gutter-5 post-media morethan-two'
            }
        };

        var isCommenting = false;
        $scope.$on('commentEmit', function (obj, event, AlbumGUID) {
          if ( ( !isCommenting ) && ( event.which == 13 ) && ( $scope.isAttachementUploading == false ) ) {
              $scope.appendComment = 1;
              if (!event.shiftKey) {

                  isCommenting = true;
                  event.preventDefault();
                  var Comment = $('#cmt-' + AlbumGUID).val();
                  Comment = Comment.trim();
                  var MediaGUID = '';
                  var Caption = '';
                  var IsMediaExists = 0;
                  if ($('#cm-' + AlbumGUID + ' li').length > 0) {
                      MediaGUID = $('#cm-' + AlbumGUID + ' input[name="MediaGUID"]').val();
                      Caption = $('#cm-' + AlbumGUID + ' input[name="Caption"]').val();
                      IsMediaExists = 1;
                  }
                  var PComments = $('#act-' + AlbumGUID + ' .textntags-beautifier div').html();
                  jQuery('#act-' + AlbumGUID + ' .textntags-beautifier div strong').each(function (e) {
                      var details = $('#act-' + AlbumGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').attr('class');
                      var module_id = details.split('-')[1];
                      var module_entity_id = details.split('-')[2];
                      var name = $('#act-' + AlbumGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').text();
                      PComments = PComments.replace('<strong><span class="user-' + module_id + '-' + module_entity_id + '">' + name + '</span></strong>', '{{' + name + ':' + module_entity_id + ':' + module_id + '}}');
                  });
                  var Media = [];
                  $('#cmt-' + AlbumGUID).val('');
                  jQuery('#cmt-' + AlbumGUID).textntags('reset');
                  $('.textntags-beautifier div').html('');

                  var attacheMentPromises = [];

                  if($scope.albumDetails.medias && (Object.keys($scope.albumDetails.medias).length > 0)){
                    angular.forEach($scope.albumDetails.medias, function(attachement, key) {
                        attacheMentPromises.push(createAttachementArray(attachement.data).then(function(dataToAttache){
                            Media.push({
                                MediaGUID: dataToAttache.MediaGUID,
                                MediaType: dataToAttache.MediaType,
                                Caption: ''
                            });
                        }));
                    });
                  }

                  if($scope.albumDetails.files && (Object.keys($scope.albumDetails.files).length > 0)){
                    angular.forEach($scope.albumDetails.files, function(attachement, key) {
                        attacheMentPromises.push(createAttachementArray(attachement.data).then(function(dataToAttache){
                            Media.push({
                                MediaGUID: dataToAttache.MediaGUID,
                                MediaType: dataToAttache.MediaType,
                                Caption: ''
                            });
                        }));
                    });
                  }

                  $q.all(attacheMentPromises).then(function(data) {
                    if ( ( Media.length == 0 ) && ( Comment == '' ) ) {
                        //showResponseMessage('Please upload image or type any text','alert-danger');
                        $('#cmt-' + AlbumGUID).val('');
                        return;
                    }
                    Comment = PComments;
                    var jsonData = {Comment: Comment, EntityType: 'Album', EntityGUID: AlbumGUID, Media: Media, EntityOwner: 0};
                    WallService.CallPostApi(appInfo.serviceUrl + 'activity/addComment', jsonData, function (successResp) {
                        var response = successResp.data;
                        if(response.ResponseCode == 200)
                        {
                            var newArr = new Array();
                            isCommenting = false;
                            $($scope.albumDetails.Comments).each(function (k, value) {
                                newArr.push($scope.albumDetails.Comments[k]);
                            });
                            newArr.push(response.Data[0]);
                            $scope.albumDetails.Comments = newArr.reduce(function (o, v, i) {
                                o[i] = v;
                                return o;
                            }, {});
                            $scope.albumDetails.Comments = newArr;
                            $scope.albumDetails.files = {};
                            $scope.albumDetails.medias = {};
                            $scope.albumDetails['commentMediaCount'] = 0;
                            $scope.albumDetails['commentFileCount'] = 0;
                            mediaCurrentIndex = 0;
                            fileCurrentIndex = 0;
                            $scope.albumDetails.NoOfComments = parseInt($scope.albumDetails.NoOfComments) + 1;
                            $scope.albumDetails.comntData = $scope.$broadcast('appendComntEmit', $scope.albumDetails.Comments); //getPostComments($scope.activityData[key].Comments);
    //                              $('#upload-btn-' + AlbumGUID).show();
                            setTimeout(function () {
                                $('#cmt-' + AlbumGUID).trigger('focus');
                            }, 200);
                        }
                    }, function (error) {
                        isCommenting = false;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                  });
              }
          }
        });

        $scope.toggleEntityLike = function (Type, EntityGUID)
        {
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                EntityOwner: 0
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'activity/toggleLike', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (Type == 'ALBUM') {
                        if(typeof $scope.albumDetails.LikeName!=='undefined')
                        {
                            $scope.albumDetails.LikeName.Name = response.LikeName.Name;
                            $scope.albumDetails.LikeName.ProfileURL = response.LikeName.ProfileURL;
                        }

                        if ($scope.albumDetails.IsLike == 1) {
                            $scope.albumDetails.IsLike = 0;
                            $scope.albumDetails.NoOfLikes--;
                        } else {
                            $scope.albumDetails.IsLike = 1;
                            $scope.albumDetails.NoOfLikes++;
                        }
                    } else if (Type == 'COMMENT') {
                        $($scope.albumDetails.Comments).each(function (k, v) {
                            if ($scope.albumDetails.Comments[k].CommentGUID == EntityGUID) {
                                if ($scope.albumDetails.Comments[k].IsLike == 1) {
                                    $scope.albumDetails.Comments[k].IsLike = 0;
                                    $scope.albumDetails.Comments[k].NoOfLikes--;
                                } else {
                                    $scope.albumDetails.Comments[k].IsLike = 1;
                                    $scope.albumDetails.Comments[k].NoOfLikes++;
                                }
                            }
                        });
                    }
                } else {
                    // Error 
                }
            }, function (error) {
                // Error
            });
        }

        $(window).scroll(function () {
            if ($(window).scrollTop() + $(window).height() == $(document).height()) {
                if ($scope.albumpageview == 'list') {
                    $scope.loadMoreAlbums();
                } else if ($scope.albumpageview == 'detail') {
                    $scope.getAlbumMediaList($scope.albumDetails.AlbumGUID);
                }
            }
        });

        $scope.updateLikeCount = function (MediaGUID, Count)
        {
            angular.forEach($scope.albumMediaList, function (val, key) {
                if (val.MediaGUID == MediaGUID)
                {
                    if (Count == 1)
                    {
                        $scope.albumMediaList[key].NoOfLikes++;
                    } else
                    {
                        $scope.albumMediaList[key].NoOfLikes--;
                    }
                }
            });
        }

        $scope.updateCommentCount = function (MediaGUID)
        {
            angular.forEach($scope.albumMediaList, function (val, key) {
                if (val.MediaGUID == MediaGUID)
                {
                    $scope.albumMediaList[key].NoOfComments++;
                }
            });
        }

        $scope.albumMediaListShare = [];
        $scope.getAlbumMediaList = function (AlbumGUID, detail) {
            $scope.albumMediaListShare = [];
            //$scope.albumpageview = 'detail';
            if ($scope.TotalAlbumMedia <= $scope.albumMediaList.length && $scope.TotalAlbumMedia !== 0)
            {
                return;
            }
            if (typeof AlbumGUID === 'undefined')
            {
                return;
            }
            $scope.albumpageview = 'detail';
            if ($scope.stopExecution == 0) {
                var reqData = {
                    AlbumGUID: AlbumGUID,
                    PageNo: $scope.MediaListPageNo, //Math.floor($scope.albumMediaList.length/$scope.AlbumDetailLimit),
                    PageSize: $scope.AlbumDetailLimit,
                    SortBy: $scope.SortBy,
                    OrderBy: $scope.OrderBy,
                    IsEdit: $scope.IsEdit
                };
                $scope.stopExecution = 1;
                //$scope.albumMediaList = [];
                $scope.TotalAlbumMedia = 0;
                $scope.mediadetailindex = '';
                //showPageLoader();
                WallService.CallPostApi(appInfo.serviceUrl + 'album/list_media', reqData, function (successResp) {
                    var response = successResp.data;
                    $scope.stopExecution = 0;
                    if (response.ResponseCode == 200) {
                        $scope.TotalAlbumMedia = response.MediaCount;
                        var albummediaArr = [];
                        var cnt = 1;
                        $(response.Data).each(function (k1, v1) {
                            v1.VideoThumb = '';
                            if (v1.MediaType == "Youtube") {
                                var videoid = $scope.parseYoutubeVideo(v1.ImageName);
                                v1.VideoThumb = $scope.getYoutubeVideoThumb(videoid);
                            }
                            albummediaArr.push(v1);
                            var exists = false;
                            angular.forEach($scope.albumMediaList, function (v2, k2) {
                                if (v2.MediaGUID == v1.MediaGUID)
                                {
                                    exists = true;
                                }
                            });
                            if (!exists)
                            {
                                $scope.albumMediaList.push(v1);
                            }
//                            console.log(v1);
                            if(v1.Location){
                              var location = v1.Location;
                              delete v1['Location'];
                            }
                            $scope.media['media-' + k1] = { progress : true, mediaIndex : k1, data : v1, Location : location };
                            if (cnt < 5)
                            {
                                $scope.albumMediaListShare.push(v1);
                            }
                            cnt++;
                        });
                        $scope.mediaCount = $scope.mediaIndexStart = ( $scope.media && ( Object.keys($scope.media).length > 0 ) ) ? Object.keys($scope.media).length : 0;
                        $scope.MediaListPageNo++;
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                    // HidePageLoader();
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };

        $scope.hasMoreItemsToAlbumMedia = function () {
            return $scope.TotalAlbumMedia > $scope.albumMediaList.length;
        };

        $scope.loadMoreAlbumMedia = function () {
            $scope.AlbumDetailOffset++;
            $scope.getAlbumMediaList($scope.AlbumGUID, 'detail');
        };

        $scope.resetLoadMoreMediaData = function () {
            $scope.AlbumDetailOffset = 1;
            $scope.albumMediaList = [];
        };

        $scope.removeLoaders = function (index) {
            angular.element('.uploading').remove();
        };

        $scope.removeLoader = function (index) {
            var medialength = $scope.media.length;
            angular.element('#files-' + index).remove();
        };



        $scope.UpdateAlbumMedia = function (MediaData) {
            var Media = [];
            Media.push({'MediaGUID': MediaData.MediaGUID, 'Caption': "", 'Description': "", 'Keyword': ""});
            var ModuleID = $("#hdn_module_id").val();
            var reqData = {
                AlbumGUID: $scope.AlbumGUID,
                Media: Media,
                Youtube: [],
                ModuleID: ModuleID
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'album/add_media', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    showResponseMessage(response.Message, 'alert-success');

                    /*$(response.Media).each(function(k1,v1){
                     $scope.albumMediaList.push(v1);
                     });*/
                    $scope.resetLoadMoreMediaData();
                    $scope.getAlbumMediaList($scope.AlbumGUID);
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                // HidePageLoader();
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.SubmitYoutubeVideo = function () {
            var Youtube = [];
            Youtube.push({'Url': $scope.modyoutube.VideoUrl, 'Caption': "", 'Description': "", 'Keyword': "", "VideoLength": "1"});
            var ModuleID = $("#hdn_module_id").val();
            var reqData = {
                AlbumGUID: $scope.AlbumGUID,
                Media: [],
                Youtube: Youtube,
                ModuleID: ModuleID
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'album/add_media', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    showResponseMessage(response.Message, 'alert-success');
                    $scope.resetLoadMoreMediaData();
                    $scope.getAlbumMediaList($scope.AlbumGUID);
                    $scope.modyoutube = {};
                    closeModalPopup('.upload-video');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.$on('deleteCommentEmit', function (obj, CommentGUID, AlbumGUID) {
            jsonData = {CommentGUID: CommentGUID};

            showConfirmBox("Delete Comment", "Are you sure, you want to delete this comment ?", function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'activity/deleteComment', jsonData, function (successResp) {
                        var response = successResp.data;
                        var aid = '';
                        var cid = '';
                        if (response.ResponseCode == 200) {
                            $($scope.albumDetails.Comments).each(function (ckey, cvalue) {
                                if ($scope.albumDetails.Comments[ckey].CommentGUID == CommentGUID) {
                                    cid = ckey;
                                    $scope.albumDetails.Comments.splice(cid, 1);
                                    $scope.albumDetails.NoOfComments = parseInt($scope.albumDetails.NoOfComments) - 1;
                                    return false;
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
            });
        });

        $scope.deleteAlbumMedia = function (MediaGUID)
        {
            var Media = [];
            var img_index = this.$index;
            Media.push({'MediaGUID': MediaGUID});
            var reqData = {AlbumGUID: $scope.AlbumGUID, Media: Media};

            showConfirmBox('Delete Confirmation', 'Are you sure you want to delete this media?', function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'album/delete_media', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            showResponseMessage(response.Message, 'alert-success');
                            $scope.TotalAlbumMedia--;
                            $scope.albumMediaList.splice(img_index, 1);
                        } else {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
                return;
            });

        };

        $scope.getYoutubeVideoThumb = function (videoid) {
            return 'http://img.youtube.com/vi/' + videoid + '/0.jpg';
        };

        $scope.parseYoutubeVideo = function (url) {
            var videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
            if (videoid != null) {
                return videoid[1];
            } else {
                return false;
            }
        };

        $scope.generateYoutubeVideoPlayer = function (youtubeid) {
            var video_html = '<iframe class="videos-slide" src="https://www.youtube.com/embed/' + youtubeid + '?feature=player_embedded" frameborder="0" allowfullscreen></iframe>';
            return $sce.trustAsHtml(video_html);
        };
        
//        Add media function for album
      
      $scope.validateFileSize = function(file) {
        var defer = $q.defer();
        var isResolvedToFalse = false;
        var mediaPatt = new RegExp("^image|video");
        var videoPatt = new RegExp("^video");
        if(videoPatt.test(file.type)) {
          if( file.size > 41943040 ) { // if video size > 41943040 Bytes = 40 Mb
            file.$error = 'size';
            file.$error = 'Size Error';
            file.$errorMessages = file.name + ' is too large.';
            defer.resolve(false);
            isResolvedToFalse = true;
          }
        } else {
          if( file.size > 4194304 ) { // if image/document size > 4194304 Bytes = 4 Mb
            file.$error = 'size';
            file.$error = 'Size Error';
            file.$errorMessages = file.name + ' is too large.';
            defer.resolve(false);
            isResolvedToFalse = true;
          }
        }
        
        if(!isResolvedToFalse) {
          defer.resolve(true);
        }
        return defer.promise;
      }

      $scope.albumMedias = [];
      $scope.isAlbumMediaUploading = false;
      var mediaCurrentIndex = 0;
      var fileCurrentIndex = 0;
      $scope.albumDetailLoaders = {};
      $scope.uploadAlbumMedias = function(files, errFiles, album_detail) {
          $scope.errFiles = errFiles;
          var promises = [];
          if(!errFiles.length) {
              var patt = new RegExp("^image|video");
              var videoPatt = new RegExp("^video");
              $scope.isAlbumMediaUploading = true;
              if(mediaCurrentIndex === 0){
                mediaCurrentIndex = ( $scope.media && ( Object.keys($scope.media).length > 0 ) ) ? Object.keys($scope.media).length : 0;
              }
              angular.forEach(files, function(fileToUpload, key) {
                  (function (file, mediaIndex, fileIndex) {
                      WallService.setFileMetaData(file);
                      var paramsToBeSent = {                            
                              Type: 'album',
                              DeviceType: 'Native',
                              qqfile: file
                      };
                      var fileType = 'media';
                      if(patt.test(file.type)) {
                          $scope.media['media-' + mediaIndex] = file;
                          $scope.media['media-' + mediaIndex]['mediaIndex'] = mediaIndex;
                          if ( album_detail == 1 ) {
                            $scope.albumDetailLoaders['media-' + mediaIndex] = { progress : false };
                          }
                          $scope.mediaCount = $scope.mediaIndexStart = ( $scope.media && ( Object.keys($scope.media).length > 0 ) ) ? Object.keys($scope.media).length : 0;
//                          $scope.MediaListPageNo++;
                      } else {
                          showResponseMessage('Please upload image/video files only.', 'alert-danger');
                          return false;
                      }                                            
                      
                      var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                      var promise = WallService.CallUploadFilesApi(
                      paramsToBeSent,
                      url,
                      function (response) {
                        var responseJSON = response.data;
                        if(fileType === 'media') {
                            $scope.medias = $scope.media;
                        } else {
                            
                        }
                        
                        var extraObj = {fileType : fileType, scopeObj : $scope.albumDetailLoaders, fileIndex : key, mediaIndex : key};
                        WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope, fileIndex : fileIndex, mediaIndex : mediaIndex, extraObj : extraObj}, {}, response);
                          if(fileType === 'media') {  
                              if(responseJSON.ResponseCode === 200) {
                                var PushData = responseJSON.Data;
                                $scope.media['media-' + mediaIndex].progress = true;
                                if ($scope.modalbum.Location.UniqueID !== undefined) {
//                                    PushData.Location = $scope.modalbum.Location;
                                    $scope.media['media-' + mediaIndex].Location = $scope.modalbum.Location;
                                }
                                if ( album_detail == 1 ) {
                                    delete $scope.albumDetailLoaders['media-' + mediaIndex];
                                    PushData.MediaType = $scope.MediaType;
                                    $scope.media['media-' + mediaIndex].data = PushData;
                                    //Add Media
                                    var media = [];
                                    media.push({MediaGUID: responseJSON.Data.MediaGUID, Caption: '', MediaType: $scope.MediaType});
                                    $scope.addExistingAlbumMedia(media);
                                    $scope.albumDetails.MediaCount++;
                                } else {
                                    //PushData.IsNew = 1;
                                    $scope.media['media-' + mediaIndex].data = PushData;
//                                  $scope.IsUploading = false;
                                }
                              } else {
                                  delete $scope.media['media-' + mediaIndex];
                                  $scope.mediaCount = Object.keys($scope.media).length;
                                  if ( album_detail == 1 ) {
                                    delete $scope.albumDetailLoaders['media-' + mediaIndex];
                                  }
                                  showResponseMessage(responseJSON.Message, 'alert-danger');
                              }
                          } else {
//                              if(responseJSON.ResponseCode === 200) {
//                                  $scope.files['file-' + fileIndex].data = responseJSON.Data;
//                                  $scope.files['file-' + fileIndex].progress = true;
//                              } else {
//                                  $scope.files.splice(fileIndex,1);
//                                  delete $scope.files['file-' + fileIndex];
//                                  $scope.fileCount = Object.keys($scope.files).length;
//                                  showResponseMessage(responseJSON.Message, 'alert-danger');
//                              }
                          }
                      },
                      function (response) {
                          if(fileType === 'media') {
                              delete $scope.media['media-' + mediaIndex];
                              $scope.mediaCount = Object.keys($scope.medias).length;
                              if ( album_detail == 1 ) {
                                delete $scope.albumDetailLoaders['media-' + mediaIndex];
                              }
                          } else {
//                              delete $scope.files['file-' + fileIndex];
//                              $scope.fileCount = Object.keys($scope.files).length;
                          }
                      },
                      function (evt) {
                        if(fileType === 'media') {
                            $scope.medias = $scope.media;
                        } else {
                            
                        }
                            var extraObj = {fileType : fileType, scopeObj : $scope.albumDetailLoaders, fileIndex : key, mediaIndex : key};
                          WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope, fileIndex : fileIndex, mediaIndex : mediaIndex, extraObj : extraObj}, evt);                          
                      });

                      promises.push(promise);

                  })(fileToUpload, mediaCurrentIndex, fileCurrentIndex);
                  if(patt.test(fileToUpload.type)) {
                    mediaCurrentIndex++;
                  } else {
                    fileCurrentIndex++;
                  }
              });
              $q.all(promises).then(function(data) {
                  $scope.isAlbumMediaUploading = false;
              });
          } else {
//            console.log(errFiles);
            var msg = '';
            angular.forEach(errFiles, function(errFile, key) {
                msg += '\n' + errFile.$errorMessages;
                promises.push(makeResolvedPromise(msg));
            });
            $q.all(promises).then(function(data) {
                showResponseMessage(msg, 'alert-danger');
            });
          }
      };
      
      $scope.removeAlbumMedia = function(index) {
        $scope.mediaToBeRemoved.push($scope.media['media-' + index]);
        delete $scope.media['media-' + index];
        $scope.mediaCount = $scope.mediaIndexStart = ( $scope.media && ( Object.keys($scope.media).length > 0 ) ) ? Object.keys($scope.media).length : 0;
      };

        $scope.getAlbumMediaDetails = function (AlbumGUID, MediaGUID)
        {
            var reqData = {AlbumGUID: AlbumGUID, MediaGUID: MediaGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'album/list_media', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.albumMediaList.unshift(response.Data[0]);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.addExistingAlbumMedia = function (media)
        {
            var reqData = {AlbumGUID: $scope.albumDetails.AlbumGUID, Media: media};
            WallService.CallPostApi(appInfo.serviceUrl + 'album/add_media', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.getAlbumMediaDetails($scope.albumDetails.AlbumGUID, media[0].MediaGUID);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        // Media Starts
        $scope.mediaDetails = [];
        $scope.$on('showMediaPopupEmit', function (obj, MediaGUID, Paging,IsAll) {
            var reqData = {MediaGUID: MediaGUID, Paging: Paging};
            var service = 'media/details';
            if(IsAll == 'all')
            {
                service = 'media/details_all';
            }
            WallService.CallPostApi(appInfo.serviceUrl + service, reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.mediaDetails = response.Data;
                    $scope.mediaDetails.YoutubeVideoId = '';
                    if ($scope.mediaDetails.MediaType == 'Youtube') {
                        $scope.mediaDetails.YoutubeVideoId = $scope.parseYoutubeVideo($scope.mediaDetails.ImageName);
                        ;
                    }
                    if ($scope.mediaDetails.MediaIndex == 0) {
                        $scope.mediadetailindex = 0;
                    } else {
                        $scope.mediadetailindex = $scope.mediaDetails.MediaIndex - 1;
                    }
                    $scope.mediaDetails['Comments'] = [];
                    var ShowAll = 0;
                    if ($('#ShowAll').length > 0)
                    {
                        ShowAll = $('#ShowAll').val();
                    }
                    var reqData2 = {MediaGUID: response.Data.MediaGUID, PageNo: 1, ShowAll: ShowAll};
                    WallService.CallPostApi(appInfo.serviceUrl + 'media/comments', reqData2, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $(response.Data).each(function (k, v) {
                                var append = true;
                                $($scope.mediaDetails.Comments).each(function (k1, v1) {
                                    if (v.CommentGUID == v1.CommentGUID) {
                                        append = false;
                                    }
                                });
                                if (append) {
                                    $scope.mediaDetails.Comments.push(v);
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                    $scope.ImageServerPath = image_server_path;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        });

        $scope.getAllMediaComments = function (MediaGUID) {
            var reqData = {MediaGUID: MediaGUID, PageNo: 1, PageSize: $scope.mediaDetails.NoOfComments};
            WallService.CallPostApi(appInfo.serviceUrl + 'media/comments', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.mediaDetails.Comments = response.Data;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.deleteMedia = function (MediaGUID, album_detail) {
            showConfirmBox("Delete Media", "Are you sure, you want to delete this media ?", function (e) {
                if (e) {
                    var reqData = {MediaGUID: MediaGUID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'media/delete', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if (album_detail == '1')
                            {
                                angular.forEach($scope.albumMediaList, function (val, key) {
                                    if (val.MediaGUID == MediaGUID)
                                    {
                                        $scope.albumMediaList.splice(key, 1);
                                        $scope.albumDetails.MediaCount--;
                                    }
                                });
                            } else
                            {
                                $scope.hideMediaPopup();
                            }
                        } else {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    }, function (error) {
                      // showResponseMessage('Something went wrong.', 'alert-danger');
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
                        fn: fn,
                        mediaLikeDetails : 1
                    });
                },
            });
        }
        $scope.likeDetailsEmitMedia = function (EntityGUID, EntityType) {
            likeDetails(EntityGUID, EntityType, 'likeDetailsEmit');
        };
        
        $scope.mediaLikeMessage = '';
        $scope.mediaTotalLikes = 0;
        $scope.mediaLikeDetails = [];
        $scope.getMediaLikeDetails = function (MediaGUID) {
            var reqData = {MediaGUID: MediaGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'media/like_details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (!$('#mediaTotalLikes').is(':visible')) {
                        $('#mediaTotalLikes').modal('show');
                        $('#MediaLikePageNo').val(0);
                        $scope.mediaLikeDetails = [];
                        if (response.Data == '') {
                            $scope.mediaLikeDetails = [];
                        }
                    }

                    if (response.Data !== '') {
                        $(response.Data).each(function (k, v) {
                            $scope.mediaLikeDetails.push(response.Data[k]);
                        });
                    }

                    console.log($scope.mediaLikeDetails);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.updateAlbumLen = function (boxId) {
            var Text_data = $("#" + boxId).val();
            var Text_data_length = Text_data.length;
            var max_length = $("#" + boxId).attr("maxcount");
            if (typeof max_length == 'undefined')
                max_length = 150;

            var album_desc_len = max_length - parseInt(Text_data_length);
            if (album_desc_len < 0)
                album_desc_len = 0;

            if (Text_data_length > max_length) {
                Text_data = Text_data.substring(0, max_length);
                $("#" + boxId).val(Text_data);
                album_desc_len = 0;
            }

            return album_desc_len + " characters remaining.";

        };

        $scope.getRemainingLikes = function (count) {
            var remain_likes = parseInt(count) - 1;

            var return_val = remain_likes;
            if (remain_likes > 1) {
                return_val = remain_likes + " others";
            } else {
                return_val = remain_likes + " other";
            }
            return return_val;
        }

        /*$scope.UTCtoTimeZone = function(date){
         var localTime  = moment.utc(date).toDate();
         return moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
         }*/

        $scope.date_format = function (date) {
            return GlobalService.date_format(date);

        };



        function passErrorRemove() {
            $('.passres').each(function (k) {
                if ($('#spnError' + $(this).attr('id')).html() != '') {
                    $('#spnError' + $(this).attr('id')).html('');

                    var mszLoca = $(this).attr('data-msglocation')
                    $('#' + mszLoca).html('');
                    $(this).parents('[data-error]').removeClass('hasError')
                }
            });
        }

        // Wall Functions
        $scope.parseYoutubeVideo = function (url) {
            var videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
            if (videoid != null) {
                return videoid[1];
            } else {
                return false;
            }
        }
    
        //Linkify
        $scope.textToLink = function (inputText) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                var replacedText, replacePattern1, replacePattern2, replacePattern3;
                replacedText = inputText.replace("<br>", " ||| ");

                replacedText = replacedText.replace(/</g, '&lt');
                replacedText = replacedText.replace(/>/g, '&gt');
                replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                replacedText = replacedText.replace(/lt&lt/g, '<');
                replacedText = replacedText.replace(/gt&gt/g, '>');

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
                    var youtubeid = $scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
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
                    var youtubeid = $scope.parseYoutubeVideo($1);
                    if (youtubeid) {
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
                    } else {
                        return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                    }
                });

                //Change email addresses to mailto:: links.
                replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                replacedText = replacedText.replace(" ||| ", "<br>");

                replacedText = replacedText;
                var repTxt = removeTags(replacedText);
                if (repTxt.length > 200) {
                    replacedText = '<span class="show-less">' + smart_sub_str(200, replacedText, false) + '</span><span class="show-more">' +
                                replacedText + '</span>  ';
                   // replacedText = '<span class="show-less">' + smart_substr(200, replacedText) + '... <a onclick="showMore(this);">See More</a></span><span class="show-more">' + replacedText + ' <a onclick="showLess(this)">See Less</a></span>';
                }

                replacedText = $sce.trustAsHtml(replacedText);
                var string = '<strong><span>Hii </span> <p>this is just a demo <span>string<span></p></strong>';

                return replacedText
            } else {
                return '';
            }
        }

        $scope.getCommentTitle = function (name, link, ModuleID, ModuleEntityGUID) {
            if (ModuleID == 18) {
                name = '<a class="taggedb loadbusinesscard" entityguid="' + ModuleEntityGUID + '" entitytype="page" href="' + base_url + 'page/' + link + '">' + name + '</a>';
            } else if (ModuleID == 3) {
                name = '<a class="taggedb loadbusinesscard" entityguid="' + ModuleEntityGUID + '" entitytype="user" href="' + base_url + link + '">' + name + '</a>';
            }
            return name;
        }

        $scope.viewAllComntEmit = function (AlbumGUID)
        {
            var reqData = {EntityType: 'Album', EntityGUID: AlbumGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/getAllComments', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    var tempComntData = response.Data;
                    $scope.albumDetails.Comments = [];

                    for (j in tempComntData) {
                        $scope.albumDetails.Comments.push(tempComntData[j]);
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        //section section and section guid (page, group, event and users)
        $scope.setModuleSection = function (section, sectionGUID)
        {
            $scope.moduleSection = section
            $scope.moduleSectionGUID = sectionGUID
        };

        $scope.albumBaseURL = function ()
        {
            switch ($scope.moduleSection) {
                case 'user':
                    return site_url + $scope.ProfileURL + '/media';
                    break;
                case 'group':
                    
                    var groupMediaUrl = $('#GroupMediaUrl').val();
                    return site_url + groupMediaUrl;
                    //return site_url + 'group/media/' + $rootScope.config_detail.ModuleEntityID;
                    break;
                case 'event':
                    return site_url + 'events/media/' + $scope.moduleSectionGUID;
                    break;
                case 'page':
                    return site_url + 'page/' + $scope.moduleSectionGUID + '/media';
                    break;
                case 'forumcategory':
                    return site_url + $('#cat_url').val()+'/media';
                    break;
            }

        }

        $scope.removeMedia = function (index)
        {
            $scope.media.splice(index, 1);
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


        $scope.$on('likeDetailsEmit', function (event, EntityGUID, EntityType) {
            $scope.LastLikeActivityGUID = EntityGUID;
            jsonData = {EntityGUID: EntityGUID, EntityType: EntityType, PageNo: $('#LikePageNo').val(), PageSize: 8};
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/getLikeDetails', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {

                    if (!$('#totalLikes').is(':visible')) {
                        $('#totalLikes').modal('show');
                        //$('#totalLikes').show();
                        $('#LikePageNo').val(0);
                        $scope.likeDetails = [];
                        if (response.Data == '') {
                            $scope.likeDetails = [];
                            $scope.totalLikes = 0;
                            $scope.likeMessage = 'No likes yet.';
                        }
                    }

                    if (response.Data !== '') {
                        //$scope.likeDetails = response.Data;
                        $(response.Data).each(function (k, v) {
                            $scope.likeDetails.push(response.Data[k]);
                        });
                        $scope.totalLikes = response.TotalRecords;
                        $scope.likeMessage = '';
                        $('#LikePageNo').val(parseInt($('#LikePageNo').val()) + 1);
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        });

        $scope.shareActivity = function () {
            $scope.ShareEntityType = 'Album';
            var Error = false;
            var sharetype = $("#sharetype").val();
            var EntityUserGUID = $("#ShareEntityUserGUID").val();
            var ModuleEntityGUID = $('#ShareModuleEntityGUID').val();
            if ($('#friend-src').val() == '') {
                EntityUserGUID = '';
            }
            if (sharetype == "friend-wall") {
                if (EntityUserGUID == "") {
                    Error = true;
                    showResponseMessage('Please select one of your  friend.', 'alert-danger');
                }
            }
            if (Error == false) {
                var PostContent = '';
                if ($('#PCnt').length > 0 && $('#PCnt').val().length > 0) {
                    PostContent = $('#PCnt').val();
                }
                if ($('#PCnt2').length > 0 && $('#PCnt2').val().length > 0) {
                    PostContent = $('#PCnt2').val();
                }
                if ($('.own-wall').is(':visible')) {
                    var shareVisibleFor = $('#shareVisibleFor').val();
                    if ($('#shareComment').hasClass('on')) {
                        $('#shareComment').removeClass('on');
                    }
                } else {
                    var shareCommentSettings = '';
                    var shareVisibleFor = '';
                }
                var shareCommentSettings = $('#shareCommentSettings').val();

                var id = $('[data-guid="act-' + ModuleEntityGUID + '"]').attr('id');
                var element = $('#' + id + ' .post-as-data');

                var reqData = {
                    EntityGUID: ModuleEntityGUID,
                    ModuleEntityGUID: EntityUserGUID,
                    ModuleID: 3,
                    PostContent: PostContent,
                    Commentable: shareCommentSettings,
                    Visibility: shareVisibleFor,
                    EntityType: $scope.ShareEntityType,
                    PostAsModuleID: element.attr('data-module-id'),
                    PostAsModuleEntityGUID: element.attr('data-module-entityid')
                };
                WallService.CallApi(reqData, 'activity/sharePost').then(function (response) {
                    if (response.ResponseCode == 200) {
                        showResponseMessage('Post has been shared successfully.', 'alert-success');
                        $('#sharemodal').modal('toggle');
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }

                });
                $(".ShareEntityType:checked").prop('checked', false);
            }
        };

        $scope.shareEmit = function (AlbumGUID) {
            $scope.singleActivity = [];
            if (!$scope.$$phase) {
                $scope.$apply();
            }
            $('#ShareModuleEntityGUID').val(AlbumGUID);
            $('#ShareEntityUserGUID').val($('#module_entity_guid').val());

            $('.about-name input').val('');
            $('#PCnt').val('');
            $('#sharemodal .text-field-select select').val('own-wall');
            $('#sharemodal .text-field-select select').trigger("chosen:updated");
            if (!$('.about-name').hasClass('hide')) {
                $('.about-name').addClass('hide');
            }
        };

    }]);

app.directive('lightgallery', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            if (scope.$last) {
                setTimeout(function () {
                    element.parent().lightGallery();
                }, 3000);
                // ng-repeat is completed
            }
        }
    };
});

$(function () {

    //remove media
//    $('#albummediaul').delegate('.remove-button-media','click',function(){
//       $(this).parent('li').remove();
//       var picId = $(this).attr('data-rel');
//       var albumScope = angular.element('#AlbumCtrl').scope();
//       //albumScope.media.splice(picId, 1);
//       delete(albumScope.media[picId]);
//    });

    //makeCover

    $('#albummediaul').delegate('.makeCover', 'click', function () {
        $('.makeCover').removeClass('CoverPic');
        $(this).addClass('CoverPic');
    });
});

function getExtension(filename) {
    var parts = filename.split('.');
    return parts[parts.length - 1];
}
function isImage(filename) {
    var ext = getExtension(filename);
    switch (ext.toLowerCase()) {
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'bmp':
        case 'png':
            //etc
            return true;
    }
    return false;
}

function isVideo(filename) {
    var ext = getExtension(filename).toLowerCase();
    console.log(ext);
    if ($.inArray(ext, ['avi', 'AVI', 'flv', 'FLV', 'mpeg', 'MPEG', 'mpg', 'MPG', 'wmv', 'WMV', 'swf', 'SWF', 'asf', 'ASF', 'mov', 'mp4', 'MP4', 'ogg', 'OGG', 'webm', 'WEBM']) == -1) {
        return true;
    }
}
