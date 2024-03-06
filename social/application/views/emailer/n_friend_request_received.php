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
                              <img src="<?php echo base_url() ?>assets/img/emailer/ic-friend.png"  style="vertical-align:middle;">
                            </td>
                              <td style="padding:10px 0px; width:100%;" class="mob-padding">
                               <p style="color:#444444; font-size:14px;"> <?php echo lang('notify_received_friend_request_from') ?>  <a href="<?php echo site_url().'/'.$data['From']['ProfileURL'] ?>" style="color:#00529F; font-weight:500;"> <?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?></a>
                               <?php if($data['mutual_friend']){ ?>
                               , <?php echo lang('notify_friend_with') ?> <a href="<?php echo site_url().'/'.$data['mutual_friend']['ProfileURL'] ?>" style="color:#00529F; font-weight:500;"><?php echo $data['mutual_friend']['FirstName']." ".$data['mutual_friend']['LastName'] ?></a>.
                               <?php } else { ?>
                               . 
                               <?php } ?>
                               </p>
                              </td>
                            </tr>
                          </table>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
                            <tr>
                              <td class="add-frnds" style="padding:20px 0;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td colspan="2" style="padding:0;" class="mob-paddinglr">
                                      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                      <tr>
                                        <td class="small" style="vertical-align: top; padding:0 0 0 20px; width:48px;">
                                          <?php if($data['From']['ProfilePicture']){ ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['From']['ProfilePicture'] ?>"  />
                                          <?php } else { ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
                                          <?php } ?>
                                        </td>
                                    <td style="padding:0px 0 0 20px;" class="mob-content">
                                      <p style="font-size:14px; color:#444444; display:block; margin:0 0 5px 0;"><span style="font-weight:500; font-size:16px;"><?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?> </span> <?php echo lang('notify_wants_to_be_friend') ?>
                                      </p>
                                      <p style="font-size:14px; color:#00529F; margin:0px 15px 10px 0;">
                                      <a href="<?php echo site_url().'/'.$data['From']['ProfileURL'] ?>" style="font-weight:500;"><?php echo lang('notify_accept') ?> </a>.<a href="<?php echo site_url().'/'.$data['From']['ProfileURL'] ?>" style="font-weight:500;"> <?php echo lang('notify_deny') ?></a></p>
                                    </td>
                                      </tr>
                                      </table>
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