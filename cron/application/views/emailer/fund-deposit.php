<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <!-- Admin event -->
        <?php if(isset($content['source']) && $content['source'] == "0"){ ?>
            <p>
                Congratulations!!!<br>
                <?php if(!empty($content['transaction_amount_type']) && $content['transaction_amount_type']=='BONUS_CASH') { ?>
                    <span><?php echo $content['amount']; ?> bonus cash</span>
                    <?php }else if(!empty($content['transaction_amount_type']) && $content['transaction_amount_type']=='COINS'){ ?>
                    <span><?php echo $content['amount']; ?> coin/s</span>
                <?php }else{ ?>
                    <span><?php echo CURRENCY_CODE_HTML; ?><?php echo $content['amount']; ?></span>
                <?php } ?>
                has been credited to your wallet by the site admin.
            </p>
            <p>
                <span>Reason - </span> <?php echo $content['reason']; ?><br>
                Game on <img src="<?php echo WEBSITE_URL;?>cron/assets/img/hand-img.png">
            </p>
            <div class="booking-btn-wrapper text-left">
                <a href="<?php echo WEBSITE_URL; ?>" class="booking-btn">Play Now</a> 
            </div>
        <?php }else{ ?>
            <p>
                You're all set! 
                <?php if(!empty($content['transaction_amount_type']) && $content['transaction_amount_type']=='BONUS_CASH') { ?>
                    <span><?php echo $content['amount']; ?> bonus cash</span>
                <?php }else{ ?>
                    <span><?php echo CURRENCY_CODE_HTML; ?><?php echo $content['amount']; ?></span>
                <?php } ?>
                has been deposited in your wallet. Build your All-Star team and start <br>
                playing!
            </p>
            <p>
                Game on <img src="<?php echo WEBSITE_URL;?>cron/assets/img/hand-img.png">
            </p>
            <div class="booking-btn-wrapper text-left">
                <a href="<?php echo WEBSITE_URL; ?>my-wallet" class="booking-btn">Check Balance</a> 
            </div>
        <?php } ?>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>