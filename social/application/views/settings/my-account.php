<aside class="multiple-education" style="display:none;">
  <aside class="col-xs-12 col-sm-12">
    <div class="form-group">
      <label>University Name</label>
      <button onclick="deleteEdu(this)" class="btn-link remove-block" type="button"> Delete</button>
      <div data-error="hasError" class="text-field">
        <input data-controltype="customval" data-req-minlen="2" maxlength="50" data-req-maxlen="50" name="university[]" type="text" value="" placeholder="University Name" uix-input="" />
        <label id="errorGeneral5" class="error-block-overlay"></label>
        <!--<span>{{generalError}}</span> -->
      </div>
    </div>
  </aside>
  <aside class="col-xs-12 col-sm-6">
    <div class="form-group">
      <label>Course Name</label>
      <div data-error="hasError" class="text-field">
        <input data-controltype="alphanum" data-req-minlen="2" maxlength="50" data-req-maxlen="50" name="course[]" type="text" value="" placeholder="Course Name" uix-input="" />
        <label id="errorGeneral6" class="error-block-overlay"></label>
      </div>
    </div>
  </aside>
  <aside class="col-xs-12 col-sm-6">
    <div class="form-group">
      <label>Dates Attended</label>
      <aside class="row">
        <aside class="col-xs-6">
          <div class="text-field-select">
            <select onchange="resetChosen(this)" name="estartyear[]" data-chosen="" data-disable-search="true" data-placeholder="From Year">
              <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
              <option value="<?php echo $i ?>"><?php echo $i ?></option>
              <?php } ?>
            </select>
            <label class="error-block-overlay" style="display:none;">Invalid Date</label>
          </div>
        </aside>
        <aside class="col-xs-6">
          <div class="text-field-select">
            <select onchange="resetChosen(this)" name="eendyear[]" data-chosen="" data-disable-search="true" data-placeholder="To Year">
              <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
              <option value="<?php echo $i ?>"><?php echo $i ?></option>
              <?php } ?>
            </select>
            <label class="error-block-overlay" style="display:none;">Invalid Date</label>
          </div>
        </aside>
      </aside>
    </div>
  </aside>
</aside>
<aside class="multiple-experience" style="display:none;">
  <aside class="col-xs-12 col-sm-12">
    <div class="form-group">
      <label>Organisation Name</label>
      <button onclick="deleteExp(this)" class="btn-link remove-block" type="button"> Delete</button>
      <div data-error="hasError" class="text-field">
        <input data-controltype="customval" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="organisation[]" value="" placeholder="Organisation Name" uix-input="" />
        <label id="errorOrgname" class="error-block-overlay"></label>
      </div>
    </div>
  </aside>
  <aside class="col-xs-12 col-sm-12">
    <div class="form-group">
      <label>Designation/Project </label>
      <div data-error="hasError" class="text-field">
        <input data-controltype="alphanum" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="designation[]" value="" placeholder="Designation/Project" uix-input=""/>
        <label id="errorDesig" class="error-block-overlay"></label>
      </div>
    </div>
  </aside>
  <aside class="col-xs-12 col-sm-12">
    <label class="control-label">Time Period</label>
    <aside class="row four-cell">
      <aside class="col-xs-6 col-sm-3 m-t-10">
        <div class="text-field-select">
          <select onchange="resetChosen(this)" name="startmonth[]" data-chosen="" data-disable-search="true" data-placeholder="From Month">
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
          </select>
          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
        </div>
      </aside>
      <aside class="col-xs-6 col-sm-3 m-t-10">
        <div class="text-field-select">
          <select onchange="resetChosen(this)" name="startyear[]" data-chosen="" data-disable-search="true" data-placeholder="From Year">
            <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
            <option value="<?php echo $i ?>"><?php echo $i ?></option>
            <?php } ?>
          </select>
          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
        </div>
      </aside>
      <aside class="col-xs-6 col-sm-3 m-t-10">
        <div class="text-field-select">
          <select onchange="resetChosen(this)" name="endmonth[]" data-chosen="" data-disable-search="true" data-placeholder="To Month">
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
          </select>
          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
        </div>
        <div class="checkbox check-primary custom-m-t-10 pull-left">
          <input type="checkbox" value="1" name="TillDate[]" class="till-date-checkbox">
          <label class="pull-right m-l-10 till-date" onclick="checkTillDate(this);" for="till-date-checkbox">Till Date</label>
        </div>
      </aside>
      <aside class="col-xs-6 col-sm-3 m-t-10">
        <div class="text-field-select">
          <select onchange="resetChosen(this)" name="endyear[]" data-chosen="" data-disable-search="true" data-placeholder="To Year">
            <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
            <option value="<?php echo $i ?>"><?php echo $i ?></option>
            <?php } ?>
          </select>
          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
        </div>
      </aside>
    </aside>
  </aside>
