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
                              <img src="<?php echo base_url() ?>assets/img/emailer/ic-star.png" >
                            </td>
                              <td style="padding:10px 0px; width:100%;" class="mob-padding">
                               <p style="color:#444444; font-size:14px;"> <a href="<?php echo site_url() ?>/<?php echo $data['entity_data']['ProfileURL'] ?>" style="color:#00529F; font-weight:500;"><?php echo $data['entity_data']['Name'] ?></a> <?php echo lang('notify_posted_review_on') ?> <a href="<?php echo site_url('page').'/'.get_detail_by_id($data['page_data']['PageID'],18,'PageURL').'/ratings/'.get_detail_by_id($data['rating_data']['RatingID'],23,'RatingGUID',1); ?>" style="color:#00529F; font-weight:500;"><?php echo $data['page_data']['Title'] ?>.</a></p>
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
                                          <?php if($data['entity_data']['ProfilePicture']){ ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$data['entity_data']['ProfilePicture'] ?>"  />
                                          <?php } else { ?>
                                            <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/event-placeholder.png' ?>"  />
                                          <?php } ?>
                                        </td>
                                    <td style="padding:0px 20px 0 20px;" class="mob-content">
                                      <p style="font-size:14px; color:#444444; display:block; margin:0 0 20px 0;"><span style="font-weight:500; font-size:16px;"><?php echo $data['entity_data']['Name'] ?>  </span> <span style="color:#999999;">  <?php echo lang('notify_reviewed') ?> </span> <a style="color:#00529F; font-weight:500;"><?php echo $data['page_data']['Title'] ?></a>
                                      </p>
                                      <table  width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #F0F0F0; background-color:#FAFAFA;">
                                      <tr>
                                        <td style="padding:20px">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                          <tr>
                                           <td style="background-color:#5BA829; color:#FFF; font-size:13px; font-weight:bold; border-radius:2px; width:80px; text-align:center; padding:1px 0;"><?php echo lang('notify_rated') ?> <?php echo $data['rating_data']['RateValue'] ?></td>
                                           <td style="color:#444444; font-size:14px; font-weight:bold; padding-left:5px"> <?php echo $data['review_data']['Title'] ?></td>
                                          </tr>
                                        </table>
                                          
                                          <p style="color:#444444; font-size:14px; line-height:25px; margin-top:20px;">
                                          <?php 
                                              echo html_substr($data['review_data']['Description'],50);
                                          ?>
                                          </p>
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
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-top:1px solid #E5E5E5;">
                            <tbody><tr>
                              <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                                <a href="<?php echo site_url('page').'/'.get_detail_by_id($data['page_data']['PageID'],18,'PageURL').'/ratings/'.get_detail_by_id($data['rating_data']['RatingID'],23,'RatingGUID',1); ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('notify_view_review') ?> </a>
                              </td>
                            </tr>
                          </tbody></table>
                      </td>
                      </tr>
                    </table>
                    <?php $this->load->view('emailer/notification_settings') ?>