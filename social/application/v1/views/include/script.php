<?php 
    $app_environment = ENVIRONMENT;
    if($this->session->userdata('UserGUID')) {
?>
    <audio id="chatAudio">
        <source src="<?php echo base_url() ?>assets/sound/ding.ogg" type="audio/ogg">
        <source src="<?php echo base_url() ?>assets/sound/ding.mp3" type="audio/mpeg">
        <source src="<?php echo base_url() ?>assets/sound/ding.wav" type="audio/wav">
    </audio>   
<?php
    }
?>    
    
<!-- Settings File --> 
<script>
    var IsNewsFeed = <?php echo isset($IsNewsFeed) ? "1" : "0"; ?>;    
<?php     
    $this->isJSRequest = 1; echo $this->load->view('home/settings_file', '', true);    
?>
</script>
    
<?php 
    if($app_environment=='development') {
?>
        <script src="<?php echo ASSET_BASE_URL ?>js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery-1.11.2.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery-ui.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/vendor/bootstrap.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/jquery-validation/js/jquery.validate.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/plugins.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/jquery-textntags.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/lib/jquery.cookie.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/slick/slick.js"></script>        
        <script src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery.initialize.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/main.js<?php version_control(); ?>"></script>        
        <script src="<?php echo ASSET_BASE_URL ?>js/mycustom.js<?php version_control(); ?>"></script>        
        <script src="<?php echo ASSET_BASE_URL ?>js/wall.js<?php version_control(); ?>"></script>        
        <script src="<?php echo ASSET_BASE_URL ?>plugins/socket.io-1.3.5.js<?php version_control(); ?>"></script>
        
        <script src="<?php echo ASSET_BASE_URL ?>js/lib/angular.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/lib/ng-plugins.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/lib/angular-animate.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/lib/angular-sanitize.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/lib/angular-slick.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/app/app.js"></script> 
        <script src="<?php echo ASSET_BASE_URL ?>js/app/services.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/app/UtilSrvc.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/viewport-watch/scrollMonitor.js"></script> 
        <script src="<?php echo ASSET_BASE_URL ?>js/viewport-watch/viewport-watch.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/lib/angucomplete-alt.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/summernote/summernote.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/summernote/angular-summernote.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/summernote/summernote-cleaner.js"></script>
        
        <script src="<?php echo ASSET_BASE_URL ?>plugins/tam-emoji/js/font-awesome.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/tam-emoji/js/config.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/tam-emoji/js/tam-emoji.js"></script>
        <script src="<?php echo ASSET_BASE_URL?>js/lib/ngStorage-master/ngStorage.min.js"></script>

        <script src="<?php echo ASSET_BASE_URL ?>js/app/wall/services.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/app/directives/utils.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/app/webStorage.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/ui-bootstrap-custom-tpls-2.3.1.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>plugins/ng-file-upload/ng-file-upload.js<?php version_control(); ?>" ></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/range-slider.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/vendor/ng-infinite-scroll.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/vendor/ng-infinite-scroll-with-container.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/ocLazyLoad.js"></script>

        <script src="<?php echo ASSET_BASE_URL?>js/scrollfix.js"></script> 
        <script src="<?php echo ASSET_BASE_URL ?>js/angular/angular-typed.min.js<?php version_control(); ?>"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/bootstrap-tour.min.js<?php version_control(); ?>"></script>
        
        
<?php
    } else {
?>
        <script src="<?php echo ASSET_BASE_URL ?>js/modernizr.min.js"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/plugins.min.js<?php version_control(); ?>"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/ng-plugins.min.js<?php version_control(); ?>"></script>        
<?php        
    }
?>
         
        <script src="//content.jwplatform.com/libraries/yo0beZEB.js"></script>
        <script>jwplayer.key="Dtw8XIrirt0jOoDeYv+GewD2piVCeaDQezuMKg==";</script>
        
<!-- Language File --> 
<script src="home/language_file.js<?php version_control(); ?>"></script>


