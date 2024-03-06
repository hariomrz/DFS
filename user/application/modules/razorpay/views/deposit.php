 
<!--  The entire list of Checkout fields is available at
 https://docs.razorpay.com/docs/checkout-form#checkout-fields -->

<form id="razorpayForm" action="<?php echo $action; ?>" method="POST">
  <script
    src="https://checkout.razorpay.com/v1/checkout.js"
    data-key="<?php echo $key; ?>"
    data-amount="<?php echo $amount; ?>"
    data-currency="<?php echo $currency; ?>"
    data-name="<?php echo $name; ?>"
    data-image=""
    data-description="<?php echo $description; ?>"
    data-prefill.name="<?php echo $prefill['name']; ?>"
    data-prefill.email="<?php echo $prefill['email']; ?>"
    data-prefill.contact="<?php echo $prefill['contact']; ?>"
    data-notes.shopping_order_id="<?php echo $merchant_order_id; ?>"
    data-order_id="<?php echo $order_id; ?>"
  >
  </script>
  <!-- Any extra fields to be submitted with the form but not sent to Razorpay -->
  <input type="hidden" name="shopping_order_id" value="<?php echo $merchant_order_id; ?>">
</form>
