!(function () {
    'use strict';
    app.controller('ContentSearchController', ['$rootScope', '$scope', '$log', '$window', '$q', 'appInfo', 'WallService',
        function ($rootScope, $scope, $log, $window, $q, appInfo, WallService) {
            var ContentSearch = this;
            var lookForMore = [];            
            
            ContentSearch.sliderOptions = {
                min: 1,
                max: 100,
                step: 1,
                precision: 2,
                orientation: 'horizontal', // vertical
                handle: 'round', //'square', 'triangle' or 'custom'
                tooltip: 'show', //'hide','always'
                tooltipseparator: ':',
                tooltipsplit: false,
                enabled: true,
                naturalarrowkeys: false,
                range: false,
                ngDisabled: false,
                reversed: false
            };
            ContentSearch.ImageServerPath = image_server_path;
            
            ContentSearch.defaultSearchOptions = function () {
                
                
                ContentSearch.PostedByLookedMore = [];
                ContentSearch.CreatedLastUpdate = 1;
                ContentSearch.ModifiedLastUpdate = 1;
                ContentSearch.created = 'Anytime';
                ContentSearch.updated = 'updatedAnytime';
                ContentSearch.searchFor = {posts: true, comments: true};
                ContentSearch.requestPayload = {
                    SearchKey: '',
                    StartDate: '',
                    EndDate: '',
                    UpdatedStartDate: '',
                    UpdatedEndDate: '',
                    IsMediaExists: '2',
                    ViewEntityTags: '1',
                    SearchOnlyFor: [1, 2],
                    IncludeArchive: 1,
                    IncludeAttachment: 1,
                    IncludeUserAndGroup: 1,
                    PostedBy: '0'
                };
                
                ContentSearch.requestPayloadDefault = angular.copy(ContentSearch.requestPayload);
            }
            
            ContentSearch.defaultSearchOptions();
                        
            ContentSearch.ResetFilter = function() {
                ContentSearch.defaultSearchOptions();
                ContentSearch.searchForContent();
            }
            
            ContentSearch.isDefaultFilter = function() {
                                
                for(var indexKey in ContentSearch.requestPayloadDefault) {
                    
                    if(indexKey == 'SearchOnlyFor') {
                        if(!(ContentSearch.requestPayload[indexKey][0] == 1 && ContentSearch.requestPayload[indexKey][1] == 2)) {
                            return false;
                        }
                        
                        continue;
                    }
                    
                    if(ContentSearch.requestPayload[indexKey] != ContentSearch.requestPayloadDefault[indexKey]) {
                        return false;
                    }
                }
                
                var isAltered = ContentSearch.searchFor.posts != true || ContentSearch.searchFor.comments != true || ContentSearch.CreatedLastUpdate != 1
                        || ContentSearch.created != 'Anytime' || ContentSearch.updated != 'updatedAnytime' || ContentSearch.PostedByLookedMore.length;
                
                if(isAltered) {
                    return false;
                }
                
                
                
                return true;
                
            }

            ContentSearch.makeDateRangeToSearch = function (scenario, selection) {
                if (selection && (selection === 'Anytime')) {
                    if (scenario && (scenario === 'createdDate')) {
                        ContentSearch.requestPayload.StartDate = '';
                        ContentSearch.requestPayload.EndDate = '';
                    } else {
                        ContentSearch.requestPayload.UpdatedStartDate = '';
                        ContentSearch.requestPayload.UpdatedEndDate = '';
                    }
                    ContentSearch.searchForContent();
                } else if (selection && (selection === 'LastUpdate')) {
                    var range = createRangeByDay(1);
                    if (scenario && (scenario === 'createdDate')) {
                        ContentSearch.requestPayload.StartDate = range.startDate;
                        ContentSearch.requestPayload.EndDate = range.endDate;
                    } else {
                        ContentSearch.requestPayload.UpdatedStartDate = range.startDate;
                        ContentSearch.requestPayload.UpdatedEndDate = range.endDate;
                    }
                    ContentSearch.searchForContent();
                } else if (selection && (selection === 'Between')) {
                    if ((scenario && (scenario === 'createdDate') && (ContentSearch.requestPayload.StartDate || ContentSearch.requestPayload.StartDate)) || (scenario && (scenario === 'modifiedDate') && (ContentSearch.requestPayload.UpdatedStartDate || ContentSearch.requestPayload.UpdatedEndDate))) {
                        ContentSearch.searchForContent();
                    }
                }

            };
            
            

            /*function createRangeByDay(dayToAdd) {
             if (!dayToAdd) {
             dayToAdd = 1;
             }
             var now = new Date();
             var daysLater = new Date(now);
             daysLater.setDate(daysLater.getDate() - dayToAdd);
             return {
             startDate: daysLater.getFullYear() + '-' + (daysLater.getMonth() + 1) + '-' + daysLater.getDate(),
             endDate: now.getFullYear() + '-' + (now.getMonth() + 1) + '-' + now.getDate()
             };
             }*/

            /* Change from waseem start */

            function createDateFormat(dateObject) {
                return dateObject.getFullYear() + '-' + ('0' + (dateObject.getMonth() + 1)).slice(-2) + '-' + ('0' + dateObject.getDate()).slice(-2);
            }

            function createRangeByDay(dayToAdd) {
                if (!dayToAdd) {
                    dayToAdd = 1;
                }
                var now = new Date();
                var daysLater = new Date(now);
                daysLater.setDate(daysLater.getDate() - dayToAdd);

                return {
                    startDate: createDateFormat(daysLater),
                    endDate: createDateFormat(now)
                };
            }

            /* Change from waseem ends */


            ContentSearch.updateToSearchFor = function () {
                var SearchOnlyFor = angular.copy(ContentSearch.requestPayload.SearchOnlyFor);
                var postIndex = SearchOnlyFor.indexOf(1);
                var commentIndex = SearchOnlyFor.indexOf(2);
                if (ContentSearch.searchFor.posts && (postIndex === -1)) {
                    SearchOnlyFor.push(1); //1-> Post,
                } else if (!ContentSearch.searchFor.posts && postIndex > -1) {
                    SearchOnlyFor.splice(postIndex, 1);
                }

                if (ContentSearch.searchFor.comments && (commentIndex === -1)) {
                    SearchOnlyFor.push(2); //1-> Post,
                } else if (!ContentSearch.searchFor.comments && commentIndex > -1) {
                    SearchOnlyFor.splice(commentIndex, 1);
                }
                ContentSearch.requestPayload.SearchOnlyFor = angular.copy(SearchOnlyFor);
                ContentSearch.searchForContent();
            };

            ContentSearch.searchUsers = function ($query) {
                var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 10};
                if ($query) {
                    var url = appInfo.serviceUrl + 'search/user';
                    return WallService.CallPostApi(url, requestPayload, function (successResp) {
                        var response = successResp.data;
                        var promises = [];
                        var userList = [];
                        var addedUser = [];
                        if ((response.ResponseCode === 200) && (response.Data.length > 0)) {
                            angular.forEach(response.Data, function (user, key) {
                                promises.push(makeResolvedPromise(user, key).then(function (newUser, newKey) {
                                    userList.splice(newKey, 0, newUser);
                                }));
                            });
                            return $q.all(promises).then(function (data) {
                                return userList.filter(function (flist) {
//                  $log.log(flist);
                                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                                });
                            });
                        } else {
                            return userList;
                        }
                    }, function (error) {
                        return [];
                    });
                } else {
                    return [];
                }
            };

            function makeResolvedPromise(userData, key) {
                var deferred = $q.defer();
                var name = '';
                name = (userData && userData.FirstName && (userData.FirstName != '')) ? userData.FirstName : '';
                name += (userData && userData.LastName && (userData.LastName != '')) ? ' ' + userData.LastName : '';
                if (userData.ProfilePicture && (userData.ProfilePicture != '')) {
                    userData['profileImageServerPath'] = ContentSearch.ImageServerPath + 'upload/profile/220x220/' + userData.ProfilePicture;
                } else {
                    userData.ProfilePicture = 'user_default.jpg';
                    userData['profileImageServerPath'] = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                }
                userData['Name'] = name;
                deferred.resolve(userData, key);
                return deferred.promise;
            }
            ;

            function makeResolvedPromise1(data, key) {
                var deferred = $q.defer();
                var newData = [];
                var name = '';
                name = (data && data.FirstName && (data.FirstName != '')) ? data.FirstName : '';
                name += (data && data.LastName && (data.LastName != '')) ? ' ' + data.LastName : '';
                newData['Name'] = name;
                newData['UserGUID'] = data.UserGUID;
                deferred.resolve(newData, key);
                return deferred.promise;
            }
            ;

            ContentSearch.rangeSliderFormatter = function (scenario, value) {
                var range = createRangeByDay(value);
                if (scenario && (scenario === 'createdDate')) {
                    ContentSearch.requestPayload.StartDate = range.startDate;
                    ContentSearch.requestPayload.EndDate = range.endDate;
                } else {
                    ContentSearch.requestPayload.UpdatedStartDate = range.startDate;
                    ContentSearch.requestPayload.UpdatedEndDate = range.endDate;
                }
                var days = (value > 1) ? 'Days' : 'Day';
                return  value + ' ' + days;
            };


            ContentSearch.makeDuringLastValue = function (value) {
                var days = (value > 1) ? 'Days' : 'Day';//Fix date range
                return  value + ' ' + days;
            };


            ContentSearch.checkPostedBy = function (action, taggedData) {
                var requestPayLoad = angular.copy(ContentSearch.requestPayload);
                if ((ContentSearch.requestPayload.PostedBy == 4) && action && taggedData) {
                    if (taggedData && taggedData.UserGUID) {
                        var tagIndex = lookForMore.indexOf(taggedData.UserGUID);
                        if ((action === 'add') && (tagIndex === -1)) {
                            lookForMore.push(taggedData.UserGUID);
                        } else if ((action === 'remove') && (tagIndex > -1)) {
                            lookForMore.splice(tagIndex, 1);
                        }
                    }
                    requestPayLoad.PostedBy = lookForMore;
                } else {
                    lookForMore = [];
                    ContentSearch.PostedByLookedMore = [];
                }
                $scope.GetwallPost(false, false, requestPayLoad);
            };

            ContentSearch.searchForContent = function () {
                var requestPayLoad = angular.copy(ContentSearch.requestPayload);
                if (ContentSearch.requestPayload.PostedBy == 4) {
                    requestPayLoad.PostedBy = lookForMore;
                } else {
                    ContentSearch.requestPayload.PostedBy = requestPayLoad.PostedBy = 0;
                }
                $scope.GetwallPost(false, false, requestPayLoad);
                $(document).scrollTop(0);
            };



        }]);

})();