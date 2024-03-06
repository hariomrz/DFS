<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Hi <?php echo stripslashes($data['FirstLastName']); ?>,</p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Administrator has invited you as a Beta tester, please use the private BETA invitation code given below: </p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo $data['Code']; ?></p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><a style="color:#00529F;cursor:pointer; display:block; text-decoration:none;" href="<?php echo $data['InviteUrl']; ?>">Please Click here to access our site.</a></p>