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
                               <p style="color:#444444; font-size:14px;">  <span style="color:#444444; font-weight:bold;"><?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?></span> 
                               <?php 
                               if($data['is_owner'])
                               {
                                echo lang('notify_commented_post');
                               }
                               else
                               {
                                echo lang('notify_commented_post_other'); 
                               }
                               ?> 
                               <?php if($data['activity_data']['ModuleID'] == 3){ ?>
                                   <a href="<?php echo $data['activity_data']['Link'] ?>" style="color:#00529F; font-weight:500;"><?php echo $data['entity_type'] ?></a>
                               <?php } else { ?>
                                <?php echo $data['entity_type'] ?> from 
                                <?php if($data['activity_data']['ModuleID'] == 1){ ?>
                                  <a href="<?php echo site_url('group').'/wall/'.$data['entity_data']['GroupID'] ?>"><?php echo $data['entity_data']['GroupName'] ?></a>
                                <?php } elseif($data['activity_data']['ModuleID'] == 14) { ?>
                                  <a href="<?php echo site_url($data['entity_data']['ProfileURL']); ?>"><?php echo $data['entity_data']['Title'] ?></a>
                                <?php } elseif($data['activity_data']['ModuleID'] == 18) { ?>
                                  <a href="<?php echo site_url('page').'/'.$data['entity_data']['PageURL'] ?>"><?php echo $data['entity_data']['Title'] ?></a>
                                <?php } ?>
                               <?php } ?>
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
                                      <table  cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                        <tr>
                            <?php foreach($data['activity_data']['Album'][0]['Media'] as $media){ ?>
                             <td align="left" width="70px" height="60px">
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
                                           
                                      echo link_it($data['activity_data']['PostContent']);
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
                                      <table>
                                      <tr>
                                      <td style="padding:0 20px 0 0; vertical-align:top;">
                                        <?php if($data['From']['ProfilePicture']){ ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['From']['ProfilePicture'] ?>"  />
                                          <?php } else { ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
                                          <?php } ?>
                                      </td>
                                      <td>
                                         <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $data['From']['FirstName'].' '.$data['From']['LastName'] ?></p>
                                        <p style="font-size:14px; color:#444444; margin:5px 0">
                                          <?php 
                                           // echo html_substr($data['comment_data']['PostComment'],50);
                                          echo link_it($data['comment_data']['PostComment']);
                                          ?>
                                        </p>
                                        <?php if($data['comment_data']['MediaImage']!=''){ ?>
                                        <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                        <tbody><tr>
                                          <td align="left" style="width:70px;">
                                              <img  src="<?php echo IMAGE_SERVER_PATH.'upload/comments/'.$data['comment_data']['MediaImage'] ?>">
                                          </td>
                                        </tr>
                                      </tbody></table>
                                        <?php } ?>
                                        <a href="<?php echo $data['activity_data']['Link'] ?>" style="color:#00529F; font-size:14px; font-weight:500;"><?php echo lang('view_comment') ?></a>
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
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                            <tr>
                              <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                                <a href="<?php echo $data['activity_data']['Link'] ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('view_this_post') ?></a>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <?php $this->load->view('emailer/notification_settings') ?>