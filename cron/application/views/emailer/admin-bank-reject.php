<?php echo $this->load->view("emailer/header",array(),TRUE); 

if($content['allow_crypto']==1)
{
$crypto_or_bank = " crypto wallet";
}else{
$crypto_or_bank = " bank documents";
}
?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>
            Your <?php echo $crypto_or_bank;?> has been rejected. Please note that <?php echo $crypto_or_bank;?> approval is a compulsary step before <br>
            you can withdraw money.
           
        <p>
            <span>Reason - </span><?php echo $content['message']; ?><br>
            Please do contact us on <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?> </a>if you feel there is any discrepancy regarding this.
        </p>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
