<?php 
echo $this->load->view("emailer/header",array(),TRUE);

$pg_charges = "";
if(isset($content['isIW']) && $content['isIW']==1)
{
    $pg_charges = "(including payment gateway charges ".CURRENCY_CODE_HTML." ".$content['pg_fee'].")";
}
?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <!-- Admin event -->
        <?php if(isset($content['source']) && $content['source'] == "0"){ ?>
        	<p>
                This is to inform you that an amount of 
                <?php if(!empty($content['cash_type']) && $content['cash_type']=='1') { ?>
                    <span><?php echo $content['amount']; ?> bonus cash</span>
                <?php }else{ ?>
                    <span><?php echo CURRENCY_CODE_HTML; ?><?php echo $content['amount']." ".$pg_charges; ?></span>
                <?php } ?>
                has been deducted from your wallet.
            <p>
                <span>Reason - </span><?php echo $content['reason']; ?><br>
                Please do contact us on <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?> </a>if you feel there is any discrepancy regarding this.
            </p>
    	<?php }else{
            
            if($content['isIW'] ==1) { ?>
	        <p>                
	            Your withdrawal request of <b><span><?php echo CURRENCY_CODE_HTML; ?></span><?php echo $content['amount']." ".$pg_charges; ?></b> through <?php echo $content['payment_option']; ?> has been received.<br>
	            Amount will be credited in your bank account within 1 to 2 hours.<br></p>
        <?php }else{ ?>
             <p>                
	            Your withdrawal request of <b><span><?php echo CURRENCY_CODE_HTML; ?></span><?php echo $content['amount']." ".$pg_charges; ?></b> through <?php echo $content['payment_option']; ?> has been received.<br>
	            We will update you once your withdrawal request is processed.<br>
	        </p>
       <?php }
    
    
    } ?>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>