<div ng-cloak class="panel panel-default" ng-init="getUserConnection()" ng-show="userConnection.TotalRecords>0">
    <div class="panel-heading p-heading">
        <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
            <h3 ng-cloak>{{::lang.w_my_connection_caps}} ({{userConnection.TotalRecords}})</h3>
        <?php } else { ?>
            <h3 ng-cloak>
                <span class="capt" ng-bind="userConnection.TotalRecords+' Connections'"></span> 
                <span ng-if="userConnection.MutualFriendCount>=1" ng-bind="'('+userConnection.MutualFriendCount+' Mutual)'"></span>
            </h3>
        <?php } ?>
    </div>
    <div class="panel-body">
        <div class="list-vertical row">
            <div class="list-item col-xs-6" ng-repeat="friends in userConnection.Members">
                <figure>
                    <a target="_self" ng-href="{{'<?php echo site_url() ?>'+friends.ProfileLink}}">
                        <img ng-if="friends.ProfilePicture!==''"   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+friends.ProfilePicture}}">
                        <img ng-if="friends.ProfilePicture==''"   class="img-circle" ng-src="{{SiteURL+'assets/img/profiles/'}}" err-Name="{{friends.FirstName+' '+friends.LastName}}">
                    </a>
                </figure>
                <a target="_self" ng-href="{{'<?php echo site_url() ?>'+friends.ProfileLink}}" class="a-link name" ng-bind="friends.FirstName+' '+friends.LastName"></a> 
                <div class="button-wrap-sm" ng-cloak ng-if="friends.FriendStatus=='2' || friends.FriendStatus=='4' ">
                    <button ng-cloak ng-if="friends.FriendStatus=='2'" ng-click="rejectRequest(friends.UserGUID,'connectionwidget')" class="btn btn-default btn-xs" ng-bind="lang.cancel_request"></button>
                    <button ng-cloak ng-if="friends.FriendStatus=='4'" ng-click="sendRequest(friends.UserGUID,'connectionwidget')" class="btn btn-default btn-xs" ng-bind="lang.send_request"></button>
                </div>
                <a target="_self" class="remove" ng-click="newConnection(friends.UserGUID)"><i class="ficon-cross"></i></a>
            </div>
            <div class="footer-link">
                <a target="_self" ng-href="{{'<?php echo site_url() ?>'+Username+'/connections'}}" class="pull-right" ng-bind="lang.see_all"></a>
            </div>
        </div>
    </div>
</div>