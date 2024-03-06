<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
            You're almost on the verge of verifying your email address. Please click on the button <br> to confirm -
        </p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo $content['link']; ?>" class="booking-btn">Verify Email</a> 
        </div>
        <p>Or paste this link into your browser : <a href="<?php echo $content['link']; ?>"><?php echo $content['link']; ?></a></p>

    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>