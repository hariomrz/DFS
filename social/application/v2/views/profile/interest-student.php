<form id="siform" >
<div class="qusAnsEditableRegion p-t-10">
<div class="que" ng-show="topic.length > 0">Topics</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="topic.length > 0">
<ul class="tagedit-list" style="border: medium none;">
<li class="tagedit-listelement tagedit-listelement-old" ng-repeat="list in topic" ng-bind="list"></li>
</ul>
</div>
<div class="editableAnsRegion" id="topics">
<input type="text" name="topic[]" ng-value="list" ng-repeat="list in topic" class="form-control input-lg tagtopic" >
</div>
</div>
</div>


<div class="qusAnsEditableRegion">
<div class="que" ng-show="tmplevel != null">Level</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="tmplevel != null"><span ng-bind="level"></span></div>
<div class="editableAnsRegion" ng-init='getevels();'>
    <div class="que">Level</div>
<select class="form-control" data-chosen="" data-disable-search="false" name="level" id="idlevel" data-ng-model="level" data-ng-options="singlelevel.LevelName for singlelevel in levellist track by singlelevel.LevelID"></select>
</div>
</div>
</div>  

<div class="qusAnsEditableRegion">
<div class="que" ng-show="practices.length > 0">I Practice</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="practices.length > 0">
<ul class="tagedit-list" style="border: medium none;">
<li class="tagedit-listelement tagedit-listelement-old" ng-repeat="list in practices" ng-bind="list" ></li>
</ul>
</div>
<div class="editableAnsRegion" id="practice" >
    
<input type="text" name="practice[]" ng-value="n" ng-repeat="n in practices" class="form-control input-lg tagpractice">
</div>
</div>
</div>
<div class="qusAnsEditableRegion" style="border: medium none;">
<div class="que" ng-show="look.length > 0">I AM LOOKING IN VCOMMONSOCIAL FOR</div>
<div class="ans-region">
<div class="ans AnsRegion" ng-show="look.length > 0">
<ul class="tagedit-list" style="border: medium none;">
<li class="tagedit-listelement tagedit-listelement-old" ng-repeat="list in look" ng-bind="list"></li>
</ul>
</div>
<div class="editableAnsRegion" id="looks" >
<input type="text" name="luk[]" ng-value="n" ng-repeat="n in look" class="form-control input-lg taglooks">
</div>
</div>

</div>
<div class="grid-title  no-border p-t-10 footer border-bottom-radius3">
<button type="button" class="btn btn-success pull-right btn-sm btn-small SavaDetail" ng-click="SaveIntereststudent()" >Save</button>
<div class="clearfix"></div>
</div>
</form>