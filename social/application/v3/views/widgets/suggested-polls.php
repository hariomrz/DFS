<div ng-cloak ng-if="SettingsData.m30=='1'" class="panel panel-default" id="PollCtrl3" ng-controller="PollCtrl" ng-init="get_polls_about_to_close()" ng-show="polls_about_to_close.length>0">
    <div class="panel-heading p-heading">
        <h3>{{::lang.w_polls_caps}}<a target="_self" href="<?php echo site_url('poll') ?>" class="pull-right gray-clr" ng-bind="lang.create"></a></h3>
    </div>
    <div class="panel-body overflowh" id="collapseaboutClose">
        <aside class="pollSlide" ng-class="!is_sidebar_option?'':'onSlide'">
            <ul class="list-group removed-peopleslist slideContent ">
                <li class="list-group-item" ng-repeat="polls in polls_about_to_close" ng-cloak="">
                    <div class="poll-vote">
                        <div class="description">
                            <a target="_self" class="a-link name slideAction" href="javascript:void(0);" data-ng-click="show_poll_option_sidebar(polls);" ng-bind-html="textToLink(polls.PostContent);"></a>
                            <span class="text-secondary" ng-bind="lang.w_by"></span>
                            <a target="_self" ng-if="polls.PostAsEntityOwner!=='1'" ng-cloak class="a-link loadbusinesscard" entitytype="{{polls.EntityModuleType}}" entityguid="{{polls.EntityGUID}}" href="<?php echo base_url();?>{{polls.EntityProfileURL}}" ng-bind="polls.PollData[0].CreatedBy.Name"></a>
                            <a target="_self" ng-if="polls.PostAsEntityOwner=='1'" ng-cloak class="a-link loadbusinesscard" entitytype="{{polls.EntityModuleType}}" entityguid="{{polls.EntityGUID}}" href="<?php echo base_url();?>page/{{polls.EntityProfileURL}}" ng-bind="polls.PollData[0].CreatedBy.Name"></a>
                            <span id="countdown_about{{polls.PollData[0].PollGUID}}" class="countdown">
                                    {{::lang.w_closes}}
                                    <span class="days"></span>
                            <span class="timeRefDays"></span>
                            <span class="hours"></span>
                            <span class="timeRefHours"></span>
                            </span>
                            <span class="location"><span ng-bind="polls.PollData[0].ExpiryDateTime | convert_poll_expiry:'about'+polls.PollData[0].PollGUID">Today</span></span>
                        </div>
                        <a target="_self" href="javascript:void(0);" class="remove" data-ng-click="remove_poll_sidebar($index);"><i class="ficon-cross"></i></a>
                    </div>
                </li>
            </ul>
            <div class="slideContent questionList" id="pollscope_detail_{{poll_detail.PollData[0].PollGUID}}" ng-controller="PollCtrl">
                <div class="poll-vote">
                    <aside class="poll-que">
                        <a target="_self" class="backSlide"><i class="icon-arrow-ac" data-ng-click="show_poll_option_sidebar();"></i></a>
                        <div class="description">
                            <span class="a-link name" href="javascript:void(0);" ng-bind-html="textToLink(poll_detail.PostContent)"></span>
                            <span class="text-secondary" ng-bind="lang.w_by"></span>
                            <a target="_self" ng-if="poll_detail.PostAsModuleID=='3'" ng-cloak class="a-link loadbusinesscard" entitytype="{{poll_detail.EntityModuleType}}" entityguid="{{poll_detail.EntityGUID}}" href="<?php echo base_url();?>{{poll_detail.EntityProfileURL}}" ng-bind="poll_detail.PollData[0].CreatedBy.Name" "></a>
                                <a target="_self" ng-if="poll_detail.PostAsModuleID=='18' " ng-cloak class="a-link loadbusinesscard " entitytype="{{poll_detail.EntityModuleType}} " entityguid="{{poll_detail.EntityGUID}} " href="<?php echo base_url();?>page/{{poll_detail.EntityProfileURL}}" ng-bind="poll_detail.PollData[0].CreatedBy.Name""></a>
                            <span id="countdown_detail{{poll_detail.PollData[0].PollGUID}}" class="countdown">
                                    {{::lang.w_closes}}
                                    <span class="days"></span>
                            <span class="timeRefDays"></span>
                            <span class="hours"></span>
                            <span class="timeRefHours"></span>
                            </span>
                            <span class="location"><span ng-bind="poll_detail.PollData[0].ExpiryDateTime | convert_poll_expiry:'detail'+poll_detail.PollData[0].PollGUID">Today</span></span>
                        </div>
                        <a target="_self" href="javascript:void(0);" class="remove"><i class="ficon-cross"></i></a>
                    </aside>
                    <div class="queVote" id="detail_{{poll_detail.PollData[0].PollGUID}}">
                        <ul class="poll-que-description" ng-if="poll_detail.PollData[0].IsVoted==0">
                            <li ng-repeat="option in poll_detail.PollData[0].Options | filter : getPercentage">
                                <div class="img-count" ng-if="option.Media.length>0">
                                    <span class="count" ng-bind="option.Media.length"></span>
                                    <span id="lg-{{option.OptionGUID}}">
                                            <svg height="14px" width="14px" class="svg-icons" ng-if="$index==0" ng-init="callLightGallery(option.OptionGUID)" ng-if="option.Media.length>0" ng-repeat="media in option.Media"  ng-data-src="{{ImageServerPath+'upload/poll/'+media.ImageName}}">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#sm-imageIcn'}}"></use>
                                            </svg>
                                            <span class="hide" ng-init="callLightGallery(option.OptionGUID)"  ng-if="$index>0" ng-data-src="{{ImageServerPath+'upload/poll/'+media.ImageName}}" ng-if="option.Media.length>0" ng-repeat="media in option.Media">
                                               <img width="48" height="48" class="img-rounded"   ng-src="{{ImageServerPath+'upload/poll/220x220/'+media.ImageName}}" />
                                            </span>
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="radio">
                                        <input id="{{option.OptionGUID}}detailradio" type="radio" name="vote" id="" ng-model="OptionGUID" value="{{option.OptionGUID}}">
                                        <label for="{{option.OptionGUID}}detailradio" ng-bind="option.Value"></label>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <ul class="poll-que-description" ng-if="poll_detail.PollData[0].IsVoted==1">
                            <li ng-repeat="option in poll_detail.PollData[0].Options | filter : getPercentage" class="checked voted">
                                <div class="img-count" ng-init="callLightGallery(option.OptionGUID)" ng-if="option.Media.length>0">
                                    <span class="count" ng-bind="option.Media.length"></span>
                                    <span id="lg-{{option.OptionGUID}}">
                                            <svg height="14px" width="14px" class="svg-icons" ng-if="$index==0" ng-if="option.Media.length>0" ng-repeat="media in option.Media"  ng-data-src="{{ImageServerPath+'upload/poll/220x220/'+media.ImageName}}">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#sm-imageIcn"></use>
                                            </svg>
                                            <span class="hide" ng-init="callLightGallery(option.OptionGUID)"  ng-if="$index>0" ng-data-src="{{ImageServerPath+'upload/poll/220x220/'+media.ImageName}}" ng-if="option.Media.length>0" ng-repeat="media in option.Media">
                                               <img width="48" height="48" class="img-rounded"   ng-src="{{ImageServerPath+'upload/poll/220x220/'+media.ImageName}}" />
                                            </span>
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{{option.Percentage}}" aria-valuemin="0" aria-valuemax="100" style="width:{{option.Percentage}}%">
                                    </div>
                                    <span class="progress-info">
                                            <span class="percent-txt" ng-if="option.NoOfVotes>0" ng-bind="option.Percentage+'%'"></span> <span ng-bind="option.Value"></span>
                                    </span>
                                    <span class="vote-count" ng-if="poll_detail.PollData[0].Options.Members.length==0"><span ng-bind="poll_detail.PollData[0].Options.Members.length"></span> voted</span>
                                    </span>
                                </div>
                            </li>
                        </ul>
                        <button ng-if="poll_detail.PollData[0].IsVoted==0" class="btn btn-xs btn-primary minW-56 m-t-10" type="button" data-ng-click="vote($event,poll_detail.PollData[0].ActivityGUID,'detail_'+poll_detail.PollData[0].PollGUID)" ng-bind="lang.w_vote"></button>
                        <a target="_self" ng-if="poll_detail.PostAsModuleID=='3'" ng-cloak class="a-link pull-right m-t-10 font-medium" href="<?php echo base_url();?>{{poll_detail.EntityProfileURL}}/activity/{{poll_detail.ActivityGUID}}" ng-bind="lang.w_view_detail"></a>
                        <a target="_self" ng-if="poll_detail.PostAsModuleID=='18'" ng-cloak class="a-link pull-right m-t-10 font-medium" href="<?php echo base_url();?>page/{{poll_detail.EntityProfileURL}}/activity/{{poll_detail.ActivityGUID}}" ng-bind="lang.w_view_detail"></a>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
