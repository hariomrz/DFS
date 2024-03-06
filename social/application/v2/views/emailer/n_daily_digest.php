<?php echo $this->load->model('users/friend_model') ?>
<tbody style="background-color:#FFFFFF;">
                <tr>
                  <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;" >
                    <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:10px;"><?php echo lang('notify_hi') ?> <?php echo $data['To']['FirstName'] ?>,</p>
                    <p style="font-family: 'Roboto', sans-serif; color:#444444; font-size:13px; margin: 0 0 25px 0;"><?php echo lang('top_stories_from_vsocial') ?></p>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
                      <tr>
                        <td>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td style="background-color:#FAFAFA; border-bottom:1px solid #E5E5E5;padding:10px 20px; width:100%;">
                                <p style="color:#444444; font-size:13px; display:block"><?php echo lang('popular_in_network') ?></p>
                              </td>
                            </tr>
                          </table>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
                            
                            <?php foreach($data['activity_data'] as $activity){ ?>
                              <tr>
                                <td class="add-frnds" style="border-bottom:1px solid #E5E5E5; padding:20px 0 10px;">
                                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td colspan="2">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                          <td class="small" style="vertical-align: top; padding:0 20px 0 20px; width:48px;">
                                          <?php if($activity['UserProfilePicture']){ ?>
                                              <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$activity['UserProfilePicture'] ?>"  />
                                            <?php } else { ?>
                                              <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
                                            <?php } ?>
                                          </td>
                                      <td>
                                        <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;">
                                        <?php 
                                          if($activity['ActivityType'] == 'PostSelf')
                                          {
                                            echo str_replace('{{SUBJECT}}', $activity['UserName'], $activity['Message']); 
                                          }
                                          elseif($activity['ActivityType'] == 'GroupPostAdded')
                                          {
                                            echo str_replace('{{User}}', $activity['UserName'], $activity['Message']).' '.lang('notify_posted_in').' '.$activity['EntityName']; 
                                          }
                                          elseif($activity['ActivityType'] == 'EventWallPost')
                                          {
                                            echo str_replace('{{User}}', $activity['UserName'], $activity['Message']).' '.lang('notify_posted_in').' '.$activity['EntityName'];
                                          }
                                          elseif($activity['ActivityType'] == 'PagePost')
                                          {
                                            echo str_replace('{{User}}', $activity['EntityName'], $activity['Message']); 
                                          }
                                        ?>
                                        </p>
                                        <p style="color:#999999; font-size:14px; margin:2px 0;"><?php echo $this->friend_model->get_mutual_friend($data['To']['UserID'],get_detail_by_guid($activity['UserGUID'],3),'',TRUE).' '.lang('notify_mutual_friends') ?></p>
                                      </td>
                                        </tr>
                                        </table>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td colspan="2" style="padding:15px 0 0px 20px;"><p style="font-size:14px; color:#444444;"><?php echo $activity['PostContent'] ?></p></td>
                                    </tr>
                                    <tr>
                                      <td colspan="2" style="padding:15px 20px 0 20px">
                                       <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0;">
                                       <tr>
                                         <?php if($activity['Album'] && isset($activity['Album'][0]['Media'])){ ?>
                                          <?php foreach($activity['Album'][0]['Media'] as $media){ ?>
                                           <td style="padding:0 5px 0 0" class="add-frnds">
                                             <img src="<?php echo IMAGE_SERVER_PATH.'upload/wall/220x220/'.$media['ImageName'] ?>" style=""/>
                                           </td>
                                         <?php } } ?>
                                       </tr>
                                       </table>
                                     </td>
                                    </tr>
                                    <tr>
                                      <td colspan="2" style="color:#999999; font-size:14px; padding:15px 0 5px 20px">
                                        <p style="color:#666666; font-size:14px; margin:15px 0 0 0;"><span><?php echo $activity['NoOfLikes'] ?> <?php echo ucfirst(lang('notify_likes')) ?></span> . <span><?php echo $activity['NoOfComments'] ?> <?php echo ucfirst(lang('notify_comments')) ?></span></p>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            <?php } ?>
                            
                          </table>
                          

                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-top:1px solid #E5E5E5;">
                            <tr>
                              <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                                <a href="<?php echo site_url() ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('notify_go_to').' '.SITE_NAME ?></a>
                              </td>
                            </tr>
                          </table>
                          </td>
                      </tr>
                    </table>
                        
                    <?php $this->load->view('emailer/notification_settings') ?>