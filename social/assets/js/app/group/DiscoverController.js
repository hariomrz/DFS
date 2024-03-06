!(function (app, angular) {
    angular.module('DiscoverModule',[])
    .controller('DiscoverCtrl',DiscoverCtrl);

    DiscoverCtrl.$inject = ['$location', '$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService','lazyLoadCS', '$sce'];


    function DiscoverCtrl($location, $rootScope, $scope, appInfo, $http, profileCover, WallService,lazyLoadCS, $sce)
    {
        $scope.ShowCategory = 0;
        $scope.CategoryName = '';
        $scope.SortFilterLabel = 'Activity Date';
        $scope.SortBy = 'LastActivity';
        $scope.suggested_group = AssetBaseUrl + 'partials/widgets/suggested_groups.html'+$scope.app_version;
        $scope.Order = 'DESC';

        $scope.categoryConfig =         {
            method: {},
          infinite: true,
          slidesToShow: 4,
          slidesToScroll: 1,
          adaptiveHeight: true,
          responsive: [{
              breakpoint: 1200,
              settings: {
                slidesToShow: 3
              }
            },
            {
              breakpoint: 992,
              settings: {
                slidesToShow: 2
              }
            },
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 1
              }
            }
          ]
        }

        $scope.updateShowCategory = function(i)
        {
            $scope.ShowCategory = i;
        }

        var cat_id = 0;
        var cat_name = '';
        var first_time = 1;
        $scope.getDiscoverList = function()
        {
            $rootScope.IsLoading = true;
            var reqData = {};
            WallService.CallPostApi(appInfo.serviceUrl + 'group/get_discover_list', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    var slug_data = $location.path().split('/');
                    if(slug_data.length==5)
                    {
                        cat_id = slug_data[4];
                    }
                    angular.forEach(response.Data,function(val,key){
                        response.Data[key]['AllGroups'] = [];
                        response.Data[key]['Groups'] = response.Data[key].GroupDetail;
                        if(cat_id == val.CategoryID)
                        {
                            cat_name = val.Name;
                        }
                    });
                    $scope.DiscoverCategories = response.Data;
                }
                if(slug_data.length==5 && first_time == 1)
                {
                    $scope.viewAllGroups(cat_id,cat_name,20);
                    first_time++;
                }
                $rootScope.IsLoading = false;
            }, function (error) {
                $rootScope.IsLoading = false;
            });
        }

        $scope.detailPageNo = 1;
        $scope.totalCategoryGroups = 0;
        $scope.ShowLoaderDiscover = false;
        $scope.viewAllGroups = function(category_id,category_name,page_size)
        {
            if($scope.ShowCategory == '0')
            {
                $scope.detailPageNo = 1;
                $rootScope.IsLoading = true;
            }
            $scope.ShowCategory = category_id;
            $scope.CategoryName = category_name;
            var reqData = {Order:$scope.Order,OrderBy:$scope.SortBy,PageNo:$scope.detailPageNo,PageSize:page_size,CategoryIDs:category_id};
            $scope.ShowLoaderDiscover = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'group/category_group', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach($scope.DiscoverCategories,function(val,key){
                        if(val.CategoryID == category_id)
                        {
                            if($scope.detailPageNo == 1)
                            {
                                $scope.DiscoverCategories[key].AllGroups = response.Data;
                            }
                            else
                            {
                                angular.forEach(response.Data,function(v,k){
                                    $scope.DiscoverCategories[key].AllGroups.push(v);
                                });
                            }
                            $scope.changeGroupData('AllGroups');
                        }
                    });
                    $scope.totalCategoryGroups = response.TotalRecords;
                    $scope.detailPageNo++;
                    $scope.ShowLoaderDiscover = false;
                    $rootScope.IsLoading = false;
                }
                else
                {
                    $scope.ShowLoaderDiscover = false;
                    $rootScope.IsLoading = false;
                }
            }, function (error) {
            });
        }

        $scope.changeGroupData = function(key)
        {
            angular.forEach($scope.DiscoverCategories,function(v,k){
                $scope.DiscoverCategories[k]['Groups'] = v[key];
            });
        }

        $scope.showAllCategories = function()
        {
            $scope.ShowCategory = 0;
            $scope.CategoryName = '';
            $scope.changeGroupData('GroupDetail');
        }

        $scope.parseYoutubeVideo = function (url) {
            var videoid = url.match(/((https?|ftps?):\/\/?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([a-zA-Z0-9-]+)+)(?![^<>]*?>|[^<>]*?<\/)/);
            if (videoid != null) {
                return videoid[3];
            } else {
                return false;
            }
        }

        $scope.textToLink = function (inputText, onlyShortText, count) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                inputText = inputText.toString();
                inputText = inputText.replace(new RegExp('contenteditable', 'g'), 'contenteditabletext');
                var replacedText, replacePattern1, replacePattern2, replacePattern3;
                inputText = inputText.replace(new RegExp('contenteditable', 'g'), "contenteditabletext");
                replacedText = inputText.replace("<br>", " ||| ");

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
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return href;
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
                        return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        return href;
                    }

                });
                //Change email addresses to mailto:: links.
                replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');
                
                replacedText = replacedText.replace("href=\"<iframe width=\"420\" height=\"315\" src=\"","href=\"");
                replacedText = replacedText.replace("frameborder=\"0\" allowfullscreen></iframe>","");

                replacedText = replacedText.replace(" ||| ", "<br>");
                replacedText = $scope.checkTaggedData(replacedText);
                var repTxt = $scope.removeTags(replacedText);
                var totalwords = 200;
                if ($('#IsForum').length > 0)
                {
                    totalwords = 80;
                    if (count)
                    {
                        totalwords = count;
                    }
                }

                if ($scope.IsSinglePost)
                {
                    replacedText = $sce.trustAsHtml(replacedText);
                    return replacedText
                }

                if (repTxt && (repTxt.length > totalwords)) {
                    var smart_text = '<span class="show-less">' + smart_sub_str(totalwords, replacedText, onlyShortText) + '</span>';
                    if (!onlyShortText) {
                        replacedText = smart_text + '<span class="show-more">' +replacedText + '</span>';
                    } else {
                        replacedText = smart_text;
                    }
                }
                replacedText = $sce.trustAsHtml(replacedText);
                return replacedText
            } else {
                return '';
            }
        }

        $scope.checkTaggedData = function(replacedText) {
            if (replacedText) {
                var regex = /<a\shref[\s\S]*>([\s\S]*)<\/a>/g,
                        matched,
                        highLightedText;
                if ((matched = regex.exec(replacedText)) !== null) {
                    replacedText = replacedText.replace(matched[0], '{{:*****:}}');
                    replacedText = $scope.getHighlighted(replacedText);
                    if (matched[1]) {
                        highLightedText = $scope.getHighlighted(matched[1]);
                        matched[0] = matched[0].replace(matched[1], highLightedText);
                    }
                    replacedText = replacedText.replace('{{:*****:}}', matched[0]);
                    return replacedText;
                } else {
                    return $scope.getHighlighted(replacedText);
                }
            }
        }

        $scope.removeTags = function(txt) {
            if (txt) {
                var rex = /(<([^>]+)>)/ig;
                return txt.replace(rex, "");
            } else {
                return txt;
            }
        }

        $scope.getHighlighted = function (str) {
            var advancedSearchKeyword = angular.element('#advancedSearchKeyword').val();
            if ($('#BtnSrch i').hasClass('icon-removeclose') || advancedSearchKeyword) {

                if (!advancedSearchKeyword) {
                    advancedSearchKeyword = $('#srch-filters').val();
                }

                if (typeof str === 'undefined') {
                    str = '';
                }
                if (str.length > 0 && advancedSearchKeyword.length > 0) {
                    str = str.replace(new RegExp(advancedSearchKeyword, 'gi'), "<span class='highlightedText'>$&</span>");
                }
                return str;
            } else {
                return str;
            }
        }

        $scope.get_members_talking = function (members)
        {
            if (!members)
            {
                return;
            }
            var total_members_count = members.length;
            var loopCount = 2;
            var html = '';
            var count = 0;
            if (total_members_count <= loopCount)
            {
                angular.forEach(members, function (val, key) {
                    count++;
                    html += '<span class="text-brand">' + val.Name + '</span>'
                    if (total_members_count == count)
                    {
                        if (total_members_count == 1)
                        {
                            html += ' <span> is talking </span> ';
                        } else
                        {
                            html += ' <span> are talking </span> ';
                        }
                    } else if (total_members_count - 1 == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + '</span>';
                    } else
                    {
                        html += '<span >,</span> ';
                    }
                });
            }else{
                angular.forEach(members, function (val, key) {
                    if (count > loopCount + 1) {
                        return;
                    }
                    count++;
                    if (count <= loopCount)
                    {
                        html += '<span class="text-brand">' + val.Name + '</span>'
                    }
                    if (loopCount + 1 == count)
                    {
                        if (total_members_count - loopCount == 1)
                        {
                            html += ' <span> other is talking </span> ';
                        } else
                        {
                            html += ' <span>others are talking </span> ';
                        }
                    } else if (loopCount == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + (total_members_count - loopCount) + '</span>';
                    } else if (count < loopCount)
                    {
                        html += '<span >,</span> ';
                    }
                });
            }
            return html;
        };

        $scope.lastSortBy = '';
        $scope.sortBy = function(sortBy,sortByLabel,category_id,category_name)
        {
            $scope.SortBy = sortBy;
            $scope.SortFilterLabel = sortByLabel;
            if(sortBy == $scope.lastSortBy)
            {
                if($scope.Order == 'DESC')
                {
                    $scope.Order = 'ASC';
                }
                else
                {
                    $scope.Order = 'DESC';
                }
            }
            else
            {
                if($scope.SortBy == 'Popularity')
                {
                    $scope.Order = 'DESC';   
                }
                if($scope.SortBy == 'GroupName')
                {
                    $scope.Order = 'ASC';   
                }
                if($scope.SortBy == 'LastActivity')
                {
                    $scope.Order = 'DESC';   
                }
            }
            $scope.detailPageNo = 1;
            $scope.lastSortBy = sortBy;
            $scope.viewAllGroups(category_id,category_name,20);
        }

        $scope.requestGroupInvite = function (GroupGUID, Action,obj)
        {
            var UserGUID = $('#UserGUID').val();

            reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};

            WallService.CallPostApi(appInfo.serviceUrl + 'group/request_invite', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    obj.Permission.IsInviteSent = true;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.cancelGroupInvite = function (GroupGUID, Action,obj)
        {
            var UserGUID = $('#UserGUID').val();

            reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};

            WallService.CallPostApi(appInfo.serviceUrl + 'group/cancel_invite', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    obj.Permission.IsInviteSent = false;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.joinPublicGroup = function (GroupGUID, Action,obj)
        {
            var userProfile = angular.element($('#UserProfileCtrl')).scope();

            reqData = {GroupGUID: GroupGUID};

            WallService.CallPostApi(appInfo.serviceUrl + 'group/join', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    obj.Permission.IsActiveMember = true;
                    obj.Permission.DirectGroupMember = true;
                }
            }, function (error) {
            });
        }

        $scope.groupDropOutAction = function (GroupGUID, Action,obj)
        {
            var UserGUID = $('#UserGUID').val();

            reqData = {GroupGUID: GroupGUID, ModuleEntityGUID: UserGUID, ModuleID: 3};

            showConfirmBox('Leave Group', 'Are you sure you want to leave this group?', function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/leave', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            obj.Permission.IsActiveMember = false;
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
                return;
            });
        };

        $scope.$on('onDefaultState', function (event, data) {
            $scope.showAllCategories();
        });

        $scope.callIsotope = function()
        {
            /*setTimeout(function(){
                var $container = $('#bootIsotope').isotope({
                    itemSelector : '.items',
                    isFitWidth: true
                });

                $(window).smartresize(
                    function(){
                        $container.isotope({
                        columnWidth: '.items'
                    });
                });
            },1000);*/

            setTimeout(function(){
                if($(window).width() > 991){      
                    $('.masonry').masonry({        
                      itemSelector: '.masonry-items'
                    });
                    $(window).resize(function() {
                      $('.masonry').masonry({
                        itemSelector: '.masonry-items'
                      });
                    }); 
                } 
                
            },1000);
  
        }

        $scope.get_category_url_slug = function(category)
        {
            var name = category.Name.replace(/ /g,'');
            name = name.toLowerCase();
            return name+'/'+category.CategoryID;
        }

    }


})(app, angular);