</aside>
<div class="container wrapper" id="MyAccountCtrl" ng-controller="teachManProfCtrl">
  <div class="custom-modal">
    <h4 class="label-title secondary-title"><?php echo lang('settings');?></h4>
    <div class="tab-dropdowns profile-tabs"> <a href="javascript:void(0);"> <i class="icon-smallcaret"></i> <span>MEMBERS</span> </a> </div>
    <div class="panel panel-default">
      <div class="modal-content">
        <div class="panel-body">
          <div class="setting-wrapper">
            <div role="tabpanel">
              <!-- Nav tabs -->
              <ul role="tablist" class="secondary-tabs small-screen-tabs hidden-xs">
                <li class="active"> <a data-toggle="tab" role="tab" aria-controls="basic-info" href="#basic-info" class="active" aria-expanded="true"> <span><i class="icon-onlyme hidden-xs hidden-sm"></i><?php echo lang('basic_info');?></span> </a> </li>
                <li class=""> <a onclick="passErrorRemove();" data-toggle="tab" role="tab" aria-controls="pswrd" href="#pswrd" aria-expanded="false"> <span> <i class="icon-sett-pswrd hidden-xs hidden-sm"></i>
                  <resetpassword ng-cloak ng-if="SetPassword==0"><?php echo lang('set_password');?></resetpassword>
                  <resetpassword ng-cloak ng-if="SetPassword==1"><?php echo lang('reset_password');?></resetpassword>
                  </span> </a> </li>
                <li class=""> <a data-toggle="tab" role="tab" aria-controls="lang" href="#lang" aria-expanded="false"> <span><i class="icon-sett-lang hidden-xs hidden-sm"></i><?php echo lang('language');?></span> </a> </li>
                <li class=""> <a data-toggle="tab" role="tab" aria-controls="notification" href="#notification" aria-expanded="false"> <span><i class="icon-sett-noti hidden-xs hidden-sm"></i><?php echo lang('notifications');?></span> </a> </li>
                <li class=""> <a data-toggle="tab" role="tab" aria-controls="privacy" href="#privacy" aria-expanded="false"> <span><i class="icon-sett-privacy hidden-xs hidden-sm"></i><?php echo lang('privacy');?></span> </a> </li>
              </ul>
              <!-- Tab panes -->
              <div class="tab-content secondary-tab-content">
                <div id="basic-info" class="tab-pane secondary-tab-pane active" role="tabpanel">
                  <form ng-submit="submitAboutMe();" id="allcontrolform" class="" role="form">
                    <div class="form clearfix">
                      <div class="form-group">
                        <aside class="row">
                          <aside class="col-xs-12 col-sm-6">
                            <div class="form-group">
                              <label><?php echo lang('name');?> <span class="req-sign">*</span></label>
                              <div class="text-field" data-error="hasError">
                                <input type="text" uix-input="" data-req-minlen="2" maxlength="50" data-req-maxlen="50" ng-model="FirstName" placeholder="First Name" id="firsttnamefieldCtrlID" value="" data-controltype="namefield" data-mandatory="true" data-msglocation="errorFirstname" data-requiredmessage="First Name Required">
                                <label class="error-block-overlay" id="errorFirstname"></label>
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-6">
                            <div class="form-group">
                              <label>&nbsp;</label>
                              <div class="text-field" data-error="hasError">
                                <input type="text" uix-input="" data-req-minlen="2" maxlength="50" data-req-maxlen="50" placeholder="Last Name" ng-model="LastName" id="lastnamefieldCtrlID" value="" data-controltype="namefield" data-mandatory="true" data-msglocation="errorLastname" data-requiredmessage="Last Name Required">
                                <label class="error-block-overlay" id="errorLastname"></label>
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-6">
                            <div class="form-group">
                              <label><?php echo lang('username');?> <span class="req-sign">*</span></label>
                              <div class="text-field" data-error="hasError">
                                <input type="text" uix-input="" data-req-minlen="4" maxlength="50" data-req-maxlen="50" placeholder="Username" ng-model="Username" id="usernameCtrlID" value="" data-controltype="username" data-mandatory="true" data-msglocation="errorUsername" data-data-requiredmessage="Username Required">
                                <label class="error-block-overlay" id="errorUsername"></label>
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-6">
                            <div class="form-group">
                              <label><?php echo lang('email');?> <span class="req-sign">*</span></label>
                              <div class="text-field" data-error="hasError">
                                <input type="text" uix-input="" maxlength="50" data-req-maxlen="50" placeholder="Email" ng-model="Email" id="emailCtrlID" value="" data-controltype="email" data-mandatory="true" data-msglocation="errorEmail" data-requiredmessage="Email Required.">
                                <label class="error-block-overlay" id="errorEmail"></label>
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-6">
                            <div class="form-group">
                              <label>Gender</label>
                              <div class="text-field-select">
                                <select ng-init="genderSelect()" ng-model="Gender" name="Gender" ng-value="Gender" data-chosen="" data-disable-search="true" data-placeholder="Choose Gender">
                                  <option value="1">Male</option>
                                  <option value="2">Female</option>
                                  <option value="3">Other</option>
                                </select>
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-6">
                            <div class="form-group">
                              <label>Date of Birth</label>
                              <div class="text-field">
                                <input ng-model="DOB" type="text" readonly id="Datepicker3" name="DOB" placeholder="__/__/____" />
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-12">
                            <div class="form-group">
                              <label>Location</label>
                              <div class="text-field">
                                <input type="text" ng-keyup="locationKeyUp()" onblur="checkOldLocation(); $('.ui-autocomplete').hide();" class="location-data" uix-input="" data-req-minlen="5" maxlength="50" data-req-maxlen="50" placeholder="Location" ng-model="Location" id="address" value="">
                                <input type="hidden" ng-model="City" />
                                <input type="hidden" ng-model="State" />
                                <input type="hidden" ng-model="Country" />
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-6">
                            <div class="form-group">
                              <label>Relationship</label>
                              <div class="text-field-select">
                                <select ng-init="martialSelect()" ng-model="MartialStatus" ng-value="MartialStatus" id="MStatus" name="MaritalStatus" data-chosen="" data-disable-search="true" data-placeholder="Choose Marital Status">
                                  <option value="1">Single</option>
                                  <option value="2">In a relationship</option>
                                  <option value="3">Engaged</option>
                                  <option value="4">Married</option>
                                  <option value="5">Its complicated</option>
                                  <option value="6">Separated</option>
                                  <option value="7">Divorced</option>
                                </select>
                              </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-6">
                            <label>&nbsp;</label>
                            <div class="text-field">
                              <!-- <input type="text" placeholder="" /> -->
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-12">
                            <div class="form-group">
                              <label><?php echo lang('about');?></label>
                              <div class="textarea-field" data-error="hasError">
                                <textarea uix-textarea="" data-req-minlen="2" maxlength="200" data-req-maxlen="200" class="form-control" placeholder="Please enter something about yourself" ng-model="aboutme" id="aboutText" maxcount="200"></textarea>
                                <span id="spn2textareaID" style="cursor: pointer; color: Red; position: inherit;"></span><br>
                                <span id="noOfCharaboutText"></span> </div>
                            </div>
                          </aside>
                          <aside class="col-xs-12 col-sm-12">
                            <div class="form-group">
                              <label>Time Zone</label>
                              <div class="text-field-select" ng-init="getTimeZoneList();" data-error="hasError">
                                <select data-chosen="" data-disable-search="false" data-placeholder="Select Timezone" ng-model="TZone" ng-options="key as value for (key,value) in TimeZoneList">
                                </select>
                              </div>
                            </div>
                          </aside>
                          <!-- Work Experience Start -->
                          <aside id="addWorkBlock">
                            <aside class="col-xs-12 col-sm-12"> <a class="pull-right profile-link" id="addWork" href="javascript:void(0);">Add Work</a>
                              <h5 class="profile-title">WORK EXPERIENCE</h5>
                            </aside>
                            <aside class="addWorkBlock">
                              <aside class="addWorkBlockInner">
                                <!-- If WorkExperience length is 0 -->
                                <aside class="multiple-experience" ng-if="WorkExperience.length>0" ng-repeat="WExp in WorkExperience">
                                  <aside class="col-xs-12 col-sm-12">
                                    <div class="form-group">
                                      <label>Organisation Name</label>
                                      <button onclick="deleteExp(this)" class="btn-link remove-block" type="button"> Delete</button>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="customval" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="organisation[]" ng-value="WExp.OrganizationName" placeholder="Organisation Name" uix-input="" />
                                        <label id="errorOrgname" class="error-block-overlay"></label>
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-12">
                                    <div class="form-group">
                                      <label>Designation/Project </label>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="alphanum" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="designation[]" ng-value="WExp.Designation" value="" placeholder="Designation/Project" uix-input=""/>
                                        <label id="errorDesig" class="error-block-overlay"></label>
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-12">
                                    <label class="control-label">Time Period</label>
                                    <aside class="row four-cell">
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="startmonth[]" class="start-year" data-chosen="" ng-value="WExp.StartMonth" data-disable-search="true" data-placeholder="From Month">
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                      </aside>
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="startyear[]" ng-value="WExp.StartYear" data-chosen="" data-disable-search="true" data-placeholder="From Year">
                                            <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                            <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                            <?php } ?>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                      </aside>
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="endmonth[]" class="end-year" ng-value="WExp.EndMonth" data-chosen="" data-disable-search="true" data-placeholder="To Month">
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                        <div class="checkbox check-primary custom-m-t-10 pull-left">
                                          <input type="checkbox" value="1" ng-checked="CurrentlyWorkHere=='1'" name="TillDate[]" class="till-date-checkbox{{$index+2}}">
                                          <label class="pull-right m-l-10 till-date" onclick="updateCheckBoxStatus(this);" for="till-date-checkbox{{$index+2}}">Till Date</label>
                                        </div>
                                        <input type="hidden" ng-value="WExp.WorkExperienceGUID" name="WorkExperienceGUID[]">
                                      </aside>
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="endyear[]" ng-value="WExp.EndYear" data-chosen="" data-disable-search="true" data-placeholder="To Year">
                                            <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                            <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                            <?php } ?>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                      </aside>
                                    </aside>
                                  </aside>
                                </aside>
                                <!-- If WorkExperience length is 0 -->
                                <aside class="multiple-experience" ng-if="WorkExperience.length==0">
                                  <aside class="col-xs-12 col-sm-12">
                                    <div class="form-group">
                                      <label>Organisation Name</label>
                                      <button onclick="deleteExp(this)" class="btn-link remove-block" type="button"> Delete</button>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="customval" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="organisation[]" value="" placeholder="Organisation Name" uix-input="" />
                                        <label id="errorOrgname" class="error-block-overlay"></label>
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-12">
                                    <div class="form-group">
                                      <label>Designation/Project </label>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="alphanum" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="designation[]" value="" placeholder="Designation/Project" uix-input=""/>
                                        <label id="errorDesig" class="error-block-overlay"></label>
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-12">
                                    <label class="control-label">Time Period</label>
                                    <aside class="row four-cell">
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="startmonth[]" data-chosen="" data-disable-search="true" data-placeholder="From Month">
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                      </aside>
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="startyear[]" data-chosen="" data-disable-search="true" data-placeholder="From Year">
                                            <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                            <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                            <?php } ?>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                      </aside>
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="endmonth[]" data-chosen="" data-disable-search="true" data-placeholder="To Month">
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                        <div class="checkbox check-primary custom-m-t-10 pull-left">
                                          <input type="checkbox" value="1" name="TillDate[]" class="till-date-checkbox-1">
                                          <label class="pull-right m-l-10 till-date" onclick="updateCheckBoxStatus(this);" for="till-date-checkbox-1">Till Date</label>
                                        </div>
                                      </aside>
                                      <aside class="col-xs-6 col-sm-3 m-t-10">
                                        <div class="text-field-select">
                                          <select onchange="resetChosen(this)" name="endyear[]" data-chosen="" data-disable-search="true" data-placeholder="To Year">
                                            <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                            <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                            <?php } ?>
                                          </select>
                                          <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                        </div>
                                      </aside>
                                    </aside>
                                  </aside>
                                </aside>
                              </aside>
                              <aside class="m-r-15 overflow pull-right">
                                <!--<button class="btn-link cancleEducation" type="button"> Cancel </button>
