<div  ng-init="getUserPendingSkills('init');">
    <div class="panel panel-default"  ng-if="PendingSkillData.length > 0">    
        <div class="pending-skills">
            <label><?php echo lang('Pending_skills'); ?></label>
            <span class="pending-widgets pull-right" ng-bind="{{PendingSkillData.length}}"></span>
        </div>
        <aside class="skill-section">
            <div class="pending-skills-content">
                <div class="skills-view">
                    <div class="tagging" ng-if="TempCount > 2">

                        <span ng-repeat="Details in TempPendingArr track by $index" ng-if="$index < 2">
                            <a class="name loadbusinesscard" entityguid="{{Details.ModuleEntityGUID}}" entitytype="user"  ng-href="<?php echo base_url(); ?>{{Details.ProfileURL}}">{{Details.Name}}</a>
                            <span ng-if="$index == '0'">, </span>
                        </span>
                        <?php echo lang('and'); ?> <a class="name"><span ng-bind="{{TempCount - 2}}"></span>
                            <span ng-if="TempCount - 2 > '1'">others</span> <span ng-if="TempCount - 2 == '1'"><?php echo lang('other'); ?> </span>
                        </a><?php echo lang('have_endorsed_you_for_new_skills_and_expertise'); ?> 
                    </div>

                    <div ng-if="TempCount == 1" class="tagging">
                        <a ng-repeat="Details in TempPendingArr track by $index" class="name loadbusinesscard" entityguid="{{Details.ModuleEntityGUID}}" entitytype="user"  ng-href="<?php echo base_url(); ?>{{Details.ProfileURL}}">{{Details.Name}}</a><?php echo lang('have_endorsed_you_for_new_skills_and_expertise'); ?> 
                    </div>

                    <div ng-if="TempCount == 2" class="tagging">
                        <span ng-repeat="Details in TempPendingArr track by $index">
                            <a class="name loadbusinesscard" entityguid="{{Details.ModuleEntityGUID}}" entitytype="user"  ng-href="<?php echo base_url(); ?>{{Details.ProfileURL}}">{{Details.Name}}</a>
                            <span ng-if="$index == '0'"> <?php echo lang('and'); ?>  </span>
                        </span>
                       <?php echo lang('have_endorsed_you_for_new_skills_and_expertise'); ?> 
                    </div>
                    <ul class="skill-added top-skill">
                        <li class="viewer-pic-container" ng-repeat="userskilldata in PendingSkillData">
                            <div class="skill remove-skill">
                                <span class="endorse-item-name">
                                    <span class="catg-img" ng-if="userskilldata.SkillImageName != '' || userskilldata.CategoryImageName != ''">
                                        <img height="14" width="14" ng-if="userskilldata.SkillImageName != ''" ng-src="{{ImageServerPath + 'upload/skill/220x220/' + userskilldata.SkillImageName}}" >
                                        <img height="14" width="14" class="img-circle" ng-if="userskilldata.CategoryImageName != ''" ng-src="{{ImageServerPath + 'upload/category/220x220/' + userskilldata.CategoryImageName}}" >
                                    </span>
                                    <span ng-if="userskilldata.CategoryName != ''" ng-bind="userskilldata.CategoryName"> </span>
                                    <span ng-if="userskilldata.SubCategoryName != ''" ng-bind="userskilldata.SubCategoryName"> </span>
                                    <abbr ng-if="userskilldata.Name != ''" ng-bind="userskilldata.Name"></abbr>
                                </span>
                                <a class="endorse-item-close" ng-click="CancelPendingSkill(userskilldata.EntitySkillGUID)">
                                    <svg class="svg-icons" width="10px" height="10px">
                                    <use xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#closeIcn' ?>" xmlns:xlink="http://www.w3.org/1999/xlink"/>
                                    </svg>
                                </a>
                            </div>
                            <div class="endorsers-container">
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

                    <div class="en-footer">
                        <div class="pull-right">
                            <button type="button" class="btn btn-default" ng-click="CancelPendingSkill('All')"><?php echo lang('Dont_add_to_profile'); ?></button>
                            <button type="button" ng-click="AddSkillToProfile();" class="btn btn-primary " ng-class="{'loader-btn':LoaderBtn}"><?php echo lang('Add_to_profile'); ?>
                                <span class="btn-loader">
                                    <span class="spinner-btn">&nbsp;</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>