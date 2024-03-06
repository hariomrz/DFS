<div ng-controller="WallPostCtrl as WallPost">
    <div data-ng-controller="PageCtrl" id="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"]; ?>'); GetPageDetails('<?php echo $PageGUID; ?>')">
        <?php $this->load->view('users/files_list') ?>
    </div>
</div>