<?php  if(!$this->settings_model->isDisabled(18)) : // Check if page module is enabled   ?>
<div class="panel panel-default" ng-init="get_top_user_pages()" ng-cloak>
    <div class="panel-heading p-heading">
        <h3 ng-cloak="">
            <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
                {{::lang.w_my_pages}}
                <a target="_self" class="pull-right gray-clr" href="<?php echo site_url('pages/types') ?>" ng-bind="lang.create"></a>
                <?php }else{ ?>
                <span ng-cloak="" class="capt" ng-bind="FirstName +'\'s Pages' "></span>
            <?php } ?>
        </h3>
    </div>
    <div ng-if="pages_length==0" class="blank-view">
        <img class="img-circle" src="<?php echo ASSET_BASE_URL ?>img/page-default.jpg" />
      </div>
    <div class="panel-body">
        <ul class="list-group thumb-30">
            <li ng-repeat="user_pages in top_user_pages|limitTo:3">
                <figure>
                    <a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="loadbusinesscard"><img ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{user_pages.ProfilePicture}}" class="img-circle"   /></a>
                </figure>
                <div class="description">
                    <a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url('page') ?>/{{user_pages.PageURL}}" class="a-link" ng-bind="user_pages.Title"></a>
                    <!-- <span class="location" ng-bind="user_pages.NoOfFollowers+' Followers'"></span>
                    <div ng-bind="user_pages.Description"></div> -->
                    <div ng-if="LoginSessionKey!=='' && user_pages.FollowStatus=='0'" ng-click="toggleFollowPage(user_pages.PageID)" class="button-wrap-sm">
                        <button class="btn btn-default btn-xs" ng-bind="lang.w_follow_f_caps"></button>
                    </div>
                    <div ng-if="LoginSessionKey=='' && user_pages.FollowStatus=='0'" ng-click="likeEmit('', 'ACTIVITY', '');" class="button-wrap-sm">                        
                    <button class="btn btn-default btn-xs" ng-bind="lang.w_follow_f_caps"></button>
                    </div>
                </div>
            </li>
        </ul>
        <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
        <div class="footer-link">
            <a target="_self" class="pull-right" href="<?php echo site_url('pages') ?>" ng-bind="lang.see_all"></a>
        </div>
        <?php } ?>
    </div>
</div>

<?php endif; ?>