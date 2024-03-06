<script type="text/javascript">
    var LoginSessionKey = '';
    var LoggedInUserGUID = '';
    var IsNewsFeed = 0;
    var visibleBaner = true;
    var Dragging = false;
    var LoggedInUserID = '';</script>
<?php $BodyClass = isset($body_class) ? $body_class : '' ; ?>
    <?php if (isset($whiteBG) && !empty($whiteBG)) { ?>
    <body ng-controller="settingsCtrl" class="white-container <?php echo $BodyClass ?>" ng-init="getSettings()">
    <?php } else { ?>
    <body ng-controller="settingsCtrl" class="<?php echo $BodyClass ?>" ng-init="getSettings()">
    <?php } ?>
    <?php if (isset($IsNewsFeed) && $IsNewsFeed == '1' && !$IsLoggedIn) { ?>
        <div id="fb-root"></div>
    <?php } ?>
    <div ng-controller="UserProfileCtrl" ng-init="fetchDetails('load')" id="UserProfileCtrl">
<div style="display:none;" class="message-popup alertify fadeInUp">
  <a onclick="$('.message-popup').hide();" class="icon">        
    <i class="ficon-cross"></i>
  </a>
  <span class="text" id="alertmessage"></span>
</div>
<div class="header-wrap">
    <header  class="header">
  <!--NavBar-->
      <nav id="myNavbar" class="nav navbar-inverse navbar-fixed-top" role="navigation">        
          <div class="container-fluid">
            
            <div class="navbar-header"> 
            	<a class="navbar-brand logo" href="<?php echo base_url(); ?>" target="_self">
                <?php
                  if(ENVIRONMENT == 'demo')
                  {
                ?>
                  <img src="<?php echo ASSET_BASE_URL ?>img/air_logo.png" alt="{{lang.web_name}}" title="{{lang.web_name}}" />
                <?php      
                    }
                    else
                    {
                ?>        
                  <img src="<?php echo ASSET_BASE_URL ?>img/logo.svg" alt="{{lang.web_name}}" title="{{lang.web_name}}" />
                <?php
                    }
                ?>          
              </a>
            </div>
            <!--Primary Nav-->
            <?php
                 if((isset($IsNewsFeed) && $IsNewsFeed=='1') || (isset($pname) && $pname=='wall') || (trim(strtolower($this->page_name))=='group') || (trim(strtolower($this->page_name))=='events') || (trim(strtolower($this->page_name))=='forum') || (isset($pname) && $pname=='wiki') || (isset($pname) && $pname=='terms') )
                {
            ?>      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>    
                    <ul class="nav navbar-right">
                        <li><a class="btn btn-default outline btn-sm" target="_self" href="<?php echo site_url('signin'); ?>">Login</a></li>
                        <li><a class="btn btn-default outline btn-sm" target="_self" href="<?php echo site_url('signup'); ?>">Join</a></li>
                    </ul>
                    <div class="navbar-collapse collapse main-nav" id="navbar">
                      <div class="header-right">
                        <ul class="navigation">
                            <li class="<?php if (isset($pname) && $pname == 'dashboard' && (!isset($sub_name) || $sub_name!='forum')) { ?>active<?php } ?>">
                                <a target="_self" data-active="dashboard" href="<?php echo site_url('feeds') ?>">
                                <?php echo lang('news_feed'); ?>
                                </a>
                            </li>
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

                            <li ng-cloak ng-if="Settings.m1 == '1'" class="<?php if (isset($pname) && $pname == 'groups') { ?>active<?php } ?>">
                                <a target="_self"  data-active="groups" href="<?php echo site_url('group'); ?>">
                                <?php echo lang('menu_groups'); ?>
                                </a>
                            </li>
                            <li ng-cloak ng-if="Settings.m38 == '1'" class="<?php if (isset($pname) && $pname == 'wiki') { ?>active<?php } ?>">
                                <a target="_self" data-active="wiki" href="<?php echo site_url('article'); ?>">
                                    Articles
                                </a>
                            </li>
                            
                        </ul>
                        </div>
                    </div>
                    <?php
                        } else {
                    ?>                    
                        <ul class="nav navbar-right">                      
                          <li><span class="text">Not a member?</span></li>
                          <li><a class="btn btn-default btn-sm" onclick="window.location = '<?php echo site_url() ?>signup'">Sign Up</a></li>                      
                        </ul>
                    <?php
                        }
                    ?>
                    </div>
                </nav>
                <!--//NavBar--> 
            </header>
        </div>
        <!--//Header-->

        <!-- <div class="loader-fad" style="display:none;">
            <div class="loader-view spinner48-b">&nbsp;</div>
        </div> -->
        <business-card data="businesscard"></business-card>
