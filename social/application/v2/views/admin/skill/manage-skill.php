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
                    <li><span>Manage Skills</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">
    <div class="container" id="SkillCtrl" ng-controller="SkillCtrl" ng-init="pageType = 'ManageSkill';
                getCategory();
                getPopularSkill();
                getPendingSkill();">
        <h4 class="semibold pageHeading pull-left">MANAGE SKILLS</h4>
        <div class="btn-group pull-right">
            <a class="btn btn-default" href="<?php echo base_url('admin/skill/add_skill'); ?>">ADD NEW SKILLS</a>
        </div>
        <div class="clearfix"></div>

        <div class="panel panel-search">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="input-group">
                                <tags-input ng-model="manageskill.category" placeholder="Search Categories" display-property="Name" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="tagAdded_category($tag)" on-tag-removed="tagRemoved_category($tag)">
                                    <auto-complete source="loadTags_category($query)" load-on-focus="true" load-on-empty="true" max-results-to-show="32" highlight-matched-text=true></auto-complete>
                                </tags-input>
                                <span class="input-group-addon">
                                <svg class="svg-icons" width="14px" height="14px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../assets/admin/img/sprite.svg#searchIco"></use>
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
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../assets/admin/img/sprite.svg#searchIco"></use>
                                </svg>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="search-option">
                    <ul class="nav-dot">
                        <li><span>Popular Skills<b ng-bind="PopularSkillCount"></b></span></li>
                        <li><span>Other Skills<b ng-bind="OtherSkillCount"></b></span></li>
                        <li><span>Pending Skills<a class="badge badge-primary" ng-bind="PendingSkillCount"></a></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        <section class="m-t-sm">
            <h4 ng-if="PopularSkillCount > 0" class="page-header semibold pull-left m-t-0"><span ng-bind="PopularSkillCount"></span> Popular Skills</h4>
            <a class="btn  mSkill pull-right" href="<?php echo base_url('admin/skill/merge_skill'); ?>">Merge Skills</a>
            <div class="clearfix"></div>
            <div class="panel panel-default m-t-sm">
                <div class="panel-body" <div ng-if="PopularSkillCount > 0">
                    <ul class="list-circle">
                        <li class="circle-item" style="width:{{PopularList.height}}px; height:{{PopularList.height}}px;" ng-repeat="PopularList in PopularSkillList">
                            <div class="block" data-toggle="dropdown">
                                <span ng-if="PopularList.ParentCategorName != ''" ng-bind="PopularList.ParentCategorName"></span>
                                <span ng-if="PopularList.CategoryName != ''" ng-bind="PopularList.CategoryName"></span>
                                <span ng-if="PopularList.Name != ''"><b ng-bind="PopularList.Name"></b></span>
                                <em ng-bind="PopularList.ProfileCount"></em>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-center dropdown-sm">
                                <li><a href="<?php echo base_url() . 'admin/skill/add_skill/'; ?>{{PopularList.ID}}">Edit</a></li>
                                <li ng-click="RemoveSkillConfirmation(PopularList, $index, 'Popular');"><a>Remove</a></li>
                            </ul>
                        </li>
                </div>
            </div>

            <div ng-if="OtherSkillCount > 0">
                <h4 class="page-header semibold"><span ng-bind="OtherSkillCount"></span> Other Skills</h4>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <ul class="compact-view">
                            <li class="endorse-item" ng-repeat="OtherList in OtherSkillList">
                                <span class="skill-pill">
                                        <a class="endorse-count" ng-if="OtherList.ProfileCount > 0">
                                            <span class="num-endorsements" ng-bind="OtherList.ProfileCount"></span>
                                </a>
                                <span class="endorse-item-name">
                                    <span class="endorse-item-icon" ng-if="OtherList.SkillImageName != '' || OtherList.CategoryImageName != ''">
                                        <img ng-if="OtherList.SkillImageName != ''"  class="svg" src="{{image_path + 'skill/220x220/' + OtherList.SkillImageName}}">
                                        <img ng-if="OtherList.CategoryImageName != ''"  class="svg" src="{{image_path + 'category/220x220/' + OtherList.CategoryImageName}}">
                                    </span>
                                <span ng-if="OtherList.ParentCategorName != ''" ng-bind="OtherList.ParentCategorName"></span>
                                <span ng-if="OtherList.CategoryName != ''" ng-bind="OtherList.CategoryName"></span>
                                <em data-toggle="dropdown" ng-if="OtherList.Name != ''" ng-bind="OtherList.Name"></em>
                                <ul class="dropdown-menu dropdown-menu-right dropdown-sm">
                                    <li><a href="<?php echo base_url() . 'admin/skill/add_skill/'; ?>{{OtherList.ID}}">Edit</a></li>
                                    <li ng-click="RemoveSkillConfirmation(OtherList, $index, 'Other');"><a>Remove</a></li>
                                </ul>
                                </span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div ng-if="PendingSkillCount > 0">
                <h4 class="page-header semibold"><span ng-bind="PendingSkillCount"></span> Pending Skills</h4>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <ul class="compact-view pending-skill">
                            <li class="endorse-item" ng-repeat="PendingList in PendingSkillList">
                                <span class="skill-pill" data-toggle="dropdown">                  
                                        <span class="endorse-item-name">
                                            <span ng-if="PendingList.Name != ''" ng-bind="PendingList.Name"></span>
                                </span>
                                </span>
                                <ul class="dropdown-menu dropdown-menu-center dropdown-sm">
                                    <li><a href="<?php echo base_url() . 'admin/skill/add_skill/'; ?>{{PendingList.ID}}">Approve</a></li>
                                    <li ng-click="RemoveSkillConfirmation(PendingList, $index, 'Pending');"><a>Disapprove</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <div class="popup popup-sm animated removeCategory" id="removeCategoryPopup">
            <div class="popup-title">
                <i class="icon-close" onClick="closePopDiv('removeCategoryPopup', 'bounceOutUp');">&nbsp;</i>
                <div class="skill-cir">
                    <span class="icn-holder ">
                        <span class="endorse-item-icon" ng-if="SelectedRemoveSkill.SkillImageName != '' || SelectedRemoveSkill.CategoryImageName != ''">
                            <img height="14" width="14" ng-if="SelectedRemoveSkill.SkillImageName != ''"  class="svg" src="{{image_path + 'skill/220x220/' + SelectedRemoveSkill.SkillImageName}}">
                            <img height="14" width="14"  ng-if="SelectedRemoveSkill.CategoryImageName != ''"  class="svg" src="{{image_path + 'category/220x220/' + SelectedRemoveSkill.CategoryImageName}}">
                        </span>
                    </span>
                </div>
                <span class="text" ng-bind="SelectedRemoveSkill.Name"></span>
            </div>
            <div class="popup-content">
                <p>You are about to remove the skill, with its <b ng-bind="RemoveSkillData.ProfileCount">7</b> profile count . <b ng-bind="RemoveSkillData.EndorsementsCount">35</b> endorsements </p>
                <p> All users will lose the skills and endorsements associated to their profile. They will receive a notification regarding this change.</p>
                <a class="remove-btn max-w266" ng-click="RemoveSkill();">
                    <b ng-if="SelectedRemoveSkill.Type!='Pending' ">Remove Skill </b>
                    <b ng-if="SelectedRemoveSkill.Type=='Pending' ">Disapprove Skill </b>
                </a>
            </div>
        </div>

    </div>
</section>