<tbody>
  <tr>
    <td align="center" style="padding:60px 0 0" class="mob-gutter">
      <h3 style="font-family: 'Roboto', sans-serif; font-weight: normal;font-style: normal; padding:0 0 30px; margin:0; font-size:22px;">Hi <b style="font-weight:700; color:#000;"><?php echo $data['To']['FirstName'] ?></b></h3>
      <?php if($data['participant_details']){ ?>
        <h3 style="font-family: 'Roboto', sans-serif; font-weight: normal;font-style: normal; padding:0 0 15px; margin:0; font-size:22px; color:#333;"><?php echo $data['participant_details'][0]['FirstName'] ?> participated in the contest</h3>
      <?php } ?>
      <p style="font-family: 'Roboto', sans-serif; font-weight: normal;font-style: normal; padding:0 0 20px 0; font-size:16px; color:#666;">Go ahead and participate in this contest to win some exciting stuff.</p>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0 0 30px 0;">
        <hr style="height:3px; width:50px; background-color:#FD6C4F; border:none; margin:0; border-radius:10px;">
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0 0 10px;">
      <img src="<?php echo base_url() ?>assets/img/emailer/win-iphonex.jpg"  class="email-img">
    </td>
  </tr>
  <tr>
    <td class="mob-gutter" align="center" style="padding:0 140px 40px 140px">                  
      <p style="font-family: 'Roboto', sans-serif; font-style: normal; font-size:18px; font-weight:700; color:#333; line-height:26px;"><?php echo $data['activity_data']['PostContent'] ?></p>                  
    </td>
  </tr>
  <tr>
    <td class="mob-gutter" align="center" style="padding:0 205px 40px 205px">                  
      <a style="font-family: 'Roboto', sans-serif; font-style: normal; line-height:20px; font-size:14px; font-weight:700;  cursor:pointer; display:block; padding:14px 0; color:#fff; background-color:#46348D; border-radius:3px; text-transform:uppercase;" href="<?php echo $data['activity_data']['Link'] ?>">View contest</a>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0">                  
      <p style="font-family: 'Roboto', sans-serif; font-weight:500;font-style: normal;font-size:14px;padding:0 0 15px 0; color:#9B9B9B; ">Thanks & Regards</p>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0 0 60px 0;">                  
      <p style="font-family: 'Roboto', sans-serif; font-weight:700;font-style: normal;font-size:14px;padding:0; color:#9B9B9B; ">Team VSocial</p>
    </td>
  </tr>