<div class="modal fade " id="flagModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title" id="myModalLabel3" ng-if="flagUserData.length >0"><span ng-if="flagUserData.length <=1" ng-bind=" flagUserData.length +' Flag'"> </span><span ng-if="flagUserData.length >1" ng-bind=" flagUserData.length +' Flags'"> </span> </h4>
            </div>
            <div class="modal-body padd-l-r-0 non-footer">
                <div class="designer-scroll mCustomScrollbar">
                    <ul class="list-group ">
                        <li ng-repeat="flag_user_data in flagUserData" class="list-group-item ">
                            <figure>
                                <a ng-href="{{ SiteURL + flag_user_data.ProfileURL}}">
                                        <img err-name="{{flag_user_data.FirstName+' '+flag_user_data.LastName}}"   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+flag_user_data.ProfilePicture}}">
                                    </a>
                            </figure>
                            <div class="description">
                                <a ng-href="{{SiteURL + flag_user_data.ProfileURL}}" class="name" ng-bind="flag_user_data.FirstName+' '+flag_user_data.LastName"></a>
                                <span class="location" ng-if="flag_user_data.CityName !== '' &&  flag_user_data.CountryName !== '' " ng-bind="flag_user_data.CityName+', '+flag_user_data.CountryName"></span>
                                <span class="black">Reason: <span ng-bind="flag_user_data.FlagReason"></span> </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <!--  <div class="modal-footer">                    
                    <div class="pull-right">
                        <a href="javascript:void(0)" class="btn-link">Remove</a>
                        <button class="btn  btn-primary btn-icon" type="button">Mark Clean</button>
                    </div>
                </div>-->
        </div>
    </div>
</div>