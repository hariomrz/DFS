    <!--Bread crumb-->

     <div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/media') ?>"><?php echo lang('Media_Media'); ?></a></li>
                    <li>/</li>
                    <li><span><a href="<?php echo base_url('admin/media/media_abused'); ?>"><?php echo lang('Media_Abused'); ?></a></span></li>
                    <li>/</li>
                    <li><span><?php echo lang('Media_Abuse'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>   
    <!--/Bread crumb-->
<section class="main-container">    
<div  ng-controller="mediaAbuseDetailCtrl" class="container">
    
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><?php echo lang('Media_Abuse'); ?></h2>
        <div class="info-row-right"><a href="javascript:void(0);" class="btn-link" onClick="window.history.back();"><span><?php echo lang('Back'); ?></span></a>
            <ul class="button-list float-right">
                <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-click="updateMedia(mediaDetail, 'approve');"><?php echo lang('Media_Approve'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a  href="javascript:void(0);" ng-click="updateMedia(mediaDetail, 'delete');"><?php echo lang('Media_Delete'); ?></a></li>
                <?php } ?>
            </ul>
        </div>

    </div>
    <!--/Info row-->
    <div class="row-flued">
        <div class="abuse-media">
            <div class="abuse-top">
                <a href="javascript:void(0);" class="user-thmb">
                    <img class="userimg" ng-src="{{mediaDetail.profilepicture}}" alt="Profile Image" width="94">
                </a>
                <div class="overflow">
                    <table class="member-detail">
                        <tr>
                            <td width="32%"><a href="javascript:void(0);" ng-click="viewUserProfile(mediaDetail.UserGUID)" class="">{{mediaDetail.UserName}}</a></td>
                            <td width="68%">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>Member since: {{mediaDetail.membersince}}</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="abuse-content">
                <div class="abuse-content-top">
                    <div class="abuse-img">
                        <img class="abuse_media_img" ng-src="{{mediaDetail.ImageUrl}}" >

                        <div class="abuse-reasons">
                            <h2><?php echo lang('Media_AbuseReasons'); ?></h2>

                            <table>
                                <tr>
                                    <td><?php echo lang('Media_Spam'); ?></td>
                                    <td>{{mediaDetail.SpamCount}}</td>
                                </tr>
                                <tr>
                                    <td><?php echo lang('Media_AbuseContent'); ?></td>
                                    <td>{{mediaDetail.AbuseContent}}</td>
                                </tr>
                                <tr>
                                    <td><?php echo lang('Media_AbusePicture'); ?></td>
                                    <td>{{mediaDetail.AbuseCount}}</td>
                                </tr>
                            </table>
                        </div> 

                    </div>
                    <div class="comment-content">
                        <h2><?php echo lang('Media_Comments'); ?></h2>
                        <div class="overflow">
                            <ul class="comment-list" id="commentList">
                                <li ng-repeat="mediacomment in mediaComments">
                                    <a href="javascript:void(0);" class="user-thmb40"><img class="userimg40" ng-src="{{mediacomment.profilepicture}}" ></a>
                                    <div class="overflow">
                                        <p>{{mediacomment.Description}}</p>
                                        <div class="date-time">{{mediacomment.CreatedDate}}</div>
                                    </div>
                                </li>                                     	
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="clearfix"></div>
    <input type="hidden" name="hdnMediaId" id="hdnMediaId" value="<?php echo $media_id; ?>">
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
</section>
