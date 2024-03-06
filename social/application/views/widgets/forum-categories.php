<div ng-cloak class="panel panel-default" ng-if="category_detail.SubCategory.length>0">
    <div class="panel-heading p-heading">
        <h3 ng-bind="category_detail.Name"></h3>
    </div>
    <div class="panel-body p-h-sm">
        <ul class="listing-group vertical list-group-v10" ng-cloak>
            <li class="col-xs-4" ng-repeat="sub_cat in category_detail.SubCategory">
                <div class="list-items-md">
                    <div class="list-inner">
                        <figure>
                            <a target="_self" ng-href="{{BaseURL+sub_cat.FullURL}}"><img err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" src="{{ImageServerPath+'upload/profile/220x220/'+sub_cat.ProfilePicture}}" class="img-circle"  ></a>
                        </figure>
                        <div class="list-item-body">
                            <a target="_self" ng-href="{{BaseURL+sub_cat.FullURL}}" class="list-heading-base text-black ellipsis" ng-bind="sub_cat.Name"></a>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>