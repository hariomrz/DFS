<aside id="personalInfo" class="content-block-region" ng-init="getTimeZoneList();">
    <div class="title">
        <div class="editSave">
            <a class="btn btn-default btn-icon hide" title="Edit" data-ng-class="{'show': !personalInfoEdit, 'hide': personalInfoEdit}" data-ng-click="personalInfoEdit= !personalInfoEdit; initDatepicker();ChangePanelStatus('personalInfoEdit');" ng-cloak>
                <i class="ficon-pencil"></i>Edit
            </a>
            <div class="save-cancel hide" data-ng-class="{'show': personalInfoEdit, 'hide': !personalInfoEdit}" ng-cloak>
                <a class="cancelEdit btn btn-link gray-clr" title="Cancel" data-ng-click="personalInfoEdit= !personalInfoEdit; getResetValue('personalInfo');">Cancel</a>
                <input type="submit" onclick="return checkstatus('allcontrolform');" class="saveAccount btn btn-default" title="Save" value="Save" />
            </div>
        </div>
        <span class="title-text">PERSONAL INFORMATION</span>
    </div>
    
    <div on="personalInfoEdit" data-ng-switch="" class="ng-scope">
        <div class="table-content" data-ng-switch-default>
            <aside class="row">
                <aside class="col-xs-12 col-sm-12">
                    <div class="form-group">
                        <label>Name</label>
                        <div class="viewMode"> <span ng-bind="FirstName+' '+LastName"></span> </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label>Username</label>
                        <div class="viewMode"> <span ng-bind="Username"></span> </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label>Email</label>
                        <div class="viewMode"> <span ng-bind="Email"></span> </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="viewMode"> <span ng-bind="GenderValue"></span> </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Date of Birth</label>
                        <div class="viewMode"> <span ng-bind="formatDOB(DOB)"></span> </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Location</label>
                        <div class="viewMode"> <span ng-bind="Location"></span> </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Hometown</label>
                        <div class="viewMode"> <span ng-bind="HLocationEdit"></span> </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Time Zone</label>
                        <div class="viewMode"> <span ng-bind="TimeZoneText"></span> </div>
                    </div>
                </aside>
            </aside>
        </div>
        <div class="table-content hide" data-ng-switch-when="true" data-ng-class="{'show': personalInfoEdit, 'hide': !personalInfoEdit}">
            <div class="form clearfix">
                <aside class="row">
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label>Name</label>
                            <div data-error="hasError" class="text-field">
                                <input type="text" uix-input="" data-req-minlen="2" data-req-maxlen="50" maxlength="50" ng-model="$parent.FirstNameEdit" id="firsttnamefieldCtrlID" value="" data-controltype="namefield" data-mandatory="true" data-msglocation="errorFirstname" data-requiredmessage="Required" placeholder="First Name">
                                <label class="error-block-overlay" id="errorFirstname"></label>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label class="hidden-xs">&nbsp;</label>
                            <div data-error="hasError" class="text-field">
                                <input type="text" uix-input="" data-req-minlen="2" data-req-maxlen="50" maxlength="50" placeholder="Last Name" ng-model="$parent.LastNameEdit" id="lastnamefieldCtrlID" value="" data-controltype="namefield" data-mandatory="true" data-msglocation="errorLastname" data-requiredmessage="Required">
                                <label class="error-block-overlay" id="errorLastname"></label>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label>Username</label>
                            <div data-error="hasError" class="text-field">
                                <input type="text" uix-input="" data-req-minlen="2" maxlength="50" data-req-maxlen="50" placeholder="Username" ng-model="$parent.UsernameEdit" id="usernameCtrlID" value="" data-controltype="username" data-mandatory="true" data-msglocation="errorUsername" data-data-requiredmessage="Required">
                                <label class="error-block-overlay" id="errorUsername"></label>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label>Email</label>
                            <div data-error="hasError" class="text-field">
                                <input type="text" uix-input="" maxlength="50" data-req-maxlen="50" placeholder="Email" ng-model="$parent.EmailEdit" id="emailCtrlID" value="" data-controltype="email" data-mandatory="true" data-msglocation="errorEmail" data-requiredmessage="Required">
                                <label class="error-block-overlay" id="errorEmail"></label>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Gender</label>
                            <div class="text-field-select">
                                <select ng-init="genderSelect()" ng-model="$parent.GenderEdit" id="Gender" name="Gender" ng-value="$parent.Gender" data-chosen="" data-placeholder="Choose Gender">
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                    <option value="3">Other</option>
                                </select>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Date of Birth</label>
                            <div data-error="hasError" class="text-field">
                                <input ng-model="$parent.DOBEdit" type="text" readonly id="Datepicker3" name="DOB" placeholder="__/__/____" />
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6" ng-init="initDatepicker()">
                        <div class="form-group">
                            <label>Location</label>
                            <div data-error="hasError" class="text-field">
                                <input type="text" class="location-data" data-mandatory="true" autocomplete="off" uix-input="" data-req-minlen="5" maxlength="50" data-req-maxlen="50" placeholder="City,State,Country" ng-model="$parent.LocationEdit" id="address" value="" data-msglocation="errorLocation">
                                <label id="errorLocation" class="error-block-overlay"></label>
                                <input type="hidden" ng-model="$parent.CityEdit" />
                                <input type="hidden" ng-model="$parent.StateEdit" />
                                <input type="hidden" ng-model="$parent.CountryEdit" />
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label>Hometown</label>
                            <div data-error="hasError" class="text-field">
                                <input ng-model="$parent.HLocationEdit" type="text" id="hometown" data-data-requiredmessage="Required" data-msglocation="errorHometown" data-mandatory="true" data-controltype="" value="" placeholder="Hometown" uix-input="">
                                <input type="hidden" ng-model="$parent.HCity" />
                                <input type="hidden" ng-model="$parent.HState" />
                                <input type="hidden" ng-model="$parent.HCountry" />
                                <label id="errorHometown" class="error-block-overlay"></label>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label>Time Zone</label>
                            <div class="text-field-select">
                                <select name="timeZone" class="start-year" data-chosen="" ng-model="$parent.TZoneModel" data-disable-search="false" data-placeholder="Select Timezone" ng-options="timezone.TimeZoneName for timezone in TimeZoneList track by timezone.TimeZoneID">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                    </aside>
                </aside>
            </div>
        </div>
    </div>
    <!-- ngSwitchWhen: true -->
</aside>
