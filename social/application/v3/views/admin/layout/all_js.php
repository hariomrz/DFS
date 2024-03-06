<?php
$assets_path = base_url()."assets/";
?>
<!-- Main JS Files -->  
<script src="<?php echo $assets_path.'admin/js/vendor/jquery-1.10.1.min.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/plugins/bootstrap/js/bootstrap.min.js'?>"></script>
<script src="<?php echo $assets_path.'admin/js/vendor/jquery-ui.min.js' ?>"></script>

<!-- <script src="<?php echo base_url(); ?>assets/admin/plugins/jquery.summernote/summernote.js" type="text/javascript"></script> -->
<script src="<?php echo $assets_path ?>plugins/summernote/summernote.js" type="text/javascript"></script>
<?php
    if(!in_array($this->page_name, array('users', 'dashboard', 'announcement', 'all_question', 'orientation', 'album', 'album_detail', 'crm_users', 'daily_digest', 'ward', 'banner'))) {
?>
<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=true&libraries=places&key=AIzaSyCKC9GSEkJ7lR1B4HG4eiMogJhyo7dFh34"></script>
<script src="https://www.google.com/jsapi"></script>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<?php
    }
?>
<script src="<?php echo $assets_path.'admin/js/jquery.tipsy.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/vendor/jquery.lightbox-0.5.js' ?>"></script> 
<script src="<?php echo $assets_path.'js/lib/angular-1.4.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/sortable.js' ?>"></script>

<script src="<?php echo $assets_path ?>js/lib/ng-plugins.js<?php version_control(); ?>"></script>
<!--<script data-require="angular-ui-bootstrap@0.3.0" data-semver="0.3.0" src="<?php //echo $assets_path.'admin/lib/ui-bootstrap-tpls-0.3.0.min.js' ?>"></script>-->
<script data-require="angular-ui-bootstrap@2.5.0" data-semver="2.5.0" src="<?php echo $assets_path.'admin/lib/ui-bootstrap-tpls-2.5.0.min.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/controller.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/services.js' ?>"></script>
<script src="<?php echo $assets_path.'js/app/services.js' ?>"></script>
<script src="<?php echo base_url('assets/admin/js/ng-img-crop.js') ?>"></script>
<script src="<?php echo $assets_path.'js/vendor/moment.min.js' ?>"></script>
<script src="<?php echo $assets_path.'js/vendor/moment-timezone.js' ?>"></script>
<script src="<?php echo $assets_path.'js/imagesloaded.js' ?>"></script>
<script src="<?php echo $assets_path.'js/imagefill.js' ?>"></script>
<script src="<?php echo $assets_path.'js/mCustomScrollbar.js' ?>"></script>
<script type="text/javascript" src="<?php echo $assets_path ?>plugins/ng-file-upload/ng-file-upload.js<?php version_control(); ?>" ></script>
<script type="text/javascript" src="<?php echo $assets_path ?>js/jquery.cropit.js<?php version_control(); ?>" ></script>
<!--<script src="<?php echo $assets_path.'admin/js/vendor/angucomplete-alt.js' ?>"></script>-->

<script src="<?php echo $assets_path ?>plugins/summernote/angular-summernote.js"></script>
<script src="<?php echo $assets_path ?>plugins/summernote/summernote-cleaner.js"></script>

<script src="<?php echo $assets_path?>admin/js/scrollfix.js"></script>

<script src="<?php echo $assets_path?>admin/lib/ng-infinite-scroll.min.js"></script>
<script src="<?php echo $assets_path?>js/vendor/lightGallery.js"></script>
<script src="<?php echo $assets_path?>/admin/lib/ngStorage-master/ngStorage.min.js"></script>


<!-- Language File --> 
<script src="<?php echo base_url('home/language_file.js') ?><?php version_control(); ?>"></script>

<script src="<?php echo $assets_path.'admin/js/app/controllers/baseCtrl.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/controllers/msgModalPopupCtrl.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/controllers/groupDetailModalPopupCtrl.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/services/commonRequestService.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/services/webStorage.js' ?>"></script>


<script src="<?php echo $assets_path.'admin/lib/lazy-load/ocLazyLoad.js'; version_control(); ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/services/UtilSrvc.js'; version_control(); ?>"></script>
<script src="<?php echo $assets_path.'admin/js/app/directives/utils.js'; version_control(); ?>"></script>


