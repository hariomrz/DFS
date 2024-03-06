<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
        You have redeemed  <?php echo $content['reward_detail']['redeem_coins']; ?> coins for a <?php 
        
        switch($content['reward_detail']['type'])
        {
            case 1://bonus
            case 2://real
                echo $content['reward_detail']['value'].' '.$content['event'].'.';
            break;
            case 3://gift voucher
                echo $content['reward_detail']['detail'].' Worth '.CURRENCY_CODE_HTML.' '.$content['reward_detail']['value'].". Admin will contact you shortly on the registered Mobile No. for further details.";
            break;
            default:
            {

            }
        }
       
        
        
        ?>
                
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
                        <h6>Join Cash Contests</h6>
                        <p>Play your part, win cash 
                            contests & get recognised</p>
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