 
<form id="payuForm" action="<?php echo $action; ?>" method="post" name="payuForm">
    <input readonly="1" type="hidden" name="key" value="<?php echo $key ?>" />
    <input readonly="1" type="hidden" name="hash" value="<?php echo $hash ?>"/>
    <input readonly="1" type="hidden" name="txnid" value="<?php echo $txnid ?>" />

    <!-- User fields -->
    <input readonly="1" name="amount" value="<?php echo $amount; ?>" />
    <input readonly="1" name="firstname" id="firstname" value="<?php echo $firstname; ?>" />
    <input readonly="1" name="email" id="email" value="<?php echo $email; ?>" />
    <input readonly="1" name="phone" value="<?php echo $phone; ?>" />
    <textarea readonly="1" name="productinfo" value="<?php echo $productinfo; ?>" id="productinfo"><?php echo $productinfo; ?></textarea>
    <input readonly="1" name="surl" value="<?php echo $surl ?>" size="64" />
    <input readonly="1" name="furl" value="<?php echo $furl ?>" size="64" />
    <input readonly="1" type="hidden" name="service_provider" value="payu_paisa" size="64" />
    <?php if (!$hash) { ?>
        <input type="submit" value="Submit" />
    <?php } ?>
</form>
<script>
    /*var hash = '<?php echo $hash ?>';
    setTimeout(function(){alert("okay");submitPayuForm(); }, 1000);
    function submitPayuForm() { 
        var payuForm = document.forms.payuForm;
        payuForm.submit();
    }*/
</script> 