<button id="saveAddWork" class="btn btn-primary" type="button"> Save</button>-->
                              </aside>
                            </aside>
                          </aside>
                          <!-- Work Experience End -->
                          <!-- Education Start -->
                          <aside id="addEducationBlock">
                            <aside class="col-xs-12 col-sm-12"> <a class="pull-right profile-link" id="addEducation" href="javascript:void(0);">Add Education</a>
                              <h5 class="profile-title">ADD EDUCATION</h5>
                            </aside>
                            <aside class="addEducationBlock">
                              <aside class="addEducationBlockInner">
                                <!-- If Education Length is greater than 0 -->
                                <aside class="multiple-education" ng-if="UserEducation.length==0">
                                  <aside class="col-xs-12 col-sm-12">
                                    <div class="form-group">
                                      <label>University Name</label>
                                      <button onclick="deleteEdu(this)" class="btn-link remove-block" type="button"> Delete</button>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="customval" data-req-minlen="2" maxlength="50" data-req-maxlen="50" name="university[]" type="text" value="" placeholder="University Name" uix-input="" />
                                        <label id="errorGeneral5" class="error-block-overlay"></label>
                                        <!--<span>{{generalError}}</span> -->
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                      <label>Course Name</label>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="alphanum" data-req-minlen="2" maxlength="50" data-req-maxlen="50" data-controltype="alphanum" data-req-minlen="2" maxlength="50" data-req-maxlen="50" name="course[]" type="text" value="" placeholder="Course Name" uix-input="" />
                                        <label id="errorGeneral6" class="error-block-overlay"></label>
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                      <label>Dates Attended</label>
                                      <aside class="row">
                                        <aside class="col-xs-6">
                                          <div class="text-field-select">
                                            <select onchange="resetChosen(this)" name="estartyear[]" data-chosen="" data-disable-search="true" data-placeholder="From Year">
                                              <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                              <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                              <?php } ?>
                                            </select>
                                            <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                          </div>
                                        </aside>
                                        <aside class="col-xs-6">
                                          <div class="text-field-select">
                                            <select onchange="resetChosen(this)" name="eendyear[]" data-chosen="" data-disable-search="true" data-placeholder="To Year">
                                              <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                              <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                              <?php } ?>
                                            </select>
                                            <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                          </div>
                                        </aside>
                                      </aside>
                                    </div>
                                  </aside>
                                </aside>
                                <!-- If Education Length is 0 -->
                                <aside class="multiple-education" ng-if="UserEducation.length>0" ng-repeat="Edu in UserEducation">
                                  <aside class="col-xs-12 col-sm-12">
                                    <div class="form-group">
                                      <label>University Name</label>
                                      <button onclick="deleteEdu(this)" class="btn-link remove-block" type="button"> Delete</button>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="customval" data-req-minlen="2" maxlength="50" data-req-maxlen="50" name="university[]" ng-value="Edu.University" type="text" value="" placeholder="University Name" uix-input="" />
                                        <label id="errorGeneral5" class="error-block-overlay"></label>
                                        <!--<span>{{generalError}}</span> -->
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                      <label>Course Name</label>
                                      <div data-error="hasError" class="text-field">
                                        <input data-controltype="alphanum" data-req-minlen="2" maxlength="50" data-req-maxlen="50" name="course[]" ng-value="Edu.CourseName" type="text" value="" placeholder="Course Name" uix-input="" />
                                        <label id="errorGeneral6" class="error-block-overlay"></label>
                                      </div>
                                    </div>
                                  </aside>
                                  <aside class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                      <label>Dates Attended</label>
                                      <aside class="row">
                                        <aside class="col-xs-6">
                                          <div class="text-field-select">
                                            <select onchange="resetChosen(this)" name="estartyear[]" ng-value="Edu.StartYear" data-chosen="" data-disable-search="true" data-placeholder="From Year">
                                              <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                              <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                              <?php } ?>
                                            </select>
                                            <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                          </div>
                                        </aside>
                                        <aside class="col-xs-6">
                                          <div class="text-field-select">
                                            <select onchange="resetChosen(this)" name="eendyear[]" ng-value="Edu.EndYear" data-chosen="" data-disable-search="true" data-placeholder="To Year">
                                              <?php for($i=date('Y'); $i>=date('Y')-50; $i--){ ?>
                                              <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                              <?php } ?>
                                            </select>
                                            <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                                            <input type="hidden" name="EducationGUID[]" ng-value="Edu.EducationGUID" />
                                          </div>
                                        </aside>
                                      </aside>
                                    </div>
                                  </aside>
                                </aside>
                              </aside>
                              <aside class="m-r-15 pull-right overflow">
                                <!--<button class="btn-link cancleEducation" type="button"> Cancel</button>
