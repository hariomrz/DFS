<script type="text/javascript">
    var page_name = '<?php echo $page_name; ?>';
    var entity_type = '';
    var module_entity_guid = '<?php $ModuleEntityGUID; ?>';
    var log_view = '<?php echo (!isset($AllActivity)) ? 1 : 0 ?>';
    var module_id = '<?php echo $ModuleID; ?>';
    var is_admin = '<?php echo $IsAdmin; ?>';
    var default_privacy = '<?php echo (isset($DefaultPrivacy)) ? $DefaultPrivacy : 0; ?>';</script>

<div ng-controller="ForumCtrl" id="ForumCtrl" class="">
    <div ng-controller="WallPostCtrl" id="WallPostCtrl" ng-init="GetWallPostInit(); mediaSectionInit();">
        <div ng-view ng-init="filterPostType(post_type)"></div>
        <div ng-include="AssetBaseUrl + 'partials/widgets/breadcrumb.html' + app_version"></div>
        <div ng-include="AssetBaseUrl + 'partials/widgets/category_details.html' + app_version"></div>

        <div class="nav-tab-nav" data-scrollfix="scrollFix">
            <!--  secondary-nav -->
            <div class="container">
                <div class="nav-tab-filter">
                    <div class="filter-fixed ng-hide" ng-show="filterFixed" ng-cloak>
                        <button class="btn btn-default close-filter" ng-if="LoginSessionKey" ng-cloak ng-click="setFilterFixed(false)">
                            <span class="icon">
                                <i class="ficon-cross"></i>
                            </span>
                        </button>
                        <div class="main-filter-nav">
                            <nav class="navbar navbar-static">
                                <div ng-include="AssetBaseUrl + 'partials/forum/filter_options.html' + app_version"></div>
                            </nav>
                        </div>
                    </div>
                    <!-- //Filter Name -->
                    <div ng-include="AssetBaseUrl + 'partials/forum/nav.html' + app_version"></div>
                </div>
            </div>
        </div>
        <div class="container wrapper">
            <div class="row">
                <div class="col-sm-12">
                    <!-- Post Activity-->
                    <div ng-show="(category_detail.CanPostOnWall == '1' || !category_detail.Permissions.IsMember) && category_detail.IsDiscussionAllowed == '1'">
                        <div ng-include="AssetBaseUrl + 'partials/include/post/forum_category.html' + app_version"></div>
                    </div>
                    <!-- //Post Activity-->
                    <span ng-show="DetailPageLoaded == 0" class="loader text-lg" style="display:block;">&nbsp;</span>
                    <!-- media section begins here-->
                    <div ng-include="category_media"></div>
                    <!-- media section ends here-->
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="ForumID" value="<?php echo $ForumID ?>"/>
<input type="hidden" id="ForumCategoryID" value="<?php echo $ForumCategoryID ?>"/>
<input type="hidden" id="post_type" name="post_type" value="1"/>
<input type="hidden" id="postGuid" name="postGuid" value=""/>
<input type="hidden" id="UserGUID" value="<?php echo $this->session->userdata('UserGUID') ?>"/>
<input type="hidden" id="WallPageNo" value="1"/>
<input type="hidden" id="FeedSortBy" value="2"/>
<input type="hidden" id="IsMediaExists" value="2"/>
<input type="hidden" id="PostOwner" value=""/>
<input type="hidden" id="ActivityFilterType" value="0"/>
<input type="hidden" id="AsOwner" value="0"/>
<input type="hidden" id="IsWall" value="1"/>
<input type="hidden" id="IsForum" value="1"/>
<input type="hidden" id="CatMediaGUID" value=""/>
<input type="hidden" id="IsAdmin" value="<?php echo ($IsAdmin) ? '1' : '0'; ?>"/>
<input type="hidden" id="loginUserGUID" value="<?php echo $this->session->userdata('UserGUID'); ?>"/>
<input type="hidden" id="IsForumWall" value="1" />
<input type="hidden" id="IsSubCat" value="<?php echo isset($IsSubCat) ? $IsSubCat : 0; ?>">
<input type="hidden" id="IsForumWall" value="1" />
<input type="hidden" id="page_name" value="forum_media"/>