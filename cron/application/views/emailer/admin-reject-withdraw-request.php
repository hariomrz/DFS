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
            We regret to inform you that your withdrawal request of <span><?php echo CURRENCY_CODE_HTML; ?><?php echo $content['amount']; ?></span> on <?php echo $date.'('.$timezone.') '; ?> could not be processed.
           
        <p>
            <span>Reason - </span><?php echo $content['reason']; ?><br>
            Please do contact us on <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?> </a>if you feel there is any discrepancy regarding this.
        </p>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>