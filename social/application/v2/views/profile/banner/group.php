<div class="banner-cls banner-cover" ng-cloak ng-show="GroupDetails.CoverImageState=='1'">
    <div ng-if="LoginSessionKey!==''" ng-click="save_cover_image_state();GroupDetails.CoverImageState='2'" class="banner-button hidden-xs" data-banner="hide"><i class="ficon-arrow-down"></i></div>
    <div class="banner">
        <div class="image-cover clearfix">
            <div id="ib-main-wrapper" class="cover-inner">
                <input type="hidden" id="coX" value="0" />
                <input type="hidden" id="coY" value="" />
                <input type="hidden" id="hidden_image_cover" value="" />
                <input type="hidden" id="hidden_image_cover_data" value="" />
                <input type="hidden" id="image_src" value="0" />
                <input type="hidden" id="windowWidth" value="1920" />
                <!--<input type="file" id="upload_cover" name="upload_cover" >-->
                <div class="hiddendiv" id="CoverUpload"></div>
                <div class="draggable holder ib-main" style="display:none; width:100%;" id="coverDragimg">
                    <img id="image_cover" ng-src="{{CoverImage}}"  />
                </div>
                <div class="holder ib-main" id="coverViewimg">
                    <div class="blur-bg" ng-class="CoverImage == '' ? 'hide' : '';" style="background-image:url({{CoverImage}}); "></div>
                    <img class="cursor-pointer" ng-class="CoverImage == '' ? 'hide' : '';" id="coverImgProfile" ng-click="$emit('showMediaPopupGlobalEmitByImage', CoverImage, 1);" ng-src="{{CoverImage}}"  />
                </div>
            </div>
            <div class="loaderbtn cover-picture-loader">
                <div class="loader" ng-if="applyCoverPictureLoader"></div>
                <div ng-if="profileCoverUploadPrgrs.progressPercentage && profileCoverUploadPrgrs.progressPercentage < 101" data-percentage="{{profileCoverUploadPrgrs.progressPercentage}}" upload-progress-bar-cs></div>
            </div>
            <div class="container cover-content">
                <div class="btn drag-cover hidden-xs"><i class="icon-drag"></i> Drag to Reposition Cover</div>
                <div class="row">
                    <div class="profile-container">
                        <div class="row">
                            <!--Left User info-->
                            <aside ng-cloak class="profile-pic col-lg-9 col-md-8">
                                <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="cursor-pointer user-wall-thumb">
                                    <img ng-src="{{ProfileImage}}" class="img-circle" ng-if="GroupDetails.Type != 'INFORMAL'" />
                                    <img ng-if="GroupDetails.Type == 'INFORMAL' && GroupDetails.ProfilePicture != '' && GroupDetails.ProfilePicture != 'group-no-img.jpg' && GroupDetails.ProfilePicture != 'user_default.jpg'" ng-src="{{'<?php echo IMAGE_SERVER_PATH . 'upload/profile/220x220/' ?>'+GroupDetails.ProfilePicture}}" err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="img-circle"  >
                                    <div ng-if="GroupDetails.Type == 'INFORMAL' && (GroupDetails.ProfilePicture == 'group-no-img.jpg' || GroupDetails.ProfilePicture == 'user_default.jpg')" ng-class="(GroupDetails.EntityMembers.length > 2) ? 'group-thumb' : 'group-thumb-two';" class="group-thumb big" ng-if="thread.ThreadImageName == ''">
                                        <span ng-repeat="recipients in GroupDetails.Members|limitTo:3" ng-if="recipients">
                                            <img  err-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-src="{{ImageServerPath}}upload/profile/220x220/{{recipients.ProfilePicture}}" entitytype="user" ng-if="$index <= 2">
                                        </span>
                                    </div>
                                    <div class="loaderbtn profile-picture-loader">
                                        <div class="spinner32"></div>
                                    </div>
                                </figure>
                                <!--Start  Group-->
                                <div class="profile-info" ng-class="{'with-caption' : GroupDetails.Category.Name}">
                                    <div class="user-name">
                                        <label ng-if="GroupDetails.Type == 'FORMAL'" ng-bind="GroupDetails.GroupName" ng-cloak></label>
                                        <label ng-if="GroupDetails.Type == 'INFORMAL'" ng-cloak>
                                            <span ng-repeat="Member in GroupDetails.EntityMembers">
                                                <span ng-bind="Member.FirstName" ng-if="$index <= 2"> </span>
                                            <span ng-if="$index < 2 && GroupDetails.EntityMembers.length >= 3">,</span>
                                            <span ng-if="$index < (GroupDetails.EntityMembers.length - 1) && GroupDetails.EntityMembers.length < 3">,</span>
                                            </span>
                                            <span ng-if="GroupDetails.EntityMembers.length > 3">and {{GroupDetails.EntityMembers.length - 3}} others</span>
                                        </label>
                                        <span class="group-secure"> 
                                            <i class="icon-n-global-w" tooltip data-original-title="Anyone can see the group, its members and their posts" data-toggle="tooltip" ng-if="GroupDetails.IsPublic == 1"></i>
                                            <i class="icon-n-closed-w" tooltip data-original-title="Anyone can see the group, but only members can post" data-toggle="tooltip" ng-if="GroupDetails.IsPublic == 0"></i>
                                            <i class="icon-n-group-secret-w" tooltip data-original-title="Only invited members can see group" data-toggle="tooltip" ng-if="GroupDetails.IsPublic == 2"></i>
                                        </span>
                                    </div>
                                    <ul class="activity-nav cat-sub-nav">
                                        <li>
                                            <span class="cat-name" ng-bind="GroupDetails.Category.Name"></span>
                                        </li>
                                        <li ng-if="GroupDetails.Category.SubCategory!=''"><span class="cat-name" ng-bind="GroupDetails.Category.SubCategory.Name"></span></li>
                                    </ul>
                                    <!--  <p class="profile-nametitle" ng-bind="GroupDetails.Category.Name"></p> -->
                                </div>
                                <!--End  Group-->
                                <input type="hidden" id="isuserprofile" value="1" />
                                <div ng-cloak="" ng-if="config_detail.IsAdmin == true && LoginSessionKey!==''" class="dropdown thumb-dropdown">
                                    <a class="edit-profilepic dropdown-toggle" href="javascript:void(0);" data-toggle="dropdown"> <i class="ficon-pencil"></i> </a>
                                    <ul class="dropdown-menu">
                                        <li ng-init="getPreviousProfilePictures();">
                                            <a ng-show="previousPictures.length > 0" data-target="#uploadModal" data-toggle="modal" href="javascript:void(0);" ng-cloak>
                                                <span class="space-icon"><i class="ficon-upload"></i></span>
                                                <?php echo lang('upload_new'); ?>
                                            </a>
                                            <a id="uploadProPic" ng-show="previousPictures.length === 0" ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);" ng-cloak>
                                                <span class="space-icon"><i class="ficon-upload"></i></span>
                                                <?php echo lang('upload_new'); ?>
                                            </a>
                                        </li>
                                        <li><a href="javascript:void(0);" ng-if="ProfilePictureExists == 1" ng-click="removeProfilePicture()"><span class="space-icon"><i class="ficon-cross"></i></span><?php echo lang('remove'); ?></a></li>
                                    </ul>
                                </div>
                            </aside>
                            <!--//Left User info-->
                            <!--Right wall action-->
                            <aside class="wall-actions col-lg-3 col-md-4 hidden-xs hidden-sm">
                                <aside ng-cloak="" ng-if="config_detail.IsAdmin == true && LoginSessionKey!==''" class="pull-right hidden-xs hidden-sm">
                                    <div class="dropdown changecover-dropdown">
                                        <button type="button" class="dropdown-toggle btn btn-sm change-cover hidden-xs" data-toggle="dropdown">
                                            <?php echo lang('change_cover'); ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a id="profilebanner2" href="javascript:void(0);">
                                                    <input type="file" id="upload_cover" name="upload_cover" ngf-select="uploadCoverPhoto($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file, { validExtensions : ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG'] } );">
                                                    <span class="space-icon"><i class="ficon-upload"></i></span>
                                                    <?php echo lang('upload_new'); ?>
                                                </a>
                                            </li>
                                            <li>
                                                <a ng-click="selectBannerThemeModal();"> <span class="space-icon"><i class="ficon-upload"></i></span>Select Theme</a>
                                            </li>
                                            <li><a href="javascript:void(0);" ng-if="CoverExists == '1'" ng-click="removeProfileCover()"><span class="space-icon"><i class="ficon-cross"></i></span><?php echo lang('remove'); ?></a></li>
                                        </ul>
                                        <div style="display:none;" class="action-conver hidden-xs">
                                            <button class="btn btn-primary btn-sm" ng-click="ajax_save_crop_image();"><span><?php echo lang('apply'); ?></span></button>
                                            <button class="btn btn-default btn-sm" id="cancelCover" ng-click="apply_old_image(CoverImage)"><span><?php echo lang('cancel'); ?></span></button>
                                        </div>
                                    </div>
                                </aside>
                                <div class="clear"></div>
                            </aside>
                            <!--//Right wall action-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--//Banner-->
