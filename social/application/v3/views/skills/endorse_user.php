<div class="modal fade" id="endorsedList" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close"></i></span>
                </button>
                <h4 class="modal-title"><?php echo lang('Endorse'); ?></h4>
            </div>
            <div class="modal-body listing-space">
                <div class="search-cmn suggest-search">
                    <form>
                        <div class="input-group global-search">
                            <input type="text" id="globalsrch-filters" name="srch-filters" ng-model="endorseSearchUser" placeholder="<?php echo lang('Search_user'); ?>" class="form-control">
                            <div class="input-group-btn">
                                <button type="button" class="btn-search"><i class="icon-search-gray"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
                 <?php  if ($IsAdmin == 1) { ?>
                <div class="info-text-region tagging" ng-if="EndorsementUserLists.length > 0">
                    <p><span ng-bind="EndorsementCount"></span> <?php echo lang('People_have_endorsed_you_for'); ?> <a class="name" ng-bind="EndorsementSkillName"> </a> <?php echo lang('would_you_like_to'); ?><a class="name"><?php echo lang('Endorse_them'); ?>.</a></p>
                </div>
            <?php } ?>
                 <div  ng-cloak="" class="info-text-region tagging" ng-if="EndorsementUserLists.length <= 0">
                 <p ><?php echo lang('Record_not_found'); ?></p>
                 </div>
                <div class="default-scroll scrollbar">
                    <ul class="list-group button-absolute">
                        <li ng-repeat="endorsementuserdata in EndorsementUserLists">
                            <figure>
                                <a href="<?php echo base_url(); ?>{{endorsementuserdata.ProfileURL}}">
                                    <img   ng-if="( endorsementuserdata.ProfilePicture != '' && endorsementuserdata.ProfilePicture != 'user_default.jpg')" class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/36x36/' + endorsementuserdata.ProfilePicture}}"> 

                                    <span ng-if="endorsementuserdata.ProfilePicture=='' || endorsementuserdata.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(endorsementuserdata.Name)"></span></span>

                                </a>
                            </figure>
                            <div class="description">
                                <a class="name"  href="<?php echo base_url(); ?>{{endorsementuserdata.ProfileURL}}" ng-bind="endorsementuserdata.Name"></a>
                                <!--<div>HR Head at Acapella GLOBAL (IT/Software)</div>-->
                                <span class="location" ng-if="endorsementuserdata.Location.Location != ''" ng-bind="endorsementuserdata.Location.Location"></span>
                                <button ng-if="endorsementuserdata.CanEndorse" ng-click="EndorseUserPopup('init', endorsementuserdata.ModuleID, endorsementuserdata.ModuleEntityGUID)" type="button" class="btn btn-default btn-abs" ><?php echo lang('Endorse'); ?></button>
                            </div>
                        </li>
                    </ul>

                </div>
                <div  ng-if="IsEndorsementLoadMore" ng-click="ScrollEndrosementList();"><?php echo lang('load_more'); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="endorsedTheme" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close"></i></span>
                </button>
                <div class="en-header">
                    <ul class="list-group">
                        <li>
                            <figure>
                                <img err-name="{{FirstName + ' ' + LastName}}" ng-src="{{ProfileImage}}" class="img-circle" />

                            </figure>
                            <div class="description">
                                <div class="tagging">
                                    <?php echo lang('Endorse_your_connections'); ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-body" >
                <div ng-if="EndorseConnection.length > 0">
                    <div class="connection-list">
                        <div class="selected-connection">
                            <img   ng-if="( SelectedEndorseConnection.ProfilePicture != '' && SelectedEndorseConnection.ProfilePicture != 'user_default.jpg')" class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + SelectedEndorseConnection.ProfilePicture}}"> 

                            <span ng-if="SelectedEndorseConnection.ProfilePicture=='' || SelectedEndorseConnection.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(SelectedEndorseConnection.FirstName+' '+SelectedEndorseConnection.LastName)"></span></span>

                        </div>
                        <ul class="connection-listing">
                            <li ng-repeat="endorseconnection in EndorseConnection" ng-if="endorseconnection.IsSelecte == false" ng-click="SelectConnectionUser(endorseconnection)"> 

                                <img   ng-if="( endorseconnection.ProfilePicture != '' && endorseconnection.ProfilePicture != 'user_default.jpg')" class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + endorseconnection.ProfilePicture}}"> 

                                <span ng-if="endorseconnection.ProfilePicture=='' || endorseconnection.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(endorseconnection.FirstName+' '+endorseconnection.LastName)"></span></span>

                            </li>

                        </ul>
                    </div>
                    <div class="added-skills boxarrow" ng-if="SelectedEndorseConnection.EndorseSuggestion.length > 0" >
                        <ul class="skill-added">
                            <li ng-repeat="EndorseConnectionSkill in SelectedEndorseConnection.EndorseSuggestion">
                                <div class="skill remove-skill">
                                    <span class="endorse-item-name">
                                        <span class="catg-img" ng-if="EndorseConnectionSkill.SkillImageName != '' || EndorseConnectionSkill.CategoryImageName != ''">
                                            <img height="14" width="14" ng-if="EndorseConnectionSkill.SkillImageName != ''" ng-src="{{ImageServerPath + 'upload/skill/220x220/' + EndorseConnectionSkill.SkillImageName}}" >
                                            <img height="14" width="14" class="img-circle" ng-if="EndorseConnectionSkill.CategoryImageName != ''" ng-src="{{ImageServerPath + 'upload/category/220x220/' + EndorseConnectionSkill.CategoryImageName}}" >
                                        </span>
                                        <span ng-if="EndorseConnectionSkill.CategoryName != ''" ng-bind="EndorseConnectionSkill.CategoryName"> </span>
                                        <span ng-if="EndorseConnectionSkill.SubCategoryName != ''" ng-bind="EndorseConnectionSkill.SubCategoryName"> </span>
                                        <abbr ng-if="EndorseConnectionSkill.Name != ''" ng-bind="EndorseConnectionSkill.Name"></abbr>
                                    </span>
                                    <a class="endorse-item-close" ng-click="RemoveConnectionSkill($index);">
                                        <svg height="10px" width="10px" class="svg-icons">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL.'img/sprite.svg#closeIcn'; ?>"></use>
                                        </svg>
                                    </a>
                                </div>
                            </li>

                        </ul>
                    </div>
                    <div class="skills-view" >
                        <label><?php echo lang('add_skills'); ?></label>
                        <div class="input-group add-skills">
                            <tags-input key-property="Name" replace-spaces-with-dashes="false" min-length="1" id="SkillName" class="SkillName" placeholder="<?php echo lang('What_are_their_area_of_expertise'); ?>" data-ng-model="EndorseConnectionSkills" display-property="Name" tabindex="2" max-tags="1" add-on-comma="true" add-on-enter="true" template="tag-template">
                                <auto-complete source="EndorseConnectionAutocomplete($query)" template="my-custom-skill-template"></auto-complete>
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
                                <button type="button" class="btn btn-dafult" ng-disabled="EndorseConnectionSkills.length <=0 "  ng-click="add_endorse_connection_skill();"><?php echo lang('Add'); ?></button>
                            </div>
                            <!-- Auto Suggest  -->

                        </div>
                        <div><small><?php echo lang('Use_comma'); ?></small></div>
                    </div>
                    
                </div>
                <div class="blank-view"  ng-cloak ng-if="EndorseConnection.length <= 0">
                    <span class="border-dotted"> 
                        <svg class="svg-icon" width="20px" height="20px">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL . 'img/sprite.svg#plusIcn' ?>"></use>
                        </svg>
                    </span>
                    <p><?php echo lang('Record_not_found'); ?></p>
                </div>
            </div>
            <div class="panel-footer" ng-if="EndorseConnection.length > 0">
                <div class="pull-right">
                    <button type="button" class="btn btn-default" class="close" data-dismiss="modal" aria-label="Close"><?php echo lang('cancel'); ?></button>
                    <button type="button" class="btn btn-primary " ng-click="SaveEndorseConnection()">Endorse <span ng-bind="SelectedEndorseConnection.FirstName+' '+SelectedEndorseConnection.LastName"></span></button>
                </div>
            </div>
        </div>
    </div>
</div>
