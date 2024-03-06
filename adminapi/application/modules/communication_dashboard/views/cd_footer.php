
<td colspan="3" class="text-center">
                <div class="bootom">
                    <p class="copyright-p">
                    <?php if($content['int_version']==1){
                        echo "Invite your friends & earn coins as they play!";
                        }
                        else{
                        echo "Invite your friends & earn as they play!";
                        } ?>
                    </p>
                    <div class="booking-btn-wrapper">
                        <a href="<?php echo WEBSITE_URL; ?>refer-friend" class="invite-btn">Invite Now</a> 
                    </div>
                    <p class="copyright-p">
                    <?php if(!empty(ANDROID_APP_LINK) || !empty(IOS_APP_LINK)){ ?>
                        Download the app 
                        <?php if(!empty(ANDROID_APP_LINK)){ ?>
                    	<a href="<?php echo ANDROID_APP_LINK; ?>">
                    		<img src="<?php echo WEBSITE_URL;?>cron/assets/img/android-btn.png" class="android-btn">
                    	</a>
                        <?php } if(!empty(IOS_APP_LINK)){ ?>
                            <a href="<?php echo IOS_APP_LINK; ?>">
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
                            <a href="<?php echo FB_LINK; ?>"><img src="<?php echo WEBSITE_URL;?>cron/assets/img/fb.png"></a> 
                        </li>
                        <li>
                            <a href="<?php echo TWITTER_LINK; ?>"><img src="<?php echo WEBSITE_URL;?>cron/assets/img/twitter.png"></a>
                        </li>
                        <li>
                            <a href="<?php echo INSTAGRAM_LINK; ?>"><img src="<?php echo WEBSITE_URL;?>cron/assets/img/instagram.png"></a>
                        </li>
                    </ul>
                    <p class="copyright-p">&copy; <?php echo date("Y"); ?> <span><?php echo SITE_TITLE; ?>.</span> All Rights Reserved </p>
                </div>
            </td>