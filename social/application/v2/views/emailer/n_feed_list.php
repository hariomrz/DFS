<?php

function get_activity_title_msg($activity) {


    $user_name = '<a style="color:#135EEE;" href="'.base_url().$activity['UserGUID'].'">' . $activity['UserName'] . '</a>';

    $entity_name = $activity['EntityName'];
    if (strlen($entity_name) > 30) {
        $entity_name = substr($entity_name, 0, 30) . '...';
    }

    $activity_message = $activity['Message'];
    
    
    

    if ($activity['ActivityType'] == 'PostSelf') {
                
        return str_replace('{{SUBJECT}}', $user_name, $activity['Message']);
                
    } elseif ($activity['ActivityType'] == 'GroupPostAdded') {
        
        $entity_name = '<a style="color:#135EEE;"  href="'.base_url().'group/'.$activity['EntityProfileURL'].'"  >' . $entity_name . '</a>';
        
        return str_replace('{{User}}', $user_name, $activity['Message']) . ' ' . lang('notify_posted_in') . ' ' . $entity_name;
        
        
    } elseif ($activity['ActivityType'] == 'EventWallPost') {
        
        $entity_name = '<a style="color:#135EEE;"  href="'.base_url().'events/'.$activity['EntityProfileURL'].'/about"  >' . $entity_name . '</a>';
        
        return str_replace('{{User}}', $user_name, $activity['Message']) . ' ' . lang('notify_posted_in') . ' ' . $entity_name;
        
        
    } elseif ($activity['ActivityType'] == 'PagePost') {
        
        $entity_name = '<a style="color:#135EEE;"  href="'.base_url().'page/'.$activity['EntityProfileURL'].'"  >' . $entity_name . '</a>';

        if ($activity_message == '{{User}}') {
            return str_replace('{{User}}', $entity_name, $activity['Message']);
        } else {                                    
            $title_msg = str_replace('{{User}}', $user_name, $activity['Message']);
            return str_replace('{{Entity}}', $entity_name, $title_msg);
        }
        
        
        
    } else if ($activity['ActivityType'] == 'ForumPost') {
        
        $entity_name = '<a style="color:#135EEE;"  href="'.base_url().$activity['EntityProfileURL'].'"  >' . $entity_name . '</a>';
        
        $title_msg = str_replace('{{User}}', $user_name, $activity['Message']);
        return str_replace('{{Entity}}', $entity_name, $title_msg);
    }
}
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td class="mob-padding" style="padding:10px 20px; border-bottom:1px solid #E5E5E5;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tbody>


<?php foreach ($data['activities'] as $activity): ?>

                            <tr>
                                <td style="padding:10px 0;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#F3F3F3;">
                                                        <tbody><tr>
                                                                <td class="mob-padding" style="padding:20px;">
                                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                        <tbody><tr>
                                                                                <td class="small" style="vertical-align: top; padding:0 20px 0 0; width:48px;">                                                                                                                                                                
    <?php if ($activity['UserProfilePicture']) { ?>
                                                                                        <img width="48" height="48" src="<?php echo IMAGE_SERVER_PATH . 'upload/profile/220x220/' . $activity['UserProfilePicture'] ?>"  style="border-radius:50%;" />
    <?php } else { ?>
                                                                                        <img width="48" height="48" src="<?php echo IMAGE_SERVER_PATH . 'upload/profile/220x220/user_default.jpg' ?>"  style="border-radius:50%;" />
                                                                                    <?php } ?>

                                                                                </td>
                                                                                <td class="mob-content">
                                                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                                        <tbody><tr>
                                                                                                <td>
                                                                                                    <p style="display:block; color:#444; font-size:14px; margin:0; padding:0 0 5px 0;">
    <?php echo get_activity_title_msg($activity); ?>



                                                                                                    </p>
                                                                                                    <p style="display:block; color:#868686; font-size:12px; margin:0; padding:0 0 10px 0;">
                                                                                                        <a style="color:#135EEE;" href="<?php echo site_url() . get_single_post_url($activity); ?>">
                                                                                                        <?php echo date("d M", strtotime($activity['CreatedDate'])) . ' at ' . date("h:i A", strtotime($activity['CreatedDate'])) ?>
                                                                                                        </a>

                                                                                                    </p>
                                                                                                    <p style="font-size:13px; color:#444; margin:0; padding:0 10px 0 0;">
    <?php echo $activity['PostContent']; ?>
                                                                                                    </p>
                                                                                                </td>
                                                                                            </tr>

                                                                                            <tr>
                                                                                                <td style="padding:20px 0 0;">
                                                                                                    <table border="0" cellspacing="0" cellpadding="0">
                                                                                                        <tbody><tr>
                                                                                                                <td style="padding:0 20px 0 0;">
                                                                                                                    <p style="display:block; width:58px; height:18px; border: 1px solid #E7E7E7;border-radius: 100px;background-color: #FFFFFF; padding:7px 0 3px 0;">
                                                                                                                        <span style="line-height:18px; padding:0 0 0 10px;">
    <?php if ($activity['NoOfLikes'] > 0): ?>
                                                                                                                                <img src="<?php echo ASSET_BASE_URL ?>img/emailer/heart-like.png" >
    <?php else: ?>
                                                                                                                                <img src="<?php echo ASSET_BASE_URL ?>img/emailer/heart.png" >
    <?php endif; ?>
                                                                                                                        </span>
                                                                                                                        <span style="color:#444; font-size:14px; line-height:15px; vertical-align: top;">
                                                                                                                            <?php echo $activity['NoOfLikes'] ?>
                                                                                                                        </span>
                                                                                                                    </p>
                                                                                                                </td>
                                                                                                                <td style="padding:0; color:#666; font-size:14px;">
                                                                                                                    Comment (<?php echo $activity['NoOfComments'] ?>)
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody></table>
                                                                                                </td>
                                                                                            </tr>

                                                                                        </tbody></table>
                                                                                </td>
                                                                            </tr>                                                  
                                                                        </tbody></table>
                                                                </td>
                                                            </tr>
                                                        </tbody></table>
                                                </td>
                                            </tr>
                                        </tbody></table>
                                </td>
                            </tr>



<?php endforeach; ?>




                    </tbody></table>
            </td>
        </tr>
    </tbody></table>