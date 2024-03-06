<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
            We are glad to have you on onboard.<br>
            <?php echo SITE_TITLE; ?> is a product which evolved out of our passion for sports.<br> 
            We value what makes the game tick - The Fans <img src="<?php echo BASE_APP_PATH;?>cron/assets/img/smile.png">
                
        </p>

    </td>
</tr>
<tr>
    <td colspan="3">
        <table class="middle-table">
            <tr>
                <td colspan="3" class="text-center">
                    <h5>::: Here's how to use <?php echo SITE_TITLE; ?> :::</h5>
                </td>
            </tr>
            <tr>
                <td class="instruction-td">
                    <div class="select-match">
                        <div class="svg-icon">
                            <img src="<?php echo WEBSITE_URL;?>cron/assets/img/select-match.png" alt="calender-icon">
                        </div>
                        <h6>
                            Select a Match 
                        </h6>
                        <p>Select an upcoming match
                            of your choice</p>
                    </div>
                </td>
                <td  class="instruction-td">
                    <div class="create-team">
                        <div class="svg-icon">
                            <img src="<?php echo WEBSITE_URL;?>cron/assets/img/create-team.png" alt="calender-icon">
                        </div>
                        <h6>
                            Create your Team
                        </h6>
                        <p>Unleash your skills & knowledge
                             to build your own team</p>
                    </div>
                </td>
                <?php if(!empty($this->app_config['currency_code']['key_value'])){ ?>
                <td  class="instruction-td">
                    <div class="select-match">
                        <div class="svg-icon">
                            <img src="<?php echo WEBSITE_URL;?>cron/assets/img/join-contest.png" alt="calender-icon">
                        </div>
                        <h6>
                        <?php
                       if($this->app_config['int_version']['key_value']==1){
                           echo "Join Contests"; 
                        }
                        else{
                            echo "Join Cash Contests"; 
                            }
                        ?>
                        </h6>
                        <p>
                        <?php 
                        if($this->app_config['int_version']['key_value']==1){
                            echo "Play your part, win contests & get recognised";
                        }else{
                            echo "Play your part, win cash contests & get recognised";
                        }
                            ?></p>
                    </div>
                </td>
                <?php } ?>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td colspan="3">
        <div class="booking-btn-wrapper">
            <a href="<?php echo WEBSITE_URL; ?>" class="booking-btn">Play Now</a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>