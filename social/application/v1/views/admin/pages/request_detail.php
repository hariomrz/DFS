<aside class="content-wrapper" ng-controller="PageListCtrl" id="PageListCtrl" ng-cloak>
    
    <div class="clearfix">&nbsp;</div>
    <!--Info row-->
    <div class="info-row row-flued">
        <h2 ng-if="BRequestData.EmailTypeID == 23"><?php echo lang('business_request'); ?></h2>
        <h2 ng-if="BRequestData.EmailTypeID == 29"><?php echo lang('business_verify_request'); ?></h2>
        
        <div ng-if="BRequestData.StatusID != 18 && BRequestData.EmailTypeID == 23" class="info-row-right rightdivbox" >
            <a class="button float-right marl10" href="<?php echo base_url(); ?>admin/pages/create?breq={{BRequestData.CommunicationID}}"><?php echo lang('page_create') ?></a>
        </div>
        
        <div ng-if="BRequestData.StatusID != 5 && BRequestData.EmailTypeID == 29 && BRequestData.IsVerified != 1" class="info-row-right rightdivbox" >
            
            <div class="popup confirme-popup animated" id="confirmeCommissionPopup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p class="text-center">{{confirmationMessage}}</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="updateBRequestStatusFromDetail('5', BRequestData.CommunicationID, BRequestData.PageID)"><?php echo lang('Confirmation_popup_Yes'); ?></button>
                    </div>
                </div>
            </div>
            
            <a class="button float-right marl10" ng-click="SetStatus(5);" href="javascript:void(0);">Mark Verified</a>
        </div>
    </div>
    <!--/Info row-->
    
    
    <div class="row-flued" ng-init="<?php
            if (!empty($communication_id)) {
                echo "request_detail('$communication_id')";
            }
            ?>">
        
        <div class="">
            
            <div class="abuse-content">
                <div class="abuse-content-top">
                    <div class="">
                        
                        <div class="abuse-reasons">
                            <table>
                                <tr>
                                    <td><?php echo lang('breq_firstname'); ?> : </td>
                                    <td>{{BRequestData.BusinessFirstName}}</td>
                                </tr>
                                <tr>
                                    <td><?php echo lang('breq_lastname'); ?> : </td>
                                    <td>{{BRequestData.BusinessLastName}}</td>
                                </tr>
                                <tr>
                                    <td><?php echo lang('breq_email'); ?> : </td>
                                    <td>{{BRequestData.BusinessEmail}}</td>
                                </tr>
                                <tr>
                                    <td><?php echo lang('breq_url'); ?> : </td>
                                    <td>{{BRequestData.BusinessURL}}</td>
                                </tr>
                                <tr>
                                    <td><?php echo lang('breq_phone_number'); ?> : </td>
                                    <td>{{BRequestData.BusinessPhone}}</td>
                                </tr>
                                
                                <tr ng-if="BRequestData.EmailTypeID == 23">
                                    <td><?php echo lang('breq_business_name'); ?> : </td>
                                    <td>{{BRequestData.BusinessName}}</td>
                                </tr>
                                <tr ng-if="BRequestData.EmailTypeID == 29">
                                    <td><?php echo lang('breq_business_name'); ?> : </td>
                                    <td>{{BRequestData.Title}}</td>
                                </tr>
                                
                                <tr>
                                    <td><?php echo lang('Status'); ?> : </td>
                                    <td>{{BRequestData.StatusName}}</td>
                                </tr>
                                <tr>
                                    <td><?php echo lang('breq_business_description'); ?> : </td>
                                    <td ng-bind-html="trustAsHtml(BRequestData.BusinessMessage);"></td>
                                </tr>
                                
                            </table>
                        </div> 

                    </div>
                    
                </div>
            </div>

        </div>
    </div>
    <div class="clearfix"></div>
</aside>

