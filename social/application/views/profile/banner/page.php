<div class="banner-cls banner-cover" ng-cloak ng-show="pageDetails.CoverImageState=='1'">
    <div ng-if="LoginSessionKey!==''" ng-click="save_cover_image_state();pageDetails.CoverImageState='2'" class="banner-button hidden-xs" data-banner="hide"><i class="ficon-arrow-down"></i></div>
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
                                    <img ng-src="{{ProfileImage}}" class="img-circle" />
                                    <div class="loaderbtn profile-picture-loader">
                                        <div class="spinner32"></div>
                                    </div>
                                </figure>
                                <!--Start  Group-->
                                <div class="profile-info profile-rating" ng-class="{'with-caption' : pageDetails.Category}" ng-if="config_detail.ModuleID == 18">
                                    <div class="user-name profile-rating-2">
                                        <label ng-bind="pageDetails.Title"></label>
                                        <span class="secured hidden-xs" ng-show="pageDetails.IsVerified == 1">
                                            <i class="ficon-checkmark f-green"></i>
                                        </span>
                                        <span ng-if="avgRateValue > 0" class="rating-class {{RateClassName}}" ng-bind=" ' RATED ' + avgRateValue"> </span>
                                    </div>
                                    <p class="profile-nametitle" ng-bind="pageDetails.Category"></p>
                                </div>
                                <!--End  Group-->
                                <input type="hidden" id="isuserprofile" value="1" />
                                <div ng-cloak="" ng-if="config_detail.IsAdmin == true && LoginSessionKey!==''" class="dropdown thumb-dropdown">
                                    <a class="edit-profilepic dropdown-toggle" data-toggle="dropdown"> <i class="ficon-pencil"></i> </a>
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
<div class="secondary-nav" data-nav="fixed">
    <div ng-click="save_cover_image_state();pageDetails.CoverImageState='1'" ng-cloak ng-show="pageDetails.CoverImageState=='2' && LoginSessionKey!==''" class="banner-button" data-banner="show"><i class="ficon-arrow-up"></i></div>
    <div class="container">
        <div class="row nav-row">
            <div class="filter-fixed" ng-show="filterFixed" ng-cloak>
                <button class="btn btn-default close-filter" ng-click="filterFixed = false">
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
            <div class="col-xs-12 col-sm-8 col-md-9">
                <aside class="pulled-nav tabs-menus">
                    <div ng-cloak="" class="navbar navbar-static">
                        <div class="navbar-header">
                            <button ng-cloak="" type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#seconDaynav" aria-expanded="false" ng-bind="lang.{{config_detail.page_name}}" ng-if="( config_detail.page_name == 'wall' ) || ( config_detail.page_name == 'members' ) || ( config_detail.page_name == 'media' ) || ( config_detail.page_name == 'files' ) || ( config_detail.page_name == 'links' ) "></button>
                            <button ng-cloak="" type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#seconDaynav" aria-expanded="false" ng-cloak="" ng-bind="lang.wall" ng-if="( config_detail.page_name != 'wall' ) && ( config_detail.page_name != 'members' ) && ( config_detail.page_name != 'media' ) && ( config_detail.page_name != 'event' ) && ( config_detail.page_name != 'files' ) && ( config_detail.page_name != 'links' ) "></button>
                        </div>
                        <div class="navbar-collapse collapse" id="seconDaynav">
                            <ul class="nav navbar-nav nav-caret">
                                <?php if($pname=='wall' && empty($ActivityGUID)){ ?>
                                <li class="dropdown active">
                                    <a class="dropdown-toggle" data-toggle="dropdown"><span ng-bind="PostTypeName"></span> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-left">
                                        <li ng-hide="PostType=='0'">
                                            <a ng-click="filterPostType({'Value':0,'Label':'All Posts'})">All Posts</a>
                                        </li>
                                        <li ng-hide="PostType=='1'">
                                            <a ng-click="filterPostType({'Value':1,'Label':'Discussion'})">Discussion</a>
                                        </li>
                                        <li ng-hide="PostType=='2'">
                                            <a ng-click="filterPostType({'Value':2,'Label':'Q & A'})">Q & A</a>
                                        </li>
                                    </ul>
                                </li>
                                <?php } else { ?>
                                <li >
                                    <a target="_self" href="<?php echo base_url() . "page/" ?>{{pageDetails.PageURL}}">
                                        <?php echo lang('wall'); ?>
                                    </a>
                                </li>
                                <?php } ?>

                                <li class="<?php if($pname=='followers'){ echo 'active'; } ?>">
                                    <a href="<?php echo base_url() ?>page/{{pageDetails.PageURL}}/followers"><?php echo lang('followers'); ?></a>
                                </li>
                                <li class="<?php if($pname=='media'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/media"><?php echo lang('media'); ?></a>
                                </li>
                                
                                <?php if(!$this->settings_model->isDisabled(14)): // Check if event module is enabled ?>
                                <li class="<?php if($pname=='event'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/event">
                                        <?php echo lang('events_text'); ?>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <li class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown">More<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li class="<?php if($pname=='files'){ echo 'active'; } ?>">
                                            <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/files"><?php echo lang('files'); ?></a>
                                        </li>
                                        <li class="<?php if($pname=='links'){ echo 'active'; } ?>">
                                            <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/links"><?php echo lang('links'); ?></a>
                                        </li>
                                        <li ng-if="Settings.m23 == '1'" class="<?php if($pname=='ratings'){ echo 'active'; } ?>">
                                            <a ng-href="<?php echo base_url() ?>page/{{pageDetails.PageURL}}/ratings"><?php echo lang('ratings'); ?></a>
                                        </li>
                                    </ul>
                                </li>                               
                                
                            </ul>
                        </div>
                    </div>
                </aside>
                <div class="sub-nav-fix">
                    <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="m-user-thmb">
                        <img ng-src="{{ProfileImage}}" class="img-circle" />
                    </figure>
                    <ul class="group-info-tab" ng-cloak>
                        <li ng-cloak>
                            <span class="g-info-name" ng-bind="pageDetails.Title" ng-cloak></span>
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
                                    <li ng-hide="PostType=='0'">
                                        <a ng-click="filterPostType({'Value':0,'Label':'All Posts'})">All Posts</a>
                                    </li>
                                    <li ng-hide="PostType=='1'">
                                        <a ng-click="filterPostType({'Value':1,'Label':'Discussion'})">Discussion</a>
                                    </li>
                                    <li ng-hide="PostType=='2'">
                                        <a ng-click="filterPostType({'Value':2,'Label':'Q & A'})">Q & A</a>
                                    </li>
                                <?php } else { ?>
                                <li>
                                    <a target="_self" href="<?php echo base_url() . "page/" ?>{{pageDetails.PageURL}}">
                                        <?php echo lang('wall'); ?>
                                    </a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="<?php echo base_url() . "pages/followers/" . $PageGUID ?>"><?php echo lang('followers'); ?></a>
                                </li>
                                <li>
                                    <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/media"><?php echo lang('media'); ?></a>
                                </li>
                                
                                <?php if(!$this->settings_model->isDisabled(14)): // Check if event module is enabled ?>
                                <li>
                                    <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/event"><?php echo lang('events_text'); ?></a>
                                </li>
                                <?php endif; ?>
                                
                                <li>
                                    <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/files"><?php echo lang('files'); ?></a>
                                </li>
                                <li>
                                    <a target="_self" href="<?php echo base_url(); ?>page/{{pageDetails.PageURL}}/links"><?php echo lang('links'); ?></a>
                                </li>
                                <li>
                                    <a ng-href="<?php echo base_url() ?>page/{{pageDetails.PageURL}}/ratings"><?php echo lang('ratings'); ?></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-3">
                <aside class="filters">
                    <div class="inner-follow-frnds pull-right">
                        <div class="inner-follow-frnds">
                            
                                <?php
                                    if ($IsFollowerPage == 1)
                                    {
                                        ?>
                                        <button type="button" class="btn btn-default  m-l-10" data-toggle="dropdown" aria-expanded="false" ng-cloak ng-click="toggleFollow(pageDetails.PageID, 'FollowerPage', pageDetails.PageGUID);">
                                            <?php
                                        } else
                                        {
                                            ?>
                                            <button type="button" ng-class="(pageDetails.IsFollowed == 1) ? 'following btn-text' : '' ;" class="btn btn-default  m-l-10" data-toggle="dropdown" aria-expanded="false" ng-cloak ng-click="toggleFollow(pageDetails.PageID, 'PageWall', '');">
                                            <?php }
                                            ?>
                                            <span class="text" ng-cloak ng-if="pageDetails.IsFollowed != 1">Follow</span>
                                            <span class="text" ng-cloak ng-if="pageDetails.IsFollowed == 1"><span>Following</span></span>
                                        </button>
                                <div class="dropdown m-l-10" ng-cloak>
                                <button ng-cloak ng-if="config_detail.IsAdmin == true" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span class="icon"><i class="ficon-settings"></i></span>
                                </button>
                                <ul class="dropdown-menu" role="menu" ng-cloak  ng-if="config_detail.IsAdmin == true">
                                    <!-- <li ng-cloak ng-if="pageDetails.IsSubscribed !== 1"><a ng-click="toggle_subscribe_entity(pageDetails.PageGUID, 'PAGE')">Subscribe</a></li>
                                    <li ng-cloak ng-if="pageDetails.IsSubscribed == 1"><a ng-click="toggle_subscribe_entity(pageDetails.PageGUID, 'PAGE')">Unsubscribe</a></li> -->
                                    <li ng-if="config_detail.IsAdmin == true ">
                                        <a href="<?php echo base_url() . "pages/edit_page/{{pageDetails.PageGUID}}"; ?>" >Edit</a>
                                    </li>
                                    <li ng-if="config_detail.IsAdmin == true ">
                                        <a ng-click='deletePage(pageDetails.PageGUID, "<?php echo lang('delete_page'); ?>", "<?php echo lang('delete_page_message'); ?>")'>Delete</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div ng-cloak="" ng-if="ratingList.length > 0" class="dropdown m-l-10">
                            <div class="dropdown"> <a data-toggle="dropdown" class="dropdown-toggle btn btn-default" href="javascript:void(0);" aria-expanded="true"> <span class="icon"><i class="ficon-filter"></i></span> </a>
                                <ul class="dropdown-menu custom-filters">
                                    <li class="list-head"><span>Sort By</span> <!-- ngIf: IsSFilter == '1' --></li>
                                    <li ng-class="(filter.SortBy == '2') ? 'active' : '';"><a ng-click="setSortBy(2)"><!-- ngIf: filter.SortBy == '2' -->Most helpful</a></li>
                                    <li ng-class="(filter.SortBy == '1') ? 'active' : '';"><a ng-click="setSortBy(1)"><!-- ngIf: filter.SortBy == '1' -->Recent</a></li>
                                    <li ng-class="(filter.SortBy == '3') ? 'active' : '';"><a ng-click="setSortBy(3)"><!-- ngIf: filter.SortBy == '3' -->My network</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php if($pname=='wall'){ ?>
                    <div ng-cloak ng-show="config_detail.IsAdmin=='1'" class="dropdown" ng-click="filterFixed = true">
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
<input type="hidden" name="cover_image_state" ng-value="config_detail.ConverImageState" id="cover_image_state">
<input type="hidden" name="LandingPage" id="LandingPage" value="<?php if(isset($LandingPage)) { echo $LandingPage; } ?>" />
<!-- // secondary-nav -->
