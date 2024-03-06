<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
$converted_date = get_timezone(strtotime($content['date_added']),'d M Y',$this->app_config['timezone']);
$date = $converted_date['date'];
$timezone =  $converted_date['tz'];
?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
            We're happy to inform you that your withdrawal request of <span><?php echo CURRENCY_CODE_HTML; ?><?php echo $content['amount']; ?></span> on <?php echo $date.'('.$timezone.') '; ?> has been processed.
        </p>
        <p>
            The amount will be credited to your account within next 7 working days! <br>
            Game on <img src="<?php echo WEBSITE_URL;?>cron/assets/img/hand-img.png">
        </p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>" class="booking-btn">Play Now</a> 
        </div>

    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>