<div data-ng-controller="PageCtrl" ng-init="initialize()" ng-cloak>
  <div ng-init="GetPageDetails();GetPageFollower()"> 
    <!--Header-->
    <?php $this->load->view('profile/profile_banner'); ?>
    <!--//Header--> 
    <!--Container-->
    <div class="container wrapper" data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl">
	    <div class="row">
	      	<div class="col-md-8 col-lg-9">
	      		<div data-ng-controller="ModuleEventController" ng-if="PageID" ng-include="module_event_list" ng-init="loadPopUp('module_event_list','partials/event/page_event_list.html');"></div>
	      	</div>

	      	<div class="col-md-4 col-lg-3 sidebar" data-scroll="fixed">
		        <div class="cards cards-no-data cards-create-event" ng-if="pageDetails.IsUserEmailVerified == 2 && pageDetails.IsVerified == 1 && (pageDetails.IsAdmin || pageDetails.IsOwner)">
		          	<div class="cards-no-data-img">
			            <div class="twinkling-stars">
			              <img ng-src="{{AssetBaseUrl}}img/event/create-event.png" >
			            </div>
		          	</div>
		          	<p class="create-info" ng-bind="lang.create_info"></p>
		          	
                                <a ng-if="!(LoginSessionKey)" class="btn btn-block btn-lg btn-light" onclick="showConfirmBoxLogin('Login Required', 'Please login to perform this action.',function(){ return false;});"><span class="sml" ng-bind="::lang.create_event"></span></a>
	              	
                		<a ng-if="LoginSessionKey && PageID" class="btn btn-block btn-lg btn-light" ng-click="getEventCategories('');loadPopUp('create_event','partials/event/create_event.html');"><span class="sml" ng-bind="::lang.create_event" ></span></a>
	              	
		        </div>

                    <div data-ng-controller="SuggestedEventController" ng-if="PageID" ng-include="suggest_event_list" ng-init="loadPopUp('suggest_event_list','partials/event/suggest_event_list.html');" ></div>
	      	</div>
	    </div>
	    <!-- add event popup start here-->
	    <div ng-include="create_event"  id="create_event"></div>
	    <input type="hidden" id="hdngrpid" value="{{PageID}}" />
	    <input type="hidden" id="hdnmoduleid" value="18" />
	    <!-- add event popup end here-->
	</div>

    <!--//Container--> 
  </div>
</div>
<input type="hidden" name="Visibility" id="visible_for" value="1" />
<input type="hidden" name="Commentable" id="comments_settings" value="1" />
<input type="hidden" name="DeviceType" id="DeviceType" value="Native" />




