<tr>
    <td align="center" style="padding:40px 0 0; background:#FFFFFF;">
        <img src="<?php echo ASSET_BASE_URL ?>img/emailer/incomplete-registration.png" alt="Incomplete Registration">
    </td>
</tr>



<tr>
    <td class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;">
        <p style="font-family: 'Arial', sans-serif; font-size:16px; color:#444; font-weight:600; margin-bottom:20px;">
            Hi 
                
            <?php 
            if(!empty($data['user']['FirstName'])) {
                echo $data['user']['FirstName'];
            } else {
                echo 'there';
            }                
            ?>,
        </p>
        <p style="font-family: 'Arial', sans-serif; font-size:14px; color:#444; font-weight:400; line-height:24px; margin-bottom:15px;">
            We noticed that you forgot to complete your registration on Vsocial.
        </p>
        <p style="font-family: 'Arial', sans-serif; font-size:14px; color:#444; font-weight:400; line-height:24px; margin-bottom:25px;">
            How about completing your profile and making it outshine? It guarantees you to bag a few followers! wink
        </p>   
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:2px;">
            <tbody><tr>
                    <td style="width:100%; font-size:14px; font-weight:600; text-align:center; margin:0; padding:10px 0;">
                        <a style="color:#46348D;cursor:pointer; display:block" href="<?php echo site_url(); ?>">Complete your Registration</a>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php $this->load->view('emailer/notification_settings') ?>
    </td>
</tr>