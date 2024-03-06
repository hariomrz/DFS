<tbody>
                <tr>
                  <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
                    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo lang('notify_hi') ?> <?php echo $data['To']['FirstName'].' '.$data['To']['LastName'] ?>,</p>
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
                      <tr>
                        <td>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAFAFA;border-bottom:1px solid #E5E5E5;">
                            <tr>
                            <td style="padding:10px 0 10px 20px" class="mob-padding">
                              <img src="<?php echo base_url() ?>assets/img/emailer/ic-comment.png"  style="vertical-align:middle;">
                            </td>
                              <td style="padding:10px 0px; width:100%;" class="mob-padding">
                               <p style="color:#444444; font-size:14px;">  <span style="color:#444444; font-weight:bold;"><?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?></span> liked your post
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
                                          <?php if($data['owner_data']['ProfilePicture']){ ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['owner_data']['ProfilePicture'] ?>"  />
                                          <?php } else { ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
                                          <?php } ?>
                                        </td>
                                    <td style="padding:20px 0;" class="mob-content">
                                      <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0 0 10px 0;"><?php echo $data['owner_data']['FirstName'].' '.$data['owner_data']['LastName'] ?></p>
                                      
                                      <?php if($data['activity_data']['Album'] && isset($data['activity_data']['Album'][0]['Media'])){ ?>
                                      <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                        <tr>
                            <?php foreach($data['activity_data']['Album'][0]['Media'] as $media){ ?>
                            <td align="left">
                            <?php if($data['activity_data']['Album'][0]['AlbumName']=='Wall Media'){ ?>
                              <img width="60px" height="60px"  src="<?php echo IMAGE_SERVER_PATH.'upload/wall/220x220/'.get_media_thumb($media['ImageName']) ?>">
                            <?php } else { ?>
                              <img width="60px" height="60px"  src="<?php echo IMAGE_SERVER_PATH.'upload/album/220x220/'.get_media_thumb($media['ImageName']) ?>">
                            <?php } ?>
                            </td>
                            <?php } ?>
                          </tr>
                                      </tbody>
                                      </table>
                                      <?php } ?>
                                      
                                      <?php
                                        if(isset($data['activity_data']['PostTitle']) && !empty($data['activity_data']['PostTitle']))
                                        {
                                      ?>
                                        <p style="font-size:14px;font-weight:bold;color:#444444;margin: 0px 15px 15px 0;">
                                          <?php
                                            echo $data['activity_data']['PostTitle'];
                                          ?>
                                        </p>
                                      <?php
                                        }
                                      ?>
                                      
                                      <p style="font-size:14px; color:#444444; margin:0px 15px 0 0">
                                      <?php
                                          echo html_substr($data['activity_data']['PostContent'],50); 
                                      ?>
                                      </p>
                                      <p style="color:#666666; font-size:14px; margin:15px 0 0 0;"><span><?php echo $data['activity_data']['NoOfLikes'] ?> <?php echo ucfirst(lang('notify_likes')) ?></span> . <span><?php echo $data['activity_data']['NoOfComments'] ?> <?php echo ucfirst(lang('notify_comments')) ?></span></p>
                                    </td>
                                      </tr>
                                      </table>
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#F4F4F4; border-top:1px solid #E5E5E5;">
                                        <tr>
                                          <td class="mob-hidden" style="vertical-align: top; padding:20px; width:48px;">
                                            <img src="<?php echo base_url() ?>/assets/img/emailer/trans.png"  />
                                          </td>
                                      <td style="padding:20px 0;" class="mob-padding">
                                      </td>
                                        </tr>
                                        </table>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            
                            
                          </table>
                         <!-- <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                            <tr>
                              <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                                <a href="<?php echo $data['activity_data']['Link'] ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('view_this_post') ?></a>
                              </td>
                            </tr>
                          </table>-->
                        </td>
                      </tr>
                    </table>
                    <?php $this->load->view('emailer/notification_settings') ?>