<tbody>
    <tr>
        <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" >
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
                <tr>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAFAFA;border-bottom:1px solid #E5E5E5;">
                            <tr>
                                <td style="padding:10px 10px 10px 20px" class="mob-padding">
                                    <!--<img src="<?php echo base_url() ?>assets/img/emailer/ic-friend.png"  style="vertical-align:middle;">-->
                                    <?php
                                    if ($data['ProfilePicture'])
                                    {
                                        ?>
                                        <img width="36px" height="36px" src="<?php echo IMAGE_SERVER_PATH . 'upload/profile/36x36/' . $data['ProfilePicture'] ?>"  style="vertical-align:middle;"/>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <img width="36px" height="36px" src="<?php echo IMAGE_SERVER_PATH . 'upload/profile/36x36/default-148.png' ?>"  style="vertical-align:middle;"/>
                                    <?php } ?>
                                </td>
                                <td style="padding:10px 0px; width:100%;" class="mob-padding">
                                    <p style="color:#444444; font-size:14px;"><a href="<?php echo $data['ProfileURL'] ?>" style="color:#00529F; font-weight:500;"> <?php echo $data['FirstName'] . ' ' . $data['LastName'] ?></a> Shared event detail with you</p>
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
                                                        <td style="padding:0px 0 0 20px;" class="mob-content">
                                                            <p style="font-size:14px; color:#444444; display:block; margin:0 0 5px 0;">
                                                                <?php echo $data['Message'] ?>
                                                            </p>
                                                            <p style="font-size:14px; color:#00529F; margin:0px 15px 10px 0;">
                                                                <a href="<?php echo $data['Link'] ?>" style="font-weight:500;">View</a>
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