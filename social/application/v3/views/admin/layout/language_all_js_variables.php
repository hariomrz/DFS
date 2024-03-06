<script>
    var LoadingMsg = '<?php echo lang('Loading'); ?>';
    var WeAreWorking = '<?php echo lang('WeAreWorking'); ?>';
    var StillWeAreWorking = '<?php echo lang('StillWeAreWorking'); ?>';
    var SeemsSomethingWrong = '<?php echo lang('SeemsSomethingWrong'); ?>';
    var SeemsSomethingWrongRefresh = '<?php echo lang('SeemsSomethingWrongRefresh'); ?>';
    var ItemsSelected = ' <?php echo lang("ItemsSelected"); ?>';
    var ItemSelected = ' <?php echo lang("ItemSelected"); ?>';
    var ThereIsNoRecordToShow = '<?php echo lang("ThereIsNoRecordToShow"); ?>';
    var Sure_Delete = ' <?php echo lang("Sure_Delete"); ?>';
    var Sure_Block = ' <?php echo lang("Sure_Block"); ?>';
    var Sure_Unblock = ' <?php echo lang("Sure_Unblock"); ?>';
    var Sure_Approve = ' <?php echo lang("Sure_Approve"); ?>';
    var Sure_Active = '<?php echo lang('Sure_Active'); ?>';
    var Sure_Inactive = '<?php echo lang('Sure_Inactive'); ?>';
    var Sure_Complete = ' <?php echo lang("Sure_Complete"); ?>';
    var Sure_Ignore = '<?php echo lang('Sure_Ignore'); ?>';
    var Sure_Pending = '<?php echo lang('Sure_Pending'); ?>';
    var Allow_IPs = '<?php echo lang('Allow_IPs'); ?>';
    var Blocked_IPs = '<?php echo lang('Blocked_IPs'); ?>';
    var Media_ShowAdvanceFilters = '<?php echo lang('Media_ShowAdvanceFilters'); ?>';
    var Media_HideAdvanceFilters = '<?php echo lang('Media_HideAdvanceFilters'); ?>';
    var Email_Sure_Inactive = '<?php echo lang('Email_Sure_Inactive'); ?>';
    var ThereIsNoUserToShow = '<?php echo lang('ThereIsNoUserToShow'); ?>';
    var ThereIsNoHistoricalDataToShow = '<?php echo lang('ThereIsNoHistoricalDataToShow'); ?>';
    var ThereIsNoEmailToShow = '<?php echo lang('ThereIsNoEmailToShow'); ?>';
    var ThereIsNoEmailSettingToShow = '<?php echo lang('ThereIsNoEmailSettingToShow'); ?>';
    var ThereIsNoRoleToShow = '<?php echo lang('ThereIsNoRoleToShow'); ?>';
    var ThereIsNoPermissionToShow = '<?php echo lang('ThereIsNoPermissionToShow'); ?>';
    var ThereIsNoIPsToShow = '<?php echo lang('ThereIsNoIPsToShow'); ?>';
    var PermissionDeniedAction = '<?php echo lang('permission_denied'); ?>';
    
    var WeAreWorkingTime = '<?php echo WE_ARE_WORKING_TIME ?>';
    var StillWeAreWorkingTime = '<?php echo STILL_WE_ARE_WORKING_TIME ?>';
    var SeemsSomethingWrongRefreshTime = '<?php echo SEEMS_SOMETHING_WRONG_REFRESH_TIME ?>';
	var ImageServerPath = '<?php echo IMAGE_SERVER_PATH; ?>';
    var Category = '<?php echo lang('Category'); ?>';
    var ThereIsNoCategoryToShow = '<?php echo lang('ThereIsNoCategoryToShow'); ?>';
</script>
<!-- Switch Case for include js according page wise-->
<?php 
$active_menu_tab = 'users';
switch ($this->page_name) {
    
    /* Case User listings*/
    case 'users':    
?>
    <script>
        var User_Index_RegisteredUsers = '<?php echo lang('User_Index_RegisteredUsers'); ?>';
        var User_Index_DeletedUsers = '<?php echo lang('User_Index_DeletedUsers'); ?>';
        var User_Index_BlockedUsers = '<?php echo lang('User_Index_BlockedUsers'); ?>';
        var User_Index_WaitingForApproval = '<?php echo lang('User_Index_WaitingForApproval'); ?>';
    </script>

<?php break; 
     /* Case User listings*/
    case 'betainvite':  
?>
    <script>
        var BetaInvite_JoinedUsers = '<?php echo lang('BetaInvite_JoinedUsers'); ?>';
        var BetaInvite_NotJoinedYet = '<?php echo lang('BetaInvite_NotJoinedYet'); ?>';
        var BetaInvite_DeletedUsers = '<?php echo lang('BetaInvite_DeletedUsers'); ?>';
        var BetaInvite_RemovedAccessUsers = '<?php echo lang('BetaInvite_RemovedAccessUsers'); ?>';
        var Users_Upload_Success = '<?php echo lang('Users_Upload_Success'); ?>';
        var User_Upload_Success = '<?php echo lang('User_Upload_Success'); ?>';
        var Sure_RemoveAccess = '<?php echo lang('Sure_RemoveAccess'); ?>';
        var Sure_GrantAccess = '<?php echo lang('Sure_GrantAccess'); ?>';
        var Sure_Reinvite = '<?php echo lang('Sure_Reinvite'); ?>';
        var ThereIsNoRecordToImport = '<?php echo lang('ThereIsNoRecordToImport'); ?>';
    </script>    
<?php break; 
/* Case User listings*/
    case 'team':  
?>
    <script>
        var no_record       = '<?php echo lang('no_record'); ?>';
    </script>    
<?php break;
/* Case blog */
    case 'blog':  
    case 'announcement':  
?>
    <script>
        var required_blog_title         = '<?php echo lang('required_blog_title'); ?>';
        var required_blog_description   = '<?php echo lang('required_blog_description'); ?>';
        var no_record                   = '<?php echo lang('no_record'); ?>';
    </script>    
<?php break;  
}
?>