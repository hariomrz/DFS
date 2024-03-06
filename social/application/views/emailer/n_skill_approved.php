<tbody>
    <tr>
        <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
            <p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"><?php echo lang('notify_hi') ?> <?php echo $data['To']['FirstName'] . ' ' . $data['To']['LastName'] ?>,</p>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
                <tr>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0px 0;">
                            <tr>
                                <td class="add-frnds" style=" padding:20px 0;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td colspan="2" style="padding:0 20px;" class="mob-paddinglr">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="">
                                                    <tr>
                                                        <td class="small" style="vertical-align: top; padding:20px; width:48px;">
                                                            <img src="<?php echo ASSET_BASE_URL.'img/thumb-4.png'; ?>"  />
                                                        </td>
                                                        <td style="padding:20px 0;" class="mob-content">
                                                            <p style="color:#444444; display:block; margin:0 0 25px 0;"><span style="font-size:14px; color:#2CB200; ">Congrats! </span> <span style="font-size:14px;">VSocial Admin has approved new skills added by you and associated them with suitable categories.</span></p>
                                                            <table  border="0" cellspacing="0" cellpadding="0" style="background-color:#EEEEEE; border:1px solid #DDDDDD; border-radius:2px; margin-bottom:10px">
                                                                <tr>
                                                                    <?php if($data['Skill']['SkillImageName']) { ?>
                                                                    <td style="padding-left:10px; padding-top:6px; padding-bottom:3px; vertical-align:middle;">
                                                                        <img height="14px" src="<?php echo IMAGE_SERVER_PATH.'upload/skill/220x220/'.$data['Skill']['SkillImageName'] ?>" >
                                                                    </td>
                                                                     <?php } ?>
                                                                     <?php if($data['Skill']['CategoryImageName']) { ?>
                                                                    <td style="padding-left:10px; padding-top:6px; padding-bottom:3px; vertical-align:middle;">
                                                                        <img height="14px" src="<?php echo IMAGE_SERVER_PATH.'upload/category/220x220/'.$data['Skill']['CategoryImageName'] ?>" >
                                                                    </td>
                                                                     <?php } ?>
                                                                     <?php if($data['Skill']['ParentCategorName']) { ?>
                                                                    <td style="padding-left:10px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle;"><?php echo $data['Skill']['ParentCategorName']; ?></td>
                                                                    <td style="padding-left:10px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle;">
                                                                        <img src="<?php echo ASSET_BASE_URL.'img/right-arrow.png'; ?>" >
                                                                    </td>
                                                                    <?php } ?>
                                                                     <?php if($data['Skill']['CategoryName']) { ?>
                                                                    <td style="padding-left:10px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle;"><?php echo $data['Skill']['CategoryName']; ?></td>
                                                                    <td style="padding-left:10px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle;">
                                                                        <img src="<?php echo ASSET_BASE_URL .'img/right-arrow.png'; ?>" >
                                                                    </td>
                                                                     <?php } ?>
                                                                    <?php if($data['Skill']['Name']){ ?>
                                                                        <td style="padding-left:10px; padding-right:10px; padding-top:5px; padding-bottom:5px; font-size:14px; vertical-align:middle;">
                                                                            <a href="javascript:void(0)" style="color:#00529F; text-decoration:none;"><?php echo $data['Skill']['Name']; ?></a>
                                                                        </td>
                                                                        <?php } ?>
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
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAFAFA;">
                            <tr>
                                <td style="width:100%;font-size:14px; text-align:center; margin:0; padding:15px 0; border-bottom:1px solid #E5E5E5;">
                                    These skills are now visible to other users and available for their endorsements.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <p style="color:#999999; font-size:13px; margin:30px 0 5px 0">Want to talk to Admin? Send a mail to <a style="color:#00529F; font-size:13px; font-weight:500; cursor:pointer; text-decoration:none" href="mailto:joe@example.com?subject=feedback" "admin@vinfotech.com">admin@vinfotech.com</a>
            </p>
