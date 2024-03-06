<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
        <p style="color:#3FAFEF;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:50px;">Congratulations <?php echo $user_name; ?>,</p>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">

        You've successfully joined contest with an entry fee of <b>

             <?php if($content['entry_fee'] > 0){
                ?>
        

          <span><?php echo CURRENCY_CODE_HTML; ?></span> <?php echo $content['entry_fee']; ?>

        <?php }else { echo "Free Entry"; }?>

        </b> for <b><?php echo (!empty($content['collection_name'])) ?  $content['collection_name'] : $content['contest_name']; ?></b> match.

    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
       Team fantasy wishes you luck. In case of any queries, please 
       <a target="_blank" href="<?php echo WEBSITE_URL."contact-us";?>">contact us</a>.
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