<div ng-controller="GroupMemberCtrl" id="GroupMemberCtrl" ng-init="GroupDetail()" ng-cloak="">
    <?php $this->load->view('profile/profile_banner') ?>
    <!--Container-->
    <div class="container wrapper grp-wall" ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="GetWallPostInit()">
        <div class="row">
            <div class="col-md-2 col-sm-12"> 
                <?php $this->load->view('sidebars/left'); ?>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-12 pull-right sidebar">
                <?php $this->load->view('sidebars/right'); ?>
            </div>
            <!--<div class="col-sm-8 col-xs-12 pull-left" ng-class="{'col-md-9':IsSingleActivity == true, 'col-md-6':IsSingleActivity == false}">-->
            <div class="col-md-7 col-sm-8 col-xs-12 pull-left" ng-class="{'col-md-9':IsSingleActivity == true, 'col-md-7':IsSingleActivity == false}">
                                
                <div ng-include="AssetBaseUrl + 'partials/wall/wall2.html'" ></div>
                
                
             </div>
            <!--</div>-->
        </div>
          <?php $this->load->view('include/flag-modal'); ?>
    </div>
    <input type="hidden" id="post_type" name="post_type" value="1" />
    <input type="hidden" id="postGuid" name="postGuid" value="" />
    <input type="hidden" id="UserGUID" value="<?php echo $UserGUID; ?>" />
    <input type="hidden" id="WallPageNo" value="1" />
    <input type="hidden" id="FeedSortBy" value="2" />
    <input type="hidden" id="IsMediaExists" value="2" />
    <input type="hidden" id="PostOwner" value="" />
    <input type="hidden" id="ActivityFilterType" value="0" />
    <input type="hidden" id="AsOwner" value="0" />
    
    