<!-- Switch Case for include js according page wise-->
<?php
$active_menu_tab = 'users';
switch ($this->page_name)
{

    /* Case login */
    case 'login':
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/login.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/login.js' ?>"></script>

        <?php
        break;
    case 'discover':
        $active_menu_tab = 'discover';
        ?>        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/discover/discover.js?v=1.0' ?>"></script>
        <?php
        break;
    case 'trending_tags':
    case 'top_followed':
    case 'mute_tags';
        $active_menu_tab = 'discover';
        ?>        
            <script src="<?php echo $assets_path.'admin/js/app/controllers/discover/tag.js?v=1.0' ?>"></script>
        <?php
        break;    
    case 'rules':
        ?>        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/RulesCtrl.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/rules.js' ?>"></script>
        <?php
        break;
    /* Case User listings */
    case 'crm_users' :
        ?>
        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/crmUserList.js' ?>" ></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/crmUserListExtra.js' ?>" ></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/crmUserFun.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/notes.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/userList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/directives/dashboard.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/dashboardFeed.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/dashboard.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/MainController.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/NewsFeedController.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/services.js' ?>"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>plugins/socket.io-1.3.5.js?v=1.0"></script>
        <script src="<?php echo $assets_path ?>js/wall.js"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>js/mycustom.js<?php version_control(); ?>"></script>
        
        <?php
    break;
    case 'top_following' :
        ?>        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/crmFollowList.js' ?>" ></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>       
        <script type="text/javascript" src="<?php echo $assets_path ?>js/mycustom.js<?php version_control(); ?>"></script>
        
        <?php
    break;
    case 'top_follow' :
        ?>        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/crmTopFollowedList.js' ?>" ></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>       
        <script type="text/javascript" src="<?php echo $assets_path ?>js/mycustom.js<?php version_control(); ?>"></script>
        
        <?php
    break;
    case 'ward' :
        $active_menu_tab = 'ward';
        ?>
        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/wardList.js' ?>" ></script>
        
        <?php
    break;
    case 'newsletter_users' :
        ?>
        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/newsletter/userList.js' ?>" ></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/newsletter/userListExtra.js' ?>" ></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/newsletter/userFun.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/notes.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/userList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/directives/dashboard.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/dashboardFeed.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/dashboard.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/MainController.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/NewsFeedController.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/services.js' ?>"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>plugins/socket.io-1.3.5.js?v=1.0"></script>
        <script src="<?php echo $assets_path ?>js/wall.js"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>js/mycustom.js<?php version_control(); ?>"></script>
        
        <?php
    break;


    case 'users':
        $active_menu_tab = 'users';
        ?>
        <script>
            var LoggedInUserGUID = '';
            var IsNewsFeed = 0;
        </script>
        <script src="<?php echo $assets_path ?>js/viewport-watch/scrollMonitor.js"></script> 
        <script src="<?php echo $assets_path ?>js/viewport-watch/viewport-watch.js"></script>

        <script src="<?php echo $assets_path.'admin/js/app/controllers/userList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/userList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/notes.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/MainController.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/NewsFeedController.js' ?>"></script>
        
        
        <script src="<?php echo $assets_path.'js/app/wall/services.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/directives/dashboard.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/dashboardFeed.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/dashboard.js' ?>"></script>
        
        <script src="<?php echo $assets_path ?>js/wall.js"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>js/mycustom.js<?php version_control(); ?>"></script>

        <!-- For Communication Editor -->

        <script>
            $(document).ready(function () {
                setTimeout(function () {
                    $('.text-editor').summernote({
                        height: 200,
                        disableResizeEditor: true,
                        toolbar: [
                            ['style', ['bold', 'italic', 'underline']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link', 'picture', 'hr']]
                        ]
                    });
                }, 500);

                $('#datesuspend').datepicker();
            });
        </script>

        <?php
        break;
    /* Case Media listings */
    case 'media':
        $active_menu_tab = 'media';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/media.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/media.js' ?>"></script>


        <?php
        break;
    /* Case MediaAbuse listings */

    /* Announcement Popup */
    case 'popup':
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/PopupController.js'; ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/CommonHttpService.js'; ?>"></script>
        <?php   
        break;
    /* Announcement Popup */

    case 'media_abuse':
        $active_menu_tab = 'media';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/mediaAbuse.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/mediaAbuse.js' ?>"></script>


        <?php
        break;
    /* Case user_profile */
    case 'user_profile':
        $active_menu_tab = 'users';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/user.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/user.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/userChart.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/userChart.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/userIp.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/userIp.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/media.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/media.js' ?>"></script>

        <!-- For Communication Editor -->
        <script>
            $(document).ready(function () {
                setTimeout(function () {
                    $('.text-editor').summernote({
                        height: 200,
                        disableResizeEditor: true,
                        toolbar: [
                            ['style', ['bold', 'italic', 'underline']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link', 'picture', 'hr']]
                        ]
                    });
                }, 500);
            });
        </script>

        <?php
        break;
    /* Case email_analytics */
    case 'email_analytics_old':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/emailAnalyticsOld.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/emailAnalyticsOld.js' ?>"></script>

        <?php
        break;
    /* Case email_analytics */
    case 'email_analytics':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/emailAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/emailAnalytics.js' ?>"></script>

        <?php
        break;
    /* Case login_analytics */
    case 'login_analytics':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/loginAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/loginAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path;?>admin/js/google_analytics/js/lib/chart-js/Chart.bundle.js"></script>
        <script src="<?php echo $assets_path;?>admin/js/google_analytics/js/highcharts.js"></script>
        <script src="<?php echo $assets_path;?>admin/js/google_analytics/js/exporting.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                loadLoginAnalyticsChartOnScroll();
            });
        </script>

        <?php
        break;
    case 'login_dashboard':
        $active_menu_tab = 'analytics';
        ?>

        <script src="<?php echo $assets_path.'admin/js/app/controllers/loginDashboard.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/loginDashboard.js' ?>"></script>
        <script type="text/javascript">
            $(function () {
                $('body').addClass('bg-color');
            })
        </script>

        <?php
        break;
    /* Case signup_analytics */
    case 'signup_analytics':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/signupAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/signupAnalytics.js' ?>"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                loadSignupAnalyticsChartOnScroll();
            });
        </script>

        <?php
        break;
    /* Case Email listings */
    case 'emails':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/emailList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/emailList.js' ?>"></script>

        <?php
        break;
    /* Case active User listings */
    case 'most_active_user':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/mostActiveUserList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/mostActiveUserList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/communication.js' ?>"></script>

        <!-- For Communication Editor --- -->
        <script>
            $(document).ready(function () {
                setTimeout(function () {
                    $('.text-editor').summernote({
                        height: 200,
                        disableResizeEditor: true,
                        toolbar: [
                            ['style', ['bold', 'italic', 'underline']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link', 'picture', 'hr']]
                        ]
                    });
                }, 500);
            });
        </script>

        <?php
        break;
    /* Case smtp email listings */
    case 'emailsetting':
        $active_menu_tab = 'emailsetting';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/emailSettings.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/emailSettings.js' ?>"></script>

        <?php
        break;
    /* Case add smtp email */
    case 'smtpsetting':
        $active_menu_tab = 'emailsetting';
        ?>
        <script src="<?php echo $assets_path.'admin/lib/BaseControl.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/emailSettings.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/emailSettings.js' ?>"></script>

        <?php
        break;
    /* Case smtp email listings */
    case 'ips':
        $active_menu_tab = 'ips';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/ips.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/ips.js' ?>"></script>

        <?php
        break;
    /* Case User listings */
    case 'media_analytics':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/mediaAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/mediaAnalytics.js' ?>"></script>

        <?php
        break;
    /* Case User listings */
    case 'configuration':
        $active_menu_tab = '';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/configuration.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/configuration.js' ?>"></script>

        <?php
        break;
    /* Case User listings */
    case 'cultureinfo':
        $active_menu_tab = '';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/cultureinfo.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/cultureinfo.js' ?>"></script>

        <?php
        break;
    /* Case User listings */
    case 'analytictools':
        $active_menu_tab = 'tools';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/analyticTools.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/analyticTools.js' ?>"></script>

        <?php
        break;
    /* Case Roles listings */
    case 'roles':
        $active_menu_tab = 'tools';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/rolesList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/rolesList.js' ?>"></script>

        <?php
        break;
    /* Case Roles permissions */
    case 'rolepermission':
        $active_menu_tab = 'tools';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/rolePermissions.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/rolePermissions.js' ?>"></script>

        <?php
        break;
    /* Case Role Users */
    case 'manageroleuser':
        $active_menu_tab = 'tools';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/roleUsers.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/roleUsers.js' ?>"></script>

        <?php
        break;
    /* Case Support error logs */
    case 'support':
        $active_menu_tab = 'tools';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/support.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/support.js' ?>"></script>

        <?php
        break;
    /* Case User listings */
    case 'betainvite':
        $active_menu_tab = '';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/betainvite.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/betainvite.js' ?>"></script>

        <?php
        break;
    /* Case User listings */
    case 'google_analytics':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/googleAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/googleAnalytics.js' ?>"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                loadGoogleAnalyticsChartOnScroll();
            });
        </script>

        <?php
        break;
    /* Case User listings */
    case 'google_analytics_dash':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/googleAnalyticsDash.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/googleAnalyticsDash.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/loginDashboard.js' ?>"></script>
        <script src="assets/js/main.js"></script>
        <script src="<?php echo $assets_path;?>admin/js/google_analytics/js/lib/chart-js/Chart.bundle.js"></script>
        <script src="<?php echo $assets_path;?>admin/js/google_analytics/js/highcharts.js"></script>
        <script src="<?php echo $assets_path;?>admin/js/google_analytics/js/exporting.js"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/dashboardFeed.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/dashboard.js' ?>"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>plugins/socket.io-1.3.5.js?v=1.0"></script>
        
        <!------------abminLTE ---------->
                
        <!-- <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/jquery/jquery.min.js"></script> -->
        <!-- jQuery UI 1.11.4 -->
        <!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script> -->
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
          $.widget.bridge('uibutton', $.ui.button)
        </script>
        <!-- Bootstrap 4 -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- Morris.js charts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/morris/morris.min.js"></script>
        <!-- Sparkline -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/sparkline/jquery.sparkline.min.js"></script>
        <!-- jvectormap -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
        <!-- jQuery Knob Chart -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/knob/jquery.knob.js"></script>
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/chartjs-old/Chart.min.js"></script>
        <!-- daterangepicker -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/daterangepicker/daterangepicker.js"></script>
        <!-- datepicker -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/datepicker/bootstrap-datepicker.js"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
        <!-- Slimscroll -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/slimScroll/jquery.slimscroll.min.js"></script>
        <!-- FastClick -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/fastclick/fastclick.js"></script>
        <!-- AdminLTE App -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>dist/js/adminlte.js"></script>
        <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>dist/js/pages/dashboard.js"></script>
        <!-- <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>dist/js/pages/dashboard2.js"></script> -->
        <!-- AdminLTE for demo purposes -->
        <script src="<?php echo $assets_path.'admin/AdminLTE/' ?>dist/js/demo.js"></script>
        <!-- <script>
                    ( function ( $ ) {
                        "use strict";
                    } )( jQuery );
                </script> -->
        <!------------abminLTE ---------->

        <?php
        break;    
    /* Case User listings */
    case 'google_analytics_device':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/googleAnalyticsDevices.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/googleAnalyticsDevices.js' ?>"></script>

        <?php
        break;
    case 'message_analytics':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/messageAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/messageAnalytics.js' ?>"></script>

        <?php
        break;
    case 'user_analytics':
        $active_menu_tab = 'analytics';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/userAnalytics.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/userAnalytics.js' ?>"></script>

        <?php
        break;
    /* Case including team page section js */
    case 'team':
        $active_menu_tab = 'team';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/page.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/page.js' ?>"></script>
        <?php
        break;
    case 'events':
        $active_menu_tab = 'tools';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/event.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/event.js' ?>"></script>
        <?php
        break;
    /* Case including blog page section js */
    case 'blog':
        $active_menu_tab = 'blog';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/blog.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/blog.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/vendor/jquery.fineuploader-3.4.1.min.js' ?>"></script>

        <script>
           $(function () {
                   //mainNav(4);
                     // $(".responsive-tabs").responsiveTabs();
                  });

                  function createModal(tab){
                    $('#createModal').on('show.bs.modal',function(){
                      setTimeout(function(){
                      if(!$('.note-dialog>div').is(':visible')){
                        $('.modal-create .tab-pane').removeClass('active in');
                        $('.modal-create .nav-tabs li').removeClass('active');
                        $('.modal-create .nav-tabs a[href="#' + tab + '"]').tab('show');
                      }
                      },0);
                      if(( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )) {
                      $('.note-toolbar .btn').tooltip('destroy');
                      }
                    });
                  }
                  $('#createModal').on('hide.bs.modal',function(){
                    if($('body').find('.modal-backdrop').length<=2){
                    $('body').find('.modal-backdrop').not(':first').remove();
                    }
                  });
                  $('#createModal').on('show.bs.modal',function(){
                    $('.note-dialog>div').on('hidden.bs.modal',function(){
                      setTimeout(function(){
                      $('body').addClass('modal-open');
                      },0);
                    });
                    $('.note-dialog>div').modal('hide');
                  });
        </script>


      
        <?php
        break;
    /* Case Category */
    
    case 'announcement':
        $active_menu_tab = 'announcement';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/announcement.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/announcement.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/vendor/jquery.fineuploader-3.4.1.min.js' ?>"></script>        
        <?php
        break;
    /* Case Category */
    case 'category':
        $active_menu_tab = 'category';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/category.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/category.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/vendor/jquery.fineuploader-3.4.1.min.js' ?>"></script>

        <?php
        break;
    case 'skill':
        $active_menu_tab = 'tools';
        ?>

        <script src="<?php echo $assets_path.'admin/js/app/controllers/skill.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/vendor/jquery.fineuploader-3.4.1.min.js' ?>"></script>

        <?php
        break;
    case 'posts':
        ?>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/posts.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/posts.js') ?>"></script>    
        <?php
        break;
    case 'group_permission':
        ?>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/group.js') ?>"></script> 
        <?php
        break; 
    case 'pages':
        $active_menu_tab = 'pages';
        ?>
        <script src="<?php echo base_url('assets/admin/js/vendor/jquery.fineuploader-3.4.1.min.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/pages.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/pages.js') ?>"></script>
        <?php
        break;
    case 'flag':
        $active_menu_tab = 'flag';
        ?>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/flag.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/flag.js') ?>"></script>
        <?php
        break;
    case 'all_question':
        $active_menu_tab = 'dashboard';
        ?><script>
            IsAdminDashboard = true;
        </script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/dashboardFeed.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/acitvityFilterCtrl.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/questionCtrl.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/directives/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/userList.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/userList.js') ?>"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>plugins/socket.io-1.3.5.js?v=1.0"></script>
        <?php
        break;  
    case 'dashboard':
    case 'daily_digest':
    // case 'all_question':    
        $active_menu_tab = 'dashboard';
        if($this->page_name == "daily_digest") {
            $active_menu_tab = 'daily_digest';
        }
        
        ?>
        <script>
            IsAdminDashboard = true;
        </script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/dashboardFeed.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/acitvityFilterCtrl.js') ?>"></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/MainController.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/NewsFeedController.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/services.js' ?>"></script>
        <script src="<?php echo $assets_path ?>js/wall.js"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/directives/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/filters/helperFilters.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/notes.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/userList.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/userList.js') ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/communication.js' ?>"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>plugins/socket.io-1.3.5.js?v=1.0"></script>

    
        <script>
            function sucessMsz() {
                $('.notifications').addClass('active');
                setTimeout(function() {
                    $('.notifications').removeClass('active');
                }, 3000);
            }
            $(function() {
                //$('select').selectbox();
                $('#dateFrom').datepicker();
                $('#dateTo').datepicker();
                $('.datepicker').datepicker();
                $('#ui-datepicker-div').mouseup(function(e) {
                    return false;
                });

                setTimeout(function(){ 
                    $(".chosen-select").chosen();
                }, 1000);

            });

            $(function() {  

                $('[data-toggle="popover"]').popover({
                    html: true,
                    trigger:'manual',
                    template: '<div class="popover popover-sm"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-body"><ul class="list-thumb-grid"><li class="list-items"><div class="list-body"><div class="content"><h6 class="list-title"><span class="text">Ivan Alexander</span><span class="icn"><i class="ficon-globe"></i></span></h6><span class="text-sm-off bold">Indore, India</span><ul class="list-info"><li><span class="text">Alexander@gmail.com</span><span class="icn circle-icn circle-default"><i class="ficon-facebook"></i></span></li><li><span class="icn"><i class="ficon-cake f-14"></i></span><span class="text">Jan 12 1982, M</span> </li></ul></div></div></li></ul></div><div class="popover-footer"><div class="btn-toolbar btn-toolbar-center"><a class="btn btn-xs btn-icn btn-default"><span class="icn"><i class="ficon-bin"></i></span></a><a class="btn btn-xs btn-icn btn-default"><span class="icn"><i class="ficon-envelope"></i></span></a><a class="btn btn-xs btn-default">Feature</a><a class="btn btn-xs btn-primary"><span class="icn"><i class="ficon-check"></i></span><span class="text">Verify</span></a></div></div></div>'
                }).on("mouseenter", function () {
                    var _this = this;
                            $(this).popover("show");
                            $(".popover").on("mouseleave", function () {
                                $(_this).popover('hide');
                            });
                        }).on("mouseleave", function () {
                            var _this = this;
                            setTimeout(function () {
                                if (!$(".popover:hover").length) {
                                    $(_this).popover("hide");
                                }
                            }, 40);
                    });
            });  

            $(function() {
                $(".img-check").click(function(){
                    $(this).parents('figure').toggleClass("selected");
                });
            });        
            </script>    
        <?php
        break;   
        case 'orientation':
            // case 'all_question':    
            $active_menu_tab = 'orientation';            
        ?>
            <script>
                IsAdminDashboard = true;
            </script>
            <script src="<?php echo base_url('assets/admin/js/app/controllers/dashboard.js') ?>"></script>
            <script src="<?php echo base_url('assets/admin/js/app/controllers/dashboardFeed.js') ?>"></script>
            <script src="<?php echo base_url('assets/admin/js/app/controllers/acitvityFilterCtrl.js') ?>"></script>
            <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>
            
            <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
            <script src="<?php echo base_url('assets/admin/js/app/directives/dashboard.js') ?>"></script>
            <script src="<?php echo base_url('assets/admin/js/app/filters/helperFilters.js') ?>"></script>
            
            <script>
                function sucessMsz() {
                    $('.notifications').addClass('active');
                    setTimeout(function() {
                        $('.notifications').removeClass('active');
                    }, 3000);
                }
                $(function() {
                    //$('select').selectbox();
                    $('#dateFrom').datepicker();
                    $('#dateTo').datepicker();
                    $('.datepicker').datepicker();
                    $('#ui-datepicker-div').mouseup(function(e) {
                        return false;
                    });

                    setTimeout(function(){ 
                        $(".chosen-select").chosen();
                    }, 1000);

                });      
            </script>    
        <?php
    break; 
    case 'dashboard_detail':
        $active_menu_tab = 'dashboard';
        ?>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/dashboardDetail.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/directives/dashboard.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/filters/helperFilters.js') ?>"></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/MainController.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/NewsFeedController.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/services.js' ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/notes.js') ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/userList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/userList.js' ?>"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>plugins/socket.io-1.3.5.js?v=1.0"></script>
        <?php
        break;   
    case 'banner':
        $active_menu_tab = 'banner';
        ?>
        <script src="<?php echo base_url('assets/admin/js/vendor/ngAutocomplete.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/lib/BaseControl.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/advertise.js') ?>"></script>
        <script src="<?php echo base_url('assets/admin/js/app/services/advertise.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/vendor/jquery.fineuploader-3.4.1.min.js') ?>"></script>
