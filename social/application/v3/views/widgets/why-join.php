<div ng-cloak class="panel panel-default shadow-effect">
    <div class="why-join-us">
        <div class="join-heading" ng-bind="lang.w_why_join_us"></div>
        <div class="why-us-content">
            <ul class="why-us-slide" data-uix-bxslider="mode: 'horizontal', pager: false, controls: false, minSlides: 1, slideMargin:0, infiniteLoop: false, hideControlOnEnd: true">
                <li ng-repeat="(key, value) in ['1']" data-notify-when-repeat-finished ng-cloak>
                   <div class="text-view">{{lang.feature_one}}</div>
                    <div class="join-button">
                        <a target="_self" href="{{BaseUrl+'signup'}}"  class="btn btn-default btn-xs" ng-bind="lang.w_join"></a>
                    </div>
                </li>
            </ul> 
        </div>
    </div>
 </div>