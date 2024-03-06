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
                               <p style="color:#444444; font-size:14px;">You have been added as an admin for the forum <a href="<?php echo site_url('forum') ?>" style="color:#00529F; font-weight:500;"> <?php echo $data['forum_data']['Name'] ?></a>.</p>
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
                                          <td class="small" style="vertical-align: top; padding:20px; width:48px;"></td>
                                    <td style="padding:20px 0;" class="mob-content">
                                      <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $data['forum_data']['Name'] ?></p>
                                      <p style="font-size:14px; color:#444444; margin:5px 15px 10px 0">
                                        <?php if(strlen($data['forum_data']['Description'])>50)
                                        {
                                          echo substr($data['forum_data']['Description'], 0, 50).'...';
                                        } 
                                        else 
                                        {
                                          echo $data['forum_data']['Description'];
                                        }
                                        ?>
                                      </p>
                                      <a href="<?php echo site_url('forum') ?>" style="color:#00529F; font-size:14px; font-weight:500;"><?php echo lang('notify_leave') ?></a>
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
                                <a href="<?php echo site_url('forum') ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('notify_view_forum') ?></a>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <?php $this->load->view('emailer/notification_settings') ?>