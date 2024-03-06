<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><?php echo lang('AnalyticsTools_Tools'); ?></li>
                    <li>/</li>
                    <li><span><?php echo lang('ManagePermission_ManagePermission'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!--/Bread crumb-->
<section class="main-container">
    <div class="container" ng-controller="GroupCtrl" ng-init="get_group_permission();">

        <div class="page-heading">
            <h2 class="page-title">Manage Permission</h2>
        </div>
        <!-- Manage Groups -->
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-3">
                    <h5>Manage Groups</h5>
                </div>
                <div class="col-xs-9">
                    <ul class="list-group border-top gutter-v-5">
                        <li class="list-group-item">
                            <div class="form-group row">
                                <div class="col-xs-3">
                                    <label class="control-label-default">All actions</label>
                                </div>
                                <div class="col-xs-9">
                                    <div class="form-group">
                                        <div class="checkbox-list">
                                            <label class="checkbox checkbox-inline">
                                                <input type="checkbox" name="" ng-model="discussion_check" ng-value="1" ng-click="disable_setting('discussion');">
                                                <span class="label">Discussion</span>
                                            </label>
                                            <label class="checkbox checkbox-inline">
                                                <input type="checkbox" name="" ng-value="1" ng-model="qa_check" ng-click="disable_setting('qa_check');">
                                                <span class="label">Q & A</span>
                                            </label>
                                            <label class="checkbox checkbox-inline">
                                                <input type="checkbox" ng-value="1" name="" ng-model="kb_check" ng-click="disable_setting('kb_check');">
                                                <span class="label">Article</span>
                                            </label>
                                            <label class="checkbox checkbox-inline">
                                                <input type="checkbox" ng-value="1" name="" ng-model="announcements_check" ng-click="disable_setting('announcements_check');">
                                                <span class="label">Announcements</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="form-group row">
                                <div class="col-xs-3">
                                    <label class="control-label-default">Discussion</label>
                                </div>
                                <div class="col-xs-9">
                                    <tags-input ng-disabled="discussion_checked" ng-model="discussion" placeholder="click to select users, groups" display-property="text" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_merge_subcategory($tag)" on-tag-removed="tagRemoved_merge_subcategory($tag)">
                                        <auto-complete source="loadTags($query)" load-on-focus="true" load-on-empty="true" min-length="2" max-results-to-show="32" highlight-matched-text=true template="discussion-template"></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="discussion-template">
                                        <div class="tag-template">
                                            <div>
                                                <span>{{$getDisplayText()}}</span>
                                                <span ng-if='data.ModuleID=="1" && data.Privacy=="1"' class="icons group-type" tooltip data-placement="top" title="Public">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnGobal"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='0'" class="icons group-type" tooltip data-placement="top" title="Closed">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnLock"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='2'" class="icons group-type" tooltip data-placement="top" title="Secret">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#iconSecret"></use>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </script>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="form-group row">
                                <div class="col-xs-3">
                                    <label class="control-label-default">Q & A</label>
                                </div>
                                <div class="col-xs-9">
                                    <tags-input ng-model="question" ng-disabled="qa_checked" placeholder="click to select users, groups" display-property="text" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_merge_subcategory($tag)" on-tag-removed="tagRemoved_merge_subcategory($tag)">
                                        <auto-complete source="loadTags($query)" load-on-focus="true" load-on-empty="true" min-length="2" max-results-to-show="32" highlight-matched-text=true template="question-template"></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="question-template">
                                        <div class="tag-template">
                                            <div>
                                                <span>{{$getDisplayText()}}</span>
                                                <span ng-if='data.ModuleID=="1" && data.Privacy=="1"' class="icons group-type" tooltip data-placement="top" title="Public">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnGobal"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='0'" class="icons group-type" tooltip data-placement="top" title="Closed">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnLock"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='2'" class="icons group-type" tooltip data-placement="top" title="Secret">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#iconSecret"></use>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </script>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="form-group row">
                                <div class="col-xs-3">
                                    <label class="control-label-default">Article</label>
                                </div>
                                <div class="col-xs-9">
                                    <tags-input ng-model="knowledge_base" ng-disabled="kb_checked" placeholder="click to select users, groups" display-property="text" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_merge_subcategory($tag)" on-tag-removed="tagRemoved_merge_subcategory($tag)">
                                        <auto-complete source="loadTags($query)" load-on-focus="true" load-on-empty="true" min-length="2" max-results-to-show="32" highlight-matched-text=true template="knowledge-template"></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="knowledge-template">
                                        <div class="tag-template">
                                            <div>
                                                <span>{{$getDisplayText()}}</span>
                                                <span ng-if='data.ModuleID=="1" && data.Privacy=="1"' class="icons group-type" tooltip data-placement="top" title="Public">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnGobal"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='0'" class="icons group-type" tooltip data-placement="top" title="Closed">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnLock"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='2'" class="icons group-type" tooltip data-placement="top" title="Secret">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#iconSecret"></use>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </script>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="form-group row">
                                <div class="col-xs-3">
                                    <label class="control-label-default">Announcements</label>
                                </div>
                                <div class="col-xs-9">
                                    <tags-input ng-model="announcements_base" ng-disabled="announcements_checked" placeholder="click to select users, groups" display-property="text" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_merge_subcategory($tag)" on-tag-removed="tagRemoved_merge_subcategory($tag)">
                                        <auto-complete source="loadTags($query)" load-on-focus="true" load-on-empty="true" min-length="2" max-results-to-show="32" highlight-matched-text=true template="announcements-template"></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="announcements-template">
                                        <div class="tag-template">
                                            <div>
                                                <span>{{$getDisplayText()}}</span>
                                                <span ng-if='data.ModuleID=="1" && data.Privacy=="1"' class="icons group-type" tooltip data-placement="top" title="Public">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnGobal"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='0'" class="icons group-type" tooltip data-placement="top" title="Closed">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#icnLock"></use>
                                                    </svg>
                                                </span>
                                                <span ng-if="data.ModuleID=='1' && data.Privacy=='2'" class="icons group-type" tooltip data-placement="top" title="Secret">
                                                    <svg height="14px" width="14px" class="svg-icons no-hover">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#iconSecret"></use>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </script>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="form-group row">
                                <button type="submit" ng-click="save_group_config();" id="btnpublish" ng-click="save_blog('PUBLISHED');">Save</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>