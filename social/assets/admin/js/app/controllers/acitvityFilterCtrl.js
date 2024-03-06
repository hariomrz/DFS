!(function () {
  'use strict';
  app.controller('AcitvityFilterController', ['$scope', '$rootScope', '$timeout', '$q', 'DashboardService', function ($scope, $rootScope, $timeout, $q, DashboardService) {
      var defaultShowMeContent = [
          { Value: 0, Label: 'All Posts', IsSelect: true },
          { Value: 1, Label: 'Discussion', IsSelect: true },
          /* { Value: 2, Label: 'Q & A', IsSelect: true },
          { Value: 4, Label: 'Article', IsSelect: true },
              */
          { Value: 7, Label: 'Announcements', IsSelect: true }
      ],
      defualtFilterOptions = {
        PostType: [0],//0: All Posts, 1: Discussion, 2 : Q & A, 4 : Article, 7: Announcements
        SearchKey: '',
        Tags: [],
        ActivityFilterType: 0,
        PollFilterType: 0,
        IsMediaExists: 2,
        FeedUser: [],
        StartDate: '',
        EndDate: '',
        City: '',
        State: '',
        Country: '',
        CountryCode: '',
        StateCode: '',
        Gender: 0,
        AgeGroupID: 0,
        FeedSortBy:2,
        UserID:[],
        IsPromoted : 0,
        Verified : 0,
        WID: '1',
        WN: 'All',
        IsDailyDigest: 0,
        IsAllQuestion: 0,
        FilterType: 0,
        ActivityTypeFilter: 0,
        TID: 0,
        TName: 'All',
      };


      $scope.filterdStatus = {
        ShowMe: false,
        Content: false,
        Type: false,
        Ownership: false,
        TimePeriod: false,
        Demographics: false,
        SortBy: false
      };

      $scope.showMeLabelName = 'All Posts';
      $scope.activeActivityType = 'All';
      $scope.activeSortBy = 'Recent Post';
      $scope.isFilterApplied = false;
      $scope.searchTags = [];
      $scope.PostedByLookedMore = [];
      $scope.ageGroupArray = [];
      $scope.selectedAgeGroup = {};
      $scope.selectedWard = {WID:'1',WName:'All',WNumber:'0',WDescription:''};
      $scope.WID = '1';
      

      $scope.activityTypes = {
        All: { ActivityFilterType: '0', PollFilterType: '0', IsMediaExists: 2},
        /*Archived: { ActivityFilterType: '4', PollFilterType: '4', IsMediaExists: 2},
        Deleted: { ActivityFilterType: '7', PollFilterType: '7', IsMediaExists: 2},
        Favourite: { ActivityFilterType: 'Favourite', PollFilterType: 'Favourite', IsMediaExists: 2},
        Featured: { ActivityFilterType: '11', PollFilterType: '11', IsMediaExists: 2},*/
        Text: { ActivityFilterType: '0', PollFilterType: '0', IsMediaExists: 0},
        Media: { ActivityFilterType: '0', PollFilterType: '0', IsMediaExists: 1},
        'City News': { ActivityFilterType: '12', PollFilterType: '0', IsMediaExists: 2},
       /* Promoted: { ActivityFilterType: '0', PollFilterType: '0', IsMediaExists: 2, IsPromoted : 1},
        Drafts: { ActivityFilterType: '10', PollFilterType: '10', IsMediaExists: 2},*/
//        Flag: { ActivityFilterType: '2', PollFilterType: '2', IsMediaExists: 2}
      };

      
      $scope.customActivityTypes = {
          All: { ActivityFilterType: '0', PollFilterType: '0', IsMediaExists: 2},
          'Post with description': { ActivityFilterType: '13', PollFilterType: '0', IsMediaExists: 2},
          'Post without description': { ActivityFilterType: '14', PollFilterType: '0', IsMediaExists: 2},
      };
      
      $scope.sortByOptions = {
        RecentPost: { Label: 'Recent Post', FeedSortBy: 2 },
        RecentUpdated: { Label: 'Recent Updated', FeedSortBy: 1 },
        Popular: { Label: 'Popular', FeedSortBy: 3 }
      };
      
      $scope.demoGraphics = {
        locationLable: '',
        genderLable: '',
        ageGroupLable: '',
        location: '',
        details: ''
      };
      
      $scope.ShowMe = angular.copy(defaultShowMeContent);
      
      $scope.filterOptions = angular.copy(defualtFilterOptions);
      
      $scope.activityVerifyTypes = {
        2 : 'All',
        1 : 'Verified',
        0 : 'Unverified'
      }

      $scope.activityTypeFilter = {
        0 : 'All',
        1 : 'Only Post',
        2 : 'Only Comment'
      }
      
      $scope.verifyCheckedStatus = function () {
        var checkedCount = 0,
        showMeLabelName = '';
        $scope.filterOptions.PostType = [];
        angular.forEach($scope.ShowMe, function (filterData, filterKey) {
          if ( ( filterData.Value > 0 ) && filterData.IsSelect ) {
            checkedCount++;
            showMeLabelName += ( showMeLabelName ) ? ( ',' + filterData.Label ) : filterData.Label ;
            $scope.filterOptions.PostType.push(filterData.Value);
          }
        });
        if ( checkedCount === ( $scope.ShowMe.length - 1 ) ) {
          $scope.ShowMe[0].IsSelect = true;
          showMeLabelName = 'All Posts';
          $scope.filterOptions.PostType = [$scope.ShowMe[0].Value];
        } else {
          $scope.ShowMe[0].IsSelect = false;
        }
        if ( showMeLabelName ) {
          $scope.showMeLabelName = showMeLabelName;
        } else {
          $scope.filterOptions.PostType = [$scope.ShowMe[0].Value];
          $scope.showMeLabelName = 'All Posts';
        }
        $scope.filterdStatus.ShowMe = ( $scope.showMeLabelName !== 'All Posts' ) ? true : false;
        $scope.applyFiltersOptions();
      };
      
      $scope.verifyAllCheckedStatus = function () {
        var checkedCount = 0,
            showMeLabelName = '';
        angular.forEach($scope.ShowMe, function (filterData, filterKey) {
          if ( $scope.ShowMe[0].IsSelect ) {
            $scope.ShowMe[filterKey].IsSelect = true;
          } else {
            $scope.ShowMe[filterKey].IsSelect = false;
          }
          if ( ( filterData.Value > 0 ) && filterData.IsSelect ) {
            checkedCount++;
            showMeLabelName += ( showMeLabelName ) ? ( ',' + filterData.Label ) : filterData.Label ;
            $scope.filterOptions.PostType.push(filterData.Value);
          }
        });
        if ( checkedCount === ( $scope.ShowMe.length - 1 ) ) {
          showMeLabelName = 'All Posts';
          $scope.filterOptions.PostType = [$scope.ShowMe[0].Value];
        }
        if ( showMeLabelName ) {
          $scope.showMeLabelName = showMeLabelName;
        } else {
          $scope.filterOptions.PostType = [$scope.ShowMe[0].Value];
          $scope.showMeLabelName = 'All Posts';
        }
        $scope.filterdStatus.ShowMe = ( $scope.showMeLabelName !== 'All Posts' ) ? true : false;
        $scope.applyFiltersOptions();
      };
      
      $scope.loadSearchTags = function ($query) {
        var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, Offset: 0, Limit: 10, type:1};
        return DashboardService.CallPostApi('api/search/tag', requestPayload, function (resp) {
          var response = resp.data;
          if ( ( response.ResponseCode == 200 ) && ( response.Data.length > 0 ) ) {
            return response.Data.filter(function (flist) {
              return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
          } else { 
            return [];
          }
        });
      };

      $scope.loadSearchUsers = function ($query) {
        var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 10};
        return DashboardService.CallPostApi('admin_api/users/user_search', requestPayload, function (resp) {
          var response = resp.data;
          if ((response.ResponseCode == 200) && (response.Data.length > 0)) { 
            return response.Data.filter(function (flist) {
              return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
          } else {
            return [];
          }
        });
       
      };

      $scope.addOwnershipInfoById = function(UserID) {
        if ( UserID ) {
          $scope.filterOptions.UserID.splice(0, 0, UserID);
          $scope.filterdStatus.Ownership = ( $scope.filterOptions.UserID.length > 0 ) ? true : false;
          $scope.applyFiltersOptions();
        }
      };
      
      $scope.removeOwnershipInfoById = function(UserID) {
        if ( UserID ) {
          var index = $scope.filterOptions.UserID.indexOf( UserID );
          if ( index > -1 ) {
            $scope.filterOptions.UserID.splice(index, 1);
          }
          $scope.filterdStatus.Ownership = ( $scope.filterOptions.UserID.length > 0 ) ? true : false;
          $scope.applyFiltersOptions();
        }
      };

      $scope.addOwnershipInfo = function(ModuleEntityGUID) {
        if ( ModuleEntityGUID ) {
          $scope.filterOptions.FeedUser.splice(0, 0, ModuleEntityGUID);
          $scope.filterdStatus.Ownership = ( $scope.filterOptions.FeedUser.length > 0 ) ? true : false;
          $scope.applyFiltersOptions();
        }
      };
      
      $scope.removeOwnershipInfo = function(ModuleEntityGUID) {
        if ( ModuleEntityGUID ) {
          var index = $scope.filterOptions.FeedUser.indexOf( ModuleEntityGUID );
          if ( index > -1 ) {
            $scope.filterOptions.FeedUser.splice(index, 1);
          }
          $scope.filterdStatus.Ownership = ( $scope.filterOptions.FeedUser.length > 0 ) ? true : false;
          $scope.applyFiltersOptions();
        }
      };
      
      $scope.resetOwnership = function() {
        $scope.PostedByLookedMore = [];
        $scope.filterOptions.FeedUser = [];
        $scope.filterdStatus.Ownership = false;
        $scope.applyFiltersOptions();
      };
      
      $scope.updateSearchTag = function (action, TagId) {
        var index = $scope.filterOptions.Tags.indexOf(TagId);
        if ( action == 'added') {
          if ( index === -1 ) {
            $scope.filterOptions.Tags.splice(0, 0, TagId);
          }
        } else {
          if ( index > -1 ) {
            $scope.filterOptions.Tags.splice(index, 1);
          }
        }
        $scope.filterdStatus.Content = ( ( $scope.filterOptions.Tags.length > 0 ) || ( $scope.filterOptions.SearchKey !== '' ) ) ? true : false;
        $scope.applyFiltersOptions();
      }
      
      $scope.setSearchStatus = function () {
        $scope.filterdStatus.Content = ( ( $scope.filterOptions.Tags.length > 0 ) || ( $scope.filterOptions.SearchKey !== '' ) ) ? true : false;
      }

      $scope.setActivityType = function (key, value) {
        if ( $scope.activityTypes[key] ) {
          $scope.filterdStatus.Type = ( key === 'All' ) ? false : true;
          $scope.activeActivityType = key;
          $scope.filterOptions.ActivityFilterType = $scope.activityTypes[key].ActivityFilterType;
          $scope.filterOptions.PollFilterType = $scope.activityTypes[key].PollFilterType;
          $scope.filterOptions.IsMediaExists = $scope.activityTypes[key].IsMediaExists;
          $scope.filterOptions.IsPromoted = ($scope.activityTypes[key].IsPromoted) ? $scope.activityTypes[key].IsPromoted : 0;
          
          $scope.applyFiltersOptions();
        } else if(key == 'Verified'){
            $scope.filterOptions.Verified = value;
            $scope.applyFiltersOptions();
        }  else if(key == 'ActivityTypeFilter'){
          $scope.filterOptions.ActivityTypeFilter = value;
          $scope.applyFiltersOptions();
        }
      }


      $scope.setCustomActivityType = function (key, value) {
        if ( $scope.customActivityTypes[key] ) {
          $scope.filterdStatus.Type = ( key === 'All' ) ? false : true;
          $scope.activeActivityType = key;
          $scope.filterOptions.ActivityFilterType = $scope.customActivityTypes[key].ActivityFilterType;
          $scope.applyFiltersOptions();
        } else if(key == 'Verified'){
            $scope.filterOptions.Verified = value;
            $scope.applyFiltersOptions();
        }
      }
      
      $scope.checkValDatepicker = function () {
        $scope.filterdStatus.TimePeriod = true;
        $scope.applyFiltersOptions();
      }
      
      $scope.setSortByOption = function (key) {
        if ( $scope.sortByOptions[key] ) {
          $scope.filterdStatus.SortBy = ( key === 'RecentPost' ) ? false : true;
          $scope.activeSortBy = $scope.sortByOptions[key].Label;
          $scope.filterOptions.FeedSortBy = $scope.sortByOptions[key].FeedSortBy;
          $scope.applyFiltersOptions();
        }
      }
      
      $scope.location_filter = [];

      $scope.setLocationFilter = function () {
        if ( $scope.demoGraphics.location && $scope.demoGraphics.details ) {
          $scope.filterOptions.City = $scope.demoGraphics.details[0].CityName;
          $scope.filterOptions.State = $scope.demoGraphics.details[1].StateName;
          $scope.filterOptions.StateCode = $scope.demoGraphics.details[1].StateCode;
          $scope.filterOptions.Country = $scope.demoGraphics.details[2].CountryName;
          $scope.filterOptions.CountryCode = $scope.demoGraphics.details[2].CountryCode;
        } else {
          $scope.filterOptions.City = '';
          $scope.filterOptions.State = '';
          $scope.filterOptions.StateCode = '';
          $scope.filterOptions.Country = '';
          $scope.filterOptions.CountryCode = '';
        }
        $scope.location_filter = {City:$scope.filterOptions.City,State:$scope.filterOptions.State,StateCode:$scope.filterOptions.StateCode,Country:$scope.filterOptions.Country,CountryCode:$scope.filterOptions.CountryCode};
        $scope.demoGraphics.locationLable = $scope.filterOptions.City;
        $scope.filterdStatus.Demographics = ( $scope.demoGraphics.locationLable || $scope.demoGraphics.locationLable || $scope.demoGraphics.locationLable ) ? true : false;
        $scope.applyFiltersOptions();
        //console.log($scope.filterdStatus.Demographics);
      }

      $scope.setGenderValue = function () {
        $scope.demoGraphics.genderLable = ( $scope.filterOptions.Gender == 1 ) ? 'Male' : 'Female' ;
        $scope.filterdStatus.Demographics = ( $scope.demoGraphics.locationLable || $scope.demoGraphics.locationLable || $scope.demoGraphics.locationLable ) ? true : false;
        $scope.applyFiltersOptions();
      }
      
      $scope.getAgeGroup = function () {
        DashboardService.CallPostApi('admin_api/rules/get_age_group', {}, function (resp) {
          var response = resp.data;
          if ((response.ResponseCode == 200) && (response.Data.length > 0)) {
            $scope.ageGroupArray = response.Data;
            /*console.log($scope.ageGroupArray);
            setTimeout(function(){
              //$('.chosen-select').chosen();
              console.log('chosen called');
            },2000);*/
          }
        });
      };

      $scope.ward_list  = [];
      $scope.getWardList = function () {
          DashboardService.CallPostApi('admin_api/ward/list', {}, function (response) {                
              var response = response.data;
              if (response.ResponseCode != 200) {
                  ShowErrorMsg(response.Message);
                  return;
              }

              if (response.ResponseCode == 200)
              {
                  $scope.ward_list = response.Data;
                  // console.log($scope.ward_list);
              }    
          
          });
      }

      $scope.wardSelected = function () {
        
        $scope.filterOptions.WID = $scope.WID;
        $scope.filterOptions.WN = 'All';
        if($scope.WID > 1) {
          $scope.filterOptions.WN = $("#select_ward option:selected").text();
        }
        //console.log($scope.filterOptions.WN);      
        $scope.applyFiltersOptions();
      }

      $scope.teamMemberSelected = function () {
        
        $scope.filterOptions.TID = $scope.TID;
        $scope.filterOptions.TName = 'All';
        if($scope.TID > 0) {
          $scope.filterOptions.TName = $("#select_team option:selected").text();
        }
        //console.log($scope.filterOptions.WN);      
        $scope.applyFiltersOptions();
      }

      $scope.ageGroupSelected = function () {
        $scope.filterOptions.AgeGroupID = $scope.selectedAgeGroup.AgeGroupID;
        $scope.demoGraphics.ageGroupLable = $scope.selectedAgeGroup.Name;
        $scope.filterdStatus.Demographics = ( $scope.demoGraphics.locationLable || $scope.demoGraphics.locationLable || $scope.demoGraphics.locationLable ) ? true : false;
        $scope.applyFiltersOptions();
      }
      
      $scope.addToRulePopup = function()
      {
        $('#addExistingRules').modal('show');
        var rules_ctrl = angular.element(document.getElementById('RulesCtrl')).scope();
        rules_ctrl.rule.Location = [];
        if($scope.location_filter)
        {
          rules_ctrl.rule.Location.push($scope.location_filter);
        }

        rules_ctrl.update_rule_gender($scope.filterOptions.Gender);
        rules_ctrl.update_rule_age_group($scope.selectedAgeGroup.AgeGroupID);
      }

      $scope.resetAllAppliedFilterOptions = function (filter_type) {
//        if ( $scope.filterdStatus.ShowMe ) {
//          $scope.showMeLabelName = 'All Posts';
//          $scope.ShowMe = angular.copy(defaultShowMeContent);
//        }
//        
//        if ( $scope.filterdStatus.Content ) {
//          $scope.searchTags = '';
//        }
//        
//        if ( $scope.filterdStatus.Type ) {
//          $scope.activeActivityType = 'All';
//        }
//        
//        if ( $scope.filterdStatus.Ownership ) {
//          $scope.PostedByLookedMore = [];
//        }
//        
//        
//        if ( $scope.filterdStatus.Demographics ) {
//          $scope.selectedAgeGroup = {};
//          $scope.demoGraphics.locationLable = '';
//          $scope.demoGraphics.genderLable = '';
//          $scope.demoGraphics.ageGroupLable = '';
//          $scope.demoGraphics.location = '';
//          $scope.demoGraphics.details = '';
//        }
//        
//        if ( $scope.filterdStatus.SortBy ) {
//          $scope.activeSortBy = $scope.sortByOptions.RecentPost.Label;
//        }
//        
//        $scope.filterOptions = angular.copy(defualtFilterOptions);
//        $scope.filterdStatus.ShowMe = false;
//        $scope.filterdStatus.Content = false;
//        $scope.filterdStatus.Type = false;
//        $scope.filterdStatus.Ownership = false;
//        $scope.filterdStatus.TimePeriod = false;
//        $scope.filterdStatus.Demographics = false;
//        $scope.filterdStatus.SortBy = false;

        switch (true) {
          case ( $scope.filterdStatus.ShowMe ):
            $scope.showMeLabelName = 'All Posts';
            $scope.ShowMe = angular.copy(defaultShowMeContent);
          case ( $scope.filterdStatus.Content ):
            $scope.searchTags = '';
          case ( $scope.filterdStatus.Type ):
            $scope.activeActivityType = 'All';
          case ( $scope.filterdStatus.Ownership ):
            $scope.PostedByLookedMore = [];
            $scope.filterdStatus.UserID = [];
          case ( $scope.filterdStatus.Demographics ):
            $scope.selectedAgeGroup = {};
            $scope.demoGraphics.locationLable = '';
            $scope.demoGraphics.genderLable = '';
            $scope.demoGraphics.ageGroupLable = '';
            $scope.demoGraphics.location = '';
            $scope.demoGraphics.details = '';
          case ( $scope.filterdStatus.SortBy ):
            $scope.activeSortBy = $scope.sortByOptions.RecentPost.Label;
          default:
            $scope.filterOptions = angular.copy(defualtFilterOptions);
            $scope.filterdStatus.ShowMe = false;
            $scope.filterdStatus.Content = false;
            $scope.filterdStatus.Type = false;
            $scope.filterdStatus.Ownership = false;
            $scope.filterdStatus.TimePeriod = false;
            $scope.filterdStatus.Demographics = false;
            $scope.filterdStatus.SortBy = false;
            $scope.filterdStatus.UserID = [];
            $scope.demoGraphics.locationLable = '';
            $scope.demoGraphics.genderLable = '';
            $scope.demoGraphics.ageGroupLable = '';
            $scope.filterOptions.WID = 1;
            $scope.filterOptions.WN = 'All';
            $scope.filterOptions.ActivityTypeFilter = 0;
            $scope.WID = '1';
            $scope.filterOptions.TID = 0;
            $scope.TID = 0;
            $scope.filterOptions.TName = 'All';
        }
        $scope.filterOptions.FilterType=filter_type;
       /* if(filter_type==1) {
          $scope.filterOptions.IsDailyDigest=1;
          $scope.filterOptions.IsAllQuestion=0;
        }
        if(filter_type==2) {
          $scope.filterOptions.IsDailyDigest=0;
          $scope.filterOptions.IsAllQuestion=1;
        }
        if(filter_type==3) {
          $scope.filterOptions.IsDailyDigest=0;
          $scope.filterOptions.IsAllQuestion=0;
          $scope.filterOptions.IsUserOrientation=0;
        }
        */
        
        $scope.applyFiltersOptions();
      }
      
      $scope.applyFiltersOptions = function () {

        var from = angular.element("#adminDashboardFilterDatepicker");
          var to = angular.element("#adminDashboardFilterDatepicker2");
          from.datepicker("option", "maxDate", 0);
          to.datepicker("option", "minDate", null);
          to.datepicker("option", "maxDate", 0);
          
        $scope.isFilterApplied = $scope.demoGraphics.genderLable || $scope.filterOptions.AgeGroupID || 
                $scope.filterdStatus.ShowMe || $scope.filterdStatus.Content || $scope.filterdStatus.Type || 
                $scope.filterdStatus.Ownership || $scope.filterdStatus.TimePeriod || $scope.filterdStatus.Demographics || 
                $scope.filterdStatus.SortBy || $scope.filterOptions.Verified || $scope.filterOptions.ActivityTypeFilter || $scope.filterOptions.WID!=1 || $scope.filterOptions.TID!=0 ;
        //console.log($scope.filterOptions.FilterType);
        if($scope.filterOptions.FilterType==1) {
          $scope.getDailyDigest(false, $scope.filterOptions);
        }else if($scope.filterOptions.FilterType==2) {
          $scope.getQuestionsList($scope.filterOptions);
        } else if($scope.filterOptions.FilterType==3) {
          $scope.getUserOrientation(false, $scope.filterOptions);
        } else {        

          $scope.getActivityList(false, $scope.filterOptions);
        }
        
        $rootScope.scroll_disable = false;
      }
      
  }]);

})();