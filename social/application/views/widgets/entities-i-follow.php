<?php  if ($this->session->userdata('UserID') == $UserID) { ?>
<div ng-cloak class="panel panel-default" ng-init="get_entities_i_follow()" ng-show="entities_i_follow.length>0">
    <div class="panel-heading p-heading">
        <h3 ng-bind="lang.w_i_follow"></h3>
    </div>
    <div class="panel-body">
        <ul class="list-group thumb-30">
            <li ng-repeat="user_pages in entities_i_follow|limitTo:5">
                <figure>
                    <a><img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-src="{{ImageServerPath}}upload/profile/220x220/{{user_pages.ProfilePicture}}" class="img-circle"   /></a>
                    
                </figure>
                <div class="description">
                    <a target="_self" ng-if="user_pages.ModuleID=='3'" ng-href="<?php echo site_url() ?>{{user_pages.ProfileUrl}}" class="a-link" ng-bind="user_pages.FirstName+' '+user_pages.LastName"></a>
                    <a target="_self" ng-if="user_pages.ModuleID=='18'" ng-href="<?php echo site_url('page') ?>/{{user_pages.ProfileUrl}}" class="a-link" ng-bind="user_pages.FirstName+' '+user_pages.LastName"></a> 
                    <div>
                        <!--<div ng-click="toggleFollowPage(user_pages.ModuleEntityGUID,user_pages.ModuleID,1)" class="button-wrap-sm">
                            <button ng-if="user_pages.FollowStatus=='1'" class="btn btn-default btn-xs">Unfollow</button>
                            <button ng-if="user_pages.FollowStatus=='0'" class="btn btn-default btn-xs">Follow</button>
                        </div>-->
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
<?php } ?>