<?php
        break;
    case 'modules':
        $active_menu_tab = 'modules';
        ?>
        <script src="<?php echo base_url('assets/admin/js/app/controllers/module.js') ?>"></script>
        <?php
        break;
    case 'album':
        $active_menu_tab = 'album';
        ?>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/album.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/album.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/vendor/jquery.fineuploader-3.4.1.min.js' ?>"></script>

        <?php
        break; 
        case 'album_list':
            $active_menu_tab = 'album_list';
            ?>
            <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
            <script src="<?php echo $assets_path.'admin/js/app/controllers/albumList.js' ?>"></script>
            <script src="<?php echo $assets_path.'admin/js/app/services/album.js' ?>"></script>
            <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
            <script src="<?php echo $assets_path.'admin/js/vendor/jquery.fineuploader-3.4.1.min.js' ?>"></script>
    
            <?php
            break; 
        case 'album_detail':
            $active_menu_tab = 'album_detail';
            ?>
            <script src="<?php echo $assets_path.'admin/js/app/controllers/notes.js' ?>"></script>
            <script src="<?php echo $assets_path.'admin/js/app/controllers/crmUserList.js' ?>" ></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/crmUserListExtra.js' ?>" ></script>
        <script src="<?php echo $assets_path.'js/jquery.initialize.min.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/crmUserFun.js' ?>"></script>
        
            <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
            <script src="<?php echo base_url('assets/admin/js/app/directives/dashboard.js') ?>"></script>
            <script src="<?php echo $assets_path.'admin/js/app/controllers/albumPhotoList.js' ?>"></script>
            
            
        <script src="<?php echo $assets_path.'admin/js/app/services/userList.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/controllers/communication.js' ?>"></script>
        <script src="<?php echo $assets_path.'admin/js/app/services/communication.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'admin/js/app/controllers/dashboardFeed.js' ?>"></script>
        
        
        <script src="<?php echo $assets_path.'js/app/wall/MainController.js' ?>"></script>
        <script src="<?php echo $assets_path.'js/app/wall/NewsFeedController.js' ?>"></script>
        
        <script src="<?php echo $assets_path.'js/app/wall/services.js' ?>"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>plugins/socket.io-1.3.5.js?v=1.0"></script>
        <script src="<?php echo $assets_path ?>js/wall.js"></script>
        <script type="text/javascript" src="<?php echo $assets_path ?>js/mycustom.js<?php version_control(); ?>"></script>

            <script src="<?php echo $assets_path.'admin/js/app/services/album.js' ?>"></script>
            <script src="<?php echo base_url('assets/admin/js/app/services/dashboard.js') ?>"></script>
            <script src="<?php echo $assets_path.'admin/js/vendor/jquery.fineuploader-3.4.1.min.js' ?>"></script>
    
            <?php
            break; 
}
?>
<!-- End of Switch Case-->

