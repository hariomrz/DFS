<tbody>
                <tr>
                  <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
                    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo lang('notify_hi') ?> <?php echo $data['To']['FirstName'].' '.$data['To']['LastName'] ?>,</p>
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
                      <tr>
                        <td>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAFAFA;border-bottom:1px solid #E5E5E5;">
                            <tr>
                            <td style="padding:6px 10px 10px 20px" class="mob-padding">
                              <img src="<?php echo base_url() ?>assets/img/emailer/ic-calender.png"  style="vertical-align:middle;">
                            </td>
                              <td style="padding:10px 0px; width:100%;" class="mob-padding">
                              <p><?php echo $data['Subject'] ?></p>
                               
                              </td>
                            </tr>
                          </table>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
                            <?php if($data['event_data']){ ?>
                            <?php foreach($data['event_data'] as $event_data){ ?>
                            <tr>
                              <td class="add-frnds" style="border-bottom:1px solid #E5E5E5; padding:20px 0;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td colspan="2" style="padding:0 20px;" class="mob-paddinglr">
                                      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#F4F4F4;">
                                      <tr>
                                        <td class="small" style="vertical-align: top; padding:20px; width:48px;">

                                        <?php if($event_data['ProfilePicture']){ ?>
                                          <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$event_data['ProfilePicture'] ?>"  />
                                        <?php } else { ?>
                                          <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/event-placeholder.png' ?>"  />
                                        <?php } ?></td>
                                    <td style="padding:20px 0;" class="mob-content">
                                      <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $event_data['Title'] ?></p>
                                      <p style="font-size:14px; color:#444444; margin:5px 15px 5px 0">
                                        <?php 
                                          if(strlen($event_data['Description'])>50)
                                          {
                                            echo substr($event_data['Description'],0,50).'...';
                                          } 
                                          else 
                                          {
                                            echo $event_data['Description'];
                                          }
                                        ?>
                                      </p>
                                      <p style="color:#444444; font-size:14px;"><?php echo date("d M, Y", strtotime($event_data['StartDate'])).' '.date("h:i a", strtotime($event_data['StartTime'])).' - '.date("d M, Y", strtotime($event_data['EndDate'])).' '.date("h:i a", strtotime($event_data['EndTime'])); ?></p>
                                      <p style="color:#444444; font-size:14px; margin:5px 0 10px 0"><?php echo $event_data['Venue'] ?> <span style="color:#999999;"><?php echo lang('notify_at') ?></span> <?php echo $event_data['Location']['FormattedAddress'] ?></p>
                                    </td>
                                      </tr> 
                                      </table>
                                    </td>
                                  </tr>
                                  <tr>
                                        <td>
                                            <p style="color:#444444; font-size:14px; padding:15px 20px 0;"><?php echo $event_data['Tagline'] ?></p>
                                          </td>
                                      </tr>
                                </table>
                              </td>
                            </tr>
                            <?php } } ?>
                          </table>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                                <a href="<?php echo site_url('events') ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('view_events') ?></a>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
<?php $this->load->view('emailer/notification_settings') ?>