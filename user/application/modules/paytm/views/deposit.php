<form method="post" action="<?php echo $url; ?>" method="post" name="paytmForm" id="paytmForm">
    <table border="1">
        <tbody>
        <?php
        foreach($paramList as $name => $value) {
            echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
        }
        ?>
       
        </tbody>
    </table>
</form>

<script>

    setTimeout(function(){submitPayTmForm(); }, 1000);
    function submitPayTmForm() { 
        var paytmForm = document.forms.paytmForm;
        paytmForm.submit();
    }
</script> 