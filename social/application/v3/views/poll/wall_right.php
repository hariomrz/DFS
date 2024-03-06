<!--<aside class="col-md-3 col-sm-4" >-->
    <div class="panel panel-widget aboutClose" data-ng-init="get_polls_about_to_close();" ng-show="polls_about_to_close.length > 0" ng-cloak>
        <div class="panel-heading ">
            <h3 class="panel-title">About to close <a class="arrow-wrap collapsed pull-right visible-xs" href="#collapseaboutClose" data-toggle="collapse" aria-expanded="false" aria-controls="collapseaboutClose"><i class="icon-arrow-ac"></i></a></h3>
        </div>
        <div class="panel-body overflowh collapse no-padding" id="collapseaboutClose">
            <aside class="pollSlide" ng-class="!is_sidebar_option?'':'onSlide'">
                <ul class="list-items-group list-items-borderd slideContent ">
                    <li ng-repeat="polls in polls_about_to_close"  ng-cloak="">
                        <div class="list-items-xs">
                            <div class="actions">                                                                    
                                <a href="javascript:void(0);"  data-ng-click="remove_poll_sidebar($index);"><i class="ficon-cross"></i></a>                                  
                            </div>
                            <div class="list-item-body">
                                <div class="poll-vote">

                                    <a class="name slideAction text-primary" href="javascript:void(0);" data-ng-click="show_poll_option_sidebar(polls);" ng-bind-html="textToLink(polls.PostContent);"></a>
                                    <span class="text-off">by</span>
                                    <a ng-if="polls.PostAsEntityOwner !== '1'" ng-cloak class="loadbusinesscard text-link" entitytype="{{polls.EntityModuleType}}" entityguid="{{polls.EntityGUID}}" href="<?php echo base_url(); ?>{{polls.EntityProfileURL}}" ng-bind="polls.PollData[0].CreatedBy.Name"></a>
                                    <a ng-if="polls.PostAsEntityOwner == '1'" ng-cloak class="loadbusinesscard text-link" entitytype="{{polls.EntityModuleType}}" entityguid="{{polls.EntityGUID}}" href="<?php echo base_url(); ?>page/{{polls.EntityProfileURL}}" ng-bind="polls.PollData[0].CreatedBy.Name"></a>

                                    <span id="countdown_about{{polls.PollData[0].PollGUID}}" class=" text-sm-off block">
                                        Closes
                                        <span class="days"></span>
                                        <span class="timeRefDays"></span>
                                        <span class="hours"></span>
                                        <span class="timeRefHours"></span>
                                    </span>
                                    <span class="location"><span ng-bind="polls.PollData[0].ExpiryDateTime | convert_poll_expiry:'about'+polls.PollData[0].PollGUID">Today</span></span>

                                </div>
                            </div> 
                        </div>
                    </li>
                </ul>
                <div class="slideContent questionList" id="pollscope_detail_{{poll_detail.PollData[0].PollGUID}}" ng-controller="PollCtrl">
                    <div class="poll-vote">
                        <ul class="list-items-group list-items-borderd">
                            <li>
                                    <a class="backSlide back-arrow"  data-ng-click="show_poll_option_sidebar();"><span class="icon"><i class="ficon-arrow-left"></i></span> <span class="text">Back</span></a>
                                    <a class="backSlide"><i class="icon-arrow-ac" data-ng-click="show_poll_option_sidebar();"></i></a>

                                    <div class="list-items-xs"> 
                                        <div class="list-item-body">
                                            <div class="poll-vote">
                                                <a class="text-primary slideAction" href="javascript:void(0);" ng-bind-html="textToLink(poll_detail.PostContent)"></a>
                                                <span class="text-off">by</span>
                                                <a ng-if="poll_detail.PostAsModuleID == '3'" ng-cloak class="loadbusinesscard text-link" entitytype="{{poll_detail.EntityModuleType}}" entityguid="{{poll_detail.EntityGUID}}" href="<?php echo base_url(); ?>{{poll_detail.EntityProfileURL}}" ng-bind="poll_detail.PollData[0].CreatedBy.Name""></a>
                                                <a ng-if="poll_detail.PostAsModuleID == '18'" ng-cloak class="loadbusinesscard text-link" entitytype="{{poll_detail.EntityModuleType}}" entityguid="{{poll_detail.EntityGUID}}" href="<?php echo base_url(); ?>page/{{poll_detail.EntityProfileURL}}" ng-bind="poll_detail.PollData[0].CreatedBy.Name""></a>

                                                <span id="countdown_detail{{poll_detail.PollData[0].PollGUID}}" class="text-sm-off block">
                                                    Closes
                                                    <span class="days"></span>
                                                    <span class="timeRefDays"></span>
                                                    <span class="hours"></span>
                                                    <span class="timeRefHours"></span>
                                                </span>
                                                <span class="location"><span ng-bind="poll_detail.PollData[0].ExpiryDateTime | convert_poll_expiry:'detail'+poll_detail.PollData[0].PollGUID">Today</span></span>
                                            </div>
                                        </div>
                                    </div>

                                <div class="queVote" id="detail_{{poll_detail.PollData[0].PollGUID}}">
                                    <ul class="poll-que-description" ng-if="poll_detail.PollData[0].IsVoted == 0">
                                        <li ng-repeat="option in poll_detail.PollData[0].Options| filter : getPercentage" >
                                            <div class="img-count" ng-if="option.Media.length > 0">
                                                <span class="count" ng-bind="option.Media.length"></span>
                                                <span id="lg-{{option.OptionGUID}}">
                                                    <svg height="14px" width="14px" class="svg-icons" ng-if="$index == 0" ng-init="callLightGallery(option.OptionGUID)" ng-if="option.Media.length > 0" ng-repeat="media in option.Media"  ng-data-src="{{ImageServerPath + 'upload/poll/' + media.ImageName}}">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#sm-imageIcn'}}"></use>
                                                    </svg>
                                                    <span class="hide" ng-init="callLightGallery(option.OptionGUID)"  ng-if="$index > 0" ng-data-src="{{ImageServerPath + 'upload/poll/' + media.ImageName}}" ng-if="option.Media.length > 0" ng-repeat="media in option.Media">
                                                        <img width="48" height="48" class="img-rounded"   ng-src="{{ImageServerPath + 'upload/poll/220x220/' + media.ImageName}}" />
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="progress" >
                                                <div class="radio">
                                                    <input id="{{option.OptionGUID}}detailradio" type="radio" name="vote" id="" ng-model="OptionGUID" value="{{option.OptionGUID}}">
                                                    <label for="{{option.OptionGUID}}detailradio" ng-bind="option.Value"></label>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <ul class="poll-que-description" ng-if="poll_detail.PollData[0].IsVoted == 1">
                                        <li ng-repeat="option in poll_detail.PollData[0].Options| filter : getPercentage" class="checked voted">
                                            <div class="img-count" ng-init="callLightGallery(option.OptionGUID)" ng-if="option.Media.length > 0">
                                                <span class="count" ng-bind="option.Media.length"></span>
                                                <span id="lg-{{option.OptionGUID}}">
                                                    <svg height="14px" width="14px" class="svg-icons" ng-if="$index == 0" ng-if="option.Media.length > 0" ng-repeat="media in option.Media"  ng-data-src="{{ImageServerPath + 'upload/poll/220x220/' + media.ImageName}}">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#sm-imageIcn"></use>
                                                    </svg>
                                                    <span class="hide" ng-init="callLightGallery(option.OptionGUID)"  ng-if="$index > 0" ng-data-src="{{ImageServerPath + 'upload/poll/220x220/' + media.ImageName}}" ng-if="option.Media.length > 0" ng-repeat="media in option.Media">
                                                        <img width="48" height="48" class="img-rounded"   ng-src="{{ImageServerPath + 'upload/poll/220x220/' + media.ImageName}}" />
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="progress" >
                                                <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{{option.Percentage}}"  aria-valuemin="0" aria-valuemax="100" style="width:{{option.Percentage}}%">
                                                </div>
                                                <span class="progress-info" >
                                                    <span class="percent-txt" ng-if="option.NoOfVotes > 0" ng-bind="option.Percentage + '%'"></span> <span ng-bind="option.Value"></span>
                                                </span>
                                                <span class="vote-count" ng-if="poll_detail.PollData[0].Options.Members.length == 0"><span ng-bind="poll_detail.PollData[0].Options.Members.length"></span> voted</span>
                                            </div>
                                        </li>
                                    </ul>
                                    <button ng-if="poll_detail.PollData[0].IsVoted == 0" class="btn btn-xs btn-primary minW-56 m-t-10" type="button" data-ng-click="vote($event, poll_detail.PollData[0].ActivityGUID, 'detail_' + poll_detail.PollData[0].PollGUID)">VOTE</button>
                                    <a ng-if="poll_detail.PostAsModuleID == '3'" ng-cloak class="pull-right m-t-10 font-medium" href="<?php echo base_url(); ?>{{poll_detail.EntityProfileURL}}/activity/{{poll_detail.ActivityGUID}}">View Detail</a>
                                    <a ng-if="poll_detail.PostAsModuleID == '18'" ng-cloak class="pull-right m-t-10 font-medium" href="<?php echo base_url(); ?>page/{{poll_detail.EntityProfileURL}}/activity/{{poll_detail.ActivityGUID}}">View Detail</a>
                                </div>

                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
<!--</aside>-->

<div ng-show="polls_about_to_close.length == 0" ng-cloak>
    <?php 
        if(!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
            $this->load->view('widgets/people-you-may-know'); 
        } else {
            $this->load->view('widgets/people-you-may-follow'); 
        }    
    ?>
</div>


<input type="hidden" id="poll-wall-right-hidden" />