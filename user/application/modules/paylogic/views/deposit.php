<?php 
$url = PAYLOGIC_PAY_TURL;
if(ENVIRONMENT=='production')
{
    $url = PAYLOGIC_PAY_URL;
}
echo '
<html>
<BODY>
<form name="paylogicform" action="'.$url.'" method="post">
    <input type="text" name="reqData" value="'.$fields['reqData'].'"  />
    <input type="text" name="merchantId" value="'.$fields['merchantId'].'"  />
    </form>
    </BODY>
    </HTML>
';
?>
<!-- <script>
    setTimeout(function(){submitPaylogicForm(); }, 1000);
    function submitPaylogicForm() { 
        var paylogicForm = document.forms.paylogicForm;
        paylogicForm .submit();
    }
</script>  -->




