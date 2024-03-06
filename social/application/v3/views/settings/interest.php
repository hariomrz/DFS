<script type="text/javascript">
    var redirect_url = "<?php echo $redirect_url ?>";
</script>
<div class="container wrapper">
    <h4 class="label-title secondary-title" ng-cloak>{{::lang.hi_caps}} <span class="secondary-title-span" ng-bind="FirstName"></span>, {{::lang.let_us_help_caps}}</h4>
</div>
<div class="interest-section">
    <div class="container">
        <div class="row">
            <div class="interest-header">
                <h5 ng-bind="::lang.select_interests"></h5>
                <p ng-bind="::lang.add_5_interest"></p>
            </div>
        </div>
        <div class="row" ng-init="getParentInterestCategory()">
            <div class="col-xs-12">
                <div class="interest-nav">
                  <ul  ng-cloak class="interest-nav-tab" 
                      data-uix-bxslider="mode: 'horizontal', pager: false, controls: true, minSlides: 1, maxSlides:10, slideWidth: 120, slideMargin:0, infiniteLoop: false, hideControlOnEnd: true">
                    <li data-ng-repeat="interest in interestsList" data-notify-when-repeat-finished>
                      <a ng-class="(interest.IsSelected=='1') ? 'active' : '' ;" ng-click="getSubCategory(interest.CategoryID)">{{interest.Name}}</a>
                    </li>
                  </ul> 
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container wrapper">
    <div class="row">
        <div class="col-sm-3 col-xs-12 pull-right">
            <div class="button-block">
              <button ng-click="save_user_info()" class="btn btn-primary btn-sm">I AM DONE</button>
            </div>

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="what-are-you">

                        <h3 ng-bind="::lang.what_are_you_here_for"></h3>
                        <ul class="your-listing">
                            <li>
                                <div class="checkbox">
                                    <input checked="checked" name="WhyYouHere[]" id="Networking" type="checkbox" value="1">
                                    <label for="Networking" ng-bind="::lang.networking"></label>
                                </div>
                            </li>
                            <li>
                                <div class="checkbox">
                                    <input checked="checked" name="WhyYouHere[]" id="talentHunting" type="checkbox" value="2">
                                    <label for="talentHunting" ng-bind="::lang.talent_hunting"></label>
                                </div>
                            </li>
                            <li>
                                <div class="checkbox">
                                    <input checked="checked" name="WhyYouHere[]" id="jobSearch" type="checkbox" value="3">
                                    <label for="jobSearch" ng-bind="::lang.job_search"></label>
                                </div>
                            </li>
                        </ul>
                        <h3 ng-bind="::lang.people_you_want_to_connect_with"></h3>
                        <label ng-bind="::lang.people_interested_in"></label>
                        <div>
                            <div class="checkbox">
                                <input ng-model="IsAllInterestChk" id="allInterests" type="checkbox" value="1">
                                <label for="allInterests" ng-bind="::lang.all_interests"></label>
                            </div>
                        </div>
                        <div class="all-interest" ng-class="(IsAllInterestChk) ? 'grey-bg' : '' ;">
                            <tags-input ng-disabled="IsAllInterestChk" ng-model="ConnectWith" key-property="CategoryID" display-property="Name" placeholder="Interests" add-from-autocomplete-only="true" replace-spaces-with-dashes="false">
                                <auto-complete source="allInterests($query)" min-length="0" load-on-focus="false" load-on-empty="true" max-results-to-show="4"></auto-complete>
                            </tags-input>
                        </div>
                        <div class="form-list">
                            <label>From</label>
                            <div>
                                <div class="checkbox">
                                    <input ng-model="IsWorldwideChk" id="worldWide" type="checkbox" value="world wide">
                                    <label for="worldWide" ng-bind="::lang.worldwide"></label>
                                </div>
                            </div>
                            <div id="readonlyinput">
                                <tags-input readonly="readonly" ng-model="intlocation" key-property="CityID" display-property="Name" add-from-autocomplete-only="true" placeholder="Location" replace-spaces-with-dashes="false">
                                </tags-input>
                            </div>
                            <div class="text-field">
                                <input placeholder="Location" ng-disabled="IsWorldwideChk" type="text" id="cities" ng-init="initCity()" />
                            </div>
                        </div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-9 col-xs-12">             
            <ul class="profiles-listing int-listing row">
                <li ng-cloak class="col-sm-6 col-md-4" ng-repeat="intlist in allInterestList" ng-click="toggleBtn(intlist)" 
                ng-class="{'active' : intlist.IsInterested=='1', 'active-off' : intlist.IsInterested!=='1'}">
                    <div class="listing-content">
                        <div class="listing-desc">
                            <figure>
                                <img err-src="<?php echo site_url() ?>assets/img/Interest-default.jpg" ng-src="{{ImageServerPath+'upload/category/220x220/'+intlist.ImageName}}" >
                            </figure>
                            <a data-toggle="tooltip" data-original-title="{{intlist.Name}}">{{intlist.Name}}</a>
                            <span class="location" ng-bind="intlist.Followers+' Followers'"></span>
                        </div>
                    </div>
                </li> 
            </ul>
        </div>
    </div>
</div>

<input type="hidden" id="IsInterestPage" value="1" />