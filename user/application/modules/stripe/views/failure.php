<?php if ($hash != $posted_hash) { ?>
Invalid Transaction. Please try again.
<?php } else { ?>
<h3>Your order status is <?php echo $status ?>.</h3>
<h4>Your transaction id for this transaction is <?php echo $txnid ?>.</h4>
<?php } ?>