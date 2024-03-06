<aside class="col-sm-7 col-md-7 col-xs-12 pull-left" ng-init="getEventSearchList(Keyword,10,1)">
    <section class="news-feed" ng-cloak>
        <div ng-if="EventTotalRecords>0" class="feed-title"><span ng-bind="(EventTotalRecords>0) ? EventTotalRecords : 0 ;"></span> <span ng-bind="(EventTotalRecords>1) ? 'results' : 'result' ;"></span> found</div>
        <div class="news-feed-listing">
            <div class="feed-body">
                <ul ng-if="EventTotalRecords>0" class="list-group thumb-68">
                    <li ng-repeat="Event in EventSearch" ng-cloak>
                        <figure>
                            <a entitytype="event" entityguid="{{Event.EventGUID}}" target="_self" ng-href="{{BaseUrl+Event.ProfileURL}}">
                                <img   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+Event.ProfilePicture}}">
                            </a>
                        </figure>
                        <div class="description">
                            <div class="btn-group btn-group-xs pull-right m-t-5">                                
                                <button aria-expanded="false"  
                                        class="btn btn-default dropdown-toggle" 
                                        type="button" ng-cloak 
                                        ng-if="((Event.loggedUserPresence == '' || Event.loggedUserPresence == 'NOT_ATTENDING' || Event.loggedUserPresence == 'DECLINED') && Event.Privacy == 'PUBLIC') &&  Event.ModuleRoleID!='1' && Event.ModuleRoleID!='2' && Event.EventStatus==0" 
                                        data-ng-click="UpdateUsersPresence('ATTENDING','Attending',Event.EventGUID,'search', Event);"> 
                                    <span class="text"  ><span ><?php echo lang('join'); ?></span></span> 
                                </button>

                                <button aria-expanded="false"  class="btn btn-default dropdown-toggle" type="button" ng-cloak ng-if="Event.loggedUserPresence && Event.ModuleRoleID == '1'"> <span class="text"  ><span ng-bind="Event.loggedUserPresence"></span></span> </button>             

                                <!-- <button aria-expanded="false"  class="btn btn-default dropdown-toggle"  type="button" ng-cloak ng-if="Event.loggedUserPresence && Event.ModuleRoleID != '1'" > <span class="text"  ><span ng-bind="Event.loggedUserPresence" > </span></span></button>

                                 <button aria-expanded="false" ng-cloak class="btn btn-default" type="button" ng-show=" Event.loggedUserPresence != 'Arrived' && Event.ModuleRoleID == '1' && EventDetail.ModuleRoleID !== 'Past'"> <span class="text" ng-bind="Event.loggedUserPresence"></span></button> -->

                                <button aria-expanded="false" ng-cloak class="btn btn-default dropdown-toggle" type="button" ng-show="Event.EventStatus==1 && Event.loggedUserPresence == 'ATTENDING' && Event.ModuleRoleID != '1'"> <span class="text" ng-bind="Event.loggedUserPresence"></span></button>
                                <button aria-expanded="false" data-toggle="dropdown" ng-cloak class="btn btn-default dropdown-toggle" type="button" ng-show="Event.EventStatus==0 && Event.loggedUserPresence == 'ATTENDING' && Event.ModuleRoleID != '1'"> <span class="text" ng-bind="Event.loggedUserPresence"></span><i class="caret" ></i></button>

                                <button aria-expanded="false" ng-cloak class="btn btn-default dropdown-toggle" type="button" ng-show="Event.EventStatus==1 && Event.loggedUserPresence == 'INVITED' && Event.ModuleRoleID != '1'"> <span class="text" ng-bind="Event.loggedUserPresence"></span></button>
                                <button aria-expanded="false" data-toggle="dropdown" ng-cloak class="btn btn-default dropdown-toggle" type="button" ng-show="Event.EventStatus==0 && Event.loggedUserPresence == 'INVITED' && Event.ModuleRoleID != '1'"> <span class="text" ng-bind="Event.loggedUserPresence"></span><i class="caret" ></i></button>

                                <ul role="menu" class="dropdown-menu" ng-cloak ng-if="Event.loggedUserPresence && Event.ModuleRoleID != '1'">
                                    <li><a href="javascript:void(0);" ng-if="Event.loggedUserPresence == 'INVITED'" data-ng-click="UpdateUsersPresence('DECLINED', 'Not Attending',Event.EventGUID,'search', Event);">Unable to Attend</a></li>
                                    <li><a href="javascript:void(0);" ng-if="Event.loggedUserPresence != 'Not Attending' && Event.loggedUserPresence != 'INVITED'" data-ng-click="UpdateUsersPresence('NOT_ATTENDING', 'Not Attending',Event.EventGUID,'search', Event);">Not Attending</a></li>
                                    <li><a href="javascript:void(0);" ng-if="Event.loggedUserPresence != 'Attending' || Event.loggedUserPresence == 'Invited'" data-ng-click="UpdateUsersPresence('ATTENDING', 'Attending',Event.EventGUID,'search', Event);">Attending</a></li>
                                </ul>
                                
                                
                                
                            </div>
                            <!-- Action button ends -->

                            <a class="name" entitytype="event" entityguid="{{Event.EventGUID}}" target="_self" ng-href="{{BaseUrl+Event.ProfileURL}}" class="name" ng-bind="Event.Title"></a>
                            <div class="time">Hosted by <span ng-bind="Event.CreatedBy+', '+UTCtoTimeZone(Event.StartDate+' '+Event.StartTime, 'dddd, MMM D YYYY hh:mm A')"></span></div>
                            <ul class="sub-nav-listing" ng-if="(Event.CityName!=='' && Event.CountryName!=='') || Event.FormattedAddress!==''" ng-cloak>
                                <li>
                                    <div class="location">
                                        <i class="icon">
                                            <svg width="16px" height="16px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnMapMarke'}}"></use>
                                            </svg>
                                        </i> 
                                        <span ng-if="Event.CityName==''" ng-bind="Event.FormattedAddress"></span>
                                        <span ng-if="Event.CityName!==''" ng-bind="Event.CityName+', '+Event.CountryName"></span>
                                    </div>
                                </li>
                            </ul>
                            <p class="m-t-5" ng-bind="Event.Description"></p>
                        </div>
                    </li>
                </ul>
                <div class="nodata-panel" ng-cloak ng-if="EventTotalRecords==0">
                    <div class="nodata-text">
                        <span class="nodata-media">
                            <img src="assets/img/empty-img/empty-no-search-results-found.png" >
                        </span>
                        <h5>No Results Found !</h5>
                        <p class="text-off">
                        Seems like there are no events matching your search criteria! <br>Change your search terms, or tweak your filters. 
                        </p>
                        <a ng-href="<?php echo site_url('events') ?>">Here's something for you to explore!</a>
                    </div>
                </div>                           
            </div>
        </div>
    </section>
</aside>