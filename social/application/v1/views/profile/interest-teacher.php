<form id="sideform" >
<div class="useprofiletag-list">
<div class="qusAnsEditableRegion p-t-10">
<div class="que" ng-show="teachs.length > 0">I CAN TEACH</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="teachs.length > 0">
<ul class="tagedit-list" style="border: medium none;">
<li class="tagedit-listelement tagedit-listelement-old" ng-repeat="list in teachs" ng-bind="list"></li>
</ul>
</div>
<div class="editableAnsRegion" id="teach">
<input type="text" name="teach[]" ng-value="list" ng-repeat="list in teachs" class="form-control input-lg tagteach" >
</div>
</div>
</div>
<div class="qusAnsEditableRegion userprofile">
<div class="que" ng-show="tmpwebsite != null">MY WEBSITE</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="tmpwebsite != null"><span ng-bind="webSites"></span></div>
<div class="editableAnsRegion">
    <div class="que">MY WEBSITE</div>
<input type="text" name="website" data-ng-model="WebSitesInText" class="form-control input-lg tag" value="{{WebSitesInText}}">
</div>
</div>
</div>
<div class="qusAnsEditableRegion">
<div class="que" ng-show="offers.length > 0">I OFFER</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="offers.length > 0">
<ul class="tagedit-list" style="border: medium none;">
<li class="tagedit-listelement tagedit-listelement-old" ng-repeat="list in offers" ng-bind="list"></li>
</ul>
</div>
<div class="editableAnsRegion" id="offer">
<input type="text" name="offer[]"  ng-repeat = "n in offers" ng-value="n" class="form-control input-lg tagoffer"  />
</div>
</div>
</div>
<div class="qusAnsEditableRegion">
<div class="que" ng-show="look.length > 0">I AM LOOKING IN VCOMMONSOCIAL FOR</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="look.length > 0">
<ul class="tagedit-list" style="border: medium none;">
<li class="tagedit-listelement tagedit-listelement-old" ng-repeat="list in look" ng-bind="list"></li>
</ul>
</div>
<div class="editableAnsRegion" id="lookings">
<input type="hidden" value="" />
<input type="text" name="looking[]" ng-value="n" ng-repeat="n in look" class="form-control input-lg taglooking">
</div>
</div>

</div>
<div class="grid-title  no-border p-t-10 footer border-bottom-radius3">
<button type="button" class="btn btn-success pull-right btn-sm btn-small SavaDetail" ng-click="SaveInterestteacher()" >Save</button>
<div class="clearfix"></div>
</div>
</div>
</form>