<?php     
    if($app_environment=='development') { 
        if( ( isset($isFileTab) && $isFileTab) ) {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/files/FileTabController.js<?php version_control(); ?>"></script>
<?php   
        } 
        
        if( ( isset($isLinkTab) && $isLinkTab) ) {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/links/LinkTabController.js<?php version_control(); ?>"></script>
<?php   
        } 
        
        if(!$this->settings_model->isDisabled(42) && ( ( isset($IsNewsFeed) && $IsNewsFeed == 1 ) || ( ( isset($pname) && in_array($pname, ['wall', 'files', 'links']) ) ) )) { 
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/stickyPost/StickyPostController.js<?php version_control(); ?>"></script>
<?php 
        }
    } else {
        if( ( isset($isFileTab) && $isFileTab) ) {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/FileTabController.min.js<?php version_control(); ?>"></script>
<?php   
        } 
        
        if( ( isset($isLinkTab) && $isLinkTab) ) {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/LinkTabController.min.js<?php version_control(); ?>"></script>
<?php   
        } 
        
        if(!$this->settings_model->isDisabled(42) && ( ( isset($IsNewsFeed) && $IsNewsFeed == 1 ) || ( ( isset($pname) && in_array($pname, ['wall', 'files', 'links']) ) ) )) { 
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/StickyPostController.min.js<?php version_control(); ?>"></script>
<?php 
        }        
    }
?>

