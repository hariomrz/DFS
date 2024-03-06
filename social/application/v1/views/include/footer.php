<div ng-cloak ng-if="IsLoading" class="is-loading">
  <div class="loader"></div>
</div>
<!-- includes -->
<div ng-include="create_group"></div>
<!-- includes -->

<input type="hidden" id="module_entity_id" name="module_entity_id" value="<?php if(!empty($ModuleEntityID) && isset($ModuleEntityID)) {echo $ModuleEntityID ;  }?>" />
<input type="hidden" name="ModuleID" id="module_id" value="<?php if(!empty($ModuleID) && isset($ModuleID)) {echo $ModuleID ;  } else { echo 0; }?>" />

<input type="hidden" name="OldModuleID" id="old_module_id" value="<?php if(!empty($ModuleID) && isset($ModuleID)) {echo $ModuleID ;  } else { echo 0; }?>" />

<input type="hidden" name="ModuleEntityGUID" id="module_entity_guid" value="<?php if(!empty($ModuleEntityGUID) && isset($ModuleEntityGUID)) {echo $ModuleEntityGUID ;  } else { echo 0; }?>" />

<input type="hidden" name="OldModuleEntityGUID" id="old_module_entity_guid" value="<?php if(!empty($ModuleEntityGUID) && isset($ModuleEntityGUID)) {echo $ModuleEntityGUID ;  } else { echo 0; }?>" />
<?php 
  $this->load->view('media/popup');
  if($this->session->userdata('UserGUID'))
  { 
    $this->load->view('profile/profile_picture');
?>    

    <div ng-include="report_abuse_media_modal_tmplt"></div>

    <div class="modal fade" id="mediaTotalLikes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false"> 
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" onClick="$('#mediaTotalLikes').hide();" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
              <h4 class="modal-title" id="myModalLabel">LIKES (<span ng-bind="mediaTotalLikes"></span>)</h4>
            </div>
            <div class="modal-body listing-space non-footer">
              <div class="default-scroll scrollbar">
                <ul class="list-group removed-peopleslist">
                  <li ng-repeat="ld in mediaLikeDetails track by ld.UserGUID" class="list-group-item">
                    <figure>
                    <a ng-if="ld.ModuleID=='18'" ng-href="{{SiteURL+'page/'+ld.ProfileURL}}"> 
                    <a ng-if="ld.ModuleID=='3'" ng-href="{{SiteURL+ld.ProfileURL}}" >
                    <img  ng-if="ld.ProfilePicture!==''"  class="img-circle" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{ld.ProfilePicture}}" /> 
                    <img  ng-if="ld.ProfilePicture==''"  class="img-circle" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" />
                    </a>
                    </figure>
                    <div class="description">
                      <a ng-if="ld.ModuleID=='18'" ng-href="{{SiteURL+'page/'+ld.ProfileURL}}" class="name" ng-bind="ld.FirstName+ ' ' +ld.LastName"></a> 
                      <a ng-if="ld.ModuleID=='3'" ng-href="{{SiteURL+ld.ProfileURL}}" class="name" ng-bind="ld.FirstName+ ' ' +ld.LastName"></a> 
                      <span class="location" ng-if="ld.CityName!=='' && ld.CountryName!==''" ng-bind="ld.CityName+', '+ld.CountryName"></span> </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      
      <input type="hidden" id="MediaLikePageNo" value="0" />
    </div>
      <!-- Share Popup Code Starts -->
      <div ng-include="share_media_popup_tmplt"></div>
      <!-- Share Popup Code Ends -->
    <div class="modal fade" id="changeEmail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
       
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button>
              <h4 class="modal-title" id="myModalLabel">Change Email</h4>
            </div>
            <form id="changeEmailFrm" class="ng-pristine ng-valid" ng-submit="UpdateEmailData('<?php echo $this->session->userdata('UserGUID')?>');">
              <div class="modal-body">
                  <div class="form-group">
                    <label>Email</label>
                    <div data-error="hasError" class="text-field">
                      <input 
                      type="text" 
                      data-requiredmessage="Required" 
                      data-msglocation="errorUsername" 
                      data-mandatory="true" 
                      data-controltype="email"
                      id="usernameChangeEmailCtrlID" 
                      placeholder="xyz@vinfotech.com" 
                      uix-input=""
                      ng-model="UserChangeEmail">
                      <label id="errorUsername" class="error-block-overlay"></label>
                    </div>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary pull-right" id="SubmitThanksBtn" onclick="return checkstatus('changeEmailFrm');">Submit
                  <span class="btn-loader" style="display: none;">
                    <span class="spinner-btn">&nbsp;</span>
                  </span>
                </button>
              </div>
            </form>
          </div>
          
        </div>
      
    </div>

    <div class="modal fade" id="MutualFriendsPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
       
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" onClick="$('#MutualFriendsPopup').hide();" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
              <h4 class="modal-title" id="myModalLabel"><span ng-bind="'Mutual Friends With ' + MutualFriendName"></span></h4>
            </div>
            <div class="modal-body listing-space">
              <div class="default-scroll scrollbar" id="mutual_friends_popup_id_scoll">
                <ul class="list-group removed-peopleslist" >
                  <li ng-repeat="fr in MutualFriends track by fr.UserGUID" class="list-group-item">
                    <figure><a class="loadbusinesscard" entitytype="user" entityguid="{{fr.UserGUID}}" target="_self" ng-href="<?php echo site_url() ?>{{fr.ProfileURL}}"> <img   ng-if="fr.ProfilePicture!==''" class="img-circle" ng-src="{{ImageServerPath}}upload/profile/220x220/{{fr.ProfilePicture}}" /> <img   ng-if="fr.ProfilePicture==''" class="img-circle" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" /> </a></figure>
                    <div class="description"> <a class="loadbusinesscard" entitytype="user" entityguid="{{fr.UserGUID}}" target="_self" ng-href="<?php echo site_url() ?>{{fr.ProfileURL}}" class="name" ng-bind="fr.FirstName+ ' ' +fr.LastName"></a> 
                    <span class="location" ng-cloak ng-if="fr.Location && fr.Location.City !== '' && fr.Location.Country !=='' " ng-bind="fr.Location.City+', '+fr.Location.Country"></span> 
                    <span class="location" ng-cloak ng-if="fr.Location && fr.Location.City!=='' && fr.Location.Country==''" ng-bind="fr.Location.City"></span> </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
       
    </div>

    <div class="modal fade" id="GroupMembersPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
      
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" onClick="$('#GroupMembersPopup').hide();" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
              <h4 class="modal-title" id="myModalLabel"><span ng-bind="'Members'"></span></h4>
            </div>
            <div class="modal-body listing-space">
              <div class="default-scroll scrollbar">
                <ul class="list-group removed-peopleslist">
                  <li ng-repeat="fr in MutualFriends" class="list-group-item">
                    <figure><a target="_self" ng-href="<?php echo site_url() ?>{{fr.ProfileURL}}"> <img   ng-if="fr.ProfilePicture!==''" class="img-circle" ng-src="{{ImageServerPath}}upload/profile/220x220/{{fr.ProfilePicture}}" /> <img   ng-if="fr.ProfilePicture==''" class="img-circle" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg"/> </a></figure>
                    <div class="description"> <a target="_self" ng-href="<?php echo site_url() ?>{{fr.ProfileURL}}" class="name" ng-bind="fr.FirstName+ ' ' +fr.LastName"></a> </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      
    </div>

    <div class="modal fade" id="EventGuestsPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
      
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" onClick="$('#EventGuestsPopup').hide();" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
              <h4 class="modal-title" id="myModalLabel"><span ng-bind="'Guests'"></span></h4>
            </div>
            <div class="modal-body listing-space">
              <div class="default-scroll scrollbar">
                <ul class="list-group removed-peopleslist">
                  <li ng-repeat="fr in MutualFriends" class="list-group-item">
                    <figure><a target="_self" ng-href="<?php echo site_url() ?>{{fr.ProfileURL}}"> <img   ng-if="fr.ProfilePicture!==''" class="img-circle" ng-src="{{ImageServerPath}}upload/profile/220x220/{{fr.ProfilePicture}}" /> <img   ng-if="fr.ProfilePicture==''" class="img-circle" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" /> </a></figure>
                    <div class="description"> <a target="_self" ng-href="<?php echo site_url() ?>{{fr.ProfileURL}}" class="name" ng-bind="fr.FullName"></a> </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      
    </div>

    <!-- Report Abuse Desc Starts -->
    <div ng-controller="reportAbuseCtrl" class="modal fade" id="reportAbuse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
         
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" onclick="$('#commonErrorModal').html('')" class="close" data-dismiss="modal" aria-hidden="true">
                            <i class="icon-close"></i>
                        </button>
                        <h4 class="modal-title"><?php echo lang('report_abuse');?></h4>
                    </div>

                    <div class="modal-body" id="ReportAbuse">
                        <h6>Reason</h6>
                        <ul class="list-group single-lisitng">
                            <li class="list-group-item">
                                <div class="checkbox check-default">
                                    <input type="checkbox" name="flagReason" id="cbox1" class="reportAbuseDesc" value="Using Adult Content" />
                                    <label for="cbox1">Using Adult Content</label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="checkbox check-default">
                                    <input type="checkbox" name="flagReason" id="cbox2" class="reportAbuseDesc" value="Abusing Me" />
                                    <label for="cbox2">Abusive Content</label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="checkbox check-default">
                                    <input type="checkbox" name="flagReason" id="cbox3" class="reportAbuseDesc" value="Using My Details" />
                                    <label for="cbox3">Using My Details</label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="checkbox check-default">
                                    <input type="checkbox" name="flagReason" id="cbox4" class="reportAbuseDesc" value="Spam On My Profile" />
                                    <label for="cbox4">Spam On My Profile</label>
                                </div>
                            </li>
                        </ul>
                        <input type="hidden" class="flagType" value="" />
                        <input type="hidden" class="typeID" value="" />
                        <input type="hidden" class="flagModuleID" value="" />
                        <input type="hidden" class="flagIsGUID" value="" />
                    </div>
                    <div class="modal-footer">
                        <button ng-click="flagUserOrActivity()" class="btn btn-primary pull-right">Submit</button>
                    </div>
                </div>
            </div>
         
    </div>

    <div ng-include="select_banner_theme"></div>

    <input type="hidden" id="UserGUID" value="<?php echo $this->session->userdata('UserGUID')?>" />
  </div>
