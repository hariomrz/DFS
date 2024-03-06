
!(function (app, angular) {
    app.controller('DiscoverCtrl', DiscoverCtrl);

    function DiscoverCtrl($http, $q, $scope, $rootScope, $window, apiService, CommonService, UtilSrvc) {

        var adminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $scope.tag_categories = [];
        $scope.current_tag_category_id = 0;
        $scope.current_tag_category = {Tags:[], Name:''};
        
        $scope.numPerPage = 20;
        $scope.currentPage = 1;
        $scope.maxSize = 3;
              
        function getTagCategoryList(reqData) {
            
            $http.post(base_url + 'api/tag/get_tag_categories', reqData).success(function (response) {                
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    onTagCategoryListSuccess(reqData, response);
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
        }
        
        function onTagCategoryListSuccess(reqData, response) {
                       
            $scope.tag_categories = response.Data;
            $scope.numPerPage = reqData.PageSize;
            $scope.currentPage = reqData.PageNo;
            
            
            angular.forEach($scope.tag_categories, function (category, index) {                
                $scope.tag_categories[index]['TagsStr'] = formatTags(category.Tags);
            });

            function formatTags(tags) {
                var tagStr = [], tagMoreStr = [], allowedNoTags = 3, tagMoreStrTitle = '';
                angular.forEach(tags, function (tag) {
                    if (tagStr.length < allowedNoTags) {
                        tagStr.push(tag.Name); 
                    } else {
                        tagMoreStr.push(tag.Name);
                        tagMoreStrTitle += '<li><span>'+tag.Name+'</span></li>';                       
                    }
                });
                
                tagMoreStrTitle = '<div class="more-tag"><ul class="tags-list clearfix">'+tagMoreStrTitle+'</ul></div>';
                
                return {
                    tagStr: tagStr,
                    tagMoreStr: tagMoreStr,
                    tagMoreStrTitle : tagMoreStrTitle
                };
            }            
        }
       
        //Get no. of pages for data
        $scope.numPages = function () {
            return Math.ceil($scope.tag_categories.length / $scope.numPerPage);
        };

        $scope.getThisPage = function () {
            var requestObj = {
                PageNo: $scope.currentPage
            };

            getTagCategoryList(requestObj);
        }
                
        $scope.onTagsGet = function(query, entity_type_set_val) {
            
            var url = base_url + 'api/tag/get_entity_tags?TagType=ACTIVITY&SearchKeyword=' + query;
            
            //var url = base_url + 'api/tag/get_entity_tags?EntityType=USER&SearchKeyword=' + query;
            
            return $http.get(url).then(function(response, status) {
                var tags = [];
                angular.forEach(response.data.Data, function(tagObj){
                    tagObj.text = tagObj.Name;
                    tags.push(tagObj);
                });
                
                return tags; 
            });
            
            
        }
        
        $scope.loadTags = function($query) {
            return $http.get(base_url + 'api/tag/get_entity_tags?EntityType=ACTIVITY&TagType=ACTIVITY&SearchKeyword=' + $query, { cache: false }).then(function(response) {
                var tagList = response.data.Data;
                return tagList.filter(function(flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };
        
        
       
        $scope.delete_tag_category = function(tag_category_id) {
            showAdminConfirmBox('Delete Tag Category','Are you sure you want to delete this category ?',function(e){
                if(e)
                {
                    var reqData = { TagCategoryID: tag_category_id };
                    $http.post(base_url + 'api/tag/delete_tag_category', reqData).success(function (response) { 
                        if (response.ResponseCode == 200) {
                            angular.forEach($scope.tag_categories, function(val, key) {
                                if(val.TagCategoryID == tag_category_id) {
                                    $scope.tag_categories.splice(key,1);
                                }
                            });
                        } else {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                    });
                }
            });
        }  
        $scope.is_category_order = 0;
        $scope.manage_category_order = function () {
            $scope.is_category_order = 1;
        }
        $scope.cancel_category_reorder = function () {
            $scope.is_category_order = 0;
        }
        $scope.change_category_order = function () {
            var reqData = {OrderData: []};
            var count = 1;
            angular.forEach($scope.tag_categories, function (val, key) {
                reqData.OrderData.push({TagCategoryID: val.TagCategoryID, DisplayOrder: count});
                count++;
            });
            $http.post(base_url + 'api/tag/change_category_tag_order', reqData).success(function (response) {                  
                if (response.ResponseCode == 200) {
                    getTagCategoryList({});
                    $scope.is_category_order = 0;
                } else {
                    ShowErrorMsg(response.Message);
                    return;
                }
            }).error(function (data) {
                ShowWentWrongError();
            });
        }
        
        $scope.save_tag_category = function(btn) {
            $('.'+btn).attr('disabled',true);
            var reqData = {};
            reqData['Name'] = $scope.current_tag_category.Name;
            reqData['TagsList'] = $scope.current_tag_category.Tags;            
            $http.post(base_url + 'api/tag/save_tag_category', reqData).success(function (response) {  
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg('Tag category saved successfully');
                    console.log(btn);
                    $('.'+btn).attr('disabled',false);
                    $('#'+btn).modal('hide');
                    getTagCategoryList({});
                    
                } else {
                     $('.'+btn).attr('disabled',false);
                    ShowErrorMsg(response.Message);
                    return;
                }
            }).error(function () {
                 $('.'+btn).attr('disabled',false);
                ShowWentWrongError();
            });
        }
        
        $scope.set_current_tag_category = function(tag_category) {
            $scope.current_tag_category_id = tag_category.TagCategoryID;
            $scope.current_tag_category = tag_category;
        }

        $scope.clear_current_tag_category = function() {
            $scope.current_tag_category_id = 0;
            $scope.current_tag_category = {};
        }
        
        // Init process
        function initFn() {
            getTagCategoryList({});
            //$(document).on('click', '.userCheckBox', onChecked);            
        }
        initFn();
    }
    DiscoverCtrl.$inject = ['$http', '$q', '$scope', '$rootScope', '$window', 'apiService', 'CommonService', 'UtilSrvc'];

})(app, angular);
