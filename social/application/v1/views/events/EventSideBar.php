
<aside class="col-sm-4 col-xs-12 sidebar pull-right fadeInDown" ng-class="{'col-md-3':IsSingleActivity == true, 'col-md-4':IsSingleActivity == false}"  ng-cloak>
  <div class="panel panel-default"  ng-cloak ng-if="!(IsSingleActivity)">
    <div class="panel-body no-space">
      <ul class="event-detail-listing">
        <li>
            <i class="icon-datepkr">&nbsp;</i>
            <span>Start Date</span>
            <ul class="list-sub-nav">
               <li ng-cloak ng-if="EventDetail" ng-bind="getEventDate(EventDetail.StartDate,EventDetail.StartTime)"></li> 
               <li ng-cloak ng-if="EventDetail" ng-bind="getEventTime(EventDetail.StartDate,EventDetail.StartTime)"></li> 
             </ul>
        </li>
        <li>
            <i class="icon-datepkr">&nbsp;</i>
            <span>End Date</span>
            <ul class="list-sub-nav">
               <li ng-cloak ng-if="EventDetail" ng-bind="getEventDate(EventDetail.EndDate,EventDetail.EndTime)"></li> 
               <li ng-cloak ng-if="EventDetail" ng-bind="getEventTime(EventDetail.EndDate,EventDetail.EndTime)"></li> 
             </ul> 
        </li>
        <li>
          <i class="icon-venue">&nbsp;</i>
          <span>Venue</span>
          <ul class="list-sub-nav">
             <li class="capitilize-text" ng-if="EventDetail.Venue" ng-bind="EventDetail.Venue"></li> 
           </ul>
        </li>
        <li>
            <i class="icon-location-e">&nbsp;</i>
            <span>Location</span>
            <ul class="list-sub-nav">
               <li class="capitilize-text" ng-if="EventDetail.Location.FormattedAddress" ng-bind="EventDetail.Location.FormattedAddress"></li> 
             </ul>
        </li>
        <li ng-if="EventDetail.EventURL">
            <i class="icon-url-e">&nbsp;</i>
            <span>Event URL</span>
            <ul class="list-sub-nav">
               <li><a target="_blank" ng-bind="EventDetail.EventURL" ng-href="{{getEventHref(EventDetail.EventURL)}}"></a></li> 
             </ul>
        </li>
      </ul>
    </div>
    <div class="event  panel-footer">
      <ul class="guest-listing">
        <li>
            <h4 ng-bind="EventDetail.Presence.ATTENDING"></h4>
            <p><?php echo lang('going');?></p>
        </li>
        <li>  
             <h4 ng-bind="EventDetail.Presence.MAY_BE"></h4>
            <p><?php echo lang('maybe');?></p>
         </li>
         <li>   
             <h4 ng-bind="EventDetail.Presence.INVITED"></h4>
            <p><?php echo lang('invited');?></p>
         </li>
       </ul>
      <div class="clearfix"></div>
    </div>
  </div>
  <div class="panel panel-default"  ng-cloak ng-if="!(IsSingleActivity)">
    <div class="panel-heading p-heading">
      <h3 ><?php echo lang('about');?></h3>
    </div>
    <?php $this->load->view('events/InviteUsers');?> 
    <!--<div class="panel-footer">
          <a href="javascript:void(0);" class="view-more">View All</a>
        </div>--> 
  </div>
    <?php
    if (!$this->settings_model->isDisabled(42) && ((( isset($pname) && in_array($pname, ['wall', 'files', 'links'])) || ( isset($IsNewsFeed) && $IsNewsFeed == 1 )) && $ActivityGUID=='')) {
        $this->load->view('widgets/sticky-post');
    }
    if(!empty($ActivityGUID))
    {
        $this->load->view('widgets/similar-discussions');
    }
    ?>
</aside>
<!-- Create Group Modal --> 
