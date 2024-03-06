<tbody>
    <tr>
        <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
            <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo lang('notify_hi') ?> <?php echo $data['To']['FirstName'] . ' ' . $data['To']['LastName'] ?>,</p>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">

                <tr>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAFAFA;border-bottom:1px solid #E5E5E5;">
                            <tr>
                                <td style="padding:10px 3px 10px 20px" class="mob-padding">
                                    <img src="<?php echo ASSET_BASE_URL . 'img/emailer/ic-like.png' ?>"  style="vertical-align:middle;">
                                </td>
                                <td style="padding:10px 0px; width:100%;" class="mob-padding">
                                    <p style="color:#444444; font-size:14px;">  <span style="color:#444444; font-weight:bold;"><?php echo $data['EndorseUser'] ?></span> <a style="color:#00529F; font-weight:500;">endorsed</a> you.</p>
                                </td>
                            </tr>
                        </table>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
                            <tr>
                                <td>
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                        <tr>
                                            <td class="small" style="vertical-align: top; padding:20px; width:48px;">
                                                <img src="<?php echo IMAGE_SERVER_PATH . 'upload/profile/56x56/' . $data['EndorseUserImage']; ?>"  />
                                            </td>
                                            <td style="padding:20px 0;" class="mob-content">
                                                <p style="color:#444444; display:block; margin:0 0 25px 0;"><span style="font-size:16px; color:#444444; font-weight:500;"><?php echo $data['EndorseUser'] ?></span> <span style="font-size:14px;">has endorsed you for following skill(s) on VSocial</span></p>
                                                <?php
                                                foreach ($data['Skill'] as $item)
                                                {
                                                    ?>
                                                    <table  border="0" cellspacing="0" cellpadding="0" style="margin-bottom:20px;">
                                                        <tr>

                                                            <td class="mob-skillCont" style="background-color:#EEEEEE; border:1px solid #DDDDDD; border-radius:2px; margin-bottom:10px; max-width:360px; margin-bottom:10px; padding-left:8px; padding-right:8px;">
                                                                <?php
                                                                if ($item['SkillImageName'])
                                                                {
                                                                    ?>
                                                                    <span style="padding-right:8px; padding-top:6px; padding-bottom:3px; vertical-align:middle; display:inline-block">
                                                                        <img height="14px" src="<?php echo IMAGE_SERVER_PATH . 'upload/skill/220x220/' . $item['SkillImageName'] ?>" >
                                                                    </span>
                                                                <?php } ?>
                                                                <?php
                                                                if ($item['CategoryImageName'])
                                                                {
                                                                    ?>
                                                                    <span style="padding-right:8px; padding-top:6px; padding-bottom:3px; vertical-align:middle; display:inline-block">
                                                                        <img height="14px" src="<?php echo IMAGE_SERVER_PATH . 'upload/category/220x220/' . $item['CategoryImageName'] ?>" >
                                                                    </span>
                                                                <?php } ?>
                                                                <?php
                                                                if ($item['ParentCategorName'])
                                                                {
                                                                    ?>
                                                                    <span style="padding-right:8px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle; display:inline-block">
        <?php echo $item['ParentCategorName']; ?>
                                                                    </span>
                                                                    <span style="padding-right:8px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle; display:inline-block">
                                                                        <img src="<?php echo ASSET_BASE_URL . 'img/right-arrow.png'; ?>" >
                                                                    </span>
                                                                <?php } ?>
                                                                <?php
                                                                if ($item['CategoryName'])
                                                                {
                                                                    ?>
                                                                    <span style="padding-right:8px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle; display:inline-block">
        <?php echo $item['CategoryName']; ?>
                                                                    </span>
                                                                    <span style="padding-right:8px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle; display:inline-block">
                                                                        <img src="<?php echo ASSET_BASE_URL . 'img/right-arrow.png'; ?>" >
                                                                    </span>
                                                                <?php } ?>
                                                                <?php
                                                                if ($item['Name'])
                                                                {
                                                                    ?>
                                                                    <span style="padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle; display:inline-block">
                                                                        <a href="javascript:void(0)" style="color:#00529F; text-decoration:none;"><?php echo $item['Name']; ?></a>
                                                                    </span>
    <?php } ?>
                                                            </td>
                                                        </tr>
                                                    </table>
<?php } ?>
                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                            <tr>
                                <td style="width:100%;font-size:14px; color:#00529F; font-weight:500; text-align:center; margin:0; padding:10px 0;">
                                    <a href="<?php echo base_url() . $data['To']['ProfileURL'].'/endorsment/'.$data['EndorseUserGUID'] ; ?>" style="color:#00529F;cursor:pointer; display:block">View Endorsements </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>


            <p style="color:#999999; font-size:13px; margin:30px 0 5px 0">Want to talk to Admin? Send a mail to <a style="color:#00529F; font-size:13px; font-weight:500; cursor:pointer; text-decoration:none" href="mailto:joe@example.com?subject=feedback" "admin@vinfotech.com">admin@vinfotech.com</a>
            </p>
