<?php
$action = base_url().'user/stripe/deposit';
$secret_key = "sk_test_51IoUbuSBZMeSOpKcPBgN1hgDrBZ5HGdSNtZpXdzLyDxV4l57YXBmqHR6V4HqDIRi7yzDLJQRnYToRQ46BrSTPBkB00Aq00QH7J";
$publish_key = "pk_test_51IoUbuSBZMeSOpKcbnKTPEBXI5BZH8Mn2nko8yLC0bCU72abWscQ6ftN0DE1usePAOillML8XTCbco5QbPHTOPgY00yNQ9K4ya";
?>
<form action=<?php echo $action; ?> method="post">
<script
src = "https://checkout.stripe.com/checkout.js" class="stripe-button"
data-key = "<?php echo $publish_key; ?>"
data-amount = "500"
data-name = "testing"
data-description = "test description"
data-image = ""
data-currency = "inr"
data-email = "rathoreakhilesh74@gmail.com"
data-invoice = "564665"
></script>
</form>
