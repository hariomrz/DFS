<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
      <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo lang('notify_hi') ?> <?php echo $data['FirstLastName'];?>,</p>
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
                     <span style="color:#444444; font-weight:bold;"><?php echo $data['From'];?> </span>  
                     started a new conversation with you 
                     <?php if(!empty($data['member_description'])){ ?>
                     , <a href="javascript:void(0)" style="color:#00529F; font-weight:500;"><?php if(!empty($data['member_description'])){echo $data['member_description'];}?></a>
                     <?php } ?>
                     .
                 </p>
                </td>
              </tr>
            </table>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
              <tr>
                <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                  <a href="<?php echo $data['Link']; ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('join_conversation') ?></a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php $this->load->view('emailer/notification_settings') ?>
