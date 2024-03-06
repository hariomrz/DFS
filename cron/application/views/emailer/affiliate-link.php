<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
        Congratulations, You are now registered as an affiliate, Please share below link among your subscribers.<br>
You will earn a commission on each signup and deposit.<br><br>
            Your affiliate link : <a href="<?php  echo $content['aff_link']; ?>" class="!booking-btn"><?php  echo $content['aff_link']; ?></a>
            <br>
            
           
        <!-- <p>
            <span>Reason - </span><?php //echo $content['message']; ?><br>
            Please do contact us on <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?> </a>if you feel there is any discrepancy regarding this.
        </p> -->
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
