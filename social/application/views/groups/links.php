<div ng-controller="WallPostCtrl as WallPost">
    <div ng-controller="GroupMemberCtrl" id="GroupMemberCtrl" ng-init="GroupDetail()">
    <?php $this->load->view('users/links_list') ?>

    <input type="hidden" id="hdn_module_id" name="hdn_module_id" value="<?php if(!empty($ModuleID) && isset($ModuleID)) {echo $ModuleID ;  }?>" />
    <input type="hidden" id="post_type" name="post_type" value="1" />
    <input type="hidden" id="postGuid" name="postGuid" value="" />
    <input type="hidden" id="WallPageNo" value="1" />
    </div>
</div>  