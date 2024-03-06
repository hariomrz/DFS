/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
app.controller('SkillCtrl', function ($scope, $http, $rootScope, $window, $timeout, apiService, $q) {
    $scope.allCategories = [];
    $scope.allSubCategories = [];
    $scope.allSuggestedCategories = [];
    $scope.manageskill = {'category': [], 'subcategory': []};
    //$scope.mergeskill = {'Name': '', 'category': [], 'subcategory': []};
    $scope.addskill = {SkillID: '', 'Name': '', 'category': [], 'subcategory': [], similarskill: []};
    $scope.ParentID = '';
    $scope.PopularSkillList = [];
    $scope.PopularSkillCount = '';
    $scope.OtherSkillList = [];
    $scope.OtherSkillCount = '';
    $scope.PendingSkillList = [];
    $scope.PendingSkillCount = '';
    $scope.SkillList = [];
    $scope.SkillCount = '';
    $scope.CategoryIDs = [];
    $scope.startDate = '';
    $scope.endDate = '';
    $scope.pageType = '';
    $scope.SelectedMergeSkill = [];
    $scope.MergeSkillDetail = [];
    $scope.image_path = image_path;
    $scope.MergeSubCategory = [];
    $scope.SubCategory = [];
    $scope.SelectedRemoveSkill = [];
    $scope.RemoveSkillData = [];
    $scope.similarSkill = [];
    $scope.SearchsimilarData = [];
    $scope.similarskill_search = [];
    $scope.EditSkill = {similarSkill: []};
    $scope.SuggestedSkill = [];
    $scope.suggestedSearchKeyword = '';
    $scope.currentData = {'MediaGUID': "", "ImageName": "", "OriginalName": ""};
    $scope.getCategory = function () {
        reqData = {}
        apiService.call_api(reqData, 'admin_api/skills/categories').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.allCategories = response.Data;
            }
        });
    }

    $scope.getSubCategory = function () {
        reqData = {ParentID: $scope.ParentID}
        apiService.call_api(reqData, 'admin_api/skills/categories').then(function (response) {
            if (response.ResponseCode == 200) {
                $.each(response.Data, function () {
                    this.ParentID = $scope.ParentID;
                    $scope.manageskill.subcategory.push(this);
                    $scope.allSubCategories.push(this);
                })
                $scope.callSkillFunction();
            }
        });
    }

    $scope.callSkillFunction = function () {
        if ($scope.pageType == 'ManageSkill')
        {
            $scope.getPopularSkill();
        }
        else if ($scope.pageType == 'MergeSkill')
        {
            $scope.getSkill();
        }
    }
    $scope.loadTags_category = function ($query) {
        return $scope.allCategories.filter(function (country) {
            return country.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
        });
    };
    $scope.loadTags_subCategory = function ($query) {
        var deferred = $q.defer();
        $scope.allSubCategories.filter(function (country) {
            return country.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
        });
        deferred.resolve($scope.allSubCategories);
        return deferred.promise;
    };

    $scope.tagAdded_category = function ($tag) {
        $scope.ParentID = $tag.ID
        $scope.getSubCategory();
        //$scope.tagChange_category();
    };
    $scope.tagRemoved_category = function ($tag) {

        $scope.manageskill.subcategory = $.grep($scope.manageskill.subcategory, function (e) {
            return e.ParentID != $tag.ID;
        });
        $scope.allSubCategories = $.grep($scope.allSubCategories, function (e) {
            return e.ParentID != $tag.ID;
        });

        $scope.callSkillFunction();

    };
    $scope.tagAdded_subCategory = function ($tag) {
        $scope.callSkillFunction();
    };
    $scope.tagRemoved_subCategory = function ($tag) {
        $scope.callSkillFunction();
    };

    $scope.setAllCategory = function ($tag) {
        $scope.CategoryIDs = [];
        $.each($scope.manageskill.category, function () {
            $scope.CategoryIDs.push(this.ID);
        })

        $.each($scope.manageskill.subcategory, function () {
            $scope.CategoryIDs.push(this.ID);
        })
    };


    $scope.getPopularSkill = function () {
        $scope.setAllCategory();
        $scope.SetDate();
        reqData = {CategoryIDs: $scope.CategoryIDs, PageNo: 1, PageSize: 10, StartDate: $scope.startDate, EndDate: $scope.endDate, SkillType: 'Popular'}
        apiService.call_api(reqData, 'admin_api/skills/list').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.PopularSkillList = response.Data;
                $scope.PopularSkillCount = response.TotalRecords;
                if ($scope.PopularSkillCount >= 10)
                {
                    $scope.PopularSkillCount = 10;
                }
                $scope.getOtherSkill();
            }
        });
    }


    $scope.getOtherSkill = function () {
        var SkillIDs = [];

        $.each($scope.PopularSkillList, function () {
            SkillIDs.push(this.ID);
        })
        $scope.SetDate();
        reqData = {CategoryIDs: $scope.CategoryIDs, PageNo: 1, PageSize: 0, StartDate: $scope.startDate, EndDate: $scope.endDate, SkillType: 'Other', "SkillIDs": SkillIDs}
        apiService.call_api(reqData, 'admin_api/skills/list').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.OtherSkillList = response.Data;
                $scope.OtherSkillCount = response.TotalRecords;
            }
        });
    }

    $scope.getPendingSkill = function () {
        $scope.SetDate();
        reqData = {CategoryIDs: $scope.CategoryIDs, PageNo: 1, PageSize: 0, StartDate: $scope.startDate, EndDate: $scope.endDate, SkillType: 'Pending'}
        apiService.call_api(reqData, 'admin_api/skills/list').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.PendingSkillList = response.Data;
                $scope.PendingSkillCount = response.TotalRecords;
            }
        });
    }

    $scope.getSkill = function () {
        $scope.setAllCategory();
        $scope.SetDate();
        reqData = {CategoryIDs: $scope.CategoryIDs, PageNo: 1, PageSize: 0, StartDate: $scope.startDate, EndDate: $scope.endDate}
        apiService.call_api(reqData, 'admin_api/skills/list').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.SkillList = response.Data;
                $scope.SkillCount = response.TotalRecords;

                $scope.SkillList.map(function (repo) {
                    repo.IsSelecte = false;
                });

                $scope.SkillList = $.grep($scope.SkillList, function (element) {
                    return $.grep($scope.SelectedMergeSkill, function (e) {
                        if (e.ID == element.ID)
                        {
                            element.IsSelecte = true;
                        }
                    });
                });
            }
        });
    }

    $scope.SetDate = function () {
        if ($('#dateFilterText').html() != 'All') {
            $scope.startDate = $('#SpnFrom').val();
            $scope.endDate = $('#SpnTo').val();
        } else {
            $scope.startDate = '';
            $scope.endDate = '';
        }
    }

    $scope.selectSkill = function (data) {
        if (data.IsSelecte) {
            data.IsSelecte = false;
            $scope.SelectedMergeSkill = $.grep($scope.SelectedMergeSkill, function (e) {
                return e.ID != data.ID;
            });
            $scope.SkillList = $.grep($scope.SkillList, function (e) {
                if (e.ID == data.ID) {
                    e.IsSelecte = false;
                }
                return e;
            });
        }
        else {
            data.IsSelecte = true;
            $scope.SelectedMergeSkill.push(data);
        }

    }

    $scope.GetSkillDetail = function () {
        if ($scope.SelectedMergeSkill.length <= 1) {
            ShowErrorMsg("Pleae select atleast two skills.");
            return false;
        }
        var SkillIDs = [];
        $.each($scope.SelectedMergeSkill, function () {
            SkillIDs.push(this.ID);
        })
        reqData = {SkillIDs: SkillIDs}
        apiService.call_api(reqData, 'admin_api/skills/merge_skills_details').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.MergeSkillDetail = response.Data;

                $scope.MergeSkillDetail.map(function (repo) {
                    repo.IsSelecte = true;
                });
            }
            openPopDiv('mergeskillsPopup', 'bounceInDown');
        });
        // openPopDiv('mergeskillsPopup', 'bounceInDown');
    }
    $scope.RemoveSelectedSkill = function (data) {

        $scope.selectSkill(data);
        $scope.MergeSkillDetail = $.grep($scope.MergeSkillDetail, function (e) {
            return e.ID != data.ID;
        });
    }

    $scope.tagAdded_merge_category = function ($tag) {
        $scope.addskill.category = [];
        $scope.addskill.category.push($tag);
        $scope.addskill.subcategory = [];
        $scope.getMergeSubCategory($tag.ID)
        $scope.SuggestedSkill.map(function (repo) {
            repo.IsSelecte = false;
        });
    };
    $scope.loadTags_merge_category = function ($query) {
        return $scope.allCategories.filter(function (country) {
            return country.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
        });
    };
    $scope.tagRemoved_merge_category = function ($tag) {
        $scope.addskill.SubCategory = [];
        $scope.addskill.subcategory = [];
        $scope.SubCategory = [];
        $scope.SuggestedSkill.map(function (repo) {
            repo.IsSelecte = false;
        });
    };

    $scope.loadTags_merge_subcategory = function ($query) {
        return $scope.SubCategory.filter(function (Data) {
            return Data.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
        });
    };

    $scope.tagAdded_merge_subcategory = function ($tag) {
        $scope.addskill.subcategory = [];
        $scope.addskill.subcategory.push($tag);
        $scope.SuggestedSkill.map(function (repo) {
            repo.IsSelecte = false;
        });
    };

    $scope.tagRemoved_merge_subcategory = function ($tag) {

        $scope.SuggestedSkill.map(function (repo) {
            repo.IsSelecte = false;
        });
    };

    /*$scope.$watch('mergeskill.category', function (newVal, oldVal) {
     if (newVal != null && newVal != undefined && newVal != '')
     {
     if (newVal != oldVal) {
     $scope.MergeSubCategory = [];
     $scope.SubCategory = [];
     $scope.getMergeSubCategory(newVal.originalObject.ID)
     }
     }
     })*/

    /*   $scope.$watch('addskill.category', function (newVal, oldVal) {
     console.log(newVal);
     if (newVal != oldVal) {
     
     if (newVal == undefined || newVal == '' || newVal == null) {
     $scope.SubCategory = [];
     $scope.addskill.subcategory = [];
     
     } else {
     $scope.getMergeSubCategory(newVal.originalObject.ID);
     
     }
     $scope.$broadcast('angucomplete-alt:clearInput', 'AddSkillSubCategory');
     }
     })*/

    $scope.CheckValideCatgory = function (id) {
        console.log(id);
        $scope.$broadcast('angucomplete-alt:clearInput', id);
    }
    $scope.getMergeSubCategory = function (ParentID) {
        $scope.SubCategory = [];
        reqData = {ParentID: ParentID}
        apiService.call_api(reqData, 'admin_api/skills/categories').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.SubCategory = response.Data;
            }
        });
    }

    $scope.SaveMergeSkill = function () {

        if ($scope.SelectedMergeSkill.length <= 1) {
            ShowErrorMsg("Please select atleast two skills.");
            return false;
        }

        if ($scope.addskill.Name == '') {
            ShowErrorMsg("Please enter skill name");
            return false;
        }
        var Name = $scope.addskill.Name;
        var SkillIDs = [];
        var CategoryID = 0;
        var SubCategoryID = 0;

        $.each($scope.SelectedMergeSkill, function () {
            SkillIDs.push(this.ID);
        })

        $.each($scope.addskill.category, function () {
            CategoryID = this.ID
        })
        $.each($scope.addskill.subcategory, function () {
            SubCategoryID = this.ID
        })

        reqData = {SkillIDs: SkillIDs
            , Name: $scope.addskill.Name
            , CategoryID: CategoryID
            , SubCategoryID: SubCategoryID
        }
        apiService.call_api(reqData, 'admin_api/skills/merge').then(function (response) {
            if (response.ResponseCode == 200) {
                closePopDiv('mergeskillsPopup', 'bounceOutUp');
                $scope.SelectedMergeSkill = [];
                $scope.getSkill();

                ShowSuccessMsg(response.Message);
            }
            else {
                ShowErrorMsg(response.Message);
            }
        });
        // closePopDiv('mergeskillsPopup', 'bounceOutUp');
    }

    $scope.RemoveSkillConfirmation = function (List, $index, Type) {

        $scope.SelectedRemoveSkill = List;
        $scope.SelectedRemoveSkill.index = $index;
        $scope.SelectedRemoveSkill.Type = Type;
        reqData = {SkillID: List.ID}
        apiService.call_api(reqData, 'admin_api/skills/skill_profile_count').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.RemoveSkillData = response;
                openPopDiv('removeCategoryPopup', 'bounceInDown');
            }
        });
    }

    $scope.RemoveSkill = function () {
        var SkillIDs = [];
        SkillIDs.push($scope.SelectedRemoveSkill.ID);
        reqData = {SkillIDs: SkillIDs}
        closePopDiv('removeCategoryPopup', 'bounceOutUp');
        if ($scope.SelectedRemoveSkill.Type == 'Popular') {
            $scope.PopularSkillList.splice($scope.SelectedRemoveSkill.index, 1);
            $scope.PopularSkillCount = parseInt($scope.PopularSkillCount) - 1;
        }
        else if ($scope.SelectedRemoveSkill.Type == 'Other') {
            $scope.OtherSkillList.splice($scope.SelectedRemoveSkill.index, 1);
            $scope.OtherSkillCount = parseInt($scope.OtherSkillCount) - 1;
        }
        else if ($scope.SelectedRemoveSkill.Type == 'Pending') {
            $scope.PendingSkillList.splice($scope.SelectedRemoveSkill.index, 1);
            $scope.PendingSkillCount = parseInt($scope.PendingSkillCount) - 1;
        }
        apiService.call_api(reqData, 'admin_api/skills/remove').then(function (response) {
            if (response.ResponseCode == 200) {
            }
        });
    }

    $scope.SaveSkill = function () {

        if ($scope.addskill.Name == '') {
            $scope.ShowSkillNameError = 'Please enter skill name';
            return false;
        } else {
            $scope.ShowSkillNameError = '';
        }

        if ($scope.similarskill_search.length > 0) {
            // $('html,body').animate({scrollTop: $('#successMsz').offset().top}, 500);
            $('html,body').animate({scrollTop: 0}, 'slow');
            ShowErrorMsg('Please add similar skill');
            return false;
        }
        if ($('.error-text').is(':visible') == true)
            return false;
        var similarSkill = [];
        var CategoryID = 0;
        var SubCategoryID = 0;

        $.each($scope.similarSkill, function () {
            if (this.IsSelecte) {
                similarSkill.push(this.ID);
            }
        })
        $.each($scope.addskill.category, function () {
            CategoryID = this.ID
        })
        $.each($scope.addskill.subcategory, function () {
            SubCategoryID = this.ID
        })

        reqData = {SkillID: $scope.addskill.SkillID
            , Name: $scope.addskill.Name
            , CategoryID: CategoryID
            , SubCategoryID: SubCategoryID
            , MediaGUID: $scope.currentData.MediaGUID
            , SimilarSkillIDs: similarSkill
        }
        apiService.call_api(reqData, 'admin_api/skills/save').then(function (response) {
            if (response.ResponseCode == 200) {
                //$scope.SubCategory = response.Data;
                $('html,body').animate({scrollTop: 0}, 'slow');
                ShowSuccessMsg(response.Message);

                setTimeout(function () {
                    $window.location.href = base_url + 'admin/skill';
                }, 1000)
            }
            else {
                $('html,body').animate({scrollTop: 0}, 'slow');
                ShowErrorMsg(response.Message);
            }
        });
        // closePopDiv('mergeskillsPopup', 'bounceOutUp');
    }

    $scope.delete_skill_image = function () {
        $scope.currentData = {'MediaGUID': "", "ImageName": "", "OriginalName": ""};
        $('.upload-btn-show').show();
    }

    $scope.checkSkillData = function () {
        if ($scope.addskill.Name == '')
        {
            $scope.addskill.category = [];
            $scope.addskill.subcategory = [];
            $scope.SuggestedSkill = [];
            $scope.similarSkill = [];
            return false;
        }
        $scope.addskill.category = [];
        $scope.getSuggestedSkill();
        $scope.getsimilarSkill();
    }
    $scope.getSuggestedSkill = function () {

        reqData = {Keyword: $scope.addskill.Name}
        apiService.call_api(reqData, 'admin_api/skills/suggested').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.SuggestedSkill = response.Data;
                $scope.SuggestedSkill.map(function (repo) {
                    repo.IsSelecte = false;
                });

            }
        });
    }

    $scope.getsimilarSkill = function () {
        var SkillIDs = [];
        $.each($scope.similarSkill, function () {
            if (this.IsSelecte)
            {
                SkillIDs.push(this.ID);
            }
        })

        if ($scope.addskill.SkillID != '') {
            SkillIDs.push($scope.addskill.SkillID);
        }

        reqData = {Keyword: $scope.addskill.Name
            , PageNo: 1
            , PageSize: 10
            , SkillIDs: SkillIDs
        }

        apiService.call_api(reqData, 'admin_api/skills/similar').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.similarSkill = response.Data;
                $scope.similarSkill.map(function (repo) {
                    repo.IsSelecte = false;
                });

                $.each($scope.EditSkill.similarSkill, function () {
                    $scope.similarSkill.push(this);
                })
            }
        });
    }

    $scope.AddSimilierSkill = function () {
        $.each($scope.similarskill_search, function () {
            this.IsSelecte = true;
            $scope.similarSkill.push(this)
        })
        $scope.similarskill_search = [];
    }
    $scope.searchsimilarSkill = function (Keyword) {
        var SkillIDs = [];
        $.each($scope.similarSkill, function () {
            SkillIDs.push(this.ID);
        })

        if ($scope.addskill.SkillID != '') {
            SkillIDs.push($scope.addskill.SkillID);
        }

        reqData = {Keyword: Keyword
            , PageNo: 1
            , PageSize: 10
            , SkillIDs: SkillIDs
        }
        apiService.call_api(reqData, 'admin_api/skills/similar').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.SearchsimilarData = response.Data;

                $scope.SearchsimilarData.map(function (repo) {
                    repo.IsSelecte = false;
                });
            }
        });
    }

    $scope.SelectSuggestedSkill = function (data) {
        var oldState = data.IsSelecte;
        $scope.SuggestedSkill.map(function (repo) {
            repo.IsSelecte = false;
        });
        $scope.addskill.category = [];
        $scope.addskill.subcategory = [];
        if (oldState) {

            data.IsSelecte = false;
        } else {
            data.IsSelecte = true;
            if (data.CategoryID != '') {
                $scope.addskill.category.push({'ID': data.CategoryID, 'Name': data.CategorName});
                $scope.addskill.subcategory.push({'ID': data.SubCategoryID, 'Name': data.SubCategoryName});
            } else {
                $scope.addskill.category.push({'ID': data.SubCategoryID, 'Name': data.SubCategoryName});
            }
            //    console.log($scope.addskill.category);
            $scope.getMergeSubCategory($scope.addskill.category[0].ID)

        }
        return data;
    }

    $scope.SelectsimilarSkill = function (data) {
        if (data.IsSelecte) {
            data.IsSelecte = false;
        } else {
            data.IsSelecte = true;
        }
        return data;
    }
    $scope.load_similar_skill = function ($query) {
        var deferred = $q.defer();
        $scope.searchsimilarSkill($query);
        deferred.resolve($scope.SearchsimilarData);
        return deferred.promise;
    };

    $scope.get_single_skill = function (id) {

        reqData = {SkillID: id}
        apiService.call_api(reqData, 'admin_api/skills/get_single_skill').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.EditSkill = response.Data;
                $scope.addskill.Name = $scope.EditSkill.Name;
                $scope.addskill.SkillID = $scope.EditSkill.ID;

                if ($scope.EditSkill.ParentCategoryID != '' && $scope.EditSkill.CategoryID != '') {
                    $scope.addskill.category = [{ID: $scope.EditSkill.ParentCategoryID, Name: $scope.EditSkill.ParentCategorName}];
                    $scope.addskill.subcategory = [{ID: $scope.EditSkill.CategoryID, Name: $scope.EditSkill.CategoryName}];
                    $scope.getMergeSubCategory($scope.EditSkill.ParentCategoryID);
                }
                else if ($scope.EditSkill.ParentCategoryID == '' && $scope.EditSkill.CategoryID != '') {
                    $scope.addskill.category = [{ID: $scope.EditSkill.CategoryID, Name: $scope.EditSkill.CategoryName}];
                    $scope.getMergeSubCategory($scope.EditSkill.CategoryID);
                }

                /*  if ($scope.EditSkill.ParentCategoryID != '') {
                 $scope.addskill.category = [{ID: $scope.EditSkill.ParentCategoryID, Name: $scope.EditSkill.ParentCategorName}];
                 $scope.getMergeSubCategory($scope.EditSkill.ParentCategoryID);
                 }*/
                /*  if ($scope.EditSkill.CategoryID != '') {
                 $scope.addskill.subcategory = [{ID: $scope.EditSkill.CategoryID, Name: $scope.EditSkill.CategoryName}];
                 }*/
                if ($scope.EditSkill.SkillImageName != '')
                {
                    $scope.currentData.ImageName = $scope.EditSkill.SkillImageName;
                    $scope.currentData.MediaGUID = $scope.EditSkill.MediaGUID;
                    $scope.currentData.OriginalName = $scope.EditSkill.OriginalName;
                }
                //  console.log( response.Data);
                $scope.similarSkill = $scope.EditSkill.similarSkill;

                $scope.similarSkill.map(function (repo) {
                    repo.IsSelecte = true;
                });
            }
        });
    }

    $scope.ShowAllCategory = function () {
        $scope.allSuggestedCategories = $scope.allCategories;
        $scope.allSuggestedCategories.map(function (repo) {
            repo.IsSelecte = false;
        });
        openPopDiv('skillshowallPopup', 'bounceInDown');
    }

    $scope.getSuggestedSubCategory = function ($index, ParentID) {
        if ($scope.allSuggestedCategories[$index].SubCategories.length > 0) {
            return false;
        }
        reqData = {ParentID: ParentID}
        apiService.call_api(reqData, 'admin_api/skills/categories').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.allSuggestedCategories[$index].SubCategories = [];
                $.each(response.Data, function () {
                    $scope.allSuggestedCategories[$index].SubCategories.push(this);
                })

                $scope.allSuggestedCategories[$index].SubCategories.map(function (repo) {
                    repo.IsSelecte = false;
                });
                //response.Data
            }
        });
    }

    $scope.SuggestedSelectSubCategory = function (Category, SubCategory) {
        $scope.SuggestedSkill.map(function (repo) {
            repo.IsSelecte = false;
        });
        $('#chk' + Category.ID).trigger('click');
        $scope.addskill.category = [];
        $scope.addskill.subcategory = [];
        $scope.addskill.category.push({'ID': Category.ID, 'Name': Category.Name});
        $scope.addskill.subcategory.push({'ID': SubCategory.ID, 'Name': SubCategory.Name});
    }
    $scope.SuggestedSelectCategory = function (Category) {
        $scope.SuggestedSkill.map(function (repo) {
            repo.IsSelecte = false;
        });
        $("input[type=radio][name=suggest_subcategory]").removeAttr('checked');
        $scope.addskill.category = [];
        $scope.addskill.subcategory = [];
        $scope.addskill.category.push({'ID': Category.ID, 'Name': Category.Name});
    }

    timeVar = '';
    $scope.$watch('suggestedSearchKeyword', function (newVal, oldVal) {
        if (newVal != oldVal) {
            clearTimeout(timeVar);
            timeVar = setTimeout(function () {
                $scope.SearchSuggestedCategory();
            }, 500)
        }
    })

    $scope.SearchSuggestedCategory = function () {
        reqData = {Keyword: $scope.suggestedSearchKeyword}
        apiService.call_api(reqData, 'admin_api/skills/categories').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.allSuggestedCategories = response.Data;
            }
        });
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
                        // var html = "<li id='dummy_img'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='"+base_url+"assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                        //$('#attached-media-'+$(element).attr('unique-id')).html(html);
                        $('.upload-btn-show').hide();
                        $('.upload-btn-loader').show();
                    },
                    onProgress: function (id, fileName, loaded, total) {
                    },
                    onComplete: function (id, fileName, responseJSON) {
                        $('.upload-btn-loader').hide();
                        if (responseJSON.Message == 'Success')
                        {
                            if ($(element).attr('image-type') == "landscape")
                            {
                                $('#attached-media-' + $(element).attr('unique-id')).html("<label>" + responseJSON.Data.ImageName + "</label>");
                            }
                            else
                            {
                                var CategoryCtrl = angular.element('#SkillCtrl').scope();

                                CategoryCtrl.$apply(function () {
                                    CategoryCtrl.currentData.ImageName = responseJSON.Data.ImageName;
                                    CategoryCtrl.currentData.MediaGUID = responseJSON.Data.MediaGUID;
                                    CategoryCtrl.currentData.OriginalName = responseJSON.Data.OriginalName;
                                });
                                /*click_function = 'remove_image("'+responseJSON.Data.MediaGUID+'");';
                                 var html = "<li id='"+responseJSON.Data.MediaGUID+"'><a class='smlremove' onclick='"+click_function+"'></a>";
                                 html+= "<figure><img alt='' width='98px' class='img-"+$(element).attr('image-type')+"-full' media_type='IMAGE' is_cover_media='0' media_name='"+responseJSON.Data.ImageName+"' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+responseJSON.Data.ImageServerPath +'/220x220/'+responseJSON.Data.ImageName+"'></figure>";
                                 html+= "<span class='radio'></span></li>";
                                 
                                 $('#attached-media-'+$(element).attr('unique-id')).html(html);
                                 var $items = $('.img-full');*/
                            }
                        }
                        else if (responseJSON.ResponseCode !== 200)
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
                            }
                            else
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
                template: ' <a class="qq-upload-button"  title="Attach a Photo"><span class="up-icon"><label for="addIcon"></label><svg height="20px" width="25px" class="svg-icons"><use xlink:href="' + base_url + 'assets/admin/img/sprite.svg#defaultIcn" xmlns:xlink="http://www.w3.org/1999/xlink"/></svg></span><span class="up-text">Upload Icon</span></a><span class="qq-drop-processing qq-upload-drop-area" style="display:none;"></span>' + '<ul class="qq-upload-list" style="display:none;margin-top: 10px; text-align: center;"></ul>',
                chunking: {
                    //enabled: false
                    //onclick=$(\'#cmt-'+attributes.uniqueId+'\').trigger(\'focus\');
                }
            });
        }
    };
});
