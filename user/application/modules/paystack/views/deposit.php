<?php
$url = 'https://checkout.paystack.com/'.$fields['access_code'];
$attr = [
'name'=>'paystackform'
];
unset($fields['access_code']);
echo form_open($url,$attr);
foreach($fields as $name=>$value){
    echo form_hidden($name,$value);
}
//echo form_submit("submit","submit");
?>
<script>
    setTimeout(function(){submitPaystackForm(); }, 1000);
    function submitPaystackForm() { 
        var paystackForm = document.forms.paystackform;
        paystackForm .submit();
    }
</script> 

<?php echo form_close(); ?>