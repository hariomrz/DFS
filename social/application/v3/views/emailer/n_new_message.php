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
                <img src="<?php echo base_url() ?>assets/img/emailer/ic-msg.png"  style="vertical-align:middle;">
              </td>
                <td style="padding:10px 0px; width:100%;" class="mob-padding">
                <p style="color:#444444; font-size:14px;"> <a href="<?php echo site_url() ?>/<?php echo $data['From']['ProfileURL'] ?>" style="color:#00529F; font-weight:500;"> <?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?> </a><?php echo lang('notify_send_msg') ?></p>
                </td>
              </tr>
            </table>
             <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
              <tr>
                <td class="add-frnds" style="">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:20px 0">
                    <tr>
                      <td class="small" width="10%" style="vertical-align: top; padding:0 20px 0 20px;">
                        <?php if($data['From']['ProfilePicture']){ ?>
                          <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['From']['ProfilePicture'] ?>"  />
                        <?php } else { ?>
                          <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/event-placeholder.png' ?>"  />
                        <?php } ?>
                      </td>
                      <td>
                        <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?></p>
                        <p style="font-size:14px; color:#444444; margin:5px 15px 5px 0"><?php echo $data['message_data']['Body'] ?></p>
                        <a href="<?php echo site_url('messages').'/thread/'.$data['thread_guid'] ?>" style="color:#00529F; font-size:14px; font-weight:500;"><?php echo lang('notify_reply') ?></a>  
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php $this->load->view('emailer/notification_settings') ?>