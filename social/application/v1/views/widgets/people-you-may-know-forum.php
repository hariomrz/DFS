<div ng-cloak class="panel panel-striped" data-ng-init="getPeopleYouMayKnow(2,0,0);" ng-show="peopleYouMayKnow.length>0">
    <div class="panel-heading">
        <h3 class="panel-title">
            <a target="_self" class="view" href="<?php echo site_url('network/grow_your_network') ?>" ng-bind="lang.view_all_caps"></a>
            <span class="text">{{lang.recommendation_for_you}}</span>
        </h3>
    </div>
    <div class="panel-body">
        <div class="list-vertical row">
            <div class="list-item col-xs-6" ng-repeat="peopleYouKnow in peopleYouMayKnow" repeat-done="triggerTooltip()">
                <figure>
                    <a target="_self" entitytype="user" entityguid="{{peopleYouKnow.UserGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url() ?>{{peopleYouKnow.ProfileURL}}" target="_self"> 
                        <img ng-if="peopleYouKnow.ProfilePicture!==''" ng-src="{{ImageServerPath+'upload/profile/220x220/'+peopleYouKnow.ProfilePicture}}"  class="img-circle"   err-Name="{{peopleYouKnow.FirstName+' '+peopleYouKnow.LastName}}" />

                        <span ng-if="peopleYouKnow.ProfilePicture=='' || peopleYouKnow.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(peopleYouKnow.FirstName+' '+peopleYouKnow.LastName)"></span></span>
                  </a>
                </figure>
                <a target="_self" entitytype="user" entityguid="{{peopleYouKnow.UserGUID}}" class="a-link name loadbusinesscard" ng-href="<?php echo site_url() ?>{{peopleYouKnow.ProfileURL}}" ng-bind="peopleYouKnow.FirstName+' '+peopleYouKnow.LastName" target="_self"></a>
                <span class="location" ng-if="peopleYouKnow.Interest.length==1" ng-bind="peopleYouKnow.Interest[0].Name"></span>
                <span class="location" ng-if="peopleYouKnow.Interest.length>1" ng-bind="peopleYouKnow.Interest[0].Name+' & other '+(peopleYouKnow.Interest.length-1)"></span>
                <span class="location" ng-if="peopleYouKnow.MutualFriends=='1'" ng-bind="peopleYouKnow.MutualFriends+' Mutual Friend'"></span>
                <span class="location" ng-if="peopleYouKnow.MutualFriends>'1'" ng-bind="peopleYouKnow.MutualFriends+' Mutual Friends'"></span>
                <div class="button-wrap-sm">
                    <button ng-cloak ng-if="peopleYouKnow.SentRequest=='1'" ng-click="rejectRequest(peopleYouKnow.UserGUID,'peopleyoumayknow')" class="btn btn-default btn-xs" ng-bind="lang.w_cancel_request"></button>
                    <button ng-cloak ng-if="peopleYouKnow.SentRequest=='0'" ng-click="sendRequest(peopleYouKnow.UserGUID,'peopleyoumayknow')" class="btn btn-default btn-xs" ng-bind="lang.w_add_as_friend"></button>
                </div>
                <a target="_self" ng-click="hideSuggestedPeople(peopleYouKnow.UserGUID)" class="remove"><i class="ficon-cross"></i></a>
            </div>
        </div>
    </div>
</div>