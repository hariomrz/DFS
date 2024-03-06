<div ng-controller="WallPostCtrl as WallPost">
    <div data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" data-ng-init="GetEventDetail('<?php echo $auth['EventGUID'] ?>'); initialize('<?php echo $Section; ?>');">
        <?php $this->load->view('users/links_list') ?>
    </div>
    
</div>