<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
            We regret to inform you that the contest  with entry fee of <span><?php echo CURRENCY_CODE_HTML.$content['entry_fee']; ?></span> for <span><?php echo $content['collection_name']; ?></span> game has been canceled due to insufficient participation.
        </p>
        <p> 
            <?php if($content['entry_fee'] > 0){
                echo "Your entry fee has been refunded. ";
            }
            ?>
            Dont worry though, you can join other contests to win more cash prizes! 
        </p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>lobby" class="booking-btn">More Fixtures</a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>