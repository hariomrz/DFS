<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
      <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo lang('notify_hi') ?> <?php echo $data['u_details']['FirstName'].' '.$data['u_details']['LastName'] ?>,</p>
       <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
        <tr>
          <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAFAFA;border-bottom:1px solid #E5E5E5;">
              <tr>
              <td style="padding:10px 10px 10px 20px" class="mob-padding">
                <!-- <img src="<?php echo base_url() ?>assets/img/emailer/ic-calender.png"  style="vertical-align:middle;"> -->
              </td>
                <td style="padding:10px 0px; width:100%;" class="mob-padding">
                 <p style="color:#444444; font-size:14px;"> <span style="color:#444444; font-weight:bold;"><?php echo $data['user_details']['FirstName'].' '.$data['user_details']['LastName'] ?></span> created an event <a href="<?php echo site_url($data['event_data']['ProfileURL']); ?>" style="color:#00529F; font-weight:500;">  <?php echo $data['event_data']['Title'] ?></a></p>
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
                            <?php if($data['event_data']['ProfilePicture']){ ?>
                              <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['event_data']['ProfilePicture'] ?>"  />
                            <?php } else { ?>
                              <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/event-placeholder.png' ?>"  />
                            <?php } ?>
                          </td>
                      <td style="padding:20px 0;" class="mob-content">
                        <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $data['event_data']['Title'] ?></p>
                        <p style="font-size:14px; color:#444444; margin:5px 15px 5px 0">
                          <?php 
                              if(strlen($data['event_data']['Description'])>50)
                              {
                                echo substr($data['event_data']['Description'],0,50).'...';
                              } 
                              else 
                              {
                                echo $data['event_data']['Description'];
                              }
                            ?>
                        </p>
                        <p style="color:#444444; font-size:14px;"><?php echo $data['event_data']['StartDate'].' '.$data['event_data']['StartTime'].' - '.$data['event_data']['EndDate'].' '.$data['event_data']['EndTime']; ?></p>
                        <p style="color:#444444; font-size:14px; margin:5px 0 10px 0"><?php echo $data['event_data']['Venue'] ?> <span style="color:#999999;"><?php echo lang('notify_at') ?></span> <?php echo $data['event_data']['Location']['FormattedAddress'] ?></p>
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
                  <a style="color:#00529F;cursor:pointer; display:block" href="<?php echo site_url($data['event_data']['ProfileURL']); ?>"><?php echo lang('notify_view_this_event') ?></a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php $this->load->view('emailer/notification_settings') ?>