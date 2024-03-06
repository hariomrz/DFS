<?php $this->load->view('profile/profile_banner') ?>
<div class="container wrapper" ng-controller="SkillsCtrl" ng-init="getEndorsement('init'); PageType = 'Endorsement'" ng-cloak>
    <div class="row">
        <aside class="col-sm-9 col-xs-12">
            <div class="panel panel-default fadeInDown">
                <aside class="skill-section">
                    <div class="heading">
                        <label><?php echo lang('Endorsements'); ?></label>
                    </div>
                    <div class="skills-view">
                        <ul class="list-group">
                            <li ng-repeat="endorsmentdata in Endorsement">
                                <figure>
                                    <a class="loadbusinesscard" href="<?php base_url(); ?>{{endorsmentdata.ProfileURL}}" entityguid="{{endorsmentdata.ModuleEntityGUID}}" entitytype="user" >
                                    
                                        <img   ng-if="( endorsmentdata.ProfilePicture != '' && endorsmentdata.ProfilePicture != 'user_default.jpg')" class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/36x36/' + endorsmentdata.ProfilePicture}}"> 

                                        <span ng-if="endorsmentdata.ProfilePicture=='' || endorsmentdata.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(endorsmentdata.Name)"></span></span>

                                    </a>
                                </figure>
                                <div class="description">

                                    <div class="tagging">

                                        <a class="name loadbusinesscard" entityguid="{{endorsmentdata.ModuleEntityGUID}}" entitytype="user" href="<?php base_url(); ?>{{endorsmentdata.ProfileURL}}" ng-bind="endorsmentdata.Name"></a> 
                                        <?php echo lang('has_endorsed'); ?><a class="name loadbusinesscard" entityguid="<?php echo $ModuleEntityGUID; ?>" entitytype="user" href="<?php base_url(); ?>{{ProfileURL}}">
                                        <span ng-if="FromModuleEntityGUID != ToModuleEntityGUID" ng-bind="FirstName+' '+LastName"></span>
                                        </a><span ng-if="FromModuleEntityGUID == ToModuleEntityGUID">you</span>
                                          <?php echo lang('for'); ?> 
                                        <span ng-if="endorsmentdata.Skill.TotalRecords == 1">
                                            <span ng-if="endorsmentdata.Skill.TotalRecords == 1" class="name">
                                                <a class="name">
                                                    <span ng-repeat="userskilldata in endorsmentdata.Skill.Data track by $index" ng-bind="userskilldata.Name"></span>
                                                </a>
                                            </span>
                                        </span>
                                        <span ng-if="endorsmentdata.Skill.TotalRecords == 2">
                                            <span  ng-repeat="userskilldata in endorsmentdata.Skill.Data track by $index" >
                                                <a class="name">
                                                    <span  ng-bind="userskilldata.Name"></span>
                                                </a>
                                                <span ng-if="$index == '0'">  <?php echo lang('and'); ?> </span>
                                            </span>
                                        </span>
                                        <span ng-if="endorsmentdata.Skill.TotalRecords > 2">
                                            <span ng-if="$index < 2"  ng-repeat="userskilldata in endorsmentdata.Skill.Data track by $index" >
                                                <a class="name">
                                                    <span   ng-bind="userskilldata.Name"></span>
                                                </a>
                                                <span ng-if="$index == '0'"> , </span>
                                            </span> 
                                             <?php echo lang('and'); ?> <span class="name"><span ng-bind="{{endorsmentdata.Skill.TotalRecords - 2}}"></span>
                                                <a class="name">
                                                <span ng-if="endorsmentdata.Skill.TotalRecords - 2 > '1'"><?php echo lang('other').lang('plural'); ?></span> <span ng-if="endorsmentdata.Skill.TotalRecords - 2 == '1'"> <?php echo lang('other'); ?></span>
                                                </a>
                                            </span> 
                                        </span>
                                    </div>
                                    <span class="location" ng-bind="getTimeFromDate(UTCtoTimeZone(endorsmentdata.CreatedDate));"></span>
                                    <ul class="skill-added top-skill">
                                        <li ng-repeat="userskilldata in endorsmentdata.Skill.Data">
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
                                                <!--                                                <div class="endorsing">
                                                                                                    <div>
                                                                                                        <span class="endorse-plus" title="Add To Profile" data-toggle="tooltip">
                                                                                                            <svg class="svg-icon" width="13px" height="13px">
                                                                                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../img/sprite.svg#plusIcn"></use>
                                                                                                            </svg>
                                                                                                        </span>
                                                                                                    </div>
                                                                                                </div>-->
                                            </div>
                                            <div class="endorsers-container">
                                                <ul class="endorsers-list">
                                                    <li ng-if="userskilldata.TotalEndorsement > 5">
                                                        <div class="more-content"><span  ng-bind=" {{ userskilldata.TotalEndorsement - 5}}"></span></div>
                                                        <img ng-src="{{ImageServerPath + 'upload/profile/36x36/user_default.jpg'}}" >
                                                    </li>
                                                    <li ng-repeat="Endorsement in userskilldata.Endorsements">
                                                        <img ng-src="{{ImageServerPath + 'upload/profile/36x36/' + Endorsement.ProfilePicture}}" >
                                                    </li>
                                                    <li class="endorsers-action" ng-if="userskilldata.Endorsements.length > 0" ng-click="EndorsementPopup(userskilldata.EntitySkillID, userskilldata.Name, 'init');" ><span class="see-all-endorsers"></span></li>

                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="clearfix" ng-if="endorsmentdata.CanEndorse" ng-click="EndorseUserPopup('init', endorsmentdata.ModuleID, endorsmentdata.ModuleEntityGUID)"> 
                                        <a class="pull-right name" >Endorse <span ng-bind="endorsmentdata.Name"></span></a>
                                    </div>
                                </div>
                            </li>
                        </ul>

                    </div>
                </aside>
            </div>
        </aside>
        <aside class="col-sm-3 col-xs-12">
            <?php $this->load->view('sidebars/right'); ?>
        </aside>
    </div>
    <?php $this->load->view('skills/endorse_user'); ?>
</div>

<input type="hidden" id="UserID" value="<?php
if (isset($UserID))
{
    echo $UserID;
}
?>" />
<input type="hidden" id="EndorsmentEntityGUID" value="<?php echo $this->uri->segment(3); ?>" />
<input type="hidden" value="1" id="UserWall">