<button id="saveEducation" class="btn btn-primary" type="button"> Save</button>-->
                              </aside>
                            </aside>
                          </aside>
                          <!-- Education End -->
                          <aside class="col-xs-12">
                            <h5 ng-init="checkSocialAccounts()" class="profile-title"><?php echo strtoupper(lang('social_accounts'));?></h5>
                          </aside>
                          <div class="social-media col-xs-12">
                            <ul class="list-group setting-list-group">
                              <!-- <li>
<div class="social-wrapper">
<div class="social-icons">
<div class="social-buttons">
<button class="btn btn-primary btn-facebook btn-sm rounded-corner m-r-5" id="facebookloginbtn" onClick="fb_obj.FbLoginStatusCheck();" type="button"><i class="icon-facebook"></i> </button>
</div>
</div>
<aside class="overflow">
<a ng-show="facebook.profileUrl" style="float: right; width: 10%; padding: 8px 0 0 8px;" href="javascript:void(0);" title="Detach Account" ng-click="detachAccount('Facebook API')"><i class="ficon-cross"></i></a>
<div style="width:90%;" class="text-field" ng-show="facebook.profileUrl" data-error="hasError">
<input readonly type="text" uix-input="" ng-model="facebook.profileUrl" ng-init="facebook.profileUrl=''" ng-value="facebook.profileUrl" placeholder="Facebook" value="">
</div>
</aside>
</div>
</li> -->
                              <li>
                                <div class="social-wrapper">
                                  <div class="social-icons">
                                    <div class="social-buttons">
                                      <button type="button" onClick="fb_obj.FbLoginStatusCheck();" class="btn btn-primary btn-facebook btn-sm rounded-corner m-r-5"><i class="icon-facebook"></i> </button>
                                    </div>
                                  </div>
                                  <aside class="overflow">
                                    <div ng-if="facebook.profileUrl==''" class="addsocial-link"> <a onClick="fb_obj.FbLoginStatusCheck();" href="javascript:void(0);"> Add Your Facebook Account </a>
                                      <!-- <a ng-if="facebook.profileUrl!==''" ng-href="{{facebook.profileUrl}}" ng-bind="facebook.profileUrl"></a> -->
                                    </div>
                                    <div ng-if="facebook.profileUrl!==''" class="add-social-ac show">
                                      <button ng-click="detachAccount('Facebook API')" type="button" class="btn-link save-action">Remove</button>
                                      <div class="text-field"> <a ng-if="facebook.profileUrl!==''" ng-bind="facebook.profileUrl" target="_blank" href="{{facebook.profileUrl}}"></a> </div>
                                    </div>
                                  </aside>
                                </div>
                              </li>
                              <li>
                                <!-- <div class="social-wrapper">