</div>  
<?php } ?>

</div>
<!-- Message Popup -->

<input type="hidden" value="" id="CityHidden" />
<input type="hidden" value="" id="StateHidden" />
<input type="hidden" value="" id="StateCodeHidden" />
<input type="hidden" value="" id="CountryCodeHidden" />
<input type="hidden" value="" id="CountryHidden" />
<input type="hidden" value="" id="LocationHidden" />
<?php if ($this->session->userdata('BetaInviteGUID')) { ?>
    <input type="hidden"  name="BetaInviteGuId" id="BetaInviteGuId" 
value="<?php echo $this->session->userdata('BetaInviteGUID'); ?>"/>
<?php } ?>

<!-- Message Popup open via business card Start -->
<div ng-include="MsgFormCardModal"></div>
<!--  Message Popup open via business card Ends -->

<!-- Model Popups -->
    <div ng-if="LoginSessionKey" class="modal fade" tabindex='-1' data-backdrop="static" id="AnnouncementPopup" ng-controller="UserProfileCtrl" ng-init="triggerPopup()">
      <div class="modal-dialog  announcement-popup">
          <div class="modal-content">
              <div class="modal-header white-bg">
                  <button type="button" class="close" ng-click="closePopup()" aria-label="Close">
                      <span aria-hidden="true" tooltip data-placement="top" data-container="body" data-title="Close">
                          <i class="icon-close"></i>
                      </span>
                  </button>
                  <h4 class="modal-title" id="myModalLabel" ng-bind-html="sanitizeMe(PopupTitle)"></h4>
              </div>
              <div class="modal-body">
                   <div class="content-inner">
                      <div ng-class="( IsImageData == '1' ) ? 'text-center' : 'post-content';" >
                      <!-- <div class="text-center post-content" > -->
                           <!-- <h2 ng-bind-html="PopupTitle"></h2> -->
                           <p ng-bind-html="sanitizeMe(PopupContent)" ></p>
                          
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button ng-click="skipPopup()" class="btn btn-default btn-link btn-block"> Don’t show me again. </button>
              </div>
          </div>
      </div>
  </div>

<input type="hidden" name="EditActivityGUID" id="EditActivityGUID" value="" />

<a class="btn btn-sm btn-gotop" data-back="top" >
    <span class="icon">
      <i class="ficon-arrow-up"></i>
    </span>
    <span class="text">Back to Top</span>
  </a>

<!-- Don't put html below it as it blocks rendering dom  -->
<?php $this->load->view('include/script'); ?>



</body></html>