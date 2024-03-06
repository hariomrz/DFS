<?php $this->load->view("emailer/header"); ?>
<tr>
  <td style="background-color:#ffffff;"> </td>
</tr>
<tr>
  <td style="background-color:#ffffff;"> </td>
</tr>
<tr>
  <td style="padding:0 30px 0 30px;background-color:#ffffff;">
	<p style="color:#3FAFEF;font-size:18px;font-family:'MuliBold';margin:0px;padding:0;font-weight:bold;line-height:50px;">Dear *|firstname|* *|lastname|*,</p>
  </td>
</tr>
<tr>
  <td style="background-color:#ffffff;"> </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:'MuliRegular';font-size:14px;">
  We are sorry to see that you have not utilized bonus cash <b>₹*|bonus_cash_amount|*</b> in expiration duration of <b>*|expiration_duration|*</b> days.</td>
</tr>
<tr>
  <td style="background-color:#ffffff;"> </td>
</tr>
<tr>
  <td style="background-color:#ffffff;"> </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:'MuliRegular';font-size:14px;">
	Cheers,<br>
	Fantasy Sports Team
  </td>
</tr>
<tr>
  <td style="background-color:#ffffff;"> </td>
</tr>
<tr>
  <td style="background-color:#ffffff;"> </td>
</tr>
<tr>
  <td> </td>
</tr>
<?php $this->load->view("emailer/footer"); ?>