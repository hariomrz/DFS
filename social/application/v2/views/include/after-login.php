<?php
//header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
//header("Pragma: no-cache");
//header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
?>
<style type="text/css">
    tooltip div {
        text-align: left;
    }
</style>
<?php $BodyClass = isset($body_class) ? $body_class : '' ; ?>

<?php if ($this->session->userdata('UserStatusID') == 1 || $this->session->userdata('UserStatusID') == 6) { ?>   
    <?php if (!empty($whiteBG)) { ?>
            <body data-type="alertShow" ng-controller="settingsCtrl" ng-init="getSettings()" <?php if (isset($CoverImageState) && $CoverImageState != 1) {
                echo 'class="naveFixed bannerHide '.$BodyClass.'"';
        } else {
             echo 'class="white-container"';
        } ?>>    
    <?php } else { ?>
        <body data-type="alertShow" ng-controller="settingsCtrl" ng-init="getSettings()" <?php if (isset($CoverImageState) && $CoverImageState != 1) {
            echo 'class="naveFixed bannerHide '.$BodyClass.'"';
        } else { echo 'class="'.$BodyClass.'"'; } ?>>
    <?php } ?>
    
        <div class="emailconfirm-alert" ng-cloak>
            {{lang.confirmEmailMsg}}&nbsp;
            <a ng-init="getEmailSentCount()" ng-if="EmailSentCount<5" href="javascript:void(0);" ng-click="ResendActivationLink('<?php echo $this->session->userdata('UserGUID')?>');" >&nbsp;Resend email</a> 
            <span ng-if="EmailSentCount<5">OR</span>
            <a href="javascript:void(0);" data-toggle="modal" data-target="#changeEmail">change email.</a> 
            <i class="remove-confirm-strip icon-removealt ficon-cross"></i>
        </div>


    <?php } else { ?>
        <?php if (isset($whiteBG) && !empty($whiteBG)) { ?>
        <body ng-controller="settingsCtrl" ng-init="getSettings(<?php echo $this->session->userdata('isSuperAdmin'); ?>)" <?php if (isset($CoverImageState) && $CoverImageState != 1) {
            echo 'class="naveFixed bannerHide white-container '.$BodyClass.'"';
        } else {
            echo 'class="white-container '.$BodyClass.'"';
        } ?>>
    <?php } else { ?>
        <body ng-controller="settingsCtrl" ng-init="getSettings(<?php echo $this->session->userdata('isSuperAdmin'); ?>)" <?php if (isset($CoverImageState) && $CoverImageState != 1) {
            echo 'class="naveFixed bannerHide '.$BodyClass.'"';
        } else { echo 'class="'.$BodyClass.'"'; } ?>>
    <?php } ?>
<?php } ?>
    <div ng-controller="UserProfileCtrl" ng-init="fetchDetails('load')" id="UserProfileCtrl">
        <div style="display:none;" class="alert-desk fadeInDown">You are now using My Desk</div>

        <div style="display:none;" class="message-popup alertify fadeInUp">
            <a onclick="$('.message-popup').hide();" class="icon">        
                <i class="ficon-cross"></i>
            </a>
            <span class="text" id="alertmessage"></span>
        </div>
        <div class="header-wrap">
            <header class="header" ng-class="(IsMyDeskTab) ? 'my-desk' : '';">
                <!--NavBar-->
                <nav id="myNavbar" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
                    <div class="container-fluid">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbarCollapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
                        </button>
                        <div class="navbar-header">
                            <a class="navbar-brand logo" target="_self" href="<?php echo site_url() ?>">
                            <?php
                            if (ENVIRONMENT == 'demo') {
                            ?>
                                    <img src="<?php echo ASSET_BASE_URL . 'img/air_logo.png'; ?>" alt="{{lang.web_name}}" />
                            <?php
                            } else {
                                ?>
                                    <img src="<?php echo ASSET_BASE_URL ?>img/logo.svg" alt="{{lang.web_name}}" title="{{lang.web_name}}" />
                                <?php
                            }
                            ?>
                            </a>
                        </div>
                        <ul class="nav navbar-right">
                            <li>
                                <!-- Notification  -->
                                <div class="notification-block" id="NotificationCtrl" ng-controller="NotificationCtrl as NC" ng-init="getNotificationCount()">
                                    <?php $this->load->view('include/notification-top') ?>
                                </div>
                            </li>
                            <li>
                              <!--//Primary Nav-->
                                <div class="user-nav">
                                    <a class="user-thumb" ng-click="(isSuperAdmin) ? setDummyUsers() : '' ;" data-toggle="dropdown">
                                        <img err-name="{{config_detail.LoggedInUserName}}" id="profilepictop" ng-src="{{'<?php echo IMAGE_SERVER_PATH.'upload/profile/36x36/'.$this->session->userdata('ProfilePicture') ?>'}}" class="img-circle" />
                                    </a>
                                    
                                    <ul class="dropdown-menu login-user-dropdown">
                                       <li class="users visible-xs visible-sm" ng-if="isSuperAdmin">
                                            <a target="_self" ng-href="{{user_profile_url}}">
                                                <figure>
                                                    <img 
                                                         err-name="{{config_detail.LoggedInUserName}}" id="profilepictop" ng-src="{{'<?php echo IMAGE_SERVER_PATH.'upload/profile/36x36/'.$this->session->userdata('ProfilePicture') ?>'}}" class="img-circle"
                                                    />
                                                </figure>
                                                <span ng-bind="config_detail.LoggedInUserName"></span>
                                            </a>
                                        </li>

                                        <li class="hidden-sm hidden-xs">
                                            <div class="user-dropdown-scroll" id="notifyscroll_dummy_user">
                                                <ul>
                                                    <li class="users" ng-repeat="(key, dummyUser) in dummyUsers" ng-click="setDummyUser(dummyUser)">
                                                        <a >
                                                            <figure>
                                                                <img 
                                                                    err-name="{{dummyUser.Name}}" 
                                                                    ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/36x36/';?>{{dummyUser.ProfilePicture}}" 
                                                                    class="img-circle"
                                                                />
                                                            </figure>
                                                            <span ng-bind="dummyUser.Name"></span>
                                                            <label ng-if="key > 0 && dummyUser.TotalNotificationRecords > 0" ng-bind="dummyUser.TotalNotificationRecords"></label>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>                                                 
                                        
                                        <li ng-if="!isSuperAdmin">
                                            <div class="user-dropdown-scroll" id="notifyscroll_dummy_user">
                                                <ul>
                                                    <li class="users" >
                                                        <a target="_self" href="<?php echo site_url() . ($this->session->userdata('ProfileURL')) ? $this->session->userdata('ProfileURL') : $this->session->userdata('UserGUID'); ?>">
                                                            <figure>
                                                                <img 
                                                                    err-name="<?php echo $this->session->userdata('FirstName') .' '. $this->session->userdata('LastName') ?>" 
                                                                    ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/36x36/';?><?php echo $this->session->userdata('ProfilePicture') ?>" 
                                                                    class="img-circle"
                                                                />
                                                            </figure>
                                                            <span >
                                                                <?php echo $this->session->userdata('FirstName') .' '. $this->session->userdata('LastName') ?>
                                                            </span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li> 
                                        
                                        
                                        <li class="<?php if(isset($pname) && $pname=='myaccount'){ echo 'active'; } ?>" ng-class="(dummyUsers.length)? 'border-top' : ''" >
                                            <a target="_self" href="<?php echo site_url('myaccount') ?>">
                                                <span class="icon">
                                                    <i class="ficon-settings f-lg"></i>
                                                </span>
                                                <?php echo lang('settings');?>
                                            </a>
                                        </li>

                                        <li>
                                            <a ng-click="logout()" target="_self">
                                                 <span class="icon">
                                                    <i class="ficon-logout f-lg"></i>
                                                </span>
                                                <?php echo lang('menu_log_out');?>
                                            </a>
                                        </li>

                                        <li id="dummy_users_loader" style="display:none;">
                                            <div class="notification-loader" >
                                                <div class="spinner32"></div>
                                            </div>
                                        </li>                                                
                                    </ul>
                                </div>  
                            </li>
                        </ul>
                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <div class="navbar-collapse collapse main-nav" id="navbarCollapse">                          
                            <!-- <form class="global-search"> -->
                            <div class="global-search">
                                <div class="form-group">
                                    <div class="hidden-sm" initial-value="'<?php echo isset($Keyword) ? str_replace('%20', ' ', $Keyword) : ''; ?>'" angucomplete-alt id="search-input" placeholder="{{(SettingsData.m1=='1') ? 'Search for people, groups and more...' : 'Search for people and more' ;}}" pause="300" remote-url="<?php echo site_url() ?>api/search/all/5" remote-url-data-field="Data" search-fields="FirstName,LastName" title-field="FirstName,LastName" image-field="ProfilePicture" minlength="1"></div>
                                    <button class="btn" type="button" onclick="redirectToSearch('#search-input input')">
                                        <span class="icon">
                                            <i class="ficon-search f-lg"></i>
                                        </span>
                                    </button>
                                </div>
                                <input type="hidden" id="advancedSearchKeyword" value="<?php echo isset($Keyword) ? $Keyword : ''; ?>" />
                            </div>
                            <!-- </form> -->
                           
                            <!--Primary Nav-->
                            <div class="header-right">
                                <ul class="navigation">
                                    <?php if (( isset($IsNewsFeed) && ( $IsNewsFeed == '1' ) ) && !$isFileTab && !$isLinkTab): ?>
                                    <li class="switch" data-toggle="tooltip" data-placement="bottom" data-original-title="{{(IsMyDeskTab) ? 'Go To Newsfeed' : 'Go To My Desk' ;}}">
                                        <div class="toggle-checkbox reminder-tab">
                                            <?php if (isset($IsNewsFeed) && $IsNewsFeed == '1' && !isset($_GET['files']) && !isset($_GET['links'])): ?>
                                            <input id="mydesktoggle" ng-model="IsMyDeskTab" ng-change="resetFilterValues();" class="toggle" type="checkbox">
                                            <?php else: ?>
                                            <input id="mydesktoggle" ng-model="IsMyDeskTab" ng-change="resetFilterValues();" class="toggle" type="checkbox" disabled="disabled">

                                            <?php endif; ?>
                                            <label for=""></label>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                    <?php if(isset($IsNewsFeed) && $IsNewsFeed=='1' && !$isFileTab && !$isLinkTab && (!isset($sub_name) || $sub_name!='forum')): ?>
                                    <li ng-class="(IsMyDeskTab) ? '' : '<?php if(isset($pname) && $pname == 'dashboard' && (!isset($sub_name) || $sub_name!='forum')){ ?>active<?php } ?>';" class="dropdown-hover">
                                            <a data-toggle="dropdown" ng-click="resetAnnouncement()">Newsfeed</a>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a ng-click="filterAnnouncement()">
                                                      Announcement
                                                    </a>
                                                </li>
                                            </ul>
                                    </li>
                                    <?php else : ?>
                                    <li ng-class="(IsMyDeskTab) ? '' : '<?php if(isset($pname) && $pname == 'dashboard' && (!isset($sub_name) || $sub_name!='forum')){ ?>active<?php } ?>'">
                                        <a target="_self" ng-if="Settings.m2=='1'" data-active="dashboard" href="<?php echo site_url() ?>">
                                            <?php echo lang('news_feed');?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li ng-cloak ng-if="Settings.m33 == '1'" class="<?php if (isset($pname) && $pname == 'dashboard' && isset($sub_name) && $sub_name=='forum') { ?>active<?php } ?>">
                                        <a target="_self" data-active="discover" href="<?php echo site_url() ?>">
                                            Community
                                        </a>
                                    </li>

                                    <li ng-cloak ng-if="Settings.m14 == '1'" class="<?php if (isset($pname) && $pname == 'events') { ?>active<?php } ?>">
                                        <a target="_self" data-active="events" href="<?php echo site_url('events'); ?>">
                                        <?php echo lang('menu_events'); ?>
                                        </a>
                                    </li> 

                                    <li class="dropdown more-option">
                                        <a data-toggle="dropdown" class="hidden-sm hidden-xs">More <i class="ficon-arrow-down"></i></a>
                                        <ul class="dropdown-menu">
                                            <li ng-cloak ng-if="Settings.m1 == '1'" class="<?php if (isset($pname) && $pname == 'groups') { ?>active<?php } ?>">
                                                <a target="_self"  data-active="groups" href="<?php echo site_url('group'); ?>">
                                                <?php echo lang('menu_groups'); ?>
                                                </a>
                                            </li>

                                            <li ng-cloak ng-if="Settings.m18 == '1'" class="<?php if (isset($pname) && $pname == 'pages') { ?>active<?php } ?>">
                                                <a target="_self" ng-if="Settings.m18 == '1'" data-active="pages" href="<?php echo site_url('pages'); ?>">
                                                    <?php echo lang('menu_pages'); ?>
                                                </a>
                                            </li>


                                            <li ng-cloak ng-if="Settings.m38 == '1'" class="<?php if (isset($pname) && $pname == 'wiki') { ?>active<?php } ?>">                                                    
                                                <a target="_self" data-active="wiki" href="<?php echo site_url('article'); ?>">
                                                    Articles
                                                </a>
                                            </li>
                                            <li ng-cloak ng-if="Settings.m30 == '1'" class="<?php if (isset($pname) && $pname == 'polls') { ?>active<?php } ?>">
                                                <a target="_self"  data-active="poll" href="<?php echo site_url('poll'); ?>">
                                                    <?php echo lang('menu_polls'); ?>
                                                </a>
                                            </li>
                                            <li class="<?php if (isset($pname) && $pname == 'files') { ?>active<?php } ?>">
                                                <a target="_self" data-active="files" href="<?php echo site_url('dashboard'); ?>?files=1">
                                                <?php echo lang('files'); ?>
                                                </a>
                                            </li>
                                            <li class="<?php if (isset($pname) && $pname == 'links') { ?>active<?php } ?>">
                                            <li <?php echo (isset($pname) && $pname == 'links' ) ? 'class="active"' : ''; ?>">
                                                <a target="_self" data-active="links" href="<?php echo site_url('dashboard'); ?>?links=1">
                                                <?php echo lang('links'); ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>                                
                            </div>
                        </div>
                        
                    </div>
                </nav>
                <!--//NavBar-->
            </header>
        </div>
        <!--//Header-->
        <div class="loader-fad" ng-cloak>
            <?php if (isset($IsNewsFeed) && $IsNewsFeed == '1'): ?>
                <div class="loader-view"></div>
            <?php else: ?>
                <div class="loader-view spinner48-b"></div>
            <?php endif; ?>
        </div>
        <business-card data="businesscard"></business-card>