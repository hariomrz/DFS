<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td"> 
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <!-- Admin event -->
            <p>
	            Site admin withdraw <b>
                <?php if(!empty($content['transaction_amount_type']) && $content['transaction_amount_type']=='BONUS_CASH') { ?>
                    <span><?php echo $content['amount']; ?> bonus cash</span>
                    <?php }else if(!empty($content['transaction_amount_type']) && $content['transaction_amount_type']=='COINS'){ ?>
                    <span><?php echo $content['amount']; ?> coin/s</span>
                <?php }else{ ?>
                <span><?php echo CURRENCY_CODE_HTML; ?></span><?php echo $content['amount']; ?>
                <?php } ?>
                </b> from your wallet.<br>
            </p>
            <p>
                <span>Reason - </span> <?php echo $content['reason']; ?><br>
            </p>
        
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>