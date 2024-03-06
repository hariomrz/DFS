<div ng-cloak class="panel m-b" ng-controller="GroupPageCtrl" id="GroupPageCtrl" ng-init="suggestedGroupList(5,'0',0)" ng-show="suggestedlist.length>0">
    <div class="panel-body no-padding">
        <div class="panel-heading">
            <h3 class="panel-title border-bottom bold" ng-bind="lang.suggested_groups"></h3>
        </div>
       
        <div class="bx-slider-fluid" ng-class="(suggestedlist.length=='1') ? 'single-slide' : '' ;">
            <ul ng-cloak class="listing-group suggested-group" data-uix-bxslider="mode:'horizontal', pager:false, controls:true, minSlides:1, maxSlides:2, slideWidth: 590, slideMargin:20, infiniteLoop: false, hideControlOnEnd: true">
                <li class="col-sm-6" id="grp{{list.GroupGUID}}" ng-repeat="list in listObj = suggestedlist|limitTo:3" ng-cloak data-notify-when-repeat-finished>
                    <div class="p-h">
                        <div class="list-items-xmd">
                            <div class="list-inner">
                                <figure>
                                    <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}"> <img ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  > </a>
                                </figure>
                                <div class="list-item-body m-b-sm">
                                    <a target="_self" class="list-heading-sm text-black ellipsis" ng-bind="list.GroupName"></a>
                                    <ul class="list-activites list-icons-arrow">
                                        <li ng-if="list.Category" ng-bind="list.Category.Name"><a></a></li>
                                        <li ng-if="list.Category.SubCategory.length>0" ng-bind="list.Category.SubCategory.Name"><a></a></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="ellipsis" ng-bind="list.GroupDescription"></p>
                            <ul class="list-activites">
                                <li>
                                    <span ng-if='list.Members.length>0' ng-repeat='member in list.Members | limitTo:3'>
                                     {{member.FirstName}} {{$last ? '' : ($index==list.Members.length-1) ? '' : ', '}}
                                     </span> <span ng-if='list.Members.length>3'> & {{ (list.MemberCount-list.Members.length)>0?list.MemberCount-list.Members.length:'' }} 
                                     <span ng-if="list.MemberCount-list.Members.length>0">
                                     {{ (list.MemberCount-list.Members.length)>1?'Members':'Member' }} 
                                     </span>
                                </li>
                            </ul>
                            <div class="listing-footer">
                                <div class="btn-toolbar btn-toolbar-xs left">
                                    <button ng-cloak ng-if="list.IsJoined!='1'" href="javascript:void(0);" ng-click="joinPublicGroup(list.GroupGUID,'discover')" class="btn btn-default btn-xs" ng-bind="lang.join"></button>
                                    <button ng-cloak ng-if="list.IsJoined=='1'" href="javascript:void(0);" ng-click="groupDropOutAction(list.GroupGUID,'discover')" class="btn btn-default btn-xs" ng-bind="lang.w_leave"></button>
                                </div>
                                <ul class="pull-right list-icons">
                                    <li>
                                        <span ng-if="list.IsPublic==1" class="icon group-type" tooltip data-placement="top" title="Public">
                                           <i class="ficon-globe"></i>
                                        </span>
                                        <span ng-if="list.IsPublic==0" class="icon group-type" tooltip data-placement="top" title="Closed">
                                           <i class="ficon-close f-lg"></i>
                                        </span>
                                        <span ng-if="list.IsPublic==2" class="icon group-type" tooltip data-placement="top" title="Secret">
                                            <i class="ficon-secrets f-lg"></i>
                                        </span>
                                    </li>
                                    <li>
                                        <span class="icon group-activity-lavel" ng-class="list.Popularity=='High'?'heigh':'moderate'" tooltip data-placement="top" title="Activity Level : {{::list.Popularity}}">
                                              <svg width="14px" height="14px"  class="svg-icons no-hover">
                                                 <use xlink:href="{{SiteURL+'assets/img/sprite.svg#iconGrouppactivity'}}"></use>
                                              </svg>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
       
    </div>
</div>