<div id="fb-root"></div>
<script type="text/javascript">
    window.fbAsyncInit = function () {
        FB.init({
            appId:FacebookAppId,
            xfbml: true,
            version: 'v2.5'
        });
    };
        (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>

<div class="banner-cover" ng-cloak ng-show="EventDetail.CoverImageState=='1'">
    <div class="banner banner-md">
        <div ng-if="LoginSessionKey!==''" ng-click="save_cover_image_state();EventDetail.CoverImageState='2'" class="banner-button hidden-xs" data-banner="hide"><i class="ficon-arrow-down"></i></div>
        <div class="breadcrumb-fluid">
          <ol class="breadcrumb container">
            <li class="breadcrumb-item">
              <a href="<?php echo site_url('events'); ?>" ng-bind="lang.event_listing"></a>
            </li>
            <li class="breadcrumb-item active">
              <span class="icon">
                <i class="ficon-arrow-right"></i>
              </span> {{EventDetail.Title}}
            </li>
          </ol>
        </div>
        <div class="image-cover clearfix">
            <div class="cover-inner" id="ib-main-wrapper">
                <input type="hidden" id="coX" value="0" />
                <input type="hidden" id="coY" value="" />
                <input type="hidden" id="hidden_image_cover" value="" />
                <input type="hidden" id="hidden_image_cover_data" value="" />
                <input type="hidden" id="image_src" value="0" />
                <input type="hidden" id="windowWidth" value="1920" />
                <!--<input type="file" id="upload_cover" name="upload_cover" >-->
                <div class="hiddendiv" id="CoverUpload"></div>
                <div style="display:none; width:100%;" id="coverDragimg" class="draggable holder ib-main">
                    <img id="image_cover" ng-src="{{CoverImage}}"  />
                </div>
                <div class="holder ib-main" id="coverViewimg">
                    <div class="blur-bg" ng-class="CoverImage == '' ? 'hide' : '';" style="background-image:url({{CoverImage}}); "></div>
                    <img class="cursor-pointer" ng-class="CoverImage == '' ? 'hide' : '';" id="coverImgProfile" ng-click="$emit('showMediaPopupGlobalEmitByImage', CoverImage, 1);" ng-src="{{CoverImage}}"  />
                </div>
            </div>
            <div class="loaderbtn cover-picture-loader" style="display:none;">
                <div class="loader" ng-if="applyCoverPictureLoader"></div>
                <div ng-if="profileCoverUploadPrgrs.progressPercentage && profileCoverUploadPrgrs.progressPercentage < 101" data-percentage="{{profileCoverUploadPrgrs.progressPercentage}}" upload-progress-bar-cs></div>
            </div>
            <div class="container cover-content">
                <div class="btn drag-cover hidden-xs"><i class="icon-drag"></i> {{lang.drag_repository_cover}}</div>
                <div class="row">
                  <div class="profile-container">
                    <div class="row">
                      <aside ng-cloak class="profile-pic col-md-8">
                        <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="cursor-pointer user-wall-thumb">
                            <img ng-src="{{ProfileImage}}" class="img-circle" />
                            <div class="loaderbtn profile-picture-loader">
                                <div class="spinner32"></div>
                            </div>
                        </figure>
                        <div class="profile-info with-caption" ng-class="{'with-caption' : EventDetail.Category}" ng-if="config_detail.ModuleID == 14">
                            <div class="user-name">
                                <label ng-bind="EventDetail.Title"></label>                                
                            </div>
                            <p class="profile-nametitle" ng-bind="EventDetail.CategoryName"></p>
                            <p class="profile-nametitle" ng-bind="EventDetail.Venue+', '+EventDetail.Location.FormattedAddress"></p>
                        </div>
                        <input type="hidden" id="isuserprofile" value="1" />
                        <div class="dropdown thumb-dropdown" ng-cloak="" ng-if="config_detail.IsAdmin == true && LoginSessionKey!==''">
                            <a class="edit-profilepic dropdown-toggle" href="javascript:void(0);" data-toggle="dropdown">
                                <i class="ficon-pencil"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li ng-init="getPreviousProfilePictures();">
                                    <a ng-show="previousPictures.length > 0" data-target="#uploadModal" data-toggle="modal" href="javascript:void(0);" ng-cloak>
                                        <span class="space-icon"><i class="ficon-upload"></i></span>{{lang.upload_new}}
                                    </a>
                                    <a id="uploadProPic" ng-show="previousPictures.length === 0" ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);" ng-cloak>
                                        <span class="space-icon"><i class="ficon-upload"></i></span>
                                        {{lang.upload_new}}
                                    </a>
                                </li>
                                <li><a href="javascript:void(0);" ng-if="ProfilePictureExists == 1" ng-click="removeProfilePicture()"><span class="space-icon"><i class="ficon-cross"></i></span>{{lang.remove}}</a></li>
                            </ul>
                        </div>
                      </aside>

                      <aside class="wall-actions col-md-4 hidden-xs hidden-sm">
                        <div class="pull-right" ng-cloak="" ng-if="config_detail.IsAdmin == true && LoginSessionKey!==''">
                          <div class="dropdown changecover-dropdown">
                            <button type="button" class="dropdown-toggle btn btn-sm change-cover hidden-xs" data-toggle="dropdown" ng-bind="lang.change_cover"></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a id="profilebanner2" href="javascript:void(0);">
                                        <input type="file" id="upload_cover" name="upload_cover" ngf-select="uploadCoverPhoto($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file, { validExtensions : ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG'] } );">
                                        <span class="space-icon">
                                            <i class="ficon-upload"></i>
                                        </span>{{lang.upload_new}}
                                    </a>
                                </li>
                                <li><a href="javascript:void(0);" ng-click="selectBannerThemeModal();"><span class="space-icon"><i class="ficon-upload"></i></span>{{lang.select_theme}}</a></li>
                              <li><a href="javascript:void(0);" ng-if="CoverExists == '1'" ng-click="removeEventProfileCover()"><span class="space-icon"><i class="ficon-cross"></i></span>{{lang.remove}}</a></li>
                            </ul>
                            <div style="display:none;" class="action-conver hidden-xs">
                                <button class="btn btn-primary btn-sm" ng-click="ajax_save_crop_image();"><span>{{lang.apply}}</span></button>
                                <button class="btn btn-default btn-sm" id="cancelCover" ng-click="apply_old_image(CoverImage)"><span>{{lang.cancel}}</span></button>
                            </div>
                          </div>
                        </div>
                      </aside>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--//Banner-->
