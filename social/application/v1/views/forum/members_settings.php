<div ng-controller="WallPostCtrl" id="WallPostCtrl" ng-cloak="">
    <div ng-controller="ForumCtrl" id="ForumCtrl" ng-cloak="">
        <div ng-include="AssetBaseUrl + 'partials/forum/members_settings.html'"></div>
    </div>
</div>

<input type="hidden" id="ForumID" value="<?php echo $ForumID ?>" />
<input type="hidden" id="ForumCategoryID" value="<?php echo $ForumCategoryID ?>" />
<input type="hidden" id="PageNo" value="1" />