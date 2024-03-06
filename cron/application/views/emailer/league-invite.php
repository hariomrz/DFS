<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
        <p style="color:#3FAFEF;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:50px;">Dear <?php echo $user_name; ?>,</p>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Your friend <?php echo $content['user_name']; ?> has welcome you to play on <?php echo SITE_TITLE; ?>. Join <?php echo SITE_TITLE; ?> from website <a href="<?php echo BASE_APP_PATH; ?>">click here </a> and use league code <b><?php echo $content['code']; ?></b> to join the contest. Below are the details:</td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        League Name - <?php echo $content['contest_name']; ?><br>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Prize Pool - <?php

        if($content['prize_pool'] > 0)
        {
            if(isset($content['prize_type']) && ($content['prize_type']==2 ||$content['prize_type']==3)){
                echo $content['prize_pool'].' Coins';
            }
            else
            {
                echo CURRENCY_CODE_HTML.$content['prize_pool'];
            }
         //echo $content['prize_pool'];  echo ( isset($content['prize_type']) && ($content['prize_type']==2 ||$content['prize_type']==3))?' Coins':' '.CURRENCY_CODE_HTML; 
        }
        elseif($content['prize_pool'] == 0)
        {
            echo "WIN EXICITING PRIZES";
        }

         ?><br>
       

    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Size - <?php echo $content['size']; ?><br>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Entry Fee - <?php

        if($content['entry_fee'] > 0)
        {
            if(isset($content['prize_type']) && ($content['prize_type']==2 ||$content['prize_type']==3)){
                echo $content['entry_fee'].' Coins';
            }
            else
            {
                echo CURRENCY_CODE_HTML.$content['entry_fee'];
            }
            //echo ( isset($content['prize_type']) && ($content['prize_type']==2 ||$content['prize_type']==3))?' Coins':' '.CURRENCY_CODE_HTML; 
        }
        else if($content['entry_fee'] == 0)
        {
            echo "FREE";
        }


         ?><br>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Note - <?php echo $content['note']; ?><br>
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
<?php $this->load->view("emailer/footer"); ?>