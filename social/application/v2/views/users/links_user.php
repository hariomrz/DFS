<div ng-controller="WallPostCtrl as WallPost">
    <?php $this->load->view('users/links_list_user') ?>
</div>

<input type="hidden" name="Type" id="Type"  value="<?php echo $Type; ?>"/>
<input type="hidden" id="FollowersPageNo" value="1" />
<input type="hidden" id="UID" value="<?php echo $UID ?>" />
<input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />