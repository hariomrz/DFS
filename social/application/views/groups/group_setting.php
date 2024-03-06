<div ng-controller="GroupMemberCtrl"  ng-init="GroupDetail();initSetting();GroupGUID='<?php echo $ModuleEntityGUID;?>'">
<?php $this->load->view('profile/profile_banner') ?> 
  

    <div class="container wrapper ">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <h3 class="panel-title border-bottom">GROUP SETTINGS</h3>
                    <div class="panel-body">
                        <div class="padding-inner border-bottom">
                             <div class="form-group">
                                <label>Group Privacy</label>
                                <div class="privat-lisitng">
                                    <ul class="list-group">
                                        <li ng-if="showIsPublic" class="col-sm-6">
                                            <div class="radio">
                                                <input id="open" type="radio" value="1" ng-checked="GroupDetails.IsPublic==1" name="group" ng-model="GroupDetails.IsPublic"  >
                                                <label for="open"> <i class="ficon-globe"></i> Open</label>
                                                <p>Anyone can see the group, its members and their posts.</p>
                                            </div>
                                        </li>
                                        <li ng-if="showIsClose" class="col-sm-6">
                                            <div class="radio">
                                                <input id="closed" ng-checked="GroupDetails.IsPublic==0"  type="radio" name="group"  ng-model="GroupDetails.IsPublic" value="0">
                                                <label for="closed"> <i class="ficon-close f-lg"></i> Closed </label>
                                                <p>Anyone can see the group, but only members can post.</p>
                                            </div>
                                        </li>
                                        <li ng-if="showIsSecret" class="col-sm-6">
                                            <div class="radio">
                                                <input id="secret" ng-checked="GroupDetails.IsPublic==2"  type="radio" name="group" ng-model="GroupDetails.IsPublic" value="2" >
                                                <label for="secret"><i class="ficon-secrets f-lg"></i> Secret </label>
                                                <p>Only invited members can see group.</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                       
                        <div class="padding-inner border-bottom">
                             <div class="form-group">
                                <label>Group Content</label>
                                <div class="privat-lisitng"> 
                                    <ul class="list-group">
                                        <li class="col-sm-4 col-md-2" ng-repeat="Content in ContentTypes"  repeat-done="do_disable_options()" ng-cloak>

                                            <label class="checkbox">
                                                <input type="checkbox" name="AllowedTypes[]" value="{{Content.Value}}"  ng-checked="checkAllowedType(Content.Value)"  ng-click="toggleAllowedTypes(Content)">
                                                <span class="label"> {{Content.Label}}</span>
                                            </label>
                                        </li>
                                    </ul> 
                                </div>                                
                            </div>
                        </div>

                        <div class="padding-inner">
                            <div class="form-group">
                                <label>Default Landing Tab</label>
                                <div class="privat-lisitng"> 
                                    <div class="col-sm-4">
                                        <select class="form-control" data-chosen="" ng-model="GroupDetails.SelectedPage" data-placeholder="Select main category"   data-disable-search="false" data-ng-options="tab.Label for tab in DefaultTab track by tab.Label" options-disabled="DisableTabs.indexOf(tab.Label)>-1 for tab in DefaultTab" >
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>                                
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <div class="pull-right">                        
                            <button type="button" class="btn btn-primary" ng-click="update_group_setting()">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- <input type="hidden" id="hdngrpid" value="<?php echo $ModuleEntityID ; ?>" /> -->

<input type="hidden" id="ModuleEntityGUID" value="<?php echo $ModuleEntityGUID; ?>" />


