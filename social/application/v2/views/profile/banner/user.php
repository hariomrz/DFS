<div class="banner-cover" ng-cloak  ng-show="config_detail.CoverImageState=='1'">
    <div ng-if="LoginSessionKey!==''" ng-click="save_cover_image_state();config_detail.CoverImageState='2'" class="banner-button hidden-xs" data-banner="hide"><i class="ficon-arrow-down"></i></div>
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
                                    <img err-name="{{FirstName + ' ' + LastName}}"  ng-src="{{ProfileImage}}" class="img-circle" />
                                    <div class="loaderbtn profile-picture-loader">
                                        <div class="spinner32"></div>
                                    </div>
                                </figure>
                                <!--Start  Group-->
                                <div class="profile-info" ng-class="{'with-caption' : Tagline}">
                                    <div class="user-name">
                                         <label ng-bind="FirstName + ' ' + LastName" ng-cloak></label>
                                    </div>
                                    <p ng-if="Tagline" class="profile-nametitle" ng-bind="Tagline"></p>
                                    <ul class="activity-nav cat-sub-nav">
                                    </ul>
                                    <!--  <p class="profile-nametitle" ng-bind="GroupDetails.Category.Name"></p> -->
                                </div>
                                <!--End  Group-->
                                <input type="hidden" id="isuserprofile" value="1" />
                                <div ng-cloak="" ng-if="config_detail.IsAdmin == true && LoginSessionKey!==''" class="dropdown thumb-dropdown">
                                    <a class="edit-profilepic dropdown-toggle" data-toggle="dropdown"> <i class="ficon-pencil"></i> </a>
                                    <ul class="dropdown-menu">
                                        <li ng-init="getPreviousProfilePictures();">
                                            <a ng-show="previousPictures.length > 0" data-target="#uploadModal" data-toggle="modal" ng-cloak>
                                                <span class="space-icon"><i class="ficon-upload"></i></span>
                                                <?php echo lang('upload_new'); ?>
                                            </a>
                                            <a id="uploadProPic" ng-show="previousPictures.length === 0" ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);" ng-cloak>
                                                <span class="space-icon"><i class="ficon-upload"></i></span>
                                                <?php echo lang('upload_new'); ?>
                                            </a>
                                        </li>
                                        <li><a ng-if="ProfilePictureExists == 1" ng-click="removeProfilePicture()"><span class="space-icon"><i class="ficon-cross"></i></span><?php echo lang('remove'); ?></a></li>
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
    <div ng-click="save_cover_image_state();config_detail.CoverImageState='1'" ng-cloak ng-show="config_detail.CoverImageState=='2' && LoginSessionKey!==''" class="banner-button" data-banner="show"><i class="ficon-arrow-up"></i></div>
    <div class="container">
        <div class="row nav-row">
            <div class="filter-fixed" ng-show="filterFixed" ng-cloak>
                <button class="btn btn-default close-filter" ng-click="filterFixed = false">
                    <span class="icon">
                        <i class="ficon-cross"></i>
                    </span>                    
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
                            <button type="button" class="navbar-toggle collapsed capt" data-toggle="collapse" data-target="#seconDaynav">
                                <span class="text"><?php echo $pname ?></span>
                                <span class="icon"><i class="ficon-arrow-down"></i></span>
                            </button>
                        </div>
                        <div class="navbar-collapse collapse" id="seconDaynav">
                            <ul class="nav navbar-nav nav-caret">
                                <li class="userIn">
                                    <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="m-user-thmb">
                                        <img err-name="{{FirstName + ' ' + LastName}}" ng-src="{{ProfileImage}}" class="img-circle" />
                                    </figure>
                                </li>
                                <?php if($this->session->userdata('UserID')){ ?>
                                <li ng-class="{'active':config_detail.page_name== 'about'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/about" ng-bind="lang.about" ng-cloak=""></a></li>
                                <?php } ?>
                                <li ng-class="{'active':config_detail.page_name== 'wall'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>" ng-bind="lang.wall" ng-cloak=""></a></li>
                                <?php if($this->session->userdata('UserID')){ ?>
                                <li ng-class="{'active':config_detail.page_name== 'connections'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/connections" ng-bind="lang.connections" ng-cloak=""></a></li>
                                <?php if($this->session->userdata('UserID')!=$UserID && !$this->settings_model->isDisabled(1)){ ?>
                                <li class="<?php if($pname=='groups') { echo 'active'; } ?>" ng-class="{'active':config_detail.page_name== 'groups'}">
                                    <a target="_self" href="<?php echo get_entity_url($UserID) ?>/groups" ng-bind="lang.groups" ng-cloak=""></a>
                                </li>
                                <?php } ?>
                                <li ng-class="{'active':config_detail.page_name== 'media'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/media" ng-bind="lang.media" ng-cloak=""></a></li>
                                <li class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown">More<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li ng-class="{'active':config_detail.page_name== 'files'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/files" ng-bind="lang.files" ng-cloak=""></a></li>    
                                        <li ng-class="{'active':config_detail.page_name== 'links'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/links" ng-cloak ng-bind="lang.links"> Links </a></li>    
                                        <li ng-if="ProfileEndorse == true && ProfileEndorseCount == true" ng-class="{'active':config_detail.page_name== 'endorsment'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/endorsment">Endorsement</a></li>
                                    </ul>
                                </li>                                
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </aside>
                <!-- <div class="sub-nav-fix">
                    <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="m-user-thmb">
                        <img err-name="{{FirstName + ' ' + LastName}}" ng-src="{{ProfileImage}}" class="img-circle" />
                    </figure>
                    <ul class="group-info-tab" ng-cloak>
                        <li ng-cloak>
                            <span class="g-info-name" ng-bind="FirstName + ' ' + LastName" ng-cloak></span>
                            <input type="hidden" id="isuserprofile" value="1" />
                        </li>
                        <li ng-cloak ng-if="LoginSessionKey!==''">
                            <button ng-cloak type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <span class="text capt"><?php echo $pname ?></span>
                                <i class="caret"></i>
                            </button>
                            <ul ng-cloak class="dropdown-menu" role="menu">
                                <?php if($this->session->userdata('UserID')){ ?>
                                <li ng-class="{'active':config_detail.page_name== 'about'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/about" ng-bind="lang.about" ng-cloak=""></a></li>
                                <?php } ?>
                                <li ng-class="{'active':config_detail.page_name== 'wall'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>" ng-bind="lang.wall" ng-cloak=""></a></li>
                                <?php if($this->session->userdata('UserID')){ ?>
                                <li ng-class="{'active':config_detail.page_name== 'connections'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/connections" ng-bind="lang.connections" ng-cloak=""></a></li>
                                <?php if($this->session->userdata('UserID')!=$UserID && !$this->settings_model->isDisabled(1)){ ?>
                                <li ng-class="{'active':config_detail.page_name== 'groups'}">
                                    <a target="_self" href="<?php echo get_entity_url($UserID) ?>/groups" ng-bind="lang.groups" ng-cloak=""></a>
                                </li>
                                <?php } ?>
                                <li ng-class="{'active':config_detail.page_name== 'media'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/media" ng-bind="lang.media" ng-cloak=""></a></li>
                                <li ng-class="{'active':config_detail.page_name== 'files'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/files" ng-bind="lang.files" ng-cloak=""></a></li>    
                                <li ng-class="{'active':config_detail.page_name== 'links'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/links" ng-cloak ng-bind="lang.links"> Links </a></li>    
                                <li ng-if="ProfileEndorse == true && ProfileEndorseCount == true" ng-class="{'active':config_detail.page_name== 'endorsment'}"><a target="_self" href="<?php echo get_entity_url($UserID) ?>/endorsment">Endorsement</a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                </div> -->
            </div>
            <div class="col-xs-7 col-sm-4 col-md-3">
                <aside class="nav-action-ctrl">
                    <div class="action-items" id="UserListCtrl1" ng-controller="UserListCtrl" ng-init="getProfileUser()">
                        
                        <div class="btn-group" ng-cloak ng-if="config_detail.IsAdmin == false && config_detail.ModuleID == 3 && LoginSessionKey!==''"> 
                            
                                <button type="button" data-toggle="dropdown" ng-class="{'loader-btn':FrndsReqLoaderBtn}" class="btn btn-default dropdown-toggle addfrnds" lang="{{profileUser.UserID}}" ng-cloak ng-if="profileUser.FriendStatus == '4' && profileUser.ShowFriendsBtn == '1'"> <span class="text"><?php echo lang('send_request') ?></span> <i class="caret"></i>
                                    <span class="btn-loader">
                                        <span class="spinner-btn">&nbsp;</span>
                                    </span>
                                </button>

                                <button type="button" data-toggle="dropdown" ng-class="{'loader-btn':FrndsReqLoaderBtn}"  class="btn btn-default dropdown-toggle addfrnds" lang="{{profileUser.UserID}}" ng-cloak ng-if="profileUser.FriendStatus == '2' && profileUser.ShowFriendsBtn == '1'" ><span class="text"><?php echo lang('cancel_request') ?></span> <i class="caret"></i>
                                    <span class="btn-loader">
                                        <span class="spinner-btn">&nbsp;</span>
                                    </span>
                                </button>

                                <button type="button" data-toggle="dropdown" ng-class="{'loader-btn':FrndsReqLoaderBtn}"  class="btn btn-default dropdown-toggle addfrnds" lang="{{profileUser.UserID}}" ng-cloak ng-if="profileUser.FriendStatus == '3'" ><span class="text"><?php echo lang('accept') ?></span> <i class="caret"></i>
                                    <span class="btn-loader">
                                        <span class="spinner-btn">&nbsp;</span>
                                    </span>
                                </button>

                                <button ng-if="profileUser.ShowFriendsBtn == '0' && profileUser.ShowFollowBtn == 1 && profileUser.follow !== ''" class="btn btn-default addfrnds" ng-click="follow(profileUser.UserGUID)" ng-bind="profileUser.follow" id="followmem{{profileUser.UserGUID}}"></button>

                                <button type="button" data-toggle="dropdown" ng-class="{'loader-btn':FrndsReqLoaderBtn}"  class="btn btn-default dropdown-toggle addfrnds"lang="{{profileUser.UserID}}" ng-cloak ng-if="profileUser.FriendStatus == '1' && profileUser.ShowFriendsBtn == '1'"><span class="text"><?php echo lang('delete_request') ?></span> <i class="caret"></i>
                                    <span class="btn-loader">
                                        <span class="spinner-btn">&nbsp;</span>
                                    </span>
                                </button>

                                <ul role="menu" class="dropdown-menu">
                                    <li ng-if="profileUser.FriendStatus == '4' && profileUser.ShowFriendsBtn == '1'"><a id="friendrequest" ng-click="sendRequest(profileUser.UserGUID)"><?php echo lang('send_request') ?></a></li>

                                    <li ng-if="profileUser.FriendStatus == '2' && profileUser.ShowFriendsBtn == '1'"><a ng-click="rejectRequest(profileUser.UserGUID)"><?php echo lang('cancel_request') ?></a></li>

                                    <li ng-if="profileUser.FriendStatus == '3'"><a ng-click="acceptRequest(profileUser.UserGUID)"><?php echo lang('accept') ?></a></li>

                                    <li ng-if="profileUser.FriendStatus == '3'"><a ng-click="denyRequest(profileUser.UserGUID)"><?php echo lang('deny') ?></a></li>

                                    <li ng-if="profileUser.FriendStatus == '1' && profileUser.ShowFriendsBtn == '1'"><a ng-click="removeFriend(profileUser.UserGUID)"><?php echo lang('delete_request') ?></a></li>

                                    <li ng-if="profileUser.ShowFollowBtn == 1 && profileUser.follow !== ''"><a class="followuser" ng-click="follow(profileUser.UserGUID)" ng-bind="profileUser.follow" id="followmem{{profileUser.UserGUID}}"></a></li>
                                </ul>
                            
                        </div>
                      
                        <div class="dropdown" ng-cloak ng-if="config_detail.IsAdmin == false && config_detail.ModuleID == 3 && LoginSessionKey!==''">
                            <button aria-expanded="true" data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><span class="icon"><i class="ficon-settings"></i></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <li ng-cloak ng-if="profileUser.ShowMessageBtn == 1"><a data-target="#newMsg" data-toggle="modal">Message</a></li>
                                <li><a ng-if="profileUser.CanReport == '1'" data-toggle="modal" id="tid-user-{{profileUser.UserGUID}}" data-target="#reportAbuse" onClick="flagValSet(this, 'User')"><?php echo lang('report'); ?></a></li>
                                <li><a ng-click="blockUser2(<?php echo $UserID ?>)"><?php echo lang('block'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                    <?php $this->load->view('widgets/message-popup') ?>
                    <?php if($pname=='wall' && (empty($ActivityGUID)) ){ ?>
                    <div class="action-items">
                        <div ng-cloak ng-show="config_detail.IsAdmin=='1'" class="dropdown" ng-click="filterFixed=true">
                            <a class="btn btn-default">
                                <span class="icon"><i class="ficon-filter"></i></span>
                            </a>
                        </div>
                    </div>
                    <?php } ?>
                </aside>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="posted_by" id="postedby" value="Anyone">
<input type="hidden" name="page_url" id="page_url" value="<?php echo $pname ?>">
<input type="hidden" name="cover_image_state" ng-value="config_detail.ConverImageState" id="cover_image_state">
<input type="hidden" name="LandingPage" id="LandingPage" value="<?php if(isset($LandingPage)) { echo $LandingPage; } ?>" />
<!-- // secondary-nav -->
