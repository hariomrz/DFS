<div class="panel panel-default fadeInDown"   ng-init="getUserSkills('init');" ng-show="ProfileEndorse" ng-cloak>
    <aside class="skill-section"  <?php if($IsAdmin !=1){?> ng-if=" UserSkillData.length > 0 " <?php } ?>>
        <div class="heading">
            <i class="icon">
                <svg class="svg-icon" width="20px" height="24px">
                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#skillsIcn' ?>"></use>
                </svg>
            </i>
            <label class="m-l-10"><?php echo strtoupper(lang('Skill').lang('plural'));  ?></label>
            <?php
            if ($IsAdmin == 1)
            {
                ?>
                <button type="button" class="btn btn-default btn-sm pull-right" id="reorderBtn" ng-click="editSkillBox()" ng-if="editMode == false && UserSkillData.length > 0"><?php echo lang('Manage'); ?></button>
        <?php } ?>
        </div>
        <?php
        if ($IsAdmin == 1)
        {
            ?>
            <div class="blank-view"  ng-cloak ng-show="(UserSkillData.length <= 0 && addNewSkill != true)">
                <span class="border-dotted" ng-cloak=""> 
                    <svg class="svg-icon" width="20px" height="20px">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#plusIcn' ?>"></use>
                    </svg>
                </span>
                <p><?php echo lang('Add_skills_here'); ?>
                    <br> <?php echo lang('you_and_more_people_find_you'); ?></p>
                <a ng-click="addNewSkill = true"><?php echo lang('Add_Skills'); ?></a>
            </div>
        <?php } ?>
        <?php
        if ($IsAdmin == 1)
        {
            ?>
            <div class="skills-view" ng-show="editMode == false && (UserSkillData.length > 0 || addNewSkill)">
                <label><?php echo lang('add_skills'); ?> <span>*</span></label>
                <div class="input-group add-skills">
                    <!--<input type="text" class="form-control" placeholder="What are your areas of expertise ?">-->
                    <tags-input key-property="Name" replace-spaces-with-dashes="false" min-length=""
                                class="SkillName" placeholder="<?php echo lang('What_are_your_areas_of_expertise'); ?>" data-ng-model="SkillData" display-property="Name" tabindex="2" max-tags="1" add-on-comma="true" add-on-enter="true" template="tag-template">
                        <auto-complete source="InitUserSkillAutocomplete($query)" template="my-custom-skill-template"></auto-complete>
                    </tags-input>
                    <script type="text/ng-template" id="tag-template">
                        <div class="skill remove-skill">
                        <span class="endorse-item-name">
                        <span class="catg-img" ng-if="data.categoryicon">
                        <img ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{data.CategoryIcon}}" >
                        </span>
                        <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                        <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                        <abbr ng-if="data.Name">{{data.Name}}</abbr>
                        </span>
                        <a class="endorse-item-close" ng-click="$removeTag()">
                        <svg height="10px" width="10px" class="svg-icons">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#closeIcn' ?>"></use>
                        </svg>
                        </a>
                        </div>
                    </script>
                    <script type="text/ng-template" id="my-custom-skill-template">
                        <div class="skill autosuggest">
                        <span class="endorse-item-name">
                        <span class="catg-img" ng-if="data.categoryicon"><img ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{data.CategoryIcon}}" ></span>
                        <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                        <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                        <abbr ng-if="data.Name">{{data.Name}}</abbr>
                        </span>
                        </div>
                    </script>


                    <div class="input-group-addon">
                        <button type="button" class="btn btn-dafult" ng-disabled="SkillData.length <=0 " ng-click="save_skills();"><?php echo lang('Add'); ?></button>
                    </div>

                </div> 
                <div><small><?php echo lang('Use_comma'); ?></small></div>
            </div>
