<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" ><p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Hi <?php echo $data['FirstLastName']; ?>,</p>
      <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">We received a forgot password request associated with this e-mail address. If you made this request, please follow the instructions below. </p>
      <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">One time password reset link is given below, you can reset your password by clicking on given link.</p>
      <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo anchor($data['Link'], 'Reset Password', 'style="color:#00529F;cursor:pointer; display:block; text-decoration:none;"');?></p>
      <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">If you did not request you can safely ignore this email. Rest assured your account is safe.</p>
