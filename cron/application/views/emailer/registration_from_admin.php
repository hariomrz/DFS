;<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">                                                                <!-- $content['user_name'] remaining --> 
      <p style="color:#3FAFEF;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:50px;">Dear <?php echo $username?>,</p>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
        <p style="color:#3FAFEF;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:50px;">Welcome to <?php echo SITE_TITLE; ?></p>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
     You are registred as  <?php if($content['role']==2) { echo "Master Distributor"; } else if($content['role']==3){echo "Distributor"; } else { echo "Agent"; } ?>  on <?php echo SITE_TITLE; ?>. Please click on the link below to login.  
    </td>
    
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        <a href="<?php echo $content['site_url']; ?>">click here</a>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
     Your login email  : <?php echo $content['email']; ?> 
    </td>
    
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
    Password :   <?php echo $content['password']; ?> 
    </td>
    
</tr>


<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
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
    <td></td>
</tr>
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