<!--Additional JS Files --> 
<script src="<?php echo $assets_path.'admin/js/plugins.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/main.js' ?>"></script>
<script src="<?php echo $assets_path.'admin/js/admin_functions.js' ?>"></script>

<script type="text/javascript">
            $(document).ready(function () {
                setMenuTabActive('<?php echo $active_menu_tab; ?>');
            });
</script>

<script>
    /* Document ready */
    $(function () {
        $('.icon-zoomlist').lightBox();

        var winHeight = $(window).height() - 110;
        $('.admin-login').height(winHeight);

        $('#mycarousel').jcarousel();
        //Hide next prev button in jcarousel
        var total_items = $('#mycarousel li').length;
        if (total_items < 4)
        {
            $('.jcarousel-prev, .jcarousel-next, .jcarousel-pagination').addClass('hide');
        }

        /*$('.selectbox_ele').selectbox({
         onChange: function (val, inst)
         {
         var pageName = '<?php echo $this->page_name ?>';
         
         if($(this).attr("id") == "languages"){
         SelectedLanguageChanged();
         }
         else if(pageName == 'google_analytics'){
         $("#filter_val").val($("#Selectorformetric").val());
         angular.element(document.getElementById('googleAnalyticsCtrl')).scope().loadAllAnalyticsData();
         loadGoogleAnalyticsChartOnScroll();
         }
         else if(pageName == 'google_analytics_device'){
         $("#filter_val").val($("#Selectorformetric").val());
         angular.element(document.getElementById('googleAnalyticsDevicesCtrl')).scope().loadAllAnalyticsDeviceData();
         }
         else if(pageName != 'login_analytics' && pageName != 'signup_analytics'){
         switch(val)
         {
         case '2'://Delete
         openPopDiv('delete_popup', 'bounceInDown');
         break;
         
         case '3'://Block
         openPopDiv('block_popup', 'bounceInDown');
         break;
         
         case '4'://UnBlock
         openPopDiv('unblock_popup', 'bounceInDown');
         break;
         
         case '5'://Communicate
         openPopDiv('communicate_single_user', 'bounceInDown');
         break;
         
         case '6'://ChangePassword
         openPopDiv('change_user_password', 'bounceInDown');
         break;
         
         case '7'://Approve
         openPopDiv('approve_popup', 'bounceInDown');
         break;
         }
         }
         
         //Call a js function for set value for analytics_filter and change line graph
         if($(this).attr("id") == "analytics_filter")
         changeLineGraph(val,pageName);
         
         },
         });*/
        showHideOption();
        /*$('#dateFrom').datepicker();
        $('#dateTo').datepicker();*/
        $('#ui-datepicker-div').mouseup(function (e) {
            return false;
        });
    });
    /* Document ready end */

    /* Window load */
    $(window).resize(function () {
        var winHeight = $(window).height() - 110;
        $('.admin-login').height(winHeight);
    });
    /* Window load end */
