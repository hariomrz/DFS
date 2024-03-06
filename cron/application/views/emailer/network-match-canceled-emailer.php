<?php echo $this->load->view("emailer/header", array(), TRUE); ?>
<!--Start middle section-->
<?php
$converted_date = get_timezone(strtotime($content['season_scheduled_date']),'d M Y',$this->app_config['timezone']);
$contest_date = $converted_date['date'];
$timezone =  $converted_date['tz'];
?>
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
            The <span><?php echo $content['collection_name']; ?> match</span> on <span><?php echo $contest_date.' ('.$timezone.') '; ?> </span>was abandoned & your entry fee has been refunded to your wallet. 
        </p>
        <p>
            Don't worry though! We've got plenty of other matches.
        </p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>lobby" class="booking-btn">Play the next game!</a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer", array(), TRUE); ?>    