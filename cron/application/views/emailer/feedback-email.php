<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
        <p style="color:#3FAFEF;font-size:18px;font-family: 'MuliBold';margin:0px;padding:0;font-weight:bold;line-height:50px;">Hello,</p>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
        <a href="<?php echo $content['site_url']; ?>"><?php echo SITE_TITLE; ?></a> admin posted feedback to you.
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
        
            Name: <?php echo $content['full_name']; ?>
        </br>
            Email: <?php echo $content['email']; ?>
        </br>
            URL: <a href="<?php echo $content['site_url']; ?>"><?php echo $content['site_url']; ?></a>
        </br>
            Date: <?php echo $content['post_date']; ?>
        </br>
        
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:18px;">
       <?php echo $content['title']; ?>
    </td>

</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:16px;">
       <?php echo $content['description']; ?>
    </td>

</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
        <?php 
        if(!empty($content['image'])) {
        ?>
        <a href="<?php echo $content['image']; ?>" target="_blank" title="View Image"><img width="100%" src="<?php echo $content['image']; ?>" /></a>
        <?php
        }?>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
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
<?php $this->load->view("emailer/footer"); ?>