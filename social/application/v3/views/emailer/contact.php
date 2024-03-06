<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Hello,</p>
    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Here are the details of the new enquiry :</p>
    <?php if($data['Name']) { ?>
        <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Name: <strong><?php echo $data['Name']; ?></strong></p>
    <?php } ?>
    <?php if($data['Mobile']) { ?>
        <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Phone Number: <strong><?php echo $data['Mobile']; ?></strong></p>
    <?php } ?>
    <?php if($data['Message']) { ?>
        <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Message: <strong><?php echo $data['Message']; ?></strong></p>
    <?php } ?>
    