<!--  secondary-nav -->
<div class="secondary-nav" data-nav="fixed" data-scrollfix="scrollFix" ng-cloak>
    <div ng-click="save_cover_image_state();GroupDetails.CoverImageState='1'" ng-cloak ng-show="GroupDetails.CoverImageState=='2' && LoginSessionKey!==''" class="banner-button" data-banner="show"><i class="ficon-arrow-up"></i></div>
    <div class="container">
        <div class="row nav-row">
            <div class="filter-fixed" ng-show="filterFixed" ng-cloak>
                <button class="btn btn-default close-filter" ng-click="ResetFilter();filterFixed = false">
                    <span class="icon">
                        <i class="ficon-cross"></i>
                    </span>
                    <span class="caret"></span>
                </button>
                <div class="main-filter-nav">
                    <nav class="navbar navbar-default navbar-static">
                        <?php $this->load->view('include/filter-options') ?>
                    </nav>
                </div>
            </div>
            <div class="col-xs-5 col-sm-8 col-md-9">
                <aside class="pulled-nav tabs-menus">
                    <div ng-cloak="" class="navbar navbar-static">
                        <div class="navbar-header">
                            <?php if($pname=='wall' || $pname=='members' || $pname=='media' || $pname=='files' || $pname=='links' || $pname=='event'){ ?>
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#seconDaynav" aria-expanded="false">
                                <span class="text"><?php echo ucfirst($pname) ?></span>
                                <span class="icon"><i class="ficon-arrow-down"></i></span>
                            </button>
                            <?php } else { ?>
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#seconDaynav" aria-expanded="false" ng-cloak=""  ng-if="( config_detail.page_name != 'wall' ) && ( config_detail.page_name != 'members' ) && ( config_detail.page_name != 'media' ) &&  ( config_detail.page_name != 'event' ) && ( config_detail.page_name != 'files' ) && ( config_detail.page_name != 'links' ) ">
                                <span class="text" ng-bind="lang.wall"></span>
                                <span class="icon"><i class="ficon-arrow-down"></i></span>                                
                            </button>
                            <?php } ?>
                        </div>
                        <div class="navbar-collapse collapse" id="seconDaynav">
                            <ul class="nav navbar-nav nav-caret">
                                <li class="userIn">
                                    <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="m-user-thmb">
                                        <img ng-src="{{ProfileImage}}" class="img-circle" />
                                    </figure>
                                </li>
                                <?php if($pname=='wall' && empty($ActivityGUID)){ ?>                                
                                <li class="dropdown active">
                                    <a class="dropdown-toggle" data-toggle="dropdown" >
                                        <span ng-bind="PostTypeName"></span> 
                                        <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left">
                                        <li ng-hide="PostType=='0'">
                                            <a ng-click="filterPostType({'Value':0,'Label':'Wall'})">Wall</a>
                                        </li>
                                        <li ng-repeat="posttype in GroupDetails.AllowedPostType" ng-hide="posttype.Value==PostType" >
                                            <a ng-click="filterPostType(posttype)" ng-bind="posttype.Label"></a>
                                        </li>
                                    </ul>
                                </li>
                                
                                <?php } else { ?>
                                <li>
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'wall'); ?>">
                                        <?php echo lang('wall'); ?>
                                    </a>
                                </li>
                                <?php } ?>

                                <li class="<?php if($pname=='members'){ echo 'active'; } ?>" >
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'members'); ?>">
                                        <?php echo lang('members'); ?>
                                    </a>
                                </li>

                                <li class="<?php if($pname=='media'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'media'); ?>">
                                        <?php echo lang('media'); ?>
                                    </a>
                                </li>

                                <?php if(!$this->settings_model->isDisabled(14)): // Check if event module is enabled ?>
                                <li class="<?php if($pname=='event'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'event'); ?>">
                                        <?php echo lang('events_text'); ?>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <li class="dropdown" ng-if="LoginSessionKey!==''">
                                    <a class="dropdown-toggle" data-toggle="dropdown">More<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li class="<?php if($pname=='files'){ echo 'active'; } ?>">
                                            <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'files'); ?>">
                                                <?php echo lang('files'); ?>
                                            </a>
                                        </li>
                                        <li class="<?php if($pname=='links'){ echo 'active'; } ?>">
                                            <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'links'); ?>">
                                                <?php echo lang('links'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
                <!-- <div class="sub-nav-fix">
                    <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="m-user-thmb">
                        <img ng-src="{{ProfileImage}}" class="img-circle" />
                    </figure>
                    <ul class="group-info-tab" ng-cloak>
                        <li ng-cloak>
                            <span ng-if="config_detail.ModuleID == 1">
                                    <span ng-if="GroupDetails.Type == 'FORMAL'" class="g-info-name" ng-bind="GroupDetails.GroupName" ng-cloak></span>
                            <label ng-if="GroupDetails.Type == 'INFORMAL'" ng-cloak>
                                <span ng-repeat="Member in GroupDetails.EntityMembers"><span ng-bind="Member.FirstName" ng-if="$index <= 2"></span><span ng-if="$index < 2 && GroupDetails.EntityMembers.length >= 3">,</span><span ng-if="$index < (GroupDetails.EntityMembers.length - 1) && GroupDetails.EntityMembers.length < 3">,</span> </span>
                                <span ng-if="GroupDetails.EntityMembers.length > 3">and {{GroupDetails.EntityMembers.length - 3}} others</span>
                            </label>
                            <i class="icon-n-global" ng-if="GroupDetails.IsPublic == 1"></i>
                            <i class="icon-n-closed" ng-if="GroupDetails.IsPublic == 0"></i>
                            <i class="icon-n-group-secret" ng-if="GroupDetails.IsPublic == 2"></i>
                            </span>
                        </li>
                        <li ng-cloak ng-if="LoginSessionKey!==''">
                            <button ng-cloak type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <?php if($pname=='wall' && empty($ActivityGUID)){ ?>
                                <span class="text" ng-bind="PostTypeName"></span>
                                <?php } else { ?>
                                <span class="text"><?php echo ucfirst($pname) ?></span>
                                <?php } ?>
                                <i class="caret"></i>
                            </button>
                            <ul ng-cloak class="dropdown-menu" role="menu">
                                <?php if($pname=='wall' && empty($ActivityGUID)){ ?>
                                <li>
                                    <a ng-click="filterPostType({'Value':0,'Label':'Wall'})">Wall</a>
                                </li>
                                <li ng-repeat="posttype in GroupDetails.AllowedPostType" ng-if="LoginSessionKey!==''">
                                    <a ng-click="filterPostType(posttype)" ng-bind="posttype.Label"></a>
                                </li>
                                <?php } else { ?>
                                <li>
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'wall'); ?>">
                                        <?php echo lang('wall'); ?>
                                    </a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'members'); ?>">
                                        <?php echo lang('members'); ?>
                                    </a>
                                </li>
                                <li ng-if="LoginSessionKey!==''">
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'media'); ?>">
                                        <?php echo lang('media'); ?>
                                    </a>
                                </li>
                                 <?php if(!$this->settings_model->isDisabled(14)): // Check if event module is enabled ?>
                                <li ng-if="LoginSessionKey!==''">
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'event'); ?>">
                                        <?php echo lang('events_text'); ?>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li ng-if="LoginSessionKey!=='' && SettingsData.m38=='1'">
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'article'); ?>">
                                        Articles  
                                    </a>
                                </li>
                                <li ng-if="LoginSessionKey!==''">
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'files'); ?>">
                                        <?php echo lang('files'); ?>
                                    </a>
                                </li>
                                <li ng-if="LoginSessionKey!==''">
                                    <a target="_self" href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'links'); ?>">
                                        <?php echo lang('links'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div> -->
            </div>
            <div class="col-xs-7 col-sm-4 col-md-3">
                <aside class="nav-action-ctrl">
                    <div class="action-items" data-ng-controller="GroupPageCtrl">
                        <?php if($pname=='wiki' && isset($CanCreateWiki) && $CanCreateWiki){ ?>
                        <!--<button aria-expanded="false" class="btn  btn-primary" type="button" data-toggle="modal" data-target="#addWiki">
                            <span class="text"><i class="icon-add"></i> Add a Wiki</span>
                        </button>-->
                        <?php } ?>
                        <div class="btn-group" ng-cloak ng-if="GroupDetails.Permission.IsActiveMember == 1 && GroupDetails.Permission.DirectGroupMember == 1 ">
                            <span>
                                            <button  aria-expanded="false" data-toggle="dropdown" class="btn  btn-default dropdown-toggle" type="button"> <span class="text"><?php echo lang('joined'); ?></span> <i class="caret"></i> </button>
                            <ul role="menu" class="dropdown-menu">
                                <li>
                                    <a href="javascript:void(0);" ng-click='groupDropOutAction("", "fromWall")'>
                                        <?php echo lang('leave_group'); ?>
                                    </a>
                                </li>
                            </ul>
                            </span>
                        </div>
                        <div class="btn-group" ng-cloak ng-if="GroupDetails.Permission.IsInvited != 1 && GroupDetails.Permission.IsActiveMember != 1 && GroupDetails.IsPublic == 1 ">
                            <span>
                                                <button aria-expanded="false" class="btn btn-default" type="button" ng-click="joinPublicGroup('', 'fromWall');"> <span class="text"><?php echo lang('join_group'); ?></span> </button>
                            </span>
                        </div>
                        <div class="btn-group" ng-cloak ng-if="GroupDetails.Permission.IsInvited == false && GroupDetails.Permission.IsActiveMember == false && GroupDetails.IsPublic ==0 ">
                            <span ng-if="GroupDetails.Permission.IsInviteSent">
                                            <button aria-expanded="false" class="btn btn-default" type="button" ng-click="cancelInvite();"> <span class="text">Cancel Request</span> </button>
                            </span>
                            <span ng-if="!GroupDetails.Permission.IsInviteSent">
                                            <button aria-expanded="false" class="btn btn-default" type="button" ng-click="requestInvite();"> <span class="text">Request Invite</span> </button>
                            </span>
                        </div>
                        <div class="btn-group" ng-cloak ng-if="GroupDetails.Permission.IsInvited == 1  ">
                            <span>
                                                <button  aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"> <span class="text"><?php echo lang('accept') ?></span> <i class="caret"></i> </button>
                            <ul role="menu" class="dropdown-menu">
                                <li>
                                    <a ng-click="groupAcceptDenyRequest('', '2', 'FromWall')">
                                        <?php echo lang('accept') ?>
                                    </a>
                                </li>
                                <li>
                                    <a ng-click="groupAcceptDenyRequest('', '13', 'FromWall')">
                                        <?php echo lang('deny') ?>
                                    </a>
                                </li>
                            </ul>
                            </span>
                        </div>
                        
                        <div class="dropdown" ng-if="GroupDetails.Permission.IsAdmin == true ||  GroupDetails.Permission.IsCreator == true">
                            <button aria-expanded="true" data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"> <span class="icon"><i class="ficon-settings"></i></span> </button>
                            <ul role="menu" class="dropdown-menu">
                                <li ng-if="GroupDetails.Permission.IsAdmin == true ||  GroupDetails.Permission.IsCreator == true">
                                    <a href="javascript:void(0);" ng-click="loadCreateGroup('EditGroup',GroupDetails.GroupGUID);">
                                        <?php echo lang('edit'); ?>
                                    </a>
                                </li>
                                <li ng-if="GroupDetails.Permission.IsAdmin == true ||  GroupDetails.Permission.IsCreator == true">
                                    <a target="_self" ng-href="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'setting'); ?>">
                                        <?php echo lang('settings'); ?>
                                    </a>
                                </li>
                                <li ng-if="GroupDetails.Permission.IsCreator == true">
                                    <a ng-click="groupDelete(list.GroupGUID, 'Delete', '', 'fromWall')">
                                        <?php echo lang('delete'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php if($pname=='wall' || $pname=='wiki'){ ?>
                    <div class="action-items" ng-cloak ng-show="config_detail.IsAdmin=='1'" ng-click="filterFixed = true">
                        <a class="btn btn-default">
                            <span class="icon"><i class="ficon-filter"></i></span>
                        </a>
                    </div>
                    <?php } ?>
                </aside>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="posted_by" id="postedby" value="Anyone">
<input type="hidden" name="page_url" id="page_url" ng-value="config_detail.page_name">
<input type="hidden" name="cover_image_state" ng-value="GroupDetails.ConverImageState" id="cover_image_state">
<input type="hidden" name="LandingPage" id="LandingPage" value="<?php if(isset($LandingPage)) { echo $LandingPage; } ?>" />

<input type="hidden" id="GroupMediaUrl" value="<?php echo $this->group_model->get_group_url($ModuleEntityID, $GroupNameTitle, false, 'media'); ?>" />

<!-- // secondary-nav -->
