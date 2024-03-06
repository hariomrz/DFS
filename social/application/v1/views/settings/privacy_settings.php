<div class="container wrapper" ng-ini>
    <div class="row">
    <!-- Right Wall-->
<?php $this->load->view('settings/sidebar') ?>
<!-- //Right Wall-->
      <!-- Left Wall-->
      <aside class="col-md-8 col-sm-8 col-xs-12">
     
 <div class="panel panel-default fadeInDown" ng-controller="PrivacyCtrl" id="PrivacyCtrl" ng-init="getUserPrivacy()" ng-cloak>
    <div class="panel-heading p-heading">
      <h3 ng-bind="::lang.privacy"></h3>
    </div>
    <div id="customSetting" ng-if="IsReady=='1'" class="panel-body" ng-class="(Privacy=='customize') ? 'customize' : '' ;" ng-cloak>
        <div class="super-privacy-section">
            <h2 ng-cloak ng-bind="::lang.super_privacy"></h2>
            <p ng-cloak ng-bind="::lang.keep_it_simple"></p>
            <div class="row">
              <div class="col-md-8 col-sm-8">
                  <ul class="super-privacy-nav">
                      <li class="radio-btn" ng-class="(Privacy=='low') ? 'selected' : '' ;" ng-click="changePrivacy('low')" ng-cloak>{{::lang.low}}<input type="radio" value="Low" name="privacy"></li>
                      <li class="radio-btn" ng-class="(Privacy=='medium') ? 'selected' : '' ;" ng-click="changePrivacy('medium')" ng-cloak>{{::lang.medium}}<input type="radio" value="Medium" name="privacy"></li>
                      <li class="radio-btn" ng-class="(Privacy=='high') ? 'selected' : '' ;" ng-click="changePrivacy('high')" ng-cloak>{{::lang.high}}<input type="radio" value="High" name="privacy"></li>
                  </ul>
              </div>
              <div class="col-md-3 col-sm-4 col-md-offset-1 customize-block">
                  <div class="checkbox">
                    <input type="checkbox" ng-value="1" ng-model="customizeSetting" id="customizeSetting" ng-click="checkCustomSettings();">
                    <label for="customizeSetting" ng-cloak>{{::lang.customize}}</label>
                  </div>
              </div>
            </div>
        </div>
        <div class="setting-block">
           <h5 ng-cloak>{{::lang.profile_privacy}}</h5>
           <div class="row">
              <div class="col-sm-6 col-xs-5" ng-cloak>{{::lang.manage_info}}</div>
              <div class="col-sm-3 col-xs-3">
                 <label ng-if="ModlStg.m10 == 1" ng-cloak>{{::lang.friends}}</label>                      
              </div>
              <div class="col-sm-3 col-xs-4 text-right">
                <label ng-cloak>{{::lang.everyone}}</label>
              </div>
           </div>       
           <!-- Angular -->
           <div class="row" ng-repeat="l in Label" ng-if="l.Section=='profile'" repeat-done="superPrivacy();">
              <div class="col-sm-6" ng-bind="l.Name"></div>
              <div class="col-sm-6">
                  <div class="privacy-ability">
                      <div class="radio" ng-if="ModlStg.m10 == 1" ng-class="getSelectedClass('friend',Opt[l.Value])">
                          <input type="radio" ng-click="changeOptVal(l.Value,'friend',$event)" ng-model="Opt[l.Value]" value="friend" name="{{l.Value}}" id="{{l.Value}}-fr">
                          <label ng-cloak for="{{l.Value}}-fr">&nbsp;</label> 
                      </div>
                      
                      <div class="radio" ng-class="getSelectedClass('everyone',Opt[l.Value])">
                          <input type="radio" ng-click="changeOptVal(l.Value,'everyone',$event)" ng-model="Opt[l.Value]" value="everyone" name="{{l.Value}}" id="{{l.Value}}-Everyone">
                          <label for="{{l.Value}}-Everyone">&nbsp;</label> 
                      </div> 
                  </div>
                  <input style="display:none;" type="radio" ng-model="Opt[l.Value]" value="self" name="{{l.Value}}" id="{{l.Value}}-self">
              </div>
           </div>
           <!-- Angular -->

        </div>

        <div class="setting-block">
            <h5 ng-cloak>{{::lang.post_and_tag}}</h5>
            <div class="row" ng-repeat="l in Label" ng-if="l.Section=='post'" repeat-done="superPrivacy();">
              <div class="col-sm-6" ng-bind="l.Name"></div>
              <div class="col-sm-6">
                  <div class="privacy-ability">
                      <div class="radio" ng-class="getSelectedClass('friend',Opt[l.Value])" ng-if="ModlStg.m10 == 1">
                          <input type="radio" ng-click="changeOptVal(l.Value,'friend')" ng-model="Opt[l.Value]" value="friend" name="{{l.Value}}" id="{{l.Value}}-fr">
                          <label for="{{l.Value}}-fr">&nbsp;</label> 
                      </div>
                      
                      <div class="radio" ng-class="getSelectedClass('everyone',Opt[l.Value])">
                          <input type="radio" ng-click="changeOptVal(l.Value,'everyone')" ng-model="Opt[l.Value]" value="everyone" name="{{l.Value}}" id="{{l.Value}}-Everyone">
                          <label for="{{l.Value}}-Everyone">&nbsp;</label> 
                      </div> 
                  </div>
                  <input style="display:none;" type="radio" ng-model="Opt[l.Value]" value="self" name="{{l.Value}}" id="{{l.Value}}-self">
              </div>
           </div>

        </div>

        <div class="setting-block">
            <h5 ng-cloak>{{::lang.Endorsement}}</h5>
            <div class="row" ng-repeat="l in Label" ng-if="l.Section=='endorsement'" repeat-done="superPrivacy();">
              <div class="col-sm-6" ng-bind="l.Name"></div>
              <div class="col-sm-6">
                  <div class="privacy-ability">
                      <div class="radio" ng-class="getSelectedClass('friend',Opt[l.Value])" ng-if="ModlStg.m10 == 1">
                          <input type="radio" ng-click="changeOptVal(l.Value,'friend')" ng-model="Opt[l.Value]" value="friend" name="{{l.Value}}" id="{{l.Value}}-fr">
                          <label for="{{l.Value}}-fr">&nbsp;</label> 
                      </div>
                      
                      <div class="radio" ng-class="getSelectedClass('everyone',Opt[l.Value])">
                          <input type="radio" ng-click="changeOptVal(l.Value,'everyone')" ng-model="Opt[l.Value]" value="everyone" name="{{l.Value}}" id="{{l.Value}}-Everyone">
                          <label for="{{l.Value}}-Everyone">&nbsp;</label> 
                      </div> 
                  </div>
                  <input style="display:none;" type="radio" ng-model="Opt[l.Value]" value="self" name="{{l.Value}}" id="{{l.Value}}-self">
              </div>
           </div>

        </div>

        <div class="setting-block" ng-if="enabledSections.contact">
            <h5 ng-cloak>{{::lang.contact_and_invite}}</h5>
            <div class="row" ng-repeat="l in Label" ng-if="l.Section=='contact'" repeat-done="superPrivacy();">
              <div class="col-sm-6" ng-bind="l.Name"></div>
              <div class="col-sm-6">
                  <div class="privacy-ability" ng-class="(l.Value=='friend_request') ? 'frist-disabled' : '' ;">
                      <div class="radio" ng-class="getSelectedClass('friend',Opt[l.Value],1)" ng-if="ModlStg.m10 == 1">
                          <input ng-disabled="l.Value=='friend_request'" type="radio" ng-click="changeOptVal(l.Value,'friend')" ng-model="Opt[l.Value]" value="friend" name="{{l.Value}}" id="{{l.Value}}-fr">
                          <label for="{{l.Value}}-fr">&nbsp;</label> 
                      </div>
                      
                      <div class="radio" ng-class="getSelectedClass('everyone',Opt[l.Value])">
                          <input type="radio" ng-click="changeOptVal(l.Value,'everyone')" ng-model="Opt[l.Value]" value="everyone" name="{{l.Value}}" id="{{l.Value}}-Everyone">
                          <label for="{{l.Value}}-Everyone">&nbsp;</label> 
                      </div> 
                  </div>
                  <input style="display:none;" type="radio" ng-model="Opt[l.Value]" value="self" name="{{l.Value}}" id="{{l.Value}}-self">
              </div>
           </div>

        </div>

        <div class="setting-block">
          <h5 ng-cloak>{{::lang.search}}</h5>
          <div class="row" ng-repeat="l in Label" ng-if="l.Section=='search'" repeat-done="superPrivacy();">
              <div class="col-sm-6" ng-bind="l.Name"></div>
              <div class="col-sm-6">
                  <div class="privacy-ability">
                      <div class="radio" ng-class="getSelectedClass('friend',Opt[l.Value])" ng-if="ModlStg.m10 == 1">
                          <input type="radio" ng-click="changeOptVal(l.Value,'friend')" ng-model="Opt[l.Value]" value="friend" name="{{l.Value}}" id="{{l.Value}}-fr">
                          <label for="{{l.Value}}-fr">&nbsp;</label> 
                      </div>
                      
                      <div class="radio" ng-class="getSelectedClass('everyone',Opt[l.Value])">
                          <input type="radio" ng-click="changeOptVal(l.Value,'everyone')" ng-model="Opt[l.Value]" value="everyone" name="{{l.Value}}" id="{{l.Value}}-Everyone">
                          <label for="{{l.Value}}-Everyone">&nbsp;</label> 
                      </div> 
                  </div>
                  <input style="display:none;" type="radio" ng-model="Opt[l.Value]" value="self" name="{{l.Value}}" id="{{l.Value}}-self">
              </div>
           </div>
          </div>

          <div class="setting-block">
          <h5 ng-cloak>{{::lang.connections}}</h5>
          <div class="row" ng-repeat="l in Label" ng-if="l.Section=='connection'" repeat-done="superPrivacy();">
              <div class="col-sm-6" ng-bind="l.Name"></div>
              <div class="col-sm-6">
                  <div class="privacy-ability">
                      <div class="radio" ng-class="getSelectedClass('friend',Opt[l.Value])"  ng-if="ModlStg.m10 == 1">
                          <input type="radio" ng-click="changeOptVal(l.Value,'friend')" ng-model="Opt[l.Value]" value="friend" name="{{l.Value}}" id="{{l.Value}}-fr">
                          <label for="{{l.Value}}-fr">&nbsp;</label> 
                      </div>
                      
                      <div class="radio" ng-class="getSelectedClass('everyone',Opt[l.Value])">
                          <input type="radio" ng-click="changeOptVal(l.Value,'everyone')" ng-model="Opt[l.Value]" value="everyone" name="{{l.Value}}" id="{{l.Value}}-Everyone">
                          <label for="{{l.Value}}-Everyone">&nbsp;</label> 
                      </div> 
                  </div>
                  <input style="display:none;" type="radio" ng-model="Opt[l.Value]" value="self" name="{{l.Value}}" id="{{l.Value}}-self">
              </div>
           </div>
          </div> 
     </div>
    <div class="panel-footer privacy-footer" ng-cloak ng-if="IsReady=='1'">
        <button type="button" class="btn btn-primary btn-sm pull-right" ng-click="savePrivacy()">{{::lang.save}}</button>
        <button type="button" class="btn btn-primary   pull-right btn-link" ng-click="resetPrivacySettings()">{{::lang.reset}}</button>
    </div>

 </div> 
 

</aside>
<!-- //Left Wall-->


</div>
</div>