<form id="payForm" action="<?php echo $action; ?>" method="post" name="payForm">
    <input readonly="1" type="hidden" name="mid" value="<?php echo $mid ?>" />
    <input readonly="1" type="hidden" name="hash" value="<?php echo $hash ?>"/>
    <input readonly="1" type="hidden" name="txnid" value="<?php echo $txnid ?>" />
    <input readonly="1" type="hidden" name="refid" value="<?php echo $refid ?>" />

    <!-- User fields -->
    <input readonly="1" name="amount" value="<?php echo $amount; ?>" />
    <input readonly="1" name="title" id="title" value="<?php echo $title; ?>" />
    <input readonly="1" name="mobile" value="<?php echo $mobile; ?>" />
    <input readonly="1" name="email" id="email" value="<?php echo $email; ?>" />
    <input readonly="1" name="surl" value="<?php echo $surl ?>"/>
    <input readonly="1" name="furl" value="<?php echo $furl ?>"/>
    <?php if (!$hash) { ?>
        <input type="submit" value="Submit" />
    <?php } ?>
</form>