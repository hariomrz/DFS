<?php
$type_id = $_GET['id'];
$emails_type = lang('EmailAnalytics_AllEmail');
if ($type_id == 1) {
    $emails_type = lang('EmailAnalytics_CommunicationEmail');
} else if ($type_id == 2) {
    $emails_type = lang('EmailAnalytics_RegistrationEmail');
}else if ($type_id == 4) {
    $emails_type = lang('EmailAnalytics_BetaInviteEmail');
}
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <ul class="bread-crumb-nav clearfix">
            <li><span><a href="javascript:void(0);"><?php echo lang('Analytics'); ?></a></span></li>
            <li><i class="icon-rightarrow">&nbsp;</i></li>
            <li><span><a target="_self" href="<?php echo base_url('admin/analytics/email_analytics'); ?>"><?php echo lang('EmailAnalytics'); ?></a></span></li>
            <li><i class="icon-rightarrow">&nbsp;</i></li>
            <li><span><?php echo $emails_type; ?></span></li>
        </ul>
    </div> 
</div>
<!--/Bread crumb-->
<section class="main-container">
<div class="container">
<!--Info row-->
<div class="info-row row-flued">
    <h2><span id="spnh2"><?php echo $emails_type; ?></span> ({{totalEmails}})</h2>    
</div>
<!--/Info row-->

<div class="row-flued">
    <div class="panel panel-secondary">
        <div class="panel-body" ng-controller="EmailListCtrl" id="EmailListCtrl">
            <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
            <table class="table table-hover email_table">
                <tr>
                    <th id="subject" class="ui-sort" ng-click="orderByField = 'subject'; reverseSort = !reverseSort; sortBY('subject')">                           
                        <div class="shortdiv">Subject<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="username" class="ui-sort" ng-click="orderByField = 'username'; reverseSort = !reverseSort; sortBY('username')">
                        <div class="shortdiv">Users Name<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                    </th>
                    <th id="email" class="ui-sort" ng-click="orderByField = 'email'; reverseSort = !reverseSort; sortBY('email')">
                        <div class="shortdiv">Email<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="created_date" class="ui-sort selected" ng-click="orderByField = 'created_date'; reverseSort = !reverseSort; sortBY('created_date')">
                        <div class="shortdiv sortedUp">Date And Time<span class="icon-arrowshort">&nbsp;</span></div>
                    </th>
                    <th>Actions</th>
                </tr>

                <tr ng-repeat="emaillist in listData[0].ObjEmails" ng-class="cls($index)">
                    <td>{{emaillist.subject}}</td>
                    <td>
                        <a href="javascript:void(0)" class="thumbnail40" title="Click to select" rel="tipsynw">                                        
                            <img ng-src="{{emaillist.profilepicture}}" >
                        </a>
                        <a href="javascript:void(0)" class="name" ng-click="viewUserProfile(emaillist.userguid)">{{emaillist.username}}</a>
                    </td>                       
                    <td><a href="mailto:{{emaillist.email}}" original-title="mailto:{{emaillist.email}}">{{emaillist.email}}</a></td>
                    <td>{{emaillist.created_date}}</td>                       
                    <td>
                        <a href="javascript:void(0);" ng-click="SetEmail(emaillist);" class="email-action" onClick="emailActiondropdown()">
                            <i class="icon-setting">&nbsp;</i>
                        </a>
                    </td>
                </tr>                  
            </table>
            <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
        </div>
    </div>    
        <!--Actions Dropdown menu-->
        <ul class="dropdown-menu emailActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
            <?php if(in_array(getRightsId('email_analytics_emails_view_event'), getUserRightsData($this->DeviceType))){ ?>
                <li id="ActionView"><a ng-click="summaryPopup()" href="javascript:void(0);">View</a></li>
            <?php } ?>
            <?php if(in_array(getRightsId('email_analytics_emails_resend_event'), getUserRightsData($this->DeviceType))){ ?>
                <li id="ActionResend" ng-hide="currentUserStatusId==3"><a ng-click="ResendEmail()" href="javascript:void(0);">Resend</a></li>
            <?php } ?>
        </ul>
        <!--/Actions Dropdown menu-->
        
    

    <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
</div>

<!--Popup for Show message which send to a user -->
<div class="popup animated wid600" id="summaryPopup">
    <div class="popup-content">
        <div class="scroller">
            <table class="popup-table">
                <tbody>
                    <tr>
                        <td class="text-bold">Subject</td>
                        <td>:</td>
                        <td class="title-view" id="summarySubject"></td>
                    </tr>
                    <tr>  
                        <td class="text-bold">Date</td>
                        <td>:</td>               	
                        <td id="summaryCreatedDate"></td>
                    </tr>
                    <tr>
                        <td colspan="3" id="summaryBody"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="communicate-footer seprator">
            <button onclick="closePopDiv('summaryPopup', 'bounceOutUp');" class="button"><?php echo lang('Close'); ?></button>
        </div>
    </div>
</div>
<!--Popup end for Show message which send to a user -->
</div>
</section>
<input type="hidden"  name="hdnEmailTypeID" id="hdnEmailTypeID" value="<?php echo $type_id; ?>"/>
<input type="hidden"  name="hdnEmailID" id="hdnEmailID" value=""/>
<input type="hidden"  name="hdnCommunicationID" id="hdnCommunicationID" value=""/>