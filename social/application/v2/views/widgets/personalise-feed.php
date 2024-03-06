<div ng-hide="postEditormode" ng-cloak class="personalise panel panel-default" ng-show="IsReminder==0">
          <div class="panel-heading p-heading">
              <h3 class="collapsed" data-toggle="collapse" href="#collapsePersonalise" aria-expanded="false" aria-controls="collapsePersonalise">{{::lang.w_personalise_feed}} 
                   <span class="arrow-acc icon">
                      <i class="ficon-arrow-left-sml f-lg"></i>
                  </span>   
               </h3>
          </div>
          <div class="panel-body collapse" id="collapsePersonalise" ng-init="getFeedSetting()">
              <div class="muteByfeed">
                  <h6 ng-bind="lang.w_mute_by_feed_type"></h6>
                  <ul class="feed-setting-list">
                    <li>
                        <span ng-bind="lang.w_group_feed+' '"></span>
                        <div class="toggle-checkbox pull-right">
                            <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('g')" ng-checked="settingEnabled(newsFeedSetting.g)" >
                            <label for=""></label>
                        </div>
                    </li>
                    <li>
                        <span ng-bind="lang.w_event_feed+' '"></span>
                        <div class="toggle-checkbox pull-right">
                            <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('e')" ng-checked="settingEnabled(newsFeedSetting.e)">
                            <label for=""></label>
                        </div>
                    </li>
                    <li>
                        <span ng-bind="lang.w_page_feed+' '"></span>
                        <div class="toggle-checkbox pull-right">
                            <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('p')" ng-checked="settingEnabled(newsFeedSetting.p)">
                            <label for=""></label>
                        </div>
                    </li>
                    <li>
                        <span ng-bind="lang.w_media_posts+' '"></span>
                        <div class="toggle-checkbox pull-right">
                            <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('m')" ng-checked="settingEnabled(newsFeedSetting.m)">
                            <label for=""></label>
                        </div>
                    </li>
                     <li>
                        <span ng-bind="lang.w_rating_n_reviews+' '"></span>
                        <div class="toggle-checkbox pull-right">
                            <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('r')" ng-checked="settingEnabled(newsFeedSetting.r)">
                            <label for=""></label>
                        </div>
                    </li>
                    <!-- <li>
                        <span>Suggestions </span>
                        <div class="toggle-checkbox pull-right">
                            <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('s')" ng-checked="settingEnabled(newsFeedSetting.s)">
                            <label for=""></label>
                        </div>
                    </li>  -->
                    <li class="prioritize-Source">
                        <a target="_self" class="semi-bold" href="<?php echo site_url('myaccount/personalize')?>">{{::lang.w_prioritize_by_source}} <i class="icon-n-arrow-f pull-right">&nbsp;</i> </a>
                        <a target="_self" class="semi-bold" href="<?php echo site_url('myaccount/personalize')?>">{{::lang.w_mute_unmute_sources}} <i class="icon-n-arrow-f pull-right">&nbsp;</i></a>
                    </li>
                  </ul>
              </div>
          </div>
      </div>