<?php
switch (trim(strtolower($this->page_name)))
{
    case 'terms':
    case 'signin' :
    case 'signup' :
        ?>
        
            <script type="text/javascript" src="//platform.linkedin.com/in.js">
                api_key:<?php echo LINKEDIN_SCRIPT ?>
                scope:r_basicprofile,r_emailaddress
                authorize: true
                onLoad: checkLinkedInLoaded
            </script>
<?php            
            if($app_environment=='development') {
?>              
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/signup/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/facebook_lib.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/google_lib.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/linkedin_lib.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social_login.js<?php version_control(); ?>"></script>
<?php
            } else {
?>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/signup.min.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social-plugins.min.js<?php version_control(); ?>"></script>
<?php
            }

            if (isset($this->sub_page) && $this->sub_page == 'thanks') {
?>  
                <script type="text/javascript">
                    setTimeout(function () {window.top.location = "<?php echo site_url(); ?>";}, 5000);
                </script>
<?php
            }
        break;
    case 'search' :
        ?>         
<?php            
        if($app_environment=='development') {
?>          
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/search/ContentSearchController.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/search/controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/pages/pages_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/controller.js<?php version_control(); ?>"></script>                
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/GroupMembrCtrl.js<?php version_control(); ?>"></script>                
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>        
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/ratings/controllers.js<?php version_control(); ?>"></script>
<?php
        } else {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/search.min.js<?php version_control(); ?>"></script>
<?php
        }
?>  
<?php
        break;
    case 'network' :
        ?>
        <script src="https://apis.google.com/js/client.js"></script> 
 <?php
        if($app_environment=='development') {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/build-network/facebook.js<?php version_control(); ?>"></script>        
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/build-network/google.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/build-network/networkscript.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/network/network_controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/window-live.js<?php version_control(); ?>"></script>                
<?php        
        } else {
 ?> 
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/build-network.min.js<?php version_control(); ?>"></script>
<?php
        }
?>
        <script type="text/javascript">
        WL.init({
            client_id: "<?php echo OUTLOOK_CLIENT_ID; ?>",
            redirect_uri: base_url+"crop/live.html",
            scope: ["wl.basic", "wl.contacts_emails"],
            response_type: "token"
        });
        </script>
        <?php
        break;
    case 'forgotpassword' :
            if($app_environment=='development') {
?>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/signup/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>        
<?php
            } else {
?>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/signup.min.js<?php version_control(); ?>"></script>
<?php
            }
?>                 
        <?php
        break;
    case 'group' :
        
            if($app_environment=='development') {
?>              
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/signup/controllers.js<?php version_control(); ?>"></script>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/profile_cover.js<?php version_control(); ?>"></script>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/GroupMembrCtrl.js<?php version_control(); ?>"></script>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/GroupController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>        
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/album/controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/skills/controllers.js<?php version_control(); ?>"></script>
                <!-- <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/jquery.isotope.min.js"></script> -->
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/lib/masonry.pkgd.js<?php version_control(); ?>"></script>
<?php
                if($pname == 'event'){
?>                    
                    <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/events/form_validation_directive.js<?php version_control(); ?>"></script>
                    <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery.ui.timepicker.js<?php version_control(); ?>"></script>
<?php
                }
            } else {
?>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/group.min.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/group-event.min.js<?php version_control(); ?>"></script>
<?php
            }
?>        
        <script type="text/javascript">
            $(document).ready(function () {
                $("#datepicker,#datepicker2").datepicker({
                    maxDate: '0'
                });
            });
        </script>
        <script type="text/javascript">
<?php
    if ($this->router->fetch_method() == 'wall') {
?>
        $(document).ready(function () {
            setGroupTab(1);
        });
<?php
    } if ($this->router->fetch_method() == 'group_member') {
?>
        $(document).ready(function () {
            setGroupTab(2);
        });
<?php
    }   
?>
        </script>        
 <?php
        break;
    case 'forum' :        
            //$app_environment=='development'  
            if($app_environment=='development') {
?>            
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/signup/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>        
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/profile_cover.js<?php version_control(); ?>"></script> 
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/GroupMembrCtrl.js<?php version_control(); ?>"></script>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/album/controller.js<?php version_control(); ?>"></script>       
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/forum/controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/widgets/tag_clouds.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery.ui.timepicker.js<?php version_control(); ?>"></script>

<?php
            } else {
?>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social-plugins.min.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/forum.min.js<?php version_control(); ?>"></script>
<?php
            }
            break;
?>
<?php       
    case 'activity_details' :    //echo $this->page_name; die;        
?>        
        <script type="text/javascript" src="//platform.linkedin.com/in.js">
            api_key:<?php echo LINKEDIN_SCRIPT ?>
            scope:r_basicprofile,r_emailaddress
            authorize: true
            onLoad: checkLinkedInLoaded
        </script> 
<?php  
            if($app_environment=='development') {
?>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>        
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>                                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/ActivityDetails.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/controller.js<?php version_control(); ?>"></script>
<?php
            } else {
?>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/activity-details.min.js<?php version_control(); ?>"></script>
<?php
            }            
            break;
    case 'manage_admin' :
    ?>        
<?php          
            //$app_environment=='development'  
            if($app_environment=='development') {
?>            
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/signup/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>        
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/profile_cover.js<?php version_control(); ?>"></script> 
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/GroupMembrCtrl.js<?php version_control(); ?>"></script>                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/album/controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/forum/controller.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/forum/manage_admin.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/widgets/tag_clouds.js<?php version_control(); ?>"></script>
<?php
            } else {
?>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social-plugins.min.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/forum.min.js<?php version_control(); ?>"></script>
<?php
            }
   
        break;
    case 'profilesetting':
    case 'myaccount' :
?>         
        <script type="text/javascript" src="//platform.linkedin.com/in.js<?php version_control(); ?>">
            api_key:<?php echo LINKEDIN_SCRIPT ?>
            scope:r_basicprofile,r_emailaddress
            authorize: true
            onLoad: checkLinkedInLoaded
        </script>
<?php            
            if($app_environment=='development') {
?>              
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/facebook_lib.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/google_lib.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/linkedin_lib.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social_login.js<?php version_control(); ?>"></script>
                
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/account_attach.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/myaccount/myaccount_controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/profile_cover.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/privacy/controllers.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/network/network_controllers.js<?php version_control(); ?>"></script>
<?php
            } else {
?>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social-plugins.min.js<?php version_control(); ?>"></script>
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/myaccount-controllers.min.js<?php version_control(); ?>"></script>
<?php
            }
        break;    
    case 'userprofile' : 
?>          
            <script type="text/javascript" src="https://www.google.com/jsapi"></script>        
            <script type="text/javascript" src="//platform.linkedin.com/in.js<?php version_control(); ?>">
                api_key:<?php echo LINKEDIN_SCRIPT ?>
                scope:r_basicprofile,r_emailaddress
                authorize: true
                onLoad: checkLinkedInLoaded
            </script>            
<?php
        if($app_environment=='development') {
?>                        
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/media/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script>
            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/profile_cover.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/group/GroupMembrCtrl.js<?php version_control(); ?>"></script>                
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/pages/pages_controller.js<?php version_control(); ?>"></script>
            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/ratings/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/album/controller.js<?php version_control(); ?>"></script>
            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/myaccount/myaccount_controllers.js<?php version_control(); ?>"></script>            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/skills/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/article/article_user_categories_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/forum/controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/about/controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery.ui.timepicker.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/widgets/tag_clouds.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/facebook_lib.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/google_lib.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/linkedin_lib.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social_login.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/account_attach.js<?php version_control(); ?>"></script>            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/article/article_list_controller.js<?php version_control(); ?>"></script>
<?php
        } else {
?>            
            <script src="<?php echo ASSET_BASE_URL ?>js/newsfeed.min.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/social-plugins.min.js<?php version_control(); ?>"></script>
<?php
        }
?>
            <script> 
                $(function () {$('.custom-filters > li a').on('touchstart click', function (e) {var target = $(this).attr('rel');$("#" + target).removeClass('hide').siblings("div").addClass('hide');});})
            </script>
<?php         
        if(isset($pname) && $pname=='dashboard'){  
?>
        <script type="text/javascript">
            $(window).load(function () {
<?php
                $show_intro_popup = false;

                if (@$_REQUEST['showIntro'] == 1) {
                    $show_intro_popup = true;
                } elseif (@get_cookie('site_intro_popup_'.$this->session->userdata('UserGUID')) != date('Ymd')) { 
                       set_cookie(array('name' => 'intro_popup_'.$this->session->userdata('UserGUID') , 'value' => date('Ymd'), 'expire' => '86500', 'prefix' => 'site_'));
                        $show_intro_popup = true;
                } if ($show_intro_popup) {
?>
                    $('#Introduction').modal('show');
<?php 
                } 
?>
            });
        </script>
<?php   
        }
        break;
    case 'messages' :
        if($app_environment=='development') {
?>          
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>        
<?php
        } else {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/user-profile.min.js<?php version_control(); ?>"></script>        
<?php
        }
?>
        <script type="text/javascript">
            if ($(window).width() < 767) {$('.messsag-left-col').hide();$('.messsag-right-col').show();}
        </script>
        <?php
        break;
    case 'events' :           
            
        if($app_environment=='development') {
?>      
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/events/form_validation_directive.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>        
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/profile_cover.js<?php version_control(); ?>"></script>            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>            
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery.ui.timepicker.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/album/controller.js<?php version_control(); ?>"></script>        
<?php
        } else {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/event.min.js<?php version_control(); ?>"></script>
<?php
        }
?>            
        <script type="text/javascript">
            $(document).ready(function () {$('.parallax-layer').vParallax();});
        </script>
<?php
        break; 
    case 'poll':
        if($app_environment=='development') {
?>       
            <script type="text/javascript" src="https://www.google.com/jsapi"></script>
            <script type="text/javascript" src="assets/js/vendor/jquery.fineuploader-3.4.1.min.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="assets/plugins/jquery-tagedit/jquery.autoGrowInput.js<?php version_control(); ?>" ></script>                 
            <script type="text/javascript" src="assets/js/app/userProfile/controllers.js<?php version_control(); ?>"></script>        

            <script type="text/javascript" src="assets/js/app/wall/MainController.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="assets/js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>

            <script type="text/javascript" src="assets/js/app/users/user_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="assets/js/app/poll/poll_controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="assets/js/app/pages/pages_controller.js<?php version_control(); ?>"></script>
<?php
        } else {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/poll.min.js<?php version_control(); ?>"></script>
<?php
        }
?>         
        <script type="text/javascript">
            $(document).ready(function () {
                $('#CreatePoll').on('click', function () {
                    $(this).closest('[data-poll="creation"]').addClass('active');                    
                });
                $("#AddLink").click(function () {
                    $("#addMemberBlock").show();
                    $(".add-member-block").show();
                });
            });
        </script>
        <?php
        break; 
    case 'pages' :
        if($app_environment=='development') {
?>                    
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/events/form_validation_directive.js<?php version_control(); ?>"></script>     
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/MainController.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/wall/NewsFeedController.js<?php version_control(); ?>"></script>        
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/profile_cover.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/vendor/jquery.ui.timepicker.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/users/user_controller.js<?php version_control(); ?>"></script> 
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/pages/pages_controller.js<?php version_control(); ?>"></script>        
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/ratings/controllers.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/album/controller.js<?php version_control(); ?>"></script>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/app/poll/poll_controller.js<?php version_control(); ?>"></script>
<?php
        } else {
?>
            <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/page.min.js<?php version_control(); ?>"></script>
<?php
        }
?>         
        <script type="text/javascript">           
<?php
        if (isset($IsRatingPage) && $IsRatingPage == '1') {
?>
                $(document).ready(function () {
                    $('.media-right,.media-detail-nav,.btn-post-action').remove();
                    $('.media-popup-content .media-detail label,.media-popup-content .media-detail span').remove();
                    var u_n = $('.media-popup-content .media-detail a').html();
                });
<?php 
        } 
?>
        </script>
<?php
        break;
    case 'betainvite':
            if($app_environment=='development') {
?>
                <script src="<?php echo ASSET_BASE_URL . 'js/app/betainvite/betainvite_services.js' ?>"></script>
                <script src="<?php echo ASSET_BASE_URL . 'js/app/betainvite/betainvite_controller.js' ?>"></script>        
<?php
            } else {
?>     
                <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/betainvite.min.js<?php version_control(); ?>"></script>
<?php
            }
        break;
}
?>

