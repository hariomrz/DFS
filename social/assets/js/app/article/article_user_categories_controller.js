/**** Article Controller ***/

!(function (app, angular) {

    function ArticleUsrCatCtrl($scope, $http, $q) {
        $scope.categories = [];
        $scope.selected_categories;
        $scope.BaseUrl = base_url;
        //$scope.ImageServerPath = image_server_path;
        $scope.selected_categories_loading = 1;
        $scope.categories_loading = 0;
        $scope.boxSelectedCategories = [];
        $('.loader-fad,.loader-view').show();
        var pageNo = 1;
        $scope.modal_show_all_cats = 1;
        $scope.totalCats = 0;
        $scope.searchCatBox = '';

        getForumCategories(1, '', 1);

        $(function () {

        });

        var boxSelectedCategories = {};

        function getForumCategories(onlySelected, search, refreshCat) {
            if (settings_data.m34 == '0') {
                return false;
            }
            var propertyName = (onlySelected) ? 'selected_categories' : 'categories';
            var loaderPropName = (onlySelected) ? 'selected_categories_loading' : 'categories_loading';
            var requestData = {
                OnlySelected: onlySelected,
                search: (search) ? search : ''
            };
            if (!onlySelected) {
                requestData['PageNo'] = pageNo;
            }
            $scope[loaderPropName] = 1;
            $('.loader-fad,.loader-view').show();
            $http.post(base_url + 'api/forum_user_categories/list', requestData).then(function (response) {
                var response = response.data;
                if (response.ResponseCode == 200) {
                    catSuccessCall(response, onlySelected, search, propertyName, refreshCat);
                }
                $scope[loaderPropName] = 0;


                if (!onlySelected && pageNo > 1) {
                    var deferred = $q.defer();
                    deferred.resolve();
                }

                $('.loader-fad,.loader-view').hide();

            }, function (data) {
                $scope[loaderPropName] = 0;
                $('.loader-fad,.loader-view').hide();
            });
        }

        function catSuccessCall(response, onlySelected, search, propertyName, refreshCat) {
            var responseCats = [];
            responseCats = response.Data.entities;
            angular.forEach(responseCats, function (val, index) {
                responseCats[index] = preProcessCategory(val, onlySelected);
            });

            if (search && onlySelected) {
                selectOlderCats(onlySelected, responseCats, refreshCat);
                $scope.boxSelectedCategories = responseCats;
                return;
            }

            if (!onlySelected && pageNo > 1) {
                $scope[propertyName] = $scope[propertyName].concat(responseCats);
            } else {
                $scope[propertyName] = responseCats;
            }

            selectOlderCats(onlySelected, responseCats, refreshCat);

            if (!onlySelected) {
                $scope.totalCats = response.Data.total;
            }
        }

        function selectOlderCats(onlySelected, cats, refreshCat) {
            if (refreshCat) {
                return;
            }

            angular.forEach(cats, function (cat, index) {
                if (cat.ForumCategoryID in boxSelectedCategories) {
                    cat.Selected = 1;
                } else {
                    (onlySelected) ? cats.splice(index, 1) : cat.Selected = 0;
                }
            });

            if (onlySelected && cats.length == 0) {
                $scope.modal_show_all_cats = 1;
            }

        }

        function saveCategories(categories) {

            $('.loader-fad,.loader-view').show();
            // Prepare request data
            var selectedCategories = [];
            var OnlyRemove = (categories) ? 1 : 0;
            categories = categories || $scope.categories;
            angular.forEach(boxSelectedCategories, function (category, index) {
                if (category.Selected) {
                    selectedCategories.push(category.ForumCategoryID);
                }
            });
            var requestData = {
                ForumCategoryIDs: selectedCategories,
                OnlyRemove: OnlyRemove
            };

            // Save and update view
            $http.post(base_url + 'api/forum_user_categories/save_categories', requestData).then(function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    boxSelectedCategories = {};
                    getForumCategories(1, '', 1);
                }

            }, function (data) {

            });
        }

        function preProcessCategory(category, onlySelected) {
            if (!category.ProfilePicture) {
                category.ProfilePicture = image_server_path + 'upload/profile/220x220/category_default.png';
            } else {
                category.ProfilePicture = image_server_path + 'upload/profile/220x220/' + category.ProfilePicture;
            }

            category.Selected = +category.Selected;

            if (onlySelected && category.all_categories && angular.isArray(category.all_categories)) {
                var moreCatCount = 0;
                var moreCatLabels = '';
                category.FirstCat = {};
                category.SecondCat = {};
                angular.forEach(category.all_categories, function (tempCat) {
                    if (tempCat.ForumCategoryID == category.ForumCategoryID) {
                        return;
                    }

                    if (!category.FirstCat.Name) {
                        category.FirstCat = tempCat;
                        return;
                    }

                    if (!category.SecondCat.Name) {
                        category.SecondCat = tempCat;
                        return;
                    }

                    moreCatLabels += tempCat.Name + '<br/>';
                    moreCatCount++;
                });

                category.moreCatCount = moreCatCount;
                category.moreCatLabels = moreCatLabels;
            }

            return category;
        }

        $scope.initCatModel = function () {

//            $('#user-category-load-more').mCustomScrollbar({
//                callbacks: {
//                    onTotalScroll: function () {
//                        if ($scope.totalCats == $scope.categories.length) {
//                            return;
//                        }
//                        pageNo++;
//                        getAllCatesBox(0);
//                    }
//                }
//            });

            
            
            if($scope['categories_loading'] == 1) {
                return;
            }
            
            getAllCatesBox(0);

        }

        function getAllCatesBox(onlySelected, search) {
            $('.loader-fad,.loader-view').show();
            getForumCategories(onlySelected, search);
        }

        function getAllCatesBoxLoadMore() {                        
            
            if($scope['categories_loading']) {
                return;
            }
            
            if ($scope.totalCats == $scope.categories.length) {
                return;
            }
            pageNo++;
            getAllCatesBox(0);
        }

        function deleteCategory(category) {
            category.Selected = 1;
            saveCategories([category]);
        }

        $scope.searchCat = function (evt) {
            
            $scope.searchCatBox = evt.currentTarget.value;
            
            //evt.currentTarget.value = $scope.searchCatBox;
            
            if ($scope.searchCatBox.length <= 1 && $scope.searchCatBox.length != 0) {
                return;
            }
            pageNo = 1;
            getAllCatesBox(0, $scope.searchCatBox);

            if ($scope.searchCatBox.length != 0) {
                getAllCatesBox(1, $scope.searchCatBox);
            } else {
                $scope.boxSelectedCategories = [];
                angular.forEach(boxSelectedCategories, function (category) {
                    $scope.boxSelectedCategories.push(category);
                });
            }

        }
        $scope.getAllCatesBoxLoadMore = getAllCatesBoxLoadMore;
        $scope.saveCategories = saveCategories;
        $scope.deleteCategory = deleteCategory;
        $scope.openCategorySelectBox = function () {
            $scope.modal_show_all_cats = 1;
            pageNo = 1;
            getAllCatesBox(0);
            $scope.searchCatBox = '';
            boxSelectedCategories = {};

            $scope.boxSelectedCategories = angular.copy($scope.selected_categories);
            angular.forEach($scope.boxSelectedCategories, function (category, index) {
                boxSelectedCategories[category.ForumCategoryID] = category;
            });

            $("#selectCategory").modal();
        }

        $scope.toggleBoxes = function () {
            $scope.modal_show_all_cats = +(!$scope.modal_show_all_cats);
        }

        $scope.selectDeselectCategory = function (category) {

            category.Selected = +(!category.Selected);

            // If category is selected then check maximum category allowed
            if (category.Selected && $scope.boxSelectedCategories.length >= 8) {
                category.Selected = 0;
                return;
            }

            //In case of select category
            if (category.Selected) {
                $scope.boxSelectedCategories.push(category);
                boxSelectedCategories[category.ForumCategoryID] = category;

                return;
            }

            // In case of remove category

            angular.forEach($scope.boxSelectedCategories, function (cat, index) {
                if (cat.ForumCategoryID == category.ForumCategoryID) {
                    $scope.boxSelectedCategories.splice(index, 1);
                    delete boxSelectedCategories[cat.ForumCategoryID];
                }
            });

            angular.forEach($scope.categories, function (cat, index) {
                if (cat.ForumCategoryID == category.ForumCategoryID) {
                    cat.Selected = category.Selected;
                }
            });

            if ($scope.boxSelectedCategories.length == 0) {
                $scope.modal_show_all_cats = 1;
            }
        }
    }
    ArticleUsrCatCtrl.$inject = ['$scope', '$http', '$q'];
    app.controller('ArticleUsrCatCtrl', ArticleUsrCatCtrl);

})(app, angular);


