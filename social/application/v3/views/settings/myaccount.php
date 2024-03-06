<div class="container wrapper" id="MyAccountCtrl" ng-controller="teachManProfCtrl"> 
    <h4 class="label-title secondary-title"><?php echo lang('settings');?></h4>
    <div class="tab-dropdowns profile-tabs"> <a href="javascript:void(0);"> <i class="icon-smallcaret"></i> <span>MEMBERS</span> </a> </div>
    <div class="panel panel-default">
      <div class="modal-content">
        <div class="panel-body">
          <div class="setting-wrapper">
            <div role="tabpanel">
              <!-- Nav tabs -->
              <ul role="tablist" class="secondary-tabs small-screen-tabs  hidden-xs">
                <li class="active">
                  <a data-toggle="tab" role="tab" aria-controls="basic-info" href="#basic-info" class="active" aria-expanded="true"> <span><i class="ficon-user hidden-xs hidden-sm"></i><?php echo lang('basic_info');?></span> </a> 
                </li>
                <li class="">
                  <a onClick="passErrorRemove();" data-toggle="tab" role="tab" aria-controls="pswrd" href="#pswrd" aria-expanded="false"> <span> <i class="ficon-dots hidden-xs hidden-sm"></i>
                  <resetpassword ng-cloak ng-if="SetPassword==0"><?php echo lang('set_password');?></resetpassword>
                  <resetpassword ng-cloak ng-if="SetPassword==1"><?php echo lang('reset_password');?></resetpassword>
                  </span> </a> 
                </li>
                <li class="">
                  <a data-toggle="tab" role="tab" aria-controls="lang" href="#lang" aria-expanded="false"> <span><i class="ficon-lang hidden-xs hidden-sm"></i><?php echo lang('language');?></span> </a>
                </li>
                <li class="">
                  <a href="<?php echo site_url('notification/settings') ?>"> <span><i class="ficon-alarm hidden-xs hidden-sm"></i><?php echo lang('notifications');?></span> </a>
                </li>
                <li class="">
                  <a href="<?php echo site_url('myaccount/privacy') ?>"> <span><i class="ficon-cog hidden-xs hidden-sm"></i><?php echo lang('privacy');?></span> </a>
                </li>
              </ul>
              <!-- Tab panes -->
              <div class="tab-content secondary-tab-content">
                <div id="basic-info" class="tab-pane secondary-tab-pane active" role="tabpanel" >
                  <form ng-submit="ValidateEditAccount();" id="allcontrolform" class="" role="form">
                  <!--PERSONAL INFORMATION-->
                  <?php
                    $this->load->view('settings/personal_information');
                  ?>
                  <!--OTHER INFORMATION-->
                  
                    <aside id="otherInfo" class="content-block-region">
                      <div class="title">
                        <div class="editSave"> <a data-ng-click="otherInfoEdit= !otherInfoEdit;ChangePanelStatus('otherInfoEdit');"  data-ng-class="{'show': !otherInfoEdit, 'hide': otherInfoEdit}" title="Edit" class="btn btn-default btn-icon hide"> <i class="ficon-pencil"></i>Edit </a>
                          <div data-ng-class="{'show': otherInfoEdit, 'hide': !otherInfoEdit}" class="save-cancel hide"> <a data-ng-click="otherInfoEdit= !otherInfoEdit;  getResetValue('otherInfo');" title="Cancel" class="cancelEdit btn btn-link gray-clr">Cancel</a>
                            <input type="submit" value="Save" onClick="return checkstatus('otherInfo');" title="Save" class="saveAccount btn btn-default">
                          </div>
                        </div>
                        <span class="title-text">OTHER INFORMATION</span> </div>
                      <div on="otherInfoEdit" data-ng-switch="">
                        <!-- ngSwitchDefault:  -->
                        <div data-ng-switch-default="" class="table-content ng-scope">
                          <aside class="row">
                            <aside class="col-xs-12 col-sm-6">
                              <div class="form-group">
                                <label>Relationship</label>
                                <div class="viewMode"> 
                                  <span class="" ng-bind="MartialStatusTxt"></span>
                                  <span ng-if="$parent.RelationWithInput!=''">
                                  <span ng-if="$parent.MartialStatusEdit==2 || $parent.MartialStatusEdit==5">With</span>
                                        <span ng-if="$parent.MartialStatusEdit==3 || $parent.MartialStatusEdit==4">To</span>
                                        <a ng-if="$parent.RelationWithURL!==''" ng-href="<?php echo site_url() ?>{{$parent.RelationWithURL}}" ng-bind="$parent.RelationWithInput"></a><span ng-if="$parent.RelationWithURL==''" ng-bind="$parent.RelationWithInput"></span> <!-- <span class="gray-clr">to</span> <a class="font-medium" href="javascript:void(0);">Milind</a> --> 
                                  </span>
                                  </div>
                              </div>
                            </aside>
                            <aside class="col-xs-12 col-sm-6">
                              <div class="form-group">
                                <label>About</label>
                                <div class="viewMode"> <span ng-bind-html="aboutme | nl2br"></span> </div>
                              </div>
                            </aside>
                            <aside class="col-xs-12 col-sm-6" >
                              <div class="form-group">
                                <label>Introduction</label>
                                <div class="viewMode"> <span ng-bind-html="Introduction | nl2br"></span> </div>
                              </div>
                            </aside>
                          </aside>
                        </div>
                        <div class="table-content hide" data-ng-switch-when="true" data-ng-class="{'show': otherInfoEdit, 'hide': !otherInfoEdit}" >
                             <div class="form clearfix">
                                  <aside class="row">
                                    <div class="row">
                                      <aside class="col-xs-12 col-sm-6">
                                        <div class="form-group">
                                          <label>Relationship</label>
                                          <div class="text-field-select" ng-init="RelationshipOptions=[{val:'1',Relation:'Single'},{val:'2',Relation:'In a relationship'},{val:'3',Relation:'Engaged'},{val:'4',Relation:'Married'},{val:'5',Relation:'Its complicated'},{val:'6',Relation:'Separated'},{val:'7',Relation:'Divorced'}]">
                                              <select  
                                                  ng-model="$parent.MartialStatusEdit" 
                                                  ng-value="$parent.MartialStatus" 
                                                  id="MStatus" 
                                                  name="MaritalStatus" 
                                                  data-chosen="" 
                                                  data-disable-search="true" 
                                                  data-ng-change="showRelationWith();" 
                                                  data-placeholder="Choose Marital Status"
                                                  ng-options="Relationship.val as Relationship.Relation for Relationship in RelationshipOptions">
                                                  <option value=""></option>
                                              </select>
                                          </div>
                                        </div>
                                      </aside>
                                      <aside class="col-xs-12 col-sm-6" ng-show="showRelationOption==1" ng-init="InitRelationTo();">
                                        <div class="form-group">
                                          <label ng-if="RelationReferenceTxt==0">To</label>
                                          <label ng-if="RelationReferenceTxt==1">With</label>
                                            <div data-error="hasError" class="text-field">
                                              <input type="text" ng-model="$parent.RelationWithInputEdit" data-requiredmessage="Required" data-msglocation="errorTo" data-mandatory="false" data-controltype="relationfield" value="" id="RelationTo" class="form-control ui-autocomplete-input" placeholder="Start typing" uix-input="" />
                                              <label id="errorTo" class="error-block-overlay"></label>
                                            </div>
                                        </div>
                                        <!-- <span class="relate hidden-xs">-</span> -->
                                      </aside>
                                    </div>
                                    <aside class="col-xs-12 col-sm-6">
                                      <div class="form-group">
                                        <label>About</label>
                                        <div data-error="hasError" class="textarea-field">
                                          <textarea uix-textarea="" data-req-minlen="2" maxlength="200" data-req-maxlen="200" class="form-control" placeholder="Please enter something about yourself" ng-model="$parent.aboutmeEdit" id="About" maxcount="200"></textarea>
                                          <span id="spn2textareaID" style="cursor: pointer; color: Red; position: inherit;"></span><br>
                                        <span class="max-count" ng-bind="200-aboutmeEdit.length"></span>

                                        </div>
                                      </div>
                                    </aside>
                                    <aside class="col-xs-12 col-sm-6">
                                      <div class="form-group">
                                        <label>Introduction</label>
                                        <div data-error="hasError" class="textarea-field">
                                          <textarea uix-textarea="" data-req-minlen="0" maxlength="140" data-req-maxlen="140" class="form-control" placeholder="Please introduce yourself" ng-model="$parent.IntroductionEdit" id="Introduction" maxcount="140"></textarea>
                                          <span id="spn2textareaID1" style="cursor: pointer; color: Red; position: inherit;"></span><br>
                                          <span class="max-count"  ng-bind="140-IntroductionEdit.length"></span>

                                        </div>
                                      </div>
                                    </aside>
                                  </aside>
                              </div>
                            </div>
                        <!-- ngSwitchWhen: true -->
                      </div>
                    </aside>
                 
                  <!--WORK EXPERIENCE-->
                  <?php
                    $this->load->view('settings/work_experience');
                  ?>
                  <!--WORK EXPERIENCE-->
                  <!--EDUCATION-->
                  <?php
                    $this->load->view('settings/education');
                  ?>
                  <!--EDUCATION-->
                  
                  <!--SOCIAL ACCOUNTS-->
                  <?php
                    $this->load->view('settings/social_account');
                  ?>
                  <!--SOCIAL ACCOUNTS-->
                  </form>
                </div>
                
                <!-- RESET PASSWORD-->
                <?php $this->load->view('settings/reset_password');?>

                <!-- SET PASSWORD (SHOW WHILE LOGIN FROM SOCIAL NETWORK)-->
                <?php $this->load->view('settings/set_password');?>
                
                <div id="lang" class="tab-pane" role="tabpanel">
                   <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12 center-block">
                  	<div class="inner-form clearfix">
                    <div class="form clearfix">
                    <div class="form-group">
                      <label><?php echo lang('select');?> <?php echo lang('language');?></label>
                      <div class="text-field-select">
                        <select data-disable-search="true" onChange="changeLanguage(this.value)" data-chosen="">
                          <option <?php if($this->config->item('language')=='english'){ echo 'selected="selected"'; } ?> value="english">English</option>
                          <option <?php if($this->config->item('language')=='french'){ echo 'selected="selected"'; } ?> value="french">French</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  </div>
                  </div>
                </div>
                <div id="notification" class="tab-pane" role="tabpanel">
                  <div class="form clearfix">
                    <div class="form-group">
                      <div class="notif-section">
                        <button class="btn btn-default btn-md-capture"><i class="icon-notification-new"></i></button>
                        <p><?php echo lang('coming_soon');?>...</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="privacy" class="tab-pane" role="tabpanel">
                  <div class="form clearfix">
                    <div class="form-group">
                      <div class="notif-section">
                        <button class="btn btn-default btn-md-capture"><i class="icon-notification-new"></i></button>
                        <p><?php echo lang('coming_soon');?>...</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.Nav tabs -->
          </div>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog --> 
  <!-- /.modal -->
</div>


<input type="hidden" name="UserGUID" value="<?php echo $this->session->userdata('UserGUID'); ?>" data-ng-model="UserGUID" ng-init="UserGUID='<?php echo $this->session->userdata('UserGUID'); ?>'" id="UserGUID" />