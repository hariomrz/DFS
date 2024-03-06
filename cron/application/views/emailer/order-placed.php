<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
  <td style="background-color:#ffffff;text-align: right;font-size: 20px;font-weight: bold;padding: 10px 10px 0 0;">Order Placed</td>
</tr>
<tr>
  <td style="background-color:#ffffff;text-align: right;font-size: 12px;line-height: 14px;padding:15px 10px 0 10px;">Order Id :<span style="color: #3FAFEF;"><?php echo $content['product_order_unique_id']; ?></span></td>
</tr>

<tr>
  <td style="padding:15px 10px 0 10px;background-color:#ffffff;">
    <p style="color:#ea7517;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:22px;">Dear <?php echo $content['user_name']; ?>,</p>
  </td>
</tr>
<tr>
  <td style="padding:15px 10px 30px 10px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height: 14px;">
    Thank you for shopping with us. We'd like to let you know that <?php echo $content['product_name']; ?> has received your order, and is preparing it for shipment. 
  </td>
</tr>
<tr>
  <td style="padding:0 15px 10px 15px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
    <table style="width: 100%;border: 1px solid #F0F0F0;border-radius: 2px;">
      <tbody>
        <tr>
          <td style="color: #333;font-size: 14px; font-weight: bold;line-height: 18px;padding: 15px 15px 0 15px;">
            Order Number: <?php echo $content['product_order_unique_id']; ?>
          </td>
        </tr>
        <tr>
          <td style="font-size: 11px;line-height: 12px;color: #777;padding: 0 15px 0 15px;">
            Date: <?php echo $content['order_date']; ?>
          </td>
        </tr>
        <tr>
          <td style="padding: 10px 15px 15px 15px;width: 50%;">
            <table style="width: 100%;">
              <tbody>
                <tr>
                  <td style="width: 30%;">
                    <img src="<?php echo $content['product_image']; ?>" width="86px">
                  </td>
                  <td style="width: 70%;">
                    <p style="color: #223E7B;font-size: 12px;line-height: 20px; margin: 0;"><?php echo $content['product_name']; ?></p>
                    <p style="font-size: 13px;line-height: 22px; margin: 0;"><?php echo $content['total_price']; ?> Coins</p>
                    <p style="font-size: 12px;line-height: 18px; margin: 0;"><?php echo $content['short_desc']; ?></p>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
          <td style="padding: 10px 15px 15px 15px;width: 50%;">
             <table style="width: 100%;">
              <tbody>
                <tr>
                  <td style="width: 30%;">
                    <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/shippingAdd.png" width="72px">
                  </td>
                  <td style="width: 70%;">
                    <p style="color: #333;font-size: 12px;line-height: 20px;font-weight: bold; margin: 0;">Shipping Address</p>
                    <p style="color: #333;font-size: 12px;line-height: 20px;font-weight: bold; margin: 0;"><?php echo $content['user_name']; ?></p>
                    <p style="color: #333;font-size: 12px;line-height: 20px; margin: 0;"><?php echo $content['address'].", ".$content['state_name']." ".$content['zip_code']." ".$content['country_name']; ?></p>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Cheers,<br>
        <?php echo SITE_TITLE; ?> Team
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td></td>
</tr>
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>