<div class="social-icons">
<div class="social-buttons">
<button class="btn btn-primary btn-twitter btn-sm rounded-corner m-r-5" id="twitterloginbtn" type="button"><i class="icon-twitter"></i> </button>
</div>
</div>
<aside class="overflow">
<a ng-show="twitter.profileUrl" style="float: right; width: 10%; padding: 8px 0 0 8px;" href="javascript:void(0);" title="Detach Account" ng-click="detachAccount('Twitter API')"><i class="ficon-cross"></i></a>
<div style="width:90%;" class="text-field" ng-show="twitter.profileUrl" data-error="hasError">
<input readonly type="text" uix-input="" ng-model="twitter.profileUrl" ng-init="twitter.profileUrl=''" ng-value="twitter.profileUrl" placeholder="Twitter" value="">
</div>
</aside>
</div> -->
                                <div class="social-wrapper">
                                  <div class="social-icons">
                                    <div class="social-buttons">
                                      <button type="button" id="twitterloginbtn" class="btn btn-primary btn-twitter btn-sm rounded-corner m-r-5"><i class="icon-twitter"></i> </button>
                                    </div>
                                  </div>
                                  <aside class="overflow">
                                    <div ng-if="twitter.profileUrl==''" class="addsocial-link"> <a onClick="$('#twitterloginbtn').trigger('click')" href="javascript:void(0);"> Add Your Twitter Account </a>
                                      <!-- <a ng-if="twitter.profileUrl!==''" ng-href="{{twitter.profileUrl}}" ng-bind="twitter.profileUrl"></a> -->
                                    </div>
                                    <div ng-if="twitter.profileUrl!==''" class="add-social-ac show">
                                      <button ng-click="detachAccount('Twitter API')" type="button" class="btn-link save-action">Remove</button>
                                      <div class="text-field"> <a ng-if="twitter.profileUrl!==''" ng-bind="twitter.profileUrl" target="_blank" href="{{twitter.profileUrl}}"></a> </div>
                                    </div>
                                  </aside>
                                </div>
                              </li>
                              <li>
                                <!-- <div class="social-wrapper">
