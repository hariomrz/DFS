<?php
$attr = [
'name'=>'ipayform'
];
echo form_open(IPAY_ACTION,$attr);
foreach($fields as $name=>$value){
    echo form_hidden($name,$value);
}
//echo form_submit("submit","submit");
echo form_close();
?>
<script>
    setTimeout(function(){submitIpayForm(); }, 1000);
    function submitIpayForm() { 
        var ipayForm = document.forms.ipayform;
        ipayForm.submit();
    }
</script> 