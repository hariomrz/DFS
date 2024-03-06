<?php if($this->session->userdata('LoginSessionKey')){ ?>
<div ng-controller="UserProfileCtrl" ng-cloak ng-init="getPeopleYouMayFollow(10,0,0)" ng-show="peopleYouMayFollow.length>0" class="panel panel-widget">
    <div class="panel-heading">
            <h3 class="panel-title">{{lang.recommendation_for_you}}</h3>
    </div>
    <div class="panel-body">
        <div class="people-you-know">
            <ul class="vertical-list" ng-cloak ng-if="peopleYouMayFollow.length>0" id="peopleYouknow">
                <slick class="slider" settings="peopleYouMayKnowConfig">
                    <li ng-cloak ng-if="peopleYouMayFollow.length>0" ng-repeat="(personYouMayFollowKey, personYouMayFollow) in peopleYouMayFollow" repeat-done="triggerTooltip();" class="ng-scope">
                        <div class="list-inner">
                            <figure>
                                <a target="_self" target="_self" entitytype="user" entityguid="{{personYouMayFollow.UserGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url() ?>{{::personYouMayFollow.ProfileURL}}" target="_self"> 
                                    <img err-name="{{personYouMayFollow.FirstName+' '+personYouMayFollow.LastName}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+personYouMayFollow.ProfilePicture}}"  class="img-circle"   err-Name="{{personYouMayFollow.FirstName+' '+personYouMayFollow.LastName}}" />
                                </a>
                            </figure>
                            <div class="list-item-body">
                                <h4 class="list-heading-xs ellipsis">
                                    <a target="_self" target="_self" entitytype="user" entityguid="{{personYouMayFollow.UserGUID}}" class="a-link name loadbusinesscard" ng-href="<?php echo site_url() ?>{{::personYouMayFollow.ProfileURL}}" target="_self">{{::personYouMayFollow.FirstName+' '+personYouMayFollow.LastName}}</a>
                                </h4>
                                <small class="location" >&nbsp;</small>
                                <button ng-cloak ng-if="personYouMayFollow.SentRequest" ng-click="follow(personYouMayFollow.UserGUID, peopleYouMayFollow, personYouMayFollow, personYouMayFollowKey, 'peopleYouMayFollow', 1, 0)" class="btn btn-default btn-block" ng-bind="lang.w_unfollow">
                                </button>
                                <button ng-cloak ng-if="!personYouMayFollow.SentRequest" ng-click="follow(personYouMayFollow.UserGUID, peopleYouMayFollow, personYouMayFollow, personYouMayFollowKey, 'peopleYouMayFollow', 1, 1);" class="btn btn-default btn-block" ng-bind="lang.w_follow_f_caps">
                                </button>
                            </div>
                        </div>
                    </li>
                </slick>
            </ul>
        </div> 
    </div>
    <div class="panel-footer">
        <a target="_self" target="_self" class="view-link" ng-href="<?php echo site_url('network/grow_your_network') ?>" ng-bind="lang.view_all"></a>
    </div>
</div>
<?php } ?>