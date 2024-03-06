<div class="container-fluid" id="MyAccountCtrl" ng-controller="teachManProfCtrl">
    <span ng-init="getDefaultLocation();"></span>
    <div class="row full-page-registration" ng-init="initGoogleLocation();">
        <div class="col-md-5 col-lg-3 vertical-banner-section hidden-xs hidden-sm">
            <div class="vertical-middle-pseudo">
                <div class="vertical-middle-section">
                    <div class="vertical-banner-content" ng-cloak>
                        <h3 class="list-heading-xxlg" ng-bind="::lang.tell_us_about"></h3>
                        <p ng-bind="::lang.tell_us_p1" ng-cloak></p>
                    </div>
                </div>
            </div>
            <div class="vertical-banner-footer" ng-cloak>
                © <script>document.write(new Date().getFullYear())</script> {{::lang.web_name}}. All rights reserved
            </div>
            <div class="vertical-banner-bg"> </div>
        </div>
        <div class="col-md-7 col-lg-9 login-section">
            <div class="login-block vertical-middle-pseudo">
                <div class="vertical-middle-section">
                    <h2 class="login-block-title" ng-bind="'Hi '+FirstName+' '+LastName+','"></h2>
                    <p class="text-muted">Please tell us a little bit more about you.</p>
                    <form id="setupProfile" name="SetupProfile" ng-submit="SetupProfile.$valid && submitAboutMe('', SetupProfile);" novalidate>
                        <input type="hidden" ng-model="Username" />
                        <input type="hidden" ng-model="Email" />
                        <div class="form-group relative input-group" ng-class="((SetupProfile.$submitted && SetupProfile.DOB.$invalid) || !DOB && !SetupProfile.DOB.$pristine) ? 'has-error' : '' ;">
                            <input type="text" readonly class="form-control floated-label-control datepicker" required ng-model="DOB" type="text" id="datepicker_signup" name="DOB" on-focus>
                            <label class="control-label">Date of Birth</label>
                            <label class="input-group-addon addon-white" for="dob">
                                <i class="ficon-calc f-lg"></i>
                            </label>
                            <span class="block-error" ng-bind="::lang.dob_required"></span>
                        </div>
                        <div class="form-group relative input-group" ng-class="((SetupProfile.$submitted && SetupProfile.location.$invalid) || !LocationTmpl && !SetupProfile.location.$pristine || locationRequiredMessage) ? 'has-error' : '' ;">
                            <input type="text" class="form-control floated-label-control" required id="address" name="location"
                                   ng-model="LocationTmpl" on-focus onfocus="this.placeholder = ''"
                                   onblur="this.placeholder = ''" >
                            <label class="control-label">Location</label>
                            <span class="input-group-addon addon-white">
                              <i class="ficon-location f-lg"></i>
                            </span>
                            <span class="block-error" ng-bind="::lang.location_required"></span>
                            <input type="hidden" ng-model="City" />
                            <input type="hidden" ng-model="State" />
                            <input type="hidden" ng-model="Country" />
                        </div>
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group relative" ng-class="((SetupProfile.$submitted && SetupProfile.Gender.$invalid) || !Gender && !SetupProfile.Gender.$pristine) ? 'has-error' : '' ;">
                                    <label class="floated-label-option">
                                        <input ng-model="Gender" ng-value="1" type="radio" name="Gender" id="male" ng-checked="(Gender == 1) ? 'checked' : '' ;" required>
                                        <div class="input-base">
                                            <span class="input-base-label">Gender</span>
                                            <span class="input-text">
                                                <i class="ficon-check check f-lg"></i>Male
                                            </span>
                                        </div>
                                    </label>
                                    <span class="block-error" ng-bind="lang.gender_required"></span>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group relative">
                                    <label class="floated-label-option">
                                        <input ng-model="Gender" ng-value="2" type="radio" name="Gender" id="fmale" ng-checked="(Gender == 2) ? 'checked' : '' ;" required>
                                        <div class="input-base">
                                            <span class="input-text"><i class="ficon-check check f-lg"></i>Female</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-help-links">
                            <input type="hidden" id="ProfileSetup" value="1" />
                            <button class="btn btn-primary btn-lg btn-block uppercase">
                                Alright! What's Next?
                                <span class="loader" ng-cloak ng-if="SubmitFormPostLoader"> &nbsp; </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="isuserprofile" value="1" />
<?php if(isset($location) && $location){ ?>
<input type="hidden" id="isUserLocationSet" value="1" />
<input type="hidden" id="userCity" value="<?php echo $City ?>" />
<input type="hidden" id="userState" value="<?php echo $State ?>" />
<input type="hidden" id="userCountry" value="<?php echo $Country ?>" />
<input type="hidden" id="userCountryCode" value="<?php echo $CountryCode ?>" />
<input type="hidden" id="userLat" value="<?php echo $Lat ?>" />
<input type="hidden" id="userLng" value="<?php echo $Lng ?>" />
<?php }else{ ?>
    <input type="hidden" id="isUserLocationSet" value="0" />
<?php } ?>