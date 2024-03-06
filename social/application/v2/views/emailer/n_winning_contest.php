<!-- New -->
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
          <td rowspan="2" valign="bottom" align="center" style="padding:0; background-color: #ffffff;">
            <?php if($data['To']['ProfilePicture']){ ?>
              <img style="width:150px; height:150px; border:4px solid #fff;" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['To']['ProfilePicture'] ?>"  />
            <?php } else { ?>
              <img style="width:150px; height:150px; border:4px solid #fff;" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
            <?php } ?>
          </td>
          <td valign="top" style="padding:0; width:102px; height:90px;">
            <img src="<?php echo base_url() ?>assets/img/emailer/graffiti-right.png"  style="width:100%;">
          </td>
        </tr>
        <tr>
          <td colspan="3" style="padding:5px 0; height:20px; background-color:#F4F5F6;"></td>
        </tr> 
      </table>
    </td>                
  </tr>                           
  <tr>
    <td class="mob-gutter" align="center" style="padding:20px 0 30px 0; background-color: #F4F5F6;">
      <h3 style="font-family: 'Roboto', sans-serif; font-weight:700;font-style: normal; padding:0; margin:0; font-size:22px; color:#FD6C4F;">Congratulations!! <?php echo $data['To']['FirstName'] ?></h3> 
    </td>
  </tr>
  <tr>
    <td class="mob-gutter" align="center" style="padding:0 90px 40px 90px; background-color: #F4F5F6;">
      <p style="font-family: 'Roboto', sans-serif; font-weight:normal; font-style: normal; font-size:22px; line-height:28px; color:#333;">On winning the contest ultimately we chose a mission that we can feel proud </p>
    </td>
  </tr>
  <tr>
    <td class="mob-gutter" align="center" style="padding:0 120px 20px 120px; background-color: #F4F5F6;">
      <p style="font-family: 'Roboto', sans-serif; font-weight:700; font-style: normal; font-size:18px; color:#333; line-height:28px;">"<?php echo $data['activity_data']['PostContent'] ?>"</p>
    </td>
  </tr>
  <?php if($data['winner_details']){ ?>
    <tr>
      <td class="mob-gutter" align="center" style="padding:0 0 15px 0; background-color: #F4F5F6;">
        <p style="font-family: 'Roboto', sans-serif; font-weight:normal; font-style: normal; font-size:16px; color:#666; line-height:24px;">Here are <?php echo count($data['winner_details']) ?> other winners who won the contest.</p>
      </td>
    </tr>
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
    <td class="mob-gutter" align="center" style="padding:20px 0 40px 0">                  
      <p style="font-family: 'Roboto', sans-serif; font-style: normal; font-size:16px; color:#666; line-height:24px;">Our team will contact you soon regarding your prize!</p>                  
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
      <p style="font-family: 'Roboto', sans-serif; font-weight:700;font-style: normal;font-size:14px;padding:0; color:#9B9B9B; ">Team Vsocial</p>
    </td>
  </tr>
<!-- New -->