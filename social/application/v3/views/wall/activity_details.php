
<?php
    if($this->page_name == 'forum')
    {
        //echo "<div class='row'>";
    }
?>
<aside>
    <div class="overlay-div ng-scope" ng-if="postEditormode" ng-click="confirmCloseEditor();"></div>
    
    <div class="stiky-overlay" ng-class="{'active': isOverlayActive}" ng-click="toggleStickyPopup('close', popupType);"></div>
    <?php
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
           
                           
                            
                               <section class="news-feed" ng-cloak  ng-controller="NewsFeedCtrl" id="NewsFeedCtrl">                               
                                    <?php if(in_array($Activity_ModuleID, [33,34])) { ?>
                                        <div 
                                            
                                            id="activityFeedId-{{ data.ActivityID }}" 
                                            ng-repeat="data in activityData track by $index" 
                                            repeat-done="wallRepeatDone();" 
                                            ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index; initTagsItem($index); " 
                                            viewport-watch 
                                            ng-class="getCollpaseClass(data, 'feed-list')"
                                        >
                                        
                                        <div  class="inner-wall-post" ng-include="AssetBaseUrl + 'partials/wall/ForumPostDetail.html<?php echo version_control() ?>'" ></div>
                                        
                                        </div>
                                    <?php } else { ?>

                                      <div
                                            id="activityFeedId-{{ $index }}" 
                                            ng-repeat="data in activityData track by $index"
                                            repeat-done="wallRepeatDone();" 
                                            ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index; initTagsItem($index);"  
                                            ng-class="getCollpaseClass(data, 'feed-list')" 
                                            class="feed-list" 
                                            viewport-watch
                                            ng-class="{'overlay-content': data.stickynote}"
                                        >
                                        <div class="inner-wall-post" ng-include="getTemplateUrl(data)" ></div>
                                        </div>

                                    <?php } ?>
                                    
                                    
                                    <?php $this->load->view('include/feed-loader'); ?>
                                
                            </section>                            
                        
<?php
if ($IsGroup == '1')
{
    $this->load->view('groups/group_invite');
}
?>
</aside>
<?php
    if($this->page_name == 'forum')
    {
        //echo "</div>";
    }
?>
<!-- Poll popup ends -->
<?php 
if ($ModuleID == '18')
{
    $this->load->view('include/flag-modal');
} 
?> 
<!-- Chart popup ends -->

<?php 
    $this->load->view('include/wall-modal');
    if(!$this->settings_model->isDisabled(30)) {
        $this->load->view('poll/invite_popup');
    }
    $this->load->view('include/invite-modal-popup');
?>



<script>
var isPostDetailsPage = 1;
</script>