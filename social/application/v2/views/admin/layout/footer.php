<!--footer-wrapper start from here-->
<?php if ($this->session->userdata('AdminLoginSessionKey')) { ?>
    <input type="hidden"  name="AdminLoginSessionKey" id="AdminLoginSessionKey" value="<?php echo $this->session->userdata('AdminLoginSessionKey'); ?>"/>
    <input type="hidden" name="AdminGUID" id="AdminGUID" value="<?php echo $this->session->userdata('AdminGUID'); ?>">
    <input type="hidden" name="AdminUserID" id="AdminUserID" value="<?php echo $this->session->userdata('AdminUserID'); ?>">
<?php } ?>

<input type="hidden"  name="pageName" id="pageName" value="<?php echo $this->page_name; ?>"/>
<section class="footer-wrapper" ng-cloak>
    <footer>
        <ul class="footer-links">
<!--            <a href="< ?php echo base_url(); ?>usersite/sitemap">Sitemap</a>&nbsp;&nbsp;&nbsp;-->
            <?php
            if ($this->session->userdata('AdminLoginSessionKey') != '') {
                if (isset($global_settings['footer']['copyright'])) {
                    ?>
                    <?php echo $global_settings['footer']['copyright']; ?>
                    <?php
                }
            }
            ?>
        </ul>
        <div class="powered-by">
            <label>POWERED BY </label>
            <a target="_blank" href="<?php echo POWERED_BY; ?>" id="LblBottom">
                <img src="<?php echo $global_settings['footer']['logo']; ?>">
            </a>
        </div>
        <!--  <div class="language_div">
             <form action="< ?php echo base_url() ?>admin/configuration/changelanguage" id="languageForm" method="post" name="languageForm">
                 <input type="hidden" id="LanguageName" name="LanguageName" value="en"/>
                 <input type="hidden" id="txtReturnUrl1" name="returnUrl" value="" />
                 <label>Offered Languages </label>
                 <div class="langdiv">
                     <select chosen data-disable-search="false" name="languages" id="languages" onchange="SelectedLanguageChanged();">
                         <option value="en">Select Language</option>
                         < ?php foreach (getLanguageList() as $key=>$val){ 
                             $selected = '';
                             if($this->config->item('language') == strtolower($val))
                                 $selected = 'selected=selected';
                             else if(!get_cookie('site_language') && $key == 'en')
                                 $selected = 'selected=selected';
                         ?>
                             <option < ?php echo $selected; ?> value="< ?php echo $key; ?>">< ?php echo $val; ?></option>
                         < ?php } ?>
                     </select>
                 </div>
             </form>
         </div>
         < ?php if ($this->session->userdata('AdminLoginSessionKey') != '') { ?>
             <p class="asocial">
                 < ?php if (isset($global_settings['social_media']['facebook'])) echo $global_settings['social_media']['facebook']; ?>
                 < ?php if (isset($global_settings['social_media']['twitter'])) echo $global_settings['social_media']['twitter']; ?>
                 < ?php if (isset($global_settings['social_media']['googleplus'])) echo $global_settings['social_media']['googleplus']; ?>
                 < ?php if (isset($global_settings['social_media']['linkedin'])) echo $global_settings['social_media']['linkedin']; ?>
             </p>
         < ?php } ?>    -->     
    </footer>
</section>
<!--footer-wrapper end here-->

<!--Popup for change password of a user  -->
<div id="change_user_password" class="popup changepwd animated">
    <div class="popup-title"><?php echo lang('ChangePassword_popup_ChangePassword'); ?> <i onclick="closePopDiv('change_user_password', 'bounceOutUp');" class="icon-close">&nbsp;</i></div>
    <div class="popup-content popup-padding">
        <div>  

            <div>
                <div data-type="focus">
                    <label for="new_password"><?php echo lang('ChangePassword_popup_NewPassword'); ?></label>
                    <input type="password" name="new_password" id="new_password" class="form-control">                                  
                </div>
                <div class="error-holder"><span id="spn_new_password"></span></div>
            </div>

            <div>
                <div data-type="focus">
                    <label for="retype_new_password"><?php echo lang('ChangePassword_popup_RetypeNewPassword'); ?></label>
                    <input type="password" name="retype_new_password" id="retype_new_password" class="form-control">                      
                </div>
                <div class="error-holder"><span id="spn_retype_new_password"></span></div>
            </div>

            <div>
                <button class="button wht" onClick="closePopDiv('change_user_password', 'bounceOutUp');"><?php echo lang('ChangePassword_popup_Cancel'); ?></button>
                <button class="button" onClick="ChangeUserPassword('change_user_password');" id="button_user_pwd" name="button_user_pwd">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Update'); ?>
                </button>
            </div>

        </div>
    </div>
</div>
<!--Popup end for change password of a user  -->

<!--Popup for change password of admin  -->
<div class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog" id="admin_change_password">
    <div class="modal-dialog modal-sm m-t-elg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="ficon-cross"></i></span></button>
                <h4 class="modal-title ng-binding" id="myModalLabel"><?php echo lang('ChangePassword_popup_ChangePassword'); ?></h4>
            </div>
            <div class="modal-body modal-thumbup">




                <div class="form-group">
                    <label class="control-label" for="admin_old_password"><?php echo lang('ChangePassword_popup_OldPassword'); ?></label>
                    <input class="form-control" type="password" name="admin_old_password" id="admin_old_password">                                  

                    <div class="error-holder"><span id="spn_admin_old_password"></span></div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="admin_new_password"><?php echo lang('ChangePassword_popup_NewPassword'); ?></label>
                    <input class="form-control" type="password" name="admin_new_password" id="admin_new_password">                                

                    <div class="error-holder"><span id="spn_admin_new_password"></span></div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="admin_retype_password"><?php echo lang('ChangePassword_popup_RetypeNewPassword'); ?></label>
                    <input class="form-control" type="password" name="admin_retype_password" id="admin_retype_password">                      

                    <div class="error-holder"><span id="spn_admin_retype_password"></span></div>
                </div>

                <div class="btn-toolbar btn-toolbar-center m-t">
                    <button class="button wht" data-dismiss="modal"><?php echo lang('ChangePassword_popup_Cancel'); ?></button>
                    <button class="button" onClick="ChangeAdminPassword('admin_change_password');" id="button_admin_pwd" name="button_admin_pwd">
                        <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                    </button>
                </div>


            </div>
        </div>
    </div></div>
<!--Popup end for change password of admin --->
<noscript>
<meta http-equiv="refresh" content="0;url=<?php echo base_url(); ?>/jserror" >
</noscript>