<?php 
    if(!in_array($this->page_name, array('users', 'dashboard', 'announcement','all_question', 'orientation', 'album', 'album_detail', 'crm_users', 'daily_digest', 'ward', 'banner'))) {
?>
    /* Load Google Chart */
    google.load("visualization", "1", {packages: ["corechart"]});
    /* Load Google Chart end */
    <?php
    }
    ?>

    function ChangeAnalyticData(pageName) {
        var val = $("#analytics_filter").val();
        changeLineGraph(val, pageName);
    }

    function ChangeGoogleAnalytic() {
        $("#filter_val").val($("#Selectorformetric").val());
        angular.element(document.getElementById('googleAnalyticsCtrl')).scope().loadAllAnalyticsData();
        loadGoogleAnalyticsChartOnScroll();
    }

    function ChangeGoogleAnalyticDeviceInfo() {
        $("#filter_val").val($("#Selectorformetric").val());
        angular.element(document.getElementById('googleAnalyticsDevicesCtrl')).scope().loadAllAnalyticsDeviceData();
    }

    function ProfileAction() {
        var val = $("#csutomSelect").val();
        switch (val)
        {
            case '2'://Delete
                openPopDiv('delete_popup', 'bounceInDown');
                break;

            case '3'://Block
                openPopDiv('block_popup', 'bounceInDown');
                break;

            case '4'://UnBlock
                openPopDiv('unblock_popup', 'bounceInDown');
                break;

            case '5'://Communicate
                openPopDiv('communicate_single_user', 'bounceInDown');
                break;

            case '6'://ChangePassword
                openPopDiv('change_user_password', 'bounceInDown');
                break;

            case '7'://Approve
                openPopDiv('approve_popup', 'bounceInDown');
                break;

            case '8'://login as user
                var userid = $("#hdnUserID").val();
                angular.element(document.getElementById('userCtrl')).scope().autoLoginUser();
                break;
        }
    }

    function redirectToBlockedIP() {
        window.location.href = base_url + 'blockedip';
    }

    function loadGoogleAnalyticsChartOnScroll() {
        var popularPages = $("#googleAnalyticPopularPages").outerHeight();
        var geoChartHeight = $("#googleAnalyticsGeoChart").outerHeight();
        var geoChartLoad = 0;
        var popularPagesLoad = 0;
        $(document).scroll(function () {
            if ($(this).scrollTop() > popularPages) {
                if (popularPagesLoad == 0) {
                //    angular.element(document.getElementById('googleAnalyticsCtrl')).scope().googleAnalyticPopularPages();
                }
                popularPagesLoad = 1;
            }
            if ($(this).scrollTop() > geoChartHeight) {
                if (geoChartLoad == 0) {
                   // angular.element(document.getElementById('googleAnalyticsCtrl')).scope().googleAnalyticsGeoChart();
                }
                geoChartLoad = 1;
            }
        });
    }

    function loadLoginAnalyticsChartOnScroll() {
        var Row3Height = $("#loginanalytic_row3").outerHeight();
        var Row4Height = $("#loginanalytic_row4").outerHeight();
        var Row3ChartLoad = 0;
        var Row4ChartLoad = 0;
        $(document).scroll(function () {
            if ($(this).scrollTop() > Row3Height + 80) {
                if (Row3ChartLoad == 0) {
                    angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginPopDaysChart();
                    angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginPopTimeChart();
                    angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginFailureChart();
                }
                Row3ChartLoad = 1;
            }
            if ($(this).scrollTop() > Row4Height + 80) {
                if (Row4ChartLoad == 0) {
                    angular.element(document.getElementById('loginAnalyticsCtrl')).scope().loginGeoChart();
                }
                Row4ChartLoad = 1;
            }
        });
    }

    function loadSignupAnalyticsChartOnScroll() {
        var Row3Height = $("#signupanalytic_row3").outerHeight();
        var Row4Height = $("#signupanalytic_row4").outerHeight();
        var Row3ChartLoad = 0;
        var Row4ChartLoad = 0;
        $(document).scroll(function () {
            if ($(this).scrollTop() > Row3Height + 80) {
                if (Row3ChartLoad == 0) {
                    angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupTimeChart();
                    angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupPopDaysChart();
                    angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupPopTimeChart();
                }
                Row3ChartLoad = 1;
            }
            if ($(this).scrollTop() > Row4Height + 80) {
                if (Row4ChartLoad == 0) {
                    angular.element(document.getElementById('signupAnalyticsCtrl')).scope().signupGeoChart();
                }
                Row4ChartLoad = 1;
            }
        });
    }

    $(document).ready(function () {
        $("#languages").val('en');

        $(".profile_select_div .sbOptions li").first().css("display", "none");

    });

    function SelectedLanguageChanged() {
        var langVal = $('#languages option:selected').val() == "" ? "en" : $('#languages option:selected').val(); //alert(langVal);
        $('#LanguageName').val(langVal);
        $("#txtReturnUrl1").val(window.location.href);
        //JavaScript way to post form
        document.languageForm.submit();
    }