<!--  secondary-nav -->
<div class="secondary-nav" data-nav="fixed" ng-cloak>
    <div ng-click="save_cover_image_state();EventDetail.CoverImageState='1'" ng-cloak ng-show="EventDetail.CoverImageState=='2' && LoginSessionKey!==''" class="banner-button" data-banner="show"><i class="ficon-arrow-up"></i></div>
    <div class="container">
        <div class="row nav-row">
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-12" ng-show="filterFixed" ng-cloak>
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
                    </div>
                    <div class="col-lg-9 col-xs-7 col-md-7 col-sm-8">
                        <aside class="pulled-nav unpulled-nav tabs-menus">
                            <div class="tab-dropdowns">
                                <a href="javascript:void(0);">
                                    <i class="icon-smallcaret"></i> 
                                    <span ng-bind="lang.{{config_detail.page_name}}" ng-if="( config_detail.page_name == 'wall' ) || ( config_detail.page_name == 'members' ) || ( config_detail.page_name == 'media' ) || ( config_detail.page_name == 'files' ) || ( config_detail.page_name == 'links' ) "></span>

                                    <span ng-bind="lang.wall" ng-if="( config_detail.page_name != 'wall' ) && ( config_detail.page_name != 'members' ) && ( config_detail.page_name != 'media' ) && ( config_detail.page_name != 'files' ) && ( config_detail.page_name != 'links' )"></span>
                                </a>
                            </div>
                            <ul class="nav navbar-nav small-screen-tabs hidden-xs">
                                <li class="<?php if($sub_pname=='about'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('about', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('about'); ?></a>
                                </li>
                                <li class="<?php if($sub_pname=='wall'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('wall', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('wall'); ?></a>
                                </li>
                                <li class="<?php if($sub_pname=='members'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('members', $ModuleEntityGUID, $EventTitleUrl);  ?>"><?php echo lang('attendes'); ?></a>
                                </li>
                                <li ng-if="EventDetail.TotalMediaCount > 0" class="<?php if($sub_pname=='media'){ echo 'active'; } ?>">
                                    <a target="_self" href="<?php echo  $this->event_model->getEventTitleUrl('media', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('media'); ?></a>
                                </li>
                            </ul>
                        </aside>
                        <div class="sub-nav-fix">
                            <figure ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" class="m-user-thmb">
                                <img ng-src="{{ProfileImage}}" />
                            </figure>
                            <ul class="group-info-tab" ng-cloak>
                                <li ng-cloak>
                                    <span  class="g-info-name" ng-bind="EventDetail.Title" ng-cloak></span>
                                </li>
                                <li ng-cloak ng-if="LoginSessionKey!==''">
                                    <button ng-cloak type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <?php
                                            if($sub_pname == 'members'){
                                        ?>
                                            <span class="text capt"><?php echo ucfirst(lang('members')); ?></a></span>
                                        <?php
                                            }else{
                                        ?>
                                            <span class="text capt"><?php echo ucfirst($sub_pname) ?></span>
                                        <?php
                                            }
                                        ?>
                                        
                                        <i class="caret"></i>
                                    </button>
                                    <ul ng-cloak class="dropdown-menu" role="menu">
                                        <li>
                                            <a target="_self" href="<?php echo  $this->event_model->getEventTitleUrl('about', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('about'); ?></a>
                                        </li>
                                        <li>
                                            <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('wall', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('wall'); ?></a>
                                        </li>
                                        <li>
                                            <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('members', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('attendes'); ?></a>
                                        </li>
                                        <li ng-if="EventDetail.TotalMediaCount > 0">
                                            <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('media', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('media'); ?></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-5 col-md-5 col-sm-4">
                        <aside class="filters dropdown">
                            <button ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'" aria-expanded="true" data-toggle="dropdown" class="btn btn-default btn-sm btn-filter pull-right m-l-10" type="button"> <span class="icon"><i class="ficon-settings"></i></span> <i class="caret"></i></button>
                            <ul role="menu" class="dropdown-menu" ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'">
                                <li ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'"><a href="" data-toggle="modal" ng-if="EventDetail.EventStatus !== 'Past'" data-ng-click="loadPopUp('edit_event','assets/partials/event/edit_event.html');"><?php echo lang('edit'); ?></a></li>

                                <li ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'"><a href="" ng-if="EventDetail.EventStatus == 'Past' || (EventDetail.EventStatus == 'Upcoming' && EventDetail.MemberCount == 1)" data-ng-click="DeleteEvent(1);" ng-bind="lang.delete"></a></li>
                                <li ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'"><a href="" ng-if="(EventDetail.EventStatus == 'Upcoming' && EventDetail.MemberCount > 1) || EventDetail.EventStatus == 'Running'" data-ng-click="DeleteEvent(2);" ng-bind="lang.cancel"></a></li>
                            </ul>

                            <?php if($sub_pname=='wall'){ ?>
                            <button class="btn btn-default btn-sm btn-filter pull-right" ng-click="filterFixed = true" ng-cloak ng-show="config_detail.IsAdmin=='1'">
                                <span class="icon">
                                    <i class="ficon-filter"></i>
                                </span>
                            </button>
                            <?php } ?>
                        </aside>
                    </div>
                </div>
            </div>                 
        </div>
    </div>
</div>
<input type="hidden" name="posted_by" id="postedby" value="Anyone">
<input type="hidden" name="page_url" id="page_url" ng-value="config_detail.page_name">
<input type="hidden" name="cover_image_state" ng-value="config_detail.ConverImageState" id="cover_image_state">
<input type="hidden" name="LandingPage" id="LandingPage" value="<?php if(isset($LandingPage)) { echo $LandingPage; } ?>" />
<!-- // secondary-nav -->

