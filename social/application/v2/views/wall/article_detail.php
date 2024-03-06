<div ng-controller="ActivityDetailsCtrl" ng-init="initActivityDetails()">
     <?php $this->load->view('widgets/breadcrumb-forum_all_types') ?>
    <div class="main-container article-container">
        <div class="wrapper">
            <div ng-controller="WallPostCtrl" id="WallPostCtrl" ng-init="GetWallPostInit()">                
                <?php //$this->load->view('include/post/newsfeed'); ?>                
                <div ng-include="AssetBaseUrl + 'partials/wall/wall2.html'" ></div>                    
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="ForumID" value="<?php echo $ForumID ?>" />
<input type="hidden" id="ForumCategoryID" value="<?php echo $ForumCategoryID ?>" />
<input type="hidden" id="post_type" name="post_type" value="1" />
<input type="hidden" id="postGuid" name="postGuid" value="" />
<input type="hidden" id="UserGUID" value="<?php echo $this->session->userdata('UserGUID') ?>" />
<input type="hidden" id="WallPageNo" value="1" />
<input type="hidden" id="FeedSortBy" value="2" />
<input type="hidden" id="IsMediaExists" value="2" />
<input type="hidden" id="PostOwner" value="" />
<input type="hidden" id="ActivityFilterType" value="0" />
<input type="hidden" id="AsOwner" value="0" />
<input type="hidden" id="IsWall" value="1" />
<input type="hidden" id="IsForum" value="1" />
<input type="hidden" id="CatMediaGUID" value="" />
<input type="hidden" id="IsAdmin" value="<?php echo ($IsAdmin) ? '1' : '0' ; ?>" />
<input type="hidden" id="Activity_ModuleID" value="<?php echo $Activity_ModuleID; ?>" />
<input type="hidden" id="Activity_ModuleEntityID" value="<?php echo $Activity_ModuleEntityID ?>" />