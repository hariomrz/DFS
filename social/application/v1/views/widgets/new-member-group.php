<div ng-cloak class="feed-title">Suggested Group</div>
<div ng-cloak class="news-feed-listing" ng-controller="GroupPageCtrl" id="GroupPageCtrl" ng-init="suggestedGroupList(2,'0',0)">
    <div class="feed-body">
        <ul class="list-group suggested-feed">
            <li ng-repeat="list in listObj = suggestedlist">
                <figure>
                    <a entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="<?php echo base_url();?>group/wall/{{list.GroupID}}"><img ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle" alt="" title=""></a>
                </figure>
                <div class="description">
                    <a class="name" href="<?php echo base_url();?>group/wall/{{list.GroupID}}" ng-bind="list.GroupName"> <span class="group-secure"><i class="icon-n-global"></i></span></a>
                    <button href="javascript:void(0);" ng-click="joinPublicGroup(list.GroupGUID,'fromNewsFeed')" class="btn btn-default btn-xs pull-right m-r-15">Join</button>
                    <ul class="activity-nav">
                        <li><i class="ficon-followers">&nbsp;</i><a ng-bind="list.MemberCount+' Members'"></a> <span ng-if="list.FriendCount>0">&amp;</span> <a ng-if="list.FriendCount>0" ng-bind="list.FriendCount+' Friends'"></a></li>
                        <!-- <li><span class="gray-clr">Active :</span> High</li> -->                    </ul>
                </div>
                <p ng-bind="list.GroupDescription"></p>
                <a class="remove"><i class="ficon-cross"></i></a>
            </li>
        </ul>
    </div>
</div>