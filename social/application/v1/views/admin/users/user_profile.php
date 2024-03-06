<?php 
$default_value = '';
$user_type = 'register';
//Code for set breadcrumb
switch($user_status)
{
    case '1':
        $breadcrumb = lang("User_Index_WaitingForApproval");
        $user_type = 'waiting';
    break;

    case '2':
        $breadcrumb = lang("User_Index_RegisteredUsers");
        $user_type = 'register';
    break;

    case '3':
        $breadcrumb = lang("User_Index_DeletedUsers");
        $user_type = 'deleted';
    break;

    case '4':
        $breadcrumb = lang("User_Index_BlockedUsers");
        $user_type = 'blocked';
    break;

    case '5':
        $breadcrumb = lang("User_Index_VerifiedUsers");
        $user_type = 'verify';
    break;
    default;
        $breadcrumb = lang("User_Index_RegisteredUsers");
        $user_type = 'register';
}

$default_tab = '';
if(in_array(getRightsId('user_profile_overview'), getUserRightsData($this->DeviceType))){
    $default_tab = 'overview_tab';
}else if(in_array(getRightsId('user_profile_communicate'), getUserRightsData($this->DeviceType))){
    $default_tab = 'communicate_tab';
}else if(in_array(getRightsId('user_profile_media'), getUserRightsData($this->DeviceType))){
    $default_tab = 'media_tab';
}
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <ul class="bread-crumb-nav clearfix">
        <li><span><a href="<?php echo base_url('admin/users/index/'.$user_type) ?>"><?php echo $breadcrumb; ?></a></span></li>
        <li><i class="icon-rightarrow">&nbsp;</i></li>
            <li><span><?php echo lang('User_UserProfile_UserProfile'); ?></span></li>
    </ul>
    <input type="hidden" name="hdnUserStatus" id="hdnUserStatus" value="<?php echo $user_status; ?>"/>
    <input type="hidden" name="hdnUserGUID" id="hdnUserGUID" value="<?php echo $user_guid; ?>"/>
    <input type="hidden" name="hdnUserID" id="hdnUserID" value="<?php echo $user_id; ?>"/>
    <input type="hidden" name="hdnUserRoleID" id="hdnUserRoleID" value="<?php echo $userroleid; ?>"/>
    <input type="hidden" name="hdnPageName" id="hdnPageName" value="UserProfile"/>
    </div>
