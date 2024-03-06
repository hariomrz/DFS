<!--Start footer section-->
        <tr class="footer-tr">
            <td colspan="3" class="text-center">
                <div class="bootom">
                    <p class="copyright-p">
                    <?php 
                        echo "Invite your friends & earn as they play!";
                     ?>
                    </p>
                    <div class="booking-btn-wrapper">
                        <a href="<?php echo WEBSITE_URL; ?>refer-friend" class="invite-btn">Invite Now</a> 
                    </div>
                    <p class="copyright-p">
                    <?php 
                     $android_app = isset($this->app_config['android_app']['key_value'])?$this->app_config['android_app']['custom_data']:0;
                     $ios_app = isset($this->app_config['ios_app']['key_value'])?$this->app_config['ios_app']['custom_data']:0;
                    if(!empty($android_app['android_app_link']) || !empty($ios_app['ios_app_link'])) { ?>
                        Download the app 
                        <?php if(!empty($android_app['android_app_link'])){ ?>
                    	<a href="<?php echo $android_app['android_app_link']; ?>">
                    		<img src="<?php echo WEBSITE_URL;?>cron/assets/img/android-btn.png" class="android-btn">
                    	</a>
                        <?php } if(!empty($ios_app['ios_app_link'])){ ?>
                            <a href="<?php echo $ios_app['ios_app_link']; ?>">
                    		<img src="<?php echo WEBSITE_URL;?>cron/assets/img/iso-btn.png" class="android-btn">
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
                        <!-- <li>|</li>
                        <li>
                            <a href="#">Email Preferences</a>
                        </li> -->
                    </ul>
                    <ul class="bootom-nav">
                        <li>
                            <a href="<?php echo isset($this->app_config['fb_link']) ? $this->app_config['fb_link']['key_value'] : ''; ?>"><img src="<?php echo WEBSITE_URL;?>cron/assets/img/fb.png"></a> 
                        </li>
                        <li>
                            <a href="<?php echo isset($this->app_config['twitter_link']) ? $this->app_config['twitter_link']['key_value'] : ''; ?>"><img src="<?php echo WEBSITE_URL;?>cron/assets/img/twitter.png"></a>
                        </li>
                        <li>
                            <a href="<?php echo isset($this->app_config['instagram_link']) ? $this->app_config['instagram_link']['key_value'] : ''; ?>"><img src="<?php echo WEBSITE_URL;?>cron/assets/img/instagram.png"></a>
                        </li>
                    </ul>
                    <p class="copyright-p">&copy; <?php echo date("Y"); ?> <span><?php echo isset($this->app_config['site_title']) ? $this->app_config['site_title']['key_value'] : 'Fantasy Sports'; ?>.</span> All Rights Reserved </p>
                </div>
            </td>
        </tr>
        <!--End footer section-->
	</table>
</body>
</html>