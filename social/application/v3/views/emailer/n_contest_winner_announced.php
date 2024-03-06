<tbody>
  <tr>
    <td valign="top" style="padding:0; height:59px;">
      <img src="<?php echo base_url() ?>assets/img/emailer/graffiti-top.png"  style="width:100%;">
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0;">
      <table border="0" cellspacing="0" cellpadding="0" width="100%" style="padding:0;">
        <tr>
          <td valign="top" style="padding:0; width:123px; height:69px;">
            <img src="<?php echo base_url() ?>assets/img/emailer/graffiti-left.png"  style="width:100%;">
          </td>
          <td align="center" style="padding:0" class="mob-gutter">
            <h3 style="font-family: 'Roboto', sans-serif; font-weight: normal;font-style: normal; padding:0; margin:0; font-size:22px;">Hi <b style="font-weight:700; color:#000;"><?php echo $data['To']['FirstName'] ?></b></h3>                         
          </td>
          <td valign="top" style="padding:0; width:102px; height:90px;">
            <img src="<?php echo base_url() ?>assets/img/emailer/graffiti-right.png"  style="width:100%;">
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0 0 20px 0;" class="mob-gutter">
      <h3 style="font-family: 'Roboto', sans-serif; font-weight: normal;font-style: normal; padding:0; margin:0; font-size:22px; line-height:28px;  color:#333;">Here’s some exciting news. Winners for the <br>contest has been announced.</h3>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0 0 30px 0;">
      <hr style="height:3px; width:50px; background-color:#FD6C4F; border:none; margin:0; border-radius:10px;">
    </td>
  </tr>

  <?php if($data['winner_details']){ ?>
    <tr>
      <td align="center" style="padding:60px 10px 0 10px; background-color: #F4F5F6;">
        <table border="0" cellspacing="0" cellpadding="0" style="padding:0;">
          <tr>
            <?php foreach($data['winner_details'] as $winner){ ?>
              <td class="mob-fluid" valign="top" align="center" style="padding:0 10px 10px 10px; width:130px;">
                <table border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td align="center" style="padding:0 0 10px 0;">
                      <?php if($winner['ProfilePicture']){ ?>
                        <img width="130" height="130" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$winner['ProfilePicture'] ?>"  />
                      <?php } else { ?>
                        <img width="130" height="130" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
                      <?php } ?>     
                    </td>                            
                  </tr>
                  <tr>
                    <td align="center" style="padding:0;">
                      <p style="display:block; font-family: 'Roboto', sans-serif; font-weight:700; font-style: normal; padding:0 0 10px 0;  font-size:14px; color:#444;"><?php echo $winner['FirstName'].' '.$winner['LastName'] ?></p>                              
                    </td>
                  </tr>
                </table>
              </td>
            <?php } ?>
          </tr>                    
        </table>
      </td>
    </tr>
  <?php } ?>

  <tr>
    <td class="mob-gutter" align="center" style="padding:0 125px 60px 125px; background-color: #F4F5F6;">
      <p style="font-family: 'Roboto', sans-serif; font-weight:700; font-style: normal; font-size:18px; color:#333; line-height:22px;">"<?php echo $data['activity_data']['PostContent'] ?>"</p>
    </td>
  </tr>
  <tr>
    <td class="mob-gutter" align="center" style="padding:25px 140px 40px 140px">                  
      <p style="font-family: 'Roboto', sans-serif; font-style: normal; font-size:16px; color:#666; line-height:24px;">Want to win more prizes?<br>Go ahead and participate in more contests!</p>                  
    </td>
  </tr>
  <tr>
    <td class="mob-gutter" align="center" style="padding:0 205px 40px 205px">                  
      <a style="font-family: 'Roboto', sans-serif; font-style: normal; line-height:20px; font-size:14px; font-weight:700;  cursor:pointer; display:block; padding:14px 0; color:#fff; background-color:#46348D; border-radius:3px; text-transform:uppercase;" href="<?php echo site_url('feeds') ?>">View all Contests</a>
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