</script>
<script src="<?php echo $assets_path.'plugins/chosen/js/chosen.jquery.min.js' ?>"></script>
<script src="<?php echo $assets_path.'plugins/chosen/js/chosen.js' ?>"></script>
<!-- <script src="<?php echo $assets_path.'admin/js/ImageSelect.jquery.js' ?>"></script> -->

<!-- For Auto Logout and Update logged in user time -->
<script src="<?php echo $assets_path.'admin/js/jquery.cookie.js' ?>"></script>
<script>
    $(document).ready(function () {

        /*$('.skill-input').tokenfield({
         autocomplete: {
         source: ['red', 'blue', 'green', 'yellow', 'violet', 'brown', 'purple', 'black', 'white'],
         delay: 100
         },
         showAutocompleteOnFocus: true
         })*/
        setInterval(function () {
            updateadminusertime();
        }, 60000);

        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        if (AdminLoginSessionKey && AdminLoginSessionKey != "" && auto_logout == 1) {
            ExpireCookie(auto_logout_time);
            setInterval(function () {
                if (!$.cookie('AdminLoginExpire') && AdminLoginSessionKey && AdminLoginSessionKey != "") {
                    signout();
                }
                //console.log($.cookie('AdminLoginExpire'));
            }, 2000);
            $(this).mousemove(function (e) {
                ExpireCookie(auto_logout_time);
            });
            $(this).keypress(function (e) {
                ExpireCookie(auto_logout_time);
            });
        }
    });

    function ExpireCookie(minutes) {
        var date = new Date();
        var m = minutes;
        date.setTime(date.getTime() + (m * 60 * 1000));
        $.cookie("AdminLoginExpire", date, {path: base_url + '/admin/', expires: date});
    }

    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip({container: "body"});
    });

</script>
