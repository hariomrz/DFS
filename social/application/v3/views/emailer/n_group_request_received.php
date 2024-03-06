<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
      <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo lang('notify_hi') ?> <?php echo $data['To']['FirstName'].' '.$data['To']['LastName'] ?>,</p>
       <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
        <tr>
          <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAFAFA;border-bottom:1px solid #E5E5E5;">
              <tr>
              <td style="padding:10px 10px 10px 20px" class="mob-padding">
                <img src="<?php echo base_url() ?>assets/img/emailer/ic-user.png"  style="vertical-align:middle;">
              </td>
                <td style="padding:10px 0px; width:100%;" class="mob-padding">
                 <p style="color:#444444; font-size:14px;">  
                     <span style="color:#444444; font-weight:bold;"><?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?> </span>
                     <?php echo lang('invited_you_to').' '.lang('notify_join')?> <a href="<?php echo site_url($data['group_data']['EntityURL']); ?>" style="color:#00529F; font-weight:500;"> <?php echo $data['group_data']['GroupName'] ?></a>.
                 </p>
                </td>
              </tr>
            </table>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
              <tr>
                <td class="add-frnds" style="border-bottom:1px solid #E5E5E5; padding:20px 0;">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td colspan="2" style="padding:0 20px;" class="mob-paddinglr">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#F4F4F4;">
                        <tr>
                          <td class="small" style="vertical-align: top; padding:20px; width:48px;">
                              <?php if($data['group_data']['GroupImage']){ ?>
                                <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['group_data']['GroupImage'] ?>"  />
                              <?php } else { ?>
                                <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/group-no-img.jpg' ?>"  />
                              <?php } ?>
                          </td>
                      <td style="padding:20px 0;" class="mob-content">
                         <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $data['group_data']['GroupName'] ?></p>
                            <p style="font-size:14px; color:#444444; margin:5px 15px 10px 0">
                              <?php if(strlen($data['group_data']['GroupDescription'])>50)
                              {
                                echo substr($data['group_data']['GroupDescription'], 0, 50).'...';
                              } 
                              else 
                              {
                                echo $data['group_data']['GroupDescription'];
                              }
                              ?>
                            </p>
                            <a href="<?php echo site_url($data['group_data']['EntityURL']); ?>" style="color:#00529F; font-size:14px; font-weight:500;"><?php echo lang('notify_view_respond_invite') ?></a>
                      </td>
                        </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>


            </table>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
              <tr>
                <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                  <a href="<?php echo site_url($data['group_data']['EntityURL']); ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('notify_view_group') ?></a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php $this->load->view('emailer/notification_settings') ?>