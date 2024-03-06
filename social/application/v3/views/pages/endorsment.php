<div data-ng-controller="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"];?>')" ng-cloak>
  <div ng-init="GetPageDetails('<?php echo $PageGUID;?>');GetPageFollower('<?php echo $PageGUID;?>')"> 
    <!--Header-->
    <?php $this->load->view('profile/profile_banner'); ?>
    <!--//Header--> 
    <!--Container-->
    <div class="container wrapper" ng-controller="SkillsCtrl" ng-init="getEndorsement('init'); PageType = 'Endorsement'" ng-cloak>
    <div class="row">
        <aside class="col-sm-8 col-xs-12">
            <div class="panel panel-default fadeInDown">
                <aside class="skill-section">
                    <div class="heading">
                        <label>Endorsements</label>
                    </div>
                    <div class="skills-view">
                        <ul class="list-group">
                            <li ng-repeat="endorsmentdata in Endorsement">
                                <figure>
                                    <a class="loadbusinesscard" href="<?php base_url(); ?>{{endorsmentdata.ProfileURL}}" entityguid="{{endorsmentdata.ModuleEntityGUID}}" entitytype="user" ><img src="{{ImageServerPath + 'upload/profile/220x220/' + endorsmentdata.ProfilePicture}}"  class="img-circle"   /></a>
                                </figure>
                                <div class="description">

                                    <div class="tagging">

                                        <a class="name loadbusinesscard" entityguid="{{endorsmentdata.ModuleEntityGUID}}" entitytype="user" href="<?php base_url(); ?>{{endorsmentdata.ProfileURL}}" ng-bind="endorsmentdata.Name"></a> 
                                        has endorsed 
                                        <a class="name loadbusinesscard" entityguid="<?php echo $ModuleEntityGUID; ?>" entitytype="page" href="<?php base_url(); ?>{{pageDetails.PageURL}}">
                                        <span ng-if="FromModuleEntityGUID != ToModuleEntityGUID" ng-bind="pageDetails.Title"></span>
                                        </a>
                                        <span ng-if="FromModuleEntityGUID == ToModuleEntityGUID">you</span>
                                         for 
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
                                                <span ng-if="$index == '0'"> and </span>
                                            </span>
                                        </span>
                                        <span ng-if="endorsmentdata.Skill.TotalRecords > 2">
                                            <span ng-if="$index < 2"  ng-repeat="userskilldata in endorsmentdata.Skill.Data track by $index" >
                                                <a class="name">
                                                    <span   ng-bind="userskilldata.Name"></span>
                                                </a>
                                                <span ng-if="$index == '0'"> , </span>
                                            </span> 
                                            and <span class="name"><span ng-bind="{{endorsmentdata.Skill.TotalRecords - 2}}"></span>
                                                <a class="name">
                                                <span ng-if="endorsmentdata.Skill.TotalRecords - 2 > '1'">others</span> <span ng-if="endorsmentdata.Skill.TotalRecords - 2 == '1'">other</span>
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
                                                        <img height="14" width="14" ng-if="userskilldata.SkillImageName != ''" src="{{ImageServerPath + 'upload/skill/220x220/' + userskilldata.SkillImageName}}" >
                                                        <img height="14" width="14" class="img-circle" ng-if="userskilldata.CategoryImageName != ''" src="{{ImageServerPath + 'upload/category/220x220/' + userskilldata.CategoryImageName}}" >
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
                                                        <img src="{{ImageServerPath + 'upload/profile/36x36/user_default.jpg'}}" >
                                                    </li>
                                                    <li ng-repeat="Endorsement in userskilldata.Endorsements">
                                                        <img src="{{ImageServerPath + 'upload/profile/36x36/' + Endorsement.ProfilePicture}}" >
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

        <?php $this->load->view('include/right-sidebar-wall'); ?>
    </div>
    <?php $this->load->view('skills/endorse_user'); ?>
</div>
    <!--//Container--> 
  </div>
</div>
<input type="hidden" name="Visibility" id="visible_for" value="1" />
<input type="hidden" name="Commentable" id="comments_settings" value="1" />
<input type="hidden" name="DeviceType" id="DeviceType" value="Native" />














