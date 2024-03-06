<?php
$attr = [
    'name'=>'cashfree',
    'id'=>'redirectForm',
    'method'=>'post'
    ];
echo form_open($url,$attr);
foreach($request as $name=>$value){
    echo form_hidden($name,$value);
    }
    // echo form_submit("submit","submit");
echo form_close();
?>
<script>
    setTimeout(function(){submitCashfreeForm(); }, 1000);
    function submitCashfreeForm() { 
        var cashfree = document.forms.cashfree;
        cashfree.submit();
    }
</script> 
