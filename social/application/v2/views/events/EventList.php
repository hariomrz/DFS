<!--Container-->
<div class="static-banner banner-lg parallax-wrapper">
    <div class="parallax-layer" style="background-image:url('{{AssetBaseUrl}}img/event-banner.png');"></div>
    <div class="container parallax-content-layer">
      <div class="static-content">
        <div class="content">
          <h1 class="title-style"><?php echo lang('header_image_text_1'); ?></h1>
          <div class="lead">
            <p><?php echo lang('header_image_text_2'); ?></p>
          </div>
        </div>
      </div>
    </div>
</div>
<div data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" ng-init="CallApis(1, 1, 1);" infinite-scroll="CallApis(1,0,0)"  infinite-scroll-use-document-bottom="true" infinite-scroll-disabled="busy">
    <div class="container wrapper relative">
        <a ng-cloak class="btn-create" ng-if="!LoginSessionKey" onclick="showConfirmBoxLogin('Login Required', 'Please login to perform this action.', function(){ return false; });">
            <img ng-src="{{AssetBaseUrl}}img/ic-create-event.png" alt="">
        </a>
        <a ng-cloak class="btn-create" ng-if="LoginSessionKey" ng-click="getEventCategories(''); loadPopUp('create_event', 'partials/event/create_event.html');">
            <img ng-src="{{AssetBaseUrl}}img/ic-create-event.png" alt="">
        </a>
        <div class="content-heading">
          <div class="row">
            <div class="col-sm-12">
              <div class="heading-wid-subhead">
                  <h3 class="content-title"><?php echo lang('h3_title');?></h3>
                <!-- <ul class="list-activites list-icons-disc">
                  <li><span>Discover</span></li>
                  <li><span>Experience</span></li>
                  <li><span>Inspire</span></li>
                </ul> -->
              </div>
            </div>
          </div>
        </div>
        <!-- filters -->
        <div class="secondary-nav transparent" data-scrollfix="scrollFix">
          <div class="main-filter-nav">
            <nav class="navbar navbar-default navbar-static">
                <div class="xcollapse xnavbar-collapse" id="xfilterNav">
                    <ul class="nav xnavbar-nav filter-nav filter-nav-primary  justifiedz">
                        <li class="dropdown xselected" ng-init="listing_display_type = '<?php echo lang('all_events'); ?>'; sortbyname = '<?php echo lang('activity_date'); ?>'">              
                            <a data-toggle="dropdown" role="button" ng-cloak>{{listing_display_type}} <i class="ficon-arrow-down"></i></a>
                            <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">
                                <?php
                                if (!$this->session->userdata('LoginSessionKey')) {
                                    ?>
                                    <li ng-repeat="event in [{Name:'<?php echo lang('all_events'); ?>', Key:'AllPublicEvents'}]" ng-cloak>
                                    <?php
                                } else {
                                    ?>
                                    <li ng-repeat="event in [{Name:'<?php echo lang('all_events'); ?>', Key:'AllPublicEvents'},
                           {Name:'<?php echo lang('all_my_events'); ?>', Key:'AllMyEvents'},
                               {Name:'<?php echo lang('events_i_created'); ?>', Key:'EventICreated'}, {Name:'<?php echo lang('events_i_joined'); ?>', Key:'EventIJoined'}, {Name:'<?php echo lang('events_i_invited'); ?>', Key:'EventIInvited'}, {Name:'<?php echo lang('Suggested'); ?>', Key:'Suggested'}, {Name:'<?php echo lang('my_past_event'); ?>', Key:'MyPastEvent'}]" ng-cloak ng-click="setEventType(event.Key, event.Name); closeDD();"> 
                                        <?php
                                    }
                                    ?>    
                                    <div class="radio">
                                        <input ng-checked="listing_display_type == event.Name" id="{{myEvents + key}}" type="radio" name="event.Key" value="{{event.Key}}">
                                        <label for="{{myEvents + key}}">{{event.Name}}</label>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown all_dd" ng-init="listing_display_location = '<?php echo $location['City']; ?>'" ng-cloak>
                            <a data-toggle="dropdown" role="button"> {{listing_display_location}} <i class="ficon-arrow-down"></i></a>
                            <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox" value="" ng-checked="filters.CityID.length == 0" ng-click="setLocationForEvent('<?php echo lang('all_location'); ?>', ''); closeDD();">
                                        <span class="label"><?php echo lang('all_location'); ?></span>
                                    </label>
                                </li>

                                <li ng-repeat ="location in EventLocation| orderBy:'-IsSeleted' ">
                                    <label class="checkbox">
                                        <input type="checkbox" value="{{location.CityID}}" ng-checked="location.IsSeleted == '1'" ng-click="setLocationForEvent(location.CityName, location.CityID); closeDD();">
                                        <span class="label">{{location.CityName}}</span>
                                    </label>
                                </li>
                            </ul>
                        </li>
                        <!-- <li ng-if="IsSetFilter == '1'" ng-cloak>
                            <div class="reset-button" ng-click="ResetEventFilter()">
                                <button class="btn btn-default"><?php //echo lang('reset'); ?></button> 
                            </div>
                        </li> -->
                        <li class="sort-block" ng-if="ShowSortOption == 1">
                            <ul class="sort-action">
                              <li>
                                <div class="dropdown-sort">
                                    <small class="title"><?php echo lang('sort_by'); ?></small>
                                    <div class="dropdown">
                                        <a data-toggle="dropdown">
                                            <span class="text" ng-if="filters.OrderBy == 'LastActivity'"><?php echo lang('activity_date'); ?></span>
                                            <span class="text" ng-if="filters.OrderBy == 'StartDate'">Start Date</span>
                                            <span class="text" ng-if="filters.OrderBy != 'LastActivity' && filters.OrderBy !='StartDate'" ng-bind="filters.OrderBy"></span>
                                            <span class="icon"><i class="ficon-arrow-down"></i></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a ng-click="SearchEvent('StartDate'); SortBy = 'StartDate'">Start Date</a></li>
                                            <li><a ng-click="SearchEvent('Title'); SortBy = 'Name'"><?php echo lang('title'); ?></a></li>
                                            <li><a ng-click="SearchEvent('LastActivity'); SortBy = 'Activity Date'"><?php echo lang('activity_date'); ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                              </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
          </div>
        </div>
        <div class="row">
            <div ng-class="(totalCreated==1)?'col-sm-12':'col-sm-6'" ng-if="totalCreated>0 && totalCreated<3" ng-repeat="Event in EventsObj = (listData| orderBy:orderByField:reverseSort)" ng-cloak>
                <div class="cards cards-event cards-event-secondary" ng-class="(totalCreated==1)?'only-one':'only-two'">
                    <div class="img-content" ng-if="totalCreated==1">
                      <a class="cards-image" href="<?php echo base_url(); ?>{{Event.EventUrl}}">
                        <img ng-src="{{ImageServerPath+'upload/profilebanner/220x220/event_banner.jpg'}}"  class="img-effect" ng-if="Event.ProfilePicture == 'event-placeholder.png'">
                        <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+Event.ProfilePicture}}"  class="img-effect" ng-if="Event.ProfilePicture!='event-placeholder.png'">
                        <div class="cards-labels">
                            <span class="date-label date-label-md reminder-set" ng-bind="Event.EventDay"></span>
                        </div>
                        <div class="cards-image-desc">
                          <div class="date-block">
                            <h5 ng-bind="Event.EventStartDate"></h5>
                            <span ng-bind="Event.EventStartMonth"></span>
                          </div>
                          <span class="invite-status" ng-if="Event.loggedUserPresence!='NOT_ATTENDING'" ng-bind="Event.loggedUserPresence"></span>  
                        </div>
                      </a>
                    </div>
                    <a ng-if="totalCreated==2" class="cards-image" href="<?php echo base_url(); ?>{{Event.EventUrl}}">
                        <img ng-src="{{ImageServerPath+'upload/profilebanner/220x220/event_banner.jpg'}}"  class="img-effect" ng-if="Event.ProfilePicture == 'event-placeholder.png'">
                        <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+Event.ProfilePicture}}"  class="img-effect" ng-if="Event.ProfilePicture!='event-placeholder.png'">
                        <div class="cards-labels">
                            <span class="date-label date-label-md reminder-set" ng-bind="Event.EventDay"></span>
                        </div>
                        <div class="cards-image-desc">
                          <div class="date-block">
                            <h5 ng-bind="Event.EventStartDate"></h5>
                            <span ng-bind="Event.EventStartMonth"></span>
                          </div>
                          <span class="invite-status" ng-if="Event.loggedUserPresence!='NOT_ATTENDING'" ng-bind="Event.loggedUserPresence"></span>  
                        </div>
                    </a>
                    <div class="cards-content">
                      <span class="crad-category" ng-bind="Event.CategoryName"></span>
                      <h2 class="cards-title">
                          <a ng-if="totalCreated!=1" href="<?php echo base_url(); ?>{{Event.EventUrl}}" ng-bind="Event.ShortTitle"></a>
                          <a ng-if="totalCreated==1" href="<?php echo base_url(); ?>{{Event.EventUrl}}" ng-bind="Event.Title"></a>
                      </h2>
                      <div class="event-location" ng-bind="Event.Location.CityStateCountry"></div>
                      <p class="desc-card" ng-bind-html="Event.Summary" ng-if="Event.Summary && totalCreated==1"></p>
                      <p class="desc-card" ng-bind-html="Event.Description" ng-if="Event.Description && totalCreated==1"></p>
                        <div class="hosted-by clearfix">
                          <div class="pull-left">
                                <figure>
                                    <img ng-if="Event.CreatedByProfilePicture" ng-src="{{ImageServerPath+'upload/profile/220x220/'+Event.CreatedByProfilePicture}}">
                                    <span ng-if="!Event.CreatedByProfilePicture" class="default-thumb">
                                        <span ng-bind="getDefaultImgPlaceholder(Event.CreatedBy)"></span>
                                    </span>
                                </figure>
                                <p>Hosted by <a target="_self" ng-href="{{Event.CreatedByURL}}" ng-bind="Event.CreatedBy"></a></p>
                          </div>
                          <div class="pull-right">
                              <div class="member-list-block">
                              <ul class="member-list">
                                <li class="member-item" ng-show="Event.EventUsers.length > 0">
                                    <a class="thumb-item" href="<?php echo site_url() ?>{{user.ProfileLink}}" ng-repeat="user in Event.EventUsers| limitTo:DisplayUserCount">
                                        <img ng-if="user.ProfilePicture != ''" ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}" err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg"/>
                                        <img ng-if="user.ProfilePicture == ''" ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}" err-name="{{user.FullName}}"/>
                                    </a>  
                                </li>
                                <li class="moreListing" ng-show="Event.MemberCount > DisplayUserCount" ng-show="Event.EventUsers.length > 0">
                                        <a href="#">+{{Event.MemberCount - DisplayUserCount}}</a>
                                </li>
                                <li ng-show="Event.EventUsers.length == 0"></li>
                              </ul>
                              </div>
                          </div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3" ng-if="totalCreated>=3" ng-repeat="Event in EventsObj = (listData| orderBy:orderByField:reverseSort)" ng-cloak>
                <div class="cards cards-event cards-event-secondary">
                    <a class="cards-image" href="<?php echo base_url(); ?>{{Event.EventUrl}}">
                        <img ng-src="{{ImageServerPath+'upload/profilebanner/220x220/event_banner.jpg'}}"  class="img-effect" ng-if="Event.ProfilePicture == 'event-placeholder.png'">
                        <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+Event.ProfilePicture}}"  class="img-effect" ng-if="Event.ProfilePicture!='event-placeholder.png'">
                        <div class="cards-labels">
                            <span class="date-label date-label-md reminder-set" ng-bind="Event.EventDay"></span>
                        </div>
                        <div class="cards-image-desc">
                          <div class="date-block">
                            <h5 ng-bind="Event.EventStartDate"></h5>
                            <span ng-bind="Event.EventStartMonth"></span>
                          </div>
                            <span class="invite-status" ng-if="Event.loggedUserPresence!='NOT_ATTENDING'" ng-bind="Event.loggedUserPresence"></span>
                        </div>
                    </a>
                    <div class="cards-content">
                      <span class="crad-category" ng-bind="Event.CategoryName"></span>
                      <h2 class="cards-title">
                          <a href="<?php echo base_url(); ?>{{Event.EventUrl}}" ng-bind="Event.ShortTitle"></a>
                      </h2>
                      <div class="event-location" ng-bind="Event.Location.CityStateCountry"></div>
                      
                      <div class="hosted-by clearfix">
                          <div class="pull-left">
                                <figure>
                                    <img ng-if="Event.CreatedByProfilePicture" ng-src="{{ImageServerPath+'upload/profile/220x220/'+Event.CreatedByProfilePicture}}">
                                    <span ng-if="!Event.CreatedByProfilePicture" class="default-thumb">
                                        <span ng-bind="getDefaultImgPlaceholder(Event.CreatedBy)"></span>
                                    </span>
                                </figure>
                                <p>Hosted by <a target="_self" ng-href="{{Event.CreatedByURL}}" ng-bind="Event.CreatedBy"></a></p>
                          </div>
                          <div class="pull-right">
                              <div class="member-list-block">
                              <ul class="member-list">
                                  <li class="member-item" ng-show="Event.EventUsers.length > 0">
                                    <a class="thumb-item" href="<?php echo site_url() ?>{{user.ProfileLink}}" ng-repeat="user in Event.EventUsers| limitTo:DisplayUserCount">
                                        <img ng-if="user.ProfilePicture != ''" ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}" err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg"/>
                                        <img ng-if="user.ProfilePicture == ''" ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}" err-name="{{user.FullName}}"/>
                                    </a>
                                </li>
                                <li class="moreListing" ng-show="Event.MemberCount > DisplayUserCount" ng-show="Event.EventUsers.length > 0">
                                    <a href="#">+{{Event.MemberCount - DisplayUserCount}}</a>
                                </li>
                                <li ng-show="Event.EventUsers.length == 0"></li>
                              </ul>
                              </div>
                          </div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-8 col-lg-12" ng-show="isLoading">
                <span ng-if="isLoading" class="loader text-lg" style="display:block;">&nbsp;</span>
            </div>  
            <div class="col-sm-6 col-md-8 col-lg-12 hide" ng-class="ShowSortOption == '1'?'hide':''" ng-show="totalCreated == 0 && !isLoading">
                <div class="panel panel-info" ng-if="listing_display_type == 'All Events' && !isSearchFilterApplied()">         
                    <div class="panel-body nodata-panel">
                        <div class="nodata-text p-v-elg">
                            <span class="nodata-media">
                                <img src="{{AssetBaseUrl}}img/empty-img/empty-no-event-created.png" >
                            </span>
                            <h5>{{lang.no_upcoming_event}}</h5>
                            <p class="text-off">{{lang.no_upcoming_event_text}}</p>
                        </div>
                    </div>
                </div>

                <div class="panel panel-info" ng-if="(listing_display_type == 'All My Events' || listing_display_type == 'Events I Created') && !isSearchFilterApplied()">   
                    <div class="panel-body nodata-panel">
                        <div class="nodata-text p-v-elg">
                            <span class="nodata-media">
                                <img src="{{AssetBaseUrl}}img/empty-img/empty-no-event-created.png" >
                            </span>
                            <h5>{{lang.no_event}}</h5>
                            <p class="text-off">{{lang.no_event_text}}</p>
                                <?php
                                if (!$this->session->userdata('LoginSessionKey')) {
                                ?>
                                <a ng-if="!LoginSessionKey" onclick="showConfirmBoxLogin('Login Required', 'Please login to perform this action.', function(){ return false; });"><?php echo lang('create_event_text'); ?></a>
                                    <?php
                                } else {
                                    ?>
                                <a ng-if="LoginSessionKey" ng-click="getEventCategories(''); loadPopUp('create_event', 'partials/event/create_event.html');"><?php echo lang('create_event_text'); ?></a>
                                <?php
                                }
                                ?>
                        </div>
                    </div>
                </div>

                <div class="panel panel-info" ng-if="listing_display_type == 'Events I Joined' && !isSearchFilterApplied()">         
                    <div class="panel-body nodata-panel">
                        <div class="nodata-text p-v-elg">
                            <span class="nodata-media">
                                <img src="{{AssetBaseUrl}}img/empty-img/empty-no-event-created.png" >
                            </span>
                            <h5><?php echo lang('omg'); ?></h5>
                            <p class="text-off"><?php echo lang('no_joined_event'); ?></p>
                            <a ng-click="reloadPage();"><?php echo lang('nearby_event_text'); ?></a>
                        </div>
                    </div>
                </div>

                <div class="panel panel-info" ng-if="listing_display_type == 'Events I Invited' && !isSearchFilterApplied()">         
                    <div class="panel-body nodata-panel">
                        <div class="nodata-text p-v-elg">
                            <span class="nodata-media">
                                <img src="{{AssetBaseUrl}}img/empty-img/empty-no-event-created.png" >
                            </span>
                            <h5><?php echo lang('no_invited_event'); ?></h5>
                            <p class="text-off"><?php echo lang('no_invited_event_text'); ?></p>
                            <a ng-click="reloadPage();"><?php echo lang('nearby_event_text'); ?></a>
                        </div>
                    </div>
                </div>

                <div class="panel panel-info" ng-if="(listing_display_type == 'All My Past Events' || listing_display_type == 'Suggested') && !isSearchFilterApplied()">         
                    <div class="panel-body nodata-panel">
                        <div class="nodata-text p-v-elg">
                            <span class="nodata-media">
                                <img src="{{AssetBaseUrl}}img/empty-img/empty-no-event-created.png" >
                            </span>
                            <h5><?php echo lang('omg'); ?></h5>
                            <p class="text-off"><?php echo lang('no_past_event'); ?></p>
                            <a ng-click="reloadPage();"><?php echo lang('nearby_event_text'); ?></a>
                        </div>
                    </div>
                </div>


                <div class="panel panel-info" ng-if="isSearchFilterApplied()">         
                    <div class="panel-body nodata-panel">
                        <div class="nodata-text p-v-elg">
                            <span class="nodata-media">
                                <img src="{{AssetBaseUrl}}img/empty-img/empty-no-search-results-found.png" >
                            </span>
                            <h5>No Results Found!</h5>

                            <a ng-click="reloadPage();"><?php echo lang('nearby_event_text'); ?></a>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 content-heading">
                <div class="heading-wid-subhead">
                  <h3 class="content-title">Memories so far</h3>
                  <!-- <ul class="list-activites list-icons-disc">
                    <li><span>Discover</span></li>
                    <li><span>Experience</span></li>
                    <li><span>Inspire</span></li>
                  </ul> -->
                </div>
            </div>
        </div>
    </div>
    <section class="memory-slider-container" ng-controller="PastEventController" ng-init="ListPastEvents();">
        <div class="container">
          <div class="row">
            <div class="col-sm-12">
                <div id="memorySlider" class="arrow-slicks-cust">
                    <slick class="slider" ng-if="totalPastEvents>0" settings="memorySlider">
                        <div class="row" ng-repeat="pastevent in listPastData">
                            <div class="col-md-5 col-md-offset-1 col-sm-6">
                                <ul class="multiview-grid" ng-class="(pastevent.MediaCount==1)?'img-one':(pastevent.MediaCount==2)?'img-two':(pastevent.MediaCount==3)?'img-three':(pastevent.MediaCount==4)?'img-four':(pastevent.MediaCount==5)?'img-five':'img-six'">
                                  <li ng-repeat="(k, m) in pastevent.MediaList">
                                    
                                    <a ng-href="{{pastevent.MediaURL}}" ng-if="m.MediaType == 'Image'" style="background-image: url('{{lang.image_server_path}}upload/wall/750x500/{{m.ImageName}}')"></a>
                                    <a ng-href="{{pastevent.MediaURL}}" ng-if="m.MediaType == 'Video' && m.ConversionStatus == 'Finished'" style="background-image: url('{{lang.image_server_path}}upload/wall/750x500/{{m.ImageName.substr(0, m.ImageName.lastIndexOf('.')) + '.jpg'}}')"></a>
                                    <a ng-href="{{pastevent.MediaURL}}" ng-if="m.MediaType == 'Video' && m.ConversionStatus != 'Finished'" class="process-vid processing-red">
                                        <span class="video-btn">
                                            <i class="ficon-video"></i>
                                          </span>                                       
                                    </a>
                                   
                                    <!-- <figure class="event-slide-img" ng-if="m.MediaType == 'Image'" ng-class="{'media-thumb-actions': (m.MediaType != 'Image')}">
                                        <img ng-src="{{lang.image_server_path}}upload/wall/220x220/{{m.ImageName}}">
                                    </figure>
                                    <figure class="event-slide-img" ng-if="m.MediaType == 'Video'" ng-class="(m.MediaType == 'Video' && m.ConversionStatus == 'Pending') ? 'processing-skyblue' : ''">
                                        <img ng-if="m.ConversionStatus == 'Finished'" ng-src="{{lang.image_server_path}}upload/wall/220x220/{{m.ImageName.substr(0, m.ImageName.lastIndexOf('.')) + '.jpg'}}">
                                        <span class="media-ctrl" >
                                            <i class="ficon-play"></i>
                                        </span>
                                    </figure> -->
                                </li>
                                
                                
                              </ul>
                            </div>
                            <div class="col-md-5 col-sm-6">
                              <div class="memory-description">
                                    <span class="dat-evnt" ng-cloak>{{pastevent.StartDay}} - {{pastevent.EndDay}} {{pastevent.EndMonth}}</span>
                                    <h4 class="name-evnt" ng-cloak><a ng-href="{{baseUrl + pastevent.ProfileURL}}" ng-bind="pastevent.Title"></a></h4>
                                    <span class="place-evnt" ng-cloak>
                                        <span ng-repeat="l in pastevent.Locations">
                                          <span ng-if="$index==1"> - </span>
                                          <span ng-bind="l.City"></span>
                                          <span ng-if="!$first && !$last"> - </span>
                                        </span>
                                    </span>
                                    <p class="desc-evnt" ng-cloak ng-if="pastevent.Summary" ng-bind="pastevent.Summary"></p>
                                    <p class="desc-evnt" ng-cloak ng-if="!pastevent.Summary" ng-bind="pastevent.Description"></p>
                                    <a class="btn btn-lg btn-default-outline white" ng-href="{{pastevent.MediaURL}}">View Gallery</a>
                              </div>
                            </div>
                        </div>
                    </slick>
                </div>
            </div>
          </div>
        </div>
    </section>
    <section class="bg-with-video" ng-cloak ng-if="!LoginSessionKey">
    <div class="container bg-white">
      <div class="row">
        <div class="col-sm-6 col-sm-push-6">
          <div class="vid-view">
            <iframe width="555" height="400" src="https://www.youtube.com/embed/_5T1PdY5XlA"></iframe>
          </div>
        </div>
        <div class="col-sm-6 col-sm-pull-6">
          <div class="vid-txt">
            <span>Be a part of community</span>
            <h4>The Road Trippers Club</h4>
            <p>Road trippers club is a club with the ideas of encouraging people for short road trips. Strangers can come together, pool for a car and divide expenses and do a short road trip in India.</p>
            <button class="btn btn-primary btn-lg" ng-click="loginRequired();">Join Community</button>
          </div>
        </div>
      </div>
    </div>
  </section>
    <!-- add event popup start here-->
    <div ng-include="create_event" id="create_event"></div>
    <input type="hidden" id="hdngrpid" value="" />
    <input type="hidden" id="hdnmoduleid" value="" />
    <!-- add event popup end here-->   
</div>
<!--//Container-->
<input type="hidden" id='OrderBy' value="">
<input type="hidden" id="hdnQuery" value="">
<input type="hidden" id="pageType" value="<?php echo $this->session->userdata('CurrentSection'); ?>">
<input type="hidden" id="searchgrp" value="">
<input type="hidden" id="hdncrdtype" value="">
<input type="hidden" id="GroupListPageNo" value="1" />
<input type="hidden" id="unique_id" value="" />
<input type="hidden" id="lat" value="<?php echo $location['Lat']; ?>">
<input type="hidden" id="long" value="<?php echo $location['Lng']; ?>">
<input type="hidden" id="city" value="<?php echo $location['City']; ?>">
<input type="hidden" id="UserGUID" value="<?php echo get_guid_by_id($this->session->userdata('UserID'), 3); ?>" />