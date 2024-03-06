<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" ><p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Hi <?php echo $data['FirstLastName']; ?>,</p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Message from Administrator.</p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo $data['MainContent']; ?></p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">If you have any questions or require assistance, you can write to us anytime at <a href="mailto:<?php echo $data['VCA_Info_Email']; ?>" style="color:#00529F;cursor:pointer; display:block; text-decoration:none;"><?php echo $data['VCA_Info_Email']; ?></a></p>