</div>
<!--/Bread crumb-->
<div class="clearfix">&nbsp;</div>
<section class="main-container">
    <div  class="container">
        <div ng-controller="userCtrl" ng-init="getUser();" id="userCtrl">
            <div class="panel">
            <div class="panel-body">
            <!--Info row-->
            <div class="info-row row-flued">
                <h2><span ng-bind="user.firstname"></span> <span ng-bind="user.lastname"></span><a class="icon-status {{statusClass(user.StatusID)}}" title="{{statusTitle(user.StatusID)}}" rel="tipsyse"></a></h2>
                <div class="info-row-right">
                    <div class="pull-right" style="width: 160px;">
                        <select chosen data-disable-search="true" name="csutomSelect" id="csutomSelect" data-placeholder="Select an Action" onchange="ProfileAction();">
                            <option value=""></option>
                            <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <option value="2"><?php echo lang("User_Index_Delete"); ?></option>
                            <?php } ?>
                            <?php if(in_array(getRightsId('login_as_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <option value="8"><?php echo lang("LoginAsThisUser"); ?></option>
                            <?php } ?>
                            <?php if(in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <option value="7"><?php echo lang("User_Index_Approve"); ?></option>
                            <?php } ?>
                            <?php if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <option value="3"><?php echo lang("User_Index_Block"); ?></option>
                            <?php } ?>
                            <?php if(in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <option value="4"><?php echo lang("User_Index_Unblock"); ?></option>
                            <?php } ?>
                            <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <option value="5"><?php echo lang("User_Index_Communicate"); ?></option>
                            <?php } ?>
                            <?php if(in_array(getRightsId('change_password_event'), getUserRightsData($this->DeviceType))){ ?>
                                <option value="6"><?php echo lang("User_Index_ChangePassword"); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <!--/Info row-->
            <section class="user-detial">
                <div class="detail-left"> <a href="javascript:void(0);" class="profile-thmb">
                    <img ng-src="{{user.profilepicture}}" alt="Profile Image" width="94"></a>
                    <div class="user-info overflow"> 
                        <span ng-bind="user.location"></span>
                        <span>Member since: <span ng-bind="user.membersince"></span></span>
                        <a href="javascript:void(0);">{{user.email}}</a>
                        <div class="user-report">
                            <label class="circle red"></label>
                            <label class="circle red-light"></label>
                            <label class="circle orange"></label>
                            <label class="circle green"></label>
                        </div>
                    </div>
                </div>
                <div class="detail-right">
                    <ul id="mycarousel" class="jcarousel-skin-tango">
                        <li>
                            <div class="total-count color-blue login-view"> <span class="total">{{user.totallogincount}}</span>
                                <label class="title"><?php echo lang('User_UserProfile_Login'); ?></label>
                            </div>
                        </li>
                        <li>
                            <div class="total-count color-red abuse-view"> <span class="total">{{user.totalabusereport}}</span>
                                <label class="title"><?php echo lang('User_UserProfile_Abuse'); ?><br> <?php echo lang('User_UserProfile_Reports'); ?></label>
                            </div>
                        </li>
                        <li>
                            <div class="total-count color-green picture-view"> <span class="total">{{user.totalpictures}}</span>
                                <label ng-if="user.totalpictures == 0 || user.totalpictures == 1" class="title"><?php echo lang('User_UserProfile_Picture'); ?></label>
                                <label ng-if="user.totalpictures > 1" class="title"><?php echo lang('User_UserProfile_Pictures'); ?></label>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>
            </div>
            </div>
            
            <!--Popup for Delete a user  -->
            <div class="popup confirme-popup animated" id="delete_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
                  <div class="popup-content">
                      <p><?php echo lang('Sure_Delete'); ?> ?</p>
                      <div class="communicate-footer text-center">
                           <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                           <button class="button" ng-click="ChangeSingleUserStatus('delete_popup','3');" id="button_on_delete" name="button_on_delete">
                               <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                           </button>
                      </div>
                  </div>
            </div>
            <!--Popup end Delete a user  -->
            
            
            <!--Popup for Block a user  -->
            <div class="popup confirme-popup animated" id="block_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('block_popup', 'bounceOutUp');">&nbsp;</i></div>
                  <div class="popup-content">
                      <p><?php echo lang('Sure_Block'); ?> ?</p>
                      <div class="communicate-footer text-center">
                           <button class="button wht" onClick="closePopDiv('block_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                           <button class="button" ng-click="ChangeSingleUserStatus('block_popup','4');" id="button_on_block" name="button_on_block">
                               <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                           </button>
                      </div>
                  </div>
            </div>
            <!--Popup end Block a user  -->
        
            <!--Popup for UnBlock a user  -->
            <div class="popup confirme-popup animated" id="unblock_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('unblock_popup', 'bounceOutUp');">&nbsp;</i></div>
                  <div class="popup-content">
                      <p><?php echo lang('Sure_Unblock'); ?> ?</p>
                      <div class="communicate-footer text-center">
                           <button class="button wht" onClick="closePopDiv('unblock_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                           <button class="button" ng-click="ChangeSingleUserStatus('unblock_popup','2');" id="button_on_unblock" name="button_on_unblock">
                               <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                           </button>
                      </div>
                  </div>
            </div>
            <!--Popup end UnBlock a user  -->
            
            <!--Popup for Approve a user  -->
            <div class="popup confirme-popup animated" id="approve_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('approve_popup', 'bounceOutUp');">&nbsp;</i></div>
                  <div class="popup-content">
                      <p><?php echo lang('Sure_Approve'); ?> ?</p>
                      <div class="communicate-footer text-center">
                           <button class="button wht" onClick="closePopDiv('approve_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                           <button class="button" ng-click="ChangeSingleUserStatus('approve_popup','2');" id="button_on_unblock" name="button_on_unblock">
                               <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                           </button>
                      </div>
                  </div>
            </div>
            <!--Popup end Approve a user  -->
            
        </div>

        <div class="global-tab" ng-controller="usrTabController" ng-init="loadUserProfileTab('<?php echo $default_tab; ?>');">
            <ul class="tabs custom-tabs" id="tabs">
                <?php if(in_array(getRightsId('user_profile_overview'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0)" class="selected" ng-click="selectTab('overview')" id="overview_tab"><?php echo lang('User_UserProfile_Overview'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('user_profile_communicate'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0)" ng-click="selectTab('communicate')" id="communicate_tab"><?php echo lang('User_UserProfile_Communicate'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('user_profile_media'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0)" ng-click="selectTab('media')" id="media_tab"><?php echo lang('User_UserProfile_Media'); ?></a></li>
                <?php } ?>
            </ul>
        </div>

        <div class="panel">
        <div class="panel-body">
        <div class="row-flued" id="overview_div">
            <?php if(!in_array(getRightsId('user_profile_overview'), getUserRightsData($this->DeviceType))){ 
                //echo accessDeniedHtml();
            ?>
            <?php }else{ ?>
            <div class="tabcontent">
                <section class="graph-pie-wrap" ng-controller="userChartCtrl" ng-init="getUserLoginChart()" id="userChartCtrl">
                    <aside class="source-wrap text-center">
                        <h5><?php echo lang('User_UserProfile_SourcesOfLogins'); ?></h5>
                        <div id="userLoginChart" class="text-center profile_chart_div"></div>
                    </aside>
                    
                    <aside class="devices-wrap text-center">
                        <h5><?php echo lang('User_UserProfile_Devices'); ?></h5>
                         <div id="userDeviceChart" class="text-center profile_chart_div"></div>
                    </aside>
                </section>
                
                <ul class="accordion-wrap">
                    <li>
                        <aside ng-controller="userIpsCtrl" ng-init="getUserIps()" id="userIpsCtrl">
                        <div class="title-acrdn">
                            <aside class="float-right" ng-show="totalIps > 3">
                                <a class="icon-plus" title="View more" rel="tipsyse" data-role="ipsContent" ng-click="slideTable('viewmore')"></a>
                                <a class="icon-minus" data-role="ipsContent" title="Less" rel="tipsyse"ng-click="slideTable('less')"></a>
                            </aside>
                            <h3 class="overflow"><?php echo lang('User_UserProfile_Ips'); ?></h3>
                        </div>
                        <section class="acrdnTable-content" id="ipsContent">
                            
                            <table class="overview-table" id="overview_table" ng-hide="shownorecord">
                                <tr class="alt-row">
                                    <td ng-repeat="item in userips[0]"> {{item.IPAddress}}<span class="clr999" ng-if="item.IPAddressCount != '' ">({{item.IPAddressCount}})</span></td>
                                </tr>
                            </table>
                            
                            <section class="expandable-wrap">
                                <table class="overview-table OddRow">
                                    <tr ng-repeat="items in userips">
                                        <td ng-repeat="item in items"> {{item.IPAddress}}<span class="clr999" ng-if="item.IPAddressCount != '' ">({{item.IPAddressCount}})</span></td>
                                    </tr>
                                </table>
                            </section>
                            
                            <table class="overview-table" ng-show="shownorecord">
                                <tr class="alt-row rorecordtr">
                                    <td> <div class="no-content text-center"><p><?php echo lang('ThereIsNoHistoricalDataToShow'); ?></p></div></td>
                                </tr>
                            </table>
                            
                        </section>
                        </aside>
                    </li>
                    <li ng-controller="communicationCtrl" id="communicate_li">
                        <div class="title-acrdn">
                            <h3 class="overflow"><?php echo lang('User_UserProfile_Communication'); ?> <span class="clr999">({{noOfObj}})</span></h3>
                        </div>
                        <section class="acrdnTable-content" id="communicationContent">
                            <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
                            <section class="expandable-wrap clear" style="display:block;">
                                <table class="overview-table OddRow bdr-none">
                                    <tr ng-repeat="comm in listData[0].ObjComms">
                                        <td>{{comm.created_date}}</td>
                                        <td><a ng-click="summaryPopup($index)">{{comm.subject}}</a></td>
                                        <td>{{comm.email_type}}</td>
                                    </tr>
                                </table>
                            </section>
                            <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
                            
                            <table class="overview-table" ng-show="shownocomrecord">
                                <tr class="alt-row rorecordtr">
                                    <td>
                                        <div class="no-content no-communic text-center">
                                            <p><?php echo str_replace("{{USERNAME}}",$first_name,lang('DidNotCommunicatedWith')); ?></p>
                                            <a onClick="openPopDiv('communicate_single_user', 'bounceInDown');" href="javascript:void(0);"><?php echo lang('SendaMessage'); ?></a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                        </section>
                    </li>
                </ul>
            </div>
            <?php } ?>
        </div>

        <div class="row-flued hide" id="communicate_div">
            <?php if(!in_array(getRightsId('user_profile_communicate'), getUserRightsData($this->DeviceType))){ 
                //echo accessDeniedHtml();
            ?>
                <input type="hidden" name="allowCommunicationTab" id="allowCommunicationTab" value="0"/>
            <?php }else{ ?>
            <div class="tabcontent" id="communicationTabCtrl" ng-controller="communicationTabCtrl">
                <div class="send-message" ng-hide="shownocomrecord">
                    <a href="javascript:void(0);" class="semdmsz" onClick="openPopDiv('communicate_single_user', 'bounceInDown');"><?php echo lang('SendaMessage'); ?></a>            
                </div>
                
                <div class="communicate-region">
                    <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
                    <section style="clear: both;">
                        <ul>
                            <li ng-repeat="comm in listData[0].ObjComms">
                                <div class="communicate-content">
                                    <p ng-click="showPopup($index)" ng-bind="comm.subject| limitTo:40 "></p>
                                    <span class="date-time">{{comm.created_date}}</span>
                                </div>
                            </li>
                        </ul>
                    </section>
                    <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
                    <div class="no-communicate-content" ng-show="shownocomrecord">
                        <div class="no-content no-communic text-center">
                            <p><?php echo str_replace("{{USERNAME}}",$first_name,lang('DidNotCommunicatedWith')); ?></p>
                            <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <a onClick="openPopDiv('communicate_single_user', 'bounceInDown');" href="javascript:void(0);"><?php echo lang('SendaMessage'); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                    
                </div>
            </div>
            <?php } ?>
        </div>

        <div class="row-flued hide" id="media_div">
            <?php if(!in_array(getRightsId('user_profile_media'), getUserRightsData($this->DeviceType))){ 
                //echo accessDeniedHtml();
            ?>
            <?php }else{ ?>
                <input type="hidden" name="mediaPageName" id="mediaPageName" value="profile"/>
                <div class="tabcontent" id="mediaCtrl" ng-controller="mediaCtrl">
                    <div class="user-mediadetail media-graph">
                        <section class="user-detial">
                            <ul class="payment-total-list">
                                <li class="blue login-view"><label>{{mediaSummary.totalPictures}}</label>
                                    <span ng-if="mediaSummary.totalPictures == 0 || mediaSummary.totalPictures == 1"><?php echo lang('User_UserProfile_Picture'); ?></span>
                                    <span ng-if="mediaSummary.totalPictures > 1"><?php echo lang('User_UserProfile_Pictures'); ?></span>
                                </li>
                                <li class="green"><label>{{mediaSummary.totalVideos}}</label>
                                    <span><?php echo lang('User_UserProfile_Videos'); ?></span>
                                </li>
                                <li class="red"><label>{{mediaSummary.totalPictureSize}}</label>
                                    <span><?php echo lang('User_UserProfile_PictureSize'); ?></span>
                                </li>
                                <li class="yellow"><label>{{mediaSummary.totalVideoSize}}</label>
                                    <span><?php echo lang('User_UserProfile_VideoSize'); ?></span>
                                </li>
                            </ul>
                        </section>
                    </div>

                    <div class="row-flued">
                        <div class="subcategory row-flued filter-block">
                            <div class="filter-region">
                                <div class="filter-tag selected-approve" ng-click="searchBy('IsAdminApproved', 1,'selected-approve');getSearchBox();" ng-class="approveAct" >
                                    <label><?php echo lang('Media_Approved'); ?> </label><span>{{mediaSummary.totalApproved}}</span>
                                </div>
                                <div class="filter-tag selected-reject selected" ng-click="searchBy('IsAdminApproved', 0,'selected-reject');getSearchBox();" ng-class="unApproveAct" >
                                    <label><?php echo lang('Media_YetToApproved'); ?> </label><span>{{mediaSummary.totalUnapproved}}</span>
                                </div>  
                                <a ng-hide="shownomediarecord" href="javascript:void(0);" rel="Hide Advanced Filter" id="showHidefilter"><?php echo lang('Media_ShowAdvanceFilters'); ?></a>
                            </div>
                            <div class="info-row-right" ng-hide="shownomediarecord">
                                <ul class="sub-nav matop10">
                                    <li><a href="javascript:void(0);" ng-click="sortMedia('CreatedDate',CreatedDateOrder);" class="selected"><?php echo lang('Media_MostRecent'); ?></a></li>
                                    <li><a href="javascript:void(0);" ng-click="sortMedia('Size',SizeOrder);"><?php echo lang('Media_Largest'); ?></a></li>
                                    <li><a href="javascript:void(0);" ng-click="sortMedia('AbuseCount',AbuseCountOrder);"><?php echo lang('Media_MostFlagged'); ?></a></li>
                                    
                                    <li>
                                        <i class="icon-graph" id="hideShowgraph">&nbsp;</i>
                                    </li>
                                </ul>
                                <div>
                                    <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                        <div id="selectallbox" class="text-field selectbox">
                                            <span>
                                                <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();" >
                                            </span>
                                            <label for="selectAll"><?php echo lang('Select_All'); ?></label>
                                        </div>
                                    <?php } ?>
                                    <ul class="button-list marright10" id="buttonGroup">
                                        <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <li ng-show="showapprovebtn"><a href="javascript:void(0);" ng-click="updateMultipleMedia('approve')"><?php echo lang('Media_Approve'); ?></a></li>
                                        <?php } ?>
                                        <?php if(in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <li><a href="javascript:void(0);" ng-click="updateMultipleMedia('delete')"><?php echo lang('Media_Delete'); ?></a></li>
                                        <?php } ?>
                                    </ul>                            
                                </div>

                            </div>
                        </div>
                        <div class="filter-view">
                            <div class="filter-title">
                                <label ng-repeat="criteria in criteriaList">{{criteria.Name}}<i class="icon-removed" ng-click="removeFromCriteria(criteria, $index)">&nbsp;</i></label>
                            </div>
                            <div class="filter-content">
                                <div class="filter-list">
                                    <div class="filter-result-list">
                                        <label class="label">Upload Devices</label>
                                        <div class="filter-tag selected-devices" ng-repeat="device in searchBox.upload_devices" id="device-{{device.DeviceID}}" ng-init="device.selected=false;" ng-click="device.selected= !device.selected;addToSearch('DeviceID', device, device.selected, $index, 'upload_devices');" ng-class="{'selected':device.selected}">
                                            <label>{{device.Name}} </label><span>{{device.counts}}</span>
                                        </div>
                                    </div>

                                    <div class="filter-result-list">
                                        <label class="label">Image Extension</label>
                                        <div class="filter-tag selected-extensions" ng-repeat="extension in searchBox.image_extensions" id="extension-{{extension.MediaExtensionID}}" ng-init="extension.selected=false;" ng-click="extension.selected= !extension.selected;addToSearch('MediaExtensionID', extension,extension.selected, $index,'media_extensions');" ng-class="{'selected':extension.selected}">
                                            <label>{{extension.Name}} </label><span>{{extension.counts}}</span>
                                        </div>
                                    </div>

                                    <div class="filter-result-list">
                                        <label class="label">Video Extension</label>
                                        <div class="filter-tag selected-extensions" ng-repeat="extension in searchBox.video_extensions" id="extension-{{extension.MediaExtensionID}}" ng-init="extension.selected=false;" ng-click="extension.selected= !extension.selected;addToSearch('MediaExtensionID', extension,extension.selected, $index,'media_extensions');" ng-class="{'selected':extension.selected}">
                                            <label>{{extension.Name}} </label><span>{{extension.counts}}</span>
                                        </div>
                                    </div>

                                    <div class="filter-result-list">
                                        <label class="label">Youtube Extension</label>
                                        <div class="filter-tag selected-extensions" ng-repeat="extension in searchBox.youtube_extensions" id="extension-{{extension.MediaExtensionID}}" ng-init="extension.selected=false;" ng-click="extension.selected= !extension.selected;addToSearch('MediaExtensionID', extension,extension.selected, $index,'media_extensions');" ng-class="{'selected':extension.selected}">
                                            <label>{{extension.Name}} </label><span>{{extension.counts}}</span>
                                        </div>
                                    </div>

                                    <div class="filter-result-list">
                                        <label class="label">Uploaded From</label>
                                        <div class="filter-tag selected-source" ng-repeat="mediasource in searchBox.media_source" id="mediasource-{{mediasource.SourceID}}" ng-init="mediasource.selected=false;" ng-click="mediasource.selected= !mediasource.selected;addToSearch('SourceID', mediasource, mediasource.selected, $index,'media_source');" ng-class="{'selected':mediasource.selected}">
                                            <label>{{mediasource.Name}} </label><span>{{mediasource.counts}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-list marl30">
                                    <div class="filter-result-list">
                                        <label class="label">Type of Image</label>
                                        <div class="filter-tag selected-sections" ng-repeat="sections in searchBox.media_sections" id="sections-{{sections.MediaSectionID}}" ng-init="sections.selected=false;" ng-click="sections.selected= !sections.selected;addToSearch('MediaSectionID', sections, sections.selected, $index,'media_sections');" ng-class="{'selected':sections.selected}">
                                            <label>{{sections.Name}} </label><span>{{sections.counts}}</span>
                                        </div>
                                    </div>

                                    <div class="filter-result-list">
                                        <label class="label">Media Size</label>
                                        <div class="filter-tag selected-sizes" ng-repeat="mediasize in searchBox.media_size" id="mediasize-{{mediasize.MediaSizeID}}" ng-init="mediasize.selected=false;" ng-click="mediasize.selected= !mediasize.selected;addToSearch('MediaSizeID', mediasize, mediasize.selected, $index,media_size);" ng-class="{'selected':mediasize.selected}">
                                            <label>{{mediasize.Name}} </label><span>{{mediasize.counts}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-footer">
                                    <input type="button" value="<?php echo lang('Media_Search'); ?>" class="button float-right" ng-click="loadMediaWithFilter()">
                                    <input type="button" value="<?php echo lang('Media_Reset'); ?>" class="button wht float-right" ng-click="resetFilter()">
                                </div>
                            </div>
                        </div>
                    </div> 

                    <div class="row-flued">
                        <ul class="view-listing">
                            <li ng-repeat="media in filteredMedia = (mediaList | filter:filt | orderBy:sortOrder)" id="media-{{media.MediaID}}" ng-class="{selected : isSelected(media)}" ng-init="media.indexArr=$index">
                                <img ng-src="{{media.ThumbUrl}}" alt="{{media.ImageName}}" class="img-190-160">
                                <div class="image-title">{{media.ImageName}}</div>
                                <div class="category-desc" ng-click="selectCategory(media);"  ng-class="{selected : isSelected(media)}">
                                    <!--<a href="{{media.ImageUrl}}" class="icon-zoomlist">&nbsp;</a>-->
                                    <a ng-if="media.MediaTypeId == <?php echo IMAGE_MEDIA_TYPE_ID; ?>" href="{{media.ImageUrl}}" class="icon-zoomlist">&nbsp;</a>
                                    <a class="icon-videomedia" ng-if="media.MediaTypeId == <?php echo VIDEO_MEDIA_TYPE_ID; ?> || media.MediaTypeId == <?php echo YOUTUBE_MEDIA_TYPE_ID; ?>" href="javascript:;" ng-click="playVideo(media);">&nbsp;</a>
                                    <i class="icon-selectlist">&nbsp;</i>
                                    <p>
                                        <span>{{media.MediaSection}}</span>
                                        <span class="media-date">{{media.MediaDate}}</span>
                                        <span class="media-size">{{media.MediaExtension | uppercase}} / {{media.MediaSize}}</span>
                                    </p> 
                                    <div class="desc-footer">
                                        <?php if(in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <a href="javascript:void(0);" ng-click="updateMedia(media, 'delete');$event.stopPropagation();"><?php echo lang('Media_Delete'); ?></a>
                                        <?php } ?>
                                        <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <a href="javascript:void(0);" ng-click="updateMedia(media, 'approve');$event.stopPropagation();" ng-show="media.IsAdminApproved==0"><?php echo lang('Media_Approve'); ?></a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <i class="icon-selected" ng-show="media.IsAdminApproved==1"> </i>
                            </li>
                            <li ng-show="shownomediarecord" class="nomediali">
                                <div class="no-media">
                                    <div class="no-content text-center">
                                        <p><?php echo lang('ThereIsNoHistoricalDataToShow'); ?></p>
                                    </div>
                                </div>
                            </li>
                        </ul>     
                        <div class="media_loader">
                            <img id="spinner" src="<?php echo base_url(); ?>assets/admin/img/loader.gif">
                            Loading...
                        </div>

                        <!--Actions Dropdown menu-->
                        <ul class="action-dropdown userActiondropdown">
                            <li><a href="javascript:void(0);">Edit</a></li>
                            <li><a href="javascript:void(0);">Delete</a></li>
                            <li><a href="javascript:void(0);">View All Products</a></li>
                        </ul>
                        <!--/Actions Dropdown menu-->
                        <div class="popup animated " id="mediaImagePopup">
                            <div class="popup-title"><i onclick="closePopDiv('mediaImagePopup', 'bounceOutUp');" class="icon-close">&nbsp;</i></div>
                            <div class="popup-content">
                                <img ng-src="{{popup.ImageUrl}}" alt="{{popup.ImageName}}"/>
                            </div>
                        </div>
                        
                        <div class="popup animated " id="mediaVideoPopup">
                            <div class="popup-title">Video Player Box<i onclick="closePopDiv('mediaVideoPopup', 'bounceOutUp');" class="icon-close">&nbsp;</i></div>
                            <div class="popup-content">
                                <div style="height: 200px; width: 100%; border: #ccc solid 1px;">
                                    Video Player
                                </div>
                            </div>
                        </div>
                        
                        <div class="popup confirme-popup animated" id="confirmeMediaPopup">
                            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMediaPopup', 'bounceOutUp');">&nbsp;</i></div>
                            <div class="popup-content">
                                <p class="text-center">{{confirmationMessage}}</p>
                                <div class="communicate-footer text-center">
                                    <button class="button wht" onclick="closePopDiv('confirmeMediaPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                                    <button class="button" ng-click="setStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>
        </div>
        </div>
        </div>

        <!--Popup for Communicate/send message to a user -->
        <div class="popup communicate animated" id="communicate_single_user" ng-controller="messageCtrl">
            <div class="popup-title"><?php echo lang('User_Index_Communicate'); ?> <i class="icon-close" onClick="closePopDiv('communicate_single_user', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content loader_parent_div">
                <i class="loader_communication btn_loader_overlay"></i>
                <div class="user-detial-block">
                    <a class="user-thmb" href="javascript:void(0);">
                        <img ng-src="{{user.profilepicture}}" alt="Profile Image" style="width: 48px; height: 48px" id="imgUser"></a>
                    <div class="overflow">
                        <a class="name-txt" href="javascript:void(0);" id="lnkUserName">{{user.firstname}} {{user.lastname}} </a>
                        <div class="dob-id">
                            <span id="spnProcessDate">Member Since: {{user.membersince}} </span><br>
                            <a id="lnkUserEmail" href="javascript:void(0);">{{user.email}} </a>
                        </div>
                    </div>
                </div>
                
                <div class="communicate-footer row-flued">
                    
                    <div class="from-subject">
                        <label for="subjects" class="label">Subject</label>
                        <div class="text-field">
                            <input type="text" value="" name="Subject" id="emailSubject" >
                        </div>
                        <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
                    </div>
                    <div class="text-msz editordiv">
                        <?php //echo $this->ckeditor->editor('description', @$default_value); ?>
                        <textarea id="description" name="description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                        <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;">{{errorBodyMessage}}</div>
                    </div>
                </div>

                <button ng-click="sendEmail(user,'users')" class="button float-right" type="submit" id="btnCommunicateSingle"><?php echo lang('Submit'); ?></button>
            </div>
        </div>
        <!--Popup end Communicate/send message to a user -->


        <!--Popup for Show message which send to a user -->
        <div class="popup animated wid600" id="readMessage">
            <div class="popup-content">
                <div class="scroller">
                    <table class="popup-table">
                        <tbody>
                            <tr>
                                <td class="text-bold">Subject</td>
                                <td>:</td>
                                <td class="title-view" id="readSubject"></td>
                            </tr>
                            <tr>  
                                <td class="text-bold">Date</td>
                                <td>:</td>               	
                                <td id="readDate"></td>
                            </tr>
                            <tr>
                                <td colspan="3" id="readBody"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="communicate-footer seprator">
                    <button onclick="closePopDiv('readMessage', 'bounceOutUp');" class="button"><?php echo lang('Close'); ?></button>
                </div>
            </div>
        </div>
        <!--Popup end for Show message which send to a user -->


        <div class="popup animated  wid600" id="summaryPopup">
            <div class="popup-content">
                <div class="scroller">
                    <table class="popup-table">
                        <tr>
                            <td>Subject</td>
                            <td>:</td>
                            <td class="title-view" id="summarySubject"></td>
                        </tr>
                        <tr> 
                            <td>Date</td>
                            <td>:</td>                    	
                            <td id="summaryCreatedDate"></td>
                        </tr>
                        <tr>
                            <td colspan="3" id="summaryBody"></td>
                        </tr>
                    </table>
                </div>
                <div class="communicate-footer seprator">
                    <button class="button" onClick="closePopDiv('summaryPopup', 'bounceOutUp');"><?php echo lang('Close'); ?></button>
                </div>
            </div>
        </div>

    </div>
</section>