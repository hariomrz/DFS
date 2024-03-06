<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li>
                        <?php echo lang('AnalyticsTools_Tools'); ?>
                    </li>
                    <li>/</li>
                    <li><span>Merge Skills</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">
    <div class="container" id="SkillCtrl" ng-controller="SkillCtrl" ng-init="pageType = 'MergeSkill';
                getCategory();
                getSkill()">
        <h4 class="semibold pageHeading pull-left">MANAGE SKILLS</h4>
        <div class="btn-group pull-right">
            <a class="btn btn-default" href="<?php echo base_url('admin/skill/add_skill'); ?>">ADD NEW SKILLS</a>
            <!--<a class="btn btn-primary m-l-20" onclick="openPopDiv('addCategoryPopup', 'bounceInDown');">ADD CATEGORY AND SUBCATEGORY</a>-->
        </div>
        <div class="clearfix"></div>
        <div class="panel panel-search">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="input-group">
                                <tags-input ng-model="manageskill.category" placeholder="Search Categories" display-property="Name" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_category($tag)" on-tag-removed="tagRemoved_category($tag)">
                                    <auto-complete source="loadTags_category($query)" min-length="0" load-on-focus="true" load-on-empty="true"></auto-complete>
                                </tags-input>
                                <span class="input-group-addon">
                                    <svg class="svg-icons" width="14px" height="14px">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../../assets/admin/img/sprite.svg#searchIco"></use>
                                    </svg>
                                </span>  
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="input-group">
                                <tags-input ng-model="manageskill.subcategory" placeholder="Search Subcategories" display-property="Name" replace-spaces-with-dashes="false" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_subCategory($tag)" on-tag-removed="tagRemoved_subCategory($tag)">
                                    <auto-complete source="loadTags_subCategory($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="32"></auto-complete>
                                </tags-input>
                                <span class="input-group-addon">
                                    <svg class="svg-icons" width="14px" height="14px">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../../assets/admin/img/sprite.svg#searchIco"></use>
                                    </svg>
                            </span> 
                            </div>
                        </div>
                    </div>
              </div>
            </div>

        </div>
        <h4 class="semibold pageHeading m-b-12">MERGE SKILLS</h4>
        <div class="panel panel-default select-skill-wrap">
            <div class="panel-footer ">
                <div class="select-skills form-group">
                    <label class="m-b-5">Select Skills to merge together and click ‘Merge’.</label>
                    <div class="input-group search-group">
                        <input type="text" placeholder="Type skill name to search and add..." class="form-control" ng-model="ListSkillSearch">
                        <span class="input-group-addon">
                        <svg class="svg-icons" width="14px" height="14px">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../assets/admin/img/sprite.svg#searchIco"></use>
                        </svg>
                    </span>
                    </div>
                </div>
                <div class="pull-right m-t-20">
                    <!--<a href="javascript:void(0)"  class="link">CANCEL</a>-->
                    <a class="btn btn-sm btn-primary m-l-10 fontMedium" ng-click="GetSkillDetail();">MERGE ALL SELECTED SKILLS</a>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <section class="">
            <div class="panel panel-default m-t-40 ">
                <div class="panel-body">
                    <ul class="compact-view selectSkills">
                        <li class="endorse-item" ng-repeat="SelectedList in SelectedMergeSkill" ng-class="{'selected':SelectedList.IsSelecte}">
                            <span class="skill-pill" ng-click="selectSkill(SelectedList);">
                            <a class="endorse-count" ng-if="SelectedList.ProfileCount > 0">
                                <span class="num-endorsements"  ng-bind="SelectedList.ProfileCount"></span>
                            </a>
                            <span class="endorse-item-name">
                                <span class="endorse-item-icon" ng-class="{'icon-checked':SelectedList.IsSelecte}" ng-if="SelectedList.SkillImageName != '' || SelectedList.CategoryImageName != ''">
                                    <img ng-if="SelectedList.SkillImageName != ''"  class="svg" src="{{image_path + 'skill/220x220/' + SelectedList.SkillImageName}}">
                                    <img ng-if="SelectedList.CategoryImageName != ''"  class="svg" src="{{image_path + 'category/220x220/' + SelectedList.CategoryImageName}}">
                                </span>
                            <span ng-if="SelectedList.ParentCategorName != ''" ng-bind="SelectedList.ParentCategorName"></span>
                            <span ng-if="SelectedList.CategoryName != ''" ng-bind="SelectedList.CategoryName"></span>
                            <em data-toggle="dropdown" ng-if="SelectedList.Name != ''" ng-bind="SelectedList.Name"></em>
                            <ul class="dropdown-menu dropdown-menu-right dropdown-sm">
                                <li><a onclick="openPopDiv('editCategoryPopup', 'bounceInDown');">Edit</a></li>
                                <li><a onclick="openPopDiv('removeCategoryPopup', 'bounceInDown');">Remove</a></li>
                            </ul>
                            </span>
                            </span>
                        </li>
                    </ul>
                    <hr>
                    <ul class="compact-view selectSkills">
                        <li class="endorse-item" ng-repeat="SkillList in SkillList| filter:ListSkillSearch " ng-if="SkillList.IsSelecte == false">
                            <span class="skill-pill" ng-click="selectSkill(SkillList);">
                            <a class="endorse-count" ng-if="SkillList.ProfileCount > 0">
                                <span class="num-endorsements"  ng-bind="SkillList.ProfileCount"></span>
                            </a>
                            <span class="endorse-item-name">
                                                 
                                <span class="endorse-item-icon" ng-class="{'icon-checked':SkillList.IsSelecte}" ng-if="SkillList.SkillImageName != '' || SkillList.CategoryImageName != ''">
                                    <img ng-if="SkillList.SkillImageName != ''"  class="svg" src="{{image_path + 'skill/220x220/' + SkillList.SkillImageName}}">
                                    <img ng-if="SkillList.CategoryImageName != ''"  class="svg" src="{{image_path + 'category/220x220/' + SkillList.CategoryImageName}}">
                                </span>
                            <span ng-if="SkillList.ParentCategorName != ''" ng-bind="SkillList.ParentCategorName"></span>
                            <span ng-if="SkillList.CategoryName != ''" ng-bind="SkillList.CategoryName"></span>
                            <em data-toggle="dropdown" ng-if="SkillList.Name != ''" ng-bind="SkillList.Name"></em>
                            <ul class="dropdown-menu dropdown-menu-right dropdown-sm">
                                <li><a onclick="openPopDiv('editCategoryPopup', 'bounceInDown');">Edit</a></li>
                                <li><a onclick="openPopDiv('removeCategoryPopup', 'bounceInDown');">Remove</a></li>
                            </ul>
                            </span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <div class="popup popup-sm animated mergeSkills" id="mergeskillsPopup">
            <div class="popup-title">Merge Skills<i class="icon-close" onClick="closePopDiv('mergeskillsPopup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content lightgray-bg skill-info">
                <h3>You want to merge two skills</h3>
                <p>Kindly select skills which should be removed after merging. User profiles having the removed skill will now show the other skill in which it is merged. They will receive a notification regarding this change.</p>
            </div>
            <div class="popup-content default-scroll2">
                <ul class="skill-listing">
                    <li ng-repeat="SkillDetail in MergeSkillDetail">
                        <a class="pull-right m-t-10" href="javascript:void(0)" ng-click="RemoveSelectedSkill(SkillDetail)">Remove</a>
                        <div class="">
                            <span class="endorse-item-name">
                            <span ng-if="SkillDetail.ParentCategorName != ''" ng-bind="SkillDetail.ParentCategorName"> </span>
                            <span ng-if="SkillDetail.CategoryName != ''" ng-bind="SkillDetail.CategoryName"></span>
                            <span ng-if="SkillDetail.Name != ''" ng-bind="SkillDetail.Name"></span>
                            </span>
                            <div class="clearfix"></div>
                            <div class="endorsersWrap">
                                <ul class="endorsers-list">
                                    <li ng-repeat="UserDetail in SkillDetail.Detail">
                                        <img src="{{image_path + 'profile/36x36/' + UserDetail.ProfilePicture}}" >
                                    </li>
                                    <li ng-if="SkillDetail.ProfileCount > 3">
                                        <div class="more-content"><span ng-bind="{{'+' + SkillDetail.ProfileCount - 3}}"></span></div><img src="<?php base_url() ?>assets/img/dummy-40.jpg" >
                                    </li>
                                </ul>
                                <span class="info" ng-if="SkillDetail.ProfileCount > 0">Users added in their profile</span>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="form-group with-border m-t-10 withAdd-btn">
                    <label class="label">Skill Name</label>
                    <input type="text" placeholder="Type skill name here..." class="form-control " ng-model="addskill.Name" />
                    <!--<a class="add-btn">ADD</a>-->
                    <div class="error-holder"><span>Error</span></div>
                </div>
                <div class="form-group with-border withAdd-btn">
                    <label class="label">Category</label>
                    <tags-input ng-model="addskill.category" id="MergecategoryAdd" display-property="Name" max-tags='1' replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_merge_category($tag)" on-tag-removed="tagRemoved_merge_category($tag)">
                        <auto-complete source="loadTags_merge_category($query)" min-length="1" load-on-focus="false" load-on-empty="true" max-results-to-show="32" highlight-matched-text=true></auto-complete>
                    </tags-input> 
                    <div class="error-holder"><span>Error</span></div>
                </div>
                <div class="form-group with-border withAdd-btn">
                    <label class="label">Sub-Category</label> 
                    <tags-input ng-model="addskill.subcategory" display-property="Name" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_merge_subcategory($tag)" on-tag-removed="tagRemoved_merge_subcategory($tag)">
                        <auto-complete source="loadTags_merge_subcategory($query)" load-on-focus="true" load-on-empty="true" max-results-to-show="32" highlight-matched-text=true></auto-complete>
                    </tags-input> 
                    <div class="error-holder"><span>Error</span></div>
                </div>
            </div>
            <div class="popup-footer">
                <div class="btn-toolbar btn-toolbar-right">
                    <a class="button button-link" onClick="closePopDiv('mergeskillsPopup', 'bounceOutUp');">Cancel</a>
                    <button class="button" ng-click="SaveMergeSkill();">MERGE</button>
                </div>
            </div>
        </div>
    </div>
</section>