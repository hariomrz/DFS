<div ng-controller="GroupMemberCtrl" id="GroupMemberCtrl" ng-init="GroupDetail();">
    <?php $this->load->view('profile/profile_banner') ?>
    <div class="container wrapper" data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl">
	    <div class="row">
	      	<div class="col-md-8 col-lg-9">
	      		<div data-ng-controller="ModuleEventController" ng-if="GroupGUID" ng-include="module_event_list" ng-init="loadPopUp('module_event_list','partials/event/group_event_list.html');"></div>
	      	</div>

	      	<div class="col-md-4 col-lg-3 sidebar" data-scroll="fixed">
                    <div ng-cloak class="cards cards-no-data cards-create-event" ng-if="GroupDetails.IsUserEmailVerified == 2 && (GroupDetails.IsAdmin || GroupDetails.IsCreator)">
		          	<div class="cards-no-data-img">
			            <div class="twinkling-stars">
			              <img src="{{AssetBaseUrl}}img/event/create-event.png" >
			            </div>
		          	</div>
		          	<p class="create-info" ng-bind="lang.create_info"></p>
		          	
                                <a target="_self" ng-if="!(LoginSessionKey)" class="btn btn-block btn-lg btn-light" onclick="showConfirmBoxLogin('Login Required', 'Please login to perform this action.',function(){ return false;});"><span class="sml" ng-bind="::lang.create_event"></span></a>
	              	
                		<a target="_self" ng-if="LoginSessionKey && GroupGUID" class="btn btn-block btn-lg btn-light" ng-click="getEventCategories('');loadPopUp('create_event','partials/event/create_event.html');"><span class="sml" ng-bind="::lang.create_event"></span></a>
	              	
		        </div>

		        <div ng-cloak ng-if="GroupGUID" data-ng-controller="SuggestedEventController" ng-include="suggest_event_list" ng-init="loadPopUp('suggest_event_list','partials/event/suggest_event_list.html');" ></div>
	      	</div>
	    </div>
	    <!-- add event popup start here-->
	    <div ng-include="create_event" id="create_event"></div>
	    <input type="hidden" id="hdngrpid" value="{{GroupDetails.GroupID}}" />
	    <input type="hidden" id="hdnmoduleid" value="1" />
	    <!-- add event popup end here-->
	</div>
	
	<input type="hidden" id="ModuleEntityGUID" value="{{GroupGUID}}" />
        
</div>