<div class="social-icons">
<div class="social-buttons">
<button class="btn btn-primary btn-linkedin btn-sm rounded-corner m-r-5" onClick="in_obj.InLogin();" id="linkedinloginbtn" type="button"><i class="icon-linkedin"></i> </button>
</div>
</div>
<aside class="overflow">
<a ng-show="linkedin.profileUrl" style="float: right; width: 10%; padding: 8px 0 0 8px;" href="javascript:void(0);" title="Detach Account" ng-click="detachAccount('LinkedIN API')"><i class="ficon-cross"></i></a>
<div style="width:90%;" class="text-field" ng-show="linkedin.profileUrl" data-error="hasError">
<input readonly type="text" uix-input="" ng-model="linkedin.profileUrl" ng-init="linkedin.profileUrl=''" ng-value="linkedin.profileUrl" placeholder="Linkedin" value="">
</div>
</aside>
</div> -->
                                <div class="social-wrapper">
                                  <div class="social-icons">
                                    <div class="social-buttons">
                                      <button type="button" onClick="in_obj.InLogin();" id="linkedinloginbtn" class="btn btn-primary btn-linkedin btn-sm rounded-corner m-r-5"><i class="icon-linkedin"></i> </button>
                                    </div>
                                  </div>
                                  <aside class="overflow">
                                    <div ng-if="linkedin.profileUrl==''" class="addsocial-link"> <a onClick="in_obj.InLogin();" href="javascript:void(0);"> Add Your Linkedin Account </a>
                                      <!-- <a ng-if="linkedin.profileUrl!==''" ng-href="{{linkedin.profileUrl}}" ng-bind="linkedin.profileUrl"></a> -->
                                    </div>
                                    <div ng-if="linkedin.profileUrl!==''" class="add-social-ac show">
                                      <button ng-click="detachAccount('LinkedIN API')" type="button" class="btn-link save-action">Remove</button>
                                      <div class="text-field">
                                        <div class="text-field"> <a ng-if="linkedin.profileUrl!==''" ng-bind="linkedin.profileUrl" target="_blank" href="{{linkedin.profileUrl}}"></a> </div>
                                      </div>
                                    </div>
                                  </aside>
                                </div>
                              </li>
                              <li>
                                <!-- <div class="social-wrapper">
