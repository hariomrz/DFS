<?php  if(!$this->settings_model->isDisabled(18)) : // Check if page module is enabled   ?>
<div ng-hide="postEditormode" ng-cloak class="panel-transparent">
    <div class="custom-panel">
        <div class="panel-heading transparent">
            <h5 class="uppercase" ng-bind="lang.w_my_pages"></h5>
        </div> 
        <ul class="listing-group">
            <li ng-repeat="user_pages in top_user_pages|limitTo:3">
                <div class="list-items-xs">
                    <div class="list-inner">
                        <figure>
                            <a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="loadbusinesscard">
                                <img ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{user_pages.ProfilePicture}}" class="img-circle"   />
                            </a>
                        </figure>
                        <div class="list-item-body">
                            <h4 class="list-heading-xs">
                                <a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url('page') ?>/{{user_pages.PageURL}}" class="a-link" ng-bind="user_pages.Title"></a>
                            </h4>
                            <div ng-if="LoginSessionKey!=='' && user_pages.FollowStatus=='0'" ng-click="toggleFollowPage(user_pages.PageID)" class="button-wrap-sm">
                                <button class="btn btn-default btn-xs" ng-bind="lang.w_follow_f_caps"></button>
                            </div>
                            <div ng-if="LoginSessionKey=='' && user_pages.FollowStatus=='0'" ng-click="likeEmit('', 'ACTIVITY', '');" class="button-wrap-sm">
                                <button class="btn btn-default btn-xs" ng-bind="lang.w_follow_f_caps"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </li> 
        </ul>
    </div> 
</div>
<?php endif; ?>