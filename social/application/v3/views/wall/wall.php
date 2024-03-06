<aside class="col-md-8 col-sm-8 col-xs-12 pull-left">
    <?php
//} 
if (!isset($AllActivity))
{
    switch ($ModuleID)
    {
        case 3 :
            $EntityType = 'User';
            break;
        case 1 :
            $EntityType = 'Group';
            break;
        case 14 :
            $EntityType = 'Event';
            break;
        case 19 :
            $EntityType = 'Activity';
            break;
        case 21 :
            $EntityType = 'Media';
            break;
        case 13 :
            $EntityType = 'Album';
            break;
        case 18 :
            $EntityType = 'Page';
            break;
        default:
            $EntityType = '';
            break;
    }
    ?>
        <input type="hidden" ng-controller="logCtrl" ng-init="viewCount('<?php echo $EntityType ?>', '<?php echo $ModuleEntityGUID ?>')" />
        <?php
}  
?>
            <input type="hidden" id="FeedSortBy" value="2" />
            <input type="hidden" id="IsMediaExists" value="2" />
            <input type="hidden" id="PostOwner" value="" />
            <input type="hidden" id="ActivityFilterType" value="0" />
            <input type="hidden" id="AsOwner" value="0" />
            <div class="">
                <div role="tabpanel">
                    <?php 
        if (!isset($AllActivity))
        {
            ?>
                        <ul ng-if="wallReqCnt > 1 || tr > 0" id="mytabs" role="tablist" class="nav nav-pills margin-bottom-18">
                            <li ng-cloak ng-if="tr == 1" class="all-post active">
                                <a onClick="applySearchFilter('Fav', '0')" href="javascript:void(0);">
                                    <?php echo lang('post') ?> (<span ng-bind="tr"></span>)</a>
                            </li>
                            <li ng-cloak ng-if="tr > 1" class="all-post active">
                                <a onClick="applySearchFilter('Fav', '0')" href="javascript:void(0);">
                                    <?php echo lang('post') . lang('plural'); ?> (<span ng-bind="tr"></span>)</a>
                            </li>
                            <li ng-cloak ng-if="tfr == 1" class="fav-post">
                                <a onClick="applySearchFilter('Fav', '1')" href="javascript:void(0);">
                                    <?php echo lang('favourite') ?> (<span ng-bind="tfr"></span>)</a>
                            </li>
                            <li ng-cloak ng-if="tfr > 1" class="fav-post">
                                <a onClick="applySearchFilter('Fav', '1')" href="javascript:void(0);">
                                    <?php echo lang('favourite') . lang('plural'); ?> (<span ng-bind="tfr"></span>)</a>
                            </li>
                            <?php if ($IsAdmin && $ModuleID == 18 && (isset($ActivityGUID) && $ActivityGUID=='' ) )
{ ?>
                            <li ng-cloak ng-if="tflgr == 1" class="flg-post">
                                <a onClick="applySearchFilter('Flg', '2')" href="javascript:void(0);">
                                    <?php echo lang('Flag') ?> (<span ng-bind="tflgr"></span>)</a>
                            </li>
                            <li ng-cloak ng-if="tflgr > 1" class="flg-post">
                                <a onClick="applySearchFilter('Flg', '2')" href="javascript:void(0);">
                                    <?php echo lang('Flag') . lang('plural'); ?> (<span ng-bind="tflgr"></span>)</a>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                        <div class="tab-content">
                            <div ng-cloak ng-show="IsReminder == 1 && ReminderFilter == 1" class="reminder-filter-view">
                                <ul class="filter-tags">
                                    <li ng-repeat="RFD in ReminderFilterDate">
                                        <span ng-bind="getReminderDateFormat(RFD)"></span>
                                        <i ng-click="clearReminderFilter(RFD);" class="icon-n-close">&nbsp;</i>
                                    </li>
                                </ul>
                            </div>
                            <section class="news-feed" ng-cloak>
                                <activity-item repeat-done="wallRepeatDone();" loggedinname="{{LoggedInName}}" loggedinprofilepicture="{{LoggedInProfilePicture}}" class="wall-post" ng-repeat="postItem in activityData track by $index" data="postItem" index="{{$index}}" ng-if="(postItem.ActivityType != 'AlbumAdded' && postItem.ActivityType != 'AlbumUpdated') || postItem.Album.length > 0" ng-cloak viewport-watch>
                                </activity-item>
                            </section>
                            <div class="panel panel-info" ng-if="(tr == 0 || (trr == 0 && IsReminder == 1)) && IsSinglePost == 0" ng-cloak>
                                <div class="panel-body nodata-panel">
                                <div class="nodata-text p-v-lg">
                                  <span class="nodata-media">
                                    <img src="<?php echo ASSET_BASE_URL ?>img/blank-wall-img.png" >
                                  </span>
                                  <h4 class="text-muted">No conversations yet</h4>
                                  <p class="text-off">Liven things up and start a new conversation.</p>
                                </div>
                              </div>
                            </div>     

                            <div class="panel panel-info"  ng-if="(tr == 0 || (trr == 0 && IsReminder == 1)) && IsSinglePost !== 0" ng-cloak>
                                <div class="panel-body nodata-panel">
                                <div class="nodata-text p-v-lg">
                                  <span class="nodata-media">
                                    <img src="<?php echo ASSET_BASE_URL ?>img/blank-wall-img.png" >
                                  </span>
                                    <p class="text-off">The content you requested cannot be displayed right now. Its not accessible for you.</p>
                                </div>
                              </div>
                            </div> 
                        </div>
                </div>
                <div class="wallloader">
                    <div class="spinner32"></div>
                </div>
            </div>
            <?php
if ($IsGroup == '1')
{
    $this->load->view('groups/group_invite');
}
?>
</aside>

<?php $this->load->view('include/wall-modal') ?>

<!-- Poll popup ends -->
<?php if ($ModuleID == '18')
{ ?>
<div class="modal fade " id="flagModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title" id="myModalLabel3" ng-if="flagUserData.length >0"><span ng-if="flagUserData.length <=1" ng-bind=" flagUserData.length +' Flag'"> </span><span ng-if="flagUserData.length >1" ng-bind=" flagUserData.length +' Flags'"> </span> </h4>
            </div>
            <div class="modal-body padd-l-r-0 non-footer">
                <div class="designer-scroll mCustomScrollbar">
                    <ul class="list-group ">
                        <li ng-repeat="flag_user_data in flagUserData" class="list-group-item ">
                            <figure>
                                <a ng-href="<?php echo site_url() ?>{{flag_user_data.ProfileURL}}">
                                        <img   class="img-circle" ng-if="flag_user_data.ProfilePicture!==''" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{flag_user_data.ProfilePicture}}">
                                    </a>
                            </figure>
                            <div class="description">
                                <a ng-href="<?php echo site_url() ?>{{flag_user_data.ProfileURL}}" class="name" ng-bind="flag_user_data.FirstName+' '+flag_user_data.LastName"></a>
                                <span class="location" ng-if="flag_user_data.CityName !== '' &&  flag_user_data.CountryName !== '' " ng-bind="flag_user_data.CityName+', '+flag_user_data.CountryName"></span>
                                <span class="black">Reason: <span ng-bind="flag_user_data.FlagReason"></span> </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <!--  <div class="modal-footer">                    
                    <div class="pull-right">
                        <a href="javascript:void(0)" class="btn-link">Remove</a>
                        <button class="btn  btn-primary btn-icon" type="button">Mark Clean</button>
                    </div>
                </div>-->
        </div>
    </div>
</div>
<?php } ?> 
<!-- Chart popup ends -->
<?php
if(!$this->settings_model->isDisabled(30)) {
    $this->load->view('poll/invite_popup');
}
?>
