<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
        <p style="color:#3FAFEF;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:50px;">Hi <?php echo $user_name; ?>,</p>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<!-- <tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Forgotten your password ?</td>
</tr> -->
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        You have been sent this email because we received a request to reset the password to your <?php echo SITE_TITLE; ?> Account.</td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
       To reset your password, please click the button below to enter a new password of your choice.</td>
</tr>
<tr>
    <td style="text-align: center;background-color: #fff;">
        <a href="<?php echo $content['link']; ?>" style="font-size: 16px; font-weight: bold;  line-height: 38px;  text-align: center;width: 480px;height: 38px;background-color: #00DFF1;color: #fff;border-radius: 3px;border:0;display: inline-block;text-decoration: none;box-shadow: 0 2px 4px 0 rgba(0,0,0,0.2);font-family: MuliRegular;">
            RESET PASSWORD
        </a>
     </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Cheers,<br>
        <?php echo SITE_TITLE; ?> Team
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td></td>
</tr>
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>