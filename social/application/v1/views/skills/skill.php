<div ng-controller="SkillsCtrl" ng-init="getUserTopSkills('init');" class="panel panel-info" data-ng-class="switchClass" data-ng-cloak="" >
    <div class="panel-heading">
        <?php 
            $class = 'endorsed-by-viewer';
            if($IsAdmin == 1) 
            { 
                $class = 'endorsable';
        ?>
        <div class="pull-right" ng-init="getUserPendingSkills('init');" data-ng-show="PendingTotalRecord > 0"> 
            <span class="pending-count"> <?php echo $this->lang->line('PendingSkillText');?>
                <a class="badge-arrow" data-ng-click="addSkillsItem='pending'" ng-class="{'open':addSkillsItem=='pending'}"> 
                    <span class="text">{{PendingTotalRecord}}</span>
                    <svg height="18px" width="10px" class="svg-icons">
                      <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#downArrowIco"/>
                    </svg>
                </a> 
            </span> 
        </div>
        <?php }else{?>
            <div class="pull-right" ng-init="getEndorseSkills('init');" data-ng-show="IsCanEndroseSuggestion">
              <button class="btn btn-default btn-sm" type="button" data-ng-click="addExperienceItem='edit';assignTempEndoreValue();"><?php echo $this->lang->line('EndorseMultipleSkills');?></button>
            </div>
        <?php } ?>
        <h3 class="page-header panel-title">
            <span class="icon">
                <svg class="svg-icons" width="20px" height="24px">
                  <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#skillsIco"></use>
                </svg>
            </span>
            <span class="text"><?php echo $this->lang->line('Skills');?></span> 
        </h3>
    </div>

    <?php 
        if($IsAdmin == 1) 
        { 
    ?>
        <div data-ng-show="addSkillsItem=='pending' && PendingTotalRecord > 0" class="panel-footer">
            <form class="ng-pristine ng-valid" data-rel="form">
                <div class="pending-group">

                    <h4 ng-if="TempCount > 2">
                        <span ng-repeat="Details in TempPendingArr track by $index" ng-if="$index < 2">
                            <a ng-href="<?php echo base_url();?>{{Details.ProfileURL}}">{{Details.Name}}</a>
                            <span ng-if="$index == '0'">, </span>
                        </span>
                        and <a>{{TempCount - 2}} <span ng-if="TempCount - 2  > '1'"><?php echo $this->lang->line('others');?></span> <span ng-if="TempCount - 2  == '1'"><?php echo $this->lang->line('other');?></span></a> <?php echo $this->lang->line('EndorseText');?>
                    </h4>

                    <h4 ng-if="TempCount == 1">
                        <a ng-repeat="Details in TempPendingArr track by $index" ng-href="<?php echo base_url();?>{{Details.ProfileURL}}">{{Details.Name}}</a> <?php echo $this->lang->line('EndorseText');?>
                    </h4>

                    <h4 ng-if="TempCount == 2">
                        <span ng-repeat="Details in TempPendingArr track by $index">
                            <a ng-href="<?php echo base_url();?>{{Details.ProfileURL}}">{{Details.Name}}</a>
                            <span ng-if="$index == '0'"> and </span>
                        </span>
                        <?php echo $this->lang->line('EndorseText');?>
                    </h4>

                    <ul class="skills-section">
                        <li class="endorse-item endorse-remove" ng-repeat="Skills in PendingSkillData track by $index">
                            <span class="skill-pill">
                                <span class="endorse-item-name">
                                    <span ng-if="Skills.CategoryIcon" class="endorse-item-icon"> 
                                        <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{Skills.CategoryIcon}}"> 
                                    </span> 
                                    <span ng-if="Skills.CategoryName">{{Skills.CategoryName}}</span> 
                                    <span ng-if="Skills.SubCategoryName">{{Skills.SubCategoryName}}</span> 
                                    <em>{{Skills.Name}}</em> 
                                </span>
                                <a class="endorse-item-close" ng-click="DeleteSkill(Skills.SkillID,'PendingSkill')">
                                  <svg class="svg-icons" width="10px" height="10px">
                                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIco"/>
                                  </svg>
                                </a>
                            </span>
                            <div class="endorsers-container">
                                <ul class="endorsers-pics">
                                    <li ng-repeat="Endorsement in Skills.Endorsements track by $index" ng-if="$index < 5">
                                        <a title="{{Endorsement.Name}}" href="<?php echo base_url();?>{{Endorsement.ProfileURL}}">
                                            <img ng-class="FromModuleEntityGUID == Endorsement.ModuleEntityGUID ? 'viewer-pic' : ''" width="30" height="30" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{Endorsement.ProfilePicture}}">
                                        </a>    
                                    </li>
                                    <li class="endorsers-action" data-ng-show="Skills.NoOfEndorsements > 5" data-ng-click="EndorsementPopup(Skills.EntitySkillID,Skills.Name,'init');">
                                        <a data-toggle="modal" class="see-all-endorsers"></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>

                    <hr>
                    <div class="btn-group">
                        <div class="btn-toolbar btn-toolbar-right">
                          <button data-ng-click="addSkillsItem=''" class="btn btn-default"><?php echo $this->lang->line('CloseButton');?></button>
                          <button class="btn btn-primary" data-ng-click="AddSkillToProfile();"><?php echo $this->lang->line('AddToProfileButton');?></button>
                        </div>
                    </div>
                </div>  
            </form>
        </div>

        <div class="panel-body nodata-panel" data-ng-show="TopSkillData.length == 0 && OtherSkillData.length == 0 && showSkilldisplaydiv == 0">
            <div class="nodata-text"> <span class="nodat-circle">
              <svg height="20px" width="20px" class="svg-icons">
                <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#plusIco"/>
              </svg>
              </span>
              <p><?php echo $this->lang->line('NoRecordText');?>
              </p>
              <a class="btn-link" data-ng-click="toggleSkills()"><?php echo $this->lang->line('AddSKillsButton');?></a> 
            </div>
        </div>

        <div class="panel-footer" data-ng-show="showSkilldisplaydiv == 1 || TopSkillData.length > 0 || OtherSkillData.length > 0">
            <form data-rel="form" id="UserSkillform" class="ng-pristine ng-valid">
                <div class="form-group form-group-lg">
                    <div class="control-label"><?php echo $this->lang->line('AddSKillText');?><span class="mandatory">*</span></div>
                    <div class="input-group" data-error="has-error"> 
                        <!-- <input type="text" placeholder="What are your area of expertise ?" class="form-control" on-focus> -->
                        <tags-input key-property="Name" replace-spaces-with-dashes="false" data-mandatory="true"
                                    data-msglocation="errorSports" data-controltype="general" id="SkillName" class="SkillName" placeholder="Search Skills" data-ng-model="SkillData" display-property="Name" tabindex="2" max-tags="1" add-on-comma="true" add-on-enter="false" template="tag-template">
                        <auto-complete source="InitUserSkillAutocomplete($query)" template="my-custom-skill-template"></auto-complete>
                        </tags-input>
                        <script type="text/ng-template" id="my-custom-skill-template">
                            <span class="skill-pill"> 
                                <span class="endorse-item-name"> 
                                    <span ng-if="data.CategoryIcon" class="endorse-item-icon"> 
                                        <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{data.CategoryIcon}}"> 
                                    </span> 
                                    <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                                    <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                                    <em>{{data.Name}}</em> 
                                </span>
                            </span>         
                        </script>
                        <span class="error-block" id="errorSkillName"></span> 
                        <a class="input-group-addon btn-addon" data-ng-click="toggleuserskillname()"><?php echo $this->lang->line('AddButton');?></a> 

                        <script type="text/ng-template" id="tag-template">
                          <div class="tag-template">
                            <span class="skill-pill">
                                <span class="endorse-item-name">
                                    <span ng-if="data.CategoryIcon" class="endorse-item-icon"> 
                                        <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{data.CategoryIcon}}"> 
                                    </span> 
                                    <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                                    <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                                    <em>{{data.Name}}</em>                                 
                                </span>    
                                <a class="endorse-item-close" ng-click="$removeTag()">
                                    <svg class="svg-icons" width="12px" height="10px">
                                        <use  xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIco"></use>
                                    </svg>
                                </a>
                            </span>
                          </div>
                        </script>
                    </div>

                    <div class="well well-default well-sm" ng-if="SkillDataForDisplayCount > 0">
                        <ul class="compact-view">
                            <li ng-repeat="SkillDisplay in SkillDataForDisplay track by $index" ng-if="SkillDisplay.StatusID=='2'" class="endorse-item endorse-remove">
                                <span class="skill-pill">
                                    <span class="endorse-item-name" id="skill_{{SkillDisplay.SkillID}}">
                                        <span ng-if="SkillDisplay.CategoryIcon" class="endorse-item-icon"> 
                                            <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{SkillDisplay.CategoryIcon}}"> 
                                        </span> 
                                        <span ng-if="SkillDisplay.CategoryName">{{SkillDisplay.CategoryName}}</span> 
                                        <span ng-if="SkillDisplay.SubCategoryName">{{SkillDisplay.SubCategoryName}}</span> 
                                        <em>{{SkillDisplay.Name}}</em> 
                                    </span>
                                    <a class="endorse-item-close" data-ng-click="removeskill($index,SkillDisplay.SkillID)">
                                        <svg class="svg-icons" width="12px" height="10px">
                                        <use  xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIco"></use>
                                        </svg>
                                    </a>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="btn-group" ng-if="SkillDataForDisplayCount > 0">
                    <div class="btn-toolbar btn-toolbar-right">
                        <button class="btn btn-default" data-ng-click="toggleAddedSkills()"><?php echo $this->lang->line('CancelButton');?></button>

                        <button class="btn btn-primary" id="SaveSkill" data-ng-click="save_skills()"><?php echo $this->lang->line('SaveButton');?></button>

                    </div>
                </div>
                <input type="hidden" id="selectedUserSkill" value="" />
            </form>
        </div>
    <?php 
        }
        else
        { 
    ?>

        <div class="panel-body nodata-panel" data-ng-show="TopSkillData.length == 0 && OtherSkillData.length == 0 && showSkilldisplaydiv == 0">
            <div class="nodata-text"> <span class="nodat-circle">
              <svg height="20px" width="20px" class="svg-icons">
                <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIco"/>
              </svg>
              </span>
              <p><?php echo $this->lang->line('OtherNoRecordText');?>
              </p>
            </div>
        </div>
        
        <div class="panel-footer" data-ng-show="addExperienceItem=='edit' && IsCanEndroseSuggestion">
            <form data-rel="form" class="ng-pristine ng-valid">
              <div class="form-group form-group-lg">
                <div class="control-label"><?php echo $this->lang->line('DoesText');?> <a>{{FirstName}}</a> <?php echo $this->lang->line('EndorseSkillText');?></div>
                <div class="well well-default well-sm">
                    <tags-input key-property="Name" replace-spaces-with-dashes="false" id="SkillName" class="SkillName" placeholder="Search Endorses" data-ng-model="getTempEndorseSkills" display-property="Name" tabindex="2" max-tags="1" add-on-comma="true" add-on-enter="false" template="tag-template">
                    <auto-complete source="EndorseSkillAutocomplete($query)" template="my-custom-skill-template"></auto-complete>
                    </tags-input>
                    <script type="text/ng-template" id="my-custom-skill-template">
                        <span class="skill-pill"> 
                            <span class="endorse-item-name"> 
                                <span ng-if="data.CategoryIcon" class="endorse-item-icon"> 
                                    <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{data.CategoryIcon}}"> 
                                </span> 
                                <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                                <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                                <em>{{data.Name}}</em> 
                            </span>
                        </span>         
                    </script>

                    <script type="text/ng-template" id="tag-template">
                      <div class="tag-template">
                        <span class="endorse-item-name">
                            <span ng-if="data.CategoryIcon" class="endorse-item-icon"> 
                                <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{data.CategoryIcon}}"> 
                            </span> 
                            <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                            <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                            <em>{{data.Name}}</em> 
                            <a class="remove-button" ng-click="$removeTag()">&#10006;</a>
                        </span>    
                      </div>
                    </script>
                </div>
              </div>
              <div class="btn-group">
                <div class="btn-toolbar btn-toolbar-right">
                  <button class="btn btn-default" data-ng-click="addExperienceItem=''"><?php echo $this->lang->line('CloseButton');?></button>
                  <button class="btn btn-primary" data-ng-click="SaveSuggestionEndorse();"><?php echo $this->lang->line('EndorseButton');?></button>
                </div>
              </div>
            </form>
        </div>
        <!-- <div class="panel-heading" data-ng-show="SkillDataForDisplay.length > 0">
            
            <h3 class="page-header panel-title">
                <span class="icon">
                    <svg class="svg-icons" width="20px" height="24px">
                        <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#skillsIco"></use>
                    </svg>
                </span>
                <span class="text">SKILLS</span> 
            </h3>
        </div> -->
    <?php  
        } 
    ?>
    <!-- <div class="panel-body" data-ng-show="SkillDataForDisplay.length > 0 && showskillform == 0">
        <div class="editable-panel">
            <div class="editable-item">
               <?php if($this->data['UserID']== $this->session->userdata('UserID')) { ?>

                <button class="btn edit-btn" data-ng-click="displayuserSkillData(UserSkills)">
                    <svg class="svg-icons" width="10px" height="10px">
                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#editIco"></use>
                    </svg>
                </button>
                <?php } ?>
                <div class="edit-highlight">
                    <ul class="tagged-list">
                        <li ng-repeat="Skills in SkillDataForDisplay track by $index"><span class="tag-text">{{Skills.Name}}</span></li>

                    </ul>
                </div>
            </div>
        </div>
    </div> -->

    <div class="panel-body" ng-class="TopSkillData.length > 0 || OtherSkillData.length > 0 ? 'large-pad':''">
        <h4 class="semibold" data-ng-show="TopSkillData.length > 0 && showskillform == 0"><?php echo $this->lang->line('TopSkillsText');?></h4>
        
        <ul class="skills-section" data-ng-show="TopSkillData.length > 0 && showskillform == 0">
          <li class="endorse-item"  ng-class="Skills.IsEndorse == true ? 'endorsed-by-viewer':'endorsable'" ng-repeat="Skills in TopSkillData track by $index">
            <span class="skill-pill">

              <a class="endorse-count" data-ng-show="Skills.NoOfEndorsements > 0"><span class="num-endorsements" ng-bind="Skills.NoOfEndorsements"></span></a> 
              <span class="endorse-item-name">
                <span ng-if="Skills.CategoryIcon" class="endorse-item-icon"> 
                    <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{Skills.CategoryIcon}}"> 
                </span> 
                <span ng-if="Skills.CategoryName">{{Skills.CategoryName}}</span> 
                <span ng-if="Skills.SubCategoryName">{{Skills.SubCategoryName}}</span> 
                <em>{{Skills.Name}}</em> 
              </span>
              <a class="endorse-button">
                <?php if($IsAdmin == 1) { ?>
                    <span class="endorse-plus endorse-icon" ng-click="DeleteSkill(Skills.SkillID,'TopSkill')">
                        <svg class="svg-icons" width="13px" height="13px">
                          <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIco"></use>
                        </svg>
                    </span>
                <?php }else{?>
                  <span class="endorse-plus endorse-icon" data-ng-show="IsTopSkillCanEndorse" data-ng-click="AddEndorsement(Skills.SkillID,'TopSkill')">
                    <svg height="13px" width="13px" class="svg-icons">
                      <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#plusIco"/>
                    </svg>
                  </span>

                  <span data-container="body"  data-ng-show="IsTopSkillCanEndorse" data-placement="top" data-ng-if="Skills.IsEndorse == true" data-toggle="tooltip" class="endorse-minus endorse-icon" data-original-title="Remove Endorsement" data-ng-click="DeleteEndorsement(Skills.SkillID,'TopSkill')">
                    <svg height="13px" width="13px" class="svg-icons">
                      <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#minusIco"/>
                    </svg>
                  </span>
                <?php }?>
              </a>
            </span>
            <div class="endorsers-container">
              <ul class="endorsers-pics">
                <li ng-repeat="Endorsement in Skills.Endorsements track by $index" ng-if="$index < 5">
                    <a title="{{Endorsement.Name}}" ng-href="<?php echo base_url();?>{{Endorsement.ProfileURL}}">
                        <img ng-class="FromModuleEntityGUID == Endorsement.ModuleEntityGUID ? 'viewer-pic' : ''" width="30" height="30" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{Endorsement.ProfilePicture}}">
                    </a>    
                </li>
                
                <li class="endorsers-action" data-ng-show="Skills.NoOfEndorsements > 5" data-ng-click="EndorsementPopup(Skills.EntitySkillID,Skills.Name,'init');">
                    <a data-toggle="modal" class="see-all-endorsers"></a>
                </li>
              </ul>
              <span class="line-container">
                <span class="hr-line"></span>
              </span>
            </div>
          </li>          
        </ul>

        <h4 class="semibold" data-ng-show="OtherSkillData.length > 0 && showskillform == 0">
            <?php if($EntityType == 'User') { ?>
                {{FirstName}} 
            <?php }elseif($EntityType == 'Page'){?>
                {{PageTitle}}
            <?php }?>
            <?php echo $this->lang->line('OtherSkillText');?>
        </h4>

        <ul class="skills-section compact-view" data-ng-show="OtherSkillData.length > 0 && showskillform == 0">
            <li class="endorse-item" ng-class="Skills.IsEndorse == true ? 'endorsed-by-viewer':'endorsable'" ng-repeat="Skills in OtherSkillData track by $index">
                <span class="skill-pill">
                  <span class="endorse-item-name">
                    <span ng-if="Skills.CategoryIcon" class="endorse-item-icon"> 
                        <img  class="svg" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/category/{{Skills.CategoryIcon}}"> 
                    </span> 
                    <span ng-if="Skills.CategoryName">{{Skills.CategoryName}}</span> 
                    <span ng-if="Skills.SubCategoryName">{{Skills.SubCategoryName}}</span> 
                    <em>{{Skills.Name}}</em> 
                  </span>
                  <a class="endorse-button">
                    <?php if($IsAdmin == 1) { ?>
                        <span class="endorse-plus endorse-icon" ng-click="DeleteSkill(Skills.SkillID,'OtherSkill')">
                            <svg class="svg-icons" width="13px" height="13px">
                              <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIco"></use>
                            </svg>
                        </span>
                    <?php }else{?>
                      <span class="endorse-plus endorse-icon" data-ng-show="IsOtherSkillCanEndorse" data-ng-click="AddEndorsement(Skills.SkillID,'OtherSkill')">
                        <svg height="13px" width="13px" class="svg-icons">
                          <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#plusIco"/>
                        </svg>
                      </span>

                      <span data-container="body"  data-ng-show="IsOtherSkillCanEndorse"  data-placement="top" data-ng-if="Skills.IsEndorse == true" data-toggle="tooltip" class="endorse-minus endorse-icon" data-original-title="Remove Endorsement" data-ng-click="DeleteEndorsement(Skills.SkillID,'OtherSkill')">
                        <svg height="13px" width="13px" class="svg-icons">
                          <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#minusIco"/>
                        </svg>
                      </span>

                    <?php }?>
                  </a>
                </span>
            </li>
        </ul>
    </div>


    <div role="dialog" class="modal fade" id="endorsersModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button data-dismiss="modal" class="close" type="button">
            <svg height="16px" width="16px" class="svg-icons">
              <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIco"/>
            </svg>
            </button>
            <h3 class="modal-title"><?php echo $this->lang->line('Endorsers');?></h3>
          </div>

          <div class="modal-body no-pad">
            <div class="modal-info">
              <h3>{{EndorsementCount}} <?php echo $this->lang->line('EndorsePopupText');?><span class="semibold" data-ng-bind="EndorsementSkillName"></span></h3>
            </div>
            <div class="panel-listing">
               <div class="panel-body" id="endorsersList">
                 <div class="">
                   <ul class="list-group list-group-horizontal scrollbox scrollbox-md-height" style="width: 96px; padding-right: 4px; outline: medium none; overflow: hidden;" tabindex="0">
                      <li ng-repeat="list in EndorsementUserLists track by $index">
                         <div class="media">
                            <figure class="media-left">
                                <a ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                    <img width="70" height="70"  class="img-rounded" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{list.ProfilePicture}}">
                                </a>    
                            </figure>
                            <div class="media-body">
                                <h3 class="media-heading">
                                    <a class="btn-link" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" data-ng-bind="list.Name"></a>
                                </h3>
                               <p data-ng-if="list.ProfileTypeName != ''" data-ng-bind="list.ProfileTypeName"></p>
                               <p data-ng-if="list.Location.Location != ''" class="gray-text" data-ng-bind="list.Location.Location"></p>
                            </div>
                         </div>
                      </li>

                      <li class="load-more" data-ng-show="IsEndorsementLoadMore == '1'">
                        <i class="loading"></i>
                      </li>    
                   </ul>
                   <div style="position: absolute; z-index: 1; margin: 0px; padding: 0px; display: none; left: 100px; top: 1767px;">    <div style="position: relative;" class="enscroll-track vertical-track">
                            <a style="position: absolute; z-index: 1;" href="" class="vertical-handle">
                                <div class="top"></div>
                                <div class="bottom"></div>
                            </a>
                        </div>
                    </div>
                 </div>  
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>

</div>