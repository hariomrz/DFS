<table class="main-wrapper" width="610" border="0" cellspacing="0" cellpadding="0" align="center" style=" border-radius:5px;">
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
                              <img src="<?php echo base_url() ?>assets/img/emailer/ic-birthday.png"  style="vertical-align:middle;">
                            </td>
                              <td style="padding:10px 0px; width:100%;" class="mob-padding">
                               <p style="color:#444444; font-size:14px;"><?php echo $data['Subject'] ?></p>
                              </td>
                            </tr>
                          </table>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
                            
                            <?php
                              if($data['friends_data']['Today']){
                                $len = count($data['friends_data']['Today']);
                                $i=1;
                                foreach($data['friends_data']['Today'] as $today){
                            ?>
                            <tr>
                              <td class="add-frnds" style="<?php echo ($len==$i) ? 'border-bottom:1px solid #E5E5E5; padding:0 0 20px;' : '' ; ?>">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:20px 0 0" class="mob-margint">
                                  <?php if($i==1){ ?>
                                  <tr>
                                  <td colspan="2" style="color:#444444; font-weight:bold; font-size:14px; padding:0 0 10px 20px;" class="mob-paddingbl">
                                    <?php echo lang('notify_today') ?>
                                  </td>
                                  </tr>
                                  <?php } ?>
                                  <tr>
                                    <td class="small" width="10%" style="vertical-align: top; padding:0 20px 0 20px;">
                                      <?php if($today['ProfilePicture']){ ?>
                                        <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$today['ProfilePicture'] ?>"  />
                                      <?php } else { ?>
                                        <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
                                      <?php } ?>
                                    </td>
                                    <td>
                                      <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $today['FirstName'].' '.$today['LastName'] ?></p>
                                      <a href="<?php echo site_url().'/'.$today['ProfileURL'] ?>" style="color:#0053A0; font-size:14px; font-weight:500;"><?php echo lang('notify_congratulate_him') ?>.</a>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            <?php 
                                $i++;
                                }
                              }
                            ?>

                            <?php
                              if($data['friends_data']['OtherDate']){
                                $len = count($data['friends_data']['OtherDate']);
                                $i=1;
                                foreach($data['friends_data']['OtherDate'] as $other_date){
                            ?>
                            <tr>
                              <td class="add-frnds" style="<?php echo ($len==$i) ? 'border-bottom:1px solid #E5E5E5; padding:0 0 20px;' : '' ; ?>">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:20px 0 0 " class="mob-margint">
                                  <?php if($i==1){ ?>
                                    <tr>
                                      <td colspan="2" style="color:#444444; font-weight:500; padding:0 0 10px 20px; font-size:14px;" class="mob-paddingbl">
                                        <?php echo lang('notify_upcoming') ?>
                                      </td>
                                    </tr>
                                  <?php } ?>
                                  <tr>
                                    <td class="small" width="10%" style="vertical-align: top; padding:0 20px 0 20px;">
                                      <?php if($other_date['ProfilePicture']){ ?>
                                        <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/'.$other_date['ProfilePicture'] ?>"  />
                                      <?php } else { ?>
                                        <img width="48px" height="48px" src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/user_default.jpg' ?>"  />
                                      <?php } ?>
                                    </td>
                                    <td>
                                      <p style="font-size:16px; color:#444444; font-weight:500; display:block; margin:0;"><?php echo $other_date['FirstName'].' '.$other_date['LastName'] ?></p>
                                      <p style="font-size:14px; color:#999999; margin:5px 15px 0px 0"><?php echo $other_date['BirthDate'] ?></p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            <?php 
                                $i++;
                                }
                              }
                            ?>
                           
                          </table>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                            <tr>
                              <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                                <a href="<?php echo site_url('events') ?>" style="color:#00529F;cursor:pointer; display:block"><?php echo lang('notify_plan_event') ?></a>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                    <?php $this->load->view('emailer/notification_settings') ?>