<?php $this->load->view('profile/profile_banner') ?>
<div class="container wrapper">
    <div class="row">
        <div ng-controller="aboutCtrl" id="aboutCtrl" ng-init="getTimeZoneList();" class="col-md-9 col-sm-9 col-xs-12">
            <form ng-submit="ValidateEditAccount();" id="allcontrolform" class="" role="form" name="allcontrolform"  novalidate> 
                <!--*** basic info section begins here ***-->
                <div class="panel panel-info" data-ng-init="basicInfo='view'">
                    <div class="panel-heading">
                        <h3 class="panel-title" ng-cloak>
                            {{::lang.basic_info}} <a ng-cloak ng-if="self_profile=='1'" class="icon" data-ng-click="changePanel('basicInfo','edit')"><i class="ficon-pencil f-sm"></i></a>
                        </h3>
                    </div>
                  <div class="panel-body" ng-cloak data-ng-show="basicInfo=='edit'">
                      <div class="row">
                        <div class="col-sm-4">
                          <div class="form-group" ng-class="(!FirstNameEdit && !allcontrolform.FirstNameEdit.$pristine) ? 'has-error' : '' ;"> 
                            <label class="control-label">{{::lang.first_name}}</label>
                            <input type="text" name="FirstNameEdit" ng-model="FirstNameEdit" placeholder="{{::lang.firstname_placehoder}}" class="form-control" on-focus>
                            <span class="block-error" ng-bind="::lang.first_name_required"></span>
                          </div>
                        </div>
                        <div class="col-sm-4">
                          <div class="form-group" name="LastNameEdit" ng-class="(!LastNameEdit && !allcontrolform.LastNameEdit.$pristine) ? 'has-error' : '' ;">
                            <label class="control-label">{{::lang.last_name}}</label>
                            <input type="text" ng-model="LastNameEdit" placeholder="{{::lang.lastname_placehoder}}" class="form-control" on-focus>
                            <span class="block-error" ng-bind="::lang.last_name_required"></span>
                          </div>
                        </div>
                        <div class="col-sm-4">
                          <div class="form-group" ng-class="(!UsernameEdit && !allcontrolform.UsernameEdit.$pristine) ? 'has-error' : '' ;">
                            <label class="control-label">{{::lang.UserName}}</label>
                            <input type="text" name="UsernameEdit" ng-model="UsernameEdit" placeholder="{{::lang.username_placehoder}}" class="form-control" on-focus>
                            <span class="block-error" ng-bind="::lang.username_required"></span>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-4">
                          <div class="form-group" ng-class="(!DOBEdit && !allcontrolform.DOBEdit.$pristine) ? 'has-error' : '' ;">
                            <label class="control-label">{{::lang.date_of_birth}}</label>
                            <div class="input-group">
                              <input type="text" name="DOBEdit" ng-model="DOBEdit" placeholder="{{::lang.date_of_birth}}" class="form-control datepicker" on-focus id="dob">
                              <label class="input-group-addon addon-white" for="dob">
                                <i class="ficon-calc"></i>
                              </label>
                            </div>
                            <span class="block-error" ng-bind="::lang.dob_required"></span>
                          </div>
                        </div>
                        <div class="col-sm-4" ng-init="GenderOptions=[{val:'1',label:'Male'},{val:'2',label:'Female'},{val:'3',label:'Other'}]">
                          <div class="form-group" ng-class="(!GenderEdit && !allcontrolform.GenderEdit.$pristine) ? 'has-error' : '' ;">
                            <label class="control-label">{{::lang.gender}}</label>
                            <select ng-init="genderSelect()" ng-model="GenderEdit" id="Gender" name="GenderEdit" ng-value="Gender" ng-options="Gender.val as Gender.label for Gender in GenderOptions" class="form-control" data-chosen="" data-disable-search="true">

                            </select>
                            <span class="block-error" ng-bind="::lang.gender_required"></span>
                          </div>
                        </div>
                        <div class="col-sm-4">
                          <div class="form-group" ng-class="(!EmailEdit && !allcontrolform.EmailEdit.$pristine) ? 'has-error' : '' ;">
                            <label class="control-label">{{::lang.email_address}}</label>
                            <input type="text" name="EmailEdit" ng-model="EmailEdit" placeholder="{{::lang.email_placehoder}}" class="form-control" on-focus>
                            <span class="block-error" ng-bind="::lang.email_required"></span>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-4">
                          <div class="form-group">
                            <label class="control-label">{{::lang.location}}</label>
                            <input ng-model="LocationEdit" name="LocationEdit" type="text" id="address" placeholder="{{::lang.location}}" class="form-control" on-focus>
                            <span class="block-error" ng-bind="::lang.error_msg"></span>
                            <input type="hidden" ng-model="CityEdit" />
                            <input type="hidden" ng-model="StateEdit" />
                            <input type="hidden" ng-model="CountryEdit" />
                          </div>
                        </div>
                        <div class="col-sm-8">
                          <div class="form-group" ng-class="(TaglineEdit.length>140 && !allcontrolform.TaglineEdit.$pristine) ? 'has-error' : '' ;">
                            <label class="control-label">{{::lang.tagline}}</label>
                            <input type="text" name="TaglineEdit" ng-model="TaglineEdit" placeholder="{{::lang.tagline}}" class="form-control" on-focus>
                            <span class="block-error" ng-bind="(TaglineEdit.length>140) ? '{{::lang.tagline_max_length}}' : '{{::lang.tagline_required}}' ;"></span>
                          </div>                 
                        </div>                
                      </div>
                      <div class="row">
                        <div class="col-sm-4">
                           <div class="form-group">
                            <label class="control-label">{{::lang.hometown}}</label>
                            <input ng-model="HLocationEdit" name="HLocationEdit" type="text" id="hometown" placeholder="Hometown" class="form-control" on-focus>
                            <input type="hidden" ng-model="HCity" />
                            <input type="hidden" ng-model="HState" />
                            <input type="hidden" ng-model="HCountry" />
                            <span class="block-error"></span>
                          </div>
                        </div>
                        <div class="col-sm-4" ng-init="RelationshipOptions=[{val:'1',Relation:'Single'},{val:'2',Relation:'In a relationship'},{val:'3',Relation:'Engaged'},{val:'4',Relation:'Married'},{val:'5',Relation:'Its complicated'},{val:'6',Relation:'Separated'},{val:'7',Relation:'Divorced'}]">
                          <div class="form-group">
                            <label class="control-label">{{::lang.relationship}}</label>
                            <select
                                ng-init="martialSelect()" 
                                ng-model="MartialStatusEdit" 
                                name="MartialStatusEdit" 
                                ng-value="MartialStatus" 
                                id="MStatus" 
                                name="MaritalStatus" 
                                data-chosen="" 
                                data-disable-search="true" 
                                data-ng-change="showRelationWith();" 
                                data-placeholder="Choose Marital Status"
                                ng-options="Relationship.val as Relationship.Relation for Relationship in RelationshipOptions">
                                <option value=""></option>
                            </select>
                            <span class="block-error"></span>
                          </div>
                        </div> 
                        <div class="col-sm-4" ng-show="MartialStatusEdit!=='1' && showRelationOption==1 && MartialStatusEdit!=='0'" ng-init="InitRelationTo();">
                          <div class="form-group">
                            <label class="control-label" ng-if="RelationReferenceTxt == 0" ng-bind="lang.f_to"></label>
                            <label class="control-label" ng-if="RelationReferenceTxt == 1" ng-bind="lang.f_with"></label>
                            <input type="hidden" ng-if="RelationWithInputEdit" ng-init="setRelationValue()">
                            <input type="text" ng-model="RelationWithInputEdit" data-requiredmessage="Required" data-msglocation="errorTo" data-mandatory="false" data-controltype="relationfield" value="" id="RelationTo" class="form-control ui-autocomplete-input" placeholder="Start typing" uix-input="" />
                            <span class="block-error"></span>
                          </div>
                        </div>
                        <div class="col-sm-4" ng-show="TimeZoneList.length > 0">
                          <div class="form-group">
                            <label class="control-label">{{::lang.timezone}}</label>
                            <select name="timeZone" class="start-year" data-chosen="" ng-model="TZoneModel" data-disable-search="false" data-placeholder="{{::lang.timezone_placehoder}}" ng-options="timezone.TimeZoneName for timezone in TimeZoneList track by timezone.TimeZoneID" >
                              <option value=""></option>
                            </select>
                            <span class="block-error" ng-bind="::lang.error_msg"></span>
                          </div>
                        </div>
                      </div>
                      <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                        <input type="button" ng-click="basicInfo='view'" value="Cancel" class="btn btn-default btn-xs-size">
                        <input ng-disabled="(!FirstNameEdit || !LastNameEdit || !UsernameEdit || !GenderEdit || !EmailEdit)" type="submit" value="Save" class="btn btn-primary btn-xs-size">
                      </div>
                  </div>
                  <div class="panel-body" ng-cloak data-ng-show="basicInfo=='view'">
                    <div class="row row-inline">
                      <div class="col-md-4 col-sm-6" ng-hide="DOB=='' || DOB=='0000-00-00'">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-dob"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label" ng-bind="::lang.date_of_birth"></div>
                            <div class="item-content" ng-bind="formatDOB(DOB)"></div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4 col-sm-6" ng-hide="GenderValue=='' || GenderValue=='----'">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-gender"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label" ng-bind="::lang.gender"></div>
                            <div class="item-content" ng-bind="GenderValue"></div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4 col-sm-6" ng-hide="Email==''">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-tag"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label" ng-bind="::lang.email_address"></div>
                            <div class="item-content" ng-bind="Email"></div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4 col-sm-6" ng-hide="Location==''">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-location"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label" ng-bind="::lang.location"></div>
                            <div class="item-content" ng-bind="Location"></div>
                          </div>
                        </div>
                      </div>
                      {{' - '+HLocation+' - '}}
                      <div class="col-md-4 col-sm-6" ng-hide="HLocation==''">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-location"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label" ng-bind="::lang.hometown"></div>
                            <div class="item-content" ng-bind="HLocation"></div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4 col-sm-6">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-globe"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label" ng-bind="::lang.timezone"></div>
                            <div class="item-content" ng-bind="TimeZoneText"></div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4 col-sm-6" ng-hide="MartialStatusTxt=='' || MartialStatusTxt=='----'">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-relationship"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label" ng-bind="::lang.relationship"></div>
                            <div class="item-content">
                                <span ng-bind="MartialStatusTxt"></span> 
                                <span ng-cloak ng-if="showRelationOption">
                                    <span class="text-off" ng-if="RelationWithInput != ''" ng-bind="(RelationReferenceTxt==0)?'To':'With'"></span>
                                    <a ng-if="RelationWithURL !== ''" ng-href="{{SiteURL + RelationWithURL}}" ng-bind="RelationWithInput" target="_self"></a>
                                    <span ng-if="RelationWithURL == ''" ng-bind="RelationWithInput"></span>
                                </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-8 col-sm-6" ng-hide="ProfileURL==''">
                        <div class="item-block">
                          <div class="item-icon">
                            <i class="ficon-web-url"></i>
                          </div>
                          <div class="item-inner">
                            <div class="item-label">Web URL</div>
                            <div class="item-content" ng-bind="'<?php echo site_url() ?>'+ProfileURL"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--*** basic info section ends here ***-->

                <!--*** about section begins here ***-->
                <div ng-cloak ng-hide="self_profile=='0' && aboutme==''" class="panel panel-info" data-ng-init="addAboutItem='view'">
                  <div class="panel-heading">
                    <h3 class="panel-title">
                        About <a ng-cloak ng-if="self_profile=='1' && aboutme!=''" class="icon" data-ng-click="changePanel('addAboutItem','edit'); setVar('aboutmeEdit',aboutme);"><i class="ficon-pencil f-sm"></i></a>
                    </h3>
                  </div>
                    <div class="panel-body nodata-panel" ng-cloak data-ng-show="addAboutItem=='view' && aboutme==''">
                      <div class="nodata-text">
                        <span class="nodata-media">
                            <img src="{{AssetBaseUrl}}img/empty-img/empty-about-bio.png" >
                        </span>
                        <h5>Add about you!</h5>
                        <p class="text-off">Adding personal details to your profile is a quick and easy
                          way <br>to highlight your persona.</p>
                        <a ng-cloak ng-if="self_profile=='1'" data-ng-click="addAboutItem='edit'; changePanel('addAboutItem','edit'); setVar('aboutmeEdit',aboutme);">Describe Yourself</a>
                      </div>
                    </div>
                 
                  <div class="panel-body" ng-cloak data-ng-show="addAboutItem=='edit'">
                    <form>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="form-group">
                            <label class="control-label">Description <span class="help-block" ng-bind="500-aboutmeEdit.length">500 characters</span></label>
                            <textarea id="About" maxlength="500" ng-model="aboutmeEdit" class="form-control" placeholder="Write something about you..." on-focus rows="5"></textarea>
                            <span class="block-error" ng-bind="::lang.error_msg"></span>
                          </div>
                        </div>
                      </div>
                      <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                        <input type="button" value="Cancel" class="btn btn-default btn-xs-size" data-ng-click="addAboutItem='view'">
                        <input type="submit" value="Save" class="btn btn-primary btn-xs-size">
                      </div>
                    </form>
                  </div>
                  <div class="panel-body" ng-cloak data-ng-show="addAboutItem=='view' && aboutme!==''">
                    <p ng-bind-html="aboutme | nl2br"></p>
                  </div>
                </div>
                <!--*** about section ends here ***-->

                <!--*** Work and Education section begins here ***-->
                <div ng-cloak ng-hide="self_profile=='0' && WorkExperience.length==0 && UserEducation.length==0" class="panel panel-info" data-ng-init="workPanel='view'">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Work and Education <a ng-cloak ng-if="self_profile=='1' && (WorkExperience.length>0 || UserEducation.length>0)" class="icon" data-ng-click="changePanel('workPanel','edit')"><i class="ficon-pencil f-sm"></i></a>
                        </h3>
                    </div>                    
                    <div class="panel-body nodata-panel" ng-cloak data-ng-show="workPanel=='view' && WorkExperience.length==0 && UserEducation.length==0">
                        <div class="nodata-text">
                          <span class="nodata-media">
                                <img src="{{AssetBaseUrl}}img/empty-img/empty-work-educations.png" >
                            </span>
                          <h5 ng-bind="::lang.no_work_education"></h5>
                          <p class="text-off">{{::lang.people_could_look_for_exp}} <br>{{::lang.what_are_you_waiting_work_edu}} </p>
                          <a ng-cloak ng-if="self_profile=='1'" data-ng-click="workPanel='edit'; changePanel('workPanel','edit')" ng-bind="::lang.add_work_education"></a>
                        </div>
                    </div>
                    
                    <div class="panel-body-group" ng-cloak data-ng-show="workPanel=='view' && (WorkExperience.length>0 || UserEducation.length>0)">
                        <div class="panel-body" ng-cloak ng-if="WorkExperience.length>0">
                            <h3 class="heading-primary" ng-bind="::lang.work"></h3>
                            <ul class="list-items-group list-items-borderd list-group-15">
                                <li class="list-items-xmd" ng-repeat="WExp in WorkExperience">
                                    <div class="list-inner">
                                        <figure>
                                          <div class="default-thumb default-thumb-light">
                                            <span><i class="ficon-briefcase"></i></span>
                                          </div>
                                        </figure>
                                        <div class="list-item-body">
                                          <h4 class="list-heading-xs bold" ng-bind="WExp.Designation"></h4>
                                          <div class="text-sm-muted bold" ng-bind="WExp.OrganizationName"></div>
                                          <div>
                                            <small ng-bind="getMonthNameFromNum(WExp.StartMonth)+' '+WExp.StartYear"></small> to <small ng-if="WExp.CurrentlyWorkHere=='1'">Present</small><small ng-if="WExp.CurrentlyWorkHere=='0'" ng-bind="getMonthNameFromNum(WExp.EndMonth)+' '+WExp.EndYear"></small>
                                          </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="panel-body" ng-cloak ng-if="UserEducation.length>0">
                            <h3 class="heading-primary" ng-bind="::lang.education"></h3>
                            <ul class="list-items-group list-items-borderd list-group-15">
                                <li class="list-items-xmd" ng-repeat="Edu in UserEducation">
                                    <div class="list-inner">
                                        <figure>
                                          <div class="default-thumb default-thumb-light">
                                            <span><i class="ficon-graduation"></i></span>
                                          </div>
                                        </figure>
                                        <div class="list-item-body">
                                          <h4 class="list-heading-xs bold" ng-bind="Edu.CourseName"></h4>
                                          <div class="text-sm-muted bold" ng-bind="Edu.University"></div>
                                          <div>
                                            <small ng-bind="Edu.StartYear+' - '+Edu.EndYear"></small>
                                          </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-body-group" ng-cloak data-ng-show="workPanel=='edit'">
                        <div class="panel-body">
                          <form class="form">
                            <h3 class="heading-primary" ng-bind="::lang.work"></h3>                              
                            <div class="form-body" ng-repeat="WExp in WorkExperienceEdit">
                                <input type="hidden" ng-value="WExp.WorkExperienceGUID" name="WorkExperienceGUID[]">
                                <a ng-click="removeWorkExperience($index)" class="form-close text-link">
                                  <span class="icon">
                                    <i class="ficon-cross"></i>
                                  </span>
                                </a>
                                <div class="row">
                                  <div class="col-sm-6">
                                    <div class="form-group" ng-init="InitSectionAutocomplete('WorkExperience','Designation',WExp.Designation);">
                                      <label class="control-label" ng-bind="lang.designation"></label>
                                      <input name="Designation[]" ng-value="WExp.Designation" type="text" class="form-control Designation" placeholder="Designation">
                                      <span class="block-error">Error message</span>
                                    </div>
                                  </div>
                                  <div class="col-sm-6">
                                    <div class="form-group" ng-init="InitSectionAutocomplete('WorkExperience','OrganizationName',WExp.OrganizationName);">
                                      <label class="control-label" ng-bind="lang.company_name"></label>
                                      <input name="OrganizationName[]" ng-value="WExp.OrganizationName" type="text" class="form-control OrganizationName" placeholder="Company Name">
                                      <span class="block-error">Error message</span>
                                    </div>
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col-sm-6">
                                    <label class="control-label">From</label>
                                    <div class="row">
                                      <div class="col-sm-6">
                                        <div class="form-group">
                                            <select onChange="resetChosen(this)" name="StartMonth[]" class="start-year form-control" data-chosen="" ng-model="WExp.StartMonthObj" data-disable-search="true" data-placeholder="From Month" ng-options="month.month_name for month in monthsArr track by month.month_val" ng-if="monthsArr.length > 0">
                                                <option value=""></option>
                                            </select>
                                          <span class="block-error" ng-bind="::lang.error_msg"></span>
                                        </div>
                                      </div>
                                      <div class="col-sm-6">
                                        <div class="form-group">
                                            <select onChange="resetChosen(this)" name="StartYear[]" ng-model="WExp.StartYearObj" class="start-month form-control" data-chosen="" data-disable-search="true" data-placeholder="From Year"  ng-options="year for year in yearsArr track by year" ng-if="yearsArr.length > 0">
                                                <option value=""></option>
                                            </select>
                                          <span class="block-error" ng-bind="::lang.error_msg"></span>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-sm-6">
                                    <label class="control-label">To</label>
                                    <div class="row">
                                      <div class="col-sm-6">
                                        <div class="form-group">
                                            <select onChange="resetChosen(this)" ng-change="resetTillDate($index)" name="EndMonth[]" class="end-year form-control" ng-model="WExp.EndMonthObj" data-chosen="" data-disable-search="true" data-placeholder="To Month"  ng-options="month.month_name for month in monthsArr track by month.month_val" ng-if="monthsArr.length > 0">
                                                <option value=""></option>
                                            </select>
                                          <span class="block-error">Error message</span>
                                        </div>
                                      </div>
                                      <div class="col-sm-6">
                                        <div class="form-group">
                                            <select onChange="resetChosen(this)" ng-change="resetTillDate($index)" name="EndYear[]" class="end-month form-control" ng-model="WExp.EndYearObj" data-chosen="" data-disable-search="true" data-placeholder="To Year"  ng-options="year for year in yearsArr track by year" ng-if="yearsArr.length > 0">
                                                <option value=""></option>
                                            </select>
                                          <span class="block-error">Error message</span>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col-sm-12">
                                    <div class="form-group">
                                      <label class="checkbox">
                                        <input ng-click="resetEnd($index);" ng-checked="(WExp.CurrentlyWorkHere ==1 ? true:false)" type="checkbox" id="TillDate{{$index}}" name="TillDate[]">
                                        <span class="label">
                                          I currently work here
                                      </span>
                                      </label>
                                    </div>
                                  </div>
                                </div>                  
                            </div>         
                            <div class="row">
                                <div class="col-md-12">
                                    <a class="btn btn-link btn-link-brand btn-sm no-padding-h" data-ng-click="newItem()">
                                        <span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text"> Add more work experience</span>
                                    </a>
                                </div>
                            </div>   
                          </form>
                        </div>
                        <div class="panel-body">
                          <form class="form">
                            <h3 class="heading-primary">Education</h3>                
                            <div class="form-body" ng-repeat="Edu in UserEducationEdit">
                              <a ng-click="removeEducation($index)" class="form-close text-link">
                                <span class="icon">
                                  <i class="ficon-cross"></i>
                                </span>
                              </a>
                              <div class="row">
                                <div class="col-sm-6">
                                  <div class="form-group" ng-init="InitSectionAutocomplete('Education','CourseName',Edu.CourseName);">
                                    <label class="control-label">Course Name</label>
                                    <input ng-value="Edu.CourseName" id="coursefieldCtrlID{{$index + 1}}" name="CourseName[]" type="text" class="form-control CourseName" placeholder="Ex: Bachelors in Commerce">
                                    <span class="block-error">Error message</span>
                                  </div>
                                </div>
                                <div class="col-sm-6">
                                  <div class="form-group" ng-init="InitSectionAutocomplete('Education','University',Edu.University);">
                                    <label class="control-label">University Name</label>
                                    <input ng-value="Edu.University" id="universityfieldCtrlID{{$index + 1}}" name="University[]" type="text" class="form-control University" placeholder="Ex. RGVP">
                                    <span class="block-error">Error message</span>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-sm-6">
                                  <label class="control-label">Attended From</label>
                                  <div class="form-group">
                                    <select class="form-control" onchange="resetChosen(this)" name="EStartYear[]" ng-model="Edu.StartYearObj" data-chosen="" data-disable-search="true" data-placeholder="From Year" ng-options="year for year in yearsArr track by year" ng-if="yearsArr.length > 0">
                                        <option value=""></option>
                                    </select>
                                    <span class="block-error">Error message</span>
                                  </div>
                                </div>
                                <div class="col-sm-6">
                                  <label class="control-label">Attended To</label>
                                  <div class="form-group">
                                    <select class="form-control" onchange="resetChosen(this)" name="EEndYear[]" ng-model="Edu.EndYearObj" data-chosen="" data-disable-search="true" data-placeholder="To Year" ng-options="year for year in yearsArr track by year" ng-if="yearsArr.length > 0">
                                        <option value=""></option>
                                    </select>
                                    <span class="block-error">Error message</span>
                                  </div>
                                </div>                                  
                              </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <a class="btn btn-link btn-link-brand btn-sm no-padding-h" ng-click="newEducationItem();">
                                        <span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text"> Add more education</span>
                                    </a>
                                </div>
                            </div>
                          </form>
                            <div class="form-action">
                                <div class="row">
                                    <div class="col-sm-6">&nbsp;</div>
                                    <div class="col-sm-6">
                                        <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                                            <input type="button" value="Cancel" class="btn btn-default btn-xs-size" data-ng-click="workPanel='view'">
                                            <input type="submit" value="Save" class="btn btn-primary btn-xs-size" data-ng-click="saveProfile('workExp');">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--*** Work and Education section ends here ***-->

                <!--*** Interest section ends here ***-->
                <div ng-cloak ng-hide="SettingsData.m31==0 || (self_profile=='0' && interests.length==0)" class="panel panel-info" data-ng-init="interestPanel='view'">
                  <div class="panel-heading">
                    <h3 class="panel-title">Interest <a ng-cloak ng-if="self_profile=='1' && allInterests.length>0" class="icon" data-ng-click="changePanel('interestPanel','edit')"><i class="ficon-pencil f-sm"></i></a></h3>
                  </div>
                  
                  <div class="panel-body nodata-panel" ng-cloak data-ng-show="interestPanel=='view' && interests.length==0">
                    <div class="nodata-text">
                      <span class="nodata-media">
                            <img src="{{AssetBaseUrl}}img/empty-img/img-interest-empty.png" >
                        </span>
                      <h5>No Interest added yet!</h5>
                      <p class="text-off">People could be looking for someone with your experience.. So
                        <br>what are you waiting for Add Interest Now!!! </p>
                      <a data-ng-click="interestPanel='edit'">Add Interest</a>
                    </div>
                  </div>
                  
                  <div class="panel-body" ng-cloak data-ng-show="interestPanel=='edit'">
                    <div class="form-group">
                      <label class="control-label">Add your interest</label>
                        <div class="input-tag">
                          <tags-input ng-model="interests" min-length="2" add-from-autocomplete-only="true" ng-model="search_tags" key-property="CategoryID" display-property="Name" placeholder="Tags" replace-spaces-with-dashes="false" template="interest-tags">
                            <auto-complete source="loadSearchInterest($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="4"></auto-complete>
                          </tags-input>
                          <!-- <script type="text/ng-template" id="interestTags">
                            <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.Name">
                                <span class="tag-item-text" searchfieldid="advancedSearchKeyword" ng-bind-html="data.Name"></span>
                                <a class="ficon-cross tag-remove" ng-click="$removeTag()"></a>
                            </div>
                          </script> -->
                          <script type="text/ng-template" id="interest-tags">
                            <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.Name">
                                <span onclick="clearPopup();" data-toggle="modal" data-target="#addInterest" class="tag-item-text cursor-pointer" searchfieldid="advancedSearchKeyword" ng-bind-html="data.Name"></span>
                                <a class="ficon-cross tag-remove" ng-click="$removeTag()"></a>
                            </div>
                          </script>
                        </div>
                        <div ng-cloak ng-if="suggested_interest.length>0" class="small text-off">Here are few suggetions</div>
                      </div>

                      <ul ng-cloak ng-if="suggested_interest.length>0" class="tag-info">
                        <li class="item-tag" ng-repeat="item in suggested_interest|limitTo:5">
                          <a ng-click="addToInterestSingle(item)" class="tag-text tag-item-action">
                            <span ng-bind="item.Name" class="text"></span> 
                            <span class="icon"><i class="ficon-plus"></i></span>
                          </a>
                        </li>
                      </ul>
                    
                    <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                      <input type="button" value="Cancel" class="btn btn-default btn-xs-size" data-ng-click="interestPanel='view'; cancelInterest();">
                      <input type="button" value="Save" ng-click="save_interest()" class="btn btn-primary btn-xs-size">
                    </div>
                  </div>
                  <div class="panel-body" ng-cloak data-ng-show="interestPanel=='view' && interests.length>0">
                    <ul class="tag-info tag-info-view">
                        <li class="item-tag" ng-repeat="item in interests_saved">
                          <a class="tag-text"><span ng-bind="item.Name"></span></a>
                        </li>
                      </ul>
                  </div>
                </div>
                <!--*** Interest section ends here ***-->

                <!--*** Skills section ends here ***-->
                <div class="panel panel-info" data-ng-init="skillsPanel='edit'; getUserPendingSkills('init');" ng-hide="self_profile=='0' && UserSkillData.length==0 && !ProfileEndorse">
                  <div class="panel-heading">
                    <h3 class="panel-title">
                        <div class="panel-actions">
                          <a class="btn btn-link-brand btn-sm" data-ng-hide="skillsPanel=='pending' || skillsPanel=='manage' || UserSkillData.length==0" data-ng-click="skillsPanel='manage'" ng-cloak>Manage</a>
                          <a class="btn btn-link-brand btn-sm" data-ng-show="skillsPanel=='pending' || skillsPanel=='manage'" data-ng-click="skillsPanel='edit'" ng-cloak>Back to Skills</a>
                          <a class="btn btn-link-brand btn-sm" ng-cloak data-ng-hide="PendingSkillData.length==0 || (skillsPanel=='pending' || skillsPanel == 'add')" data-ng-click="skillsPanel='pending'" ng-cloak>Pending Skills <span class="badge-count" ng-bind="PendingSkillData.length"></span></a>
                        </div>
                        <span class="text" data-ng-hide="skillsPanel=='pending' || skillsPanel=='manage'" ng-cloak>Skills</span>
                        <span class="text" data-ng-show="skillsPanel=='manage' && self_profile=='1' && UserSkillData.length>0" ng-click="editSkillBox()" ng-cloak>Manage Skills</span>
                        <span class="text" data-ng-show="skillsPanel=='pending' && self_profile=='1'" ng-cloak>Pending Skills</span>
                    </h3>
                  </div>
                  <div class="panel-body nodata-panel" data-ng-show="skillsPanel=='add' || (skillsPanel=='edit' && UserSkillData.length==0 && !addSkills && self_profile=='1')">
                    <div class="nodata-text">
                      <span class="nodata-media">
                          <img src="{{AssetBaseUrl}}img/empty-img/empty-skills.png" >
                      </span>
                      <h5>No Skills added yet!</h5>
                      <p class="text-off">People could be looking for someone with your skills.. So what
                        <br> are you waiting for Add Skills Now!!! </p>
                      <a data-ng-click="addSkills=1;skillsPanel='edit'">Add Skills</a>
                    </div>
                  </div>

                  <div class="panel-body" ng-cloak data-ng-show="((skillsPanel=='edit' && UserSkillData.length>0) || addSkills || (self_profile=='0' && ProfileEndorse)) && skillsPanel!='manage'">
                    <div class="form-group" ng-cloak ng-if="self_profile=='1'">
                      <label class="control-label">Add Skills</label>
                      <div class="input-group">
                        <div class="input-tag tag-category">
                          <tags-input key-property="Name" replace-spaces-with-dashes="false" min-length=""
                                  class="SkillName" placeholder="<?php echo lang('What_are_your_areas_of_expertise'); ?>" data-ng-model="
                                  SkillData" display-property="Name" tabindex="2" max-tags="1" add-on-comma="true" add-on-enter="true" template="tag-template-new">
                            <auto-complete source="InitUserSkillAutocomplete($query)" template="my-custom-skill-template"></auto-complete>
                          </tags-input>
                          <script type="text/ng-template" id="tag-template-new">
                            <div class="tag-item-remove">
                              <span class="tag-item-text">
                                <span class="tag-img" ng-if="data.categoryicon">
                                  <img ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{data.CategoryIcon}}" >
                                </span>
                                <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                                <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                                <abbr ng-if="data.Name">{{data.Name}}</abbr>
                              </span>
                              <a class="ficon-cross tag-remove" ng-click="$removeTag()"></a>
                            </div>
                          </script>
                           <script type="text/ng-template" id="my-custom-skill-template">
                            <ul class="tag-list tag-category">
                              <li>
                                <div class="tag-item-remove">
                                  <span class="tag-item-text">
                                    <span class="tag-img" ng-if="data.categoryicon"><img ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{data.CategoryIcon}}" ></span>
                                    <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                                    <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                                    <abbr ng-if="data.Name">{{data.Name}}</abbr>
                                  </span>                                  
                                </div>
                              </li>
                            </ul>
                          </script>
                        </div>
                        <a class="input-group-addon brand-default text-sm" ng-click="save_skills();">ADD</a>
                      </div>
                      <span class="help-block small">Use comma or return to enter multple skills</span>
                    </div>
                    
                    <div class="form-group" ng-cloak ng-if="self_profile=='0' && ProfileEndorse" ng-init="getEndorseSkills('init');">              
                      <label class="control-label text-muted">Endorse <a ng-bind="FirstName+' '+LastName"></a></label>
                      <ul class="tag-square tag-category">
                        <li class="item-tag" ng-repeat="skills in EndorseSkills">
                          <span class="tag-text tag-item-action">
                            <span class="text">
                              <span ng-if="skills.CategoryName!==''" ng-bind="skills.CategoryName"></span> 
                              <span ng-if="skills.SubCategoryName!==''" ng-bind="skills.SubCategoryName"></span> 
                              <abbr ng-bind="skills.Name"></abbr>
                            </span>  
                            <a class="icon tag-remove" ng-click="RemoveEndorseSkill($index);"><i class="ficon-cross"></i></a>
                          </span>
                        </li>
                      </ul>
                      <div class="input-group">
                        <div class="input-tag tag-category">
                        <tags-input key-property="Name" replace-spaces-with-dashes="false" min-length="1" id="SkillName" class="SkillName" placeholder="What are your areas of expertise ?" data-ng-model="getTempEndorseSkills" display-property="Name" tabindex="2" max-tags="1" add-on-comma="true" add-on-enter="true" template="tag-template-other">
                          <auto-complete source="EndorseSkillAutocomplete($query)" template="my-custom-skill-template"></auto-complete>
                        </tags-input>
                        <script type="text/ng-template" id="tag-template-other">                         
                          <div class="tag-item-remove">
                            <span class="tag-item-text">
                              <span class="tag-img"  ng-if="data.categoryicon">
                                <img ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{data.CategoryIcon}}" >
                              </span>
                              <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                              <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                              <abbr ng-if="data.Name">{{data.Name}}</abbr>
                            </span>
                            <a class="ficon-cross tag-remove" ng-click="$removeTag()"></a>
                          </div>
                        </script>
                        <script type="text/ng-template" id="my-custom-skill-template">
                          <div class="skill autosuggest">
                          <span class="endorse-item-name">
                          <span class="catg-img" ng-if="data.categoryicon"><img ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{data.CategoryIcon}}" ></span>
                          <span ng-if="data.CategoryName">{{data.CategoryName}}</span> 
                          <span ng-if="data.SubCategoryName">{{data.SubCategoryName}}</span> 
                          <abbr ng-if="data.Name">{{data.Name}}</abbr>
                          </span>
                          </div>
                        </script>
                        </div>                
                        <a class="input-group-addon brand-default text-sm" ng-click="add_endorse_skill()">ADD</a>
                      </div>
                      <span class="help-block small">Use comma or return to enter multple skills</span>
                    </div>
                    <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs" ng-cloak ng-if="self_profile=='0' && ProfileEndorse">
                      <a class="btn btn-default btn-xs-size" ng-click="CancelEndorseSkill();">Cancel</a>
                      <a class="btn btn-primary btn-xs-size" ng-click="SaveSuggestionEndorse();">Endorse</a>
                    </div> 

                    <ul class="skills-tree" ng-init="getUserSkills('init');">
                      <li ng-repeat="userskilldata in UserSkillData">
                        <ul class="endorsed-by" ng-if="userskilldata.Endorsements.length>0">
                          <li ng-repeat="endorse_by in userskilldata.Endorsements">
                            <img ng-src="{{image_server_path+'upload/profile/220x220/'+endorse_by.ProfilePicture}}" >
                          </li>
                          <li class="more-endorsers" ng-if="userskilldata.Endorsements.length>6">
                            <a ng-click="EndorsementPopup(userskilldata.EntitySkillID, userskilldata.Name, 'init');" data-target="#endorsers" data-toggle="modal">
                              <i class="ficon-arrow-right f-xs"></i>
                            </a>
                          </li>
                        </ul>
                        <div class="endorsed-skill">
                          <div class="endorsed-wrapper">
                            <div class="endorsers-count" ng-cloak ng-if="userskilldata.TotalEndorsement>0" ng-bind="userskilldata.TotalEndorsement"></div>
                            <div class="skill-name">
                              <span ng-cloak ng-if="userskilldata.CategoryName" ng-bind="userskilldata.CategoryName"></span>
                              <span ng-cloak ng-if="userskilldata.SubCategoryName" ng-bind="userskilldata.SubCategoryName"></span>
                              <abbr ng-bind="userskilldata.Name"></abbr>
                            </div>
                          </div>
                          <a href="javascript:void(0);" ng-if="self_profile=='0' && !userskilldata.IsEndorse" ng-click="AddEndorsement(userskilldata.SkillID)" class="add-skill" data-toggle="tooltip" data-placement="top" title="Endorse"><i class="ficon-plus-thin f-xs"></i></a>
                          <a href="javascript:void(0);" ng-if="self_profile=='0' && userskilldata.IsEndorse" ng-click="DeleteEndorsement(userskilldata.SkillID)" class="add-skill" data-toggle="tooltip" data-placement="top" title="Endorse"><i class="ficon-plus-thin f-xs"></i></a>
                        </div>
                      </li>
                    </ul>          
                  </div>

                  <div class="panel-body" ng-cloak data-ng-show="skillsPanel=='manage'">
                    <ul class="skills-tree" droppable="UserSkillData">
                      <li ng-repeat="userskilldata in UserSkillData" ng-show="userskilldata.StatusID != '3'">
                        <div class="endorsed-skill">
                          <div class="endorsed-wrapper">
                            <div class="endorsers-count" ng-cloak ng-if="userskilldata.TotalEndorsement>0" ng-bind="userskilldata.TotalEndorsement"></div>
                            <div class="skill-name">
                              <span ng-cloak ng-if="userskilldata.CategoryName" ng-bind="userskilldata.CategoryName"></span>
                              <span ng-cloak ng-if="userskilldata.SubCategoryName" ng-bind="userskilldata.SubCategoryName"></span>
                              <abbr ng-bind="userskilldata.Name"></abbr>
                            </div>
                          </div>
                          <a ng-click="RemoveUserSkill(userskilldata);" class="add-skill" data-toggle="tooltip" data-placement="top" title="Remove Skill"><i class="ficon-cross f-md"></i></a>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div class="panel-footer" ng-cloak data-ng-show="skillsPanel=='manage'">
                    <a class="btn btn-link-brand btn-sm pull-left no-padding-h">
                      <span class="icon">
                        <i class="ficon-drag"></i>
                      </span>
                      <span class="text">Drag Reorder</span>
                    </a>
                    <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                      <a class="btn btn-default btn-xs-size" ng-click="editSkillBox(); skillsPanel='edit'">Cancel</a>
                      <a class="btn btn-primary btn-xs-size" ng-click="SaveManageSkill(); skillsPanel='edit'">Save</a>
                    </div>
                  </div>

                  <div class="panel-body" ng-cloak data-ng-show="skillsPanel=='pending'">
                    <label class="control-label" ng-if="TempCount > 2">

                        <span ng-repeat="Details in TempPendingArr track by $index" ng-if="$index < 2">
                            <a class="name loadbusinesscard" entityguid="{{Details.ModuleEntityGUID}}" entitytype="user"  ng-href="<?php echo base_url(); ?>{{Details.ProfileURL}}">{{Details.Name}}</a>
                            <span ng-if="$index == '0'">, </span>
                        </span>
                        <?php echo lang('and'); ?> <a class="name"><span ng-bind="{{TempCount - 2}}"></span>
                            <span ng-if="TempCount - 2 > '1'">others</span> <span ng-if="TempCount - 2 == '1'"><?php echo lang('other'); ?> </span>
                        </a><?php echo lang('have_endorsed_you_for_new_skills_and_expertise'); ?> 
                    </label>

                    <label ng-if="TempCount == 1" class="control-label">
                        <a ng-repeat="Details in TempPendingArr track by $index" class="name loadbusinesscard" entityguid="{{Details.ModuleEntityGUID}}" entitytype="user"  ng-href="<?php echo base_url(); ?>{{Details.ProfileURL}}">{{Details.Name}}</a><?php echo lang('have_endorsed_you_for_new_skills_and_expertise'); ?> 
                    </label>

                    <label ng-if="TempCount == 2" class="control-label">
                        <span ng-repeat="Details in TempPendingArr track by $index">
                            <a class="name loadbusinesscard" entityguid="{{Details.ModuleEntityGUID}}" entitytype="user"  ng-href="<?php echo base_url(); ?>{{Details.ProfileURL}}">{{Details.Name}}</a>
                            <span ng-if="$index == '0'"> <?php echo lang('and'); ?>  </span>
                        </span>
                       <?php echo lang('have_endorsed_you_for_new_skills_and_expertise'); ?> 
                    </label>

                    <ul class="skills-tree">
                      <li ng-repeat="userskilldata in PendingSkillData">
                        <ul class="endorsed-by" ng-if="userskilldata.Endorsements.length>0">
                          <li ng-repeat="endorse_by in userskilldata.Endorsements">
                            <img ng-src="{{image_server_path+'upload/profile/220x220/'+endorse_by.ProfilePicture}}" >
                          </li>
                          <li class="more-endorsers" ng-if="userskilldata.Endorsements.length>6">
                            <a ng-click="EndorsementPopup(userskilldata.EntitySkillID, userskilldata.Name, 'init');" data-target="#endorsers" data-toggle="modal">
                              <i class="ficon-arrow-right f-xs"></i>
                            </a>
                          </li>
                        </ul>
                        <div class="endorsed-skill">
                          <div class="endorsed-wrapper">
                            <div class="endorsers-count" ng-cloak ng-if="userskilldata.TotalEndorsement>0" ng-bind="userskilldata.TotalEndorsement"></div>
                            <div class="skill-name">
                              <span ng-cloak ng-if="userskilldata.CategoryName" ng-bind="userskilldata.CategoryName"></span>
                              <span ng-cloak ng-if="userskilldata.SubCategoryName" ng-bind="userskilldata.SubCategoryName"></span>
                              <abbr ng-bind="userskilldata.Name"></abbr>
                            </div>
                          </div>
                          <a ng-click="CancelPendingSkill(userskilldata.EntitySkillGUID)" class="add-skill" data-toggle="tooltip" data-placement="top" title="Remove Skill"><i class="ficon-cross f-md"></i></a>
                        </div>
                      </li>
                    </ul>             
                    <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                      <a class="btn btn-default btn-xs-size" ng-click="CancelPendingSkill('All')">Don't add to profile</a>
                      <a class="btn btn-primary btn-xs-size" ng-click="AddSkillToProfile();">Add to profile</a>
                    </div>
                  </div>
                </div>
                <!--*** Skills section ends here ***-->

                <!--*** Attach AC section start here ***-->
                <div class="panel panel-info" ng-hide="self_profile=='0' && facebookURL=='' && twitterURL=='' && linkedinURL=='' && gplusURL==''" data-ng-init="accountPanel='view'">
                  <div class="panel-heading">
                    <h3 class="panel-title">ATTACH ACCOUNTS</h3>
                  </div>
                  <div class="panel-body" ng-cloak data-ng-show="accountPanel=='view'">       
                    <ul class="row list-account">
                      <li class="col-sm-6 items" ng-cloak ng-if="self_profile=='1' || facebookURL!==''">
                        <div class="list-items-sm">
                          <a ng-cloak ng-if="facebookURL!=='' && self_profile=='1'" class="icon close" ng-click="detachAccount('Facebook API')"><i class="ficon-cross"></i></a>
                          <div class="list-inner">
                            <figure>
                              <a ng-cloak ng-if="facebookURL==''" class="btn-facebook icon" onClick="fb_obj.FbLoginStatusCheck();">
                                <i class="ficon-facebook"></i>
                              </a>
                              <a ng-cloak ng-if="facebookURL!==''" ng-href="{{facebookURL}}">
                                <img ng-src="{{facebookProfilePicture}}" class="img-circle"  >
                              </a>
                            </figure>
                            <div class="list-item-body">
                              <h4 class="list-heading-base">
                                <a class="text-link text-sm" ng-cloak ng-if="facebookURL==''" onClick="fb_obj.FbLoginStatusCheck();">
                                  <span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text">Add Facebook Account</span>
                                </a>
                                <a class="text-link text-sm" ng-cloak ng-href="{{facebookURL}}" ng-if="facebookURL!==''"><span class="text" ng-bind="facebookURL"></span></a>
                              </h4>
                            </div>
                          </div>
                        </div>
                      </li>

                      <li class="col-sm-6 items" ng-cloak ng-if="self_profile=='1' || twitterURL!==''">
                        <div class="list-items-sm">
                          <a ng-cloak ng-if="twitterURL!=='' && self_profile=='1'" class="icon close" ng-click="detachAccount('Twitter API')"><i class="ficon-cross"></i></a>
                          <div class="list-inner">
                            <figure>
                              <a ng-cloak ng-if="twitterURL==''" class="btn-twitter icon" onClick="$('#twitterloginbtn').trigger('click')">
                                <i class="ficon-twitter"></i>
                              </a>
                              <a ng-cloak ng-if="twitterURL!==''" ng-href="{{twitterURL}}" ><img ng-src="{{twitterProfilePicture}}" class="img-circle"  ></a>
                            </figure>
                            <div class="list-item-body">
                              <h4 class="list-heading-base">
                                <a id="twitterloginbtn" ng-cloak ng-if="twitterURL==''" class="text-link text-sm">
                                  <span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text">Add Twitter Account</span>
                                </a>
                                <a ng-cloak ng-if="twitterURL!==''"  ng-href="{{twitterURL}}" class="text-link text-sm"><span class="text" ng-bind="twitterURL"></span></a>
                              </h4>
                            </div>
                          </div>
                        </div>
                      </li>

                      <li class="col-sm-6 items" ng-cloak ng-if="self_profile=='1' || linkedinURL!==''">
                        <div class="list-items-sm">
                          <a ng-cloak ng-if="linkedinURL!=='' && self_profile=='1'" class="icon close" ng-click="detachAccount('LinkedIN API')"><i class="ficon-cross"></i></a>
                          <div class="list-inner">
                            <figure>
                              <a ng-cloak ng-if="linkedinURL==''" class="btn-linkedin icon" onClick="in_obj.InLogin();">
                                <i class="ficon-linkedin"></i>
                              </a>
                              <a ng-cloak ng-if="linkedinURL!==''" ng-href="{{linkedinURL}}" ><img ng-src="{{linkedinProfilePicture}}" class="img-circle"  ></a>
                            </figure>
                            <div class="list-item-body">
                              <h4 class="list-heading-base">
                                <a ng-cloak ng-if="linkedinURL==''" class="text-link text-sm" onClick="in_obj.InLogin();">
                                  <span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text">Add LinkedIn Account</span>
                                </a>
                                <a ng-cloak ng-if="linkedinURL!==''"  ng-href="{{linkedinURL}}" class="text-link text-sm"><span class="text" ng-bind="linkedinURL"></span></a>
                              </h4>
                            </div>
                          </div>
                        </div>
                      </li>

                      <li class="col-sm-6 items" ng-cloak ng-if="self_profile=='1' || gplusURL!==''">
                        <div class="list-items-sm">
                          <a ng-cloak ng-if="gplusURL!=='' && self_profile=='1'" class="icon close" ng-click="detachAccount('Google API')"><i class="ficon-cross"></i></a>
                          <div class="list-inner">
                            <figure>
                              <a ng-cloak ng-if="gplusURL==''" class="btn-gplus icon" id="gplusimage">
                                <i class="ficon-googleplus"></i>
                              </a>
                              <a ng-cloak ng-if="gplusURL!==''" ng-href="{{gplusURL}}" ><img ng-src="{{gplusProfilePicture}}" class="img-circle"  ></a>
                            </figure>
                            <div class="list-item-body">
                              <h4 class="list-heading-base">
                                <div ng-cloak ng-if="gplusURL==''" class="text-link text-sm" id="gmailsignupbtn">
                                  <a class="text-link text-sm">
                                    <span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text">Add Google+ Account</span>
                                  </a>
                                </div>
                                <a ng-cloak ng-if="gplusURL!==''"  ng-href="{{gplusURL}}" class="text-link text-sm"><span class="text" ng-bind="gplusURL"></span></a>
                              </h4>
                            </div>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <!--*** Attach AC section ends here ***-->
            </form>
            <?php $this->load->view('about/popup') ?>
        </div>

        <aside class="col-md-3 col-sm-3 col-xs-12">
        <?php $this->load->view('sidebars/right'); ?>
        </aside>
    </div>
</div>
<input type="hidden" id="UserID" value="<?php
if (isset($UserID))
{
    echo $UserID;
}
?>" />
<input type="hidden" value="1" id="UserWall">