<div class="social-icons">
<div class="social-buttons">
<button class="btn btn-primary btn-gplus btn-sm rounded-corner m-r-5" id="gmailsignupbtn" type="button"><i class="icon-gplus"></i> </button>
</div>
</div>
<aside class="overflow">
<a ng-show="gplus.profileUrl" style="float: right; width: 10%; padding: 8px 0 0 8px;" href="javascript:void(0);" title="Detach Account" ng-click="detachAccount('Google API')"><i class="ficon-cross"></i></a>
<div style="width:90%;" class="text-field" ng-show="gplus.profileUrl" data-error="hasError">
<input readonly type="text" uix-input="" ng-model="gplus.profileUrl" ng-init="gplus.profileUrl=''" ng-value="gplus.profileUrl" placeholder="Google +" value="">
</div>
</aside>
</div> -->
                                <div class="social-wrapper">
                                  <div class="social-icons">
                                    <div class="social-buttons">
                                      <button type="button" id="gmailsignupbtn" class="btn btn-primary btn-gplus btn-sm rounded-corner m-r-5"><i class="icon-gplus"></i> </button>
                                    </div>
                                  </div>
                                  <aside class="overflow">
                                    <div ng-if="gplus.profileUrl==''" class="addsocial-link"> <a onClick="$('#gmailsignupbtn').trigger('click');" href="javascript:void(0);"> Add Your Google+ Account </a>
                                      <!-- <a ng-if="gplus.profileUrl!==''" ng-href="{{gplus.profileUrl}}" ng-bind="gplus.profileUrl"></a> -->
                                    </div>
                                    <div ng-if="gplus.profileUrl!==''" class="add-social-ac show">
                                      <button ng-click="detachAccount('Google API')" type="button" class="btn-link save-action">Remove</button>
                                      <div class="text-field"> <a ng-if="gplus.profileUrl!==''" ng-bind="gplus.profileUrl" target="_blank" href="{{gplus.profileUrl}}"></a> </div>
                                    </div>
                                  </aside>
                                </div>
                              </li>
                            </ul>
                          </div>
                          <div class="m-r-15 pull-right">
                            <button class="m-t-20 btn btn-primary" id="update_profile" onClick="return checkstatus('allcontrolform')" type="submit"><?php echo strtoupper(lang('update'));?> <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span> </button>
                          </div>
                        </aside>
                      </div>
                    </div>
                  </form>
                </div>
                <div id="pswrd" class="tab-pane" role="tabpanel" ng-if="SetPassword==1" data-ng-controller="ResetPasswordCtrl">
                  <form id="resetPasswordForm" class="" role="form">
                    <div class="form clearfix">
                      <div class="form-group">
                        <label><?php echo lang('old_password');?></label>
                        <div class="text-field" data-error="hasError">
                          <input class="passres" type="password" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="OldPassword=''" ng-model="OldPassword" id="oldpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorOldpassword" data-requiredmessage="Please enter old password.">
                          <label class="error-block-overlay" id="errorOldpassword"></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label><?php echo lang('new_password');?></label>
                        <div class="text-field" data-error="hasError">
                          <input class="passres" type="password" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewPassword=''" ng-model="NewPassword" id="newpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorNewpassword" data-requiredmessage="Please enter new password.">
                          <label class="error-block-overlay" id="errorNewpassword"></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label><?php echo lang('confirm_password');?></label>
                        <div class="text-field" data-error="hasError">
                          <input class="passres" type="password" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewConPassword=''" ng-model="NewConPassword" id="confirmpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorConfirmpassword" data-requiredmessage="Please confirm password.">
                          <label class="error-block-overlay" id="errorConfirmpassword"></label>
                        </div>
                      </div>
                      <div class="pull-right"> <a onclick="$('.secondary-tabs.small-screen-tabs li').removeClass('active'); $('.secondary-tabs.small-screen-tabs li:eq(0)').addClass('active'); passErrorRemove();" data-toggle="tab" role="tab" aria-controls="basic-info" href="#basic-info" aria-expanded="true" class="btn-link"><?php echo lang('cancel');?></a>
                        <button class="btn btn-primary" id="reset_password" onClick="return checkstatus('resetPasswordForm')" ng-click="ResetPassword()" type="submit"><?php echo lang('reset');?> <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span> </button>
                      </div>
                    </div>
                  </form>
                </div>
                <div id="pswrd" class="tab-pane" role="tabpanel" ng-if="SetPassword==0" data-ng-controller="SetPasswordCtrl">
                  <form id="resetPasswordForm" class="" role="form">
                    <div class="form clearfix">
                      <div class="form-group">
                        <label><?php echo lang('old_password');?></label>
                        <div class="text-field" data-error="hasError">
                          <input class="passres" type="password" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewSetPassword=''" ng-model="NewSetPassword" id="oldpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorOldpassword" data-requiredmessage="Please enter old password.">
                          <label class="error-block-overlay" id="errorOldpassword"></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label><?php echo lang('new_password');?></label>
                        <div class="text-field" data-error="hasError">
                          <input class="passres" type="password" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewSetConPassword=''" ng-model="NewSetConPassword" id="newpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorNewpassword" data-requiredmessage="Please enter new password.">
                          <label class="error-block-overlay" id="errorNewpassword"></label>
                        </div>
                      </div>
                      <div class="pull-right"> <a onclick="$('.secondary-tabs.small-screen-tabs li').removeClass('active'); $('.secondary-tabs.small-screen-tabs li:eq(0)').addClass('active'); passErrorRemove();" data-toggle="tab" role="tab" aria-controls="basic-info" href="#basic-info" aria-expanded="true" class="btn-link"><?php echo lang('cancel');?></a>
                        <button class="btn btn-primary" id="set_password" onClick="return checkstatus('resetPasswordForm')" ng-click="SetPassword()" type="submit"><?php echo lang('change_password_btn');?> <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span> </button>
                      </div>
                    </div>
                  </form>
                </div>
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
                        <button class="btn btn-default btn-md-capture"><i class="icon-privacy"></i></button>
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
  </div>
  <!-- /.modal -->
</div>

<input type="hidden" name="UserGUID" value="<?php echo $this->session->userdata('UserGUID'); ?>" data-ng-model="UserGUID" ng-init="UserGUID='<?php echo $this->session->userdata('UserGUID'); ?>'" id="UserGUID" />
<input type="hidden" id="IsSettings" value="1" />
