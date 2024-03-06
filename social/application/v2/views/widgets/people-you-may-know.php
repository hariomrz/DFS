<div ng-cloak 
     
     <?php if(empty($this->disablePeopleYouMayKnowAPI)) echo 'ng-init="getPeopleYouMayKnow(10,0,0)"'; ?>
     
     ng-show="peopleYouMayKnow.length>0 && LoginSessionKey!=='' && !IsMyDeskTab" class="panel panel-widget">
    <div class="panel-heading">
            <h3 class="panel-title">{{lang.recommendation_for_you}}</h3>
    </div>
    <div class="panel-body">
        <div class="people-you-know">
            <ul class="vertical-list" ng-cloak ng-if="peopleYouMayKnow.length>0" id="peopleYouknow">
                <slick class="slider" settings="peopleYouMayKnowConfig">
                    <li ng-cloak ng-if="peopleYouMayKnow.length>0" ng-repeat="peopleYouKnow in peopleYouMayKnow" repeat-done="triggerTooltip();" class="ng-scope">
                        <div class="list-inner">
                            <figure>
                                <a target="_self" entitytype="user" entityguid="{{peopleYouKnow.UserGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url() ?>{{::peopleYouKnow.ProfileURL}}" target="_self"> 
                                    <img err-name="{{peopleYouKnow.FirstName+' '+peopleYouKnow.LastName}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+peopleYouKnow.ProfilePicture}}"  class="img-circle"   err-Name="{{peopleYouKnow.FirstName+' '+peopleYouKnow.LastName}}" />
                                </a>
                            </figure>
                            <div class="list-item-body">
                                <h4 class="list-heading-xs ellipsis">
                                    <a target="_self" entitytype="user" entityguid="{{peopleYouKnow.UserGUID}}" class="a-link name loadbusinesscard" ng-href="<?php echo site_url() ?>{{::peopleYouKnow.ProfileURL}}" target="_self">{{::peopleYouKnow.FirstName+' '+peopleYouKnow.LastName}}</a>
                                </h4>
                                <small class="location" ng-if="peopleYouKnow.MutualFriends=='0'">&nbsp;</small>
                                <small class="location" ng-if="peopleYouKnow.MutualFriends=='1'">{{::peopleYouKnow.MutualFriends+' Mutual Friend'}}</small>
                                <small class="location" ng-if="peopleYouKnow.MutualFriends>'1'">{{::peopleYouKnow.MutualFriends+' Mutual Friends'}}</small>

                                <button ng-cloak ng-if="peopleYouKnow.SentRequest=='1'" ng-click="rejectRequest(peopleYouKnow.UserGUID,'peopleyoumayknow')" class="btn btn-default btn-sm" ng-bind="lang.w_cancel_request"></button>
                                <button ng-cloak ng-if="peopleYouKnow.SentRequest=='0'" ng-click="sendRequest(peopleYouKnow.UserGUID,'peopleyoumayknow');" class="btn btn-default btn-sm"><i class="ficon-add-friend"></i>  {{::lang.w_add_as_friend}}</button>
                            </div>
                        </div>
                    </li>
                </slick>
            </ul>
        </div> 
    </div>
    <div class="panel-footer">
        <a target="_self" ng-if="LoginSessionKey!=''" class="view-link" ng-href="<?php echo site_url('network/grow_your_network') ?>" ng-bind="lang.view_all"></a>
        <a target="_self" ng-if="LoginSessionKey==''" class="view-link" ng-click="loginRequired()" ng-bind="lang.view_all"></a>
    </div>
</div>
