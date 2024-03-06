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
                <p style="color:#444444; font-size:14px;"><?php echo lang('notify_added_to_conversation') ?></p>
                </td>
              </tr>
            </table>
             <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
              <tr>
                <td>
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:20px 0">
                    <tr>
                    <td style="padding:0 0 0 20px;">
                        <p style="color:#00529F; font-size:14px; font-weight:500;"><a href="<?php echo site_url('messages').'/thread/'.$data['thread_guid'] ?>"><?php echo lang('notify_join_conversation') ?> / <?php echo lang('notify_view_participants') ?> </a></p>
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