<?php } ?>
        <div ng-class="editMode ? 'skills-view edit-skills':'skills-view'" ng-cloak>
            <!--<label> Skills</label>-->
            <ul class="skill-added top-skill" id="UserSkills" ng-if="editMode == false" ng-cloak>
                <li ng-class="{'viewer-pic-container': userskilldata.IsEndorse}" ng-repeat="userskilldata in UserSkillData">
                    <div class="skill">
                        <var ng-if="userskilldata.TotalEndorsement > 0" ng-bind="userskilldata.TotalEndorsement"></var>
                        <span class="endorse-item-name">
                            <span class="catg-img" ng-if="userskilldata.SkillImageName != '' || userskilldata.CategoryImageName != ''">
                                <img height="14" width="14" ng-if="userskilldata.SkillImageName != ''" ng-src="{{ImageServerPath + 'upload/skill/220x220/' + userskilldata.SkillImageName}}" >
                                <img height="14" width="14" class="img-circle" ng-if="userskilldata.CategoryImageName != ''" ng-src="{{ImageServerPath + 'upload/category/220x220/' + userskilldata.CategoryImageName}}" >
                            </span>
                            <span ng-if="userskilldata.CategoryName != ''" ng-bind="userskilldata.CategoryName"> </span>
                            <span ng-if="userskilldata.SubCategoryName != ''" ng-bind="userskilldata.SubCategoryName"> </span>
                            <abbr ng-if="userskilldata.Name != ''" ng-bind="userskilldata.Name"></abbr>
                        </span> 
                        <?php
                        if ($IsAdmin != 1)
                        {
                            ?>
                            <div class="endorsing" ng-if="editMode == false">
                                <div>
                                    <span  ng-click="AddEndorsement(userskilldata.SkillID)" class="endorse-plus" title="Endorse" data-toggle="tooltip">
                                        <svg class="svg-icon" width="13px" height="13px">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#plusIcn' ?>"></use>
                                        </svg>
                                    </span>
                                    <span  ng-click="DeleteEndorsement(userskilldata.SkillID)" class="endorse-minus" title="Remove Endorsement" data-toggle="tooltip">
                                        <svg class="svg-icon" width="13px" height="13px">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#minusIcn' ?>"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
<?php } ?>
                    </div>
                    <div class="endorsers-container" ng-if="editMode == false" ng-cloak>
                        <ul class="endorsers-list" ng-if="userskilldata.Endorsements.length > 0">
                            <li ng-if="userskilldata.TotalEndorsement > 5">
                                <div class="more-content"><span  ng-bind=" {{'+' + userskilldata.TotalEndorsement - 5}}"></span></div>
                                <img ng-src="{{ImageServerPath + 'upload/profile/36x36/user_default.jpg'}}" >
                            </li>
                            <li ng-repeat="Endorsement in userskilldata.Endorsements">
                                <img   ng-if="( Endorsement.ProfilePicture != '' && Endorsement.ProfilePicture != 'user_default.jpg')" class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/36x36/' + Endorsement.ProfilePicture}}"> 

                                <span ng-if="Endorsement.ProfilePicture=='' || Endorsement.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(Endorsement.Name)"></span></span>
                                
                            </li>
                            <li class="endorsers-action" ng-click="EndorsementPopup(userskilldata.EntitySkillID, userskilldata.Name, 'init');" ><span class="see-all-endorsers"></span></li>
                        </ul>
                    </div>
                </li>
            </ul>

            <ul class="skill-added top-skill" droppable="UserSkillData" id="UserSkills" ng-if="editMode" ng-cloak>
                <li ng-class="{'viewer-pic-container': userskilldata.IsEndorse}" ng-repeat="userskilldata in UserSkillData" ng-cloak ng-show="userskilldata.StatusID != '3'">
                    <div class="skill remove-skill" >
                        <var ng-if="userskilldata.TotalEndorsement > 0" ng-bind="userskilldata.TotalEndorsement"></var>
                        <span class="endorse-item-name">
                            <span class="catg-img" ng-if="userskilldata.SkillImageName != '' || userskilldata.CategoryImageName != ''">
                                <img height="14" width="14" ng-if="userskilldata.SkillImageName != ''" ng-src="{{ImageServerPath + 'upload/skill/220x220/' + userskilldata.SkillImageName}}" >
                                <img height="14" width="14" class="img-circle" ng-if="userskilldata.CategoryImageName != ''" ng-src="{{ImageServerPath + 'upload/category/220x220/' + userskilldata.CategoryImageName}}" >
                            </span>
                            <span ng-if="userskilldata.CategoryName != ''" ng-bind="userskilldata.CategoryName"> </span>
                            <span ng-if="userskilldata.SubCategoryName != ''" ng-bind="userskilldata.SubCategoryName"> </span>
                            <abbr ng-if="userskilldata.Name != ''" ng-bind="userskilldata.Name"></abbr>
                        </span> 
                        <a class="endorse-item-close" ng-click="RemoveUserSkill(userskilldata)">
                            <svg class="svg-icons" width="10px" height="10px">
                            <use xlink:href="<?php echo ASSET_BASE_URL.'img/sprite.svg#closeIcn' ?>" xmlns:xlink="http://www.w3.org/1999/xlink"/>
                            </svg>
                        </a>
                    </div>
                </li>
            </ul>
        </div> 
    </aside>
    <div class="panel-footer show-more-skills" ng-hide=" editMode == false && UserSkillData.length >= SkillTotalRecords" ng-cloak>
        <a ng-hide="editMode" ng-if="UserSkillData.length < SkillTotalRecords" ng-click="LoadMoreSkill();"><?php echo lang('load_more'); ?></a>
        <div ng-show="editMode" ng-cloak>
            <span class="pull-left drag-info" >
                <i class="icon-n-drag"></i>  
                <?php echo lang('Drag_to_reorder'); ?>.
            </span>
            <!--<a >SHOW ALL 20 SKILLS</a>-->
            <div class="pull-right" ng-show="editMode" ng-cloak>
                <button type="button" class="btn btn-default" ng-click="editSkillBox();"><?php echo lang('cancel'); ?></button>
                <button type="button" ng-disabled ="ManageSkillSaveBtn" class="btn btn-primary" id="SaveManageSkill" ng-click="SaveManageSkill();" >  <?php echo lang('s_save'); ?></button>
            </div>
        </div>
    </div>
<?php  $this->load->view('skills/endorse_user'); ?>
    
</div>