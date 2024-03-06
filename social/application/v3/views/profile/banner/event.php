<div id="fb-root"></div>
<script type="text/javascript">
    window.fbAsyncInit = function () {
        FB.init({
            appId: FacebookAppId,
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
    }(document, 'script', 'facebook-jssdk'));</script>

<!--<div class="banner-cover" ng-cloak ng-show="EventDetail.CoverImageState == '1'">-->
<div class="breadcrumb-fluid breadcrumb-primary" ng-cloak>
    <ol class="breadcrumb container">
        <li class="breadcrumb-item">
            <a target="_self" href="{{BaseUrl}}">
                <span class="icon">
                  <i class="ficon-home"></i>
                </span>
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="icon">
                <i class="ficon-arrow-right"></i>
            </span>
            <a href="<?php echo site_url('events'); ?>" ng-bind="lang.event_listing"></a>
        </li>
        <li class="breadcrumb-item active">
            <span class="icon">
                <i class="ficon-arrow-right"></i>
            </span> {{EventDetail.Title}}
        </li>
    </ol>
</div>
<div data-ng-controller="EventUserController">
    <div class="banner-caption" ng-cloak>
        <div class="container container-primary">
            <div class="banner-content">
                <div class="row">
                    <div class="col-sm-8">
                        <div class="banner-thumb">
                            <div class="banner-thumb-inner">
                                <figure class="banner-figure">
                                    <div class="thumbnail-upload" ng-cloak>
                                        <a ng-click="$emit('showMediaPopupGlobalEmitByImage', ProfileImage, 1);" ng-cloak>
                                            <img ng-cloak ng-src="{{ProfileImage}}"/>
                                        </a>
                                        <div class="dropdown thumbup-dropdown" ng-cloak ng-if="config_detail.IsAdmin == true && LoginSessionKey !== ''">
                                            <a class="dropdown-toggle  circle-icon circle-default" data-toggle="dropdown" aria-expanded="true">
                                                <i class="ficon-camera"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-left">
                                                <li ng-init="getPreviousProfilePictures();">
                                                    <a ng-show="previousPictures.length > 0" data-target="#uploadModal" data-toggle="modal" href="javascript:void(0);" ng-cloak>
                                                        <span class="space-icon"><i class="ficon-upload"></i></span>{{lang.upload_new}}
                                                    </a>
                                                    <a id="uploadProPic" ng-show="previousPictures.length === 0" ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);" ng-cloak>
                                                        <span class="space-icon"><i class="ficon-upload"></i></span>
                                                        {{lang.upload_new}}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" ng-if="ProfilePictureExists == 1" ng-click="removeProfilePicture()"><span class="space-icon"><i class="ficon-cross"></i></span>{{lang.remove}}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </figure>
                                <div class="banner-detail" ng-cloak>
                                    <span class="text-base-off bold" ng-cloak ng-bind="EventDetail.CategoryName"></span>
                                    <h2 class="title" ng-cloak ng-bind="EventDetail.Title"></h2>
                                    <span class="loc">
                                        <span class="icon"><i class="ficon-location"></i></span> 
                                        <span class="text" ng-cloak ng-bind="EventDetail.Venue + ', ' + EventDetail.Location.FormattedAddress"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="banner-body hidden-xs">
                                <p ng-cloak ng-bind-html="EventDetail.Summary"></p>
                                <span ng-cloak class="postedby">Posted by: <a target="_self" ng-href="{{baseUrl + EventDetail.CreatedBy.ProfileURL}}" ng-bind="EventDetail.CreatedBy.Name"></a></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="banner-schedule">
                            <div class="schedule-box row no-gutter">
                                <div class="col-xs-6 col-sm-12">
                                    <div class="day" ng-cloak ng-bind="EventDetail.EventDay"></div>
                                    <div class="date" ng-cloak ng-bind="EventDetail.EventStartDate"></div>
                                    <div class="time" ng-cloak ng-if="EventDetail.StartDate == EventDetail.EndDate" ng-bind="EventDetail.DisplayStartTime + ' - ' + EventDetail.DisplayEndTime"></div>
                                    <div class="time" ng-cloak ng-bind="EventDetail.DisplayStartTime" ng-if="EventDetail.StartDate != EventDetail.EndDate"></div>
                                </div>
                                <div class="end-date col-xs-6 col-sm-12" ng-cloak ng-if="EventDetail.StartDate != EventDetail.EndDate">
                                    <span class="text-sm">END ON</span>
                                    <p>{{EventDetail.EventEndDate}} <span class="text-off">at {{EventDetail.DisplayEndTime}}</span></p>
                                </div>            
                            </div>
                            <div class="event-actions" ng-if="EventDetail.IsPast!='1'" ng-cloak> 
                                <a class="btn btn-primary btn-lg btn-block uppercase" ng-cloak
                                   ng-show="user_status == 'INVITED' || ((user_status == '' || user_status == 'NOT_ATTENDING') && EventDetail.Privacy == 'PUBLIC') && loggedUserRole != '1' && loggedUserRole != '2' && EventDetail.IsDeleted != 2" 
                                   ng-bind="lang.attend_now" 
                                   ng-if="EventDetail.IsDeleted != 2"
                                   data-ng-click="UpdateUsersPresence('ATTENDING');
                                               EventDetail.CanPostOnWall = '1'">
                                </a>
                                <div class="dropdown" ng-cloak ng-if="loggedUserRole != '1' && loggedUserRole != '2' && EventDetail.IsDeleted == 2">
                                    <a class="btn btn-default btn-block attand-now-btn btn-lg">
                                        <span class="icon"><i class="ficon-checkmark f-lg"></i></span><span class="text">{{lang.cancelled}} </span> 
                                    </a>
                                </div>

                                <div class="dropdown" ng-cloak ng-if="user_status == 'ATTENDING' && loggedUserRole != '1' && loggedUserRole != '2' && EventDetail.IsDeleted != 2">
                                    <a class="btn btn-default btn-block attand-now-btn btn-lg" data-toggle="dropdown">
                                        <span class="icon"><i class="ficon-checkmark f-lg"></i></span><span class="text">{{lang.attending}} <i class="ficon-arrow-down f-mlg"></i></span> 
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="javascript:void(0);" data-ng-click="UpdateUsersPresence('NOT_ATTENDING');
                                                    EventDetail.CanPostOnWall = '0'" ng-bind="lang.leave"></a></li>
                                    </ul>
                                </div>
                                <a class="btn btn-default btn-lg btn-block disabled" ng-cloak ng-if="user_status == 'ATTENDING' && (loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted != 2" ng-cloak>
                                    {{lang.attending}}
                                </a>
                                <div ng-cloak class="panel-footer" ng-if="loggedUserRole != '1' && loggedUserRole != '2' && (user_status == 'INVITED' || user_status == 'DECLINED')">
                                    <div>
                                        <a class="text-primary" ng-if="user_status == 'INVITED'" data-ng-click="UpdateUsersPresence('DECLINED');" ng-bind="lang.unable_to_attend_event"></a>
                                    </div>
                                    <div ng-cloak>
                                        <a class="text-link" ng-if="user_status == 'DECLINED'" data-ng-click="UpdateUsersPresence('ATTENDING');">{{lang.like_to_go}}</a>
                                    </div>
                                </div>
                                <!--                            <div ng-if="user_status == 'ATTENDING' && loggedUserRole != '1' && loggedUserRole != '2' && EventDetail.IsDeleted != 2">
                                                                <a class="text-primary" target="_blank" ng-href="{{EventDetail.EventURL}}">Reserve Spot</a>
                                                            </div>-->
                                <div class="alert alert-danger alert-primary" ng-cloak ng-if="user_status == 'DECLINED' && loggedUserRole != '1' && loggedUserRole != '2' && EventDetail.IsDeleted != 2" ng-cloak>
                                    <i class="ficon-sad icn"></i> {{lang.declined_invitation}}
                                </div>
                            </div>
                            <div class="event-actions" ng-if="EventDetail.IsPast=='1'" ng-cloak>
                                <a class="btn btn-default btn-lg btn-block disabled">
                                    {{lang.completed}}
                                </a>
                            </div>
                        </div>
                        <div class="banner-body visible-xs">
                            <p ng-cloak ng-bind="EventDetail.Summary"></p>
                            <span ng-cloak class="postedby">Posted by: <a target="_self" ng-href="{{baseUrl + EventDetail.CreatedBy.ProfileURL}}" ng-bind="EventDetail.CreatedBy.Name"></a></span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!--  secondary-nav -->
    <div class="secondary-scrollFix" data-nav="fixed" ng-cloak data-scrollfix="scrollFix">
    <!--    <div ng-click="save_cover_image_state(); EventDetail.CoverImageState = '1'" ng-cloak ng-show="EventDetail.CoverImageState == '2' && LoginSessionKey !== ''" class="banner-button" data-banner="show"><i class="ficon-arrow-up"></i></div>-->
        <div class="container container-primary">
            <div class="secondary-nav">
                <div class="row nav-row">
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

                    <div class="col-xs-10 col-md-9 col-sm-8">
                        <aside class="pulled-nav unpulled-nav tabs-menus mob-active-none">
                            <ul class="nav nav-tabs nav-tabs-liner primary nav-tabs-scroll" role="tablist">
                                <li class="<?php
                                if ($sub_pname == 'about') {
                                    echo 'active';
                                }
                                ?>">
                                    <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('about', $ModuleEntityGUID, $EventTitleUrl); ?>">Overview</a>
                                </li>
                                <li class="<?php
                                if ($sub_pname == 'wall') {
                                    echo 'active';
                                }
                                ?>">
                                    <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('wall', $ModuleEntityGUID, $EventTitleUrl); ?>">Discussions</a>
                                </li>
                                <li class="<?php
                                if ($sub_pname == 'members') {
                                    echo 'active';
                                }
                                ?>">
                                    <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('members', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('attendes'); ?></a>
                                </li>
                                <li ng-if="EventDetail.TotalMediaCount > 0" class="<?php
                                if ($sub_pname == 'media') {
                                    echo 'active';
                                }
                                ?>">
                                    <a target="_self" href="<?php echo $this->event_model->getEventTitleUrl('media', $ModuleEntityGUID, $EventTitleUrl); ?>"><?php echo lang('media'); ?></a>
                                </li>
                            </ul>
                        </aside>
                    </div>
                    <div class="col-xs-2 col-md-3 col-sm-4">
                        <div class="nav-action-ctrl">
                            <div class="action-items">
                                <?php if ($sub_pname == 'wall') { ?>
                                    <a class="btn btn-default" ng-click="filterFixed = true" ng-cloak ng-show="config_detail.IsAdmin == '1'">
                                        <span class="icon">
                                            <i class="ficon-filter"></i>
                                        </span>
                                    </a>
                                <?php } ?>
                                <a class="btn btn-default" ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'" aria-expanded="true" data-toggle="dropdown">
                                    <span class="icon">
                                        <i class="ficon-settings"></i>
                                    </span>
                                </a>
                                <ul role="menu" class="dropdown-menu" ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'">
                                    <li ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'"><a href="" data-toggle="modal" ng-if="EventDetail.EventStatus !== 'Past'" data-ng-click="loadPopUp('edit_event', 'partials/event/edit_event.html');"><?php echo lang('edit'); ?></a></li>

                                    <li ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'"><a href="" ng-if="EventDetail.EventStatus == 'Past' || (EventDetail.EventStatus == 'Upcoming' && EventDetail.MemberCount == 1)" data-ng-click="DeleteEvent(1);" ng-bind="lang.delete"></a></li>
                                    <li ng-cloak ng-if="(loggedUserRole == '1' || loggedUserRole == '2') && EventDetail.IsDeleted == '0'"><a href="" ng-if="(EventDetail.EventStatus == 'Upcoming' && EventDetail.MemberCount > 1) || EventDetail.EventStatus == 'Running'" data-ng-click="DeleteEvent(2);" ng-bind="lang.cancel"></a></li>
                                </ul>
                                <a class="btn btn-primary btn-scrollfix uppercase" ng-cloak
                                   ng-show="user_status == 'INVITED' || ((user_status == '' || user_status == 'NOT_ATTENDING') && EventDetail.Privacy == 'PUBLIC') && loggedUserRole != '1' && loggedUserRole != '2' && EventDetail.IsDeleted != 2" 
                                   ng-bind="lang.attend_now" 
                                   ng-if="EventDetail.IsDeleted != 2 && EventDetail.IsPast!='1'"
                                   data-ng-click="UpdateUsersPresence('ATTENDING');
                                       EventDetail.CanPostOnWall = '1'">
                                </a>
                                <!--                            <a class="btn btn-default btn-scrollfix" target="_blank" ng-href="{{EventDetail.EventURL}}" ng-if="user_status == 'ATTENDING' && loggedUserRole != '1' && loggedUserRole != '2' && EventDetail.IsDeleted != 2">
                                                                Reserve Spot
                                                            </a>-->
                            </div>
                        </div>
                    </div>               
                </div>
            </div>
        </div>
    </div>
</div>
<!--//Banner-->

<input type="hidden" name="posted_by" id="postedby" value="Anyone">
<input type="hidden" name="page_url" id="page_url" ng-value="config_detail.page_name">
<input type="hidden" name="cover_image_state" ng-value="config_detail.ConverImageState" id="cover_image_state">
<input type="hidden" name="LandingPage" id="LandingPage" value="<?php
if (isset($LandingPage)) {
    echo $LandingPage;
}
?>" />
<input type="hidden" name="eventWallUrl" id="eventWallUrl" value="<?php echo $this->event_model->getEventTitleUrl('wall', $ModuleEntityGUID, $EventTitleUrl); ?>">
<!-- // secondary-nav -->

