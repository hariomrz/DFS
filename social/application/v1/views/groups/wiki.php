<div ng-controller="GroupMemberCtrl" id="GroupMemberCtrl" ng-init="GroupDetail()" ng-cloak="">
    <?php $this->load->view('profile/profile_banner') ?>
    <!--Container-->
    <?php $this->load->view('wiki/wiki',array('ShowFilter'=>'0','ShowClass'=>'1')) ?>
    <!-- <input type="hidden" id="hdngrpid" name="hdngrpid" value="<?php if(!empty($ModuleEntityID) && isset($ModuleEntityID)) {echo $ModuleEntityID ;  }?>" /> -->
    <input type="hidden" id="post_type" name="post_type" value="1" />
    <input type="hidden" id="postGuid" name="postGuid" value="" />
    <input type="hidden" id="UserGUID" value="<?php echo $UserGUID; ?>" />
    <input type="hidden" id="WallPageNo" value="1" />
    <input type="hidden" id="FeedSortBy" value="2" />
    <input type="hidden" id="IsMediaExists" value="2" />
    <input type="hidden" id="PostOwner" value="" />
    <input type="hidden" id="ActivityFilterType" value="0" />
    <input type="hidden" id="AsOwner" value="0" />
