<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" ><p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Hi <?php echo $data['Email']; ?>,</p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo anchor($data['Link'], 'Accept Friend Request', 'style="color:#0053a0; text-decoration:none;"');?></p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo $data['Message']; ?></p>