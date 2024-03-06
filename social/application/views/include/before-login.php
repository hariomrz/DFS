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
    <div>

    <div style="display:none;" class="message-popup alertify fadeInUp">
        <a onclick="$('.message-popup').hide();" class="icon">        
            <i class="ficon-cross"></i>
        </a>
        <span class="text" id="alertmessage"></span>
    </div>
    <?php if (trim(strtolower($this->page_name)) == 'signup' || trim(strtolower($this->page_name)) == 'signin') 
    { 
        $onboarding = true;
        $headerClass = 'header header-before header-transparent' ;
    }else{
        $onboarding = false;
        $headerClass = 'header';
    }
    ?>
    <?php if(!$onboarding){ ?>
    <div class="header-wrap">       
    
    <?php } ?>
        <header class="<?php echo $headerClass; ?>">                
            <!--NavBar-->
            <nav id="myNavbar" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
                <div class="container-fluid">                    
                    <div class="navbar-header">
                        <a class="navbar-brand logo" target="_self" href="<?php echo base_url(); ?>">
                            <?php
                            if (ENVIRONMENT == 'demo') {
                                ?>
                                <img src="<?php echo ASSET_BASE_URL ?>img/air_logo.png" alt="{{lang.web_name}}" title="{{lang.web_name}}" />
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
                      <?php if (trim(strtolower($this->page_name)) == 'signup') {  ?>  
                      <li><span class="text">Already a member?</span></li>
                      <li><a class="btn btn-default btn-sm" onclick="window.location = '<?php echo site_url('signin') ?>'">Login</a></li>
                      <?php }
                      else {?>
                            <li><span class="text">Not a member?</span></li>
                            <li>
                                <a class="btn btn-default btn-sm" onclick="window.location = '<?php echo site_url() ?>signup'">Sign Up</a>
                            </li>
                            <?php
                        }?>
                    </ul>
                </div>
            </nav>
            <!--//NavBar-->
        </header>
    <?php if(!$onboarding){ ?>
    </div>
     <?php } ?>
    <!--//Header-->

    <!-- <div class="loader-fad" style="display:none;">
        <div class="loader-view spinner48-b">&nbsp;</div>
    </div> -->


