<!--Start footer section-->
<?php 
$site_title = isset($this->app_config['site_title']['key_value']) ? $this->app_config['site_title']['key_value'] : 'FantasySports';
$android_app = isset($this->app_config['android_app']['key_value']) && isset($this->app_config['android_app']['custom_data']) ? $this->app_config['android_app']['custom_data'] : array();
$ios_app = isset($this->app_config['ios_app']['key_value']) && isset($this->app_config['ios_app']['custom_data']) ? $this->app_config['ios_app']['custom_data'] : array();
$fb_link = isset($this->app_config['fb_link']['key_value']) ? $this->app_config['fb_link']['key_value'] : '';
$twitter_link = isset($this->app_config['twitter_link']['key_value']) ? $this->app_config['twitter_link']['key_value'] : '';
$instagram_link = isset($this->app_config['instagram_link']['key_value']) ? $this->app_config['instagram_link']['key_value'] : '';
?>
        <tr>
            <td colspan="3" class="info-td">
                Good Luck<br/>
                Team <?php echo $site_title; ?>
            </td>
        </tr>
        <tr class="footer-tr">
            <td colspan="3" class="text-center">
                <div class="bootom">
                    <p class="copyright-p">
                   Invite your friends & earn as they play!
                    </p>
                    <div class="booking-btn-wrapper">
                        <a href="<?php echo WEBSITE_URL; ?>refer-friend" class="invite-btn">Invite Now</a> 
                    </div>
                    <p class="copyright-p">
                    <?php if(!empty($android_app) || !empty($ios_app)){ ?>
                        Download the app 
                        <?php if(!empty($android_app['app_link'])){ ?>
                        <a href="<?php echo $android_app['app_link']; ?>">
                            <img src="<?php echo IMAGE_PATH;?>assets/img/android-btn.png" class="android-btn">
                        </a>
                        <?php } if(!empty($ios_app['app_link'])){ ?>
                            <a href="<?php echo $ios_app['app_link']; ?>">
                            <img src="<?php echo IMAGE_PATH;?>assets/img/iso-btn.png" class="android-btn">
                        </a>
                        <?php }
                        } ?>

                    </p>
                    <ul class="bootom-nav">
                        <li>
                            <a href="<?php echo WEBSITE_URL; ?>contact-us">Contact Us</a></li>
                        <li>|</li>
                        <li>
                            <a href="<?php echo WEBSITE_URL; ?>faq">FAQs</a>
                        </li>
                        <li>|</li>
                        <li>
                            <a href="<?php echo WEBSITE_URL; ?>rules-and-scoring">Rules & Scoring</a>
                        </li>
                    </ul>
                    <ul class="bootom-nav">
                        <?php if($fb_link != ""){ ?>
                            <li>
                                <a href="<?php echo $fb_link; ?>"><img src="<?php echo IMAGE_PATH;?>assets/img/fb.png"></a> 
                            </li>
                        <?php } ?>
                        <?php if($twitter_link != ""){ ?>
                            <li>
                                <a href="<?php echo $twitter_link; ?>"><img src="<?php echo IMAGE_PATH;?>assets/img/twitter.png"></a>
                            </li>
                        <?php } ?>
                        <?php if($instagram_link != ""){ ?>
                            <li>
                                <a href="<?php echo $instagram_link; ?>"><img src="<?php echo IMAGE_PATH;?>assets/img/instagram.png"></a>
                            </li>
                        <?php } ?>
                    </ul>
                    <p class="copyright-p">&copy; <?php echo date("Y"); ?> <span><?php echo $site_title; ?>.</span> All Rights Reserved </p>
                </div>
            </td>
        </tr>
        <!--End footer section-->
    </table>
</body>
</html>