<div class="container wrapper" ng-controller="PrivacyCtrl">
    <div class="row">
        <!-- Right Wall-->
        <?php $this->load->view('settings/sidebar') ?>
        <!-- //Right Wall-->
    <!-- Left Wall-->

    <aside class="col-sm-8 col-xs-12" ng-cloak>
        <div class="panel panel-default fadeInDown">
            <div class="panel-heading notification-header  border-bottom">
                <h3 class="panel-title" ng-bind="::lang.personalize_newsfeed"></h3> 
            </div>
            <!-- Personalize feed start -->
            <div class="personalise panel panel-transparent" ng-init="getFeedSetting()">
                <div class="panel-body" id="collapsePersonalise">
                    <div class="muteByfeed">
                        <h6 ng-bind="::lang.w_mute_by_feed_type"></h6>
                        <ul class="feed-setting-list">
                            <?php if (!$this->settings_model->isDisabled(1)): // If group module is enabled ?>
                            <li>
                                <span ng-bind="::lang.w_group_feed+' '"></span>
                                <div class="toggle-checkbox pull-right">
                                    <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('g')" ng-checked="settingEnabled(newsFeedSetting.g)">
                                    <label for=""></label>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!$this->settings_model->isDisabled(14)): // If event module is enabled ?>
                            <li>
                                <span ng-bind="::lang.w_event_feed+' '"></span>
                                <div class="toggle-checkbox pull-right">
                                    <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('e')" ng-checked="settingEnabled(newsFeedSetting.e)">
                                    <label for=""></label>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!$this->settings_model->isDisabled(18)): // If page module is enabled ?>
                            <li>
                                <span ng-bind="::lang.w_page_feed+' '"></span>
                                <div class="toggle-checkbox pull-right">
                                    <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('p')" ng-checked="settingEnabled(newsFeedSetting.p)">
                                    <label for=""></label>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <li>
                                <span ng-bind="::lang.w_media_posts+' '"></span>
                                <div class="toggle-checkbox pull-right">
                                    <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('m')" ng-checked="settingEnabled(newsFeedSetting.m)">
                                    <label for=""></label>
                                </div>
                            </li>
                            <li>
                                <span ng-bind="::lang.w_rating_n_reviews+' '"></span>
                                <div class="toggle-checkbox pull-right">
                                    <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('r')" ng-checked="settingEnabled(newsFeedSetting.r)">
                                    <label for=""></label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Personalize feed ends -->
            <div class="panel-body"> 
                <div class="select-all-header add-source">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="control-label" ng-bind="::lang.popular_sources"></label>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-xs-5 text-right">
                                    <label class="control-label" ng-bind="::lang.add_source"></label>
                                </div>
                                <div class="col-xs-7">
                                     <div class="input-group add-search-field">
                                        <input type="text" id="PrioritySource" autocompletedir name="Add a source" class="form-control" placeholder="{{getPersonalizeSearchPlaceHolder()}}">
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="source-view-section" ng-init="getUserPrioritizeSources()" ng-cloak=""> 
                    
                        <ul class="list-items-group list-items-column list-group-inline row">
                            <li class="col-sm-6" ng-if="UserPrioritizeSorces.length" ng-repeat="Source in UserPrioritizeSorces track by $index" repeat-done="PrioritizeRepeatDone()">
                                <div class="list-items-sm">
                                    <div class="actions">
                                        <a class="btn btn-xs btn-default" ng-click="unPrioritizeSources(Source,$index)" title="Unprioritize" data-toggle="tooltip"><span class="icon"><i class="ficon-cross"></i></span></a>
                                    </div>
                                    <div class="list-inner">
                                        <figure>
                                            <a href="javascript:void(0);" ng-href="<?php echo site_url();?>{{Source.ProfileURL}}">
                                                <img ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{Source.ProfilePicture}}" class="img-circle"  >
                                            </a>
                                        </figure>
                                        <div class="list-item-body">
                                            <h4 class="list-heading-xs"><a ng-href="<?php echo site_url();?>{{Source.ProfileURL}}" ng-bind="Source.Name"></a></h4>
                                            <?php //User case ?>
                                            <div ng-if="Source.ModuleID==3"><small ng-bind="Source.Location.Location"></small></div>
                                            <?php //Group case OR Page case ?>
                                            <div ng-if="Source.ModuleID==1 || Source.ModuleID==18"><small ng-bind="Source.Category"></small></div>
                                            <?php //Events case ?>
                                            <div ng-if="Source.ModuleID==14"><small ng-bind="ConverAndFormatTime(Source.DateTime)"></small></div>
                                        </div>
                                    </div>
                                </div>
                            </li>                          
                        </ul>   

                     <div class="load-more">
                        <button type="button"  ng-if="ShowPrioritizeLoadMore" ng-click="getUserPrioritizeSources()" class="btn  btn-link">{{::lang.load_more}} <span><i class="caret"></i></span></button>
                     </div>

                </div> 
                <div class="select-all-header add-source" id='MuteSorces' ng-init="getUserMuteSources();" style="display:none">
                    <div class="row">
                        <div class="col-sm-5">
                            <label class="add-title" ng-bind="::lang.unmute_source"></label>
                        </div>
                        <div class="col-sm-7">
                             <div class="pull-left add-title" ng-bind="::lang.search"></div>
                             <div class="input-group add-search-field pull-right">
                                 <input type="text" ng-keyup="getUserMuteSources(true)" ng-model="MuteSourcesString" name="Add a source" class="form-control" placeholder="{{getPersonalizeSearchPlaceHolder()}}">
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="source-view-section" ng-show="UserMuteSorces.length"> 
                        <ul class="list-group row">
                            <li class="col-sm-6" ng-if="UserMuteSorces.length" ng-repeat="Mutes in UserMuteSorces track by $index">
                                <figure>
                                    <a href="javascript:void(0);">
                                        <img ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{Mutes.ProfilePicture}}" class="img-circle"  >
                                    </a>
                                </figure>
                                <div class="description">
                                    <a class="name" ng-href="<?php echo site_url();?>{{Mutes.ProfileURL}}" ng-bind="Mutes.Name"></a>
                                    <?php //User case ?>
                                    <span class="location" ng-if="Mutes.ModuleID==3" ng-bind="Mutes.Location.Location"></span>
                                    <?php //Group case OR Page case ?>
                                    <span class="location" ng-if="Mutes.ModuleID==1 || Mutes.ModuleID==18" ng-bind="Mutes.Category"></span>
                                    <?php //Events case ?>
                                    <span class="location" ng-if="Mutes.ModuleID==14" ng-bind="ConverAndFormatTime(Mutes.DateTime)"></span>
                                </div>
                                <button class="remove btn btn-default btn-sm" ng-click="unMuteSources(Mutes,$index)">{{::lang.unmute}}</button>
                            </li>                               
                        </ul>     
                        <div class="load-more">
                            <button type="button" ng-if="ShowMuteLoadMore" ng-click="getUserMuteSources()" class="btn  btn-link">{{::lang.load_more}} <span><i class="caret"></i></span></button>
                        </div>             
                </div> 
              </div>

        </div>
    </aside>
    <!-- //Left Wall-->
   </div>
</div>