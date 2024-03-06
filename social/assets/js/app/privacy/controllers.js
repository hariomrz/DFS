app = angular.module('App').controller('PrivacyCtrl', ['GlobalService','$scope', 'appInfo', 'WallService', function(GlobalService, $scope, appInfo, WallService) {

    $scope.Options = [];
    $scope.Opt = [];
    $scope.IsReady = 0;
    $scope.userMuteSuorcePageNo = 1;
    $scope.UserMuteSorces = [];
    $scope.ShowMuteLoadMore = false;
    $scope.MutePageSize = 10;
    $scope.getUserMuteBusy = false;
    $scope.showUserMuteSource= false;
    $scope.ModlStg = settings_data;
    
    $scope.enabledSections = {contact : 0};

    $scope.getUserPrivacy = function(){
        var requestData = {};
        WallService.CallPostApi(appInfo.serviceUrl + 'privacy/details', requestData, function(successResp) {
            var response = successResp.data;
            if(response.ResponseCode == 200){
                $scope.OldPrivacy       = response.Data.Privacy;
                $scope.DefaultOptions   = response.Data.DefaultOptions;
                $scope.Label            = response.Data.Label;
                $scope.Privacy          = response.Data.Privacy;
                
                if($scope.Privacy == 'customize'){
                    $scope.customizeSetting = true;
                } else {
                    $scope.customizeSetting = false;
                }

                var CurrentPrivacy = $scope.capitalizeFirstLetter($scope.Privacy);
                angular.forEach($scope.Label,function(value,key){
                    $scope.Label[key].Customize = value[CurrentPrivacy];
                    $scope.enabledSections[value.Section] = 1;                    
                });
                
                

                $scope.setOptions();
                $scope.IsReady = 1;
            }
        }, function (error) {
          // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.settingEnabled = function (settingName) {
        if (settingName != undefined && settingName != 0) {
            return true;
        } else {
            return false;
        }
    }

    $scope.getFeedSetting = function () {
        var Url = 'privacy/news_feed_setting_details';
        var jsonData = {};
        WallService.CallApi(jsonData, Url).then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.newsFeedSetting = response.Data;
            } else {
                showResponseMessage(response.Message, 'alert-danger');
            }
        });
    }

    $scope.saveFeedSetting = function (k, newVal) {
        var Url = 'privacy/save_news_feed_setting';
        var settingArr = {};
        var postArr = [];
        angular.forEach($scope.newsFeedSetting, function (val, key) {
            if (k == key) {
                if (val == '1') {
                    obj = {
                        "Key": key,
                        "Value": "0"
                    };
                } else {
                    obj = {
                        "Key": key,
                        "Value": "1"
                    };
                }
            } else {
                obj = {
                    "Key": key,
                    "Value": val
                };
            }
            $scope.newsFeedSetting[key] = obj.Value;
            postArr.push(obj);
        });
        var jsonData = {
            news_feed_setting: postArr
        }
        WallService.CallApi(jsonData, Url).then(function (response) {
            if (response.ResponseCode == 200) {
                if (k !== 'rm') {
                    $scope.getFilteredWall();
                }
            } else {
                showResponseMessage(response.Message, 'alert-danger');
            }
        });
    }

    $scope.changePrivacy = function(val){
        $scope.Privacy = val;
        if(val!=='customize'){
            $('#customizeSetting').prop('checked',false);
            $scope.customizeSetting = false;
        } else {
            $('#customizeSetting').prop('checked',true);
            $scope.customizeSetting = true;
        }
        $scope.setOptions();
    }

    $scope.setOptions = function(){
        var CurrentPrivacy = $scope.capitalizeFirstLetter($scope.Privacy);
        angular.forEach($scope.Label,function(value,key){
            $scope.Label[key].Customize = value[CurrentPrivacy];
            $scope.Opt[value.Value] = value[CurrentPrivacy];
        });
        $scope.safeApply();
    }

    $scope.superPrivacy = function(){
        $('.privacy-ability > .radio > input[type="radio"]').on('change', function(){    
            if(!$(this).hasClass('selected')){
                $(this).parent('.radio').parent('.privacy-ability').children('.radio').removeClass('selected  disabled');
                $(this).parent('.radio').nextAll('.radio').addClass('disabled');
                $(this).parent().addClass('selected');
                
                $scope.changePrivacy('customize');
            }
        });
    }

    $scope.safeApply = function(fn) {
        var phase = this.$root.$$phase;
        if(phase == '$apply' || phase == '$digest') {
            if(fn && (typeof(fn) === 'function')) {
                fn();
            }
        } else {
            this.$apply(fn);
        }
    };

    $scope.checkCustomSettings = function(){
        if($scope.customizeSetting){
            $scope.customizeSetting = false;
        } else {
            $scope.customizeSetting = true;
        }
        if($scope.customizeSetting){
            $scope.changePrivacy('customize');
        } else {
            if($scope.OldPrivacy!=='customize'){                
                $scope.changePrivacy($scope.OldPrivacy);
            } else {
                $scope.changePrivacy('low');
            }
        }
    }

    $scope.getSelectedClass = function(key,val,disabled){
        var arr = {'self':1,'friend':2,'network':3,'everyone':4};
        var keyArr = arr[key];
        var valArr = arr[val];
        var cls = '';
        if(keyArr==valArr)
        {
            cls = 'selected';
        } else if(keyArr>valArr)
        {
            cls = 'disabled';
        }
        return cls;
    }

    $scope.changeOptVal = function(k,v,e){
        angular.forEach($scope.Label,function(value,key){
            if(value.Value == k){
                if($scope.Label[key].Customize == v){
                    $scope.Label[key].Customize = 'self';
                    $scope.Opt[k] = 'self';
                } else {
                    $scope.Label[key].Customize = v;
                }
            }
            $scope.changePrivacy('customize');
        });
    }

    $scope.resetPrivacySettings = function(){
        $scope.getUserPrivacy();
    }

    $scope.capitalizeFirstLetter = function(string){
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    $scope.savePrivacy = function(){
        var Options = [];
        angular.forEach($scope.Label,function(value,key){
            Options.push({Key:value.Value,Value:$scope.Opt[value.Value]});
        });
        var reqData = {Privacy:$scope.Privacy,Options:Options};
        WallService.CallPostApi(appInfo.serviceUrl + 'privacy/save', reqData, function(successResp) {
            var response = successResp.data;
            if(response.ResponseCode == 200){
                showResponseMessage(response.Message,'alert-success');
                $scope.getUserPrivacy();
            } else {
                showResponseMessage(response.Message,'alert-danger');
            }
        }, function (error) {
          // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }
    
    $scope.getPersonalizeSearchPlaceHolder = function() {   
        var placeholder = "Search users";
        if(parseInt(settings_data.m18)) {
            placeholder += ', pages';
        }
        
        if(parseInt(settings_data.m1) == 1) {
            placeholder += ', groups';
        }
        
        if(parseInt(settings_data.m14) == 1) {
            placeholder += ' or events';
        }
        
        return placeholder;
    }

    /*
     * Search popular sources
     */
    $scope.SearchPopularSorces = [];
    $scope.searchPrioritizeSources = function(){
        var reqData = {Search:$scope.PrioritizeSearchString,Type:'Prioritize'};
        WallService.CallPostApi(appInfo.serviceUrl + 'users/suggestion_list', reqData, function(successResp) {
            var response = successResp.data;
            if(response.ResponseCode == 200){
                $scope.SearchPopularSorces = response.Data;
            }
        }, function (error) {
          // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }
   
    /*
     * Add popular (Prioritize) sources
     */
    $scope.PrioritizeSources = function(Source){
        var reqData = {ModuleID:Source.ModuleID,ModuleEntityGUID:Source.ModuleEntityGUID};
        WallService.CallPostApi(appInfo.serviceUrl + 'users/prioritize_source', reqData, function(successResp) {
            var response = successResp.data;
            if(response.ResponseCode == 200){
                Source.Name = Source.Title;
                $scope.UserPrioritizeSorces.unshift(Source);
                //$scope.UserPrioritizeSorces.push(Source);
                $scope.SearchPopularSorces = [];
                $scope.PrioritizeSearchString = '';
                showResponseMessage(response.Message,'alert-success');
            }else{
                showResponseMessage(response.Message,'alert-danger');
            }
        });
    }

    /*
     * get User selected popular sources
     */
    $scope.userSuorcePageNo = 1;
    $scope.UserPrioritizeSorces = [];
    $scope.ShowPrioritizeLoadMore = false;
    $scope.PrioritizePageSize = 10;
    $scope.getPrioritizeBusy = false;
    $scope.getUserPrioritizeSources = function(){
        if($scope.getPrioritizeBusy){
            return;
        }
        $scope.getPrioritizeBusy = true;
        var reqData = {PageNo:$scope.userSuorcePageNo,PageSize:$scope.PrioritizePageSize};
        WallService.CallPostApi(appInfo.serviceUrl + 'users/prioritize_source_list', reqData, function(successResp) {
            var response = successResp.data;        
            if(response.ResponseCode == 200){
                angular.forEach(response.Data,function(value,key){
                    $scope.UserPrioritizeSorces.push(value);
                });
                $scope.userSuorcePageNo++;
                if(response.Data.length >= $scope.PrioritizePageSize){
                    $scope.ShowPrioritizeLoadMore = true;
                }else{
                    $scope.ShowPrioritizeLoadMore = false;
                }
                $scope.getPrioritizeBusy = false;
            }
        });
    }
    
    /*
     * Unprioritize source
     */
    $scope.unPrioritizeSources = function(Source,index){
        showConfirmBox("Unprioritize Source","Are you sure, you want to Unprioritize this source", function (e) {
            if (e) { 
                var reqData = {ModuleID:Source.ModuleID,ModuleEntityGUID:Source.ModuleEntityGUID};
                WallService.CallPostApi(appInfo.serviceUrl + 'users/un_prioritize_source', reqData, function(successResp) {
                    var response = successResp.data;
                    if(response.ResponseCode == 200){
                        $scope.UserPrioritizeSorces.splice(index, 1);
                        showResponseMessage(response.Message,'alert-success');
                    }else{
                        showResponseMessage(response.Message,'alert-danger');
                    }
                }, function (error) {
                  // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        });
    }
    
    
    /*
     * get User selected Mute sources
     */
    $scope.getUserMuteSources = function(isKeyPress){
        if(isKeyPress!==undefined){
            isKeyPress = true;
            PageNo = 1;
        }else{
            isKeyPress = false;
            PageNo = $scope.userMuteSuorcePageNo;
        }
        if($scope.getUserMuteBusy){
            return false;
        }
        $scope.getUserMuteBusy = true;
        var reqData = {Keyword:$scope.MuteSourcesString,PageNo:PageNo,PageSize:$scope.MutePageSize,Term:$scope.MuteSourcesString};
        WallService.CallPostApi(appInfo.serviceUrl + 'users/mute_source_list', reqData, function(successResp) {
            var response = successResp.data;
            if(response.ResponseCode == 200){
                if(isKeyPress){
                    $scope.UserMuteSorces = response.Data;
                }else{
                    angular.forEach(response.Data,function(value,key){
                        $scope.UserMuteSorces.push(value);
                    });
                    $scope.userMuteSuorcePageNo++;
                }
                if(response.Data.length >= $scope.MutePageSize){
                    $scope.ShowMuteLoadMore = true;
                }else{
                    $scope.ShowMuteLoadMore = false;
                }
                if($scope.UserMuteSorces.length > 0){
                    $scope.showUserMuteSource= true;
                    $('#MuteSorces').show();
                }
                $scope.getUserMuteBusy = false;
            }
        }, function (error) {
          // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }
    
    
    /*
     * un mute sources
     */
    $scope.unMuteSources = function(Source,index){

        showConfirmBox("Unmute Source","Are you sure, you want to Unmute this source", function (e) {
            if (e) { 
                var reqData = {ModuleID:Source.ModuleID,ModuleEntityGUID:Source.ModuleEntityGUID};
                WallService.CallPostApi(appInfo.serviceUrl + 'users/un_mute_source', reqData, function(successResp) {
                    var response = successResp.data;
                    if(response.ResponseCode == 200){
                        $scope.UserMuteSorces.splice(index, 1);
                        showResponseMessage(response.Message,'alert-success');
                    }else{
                        showResponseMessage(response.Message,'alert-danger');
                    }
                }, function (error) {
                  // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        });
    }
    
    $scope.ConverAndFormatTime = function(dateStr){
        var dateStr = convertTo24Hour(dateStr)+':00';
        localTime   = moment.utc(dateStr).toDate();
        utcDateTime = moment.tz(localTime, TimeZone).format('MMM DD');
        return utcDateTime;
    }
    
    $scope.PrioritizeRepeatDone = function(){
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
        });
    }
    

}]);

app.directive("autocompletedir", ['appInfo', 'WallService', function (appInfo, WallService) {
    return {
        restrict: "A",
        link: function (scope, elem, attr, ctrl) {
            elem.autocomplete({
                source: function (searchTerm, response) {
                    var reqData = {Search:searchTerm.term,Type:'Prioritize'};
                    WallService.CallPostApi(appInfo.serviceUrl + 'users/suggestion_list', reqData, function(successResp) {
                        var autocompleteResults = successResp.data;
                        if(autocompleteResults.ResponseCode == 200){
                           response(autocompleteResults.Data) ;
                        }
                    });
                },
                focus: function(event,ui) {
                    elem.val(ui.item.Title);
                    return false;
                },
                minLength: 2,
                select: function (event, selectedItem) {
                    // Do something with the selected item, e.g. 
                    scope.PrioritizeSources(selectedItem.item);
                    elem.val('');
                    scope.$apply();                   
                    event.preventDefault();
                }
            }).data("ui-autocomplete")._renderItem = function (ul, item) {
                 var apendlI= '<li>';
                    apendlI+='<div class="listing-content thumb-38">';
                    apendlI+=    '<a class="thumb-38"> ';
                    apendlI+=        '<img src="'+image_server_path+'upload/profile/220x220/'+item.ProfilePicture+'" class="img-circle" alt="" title="">';
                    apendlI+=    '</a>';
                    apendlI+=    '<div class="description"> ';
                    apendlI+=        '<a class="name">'+item.Title+'</a> ';
                            if(item.ModuleID==3){
                                //User case 
                    apendlI+=            '<div class="location">'+item.Location.Location+'</div>';
                            }
                            if(item.ModuleID==1 || item.ModuleID==18){
                                //Group case OR Page case 
                    apendlI+=            '<div class="location">'+item.Category+'</div>';
                            }
                            if(item.ModuleID==14){
                                //Events case 
                    apendlI+=            '<div class="location">'+scope.ConverAndFormatTime(item.DateTime)+'</div>';
                            }
                    apendlI+=    '</div>';
                    apendlI+='</div>';
                apendlI+='</li>';
                $(ul).addClass("dropdown-menu auto-suggestion mCustomScrollbar");
                return $(apendlI).appendTo(ul);
                
            }
//                return $("<li></li>")
//                    .append("<a>" + item.Title + "</a>")
//                    .appendTo(ul);
//                };
        }
    };
}]);