<?php
     if($app_environment=='development') {
?>        
        <script src="<?php echo ASSET_BASE_URL ?>js/app/userProfile/services.js"></script>        
        <script src="<?php echo ASSET_BASE_URL ?>js/app/events/events_controller.js<?php version_control(); ?>"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/app/messages/controllers.js<?php version_control(); ?>"></script>
        <script src="<?php echo ASSET_BASE_URL ?>js/app/notification/notification_controller.js<?php version_control(); ?>"></script>
<?php
    } else {
?>
        <script type="text/javascript" src="<?php echo ASSET_BASE_URL ?>js/utility.min.js<?php version_control(); ?>"></script>
<?php
    }
?>  
<script type="text/javascript">
    
<?php
    if ($this->session->flashdata('msg') != '') {
?>
        showResponseMessage("<?php echo $this->session->flashdata('msg') ?>", 'alert-success');
<?php
    }
    if ($this->session->flashdata('errMsg') != '') {
?>
        showResponseMessage("<?php echo $this->session->flashdata('errMsg') ?>", 'alert-danger');
<?php 
    }
    
    if (isset($SetCover) && $SetCover == '1') {
?>
        $(document).ready(function () {
            setTimeout(function () {
                changeCoverImageFromPopup('<?php echo $FilePath ?>');
            }, 2000);
        });
<?php 
    } 

    if (isset($pname) && ($pname == 'about' || $pname == 'connections' || $pname == 'wall' || $pname == 'members' || $pname == 'media' || $pname == 'followers' || $pname == 'ratings' || $pname == 'endorsment' || $pname == 'files' || $pname == 'links'))
    {
?>
     $(document).ready(function () {fixedHeader();});
<?php
    }
?>            
</script>
