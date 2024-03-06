<!-- Chart popup starts -->
<div class="modal fade invitedPeopleModal" id="invitedPeopleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
          <h4 class="modal-title" id="myModalLabel3"><span ng-bind="invitedPeopleModal.length"></span> People <span ng-bind="invitedType"></span></h4>
      </div>
      <div class="modal-body padd-l-r-0 non-footer">
        <div class="designer-scroll mCustomScrollbar">
            <ul class="list-group awaitinglist">
                <li class="list-group-item" ng-repeat="ipm in invitedPeopleModal">
                  <figure>
                    <a ng-href="{{SiteURL+ipm.ProfileURL}}">
                      <img   class="img-circle"  ng-if="ipm.ProfilePicture!==''"  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + ipm.ProfilePicture}}" /> 
                      <img  class="img-circle"  ng-if="ipm.ProfilePicture==''"  ng-src="{{AssetBaseUrl + 'img/profiles/user_default.jpg' }}" /> 
                    </a>
                  </figure>
                  <div class="description"> 
                      <a class="name" ng-bind="ipm.FirstName+' '+ipm.LastName"></a> 
                      <span class="location" ng-if="ipm.CityName!=='' && ipm.CountryName==''" ng-bind="ipm.CityName"></span> 
                      <span class="location" ng-if="ipm.CityName=='' && ipm.CountryName!==''" ng-bind="ipm.CountryName"></span> 
                      <span class="location" ng-if="ipm.CityName!=='' && ipm.CountryName!==''" ng-bind="ipm.CityName+', '+ipm.CountryName"></span> 
                  </div>

                  <div ng-if="ipm.IsAwaited=='1'" class="btn-group table-cell">
                    <button ng-click="remind_user(currentPollGUID,3,ipm.ModuleEntityGUID)" type="button" class="btn btn-default btn-sm"> 
                        <span class="text">Send Reminder</span> 
                    </button>
                    <!-- <small>Last Reminder on 14 April, 2016</small> -->
                  </div>
                </li>
            </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Chart popup ends -->