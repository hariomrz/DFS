<div class="panel panel-default" ng-init="getEndorseSkills('init');" ng-show="IsCanEndroseSuggestion">
    <aside class="skill-section" ng-if="ShowEndorseBox">
        <div class="pending-skills-content">
            <div class="skills-view">
                <div class="tagging">
                    <div ng-if="ToModuleID == 3" ng-cloak="">
                        <span ng-if="EndorseSkills.length > 0"> <?php echo lang('Does'); ?>  <a class="name loadbusinesscard" entityguid="<?php echo $ModuleEntityGUID; ?>" entitytype="user" ng-bind="FirstName + ' ' + LastName"></a> <?php echo lang('have_these_skills_or_expertise'); ?></span>
                        <span ng-if="EndorseSkills.length <= 0"> <?php echo lang('Endorse'); ?>  <a class="name loadbusinesscard" entityguid="<?php echo $ModuleEntityGUID; ?>" entitytype="user" ng-bind="FirstName + ' ' + LastName"></a> <?php echo lang('for_their_skills_and_expertise'); ?></span>
                    </div>
                    <div ng-if="ToModuleID == 18" ng-cloak="">
                        <span ng-if="EndorseSkills.length > 0"> <?php echo lang('Does'); ?>  <a class="name loadbusinesscard" entityguid="<?php echo $ModuleEntityGUID; ?>" entitytype="page" ng-bind="pageDetails.Title"></a><?php echo lang('have_these_skills_or_expertise'); ?></span>
                        <span ng-if="EndorseSkills.length <= 0"> <?php echo lang('Endorse'); ?>  <a class="name loadbusinesscard" entityguid="<?php echo $ModuleEntityGUID; ?>" entitytype="page" ng-bind="pageDetails.Title"></a> <?php echo lang('for_their_skills_and_expertise'); ?></span>
                    </div>
                </div>
                <ul class="skill-added top-skill">
                    <li class="viewer-pic-container" ng-repeat="userskilldata in EndorseSkills">
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
                            <a class="endorse-item-close" ng-click="RemoveEndorseSkill($index);">
                                <svg height="10px" width="10px" class="svg-icons">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#closeIcn' ?>"></use>
                                </svg>
                            </a>
                        </div>
                    </li>
                </ul>

                <div class="input-group add-skills">
                    <tags-input key-property="Name" replace-spaces-with-dashes="false" min-length="1" id="SkillName" class="SkillName" placeholder="<?php echo lang('What_are_their_area_of_expertise'); ?>" data-ng-model="getTempEndorseSkills" display-property="Name" tabindex="2" max-tags="1" add-on-comma="true" add-on-enter="true" template="tag-template">
                        <auto-complete source="EndorseSkillAutocomplete($query)" template="my-custom-skill-template"></auto-complete>
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
                    <!--<input type="text" class="form-control" placeholder="What are your areas of expertise ?">-->
                    <div class="input-group-addon">
                        <button type="button" class="btn btn-dafult" ng-disabled="getTempEndorseSkills.length <= 0" ng-click="add_endorse_skill();"><?php echo lang('Add'); ?></button>
                    </div>
                    <!-- Auto Suggest  -->

                </div>
                <div><small><?php echo lang('Use_comma'); ?></small></div>
                <div class="en-footer">
                    <div class="pull-right">
                        <button  type="button" class="btn btn-default" ng-click="CancelEndorseSkill();"><?php echo lang('cancel'); ?></button>
                        <button type="button" class="btn btn-primary" ng-click="SaveSuggestionEndorse();" ng-class="{'loader-btn':LoaderBtn}" ng-disabled="EndorseSkills.length <= 0"><?php echo lang('Endorse'); ?>
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